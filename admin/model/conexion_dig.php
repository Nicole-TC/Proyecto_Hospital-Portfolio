<?php

/*if ($_SERVER['SERVER_NAME'] == '149.50.133.15')*/ {
    // BD Digitalización - PostgreSQL
    $host_dig = '149.50.133.15';
    $contrasena_dig = "d3v_d1g!";
    $usuario_dig = "dev_dig";
    $nombre_bd_dig = "digitalizar";
    $port_dig = "9432";
} /*else {
    $host_dig = '';
    $usuario_dig = '';
    $contrasena_dig = '';
    $nombre_bd_dig = '';
    $port_dig = "9432";
}*/

try {
    $bd_dig = new PDO(
        'pgsql:host=' . $host_dig . ';port=' . $port_dig . ';dbname=' . $nombre_bd_dig,
        $usuario_dig,
        $contrasena_dig
    );
    $bd_dig->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Problema con la conexion: " . $e->getMessage();
}

?>
