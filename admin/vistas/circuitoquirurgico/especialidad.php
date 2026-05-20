<div class="col-12 mb-5 px-3">

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h2 class="blue fw-bold mb-1">Especialidades y Subespecialidades</h2>
            <p class="text-secondary mb-0">Gestión del catálogo de especialidades médicas del hospital</p>
        </div>
        <button class="btn btn-success" id="btn-nueva-especialidad" onclick="abrirModalNuevo()">
            <i class="fa-solid fa-stethoscope fa-bounce white fs-5"></i>
            <span class="fs-6 text-white ms-1">Nueva Especialidad</span>
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-journal-medical mb-2 blue" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-total">0</h3>
                    <p class="text-secondary small mb-0">Total Registros</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-layers mb-2 success" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-especialidades">0</h3>
                    <p class="text-secondary small mb-0">Especialidades</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 0.75rem;">
                <div class="card-body">
                    <i class="bi bi-diagram-3 mb-2 info" style="font-size: 1.5rem;"></i>
                    <h3 class="fw-bold mb-0" id="stat-subespecialidades">0</h3>
                    <p class="text-secondary small mb-0">Subespecialidades</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search / Filter Section -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-bold">TIPO</label>
                    <select class="form-select border-0 bg-light" id="filtroTipo" style="border-radius: 0.5rem; height: 45px;" onchange="actualizarPlaceholder()">
                        <option value="especialidad">Especialidad</option>
                        <option value="subespecialidad">Subespecialidad</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">NOMBRE A BUSCAR</label>
                    <input type="text" class="form-control border-0 bg-light" id="filtroNombre"
                           placeholder="Ingrese nombre de especialidad..."
                           style="border-radius: 0.5rem; height: 45px;"
                           onkeyup="if(event.key === 'Enter') ejecutarBusqueda()">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100 fw-bold" onclick="ejecutarBusqueda()" style="border-radius: 0.5rem; height: 45px; border: none;">
                        <i class="bi bi-search me-2"></i> BUSCAR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0" style="border-radius: 0.75rem;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary text-center" style="width:60px;">#</th>
                            <th class="py-3 border-0 text-secondary">Nombre</th>
                            <th class="py-3 border-0 text-secondary text-center">Tipo</th>
                            <th class="py-3 border-0 text-secondary text-center">Especialidad Padre</th>
                            <th class="py-3 pe-4 border-0 text-secondary text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEspecialidadesBody">
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary">
                                <i class="bi bi-search me-2"></i>
                                Ingrese un criterio de búsqueda para listar los registros.
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
            <nav aria-label="Paginación especialidades">
                <ul class="pagination pagination-sm mb-0" id="paginador">
                    <!-- Botones generados dinámicamente -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- ========== MODAL NUEVO / EDITAR ========== -->
<div class="modal fade" id="modalEspecialidad" tabindex="-1" aria-labelledby="modalEspLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 0.75rem; overflow: hidden;">

            <!-- Cabecera -->
            <div class="modal-header border-0 pb-0" style="background: #eaf4ff;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#0984e3;">
                        <i class="bi bi-stethoscope text-white" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="modalEspLabel" style="color:#0984e3;">
                            Nueva Especialidad
                        </h6>
                        <small class="text-secondary" id="modalEspSubtitulo">Complete los datos del registro</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Cuerpo -->
            <div class="modal-body px-4 pt-3 pb-2">

                <!-- Pregunta tipo -->
                <div id="bloqueTipo" class="mb-3">
                    <label class="form-label text-secondary small fw-bold">¿QUÉ DESEA AGREGAR?</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipoRegistro" id="radioEspecialidad"
                                   value="especialidad" checked onchange="cambiarTipoRegistro()">
                            <label class="form-check-label" for="radioEspecialidad">
                                <i class="bi bi-layers me-1 success"></i> Especialidad
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipoRegistro" id="radioSubespecialidad"
                                   value="subespecialidad" onchange="cambiarTipoRegistro()">
                            <label class="form-check-label" for="radioSubespecialidad">
                                <i class="bi bi-diagram-3 me-1 info"></i> Subespecialidad
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Nombre -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold" id="labelNombreModal">NOMBRE DE LA ESPECIALIDAD</label>
                    <input type="text" class="form-control border-0 bg-light" id="modalNombre"
                           placeholder="Ej: Cardiología" style="border-radius: 0.5rem;">
                    <div class="invalid-feedback" id="errorNombre"></div>
                </div>

                <!-- Especialidad padre (solo subespecialidad) -->
                <div class="mb-3" id="bloqueEspecialidadPadre" style="display:none;">
                    <label class="form-label text-secondary small fw-bold">ESPECIALIDAD PADRE</label>
                    <select class="form-select border-0 bg-light" id="modalEspecialidadPadre" style="border-radius: 0.5rem;">
                        <option value="">Seleccione la especialidad padre...</option>
                    </select>
                    <div class="invalid-feedback" id="errorPadre"></div>
                </div>

                <!-- Input oculto para modo edición -->
                <input type="hidden" id="modalId" value="">
                <input type="hidden" id="modalModo" value="crear">
            </div>

            <!-- Pie -->
            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2 justify-content-center">
                <button type="button" class="btn border-0 shadow-sm px-4"
                        data-bs-dismiss="modal"
                        style="background:#e9ecef; color:#555; border-radius:4px; font-size:0.9rem;">
                    Cancelar
                </button>
                <button type="button" class="btn border-0 shadow-sm px-4" id="btn-guardar-especialidad"
                        onclick="guardarEspecialidad()"
                        style="background:#0984e3; color:#fff; border-radius:4px; font-size:0.9rem;">
                    <i class="bi bi-floppy me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL CONFIRMAR ELIMINACIÓN ========== -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalElimLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 0.75rem; overflow: hidden;">

            <div class="modal-header border-0 pb-0" style="background: #fff3f5;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#ff6b81;">
                        <i class="bi bi-trash-fill text-white" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="modalElimLabel" style="color:#c0392b;">
                            Eliminar registro
                        </h6>
                        <small class="text-secondary">Esta acción no se puede deshacer</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-2">
                <p class="text-secondary mb-3" style="font-size:0.9rem;">
                    ¿Estás seguro que deseas eliminar el siguiente registro?
                </p>
                <div class="rounded-3 p-3 mb-1" style="background:#f8f9fa; border:1px solid #e9ecef;">
                    <div class="row g-2">
                        <div class="col-12">
                            <span class="text-secondary d-block" style="font-size:0.75rem;">Nombre</span>
                            <strong id="modal-elim-nombre" class="text-dark" style="font-size:0.95rem;">—</strong>
                        </div>
                        <div class="col-12 mt-1">
                            <span class="text-secondary d-block" style="font-size:0.75rem;">Tipo</span>
                            <strong id="modal-elim-tipo" class="text-dark" style="font-size:0.95rem;">—</strong>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="modal-elim-id" value="">
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2 justify-content-center">
                <button type="button" class="btn border-0 shadow-sm px-4"
                        data-bs-dismiss="modal"
                        style="background:#e9ecef; color:#555; border-radius:4px; font-size:0.9rem;">
                    Cancelar
                </button>
                <button type="button" class="btn border-0 shadow-sm px-4" id="btn-confirmar-eliminar"
                        onclick="confirmarEliminar()"
                        style="background:#ff6b81; color:#fff; border-radius:4px; font-size:0.9rem;">
                    <i class="bi bi-trash me-1"></i> Confirmar eliminación
                </button>
            </div>
        </div>
    </div>
</div>

<script src="js/circuitoquirurgico/especialidad.js"></script>
