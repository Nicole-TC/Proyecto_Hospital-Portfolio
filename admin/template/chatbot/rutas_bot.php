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

<script>
(function() {
    function configurarBotonVolver() {
        let backArrow = document.querySelector('.card-navegacion .bi-arrow-left-circle');
        if (!backArrow) return;
        let backLink = backArrow.closest('a');
        if (!backLink) return;

        // Quitamos el href lento del servidor
        backLink.href = 'javascript:void(0);';

        backLink.onclick = function(e) {
            e.preventDefault();
            // Si el usuario viene navegando dentro del hospital, vuelve a la pantalla previa al instante
            if (window.history.length > 1 && document.referrer && document.referrer.includes(window.location.host)) {
                window.history.back();
            } else {
                // Si entró directo o no hay historial previo en la sesión:
                let params = new URLSearchParams(window.location.search);
                if (params.get('accion')) {
                    window.location.href = 'index.php?modulo=menu_bot';
                } else {
                    window.location.href = 'index.php';
                }
            }
        };
    }

    // Se ejecuta de inmediato sin esperar a que cargue toda la página pesada
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', configurarBotonVolver);
    } else {
        configurarBotonVolver();
    }
})();
</script>
