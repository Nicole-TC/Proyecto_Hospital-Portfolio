<div class="col-sm-12 mb-5">
    <div class="card">
        <div class="card-header">
            Ingresar datos del usuario:
        </div>

        <form class="p-1" method="POST" action="app/usuarios/registrar.php" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label class="form-label">DNI</label>
                    <input type="number" class="form-control" autocomplete="off" name="dni" autofocus required>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Nombres</label>
                    <input type="text" class="form-control" autocomplete="off" name="nombre" autofocus required>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Apellidos</label>
                    <input type="text" class="form-control" autocomplete="off" name="apellido" autofocus required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="form-label">Nombre usuario</label>
                    <input type="text" class="form-control" autocomplete="off" name="username" autocomplete="off" autofocus required>
                    <p class="fw-lighter">El nombre de usuario que usará para loguearse al sistema</p>
                </div>
                <div class="form-group col-md-6">
                    <label for="password" class="form-label">Clave <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="red" viewBox="0 0 16 16">
                                <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z" />
                                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                            </svg>
                        </span>
                        <input type="password" class="form-control" name="password" id="password" autocomplete="off" autofocus required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zM8 6a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control" autocomplete="off" name="email" autofocus required>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="numer" class="form-control" autocomplete="off" name="telefono" autofocus required>
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
                                            <input type="checkbox" class="form-check-input" name="activo" id="activo">
                                            <label class="form-check-label" for="activo">Usuario activo</label>
                                        </div>

                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="admin" id="admin">
                                            <label class="form-check-label" for="admin">Usuario administrador</label>
                                            <p class="fw-lighter">Definir permisos espécíficos para administradores</p>
                                        </div>

                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="debug" id="debug">
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
                                            <input type="checkbox" class="form-check-input" name="menu_usuarios" id="menu_usuarios">
                                            <label class="form-check-label" for="menu_usuarios">Menú usuarios</label>
                                            <p class="fw-lighter">Muestra el menú de usuario</p>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="nuevo_usuario" id="nuevo_usuario">
                                            <label class="form-check-label" for="nuevo_usuario">Crear nuevo usuario</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="editar_usuario" id="editar_usuario">
                                            <label class="form-check-label" for="editar_usuario">Editar usuario</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="usuarios_activos" id="usuarios_activos">
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
                                            <input type="checkbox" class="form-check-input" name="notas" id="notas">
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
                                            <input type="checkbox" class="form-check-input" name="en_construccion" id="en_construccion">
                                            <label class="form-check-label" for="en_construccion">En Construcción</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="miperfil" id="miperfil">
                                            <label class="form-check-label" for="miperfil">Mi perfil</label>
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input type="checkbox" class="form-check-input" name="paginas_ayuda" id="paginas_ayuda">
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
                    <a class="btn btn-danger" href="index.php?modulo=menu_usuarios" role="button">Cancelar</a>
                    <input type="submit" class="btn btn-primary ml-2" value="Guardar">
                </p>
            </div>

        </form>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        // Cambia el tipo de entrada del campo de contraseña entre 'password' y 'text'
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        // Cambia el ícono del botón entre el ojo tachado y el ojo abierto
        this.querySelector('svg').classList.toggle('bi-eye');
        this.querySelector('svg').classList.toggle('bi-eye-slash');
    });
</script>