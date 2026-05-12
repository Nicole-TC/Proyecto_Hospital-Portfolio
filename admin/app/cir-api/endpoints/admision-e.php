<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/admision-b.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && ($_GET['action'] ?? '') === 'obtener_catalogos') {
    try {
        $cirugias = ConsultasAdmision::obtenerCatalogoCirugias();
        echo json_encode([
            'ok' => true,
            'cirugias' => $cirugias
        ]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => 'Error al obtener catálogos: ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'GET' && ($_GET['action'] ?? '') === 'obtener_cirujanos') {
    try {
        $cirujanos = ConsultasAdmision::obtenerCirujanos();
        echo json_encode([
            'ok' => true,
            'cirujanos' => $cirujanos
        ]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => 'Error al obtener cirujanos: ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'GET' && ($_GET['action'] ?? '') === 'buscar_dni') {
    $dni = $_GET['dni'] ?? '';

    if (!preg_match('/^\d{2}\.?\d{3}\.?\d{3}$/', $dni)) {
        echo json_encode(['ok' => false, 'error' => 'Formato de DNI inválido']);
        exit;
    }

    $dni_clean = str_replace('.', '', $dni);

    try {
        $paciente = ConsultasAdmision::buscarPaciente($dni_clean);

        if ($paciente) {
            echo json_encode(['ok' => true, 'encontrado' => true, 'paciente' => $paciente]);
        } else {
            echo json_encode(['ok' => true, 'encontrado' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => 'Error al buscar paciente: ' . $e->getMessage()]);
    }

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

try {
    $dni_clean = str_replace('.', '', $input['dni'] ?? '');
    $paciente_existente = ConsultasAdmision::buscarPaciente($dni_clean);

    $errores = ConsultasAdmision::validarDatos($input, (bool)$paciente_existente);

    if (!empty($errores)) {
        echo json_encode(['ok' => false, 'errores' => $errores]);
        exit;
    }

    if ($paciente_existente) {
        $mensaje = "Cirugía de {$input['tipoCirugia']} registrada para el paciente.";
    } else {
        $datos = [
            'dni' => $dni_clean,
            'nombre' => $input['nombre'] ?? '',
            'apellido' => $input['apellido'] ?? '',
            'fecha_nacimiento' => $input['fechaNacimiento'] ?? '',
            'email' => $input['email'] ?? '',
            'telefono' => $input['telefono'] ?? '',
            'direccion' => $input['direccion'] ?? '',
            'codigo_postal' => $input['codigoPostal'] ?? '',
        ];

        $id = ConsultasAdmision::registrarAdmision($datos);

        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'No se pudo registrar el paciente.']);
            exit;
        }

        $mensaje = "Paciente {$input['apellido']}, {$input['nombre']} registrado correctamente con cirugía de {$input['tipoCirugia']}.";
    }

    echo json_encode(['ok' => true, 'mensaje' => $mensaje]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
