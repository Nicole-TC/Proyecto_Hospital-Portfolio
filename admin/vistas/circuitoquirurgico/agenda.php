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

    <!-- Resultados -->
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
<script src="js/circuitoquirurgico/agenda.js"></script>