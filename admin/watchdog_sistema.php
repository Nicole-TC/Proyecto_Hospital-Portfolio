<?php
// Watchdog del estado del bot (FastAPI telegram-api).
//
// CLI: una pasada por ejecución, pensado para correr desde cron en el HOST
// donde vive el panel (NO en la VPS, para que sobreviva a los apagones del bot):
//   * * * * * docker exec cliente_proyecto php /var/www/html/admin/watchdog_sistema.php >> /tmp/watchdog.log 2>&1
//
// Consulta /api/panel/estado del bot por URL (BOT_API_URL o MONITOR_URL) con
// header X-API-Key y registra en errores_log (BD "bots") SOLO las transiciones
// funcional <-> caído, como eventos estado_caida / estado_recuperacion con
// contexto origen=watchdog. Idempotente: se resincroniza del último evento en BD.
//
// Variables usadas (env del container / .env del panel):
//   MONITOR_URL      opcional; si está vacía se usa BOT_API_URL
//   BOT_API_SECRET   token para el header X-API-Key
//   BOT_DB_HOST/PORT/NAME/USER/PASSWORD   conexión a la BD "bots" (errores_log)
declare(strict_types=1);

// Solo CLI: evita ejecución accidental por HTTP.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// Cargar variables de entorno desde el .env raíz si no están definidas
$envPath = __DIR__ . '/../.env';
if (empty(getenv('BOT_API_URL')) && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

function watchdog_env(string $clave): string
{
    return trim((string) getenv($clave));
}

function watchdog_log(string $mensaje): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
}

/**
 * Consulta el estado del bot y devuelve [funcional, detalle].
 */
function watchdog_probar(string $baseUrl, string $secret): array
{
    $url = $baseUrl . '/api/panel/estado';

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

    if ($respuesta === false || $codigoHttp < 200 || $codigoHttp >= 300) {
        $detalle = $error !== '' ? $error : 'HTTP ' . $codigoHttp;
        return ['funcional' => false, 'detalle' => 'inalcanzable: ' . $detalle];
    }

    $json = json_decode($respuesta, true);
    if (!is_array($json) || ($json['status'] ?? '') !== 'success') {
        return ['funcional' => false, 'detalle' => 'respuesta inválida del estado'];
    }

    $polling = (bool) ($json['polling'] ?? false);
    $db      = (bool) ($json['db'] ?? false);

    if (!$polling && !$db) {
        return ['funcional' => false, 'detalle' => 'polling y db caídos'];
    }
    if (!$polling) {
        return ['funcional' => false, 'detalle' => 'polling inactivo'];
    }
    if (!$db) {
        return ['funcional' => false, 'detalle' => 'db caída'];
    }
    return ['funcional' => true, 'detalle' => 'probe OK'];
}

/**
 * Conexión PDO a la BD "bots" (errores_log).
 */
function watchdog_conectar(): PDO
{
    $host = watchdog_env('BOT_DB_HOST');
    $port = watchdog_env('BOT_DB_PORT') !== '' ? watchdog_env('BOT_DB_PORT') : '9432';
    $name = watchdog_env('BOT_DB_NAME');
    $user = watchdog_env('BOT_DB_USER');
    $pass = watchdog_env('BOT_DB_PASSWORD');

    if ($host === '' || $name === '' || $user === '') {
        throw new RuntimeException('Faltan BOT_DB_HOST/PORT/NAME/USER/PASSWORD en .env.');
    }

    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$name}",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    $pdo->exec("SET TIME ZONE 'America/Argentina/Buenos_Aires'");
    return $pdo;
}

/**
 * Último evento de estado registrado por el watchdog. null si no hay historial.
 */
function watchdog_ultimo_evento(PDO $pdo): ?string
{
    $stmt = $pdo->query(
        "SELECT tipo
           FROM errores_log
          WHERE tipo IN ('estado_caida','estado_recuperacion','estado_inicio','estado_detencion')
            AND contexto LIKE '%watchdog%'
          ORDER BY id DESC
          LIMIT 1"
    );
    $fila = $stmt->fetch();
    return is_array($fila) && isset($fila['tipo']) ? $fila['tipo'] : null;
}

function watchdog_registrar(PDO $pdo, string $tipo, string $descripcion, array $probe, string $baseUrl): void
{
    $contexto = json_encode([
        'origen'    => 'watchdog',
        'probe_url' => $baseUrl,
        'detalle'   => $probe['detalle'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $pdo->prepare(
        "INSERT INTO errores_log (chat_id, descripcion, contexto, tipo) VALUES (NULL, :desc, :ctx, :tipo)"
    );
    $stmt->execute([
        ':desc' => $descripcion,
        ':ctx'  => $contexto,
        ':tipo' => $tipo,
    ]);

    watchdog_log("registrado {$tipo}: {$descripcion}");
}

function watchdog_ejecutar(PDO $pdo, array $probe, string $baseUrl): void
{
    $funcional     = $probe['funcional'];
    $ultimo        = watchdog_ultimo_evento($pdo);
    $ultimoFueCaida = ($ultimo === 'estado_caida');

    if ($funcional && $ultimoFueCaida) {
        watchdog_registrar($pdo, 'estado_recuperacion', 'Watchdog: bot funcional', $probe, $baseUrl);
    } elseif (!$funcional && !$ultimoFueCaida) {
        watchdog_registrar($pdo, 'estado_caida', 'Watchdog: ' . $probe['detalle'], $probe, $baseUrl);
    } else {
        watchdog_log('sin cambios (estado: ' . ($funcional ? 'funcional' : 'caído') . ')');
    }
}

// Base URL de monitoreo: MONITOR_URL si está seteada, sino BOT_API_URL.
$monitorUrl = watchdog_env('MONITOR_URL');
$baseUrl    = $monitorUrl !== '' ? rtrim($monitorUrl, '/') : rtrim(watchdog_env('BOT_API_URL'), '/');
$secret     = watchdog_env('BOT_API_SECRET');

if ($baseUrl === '' || $secret === '') {
    watchdog_log('ERROR: faltan BOT_API_URL (o MONITOR_URL) / BOT_API_SECRET en .env.');
    exit(1);
}

$probe = watchdog_probar($baseUrl, $secret);
watchdog_log('probe ' . ($probe['funcional'] ? 'funcional' : 'caído') . ' → ' . $probe['detalle']);

try {
    $pdo = watchdog_conectar();
} catch (Throwable $e) {
    watchdog_log('ERROR BD: ' . $e->getMessage());
    exit(1);
}

try {
    watchdog_ejecutar($pdo, $probe, $baseUrl);
} catch (Throwable $e) {
    watchdog_log('ERROR: ' . $e->getMessage());
    exit(1);
}