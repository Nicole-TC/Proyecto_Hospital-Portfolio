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

/* ── Feriados Argentina ── */
let feriadosCache = {};

function cargarFeriados(anio, callback) {
    if (feriadosCache[anio]) {
        callback(feriadosCache[anio]);
        return;
    }
    fetch('https://date.nager.at/api/v3/PublicHolidays/' + anio + '/AR')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            feriadosCache[anio] = {};
            data.forEach(function (f) {
                feriadosCache[anio][f.date] = f.localName;
            });
            callback(feriadosCache[anio]);
        })
        .catch(function () {
            feriadosCache[anio] = {};
            callback({});
        });
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
    const anio = weekStart.getFullYear();
    cargarFeriados(anio, function (feriados) {
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

    // Recolectar todas las fechas de la semana
    var fechas = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        fechas.push(d.toISOString().split('T')[0]);
    }

    // Traer cupos de todos los días en paralelo
    var cuposSemana = {};
    var pendientes = fechas.length;

    fechas.forEach(function (fecha) {
        fetch(ENDPOINT_AGENDA + '?action=obtener_cupos&fecha=' + fecha)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                cuposSemana[fecha] = data.ok ? data : { total: 0, ocupados: 0, libres: 0 };
            })
            .catch(function () {
                cuposSemana[fecha] = { total: CUPOS_DEFAULT, ocupados: 0, libres: CUPOS_DEFAULT };
            })
            .finally(function () {
                pendientes--;
                if (pendientes === 0) _pintarCalendario(weekStart, today, feriados, cuposSemana);
            });
    });
}

function _pintarCalendario(weekStart, today, feriados, cuposSemana) {
    const grid = document.getElementById('calGrid');
    grid.innerHTML = '';

    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        const isToday = d.getTime() === today.getTime();
        const isPast = d < today;
        const dateStr = d.toISOString().split('T')[0];
        const esFeriado = feriados[dateStr];
        const cupos = cuposSemana[dateStr] || { total: 0, ocupados: 0, libres: 0 };

        var barColor = 'bg-success';
        if (cupos.total === 0) barColor = 'bg-secondary';
        else if (cupos.libres === 0) barColor = 'bg-danger';
        else if (cupos.libres <= 3) barColor = 'bg-warning';

        var pct = cupos.total > 0 ? Math.round((cupos.ocupados / cupos.total) * 100) : 0;

        var dispTexto = '';
        if (cupos.total === 0) {
            dispTexto = '<div class="text-secondary mt-1" style="font-size:10px;">Sin cupos configurados</div>';
        } else if (cupos.libres === 0) {
            dispTexto = '<div class="text-danger mt-1" style="font-size:10px;">Sin disponibilidad</div>';
        } else {
            dispTexto =
                '<div class="d-flex justify-content-center gap-1 mt-1">' +
                '<span class="badge bg-success-subtle text-success" style="font-size:9px;">' + cupos.libres + ' libres</span>' +
                '<span class="badge bg-secondary-subtle text-secondary" style="font-size:9px;">' + cupos.ocupados + ' ocup.</span>' +
                '</div>';
        }

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
            (esFeriado
                ? '<div class="badge bg-warning-subtle text-warning mb-1" style="font-size:9px;white-space:normal;">' + esFeriado + '</div>'
                : ''
            ) +
            dispTexto +
            (cupos.total > 0
                ? '<div class="progress mt-1" style="height:4px;border-radius:99px;"><div class="progress-bar ' + barColor + '" style="width:' + pct + '%"></div></div>'
                : ''
            ) +
            '</div>' +
            '</div>';
        grid.appendChild(col);
    }
}

document.getElementById('btnPrevWeek').addEventListener('click', function () { currentWeekOffset--; renderCalendar(); });
document.getElementById('btnNextWeek').addEventListener('click', function () { currentWeekOffset++; renderCalendar(); });
document.getElementById('btnToday').addEventListener('click', function () { currentWeekOffset = 0; renderCalendar(); });

renderCalendar();

/* ── Panel de Cupos ── */
const DIAS_HABILES = ['Lunes', 'Martes', 'Miércoles'];
const CUPOS_DEFAULT = 20;
const ENDPOINT_AGENDA = 'app/cir-api/endpoints/agenda-e.php';

function getCuposDesdeDB(callback) {
    var diasMap = obtenerFechasDiasHabiles();
    var fechas = Object.values(diasMap);
    var cupos = {};
    var pendientes = fechas.length;

    fechas.forEach(function (fecha) {
        fetch(ENDPOINT_AGENDA + '?action=obtener_cupos&fecha=' + fecha)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                cupos[fecha] = data.ok ? data.total : CUPOS_DEFAULT;
            })
            .catch(function () {
                cupos[fecha] = CUPOS_DEFAULT;
            })
            .finally(function () {
                pendientes--;
                if (pendientes === 0) callback(cupos);
            });
    });
}

function obtenerFechasDiasHabiles() {
    var weekStart = getWeekStart(currentWeekOffset);
    var map = {};
    DIAS_HABILES.forEach(function (dia, i) {
        var d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        map[dia] = d.toISOString().split('T')[0];
    });
    return map;
}

function renderPanelCupos(cuposDB) {
    var panel = document.getElementById('panelCupos');
    if (!panel) return;
    var diasMap = obtenerFechasDiasHabiles();
    panel.innerHTML = '';

    DIAS_HABILES.forEach(function (dia) {
        var fecha = diasMap[dia];
        var total = cuposDB && cuposDB[fecha] !== undefined ? cuposDB[fecha] : CUPOS_DEFAULT;

        var div = document.createElement('div');
        div.className = 'mb-4';
        div.innerHTML =
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
            '<span class="fw-semibold">' + dia + '</span>' +
            '<span class="badge bg-success-subtle text-success" style="font-size:11px;">por persona</span>' +
            '</div>' +
            '<div class="input-group">' +
            '<button class="btn btn-outline-secondary" type="button" data-dia="' + dia + '" data-action="menos">−</button>' +
            '<input type="number" class="form-control text-center fw-bold" id="cupo-' + dia + '" value="' + total + '" min="0" max="100">' +
            '<button class="btn btn-outline-secondary" type="button" data-dia="' + dia + '" data-action="mas">+</button>' +
            '</div>' +
            '<div class="text-secondary mt-1" style="font-size:11px;" id="label-' + dia + '">' +
            '<i class="bi bi-people me-1"></i>' + total + ' personas como máximo' +
            '</div>' +
            (dia !== 'Miércoles' ? '<hr class="mt-3">' : '');
        panel.appendChild(div);
    });
}

/* ── Toggle dropdown cupos ── */
function abrirPanelCupos() {
    var panel = document.getElementById('panelDropdownCupos');
    panel.style.display = 'block';
    panel.getBoundingClientRect();
    panel.style.opacity = '1';
    panel.style.transform = 'translateY(0)';

    var contenedor = document.getElementById('panelCupos');
    contenedor.innerHTML = '<div class="text-center text-secondary small py-3"><div class="spinner-border spinner-border-sm me-2"></div>Cargando cupos...</div>';

    getCuposDesdeDB(function (cuposDB) {
        renderPanelCupos(cuposDB);
    });
}

function cerrarPanelCupos() {
    var panel = document.getElementById('panelDropdownCupos');
    panel.style.opacity = '0';
    panel.style.transform = 'translateY(-8px)';
    setTimeout(function () { panel.style.display = 'none'; }, 200);
}

document.getElementById('btnToggleCupos').addEventListener('click', function (e) {
    e.stopPropagation();
    var panel = document.getElementById('panelDropdownCupos');
    if (panel.style.display === 'none' || panel.style.display === '') {
        abrirPanelCupos();
    } else {
        cerrarPanelCupos();
    }
});

/* ── Delegación de eventos para botones dentro del panel ── */
document.addEventListener('click', function (e) {

    var wrapper = document.getElementById('wrapperCupos');
    if (wrapper && !wrapper.contains(e.target)) {
        cerrarPanelCupos();
    }

    if (e.target.id === 'btnCerrarCupos') {
        cerrarPanelCupos();
    }

    var btn = e.target.closest('[data-action]');
    if (btn) {
        var dia = btn.dataset.dia;
        var input = document.getElementById('cupo-' + dia);
        var label = document.getElementById('label-' + dia);
        var val = parseInt(input.value) || 0;
        if (btn.dataset.action === 'mas') val++;
        else if (val > 0) val--;
        input.value = val;
        label.innerHTML = '<i class="bi bi-people me-1"></i>' + val + ' personas como máximo';
    }

    if (e.target.id === 'btnGuardarCupos' || e.target.closest('#btnGuardarCupos')) {
        var diasMap = obtenerFechasDiasHabiles();
        var cupos = {};
        DIAS_HABILES.forEach(function (dia) {
            var input = document.getElementById('cupo-' + dia);
            cupos[diasMap[dia]] = parseInt(input.value) || 0;
        });

        fetch(ENDPOINT_AGENDA + '?action=guardar_cupos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cupos: cupos })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var alerta = document.getElementById('alertCuposGuardados');
                if (data.ok) {
                    alerta.className = 'alert alert-success small mt-3';
                    alerta.innerHTML = '<i class="bi bi-check-circle me-1"></i> ' + data.mensaje;
                    renderCalendar();
                } else {
                    alerta.className = 'alert alert-danger small mt-3';
                    alerta.innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + (data.error || 'Error al guardar.');
                }
                alerta.classList.remove('d-none');
                setTimeout(function () { alerta.classList.add('d-none'); }, 3500);
            })
            .catch(function () {
                var alerta = document.getElementById('alertCuposGuardados');
                alerta.className = 'alert alert-danger small mt-3';
                alerta.innerHTML = '<i class="bi bi-x-circle me-1"></i> No se pudo conectar con el servidor.';
                alerta.classList.remove('d-none');
                setTimeout(function () { alerta.classList.add('d-none'); }, 3500);
            });
    }
});