<div class="container-fluid mt-4 mb-5">
    
    <!-- Alerta modo edición -->
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 d-none" id="alert-modo-edicion" style="border-radius:0.75rem;">
        <i class="bi bi-pencil-square fs-5"></i>
        <div class="flex-grow-1">
            <strong>Modo edición:</strong> Estás modificando el cupo <strong id="edit-id-label">#--</strong>.
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" onclick="cancelarEdicion()">
            <i class="bi bi-x-circle me-1"></i>Cancelar
        </button>
    </div>

    <form id="form-cupos">
        <!-- Campo oculto para modo edición -->
        <input type="hidden" id="id_cupo_edit" name="id_cupo" value="">
        
        <!-- 1. Datos de Vigencia y Tipo de Turno -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
            <div class="card-header bg-primary text-white p-3 border-0" style="border-top-left-radius: 0.85rem; border-top-right-radius: 0.85rem;">
                <div class="d-flex align-items-center fw-bold">
                    <span class="badge bg-light text-primary rounded-circle me-2 p-2" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">1</span>
                    Datos de Vigencia y Tipo de Turno
                </div>
            </div>
            <div class="card-body p-4">
                
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">
                            <i class="bi bi-calendar-check me-1"></i> TIPO DE TURNO <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column flex-md-row gap-3">
                            <!-- Option 1 -->
                            <div class="flex-fill position-relative border rounded p-3 bg-light" id="card-tipo-1" style="cursor: pointer; border-radius: 0.5rem;" onclick="selectCard(this)">
                                <input class="form-check-input position-absolute top-50 start-0 ms-3 mt-0" type="radio" name="id_tipo_turno" value="1" required>
                                <div class="ms-4 ps-2">
                                    <i class="bi bi-journal-medical text-secondary fs-4 me-2 float-start"></i>
                                    <span class="fw-semibold text-dark d-block">Jornada Pre-quirúrgica</span>
                                    <small class="text-secondary">(Estudios)</small>
                                </div>
                            </div>
                            <!-- Option 2 -->
                            <div class="flex-fill position-relative border rounded p-3 bg-light" id="card-tipo-2" style="cursor: pointer; border-radius: 0.5rem;" onclick="selectCard(this)">
                                <input class="form-check-input position-absolute top-50 start-0 ms-3 mt-0" type="radio" name="id_tipo_turno" value="2" required>
                                <div class="ms-4 ps-2">
                                    <i class="bi bi-clipboard2-pulse text-secondary fs-4 me-2 float-start"></i>
                                    <span class="fw-semibold text-dark d-block">Consulta de Entrega</span>
                                    <small class="text-secondary">Pre-quirúrgico (Cirujano)</small>
                                </div>
                            </div>
                            <!-- Option 3 -->
                            <div class="flex-fill position-relative border rounded p-3 bg-light" id="card-tipo-3" style="cursor: pointer; border-radius: 0.5rem;" onclick="selectCard(this)">
                                <input class="form-check-input position-absolute top-50 start-0 ms-3 mt-0" type="radio" name="id_tipo_turno" value="3" required>
                                <div class="ms-4 ps-2">
                                    <i class="bi bi-heart-pulse text-secondary fs-4 me-2 float-start"></i>
                                    <span class="fw-semibold text-dark d-block">Turno de Cirugía</span>
                                    <small class="text-secondary">Programada</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold"><i class="bi bi-calendar me-1"></i> FECHA DESDE <span class="text-danger">*</span></label>
                        <input type="date" class="form-control border-0 bg-light" name="fecha_desde" id="fecha_desde" required min="<?= date('Y-m-d') ?>" onchange="validateDates()" style="border-radius:0.5rem; height:45px;">
                        <div class="form-text text-danger d-none mt-1" id="error-fecha_desde"><i class="bi bi-exclamation-circle me-1"></i><span></span></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold"><i class="bi bi-calendar me-1"></i> FECHA HASTA</label>
                        <input type="date" class="form-control border-0 bg-light" name="fecha_hasta" id="fecha_hasta" min="<?= date('Y-m-d') ?>" onchange="validateDates()" style="border-radius:0.5rem; height:45px;">
                        <div class="form-text text-danger d-none mt-1" id="error-fecha_hasta"><i class="bi bi-exclamation-circle me-1"></i><span></span></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 2. Parámetros Globales de la Jornada -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
            <div class="card-header bg-primary text-white p-3 border-0 d-flex justify-content-between align-items-center" style="border-top-left-radius: 0.85rem; border-top-right-radius: 0.85rem;">
                <div class="d-flex align-items-center fw-bold">
                    <span class="badge bg-light text-primary rounded-circle me-2 p-2" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">2</span>
                    Parámetros Globales de la Jornada
                </div>
                <small class="opacity-75">Aplica a todos los días laborales activos</small>
            </div>
            <div class="card-body p-4">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold"><i class="bi bi-clock me-1"></i> HORA DE INICIO ÚNICA <span class="text-danger">*</span></label>
                        <input type="time" class="form-control border-0 bg-light" name="hora_inicio" id="hora_inicio" required onchange="updateGlobalInfo()" style="border-radius:0.5rem; height:45px;">
                        <div class="form-text mt-1">Hora de apertura de agenda para todos los días activos</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold"><i class="bi bi-people me-1"></i> CUPO MÁXIMO DIARIO <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" min="1" class="form-control border-0 bg-light" name="cupos_total" id="cupos_total" placeholder="Ej: 30" required oninput="validateCupos()" style="border-radius:0.5rem; height:45px;">
                            <span class="input-group-text border-0 bg-light text-secondary ms-1" style="border-radius:0.5rem;">pacientes/día</span>
                        </div>
                        <div class="form-text mt-1" id="text-cupos">Límite estricto de admisiones diarias (mínimo 1)</div>
                        <div class="form-text text-danger d-none mt-1" id="error-cupos"><i class="bi bi-exclamation-circle me-1"></i><span></span></div>
                    </div>
                </div>

                <div class="alert alert-primary d-flex align-items-center mb-0" role="alert" style="border-radius: 0.5rem;">
                    <i class="bi bi-info-circle-fill flex-shrink-0 me-2 fs-5"></i>
                    <div class="small">
                        Configuración global: <strong id="info-cupos">-- pacientes</strong> por día, inicio a las <strong id="info-hora">--:--</strong>. Se aplicará a todos los días marcados como activos.
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. Selector de Jornada Laboral Semanal -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
            <div class="card-header bg-primary text-white p-3 border-0 d-flex justify-content-between align-items-center" style="border-top-left-radius: 0.85rem; border-top-right-radius: 0.85rem;">
                <div class="d-flex align-items-center fw-bold">
                    <span class="badge bg-light text-primary rounded-circle me-2 p-2" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">3</span>
                    Selector de Jornada Laboral Semanal
                </div>
                <small class="opacity-75 fw-semibold" id="active-days-count">0 día(s) activo(s)</small>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush border rounded-3 overflow-hidden">
                <?php
                $dias = [
                    'lunes'    => ['LUN', 'Lunes'],
                    'martes'   => ['MAR', 'Martes'],
                    'miercoles'=> ['MIÉ', 'Miércoles'],
                    'jueves'   => ['JUE', 'Jueves'],
                    'viernes'  => ['VIE', 'Viernes']
                ];

                foreach($dias as $key => $nombres):
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light day-row" id="row-<?= $key ?>">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-secondary" style="min-width:52px; font-size:13px; padding: 6px 4px;" id="badge-<?= $key ?>"><?= $nombres[0] ?></span>
                        
                        <input type="checkbox" name="<?= $key ?>" id="<?= $key ?>" value="1"
                               role="switch"
                               onchange="toggleDay('<?= $key ?>')"
                               style="
                                   cursor:pointer;
                                   appearance: none;
                                   -webkit-appearance: none;
                                   width: 3rem;
                                   height: 1.5rem;
                                   border-radius: 2rem;
                                   background-color: #adb5bd;
                                   background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'-4 -4 8 8\'%3e%3ccircle r=\'3\' fill=\'%23fff\'/%3e%3c/svg%3e');
                                   background-repeat: no-repeat;
                                   background-position: left center;
                                   background-size: 1.5rem;
                                   border: 1px solid rgba(0,0,0,.15);
                                   transition: background-color .15s ease-in-out, background-position .15s ease-in-out;
                                   flex-shrink: 0;
                               "
                               onclick="this.style.backgroundColor = this.checked ? '#0dcaf0' : '#adb5bd'; this.style.backgroundPosition = this.checked ? 'right center' : 'left center';"
                        >
                        
                        <div class="d-flex align-items-center gap-2">
                            <strong class="text-dark mb-0"><?= $nombres[1] ?></strong>
                            <span class="text-muted small mb-0" id="text-<?= $key ?>">— Día inactivo, médico no atiende</span>
                        </div>
                    </div>
                    <div class="badge bg-primary rounded-pill p-2 px-3 fw-normal d-none status-badge shadow-sm" id="status-<?= $key ?>">
                        Activo (Cupo: <span class="status-cupo">--</span> | Inicia: <span class="status-hora">--:--</span>)
                    </div>
                </li>
                <?php endforeach; ?>
                
                </ul>
            </div>
        </div>

        <!-- Botones de acción del formulario -->
        <div class="card shadow-sm border-0 mb-4 bg-white" style="border-radius: 0.85rem;">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="text-warning fw-bold small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Completá los campos obligatorios para continuar
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light border fw-semibold" onclick="cancelarEdicion()" style="border-radius:0.5rem; height:45px;">
                        <i class="bi bi-x-circle me-1"></i>Limpiar
                    </button>
                    <button type="button" class="btn btn-secondary fw-bold text-white px-4" id="btn-submit" disabled onclick="saveConfig()" style="border-radius:0.5rem; height:45px; transition: 0.3s;">
                        <span id="btn-submit-label">Aplicar Configuración <i class="bi bi-arrow-right ms-1"></i></span>
                    </button>
                </div>
            </div>
        </div>

    </form>

    <!-- 4. Tabla de Cupos Configurados -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
        <div class="card-header bg-primary text-white p-3 border-0 d-flex justify-content-between align-items-center" style="border-top-left-radius: 0.85rem; border-top-right-radius: 0.85rem;">
            <div class="d-flex align-items-center fw-bold">
                <i class="bi bi-table me-2"></i>
                Cupos Configurados
            </div>
            <button class="btn btn-sm btn-light text-primary fw-semibold" onclick="cargarTabla()" title="Actualizar listado">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
            </button>
        </div>
        <div class="card-body p-0">

            <!-- Estado: cargando -->
            <div class="text-center py-5 text-muted" id="tabla-loading">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Cargando configuraciones...
            </div>

            <!-- Estado: sin datos -->
            <div class="text-center py-5 text-muted d-none" id="tabla-empty">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <span class="small">No hay cupos configurados todavía.</span>
            </div>

            <!-- Estado: error -->
            <div class="text-center py-4 text-danger d-none" id="tabla-error">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <span id="tabla-error-msg">Error al cargar los datos.</span>
            </div>

            <!-- Tabla -->
            <div class="table-responsive d-none" id="tabla-wrapper">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-secondary small fw-bold">#</th>
                            <th class="text-secondary small fw-bold">TIPO DE TURNO</th>
                            <th class="text-secondary small fw-bold">VIGENCIA</th>
                            <th class="text-secondary small fw-bold">HORA</th>
                            <th class="text-secondary small fw-bold text-center">CUPOS/DÍA</th>
                            <th class="text-secondary small fw-bold">DÍAS ACTIVOS</th>
                            <th class="text-secondary small fw-bold text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<script src="js/circuitoquirurgico/cupos.js"></script>
