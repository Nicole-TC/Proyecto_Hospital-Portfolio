<div class="col-12 px-3 pb-5">
    <!-- encabezado -->
    <div class="card shadow-sm mb-3" style="border: 1px solid #e0e0e0; border-radius: 4px;">
        <div class="card-body py-3 d-flex justify-content-between align-items-center bg-white">
            <a href="index.php?modulo=menu_circuitoquirurgico" class="text-info text-decoration-none">
                <i class="bi bi-house"></i>
            </a>
            <h6 class="mb-0 fw-bold text-dark mx-auto">Cargar nuevo usuario</h6>
            <a href="index.php?modulo=menu_circuitoquirurgico&accion=gestion_usuarios" class="text-info text-decoration-none">
                <i class="bi bi-arrow-left-circle"></i>
            </a>
        </div>
    </div>

    <!-- formulario -->
    <div class="card shadow-sm bg-white" style="border: 1px solid #e0e0e0; border-radius: 4px;">
        <div class="card-body p-4">
            <p class="text-secondary mb-4" style="font-size: 0.95rem;">Ingresar datos del usuario:</p>
            
            <form action="#" method="POST" class="needs-validation" novalidate id="formUsuario">
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="dni" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">DNI</label>
                        <input type="text" class="form-control text-secondary" id="dni" name="dni" minlength="7" maxlength="10" pattern="\d+" required>
                    </div>
                    <div class="col-md-4">
                        <label for="nombre" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Nombres</label>
                        <input type="text" class="form-control text-secondary" id="nombre" name="nombre" maxlength="100" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" required>
                    </div>
                    <div class="col-md-5">
                        <label for="apellido" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Apellidos</label>
                        <input type="text" class="form-control text-secondary" id="apellido" name="apellido" maxlength="100" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="username" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Nombre usuario</label>
                        <input type="text" class="form-control text-secondary" id="username" name="username" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label mb-1" style="color: #e85d75; font-size: 0.9rem;">Clave *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key" style="color: #e85d75;"></i></span>
                            <input type="password" class="form-control text-secondary" id="password" name="password" required>
                            <span class="input-group-text bg-white" style="cursor: pointer;" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';"><i class="bi bi-eye text-dark"></i></span>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="fecha_nacimiento" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Fecha de nacimiento</label>
                        <input type="date" class="form-control text-secondary" id="fecha_nacimiento" name="fecha_nacimiento" required>
                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">La fecha de nacimiento del personal</small>
                    </div>
                    <div class="col-md-6">
                        <label for="rol" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Rol del sistema *</label>
                        <select class="form-select text-secondary" id="rol" name="rol" required>
                            <option value="" selected disabled>Seleccionar rol...</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Médico Cirujano">Médico Cirujano</option>
                            <option value="Administrativo Admisión">Administrativo Admisión</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="email" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Correo electrónico</label>
                        <input type="email" class="form-control text-secondary" id="email" name="email" maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label for="telefono" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Teléfono</label>
                        <input type="tel" class="form-control text-secondary" id="telefono" name="telefono" maxlength="30" pattern="[\d\s\-\+\(\)]+">
                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Número de celular sin números ni símbolos</small>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label for="direccion" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Dirección</label>
                        <input type="text" class="form-control text-secondary" id="direccion" name="direccion" maxlength="150">
                    </div>
                    <div class="col-md-4">
                        <label for="codigo_postal" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Código Postal</label>
                        <input type="text" class="form-control text-secondary" id="codigo_postal" name="codigo_postal" maxlength="10">
                    </div>
                </div>


                <div class="d-flex justify-content-center mt-5 mb-3 gap-3">
                    <a href="index.php?modulo=menu_circuitoquirurgico&accion=gestion_usuarios" class="btn border-0 shadow-sm" style="background-color: #ff6b81; color: white; padding: 8px 30px; font-weight: 400; border-radius: 4px;">Cancelar</a>
                    <button type="submit" class="btn border-0 shadow-sm" style="background-color: #00cec9; color: white; padding: 8px 30px; font-weight: 400; border-radius: 4px;">Guardar</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="js/circuitoquirurgico/form_users.js"></script>
