<?php
// =======================
// auth_validate.php
// =======================

// 1) Inicia la sesión (si no está iniciada)
if (!isset($_SESSION)) {
    session_start();
}

// 2) Si el usuario NO está autenticado, redirigirlo a login.php
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== TRUE) {
    // Construir query string con los mismos GET que vinieron (modulo y valor), si existen:
    $qs = '';
    if (isset($_GET['modulo']) && $_GET['modulo'] !== '') {
        $qs .= 'modulo=' . urlencode($_GET['modulo']);
        if (isset($_GET['valor']) && $_GET['valor'] !== '') {
            $qs .= '&valor=' . urlencode($_GET['valor']);
        }
    }
    header('Location: login.php' . (!empty($qs) ? '?' . $qs : ''));
    exit;
}

// 3) Validar que el usuario tenga permiso sobre el módulo (si se está pidiendo uno)
if (isset($_GET["modulo"])) {
    $menu    = $_GET["modulo"];
    // En $_SESSION[$menu] debemos tener 1 o 0 según el permiso
    $permiso = isset($_SESSION[$menu]) ? $_SESSION[$menu] : 0;
    
    // Si el usuario es administrador, siempre tiene permiso
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
        $permiso = 1;
    }

    if ($permiso == 0) {
        $_SESSION['info'] = "No tienes permisos para acceder a este lugar.";
        // Redirigimos nuevamente a index.php (sin parámetros)
        header('Location: index.php');
        exit;
    }
}
?>
