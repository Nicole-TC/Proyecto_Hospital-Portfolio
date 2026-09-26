<div class="col-12 mb-5 px-md-4 px-3 mt-4 ordenes">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div class="flex-grow-1 min-w-0">
            <h2 class="blue fw-bold mb-1">Órdenes Médicas</h2>
            <p class="text-secondary mb-0">Consulta, cancelación y baja lógica de órdenes del circuito quirúrgico</p>
        </div>
        <a href="index.php?modulo=menu_circuitoquirurgico&accion=admision" class="btn btn-outline-primary fw-bold om-btn-nueva">
            <i class="bi bi-plus-circle me-2"></i>Nueva orden (Admisión)
        </a>
    </div>

    <div class="card border-0 mb-4 om-card">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label text-secondary small fw-bold" for="filtroTipo">BUSCAR POR</label>
                    <select class="form-select border-0 bg-light om-select" id="filtroTipo">
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
                <div class="col-12 col-md-6 col-xl-5">
                    <label class="form-label text-secondary small fw-bold" id="filtroValorLabel" for="filtroValor">VALOR A BUSCAR</label>
                    <div class="busqueda-autocomplete" id="filtroValorWrapper">
                        <input type="search" class="form-control border-0 bg-light om-control" id="filtroValor"
                               placeholder="Ingrese el dato..."
                               autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                        <div id="sugerenciasBusqueda" class="sugerencias-busqueda d-none" role="listbox" aria-label="Sugerencias de búsqueda"></div>
                    </div>
                    <select class="form-select border-0 bg-light om-select d-none" id="filtroEstado">
                        <option value="">Seleccione un estado...</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label text-secondary small fw-bold d-none d-xl-block">&nbsp;</label>
                    <button type="button" class="btn btn-primary w-100 fw-bold om-btn" onclick="ejecutarBusqueda()">
                        <i class="bi bi-search me-2"></i> BUSCAR
                    </button>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <button type="button" class="btn btn-outline-secondary w-100 fw-bold om-btn" onclick="listarTodasOrdenes()">
                        <i class="bi bi-list-ul me-2"></i> Ver listado completo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 om-card om-card-tabla">
        <div class="card-body p-0">
            <div class="om-tabla-scroll">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th class="om-th border-0 text-center">Nº</th>
                            <th class="om-th border-0 text-center">Fecha</th>
                            <th class="om-th border-0 text-center">Paciente</th>
                            <th class="om-th border-0 text-center">DNI</th>
                            <th class="om-th border-0 text-center">Cirugía</th>
                            <th class="om-th border-0 text-center">Cirujano</th>
                            <th class="om-th border-0 text-center">Estado</th>
                            <th class="om-th om-th-acciones border-0 text-center">Acciones</th>
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
        <div class="card-footer border-0 py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2 om-footer">
            <div class="text-secondary small" id="infoPaginacion">Mostrando 0 de 0 registros</div>
            <nav aria-label="Paginación órdenes">
                <ul class="pagination mb-0" id="paginador"></ul>
            </nav>
        </div>
    </div>
</div>

<style>
/* Tarjetas, campos y botones del filtro */
.ordenes .om-card {
    border-radius: 0.75rem;
    box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
}
.ordenes .om-card-tabla {
    overflow: hidden;
}
.ordenes .om-control,
.ordenes .om-select,
.ordenes .om-btn {
    border-radius: 0.5rem;
    height: 45px;
    min-height: 45px;
}
.ordenes .om-btn-nueva {
    border-radius: 0.5rem;
    height: 45px;
    display: inline-flex;
    align-items: center;
}

/* Lista desplegable de sugerencias */
.ordenes .busqueda-autocomplete {
    position: relative;
    z-index: 1;
}
.ordenes .busqueda-autocomplete.is-open {
    z-index: 1060;
}
.ordenes .card-body .row:has(.busqueda-autocomplete.is-open) {
    overflow: visible;
}
.ordenes .sugerencias-busqueda {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0 0 0.5rem 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    max-height: 240px;
    overflow-y: auto;
    padding: 0.25rem 0;
}
.ordenes .sugerencias-busqueda .sugerencias-titulo {
    padding: 0.35rem 0.75rem 0.25rem;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #6c757d;
}
.ordenes .sugerencias-busqueda .sugerencia-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 0;
    background: transparent;
    text-align: left;
    cursor: pointer;
}
.ordenes .sugerencias-busqueda .sugerencia-item:hover,
.ordenes .sugerencias-busqueda .sugerencia-item.active {
    background-color: #f8f9fa;
}
.ordenes .sugerencias-busqueda .sugerencia-nombre {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #212529;
}
.ordenes .sugerencias-busqueda .sugerencia-meta {
    display: block;
    font-size: 0.78rem;
    color: #6c757d;
}
.ordenes .sugerencias-busqueda .sugerencia-match {
    padding: 0;
    background: rgba(13, 110, 253, 0.15);
    color: inherit;
    border-radius: 0.15rem;
}
.ordenes .sugerencias-busqueda .sugerencia-vacia {
    padding: 0.75rem;
    font-size: 0.85rem;
    color: #6c757d;
}

/* Desplazamiento horizontal, encabezados y celdas de la tabla */
.ordenes .om-tabla-scroll {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.ordenes .om-tabla-scroll table {
    width: 100% !important;
    min-width: 720px;
    margin: 0;
}
.ordenes .om-th {
    font-size: 0.9rem;
    font-weight: 700;
    border-top: none !important;
    border-bottom-width: 2px !important;
    padding: 0.85rem 1rem !important;
    vertical-align: middle;
    white-space: nowrap;
}
.ordenes .om-th-acciones,
.ordenes #tablaOrdenesBody td:last-child {
    width: 8.75rem;
}
.ordenes #tablaOrdenesBody td {
    font-size: 0.9rem;
    line-height: 1.45;
    padding: 0.85rem 1rem !important;
    vertical-align: middle;
}
.ordenes #tablaOrdenesBody td:nth-child(1),
.ordenes #tablaOrdenesBody td:nth-child(2),
.ordenes #tablaOrdenesBody td:nth-child(4) {
    white-space: nowrap;
}
.ordenes #tablaOrdenesBody tr:last-child td {
    border-bottom: none !important;
}

.ordenes .om-footer {
    background: transparent;
    border-top: 1px solid #eef1f4 !important;
}
.ordenes #paginador {
    flex-wrap: wrap;
    gap: 0.25rem;
}
.ordenes #paginador .page-link {
    border-radius: 0.5rem;
    font-weight: 600;
    min-width: 2.5rem;
    text-align: center;
    padding: 0.45rem 0.75rem;
}

.ordenes .acciones-orden {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: center;
    gap: 0.35rem;
    width: 100%;
    min-width: 7.5rem;
    margin-inline: auto;
}
@media (min-width: 1600px) {
    .ordenes .om-th-acciones,
    .ordenes #tablaOrdenesBody td:last-child {
        width: auto;
    }
    .ordenes .acciones-orden {
        flex-direction: row;
        flex-wrap: nowrap;
        width: max-content;
    }
}
.ordenes .btn-accion-orden {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    gap: 0.35rem;
    padding: 0.38rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.12s ease;
}
.ordenes .btn-accion-orden i { font-size: 0.9rem; line-height: 1; }
.ordenes .btn-accion-orden:hover:not(:disabled) { transform: translateY(-1px); }
.ordenes .btn-accion-orden:active:not(:disabled) { transform: translateY(0); }
.ordenes .btn-accion-orden--ver { background: rgba(0, 206, 201, 0.14); color: #00948f; }
.ordenes .btn-accion-orden--ver:hover:not(:disabled) { background: #00cec9; color: #fff; box-shadow: 0 3px 10px rgba(0, 206, 201, 0.35); }
.ordenes .btn-accion-orden--agendar { background: linear-gradient(135deg, #00cec9 0%, #00b4d8 100%); color: #fff; }
.ordenes .btn-accion-orden--agendar:hover:not(:disabled) { box-shadow: 0 3px 12px rgba(0, 180, 216, 0.4); filter: brightness(1.05); }
.ordenes .btn-accion-orden--cancelar { background: rgba(255, 171, 64, 0.14); color: #c77700; }
.ordenes .btn-accion-orden--cancelar:hover:not(:disabled) { background: #ffab40; color: #fff; box-shadow: 0 3px 10px rgba(255, 171, 64, 0.35); }
.ordenes .btn-accion-orden--eliminar { background: rgba(255, 107, 129, 0.12); color: #d63356; }
.ordenes .btn-accion-orden--eliminar:hover:not(:disabled) { background: #ff6b81; color: #fff; box-shadow: 0 3px 10px rgba(255, 107, 129, 0.35); }
.ordenes .btn-accion-orden:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }
@media (min-width: 1600px) {
    .ordenes .btn-accion-orden { width: auto; }
}

.ordenes .badge-estado-orden {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 6rem;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.42em 0.9em;
    border-radius: 999px;
    letter-spacing: 0.01em;
    line-height: 1.2;
    white-space: nowrap;
}
.ordenes .badge-estado-orden--pend { background: #fff3cd; color: #856404; }
.ordenes .badge-estado-orden--proc { background: rgba(13, 110, 253, 0.12); color: #0a58ca; }
.ordenes .badge-estado-orden--fin { background: rgba(25, 135, 84, 0.12); color: #146c43; }
.ordenes .badge-estado-orden--canc { background: #e9ecef; color: #495057; }
.ordenes .badge-estado-orden--rech,
.ordenes .badge-estado-orden--elim { background: rgba(220, 53, 69, 0.1); color: #b02a37; }
.ordenes .badge-estado-orden--default { background: #f1f3f5; color: #495057; }

/* Colores en modo oscuro: filtros, sugerencias, estados, acciones y paginación */
body.modo-oscuro .ordenes .form-control.bg-light,
body.modo-oscuro .ordenes .form-select.bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
    border: 1px solid #444 !important;
}
body.modo-oscuro .ordenes .om-footer {
    border-top-color: #444 !important;
}
body.modo-oscuro .ordenes .sugerencias-busqueda {
    background: #2a2a2a;
    border-color: #444;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}
body.modo-oscuro .ordenes .sugerencias-busqueda .sugerencias-titulo,
body.modo-oscuro .ordenes .sugerencias-busqueda .sugerencia-meta,
body.modo-oscuro .ordenes .sugerencias-busqueda .sugerencia-vacia { color: #aaa; }
body.modo-oscuro .ordenes .sugerencias-busqueda .sugerencia-item:hover,
body.modo-oscuro .ordenes .sugerencias-busqueda .sugerencia-item.active { background-color: #3a3a3a; }
body.modo-oscuro .ordenes .sugerencias-busqueda .sugerencia-nombre { color: #f1f1f1; }
body.modo-oscuro .ordenes .badge-estado-orden--pend { background: rgba(255, 193, 7, 0.15); color: #ffd553; }
body.modo-oscuro .ordenes .badge-estado-orden--proc { background: rgba(78, 170, 255, 0.15); color: #4eaaff; }
body.modo-oscuro .ordenes .badge-estado-orden--fin { background: rgba(107, 216, 141, 0.15); color: #6bd88d; }
body.modo-oscuro .ordenes .badge-estado-orden--canc,
body.modo-oscuro .ordenes .badge-estado-orden--default { background: #2a2a2a; color: #aaa; }
body.modo-oscuro .ordenes .badge-estado-orden--rech,
body.modo-oscuro .ordenes .badge-estado-orden--elim { background: rgba(255, 107, 107, 0.12); color: #ff6b6b; }
body.modo-oscuro .ordenes .btn-accion-orden--ver { background: rgba(0, 206, 201, 0.18); color: #00cec9; }
body.modo-oscuro .ordenes .btn-accion-orden--ver:hover:not(:disabled) { background: #00cec9; color: #fff; }
body.modo-oscuro .ordenes .btn-accion-orden--cancelar { background: rgba(255, 171, 64, 0.18); color: #ffab40; }
body.modo-oscuro .ordenes .btn-accion-orden--cancelar:hover:not(:disabled) { background: #ffab40; color: #1a1a1a; }
body.modo-oscuro .ordenes .btn-accion-orden--eliminar { background: rgba(255, 107, 107, 0.15); color: #ff6b6b; }
body.modo-oscuro .ordenes .btn-accion-orden--eliminar:hover:not(:disabled) { background: #ff6b6b; color: #fff; }
body.modo-oscuro .ordenes #paginador .page-link {
    background-color: #1f1f1f;
    color: #ccc;
    border-color: #444;
}
body.modo-oscuro .ordenes #paginador .page-item.active .page-link {
    background-color: #375a7f;
    border-color: #375a7f;
    color: #fff;
}
body.modo-oscuro .ordenes #paginador .page-item.disabled .page-link {
    background-color: #1f1f1f;
    color: #666;
    border-color: #444;
}
body.modo-oscuro .ordenes #paginador .page-link:hover {
    background-color: #333;
    color: #fff;
    border-color: #666;
}
</style>

<script src="js/circuitoquirurgico/gestion_ordenes.js?v=<?= @filemtime(__DIR__ . '/../../js/circuitoquirurgico/gestion_ordenes.js') ?: time() ?>"></script>
