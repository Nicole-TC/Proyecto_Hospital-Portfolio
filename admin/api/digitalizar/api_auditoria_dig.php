<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado. Por favor inicie sesión."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../model/conexion_dig.php';

if (!isset($bd_dig)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a la base de datos."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Use GET."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$MAX_STR_TIPO   = 20;
$MAX_STR_TABLA  = 50;
$MAX_STR_FECHA  = 10;
$MAX_LIMIT      = 200;

function badRequest($msg) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => $msg
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function validarFecha($s) {
    $d = DateTime::createFromFormat('d-m-Y', $s);
    return $d && $d->format('d-m-Y') === $s;
}

try {
    $tipo           = trim($_GET['tipo'] ?? '');
    $tabla_afectada = trim($_GET['tabla_afectada'] ?? '');
    $fecha_desde    = trim($_GET['fecha_desde'] ?? '');
    $fecha_hasta    = trim($_GET['fecha_hasta'] ?? '');

    if ($tipo === '') {
        badRequest("El parámetro 'tipo' es obligatorio.");
    }
    if (mb_strlen($tipo) > $MAX_STR_TIPO) {
        badRequest("El parámetro 'tipo' excede la longitud máxima permitida.");
    }

    $accion_map = [
        'subidos'      => 'INSERT',
        'eliminados'   => 'DELETE',
        'actualizados' => 'UPDATE'
    ];

    if (!isset($accion_map[$tipo])) {
        badRequest("Parámetro 'tipo' inválido. Use: subidos, eliminados, actualizados");
    }

    if ($tabla_afectada !== '') {
        if (mb_strlen($tabla_afectada) > $MAX_STR_TABLA) {
            badRequest("El parámetro 'tabla_afectada' excede la longitud máxima permitida.");
        }
        $stmt_wl = $bd_dig->prepare("SELECT DISTINCT tabla_afectada FROM auditoria_logs");
        $stmt_wl->execute();
        $tablas_permitidas = array_column($stmt_wl->fetchAll(PDO::FETCH_ASSOC), 'tabla_afectada');
        if (!in_array($tabla_afectada, $tablas_permitidas, true)) {
            badRequest("Tabla afectada no reconocida.");
        }
    }

    if ($fecha_desde !== '') {
        if (mb_strlen($fecha_desde) > $MAX_STR_FECHA) {
            badRequest("fecha_desde excede la longitud máxima permitida.");
        }
        if (!validarFecha($fecha_desde)) {
            badRequest("Formato de fecha_desde inválido. Use DD-MM-AAAA.");
        }
        $fecha_desde = DateTime::createFromFormat('d-m-Y', $fecha_desde)->format('Y-m-d');
    }
    if ($fecha_hasta !== '') {
        if (mb_strlen($fecha_hasta) > $MAX_STR_FECHA) {
            badRequest("fecha_hasta excede la longitud máxima permitida.");
        }
        if (!validarFecha($fecha_hasta)) {
            badRequest("Formato de fecha_hasta inválido. Use DD-MM-AAAA.");
        }
        $fecha_hasta = DateTime::createFromFormat('d-m-Y', $fecha_hasta)->format('Y-m-d');
    }
    if ($fecha_desde !== '' && $fecha_hasta !== '' && $fecha_desde > $fecha_hasta) {
        badRequest("fecha_desde no puede ser mayor que fecha_hasta.");
    }
    if ($fecha_hasta !== '' && $fecha_hasta > (new DateTime('today'))->format('Y-m-d')) {
        badRequest("fecha_hasta no puede ser una fecha futura.");
    }

    $accion_detalle = $accion_map[$tipo];
    $params = [':accion' => $accion_detalle];
    $condiciones = [];

    if ($tipo === 'eliminados') {
        $condiciones[] = "(a.detalle = :accion OR (a.detalle = 'UPDATE' AND al.valor_anterior::jsonb @> '{\"estado\":true}'::jsonb AND al.valor_nuevo::jsonb @> '{\"estado\":false}'::jsonb))";
    } else {
        $condiciones[] = "a.detalle = :accion";
    }

    if ($tabla_afectada !== '') {
        $condiciones[] = "al.tabla_afectada = :tabla_afectada";
        $params[':tabla_afectada'] = $tabla_afectada;
    }

    if ($fecha_desde !== '') {
        $condiciones[] = "al.fecha_accion >= :fecha_desde::date";
        $params[':fecha_desde'] = $fecha_desde;
    }

    if ($fecha_hasta !== '') {
        $condiciones[] = "al.fecha_accion < (:fecha_hasta::date + INTERVAL '1 day')";
        $params[':fecha_hasta'] = $fecha_hasta;
    }

    $where = "WHERE " . implode(" AND ", $condiciones);

    $from = "
        FROM auditoria_logs al
        LEFT JOIN acciones a ON a.id = al.id_accion
        LEFT JOIN admin ad ON ad.id = al.id_usuario
        LEFT JOIN personal p ON p.id = ad.id_personal
        LEFT JOIN documentos d ON d.id = al.id_documento
        LEFT JOIN tipos_documentos td ON td.id = d.id_tipo_documento
    ";

    $stmt_count = $bd_dig->prepare("SELECT COUNT(*) $from $where");
    $stmt_count->execute($params);
    $total_registros = (int) $stmt_count->fetchColumn();

    $select = "
        SELECT
            al.id,
            al.tabla_afectada,
            CASE
                WHEN al.tabla_afectada = 'tipos_documentos' THEN COALESCE((SELECT nombre FROM tipos_documentos WHERE id = al.id_documento), '-')
                WHEN al.tabla_afectada = 'documentos' THEN COALESCE(td.nombre, '-')
                ELSE '-'
            END AS tipo_documento,
            COALESCE(a.detalle, '-') AS accion,
            COALESCE(
                NULLIF(CONCAT(ad.apellido, ', ', ad.nombre), ', '),
                '-'
            ) AS usuario,
            al.valor_anterior,
            al.valor_nuevo,
            COALESCE(al.ip_origen, '-') AS ip_origen,
            al.fecha_accion
    ";

    $order = "ORDER BY al.fecha_accion DESC";

    $stmt_data = $bd_dig->prepare("$select $from $where $order LIMIT $MAX_LIMIT");
    $stmt_data->execute($params);
    $auditoria = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => [
            "total_registros" => $total_registros,
            "auditoria"       => $auditoria
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (PDOException $e) {
    error_log("Error DB en api_auditoria_dig: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status"     => "error",
        "message"    => "Error en la base de datos.",
        "error_code" => "DB_ERROR"
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status"     => "error",
        "message"    => "Error interno del servidor.",
        "error_code" => "SERVER_ERROR"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
