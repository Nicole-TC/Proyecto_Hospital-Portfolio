<?php
/*
if(!isset($_GET["modulo"])){
    $menu = "Menú principal";
    $modal = FALSE;

}
*/

switch (@$_GET["modulo"]) { 
//DEFAULT
    case NULL;
        $menu = "Menú principal";
        $modal = FALSE;
        $require = "vistas/menu_general.php";
        break;

// EN CONSTRUCCION
    case 'en_construccion':
        $menu = 'Proximamente';
        $modal = FALSE;
        $require = "vistas/en_construccion.php";
        break;
    
// MI PERFIL   
    case 'miperfil':
        $menu = 'Mi perfil';
        $modal = FALSE;
        $require = "vistas/miperfil.php";
        break;

// USUARIOS
    case 'menu_usuarios':
        $menu = 'Menú de usuarios';
        $modal = FALSE;
        $require = "vistas/menu_usuarios.php";
        break;
    case 'nuevo_usuario':
        $menu = 'Cargar nuevo usuario';
        $modal = FALSE;
        $require = "vistas/nuevo_usuario.php";
        break;
    case 'editar_usuario':
        $menu = 'Editar usuario';
        $require = "vistas/editar_usuario.php";
        $modal = FALSE;
        break;
    case 'usuarios_activos':
        $menu = 'Usuarios activos';
        $require = "vistas/usuarios_activos.php";
        $modal = FALSE;
        break;

// DOCUMENTOS
    /* case 'formularios':
        $menu = 'Formularios';
        $modal = TRUE;
        $require = "vistas/formularios.php";
        break; */
    case 'notas':
        $menu = 'Notas';
        $modal = TRUE;
        $require = "vistas/notas.php";
        break;
    
    // CIRCUITO QUIRURGICO
    case 'menu_circuitoquirurgico':
        // Delegamos las rutas a un archivo propio del grupo
        require_once "template/circuitoquirurgico/rutas_cq.php";
        

// DIGITALIZAR
    case 'menu_digitalizar':
        //delegamos las rutas a un archivo propio del modulo digtalizar
        $require = "template/digitalizacion/rutas_dig.php";
        break;
}
?>

<div class="grey-bg container-fluid">
    <div class="card-navegacion border p-1 border-opacity-10 mg-1 mb-1 fs-6 shadow" role="alert">
        <div class="row">
            <div class="col-2">
                <a class="float-left ml-0" href="<?php echo basename("/"); ?>/admin/index.php"><i class="bi bi-house"></i></a>
            </div>
            <div class="col-8 text-center">
                <a class="ml-0"><b><?php echo $menu; ?></b></a>
                <?php 
                if($modal == TRUE){
                ?>
                <a class="ml-1" data-bs-toggle="modal" data-bs-target="#ayudamenu"><i class="fa-solid fa-circle-info info"></i></i></a>
                <?php 
                }
                ?>
            </div>
            <div class="col-2">
                <a class="float-end mr-0" href="<?php echo getenv('HTTP_REFERER'); ?>"><i class="bi bi-arrow-left-circle"></i></a>
            </div>
        </div>
    </div>
</div>

<?php
// archivo requerido en los cases de arriba
require_once $require;

?>