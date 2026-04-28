<?php
session_start();
require_once '../../includes/validar_permiso.php'; 
validar_permiso($_SESSION["formularios"]);
error_reporting(E_ALL);

require_once '../../funciones/ip.php'; // Devuelve la IP 
require_once '../../funciones/mobile_detect.php'; // Devuelve Sistema operativo y el dispositivo 
include_once '../../model/conexion.php';

// Inicializar las variables de sesión para mensajes
/* $_SESSION['ok'] = isset($_SESSION['ok']) ? $_SESSION['ok'] : '';
$_SESSION['error'] = isset($_SESSION['error']) ? $_SESSION['error'] : ''; */

// Recibo todas las variables
$id_formulario = filter_var($_POST["id_formulario"], FILTER_VALIDATE_INT);

try {
    // Obtener la ruta del archivo a eliminar
    $sentencia_ruta = $bd->prepare("SELECT ruta FROM documentos_formularios WHERE id = ?;");
    $sentencia_ruta->execute([$id_formulario]);
    $ruta_archivo = $sentencia_ruta->fetchColumn();

    // Eliminar el archivo físico
    if ($ruta_archivo) {
        if (unlink("../../$ruta_archivo")) {
           $_SESSION['ok'] .= "Se ha eliminado el archivo $ruta_archivo correctamente.";
        } else {
           $_SESSION['error'] .= "Hubo un error al intentar eliminar el archivo $ruta_archivo.";
           throw new Exception("Error al eliminar el archivo físico.");
        }
    }

    // Iniciar la transacción
    $bd->beginTransaction();

    // Eliminar datos de internados_movimientos
    $sentencia = $bd->prepare("DELETE FROM documentos_formularios WHERE id = ?;");
    $resultado = $sentencia->execute([$id_formulario]);

    if ($resultado !== TRUE) {
        throw new Exception("Error al eliminar el formulario.");
    }

    // Confirmar la transacción
    $bd->commit();
    $_SESSION['ok'] .= "El formulario se ha eliminado correctamente.";
    header('Location: ' . getenv('HTTP_REFERER'));
    exit();

} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $bd->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header('Location: ' . getenv('HTTP_REFERER'));
    exit();
}
