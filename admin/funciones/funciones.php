<?php
function validar_id($id){
    $id_filtrado = filter_var($id, FILTER_VALIDATE_INT);
    if(!is_int($id_filtrado)){
        $_SESSION['error'] = "Algo no anduvo bien por ahí al intentar editar el reclamo $id_filtrado.";
        header('Location: index.php');
    }
}
//Devuelve sólo números
function validar_numero($numero){
    $reemplazar = array(".", ",", "+", "-","e");
    return str_replace($reemplazar, "", $numero);
}

function boton_switch($swicth){
    //$swith = filter_var($swicth, FILTER_SANITIZE_SPECIAL_CHARS);
    if ($swicth === "on") {
        return $swicth = 1;
    } else {
        return $swicth = 0;
    }
}

function nombre_usuario($idUsuario)
{
    include '../model/conexion.php';
    $consulta = $bd->prepare("SELECT * FROM admin WHERE id = ?");
    $consulta->execute([$idUsuario]);
    return $consulta->fetch(PDO::FETCH_OBJ);
}

function diferencia_dias($fecha) {
    $hoy = date("Y-m-d");

    // Convertir las fechas a objetos DateTime
    $fecha_obj = new DateTime($fecha);
    $hoy_obj = new DateTime($hoy);

    // Calcular la diferencia en días
    $intervalo = $fecha_obj->diff($hoy_obj);
    $diferencia_dias = $intervalo->format('%a');

    return $diferencia_dias;
}

function calcularedad($fecha_nacimiento) {
    // Obtener la fecha actual
    $hoy = new DateTime();

    // Convertir la fecha de nacimiento a un objeto DateTime
    $fecha_nacimiento_obj = new DateTime($fecha_nacimiento);

    // Calcular la diferencia entre la fecha de nacimiento y la fecha actual
    $diferencia = $fecha_nacimiento_obj->diff($hoy);

    // Obtener el componente de años de la diferencia
    $edad = $diferencia->y;

    return $edad;
}

?>
