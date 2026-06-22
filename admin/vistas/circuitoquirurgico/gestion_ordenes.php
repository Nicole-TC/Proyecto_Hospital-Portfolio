<div class="col-12 mb-5 px-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4 mt-2">
        <div>
            <h2 class="blue fw-bold mb-1">Órdenes Médicas</h2>
            <p class="text-secondary mb-0">Consulta, cancelación y baja lógica de órdenes del circuito quirúrgico</p>
        </div>
        <a href="index.php?modulo=menu_circuitoquirurgico&accion=admision" class="btn btn-outline-primary fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Nueva orden (Admisión)
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-bold">BUSCAR POR</label>
                    <select class="form-select border-0 bg-light" id="filtroTipo" style="border-radius: 0.5rem; height: 45px;">
                        <option value="todo" selected>Todo (DNI, Nombre, Apellido)</option>
                        <option value="dni">DNI del paciente</option>
                        <option value="apellido">Apellido del paciente</option>
                        <option value="nombre">Nombre del paciente</option>
                        <option value="cirugia">Cirugía</option>
                        <option value="cirujano">Cirujano</option>
                        <option value="id_orden">Nº orden</option>
                        <option value="estado">Estado</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-secondary small fw-bold" id="filtroValorLabel">VALOR A BUSCAR</label>
                    <div class="busqueda-autocomplete" id="filtroValorWrapper">
                        <input type="search" class="form-control border-0 bg-light" id="filtroValor"
                               placeholder="Ingrese el dato..."
                               style="border-radius: 0.5rem; height: 45px;"
                               autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                        <div id="sugerenciasBusqueda" class="sugerencias-busqueda d-none" role="listbox" aria-label="Sugerencias de búsqueda"></div>
                    </div>
                    <select class="form-select border-0 bg-light d-none" id="filtroEstado"
                            style="border-radius: 0.5rem; height: 45px;">
                        <option value="">Seleccione un estado...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100 fw-bold" onclick="ejecutarBusqueda()" style="border-radius: 0.5rem; height: 45px;">
                        <i class="bi bi-search me-2"></i> BUSCAR
                    </button>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-secondary w-100 fw-bold" onclick="listarTodasOrdenes()"
                            style="border-radius: 0.5rem; height: 42px;">
                        <i class="bi bi-list-ul me-2"></i> Ver listado completo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 0.75rem;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary text-center">Nº</th>
                            <th class="py-3 border-0 text-secondary text-center">Fecha</th>
                            <th class="py-3 border-0 text-secondary text-center">Paciente</th>
                            <th class="py-3 border-0 text-secondary text-center">DNI</th>
                            <th class="py-3 border-0 text-secondary text-center">Cirugía</th>
                            <th class="py-3 border-0 text-secondary text-center">Cirujano</th>
                            <th class="py-3 border-0 text-secondary text-center">Estado</th>
                            <th class="py-3 pe-4 text-center border-0 text-secondary" style="min-width: 280px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaOrdenesBody">
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <i class="bi bi-search me-2"></i> Use <strong>BUSCAR</strong> o <strong>Ver listado completo</strong> para cargar las &oacute;rdenes.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="text-secondary small" id="infoPaginacion">Mostrando 0 de 0 registros</div>
            <nav aria-label="Paginación órdenes">
                <ul class="pagination pagination-sm mb-0" id="paginador"></ul>
            </nav>
        </div>
    </div>
</div>

<script src="js/circuitoquirurgico/gestion_ordenes.js?v=<?= @filemtime(__DIR__ . '/../../js/circuitoquirurgico/gestion_ordenes.js') ?: time() ?>"></script>
