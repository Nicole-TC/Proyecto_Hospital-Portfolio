<div class="col-12 mb-5 px-3">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h2 class="blue fw-bold mb-1">Gestión de Usuarios</h2>
            <p class="text-secondary mb-0">Administración de usuarios y permisos del sistema</p>
        </div>
        <a href="index.php?modulo=menu_circuitoquirurgico&accion=form_users" class="btn btn-success float-right">
            <i class="fa-solid fa-user-plus fa-bounce white fs-3"></i>
            <span class="float-right fs-6 text-white ml-1"> Nuevo usuario</span>
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-people mb-2 blue" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0">7</h3>
                    <p class="text-secondary small mb-0">Total Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-shield-check mb-2 success" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0">6</h3>
                    <p class="text-secondary small mb-0">Usuarios Activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-shield-x mb-2 danger" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0">1</h3>
                    <p class="text-secondary small mb-0">Usuarios Inactivos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-shield-shaded mb-2 blue" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0">1</h3>
                    <p class="text-secondary small mb-0">Administradores</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm border-0" style="border-radius: 0.75rem;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary">Nombre</th>
                            <th class="py-3 border-0 text-secondary">Email</th>
                            <th class="py-3 border-0 text-secondary">Rol</th>
                            <th class="py-3 border-0 text-secondary">Estado</th>
                            <th class="py-3 border-0 text-secondary">Último Acceso</th>
                            <th class="py-3 pe-4 text-center border-0 text-secondary">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuariosBody">
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Cargando usuarios desde el servicio...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL CONFIRMAR BAJA LÓGICA ========== -->
<div class="modal fade" id="modalConfirmarBaja" tabindex="-1" aria-labelledby="modalBajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 0.75rem; overflow: hidden;">

            <!-- Cabecera -->
            <div class="modal-header border-0 pb-0" style="background: #fff3f5;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#ff6b81;">
                        <i class="bi bi-person-x-fill text-white" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="modalBajaLabel" style="color:#c0392b;">
                            Dar de baja usuario
                        </h6>
                        <small class="text-secondary">Esta acción es reversible desde la administración</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Cuerpo -->
            <div class="modal-body px-4 pt-3 pb-2">
                <p class="text-secondary mb-3" style="font-size:0.9rem;">
                    ¿Estás seguro que querés dar de baja al siguiente usuario? El usuario <strong>no podrá acceder al sistema</strong> mientras esté inactivo.
                </p>

                <!-- Datos del usuario -->
                <div class="rounded-3 p-3 mb-1" style="background:#f8f9fa; border:1px solid #e9ecef;">
                    <div class="row g-2">
                        <div class="col-6">
                            <span class="text-secondary d-block" style="font-size:0.75rem;">DNI</span>
                            <strong id="modal-baja-dni" class="text-dark" style="font-size:0.95rem;">—</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-secondary d-block" style="font-size:0.75rem;">Rol</span>
                            <strong id="modal-baja-rol" class="text-dark" style="font-size:0.95rem;">—</strong>
                        </div>
                        <div class="col-12 mt-1">
                            <span class="text-secondary d-block" style="font-size:0.75rem;">Nombre completo</span>
                            <strong id="modal-baja-nombre" class="text-dark" style="font-size:0.95rem;">—</strong>
                        </div>
                    </div>
                </div>
                <!-- ID oculto -->
                <input type="hidden" id="modal-baja-id" value="">
            </div>

            <!-- Pie -->
            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2 justify-content-center">
                <button type="button" class="btn border-0 shadow-sm px-4"
                        data-bs-dismiss="modal"
                        style="background:#e9ecef; color:#555; border-radius:4px; font-size:0.9rem;">
                    Cancelar
                </button>
                <button type="button" class="btn border-0 shadow-sm px-4" id="btn-confirmar-baja"
                        onclick="confirmarBaja()"
                        style="background:#ff6b81; color:#fff; border-radius:4px; font-size:0.9rem;">
                    <i class="bi bi-person-dash me-1"></i> Confirmar baja
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========== TOAST NOTIFICACIÓN ========== -->


<script src="js/circuitoquirurgico/gestion_usuarios.js"></script>
