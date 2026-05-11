<div class="col-12 mb-5 px-3">
  <div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
 
      <!-- ── Encabezado ── -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="fw-bold mb-2">Agenda-Turnos</h1>
          <p class="text-secondary mb-0">Programación semanal</p>
        </div>
        <div class="text-end">
          <div class="fw-bold fs-4 font-monospace" id="clockTime">--:--:--</div>
          <small class="text-secondary" id="clockDate">--</small>
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
              <button class="btn btn-sm btn-outline-secondary" id="btnPrevWeek" title="Semana anterior">
                <i class="bi bi-chevron-left"></i>
              </button>
              <span class="small fw-semibold text-secondary text-center" style="min-width: 160px;" id="weekLabel"></span>
              <button class="btn btn-sm btn-outline-secondary" id="btnNextWeek" title="Semana siguiente">
                <i class="bi bi-chevron-right"></i>
              </button>
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
        <form id="formNuevoTurno"> 
          <div class="mb-3">
            <label for="dni" class="form-label small fw-semibold text-secondary">DNI</label>
            <input type="number" class="form-control" id="dni" placeholder="Sin puntos ni espacios" required>
          </div>
          <div class="mb-3">
            <label for="paciente" class="form-label small fw-semibold text-secondary">Nombre del Paciente</label>
            <input type="text" class="form-control" id="paciente" placeholder="Ej. Juan Pérez" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" placeholder="ejemplo@correo.com" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="fecha" class="form-label small fw-semibold text-secondary">Fecha</label>
              <input type="date" class="form-control" id="fecha" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="hora" class="form-label small fw-semibold text-secondary">Hora</label>
              <input type="time" class="form-control" id="hora" required>
            </div>
          </div>
          <div class="mb-4">
            <label for="procedimiento" class="form-label small fw-semibold text-secondary">Tipo de turno</label>
            <select class="form-select" id="procedimiento" required>
              <option value="" disabled selected>Seleccione el tipo de turno</option>
              <option value="Cirugía General">Cirugía General</option>
              <option value="Pre-quirurgico">Pre-quirurgico</option>
              <option value="Entrega Pre-quirurgico">Entrega Pre-quirúrgico</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary" style="border-radius: 0.5rem;">
              Guardar Turno
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="js/circuitoquirurgico/agenda_quirurgica.js"></script>