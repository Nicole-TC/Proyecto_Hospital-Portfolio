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
        if (isset($_GET['estados']) && $_GET['estados'] === '1') {
            $reglas = ConsultasOrdenesMedicas::obtenerReglasAccionesOrden();
            echo json_encode([
                'status'  => 'success',
                'estados' => $reglas['estados'],
                'reglas'  => [
                    'cancelar' => $reglas['cancelar'],
                    'eliminar' => $reglas['eliminar'],
                ],
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
            echo json_encode(['status' => 'success', 'orden' => $orden]);
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

        echo json_encode(['status' => 'error', 'message' => 'Acción no permitida.']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
