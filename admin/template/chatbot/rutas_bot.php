<?php
// Enrutador interno para el grupo Chatbot / Bot
// Todas las URLs de este grupo serán de la forma: index.php?modulo=menu_bot&accion=lo_que_sea

$modal = FALSE; // Por defecto no mostramos el botón de ayuda modal en el menú superior

switch (@$_GET["accion"]) {
    case 'dashboard':
        $menu = 'Dashboard del Bot';
        $require = "vistas/chatbot/dashboard.php";
        break;

    case 'configuracion':
        $menu = 'Configuración del Bot';
        $require = "vistas/chatbot/configuracion.php";
        break;
        
    case 'historial':
        $menu = 'Historial de Conversaciones';
        $require = "vistas/chatbot/historial.php";
        break;

    case 'auditoria':
        $menu = 'Auditoría del Bot';
        $require = "vistas/chatbot/auditoria.php";
        break;

    default:
        // Si no hay acción, o la acción no existe, mostramos el menú principal del bot
        $menu = 'Menú del Bot';
        $require = "vistas/chatbot/menu_bot.php";
        break;
}
?>
