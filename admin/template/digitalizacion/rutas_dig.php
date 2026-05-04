<?php
// ENRUTADOR PARA TODO EL MODULO DE DIGITALIZACION


switch (@$_GET["modulo"]) { 


    case 'menu_digitalizar':
        $menu = 'Menú Digitalización';
        $modal = FALSE;
        $require = "vistas/digitalizacion/menu_digitalizar.php";
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