<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

require_once __DIR__ . '/../php/funciones.php';

$id_padre = isset($_GET['id_padre']) ? (int)$_GET['id_padre'] : 0;

if (!$id_padre) {
    echo json_encode(["status" => "error", "message" => "id_padre es requerido."]);
    exit;
}

try {
    $pdo    = conexionCircuitoQuirurgico();
    $id_act = id_estado('USUARIO', 'ACT');

    $stmt = $pdo->prepare(
        "SELECT id_especialidad, nombre
         FROM especialidad
         WHERE id_especialidad_padre = :id_padre AND id_estado = :id_act
         ORDER BY nombre"
    );
    $stmt->bindValue(':id_padre', $id_padre, PDO::PARAM_INT);
    $stmt->bindValue(':id_act',   $id_act,   PDO::PARAM_INT);
    $stmt->execute();
    $subespecialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status"           => "success",
        "subespecialidades" => $subespecialidades
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>
