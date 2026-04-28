<?php
//ob_start();

$id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

if (!is_int($id)) {
    $_SESSION['error'] = "Algo no anduvo bien por ahí al intentar editar el turno " . $_GET["id"];
    header('Location: ../../index.php?modulo=menu_usuarios');
    exit;
}

$sentencia = $bd->prepare("SELECT * FROM admin WHERE id = ?;");
$sentencia->execute([$id]);
$usuario = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$usuario) {
    $_SESSION['error'] = "El usuario no existe.";
    header('Location: ../../index.php?modulo=menu_usuarios');
    exit;
}
?>

<div class="col-sm-12 mb-5">
    <div class="card">
        <div class="card-header">
            Ingresar datos del usuario:
        </div>

        <form class="p-1" method="POST" action="app/usuarios/editar.php" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label class="form-label">DNI</label>
                    <input type="number" class="form-control" autocomplete="off" name="dni" autofocus required value="<?php echo $usuario->dni; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Nombres</label>
                    <input type="text" class="form-control" autocomplete="off" name="nombre" autofocus required value="<?php echo $usuario->nombre; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Apellidos</label>
                    <input type="text" class="form-control" autocomplete="off" name="apellido" autofocus required value="<?php echo $usuario->apellido; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="form-label">Nombre usuario</label>
                    <input type="text" class="form-control" autocomplete="off" name="username" autofocus required value="<?php echo $usuario->username; ?>">
                    <p class="fw-lighter">El nombre de usuario que usará para loguearse al sistema</p>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Clave</label><a class="ml-1" data-bs-toggle="modal" data-bs-target="#cambiar_pass"><i class="fa-solid fa-lock info"></i></a>
                    <input type="text" class="form-control" autocomplete="off" name="password" autofocus required value="Contraseña protegida" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control" autocomplete="off" name="email" autofocus required value="<?php echo $usuario->email; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" autocomplete="off" name="telefono" autofocus required value="<?php echo $usuario->telefono; ?>">
                    <p class="fw-lighter">Número de celular sin números ni símbolos</p>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12">
                    <label class="form-label mt-2">Permisos</label>
                </div>
            </div>

            <div class="form-row">

                <!-- PERMISOS ESPECIALES -->
                <div class="form-group col-lg-12">
                    <div class="accordion" id="accordionPanelsStayOpenExample">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingEspeciales">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Especiales" aria-expanded="false" aria-controls="panelsStayOpen-collapseEspeciales">
                                    <i class="fa-solid fa-chalkboard-user danger mr-1"></i> Especiales
                                </button>
                            </h2>
                            <div id="Especiales" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingEspeciales">
                                <div class="accordion-body">
                                    <div class="form-group col-lg-12">

                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="activo" id="activo" <?php if ($usuario->activo == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="activo">Usuario activo</label>
                                        </div>

                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="admin" id="admin" <?php if ($usuario->admin == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="admin">Usuario administrador</label>
                                            <p class="fw-lighter">Definir permisos espécíficos para administradores</p>
                                        </div>

                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="debug" id="debug" <?php if ($usuario->debug == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="debug">Mensajes debugger</label>
                                            <p class="fw-lighter">Muestra todos los mensajes y variables GET, POST, SESSIONS y archivos incluidos de cada proceso</p>
                                        </div>                                      

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php // USUARIOS ?>
                <div class="form-group col-lg-12">
                    <div class="accordion" id="accordionPanelsStayOpenExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingTres">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Usuarios" aria-expanded="false" aria-controls="panelsStayOpen-collapseTres">
                                    <i class="fa-solid fa-users-gear info mr-1"></i> Sistema de usuarios
                                </button>
                            </h2>
                            <div id="Usuarios" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTres">
                                <div class="accordion-body">
                                    <div class="form-group col-lg-12">
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="menu_usuarios" id="menu_usuarios" <?php if ($usuario->menu_usuarios == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="menu_usuarios">Menú usuarios</label>
                                            <p class="fw-lighter">Muestra el menú de usuario</p>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="nuevo_usuario" id="nuevo_usuario" <?php if ($usuario->nuevo_usuario == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="nuevo_usuario">Crear nuevo usuario</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="editar_usuario" id="editar_usuario" <?php if ($usuario->editar_usuario == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="editar_usuario">Editar usuario</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="usuarios_activos" id="usuarios_activos" <?php if ($usuario->usuarios_activos == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="usuarios_activos">Listado usuarios</label>
                                        </div>
                                    </div>                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DOCUMENTOS -->
                <div class="form-group col-lg-12">
                    <div class="accordion" id="accordionPanelsStayOpenExample">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingDocumentos">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Documentos" aria-expanded="false" aria-controls="panelsStayOpen-collapseDocumentos">
                                    <i class="fa-solid fa-file info mr-1"></i>Documentos
                                </button>
                            </h2>
                            <div id="Documentos" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingDocumentos">
                                <div class="accordion-body">
                                    <div class="form-group col-lg-12">

                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="notas" id="notas" <?php if ($usuario->notas == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="notas">Notas</label>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- EN CONSTRUCCION -->
                <div class="form-group col-lg-12">
                    <div class="accordion" id="accordionPanelsStayOpenExample">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingEnConstruccion">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#EnConstruccion" aria-expanded="false" aria-controls="panelsStayOpen-collapseEnConstruccion">
                                    <i class="fa-solid fa-helmet-safety danger mr-1"></i> Misceláneas
                                </button>
                            </h2>
                            <div id="EnConstruccion" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingEnConstruccion">
                                <div class="accordion-body">
                                    <div class="form-group col-lg-12">
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="en_construccion" id="en_construccion" <?php if ($usuario->en_construccion == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="en_construccion">En Construcción</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="miperfil" id="miperfil" <?php if ($usuario->miperfil == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="miperfil">Mi perfil</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="paginas_ayuda" id="paginas_ayuda" <?php if ($usuario->paginas_ayuda == 1) { echo "checked"; } ?>>
                                            <label class="form-check-label" for="paginas_ayuda">Páginas de ayuda</label>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>

            <div class="d-grid mt-2">
                <p class="text-center">
                    <input type="hidden" name="oculto" value="1">
                    <input type="hidden" name="id" value="<?php echo $usuario->id; ?>">
                    <input type="hidden" name="date_time" value="<?php echo date('Y-m-d H:i:s'); ?>">
                    <a class="btn btn-danger" href="index.php?modulo=menu_usuarios" role="button">Cancelar</a>
                    <input type="submit" class="btn btn-primary ml-2" value="Actualizar">
                </p>
            </div>

        </form>
    </div>
</div>

<!-- ACTUALIZAR PASS-->
<div class="modal fade" id="cambiar_pass" tabindex="-1" aria-labelledby="#cambiar_pass" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="#icambiar_pass"><b>Cambiando contraseña del agente <?php echo "$usuario->apellido, $usuario->nombre"; ?></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="app/usuarios/cambiar_pass.php" method="post">

                <div class="modal-body">
                    <div class="form-row">

                        <div class="col-12">
                            <label for="password" class="form-label">Clave <span class="text-danger">*</span></label>

                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-key text-danger"></i></span>
                                <input type="password" class="form-control" name="password" id="password" autocomplete="new-password" required>

                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="id" value="<?php echo $usuario->id; ?>">
                    <a class="btn btn-danger" data-bs-dismiss="modal" role="button">Cerrar</a>
                    <button type="submit" class="btn btn-primary ml-1">Actualizar contraseña</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            const icono = this.querySelector('i');
            if (icono) {
                icono.classList.toggle('fa-eye');
                icono.classList.toggle('fa-eye-slash');
            }
        });
    }
</script>