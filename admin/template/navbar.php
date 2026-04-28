<div class="container-fluid bg-info shadow-lg no-imprimir">
  <div class="row">
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">

      <div class="container-fluid">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

          <ul class="navbar-nav me-auto mb-lg-0">

            <?php if ($_SESSION["notas"] == TRUE) { ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Sistemas
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <?php HTML::li("notas", "Notas"); ?>
                </ul>
              </li>
            <?php } ?>

            <?php if ($_SESSION["paginas_ayuda"] == TRUE) { ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Páginas de ayuda
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <li><a class="dropdown-item" href="https://sites.google.com/view/panel-dph/home" target="_blank">Panel DPH</a></li>
                  <li><a class="dropdown-item" href="https://sistemas.ms.gba.gov.ar/intranet/intranet.php" target="_blank">Intranet</a></li>
                  <li><a class="dropdown-item" href="https://gdeba.gba.gob.ar/" target="_blank">GDEBA</a></li>
                  <li><a class="dropdown-item" href="https://shc.ms.gba.gov.ar/auth/login" target="_blank">HSI Historia de salud integrada</a></li>
                  <li><a class="dropdown-item" href="https://portal.rrhh.gba.gob.ar/portal/login" target="_blank">Portal del empleado</a></li>
                  <li><a class="dropdown-item" href="https://sso.siape.gba.gov.ar/loginsso/login.jsp?site2pstoretoken=v1.4~5312A8AE~95575D5DF626EAACF82420F5D585CC6E80B8C88B92E7C8B123F5CF245E303B41D404E94B063AA10F89907B7091F9EC202FC5279A676DB343861289278BE8688679381560092CF0A56968C3020C90C9C2EE402FA88348FB355394C99FDB982809FCE67A345A8A2A8FA7E1E0454B23721E2D539B8C852199246D87512AEB8C1A4A76B42B787CECA3D7E276E8B406511B31D2003EA8709AAF552A6FCA0A01240E07F84CF6A505B0FF2C7214AF31533EC1E4FC34FBD32B0A8CD43AEA48F9206EAF6C&p_error_code=&p_submit_url=https%3A%2F%2Fsso.siape.gba.gov.ar%2Fsso%2Fauth&p_cancel_url=http%3A%2F%2Fapp.siape.gba.gov.ar&ssousername=" target="_blank">SIAPE Sistema de administración de personal</a></li>
                  <li><a class="dropdown-item" href="https://pbac.cgp.gba.gov.ar/Default.aspx" target="_blank">PBAC Provincia Buenos Aires Compras</a></li>
                  <li><a class="dropdown-item" href="https://catalogo.cgp.gba.gov.ar/" target="_blank">CGP Catálogo PBA</a></li>
                </ul>
              </li>
            <?php } ?>

            <?php if ($_SESSION["menu_usuarios"] == TRUE) { ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Usuarios
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <?php HTML::li("menu_usuarios", "Menú usuarios"); ?>
                  <?php HTML::li("nuevo_usuario", "Crear nuevo usuario"); ?>
                  <?php HTML::li("usuarios_activos", "Usuarios activos"); ?>
                </ul>
              </li>
            <?php } ?>



          </ul>
          <ul class="navbar-nav">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle id=" userDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="auxiliar/sinfotoperfil.png" alt="user" width="20" height="20" class="rounded-circle">
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#"><?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?></a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <?php HTML::li("miperfil", "Mi perfil"); ?>
                <li><a class="dropdown-item" href="logout.php">Cerrar sesion</a></li>
              </ul>
            </li>
          </ul>

        </div>
      </div>
    </nav>
  </div>
</div>