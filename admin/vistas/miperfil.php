<?php
//ob_start();

$id = $_SESSION["id"] ?? null;

if (!$id) {
    $_SESSION['error'] = "No se encontró el usuario en sesión.";
    header('Location: login.php');
    exit;
}

$sentencia = $bd->prepare("SELECT * FROM admin WHERE id = ?;");
$sentencia->execute([$id]);
$usuario = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$usuario) {
    $_SESSION['error'] = "El usuario no existe.";
    header('Location: index.php?modulo=menu_usuarios');
    exit;
}
?>

<div class="col-sm-12 mb-1">
    <div class="card">
        <div class="card-header">
            Ingresar datos del usuario:
        </div>

        <form class="p-1" method="POST" action="app/usuarios/miperfil.php" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label class="form-label">DNI</label>
                    <input type="number" class="form-control" autocomplete="off" name="dni" autofocus required value="<?php echo htmlspecialchars($usuario->dni); ?>" disabled>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Nombres</label>
                    <input type="text" class="form-control" autocomplete="off" name="nombre" autofocus required value="<?php echo htmlspecialchars($usuario->nombre); ?>" disabled>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Apellidos</label>
                    <input type="text" class="form-control" autocomplete="off" name="apellido" autofocus required value="<?php echo htmlspecialchars($usuario->apellido); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="form-label">Nombre usuario</label>
                    <input type="text" class="form-control" autocomplete="off" name="username" autofocus required value="<?php echo htmlspecialchars($usuario->username); ?>" disabled>
                    <p class="fw-lighter">El nombre de usuario que usará para loguearse al sistema</p>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Clave</label><a class="ml-1" data-bs-toggle="modal" data-bs-target="#cambiar_pass"><i class="fa-solid fa-lock info"></i></a>
                    <input type="text" class="form-control" autocomplete="off" name="password_visual" autofocus required value="Contraseña protegida" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control" autocomplete="off" name="email" autofocus required value="<?php echo htmlspecialchars($usuario->email); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" autocomplete="off" name="telefono" autofocus required value="<?php echo htmlspecialchars($usuario->telefono); ?>">
                    <p class="fw-lighter">Número de celular sin símbolos</p>
                </div>
            </div>
            
            <div class="d-grid">
                <p class="text-center">
                    <input type="hidden" name="oculto" value="1">
                    <a class="btn btn-danger" href="index.php" role="button">Cancelar</a>

                    <a class="float" data-bs-toggle="modal" data-bs-target="#ayudaFecha">
                        <input type="button" class="btn btn-primary ml-2" value="Grabar">
                    </a>
                </p>
            </div>

            <!-- Modal confirmación -->
            <div class="modal fade" id="ayudaFecha" tabindex="-1" aria-labelledby="ayudaFechaLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ayudaFechaLabel">Confirmación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="fs-6">Para confirmar los cambios debes ingresar tu contraseña actual.</p>
                            <div class="form-group col">
                                <label class="form-label">Clave</label>
                                <input type="password" class="form-control" autocomplete="off" name="password" autofocus required value="">
                                <p class="fw-lighter">La contraseña se solicita únicamente para confirmar la modificación.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <p class="text-center">
                                <input type="hidden" name="oculto" value="1">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario->id); ?>">
                                
                                <a class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Cancelar</a>
                                
                                <input type="submit" class="btn btn-primary ml-2" value="Actualizar">
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<div class="modal fade" id="cambiar_pass" tabindex="-1" aria-labelledby="cambiar_pass_label" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cambiar_pass_label"><b>Cambiando contraseña del agente <?php echo htmlspecialchars($usuario->apellido . ', ' . $usuario->nombre); ?></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="app/usuarios/cambiar_pass_miperfil.php" method="post">

                <div class="modal-body">
                    <div class="form-row">

                        <div class="col-12">

                            <label for="password" class="form-label">Contraseña actual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="red" viewBox="0 0 16 16">
                                        <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z" />
                                        <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                                    </svg>
                                </span>
                                <input type="password" class="form-control" name="password" id="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zM8 6a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                    </svg>
                                </button>
                            </div>

                            <label for="password_nueva" class="form-label">Nueva contraseña<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="red" viewBox="0 0 16 16">
                                        <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z" />
                                        <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                                    </svg>
                                </span>
                                <input type="password" class="form-control" name="password_nueva" id="password_nueva" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglepassword_nueva">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zM8 6a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario->id); ?>">
                    <a class="btn btn-danger" data-bs-dismiss="modal" role="button">Cerrar</a>
                    <button type="submit" class="btn btn-primary ml-1">Actualizar contraseña</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    const togglePasswordActual = document.querySelector('#togglepassword_nueva');
    const passwordActual = document.querySelector('#password_nueva');

    if (togglePasswordActual && passwordActual) {
        togglePasswordActual.addEventListener('click', function (e) {
            const type = passwordActual.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordActual.setAttribute('type', type);
        });
    }

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
        });
    }
</script>