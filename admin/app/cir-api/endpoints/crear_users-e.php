<?php
// Configuración básica para responder en formato JSON
header('Content-Type: application/json');
require_once __DIR__ . '/../php/consultas_users-b.php';

// Recibir los datos enviados por el 'fetch' de Javascript (en formato JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar si llegaron datos
if (!$data) {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
    exit;
}

try {
    // =========================================================================
    // PARTE 1: GUARDAR EN LA BASE DE DATOS DEL CLIENTE (MYSQL)
    // =========================================================================
    // /* COMENTADO POR AHORA: Según lo requerido, posponemos la creación en tabla 'admin'
    // $idAdminMySQL = ConsultasUsers::crearLoginAdmin($data['username'], $data['password'], $data['rol']);
    // if (!$idAdminMySQL) { throw new Exception("Error al guardar en BD del cliente."); }
    // */


    // =========================================================================
    // PARTE 2: GUARDAR EN NUESTRA BASE DE DATOS (POSTGRESQL) - TABLA PERSONA
    // =========================================================================
    $idPersona = ConsultasUsers::crearPersona([
        'dni' => $data['dni'] ?? null,
        'nombre' => $data['nombre'] ?? null,
        'apellido' => $data['apellido'] ?? null,
        'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
        'email' => $data['email'] ?? null,
        'telefono' => $data['telefono'] ?? null,
        'direccion' => $data['direccion'] ?? null
        // El código postal lo omitimos por ahora si no está en la tabla persona del README.
    ]);

    // Responder al front-end
    if ($idPersona) {
        echo json_encode([
            "status" => "success", 
            "message" => "Usuario creado y guardado en tabla Persona.",
            "id_persona" => $idPersona
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo guardar los datos de la persona."]);
    }

} catch (Exception $e) {
    // Si algo falla, devolvemos el error de forma segura en JSON
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>
