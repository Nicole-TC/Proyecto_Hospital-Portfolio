<?php
session_start();

$id = filter_var($_POST["id"], FILTER_VALIDATE_INT);

//Valido que el ID recibido por GET sea un entero. En caso de FALSE retorno al index con un mensaje de ERROR
if(!is_int($id)){
    $_SESSION['error'] = "Algo no anduvo bien por ahí al intentar editar el usuario $id.";
    header('Location: ../../index.php=modulo=usuarios_activos');
    exit;
}
require_once '../../funciones/funciones.php';
require_once '../../model/conexion.php';

//Verifico que el ID exista en la base de datosEn caso de FALSE retorno al index con un mensaje de RARE
$consulta = $bd->prepare("SELECT * from admin WHERE id = ?;");
$consulta->execute([$id]);
$usuario = $consulta->fetch(PDO::FETCH_OBJ);

if($usuario->id == FALSE){
    $_SESSION['error'] = "El usuario no existe.";
    header('Location: ../../index.php');
    exit;
}






$email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
$telefono = filter_var($_POST["telefono"], FILTER_VALIDATE_INT);

$password_recibido = $_POST["password"]; //, FILTER_SANITIZE_SPECIAL_CHARS);
$password = password_hash($password_recibido, PASSWORD_BCRYPT, array("cost" => 12));

$new_password_pre = $_POST["new_password"];
$new_password = password_hash($new_password_pre, PASSWORD_BCRYPT, array("cost" => 12));

//$passwordAttempt = !empty($_POST['password']) ? trim($_POST['password']) : null;
$validPassword = password_verify($password_recibido, $usuario->password);
//If $validPassword is TRUE, the login has been successful.
if ($validPassword) {
    $_SESSION['ok'] = "La clave ingresada es correcta.";
    $_SESSION['ok'] .= "<br>Clave actual ingresada en el formulario POST: $password_recibido";
    $_SESSION['ok'] .= "<br>Compara estas claves";
    $_SESSION['ok'] .= "<br>Grabada: $usuario->password";
    $_SESSION['ok'] .= "<br>Ingresada: $password";
    $_SESSION['ok'] .= "<br>Nueva: $new_password_pre";
    $_SESSION['ok'] .= "<br>Nueva encriptada: $new_password";
    header('Location: ../../index.php?modulo=miperfil');
    exit;
}else{
    $_SESSION['error'] = "La clave ingresada es incorrecta.";
    header('Location: ../../index.php?modulo=miperfil');
    exit;
}



// X datos
//menu_usuarios,nuevo_usuario,editar_usuario,usuarios_activos,menu_ambulancias,nuevo_ingreso,editar_ingreso,ingresos,menu_agendas,auditorio,editar_reclamo,telemedicina,reclamos_archivados,acciones_pendientes,correos_enviados,fichas,menu_legales,cargar_archivo_licencias_impresas,buscar_licencias,paginas_ayuda,

$sentencia = $bd->prepare("UPDATE admin SET dni = ?, nombre = ?, apellido = ?, username = ?, email = ?, telefono = ?, activo = ?, admin = ?, debug = ?, menu_usuarios = ?, nuevo_usuario = ?, editar_usuario = ?, usuarios_activos = ?, menu_ambulancias = ?, nuevo_ingreso = ?, editar_ingreso = ?, eliminar_ingreso = ?, dar_presente = ?, buscar_ingresos = ?, ingresos = ?, menu_agendas = ?, auditorio = ?, editar_reclamo = ?, telemedicina = ?, reclamos_archivados = ?, acciones_pendientes = ?, correos_enviados = ?, fichas = ?, menu_legales = ?, cargar_archivo_licencias_impresas = ?, buscar_licencias = ?, paginas_ayuda = ?, cenat = ?  WHERE id = ?;");
$resultado = $sentencia->execute([$dni, $nombre, $apellido, $username, $email, $telefono, $activo, $admin, $debug, $menu_usuarios, $nuevo_usuario, $editar_usuario, $usuarios_activos, $menu_ambulancias, $nuevo_ingreso, $editar_ingreso, $eliminar_ingreso, $dar_presente, $buscar_ingresos,$ingresos, $menu_agendas, $auditorio, $editar_reclamo, $telemedicina, $reclamos_archivados, $acciones_pendientes, $correos_enviados, $fichas, $menu_legales, $cargar_archivo_licencias_impresas, $buscar_licencias, $paginas_ayuda, $cenat, $id]);

if ($resultado === TRUE) {
    $_SESSION['ok'] = "Los datos han sido actualizados correctamente.";
    //echo $_SESSION['ok'];
    //die("Valor de session ok = " . $_SESSION["ok"]);
    header('Location: ../../index.php?modulo=miperfil');
} else {
    $_SESSION["error"] = "Algo anduvo como el OGT al editar el perfil de $apellido, $nombre.";
    header('Location: ../../index.php?modulo=miperfil');
    exit();
}
?>