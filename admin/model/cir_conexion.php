<?php
declare(strict_types=1);

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

    return $pdo;
}
