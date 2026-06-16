<?php
$envPath = __DIR__ . '/../../../../.env';
if (empty(getenv('DB_HOST')) && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) putenv(trim($parts[0]) . '=' . trim($parts[1]));
    }
}

require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasAsistencia {

    public static function listarTurnos($filtro = null, $valor = null, $pagina = 1, $limite = 10) {
        $pdo    = conexionCircuitoQuirurgico();
        $offset = ($pagina - 1) * $limite;

        $where  = 'WHERE 1=1';
        $params = [];

        if (!empty($filtro) && !empty($valor)) {
            $v = '%' . trim($valor) . '%';
            if ($filtro === 'dni') {
                $where .= ' AND p.dni LIKE :valor';
            } elseif ($filtro === 'apellido') {
                $where .= ' AND p.apellido ILIKE :valor';
            } elseif ($filtro === 'nombre') {
                $where .= ' AND p.nombre ILIKE :valor';
            }
            $params[':valor'] = $v;
        }

        $from = "
            FROM turno t
            JOIN persona_asiste pa ON pa.id_persona_asiste = t.id_persona_asiste
            JOIN persona p          ON p.id_persona         = pa.id_persona
            JOIN tipo_turno tt     ON tt.id_tipo_turno     = t.id_tipo_turno
            JOIN estado e          ON e.id_estado          = t.id_estado
            LEFT JOIN orden_medica om ON om.id_orden        = t.id_orden
            LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
            LEFT JOIN profesional prf ON prf.id_profesional = om.id_profesional
            LEFT JOIN persona pr      ON pr.id_persona      = prf.id_persona
        ";

        $sqlCount = "SELECT COUNT(*) $from $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $sql = "
            SELECT
                t.id_turno,
                p.nombre,
                p.apellido,
                p.dni,
                t.fecha_hora,
                tt.descripcion AS tipo_turno,
                e.descripcion  AS estado,
                e.codigo       AS estado_codigo,
                cc.nombre      AS cirugia,
                TRIM(COALESCE(pr.apellido, '') || ', ' || COALESCE(pr.nombre, '')) AS medico,
                t.motivo_cancelacion
            $from
            $where
            ORDER BY t.fecha_hora DESC
            LIMIT :limite OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'turnos' => $turnos,
            'total'  => $total,
            'pagina' => $pagina,
            'limite' => $limite,
        ];
    }

    public static function obtenerTurno($id_turno) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "
            SELECT
                t.id_turno, t.id_persona_asiste, t.id_orden, t.fecha_hora,
                t.id_estado,
                e.codigo AS estado_codigo, e.descripcion AS estado_descripcion,
                p.nombre, p.apellido, p.dni,
                tt.descripcion AS tipo_turno,
                cc.nombre AS cirugia,
                TRIM(COALESCE(pr.apellido, '') || ', ' || COALESCE(pr.nombre, '')) AS medico,
                t.motivo_cancelacion, t.cancelado, t.fecha_cancelacion,
                t.ausente, t.fecha_ausencia
            FROM turno t
            JOIN persona_asiste pa ON pa.id_persona_asiste = t.id_persona_asiste
            JOIN persona p          ON p.id_persona         = pa.id_persona
            JOIN tipo_turno tt     ON tt.id_tipo_turno     = t.id_tipo_turno
            JOIN estado e          ON e.id_estado          = t.id_estado
            LEFT JOIN orden_medica om ON om.id_orden        = t.id_orden
            LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
            LEFT JOIN profesional prf ON prf.id_profesional = om.id_profesional
            LEFT JOIN persona pr      ON pr.id_persona      = prf.id_persona
            WHERE t.id_turno = :id_turno
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_turno', $id_turno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function cambiarEstadoTurno($id_turno, $nuevo_codigo_estado, $motivo = null) {
        $pdo = conexionCircuitoQuirurgico();
        $pdo->beginTransaction();

        try {
            $turno = self::obtenerTurno($id_turno);
            if (!$turno) {
                throw new Exception('Turno no encontrado.');
            }

            $id_nuevo_estado = id_estado('TURNO', $nuevo_codigo_estado);
            if (!$id_nuevo_estado) {
                throw new Exception("No se encontró el estado TURNO/$nuevo_codigo_estado en la base de datos.");
            }

            $codigos_permitidos = ['CONF', 'AUS', 'CANC'];
            if (!in_array($nuevo_codigo_estado, $codigos_permitidos)) {
                throw new Exception("El estado '$nuevo_codigo_estado' no es una acción válida.");
            }

            $sql = "UPDATE turno SET id_estado = :id_estado";
            $params = [':id_estado' => $id_nuevo_estado, ':id_turno' => $id_turno];

            if ($nuevo_codigo_estado === 'CANC') {
                $sql .= ", cancelado = :cancelado, fecha_cancelacion = NOW(), motivo_cancelacion = :motivo";
                $params[':cancelado'] = true;
                $params[':motivo'] = $motivo;
            } elseif ($nuevo_codigo_estado === 'AUS') {
                $sql .= ", ausente = :ausente, fecha_ausencia = NOW()";
                $params[':ausente'] = true;
            }

            $sql .= " WHERE id_turno = :id_turno";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $descripcion_evento = '';
            switch ($nuevo_codigo_estado) {
                case 'CONF': $descripcion_evento = 'Paciente confirmó asistencia al turno'; break;
                case 'AUS':  $descripcion_evento = 'Paciente ausente al turno'; break;
                case 'CANC': $descripcion_evento = 'Turno cancelado: ' . ($motivo ?: 'Sin motivo especificado'); break;
            }

            $sqlEvento = "
                INSERT INTO evento_clinico (id_tipo_evento, id_turno, id_orden, fecha_evento, descripcion)
                VALUES (
                    (SELECT id_tipo_evento FROM tipo_evento WHERE LOWER(evento) = 'cambio de estado' LIMIT 1),
                    :id_turno, :id_orden, NOW(), :descripcion
                )
            ";
            $stmtEv = $pdo->prepare($sqlEvento);
            $stmtEv->execute([
                ':id_turno'   => $id_turno,
                ':id_orden'   => $turno['id_orden'],
                ':descripcion' => $descripcion_evento,
            ]);

            $pdo->commit();
            return ['ok' => true, 'mensaje' => "Turno marcado como: " . $descripcion_evento];

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
