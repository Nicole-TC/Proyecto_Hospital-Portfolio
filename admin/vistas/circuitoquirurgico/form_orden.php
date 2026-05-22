<?php
$idOrden = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$urlVolver = 'index.php?modulo=menu_circuitoquirurgico&accion=gestion_ordenes';
?>
<div class="col-12 px-3 pb-5">
    <div class="card shadow-sm bg-white" style="border: 1px solid #e0e0e0; border-radius: 4px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="blue fw-bold mb-0">Editar orden médica</h4>
                <a href="<?= htmlspecialchars($urlVolver) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver al listado
                </a>
            </div>

            <?php if ($idOrden <= 0): ?>
            <div class="alert alert-danger">ID de orden inválido.</div>
            <?php else: ?>
            <form id="formOrden" novalidate data-id="<?= $idOrden ?>">
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
                        <label class="form-label text-secondary small">DNI paciente</label>
                        <input type="text" class="form-control bg-light" id="dni_paciente" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary small">Paciente</label>
                        <input type="text" class="form-control bg-light" id="nombre_paciente" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_cirugia_catalogo" class="form-label text-secondary small">Cirugía <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_cirugia_catalogo" name="id_cirugia_catalogo" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="id_profesional" class="form-label text-secondary small">Cirujano <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_profesional" name="id_profesional" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="fecha_orden" class="form-label text-secondary small">Fecha orden <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_orden" name="fecha_orden" required>
                    </div>
                    <div class="col-md-8">
                        <label for="observaciones" class="form-label text-secondary small">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary fw-bold" id="btnGuardar">
                        <i class="bi bi-check-lg me-1"></i> Guardar cambios
                    </button>
                    <a href="<?= htmlspecialchars($urlVolver) ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($idOrden > 0): ?>
<script src="js/circuitoquirurgico/form_orden.js"></script>
<?php endif; ?>
