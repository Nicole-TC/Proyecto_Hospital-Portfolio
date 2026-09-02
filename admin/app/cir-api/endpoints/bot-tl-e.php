<?php
header('Content-Type: application/json');

$envPath = __DIR__ . '/../../../../.env';
if (empty(getenv('CQ_API_TOKEN')) && file_exists($envPath)) {
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
require_once __DIR__ . '/../php/funciones.php';

function botTL_autenticar(): bool
{
    $token = getenv('CQ_API_TOKEN');
    if (!$token) {
        return false;
    }

    $autorizacion = '';
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $autorizacion = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    if ($autorizacion === '') {
        $autorizacion = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? ($_SERVER['Authorization'] ?? '');
    }

    if (preg_match('/^Bearer\s+(.+)$/i', $autorizacion, $m)) {
        $recibido = trim($m[1]);
    } else {
        $recibido = $_GET['token'] ?? '';
    }

    return is_string($recibido) && $recibido !== '' && hash_equals($token, $recibido);
}

function botTL_responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function botTL_medico(?array $fila): ?string
{
    if (!$fila) {
        return null;
    }
    $medico = trim(trim((string)($fila['medico_apellido'] ?? '')) . ', ' . trim((string)($fila['medico_nombre'] ?? '')));
    return $medico !== ', ' ? $medico : null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    botTL_responder(405, ['status' => 'error', 'message' => 'Método no permitido.']);
}

if (!botTL_autenticar()) {
    botTL_responder(401, ['status' => 'error', 'message' => 'No autorizado. Token inválido o ausente.']);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'verificar_dni':
        $dni = preg_replace('/\D/', '', trim($_GET['dni'] ?? ''));
        if (strlen($dni) < 5 || strlen($dni) > 8) {
            botTL_responder(400, ['status' => 'error', 'message' => 'DNI inválido. Debe tener entre 5 y 8 dígitos.']);
        }

        try {
            $pdo = conexionCircuitoQuirurgico();

            $stmtRol = $pdo->prepare("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'paciente' LIMIT 1");
            $stmtRol->execute();
            $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);
            $idRol = $idRol ? (int)$idRol : 4;

            $stmt = $pdo->prepare("
                SELECT p.id_persona, p.nombre, p.apellido, pa.id_persona_asiste
                FROM persona p
                LEFT JOIN persona_asiste pa ON pa.id_persona = p.id_persona
                WHERE p.dni = :dni AND p.id_rol = :id_rol
                LIMIT 1
            ");
            $stmt->execute([':dni' => $dni, ':id_rol' => $idRol]);
            $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

            botTL_responder(200, [
                'status'            => 'success',
                'dni'               => $dni,
                'encontrado'        => (bool)$paciente,
                'id_persona'        => $paciente ? (int)$paciente['id_persona'] : null,
                'nombre'            => $paciente ? $paciente['nombre'] : null,
                'apellido'          => $paciente ? $paciente['apellido'] : null,
                'id_persona_asiste' => $paciente && $paciente['id_persona_asiste'] ? (int)$paciente['id_persona_asiste'] : null,
            ]);
        } catch (Exception $e) {
            error_log("bot-tl-e.php: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    // ============================================================
    //  VERIFICAR TELÉFONO DEL PACIENTE
    // ============================================================

    case 'verificar_telefono':
        $telefono = trim($_GET['telefono'] ?? '');
        if ($telefono === '' || !preg_match('/^[\d\s\-\(\)\+]{7,20}$/', $telefono)) {
            botTL_responder(400, ['status' => 'error', 'message' => 'Teléfono inválido. Debe tener entre 7 y 20 caracteres.']);
        }

        try {
            $pdo = conexionCircuitoQuirurgico();

            $stmtRol = $pdo->prepare("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'paciente' LIMIT 1");
            $stmtRol->execute();
            $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);
            $idRol = $idRol ? (int)$idRol : 4;

            $stmt = $pdo->prepare("
                SELECT p.id_persona, p.dni, p.nombre, p.apellido, pa.id_persona_asiste
                FROM persona p
                LEFT JOIN persona_asiste pa ON pa.id_persona = p.id_persona
                WHERE p.telefono = :telefono AND p.id_rol = :id_rol
                ORDER BY p.apellido, p.nombre
            ");
            $stmt->execute([':telefono' => $telefono, ':id_rol' => $idRol]);
            $coincidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $cantidad = count($coincidencias);
            $unico = $cantidad === 1 ? $coincidencias[0] : null;

            $pacientes = array_map(static function (array $fila): array {
                return [
                    'id_persona'        => (int)$fila['id_persona'],
                    'dni'               => $fila['dni'],
                    'nombre'            => $fila['nombre'],
                    'apellido'          => $fila['apellido'],
                    'id_persona_asiste' => $fila['id_persona_asiste'] ? (int)$fila['id_persona_asiste'] : null,
                ];
            }, $coincidencias);

            botTL_responder(200, [
                'status'     => 'success',
                'telefono'   => $telefono,
                'encontrado' => $cantidad > 0,
                'cantidad'   => $cantidad,
                'pacientes'  => $pacientes,

                'id_persona'        => $unico ? (int)$unico['id_persona'] : null,
                'dni'               => $unico ? $unico['dni'] : null,
                'nombre'            => $unico ? $unico['nombre'] : null,
                'apellido'          => $unico ? $unico['apellido'] : null,
                'id_persona_asiste' => $unico && $unico['id_persona_asiste'] ? (int)$unico['id_persona_asiste'] : null,
            ]);
        } catch (Exception $e) {
            error_log("bot-tl-e.php verificar_telefono: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    // ============================================================
    //  VER TURNOS DEL PACIENTE
    // ============================================================

    case 'ver_turnos':
        $dni = preg_replace('/\D/', '', trim($_GET['dni'] ?? ''));
        if (strlen($dni) < 5 || strlen($dni) > 8) {
            botTL_responder(400, ['status' => 'error', 'message' => 'DNI inválido. Debe tener entre 5 y 8 dígitos.']);
        }

        try {
            $pdo = conexionCircuitoQuirurgico();

            $stmtRol = $pdo->prepare("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'paciente' LIMIT 1");
            $stmtRol->execute();
            $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);
            $idRol = $idRol ? (int)$idRol : 4;

            $stmtPersona = $pdo->prepare("SELECT id_persona FROM persona WHERE dni = :dni AND id_rol = :id_rol LIMIT 1");
            $stmtPersona->execute([':dni' => $dni, ':id_rol' => $idRol]);
            $idPersona = $stmtPersona->fetch(PDO::FETCH_COLUMN);

            if (!$idPersona) {
                botTL_responder(200, [
                    'status'  => 'success',
                    'dni'     => $dni,
                    'turnos'  => [],
                ]);
            }

            $stmt = $pdo->prepare("
                SELECT
                    t.id_turno,
                    TO_CHAR(t.fecha_hora, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                    tt.descripcion  AS tipo_turno,
                    cc.nombre       AS cirugia,
                    pr.apellido     AS medico_apellido,
                    pr.nombre       AS medico_nombre,
                    e.codigo        AS estado_codigo,
                    e.descripcion   AS estado
                FROM turno t
                JOIN persona_asiste pa   ON pa.id_persona_asiste = t.id_persona_asiste
                JOIN tipo_turno tt       ON tt.id_tipo_turno     = t.id_tipo_turno
                JOIN estado e            ON e.id_estado          = t.id_estado
                LEFT JOIN persona p      ON p.id_persona         = pa.id_persona
                LEFT JOIN orden_medica om ON om.id_orden         = t.id_orden
                LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
                LEFT JOIN profesional prf ON prf.id_profesional   = om.id_profesional
                LEFT JOIN persona pr      ON pr.id_persona       = prf.id_persona
                WHERE p.id_persona = :id_persona
                  AND e.codigo IN ('PROG', 'CONF', 'CANC')
                  AND e.entidad = 'TURNO'
                  AND t.fecha_hora >= NOW()
                ORDER BY t.fecha_hora ASC
            ");
            $stmt->execute([':id_persona' => $idPersona]);
            $turnos = array_map(static function (array $fila): array {
                $fila['confirmado'] = ($fila['estado_codigo'] === 'CONF');
                $fila['cancelado']  = ($fila['estado_codigo'] === 'CANC');
                return $fila;
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));

            botTL_responder(200, [
                'status' => 'success',
                'dni'    => $dni,
                'turnos' => $turnos,
            ]);
        } catch (Exception $e) {
            error_log("bot-tl-e.php ver_turnos: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    // ============================================================
    //  CANCELAR TURNO DEL PACIENTE
    // ============================================================

    case 'cancelar_turno':
        $dni     = preg_replace('/\D/', '', trim($_GET['dni'] ?? ''));
        $idTurno = filter_input(INPUT_GET, 'id_turno', FILTER_VALIDATE_INT);
        $motivo  = trim($_GET['motivo'] ?? '');

        if (strlen($dni) < 5 || strlen($dni) > 8) {
            botTL_responder(400, ['status' => 'error', 'message' => 'DNI inválido. Debe tener entre 5 y 8 dígitos.']);
        }
        if (!$idTurno) {
            botTL_responder(400, ['status' => 'error', 'message' => 'ID de turno inválido.']);
        }

        try {
            $pdo = conexionCircuitoQuirurgico();

            $stmtRol = $pdo->prepare("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'paciente' LIMIT 1");
            $stmtRol->execute();
            $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);
            $idRol = $idRol ? (int)$idRol : 4;

            $stmtPersona = $pdo->prepare("SELECT id_persona FROM persona WHERE dni = :dni AND id_rol = :id_rol LIMIT 1");
            $stmtPersona->execute([':dni' => $dni, ':id_rol' => $idRol]);
            $idPersona = $stmtPersona->fetch(PDO::FETCH_COLUMN);

            if (!$idPersona) {
                botTL_responder(404, ['status' => 'error', 'message' => 'Paciente no encontrado.']);
            }

            $stmtPA = $pdo->prepare("SELECT id_persona_asiste FROM persona_asiste WHERE id_persona = :id_persona LIMIT 1");
            $stmtPA->execute([':id_persona' => $idPersona]);
            $idPersonaAsiste = $stmtPA->fetch(PDO::FETCH_COLUMN);

            if (!$idPersonaAsiste) {
                botTL_responder(404, ['status' => 'error', 'message' => 'El paciente no tiene admisión registrada.']);
            }

            $stmtTurno = $pdo->prepare("
                SELECT
                    t.id_turno,
                    t.id_orden,
                    e.codigo AS estado_codigo,
                    TO_CHAR(t.fecha_hora, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                    tt.descripcion AS tipo_turno,
                    cc.nombre      AS cirugia,
                    pr.apellido    AS medico_apellido,
                    pr.nombre      AS medico_nombre
                FROM turno t
                JOIN estado e ON e.id_estado = t.id_estado
                JOIN tipo_turno tt ON tt.id_tipo_turno = t.id_tipo_turno
                LEFT JOIN orden_medica om ON om.id_orden = t.id_orden
                LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
                LEFT JOIN profesional prf ON prf.id_profesional = om.id_profesional
                LEFT JOIN persona pr ON pr.id_persona = prf.id_persona
                WHERE t.id_turno = :id_turno
                  AND t.id_persona_asiste = :id_persona_asiste
                LIMIT 1
            ");
            $stmtTurno->execute([':id_turno' => $idTurno, ':id_persona_asiste' => $idPersonaAsiste]);
            $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);

            if (!$turno) {
                botTL_responder(404, ['status' => 'error', 'message' => 'Turno no encontrado o no pertenece a este paciente.']);
            }

            if (!in_array($turno['estado_codigo'], ['PROG', 'CONF'])) {
                botTL_responder(400, ['status' => 'error', 'message' => 'El turno no se puede cancelar porque su estado actual es: ' . $turno['estado_codigo'] . '.']);
            }

            $idEstadoCanc = id_estado('TURNO', 'CANC');
            if (!$idEstadoCanc) {
                botTL_responder(500, ['status' => 'error', 'message' => 'Error interno: estado CANC no encontrado.']);
            }

            $pdo->beginTransaction();

            $motivoDb = $motivo !== '' ? $motivo : null;
            $stmtUpd = $pdo->prepare("
                UPDATE turno
                SET id_estado = :id_estado,
                    cancelado = true,
                    fecha_cancelacion = NOW(),
                    motivo_cancelacion = :motivo,
                    ausente = false
                WHERE id_turno = :id_turno
            ");
            $stmtUpd->execute([
                ':id_estado' => $idEstadoCanc,
                ':motivo'    => $motivoDb,
                ':id_turno'  => $idTurno,
            ]);

            $descEvento = 'Turno cancelado desde bot';
            if ($motivoDb) {
                $descEvento .= ': ' . $motivoDb;
            }

            $stmtEv = $pdo->prepare("
                INSERT INTO evento_clinico (id_tipo_evento, id_turno, id_orden, fecha_evento, descripcion)
                VALUES (
                    (SELECT id_tipo_evento FROM tipo_evento WHERE LOWER(evento) = 'cambio de estado' LIMIT 1),
                    :id_turno, :id_orden, NOW(), :descripcion
                )
            ");
            $stmtEv->execute([
                ':id_turno'    => $idTurno,
                ':id_orden'    => $turno['id_orden'],
                ':descripcion' => $descEvento,
            ]);

            $pdo->commit();

            botTL_responder(200, [
                'status'     => 'success',
                'message'    => 'Turno cancelado correctamente.',
                'id_turno'   => $idTurno,
                'estado'     => 'CANC',
                'fecha_hora' => $turno['fecha_hora'],
                'tipo_turno' => $turno['tipo_turno'],
                'cirugia'    => $turno['cirugia'],
                'medico'     => botTL_medico($turno),
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("bot-tl-e.php cancelar_turno: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    // ============================================================
    //  CONFIRMAR TURNO DEL PACIENTE
    // ============================================================

    case 'confirmar_turno':
        $dni     = preg_replace('/\D/', '', trim($_GET['dni'] ?? ''));
        $idTurno = filter_input(INPUT_GET, 'id_turno', FILTER_VALIDATE_INT);

        if (strlen($dni) < 5 || strlen($dni) > 8) {
            botTL_responder(400, ['status' => 'error', 'message' => 'DNI inválido. Debe tener entre 5 y 8 dígitos.']);
        }
        if (!$idTurno) {
            botTL_responder(400, ['status' => 'error', 'message' => 'ID de turno inválido.']);
        }

        try {
            $pdo = conexionCircuitoQuirurgico();

            $stmtRol = $pdo->prepare("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'paciente' LIMIT 1");
            $stmtRol->execute();
            $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);
            $idRol = $idRol ? (int)$idRol : 4;

            $stmtPersona = $pdo->prepare("SELECT id_persona FROM persona WHERE dni = :dni AND id_rol = :id_rol LIMIT 1");
            $stmtPersona->execute([':dni' => $dni, ':id_rol' => $idRol]);
            $idPersona = $stmtPersona->fetch(PDO::FETCH_COLUMN);

            if (!$idPersona) {
                botTL_responder(404, ['status' => 'error', 'message' => 'Paciente no encontrado.']);
            }

            $stmtPA = $pdo->prepare("SELECT id_persona_asiste FROM persona_asiste WHERE id_persona = :id_persona LIMIT 1");
            $stmtPA->execute([':id_persona' => $idPersona]);
            $idPersonaAsiste = $stmtPA->fetch(PDO::FETCH_COLUMN);

            if (!$idPersonaAsiste) {
                botTL_responder(404, ['status' => 'error', 'message' => 'El paciente no tiene admisión registrada.']);
            }

            $stmtTurno = $pdo->prepare("
                SELECT
                    t.id_turno,
                    t.id_orden,
                    e.codigo AS estado_codigo,
                    TO_CHAR(t.fecha_hora, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                    tt.descripcion AS tipo_turno,
                    cc.nombre      AS cirugia,
                    pr.apellido    AS medico_apellido,
                    pr.nombre      AS medico_nombre
                FROM turno t
                JOIN estado e ON e.id_estado = t.id_estado
                JOIN tipo_turno tt ON tt.id_tipo_turno = t.id_tipo_turno
                LEFT JOIN orden_medica om ON om.id_orden = t.id_orden
                LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
                LEFT JOIN profesional prf ON prf.id_profesional = om.id_profesional
                LEFT JOIN persona pr ON pr.id_persona = prf.id_persona
                WHERE t.id_turno = :id_turno
                  AND t.id_persona_asiste = :id_persona_asiste
                LIMIT 1
            ");
            $stmtTurno->execute([':id_turno' => $idTurno, ':id_persona_asiste' => $idPersonaAsiste]);
            $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);

            if (!$turno) {
                botTL_responder(404, ['status' => 'error', 'message' => 'Turno no encontrado o no pertenece a este paciente.']);
            }

            if (!in_array($turno['estado_codigo'], ['PROG', 'CONF'])) {
                botTL_responder(400, ['status' => 'error', 'message' => 'El turno no se puede confirmar porque su estado actual es: ' . $turno['estado_codigo'] . '.']);
            }

            if ($turno['estado_codigo'] === 'CONF') {
                botTL_responder(200, [
                    'status'     => 'success',
                    'ya_confirmado' => true,
                    'message'    => 'Ya habías confirmado este turno anteriormente.',
                    'id_turno'   => (int)$turno['id_turno'],
                    'estado'     => 'CONF',
                    'fecha_hora' => $turno['fecha_hora'],
                    'tipo_turno' => $turno['tipo_turno'],
                    'cirugia'    => $turno['cirugia'],
                    'medico'     => botTL_medico($turno),
                ]);
            }

            $idEstadoConf = id_estado('TURNO', 'CONF');
            if (!$idEstadoConf) {
                botTL_responder(500, ['status' => 'error', 'message' => 'Error interno: estado CONF no encontrado.']);
            }

            $pdo->beginTransaction();

            $stmtUpd = $pdo->prepare("
                UPDATE turno
                SET id_estado = :id_estado,
                    ausente = false
                WHERE id_turno = :id_turno
            ");
            $stmtUpd->execute([
                ':id_estado' => $idEstadoConf,
                ':id_turno'  => $idTurno,
            ]);

            $stmtEv = $pdo->prepare("
                INSERT INTO evento_clinico (id_tipo_evento, id_turno, id_orden, fecha_evento, descripcion)
                VALUES (
                    (SELECT id_tipo_evento FROM tipo_evento WHERE LOWER(evento) = 'cambio de estado' LIMIT 1),
                    :id_turno, :id_orden, NOW(), 'Paciente confirmó asistencia al turno desde bot'
                )
            ");
            $stmtEv->execute([
                ':id_turno' => $idTurno,
                ':id_orden' => $turno['id_orden'],
            ]);

            $pdo->commit();

            botTL_responder(200, [
                'status'     => 'success',
                'message'    => 'Turno confirmado correctamente.',
                'id_turno'   => $idTurno,
                'estado'     => 'CONF',
                'fecha_hora' => $turno['fecha_hora'],
                'tipo_turno' => $turno['tipo_turno'],
                'cirugia'    => $turno['cirugia'],
                'medico'     => botTL_medico($turno),
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("bot-tl-e.php confirmar_turno: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    // ============================================================
    //  RECORDATORIOS PENDIENTES (CRON BOT TL - AVISO 48 HS ANTES)
    //  Devuelve los turnos PROG dentro de las próximas 48 hs sin
    //  recordar. NO marca los turnos al entregarlos (evita la pérdida
    //  silenciosa): el bot marca con un update posterior a
    //  recordatorio_enviado=true recién tras un envío exitoso.
    //  Parametro opcional "marcar=true" reactiva el marcado atomico.
    //  Cron sugerido en el proyecto del bot (cada 30 minutos):
    //  */30 * * * * curl -s -H "Authorization: Bearer $CQ_API_TOKEN" \
    //    "https://<host>/admin/app/cir-api/endpoints/bot-tl-e.php?action=recordatorios_pendientes"
    // ============================================================

    case 'recordatorios_pendientes':
        $pdo = null;
        $marcar = filter_var($_GET['marcar'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        try {
            $pdo = conexionCircuitoQuirurgico();

            $stmt = $pdo->prepare("
                SELECT
                    t.id_turno,
                    TO_CHAR(t.fecha_hora, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                    tt.descripcion  AS tipo_turno,
                    cc.nombre       AS cirugia,
                    pr.apellido     AS medico_apellido,
                    pr.nombre       AS medico_nombre,
                    p.dni           AS paciente_dni,
                    p.nombre        AS paciente_nombre,
                    p.apellido      AS paciente_apellido,
                    p.telefono      AS paciente_telefono
                FROM turno t
                JOIN estado e            ON e.id_estado          = t.id_estado
                JOIN persona_asiste pa   ON pa.id_persona_asiste = t.id_persona_asiste
                JOIN persona p           ON p.id_persona         = pa.id_persona
                JOIN tipo_turno tt       ON tt.id_tipo_turno     = t.id_tipo_turno
                LEFT JOIN orden_medica om     ON om.id_orden            = t.id_orden
                LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
                LEFT JOIN profesional prf     ON prf.id_profesional     = om.id_profesional
                LEFT JOIN persona pr          ON pr.id_persona          = prf.id_persona
                WHERE e.entidad = 'TURNO'
                  AND e.codigo = 'PROG'
                  AND t.recordatorio_enviado = false
                  AND t.fecha_hora > NOW()
                  AND t.fecha_hora <= NOW() + INTERVAL '48 hours'
                ORDER BY t.fecha_hora ASC
            ");
            $stmt->execute();
            $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($pendientes)) {
                botTL_responder(200, [
                    'status'        => 'success',
                    'cantidad'      => 0,
                    'recordatorios' => [],
                ]);
            }

            $recordatorios = [];
            foreach ($pendientes as $fila) {
                $medico = trim(trim((string)$fila['medico_apellido']) . ', ' . trim((string)$fila['medico_nombre']));

                $recordatorios[] = [
                    'id_turno'   => (int)$fila['id_turno'],
                    'dni'        => $fila['paciente_dni'],
                    'nombre'     => $fila['paciente_nombre'],
                    'apellido'   => $fila['paciente_apellido'],
                    'telefono'   => $fila['paciente_telefono'],
                    'fecha_hora' => $fila['fecha_hora'],
                    'tipo_turno' => $fila['tipo_turno'],
                    'cirugia'    => $fila['cirugia'],
                    'medico'     => $medico !== ', ' ? $medico : null,
                ];
            }

            botTL_responder(200, [
                'status'        => 'success',
                'cantidad'      => count($recordatorios),
                'recordatorios' => $recordatorios,
                'marcado'       => $marcar,
            ]);
        } catch (Exception $e) {
            if ($pdo !== null && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("bot-tl-e.php recordatorios_pendientes: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    // ============================================================
    //  MARCAR / DESMARCAR RECORDATORIO DE UN TURNO (SOPORTE)
    // ============================================================

    case 'marcar_recordatorio':
        $idTurno      = filter_input(INPUT_GET, 'id_turno', FILTER_VALIDATE_INT);
        $enviadoParam = $_GET['enviado'] ?? '1';

        if (!$idTurno) {
            botTL_responder(400, ['status' => 'error', 'message' => 'ID de turno inválido.']);
        }
        if (!in_array($enviadoParam, ['0', '1'], true)) {
            botTL_responder(400, ['status' => 'error', 'message' => 'Parámetro enviado inválido. Debe ser 0 o 1.']);
        }
        $enviado = $enviadoParam === '1';

        try {
            $pdo = conexionCircuitoQuirurgico();

            $stmtUpd = $pdo->prepare("
                UPDATE turno
                SET recordatorio_enviado = :enviado,
                    fecha_recordatorio_enviado = :fecha
                WHERE id_turno = :id_turno
            ");
            $stmtUpd->execute([
                ':enviado' => $enviado,
                ':fecha'   => $enviado ? date('Y-m-d H:i:s') : null,
                ':id_turno'=> $idTurno,
            ]);

            if ($stmtUpd->rowCount() === 0) {
                botTL_responder(404, ['status' => 'error', 'message' => 'Turno no encontrado.']);
            }

            botTL_responder(200, [
                'status'   => 'success',
                'message'  => $enviado ? 'Recordatorio marcado como enviado.' : 'Recordatorio desmarcado.',
                'id_turno' => $idTurno,
            ]);
        } catch (Exception $e) {
            error_log("bot-tl-e.php marcar_recordatorio: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    default:
        botTL_responder(400, ['status' => 'error', 'message' => 'Acción no válida.']);
}