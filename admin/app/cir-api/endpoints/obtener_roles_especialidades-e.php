<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';
require_once __DIR__ . '/../php/funciones.php';

try {
    $pdo    = conexionCircuitoQuirurgico();
    $id_act = id_estado('USUARIO', 'ACT');

    // Roles (excluir Paciente)
    $stmtRoles = $pdo->prepare("SELECT id_rol, nombre FROM rol WHERE LOWER(nombre) != 'paciente' ORDER BY nombre");
    $stmtRoles->execute();
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // ID del rol Cirujano — evitar hardcodeo en el JS
    $stmtCirujano = $pdo->prepare("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'cirujano' LIMIT 1");
    $stmtCirujano->execute();
    $idRolCirujano = $stmtCirujano->fetchColumn();

    // Solo especialidades troncales (id_especialidad_padre IS NULL) y activas
    $stmtEsp = $pdo->prepare(
        "SELECT id_especialidad, nombre
         FROM especialidad
         WHERE id_especialidad_padre IS NULL AND id_estado = :id_act
         ORDER BY nombre"
    );
    $stmtEsp->bindValue(':id_act', $id_act, PDO::PARAM_INT);
    $stmtEsp->execute();
    $especialidades = $stmtEsp->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status"          => "success",
        "roles"           => $roles,
        "id_rol_cirujano" => (int)$idRolCirujano,
        "especialidades"  => $especialidades
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>
