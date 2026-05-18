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

    // ── Obtener cupos por fecha ────────────────────────────────────────
    public static function obtenerCuposPorFecha($fecha) {
        $pdo = conexionCircuitoQuirurgico();

        $sqlOcupados = "SELECT COUNT(*) AS ocupados FROM turno WHERE DATE(fecha_hora) = :fecha";
        $stmt = $pdo->prepare($sqlOcupados);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        $ocupados = (int)$stmt->fetch(PDO::FETCH_ASSOC)['ocupados'];

        $sqlCupo = "SELECT cupos_total FROM agenda_cupos WHERE fecha = :fecha LIMIT 1";
        $stmt2 = $pdo->prepare($sqlCupo);
        $stmt2->bindParam(':fecha', $fecha);
        $stmt2->execute();
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $total = $row ? (int)$row['cupos_total'] : 20;

        return [
            'total'    => $total,
            'ocupados' => $ocupados,
            'libres'   => max(0, $total - $ocupados)
        ];
    }

    // ── Guardar cupos para un día ──────────────────────────────────────
    public static function guardarCupos($fecha, $cupos_total) {
        $pdo = conexionCircuitoQuirurgico();
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

    // ── Obtener tipos de turno ─────────────────────────────────────────
    public static function obtenerTiposTurno() {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT id_tipo_turno, codigo, descripcion FROM tipo_turno ORDER BY descripcion";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Buscar paciente con orden médica por DNI ───────────────────────
    public static function buscarPacienteConOrden($dni) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "
            SELECT 
                p.id_persona,
                p.dni,
                p.nombre,
                p.apellido,
                p.email,
                pa.id_persona_asiste,
                om.id_orden,
                cc.nombre AS tipo_cirugia,
                e.descripcion AS estado_orden
            FROM persona p
            JOIN persona_asiste pa  ON pa.id_persona          = p.id_persona
            JOIN orden_medica om    ON om.id_persona_asiste    = pa.id_persona_asiste
            JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
            JOIN estado e            ON e.id_estado            = om.id_estado
            WHERE p.dni = :dni
            ORDER BY om.id_orden DESC
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    // ── Validar datos del turno ────────────────────────────────────────
    public static function validarDatosTurno($input) {
        $errores = [];

        if (empty($input['id_persona_asiste']) || !is_numeric($input['id_persona_asiste'])) {
            $errores[] = 'Paciente inválido.';
        }
        if (empty($input['id_orden']) || !is_numeric($input['id_orden'])) {
            $errores[] = 'Orden médica inválida.';
        }
        if (empty($input['id_tipo_turno']) || !is_numeric($input['id_tipo_turno'])) {
            $errores[] = 'Tipo de turno inválido.';
        }
        if (empty($input['fecha_hora'])) {
            $errores[] = 'La fecha y hora son obligatorias.';
        } else {
            $dt = DateTime::createFromFormat('Y-m-d H:i', $input['fecha_hora']);
            if (!$dt || $dt < new DateTime()) {
                $errores[] = 'La fecha y hora deben ser futuras.';
            }
        }

        return $errores;
    }

    // ── Programar turno ────────────────────────────────────────────────
    public static function programarTurno($datos) {
        $pdo = conexionCircuitoQuirurgico();

        // Verificar cupo disponible
        $fecha = date('Y-m-d', strtotime($datos['fecha_hora']));
        $cupos = self::obtenerCuposPorFecha($fecha);
        if ($cupos['libres'] <= 0) {
            return ['ok' => false, 'error' => 'No hay cupos disponibles para esa fecha.'];
        }

        // Estado PROG para turno
        $sqlEstado = "SELECT id_estado FROM estado WHERE codigo = 'PROG' AND entidad = 'TURNO' LIMIT 1";
        $id_estado = $pdo->query($sqlEstado)->fetchColumn();
        if (!$id_estado) {
            return ['ok' => false, 'error' => 'No se encontró el estado PROG para turno.'];
        }

        // Insertar turno
        $sqlTurno = "
            INSERT INTO turno (id_persona_asiste, id_tipo_turno, id_orden, id_estado, fecha_hora)
            VALUES (:id_persona_asiste, :id_tipo_turno, :id_orden, :id_estado, :fecha_hora)
            RETURNING id_turno
        ";
        $stmt = $pdo->prepare($sqlTurno);
        $stmt->bindParam(':id_persona_asiste', $datos['id_persona_asiste'], PDO::PARAM_INT);
        $stmt->bindParam(':id_tipo_turno',     $datos['id_tipo_turno'],     PDO::PARAM_INT);
        $stmt->bindParam(':id_orden',          $datos['id_orden'],          PDO::PARAM_INT);
        $stmt->bindParam(':id_estado',         $id_estado,                  PDO::PARAM_INT);
        $stmt->bindParam(':fecha_hora',        $datos['fecha_hora']);
        $stmt->execute();
        $id_turno = $stmt->fetch(PDO::FETCH_ASSOC)['id_turno'];

        // Registrar evento clínico
        $sqlEvento = "
            INSERT INTO evento_clinico (id_tipo_evento, id_orden, id_turno, fecha_evento, descripcion)
            VALUES (
                (SELECT id_tipo_evento FROM tipo_evento WHERE LOWER(evento) = 'turno programado' LIMIT 1),
                :id_orden, :id_turno, NOW(), 'Turno programado desde Agenda'
            )
        ";
        $stmtEv = $pdo->prepare($sqlEvento);
        $stmtEv->bindParam(':id_orden', $datos['id_orden'], PDO::PARAM_INT);
        $stmtEv->bindParam(':id_turno', $id_turno,          PDO::PARAM_INT);
        $stmtEv->execute();

        return ['ok' => true, 'id_turno' => $id_turno];
    }
}