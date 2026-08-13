<div class="col-12 mb-5 px-3">
    <div class="card shadow-sm border-0" style="border-radius: 1rem;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-list-check me-2"></i>Pacientes con Apto Físico</h5>
            </div>

            <!-- Filtros homogeneizados con seguimiento_postas -->
            <div class="row g-2 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label small text-secondary mb-1" for="filtro-fecha-desde">Apto Físico desde</label>
                    <input type="date" class="form-control" id="filtro-fecha-desde">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary mb-1" for="filtro-fecha-hasta">Apto Físico hasta</label>
                    <input type="date" class="form-control" id="filtro-fecha-hasta">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary mb-1" for="filtro-estado-apto">Estado Apto Físico</label>
                    <select class="form-select" id="filtro-estado-apto">
                        <option value="OK" selected>Apto Quirúrgico</option>
                        <option value="OBS">Observado</option>
                        <option value="TODOS">Todos</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="button" class="btn btn-primary w-100" id="btn-filtrar-espera">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 0.85rem;">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tabla-lista-espera">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary small text-uppercase fw-semibold">DNI</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Paciente</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Cirugía</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Apto Desde</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Estado</th>
                                    <th class="text-secondary small text-uppercase fw-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Programar Cirugía -->
<div class="modal fade" id="modal-programar-cirugia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 1rem; border: none;">
      <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
        <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Programar Cirugía</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="form-programar-cirugia">
        <div class="modal-body p-4">
            <input type="hidden" id="prog-id-orden" name="id_orden">
            <input type="hidden" id="prog-id-persona-asiste" name="id_persona_asiste">
            
            <p class="mb-4">Programando cirugía para <strong id="prog-nombre-paciente" class="text-primary"></strong>.</p>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Fecha y Hora de la Cirugía <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="prog-fecha-hora" name="fecha_hora" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary" for="prog-id-profesional">Cirujano Asignado <span class="text-danger">*</span></label>
                <select class="form-select" id="prog-id-profesional" name="id_profesional" required>
                    <option value="">Seleccione un profesional...</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Observaciones Quirúrgicas <span class="text-muted fw-normal">(Opcional)</span></label>
                <textarea class="form-control" id="prog-observaciones" name="observaciones" rows="2" placeholder="Ej: Material especial requerido, lateralidad..."></textarea>
            </div>
        </div>
        <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
            <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal" style="border-radius: 0.5rem; height: 45px;">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold" style="border-radius: 0.5rem; height: 45px;">Confirmar Turno</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<style>
/* Ajustes modo oscuro para inputs */
body.modo-oscuro #filtro-estado-apto,
body.modo-oscuro #prog-fecha-hora {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    filter: invert(1) brightness(200%);
    cursor: pointer;
}
</style>

<script>
let tablaListaEspera;

function inicializarTabla() {
    if ($.fn.DataTable.isDataTable('#tabla-lista-espera')) {
        $('#tabla-lista-espera').DataTable().destroy();
    }

    tablaListaEspera = $('#tabla-lista-espera').DataTable({
        ajax: function(data, callback) {
            $.ajax({
                url: 'app/cir-api/endpoints/lista_espera-e.php',
                data: {
                    estado_apto: $('#filtro-estado-apto').val(),
                    fecha_desde: $('#filtro-fecha-desde').val(),
                    fecha_hasta: $('#filtro-fecha-hasta').val()
                },
                dataType: 'json',
                success: function(json) {
                    callback({ data: json.registros || [] });
                },
                error: function() {
                    callback({ data: [] });
                }
            });
        },
        columns: [
            { data: 'dni' },
            { data: null, render: function (data) { return data.paciente_apellido + ', ' + data.paciente_nombre; } },
            { data: 'cirugia_nombre' },
            { data: 'apto_desde', render: function (data) { if (!data) return '-'; const parts = data.split(' ')[0].split('-'); return `${parts[2]}/${parts[1]}/${parts[0]}`; } },
            { data: 'estado_apto_codigo', render: function (data) { let badgeClass = 'bg-secondary'; let text = 'Pendiente'; if (data === 'OK') { badgeClass = 'bg-success'; text = 'Aprobado'; } else if (data === 'OBS') { badgeClass = 'bg-danger'; text = 'Observado'; } return `<span class="badge ${badgeClass}">${text}</span>`; } },
            { data: null, orderable: false, className: 'text-center', render: function (data) { if (data.turno_programado_fecha) { const parts = data.turno_programado_fecha.split(' ')[0].split('-'); return `<button type="button" class="btn btn-sm btn-outline-success fw-bold" style="border-radius: 0.5rem;" disabled>Turno Asignado</button>`; } if (data.estado_apto_codigo === 'OK') { return `<button type="button" class="btn btn-sm btn-primary fw-bold" style="border-radius: 0.5rem;" onclick="abrirModalProgramar(${data.id_orden}, ${data.id_persona_asiste}, '${data.paciente_apellido.replace(/'/g, "\\'")}, ${data.paciente_nombre.replace(/'/g, "\\'")}')"><i class="bi bi-calendar-plus me-1"></i> Programar</button>`; } return `<button type="button" class="btn btn-sm btn-outline-secondary fw-bold" style="border-radius: 0.5rem;" disabled>No Apto</button>`; } }
        ],
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: 'No hay pacientes.',
            paginate: {
                next: 'Siguiente',
                previous: 'Anterior'
            }
        }
    });
}

function fechaHoyISO() {
    let hoy = new Date();
    let mes = String(hoy.getMonth() + 1).padStart(2, '0');
    let dia = String(hoy.getDate()).padStart(2, '0');
    return `${hoy.getFullYear()}-${mes}-${dia}`;
}

function cargarCirujanos() {
    $.ajax({
        url: 'app/cir-api/endpoints/lista_espera-e.php?accion=obtener_cirujanos',
        method: 'GET',
        success: function(res) {
            if (res.status === 'success' && res.cirujanos) {
                let $select = $('#prog-id-profesional');
                $select.empty();
                $select.append('<option value="">Seleccione un profesional...</option>');
                res.cirujanos.forEach(function(cir) {
                    $select.append(`<option value="${cir.id_profesional}">${cir.apellido}, ${cir.nombre}</option>`);
                });
            }
        }
    });
}

$(document).ready(function() {
    $('#filtro-fecha-hasta').attr('max', fechaHoyISO());
    inicializarTabla();
    cargarCirujanos();
});

$('#btn-filtrar-espera').on('click', function() {
    let desde = $('#filtro-fecha-desde').val();
    let hasta = $('#filtro-fecha-hasta').val();
    if ((desde && !hasta) || (!desde && hasta)) {
        Swal.fire('Filtros incompletos', 'Si usás el filtro de fechas, completá "Desde" y "Hasta".', 'warning');
        return;
    }
    if (hasta && hasta > fechaHoyISO()) {
        Swal.fire('Error', 'La fecha "Hasta" no puede ser posterior a hoy.', 'warning');
        return;
    }
    tablaListaEspera.ajax.reload();
});

$('#filtro-estado-apto').on('change', function() {
    $('#btn-filtrar-espera').click();
});

let diasActivosAgenda = [];

function abrirModalProgramar(id_orden, id_persona_asiste, paciente) {
    // Verificar si hay agenda activa para CIRUGIA
    $.ajax({
        url: 'app/cir-api/endpoints/lista_espera-e.php?accion=config_agenda_cirugia',
        method: 'GET',
        success: function(res) {
            if (res.status === 'success' && res.config) {
                if (res.config.sin_config) {
                    Swal.fire('Atención', 'No hay agenda abierta para Cirugías. Debe crear cupos en la Agenda antes de programar turnos.', 'warning');
                    return;
                }
                
                diasActivosAgenda = res.config.dias_activos || [];
                
                $('#prog-id-orden').val(id_orden);
                $('#prog-id-persona-asiste').val(id_persona_asiste);
                $('#prog-nombre-paciente').text(paciente);
                
                // Establecer límites de fecha mínima y máxima
                let today = new Date();
                today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
                let minDate = today.toISOString().slice(0, 16); 
                
                let configStart = res.config.fecha_desde ? res.config.fecha_desde + 'T00:00' : null;
                if (configStart && configStart > minDate) {
                    minDate = configStart;
                }
                
                $('#prog-fecha-hora').attr('min', minDate);
                
                if (res.config.fecha_hasta) {
                    $('#prog-fecha-hora').attr('max', res.config.fecha_hasta + 'T23:59');
                } else {
                    $('#prog-fecha-hora').removeAttr('max');
                }
                
                $('#prog-fecha-hora').val('');
                $('#prog-id-profesional').val('');
                $('#prog-observaciones').val('');
                $('#modal-programar-cirugia').modal('show');
            } else {
                Swal.fire('Error', res.message || 'No se pudo verificar la agenda.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de conexión con el servidor al verificar agenda.', 'error');
        }
    });
}

// Validar que el día de la semana seleccionado coincida con los días activos de la agenda
$('#prog-fecha-hora').on('change', function() {
    let val = $(this).val();
    if (!val) return;
    
    let dateObj = new Date(val);
    let day = dateObj.getDay(); // 0 = Domingo, 1 = Lunes, etc.
    let mapDias = {1: 'lunes', 2: 'martes', 3: 'miercoles', 4: 'jueves', 5: 'viernes'};
    let diaSemana = mapDias[day];
    
    if (!diaSemana || !diasActivosAgenda.includes(diaSemana)) {
        let diasNombres = diasActivosAgenda.map(d => d.charAt(0).toUpperCase() + d.slice(1)).join(', ');
        Swal.fire('Día no habilitado', 'La agenda actual solo permite programar cirugías los días: <b>' + diasNombres + '</b>.', 'warning');
        $(this).val('');
    }
});

$('#form-programar-cirugia').on('submit', function(e) {
    e.preventDefault();
    const idOrden = $('#prog-id-orden').val();
    const idPersonaAsiste = $('#prog-id-persona-asiste').val();
    const fechaHora = $('#prog-fecha-hora').val();
    const idProfesional = $('#prog-id-profesional').val();
    
    if(!fechaHora) {
        Swal.fire('Atención', 'Debe seleccionar una fecha y hora.', 'warning');
        return;
    }

    if(!idProfesional) {
        Swal.fire('Atención', 'Debe seleccionar un cirujano.', 'warning');
        return;
    }

    $.ajax({
        url: 'app/cir-api/endpoints/lista_espera-e.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            accion: 'programar_cirugia',
            id_orden: idOrden,
            id_persona_asiste: idPersonaAsiste,
            fecha_hora: fechaHora,
            id_profesional: idProfesional,
            observaciones: $('#prog-observaciones').val()
        }),
        success: function(res) {
            if (res.status === 'success') {
                Swal.fire('Éxito', res.message, 'success');
                $('#modal-programar-cirugia').modal('hide');
                tablaListaEspera.ajax.reload();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error');
        }
    });
});
</script>
