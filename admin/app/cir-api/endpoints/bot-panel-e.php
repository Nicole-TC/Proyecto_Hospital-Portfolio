<?php
// Panel de administración del bot: proxy hacia los backends de los bots.
// Reenvía estado, listado de conversaciones y mensajes a /api/panel/* usando
// BOT_API_URL / BOT_API_SECRET (Telegram) y WSP_API_URL / WSP_API_SECRET
// (WhatsApp), con el header X-API-Key.
//
// Los dos bots escriben en la misma base `bots` y hablan el mismo contrato, así
// que acá las respuestas se unen y el front las distingue por el campo `canal`
// de cada conversación. Si un canal no está configurado o no responde, se
// devuelve lo del otro: que el bot de WhatsApp esté apagado no puede dejar sin
// Historial al de Telegram.

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

/** Canales soportados, con las variables de entorno de cada uno. */
function botPanel_canales(): array
{
    return [
        'telegram' => [
            'url'    => rtrim((string) getenv('BOT_API_URL'), '/'),
            'secret' => trim((string) getenv('BOT_API_SECRET')),
        ],
        'whatsapp' => [
            'url'    => rtrim((string) getenv('WSP_API_URL'), '/'),
            'secret' => trim((string) getenv('WSP_API_SECRET')),
        ],
    ];
}

function botPanel_responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Consulta un backend. Nunca corta la ejecución: devuelve el resultado para que
 * quien llama decida si con un solo canal alcanza.
 *
 * @return array{ok: bool, codigo: int, json: array, error: string}
 */
function botPanel_consultar(array $canal, string $ruta): array
{
    if ($canal['url'] === '' || $canal['secret'] === '') {
        return ['ok' => false, 'codigo' => 503, 'json' => [], 'error' => 'canal no configurado'];
    }

    $ch = curl_init($canal['url'] . $ruta);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . $canal['secret'],
            'Accept: application/json',
        ],
    ]);

    $respuesta  = curl_exec($ch);
    $codigoHttp = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error      = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false) {
        return ['ok' => false, 'codigo' => 502, 'json' => [], 'error' => $error];
    }

    $json = json_decode($respuesta, true);
    if (!is_array($json)) {
        return ['ok' => false, 'codigo' => 502, 'json' => [], 'error' => 'respuesta no JSON'];
    }

    return [
        'ok'     => $codigoHttp === 200 && ($json['status'] ?? '') === 'success',
        'codigo' => $codigoHttp,
        'json'   => $json,
        'error'  => (string) ($json['message'] ?? ''),
    ];
}

/** Reenvía la foto de perfil al bot de Telegram. */
function botPanel_enviar_foto(array $canal, array $archivo): void
{
    if ($canal['url'] === '' || $canal['secret'] === '') {
        botPanel_responder(503, [
            'status'  => 'error',
            'message' => 'Canal de Telegram no configurado.',
        ]);
    }
    $errorArchivo = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;
    $topeBytes = 10 * 1024 * 1024;
    if ($errorArchivo === UPLOAD_ERR_INI_SIZE || $errorArchivo === UPLOAD_ERR_FORM_SIZE || (($archivo['size'] ?? 0) > $topeBytes)) {
        botPanel_responder(400, [
            'status'  => 'error',
            'message' => 'La imagen supera los 10 MB.',
        ]);
    }
    if ($errorArchivo !== UPLOAD_ERR_OK || empty($archivo['tmp_name'])) {
        botPanel_responder(400, [
            'status'  => 'error',
            'message' => 'No se recibió la imagen.',
        ]);
    }

    $extension = strtolower(pathinfo((string) ($archivo['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'mp4'], true)) {
        botPanel_responder(400, [
            'status'  => 'error',
            'message' => 'Formato no soportado: usá JPG, PNG o MP4.',
        ]);
    }

    $mime = mime_content_type($archivo['tmp_name']) ?: 'application/octet-stream';
    $cfile = new CURLFile($archivo['tmp_name'], $mime, $archivo['name'] ?: 'foto');

    $ch = curl_init($canal['url'] . '/api/panel/configuracion/imagen');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_POSTFIELDS     => ['foto' => $cfile],
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . $canal['secret'],
            'Accept: application/json',
        ],
    ]);

    $respuesta  = curl_exec($ch);
    $codigoHttp = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error      = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false) {
        botPanel_responder(502, [
            'status'  => 'error',
            'message' => 'No se pudo contactar al backend del bot: ' . $error,
        ]);
    }

    $json = json_decode($respuesta, true);
    if (!is_array($json)) {
        botPanel_responder(502, [
            'status'  => 'error',
            'message' => 'El bot no devolvió una respuesta válida.',
        ]);
    }

    if (isset($json['detail']) && !isset($json['message'])) {
        $json['message'] = is_string($json['detail']) ? $json['detail'] : 'No se pudo cambiar la foto.';
        $json['status'] = $json['status'] ?? 'error';
    }

    botPanel_responder($codigoHttp ?: 200, $json);
}

/** Reenvía tal cual la respuesta de un solo canal (acciones que no se unen). */
function botPanel_reenviar(array $canal, string $ruta): void
{
    $r = botPanel_consultar($canal, $ruta);
    if (!$r['ok'] && $r['json'] === []) {
        botPanel_responder($r['codigo'], [
            'status'  => 'error',
            'message' => 'No se pudo contactar al backend del bot: ' . $r['error'],
        ]);
    }
    botPanel_responder($r['codigo'] ?: 200, $r['json']);
}

/**
 * `ultima_fecha` viaja como `dd/mm/aaaa hh:mm`. Se pasa a timestamp para poder
 * ordenar conversaciones de los dos canales en una sola lista.
 */
function botPanel_timestamp(?string $fecha): int
{
    if (!$fecha) return 0;
    $dt = DateTime::createFromFormat('d/m/Y H:i', $fecha);
    return $dt ? (int) $dt->getTimestamp() : 0;
}

$canales = botPanel_canales();
$action  = $_GET['action'] ?? '';

switch ($action) {
    case 'estado':
        // Las claves de primer nivel siguen siendo las de Telegram: el watchdog
        // y la vista de Configuración ya las leen así, y un WhatsApp apagado no
        // tiene que hacerles creer que se cayó el sistema. El detalle por canal
        // va aparte, en `canales`.
        $base = botPanel_consultar($canales['telegram'], '/api/panel/estado');
        $wsp  = botPanel_consultar($canales['whatsapp'], '/api/panel/estado');

        $salida = $base['json'] ?: [
            'status'   => 'success',
            'servidor' => false,
            'bot'      => false,
            'polling'  => false,
            'db'       => false,
            'en_linea' => false,
        ];
        $salida['status']  = 'success';
        $salida['canales'] = [
            'telegram' => $base['ok'] ? $base['json'] : ['en_linea' => false, 'error' => $base['error']],
            'whatsapp' => $wsp['ok']  ? $wsp['json']  : ['en_linea' => false, 'error' => $wsp['error']],
        ];
        botPanel_responder(200, $salida);
        break;

    case 'conversaciones':
        $limite = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT);
        $limite = $limite && $limite > 0 ? min($limite, 500) : 100;

        $conversaciones = [];
        $caidos         = [];

        foreach ($canales as $nombre => $canal) {
            $r = botPanel_consultar($canal, '/api/panel/conversaciones?limite=' . $limite);
            if (!$r['ok']) {
                if ($canal['url'] !== '') {
                    $caidos[$nombre] = $r['error'] ?: 'sin respuesta';
                }
                continue;
            }
            foreach (($r['json']['conversaciones'] ?? []) as $conv) {
                // Si el backend no lo dice, el canal es el que respondió.
                $conv['canal'] = $conv['canal'] ?? $nombre;
                $conversaciones[] = $conv;
            }
        }

        // Más recientes primero, mezclando los dos canales.
        usort($conversaciones, function ($a, $b) {
            return botPanel_timestamp($b['ultima_fecha'] ?? null)
                 <=> botPanel_timestamp($a['ultima_fecha'] ?? null);
        });
        $conversaciones = array_slice($conversaciones, 0, $limite);

        if (!$conversaciones && $caidos) {
            botPanel_responder(502, [
                'status'  => 'error',
                'message' => 'No se pudo contactar al backend del bot: ' . implode('; ', $caidos),
            ]);
        }

        botPanel_responder(200, [
            'status'         => 'success',
            'cantidad'       => count($conversaciones),
            'conversaciones' => $conversaciones,
            'canales_caidos' => $caidos,
        ]);
        break;

    case 'mensajes':
        $chatId = filter_input(INPUT_GET, 'chat_id', FILTER_VALIDATE_INT);
        if (!$chatId) {
            botPanel_responder(400, ['status' => 'error', 'message' => 'chat_id inválido.']);
        }

        $ruta = '/api/panel/conversaciones/' . $chatId . '/mensajes';

        // El front manda el canal de la conversación abierta. Si no viene (una
        // pestaña vieja, por ejemplo) se prueban los dos: los ids son únicos
        // entre canales porque salen de la misma tabla `chats`.
        $canalPedido = $_GET['canal'] ?? '';
        $orden = isset($canales[$canalPedido])
            ? [$canalPedido]
            : array_keys($canales);

        $ultimo = null;
        foreach ($orden as $nombre) {
            $r = botPanel_consultar($canales[$nombre], $ruta);
            $ultimo = $r;
            if ($r['ok'] && !empty($r['json']['mensajes'])) {
                botPanel_responder(200, $r['json']);
            }
        }

        // Ningún canal tenía mensajes: se devuelve la última respuesta válida
        // (lista vacía) y, si ninguna lo fue, el error.
        if ($ultimo && $ultimo['ok']) {
            botPanel_responder(200, $ultimo['json']);
        }
        botPanel_responder($ultimo['codigo'] ?? 502, [
            'status'  => 'error',
            'message' => 'No se pudo contactar al backend del bot: ' . ($ultimo['error'] ?? 'sin respuesta'),
        ]);
        break;

    case 'auditoria':
        // Solo Telegram: los eventos viven en `errores_log`, que no tiene
        // columna `canal`, así que todavía no hay forma de separar los de cada
        // bot sin tocar cómo escribe el de Telegram.
        $limite = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT);
        $limite = $limite && $limite > 0 ? min($limite, 500) : 100;
        botPanel_reenviar($canales['telegram'], '/api/panel/auditoria?limite=' . $limite);
        break;

    case 'comandos':
        botPanel_llamar('/api/panel/comandos');
        break;

    case 'config_imagen':
        botPanel_enviar_foto($canales['telegram'], $_FILES['foto'] ?? []);
        break;

    default:
        botPanel_responder(400, ['status' => 'error', 'message' => 'Acción no válida.']);
}
