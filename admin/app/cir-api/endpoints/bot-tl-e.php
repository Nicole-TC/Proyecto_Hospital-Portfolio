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

            $stmt = $pdo->prepare("SELECT 1 FROM persona WHERE dni = :dni AND id_rol = :id_rol LIMIT 1");
            $stmt->execute([':dni' => $dni, ':id_rol' => $idRol]);
            $encontrado = (bool)$stmt->fetch(PDO::FETCH_COLUMN);

            botTL_responder(200, [
                'status'     => 'success',
                'dni'        => $dni,
                'encontrado' => $encontrado,
            ]);
        } catch (Exception $e) {
            error_log("bot-tl-e.php: " . $e->getMessage());
            botTL_responder(500, ['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
        break;

    default:
        botTL_responder(400, ['status' => 'error', 'message' => 'Acción no válida.']);
}
