<div class="col-12 mb-5 px-3">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h2 class="blue fw-bold mb-1">Gestión de Usuarios</h2>
            <p class="text-secondary mb-0">Administración de usuarios y permisos del sistema</p>
        </div>
        <a href="index.php?modulo=menu_circuitoquirurgico&accion=form_users" class="btn btn-success px-3 py-2 rounded-3 fw-semibold shadow-sm">
            <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
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

<script src="js/circuitoquirurgico/gestion_usuarios.js"></script>
