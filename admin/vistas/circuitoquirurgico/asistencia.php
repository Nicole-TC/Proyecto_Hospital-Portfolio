<div class="col-12 mb-5 px-3">
  <div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="fw-bold mb-2">Control de Asistencia</h1>
          <p class="text-secondary mb-0">Registrá si los pacientes asistieron o no a sus turnos</p>
        </div>
      </div>

      <!-- Card 1: Filtros de búsqueda -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
        <div class="card-body p-4">
          <div class="row g-3 align-items-end">

            <div class="col-md-3">
              <label class="form-label text-secondary small fw-bold">BUSCAR POR</label>
              <select class="form-select border-0 bg-light" id="filtroTipo" style="border-radius:0.5rem;height:45px;">
                <option value="apellido">Apellido</option>
                <option value="nombre">Nombre</option>
                <option value="dni">DNI</option>
              </select>
            </div>

            <div class="col-md-5">
              <label class="form-label text-secondary small fw-bold">VALOR A BUSCAR</label>
              <input type="text" class="form-control border-0 bg-light" id="valorBuscador"
                placeholder="Ingrese el dato..."
                style="border-radius:0.5rem;height:45px;"
                onkeyup="if(event.key === 'Enter') ejecutarBusqueda()">
            </div>

            <div class="col-md-2">
              <label class="form-label text-secondary small fw-bold">ORDEN</label>
              <select class="form-select border-0 bg-light" id="filtroOrden"
                style="border-radius:0.5rem;height:45px;"
                onchange="paginaActual=1; ejecutarBusqueda()">
                <option value="desc" selected>Más reciente</option>
                <option value="asc">Más antiguo</option>
              </select>
            </div>

            <div class="col-md-2">
              <button class="btn btn-primary w-100 fw-bold" onclick="ejecutarBusqueda()" style="border-radius:0.5rem;height:45px;border:none;">
                <i class="bi bi-search me-2"></i> BUSCAR
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- Card 2: Tabla de resultados -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4 py-3 border-0 text-secondary small text-uppercase fw-semibold">Paciente</th>
                  <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">DNI</th>
                  <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Cirugía</th>
                  <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Médico</th>
                  <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Tipo</th>
                  <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Estado</th>
                  <th class="py-3 pe-4 border-0 text-secondary small text-uppercase fw-semibold text-end text-nowrap">Acción</th>
                </tr>
              </thead>
              <tbody id="tbodyAsistencia">
              </tbody>
            </table>
          </div>

          <div id="emptyAsistencia" class="text-center text-secondary py-5 small d-none">
            <i class="bi bi-search fs-2 d-block mb-2 opacity-25"></i>
            Ingrese un criterio de búsqueda para listar los turnos.
          </div>
        </div>

        <div class="card-footer bg-white border-0 py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
          <div class="text-secondary small" id="infoPaginacion"></div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-1">
              <label class="text-secondary small fw-bold mb-0 text-nowrap">VER</label>
              <select class="form-select form-select-sm border-0 bg-light" id="filtroLimite"
                style="border-radius:0.5rem;min-width:110px;"
                onchange="paginaActual=1; ejecutarBusqueda()">
                <option value="5">5 por pág.</option>
                <option value="10" selected>10 por pág.</option>
                <option value="20">20 por pág.</option>
              </select>
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0" id="paginacionAsistencia"></ul>
            </nav>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
/* Dark Mode - Asistencia */
body.modo-oscuro .bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .card-footer.bg-white {
    background-color: #2a2a2a !important;
    border-top: 1px solid #444 !important;
}
body.modo-oscuro .form-control,
body.modo-oscuro .form-select {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}
body.modo-oscuro .form-control.border-0,
body.modo-oscuro .form-select.border-0 {
    border: 1px solid #444 !important;
}
body.modo-oscuro .form-control:focus,
body.modo-oscuro .form-select:focus {
    background-color: #333 !important;
    color: #fff !important;
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}
body.modo-oscuro .form-control::placeholder {
    color: #888 !important;
}
body.modo-oscuro .text-secondary {
    color: #adb5bd !important;
}
body.modo-oscuro .table-hover tbody tr:hover {
    background-color: #333 !important;
    color: #fff !important;
}
body.modo-oscuro thead.bg-light th {
    background-color: #333 !important;
    color: #adb5bd !important;
}
</style>

<script src="js/circuitoquirurgico/asistencia.js"></script>
