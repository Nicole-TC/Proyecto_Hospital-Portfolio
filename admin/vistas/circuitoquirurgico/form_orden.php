<?php
$idOrden = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$urlVolver = 'index.php?modulo=menu_circuitoquirurgico&accion=gestion_ordenes';
?>
<div class="col-12 px-3 pb-5">
    <div class="card shadow-sm bg-white" style="border: 1px solid #e0e0e0; border-radius: 4px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="blue fw-bold mb-0">Ver orden médica</h4>
                <a href="<?= htmlspecialchars($urlVolver) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver al listado
                </a>
            </div>

            <?php if ($idOrden <= 0): ?>
            <div class="alert alert-danger">ID de orden inválido.</div>
            <?php else: ?>
            <div id="formOrden" data-id="<?= $idOrden ?>">
                <div id="formOrdenCargando" class="text-center py-5 text-secondary">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Cargando orden...
                </div>

                <div id="formOrdenContenido" class="d-none">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">Nº orden</label>
                            <input type="text" class="form-control bg-light" id="id_orden_display" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">Estado</label>
                            <input type="text" class="form-control bg-light" id="estado_display" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">Fecha de la orden</label>
                            <input type="text" class="form-control bg-light" id="fecha_orden_display" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">DNI paciente</label>
                            <input type="text" class="form-control bg-light" id="dni_paciente" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label text-secondary small">Paciente</label>
                            <input type="text" class="form-control bg-light" id="nombre_paciente" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small" for="cirugia_input">Cirugía</label>
                            <div class="busqueda-autocomplete" id="cirugiaWrapper">
                                <input type="search" class="form-control" id="cirugia_input"
                                       placeholder="Escriba para buscar..."
                                       autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" disabled>
                                <div id="sugerenciasCirugia" class="sugerencias-busqueda d-none" role="listbox" aria-label="Sugerencias de cirugía"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small" for="cirujano_input">Cirujano</label>
                            <div class="busqueda-autocomplete" id="cirujanoWrapper">
                                <input type="search" class="form-control" id="cirujano_input"
                                       placeholder="Escriba para buscar..."
                                       autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" disabled>
                                <input type="hidden" id="cirujano_id_profesional" value="">
                                <div id="sugerenciasCirujano" class="sugerencias-busqueda d-none" role="listbox" aria-label="Sugerencias de cirujano"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label text-secondary small" for="observaciones">Observaciones</label>
                            <textarea class="form-control" id="observaciones" rows="4" disabled
                                      placeholder="Sin observaciones"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label text-secondary small fw-bold">
                                <i class="bi bi-clock-history me-1"></i> Historial de modificaciones
                            </label>
                            <div id="historialModificaciones" class="historial-modificaciones" aria-live="polite">
                                <p class="text-secondary small mb-0">Sin modificaciones registradas.</p>
                            </div>
                        </div>
                    </div>

                    <div id="alertaSoloLectura" class="alert alert-warning d-none mb-3">
                        <i class="bi bi-lock me-2"></i>
                        Esta orden no puede modificarse en su estado actual.
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" class="btn btn-primary d-none" id="btnGuardarOrden">
                            <i class="bi bi-check-lg me-1"></i> Guardar cambios
                        </button>
                        <a href="<?= htmlspecialchars($urlVolver) ?>" class="btn btn-secondary">Volver al listado</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Clases propias - Ver Orden Médica */
.busqueda-autocomplete {
    position: relative;
    z-index: 1;
}
.busqueda-autocomplete.is-open {
    z-index: 1060;
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
.sugerencias-busqueda .sugerencia-vacia {
    padding: 0.75rem;
    font-size: 0.85rem;
    color: #6c757d;
}
.historial-modificaciones {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    max-height: 280px;
    overflow-y: auto;
}
.historial-modificaciones-item {
    padding: 0.65rem 0;
    border-bottom: 1px solid #e9ecef;
}
.historial-modificaciones-item:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}
.historial-modificaciones-item:first-child {
    padding-top: 0;
}
.historial-modificaciones-fecha {
    font-size: 0.75rem;
    color: #6c757d;
    white-space: nowrap;
}
.historial-modificaciones-cambio {
    line-height: 1.45;
    color: #343a40;
}

/* Dark Mode - Ver Orden Médica */
body.modo-oscuro .card.bg-white {
    background-color: #2a2a2a !important;
    border-color: #444 !important;
}
body.modo-oscuro .bg-light {
    background-color: #1a1a1a !important;
    color: #888 !important;
}
body.modo-oscuro .form-control,
body.modo-oscuro textarea.form-control {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}
body.modo-oscuro .form-control.bg-light {
    background-color: #1a1a1a !important;
    color: #888 !important;
}
body.modo-oscuro .form-control:focus,
body.modo-oscuro textarea.form-control:focus {
    background-color: #333 !important;
    color: #fff !important;
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}
body.modo-oscuro .form-control::placeholder {
    color: #888 !important;
}
body.modo-oscuro .form-label {
    color: #adb5bd !important;
}
body.modo-oscuro .text-secondary {
    color: #adb5bd !important;
}
body.modo-oscuro .historial-modificaciones {
    background: #1e1e1e;
    border-color: #444;
}
body.modo-oscuro .historial-modificaciones-item {
    border-bottom-color: #444;
}
body.modo-oscuro .historial-modificaciones-fecha {
    color: #aaa;
}
body.modo-oscuro .historial-modificaciones-cambio {
    color: #e0e0e0;
}
body.modo-oscuro .sugerencias-busqueda {
    background: #2a2a2a;
    border-color: #444;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}
body.modo-oscuro .sugerencias-busqueda .sugerencias-titulo { color: #aaa; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-item:hover,
body.modo-oscuro .sugerencias-busqueda .sugerencia-item.active { background-color: #3a3a3a; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-nombre { color: #f1f1f1; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-meta  { color: #aaa; }
body.modo-oscuro .sugerencias-busqueda .sugerencia-vacia { color: #aaa; }
body.modo-oscuro .alert-warning {
    background-color: #3d2e00 !important;
    color: #ffd553 !important;
    border-color: #5a4500 !important;
}
</style>

<?php if ($idOrden > 0): ?>
<script src="js/circuitoquirurgico/form_orden.js?v=<?= @filemtime(__DIR__ . '/../../js/circuitoquirurgico/form_orden.js') ?: time() ?>"></script>
<?php endif; ?>
