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

class ConsultasAgenda {

    // ── Obtener turnos ─────────────────────────────────────────────────
    public static function obtenerTurnos($fecha = null) {
        $pdo = conexionCircuitoQuirurgico();
        $where = $fecha ? "WHERE DATE(t.fecha_hora) = :fecha" : "";
        $sql = "
            SELECT
                t.id_turno,
                p.nombre,
                p.apellido,
                p.dni,
                t.fecha_hora,
                tt.descripcion AS tipo_turno,
                e.descripcion  AS estado,
                e.codigo       AS estado_codigo
            FROM turno t
            JOIN persona_asiste pa ON pa.id_persona_asiste = t.id_persona_asiste
            JOIN persona p          ON p.id_persona         = pa.id_persona
            JOIN tipo_turno tt      ON tt.id_tipo_turno     = t.id_tipo_turno
            JOIN estado e           ON e.id_estado          = t.id_estado
            $where
            ORDER BY t.fecha_hora DESC
            LIMIT 100
        ";
        $stmt = $pdo->prepare($sql);
        if ($fecha) $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Buscar turnos ──────────────────────────────────────────────────
    public static function buscarTurnos($filtro, $query) {
    $pdo = conexionCircuitoQuirurgico();

    if ($filtro === 'nombre') {
        $campo = 'LOWER(p.nombre)';
    } elseif ($filtro === 'dni') {
        $campo = 'p.dni';
    } else {
        $campo = 'LOWER(p.apellido)';
    }

    // Si hay query filtrar, si no traer todos
    $where = '';
    $params = [];
    if (!empty($query) && strlen($query) >= 2) {
        $like = '%' . strtolower($query) . '%';
        $where = "WHERE $campo LIKE :like";
        $params[':like'] = $like;
    }

    $sql = "
        SELECT
            t.id_turno,
            p.nombre,
            p.apellido,
            p.dni,
            t.fecha_hora,
            tt.descripcion AS tipo_turno,
            e.descripcion  AS estado,
            e.codigo       AS estado_codigo
        FROM turno t
        JOIN persona_asiste pa ON pa.id_persona_asiste = t.id_persona_asiste
        JOIN persona p          ON p.id_persona         = pa.id_persona
        JOIN tipo_turno tt      ON tt.id_tipo_turno     = t.id_tipo_turno
        JOIN estado e           ON e.id_estado          = t.id_estado
        $where
        ORDER BY t.fecha_hora DESC
        LIMIT 100
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}