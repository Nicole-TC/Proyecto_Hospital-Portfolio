<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            Seguimiento de Postas
        </div>
        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-postas">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">DNI</th>
                            <th scope="col">Paciente</th>
                            <th scope="col">Cirugía Indicada</th>
                            <th scope="col" class="text-center">Admisión</th>
                            <th scope="col" class="text-center">Postas (Estudios)</th>
                            <th scope="col" class="text-center">Apto Físico</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Generado por DataTables -->
                    </tbody>
                </table>
            </div>

            <!-- Leyenda -->
            <div class="mt-3 mb-2 ml-2">
                <span class="text-success"><i class="fa-solid fa-circle-check"></i> Completado / Aprobado</span> &nbsp;|&nbsp;
                <span class="text-warning"><i class="fa-solid fa-clock"></i> Pendiente</span> &nbsp;|&nbsp;
                <span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Observado</span>
            </div>

        </div>
    </div>
</div>

<!-- Modal Expediente del Paciente -->
<div class="modal fade" id="expedienteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white border-0 py-3">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-folder-open me-2"></i>Expediente Clínico del Paciente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">

        <!-- Datos del paciente -->
        <div class="card mb-4 border-0 bg-light shadow-sm">
          <div class="card-body">
              <div class="row g-3">
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-solid fa-user me-1"></i> Paciente</p>
                    <p class="fw-bold mb-0 fs-5 text-dark" id="modal-paciente">-</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-solid fa-id-card me-1"></i> DNI</p>
                    <p class="fw-bold mb-0 fs-5 text-dark" id="modal-dni">-</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-solid fa-scalpel me-1"></i> Cirugía Indicada</p>
                    <p class="fw-bold mb-0 fs-5 text-dark" id="modal-cirugia">-</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-regular fa-calendar-check me-1"></i> Fecha de Admisión</p>
                    <p class="fw-bold mb-0 fs-5 text-dark" id="modal-admision">-</p>
                </div>
             </div>
          </div>
        </div>

        <!-- Dos columnas -->
        <div class="row g-4 mt-1">
          <!-- Estudios requeridos -->
          <div class="col-md-6">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fa-solid fa-vial-circle-check me-2"></i>Estudios Requeridos</h6>
            <div class="list-group list-group-flush mt-3" id="modal-lista-estudios">
              <!-- Cargado via AJAX -->
            </div>
          </div>

          <!-- Historia de auditoría -->
          <div class="col-md-6">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historia de Apto Físico</h6>
            
            <div class="position-relative mt-4 ms-3" id="modal-auditoria">
               <!-- Cargado via AJAX -->
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer border-top pt-3 pb-3 d-flex justify-content-between align-items-center">
        
        <div class="d-flex align-items-center flex-grow-1 me-4">
            <input type="hidden" id="apto-id-orden">
            <select class="form-select w-auto me-2" id="apto-estado">
                <option value="OK">Aprobar Apto Físico</option>
                <option value="RECH">Observar / Rechazar</option>
            </select>
            <input type="text" class="form-control me-2 flex-grow-1" id="apto-observaciones" placeholder="Observaciones de la evaluación clínica...">
            <button type="button" class="btn btn-primary text-nowrap" onclick="guardarApto()">
                <i class="fa-solid fa-save me-1"></i> Registrar
            </button>
        </div>

        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<style>
/* Dark Mode - Seguimiento de Postas */
body.modo-oscuro .card {
    background-color: #2a2a2a !important;
    border-color: #444 !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .card-header {
    background-color: #333 !important;
    border-color: #444 !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .text-dark {
    color: #f1f1f1 !important;
}
body.modo-oscuro .text-muted {
    color: #adb5bd !important;
}
body.modo-oscuro .text-secondary {
    color: #adb5bd !important;
}
body.modo-oscuro .table {
    color: #f1f1f1 !important;
    border-color: #444 !important;
}
body.modo-oscuro .table thead th {
    background-color: #333 !important;
    color: #adb5bd !important;
    border-color: #444 !important;
}
body.modo-oscuro .table tbody tr:hover {
    background-color: #333 !important;
}
body.modo-oscuro .table td,
body.modo-oscuro .table th {
    border-color: #444 !important;
}
/* Dots del timeline: sacar bg-white, el ícono no necesita fondo */
body.modo-oscuro .bg-white {
    background-color: #2a2a2a !important;
}
body.modo-oscuro .badge.bg-light {
    background-color: #3a3a3a !important;
    color: #f1f1f1 !important;
    border-color: #555 !important;
}
body.modo-oscuro .list-group-item {
    background-color: transparent !important;
    border-color: #444 !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .modal-content {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
    border: 1px solid #444 !important;
}
body.modo-oscuro .modal-header.bg-primary {
    background-color: #0056b3 !important;
}
body.modo-oscuro .modal-footer {
    border-color: #444 !important;
}
body.modo-oscuro .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
/* DataTables dark mode */
body.modo-oscuro .dataTables_wrapper .dataTables_filter input,
body.modo-oscuro .dataTables_wrapper select {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}
body.modo-oscuro .dataTables_wrapper .dataTables_info,
body.modo-oscuro .dataTables_wrapper .dataTables_length label,
body.modo-oscuro .dataTables_wrapper .dataTables_filter label {
    color: #adb5bd !important;
}
</style>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<!-- DATATABLES -->
<script>
    let seguimientosMap = {}; // Para guardar los datos localmente y no tener que hacer otro fetch al abrir modal

    $(document).ready(function() {
        $('#tabla-postas').DataTable({
            'ajax': {
                'url': 'app/cir-api/endpoints/seguimiento_postas-e.php?accion=listar_seguimiento',
                'dataSrc': 'registros'
            },
            'columns': [
                { 'data': 'dni', 'className': 'text-center' },
                { 'data': null, 'render': function(data) { 
                    return data.paciente_apellido + ', ' + data.paciente_nombre; 
                }},
                { 'data': 'cirugia_nombre' },
                { 'data': 'fecha_orden', 'className': 'text-center' },
                { 'data': null, 'className': 'text-center', 'render': function(data) {
                    let total = data.estudios ? data.estudios.length : 0;
                    let realizados = 0;
                    if (data.estudios) {
                        data.estudios.forEach(e => { if (e.estado_codigo === 'REAL') realizados++; });
                    }
                    if (total === 0) return '<span class="text-muted">Sin postas requeridas</span>';
                    if (realizados === total) return `<i class="fa-solid fa-circle-check text-success"></i> ${realizados}/${total}`;
                    return `<i class="fa-solid fa-clock text-warning"></i> ${realizados}/${total}`;
                }},
                { 'data': null, 'className': 'text-center', 'render': function(data) {
                    if (data.apto_fisico && data.apto_fisico.estado_codigo === 'OK') {
                        return '<span class="badge bg-success">Aprobado</span>';
                    } else if (data.apto_fisico && data.apto_fisico.estado_codigo === 'RECH') {
                        return '<span class="badge bg-danger">Observado</span>';
                    }
                    return '<span class="badge bg-secondary">Pendiente</span>';
                }},
                { 'data': null, 'className': 'text-center', 'render': function(data) {
                    seguimientosMap[data.id_orden] = data; // Almacenamos para el modal
                    return `<a class="text-info" href="javascript:void(0)" onclick="abrirModalExpediente(${data.id_orden})" title="Ver Detalle">
                                <i class="bi bi-eye fs-5"></i>
                            </a>`;
                }}
            ],
            'language': {
                'lengthMenu': "Mostrar " +
                    `<select class ="custom-SELECT custom-SELECT-sm form-control form-control-sm">
                <option value = '10'>10</option>
                <option value = '25'>25</option>
                <option value = '50'>50</option>
                <option value = '100'>100</option>
                <option value = '-1'>Todos</option>
             </select>` +
                    ' registros por página',
                'zeroRecords': 'Sin resultados',
                'info': 'Se encontraron _TOTAL_ coincidencias',
                'infoEmpty': 'No hay registros por mostrar',
                'infoFiltered': '[de un total de _MAX_]',
                'search': 'Filtrar',
                'paginate': {
                    'next': 'Siguiente',
                    'previous': 'Anterior'
                }
            }
        });
    });

    function abrirModalExpediente(id_orden) {
        let data = seguimientosMap[id_orden];
        if (!data) return;

        // Cabecera del modal
        $('#modal-paciente').text(data.paciente_apellido + ', ' + data.paciente_nombre);
        $('#modal-dni').text(data.dni);
        $('#modal-cirugia').text(data.cirugia_nombre || 'No definida');
        $('#modal-admision').text(data.fecha_orden || '-');

        // Formulario de Apto
        $('#apto-id-orden').val(id_orden);
        $('#apto-estado').val('OK');
        $('#apto-observaciones').val('');

        // Limpiar contenedor de estudios
        let estudiosContainer = $('#modal-lista-estudios');
        estudiosContainer.empty();

        if (data.estudios && data.estudios.length > 0) {
            data.estudios.forEach(e => {
                let isCompletado = (e.estado_codigo === 'REAL');
                let icon = isCompletado ? '<i class="fa-solid fa-file-medical text-success me-2"></i>' : '<i class="fa-solid fa-clock text-warning me-2"></i>';
                
                estudiosContainer.append(`
                    <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-3 border-bottom">
                       <div>
                          <h6 class="mb-1 fw-bold text-dark">${icon} ${e.nombre}</h6>
                          <small class="text-muted"><i class="fa-solid fa-building me-1"></i> Categoria: ${e.categoria || 'Sin categoría'}</small>
                       </div>
                       ${isCompletado ? '<span class="badge bg-success">Completado</span>' : '<span class="badge bg-warning text-dark">Pendiente</span>'}
                    </div>
                `);
            });
        } else {
            estudiosContainer.append('<p class="text-muted mt-2">No hay estudios postas requeridos.</p>');
        }

        // Historial Apto Físico
        let auditoriaContainer = $('#modal-auditoria');
        auditoriaContainer.empty();
        auditoriaContainer.append('<div class="position-absolute h-100 bg-primary opacity-25" style="width: 2px; left: 7px; top: 10px;"></div>');

        // Registro Inicial (Admisión fake para mantener el mockup)
        auditoriaContainer.append(`
           <div class="d-flex mb-4 position-relative">
              <div class="bg-white text-primary mt-1 me-3 position-relative z-1" style="margin-left: -2px;"><i class="fa-solid fa-circle fs-5"></i></div>
              <div>
                 <p class="mb-0 fw-bold text-dark">Admisión Creada</p>
                 <span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar me-1"></i> ${data.fecha_orden}</span>
              </div>
           </div>
        `);

        // Si hay apto
        if (data.apto_fisico) {
            let isOk = data.apto_fisico.estado_codigo === 'OK';
            let iconClass = isOk ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger';
            let textOk = isOk ? 'Apto Físico Aprobado' : 'Apto Físico Observado';
            
            auditoriaContainer.append(`
               <div class="d-flex mb-4 position-relative">
                  <div class="bg-white ${isOk ? 'text-success' : 'text-danger'} mt-1 me-3 position-relative z-1" style="margin-left: -2px;"><i class="fa-solid ${iconClass} fs-5"></i></div>
                  <div>
                     <p class="mb-0 fw-bold text-dark">${textOk}</p>
                     <p class="mb-1 small text-secondary">Evaluado por: ${data.apto_fisico.profesional_nombre} ${data.apto_fisico.profesional_apellido} <br/> 
                     Obs: ${data.apto_fisico.checklist || 'Sin observaciones'}</p>
                     <span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar me-1"></i> ${data.apto_fisico.fecha}</span>
                  </div>
               </div>
            `);
        } else {
            auditoriaContainer.append(`
               <div class="d-flex mb-2 position-relative">
                  <div class="bg-white text-warning mt-1 me-3 position-relative z-1" style="margin-left: -2px;"><i class="fa-solid fa-circle-notch fa-spin fs-5"></i></div>
                  <div>
                     <p class="mb-0 fw-bold text-dark">Pendiente de Evaluación</p>
                     <p class="mb-1 small text-secondary">Aguardando que un profesional registre el Apto</p>
                  </div>
               </div>
            `);
        }

        let myModal = new bootstrap.Modal(document.getElementById('expedienteModal'));
        myModal.show();
    }

    function guardarApto() {
        let id_orden = $('#apto-id-orden').val();
        let estado = $('#apto-estado').val();
        let obs = $('#apto-observaciones').val();

        if (!id_orden) return;

        // Por ahora hardcodeamos el ID del profesional logueado como 1. (En la vida real vendría de la sesión)
        let id_profesional = 1; 

        fetch('app/cir-api/endpoints/seguimiento_postas-e.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                accion: 'registrar_apto',
                id_orden: id_orden,
                id_profesional: id_profesional,
                observaciones: obs,
                codigo_estado: estado
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                $('#expedienteModal').modal('hide');
                $('#tabla-postas').DataTable().ajax.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al registrar el apto.');
        });
    }
</script>