<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Sesión no válida.']);
    exit;
}

require_once __DIR__ . '/../php/ordenes_medicas-b.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['sugerencias']) && $_GET['sugerencias'] === '1') {
            $filtro = isset($_GET['filtro']) ? (string)$_GET['filtro'] : '';
            $term = isset($_GET['term']) ? (string)$_GET['term'] : '';
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 8;
            echo json_encode([
                'status'      => 'success',
                'sugerencias' => ConsultasOrdenesMedicas::obtenerSugerencias($filtro, $term, $limite),
            ]);
            exit;
        }

        if (isset($_GET['catalogos']) && $_GET['catalogos'] === '1') {
            echo json_encode([
                'status'   => 'success',
                'catalogos' => ConsultasOrdenesMedicas::obtenerCatalogosEdicion(),
            ]);
            exit;
        }

        if (isset($_GET['sugerencias_catalogo']) && $_GET['sugerencias_catalogo'] === '1') {
            $tipo = isset($_GET['tipo']) ? (string)$_GET['tipo'] : '';
            $term = isset($_GET['term']) ? (string)$_GET['term'] : '';
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
            echo json_encode([
                'status'      => 'success',
                'sugerencias' => ConsultasOrdenesMedicas::obtenerSugerenciasCatalogo($tipo, $term, $limite),
            ]);
            exit;
        }

        if (isset($_GET['estados']) && $_GET['estados'] === '1') {
            $reglas = ConsultasOrdenesMedicas::obtenerReglasAccionesOrden();
            echo json_encode([
                'status'        => 'success',
                'estados'       => $reglas['estados'],
                'reglas'        => [
                    'cancelar' => $reglas['cancelar'],
                    'eliminar' => $reglas['eliminar'],
                ],
                'configuracion' => ConsultasOrdenesMedicas::verificarConfiguracion(),
            ]);
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $orden = ConsultasOrdenesMedicas::obtenerOrden($id);
            if (!$orden) {
                echo json_encode(['status' => 'error', 'message' => 'Orden no encontrada.']);
                exit;
            }
            echo json_encode([
                'status'         => 'success',
                'orden'          => $orden,
                'modificaciones' => ConsultasOrdenesMedicas::obtenerModificacionesOrden($id),
            ]);
            exit;
        }

        $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : null;
        $valor  = isset($_GET['valor']) ? $_GET['valor'] : null;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
        $listarTodos = !empty($_GET['listar_todos']);

        $resultado = ConsultasOrdenesMedicas::listarOrdenes(
            $filtro,
            $valor,
            $pagina,
            $limite,
            $listarTodos
        );

        echo json_encode([
            'status'  => 'success',
            'ordenes' => $resultado['ordenes'],
            'total'   => $resultado['total'],
            'pagina'  => $pagina,
            'limite'  => $limite,
        ]);
        exit;
    }

    if ($method === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if (!$data) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos.']);
            exit;
        }

        $accion = $data['accion'] ?? '';
        $id_orden = filter_var($data['id_orden'] ?? '', FILTER_VALIDATE_INT);
        if (!$id_orden || $id_orden <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de orden inválido.']);
            exit;
        }

        if ($accion === 'cancelar') {
            ConsultasOrdenesMedicas::cancelarOrden($id_orden);
            echo json_encode(['status' => 'success', 'message' => 'Orden cancelada correctamente.']);
            exit;
        }

        if ($accion === 'eliminar') {
            ConsultasOrdenesMedicas::eliminarOrden($id_orden);
            echo json_encode(['status' => 'success', 'message' => 'Orden eliminada correctamente.']);
            exit;
        }

        if ($accion === 'actualizar') {
            $nombreUsuario = trim(
                trim($_SESSION['apellido'] ?? '') . ', ' . trim($_SESSION['nombre'] ?? '')
            );
            if ($nombreUsuario === ',') {
                $nombreUsuario = trim($_SESSION['user'] ?? '');
            }
            ConsultasOrdenesMedicas::actualizarDetalleOrden(
                $id_orden,
                $data['tipoCirugia'] ?? '',
                $data['id_profesional'] ?? null,
                $data['observaciones'] ?? '',
                $_SESSION['id'] ?? null,
                $nombreUsuario
            );
            echo json_encode(['status' => 'success', 'message' => 'Orden actualizada correctamente.']);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Acción no permitida.']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
