<div class="col-12 mb-5 px-3">
  <div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">

      <!-- ── Encabezado ── -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="fw-bold mb-2">Agenda-Turnos</h1>
          <p class="text-secondary mb-0">Programación semanal</p>
        </div>
        <div class="d-flex flex-column align-items-end gap-1">
          <div class="fw-bold fs-4 font-monospace" id="clockTime">--:--:--</div>
          <small class="text-secondary" id="clockDate">--</small>
          <div class="position-relative" id="wrapperCupos">
            <button class="btn btn-sm btn-outline-primary mt-1" type="button" id="btnToggleCupos">
              <i class="bi bi-sliders me-1"></i> Configurar Cupos
            </button>
            <div id="panelDropdownCupos" style="
              display: none;
              position: absolute;
              right: 0;
              top: calc(100% + 8px);
              width: 340px;
              background: white;
              border-radius: 0.85rem;
              box-shadow: 0 8px 24px rgba(0,0,0,0.12);
              z-index: 1050;
              padding: 1.25rem;
              opacity: 0;
              transform: translateY(-8px);
              transition: opacity 0.2s ease, transform 0.2s ease;
            ">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold">Configurar Cupos</span>
                <button type="button" class="btn-close btn-sm" id="btnCerrarCupos"></button>
              </div>
              <p class="text-secondary small mb-3">
                Cupos disponibles por día. Solo Lunes, Martes y Miércoles.
              </p>
              <div id="panelCupos"></div>
              <div class="alert alert-success small mt-3 d-none" id="alertCuposGuardados"></div>
              <div class="d-grid mt-3">
                <button class="btn btn-primary btn-sm" id="btnGuardarCupos">
                  <i class="bi bi-save me-1"></i> Guardar cambios
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Calendario Semanal ── -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-3">
            <div>
              <h5 class="fw-bold mb-1">Calendario Semanal</h5>
              <p class="text-secondary small mb-0">Navegación por semana de turnos programados.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-sm btn-outline-secondary" id="btnPrevWeek"><i class="bi bi-chevron-left"></i></button>
              <span class="small fw-semibold text-secondary text-center" style="min-width:160px;" id="weekLabel"></span>
              <button class="btn btn-sm btn-outline-secondary" id="btnNextWeek"><i class="bi bi-chevron-right"></i></button>
              <button class="btn btn-sm btn-outline-primary" id="btnToday">Hoy</button>
            </div>
          </div>
          <div class="row g-2" id="calGrid"></div>
        </div>
      </div>

      <!-- ── Programación Detallada ── -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
            <div>
              <h5 class="fw-bold mb-1">Programación Detallada</h5>
              <p class="text-secondary small mb-0">Listado de turnos programados por fecha</p>
            </div>
            <div class="mt-2 mt-md-0">
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoTurno">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Turno
              </button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="text-secondary small text-uppercase fw-semibold">Paciente</th>
                  <th class="text-secondary small text-uppercase fw-semibold">DNI</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Fecha</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Hora</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Tipo</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Estado</th>
                </tr>
              </thead>
              <tbody id="tbodyCirugias"></tbody>
            </table>
          </div>
          <div id="emptyCirugias" class="text-center text-secondary py-4 small">
            <i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-25"></i>
            Sin turnos programados
          </div>
        </div>
      </div>


    </div>
  </div>
</div>

<!-- ── Modal Nuevo Turno ── -->
<div class="modal fade" id="modalNuevoTurno" tabindex="-1" aria-labelledby="modalNuevoTurnoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 1rem;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="modalNuevoTurnoLabel">Programar Nuevo Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formNuevoTurno" novalidate>

          <!-- Campos ocultos -->
          <input type="hidden" id="id_orden">
          <input type="hidden" id="id_persona_asiste">

          <!-- DNI -->
          <div class="mb-3">
            <label for="tDni" class="form-label small fw-semibold text-secondary">DNI</label>
            <input type="text" class="form-control" id="tDni"
              placeholder="Sin puntos ni espacios"
              maxlength="8"
              inputmode="numeric"
              required>
            <div id="dniFeedback" class="form-text mt-1"></div>
          </div>

          <!-- Info paciente -->
          <div id="infoPaciente" class="alert alert-primary py-2 px-3 mb-3 d-none" style="border-radius:0.65rem;">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-person-check fs-5"></i>
              <div>
                <div class="fw-semibold small" id="infoPacienteNombre"></div>
                <div class="text-secondary" style="font-size:11px;" id="infoPacienteCirugia"></div>
              </div>
            </div>
          </div>

          <!-- Nombre y Apellido -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tNombre" class="form-label small fw-semibold text-secondary">Nombre</label>
              <input type="text" class="form-control" id="tNombre" readonly tabindex="-1">
            </div>
            <div class="col-md-6 mb-3">
              <label for="tApellido" class="form-label small fw-semibold text-secondary">Apellido</label>
              <input type="text" class="form-control" id="tApellido" readonly tabindex="-1">
            </div>
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label for="tEmail" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
            <input type="email" class="form-control" id="tEmail"readonly tabindex="-1">
          </div>

          <!-- Fecha y Hora -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tFecha" class="form-label small fw-semibold text-secondary">Fecha</label>
              <input type="date" class="form-control" id="tFecha" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="tHora" class="form-label small fw-semibold text-secondary">Hora</label>
              <input type="time" class="form-control" id="tHora" required>
            </div>
          </div>

          <!-- Cupos disponibles -->
          <div id="infoCupos" class="d-none mb-3 text-center">
            <span class="badge" id="badgeCuposLibres" style="font-size:12px;"></span>
          </div>

          <!-- Tipo de turno -->
          <div class="mb-4">
            <label for="tTipoTurno" class="form-label small fw-semibold text-secondary">Tipo de turno</label>
            <select class="form-select" id="tTipoTurno" required>
              <option value="" disabled selected>Seleccione el tipo de turno</option>
            </select>
          </div>

          <!-- Feedback -->
          <div id="turnoFeedback" class="alert d-none mb-3 small"></div>

          <!-- Botón -->
          <div class="d-grid">
            <button type="submit" class="btn btn-primary" id="btnGuardarTurno" style="border-radius:0.5rem;" disabled>
              <i class="bi bi-calendar-check me-1"></i> Guardar Turno
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script src="js/circuitoquirurgico/agenda.js"></script>