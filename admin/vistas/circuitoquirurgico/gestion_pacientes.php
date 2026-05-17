<div class="col-12 mb-5 px-3">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h2 class="blue fw-bold mb-1">Gesti&oacute;n de Pacientes</h2>
            <p class="text-secondary mb-0">Administraci&oacute;n de pacientes del circuito quir&uacute;rgico</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-people mb-2 blue" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-total">0</h3>
                    <p class="text-secondary small mb-0">Total Pacientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-clock-history mb-2 warning" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-en-espera">0</h3>
                    <p class="text-secondary small mb-0">En Espera</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-heart-pulse mb-2 info" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-en-curso">0</h3>
                    <p class="text-secondary small mb-0">En Cirug&iacute;a</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-check-circle mb-2 success" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-completados">0</h3>
                    <p class="text-secondary small mb-0">Completados</p>
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
                        <option value="telefono">Tel&eacute;fono</option>
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
                        <i class="bi bi-search me-2"></i> BUSCAR PACIENTE
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card shadow-sm border-0" style="border-radius: 0.75rem;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary text-center">Nombre</th>
                            <th class="py-3 border-0 text-secondary text-center">DNI</th>
                            <th class="py-3 border-0 text-secondary text-center">Email</th>
                            <th class="py-3 pe-4 text-center border-0 text-secondary text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPacientesBody">
                        <tr>
                            <td colspan="4" class="text-center py-5 text-secondary">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Cargando pacientes desde el servicio...
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
            <nav aria-label="Navegaci&oacute;n de pacientes">
                <ul class="pagination pagination-sm mb-0" id="paginador">
                    <!-- Botones generados din&aacute;micamente -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<script src="js/circuitoquirurgico/gestion_pacientes.js"></script>
