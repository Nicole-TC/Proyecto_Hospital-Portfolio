<!--
<style>
    /* Tarjetas principales */
    .modern-card {
        border: none;
        border-radius: 0.75rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: #fff; /* color de fondo de la tarjeta */
    }
    .modern-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* Contenido de la tarjeta */
    .modern-card-body {
        padding: 1rem;
    }

    /* Ícono principal de la tarjeta */
    .modern-card-icon {
        font-size: 2rem; /* Ajusta el tamaño a tu gusto */
        transition: color 0.3s;
    }
    /* Cambia el color del ícono al hover de la tarjeta */
    .modern-card:hover .modern-card-icon {
        color: #ff8a00 !important; /* color de acento */
    }

    /* Título y descripción */
    .modern-card-title {
        font-weight: 700;
        font-size: 1.125rem;
    }
    .modern-card-description {
        font-style: italic;
        font-size: 0.95rem;
        color: #555;
    }

    /* Para la versión móvil pequeña (tarjeta compacta) */
    .modern-card-mobile {
        border: none;
        border-radius: 0.75rem;
        background-color: #fff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .modern-card-mobile:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    .modern-card-mobile-body {
        padding: 0.5rem;
    }
    .modern-card-mobile-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        transition: color 0.3s;
    }
    .modern-card-mobile:hover .modern-card-mobile-icon {
        color: #ff8a00 !important;
    }
    .modern-card-mobile-title {
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
    }
</style>
-->
<?php

class HTML
{
    /* public static function tarjeta($session, $titulo, $descripcion, $icono, $color, $link)
    {
        if (@$_SESSION[$session] == true) {
            // Tarjeta para pantallas grandes
            $html = '<div class="d-none d-lg-block col-xl-3 col-lg-4 col-sm-6 col-12 mb-1">';
            $html .= '  <div class="card modern-card shadow-sm">'; // clases personalizadas
            $html .= '      <div class="card-content">';
            $html .= '          <a class="dropdown-item" href="' . $link . '">';
            $html .= '              <div class="card-body modern-card-body">'; // clase personalizada para contenido
            $html .= '                  <div class="media d-flex">';
            $html .= '                      <div class="align-self-center">';
            $html .= '                          <i class="' . $icono . ' ' . $color . ' modern-card-icon float-left"></i>'; 
            // ^ Agregamos la clase 'modern-card-icon' para aplicarle estilos
            $html .= '                      </div>';
            $html .= '                      <div class="media-body text-end ms-2">'; 
            $html .= '                          <h5 class="modern-card-title">' . $titulo . '</h5>'; 
            $html .= '                          <span class="modern-card-description">' . $descripcion . '</span>';
            $html .= '                      </div>';
            $html .= '                  </div>';
            $html .= '              </div>';
            $html .= '          </a>';
            $html .= '      </div>';
            $html .= '  </div>';
            $html .= '</div>';

            // Tarjeta para pantallas pequeñas
            $html .= '<div class="d-block d-lg-none col-4 p-0 mb-1">';
            $html .= '  <div class="card modern-card-mobile shadow-sm" style="min-height: 100%;">';
            $html .= '      <div class="card-content">';
            $html .= '          <a class="dropdown-item" href="' . $link . '">';
            $html .= '              <div class="card-body modern-card-mobile-body d-flex flex-column">';
            $html .= '                  <div class="media d-flex flex-column align-items-center">';
            $html .= '                      <div class="align-self-center">';
            $html .= '                          <i class="' . $icono . ' ' . $color . ' modern-card-mobile-icon"></i>'; 
            // ^ ícono más pequeño para móvil
            $html .= '                      </div>';
            $html .= '                      <div class="media-body text-center">';
            
            // Para el título en móvil, dividimos en dos partes para apilarlas
            $tituloParts = explode(" ", $titulo, 2);
            $html .= '                          <small class="modern-card-mobile-title">' . $tituloParts[0] . '</small>';
            if (isset($tituloParts[1])) {
                $html .= '                          <br><small class="modern-card-mobile-title">' . $tituloParts[1] . '</small>';
            }
            $html .= '                      </div>';
            $html .= '                  </div>';
            $html .= '              </div>';
            $html .= '          </a>';
            $html .= '      </div>';
            $html .= '  </div>';
            $html .= '</div>';

            echo $html;
        }
    } */
    public static function tarjeta($session, $titulo, $descripcion, $icono, $color, $link)
    {
        if (@$_SESSION[$session] == TRUE) {
            // Tarjeta para pantallas grandes
            $html = '<div class="d-none d-lg-block col-xl-3 col-lg-4 col-sm-6 col-12 mb-1">';
            $html .= '  <div class="card shadow-sm">';
            $html .= '      <div class="card-content">';
            $html .= '          <a class="dropdown-item" href="' . $link . '">';
            $html .= '              <div class="card-body">';
            $html .= '                  <div class="media d-flex">';
            $html .= '                      <div class="align-self-center">';
            $html .= '                          <i class="' . $icono . ' ' . $color . ' fs-1 float-left"></i>';
            $html .= '                      </div>';
            $html .= '                      <div class="media-body text-right">';
            $html .= '                          <h5 class="fw-bold">' . $titulo . '</h5>';
            $html .= '                          <span class="fst-italic">' . $descripcion . '</span>';
            $html .= '                      </div>';
            $html .= '                  </div>';
            $html .= '              </div>';
            $html .= '          </a>';
            $html .= '      </div>';
            $html .= '  </div>';
            $html .= '</div>';


            // Tarjeta para pantallas pequeñas
            $html .= '<div class="d-block d-lg-none col-4 p-0 mb-1;">';
            $html .= '  <div class="card shadow-sm" style="font-size: 1rem; min-height: 100%;">';
            $html .= '      <div class="card-content">';
            $html .= '          <a class="dropdown-item" href="' . $link . '">';
            $html .= '              <div class="card-body p-1 d-flex flex-column">'; 
            $html .= '                  <div class="media d-flex flex-column align-items-center">';
            $html .= '                      <div class="align-self-center">';
            $html .= '                          <i class="' . $icono . ' ' . $color . ' fs-3 mb-1"></i>';
            $html .= '                      </div>';
            $html .= '                      <div class="media-body text-center">';
            $tituloParts = explode(" ", $titulo, 2);
            $html .= '                          <small class="fw-bold p-0">' . $tituloParts[0] . '</small>';
            if (isset($tituloParts[1])) {
                $html .= '                          <br><small class="fw-bold p-0">' . $tituloParts[1] . '</small>';
            }
            $html .= '                      </div>';
            $html .= '                  </div>';
            $html .= '              </div>';
            $html .= '          </a>';
            $html .= '      </div>';
            $html .= '  </div>';
            $html .= '</div>';

            echo $html;
        }
    }

    public static function li($session, $titulo){
        if(@$_SESSION[$session] == TRUE) {
            $html = '<li><a class="dropdown-item" href="index.php?modulo=' . $session . '"><i class="fa-solid fa-chevron-right ml-1 mr-1"></i>' . $titulo . '</a></li>';
            echo $html;
        }
    }
}    
?>