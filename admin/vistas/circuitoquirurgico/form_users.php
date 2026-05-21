<?php
// Detectar modo: 'editar' o 'nuevo'
$modoEditar = isset($_GET['modo']) && $_GET['modo'] === 'editar';
$idPersona  = $modoEditar && isset($_GET['id']) ? (int)$_GET['id'] : null;
$tituloForm = $modoEditar ? 'Editar usuario' : 'Cargar nuevo usuario';
$urlVolver  = 'index.php?modulo=menu_circuitoquirurgico&accion=gestion_usuarios';
?>
<div class="col-12 px-3 pb-5">


    <!-- formulario -->
    <div class="card shadow-sm bg-white" style="border: 1px solid #e0e0e0; border-radius: 4px;">
        <div class="card-body p-4">
            <p class="text-secondary mb-4" style="font-size: 0.95rem;">
                <?= $modoEditar ? 'Modificar datos del usuario. El DNI no puede editarse.' : 'Ingresar datos del nuevo usuario:' ?>
            </p>

            <?php if ($modoEditar): ?>
            <!-- Banner informativo de modo edición -->
            <div class="alert alert-info d-flex align-items-center mb-4 py-2 px-3" style="font-size:0.87rem;">
                <i class="bi bi-info-circle-fill me-2"></i>
                <span>Estás editando un usuario existente. El <strong>DNI no puede modificarse</strong>. Los demás campos están habilitados.</span>
            </div>
            <?php endif; ?>

            <form action="#" method="POST" id="formUsuario" novalidate
                  data-modo="<?= $modoEditar ? 'editar' : 'nuevo' ?>"
                  data-id="<?= $idPersona ?? '' ?>">

                <!-- ===================== SECCIÓN DNI ===================== -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="dni" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                            DNI <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control text-secondary <?= $modoEditar ? 'bg-light' : '' ?>"
                                id="dni"
                                name="dni"
                                placeholder="Ej: 35482910"
                                maxlength="8"
                                autocomplete="off"
                                <?= $modoEditar ? 'readonly' : 'required' ?>
                            >
                            <span class="input-group-text bg-light" id="dni-status-icon">
                                <?php if ($modoEditar): ?>
                                    <i class="bi bi-lock-fill text-secondary" id="ico-dni"></i>
                                <?php else: ?>
                                    <i class="bi bi-search text-secondary" id="ico-dni"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div id="dni-feedback" class="mt-1" style="font-size: 0.82rem;">
                            <?php if ($modoEditar): ?>
                                <span class="text-secondary"><i class="bi bi-lock me-1"></i>El DNI no puede modificarse</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!$modoEditar): ?>
                        <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                            <i class="bi bi-info-circle me-1"></i>Solo números, sin puntos ni espacios. Entre 6 y 8 dígitos.
                        </small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8 d-flex align-items-end pb-1">
                        <?php if (!$modoEditar): ?>
                        <div id="dni-aviso-bloqueo" class="alert alert-warning py-2 px-3 mb-0 w-100" style="font-size: 0.85rem; display: none !important;">
                            <i class="bi bi-lock-fill me-1"></i>
                            <strong>Validá el DNI primero.</strong> El resto del formulario se habilitará una vez que el DNI sea verificado como disponible.
                        </div>
                        <div id="dni-aviso-ok" class="alert alert-success py-2 px-3 mb-0 w-100" style="font-size: 0.85rem; display: none !important;">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            DNI disponible. Podés completar el resto del formulario.
                        </div>
                        <div id="dni-aviso-existe" class="alert alert-danger py-2 px-3 mb-0 w-100" style="font-size: 0.85rem; display: none !important;">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            <strong>Este DNI ya está registrado.</strong> No podés continuar con este DNI.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ===================== RESTO DEL FORMULARIO ===================== -->
                <!-- En modo editar: sin fieldset disabled (ya está desbloqueado desde el inicio) -->
                <!-- En modo nuevo: fieldset disabled hasta que se valide el DNI -->
                <fieldset id="campos-principal" <?= $modoEditar ? '' : 'disabled' ?>>

                    <!-- Nombre y Apellido -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="nombre" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                                Nombres <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control text-secondary" id="nombre" name="nombre"
                                   placeholder="Ej: MARIA JOSE" maxlength="100" autocomplete="off" required>
                            <div id="nombre-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>Solo letras mayúsculas, acentos y Ñ. Mín. 2 caracteres.
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label for="apellido" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                                Apellidos <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control text-secondary" id="apellido" name="apellido"
                                   placeholder="Ej: GONZALEZ MUÑOZ" maxlength="100" autocomplete="off" required>
                            <div id="apellido-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>Solo letras mayúsculas, acentos y Ñ. Mín. 2 caracteres.
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                                Fecha de nacimiento <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control text-secondary" id="fecha_nacimiento"
                                   name="fecha_nacimiento" required>
                            <div id="fecha-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>El usuario debe ser mayor de 18 años.
                            </small>
                        </div>
                    </div>

                    <!-- Rol, Email, Teléfono -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="rol" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                                Rol del sistema <span class="text-danger">*</span>
                            </label>
                            <select class="form-select text-secondary" id="rol" name="rol" required>
                                <option value="" selected disabled>Seleccionar rol...</option>
                            </select>
                            <div id="rol-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                                Correo electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control text-secondary" id="email" name="email"
                                   placeholder="Ej: nombre@hospital.com" maxlength="100" autocomplete="off" required>
                            <div id="email-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="telefono" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                                Teléfono <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control text-secondary" id="telefono" name="telefono"
                                   placeholder="Ej: 1134567890" maxlength="15" autocomplete="off" required>
                            <div id="telefono-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>Solo números. Entre 7 y 15 dígitos.
                            </small>
                        </div>
                    </div>

                    <!-- Dirección y Código Postal -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="direccion" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Dirección</label>
                            <input type="text" class="form-control text-secondary" id="direccion" name="direccion"
                                   placeholder="Ej: AV. CORRIENTES 1234" maxlength="150" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label for="codigo_postal" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Código Postal</label>
                            <input type="text" class="form-control text-secondary" id="codigo_postal" name="codigo_postal"
                                   placeholder="Ej: 1414" maxlength="8" autocomplete="off">
                            <div id="cp-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>Solo letras y números. Entre 4 y 8 caracteres.
                            </small>
                        </div>
                    </div>

                    <!-- Campos de Cirujano -->
                    <div id="datos_cirujano" style="display: none;">
                        <h6 class="text-secondary mt-4 mb-3 border-bottom pb-2">Datos del Cirujano</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="especialidad" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">
                                    Especialidad Troncal <span class="text-danger">*</span>
                                </label>
                                <!-- Solo se cargan especialidades padre (id_especialidad_padre IS NULL) -->
                                <select class="form-select text-secondary" id="especialidad" name="especialidad">
                                    <option value="" selected disabled>Seleccionar especialidad...</option>
                                </select>
                                <div id="especialidad-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            </div>
                            <div class="col-md-4" id="wrap_numero_matricula">
                                <label for="numero_matricula" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Número de Matrícula <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-secondary" id="numero_matricula"
                                       name="numero_matricula" placeholder="Ej: 12345" maxlength="50">
                                <div id="matricula-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            </div>
                            <div class="col-md-4" id="wrap_tipo_matricula">
                                <label for="tipo_matricula" class="form-label text-secondary mb-1" style="font-size: 0.9rem;">Tipo de Matrícula <span class="text-danger">*</span></label>
                                <select class="form-select text-secondary" id="tipo_matricula" name="tipo_matricula">
                                    <option value="" selected disabled>Seleccionar tipo...</option>
                                    <option value="Nacional">Nacional</option>
                                    <option value="Provincial">Provincial</option>
                                </select>
                                <div id="tipo-matricula-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                            </div>
                        </div>

                        <!-- Contenedor subespecialidades: aparece cuando la especialidad padre tiene hijos -->
                        <div id="contenedor_subespecialidades" style="display: none;">
                            <div class="d-flex align-items-center gap-2 mt-4 mb-2">
                                <span style="display:inline-flex; align-items:center; justify-content:center;
                                             width:28px; height:28px; border-radius:50%;
                                             background: linear-gradient(135deg,#00cec9,#00b5ad);
                                             color:#fff; font-size:0.8rem;">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </span>
                                <span class="text-secondary fw-semibold" style="font-size:0.9rem;">
                                    Subespecialidades
                                </span>
                                <small class="text-muted" style="font-size:0.78rem;">
                                    — seleccioná las que apliquen e ingresá su matrícula
                                </small>
                            </div>
                            <div class="card border-0 shadow-sm p-3"
                                 style="border-radius:8px;
                                        border-left: 3px solid #00cec9 !important;
                                        background-color: #f8fdfd;">
                                <div id="lista_subespecialidades">
                                    <!-- Checkboxes generados dinámicamente por JS -->
                                </div>
                            </div>
                            <div id="subesp-feedback" class="mt-1" style="font-size: 0.82rem;"></div>
                        </div>
                    </div>

                </fieldset>

                <div class="d-flex justify-content-center mt-5 mb-3 gap-3">
                    <a href="<?= $urlVolver ?>" class="btn border-0 shadow-sm"
                       style="background-color: #ff6b81; color: white; padding: 8px 30px; font-weight: 400; border-radius: 4px;">
                        Cancelar
                    </a>
                    <button type="submit" id="btn-guardar" class="btn border-0 shadow-sm"
                            style="background-color: #00cec9; color: white; padding: 8px 30px; font-weight: 400; border-radius: 4px;"
                            <?= $modoEditar ? '' : 'disabled' ?>>
                        <?= $modoEditar ? 'Guardar cambios' : 'Guardar' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="js/circuitoquirurgico/form_users.js"></script>
