<?php

/*if ($_SERVER['SERVER_NAME'] == '149.50.133.15')*/ {
    // BD Desarrollo*/
    $host = '149.50.133.15';
    $contrasena = "h0sp1t4l2026";
    $usuario = "hospital_demo";
    $nombre_bd = "hospital_demo";
    $port = "3307";
} /*else {
  // Servidor (A definir)
  $host = 'localhost';
  $usuario = '';
  $contrasena = '';
  $nombre_bd = '';
  $port = "3306";
}*/

try {
    $bd = new PDO(
        'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $nombre_bd,
        $usuario,
        $contrasena,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
    );
} catch (Exception $e) {
    echo "Problema con la conexion: " . $e->getMessage();
}

?>