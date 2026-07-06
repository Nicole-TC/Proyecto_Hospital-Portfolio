<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/agenda-b.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET: turnos de un día  ───────────────────────────────────
if ($method === 'GET' && $action === 'turnos_del_dia') {
    $fecha = $_GET['fecha'] ?? '';
    $id_tipo_turno = (int)($_GET['id_tipo_turno'] ?? 0);

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['ok' => false, 'error' => 'Fecha inválida.']); exit;
    }
    try {
        $turnos = ConsultasAgenda::obtenerTurnosPorFecha($fecha, $id_tipo_turno);

        // Los turnos cancelados liberan su lugar.
        $ocupados = 0;
        foreach ($turnos as $t) {
            if (($t['estado_codigo'] ?? '') !== 'CANC') {
                $ocupados++;
            }
        }

        $cupoConfig = ['activo' => false, 'cupos_total' => 0, 'sin_config' => true];
        if ($id_tipo_turno) {
            $config = ConsultasAgenda::obtenerConfiguracionCupo($fecha, $id_tipo_turno);
            if (!$config['sin_config']) {
                $cupoConfig['activo'] = $config['activo'];
                $cupoConfig['cupos_total'] = $config['cupos_total'];
                $cupoConfig['sin_config'] = false;
            }
        }

        echo json_encode([
            'ok'         => true, 
            'turnos'     => $turnos,
            'ocupados'   => $ocupados,
            'cupo_activo'=> $cupoConfig['activo'],
            'cupos_total'=> $cupoConfig['cupos_total'],
            'sin_config' => $cupoConfig['sin_config'] ?? true
        ]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: buscar paciente con validación de circuito ───────────
if ($method === 'GET' && $action === 'buscar_paciente') {
    $dni           = preg_replace('/\D/', '', trim($_GET['dni'] ?? ''));
    $id_tipo_turno = (int)($_GET['id_tipo_turno'] ?? 0);

    if (strlen($dni) < 7 || strlen($dni) > 8) {
        echo json_encode(['ok' => false, 'error' => 'DNI inválido.']); exit;
    }
    if (!$id_tipo_turno) {
        echo json_encode(['ok' => false, 'error' => 'Tipo de turno requerido.']); exit;
    }
    try {
        echo json_encode(ConsultasAgenda::buscarPacienteConValidacion($dni, $id_tipo_turno));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: obtener id_tipo_turno prequirúrgico ───────────────────────────
if ($method === 'GET' && $action === 'obtener_tipo_turno_preq') {
    try {
        $id = ConsultasAgenda::obtenerIdTipoTurnoPrequirurgico();
        echo json_encode($id
            ? ['ok' => true,  'id_tipo_turno' => $id]
            : ['ok' => false, 'error' => 'No se encontró el tipo de turno prequirúrgico.']
        );
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: diagnóstico (solo para desarrollo) ────────────────────────────
if ($method === 'GET' && $action === 'debug_agenda') {
    try {
        $pdo = conexionCircuitoQuirurgico();
        $tipos  = $pdo->query("SELECT id_tipo_turno, codigo, descripcion FROM tipo_turno ORDER BY id_tipo_turno")->fetchAll(PDO::FETCH_ASSOC);
        $cupos  = $pdo->query("SELECT id_cupo, id_tipo_turno, fecha_desde, fecha_hasta, cupos_total, lunes, martes, miercoles, jueves, viernes, id_estado FROM agenda_cupos ORDER BY id_cupo DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $idPreq = ConsultasAgenda::obtenerIdTipoTurnoPrequirurgico();
        echo json_encode(['ok' => true, 'tipos_turno' => $tipos, 'ultimos_cupos' => $cupos, 'id_preq_detectado' => $idPreq]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}


// ── GET: obtener configuración activa (días habilitados + cupos) ────────
if ($method === 'GET' && $action === 'obtener_config_activa') {
    $id_tipo_turno = (int)($_GET['id_tipo_turno'] ?? 0);
    $fecha_ref     = $_GET['fecha']       ?? date('Y-m-d');  // último día del período
    $fecha_inicio  = $_GET['fecha_inicio'] ?? $fecha_ref;    // primer día del período
    if (!$id_tipo_turno) {
        echo json_encode(['ok' => false, 'error' => 'id_tipo_turno requerido.']); exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ref))    $fecha_ref    = date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) $fecha_inicio = $fecha_ref;
    try {
        echo json_encode(ConsultasAgenda::obtenerConfigActiva($id_tipo_turno, $fecha_ref, $fecha_inicio));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: buscar turnos (buscador) ──────────────────────────────────────
if ($method === 'GET' && $action === 'buscar_turnos') {
    $query  = trim($_GET['q']      ?? '');
    $filtro = trim($_GET['filtro'] ?? 'apellido');
    $limite = (int)($_GET['limite'] ?? 10);
    $orden  = trim($_GET['orden']  ?? 'desc');
    $pagina = (int)($_GET['pagina'] ?? 1);
    try {
        $resultado = ConsultasAgenda::buscarTurnos($filtro, $query, $limite, $orden, $pagina);
        echo json_encode(array_merge(['ok' => true], $resultado));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── POST: programar turno ───────────────────────────────────
if ($method === 'POST' && $action === 'programar_turno') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['ok' => false, 'error' => 'JSON inválido.']); exit;
    }
    try {
        echo json_encode(ConsultasAgenda::programarTurno($input));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── Método no permitido ────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método o acción no permitidos.']);