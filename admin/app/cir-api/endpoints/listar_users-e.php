<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../php/consultas_users-b.php';

try {
    // Obtenemos la lista de personas desde la base de datos PostgreSQL
    $usuarios = ConsultasUsers::listarPersonas();
    
    // Devolvemos la respuesta al front-end
    echo json_encode([
        "status" => "success", 
        "usuarios" => $usuarios
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Error al obtener usuarios: " . $e->getMessage()
    ]);
}
?>
