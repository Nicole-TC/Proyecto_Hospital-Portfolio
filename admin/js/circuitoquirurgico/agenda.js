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

/* ── Calendario semanal ── */
const DAYS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
let currentWeekOffset = 0;
const ENDPOINT_AGENDA = 'app/cir-api/endpoints/agenda-e.php';

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

    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        const isToday = d.getTime() === today.getTime();
        const isPast = d < today;
        const dateStr = d.toISOString().split('T')[0];
        const esFeriado = feriados[dateStr];

        const col = document.createElement('div');
        col.className = 'col';
        col.innerHTML =
            '<div class="card border ' + (isToday ? 'border-primary' : esFeriado ? 'border-warning' : '') + ' h-100" style="border-radius:0.65rem;min-height:120px;opacity:' + (isPast ? '.55' : '1') + ';">' +
            '<div class="card-body p-2 text-center">' +
            '<div class="text-secondary" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">' + DAYS[d.getDay()] + '</div>' +
            (isToday
                ? '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto my-1 fw-bold" style="width:32px;height:32px;font-size:15px;">' + d.getDate() + '</div>'
                : '<div class="fw-bold fs-5 my-1 ' + (esFeriado ? 'text-warning' : '') + '">' + d.getDate() + '</div>'
            ) +
            (esFeriado ? '<div class="badge bg-warning-subtle text-warning mb-1" style="font-size:9px;white-space:normal;">' + esFeriado + '</div>' : '') +
            '</div></div>';
        grid.appendChild(col);
    }
}

document.getElementById('btnPrevWeek').addEventListener('click', function () { currentWeekOffset--; renderCalendar(); });
document.getElementById('btnNextWeek').addEventListener('click', function () { currentWeekOffset++; renderCalendar(); });
document.getElementById('btnToday').addEventListener('click', function () { currentWeekOffset = 0; renderCalendar(); });

renderCalendar();

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
