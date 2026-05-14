<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido. Este endpoint solo acepta peticiones POST."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
    exit;
}

try {
    $errores = [];

    // ── ID obligatorio ─────────────────────────────────────────────────────────
    $id_persona = filter_var($data['id_persona'] ?? '', FILTER_VALIDATE_INT);
    if (!$id_persona || $id_persona <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de persona inválido."]);
        exit;
    }

    // Verificar que la persona exista
    $personaActual = ConsultasUsers::obtenerPersonaCompleta($id_persona);
    if (!$personaActual) {
        echo json_encode(["status" => "error", "message" => "El usuario no existe."]);
        exit;
    }

    // ── Sanitizar datos (el DNI NO se puede modificar) ────────────────────────
    $nombre           = mb_strtoupper(trim($data['nombre']   ?? ''), 'UTF-8');
    $apellido         = mb_strtoupper(trim($data['apellido'] ?? ''), 'UTF-8');
    $fecha_nacimiento = trim($data['fecha_nacimiento'] ?? '');
    $email            = trim($data['email'] ?? '');
    $telefono         = trim($data['telefono'] ?? '');
    $direccion        = mb_strtoupper(trim($data['direccion']     ?? ''), 'UTF-8');
    $codigo_postal    = mb_strtoupper(trim($data['codigo_postal'] ?? ''), 'UTF-8');
    $rol_id           = $data['rol'] ?? '';

    // Datos específicos de cirujano
    $especialidad_id  = $data['especialidad'] ?? null;
    $numero_matricula = trim($data['numero_matricula'] ?? '');
    $tipo_matricula   = $data['tipo_matricula'] ?? '';

    // ── VALIDACIONES (mismas reglas que crear_users-e.php) ───────────────────

    // NOMBRE
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    } elseif (mb_strlen($nombre, 'UTF-8') < 2) {
        $errores[] = "El nombre debe tener al menos 2 caracteres.";
    } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $nombre)) {
        $errores[] = "El nombre solo puede contener letras mayúsculas, acentos y Ñ.";
    }

    // APELLIDO
    if (empty($apellido)) {
        $errores[] = "El apellido es obligatorio.";
    } elseif (mb_strlen($apellido, 'UTF-8') < 2) {
        $errores[] = "El apellido debe tener al menos 2 caracteres.";
    } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $apellido)) {
        $errores[] = "El apellido solo puede contener letras mayúsculas, acentos y Ñ.";
    }

    // FECHA DE NACIMIENTO
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
                $errores[] = "La fecha de nacimiento excede la edad coherente permitida.";
            }
        }
    }

    // EMAIL
    if (empty($email)) {
        $errores[] = "El correo electrónico es obligatorio.";
    } elseif (preg_match('/\s/', $email)) {
        $errores[] = "El correo electrónico no puede contener espacios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido.";
    }

    // TELÉFONO
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio.";
    } elseif (!preg_match('/^\d+$/', $telefono)) {
        $errores[] = "El teléfono solo puede contener números.";
    } elseif (mb_strlen($telefono) < 7 || mb_strlen($telefono) > 15) {
        $errores[] = "El teléfono debe tener entre 7 y 15 dígitos.";
    }

    // ROL
    if (empty($rol_id)) {
        $errores[] = "Debe seleccionar un rol para el usuario.";
    }

    // CÓDIGO POSTAL (opcional)
    if (!empty($codigo_postal)) {
        if (!preg_match('/^[A-Z0-9]+$/', $codigo_postal)) {
            $errores[] = "El código postal solo puede contener letras y números.";
        } elseif (mb_strlen($codigo_postal) < 4 || mb_strlen($codigo_postal) > 8) {
            $errores[] = "El código postal debe tener entre 4 y 8 caracteres.";
        }
    }

    // DATOS CIRUJANO (si se envían)
    if (!empty($numero_matricula) || !empty($especialidad_id) || !empty($tipo_matricula)) {
        if (empty($especialidad_id))   $errores[] = "Si es profesional, debe seleccionar una especialidad.";
        if (empty($numero_matricula))  $errores[] = "Si es profesional, el número de matrícula es obligatorio.";
        if (empty($tipo_matricula))    $errores[] = "Si es profesional, el tipo de matrícula es obligatorio.";
    }

    if (!empty($errores)) {
        echo json_encode(["status" => "error", "message" => "Por favor, corregí los siguientes errores:", "errores" => $errores]);
        exit;
    }

    // ── ACTUALIZAR PERSONA ────────────────────────────────────────────────────
    $actualizado = ConsultasUsers::actualizarPersona($id_persona, [
        'nombre'           => $nombre,
        'apellido'         => $apellido,
        'fecha_nacimiento' => $fecha_nacimiento,
        'email'            => $email,
        'telefono'         => $telefono,
        'direccion'        => $direccion,
        'codigo_postal'    => $codigo_postal,
        'id_rol'           => $rol_id,
    ]);

    if (!$actualizado) {
        throw new Exception("No se pudo actualizar la persona en la base de datos.");
    }

    // ── MANEJAR CAMBIO DE ROL CIRUJANO ───────────────────────────────────────
    $rolAnteriorNombre = strtolower($personaActual['rol_nombre'] ?? '');
    $eraCircujano      = strpos($rolAnteriorNombre, 'cirujano') !== false;

    // Obtener nombre del nuevo rol
    $pdo            = conexionCircuitoQuirurgico();
    $stmtRol        = $pdo->prepare("SELECT nombre FROM rol WHERE id_rol = :id");
    $stmtRol->bindParam(':id', $rol_id);
    $stmtRol->execute();
    $nuevoRol       = $stmtRol->fetch(PDO::FETCH_ASSOC);
    $nuevoRolNombre = strtolower($nuevoRol['nombre'] ?? '');
    $esCirujano     = strpos($nuevoRolNombre, 'cirujano') !== false;

    if ($eraCircujano && !$esCirujano) {
        // Cambió DE cirujano A otro rol → eliminar todos los datos de cirujano
        ConsultasUsers::eliminarDatosCirujano($id_persona);

    } elseif (!$eraCircujano && $esCirujano && !empty($especialidad_id)) {
        // Cambió DE otro rol A cirujano → crear datos de cirujano
        $idProfesional = ConsultasUsers::crearProfesional($id_persona);
        if ($idProfesional) {
            ConsultasUsers::vincularEspecialidad($idProfesional, $especialidad_id);
            ConsultasUsers::crearMatricula($idProfesional, $especialidad_id, $numero_matricula, $tipo_matricula);
        }

    } elseif ($eraCircujano && $esCirujano && !empty($especialidad_id)) {
        // Sigue siendo cirujano → actualizar datos (borrar y recrear)
        ConsultasUsers::eliminarDatosCirujano($id_persona);
        $idProfesional = ConsultasUsers::crearProfesional($id_persona);
        if ($idProfesional) {
            ConsultasUsers::vincularEspecialidad($idProfesional, $especialidad_id);
            ConsultasUsers::crearMatricula($idProfesional, $especialidad_id, $numero_matricula, $tipo_matricula);
        }
    }

    echo json_encode([
        "status"  => "success",
        "message" => "Usuario actualizado correctamente."
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>
