<?php
// template/topnav.php
?>

<style>
.navbar-custom {
  background-color:#fff;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 999; 
  /* Para reducir la altura, disminuimos el padding vertical */
  padding-top: 0.1rem;
  padding-bottom: 0.1rem;
  /* Quitamos márgenes abajo */
  margin-bottom: 0 !important;
}

/* Ajustes de color */
.navbar-custom .navbar-brand,
.navbar-custom .nav-link,
.navbar-custom .navbar-toggler-icon {
  color: #000;
}

/* Ajustamos un poquito el botón hamburguesa y el toggler */
.navbar-toggler {
  border-color: rgba(0,0,0,0.3);
  padding: 0.3rem;
}
.navbar-toggler-icon {
  filter: invert(0.3);
}

/* Perfil (dropdown) */
.nav-profile .dropdown-toggle::after {
  display: none;
}
</style>

<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
  <!-- container-fluid: si quieres aún menos espacio, podrías usar .container o quitar el container -->
  <div class="container-fluid" style="margin: 0; padding: 0;">

    <!-- Botón hamburguesa pegado a la izquierda -->
    <button class="btn btn-link" id="sidebarToggle" style="text-decoration: none; margin-left: 0;">
      <i class="fa-solid fa-bars fs-4"></i>
    </button>

    <!-- Marca o título (reduce padding/margin si quieres aún menos alto) -->
    <a class="navbar-brand fw-bold ms-0" href="index.php" style="margin: 0; padding: 0;">
      HZGA Malvinas
    </a>

    <!-- Botón toggler de Bootstrap (para responsive) -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnavContent"
            aria-controls="topnavContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menú de perfil a la derecha -->
    <div class="collapse navbar-collapse" id="topnavContent">
      <ul class="navbar-nav ms-auto mb-0 mb-lg-0">
        <li class="nav-item dropdown nav-profile">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
            id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="auxiliar/sinfotoperfil.png" alt="user" width="25" height="25" class="rounded-circle me-2">
            <span class="fw-bold"><?php echo $_SESSION['nombre']." ".$_SESSION['apellido']; ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li>
              <a class="dropdown-item" href="index.php?modulo=miperfil">
                <i class="fas fa-user me-2"></i> Mi perfil
              </a>
            </li>

            <li>
              <div class="dropdown-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-moon me-2"></i> Modo oscuro</span>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" id="switchModoOscuro">
                </div>
              </div>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
              <a class="dropdown-item" href="logout.php">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar sesión
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>


  </div>
</nav>
