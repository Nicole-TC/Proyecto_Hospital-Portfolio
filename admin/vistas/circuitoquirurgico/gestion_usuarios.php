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
                    <h3 class="fw-bold mb-0" id="stat-total">0</h3>
                    <p class="text-secondary small mb-0">Total Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-heart-pulse mb-2 success" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-cirujanos">0</h3>
                    <p class="text-secondary small mb-0">Cirujanos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-person-badge mb-2 info" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-recepcionistas">0</h3>
                    <p class="text-secondary small mb-0">Admisión/Recepcionista</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-shield-shaded mb-2 primary" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-admins">0</h3>
                    <p class="text-secondary small mb-0">Administradores</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-bold">BUSCAR POR</label>
                    <select class="form-select border-0 bg-light" id="filtroTipo" style="border-radius: 0.5rem; height: 45px;">
                        <option value="dni">DNI</option>
                        <option value="apellido">Apellido</option>
                        <option value="nombre">Nombre</option>
                        <option value="email">Email</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">VALOR A BUSCAR</label>
                    <input type="text" class="form-control border-0 bg-light" id="filtroValor" 
                           placeholder="Ingrese el dato..." style="border-radius: 0.5rem; height: 45px;"
                           onkeyup="if(event.key === 'Enter') ejecutarBusqueda()">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100 fw-bold" onclick="ejecutarBusqueda()" style="border-radius: 0.5rem; height: 45px; border: none;">
                        <i class="bi bi-search me-2"></i> BUSCAR USUARIO
                    </button>
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
                            <th class="ps-4 py-3 border-0 text-secondary text-center">Nombre</th>
                            <th class="py-3 border-0 text-secondary text-center">Email</th>
                            <th class="py-3 border-0 text-secondary text-center">Rol</th>
                            <!-- <th class="py-3 border-0 text-secondary text-center">Estado</th> -->
                            <!-- <th class="py-3 border-0 text-secondary text-center">Último Acceso</th> -->
                            <th class="py-3 pe-4 text-center border-0 text-secondary text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuariosBody">
                        <tr>
                            <td colspan="4" class="text-center py-5 text-secondary">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Cargando usuarios desde el servicio...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination Footer -->
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="text-secondary small" id="infoPaginacion">
                Mostrando 0 de 0 registros
            </div>
            <nav aria-label="Navegación de usuarios">
                <ul class="pagination pagination-sm mb-0" id="paginador">
                    <!-- Botones generados dinámicamente -->
                </ul>
            </nav>
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
                    ¿Estás seguro que querés dar de baja al siguiente usuario? El usuario <strong>no podrá acceder al sistema</strong> si lo das de baja.
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

<style>
/* Dark Mode - Gestión de Usuarios */
body.modo-oscuro .bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .bg-white {
    background-color: #2a2a2a !important;
}
body.modo-oscuro .card-footer.bg-white {
    background-color: #2a2a2a !important;
    border-top: 1px solid #444 !important;
}
body.modo-oscuro .form-control,
body.modo-oscuro .form-select {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}
body.modo-oscuro #modalConfirmarBaja .modal-header {
    background: #3a1418 !important;
}
body.modo-oscuro #modalConfirmarBaja .rounded-3 {
    background: #2a2a2a !important;
    border-color: #444 !important;
}
</style>

<script src="js/circuitoquirurgico/gestion_usuarios.js"></script>
