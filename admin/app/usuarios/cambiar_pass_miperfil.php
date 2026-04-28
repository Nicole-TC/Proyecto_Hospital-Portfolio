<?php
session_start();
require_once '../../includes/validar_permiso.php';
validar_permiso($_SESSION["miperfil"]);
error_reporting(E_ALL);

$id = filter_var($_SESSION["id"], FILTER_VALIDATE_INT);

if ($id === false) {
    $_SESSION['error'] = "Algo no anduvo bien por ahí al intentar editar la contraseña del usuario.";
    header("Location: /ruta/a/donde/quieres/redirigir");
    exit;
}

require_once '../../funciones/funciones.php';
require_once '../../model/conexion.php';

$q_user = $bd->prepare("SELECT * FROM admin WHERE id = ?;");
$q_user->execute([$id]);
$user = $q_user->fetch(PDO::FETCH_OBJ);

if ($user === false) {
    $_SESSION['error'] = "El usuario no existe.";
    header("Location: " . getenv('HTTP_REFERER'));
    exit;
}

if (!isset($_POST["password"], $_POST["password_nueva"])) {
    $_SESSION['error'] = "Faltan datos necesarios para cambiar la contraseña.";
    header("Location: " . getenv('HTTP_REFERER'));
    exit;
}

$password_actual = filter_var($_POST["password"], FILTER_SANITIZE_SPECIAL_CHARS);
$password_nueva = filter_var($_POST["password_nueva"], FILTER_SANITIZE_SPECIAL_CHARS);

if (password_verify($password_actual, $user->password)) {
    $hashed_password = password_hash($password_nueva, PASSWORD_BCRYPT, array("cost" => 12));
    $sentencia = $bd->prepare("UPDATE admin SET password = ? WHERE id = ?;");
    $resultado = $sentencia->execute([$hashed_password, $id]);

    if ($resultado === TRUE) {
        $_SESSION['ok'] = "La contraseña ha sido actualizada correctamente.";
        header("Location: " . getenv('HTTP_REFERER'));
    } else {
        $_SESSION["error"] = "Algo anduvo mal al intentar actualizar la contraseña.";
        header("Location: " . getenv('HTTP_REFERER'));
        exit();
    }
} else {
    $_SESSION["error"] = "La contraseña ingresada no es correcta.";
    header("Location: " . getenv('HTTP_REFERER'));
    exit();
}
?>
