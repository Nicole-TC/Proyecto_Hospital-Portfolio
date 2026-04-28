<?php

function validar_id($id){
    $id_filtrado = filter_var($id, FILTER_VALIDATE_INT);
    if(!is_int($id_filtrado)){
        $_SESSION['error'] = "Algo no anduvo bien por ahí al intentar editar el reclamo $id_filtrado.";
        header('Location: index.php');
    }
}

function nombre_usuario($idUsuario)
{
    include '../model/conexion.php';
    $consulta = $bd->prepare("SELECT * FROM admin WHERE id = ?");
    $consulta->execute([$idUsuario]);
    return $consulta->fetch(PDO::FETCH_OBJ);
}

// muestra un número cpm decimales separado por coma, ademas usa el separador de puntos cada 3 dígitos
// Por ejemplo le paso 1234.55 (formato doube de myssql) y devuelve 1.234,55 formato en español
function numero($numero)
{
    $formatear = number_format($numero, 2, ",", ".");
    if (substr($formatear, -3) == ",00") {
        $formatear = explode(",", $formatear);
        echo $formatear[0];
    } else {
        echo $formatear;
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



//Devuleve el tiempo que toma hacer la consulta SQL
// Como se usa? Escribir esta linea antes y despues del Query timequery(); 

function timequery(){
    static $querytime_begin;
    list($usec, $sec) = explode(' ',microtime());
    if(!isset($querytime_begin)){   
          $querytime_begin= ((float)$usec + (float)$sec);
    }else{
        $querytime = (((float)$usec + (float)$sec)) - $querytime_begin;
        echo sprintf('La consulta a base de datos tardó %01.5f segundos', $querytime);
    }
}

function diferencia_dias($fecha) {
    // Obtener la fecha actual sin incluir la hora, minutos y segundos
    $hoy = date("Y-m-d");

    // Convertir las fechas a objetos DateTime
    $fecha_obj = new DateTime($fecha);
    $hoy_obj = new DateTime($hoy);

    // Calcular la diferencia en días
    $intervalo = $fecha_obj->diff($hoy_obj);
    $diferencia_dias = $intervalo->format('%a');

    return $diferencia_dias;
}

function calcular_semanas($fecha) {
    // Obtener la fecha actual
    $hoy = date("Y-m-d");

    // Crear objetos DateTime
    $fecha_obj = new DateTime($fecha);
    $hoy_obj = new DateTime($hoy);

    // Calcular la diferencia
    $intervalo = $fecha_obj->diff($hoy_obj);
    $total_dias = $intervalo->days;

    // Calcular semanas y días restantes
    $semanas = floor($total_dias / 7);
    $dias_restantes = $total_dias % 7;

    // Generar el texto de retorno según el caso
    if ($semanas > 0 && $dias_restantes > 0) {
        return "$semanas semana" . ($semanas != 1 ? "s" : "") . " y $dias_restantes día" . ($dias_restantes != 1 ? "s" : "");
    } elseif ($semanas > 0 && $dias_restantes == 0) {
        return "$semanas semana" . ($semanas != 1 ? "s" : "");
    } else {
        return "$dias_restantes día" . ($dias_restantes != 1 ? "s" : "");
    }
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

function ir_a($url) {
    echo '<script type="text/javascript">
            window.location.href = "' . $url . '";
          </script>';
    exit();
}
