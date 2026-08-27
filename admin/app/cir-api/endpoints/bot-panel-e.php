<?php
// Panel de administración del bot: proxy hacia el backend FastAPI (telegram-api)
// Reenvía estado del bot, listado de conversaciones y mensajes a /api/panel/*
// usando BOT_API_URL y BOT_API_SECRET (header X-API-Key) desde el .env.

header('Content-Type: application/json; charset=utf-8');
session_start();

if (empty($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

// Cargar variables de entorno desde el .env raíz si no están definidas
$envPath = __DIR__ . '/../../../../.env';
if (empty(getenv('BOT_API_URL')) && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

function botPanel_responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function botPanel_llamar(string $ruta): void
{
    $baseUrl = rtrim((string) getenv('BOT_API_URL'), '/');
    $secret  = trim((string) getenv('BOT_API_SECRET'));

    if ($baseUrl === '' || $secret === '') {
        botPanel_responder(500, [
            'status' => 'error',
            'message' => 'Faltan BOT_API_URL / BOT_API_SECRET en el .env.',
        ]);
    }

    $url = $baseUrl . $ruta;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . $secret,
            'Accept: application/json',
        ],
    ]);

    $respuesta  = curl_exec($ch);
    $codigoHttp = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error      = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false) {
        botPanel_responder(502, [
            'status' => 'error',
            'message' => 'No se pudo contactar al backend del bot: ' . $error,
        ]);
    }

    $json = json_decode($respuesta, true);
    if (!is_array($json)) {
        botPanel_responder(502, [
            'status' => 'error',
            'message' => 'Respuesta inválida del backend del bot.',
        ]);
    }

    botPanel_responder($codigoHttp, $json);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'estado':
        botPanel_llamar('/api/panel/estado');
        break;

    case 'conversaciones':
        $limite = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT);
        $limite = $limite && $limite > 0 ? min($limite, 500) : 100;
        botPanel_llamar('/api/panel/conversaciones?limite=' . $limite);
        break;

    case 'mensajes':
        $chatId = filter_input(INPUT_GET, 'chat_id', FILTER_VALIDATE_INT);
        if (!$chatId) {
            botPanel_responder(400, ['status' => 'error', 'message' => 'chat_id inválido.']);
        }
        botPanel_llamar('/api/panel/conversaciones/' . $chatId . '/mensajes');
        break;

    default:
        botPanel_responder(400, ['status' => 'error', 'message' => 'Acción no válida.']);
}