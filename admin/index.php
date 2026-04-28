<?php
session_start();
//error_reporting(E_ALL);
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once 'includes/auth_validate.php';
require_once 'model/conexion.php';
require_once 'template/header.php';
require_once 'includes/validar_permiso.php';
require_once 'includes/funciones.php';
require_once 'template/html.php';

$inicio_opcion1 = microtime(true);
?>

<!-- Loader parte 1/2 -->
  <div id="contenedor_carga">
    <div id="carga"></div>
  </div>


<!-- Barra superior -->
<?php require_once 'template/topnav.php'; ?>

<!-- Sidebar con animación, íconos y submenús -->
<?php require_once 'template/sidebar.php'; ?>

<!-- Contenido principal -->
<div class="container-fluid mt-3">
  <div class="row justify-content-center">
    <?php
    // Mensajes flash
    require_once 'template/mensajes_flash.php';
    // Rutas
    require_once 'template/rutas.php';
    // Debug, IP, etc.
    require_once 'template/debug.php';
    require_once 'funciones/ip.php';
    require_once 'funciones/mobile_detect.php';
    ?>
  </div>
</div>

<!-- Loader parte 2/2 -->

<script>
window.onload = function() {
  var contenedor = document.getElementById('contenedor_carga');
  if (contenedor) {
    contenedor.style.visibility = 'hidden';
    contenedor.style.opacity = '0';
  }
}
</script>


<?php
$fin_opcion1 = microtime(true);
$tiempo_opcion1 = $fin_opcion1 - $inicio_opcion1;
?>

<!-- Cierre de body/html -->
<?php require_once 'template/footer.php'; ?>
