<?php
$envPath = __DIR__ . '/../../../../.env';
if (empty(getenv('DB_HOST')) && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

require_once __DIR__ . '/../../../model/cir_conexion.php';

class ConsultasAgenda {


    // ─ Obtener cupos por fecha ─
    public static function obtenerCuposPorFecha($fecha) {
        $pdo = conexionCircuitoQuirurgico();

        // Total de turnos del día
        $sqlOcupados = "
            SELECT COUNT(*) AS ocupados
            FROM turno
            WHERE DATE(fecha_hora) = :fecha
        ";
        $stmt = $pdo->prepare($sqlOcupados);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        $ocupados = (int)$stmt->fetch(PDO::FETCH_ASSOC)['ocupados'];

        // Cupo configurado para ese día (tabla agenda_cupos)
        $sqlCupo = "
            SELECT cupos_total
            FROM agenda_cupos
            WHERE fecha = :fecha
            LIMIT 1
        ";
        $stmt2 = $pdo->prepare($sqlCupo);
        $stmt2->bindParam(':fecha', $fecha);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $total = $row ? (int)$row['cupos_total'] : 20; // default 20

        return [
            'total'    => $total,
            'ocupados' => $ocupados,
            'libres'   => max(0, $total - $ocupados)
        ];
    }

    // ── Guardar cupos para un día ──────────────────────────────────────
    public static function guardarCupos($fecha, $cupos_total) {
        $pdo = conexionCircuitoQuirurgico();

        // UPSERT: si ya existe el registro lo actualiza, si no lo inserta
        $sql = "
            INSERT INTO agenda_cupos (fecha, cupos_total)
            VALUES (:fecha, :cupos_total)
            ON CONFLICT (fecha)
            DO UPDATE SET cupos_total = EXCLUDED.cupos_total
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':fecha',       $fecha);
        $stmt->bindParam(':cupos_total', $cupos_total, PDO::PARAM_INT);
        return $stmt->execute();
    }
}