<div class="col-12 mb-5 px-md-4 px-3 mt-4 lista-espera">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div class="flex-grow-1 min-w-0">
            <h2 class="blue fw-bold mb-1">Lista de Espera Quirúrgica</h2>
            <p class="text-secondary mb-0">Pacientes con apto físico aguardando programación de cirugía</p>
        </div>
    </div>

    <div class="card border-0 mb-4 le-card">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 align-items-end">
                <div class="col-12">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-secondary small fw-bold" for="filtro-fecha-desde">APTO FÍSICO DESDE</label>
                            <input type="date" class="form-control border-0 bg-light le-control" id="filtro-fecha-desde">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-secondary small fw-bold" for="filtro-fecha-hasta">APTO FÍSICO HASTA</label>
                            <input type="date" class="form-control border-0 bg-light le-control" id="filtro-fecha-hasta">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-secondary small fw-bold" for="filtro-estado-apto">ESTADO APTO FÍSICO</label>
                            <select class="form-select border-0 bg-light le-select" id="filtro-estado-apto">
                                <option value="OK" selected>Apto Quirúrgico</option>
                                <option value="OBS">Observado</option>
                                <option value="TODOS">Todos</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-secondary small fw-bold" for="filtro-especialidad">ESPECIALIDAD</label>
                            <select class="form-select border-0 bg-light le-select" id="filtro-especialidad">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-secondary small fw-bold" for="filtro-subespecialidad">SUBESPECIALIDAD</label>
                            <select class="form-select border-0 bg-light le-select" id="filtro-subespecialidad" disabled>
                                <option value="">Seleccione una especialidad primero</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-secondary small fw-bold d-none d-md-block">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100 fw-bold le-btn" id="btn-filtrar-espera">
                                <i class="bi bi-search me-1"></i> BUSCAR
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 le-card le-card-tabla">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 w-100" id="tabla-lista-espera">
                <thead class="bg-light">
                    <tr>
                        <th class="le-th border-0">DNI</th>
                        <th class="le-th border-0">Paciente</th>
                        <th class="le-th border-0">Cirugía</th>
                        <th class="le-th border-0">Especialidad</th>
                        <th class="le-th border-0">Subespecialidad</th>
                        <th class="le-th border-0">Apto Desde</th>
                        <th class="le-th border-0">Estado</th>
                        <th class="le-th border-0 text-center">Prioridad</th>
                        <th class="le-th border-0 text-center">Fecha Asignada</th>
                        <th class="le-th border-0 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Programar Cirugía -->
<div class="modal fade" id="modal-programar-cirugia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow le-modal">
      <div class="modal-header bg-primary text-white" id="prog-modal-header">
        <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Programar Cirugía</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="form-programar-cirugia">
        <div class="modal-body p-4">
            <input type="hidden" id="prog-id-orden" name="id_orden">
            <input type="hidden" id="prog-id-persona-asiste" name="id_persona_asiste">

            <p class="mb-4">Programando cirugía para <strong id="prog-nombre-paciente" class="text-primary"></strong>.</p>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold" for="prog-fecha-hora">FECHA Y HORA DE LA CIRUGÍA <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control border-0 bg-light le-control" id="prog-fecha-hora" name="fecha_hora" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold" for="prog-id-profesional">CIRUJANO ASIGNADO <span class="text-danger">*</span></label>
                <select class="form-select border-0 bg-light le-select" id="prog-id-profesional" name="id_profesional" required>
                    <option value="">Seleccione un profesional...</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold" for="prog-observaciones">OBSERVACIONES QUIRÚRGICAS <span class="text-muted fw-normal">(Opcional)</span></label>
                <textarea class="form-control border-0 bg-light le-textarea" id="prog-observaciones" name="observaciones" rows="2" placeholder="Ej: Material especial requerido, lateralidad..."></textarea>
            </div>
        </div>
        <div class="modal-footer bg-light border-0" id="prog-modal-footer">
            <button type="button" class="btn btn-outline-secondary fw-bold le-btn" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold le-btn">Confirmar Turno</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<style>
/* Lista de espera */
.lista-espera .le-card {
    border-radius: 0.75rem;
    box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
}
.lista-espera .le-card-tabla {
    overflow: hidden;
}
.lista-espera .le-control,
.lista-espera .le-select {
    border-radius: 0.5rem;
    height: 45px;
}
.lista-espera .le-textarea {
    border-radius: 0.5rem;
}
.lista-espera .le-btn {
    border-radius: 0.5rem;
    height: 45px;
    min-height: 45px;
}

.lista-espera .le-dt-toolbar {
    border-bottom: 1px solid #eef1f4;
}
.lista-espera .le-dt-footer {
    border-top: 1px solid #eef1f4;
}
.lista-espera #tabla-lista-espera_wrapper .dataTables_length,
.lista-espera #tabla-lista-espera_wrapper .dataTables_filter,
.lista-espera #tabla-lista-espera_wrapper .dataTables_info,
.lista-espera #tabla-lista-espera_wrapper .dataTables_paginate {
    padding: 0.75rem 0;
    font-size: 0.9rem;
    color: #6c757d;
}
.lista-espera #tabla-lista-espera_wrapper .dataTables_length label,
.lista-espera #tabla-lista-espera_wrapper .dataTables_filter label {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
}
.lista-espera #tabla-lista-espera_wrapper .dataTables_filter {
    text-align: left;
}
@media (min-width: 768px) {
    .lista-espera #tabla-lista-espera_wrapper .dataTables_filter {
        text-align: right;
    }
    .lista-espera #tabla-lista-espera_wrapper .dataTables_filter label {
        justify-content: flex-end;
    }
}

/* Select "Mostrar X" de DataTables */
.lista-espera #tabla-lista-espera_wrapper .dataTables_length select {
    display: inline-block;
    width: auto;
    min-width: 4.25rem;
    margin: 0;
    height: 38px;
    padding: 0.35rem 0.75rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #0984e3;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    cursor: pointer;
}
.lista-espera #tabla-lista-espera_wrapper .dataTables_length select:focus {
    outline: none;
    border-color: #0984e3;
    box-shadow: 0 0 0 0.15rem rgba(9, 132, 227, 0.2);
}

/* Input de búsqueda de DataTables */
.lista-espera #tabla-lista-espera_wrapper .dataTables_filter input {
    width: 100%;
    max-width: 260px;
    height: 38px;
    margin-left: 0;
    padding: 0.35rem 0.9rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #343a40;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
}
.lista-espera #tabla-lista-espera_wrapper .dataTables_filter input:focus {
    outline: none;
    border-color: #0984e3;
    box-shadow: 0 0 0 0.15rem rgba(9, 132, 227, 0.2);
}
.lista-espera #tabla-lista-espera_wrapper .dataTables_filter input::placeholder {
    color: #adb5bd;
    font-weight: 400;
}

/* Scroll horizontal solo en la tabla */
.lista-espera .le-dt-scroll {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.lista-espera #tabla-lista-espera {
    width: 100% !important;
    min-width: 960px;
    margin: 0 !important;
    border-collapse: collapse;
}
.lista-espera #tabla-lista-espera thead th,
.lista-espera #tabla-lista-espera thead th.le-th {
    font-size: 0.9rem;
    font-weight: 700;
    border-top: none !important;
    border-bottom-width: 2px !important;
    padding: 0.85rem 1rem !important;
    vertical-align: middle;
    white-space: nowrap;
}
.lista-espera #tabla-lista-espera tbody td {
    font-size: 0.9rem;
    line-height: 1.45;
    padding: 0.85rem 1rem !important;
    vertical-align: middle;
}
.lista-espera #tabla-lista-espera tbody tr:last-child td {
    border-bottom: none !important;
}

.lista-espera .le-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 6rem;
    padding: 0.42em 0.9em;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.2;
    border-radius: 999px;
    white-space: nowrap;
}
.lista-espera .le-badge--ok { background: rgba(25, 135, 84, 0.12); color: #146c43; }
.lista-espera .le-badge--obs { background: rgba(220, 53, 69, 0.1); color: #b02a37; }
.lista-espera .le-badge--pend { background: #e9ecef; color: #495057; }
.lista-espera .le-badge--prio-roja { background: rgba(220, 53, 69, 0.12); color: #b02a37; }
.lista-espera .le-badge--prio-amarilla { background: #fff3cd; color: #856404; }
.lista-espera .le-badge--prio-verde { background: rgba(25, 135, 84, 0.12); color: #146c43; }
.lista-espera .le-badge--prio-na { background: #f1f3f5; color: #495057; }

.lista-espera .le-acciones {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.lista-espera .le-btn-accion {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-width: 8.5rem;
    padding: 0.38rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1;
    border: none;
    border-radius: 0.5rem;
    white-space: nowrap;
    cursor: pointer;
    transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.12s ease;
}
.lista-espera .le-btn-accion i { font-size: 0.9rem; line-height: 1; }
.lista-espera .le-btn-accion:hover:not(:disabled) { transform: translateY(-1px); }
.lista-espera .le-btn-accion:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }
.lista-espera .le-btn-accion--programar { background: linear-gradient(135deg, #00cec9 0%, #00b4d8 100%); color: #fff; }
.lista-espera .le-btn-accion--programar:hover:not(:disabled) { box-shadow: 0 3px 12px rgba(0, 180, 216, 0.4); filter: brightness(1.05); }
.lista-espera .le-btn-accion--asignado { background: rgba(25, 135, 84, 0.14); color: #146c43; }
.lista-espera .le-btn-accion--noapto { background: #e9ecef; color: #6c757d; }

.lista-espera #tabla-lista-espera_wrapper .pagination {
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 0.25rem;
    margin-bottom: 0;
}
@media (min-width: 768px) {
    .lista-espera #tabla-lista-espera_wrapper .pagination {
        justify-content: flex-end;
    }
}
.lista-espera #tabla-lista-espera_wrapper .pagination .page-link {
    border-radius: 0.5rem !important;
    font-weight: 600;
    min-width: 2.5rem;
    text-align: center;
    padding: 0.45rem 0.75rem;
}

.le-modal { border-radius: 0.75rem; }
.le-modal .modal-header {
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
}
.le-modal .modal-footer {
    border-bottom-left-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
}

/* Modo oscuro: lo que no cubre darkmode.css */
body.modo-oscuro .lista-espera .form-control.bg-light,
body.modo-oscuro .lista-espera .form-select.bg-light,
body.modo-oscuro #prog-fecha-hora,
body.modo-oscuro #prog-observaciones,
body.modo-oscuro #prog-id-profesional {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
    border: 1px solid #444 !important;
}
body.modo-oscuro .lista-espera .le-badge--ok { background: rgba(107, 216, 141, 0.15); color: #6bd88d; }
body.modo-oscuro .lista-espera .le-badge--obs { background: rgba(255, 107, 107, 0.12); color: #ff6b6b; }
body.modo-oscuro .lista-espera .le-badge--pend { background: #2a2a2a; color: #aaa; }
body.modo-oscuro .lista-espera .le-badge--prio-roja { background: rgba(255, 107, 107, 0.12); color: #ff6b6b; }
body.modo-oscuro .lista-espera .le-badge--prio-amarilla { background: rgba(255, 193, 7, 0.15); color: #ffd553; }
body.modo-oscuro .lista-espera .le-badge--prio-verde { background: rgba(107, 216, 141, 0.15); color: #6bd88d; }
body.modo-oscuro .lista-espera .le-badge--prio-na { background: #2a2a2a; color: #aaa; }
body.modo-oscuro .lista-espera .le-btn-accion--asignado { background: rgba(107, 216, 141, 0.15); color: #6bd88d; }
body.modo-oscuro .lista-espera .le-btn-accion--noapto { background: #2a2a2a; color: #aaa; }
body.modo-oscuro .lista-espera #tabla-lista-espera_wrapper .dataTables_length select,
body.modo-oscuro .lista-espera #tabla-lista-espera_wrapper .dataTables_filter input {
    background-color: #1f1f1f !important;
    color: #ccc !important;
    border-color: #444 !important;
}
body.modo-oscuro input[type="datetime-local"]::-webkit-calendar-picker-indicator,
body.modo-oscuro input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1) brightness(200%);
    cursor: pointer;
}
</style>

<script>
let tablaListaEspera;
let catalogoSubespecialidades = [];

function inicializarTabla() {
    if ($.fn.DataTable.isDataTable('#tabla-lista-espera')) {
        $('#tabla-lista-espera').DataTable().destroy();
    }

    tablaListaEspera = $('#tabla-lista-espera').DataTable({
        order: [[7, 'asc'], [5, 'asc']],
        // Mostrar/Filtrar fuera del scroll; scroll solo en la tabla
        dom:
            "<'le-dt-toolbar px-3'<'row align-items-center g-2'<'col-12 col-md-6'l><'col-12 col-md-6'f>>>" +
            "<'le-dt-scroll'tr>" +
            "<'le-dt-footer px-3'<'row align-items-center g-2'<'col-12 col-md-5'i><'col-12 col-md-7'p>>>",
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
                    if (json && json.status === 'error') {
                        Swal.fire('Error', json.message || 'Error al cargar la lista de espera.', 'error');
                        callback({ data: [] });
                        return;
                    }
                    const registros = json.registros || [];
                    callback({ data: registros });
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo comunicar con el servidor al cargar la lista.', 'error');
                    callback({ data: [] });
                }
            });
        },
        columns: [
            { data: 'dni', className: 'fw-semibold' },
            { data: null, render: function (data) {
                return '<span class="fw-semibold">' + data.paciente_apellido + ', ' + data.paciente_nombre + '</span>';
            } },
            { data: 'cirugia_nombre', className: 'text-secondary' },
            { data: 'especialidad_nombre', defaultContent: '<span class="text-muted">-</span>', className: 'text-secondary' },
            { data: 'subespecialidad_nombre', defaultContent: '<span class="text-muted">-</span>', className: 'text-secondary' },
            { data: 'apto_desde', className: 'text-center', render: function (data) {
                if (!data) return '<span class="text-muted">-</span>';
                const parts = data.split(' ')[0].split('-');
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            } },
            { data: 'estado_apto_codigo', className: 'text-center', render: function (data) {
                if (data === 'OK') return '<span class="le-badge le-badge--ok">Aprobado</span>';
                if (data === 'OBS') return '<span class="le-badge le-badge--obs">Observado</span>';
                return '<span class="le-badge le-badge--pend">Pendiente</span>';
            } },
            { data: 'prioridad_codigo', className: 'text-center', render: function (data, type) {
                if (type === 'sort' || type === 'type') {
                    if (data === 'ROJO') return 1;
                    if (data === 'AMARILLO') return 2;
                    if (data === 'VERDE') return 3;
                    return 4;
                }
                if (data === 'ROJO') return '<span class="le-badge le-badge--prio-roja">Urgente</span>';
                if (data === 'AMARILLO') return '<span class="le-badge le-badge--prio-amarilla">Media</span>';
                if (data === 'VERDE') return '<span class="le-badge le-badge--prio-verde">Baja</span>';
                return '<span class="le-badge le-badge--prio-na">-</span>';
            } },
            { data: 'turno_programado_fecha', className: 'text-center', render: function (data) {
                if (!data) return '<span class="text-muted">-</span>';
                const datePart = data.split(' ')[0];
                const timePart = data.split(' ')[1] ? data.split(' ')[1].substring(0, 5) : '';
                const parts = datePart.split('-');
                return '<span class="text-primary fw-semibold"><i class="bi bi-calendar-check me-1"></i>' +
                    parts[2] + '/' + parts[1] + '/' + parts[0] + ' ' + timePart + '</span>';
            } },
            { data: null, orderable: false, className: 'text-center', render: function (data) {
                if (data.turno_programado_fecha) {
                    return '<div class="le-acciones"><button type="button" class="le-btn-accion le-btn-accion--asignado" disabled>' +
                        '<i class="bi bi-check2-circle"></i><span>Turno asignado</span></button></div>';
                }
                if (data.estado_apto_codigo === 'OK') {
                    const ape = data.paciente_apellido.replace(/'/g, "\\'");
                    const nom = data.paciente_nombre.replace(/'/g, "\\'");
                    const prio = data.prioridad_codigo || '';
                    return '<div class="le-acciones"><button type="button" class="le-btn-accion le-btn-accion--programar" ' +
                        'onclick="abrirModalProgramar(' + data.id_orden + ', ' + data.id_persona_asiste + ', \'' + ape + ', ' + nom + '\', \'' + prio + '\')">' +
                        '<i class="bi bi-calendar-plus"></i><span>Programar</span></button></div>';
                }
                return '<div class="le-acciones"><button type="button" class="le-btn-accion le-btn-accion--noapto" disabled>' +
                    '<span>No apto</span></button></div>';
            } }
        ],
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            search: "Filtrar:",
            zeroRecords: 'No hay pacientes.',
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 registros",
            infoFiltered: "(filtrado de _MAX_)",
            paginate: {
                next: 'Siguiente',
                previous: 'Anterior'
            }
        },
        initComplete: function() {
            const $filter = $('#tabla-lista-espera_filter input');
            $filter.attr('placeholder', 'Buscar en la tabla...');
            $('#tabla-lista-espera_length select')
                .addClass('form-select form-select-sm')
                .css({ width: 'auto', display: 'inline-block' });
        }
    });
}

function cargarFiltrosEspecialidad() {
    $.ajax({
        url: 'app/cir-api/endpoints/lista_espera-e.php?accion=obtener_catalogos_especialidades',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status !== 'success') return;

            (res.especialidades || []).forEach(function(registro) {
                $('#filtro-especialidad').append(
                    $('<option>')
                        .val(registro.id_especialidad)
                        .attr('data-nombre', registro.nombre)
                        .text(registro.nombre)
                );
            });
            catalogoSubespecialidades = res.subespecialidades || [];
        },
        error: function() {
            Swal.fire('Error', 'No se pudieron cargar las especialidades del catálogo.', 'error');
        }
    });
}

function actualizarSelectSubespecialidades() {
    const idEspecialidad = $('#filtro-especialidad').val();
    const $select = $('#filtro-subespecialidad');
    $select.empty();

    if (!idEspecialidad) {
        $select.append($('<option>').val('').text('Seleccione una especialidad primero'));
        $select.prop('disabled', true);
        return;
    }

    const subespecialidades = catalogoSubespecialidades.filter(function(registro) {
        return String(registro.id_especialidad_padre) === String(idEspecialidad);
    });

    $select.append($('<option>').val('').text('Todas'));
    subespecialidades.forEach(function(registro) {
        $select.append(
            $('<option>')
                .val(registro.id_especialidad)
                .attr('data-nombre', registro.nombre)
                .text(registro.nombre)
        );
    });
    $select.prop('disabled', subespecialidades.length === 0);

    if (subespecialidades.length === 0) {
        $select.empty().append($('<option>').val('').text('Sin subespecialidades'));
    }
}

function aplicarFiltrosEspecialidad() {
    const nombreEspecialidad = $('#filtro-especialidad option:selected').attr('data-nombre') || '';
    const nombreSubespecialidad = $('#filtro-subespecialidad option:selected').attr('data-nombre') || '';
    [
        { columna: 3, valor: nombreEspecialidad },
        { columna: 4, valor: nombreSubespecialidad }
    ].forEach(function(filtro) {
        const expresion = filtro.valor
            ? '(^|,\\s*)' + $.fn.dataTable.util.escapeRegex(filtro.valor) + '(,\\s*|$)'
            : '';
        tablaListaEspera.column(filtro.columna).search(expresion, true, false);
    });
    tablaListaEspera.draw();
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
    cargarFiltrosEspecialidad();
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

$('#filtro-especialidad').on('change', function() {
    actualizarSelectSubespecialidades();
    aplicarFiltrosEspecialidad();
});

$('#filtro-subespecialidad').on('change', aplicarFiltrosEspecialidad);

let diasActivosAgenda = [];

function abrirModalProgramar(id_orden, id_persona_asiste, paciente, prioridad) {
    let headerBg = '#0d6efd', headerText = '#fff';
    let footerBg = '#f8f9fa', footerText = '';
    if (prioridad === 'ROJO') {
        headerBg = '#dc3545'; footerBg = '#dc3545'; footerText = '#fff';
    } else if (prioridad === 'AMARILLO') {
        headerBg = '#ffd400'; headerText = '#000'; footerBg = '#ffd400'; footerText = '#000';
    } else if (prioridad === 'VERDE') {
        headerBg = '#198754'; footerBg = '#198754'; footerText = '#fff';
    }
    $('#prog-modal-header').get(0).style.setProperty('background-color', headerBg, 'important');
    $('#prog-modal-header').get(0).style.setProperty('color', headerText, 'important');
    $('#prog-modal-footer').get(0).style.setProperty('background-color', footerBg, 'important');
    $('#prog-modal-footer').get(0).style.setProperty('color', footerText, 'important');
    $('#prog-modal-header .btn-close').toggleClass('btn-close-white', headerText === '#fff');

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

$('#prog-fecha-hora').on('change', function() {
    let val = $(this).val();
    if (!val) return;

    let dateObj = new Date(val);
    let day = dateObj.getDay();
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

    if (!fechaHora) {
        Swal.fire('Atención', 'Debe seleccionar una fecha y hora.', 'warning');
        return;
    }

    if (!idProfesional) {
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
