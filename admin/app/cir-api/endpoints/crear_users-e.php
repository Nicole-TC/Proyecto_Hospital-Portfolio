<?php
// Configuración básica para responder en formato JSON
header('Content-Type: application/json');
session_start();

// Validar que el usuario esté logueado en el sistema admin para evitar accesos externos por URL
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

// Validar que el método HTTP sea estrictamente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Código HTTP 405: Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Método no permitido. Este endpoint solo acepta peticiones POST."]);
    exit;
}

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
    // VALIDACIÓN DE DATOS (BACKEND)
    $errores = [];

    $dni              = trim($data['dni'] ?? '');
    $nombre           = mb_strtoupper(trim($data['nombre']   ?? ''), 'UTF-8');
    $apellido         = mb_strtoupper(trim($data['apellido'] ?? ''), 'UTF-8');
    $fecha_nacimiento = trim($data['fecha_nacimiento'] ?? '');
    $email            = trim($data['email'] ?? '');
    $telefono         = trim($data['telefono'] ?? '');
    $direccion        = mb_strtoupper(trim($data['direccion']     ?? ''), 'UTF-8');
    $codigo_postal    = mb_strtoupper(trim($data['codigo_postal'] ?? ''), 'UTF-8');
    $rol_id           = $data['rol'] ?? '';

    // Datos específicos de cirujano (opcionales dependiendo del rol)
    $especialidad_id  = $data['especialidad'] ?? null;
    $numero_matricula = trim($data['numero_matricula'] ?? '');
    $tipo_matricula   = $data['tipo_matricula'] ?? '';

    
    // ── DNI: solo dígitos, entre 6 y 8 caracteres ─────────────────────────────
    if (empty($dni)) {
        $errores[] = "El DNI es obligatorio.";
    } elseif (!preg_match('/^\d{6,8}$/', $dni)) {
        $errores[] = "El DNI debe contener entre 6 y 8 dígitos numéricos, sin puntos ni espacios.";
    } elseif (ConsultasUsers::existeDNI($dni)) {
        $errores[] = "El DNI ingresado ya se encuentra registrado en el sistema.";
    }

    // ── NOMBRE: solo letras mayúsculas (acentos y Ñ), mínimo 2 caracteres, un espacio entre palabras ──
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    } elseif (mb_strlen($nombre, 'UTF-8') < 2) {
        $errores[] = "El nombre debe tener al menos 2 caracteres.";
    } elseif (preg_match('/[0-9]/', $nombre)) {
        $errores[] = "El nombre no puede contener números.";
    } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $nombre)) {
        $errores[] = "El nombre solo puede contener letras mayúsculas, acentos, Ñ y un espacio entre palabras.";
    }

    // ── APELLIDO: mismas reglas que nombre ──────────────────────────────────────
    if (empty($apellido)) {
        $errores[] = "El apellido es obligatorio.";
    } elseif (mb_strlen($apellido, 'UTF-8') < 2) {
        $errores[] = "El apellido debe tener al menos 2 caracteres.";
    } elseif (preg_match('/[0-9]/', $apellido)) {
        $errores[] = "El apellido no puede contener números.";
    } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $apellido)) {
        $errores[] = "El apellido solo puede contener letras mayúsculas, acentos, Ñ y un espacio entre palabras.";
    }


    // ── FECHA DE NACIMIENTO: obligatoria, mayor de 18 años ─────────────────────
    if (empty($fecha_nacimiento)) {
        $errores[] = "La fecha de nacimiento es obligatoria.";
    } else {
        $fecha = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
        $hoy   = new DateTime();

        if (!$fecha || $fecha->format('Y-m-d') !== $fecha_nacimiento) {
            $errores[] = "La fecha de nacimiento tiene un formato inválido.";
        } else {
            $edad = $hoy->diff($fecha)->y;
            if ($edad < 18) {
                $errores[] = "El usuario debe ser mayor de 18 años.";
            } elseif ($edad > 110) {
                $errores[] = "La fecha de nacimiento excede la edad coherente permitida (máx. 110 años).";
            }
        }
    }

    // ── EMAIL: obligatorio, sin espacios, formato válido ──────────────────────
    if (empty($email)) {
        $errores[] = "El correo electrónico es obligatorio.";
    } elseif (preg_match('/\s/', $email)) {
        $errores[] = "El correo electrónico no puede contener espacios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido.";
    }

    // ── TELÉFONO: obligatorio, solo dígitos, entre 7 y 15 caracteres ───────────
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio.";
    } elseif (preg_match('/\s/', $telefono)) {
        $errores[] = "El teléfono no puede contener espacios.";
    } elseif (!preg_match('/^\d+$/', $telefono)) {
        $errores[] = "El teléfono solo puede contener números, sin guiones ni símbolos.";
    } elseif (mb_strlen($telefono) < 7 || mb_strlen($telefono) > 15) {
        $errores[] = "El teléfono debe tener entre 7 y 15 dígitos.";
    }

    // ── ROL ────────────────────────────────────────────────────────────────────
    if (empty($rol_id)) {
        $errores[] = "Debe seleccionar un rol para el usuario.";
    }

    // ── CÓDIGO POSTAL: opcional, alfanumérico sin espacios, 4-8 caracteres ─────
    if (!empty($codigo_postal)) {
        if (preg_match('/\s/', $codigo_postal)) {
            $errores[] = "El código postal no puede contener espacios.";
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $codigo_postal)) {
            $errores[] = "El código postal solo puede contener letras y números, sin símbolos.";
        } elseif (mb_strlen($codigo_postal) < 4 || mb_strlen($codigo_postal) > 8) {
            $errores[] = "El código postal debe tener entre 4 y 8 caracteres.";
        }
    }

    // ── DATOS DE CIRUJANO: validar si se enviaron ──────────────────────────────
    if (!empty($numero_matricula) || !empty($especialidad_id) || !empty($tipo_matricula)) {
        if (empty($especialidad_id))   $errores[] = "Si es profesional, debe seleccionar una especialidad.";
        if (empty($numero_matricula))  $errores[] = "Si es profesional, el número de matrícula es obligatorio.";
        if (empty($tipo_matricula))    $errores[] = "Si es profesional, el tipo de matrícula es obligatorio.";
    }

    if (!empty($errores)) {
        echo json_encode([
            "status" => "error", 
            "message" => "Por favor, corrige los siguientes errores:", 
            "errores" => $errores
        ]);
        exit;
    }

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
        'dni' => $dni,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'fecha_nacimiento' => $fecha_nacimiento,
        'email' => $email,
        'telefono' => $telefono,
        'direccion' => $direccion,
        'codigo_postal' => $codigo_postal,
        'id_rol' => $rol_id
    ]);

    if (!$idPersona) {
        throw new Exception("No se pudo guardar los datos de la persona en la base de datos.");
    }

    // =========================================================================
    // PARTE 3: GUARDAR DATOS DE PROFESIONAL (SI CORRESPONDE)
    // =========================================================================
    if (!empty($especialidad_id)) {
        // 1. Crear el registro en profesional
        $idProfesional = ConsultasUsers::crearProfesional($idPersona);
        
        if ($idProfesional) {
            // 2. Vincular con la especialidad (tabla intermedia)
            ConsultasUsers::vincularEspecialidad($idProfesional, $especialidad_id);
            
            // 3. Crear la matrícula (incluye id_especialidad según README)
            ConsultasUsers::crearMatricula($idProfesional, $especialidad_id, $numero_matricula, $tipo_matricula);
        } else {
            throw new Exception("Error al vincular la persona como profesional.");
        }
    }

    // Responder al front-end
    echo json_encode([
        "status" => "success", 
        "message" => "Usuario creado exitosamente con todos sus datos vinculados según el nuevo esquema.",
        "id_persona" => $idPersona
    ]);

} catch (Exception $e) {
    // Si algo falla, devolvemos el error de forma segura en JSON
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>
