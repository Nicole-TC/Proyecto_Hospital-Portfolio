<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

require_once __DIR__ . '/../php/gestion_pacientes-b.php';

$method = $_SERVER['REQUEST_METHOD'];

try {

    // ====================================================================
    // GET - Listar pacientes o obtener un paciente por ID
    // ====================================================================
    if ($method === 'GET') {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id) {
            // Obtener un paciente específico para editar
            $persona = ConsultasPacientes::obtenerPacienteCompleto($id);
            if (!$persona) {
                echo json_encode(["status" => "error", "message" => "Paciente no encontrado."]);
                exit;
            }
            echo json_encode(["status" => "success", "persona" => $persona]);
            exit;
        }

        // Listar pacientes con paginación y filtros
        $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : null;
        $valor  = isset($_GET['valor'])  ? $_GET['valor']  : null;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

        $resultado = ConsultasPacientes::listarPacientes($filtro, $valor, $pagina, $limite);
        $stats     = ConsultasPacientes::obtenerEstadisticas();

        echo json_encode([
            "status"    => "success",
            "pacientes" => $resultado['pacientes'],
            "stats"     => $stats,
            "total"     => $resultado['total'],
            "pagina"    => $pagina,
            "limite"    => $limite
        ]);
        exit;
    }

    // ====================================================================
    // POST - Actualizar datos de un paciente
    // ====================================================================
    if ($method === 'POST') {

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
            exit;
        }

        $accion = $data['accion'] ?? '';

        // ─── DESACTIVAR PACIENTE (BAJA LÓGICA) ──────────────────────────────
        if ($accion === 'eliminar') {
            $id_persona = filter_var($data['id_persona'] ?? '', FILTER_VALIDATE_INT);
            if (!$id_persona || $id_persona <= 0) {
                echo json_encode(["status" => "error", "message" => "ID de paciente inválido."]);
                exit;
            }

            try {
                $desactivado = ConsultasPacientes::desactivarPaciente($id_persona);
                if (!$desactivado) {
                    echo json_encode(["status" => "error", "message" => "No se pudo desactivar el paciente."]);
                    exit;
                }
                echo json_encode(["status" => "success", "message" => "Paciente desactivado correctamente."]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            exit;
        }

        // ─── ACTUALIZAR PACIENTE ───────────────────────────────────────────
        $id_persona = filter_var($data['id_persona'] ?? '', FILTER_VALIDATE_INT);
        if (!$id_persona || $id_persona <= 0) {
            echo json_encode(["status" => "error", "message" => "ID de persona inválido."]);
            exit;
        }

        $pacienteActual = ConsultasPacientes::obtenerPacienteCompleto($id_persona);
        if (!$pacienteActual) {
            echo json_encode(["status" => "error", "message" => "El paciente no existe."]);
            exit;
        }

        $errores = [];

        $nombre           = mb_strtoupper(trim($data['nombre']   ?? ''), 'UTF-8');
        $apellido         = mb_strtoupper(trim($data['apellido'] ?? ''), 'UTF-8');
        $fecha_nacimiento = trim($data['fecha_nacimiento'] ?? '');
        $email            = trim($data['email'] ?? '');
        $telefono         = trim($data['telefono'] ?? '');
        $direccion        = mb_strtoupper(trim($data['direccion']     ?? ''), 'UTF-8');
        $codigo_postal    = mb_strtoupper(trim($data['codigo_postal'] ?? ''), 'UTF-8');
        $obra_social      = isset($data['obraSocial']) ? ($data['obraSocial'] === 't' || $data['obraSocial'] === 'true' ? 't' : 'f') : null;

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
            } elseif ($fecha > $hoy) {
                $errores[] = "La fecha de nacimiento no puede ser posterior al día de hoy.";
            }
        }

        // EMAIL
        if (empty($email)) {
            $errores[] = "El correo electrónico es obligatorio.";
        } elseif (preg_match('/\s/', $email)) {
            $errores[] = "El correo electrónico no puede contener espacios.";
        } elseif (!preg_match('/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,}$/', $email)) {
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

        // DIRECCIÓN (opcional)
        if (!empty($direccion) && mb_strlen($direccion, 'UTF-8') < 5) {
            $errores[] = "La dirección debe tener al menos 5 caracteres.";
        }

        // CÓDIGO POSTAL (opcional)
        if (!empty($codigo_postal)) {
            if (!preg_match('/^[A-Z0-9]+$/', $codigo_postal)) {
                $errores[] = "El código postal solo puede contener letras y números.";
            } elseif (mb_strlen($codigo_postal) < 4 || mb_strlen($codigo_postal) > 8) {
                $errores[] = "El código postal debe tener entre 4 y 8 caracteres.";
            }
        }

        if (!empty($errores)) {
            echo json_encode(["status" => "error", "message" => "Por favor, corregí los siguientes errores:", "errores" => $errores]);
            exit;
        }

        $camposAActualizar = [];
        if ($nombre           !== ($pacienteActual['nombre']           ?? '')) $camposAActualizar['nombre']           = $nombre;
        if ($apellido         !== ($pacienteActual['apellido']         ?? '')) $camposAActualizar['apellido']         = $apellido;
        if ($fecha_nacimiento !== ($pacienteActual['fecha_nacimiento'] ?? '')) $camposAActualizar['fecha_nacimiento'] = $fecha_nacimiento;
        if ($email            !== ($pacienteActual['email']            ?? '')) $camposAActualizar['email']            = $email;
        if ($telefono         !== ($pacienteActual['telefono']         ?? '')) $camposAActualizar['telefono']         = $telefono;
        if ($direccion        !== ($pacienteActual['direccion']        ?? '')) $camposAActualizar['direccion']        = $direccion;
        if ($codigo_postal    !== ($pacienteActual['codigo_postal']    ?? '')) $camposAActualizar['codigo_postal']    = $codigo_postal;

        if ($obra_social !== null && ($obra_social !== ($pacienteActual['obra_social'] ?? 'f'))) $camposAActualizar['obra_social'] = $obra_social;

        if (!empty($camposAActualizar)) {
            $actualizado = ConsultasPacientes::actualizarPacienteParcial($id_persona, $camposAActualizar);
            if (!$actualizado) {
                throw new Exception("No se pudo actualizar el paciente en la base de datos.");
            }
        }

        echo json_encode([
            "status"  => "success",
            "message" => "Paciente actualizado correctamente."
        ]);
        exit;
    }

    // ====================================================================
    // Método no permitido
    // ====================================================================
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>
