<?php
// Enrutador interno para el grupo Circuito Quirúrgico
// Todas las URLs de este grupo serán de la forma: index.php?modulo=menu_circuitoquirurgico&accion=lo_que_sea

$modal = FALSE; // Por defecto no mostramos el botón de ayuda modal en el menú superior

switch (@$_GET["accion"]) {
    case 'gestion_usuarios':
        $menu = 'Gestión de Usuarios (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/gestion_usuarios.php"; // Archivo físico a cargar
        break;
    case 'dashboard':
        $menu = 'Dashboard de Gestión';
        $require = "vistas/circuitoquirurgico/dashboard.php";
        break;

    case 'admision':
        $menu = 'Admision (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/admision.php"; // Archivo físico a cargar
        break;

    case 'agenda_quirurgica':
        $menu = 'Agenda Quirúrgica (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/agenda_quirurgica.php"; // Archivo físico a cargar
        break;
    case 'lista_espera':
        $menu = 'Lista de Espera Quirúrgica';
        $require = "vistas/circuitoquirurgico/lista_de_espera_quirurgica.php";
        break;
    // Aquí se pueden agregar más 'cases' libremente sin tocar admin/template/rutas.php
    /*
    case 'programar_cirugia':
        $menu = 'Programar Cirugía';
        $require = "vistas/circuitoquirurgico/programar.php";
        break;
    */

    default:
        // Si no hay acción, o la acción no existe, mostramos el menú principal del grupo
        $menu = 'Menú Circuito Quirúrgico';
        $require = "vistas/circuitoquirurgico/menu_circuitoquirurgico.php";
        break;
}
?>
