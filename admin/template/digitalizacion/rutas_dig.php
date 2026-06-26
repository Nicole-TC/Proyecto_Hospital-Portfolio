<?php
require_once "model/conexion_dig.php" ;
// Enrutador interno para el grupo digitalizar
// Todas las URLs de este grupo serán de la forma: index.php?modulo=menu_digitalizar&accion=lo_que_sea
$modal = FALSE; // Por defecto no mostramos el botón de ayuda modal en el menú superior

function verificar_permiso_dig($permiso) {
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
        return;
    }
    if (!isset($_SESSION[$permiso]) || $_SESSION[$permiso] != 1) {
        header('Location: index.php?modulo=menu_digitalizar');
        exit;
    }
}

switch (@$_GET["accion"]) {
    case 'etiquetas_digitalizar':
        verificar_permiso_dig('dig_admin_etiquetas');
        $menu = 'Etiquetas Digitalizar';
        require_once "vistas/digitalizacion/etiquetas_digitalizar.php";
        break;
    case 'nueva_etiqueta':
        verificar_permiso_dig('dig_admin_etiquetas');
        $menu = 'Nueva Etiqueta';
        require_once "vistas/digitalizacion/nueva_etiqueta.php";
        break;
    case 'editar_etiqueta':
        verificar_permiso_dig('dig_admin_etiquetas');
        $menu = 'Editar Etiqueta';
        require_once "vistas/digitalizacion/editar_etiqueta.php";
        break;
    case 'nuevo_documento':
        verificar_permiso_dig('dig_nuevo_documento');
        $menu = 'Nuevo Documento';
        $modal = TRUE;
        require_once "vistas/digitalizacion/nuevo_documento.php";
        break;
    case 'tipos_documentos':
        verificar_permiso_dig('dig_tipos_documentos');
        $menu = 'Tipos de Documentos';
        require_once "vistas/digitalizacion/tipos_documentos.php";
        break;
    case 'buscar_documentos':
        verificar_permiso_dig('dig_buscar_documentos');
        $menu = 'Buscar Documentos';
        require_once "vistas/digitalizacion/buscar_documentos.php";
        break;
    case 'auditoria':
        verificar_permiso_dig('dig_auditoria');
        $menu = 'Auditoría';
        require_once "vistas/digitalizacion/auditoria.php";
        break;
    case 'servicios_digitalizar':
        verificar_permiso_dig('dig_servicios');
        $menu = 'Administrar Servicios';
        require_once "vistas/digitalizacion/servicios_digitalizar.php";
        break;
    case 'papelera_digitalizar':
        if (@$_SESSION['admin'] != 1) {
            header('Location: index.php?modulo=menu_digitalizar');
            exit;
        }
        $menu = 'Papelera';
        require_once "vistas/digitalizacion/papelera_digitalizar.php";
        break;

    default:
        // Si no hay acción, o la acción no existe, mostramos el menú principal del grupo
        $menu = 'Menú Digitalizar';
        require_once "vistas/digitalizacion/menu_digitalizar.php";
        break;
}
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let breadcrumb = document.querySelector(".card-navegacion .col-8.text-center a.ml-0 b");
    if (breadcrumb) {
        breadcrumb.textContent = "<?php echo $menu; ?>";
    }
});
</script>