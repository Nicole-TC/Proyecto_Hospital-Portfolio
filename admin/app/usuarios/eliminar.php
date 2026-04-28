<?php 
session_start();
require_once '../../includes/validar_permiso.php'; 
validar_permiso($_SESSION["admin"]);
error_reporting(E_ALL);

$id = filter_var($_GET["id"], FILTER_VALIDATE_INT);
if(!$id){
    $_SESSION['error'] = "Algo no anduvo bien al intentar eliminar el usuario.";
    header('Location: ../../index.php?modulo=usuarios_activos');
    exit;
}

include '../../model/conexion.php';

//Valido que el ID recibido por GET sea un entero. En caso de FALSE retorno al index con un mensaje de error
$sentencia = $bd->prepare("SELECT * from admin WHERE id = ?;");
$sentencia->execute([$id]);
$usuario = $sentencia->fetch(PDO::FETCH_OBJ);

if($usuario->id == FALSE){
    $_SESSION['error'] = "El usuario no existe.";
    header('Location: ../../index.php?modulo=usuarios_activos');
    exit;
}


$sentencia = $bd->prepare("DELETE FROM admin WHERE id = ?;");
$resultado = $sentencia->execute([$id]);

if ($resultado === TRUE) {
    $_SESSION['ok'] .= "El usuario de $usuario->apellido, $usuario->nombre ha sido eliminado correctamente.";
    header('Location: ../../index.php?modulo=usuarios_activos');
} else {
    $_SESSION['ok'] .= "Algo anduvo como el orto al intentar borrar el usuario de $usuario->apellido, $usuario->nombre.";
    header('Location: ../../index.php?modulo=usuarios_activos');
}
