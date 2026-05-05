<?php
// Enrutador interno para el grupo digitalizar
// Todas las URLs de este grupo serán de la forma: index.php?modulo=menu_digitalizar&accion=lo_que_sea
$modal = FALSE; // Por defecto no mostramos el botón de ayuda modal en el menú superior
switch (@$_GET["accion"]) {
    case 'etiquetas_digitalizar':
        $menu = 'Etiquetas Digitalizar';
        require_once "vistas/digitalizacion/etiquetas_digitalizar.php";
        break;
    default:
        // Si no hay acción, o la acción no existe, mostramos el menú principal del grupo
        $menu = 'Menú digitalizacion';
        require_once  "vistas/digitalizacion/menu_digitalizar.php";
        break;
}
?> 