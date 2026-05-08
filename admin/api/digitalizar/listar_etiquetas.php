<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/conexion_dig.php';
$stmt = $bd_dig->query("SELECT id, nombre_etiqueta, color_hex, estado FROM etiquetas ORDER BY id ASC");
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($datos);