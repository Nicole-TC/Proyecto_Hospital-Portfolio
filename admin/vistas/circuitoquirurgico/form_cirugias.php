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
                        <a href="index.php?modulo=menu_circuitoquirurgico&accion=gestion_cirugias" class="btn border-0 shadow-sm"
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


    <!-- ===================== PASO 2: ESTUDIOS PREQUIRÚRGICOS ===================== -->
    <div id="seccion-paso2" style="display:none;">
        <div class="card shadow-sm" style="border:1px solid #e0e0e0;border-radius:4px;">
            <div class="card-body p-4">

                <!-- Subtítulo del paso + badge contador -->
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-flex align-items-center justify-content-center"
                              style="width:28px;height:28px;background:#00cec9;color:#fff;font-size:0.8rem;flex-shrink:0;">
                            <i class="bi bi-2-circle-fill"></i>
                        </span>
                        <h2 class="mb-0 fw-semibold" style="font-size:1.05rem;color:#00cec9;">
                            2. Estudios Prequirúrgicos
                        </h2>
                    </div>
                    <span id="badge-seleccionados"
                          class="badge rounded-pill"
                          style="background:#00cec9;font-size:0.8rem;display:none;">
                        0 seleccionados
                    </span>
                </div>

                <!-- Descripción dinámica -->
                <p class="text-secondary mb-3" style="font-size:0.9rem;">
                    Seleccione los estudios requeridos para
                    <strong id="nombre-cirugia-display">la cirugía</strong>.
                </p>

                <!-- Buscador de estudios -->
                <div class="input-group mb-3" style="max-width:400px;">
                    <span class="input-group-text bg-light border-0" style="border-radius:6px 0 0 6px;">
                        <i class="bi bi-search text-secondary"></i>
                    </span>
                    <input
                        type="text"
                        id="buscador-estudios"
                        class="form-control border-0 bg-light"
                        placeholder="Filtrar estudios..."
                        autocomplete="off"
                        style="border-radius:0 6px 6px 0;"
                    >
                </div>

                <!-- Lista de estudios con checkboxes -->
                <div id="lista-estudios" style="max-height:360px;overflow-y:auto;border:1px solid #e9ecef;border-radius:8px;">
                    <!-- Items generados dinámicamente por JS -->
                    <div class="text-center py-4 text-secondary" id="msg-sin-estudios" style="display:none;">
                        <i class="bi bi-search me-2"></i>No se encontraron estudios con ese criterio.
                    </div>
                </div>

                <!-- Botón Agregar nuevo estudio -->
                <button type="button"
                        id="btn-abrir-nuevo-estudio"
                        class="btn w-100 mt-3 border d-flex align-items-center justify-content-center gap-2"
                        style="border-color:#0984e3!important;color:#0984e3;background:transparent;border-radius:8px;padding:12px;font-size:0.95rem;transition:all 0.2s;">
                    <i class="bi bi-plus-circle" style="font-size:1.1rem;"></i>
                    Agregar Nuevo Estudio
                </button>

                <!-- ======= FORMULARIO INLINE: Nuevo estudio prequirúrgico ======= -->
                <div id="panel-nuevo-estudio"
                     style="display:none;margin-top:12px;border:2px solid #0984e3;border-radius:8px;overflow:hidden;">

                    <!-- Header del panel inline -->
                    <div class="d-flex align-items-center justify-content-between px-3 py-2"
                         style="background:#eaf4ff;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-plus-square-fill" style="color:#0984e3;font-size:1rem;"></i>
                            <span class="fw-semibold" style="font-size:0.93rem;color:#0984e3;">
                                Nuevo Estudio Prequirúrgico
                            </span>
                        </div>
                        <button type="button"
                                id="btn-cerrar-nuevo-estudio"
                                class="btn btn-sm p-0 border-0"
                                style="color:#6c757d;line-height:1;"
                                title="Cerrar">
                            <i class="bi bi-x-lg" style="font-size:0.9rem;"></i>
                        </button>
                    </div>

                    <div class="p-3" style="background:#fff;">
                        <form id="formNuevoEstudio" novalidate>

                            <!-- Nombre del estudio -->
                            <div class="mb-3">
                                <label for="nuevo-nombre-estudio" class="form-label text-secondary mb-1" style="font-size:0.87rem;">
                                    Nombre del Estudio <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control text-secondary"
                                    id="nuevo-nombre-estudio"
                                    name="nuevo_nombre_estudio"
                                    placeholder="Ej: GLUCEMIA EN AYUNAS"
                                    maxlength="150"
                                    autocomplete="off"
                                >
                                <div id="nuevo-nombre-feedback" class="mt-1" style="font-size:0.79rem;"></div>
                                <small class="text-muted d-block mt-1" style="font-size:0.77rem;">
                                    <i class="bi bi-info-circle me-1"></i>Solo letras mayúsculas, números, acentos y Ñ. Entre 3 y 150 caracteres.
                                </small>
                            </div>

                            <!-- Descripción del estudio -->
                            <div class="mb-3">
                                <label for="nuevo-descripcion-estudio" class="form-label text-secondary mb-1" style="font-size:0.87rem;">
                                    Descripción
                                </label>
                                <textarea
                                    class="form-control text-secondary"
                                    id="nuevo-descripcion-estudio"
                                    name="nuevo_descripcion_estudio"
                                    rows="2"
                                    maxlength="300"
                                    placeholder="Breve descripción del estudio"
                                ></textarea>
                                <div class="d-flex justify-content-end">
                                    <small class="text-muted" style="font-size:0.76rem;">
                                        <span id="contador-nuevo-desc">0</span>/300
                                    </small>
                                </div>
                            </div>

                            <!-- Observaciones (Requisitos) -->
                            <div class="mb-3">
                                <label for="nuevo-obs-estudio" class="form-label text-secondary mb-1" style="font-size:0.87rem;">
                                    Observaciones / Requisitos
                                </label>
                                <textarea
                                    class="form-control text-secondary"
                                    id="nuevo-obs-estudio"
                                    name="nuevo_obs_estudio"
                                    rows="2"
                                    maxlength="300"
                                    placeholder="Ej: Ayuno de 12 horas, no tomar medicamentos"
                                ></textarea>
                                <div id="nuevo-obs-feedback" class="mt-1" style="font-size:0.79rem;"></div>
                                <div class="d-flex justify-content-end">
                                    <small class="text-muted" style="font-size:0.76rem;">
                                        <span id="contador-nuevo-obs">0</span>/300
                                    </small>
                                </div>
                            </div>

                            <!-- Botones del nuevo estudio -->
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button"
                                        id="btn-cancelar-nuevo-estudio"
                                        class="btn border-0 shadow-sm"
                                        style="background:#e9ecef;color:#555;border-radius:4px;font-size:0.87rem;padding:6px 20px;">
                                    Cancelar
                                </button>
                                <button type="button"
                                        id="btn-agregar-nuevo-estudio"
                                        class="btn border-0 shadow-sm d-flex align-items-center gap-2"
                                        style="background:#00cec9;color:#fff;border-radius:4px;font-size:0.87rem;padding:6px 20px;">
                                    <i class="bi bi-plus-circle"></i> Agregar Estudio
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                <!-- /panel-nuevo-estudio -->

                <!-- Botón Guardar vinculación -->
                <div class="mt-4">
                    <button type="button"
                            id="btn-guardar-vinculacion"
                            class="btn w-100 border-0 shadow-sm d-flex align-items-center justify-content-center gap-2"
                            style="background:#00cec9;color:#fff;border-radius:4px;padding:14px;font-size:1rem;font-weight:500;opacity:0.6;cursor:not-allowed;"
                            disabled>
                        <i class="bi bi-check-circle-fill" style="font-size:1.1rem;"></i>
                        Guardar Vinculación de Estudios
                    </button>
                    <p class="text-center text-secondary mt-2 mb-0" id="txt-guardar-hint" style="font-size:0.82rem;">
                        Seleccione al menos 1 estudio para continuar.
                    </p>
                </div>

                <!-- Botón volver al paso 1 -->
                <div class="d-flex justify-content-center mt-3">
                    <button type="button"
                            id="btn-volver-paso1"
                            class="btn border-0"
                            style="color:#6c757d;font-size:0.87rem;background:transparent;">
                        <i class="bi bi-arrow-left me-1"></i>Volver al paso anterior
                    </button>
                </div>

            </div>
        </div>
    </div><!-- /seccion-paso2 -->

</div><!-- /col-12 -->

<script src="js/circuitoquirurgico/form_cirugias.js"></script>
