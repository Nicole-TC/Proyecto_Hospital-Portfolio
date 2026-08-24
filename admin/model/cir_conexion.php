<?php
declare(strict_types=1);

// Cargar variables de entorno desde .env si no están definidas
$envPath = __DIR__ . '/../../.env';
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

/**
 * Conexion reutilizable para Circuito Quirurgico (PostgreSQL)
 * Lee variables desde entorno:
 * DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD
 */

function conexionCircuitoQuirurgico(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASSWORD') ?: '';

    if ($host === '' || $name === '' || $user === '') {
        throw new RuntimeException('Faltan variables de entorno para la conexion PostgreSQL.');
    }

    $dsn = "pgsql:host={$host};port={$port};dbname={$name}";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Opcional: fija zona horaria de sesion
    $pdo->exec("SET TIME ZONE 'America/Argentina/Buenos_Aires'");

    // Propaga la identidad del usuario logueado a la sesion de PostgreSQL para que
    // los triggers de auditoria sepan quien ejecuto la accion. Mismo patron que ya
    // usa conexion_dig.php con myapp.ip_origen.
    // Si no hay sesion activa (tarea de sistema, CLI) quedan vacios y la auditoria
    // registra el movimiento como disparado por el sistema.
    $idUsuario     = isset($_SESSION['id']) ? (string) $_SESSION['id'] : '';
    $nombreUsuario = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
    if ($nombreUsuario === '') {
        $nombreUsuario = (string) ($_SESSION['user'] ?? '');
    }
    $ipOrigen = $_SERVER['REMOTE_ADDR'] ?? '';

    $pdo->exec(
        "SELECT set_config('myapp.id_usuario', "     . $pdo->quote($idUsuario)     . ", false), "
      .        "set_config('myapp.nombre_usuario', " . $pdo->quote($nombreUsuario) . ", false), "
      .        "set_config('myapp.ip_origen', "      . $pdo->quote($ipOrigen)      . ", false)"
    );

    return $pdo;
}
