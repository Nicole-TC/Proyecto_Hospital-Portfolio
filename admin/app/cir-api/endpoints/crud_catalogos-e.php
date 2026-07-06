<?php
header('Content-Type: application/json');
session_start();

// Seguridad: validar sesión
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_cirugias-b.php';

$accion = $_GET['accion'] ?? null;
$data   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json   = file_get_contents('php://input');
    $data   = json_decode($json, true) ?? [];
    if (isset($data['accion'])) {
        $accion = $data['accion'];
    }
}

if (!$accion) {
    echo json_encode(["status" => "error", "message" => "Acción no especificada."]);
    exit;
}

/* ============================================================
   Helpers de validación reutilizables
   ============================================================ */
function validarNombreCampo(string $valor, int $minLen, int $maxLen, string $campo): ?string {
    if (empty($valor)) return "El $campo es obligatorio.";
    if (mb_strlen($valor, 'UTF-8') < $minLen) return "El $campo debe tener al menos $minLen caracteres.";
    if (mb_strlen($valor, 'UTF-8') > $maxLen) return "El $campo no puede superar $maxLen caracteres.";
    if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ0-9\s\-\/\(\)\.]+$/u', $valor))
        return "El $campo contiene caracteres no permitidos.";
    return null;
}

/* ============================================================
   SWITCH DE ACCIONES
   ============================================================ */
try {
    switch ($accion) {

        /* ---------- VALIDACIÓN EN VIVO ---------- */
        case 'validar_nombre_cirugia':
            $nombre = trim($_GET['nombre'] ?? '');
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "message" => "Nombre vacío."]);
                exit;
            }
            $excluirId = isset($_GET['excluir']) ? (int)$_GET['excluir'] : null;
            $existe = ConsultasCirugias::existeNombreCirugia($nombre, $excluirId);
            echo json_encode($existe
                ? ["status" => "error",   "message" => "Ya existe una cirugía con ese nombre."]
                : ["status" => "success", "message" => "Nombre disponible."]);
            break;

        case 'validar_nombre_categoria':
            $nombre = trim($_GET['nombre'] ?? '');
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "message" => "Nombre vacío."]);
                exit;
            }
            $excluirId = isset($_GET['excluir']) ? (int)$_GET['excluir'] : null;
            $existe = ConsultasCirugias::existeNombreCategoria($nombre, $excluirId);
            echo json_encode($existe
                ? ["status" => "error",   "message" => "Ya existe una categoría con ese nombre."]
                : ["status" => "success", "message" => "Nombre disponible."]);
            break;

        case 'validar_nombre_estudio':
            $nombre = trim($_GET['nombre'] ?? '');
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "message" => "Nombre vacío."]);
                exit;
            }
            $excluirId = isset($_GET['excluir']) ? (int)$_GET['excluir'] : null;
            $existe = ConsultasCirugias::existeNombreEstudio($nombre, $excluirId);
            echo json_encode($existe
                ? ["status" => "error",   "message" => "Ya existe un estudio con ese nombre."]
                : ["status" => "success", "message" => "Nombre disponible."]);
            break;

        /* ---------- LISTAR ---------- */
        case 'listar_categorias':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
            echo json_encode(["status" => "success", "categorias" => ConsultasCirugias::listarCategorias()]);
            break;

        case 'listar_cirugias':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
            $buscar = trim($_GET['buscar'] ?? '');
            $orden  = $_GET['orden'] ?? 'nombre_asc';
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));
            $limite = 10;
            $resultado = ConsultasCirugias::listarCirugias($buscar, $orden, $pagina, $limite);
            echo json_encode(["status" => "success"] + $resultado);
            break;

        case 'listar_estudios':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
            $buscar = trim($_GET['buscar'] ?? '');
            $orden  = $_GET['orden'] ?? 'nombre_asc';
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));
            $limite = 10;
            $resultado = ConsultasCirugias::listarEstudiosCrud($buscar, $orden, $pagina, $limite);
            echo json_encode(["status" => "success"] + $resultado);
            break;

        case 'listar_combos':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
            $buscar     = trim($_GET['buscar'] ?? '');
            $idCirugia  = isset($_GET['id_cirugia']) ? (int)$_GET['id_cirugia'] : null;
            $pagina     = max(1, (int)($_GET['pagina'] ?? 1));
            $limite     = 10;
            $resultado  = ConsultasCirugias::listarCombos($buscar, $idCirugia, $pagina, $limite);
            echo json_encode(["status" => "success"] + $resultado);
            break;

        case 'listar_cirugias_select':
            // Para poblar el <select> de combos
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
            echo json_encode(["status" => "success", "cirugias" => ConsultasCirugias::listarCirugiasSelect()]);
            break;

        /* ---------- CREAR ---------- */
        case 'crear_cirugia':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $nombre     = mb_strtoupper(trim($data['nombre'] ?? ''), 'UTF-8');
            $descripcion = trim($data['descripcion'] ?? '');

            $err = validarNombreCampo($nombre, 3, 150, 'nombre de la cirugía');
            if ($err) { echo json_encode(["status" => "error", "message" => $err]); exit; }

            if (ConsultasCirugias::existeNombreCirugia($nombre)) {
                echo json_encode(["status" => "error", "message" => "Ya existe una cirugía con ese nombre."]);
                exit;
            }

            $id = ConsultasCirugias::crearCirugiaCatalogo($nombre, $descripcion);
            echo $id
                ? json_encode(["status" => "success", "message" => "Cirugía creada correctamente.", "id" => $id])
                : json_encode(["status" => "error",   "message" => "No se pudo guardar la cirugía."]);
            break;

        case 'crear_categoria':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $nombre      = mb_strtoupper(trim($data['nombre'] ?? ''), 'UTF-8');
            $descripcion = trim($data['descripcion'] ?? '');

            $err = validarNombreCampo($nombre, 3, 150, 'nombre de la categoría');
            if ($err) { echo json_encode(["status" => "error", "message" => $err]); exit; }

            if (ConsultasCirugias::existeNombreCategoria($nombre)) {
                echo json_encode(["status" => "error", "message" => "Ya existe una categoría con ese nombre."]);
                exit;
            }

            $id = ConsultasCirugias::crearCategoria($nombre, $descripcion);
            echo $id
                ? json_encode(["status" => "success", "message" => "Categoría creada correctamente.", "id" => $id])
                : json_encode(["status" => "error",   "message" => "No se pudo guardar la categoría."]);
            break;

        case 'crear_estudio':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $nombre      = mb_strtoupper(trim($data['nombre'] ?? ''), 'UTF-8');
            $descripcion = trim($data['descripcion'] ?? '');
            $observacion = trim($data['observacion'] ?? '');
            $id_categoria_estudio = isset($data['id_categoria_estudio']) && $data['id_categoria_estudio'] ? (int)$data['id_categoria_estudio'] : null;

            $err = validarNombreCampo($nombre, 3, 100, 'nombre del estudio');
            if ($err) { echo json_encode(["status" => "error", "message" => $err]); exit; }

            $idExistente = ConsultasCirugias::existeNombreEstudio($nombre);
            if ($idExistente) {
                echo json_encode(["status" => "error", "message" => "Ya existe un estudio con ese nombre."]);
                exit;
            }

            $id = ConsultasCirugias::crearEstudio($nombre, $descripcion, $observacion, $id_categoria_estudio);
            echo $id
                ? json_encode(["status" => "success", "message" => "Estudio creado correctamente.", "id" => $id, "estudio" => ["id" => $id, "nombre" => $nombre, "descripcion" => $descripcion, "observaciones" => $observacion]])
                : json_encode(["status" => "error",   "message" => "No se pudo guardar el estudio."]);
            break;

        /* ---------- EDITAR ---------- */
        case 'editar_cirugia':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $id          = (int)($data['id'] ?? 0);
            $nombre      = mb_strtoupper(trim($data['nombre'] ?? ''), 'UTF-8');
            $descripcion = trim($data['descripcion'] ?? '');

            if (!$id) { echo json_encode(["status" => "error", "message" => "ID inválido."]); exit; }

            $err = validarNombreCampo($nombre, 3, 150, 'nombre de la cirugía');
            if ($err) { echo json_encode(["status" => "error", "message" => $err]); exit; }

            if (ConsultasCirugias::existeNombreCirugia($nombre, $id)) {
                echo json_encode(["status" => "error", "message" => "Ya existe otra cirugía con ese nombre."]);
                exit;
            }

            $ok = ConsultasCirugias::actualizarCirugia($id, $nombre, $descripcion);
            echo $ok
                ? json_encode(["status" => "success", "message" => "Cirugía actualizada correctamente."])
                : json_encode(["status" => "error",   "message" => "No se pudo actualizar la cirugía."]);
            break;

        case 'editar_categoria':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $id          = (int)($data['id'] ?? 0);
            $nombre      = mb_strtoupper(trim($data['nombre'] ?? ''), 'UTF-8');
            $descripcion = trim($data['descripcion'] ?? '');

            if (!$id) { echo json_encode(["status" => "error", "message" => "ID inválido."]); exit; }

            $err = validarNombreCampo($nombre, 3, 150, 'nombre de la categoría');
            if ($err) { echo json_encode(["status" => "error", "message" => $err]); exit; }

            if (ConsultasCirugias::existeNombreCategoria($nombre, $id)) {
                echo json_encode(["status" => "error", "message" => "Ya existe otra categoría con ese nombre."]);
                exit;
            }

            $ok = ConsultasCirugias::actualizarCategoria($id, $nombre, $descripcion);
            echo $ok
                ? json_encode(["status" => "success", "message" => "Categoría actualizada correctamente."])
                : json_encode(["status" => "error",   "message" => "No se pudo actualizar la categoría."]);
            break;

        case 'editar_estudio':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $id          = (int)($data['id'] ?? 0);
            $nombre      = mb_strtoupper(trim($data['nombre'] ?? ''), 'UTF-8');
            $descripcion = trim($data['descripcion'] ?? '');
            $observacion = trim($data['observacion'] ?? '');
            $id_categoria_estudio = isset($data['id_categoria_estudio']) && $data['id_categoria_estudio'] ? (int)$data['id_categoria_estudio'] : null;

            if (!$id) { echo json_encode(["status" => "error", "message" => "ID inválido."]); exit; }

            $err = validarNombreCampo($nombre, 3, 100, 'nombre del estudio');
            if ($err) { echo json_encode(["status" => "error", "message" => $err]); exit; }

            $idExistente = ConsultasCirugias::existeNombreEstudio($nombre, $id);
            if ($idExistente) {
                echo json_encode(["status" => "error", "message" => "Ya existe otro estudio con ese nombre."]);
                exit;
            }

            $ok = ConsultasCirugias::actualizarEstudio($id, $nombre, $descripcion, $observacion, $id_categoria_estudio);
            echo $ok
                ? json_encode(["status" => "success", "message" => "Estudio actualizado correctamente."])
                : json_encode(["status" => "error",   "message" => "No se pudo actualizar el estudio."]);
            break;

        /* ---------- ELIMINAR (borrado lógico) ---------- */
        case 'eliminar_cirugia':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $id = (int)($data['id'] ?? 0);
            if (!$id) { echo json_encode(["status" => "error", "message" => "ID inválido."]); exit; }

            // Verificar si tiene combos activos (restricción relacional)
            if (ConsultasCirugias::cirugiaEnUsoCombos($id)) {
                echo json_encode(["status" => "error",
                    "message" => "No se puede eliminar: la cirugía tiene estudios vinculados. Elimine primero los combos."]);
                exit;
            }
            if (ConsultasCirugias::cirugiaEnUsoOrdenes($id)) {
                echo json_encode(["status" => "error",
                    "message" => "No se puede eliminar: la cirugía está vinculada a órdenes médicas existentes."]);
                exit;
            }

            $ok = ConsultasCirugias::eliminarCirugia($id);
            echo $ok
                ? json_encode(["status" => "success", "message" => "Cirugía eliminada correctamente."])
                : json_encode(["status" => "error",   "message" => "No se pudo eliminar la cirugía."]);
            break;

        case 'eliminar_categoria':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $id = (int)($data['id'] ?? 0);
            if (!$id) { echo json_encode(["status" => "error", "message" => "ID inválido."]); exit; }

            // Verificar si la categoría tiene estudios
            if (ConsultasCirugias::categoriaEnUsoEstudios($id)) {
                echo json_encode(["status" => "error",
                    "message" => "No se puede eliminar: la categoría tiene estudios vinculados."]);
                exit;
            }

            $ok = ConsultasCirugias::eliminarCategoria($id);
            echo $ok
                ? json_encode(["status" => "success", "message" => "Categoría eliminada correctamente."])
                : json_encode(["status" => "error",   "message" => "No se pudo eliminar la categoría."]);
            break;

        case 'eliminar_estudio':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $id = (int)($data['id'] ?? 0);
            if (!$id) { echo json_encode(["status" => "error", "message" => "ID inválido."]); exit; }

            // Verificar si el estudio está en combos o en órdenes de estudio
            if (ConsultasCirugias::estudioEnUsoCombos($id)) {
                echo json_encode(["status" => "error",
                    "message" => "No se puede eliminar: el estudio está vinculado a una o más cirugías. Elimine primero los combos."]);
                exit;
            }
            if (ConsultasCirugias::estudioEnUsoOrdenes($id)) {
                echo json_encode(["status" => "error",
                    "message" => "No se puede eliminar: el estudio está referenciado en órdenes médicas existentes."]);
                exit;
            }

            $ok = ConsultasCirugias::eliminarEstudio($id);
            echo $ok
                ? json_encode(["status" => "success", "message" => "Estudio eliminado correctamente."])
                : json_encode(["status" => "error",   "message" => "No se pudo eliminar el estudio."]);
            break;

        case 'eliminar_combo':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            $idCirugia = (int)($data['id_cirugia'] ?? 0);
            $idEstudio = (int)($data['id_estudio'] ?? 0);
            if (!$idCirugia || !$idEstudio) {
                echo json_encode(["status" => "error", "message" => "IDs inválidos."]);
                exit;
            }

            $ok = ConsultasCirugias::eliminarCombo($idCirugia, $idEstudio);
            echo $ok
                ? json_encode(["status" => "success", "message" => "Vinculación eliminada correctamente."])
                : json_encode(["status" => "error",   "message" => "No se pudo eliminar la vinculación."]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida."]);
            break;
    }

} catch (Exception $e) {
    error_log("Error en crud_catalogos-e.php: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error interno del servidor. Por favor, intente nuevamente."]);
}
?>
