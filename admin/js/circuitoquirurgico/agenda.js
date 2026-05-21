/* ── Reloj en vivo ── */
function updateClock() {
    const now = new Date();
    document.getElementById('clockTime').textContent = now.toLocaleTimeString('es-AR');
    document.getElementById('clockDate').textContent = now.toLocaleDateString('es-AR', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
}
updateClock();
setInterval(updateClock, 1000);

/* ── Constantes ── */
const DAYS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
const MONTHS_LONG = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
const ENDPOINT_AGENDA = 'app/cir-api/endpoints/agenda-e.php';

let currentWeekOffset = 0;
let diaSeleccionado = null; // fecha string 'YYYY-MM-DD' del día clickeado
let idTipoTurnoPreq = null; // se carga al iniciar

/* ── Feriados Argentina ── */
let feriadosCache = {};

function cargarFeriados(anio, callback) {
    if (feriadosCache[anio]) { callback(feriadosCache[anio]); return; }
    fetch('https://date.nager.at/api/v3/PublicHolidays/' + anio + '/AR')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            feriadosCache[anio] = {};
            data.forEach(function (f) { feriadosCache[anio][f.date] = f.localName; });
            callback(feriadosCache[anio]);
        })
        .catch(function () { feriadosCache[anio] = {}; callback({}); });
}

function getWeekStart(offset) {
    const now = new Date();
    const monday = new Date(now);
    monday.setDate(now.getDate() - now.getDay() + 1 + offset * 7);
    monday.setHours(0, 0, 0, 0);
    return monday;
}

/*  Calendario con click por día ── */
function renderCalendar() {
    const weekStart = getWeekStart(currentWeekOffset);
    cargarFeriados(weekStart.getFullYear(), function (feriados) {
        _renderCalendarConFeriados(feriados);
    });
}

function _renderCalendarConFeriados(feriados) {
    const grid = document.getElementById('calGrid');
    const label = document.getElementById('weekLabel');
    const weekStart = getWeekStart(currentWeekOffset);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    const today = new Date(); today.setHours(0, 0, 0, 0);

    label.textContent = weekStart.getDate() + ' ' + MONTHS[weekStart.getMonth()] +
        ' – ' + weekEnd.getDate() + ' ' + MONTHS[weekEnd.getMonth()] + ' ' + weekEnd.getFullYear();
    grid.innerHTML = '';

    // Cargar cupos de todos los días de la semana
    var fechas = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        fechas.push(d.toISOString().split('T')[0]);
    }

    var cuposSemana = {};
    var pendientes = fechas.length;

    fechas.forEach(function (fecha) {
        fetch(ENDPOINT_AGENDA + '?action=turnos_del_dia&fecha=' + fecha)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                cuposSemana[fecha] = data.ok ? data.turnos.length : 0;
            })
            .catch(function () { cuposSemana[fecha] = 0; })
            .finally(function () {
                pendientes--;
                if (pendientes === 0) _pintarCalendario(weekStart, today, feriados, cuposSemana);
            });
    });
}

function _pintarCalendario(weekStart, today, feriados, ocupadosPorFecha) {
    const grid = document.getElementById('calGrid');
    grid.innerHTML = '';

    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        const isToday = d.getTime() === today.getTime();
        const isPast = d < today;
        const dateStr = d.toISOString().split('T')[0];
        const esFeriado = feriados[dateStr];
        const ocupados = ocupadosPorFecha[dateStr] || 0;
        const esSeleccionado = dateStr === diaSeleccionado;

        const col = document.createElement('div');
        col.className = 'col';

        var borderClass = '';
        if (esSeleccionado) borderClass = 'border-primary border-2';
        else if (isToday) borderClass = 'border-primary';
        else if (esFeriado) borderClass = 'border-warning';

        var badgeOcupados = ocupados > 0
            ? '<span class="badge bg-primary-subtle text-primary" style="font-size:9px;">' + ocupados + ' turno' + (ocupados !== 1 ? 's' : '') + '</span>'
            : '<span class="badge bg-secondary-subtle text-secondary" style="font-size:9px;">Sin turnos</span>';

        col.innerHTML =
            '<div class="card border ' + borderClass + ' h-100 dia-calendario' + (isPast ? ' opacity-50' : '') + '" ' +
            'data-fecha="' + dateStr + '" ' +
            'style="border-radius:0.65rem;min-height:110px;cursor:pointer;transition:box-shadow .15s,transform .15s;' +
            (esSeleccionado ? 'box-shadow:0 0 0 3px rgba(13,110,253,.25);' : '') + '">' +
            '<div class="card-body p-2 text-center">' +
            '<div class="text-secondary" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">' + DAYS[d.getDay()] + '</div>' +
            (isToday
                ? '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto my-1 fw-bold" style="width:32px;height:32px;font-size:15px;">' + d.getDate() + '</div>'
                : '<div class="fw-bold fs-5 my-1 ' + (esFeriado ? 'text-warning' : '') + '">' + d.getDate() + '</div>'
            ) +
            (esFeriado ? '<div class="badge bg-warning-subtle text-warning mb-1" style="font-size:9px;white-space:normal;">' + esFeriado + '</div>' : '') +
            '<div class="mt-1">' + badgeOcupados + '</div>' +
            '</div></div>';

        //  click abre panel del día
        col.querySelector('.dia-calendario').addEventListener('click', function () {
            var fecha = this.dataset.fecha;
            if (diaSeleccionado === fecha) {
                diaSeleccionado = null;
                cerrarPanelDia();
                renderCalendar();
            } else {
                diaSeleccionado = fecha;
                renderCalendar();
                abrirPanelDia(fecha);
            }
        });

        grid.appendChild(col);
    }
}

/* Panel de turnos del día ── */
function abrirPanelDia(fecha) {
    var panel = document.getElementById('panelDia');
    if (!panel) return;

    var d = new Date(fecha + 'T00:00:00');
    var titulo = DAYS[d.getDay()] + ' ' + d.getDate() + ' de ' + MONTHS_LONG[d.getMonth()];

    document.getElementById('panelDiaTitulo').textContent = titulo;
    panel.classList.remove('d-none');

    // Guardar fecha seleccionada en el modal
    document.getElementById('modalFechaOculta').value = fecha;

    cargarTurnosDia(fecha);
}

function cerrarPanelDia() {
    var panel = document.getElementById('panelDia');
    if (panel) panel.classList.add('d-none');
    diaSeleccionado = null;
}

function cargarTurnosDia(fecha) {
    var lista = document.getElementById('listaTurnosDia');
    var empty = document.getElementById('emptyTurnosDia');

    lista.innerHTML = '<div class="text-center text-secondary py-3 small">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando turnos...</div>';
    empty.classList.add('d-none');

    fetch(ENDPOINT_AGENDA + '?action=turnos_del_dia&fecha=' + fecha)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            lista.innerHTML = '';
            if (!data.ok || !data.turnos || data.turnos.length === 0) {
                empty.classList.remove('d-none');
                return;
            }
            data.turnos.forEach(function (t) {
                var hora = new Date(t.fecha_hora).toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
                var badgeClass = t.estado_codigo === 'PROG' ? 'bg-primary-subtle text-primary'
                    : t.estado_codigo === 'CONF' ? 'bg-success-subtle text-success'
                        : t.estado_codigo === 'AUS' ? 'bg-warning-subtle text-warning'
                            : t.estado_codigo === 'CANC' ? 'bg-danger-subtle text-danger'
                                : 'bg-secondary-subtle text-secondary';

                var medico = (t.medico_apellido && t.medico_nombre)
                    ? 'Dr/a. ' + t.medico_apellido + ', ' + t.medico_nombre
                    : '—';

                var item = document.createElement('div');
                item.className = 'd-flex align-items-center gap-3 py-3 px-1 border-bottom';
                item.innerHTML =
                    '<div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">' +
                    '<i class="bi bi-person text-primary"></i></div>' +
                    '<div class="flex-grow-1">' +
                    '<div class="fw-semibold">' + t.apellido + ', ' + t.nombre +
                    ' <span class="text-secondary fw-normal small ms-1">DNI: ' + t.dni + '</span></div>' +
                    '<div class="text-secondary small">' +
                    '<span class="badge bg-light text-dark border me-2" style="font-size:11px;">' + hora + '</span>' +
                    medico +
                    '</div></div>' +
                    '<span class="badge ' + badgeClass + '">' + t.estado + '</span>';
                lista.appendChild(item);
            });
        })
        .catch(function () {
            lista.innerHTML = '<div class="text-danger small text-center py-3">Error al cargar turnos.</div>';
        });
}

/* ── Navegación calendario ── */
document.getElementById('btnPrevWeek').addEventListener('click', function () {
    currentWeekOffset--;
    diaSeleccionado = null;
    cerrarPanelDia();
    renderCalendar();
});
document.getElementById('btnNextWeek').addEventListener('click', function () {
    currentWeekOffset++;
    diaSeleccionado = null;
    cerrarPanelDia();
    renderCalendar();
});
document.getElementById('btnToday').addEventListener('click', function () {
    currentWeekOffset = 0;
    diaSeleccionado = null;
    cerrarPanelDia();
    renderCalendar();
});

/*Modal Nuevo Turno ── */

// Buscar paciente por DNI con validación de circuito
document.addEventListener('blur', function (e) {
    if (e.target.id !== 'modalDni') return;

    var dni = e.target.value.replace(/\D/g, '').trim();
    var feedback = document.getElementById('modalDniFeedback');

    limpiarDatosPacienteModal();

    if (!dni || dni.length < 7) { feedback.innerHTML = ''; return; }

    if (!idTipoTurnoPreq) {
        feedback.innerHTML = '<span class="text-danger small"><i class="bi bi-x-circle me-1"></i>No se pudo determinar el tipo de turno.</span>';
        return;
    }

    feedback.innerHTML = '<span class="text-secondary small"><span class="spinner-border spinner-border-sm me-1"></span>Verificando paciente...</span>';

    fetch(ENDPOINT_AGENDA + '?action=buscar_paciente&dni=' + dni + '&id_tipo_turno=' + idTipoTurnoPreq)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.ok) {
                feedback.innerHTML = '<span class="text-danger small"><i class="bi bi-x-circle me-1"></i>Error al conectar.</span>';
                return;
            }

            if (data.encontrado) {
                // Autorellena
                document.getElementById('modalNombre').value = data.nombre || '';
                document.getElementById('modalApellido').value = data.apellido || '';
                document.getElementById('modalMedico').value = data.medico || '';
                document.getElementById('modalIdOrden').value = data.id_orden || '';
                document.getElementById('modalIdPA').value = data.id_persona_asiste || '';
                document.getElementById('btnGuardarTurnoModal').disabled = false;
                feedback.innerHTML = '<span class="text-success small"><i class="bi bi-check-circle me-1"></i>Paciente encontrado.</span>';
            } else {
                // Mensaje de error específico según motivo
                var icono = 'bi-x-circle';
                var color = 'text-danger';
                var accion = '';

                if (data.motivo === 'sin_admision') {
                    accion = ' <a href="index.php?modulo=menu_circuitoquirurgico&accion=admision" class="alert-link">Ir a Admisión →</a>';
                } else if (data.motivo === 'turno_duplicado') {
                    icono = 'bi-calendar-x';
                    color = 'text-warning';
                }

                feedback.innerHTML =
                    '<div class="alert alert-' + (data.motivo === 'turno_duplicado' ? 'warning' : 'danger') +
                    ' py-2 px-3 small mt-1 mb-0" style="border-radius:0.5rem;">' +
                    '<i class="bi ' + icono + ' me-1"></i>' + data.mensaje + accion + '</div>';
            }
        })
        .catch(function () {
            feedback.innerHTML = '<span class="text-danger small"><i class="bi bi-x-circle me-1"></i>Error de conexión.</span>';
        });
}, true);

// Solo números en DNI del modal
document.addEventListener('input', function (e) {
    if (e.target.id === 'modalDni') {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 8);
    }
});

function limpiarDatosPacienteModal() {
    document.getElementById('modalNombre').value = '';
    document.getElementById('modalApellido').value = '';
    document.getElementById('modalMedico').value = '';
    document.getElementById('modalIdOrden').value = '';
    document.getElementById('modalIdPA').value = '';
    document.getElementById('btnGuardarTurnoModal').disabled = true;
}

// Limpiar modal al cerrar
document.getElementById('modalNuevoTurno').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalDni').value = '';
    document.getElementById('modalDniFeedback').innerHTML = '';
    limpiarDatosPacienteModal();
});

// Submit guardar turno
document.getElementById('formNuevoTurno').addEventListener('submit', function (e) {
    e.preventDefault();

    var idPA = document.getElementById('modalIdPA').value;
    var idOrden = document.getElementById('modalIdOrden').value;
    var fecha = document.getElementById('modalFechaOculta').value;

    if (!idPA || !idOrden || !fecha || !idTipoTurnoPreq) {
        Swal.fire({ icon: 'error', title: 'Datos incompletos', text: 'Verificá que el paciente esté seleccionado.', confirmButtonColor: '#d33' });
        return;
    }

    var btn = document.getElementById('btnGuardarTurnoModal');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

    fetch(ENDPOINT_AGENDA + '?action=programar_turno', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_persona_asiste: parseInt(idPA),
            id_orden: parseInt(idOrden),
            id_tipo_turno: idTipoTurnoPreq,
            fecha_hora: fecha + ' 00:00:00'
        })
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalNuevoTurno')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Turno guardado',
                    text: 'El turno prequirúrgico fue programado correctamente.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                }).then(function () {
                    renderCalendar();
                    if (diaSeleccionado) cargarTurnosDia(diaSeleccionado);
                });
            } else {
                var msg = data.errores
                    ? '<ul class="text-start mt-2">' + data.errores.map(function (e) { return '<li class="mb-1">' + e + '</li>'; }).join('') + '</ul>'
                    : (data.error || 'Error desconocido.');
                Swal.fire({ icon: 'error', title: 'No se pudo guardar', html: msg, confirmButtonColor: '#d33' });
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-calendar-check me-1"></i> Guardar Turno';
            }
        })
        .catch(function () {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#d33' });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-calendar-check me-1"></i> Guardar Turno';
        });
});

/* ── Buscador de Turnos ── */
function ejecutarBusquedaTurnos() {
    var filtro = document.getElementById('filtroTipoTurno').value;
    var valor = document.getElementById('valorBuscadorTurnos').value.trim();
    buscarTurnos(filtro, valor);
}

function buscarTurnos(filtro, valor) {
    var resultados = document.getElementById('resultadosBuscador');
    var tbody = document.getElementById('tbodyBuscador');
    var empty = document.getElementById('emptyBuscador');

    resultados.classList.remove('d-none');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-secondary">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Buscando...</td></tr>';
    empty.classList.add('d-none');

    var url = ENDPOINT_AGENDA + '?action=buscar_turnos&filtro=' + encodeURIComponent(filtro);
    if (valor.length >= 2) url += '&q=' + encodeURIComponent(valor);

    fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            tbody.innerHTML = '';
            if (!data.ok || !data.turnos || data.turnos.length === 0) {
                empty.classList.remove('d-none');
                return;
            }
            empty.classList.add('d-none');
            data.turnos.forEach(function (t) {
                var dt = new Date(t.fecha_hora);
                var fecha = dt.toLocaleDateString('es-AR');
                var badgeClass = t.estado_codigo === 'PROG' ? 'bg-primary-subtle text-primary'
                    : t.estado_codigo === 'CONF' ? 'bg-success-subtle text-success'
                        : t.estado_codigo === 'AUS' ? 'bg-warning-subtle text-warning'
                            : t.estado_codigo === 'CANC' ? 'bg-danger-subtle text-danger'
                                : 'bg-secondary-subtle text-secondary';
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="fw-semibold">' + t.apellido + ', ' + t.nombre + '</td>' +
                    '<td class="text-secondary font-monospace">' + t.dni + '</td>' +
                    '<td>' + fecha + '</td>' +
                    '<td class="text-secondary small">' + t.tipo_turno + '</td>' +
                    '<td><span class="badge ' + badgeClass + '">' + t.estado + '</span></td>';
                tbody.appendChild(tr);
            });
        })
        .catch(function () {
            tbody.innerHTML = '';
            empty.classList.remove('d-none');
        });
}

/* ── Inicialización ── */

fetch(ENDPOINT_AGENDA + '?action=buscar_paciente&dni=00000000&id_tipo_turno=0')
    .catch(function () { });

fetch(ENDPOINT_AGENDA + '?action=obtener_tipo_turno_preq')
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok && data.id_tipo_turno) {
            idTipoTurnoPreq = data.id_tipo_turno;
        }
    })
    .catch(function () { });

renderCalendar();