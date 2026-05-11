<style>
/* SIDEBAR contenedor */
#sidebar {
  position: fixed;
  top: 41px;  /* Altura aproximada de la navbar */
  left: 0;
  width: 260px;
  height: calc(100% - 56px);
  background-color: #fff; /* #f8f9fa */
  transform: translateX(-100%); /* oculto por defecto */
  transition: transform 0.3s ease;
  z-index: 1100;
  overflow-y: auto;
  box-shadow: 2px 0 4px rgba(0,0,0,0.1);
  border-right: 1px solid #ccc; /* Borde lateral suave */
}

/* Al activarse la clase .active, se muestra el sidebar */
#sidebar.active {
  transform: translateX(0);
}

/* Cabecera del sidebar (con la X para cerrar) */
#sidebar .sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #ccc;
}

#sidebar .close-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
}

/* Listas de menús */
#sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* Quitamos el borde de cada li y agregamos un pequeño margen vertical */
#sidebar ul li {
  border: none;
  margin: 0.2rem 0; /* Separación vertical */
}

/* Pequeña separación a la izquierda (Bootstrap "ms-1" o margen manual) */
#sidebar ul li a {
  display: block;
  color: #333;
  text-decoration: none;
  transition: background 0.2s;
  padding: 0.6rem 1rem;
  margin-left: 4px; /* Margen a la izquierda */
}

#sidebar ul li a:hover {
  background-color: #e2e6ea;
}

/* Íconos del menú padre a la izquierda, con color diferenciado */
.menu-icon {
  margin-right: 0.6rem;
  width: 20px; /* Para alinear si los iconos tienen distintos tamaños */
  text-align: center;
}

/* Colores de iconos para cada menú padre (puedes ajustar) */
.icon-guardia   { color: #e74c3c; }  /* rojo */
.icon-farmacia  { color: #27ae60; }  /* verde */
.icon-ambulancia{ color: #d35400; }
.icon-seguridad { color: #9b59b6; }
.icon-mant      { color: #f1c40f; }
.icon-sis       { color: #2980b9; }
.icon-agendas   { color: #8e44ad; }
.icon-legales   { color: #c0392b; }
.icon-personal  { color: #16a085; }
.icon-patrimonio{ color: #f1c40f; }
.icon-libros    { color: #7f8c8d; }
.icon-estad     { color: #c0392b; }
.icon-compras   { color: #8e44ad; }
.icon-ayuda     { color: #16a085; }
.icon-db        { color: #2980b9; }
.icon-usuarios  { color: #e67e22; }

/* Submenús (menús hijos): mayor margen a la izquierda */
.collapse .list-unstyled li a {
  padding-left: 2.5rem; 
  font-size: 0.95rem;
}

/* Ajusta el separador (hr) para que sea más tenue y fino, con 1px de margen vertical */
hr.my-0 {
  margin: 1px 0;
  border: none;
  border-top: 1px solid #ccc; /* línea tenue */
}

/* Animación al desplegar menús hijos con transición de altura (sin “saltito”) */
.collapse,
.collapsing {
  transition: height 0.2s ease;
}
</style>

<div id="sidebar">
  <div class="sidebar-header">
    <span class="fw-bold">Menú</span>
    <button class="close-btn" id="sidebarClose">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <ul class="list-unstyled">

    <!--       Usuarios       -->
    <?php if ($_SESSION["admin"] == TRUE) { ?>
    <li>
      <a data-bs-toggle="collapse" href="#submenuUsuarios" role="button" aria-expanded="false" aria-controls="submenuUsuarios">
        <i class="fa-solid fa-user-gear menu-icon icon-usuarios"></i> Usuarios
      </a>
      <ul class="collapse list-unstyled" id="submenuUsuarios">
        <?php HTML::li("menu_usuarios", "Menú usuarios"); ?>
        <?php HTML::li("nuevo_usuario", "Crear nuevo usuario"); ?>
        <?php HTML::li("usuarios_activos", "Usuarios activos"); ?>
      </ul>
    </li>
    <?php } ?>
    
    <!--   Notas y Digitalización   -->
    <?php if (@$_SESSION["notas"] == TRUE || @$_SESSION["admin"] == 1) { ?>
    <li>
      <a data-bs-toggle="collapse" href="#submenuAyuda" role="button" aria-expanded="false" aria-controls="submenuAyuda">
        <i class="fa-solid fa-globe menu-icon icon-ayuda"></i> Páginas de ayuda
      </a>
      <ul class="collapse list-unstyled" id="submenuAyuda">
        <?php HTML::li("notas", "Notas a dirección"); ?>
      </ul>
    </li>
    <?php } ?>

    <?php if (@$_SESSION["menu_digitalizar"] == TRUE || @$_SESSION["admin"] == 1) { ?>
    <li>
      <a data-bs-toggle="collapse" href="#submenuDigitalizar" role="button" aria-expanded="false" aria-controls="submenuDigitalizar">
        <i class="fa-solid fa-folder-open menu-icon icon-sis"></i> Digitalización
      </a>
      <ul class="collapse list-unstyled" id="submenuDigitalizar">
        <?php require_once "template/digitalizacion/sidebar_dig.php"; ?>
      </ul>
    </li>
    <?php } ?>
    <!-- <hr class="my-0"> -->
  </ul>
</div>

<script>
// Referencias
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarClose = document.getElementById('sidebarClose');

// Toggle al hacer clic en el botón hamburguesa
sidebarToggle.addEventListener('click', () => {
  sidebar.classList.toggle('active');
});

// Cerrar al presionar la X
sidebarClose.addEventListener('click', () => {
  sidebar.classList.remove('active');
});

// Cerrar si se hace clic fuera del sidebar
document.addEventListener('click', (event) => {
  if (sidebar.classList.contains('active')) {
    const insideSidebar = sidebar.contains(event.target);
    const clickToggle = sidebarToggle.contains(event.target);
    if (!insideSidebar && !clickToggle) {
      sidebar.classList.remove('active');
    }
  }
});
</script>