<?php

// BD Digitalización - PostgreSQL
$host_dig = getenv('DB_DIG_HOST');
$usuario_dig = getenv('DB_DIG_USER');
$contrasena_dig = getenv('DB_DIG_PASSWORD');
$nombre_bd_dig = getenv('DB_DIG_NAME');
$port_dig = getenv('DB_DIG_PORT');

try {
    $bd_dig = new PDO(
        'pgsql:host=' . $host_dig . ';port=' . $port_dig . ';dbname=' . $nombre_bd_dig,
        $usuario_dig,
        $contrasena_dig
    );
    $bd_dig->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $bd_dig->exec("SET TIME ZONE 'America/Argentina/Buenos_Aires'");
    // echo "Conexión exitosa";
} catch (Exception $e) {
    error_log("Problema con la conexion de digitalización: " . $e->getMessage());
}

?>
