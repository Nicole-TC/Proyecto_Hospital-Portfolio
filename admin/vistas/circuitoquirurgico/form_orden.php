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

<?php if ($idOrden > 0): ?>
<script src="js/circuitoquirurgico/form_orden.js?v=<?= @filemtime(__DIR__ . '/../../js/circuitoquirurgico/form_orden.js') ?: time() ?>"></script>
<?php endif; ?>
