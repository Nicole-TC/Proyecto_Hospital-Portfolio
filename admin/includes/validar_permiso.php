<?php
function validar_permiso($permiso){
    @session_start(); // Asegurar que la sesión esté iniciada

    if (!isset($permiso) || $permiso != "1"){
        $_SESSION["error"] = "No tienes permisos para realizar esta acción.";
        header('Location:' . getenv('HTTP_REFERER'));
        exit;
    }
}
