<!-- Contenido principal -->
<main class="p-4">
  <div class="card shadow bg-white">
    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm0 13A6 6 0 1 1 8 2a6 6 0 0 1 0 12z"/>
        <path d="M8 4a.5.5 0 0 1 .5.5v3.793l2.146 2.147a.5.5 0 0 1-.708.707L7.5 8.707V4.5A.5.5 0 0 1 8 4z"/>
      </svg>
      <h3 class="mb-0 fw-bold">Auditoría Global</h3>
    </div>
    <div class="card-body">
      <p class="text-muted mb-4">Historial completo de actividades del sistema</p>

      <!-- Filtros de búsqueda -->
      <div class="border rounded p-3 mb-4">
        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
          </svg>
          Filtros de Búsqueda
        </h6>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#6c757d" viewBox="0 0 16 16">
                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
                </svg>
              </span>
              <input type="text" class="form-control" id="busqueda" placeholder="Buscar por paciente, usuario o acción...">
            </div>
          </div>
          <div class="col-md-6">
            <div class="input-group">
              <input type="date" class="form-control" id="fecha">
            </div>
          </div>
      </div>

      <!-- Tabla de auditoría -->
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="tablaAuditoria">
          <thead class="table-light">
            <tr>
              <th>Fecha/Hora</th>
              <th>Usuario Responsable</th>
              <th>Rol</th>
              <th>Acción Realizada</th>
              <th>Paciente Afectado</th>
              <th>Detalles</th>
            </tr>
          </thead>
        </table>
      </div>

    </div>
  </div>
</main>

<script>
  const TOTAL = 15;

  function filtrarTabla() {
    const textoBusqueda = document.getElementById('busqueda').value.toLowerCase().trim();
    // input type="date" devuelve YYYY-MM-DD → convertir a DD/MM/YYYY para comparar con la tabla
    const valorFecha = document.getElementById('fecha').value.trim();
    let textofecha = '';
    if (valorFecha) {
      const [anio, mes, dia] = valorFecha.split('-');
      textofecha = `${dia}/${mes}/${anio}`;
    }

    const filas = document.querySelectorAll('#tablaAuditoria tbody tr');
    let visibles = 0;

    filas.forEach(fila => {
      const textoFila = fila.textContent.toLowerCase();
      const fechaFila = fila.querySelector('td')?.textContent.trim() ?? '';

      const coincideTexto = textoBusqueda === '' || textoFila.includes(textoBusqueda);
      const coincideFecha  = textofecha   === '' || fechaFila.startsWith(textofecha);

      if (coincideTexto && coincideFecha) {
        fila.style.display = '';
        visibles++;
      } else {
        fila.style.display = 'none';
      }
    });

    // Actualizar contador
    const contador = document.getElementById('contadorResultados');
    if (visibles === 0) {
      contador.textContent = 'No se encontraron resultados para la búsqueda.';
      contador.classList.add('text-danger');
      contador.classList.remove('text-muted');

      // Mostrar fila de "sin resultados" si no existe
      let filaVacia = document.getElementById('filaVacia');
      if (!filaVacia) {
        const tbody = document.querySelector('#tablaAuditoria tbody');
        filaVacia = document.createElement('tr');
        filaVacia.id = 'filaVacia';
        filaVacia.innerHTML = `<td colspan="6" class="text-center text-muted py-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-2 d-block mx-auto" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
          </svg>
          Sin resultados para "<strong>${escapeHtml(textoBusqueda || textofecha)}</strong>"
        </td>`;
        tbody.appendChild(filaVacia);
      } else {
        filaVacia.querySelector('strong').textContent = textoBusqueda || textofecha;
        filaVacia.style.display = '';
      }
    } else {
      contador.textContent = `Mostrando ${visibles} de ${TOTAL} eventos registrados`;
      contador.classList.remove('text-danger');
      contador.classList.add('text-muted');

      const filaVacia = document.getElementById('filaVacia');
      if (filaVacia) filaVacia.style.display = 'none';
    }
  }

  function escapeHtml(text) {
    return text.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  document.getElementById('busqueda').addEventListener('input', filtrarTabla);
  document.getElementById('fecha').addEventListener('input', filtrarTabla);
</script>