<!-- ── Buscador de Auditoría Global ── -->

<!-- Card 1: Encabezado y filtros de búsqueda -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
  <div class="card-body p-4">

    <!-- Título -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
          <i class="bi bi-clock-history text-primary"></i>
          Auditoría Global
        </h5>
        <p class="text-secondary small mb-0">Historial completo de actividades registradas en el sistema.</p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="row g-3 align-items-end">

      <div class="col-md-3">
        <label class="form-label text-secondary small fw-bold">BUSCAR POR</label>
        <select class="form-select border-0 bg-light" id="filtroTipoAuditoria" style="border-radius:0.5rem; height:45px;">
          <option value="todo">Todo</option>
          <option value="paciente">Paciente / DNI</option>
          <option value="usuario">Usuario responsable</option>
          <option value="accion">Acción realizada</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label text-secondary small fw-bold">VALOR A BUSCAR</label>
        <input
          type="text"
          class="form-control border-0 bg-light"
          id="valorBuscadorAuditoria"
          placeholder="Ingrese el dato..."
          style="border-radius:0.5rem; height:45px;"
          onkeyup="if(event.key === 'Enter') ejecutarBusquedaAuditoria()"
        >
      </div>

      <!-- El rango se limita a los ultimos 3 meses: min y max los carga el JS -->
      <div class="col-md-2">
        <label class="form-label text-secondary small fw-bold">DESDE</label>
        <input
          type="date"
          class="form-control border-0 bg-light"
          id="filtroDesdeAuditoria"
          style="border-radius:0.5rem; height:45px;"
          onchange="ajustarRangoAuditoria(); ejecutarBusquedaAuditoria()"
        >
      </div>

      <div class="col-md-2">
        <label class="form-label text-secondary small fw-bold">HASTA</label>
        <input
          type="date"
          class="form-control border-0 bg-light"
          id="filtroHastaAuditoria"
          style="border-radius:0.5rem; height:45px;"
          onchange="ajustarRangoAuditoria(); ejecutarBusquedaAuditoria()"
        >
      </div>

      <div class="col-md-2">
        <button
          class="btn btn-primary w-100 fw-bold"
          onclick="ejecutarBusquedaAuditoria()"
          style="border-radius:0.5rem; height:45px; border:none;"
        >
          <i class="bi bi-search me-2"></i> BUSCAR
        </button>
      </div>

    </div>

    <p class="text-secondary small mt-3 mb-0">
      <i class="bi bi-info-circle me-1"></i>
      Se pueden consultar registros de hasta 3 meses de antigüedad.
    </p>
  </div>
</div>

<!-- Card 2: Tabla de resultados -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tablaAuditoria">
        <thead class="bg-light">
          <tr>
            <th class="ps-4 py-3 border-0 text-secondary small text-uppercase fw-semibold">Fecha / Hora</th>
            <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Usuario responsable</th>
            <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Acción realizada</th>
            <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Paciente afectado</th>
            <th class="py-3 border-0 text-secondary small text-uppercase fw-semibold">Detalles</th>
          </tr>
        </thead>
        <tbody id="tbodyAuditoria">
          <!-- Resultados dinámicos -->
        </tbody>
      </table>
    </div>

    <!-- Estado vacío -->
    <div id="emptyAuditoria" class="text-center text-secondary py-5">
      <i class="bi bi-clock-history fs-1 d-block mb-3 opacity-25"></i>
      <p class="fw-semibold mb-1">No hay datos para mostrar</p>
      <p class="small opacity-75">Usá los filtros de búsqueda para consultar el historial de actividades.</p>
    </div>

  </div>

  <!-- Footer: info + orden + límite + paginación -->
  <div class="card-footer bg-white border-0 py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

    <div class="text-secondary small" id="infoAuditoria">Mostrando 0 registros</div>

    <div class="d-flex align-items-center gap-2 flex-wrap">

      <div class="d-flex align-items-center gap-1">
        <label class="text-secondary small fw-bold mb-0 text-nowrap">ORDEN</label>
        <select
          class="form-select form-select-sm border-0 bg-light"
          id="filtroOrdenAuditoria"
          style="border-radius:0.5rem; min-width:140px;"
          onchange="paginaAuditoria=1; ejecutarBusquedaAuditoria()"
        >
          <option value="desc" selected>↓ Más reciente</option>
          <option value="asc">↑ Más antiguo</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-1">
        <label class="text-secondary small fw-bold mb-0 text-nowrap">VER</label>
        <select
          class="form-select form-select-sm border-0 bg-light"
          id="filtroLimiteAuditoria"
          style="border-radius:0.5rem; min-width:120px;"
          onchange="paginaAuditoria=1; ejecutarBusquedaAuditoria()"
        >
          <option value="5">5 por pág.</option>
          <option value="10" selected>10 por pág.</option>
          <option value="20">20 por pág.</option>
          <option value="50">50 por pág.</option>
        </select>
      </div>

      <nav>
        <ul class="pagination pagination-sm mb-0" id="paginacionAuditoria"></ul>
      </nav>

    </div>
  </div>
</div>

<style>
/* Dark Mode - Auditoría Global */
body.modo-oscuro .bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .bg-white {
    background-color: #2a2a2a !important;
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
</style>

<script src="js/circuitoquirurgico/auditoria_global.js?v=<?= @filemtime(__DIR__ . '/../../js/circuitoquirurgico/auditoria_global.js') ?: time() ?>"></script>
