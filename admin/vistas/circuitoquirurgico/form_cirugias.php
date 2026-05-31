<div class="col-12 px-3 pb-5">
    <!-- ===================== HEADER ===================== -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h2 class="blue fw-bold mb-1">Gestión de Cirugías y Estudios Prequirúrgicos</h2>
            <p class="text-secondary mb-0">Configure los estudios requeridos para cada tipo de cirugía</p>
        </div>
    </div>

    <!-- ===================== INDICADOR DE PASOS ===================== -->
    <div class="d-flex align-items-center gap-0 mb-4" id="indicador-pasos">
        <!-- Paso 1 -->
        <div class="d-flex align-items-center gap-2 paso-indicador" id="ind-paso1">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                 id="circulo-paso1"
                 style="width:34px;height:34px;background:#00cec9;color:#fff;font-size:0.9rem;flex-shrink:0;transition:all 0.3s;">
                1
            </div>
            <span style="font-size:0.9rem;font-weight:600;color:#00cec9;" id="txt-paso1">Datos de la Cirugía</span>
        </div>
        <!-- Separador -->
        <div style="flex:1;height:2px;background:#dee2e6;margin:0 12px;min-width:24px;" id="sep-pasos"></div>
        <!-- Paso 2 -->
        <div class="d-flex align-items-center gap-2 paso-indicador" id="ind-paso2">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                 id="circulo-paso2"
                 style="width:34px;height:34px;background:#dee2e6;color:#6c757d;font-size:0.9rem;flex-shrink:0;transition:all 0.3s;">
                2
            </div>
            <span style="font-size:0.9rem;font-weight:600;color:#6c757d;" id="txt-paso2">Estudios Prequirúrgicos</span>
        </div>
    </div>

    <!-- ===================== PASO 1: DATOS DE LA CIRUGÍA ===================== -->
    <div id="seccion-paso1">
        <div class="card shadow-sm" style="border:1px solid #e0e0e0;border-radius:4px;">
            <div class="card-body p-4">

                <!-- Subtítulo del paso -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="rounded-circle d-flex align-items-center justify-content-center"
                          style="width:28px;height:28px;background:#00cec9;color:#fff;font-size:0.8rem;flex-shrink:0;">
                        <i class="bi bi-1-circle-fill"></i>
                    </span>
                    <h2 class="mb-0 fw-semibold" style="font-size:1.05rem;color:#00cec9;">
                        1. Datos de la Cirugía
                    </h2>
                </div>

                <form id="formCirugia" novalidate>

                    <!-- Nombre de la cirugía -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="nombre_cirugia" class="form-label text-secondary mb-1" style="font-size:0.9rem;">
                                Nombre de la Cirugía <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control text-secondary"
                                id="nombre_cirugia"
                                name="nombre_cirugia"
                                placeholder="Ej: HERNIOPLASTIA INGUINAL"
                                maxlength="150"
                                autocomplete="off"
                                required
                            >
                            <div id="nombre-cirugia-feedback" class="mt-1" style="font-size:0.82rem;"></div>
                            <small class="text-muted d-block mt-1" style="font-size:0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>Solo letras mayúsculas, números, acentos y Ñ. Entre 3 y 150 caracteres.
                            </small>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_cirugia" class="form-label text-secondary mb-1" style="font-size:0.9rem;">
                            Descripción
                        </label>
                        <textarea
                            class="form-control text-secondary"
                            id="descripcion_cirugia"
                            name="descripcion_cirugia"
                            rows="4"
                            maxlength="500"
                            placeholder="Breve descripción del procedimiento quirúrgico..."
                        ></textarea>
                        <div id="descripcion-feedback" class="mt-1" style="font-size:0.82rem;"></div>
                        <div class="d-flex justify-content-end mt-1">
                            <small class="text-muted" style="font-size:0.78rem;">
                                <span id="contador-desc">0</span>/500 caracteres
                            </small>
                        </div>
                    </div>

                    <!-- Botones paso 1 -->
                    <div class="d-flex justify-content-center mt-5 mb-2 gap-3">
                        <a href="<?= $urlVolver ?>" class="btn border-0 shadow-sm"
                           style="background-color:#ff6b81;color:white;padding:8px 30px;font-weight:400;border-radius:4px;">
                            Cancelar
                        </a>
                        <button type="button" id="btn-paso2" class="btn border-0 shadow-sm"
                                style="background-color:#00cec9;color:white;padding:8px 30px;font-weight:400;border-radius:4px;">
                            Continuar &rarr;
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div><!-- /seccion-paso1 -->


</div><!-- /col-12 -->

<script src="js/circuitoquirurgico/form_cirugias.js"></script>
