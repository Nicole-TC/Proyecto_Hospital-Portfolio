<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasListasEspera {

    /**
     * Devuelve los pacientes en lista de espera.
     * @param string $estado_apto (OK, OBS, RECH, TODOS_MENOS_PENDIENTE)
     */
    public static function listarPacientesEspera(string $estado_apto = 'OK', string $fecha_desde = '', string $fecha_hasta = ''): array {
        $pdo = conexionCircuitoQuirurgico();

        $estado_apto = mb_strtoupper(trim($estado_apto), 'UTF-8');
        $estadosPermitidos = ['OK', 'OBS', 'TODOS'];

        if (!in_array($estado_apto, $estadosPermitidos)) {
            $estado_apto = 'OK';
        }

        // Excluir órdenes eliminadas o canceladas. Ya no filtramos FIN porque pueden querer ver la estadística.
        $where = "WHERE 1=1";
        $params = [];

        $idElim = id_estado('DATO', 'ELIM');
        $idCanc = id_estado('ORDEN', 'CANC');

        $where .= " AND om.id_estado NOT IN (:id_elim, :id_canc)";
        $params[':id_elim'] = $idElim ?: 0;
        $params[':id_canc'] = $idCanc ?: 0;

        // Filtrar por el estado del Apto Físico (Verificación)
        if ($estado_apto === 'TODOS') {
            // Todos los que tengan alguna verificación (excluye pendientes puros)
            $where .= " AND EXISTS (SELECT 1 FROM verificacion_pre_cirugia v2 WHERE v2.id_orden = om.id_orden)";
        } else {
            // Buscar que LA ÚLTIMA verificación coincida con el estado solicitado
            $where .= " AND (
                SELECT ev.codigo
                FROM verificacion_pre_cirugia v2
                INNER JOIN estado ev ON ev.id_estado = v2.id_estado
                WHERE v2.id_orden = om.id_orden
                ORDER BY v2.fecha DESC NULLS LAST, v2.id_verificacion DESC
                LIMIT 1
            ) = :estado_apto";
            $params[':estado_apto'] = $estado_apto;
        }
        
        // Filtros de fecha sobre la última verificación (Apto Físico)
        if ($fecha_desde !== '' && $fecha_hasta !== '') {
            $where .= " AND (
                SELECT DATE(v2.fecha)
                FROM verificacion_pre_cirugia v2
                WHERE v2.id_orden = om.id_orden
                ORDER BY v2.fecha DESC NULLS LAST, v2.id_verificacion DESC
                LIMIT 1
            ) BETWEEN :fecha_desde AND :fecha_hasta";
            $params[':fecha_desde'] = $fecha_desde;
            $params[':fecha_hasta'] = $fecha_hasta;
        }

        $from = "
            FROM orden_medica om
            INNER JOIN persona_asiste pa ON pa.id_persona_asiste = om.id_persona_asiste
            INNER JOIN persona p ON p.id_persona = pa.id_persona
            LEFT JOIN catalogo_cirugia c ON c.id_cirugia_catalogo = om.id_cirugia_catalogo
        ";

        $sql = "
            SELECT 
                om.id_orden, 
                pa.id_persona_asiste,
                p.dni, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                c.nombre AS cirugia_nombre,
                c.id_cirugia_catalogo,
                (
                    SELECT string_agg(especialidades.nombre, ', ' ORDER BY especialidades.nombre)
                    FROM (
                        SELECT DISTINCT CASE
                            WHEN e.id_especialidad_padre IS NULL THEN e.nombre
                            ELSE padre.nombre
                        END AS nombre
                        FROM profesional_especialidad pe
                        INNER JOIN especialidad e ON e.id_especialidad = pe.id_especialidad
                        LEFT JOIN especialidad padre ON padre.id_especialidad = e.id_especialidad_padre
                        WHERE pe.id_profesional = om.id_profesional
                          AND e.id_estado = :id_estado_especialidad
                          AND (e.id_especialidad_padre IS NULL OR padre.id_estado = :id_estado_padre)
                    ) especialidades
                ) AS especialidad_nombre,
                (
                    SELECT string_agg(subespecialidades.nombre, ', ' ORDER BY subespecialidades.nombre)
                    FROM (
                        SELECT DISTINCT e.nombre
                        FROM profesional_especialidad pe
                        INNER JOIN especialidad e ON e.id_especialidad = pe.id_especialidad
                        WHERE pe.id_profesional = om.id_profesional
                          AND e.id_especialidad_padre IS NOT NULL
                          AND e.id_estado = :id_estado_subespecialidad
                    ) subespecialidades
                ) AS subespecialidad_nombre,
                (
                    SELECT v.fecha
                    FROM verificacion_pre_cirugia v
                    WHERE v.id_orden = om.id_orden
                    ORDER BY v.fecha DESC NULLS LAST, v.id_verificacion DESC
                    LIMIT 1
                ) AS apto_desde,
                (
                    SELECT ev.codigo
                    FROM verificacion_pre_cirugia v
                    INNER JOIN estado ev ON ev.id_estado = v.id_estado
                    WHERE v.id_orden = om.id_orden
                    ORDER BY v.fecha DESC NULLS LAST, v.id_verificacion DESC
                    LIMIT 1
                ) AS estado_apto_codigo,
                (
                    SELECT t.fecha_hora
                    FROM turno t
                    INNER JOIN tipo_turno tt ON tt.id_tipo_turno = t.id_tipo_turno
                    INNER JOIN estado et ON et.id_estado = t.id_estado
                    WHERE t.id_orden = om.id_orden 
                      AND LOWER(tt.codigo) = 'cir' 
                      AND et.codigo IN ('PROG', 'CONF')
                    ORDER BY t.fecha_hora ASC
                    LIMIT 1
                ) AS turno_programado_fecha
            $from 
            $where 
            ORDER BY apto_desde ASC NULLS LAST
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $idActivo = id_estado('USUARIO', 'ACT');
        $stmt->bindValue(':id_estado_especialidad', $idActivo, PDO::PARAM_INT);
        $stmt->bindValue(':id_estado_padre', $idActivo, PDO::PARAM_INT);
        $stmt->bindValue(':id_estado_subespecialidad', $idActivo, PDO::PARAM_INT);
        $stmt->execute();
        
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prioridad se resuelve en PHP para no acoplarla al SQL principal.
        // Se toma la prioridad de la ÚLTIMA verificación de cada orden.
        $ids = array_column($registros, 'id_orden');
        $prioPorOrden = [];
        if (!empty($ids)) {
            $inPlaceholders = implode(',', array_fill(0, count($ids), '?'));
            $sqlPrio = "
                SELECT DISTINCT ON (v.id_orden) v.id_orden, ep.codigo AS prioridad_codigo
                FROM verificacion_pre_cirugia v
                LEFT JOIN estado ep ON ep.id_estado = v.id_estado_prioridad
                WHERE v.id_orden IN ($inPlaceholders)
                ORDER BY v.id_orden, v.fecha DESC NULLS LAST, v.id_verificacion DESC
            ";
            $stmtPrio = $pdo->prepare($sqlPrio);
            $stmtPrio->execute($ids);
            foreach ($stmtPrio->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $prioPorOrden[$r['id_orden']] = $r['prioridad_codigo'];
            }
        }

        $pesoPrioridad = ['ROJO' => 1, 'AMARILLO' => 2, 'VERDE' => 3];
        foreach ($registros as &$reg) {
            $reg['prioridad_codigo'] = $prioPorOrden[$reg['id_orden']] ?? null;
        }
        unset($reg);

        usort($registros, function ($a, $b) use ($pesoPrioridad) {
            $pa = $pesoPrioridad[$a['prioridad_codigo']] ?? 4;
            $pb = $pesoPrioridad[$b['prioridad_codigo']] ?? 4;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }
            return strcmp((string)($a['apto_desde'] ?? ''), (string)($b['apto_desde'] ?? ''));
        });

        return [
            'registros' => $registros,
            'total'     => count($registros),
        ];
    }

    public static function obtenerCatalogosEspecialidades(): array {
        $pdo = conexionCircuitoQuirurgico();
        $idActivo = id_estado('USUARIO', 'ACT');
        $sql = "SELECT id_especialidad, nombre, id_especialidad_padre
                FROM especialidad
                WHERE id_estado = :id_activo
                ORDER BY nombre";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_activo', $idActivo, PDO::PARAM_INT);
        $stmt->execute();

        $especialidades = [];
        $subespecialidades = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $registro) {
            if ($registro['id_especialidad_padre'] === null) {
                $especialidades[] = $registro;
            } else {
                $subespecialidades[] = $registro;
            }
        }

        return [
            'especialidades' => $especialidades,
            'subespecialidades' => $subespecialidades,
        ];
    }

    public static function obtenerCirujanos(): array {
        $pdo = conexionCircuitoQuirurgico();
        // Obtener todos los profesionales registrados (podría filtrarse por especialidad quirúrgica si existiese)
        $sql = "
            SELECT prof.id_profesional, p.nombre, p.apellido
            FROM profesional prof
            INNER JOIN persona p ON p.id_persona = prof.id_persona
            ORDER BY p.apellido ASC, p.nombre ASC
        ";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
