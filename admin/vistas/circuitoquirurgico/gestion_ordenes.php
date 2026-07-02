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

<style>
/* Clases propias - Órdenes Médicas */
.busqueda-autocomplete {
    position: relative;
    z-index: 1;
}
.busqueda-autocomplete.is-open {
    z-index: 1060;
}
.card-body .row:has(.busqueda-autocomplete.is-open) {
    overflow: visible;
}
.sugerencias-busqueda {
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
.sugerencias-busqueda .sugerencias-titulo {
    padding: 0.35rem 0.75rem 0.25rem;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #6c757d;
}
.sugerencias-busqueda .sugerencia-item {
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
.sugerencias-busqueda .sugerencia-item:hover,
.sugerencias-busqueda .sugerencia-item.active {
    background-color: #f8f9fa;
}
.sugerencias-busqueda .sugerencia-nombre {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #212529;
}
.sugerencias-busqueda .sugerencia-meta {
    display: block;
    font-size: 0.78rem;
    color: #6c757d;
}
.sugerencias-busqueda .sugerencia-match {
    padding: 0;
    background: rgba(13, 110, 253, 0.15);
    color: inherit;
    border-radius: 0.15rem;
}
.sugerencias-busqueda .sugerencia-vacia {
    padding: 0.75rem;
    font-size: 0.85rem;
    color: #6c757d;
}
.acciones-orden {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.btn-accion-orden {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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
.btn-accion-orden i { font-size: 0.9rem; line-height: 1; }
.btn-accion-orden:hover:not(:disabled) { transform: translateY(-1px); }
.btn-accion-orden:active:not(:disabled) { transform: translateY(0); }
.btn-accion-orden--ver     { background: rgba(0,206,201,0.14); color: #00948f; }
.btn-accion-orden--ver:hover:not(:disabled) { background: #00cec9; color: #fff; box-shadow: 0 3px 10px rgba(0,206,201,0.35); }
.btn-accion-orden--agendar { background: linear-gradient(135deg,#00cec9 0%,#00b4d8 100%); color: #fff; }
.btn-accion-orden--agendar:hover:not(:disabled) { box-shadow: 0 3px 12px rgba(0,180,216,0.4); filter: brightness(1.05); }
.btn-accion-orden--cancelar { background: rgba(255,171,64,0.14); color: #c77700; }
.btn-accion-orden--cancelar:hover:not(:disabled) { background: #ffab40; color: #fff; box-shadow: 0 3px 10px rgba(255,171,64,0.35); }
.btn-accion-orden--eliminar { background: rgba(255,107,129,0.12); color: #d63356; }
.btn-accion-orden--eliminar:hover:not(:disabled) { background: #ff6b81; color: #fff; box-shadow: 0 3px 10px rgba(255,107,129,0.35); }
.btn-accion-orden:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }
.badge-estado-orden {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.42em 0.9em;
    border-radius: 999px;
    letter-spacing: 0.01em;
    line-height: 1.2;
}
.badge-estado-orden--pend { background: #fff3cd; color: #856404; }
.badge-estado-orden--proc { background: rgba(13,110,253,0.12); color: #0a58ca; }
.badge-estado-orden--fin  { background: rgba(25,135,84,0.12); color: #146c43; }
.badge-estado-orden--canc { background: #e9ecef; color: #495057; }
.badge-estado-orden--rech,
.badge-estado-orden--elim { background: rgba(220,53,69,0.1); color: #b02a37; }
.badge-estado-orden--default { background: #f1f3f5; color: #495057; }

/* === Dark Mode — Gestión de Órdenes === */
body.modo-oscuro .bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
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
body.modo-oscuro .form-control.border-0,
body.modo-oscuro .form-select.border-0 {
    border: 1px solid #444 !important;
}
body.modo-oscuro .form-control:focus,
body.modo-oscuro .form-select:focus {
    background-color: #333 !important;
    color: #fff !important;
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25) !important;
}
body.modo-oscuro .form-control::placeholder {
    color: #888 !important;
}
body.modo-oscuro .text-secondary {
    color: #adb5bd !important;
}
body.modo-oscuro .table-hover tbody tr:hover {
    background-color: #333 !important;
    color: #fff !important;
}
body.modo-oscuro thead.bg-light th {
    background-color: #333 !important;
    color: #adb5bd !important;
}
body.modo-oscuro .sugerencias-busqueda {
    background: #2a2a2a;
    border-color: #444;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}
body.modo-oscuro .sugerencias-busqueda .sugerencias-titulo { color: #aaa; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-item:hover,
body.modo-oscuro .sugerencias-busqueda .sugerencia-item.active { background-color: #3a3a3a; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-nombre { color: #f1f1f1; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-meta  { color: #aaa; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-vacia { color: #aaa; }
body.modo-oscuro .badge-estado-orden--pend { background: rgba(255,193,7,0.15);   color: #ffd553; }
body.modo-oscuro .badge-estado-orden--proc { background: rgba(78,170,255,0.15);  color: #4eaaff; }
body.modo-oscuro .badge-estado-orden--fin  { background: rgba(107,216,141,0.15); color: #6bd88d; }
body.modo-oscuro .badge-estado-orden--canc { background: #2a2a2a; color: #aaa; }
body.modo-oscuro .badge-estado-orden--rech,
body.modo-oscuro .badge-estado-orden--elim { background: rgba(255,107,107,0.12); color: #ff6b6b; }
body.modo-oscuro .badge-estado-orden--default { background: #2a2a2a; color: #aaa; }
body.modo-oscuro .btn-accion-orden--ver     { background: rgba(0,206,201,0.18); color: #00cec9; }
body.modo-oscuro .btn-accion-orden--ver:hover:not(:disabled)     { background: #00cec9; color: #fff; }
body.modo-oscuro .btn-accion-orden--cancelar { background: rgba(255,171,64,0.18); color: #ffab40; }
body.modo-oscuro .btn-accion-orden--cancelar:hover:not(:disabled) { background: #ffab40; color: #1a1a1a; }
body.modo-oscuro .btn-accion-orden--eliminar { background: rgba(255,107,107,0.15); color: #ff6b6b; }
body.modo-oscuro .btn-accion-orden--eliminar:hover:not(:disabled) { background: #ff6b6b; color: #fff; }
</style>

<script src="js/circuitoquirurgico/gestion_ordenes.js?v=<?= @filemtime(__DIR__ . '/../../js/circuitoquirurgico/gestion_ordenes.js') ?: time() ?>"></script>
