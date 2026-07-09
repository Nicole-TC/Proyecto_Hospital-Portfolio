<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/ordenes_medicas-b.php';

/*
 * se lanza cuando ya existe un Apto Físico registrado para la
 * orden y se intenta registrar otro. Transporta los datos del registro
 * previo para que el endpoint los pueda devolver al frontend.
 */
class AptoDuplicadoException extends Exception {
    public string $fechaExistente;
    public string $estadoExistente;

    public function __construct(string $fechaExistente, string $estadoExistente) {
        $this->fechaExistente = $fechaExistente;
        $this->estadoExistente = $estadoExistente;
        parent::__construct('Ya existe un Apto Físico registrado para esta orden.');
    }
}

class ConsultasSeguimiento {

    // fecha_desde, fecha_hasta y estado_apto son OBLIGATORIOS.
    // Sin los 3 completos, no se devuelve ningún registro.
    public static function listarSeguimiento(string $fecha_desde, string $fecha_hasta, string $estado_apto, string $buscar = ''): array {
        $pdo = conexionCircuitoQuirurgico();

        $estado_apto = mb_strtoupper(trim($estado_apto), 'UTF-8');

        if ($fecha_desde === '' || $fecha_hasta === '' || $estado_apto === '') {
            return ['registros' => [], 'total' => 0];
        }

        if (!in_array($estado_apto, ['PEND', 'OK', 'RECH', 'TODOS'])) {
            throw new Exception("Estado de Apto Físico inválido.");
        }

        // Filtramos por órdenes que no estén ni canceladas ni eliminadas, y que no sean rechazadas.
        $where = "WHERE 1=1";
        $params = [];

        // Excluir estados ocultos (ELIM, RECH) y CANC
        $idElim = id_estado('DATO', 'ELIM');
        $idRech = id_estado('ORDEN', 'RECH');
        $idCanc = id_estado('ORDEN', 'CANC');

        $where .= " AND om.id_estado NOT IN (:id_elim, :id_rech, :id_canc)";
        $params[':id_elim'] = $idElim ?: 0;
        $params[':id_rech'] = $idRech ?: 0;
        $params[':id_canc'] = $idCanc ?: 0;

        // rango de fechas de admisión (obligatorio)
        $where .= " AND om.fecha_orden::date BETWEEN :fecha_desde AND :fecha_hasta";
        $params[':fecha_desde'] = $fecha_desde;
        $params[':fecha_hasta'] = $fecha_hasta;

        // estado del Apto Físico (obligatorio, TODOS = sin restricción)
        if ($estado_apto === 'PEND') {
            $where .= " AND NOT EXISTS (SELECT 1 FROM verificacion_pre_cirugia v2 WHERE v2.id_orden = om.id_orden)";
        } elseif ($estado_apto !== 'TODOS') {
            $where .= " AND EXISTS (
                SELECT 1 FROM verificacion_pre_cirugia v2
                INNER JOIN estado ev2 ON ev2.id_estado = v2.id_estado
                WHERE v2.id_orden = om.id_orden AND ev2.codigo = :estado_apto
            )";
            $params[':estado_apto'] = $estado_apto;
        }

        if ($buscar !== '') {
            $where .= " AND (p.nombre ILIKE :buscar OR p.apellido ILIKE :buscar OR p.dni LIKE :buscar OR c.nombre ILIKE :buscar)";
            $params[':buscar'] = '%' . $buscar . '%';
        }

        $from = "
            FROM orden_medica om
            INNER JOIN estado e_om ON e_om.id_estado = om.id_estado
            INNER JOIN persona_asiste pa ON pa.id_persona_asiste = om.id_persona_asiste
            INNER JOIN persona p ON p.id_persona = pa.id_persona
            LEFT JOIN catalogo_cirugia c ON c.id_cirugia_catalogo = om.id_cirugia_catalogo
        ";

        $sql = "
            SELECT 
                om.id_orden, 
                om.fecha_orden,
                om.observaciones,
                p.dni, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                c.nombre AS cirugia_nombre,
                e_om.codigo AS estado_orden,
                (
                    SELECT json_agg(json_build_object(
                        'id_estudio', e.id_estudio,
                        'nombre', e.nombre,
                        'estado_codigo', ee.codigo,
                        'categoria', ce.nombre
                    ))
                    FROM orden_estudio oe
                    JOIN estudio e ON e.id_estudio = oe.id_estudio
                    JOIN estado ee ON ee.id_estado = oe.id_estado
                    LEFT JOIN categoria_estudio ce ON ce.id_categoria_estudio = e.id_categoria_estudio
                    WHERE oe.id_orden = om.id_orden
                ) AS estudios,
                (
                    SELECT json_build_object(
                        'id_verificacion', v.id_verificacion,
                        'estado_codigo', ev.codigo,
                        'checklist', v.checklist,
                        'fecha', v.fecha,
                        'profesional_nombre', pp.nombre,
                        'profesional_apellido', pp.apellido
                    )
                    FROM verificacion_pre_cirugia v
                    JOIN estado ev ON ev.id_estado = v.id_estado
                    LEFT JOIN profesional pr ON pr.id_profesional = v.id_profesional
                    LEFT JOIN persona pp ON pp.id_persona = pr.id_persona
                    WHERE v.id_orden = om.id_orden
                    ORDER BY v.fecha DESC NULLS LAST, v.id_verificacion DESC
                    LIMIT 1
                ) AS apto_fisico
            $from 
            $where 
            ORDER BY om.id_orden DESC
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parsear JSON strings a arrays en PHP
        foreach ($registros as &$registro) {
            $registro['estudios'] = json_decode($registro['estudios'] ?? '[]', true) ?: [];
            $registro['apto_fisico'] = json_decode($registro['apto_fisico'] ?? '{}', true) ?: null;

            $estudiosRealizados = 0;
            $totalEstudios = count($registro['estudios']);
            foreach ($registro['estudios'] as $estudio) {
                if (($estudio['estado_codigo'] ?? '') === 'REAL') {
                    $estudiosRealizados++;
                }
            }
            $registro['postas_completas'] = $totalEstudios === 0 || $estudiosRealizados === $totalEstudios;
        }

        return [
            'registros' => $registros,
            'total'     => count($registros),
        ];
    }

    public static function registrarAptoFisico(int $id_orden, int $id_profesional, string $observaciones, string $codigo_estado): bool {
        $pdo = conexionCircuitoQuirurgico();

        // el Apto Físico se carga UNA sola vez por orden. Si ya
        // existe cualquier verificación previa (OK o RECH), se rechaza el
        // registro y se informa fecha/hora + estado del registro existente.
        $stmtCheck = $pdo->prepare("
            SELECT v.fecha, ev.codigo AS estado_codigo
            FROM verificacion_pre_cirugia v
            INNER JOIN estado ev ON ev.id_estado = v.id_estado
            WHERE v.id_orden = :id_orden
            ORDER BY v.fecha DESC NULLS LAST, v.id_verificacion DESC
            LIMIT 1
        ");
        $stmtCheck->execute([':id_orden' => $id_orden]);
        $existente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            throw new AptoDuplicadoException($existente['fecha'], $existente['estado_codigo']);
        }

        // Obtener id de estado
        $idEstado = id_estado('VERIFICACION', $codigo_estado);
        if (!$idEstado) {
            // Fallback si no existe el estado en VERIFICACION, intentar genérico o lanzar error.
            // Creamos una regla: si no lo encuentra, lanzar excepción.
            throw new Exception("El estado '$codigo_estado' no existe para Apto Físico.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO verificacion_pre_cirugia (id_orden, id_profesional, checklist, id_estado, fecha) 
            VALUES (:id_orden, :id_profesional, :checklist, :id_estado, NOW())
        ");
        
        return $stmt->execute([
            ':id_orden'       => $id_orden,
            ':id_profesional' => $id_profesional,
            ':checklist'      => $observaciones,
            ':id_estado'      => $idEstado
        ]);
    }
}
?>
