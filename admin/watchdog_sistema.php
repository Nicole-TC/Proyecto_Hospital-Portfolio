<?php
// Watchdog del estado de los bots (FastAPI telegram-api y bot-wsp-backend).
//
// CLI: una pasada por ejecución, pensado para correr desde cron en el HOST
// donde vive el panel (NO en la VPS, para que sobreviva a los apagones del bot):
//   * * * * * docker exec cliente_proyecto php /var/www/html/admin/watchdog_sistema.php >> /tmp/watchdog.log 2>&1
//
// Consulta /api/panel/estado de cada bot por URL (MONITOR_URL o BOT_API_URL para
// Telegram, WSP_API_URL para WhatsApp) con header X-API-Key y registra en
// errores_log (BD "bots") SOLO las transiciones funcional <-> caído, como eventos
// estado_caida / estado_recuperacion con contexto origen=watchdog. Idempotente:
// cada canal se resincroniza de su propio último evento en BD.
//
// Los canales son independientes: uno sin configurar se saltea y el otro se
// sigue midiendo; uno caído registra su propia caída, que es justamente lo que
// este script está para ver.
//
// Sobre registrar de más: el probe va por URL de la red docker, y el MISMO
// nombre (`bot-wsp-backend`) resuelve al contenedor local si el panel corre en
// la máquina de pruebas y al de la VPS si corre en producción. Como los dos
// escriben en la misma base `bots` con `canal='whatsapp'`, las caídas de prueba
// se mezclan con las reales y ensucian el "Total de Caídas" del panel. Por eso
// cada canal tiene su propia bandera: en local se pone en 0 y el watchdog sigue
// midiendo (probe + log), pero no inserta transiciones. Lo apagado NO es el
// default, para que desplegar este archivo a la VPS no cambie el comportamiento
// que ya tenía.
//
// Variables usadas (env del container / .env del panel):
//   MONITOR_URL          opcional; si está vacía se usa BOT_API_URL (solo Telegram)
//   BOT_API_SECRET       token para el header X-API-Key de Telegram
//   WSP_API_URL          URL del backend de WhatsApp
//   WSP_API_SECRET       token para el header X-API-Key de WhatsApp
//   BOT_REGISTRA_AUDITORIA   0 = no registrar caídas de Telegram. Vacío/1 = sí (default)
//   WSP_REGISTRA_AUDITORIA   0 = no registrar caídas de WhatsApp. Vacío/1 = sí (default)
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

/**
 * Lee una bandera booleana del entorno.
 *
 * Vacía o ausente = true: es lo que hace que desplegar este archivo en la VPS
 * no cambie el comportamiento anterior sin tener que tocar su .env. Apagarla
 * es un acto explícito, en el .env del host donde NO se quiere el historial.
 *
 * Acepta las escrituras que se usan de verdad en un .env: 1/0, true/false,
 * si/no, on/off.
 */
function watchdog_bandera(string $clave): bool
{
    $valor = strtolower(watchdog_env($clave));

    if ($valor === '') {
        return true;
    }

    return !in_array($valor, ['0', 'false', 'no', 'off'], true);
}

function watchdog_log(string $mensaje): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
}

/**
 * Canales que vigila el watchdog, con la URL y el secreto de cada uno.
 *
 * Telegram conserva el fallback a MONITOR_URL para no romper la instalación
 * que ya estaba; WhatsApp usa WSP_API_URL.
 *
 * `registra` es qué hacer con las transiciones de ese canal. Va aparte del
 * probe a propósito: aunque esté apagado, el watchdog sigue midiendo y
 * logueando, así que en la máquina de pruebas se ve el estado real del bot
 * local sin ensuciar el historial que mira el hospital.
 */
function watchdog_canales(): array
{
    $telegramUrl = watchdog_env('MONITOR_URL');
    if ($telegramUrl === '') {
        $telegramUrl = watchdog_env('BOT_API_URL');
    }

    return [
        'telegram' => [
            'url'      => rtrim($telegramUrl, '/'),
            'secret'   => watchdog_env('BOT_API_SECRET'),
            'registra' => watchdog_bandera('BOT_REGISTRA_AUDITORIA'),
        ],
        'whatsapp' => [
            'url'      => rtrim(watchdog_env('WSP_API_URL'), '/'),
            'secret'   => watchdog_env('WSP_API_SECRET'),
            'registra' => watchdog_bandera('WSP_REGISTRA_AUDITORIA'),
        ],
    ];
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
 * Último evento de estado registrado por el watchdog PARA UN CANAL.
 * null si no hay historial de ese canal.
 *
 * El filtro por canal es lo que hace que los dos sean independientes: sin él,
 * el último evento de Telegram enmascara al de WhatsApp y un canal nunca ve su
 * propia transición (se queda creyendo que ya estaba caído).
 */
function watchdog_ultimo_evento(PDO $pdo, string $canal): ?string
{
    $stmt = $pdo->prepare(
        "SELECT tipo
           FROM errores_log
          WHERE tipo IN ('estado_caida','estado_recuperacion','estado_inicio','estado_detencion')
            AND contexto LIKE '%watchdog%'
            AND canal = CAST(:canal AS canal_enum)
          ORDER BY id DESC
          LIMIT 1"
    );
    $stmt->execute([':canal' => $canal]);
    $fila = $stmt->fetch();
    return is_array($fila) && isset($fila['tipo']) ? $fila['tipo'] : null;
}

function watchdog_registrar(PDO $pdo, string $canal, string $tipo, string $descripcion, array $probe, string $baseUrl): void
{
    $contexto = json_encode([
        'origen'    => 'watchdog',
        'canal'     => $canal,
        'probe_url' => $baseUrl,
        'detalle'   => $probe['detalle'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // `canal` va explícito y casteado a canal_enum: la columna acepta NULL y su
    // DEFAULT en la base es 'whatsapp', así que omitirla etiquetaría como
    // WhatsApp las caídas del bot de Telegram.
    $stmt = $pdo->prepare(
        "INSERT INTO errores_log (chat_id, descripcion, contexto, tipo, canal)
         VALUES (NULL, :desc, :ctx, :tipo, CAST(:canal AS canal_enum))"
    );
    $stmt->execute([
        ':desc'  => $descripcion,
        ':ctx'   => $contexto,
        ':tipo'  => $tipo,
        ':canal' => $canal,
    ]);

    watchdog_log("[$canal] registrado {$tipo}: {$descripcion}");
}

function watchdog_ejecutar(PDO $pdo, string $canal, array $probe, string $baseUrl): void
{
    $funcional      = $probe['funcional'];
    $ultimo         = watchdog_ultimo_evento($pdo, $canal);
    $ultimoFueCaida = ($ultimo === 'estado_caida');

    if ($funcional && $ultimoFueCaida) {
        watchdog_registrar($pdo, $canal, 'estado_recuperacion', 'Watchdog: bot funcional', $probe, $baseUrl);
    } elseif (!$funcional && !$ultimoFueCaida) {
        watchdog_registrar($pdo, $canal, 'estado_caida', 'Watchdog: ' . $probe['detalle'], $probe, $baseUrl);
    } else {
        watchdog_log("[$canal] sin cambios (estado: " . ($funcional ? 'funcional' : 'caído') . ')');
    }
}

/**
 * Corre un canal de punta a punta.
 *
 * El probe es siempre el mismo; lo único que se puede apagar (con
 * <canal>_REGISTRA_AUDITORIA=0) es el INSERT de las transiciones.
 *
 * @return int 0 si se registró una transición, si no había cambios, si el canal
 *             no tiene la auditoría habilitada, o si todavía no está configurado
 *             (falta un paso de deploy, no es una falla); 1 solo si el probe o el
 *             registro se rompieron.
 */
function watchdog_canal(PDO $pdo, string $canal, array $cfg): int
{
    if ($cfg['url'] === '' || $cfg['secret'] === '') {
        // NoConfigured sale con 0: mientras falten WSP_API_URL / WSP_API_SECRET
        // en el .env del panel, esto corre cada minuto y no debe ensuciar el
        // log de cron con errores.
        watchdog_log("[$canal] sin configurar (falta la URL o el secreto en .env), se saltea.");
        return 0;
    }

    $probe = watchdog_probar($cfg['url'], $cfg['secret']);
    watchdog_log("[$canal] probe " . ($probe['funcional'] ? 'funcional' : 'caído') . " → {$probe['detalle']}");

    if (!$cfg['registra']) {
        // Se midió pero no se escribe: es el caso de la máquina de pruebas, donde
        // las caídas del contenedor local no deben aparecer en el historial del
        // hospital. Se probea igual para no perder el log de diagnóstico.
        watchdog_log("[$canal] auditoría desactivada en este entorno, no se registra.");
        return 0;
    }

    try {
        watchdog_ejecutar($pdo, $canal, $probe, $cfg['url']);
    } catch (Throwable $e) {
        watchdog_log("[$canal] ERROR: " . $e->getMessage());
        return 1;
    }
    return 0;
}

$canales = watchdog_canales();

try {
    $pdo = watchdog_conectar();
} catch (Throwable $e) {
    watchdog_log('ERROR BD: ' . $e->getMessage());
    exit(1);
}

// Cada canal se mide por separado: que uno esté mal configurado, o caído, no
// puede impedir que el otro se registre.
$fallos = 0;
foreach ($canales as $canal => $cfg) {
    if (watchdog_canal($pdo, $canal, $cfg) !== 0) {
        $fallos++;
    }
}

exit($fallos > 0 ? 1 : 0);