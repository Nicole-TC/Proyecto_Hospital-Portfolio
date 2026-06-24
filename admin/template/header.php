<!doctype html>
<html lang="es">

<head>
  <title>HZGA Malvinas</title>
  <link rel="icon" type="image/x-icon" href="auxiliar/logo1.png">
  <meta charset="utf-8">
  
  <?php
  if(@$_GET["modulo"]=="censo" || @$_GET["modulo"]=="guardia"){ ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <?php } else { ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <?php } ?>

  <!-- Dark mode-->
  <link rel="stylesheet" href="css/darkmode.css">

  <!-- Bootstrap CSS v5 -->
  <link rel="stylesheet" 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" 
        crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.0/font/bootstrap-icons.css">

  <!-- FontAwesome más íconos de menús) -->
  <link rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
        integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" 
        crossorigin="anonymous" 
        referrerpolicy="no-referrer" />

  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

  <!-- jQuery (opcional) -->
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>

  <!-- Colores, loader, etc. -->
  <link rel="stylesheet" type="text/css" href="css/colors.min.css">
  <link rel="stylesheet" type="text/css" href="css/loader.css">

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Bootstrap overrides -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

  <?php if (@$_GET['modulo'] === 'menu_circuitoquirurgico'): ?>
  <link rel="stylesheet" href="css/circuitoquirurgico/cq-componentes.css">
  <?php endif; ?>

  <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">

  <!-- JS adicional -->
  <script src="js/all.min.js" crossorigin="anonymous"></script>

  <!-- Bootstrap JS (para dropdown, etc.) -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-bgbN4IrZ3X3p1kJTP07CUzpsEGXq4aZcGmP3gSdEpIZT0Fhv3sNEcD3+u/ct2ecF"
          crossorigin="anonymous"></script> -->
</head>

<body class="<?= $modo_oscuro ? 'modo-oscuro' : '' ?>">

