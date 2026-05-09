<?php

require_once '../../model/circuitoquirurgico.php';

try {

    $pdo = conexionCircuitoQuirurgico();

    echo "<h2>Conexion PostgreSQL OK</h2>";

    $sql = "SELECT NOW() AS fecha";

    $query = $pdo->prepare($sql);

    $query->execute();

    $resultado = $query->fetch();

    echo "<pre>";

    print_r($resultado);

    echo "</pre>";

} catch (Exception $e) {

    echo "Error: " . $e->getMessage();

}
