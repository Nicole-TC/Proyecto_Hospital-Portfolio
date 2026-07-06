<div class="col-12 mb-5 px-md-4 px-3 mt-4">

    <!-- ========== TABS DE NAVEGACIÓN ========== -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
        <div class="card-body p-2 bg-light d-flex justify-content-between align-items-center" style="border-radius: 0.75rem;">
            <ul class="nav nav-pills gap-1" id="tabsCatalogos" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4 py-2" id="tab-cirugias-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-cirugias" type="button" role="tab" style="border-radius: 0.5rem;">
                        <i class="bi bi-scissors me-2"></i> Cirugías
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2" id="tab-estudios-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-estudios" type="button" role="tab" style="border-radius: 0.5rem;">
                        <i class="bi bi-file-medical me-2"></i> Estudios Prequirúrgicos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2" id="tab-combos-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-combos" type="button" role="tab" style="border-radius: 0.5rem;">
                        <i class="bi bi-link-45deg me-2"></i> Combos Cirugía-Estudios
                    </button>
                </li>
            </ul>
            <div class="px-3 text-secondary small d-none d-md-block">
                <i class="bi bi-shield-lock me-1"></i> Administración de Catálogos
            </div>
        </div>
    </div>

    <div class="tab-content" id="tabsContent">

        <!-- ==================== TAB CIRUGÍAS ==================== -->
        <div class="tab-pane fade show active" id="tab-cirugias" role="tabpanel">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
                <div>
                    <h2 class="blue fw-bold mb-1">Catálogo de Cirugías</h2>
                    <p class="text-secondary mb-0">Gestione las cirugías registradas en el sistema</p>
                </div>
                <button class="btn btn-success" id="btn-nueva-cirugia">
                    <i class="bi bi-plus-circle fa-bounce white fs-5"></i>
                    <span class="fs-6 text-white ms-1">Nueva Cirugía</span>
                </button>
            </div>

            <!-- Search / Filter Section -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">NOMBRE O DESCRIPCIÓN A BUSCAR</label>
                            <input type="text" class="form-control border-0 bg-light" id="buscar-cirugia"
                                   placeholder="Ingrese nombre o descripción de la cirugía..."
                                   style="border-radius: 0.5rem; height: 45px;"
                                   autocomplete="off"
                                   onkeyup="if(event.key === 'Enter') cargarCirugias(1)">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary w-100 fw-bold" onclick="cargarCirugias(1)"
                                    style="border-radius: 0.5rem; height: 45px; border: none;">
                                <i class="bi bi-search me-1"></i> BUSCAR
                            </button>
                            <button class="btn btn-outline-secondary w-100 fw-bold" onclick="listarTodosCirugias()"
                                    style="border-radius: 0.5rem; height: 45px;">
                                <i class="bi bi-list-ul me-1"></i> TODOS
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-bold">ORDENAR POR</label>
                            <select id="orden-cirugia" class="form-select border-0 bg-light"
                                    style="border-radius: 0.5rem; height: 45px;"
                                    onchange="if(window.cirugiasListadas) cargarCirugias(1)">
                                <option value="nombre_asc" selected>Nombre (A - Z)</option>
                                <option value="nombre_desc">Nombre (Z - A)</option>
                                <option value="id_desc">Últimos agregados</option>
                            </select>
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
                                    <th class="py-3 border-0 text-secondary text-center" style="width:35%;">Nombre</th>
                                    <th class="py-3 border-0 text-secondary text-center" style="width:50%;">Descripción</th>
                                    <th class="py-3 pe-4 border-0 text-secondary text-center" style="width:15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-cirugias-body">
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-secondary">
                                        <i class="bi bi-search me-2"></i>
                                        Ingrese un criterio de búsqueda o haga clic en "TODOS" para listar los registros.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="text-secondary small" id="pag-info-cirugias">Mostrando 0 de 0 registros</div>
                    <nav aria-label="Paginación cirugías">
                        <ul class="pagination pagination-sm mb-0" id="pag-cirugias"></ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- ==================== TAB ESTUDIOS ==================== -->
        <div class="tab-pane fade" id="tab-estudios" role="tabpanel">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
                <div>
                    <h2 class="blue fw-bold mb-1">Estudios Prequirúrgicos</h2>
                    <p class="text-secondary mb-0">Gestione los estudios complementarios registrados en el sistema</p>
                </div>
                <button class="btn btn-success" id="btn-nuevo-estudio">
                    <i class="bi bi-plus-circle fa-bounce white fs-5"></i>
                    <span class="fs-6 text-white ms-1">Nuevo Estudio</span>
                </button>
            </div>

            <!-- Search / Filter Section -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">NOMBRE O DESCRIPCIÓN A BUSCAR</label>
                            <input type="text" class="form-control border-0 bg-light" id="buscar-estudio"
                                   placeholder="Ingrese nombre, descripción u observaciones..."
                                   style="border-radius: 0.5rem; height: 45px;"
                                   autocomplete="off"
                                   onkeyup="if(event.key === 'Enter') cargarEstudios(1)">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary w-100 fw-bold" onclick="cargarEstudios(1)"
                                    style="border-radius: 0.5rem; height: 45px; border: none;">
                                <i class="bi bi-search me-1"></i> BUSCAR
                            </button>
                            <button class="btn btn-outline-secondary w-100 fw-bold" onclick="listarTodosEstudios()"
                                    style="border-radius: 0.5rem; height: 45px;">
                                <i class="bi bi-list-ul me-1"></i> TODOS
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-bold">ORDENAR POR</label>
                            <select id="orden-estudio" class="form-select border-0 bg-light"
                                    style="border-radius: 0.5rem; height: 45px;"
                                    onchange="if(window.estudiosListados) cargarEstudios(1)">
                                <option value="nombre_asc" selected>Nombre (A - Z)</option>
                                <option value="nombre_desc">Nombre (Z - A)</option>
                                <option value="id_desc">Últimos agregados</option>
                            </select>
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
                                    <th class="py-3 border-0 text-secondary text-center" style="width:25%;">Nombre</th>
                                    <th class="py-3 border-0 text-secondary text-center" style="width:30%;">Descripción</th>
                                    <th class="py-3 border-0 text-secondary text-center" style="width:30%;">Observaciones</th>
                                    <th class="py-3 pe-4 border-0 text-secondary text-center" style="width:15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-estudios-body">
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-secondary">
                                        <i class="bi bi-search me-2"></i>
                                        Ingrese un criterio de búsqueda o haga clic en "TODOS" para listar los registros.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="text-secondary small" id="pag-info-estudios">Mostrando 0 de 0 registros</div>
                    <nav aria-label="Paginación estudios">
                        <ul class="pagination pagination-sm mb-0" id="pag-estudios"></ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- ==================== TAB COMBOS ==================== -->
        <div class="tab-pane fade" id="tab-combos" role="tabpanel">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
                <div>
                    <h2 class="blue fw-bold mb-1">Combos Cirugía-Estudios</h2>
                    <p class="text-secondary mb-0">Vincule los estudios requeridos para cada tipo de cirugía</p>
                </div>
                <a href="index.php?modulo=menu_circuitoquirurgico&accion=form_cirugias" class="btn btn-success">
                    <i class="bi bi-plus-circle fa-bounce white fs-5"></i>
                    <span class="fs-6 text-white ms-1">Agregar Combo</span>
                </a>
            </div>

            <!-- Search / Filter Section -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-bold">FILTRAR POR CIRUGÍA</label>
                            <select id="filtro-cirugia-combo" class="form-select border-0 bg-light"
                                    style="border-radius: 0.5rem; height: 45px;"
                                    onchange="if(window.combosListados) cargarCombos(1)">
                                <option value="">— Todas las cirugías —</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-secondary small fw-bold">NOMBRE DE ESTUDIO A BUSCAR</label>
                            <input type="text" id="buscar-combo"
                                   class="form-control border-0 bg-light"
                                   placeholder="Ingrese nombre del estudio..."
                                   style="border-radius: 0.5rem; height: 45px;"
                                   autocomplete="off"
                                   onkeyup="if(event.key === 'Enter') cargarCombos(1)">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button class="btn btn-primary w-100 fw-bold" onclick="cargarCombos(1)"
                                    style="border-radius: 0.5rem; height: 45px; border: none;">
                                <i class="bi bi-search me-1"></i> BUSCAR
                            </button>
                            <button class="btn btn-outline-secondary w-100 fw-bold" onclick="listarTodosCombos()"
                                    style="border-radius: 0.5rem; height: 45px;">
                                <i class="bi bi-list-ul me-1"></i> TODOS
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
                                    <th class="py-3 border-0 text-secondary text-center" style="width: 40%;">Cirugía</th>
                                    <th class="py-3 border-0 text-secondary text-center" style="width: 45%;">Estudios Requeridos</th>
                                    <th class="py-3 pe-4 border-0 text-secondary text-center" style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-combos-body">
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-secondary">
                                        <i class="bi bi-search me-2"></i>
                                        Ingrese un criterio de búsqueda o haga clic en "TODOS" para listar los registros.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="text-secondary small" id="pag-info-combos">Mostrando 0 de 0 registros</div>
                    <nav aria-label="Paginación combos">
                        <ul class="pagination pagination-sm mb-0" id="pag-combos"></ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ========== MODAL CIRUGÍA ========== -->
<div class="modal fade" id="modalCirugia" tabindex="-1" aria-labelledby="modalCirugiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 0.75rem; overflow: hidden;">

            <div class="modal-header border-0 pb-0 modal-header-custom-blue">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px; height:40px; background:#0984e3;">
                        <i class="bi bi-scissors text-white" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="modalCirugiaLabel" style="color:#0984e3;">Nueva Cirugía</h6>
                        <small class="text-secondary" id="modal-cirugia-subtitulo">Complete los datos del catálogo</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-3">
                <input type="hidden" id="mc-id">
                <div class="mb-3">
                    <label for="mc-nombre" class="form-label text-secondary small fw-bold">NOMBRE DE LA CIRUGÍA <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="mc-nombre" placeholder="Ej. APENDICECTOMÍA LAPAROSCÓPICA"
                           style="border-radius: 4px; height:42px; text-transform: uppercase;">
                    <div id="mc-nombre-feedback" class="mt-1" style="font-size: 0.78rem;"></div>
                </div>
                <div class="mb-2">
                    <label for="mc-descripcion" class="form-label text-secondary small fw-bold">DESCRIPCIÓN</label>
                    <textarea class="form-control" id="mc-descripcion" rows="4" placeholder="Breve resumen de la práctica..."
                              style="border-radius: 4px; resize: none;" maxlength="500"
                              oninput="document.getElementById('mc-contador-desc').textContent = this.value.length"></textarea>
                    <div class="text-end text-muted small mt-1" style="font-size:0.75rem;">
                        <span id="mc-contador-desc">0</span>/500 caracteres
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2 justify-content-center">
                <button type="button" class="btn border-0 shadow-sm px-4" data-bs-dismiss="modal"
                        style="background:#e9ecef; color:#555; border-radius:4px; font-size:0.9rem;">
                    Cancelar
                </button>
                <button type="button" class="btn border-0 shadow-sm px-4" id="btn-guardar-cirugia" onclick="guardarCirugia()"
                        style="background:#0984e3; color:#fff; border-radius:4px; font-size:0.9rem;">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ========== MODAL ESTUDIO ========== -->
<div class="modal fade" id="modalEstudio" tabindex="-1" aria-labelledby="modalEstudioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 0.75rem; overflow: hidden;">

            <div class="modal-header border-0 pb-0 modal-header-custom-blue">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px; height:40px; background:#0984e3;">
                        <i class="bi bi-file-medical text-white" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="modalEstudioLabel" style="color:#0984e3;">Nuevo Estudio</h6>
                        <small class="text-secondary" id="modal-estudio-subtitulo">Complete los datos del catálogo</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-3">
                <input type="hidden" id="me-id">
                <div class="mb-3">
                    <label for="me-nombre" class="form-label text-secondary small fw-bold">NOMBRE DEL ESTUDIO <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="me-nombre" placeholder="Ej. ECG CON RIESGO QUIRÚRGICO"
                           style="border-radius: 4px; height:42px; text-transform: uppercase;">
                    <div id="me-nombre-feedback" class="mt-1" style="font-size: 0.78rem;"></div>
                </div>
                <div class="mb-3">
                    <label for="me-descripcion" class="form-label text-secondary small fw-bold">DESCRIPCIÓN / DEFINICIÓN</label>
                    <textarea class="form-control" id="me-descripcion" rows="3" placeholder="Detalles teóricos del estudio..."
                              style="border-radius: 4px; resize: none;" maxlength="300"
                              oninput="document.getElementById('me-contador-desc').textContent = this.value.length"></textarea>
                    <div class="text-end text-muted small mt-1" style="font-size:0.75rem;">
                        <span id="me-contador-desc">0</span>/300 caracteres
                    </div>
                </div>
                <div class="mb-2">
                    <label for="me-observacion" class="form-label text-secondary small fw-bold">REQUISITOS / OBSERVACIONES AL PACIENTE</label>
                    <textarea class="form-control" id="me-observacion" rows="3" placeholder="Ej. Presentarse con 8 hs de ayuno..."
                              style="border-radius: 4px; resize: none;" maxlength="300"
                              oninput="document.getElementById('me-contador-obs').textContent = this.value.length"></textarea>
                    <div class="text-end text-muted small mt-1" style="font-size:0.75rem;">
                        <span id="me-contador-obs">0</span>/300 caracteres
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2 justify-content-center">
                <button type="button" class="btn border-0 shadow-sm px-4" data-bs-dismiss="modal"
                        style="background:#e9ecef; color:#555; border-radius:4px; font-size:0.9rem;">
                    Cancelar
                </button>
                <button type="button" class="btn border-0 shadow-sm px-4" id="btn-guardar-estudio" onclick="guardarEstudio()"
                        style="background:#0984e3; color:#fff; border-radius:4px; font-size:0.9rem;">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ========== MODAL ELIMINAR ========== -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 0.75rem; overflow: hidden;">

            <div class="modal-header border-0 pb-0 modal-header-custom-red">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px; height:40px; background:#ff6b81;">
                        <i class="bi bi-exclamation-triangle text-white" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" style="color:#ff6b81;" id="modalEliminarLabel">Confirmar Eliminación</h6>
                        <small class="text-secondary">Esta acción no se puede deshacer</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-3">
                <p class="text-secondary mb-3" style="font-size:0.9rem;" id="modal-elim-msg">
                    ¿Está seguro que desea eliminar este registro?
                </p>
                <div class="rounded-3 p-3 mb-1 info-box-custom">
                    <div class="row g-2">
                        <div class="col-12">
                            <span class="text-secondary d-block" style="font-size:0.75rem;">Registro a eliminar</span>
                            <strong id="modal-elim-nombre" class="text-dark" style="font-size:0.95rem;">—</strong>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="modal-elim-id">
                <input type="hidden" id="modal-elim-tipo">
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2 justify-content-center">
                <button type="button" class="btn border-0 shadow-sm px-4" data-bs-dismiss="modal"
                        style="background:#e9ecef; color:#555; border-radius:4px; font-size:0.9rem;">
                    Cancelar
                </button>
                <button type="button" class="btn border-0 shadow-sm px-4" id="btn-confirmar-eliminar" onclick="ejecutarEliminar()"
                        style="background:#ff6b81; color:#fff; border-radius:4px; font-size:0.9rem;">
                    <i class="bi bi-trash me-1"></i> Confirmar eliminación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL VER DETALLES ========== -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 0.75rem; overflow: hidden;">
            <div class="modal-header border-0 pb-0 modal-header-custom-blue">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px; height:40px; background:#0984e3;">
                        <i class="bi bi-info-circle text-white" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="modalDetallesLabel" style="color:#0984e3;">Detalles Completos</h6>
                        <small class="text-secondary">Información ampliada del registro</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-3">
                <div id="detalles-contenido">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 justify-content-center">
                <button type="button" class="btn border-0 shadow-sm px-5" data-bs-dismiss="modal"
                        style="background:#0984e3; color:#fff; border-radius:4px; font-size:0.9rem;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Dark Mode - Catálogo Cirugías/Estudios */
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
</style>

<script src="js/circuitoquirurgico/crud_cirugiasEst.js"></script>
