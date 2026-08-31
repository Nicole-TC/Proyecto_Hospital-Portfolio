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
    // $tipo_fecha define sobre qué campo se aplica el rango:
    // ADM = fecha de admisión (om.fecha_orden), TUR = fecha del turno prequirúrgico.
    public static function listarSeguimiento(string $fecha_desde, string $fecha_hasta, string $estado_apto, string $buscar = '', string $tipo_fecha = 'ADM'): array {
        $pdo = conexionCircuitoQuirurgico();

        $estado_apto = mb_strtoupper(trim($estado_apto), 'UTF-8');
        $tipo_fecha  = mb_strtoupper(trim($tipo_fecha), 'UTF-8');

        if ($fecha_desde === '' || $fecha_hasta === '' || $estado_apto === '') {
            return ['registros' => [], 'total' => 0];
        }

        if (!in_array($estado_apto, ['PEND', 'OK', 'OBS', 'TODOS'])) {
            throw new Exception("Estado de Apto Físico inválido.");
        }

        if (!in_array($tipo_fecha, ['ADM', 'TUR'])) {
            throw new Exception("Tipo de fecha inválido. Debe ser ADM o TUR.");
        }

        // (corrección) no permitir fecha futura en "Admisión hasta",
        // validado también acá para no depender solo del input del navegador.
        $hoy = date('Y-m-d');
        if ($fecha_hasta > $hoy) {
            throw new Exception('La fecha "Admisión hasta" no puede ser posterior a hoy.');
        }
        if ($fecha_desde > $fecha_hasta) {
            throw new Exception('La fecha "Admisión desde" no puede ser posterior a "Admisión hasta".');
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

        // rango de fechas (obligatorio). Según el select "BUSCAR POR" de la vista,
        // se aplica sobre la fecha de admisión o sobre la del turno prequirúrgico.
        // Se excluyen los turnos cancelados para no traer una orden por un turno caído.
        if ($tipo_fecha === 'TUR') {
            $where .= " AND EXISTS (
                SELECT 1
                FROM turno t
                INNER JOIN tipo_turno tt ON tt.id_tipo_turno = t.id_tipo_turno
                INNER JOIN estado et ON et.id_estado = t.id_estado
                WHERE t.id_orden = om.id_orden
                  AND LOWER(tt.codigo) = 'pre'
                  AND et.codigo <> 'CANC'
                  AND t.fecha_hora::date BETWEEN :fecha_desde AND :fecha_hasta
            )";
        } else {
            $where .= " AND om.fecha_orden::date BETWEEN :fecha_desde AND :fecha_hasta";
        }
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

    public static function registrarAptoFisico(int $id_orden, int $id_profesional, string $observaciones, string $codigo_estado, ?string $codigo_prioridad = null): bool {
        $pdo = conexionCircuitoQuirurgico();

        // el Apto Físico se carga UNA sola vez por orden. Si ya
        // existe cualquier verificación previa (OK u OBS), se rechaza el
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

        // La prioridad solo aplica cuando el Apto Físico es aprobado (OK).
        // Se almacena como una referencia a estado (entidad LISTA: ROJO/AMARILLO/VERDE).
        $idEstadoPrioridad = null;
        if ($codigo_estado === 'OK' && $codigo_prioridad !== null) {
            $codigo_prioridad = mb_strtoupper(trim($codigo_prioridad), 'UTF-8');
            if (!in_array($codigo_prioridad, ['ROJO', 'AMARILLO', 'VERDE'], true)) {
                throw new Exception("El código de prioridad '$codigo_prioridad' no es válido. Debe ser ROJO, AMARILLO o VERDE.");
            }
            $idEstadoPrioridad = id_estado('LISTA', $codigo_prioridad);
            if (!$idEstadoPrioridad) {
                throw new Exception("El estado de prioridad '$codigo_prioridad' no existe en la tabla estado.");
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO verificacion_pre_cirugia (id_orden, id_profesional, checklist, id_estado, id_estado_prioridad, fecha) 
            VALUES (:id_orden, :id_profesional, :checklist, :id_estado, :id_estado_prioridad, NOW())
        ");
        
        return $stmt->execute([
            ':id_orden'             => $id_orden,
            ':id_profesional'       => $id_profesional,
            ':checklist'            => $observaciones,
            ':id_estado'            => $idEstado,
            ':id_estado_prioridad'  => $idEstadoPrioridad
        ]);
    }

    // tildado de estudios/postas. Solo llamable por admin.
    public static function marcarEstudio(int $id_orden, int $id_estudio, string $codigo_estado): bool {
        $pdo = conexionCircuitoQuirurgico();

        $idEstado = id_estado('ESTUDIO', $codigo_estado);
        if (!$idEstado) {
            throw new Exception("El estado '$codigo_estado' no existe para Estudio.");
        }

        $stmt = $pdo->prepare("
            UPDATE orden_estudio
            SET id_estado = :id_estado
            WHERE id_orden = :id_orden AND id_estudio = :id_estudio
        ");

        return $stmt->execute([
            ':id_estado'  => $idEstado,
            ':id_orden'   => $id_orden,
            ':id_estudio' => $id_estudio,
        ]);
    }

    // Obtiene los estudios activos del sistema que NO están ya asignados a esta orden
    public static function obtenerEstudiosDisponibles(int $id_orden): array {
        $pdo = conexionCircuitoQuirurgico();
        // Asumiendo que el id_estado de un estudio activo (Habilitado) es recuperable o podemos filtrarlo. 
        // O simplemente traemos todos los estudios que no estén en la orden.
        $stmt = $pdo->prepare("
            SELECT e.id_estudio AS id, e.nombre, c.nombre AS categoria
            FROM estudio e
            LEFT JOIN categoria_estudio c ON c.id_categoria_estudio = e.id_categoria_estudio
            WHERE e.id_estudio NOT IN (
                SELECT oe.id_estudio FROM orden_estudio oe WHERE oe.id_orden = :id_orden
            )
            ORDER BY e.nombre ASC
        ");
        $stmt->execute([':id_orden' => $id_orden]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agrega un estudio nuevo a la orden con estado Pendiente
    public static function agregarEstudioAdicional(int $id_orden, int $id_estudio): bool {
        $pdo = conexionCircuitoQuirurgico();

        // Verificar que el Apto Físico no haya sido evaluado ya
        $stmtApto = $pdo->prepare("SELECT 1 FROM verificacion_pre_cirugia WHERE id_orden = :id_orden LIMIT 1");
        $stmtApto->execute([':id_orden' => $id_orden]);
        if ($stmtApto->fetch()) {
            throw new Exception("No se pueden agregar estudios adicionales porque el Apto Físico ya fue evaluado.");
        }

        $idEstadoPendiente = id_estado('ESTUDIO', 'PEND');
        if (!$idEstadoPendiente) {
            throw new Exception("El estado 'PEND' no existe para Estudio.");
        }

        // Verificamos (evitar duplicados si llaman rápido a la API)
        $stmtCheck = $pdo->prepare("SELECT 1 FROM orden_estudio WHERE id_orden = :id_orden AND id_estudio = :id_estudio");
        $stmtCheck->execute([':id_orden' => $id_orden, ':id_estudio' => $id_estudio]);
        if ($stmtCheck->fetch()) {
            throw new Exception("El estudio seleccionado ya está asignado a esta orden.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO orden_estudio (id_orden, id_estudio, id_estado)
            VALUES (:id_orden, :id_estudio, :id_estado)
        ");

        return $stmt->execute([
            ':id_orden'   => $id_orden,
            ':id_estudio' => $id_estudio,
            ':id_estado'  => $idEstadoPendiente
        ]);
    }
}
?>