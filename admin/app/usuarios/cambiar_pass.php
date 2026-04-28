<?php
session_start();
require_once '../../includes/validar_permiso.php'; 
validar_permiso($_SESSION["editar_usuario"]);
error_reporting(E_ALL);

$id = filter_var($_POST["id"], FILTER_VALIDATE_INT);

//Valido que el ID recibido por GET sea un entero. En caso de FALSE retorno al index con un mensaje de ERROR
if(!is_int($id)){
    $_SESSION['error'] = "Algo no anduvo bien por ahí al intentar editar la contraseña del usuario $id.";
    header("Location: " . getenv('HTTP_REFERER')); 
    exit;
}
require_once '../../funciones/funciones.php';
require_once '../../model/conexion.php';

//Verifico que el ID exista en la base de datosEn caso de FALSE retorno al index con un mensaje de RARE
$consulta = $bd->prepare("SELECT * FROM admin WHERE id = ?;");
$consulta->execute([$id]);
$usuario = $consulta->fetch(PDO::FETCH_OBJ);

if($usuario->id == FALSE){
    $_SESSION['error'] = "El usuario no existe.";
    header("Location: " . getenv('HTTP_REFERER')); 
    exit;
}

$id = filter_var($_POST["id"], FILTER_VALIDATE_INT);
$pass = filter_var($_POST["password"], FILTER_SANITIZE_SPECIAL_CHARS); // Assuming you store hashed passwords
$password = password_hash($pass, PASSWORD_BCRYPT, array("cost" => 12));

$sentencia = $bd->prepare("UPDATE admin SET password = ? WHERE id = ?;");
$resultado = $sentencia->execute([$password, $id]);

if ($resultado === TRUE) {

    //unset($_SESSION['ok']);
    $_SESSION['ok'] = "La clave del usuario $usuario->apellido, $usuario->nombre, han sido actualizada correctamente.";
    //echo $_SESSION['ok'];
    //die("Valor de session ok = " . $_SESSION["ok"]);
    //header("Location: ../../index.php?modulo=editar_usuario&id=$usuario->id");
    header("Location: " . getenv('HTTP_REFERER')); 
} else {
    $_SESSION["error"] = "Algo anduvo como el OGT al editar los datos de $apellido, $nombre.";
    //header("Location: ../../index.php?modulo=editar_usuario&id=$usuario->id");
    header("Location: " . getenv('HTTP_REFERER')); 
    exit();
}
