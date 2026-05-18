<?php
session_start();

// Configurar cabeceras para retornar JSON y aceptar peticiones
header('Content-Type: application/json; charset=utf-8');

// Validar que el usuario esté autenticado
// (Descomenta esta validación si ya tienes el sistema de login funcionando)
/*
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error", 
        "message" => "No autorizado. Por favor inicie sesión."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
*/

// Incluir conexión a la base de datos
require_once __DIR__ . '/../../model/conexion_dig.php';

// Validar que la petición sea GET (para búsquedas suele ser GET, aunque POST también es válido)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error", 
        "message" => "Método no permitido. Utilice GET."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Aquí implementaremos la lógica de búsqueda en las siguientes tareas
    
    // Respuesta de prueba temporal para validar que el endpoint funciona
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Endpoint configurado correctamente. Listo para recibir filtros.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error interno del servidor: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
