<?php
header('Content-Type: application/json');
session_start();

// 1. Validar que el usuario esté logueado en el sistema admin
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';
require_once __DIR__ . '/../php/funciones.php';

// Obtener la acción solicitada (por GET o POST)
$accion = $_GET['accion'] ?? null;
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true) ?? [];
    if (isset($data['accion'])) {
        $accion = $data['accion'];
    }
}

if (!$accion) {
    echo json_encode(["status" => "error", "message" => "Acción no especificada."]);
    exit;
}

try {
    switch ($accion) {
        case 'verificar_dni':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $dni = trim($_GET['dni'] ?? '');
            if (empty($dni)) {
                echo json_encode(["status" => "error", "message" => "DNI no proporcionado."]);
                exit;
            }
            if (!preg_match('/^\d{6,8}$/', $dni)) {
                echo json_encode(["status" => "error", "message" => "Formato de DNI inválido."]);
                exit;
            }
            $existe = ConsultasUsers::existeDNI($dni);
            echo json_encode(["status" => "success", "existe" => $existe]);
            break;

        case 'subespecialidades':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $id_padre = isset($_GET['id_padre']) ? (int)$_GET['id_padre'] : 0;
            if (!$id_padre) {
                echo json_encode(["status" => "error", "message" => "id_padre es requerido."]);
                exit;
            }
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
            break;

        case 'roles_especialidades':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $pdo    = conexionCircuitoQuirurgico();
            $id_act = id_estado('USUARIO', 'ACT');

            // Roles (excluir Paciente)
            $stmtRoles = $pdo->prepare("SELECT id_rol, nombre FROM rol WHERE LOWER(nombre) != 'paciente' ORDER BY nombre");
            $stmtRoles->execute();
            $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

            // ID del rol Cirujano — evitar hardcodeo
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
            break;

        case 'obtener':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                echo json_encode(["status" => "error", "message" => "ID de persona inválido."]);
                exit;
            }
            $persona = ConsultasUsers::obtenerPersonaCompleta($id);
            if (!$persona) {
                echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
                exit;
            }
            echo json_encode(["status" => "success", "persona" => $persona]);
            break;

        case 'desactivar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $id_persona = filter_var($data['id_persona'] ?? '', FILTER_VALIDATE_INT);
            if (!$id_persona || $id_persona <= 0) {
                echo json_encode(["status" => "error", "message" => "ID de persona inválido."]);
                exit;
            }
            $ok = ConsultasUsers::desactivarPersona($id_persona);
            if ($ok) {
                echo json_encode(["status" => "success", "message" => "El usuario fue dado de baja correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo dar de baja al usuario."]);
            }
            break;

        case 'listar':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido para listar."]);
                exit;
            }
            $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : null;
            $valor  = isset($_GET['valor'])  ? $_GET['valor']  : null;
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

            $resultado = ConsultasUsers::listarPersonas($filtro, $valor, $pagina, $limite);
            $usuarios  = $resultado['usuarios'];
            $total     = $resultado['total'];
            $stats     = ConsultasUsers::obtenerEstadisticas();

            echo json_encode([
                "status" => "success", 
                "usuarios" => $usuarios,
                "stats" => $stats,
                "total" => $total,
                "pagina" => $pagina,
                "limite" => $limite
            ]);
            break;

        case 'crear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
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

            // Datos específicos de cirujano
            $especialidad_id    = $data['especialidad']     ?? null;
            $numero_matricula   = trim($data['numero_matricula'] ?? '');
            $tipo_matricula     = $data['tipo_matricula']   ?? '';
            $subespecialidades  = $data['subespecialidades'] ?? []; // array de IDs
            $matricula_sub      = $data['matricula_sub']    ?? []; // ['id' => 'numero']
            $tipo_matricula_sub = $data['tipo_matricula_sub'] ?? []; // ['id' => 'tipo']

            if (empty($dni)) {
                $errores[] = "El DNI es obligatorio.";
            } elseif (!preg_match('/^\d{6,8}$/', $dni)) {
                $errores[] = "El DNI debe contener entre 6 y 8 dígitos numéricos, sin puntos ni espacios.";
            } elseif (ConsultasUsers::existeDNI($dni)) {
                $errores[] = "El DNI ingresado ya se encuentra registrado en el sistema.";
            }

            if (empty($nombre)) {
                $errores[] = "El nombre es obligatorio.";
            } elseif (mb_strlen($nombre, 'UTF-8') < 2) {
                $errores[] = "El nombre debe tener al menos 2 caracteres.";
            } elseif (preg_match('/[0-9]/', $nombre)) {
                $errores[] = "El nombre no puede contener números.";
            } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $nombre)) {
                $errores[] = "El nombre solo puede contener letras mayúsculas, acentos, Ñ y un espacio entre palabras.";
            }

            if (empty($apellido)) {
                $errores[] = "El apellido es obligatorio.";
            } elseif (mb_strlen($apellido, 'UTF-8') < 2) {
                $errores[] = "El apellido debe tener al menos 2 caracteres.";
            } elseif (preg_match('/[0-9]/', $apellido)) {
                $errores[] = "El apellido no puede contener números.";
            } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $apellido)) {
                $errores[] = "El apellido solo puede contener letras mayúsculas, acentos, Ñ y un espacio entre palabras.";
            }

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

            if (empty($email)) {
                $errores[] = "El correo electrónico es obligatorio.";
            } else {
                $email = strtolower($email);
                if (preg_match('/\s/', $email)) {
                    $errores[] = "El correo electrónico no puede contener espacios.";
                } elseif (!preg_match('/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,}$/', $email)) {
                    $errores[] = "El formato del correo electrónico no es válido (solo minúsculas, números y ._-).";
                }
            }

            if (empty($telefono)) {
                $errores[] = "El teléfono es obligatorio.";
            } elseif (!preg_match('/^\d+$/', $telefono)) {
                $errores[] = "El teléfono solo puede contener números.";
            } elseif (mb_strlen($telefono) < 7 || mb_strlen($telefono) > 15) {
                $errores[] = "El teléfono debe tener entre 7 y 15 dígitos.";
            }

            if (empty($rol_id)) {
                $errores[] = "Debe seleccionar un rol para el usuario.";
            }

            if (!empty($codigo_postal)) {
                if (!preg_match('/^[A-Z0-9]+$/', $codigo_postal)) {
                    $errores[] = "El código postal solo puede contener letras y números.";
                } elseif (mb_strlen($codigo_postal) < 4 || mb_strlen($codigo_postal) > 8) {
                    $errores[] = "El código postal debe tener entre 4 y 8 caracteres.";
                }
            }

            if (!empty($especialidad_id)) {
                if (!empty($subespecialidades) && is_array($subespecialidades)) {
                    foreach ($subespecialidades as $id_sub) {
                        $id_sub = (int)$id_sub;
                        $num_s = trim($matricula_sub[$id_sub] ?? '');
                        $tip_s = trim($tipo_matricula_sub[$id_sub] ?? '');
                        if (empty($num_s)) {
                            $errores[] = "El número de matrícula es obligatorio para todas las subespecialidades seleccionadas.";
                            break;
                        }
                        if (empty($tip_s)) {
                            $errores[] = "El tipo de matrícula es obligatorio para todas las subespecialidades seleccionadas.";
                            break;
                        }
                    }
                } else {
                    if (empty($numero_matricula))  $errores[] = "Si es profesional, el número de matrícula es obligatorio.";
                    if (empty($tipo_matricula))    $errores[] = "Si es profesional, el tipo de matrícula es obligatorio.";
                }
            }

            if (!empty($errores)) {
                echo json_encode(["status" => "error", "message" => "Por favor, corregí los siguientes errores:", "errores" => $errores]);
                exit;
            }

            $datosPersona = [
                'dni'              => $dni,
                'nombre'           => $nombre,
                'apellido'         => $apellido,
                'fecha_nacimiento' => $fecha_nacimiento,
                'email'            => $email,
                'telefono'         => $telefono,
                'direccion'        => $direccion,
                'codigo_postal'    => $codigo_postal,
                'id_rol'           => $rol_id
            ];

            $pdo = conexionCircuitoQuirurgico();
            $pdo->beginTransaction();

            $idPersona = ConsultasUsers::crearPersona($datosPersona);
            if (!$idPersona) {
                throw new Exception("No se pudo registrar la información básica del usuario.");
            }

            $stmtRol = $pdo->prepare("SELECT nombre FROM rol WHERE id_rol = :id");
            $stmtRol->bindParam(':id', $rol_id);
            $stmtRol->execute();
            $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);
            $rolNombre = strtolower($rol['nombre'] ?? '');

            if (strpos($rolNombre, 'cirujano') !== false) {
                $idProfesional = ConsultasUsers::crearProfesional($idPersona);
                if (!$idProfesional) {
                    throw new Exception("No se pudo registrar la vinculación del profesional.");
                }

                if (!empty($subespecialidades) && is_array($subespecialidades)) {
                    foreach ($subespecialidades as $id_sub) {
                        $id_sub = (int)$id_sub;
                        if ($id_sub <= 0) continue;

                        $numero_mat_sub = trim($matricula_sub[$id_sub] ?? '');
                        $tipo_mat_sub   = trim($tipo_matricula_sub[$id_sub] ?? '');

                        if (empty($numero_mat_sub)) {
                            throw new Exception('Falta el número de matrícula para la subespecialidad ID ' . $id_sub . '.');
                        }
                        if (empty($tipo_mat_sub)) {
                            throw new Exception('Falta el tipo de matrícula para la subespecialidad ID ' . $id_sub . '.');
                        }

                        ConsultasUsers::vincularEspecialidad($idProfesional, $id_sub);
                        ConsultasUsers::crearMatricula($idProfesional, $id_sub, $numero_mat_sub, $tipo_mat_sub);
                    }
                } else {
                    ConsultasUsers::vincularEspecialidad($idProfesional, $especialidad_id);
                    ConsultasUsers::crearMatricula($idProfesional, $especialidad_id, $numero_matricula, $tipo_matricula);
                }
            }

            $pdo->commit();

            echo json_encode([
                "status" => "success", 
                "message" => "Usuario creado exitosamente con todos sus datos vinculados.",
                "id_persona" => $idPersona
            ]);
            break;

        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $errores = [];

            $id_persona = filter_var($data['id_persona'] ?? '', FILTER_VALIDATE_INT);
            if (!$id_persona || $id_persona <= 0) {
                echo json_encode(["status" => "error", "message" => "ID de persona inválido."]);
                exit;
            }

            $personaActual = ConsultasUsers::obtenerPersonaCompleta($id_persona);
            if (!$personaActual) {
                echo json_encode(["status" => "error", "message" => "El usuario no existe."]);
                exit;
            }

            $nombre           = mb_strtoupper(trim($data['nombre']   ?? ''), 'UTF-8');
            $apellido         = mb_strtoupper(trim($data['apellido'] ?? ''), 'UTF-8');
            $fecha_nacimiento = trim($data['fecha_nacimiento'] ?? '');
            $email            = trim($data['email'] ?? '');
            $telefono         = trim($data['telefono'] ?? '');
            $direccion        = mb_strtoupper(trim($data['direccion']     ?? ''), 'UTF-8');
            $codigo_postal    = mb_strtoupper(trim($data['codigo_postal'] ?? ''), 'UTF-8');
            $rol_id           = $data['rol'] ?? '';

            // Datos específicos de cirujano
            $especialidad_id    = $data['especialidad']     ?? null;
            $numero_matricula   = trim($data['numero_matricula'] ?? '');
            $tipo_matricula     = $data['tipo_matricula']   ?? '';
            $subespecialidades  = $data['subespecialidades'] ?? []; // array de IDs
            $matricula_sub      = $data['matricula_sub']    ?? []; // ['id' => 'numero']
            $tipo_matricula_sub = $data['tipo_matricula_sub'] ?? []; // ['id' => 'tipo']

            if (empty($nombre)) {
                $errores[] = "El nombre es obligatorio.";
            } elseif (mb_strlen($nombre, 'UTF-8') < 2) {
                $errores[] = "El nombre debe tener al menos 2 caracteres.";
            } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $nombre)) {
                $errores[] = "El nombre solo puede contener letras mayúsculas, acentos y Ñ.";
            }

            if (empty($apellido)) {
                $errores[] = "El apellido es obligatorio.";
            } elseif (mb_strlen($apellido, 'UTF-8') < 2) {
                $errores[] = "El apellido debe tener al menos 2 caracteres.";
            } elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ]+( [A-ZÁÉÍÓÚÜÑ]+)*$/u', $apellido)) {
                $errores[] = "El apellido solo puede contener letras mayúsculas, acentos y Ñ.";
            }

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

            if (empty($email)) {
                $errores[] = "El correo electrónico es obligatorio.";
            } else {
                $email = strtolower($email);
                if (preg_match('/\s/', $email)) {
                    $errores[] = "El correo electrónico no puede contener espacios.";
                } elseif (!preg_match('/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,}$/', $email)) {
                    $errores[] = "El formato del correo electrónico no es válido (solo minúsculas, números y ._-).";
                }
            }

            if (empty($telefono)) {
                $errores[] = "El teléfono es obligatorio.";
            } elseif (!preg_match('/^\d+$/', $telefono)) {
                $errores[] = "El teléfono solo puede contener números.";
            } elseif (mb_strlen($telefono) < 7 || mb_strlen($telefono) > 15) {
                $errores[] = "El teléfono debe tener entre 7 y 15 dígitos.";
            }

            if (empty($rol_id)) {
                $errores[] = "Debe seleccionar un rol para el usuario.";
            }

            if (!empty($codigo_postal)) {
                if (!preg_match('/^[A-Z0-9]+$/', $codigo_postal)) {
                    $errores[] = "El código postal solo puede contener letras y números.";
                } elseif (mb_strlen($codigo_postal) < 4 || mb_strlen($codigo_postal) > 8) {
                    $errores[] = "El código postal debe tener entre 4 y 8 caracteres.";
                }
            }

            if (!empty($especialidad_id)) {
                if (!empty($subespecialidades) && is_array($subespecialidades)) {
                    foreach ($subespecialidades as $id_sub) {
                        $id_sub = (int)$id_sub;
                        $num_s = trim($matricula_sub[$id_sub] ?? '');
                        $tip_s = trim($tipo_matricula_sub[$id_sub] ?? '');
                        if (empty($num_s)) {
                            $errores[] = "El número de matrícula es obligatorio para todas las subespecialidades seleccionadas.";
                            break;
                        }
                        if (empty($tip_s)) {
                            $errores[] = "El tipo de matrícula es obligatorio para todas las subespecialidades seleccionadas.";
                            break;
                        }
                    }
                } else {
                    if (empty($numero_matricula))  $errores[] = "Si es profesional, el número de matrícula es obligatorio.";
                    if (empty($tipo_matricula))    $errores[] = "Si es profesional, el tipo de matrícula es obligatorio.";
                }
            }

            if (!empty($errores)) {
                echo json_encode(["status" => "error", "message" => "Por favor, corregí los siguientes errores:", "errores" => $errores]);
                exit;
            }

            $camposAActualizar = [];
            if ($nombre           !== ($personaActual['nombre']           ?? '')) $camposAActualizar['nombre']           = $nombre;
            if ($apellido         !== ($personaActual['apellido']         ?? '')) $camposAActualizar['apellido']         = $apellido;
            if ($fecha_nacimiento !== ($personaActual['fecha_nacimiento'] ?? '')) $camposAActualizar['fecha_nacimiento'] = $fecha_nacimiento;
            if ($email            !== ($personaActual['email']            ?? '')) $camposAActualizar['email']            = $email;
            if ($telefono         !== ($personaActual['telefono']         ?? '')) $camposAActualizar['telefono']         = $telefono;
            if ($direccion        !== ($personaActual['direccion']        ?? '')) $camposAActualizar['direccion']        = $direccion;
            if ($codigo_postal    !== ($personaActual['codigo_postal']    ?? '')) $camposAActualizar['codigo_postal']    = $codigo_postal;
            if ($rol_id           !=  ($personaActual['id_rol']           ?? '')) $camposAActualizar['id_rol']           = $rol_id;

            $pdo = conexionCircuitoQuirurgico();
            $pdo->beginTransaction();

            if (!empty($camposAActualizar)) {
                $actualizado = ConsultasUsers::actualizarPersonaParcial($id_persona, $camposAActualizar);
                if (!$actualizado) {
                    throw new Exception("No se pudo actualizar la persona en la base de datos.");
                }
            }

            $rolAnteriorNombre = strtolower($personaActual['rol_nombre'] ?? '');
            $eraCircujano      = strpos($rolAnteriorNombre, 'cirujano') !== false;

            $stmtRol        = $pdo->prepare("SELECT nombre FROM rol WHERE id_rol = :id");
            $stmtRol->bindParam(':id', $rol_id);
            $stmtRol->execute();
            $nuevoRol       = $stmtRol->fetch(PDO::FETCH_ASSOC);
            $nuevoRolNombre = strtolower($nuevoRol['nombre'] ?? '');
            $esCirujano     = strpos($nuevoRolNombre, 'cirujano') !== false;

            if ($eraCircujano && !$esCirujano) {
                ConsultasUsers::eliminarDatosCirujano($id_persona);
            } elseif (!$eraCircujano && $esCirujano && !empty($especialidad_id)) {
                $idProfesional = ConsultasUsers::crearProfesional($id_persona);
                if ($idProfesional) {
                    if (!empty($subespecialidades) && is_array($subespecialidades)) {
                        foreach ($subespecialidades as $id_sub) {
                            $id_sub = (int)$id_sub;
                            if ($id_sub <= 0) continue;
                            $numero_mat_sub = trim($matricula_sub[$id_sub] ?? '');
                            $tipo_mat_sub   = trim($tipo_matricula_sub[$id_sub] ?? '');
                            ConsultasUsers::vincularEspecialidad($idProfesional, $id_sub);
                            ConsultasUsers::crearMatricula($idProfesional, $id_sub, $numero_mat_sub, $tipo_mat_sub);
                        }
                    } else {
                        ConsultasUsers::vincularEspecialidad($idProfesional, $especialidad_id);
                        ConsultasUsers::crearMatricula($idProfesional, $especialidad_id, $numero_matricula, $tipo_matricula);
                    }
                }
            } elseif ($eraCircujano && $esCirujano && !empty($especialidad_id)) {
                ConsultasUsers::eliminarDatosCirujano($id_persona);
                $idProfesional = ConsultasUsers::crearProfesional($id_persona);
                if ($idProfesional) {
                    if (!empty($subespecialidades) && is_array($subespecialidades)) {
                        foreach ($subespecialidades as $id_sub) {
                            $id_sub = (int)$id_sub;
                            if ($id_sub <= 0) continue;
                            $numero_mat_sub = trim($matricula_sub[$id_sub] ?? '');
                            $tipo_mat_sub   = trim($tipo_matricula_sub[$id_sub] ?? '');
                            ConsultasUsers::vincularEspecialidad($idProfesional, $id_sub);
                            ConsultasUsers::crearMatricula($idProfesional, $id_sub, $numero_mat_sub, $tipo_mat_sub);
                        }
                    } else {
                        ConsultasUsers::vincularEspecialidad($idProfesional, $especialidad_id);
                        ConsultasUsers::crearMatricula($idProfesional, $especialidad_id, $numero_matricula, $tipo_matricula);
                    }
                }
            }

            $pdo->commit();

            echo json_encode([
                "status"  => "success",
                "message" => "Usuario actualizado correctamente."
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Acción no válida."]);
            break;
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>
