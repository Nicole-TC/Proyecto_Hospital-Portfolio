<?php
ob_start();
if (!isset($_SESSION)) {
    session_start();
}

require_once "template/header.php";
require_once "model/conexion.php";           // Conexión PDO a la base de datos
require_once "funciones/ip.php";              // Obtiene la IP del usuario
require_once "funciones/mobile_detect.php";   // Detecta SO y dispositivo

// 1) Capturar parámetros de redirección (GET o POST)
$redirect_modulo = null;
$redirect_valor  = null;

// Si llega por GET (primer acceso), guardo modulo y valor
if (isset($_GET['modulo'])) {
    $raw_modulo = filter_var($_GET['modulo'], FILTER_SANITIZE_SPECIAL_CHARS);
    $redirect_modulo = !empty($raw_modulo) ? trim($raw_modulo) : null;
}
if (isset($_GET['valor'])) {
    $raw_valor = filter_var($_GET['valor'], FILTER_SANITIZE_NUMBER_INT);
    $redirect_valor = !empty($raw_valor) ? trim($raw_valor) : null;
}

// Si se envía el formulario (POST), vuelvo a asignar para no perder valores en caso de error
if (isset($_POST['redirect_modulo'])) {
    $raw_modulo = filter_var($_POST['redirect_modulo'], FILTER_SANITIZE_SPECIAL_CHARS);
    $redirect_modulo = !empty($raw_modulo) ? trim($raw_modulo) : null;
}
if (isset($_POST['redirect_valor'])) {
    $raw_valor = filter_var($_POST['redirect_valor'], FILTER_SANITIZE_NUMBER_INT);
    $redirect_valor = !empty($raw_valor) ? trim($raw_valor) : null;
}

if (isset($_POST['submit'])) {
    // Sanitizar y validar usuario y contraseña
    $usuario         = filter_var($_POST["username"], FILTER_SANITIZE_SPECIAL_CHARS);
    $username        = !empty($usuario) ? trim($usuario) : null;
    $password        = filter_var($_POST["password"], FILTER_SANITIZE_SPECIAL_CHARS);
    $passwordAttempt = !empty($password) ? trim($password) : null;

    // Consultar usuario activo
    $q_user = $bd->prepare("SELECT * FROM admin WHERE username = ? AND activo = ? LIMIT 1;");
    $q_user->execute([$username, 1]);
    $user = $q_user->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        $_SESSION["error"] = "Algo no anda bien con los datos ingresados";
    } else {
        // Verificar contraseña encriptada
        $validPassword = password_verify($passwordAttempt, $user['password']);

        if ($validPassword) {
            // Definir todas las sesiones de permisos tal como en tu código original
            $_SESSION['id']                = $user["id"];
            $_SESSION['user']              = $username;
            $_SESSION['nombre']            = $user["nombre"];
            $_SESSION['apellido']          = $user["apellido"];
            $_SESSION['user_logged_in']    = TRUE;
            $_SESSION['en_construccion']   = $user["en_construccion"];
            $_SESSION['admin']             = $user["admin"];
            $_SESSION['debug']             = $user["debug"];

            // USUARIOS
            $_SESSION['miperfil']          = $user["miperfil"];
            $_SESSION['menu_usuarios']     = $user["menu_usuarios"];
            $_SESSION['nuevo_usuario']     = $user["nuevo_usuario"];
            $_SESSION['editar_usuario']    = $user["editar_usuario"];
            $_SESSION['usuarios_activos']  = $user["usuarios_activos"];


            // DOCUMENTOS
            //$_SESSION['formularios']   = $user["formularios"];
            $_SESSION['notas']         = $user["notas"];
            $_SESSION['menu_digitalizar'] = isset($user['menu_digitalizar']) ? $user['menu_digitalizar'] : 0;

            // CIRCUITO QUIRURGICO
            $_SESSION['menu_circuitoquirurgico'] = isset($user['menu_circuitoquirurgico']) ? $user['menu_circuitoquirurgico'] : 0;

            // Construir la URL relativa para redirigir al módulo/valor correcto
            if (!empty($redirect_modulo)) {
                $url = 'index.php?modulo=' . urlencode($redirect_modulo);
                if (!empty($redirect_valor)) {
                    $url .= '&valor=' . urlencode($redirect_valor);
                }
            } else {
                $url = 'index.php';
            }

            header('Location: ' . $url);
            exit;
        } else {
            $_SESSION["error"] = "Algo no anda bien con los datos ingresados.";
        }
    }
}
?>

<div class="bg-shadow-sm py-1 py-md-5">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-12 col-md-11 col-lg-8 col-xl-7 col-xxl-4">
                <div class="mx-auto bg-white p-1 rounded shadow-sm">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <div class="text-center">
                                <a href="#!">
                                    <img src="auxiliar/logo.png" alt="BootstrapBrain Logo" width="320" height="320">
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <form action="login.php" method="post">
                        <div class="row gy-3 gy-md-4 overflow-hidden">
                            <div class="col-12">
                                <?php require_once "template/mensajes_flash.php"; ?>
                            </div>

                            <!-- Input: Usuario -->
                            <div class="col-12">
                                <label for="username" class="form-label">Usuario <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="info" viewBox="0 0 16 16">
                                            <path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5M.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5"/>
                                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" name="username" id="username" required autofocus>
                                </div>
                            </div>

                            <!-- Input: Contraseña -->
                            <div class="col-12">
                                <label for="password" class="form-label">Clave <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="red" viewBox="0 0 16 16">
                                            <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                                            <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                        </svg>
                                    </span>
                                    <input type="password" class="form-control" name="password" id="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zM8 6a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Inputs ocultos para redirección -->
                            <input type="hidden" name="redirect_modulo" value="<?php echo htmlspecialchars($redirect_modulo); ?>">
                            <input type="hidden" name="redirect_valor"  value="<?php echo htmlspecialchars($redirect_valor); ?>">

                            <div class="col-12">
                                <div class="d-grid mt-2 mb-2">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg">Entrar</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('svg').classList.toggle('bi-eye');
        this.querySelector('svg').classList.toggle('bi-eye-slash');
    });
</script>

<?php
require_once "template/footer.php";
?>
