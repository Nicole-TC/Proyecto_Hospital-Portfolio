<div class="col-12 mb-5 px-3">
  <div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">

      <!-- ── Encabezado ── -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="fw-bold mb-2">Agenda-Turnos</h1>
          <p class="text-secondary mb-0">Programación semanal · Pre-quirúrgico</p>
        </div>
        <div class="d-flex flex-column align-items-end gap-1">
          <div class="fw-bold fs-4 font-monospace" id="clockTime">--:--:--</div>
          <small class="text-secondary" id="clockDate">--</small>
        </div>
      </div>

      <!-- ── Calendario Semanal ── -->
      <div class="card shadow-sm border-0 mb-3" style="border-radius: 0.85rem;">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-3">
            <div>
              <h5 class="fw-bold mb-1">Calendario Semanal</h5>
              <p class="text-secondary small mb-0">Hacé click en un día para ver y gestionar los turnos.</p>
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

      <!--  Panel expandible del día seleccionado ── -->
      <div class="card border-primary shadow-sm border-0 mb-4 d-none" id="panelDia" style="border-radius: 0.85rem; border-left: 3px solid #0d6efd !important;">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="fw-bold mb-0" id="panelDiaTitulo">—</h5>
              <p class="text-secondary small mb-0">Turnos programados para este día</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoTurno" style="font-size: 1.2rem; padding: 10px 20px;">
                <i class="bi bi-person-plus me-1"></i> Nuevo Turno
                </button>
            </div>
          </div>

          <!-- Lista de turnos del día -->
          <div id="listaTurnosDia"></div>
          <div id="emptyTurnosDia" class="text-center text-secondary py-4 small d-none">
            <i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-25"></i>
            No hay turnos programados para este día
          </div>
        </div>
      </div>

      <!-- ── Buscador de Turnos ── -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
            <div>
              <h5 class="fw-bold mb-1">Buscador de Turnos</h5>
              <p class="text-secondary small mb-0">Buscá turnos por paciente</p>
            </div>
          </div>

          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-3">
              <label class="form-label text-secondary small fw-bold">BUSCAR POR</label>
              <select class="form-select border-0 bg-light" id="filtroTipoTurno" style="border-radius:0.5rem;height:45px;">
                <option value="apellido">Apellido</option>
                <option value="nombre">Nombre</option>
                <option value="dni">DNI</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label text-secondary small fw-bold">VALOR A BUSCAR</label>
              <input type="text" class="form-control border-0 bg-light" id="valorBuscadorTurnos"
                placeholder="Ingrese el dato..."
                style="border-radius:0.5rem;height:45px;"
                onkeyup="if(event.key === 'Enter') ejecutarBusquedaTurnos()">
            </div>
            <div class="col-md-3">
              <button class="btn btn-primary w-100 fw-bold" onclick="ejecutarBusquedaTurnos()" style="border-radius:0.5rem;height:45px;border:none;">
                <i class="bi bi-search me-2"></i> BUSCAR TURNO
              </button>
            </div>
          </div>

          <div id="resultadosBuscador" class="d-none">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="text-secondary small text-uppercase fw-semibold">Paciente</th>
                    <th class="text-secondary small text-uppercase fw-semibold">DNI</th>
                    <th class="text-secondary small text-uppercase fw-semibold">Fecha</th>
                    <th class="text-secondary small text-uppercase fw-semibold">Tipo</th>
                    <th class="text-secondary small text-uppercase fw-semibold">Estado</th>
                  </tr>
                </thead>
                <tbody id="tbodyBuscador"></tbody>
              </table>
            </div>
            <div id="emptyBuscador" class="text-center text-secondary py-4 small d-none">
              <i class="bi bi-search fs-2 d-block mb-2 opacity-25"></i>
              No se encontraron turnos para esa búsqueda
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!--  Modal Nuevo Turno ── -->
<div class="modal fade" id="modalNuevoTurno" tabindex="-1" aria-labelledby="modalNuevoTurnoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 1rem;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="modalNuevoTurnoLabel">
          <i class="bi bi-person-plus me-2 text-primary"></i>Nuevo Turno Prequirúrgico
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formNuevoTurno" novalidate>

          <!-- Campos ocultos -->
          <input type="hidden" id="modalFechaOculta">
          <input type="hidden" id="modalIdOrden">
          <input type="hidden" id="modalIdPA">

          <!-- DNI -->
          <div class="mb-3">
            <label for="modalDni" class="form-label small fw-semibold text-secondary">DNI DEL PACIENTE</label>
            <input type="text" class="form-control" id="modalDni"
              placeholder="Sin puntos ni espacios"
              maxlength="8"
              inputmode="numeric"
              autocomplete="off">
            <div id="modalDniFeedback" class="mt-2"></div>
          </div>

          <!-- Nombre y Apellido -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="modalNombre" class="form-label small fw-semibold text-secondary">NOMBRE</label>
              <input type="text" class="form-control bg-light" id="modalNombre" readonly tabindex="-1" placeholder="—">
            </div>
            <div class="col-md-6 mb-3">
              <label for="modalApellido" class="form-label small fw-semibold text-secondary">APELLIDO</label>
              <input type="text" class="form-control bg-light" id="modalApellido" readonly tabindex="-1" placeholder="—">
            </div>
          </div>

          <!-- Médico Responsable -->
          <div class="mb-4">
            <label for="modalMedico" class="form-label small fw-semibold text-secondary">MÉDICO RESPONSABLE</label>
            <input type="text" class="form-control bg-light" id="modalMedico" readonly tabindex="-1" placeholder="Se completa automáticamente">
          </div>

          <!-- Botón guardar -->
          <div class="d-grid">
            <button type="submit" class="btn btn-primary" id="btnGuardarTurnoModal" style="border-radius:0.5rem;" disabled>
              <i class="bi bi-calendar-check me-1"></i> Guardar Turno
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script src="js/circuitoquirurgico/agenda.js"></script>