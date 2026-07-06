<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/ordenes_medicas-b.php';

class ConsultasSeguimiento {

    public static function listarSeguimiento(string $buscar, int $pagina, int $limite): array {
        $pdo = conexionCircuitoQuirurgico();
        $offset = ($pagina - 1) * $limite;
        
        // Filtramos por órdenes que no estén ni canceladas ni eliminadas, y que no sean rechazadas.
        // Asumimos que los estados ocultos definidos en ordenes_medicas aplican aquí.
        // PEND, PROC son válidos.
        
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

        $sqlCount = "SELECT COUNT(*) $from $where";
        $stmtC = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
        $stmtC->execute();
        $total = (int)$stmtC->fetchColumn();

        $sql = "
            SELECT 
                om.id_orden, 
                om.fecha_orden,
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
            LIMIT :limite OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite',  $limite,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset,  PDO::PARAM_INT);
        $stmt->execute();
        
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parsear JSON strings a arrays en PHP
        foreach ($registros as &$registro) {
            $registro['estudios'] = json_decode($registro['estudios'] ?? '[]', true) ?: [];
            $registro['apto_fisico'] = json_decode($registro['apto_fisico'] ?? '{}', true) ?: null;
            
            // Verificar si todos los estudios requeridos (postas) están realizados
            $estudiosRealizados = 0;
            $totalEstudios = count($registro['estudios']);
            foreach ($registro['estudios'] as $estudio) {
                if (($estudio['estado_codigo'] ?? '') === 'REAL') {
                    $estudiosRealizados++;
                }
            }
            $registro['postas_completas'] = $totalEstudios > 0 && $estudiosRealizados === $totalEstudios;
        }

        return [
            'registros' => $registros,
            'total'     => $total,
            'pagina'    => $pagina,
            'limite'    => $limite,
        ];
    }

    public static function registrarAptoFisico(int $id_orden, int $id_profesional, string $observaciones, string $codigo_estado): bool {
        $pdo = conexionCircuitoQuirurgico();
        
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
