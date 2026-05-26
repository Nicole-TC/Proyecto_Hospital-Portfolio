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
                        <label class="form-label text-secondary small">Cirugía</label>
                        <input type="text" class="form-control bg-light" id="cirugia_display" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Cirujano</label>
                        <input type="text" class="form-control bg-light" id="cirujano_display" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label text-secondary small">Observaciones</label>
                        <textarea class="form-control bg-light" id="observaciones" rows="4" readonly></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="<?= htmlspecialchars($urlVolver) ?>" class="btn btn-secondary">Volver al listado</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($idOrden > 0): ?>
<script src="js/circuitoquirurgico/form_orden.js"></script>
<?php endif; ?>
