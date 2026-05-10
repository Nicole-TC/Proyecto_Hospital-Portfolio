<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

// require_once '../../model/conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && ($_GET['action'] ?? '') === 'buscar_dni') {
    $dni = $_GET['dni'] ?? '';

    if (!preg_match('/^\d{2}\.?\d{3}\.?\d{3}$/', $dni)) {
        echo json_encode(['ok' => false, 'error' => 'Formato de DNI inválido']);
        exit;
    }

    $dni_clean = str_replace('.', '', $dni);

    // TODO: Buscar en DB
    echo json_encode(['ok' => true, 'encontrado' => false]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

$dni = $input['dni'] ?? '';
$apellido = $input['apellido'] ?? '';
$nombre = $input['nombre'] ?? '';
$fechaNacimiento = $input['fechaNacimiento'] ?? '';
$direccion = $input['direccion'] ?? '';
$codigoPostal = $input['codigoPostal'] ?? '';
$email = $input['email'] ?? '';
$telefono = $input['telefono'] ?? '';
$tipoCirugia = $input['tipoCirugia'] ?? '';
$tipoCirujano = $input['tipoCirujano'] ?? '';

$errores = [];
$dni_clean = str_replace('.', '', $dni);

if (!preg_match('/^\d{2}\.?\d{3}\.?\d{3}$/', $dni)) {
    $errores[] = 'El formato del DNI no es válido.';
}

if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{3,100}$/', $tipoCirugia)) {
    $errores[] = 'El tipo de cirugía no es válido.';
}

if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{3,100}$/', $tipoCirujano)) {
    $errores[] = 'El tipo de cirujano no es válido.';
}

// TODO: Consultar en DB si el paciente existe
$paciente_existente = false;

if (!$paciente_existente) {
    $solo_letras = '/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,100}$/';

    if (!preg_match($solo_letras, $apellido)) {
        $errores[] = 'El apellido solo debe contener letras (mín. 2).';
    }

    if (!preg_match($solo_letras, $nombre)) {
        $errores[] = 'El nombre solo debe contener letras (mín. 2).';
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
        $errores[] = 'El formato de la fecha de nacimiento no es válido.';
    } else {
        $parts = explode('-', $fechaNacimiento);
        if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            $errores[] = 'La fecha de nacimiento no es una fecha real.';
        }
    }

    if (!preg_match('/^[a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ\s\.,#\-\/]{5,255}$/', $direccion)) {
        $errores[] = 'El formato de la dirección no es válido.';
    }

    if (!preg_match('/^\d{4}$/', $codigoPostal)) {
        $errores[] = 'El código postal debe tener 4 dígitos.';
    }

    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errores[] = 'El formato del email no es válido.';
    }

    if (!preg_match('/^[\d\s\-\(\)\+]{7,20}$/', $telefono)) {
        $errores[] = 'El formato del teléfono no es válido.';
    }
}

if (!empty($errores)) {
    echo json_encode(['ok' => false, 'errores' => $errores]);
    exit;
}

// TODO: Insertar en DB
// if ($paciente_existente) {
//     $mensaje = "Cirugía de $tipoCirugia registrada para el paciente.";
// } else {
//     $mensaje = "Paciente $apellido, $nombre registrado correctamente con cirugía de $tipoCirugia.";
// }

$mensaje = "Validación exitosa. Todos los campos son correctos. (Pendiente de inserción en DB)";

echo json_encode(['ok' => true, 'mensaje' => $mensaje]);
