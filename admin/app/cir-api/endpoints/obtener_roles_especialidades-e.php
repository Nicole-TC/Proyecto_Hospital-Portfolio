<?php
header('Content-Type: application/json');
session_start();

// Validar que el usuario esté logueado
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

// Validar que el método HTTP sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido. Este endpoint solo acepta peticiones GET."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';

try {
    $pdo = conexionCircuitoQuirurgico();

    // Obtener roles
    $stmtRoles = $pdo->query("SELECT id_rol, nombre FROM rol WHERE nombre != 'Paciente'");
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // Obtener especialidades
    $stmtEspecialidades = $pdo->query('SELECT id_especialidad, nombre FROM especialidad');
    $especialidades = $stmtEspecialidades->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener roles (excluyendo el rol 'Paciente' según solicitud)
    //$stmtRoles = $pdo->query("SELECT id_rol, nombre FROM rol WHERE nombre != 'Paciente'"); 
    //$roles = $stmtRoles->fetchAll(PDO::FETCH_NUM);
    
    // Obtener especialidades
    //$stmtEspecialidades = $pdo->query('SELECT * FROM especialidad');
    //$especialidades = $stmtEspecialidades->fetchAll(PDO::FETCH_NUM);

    echo json_encode([
        "status" => "success",
        "roles" => $roles,
        "especialidades" => $especialidades
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Error interno: " . $e->getMessage()
    ]);
}
?>
