/* ── Constantes ── */
const DAYS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
const MONTHS_LONG = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
const ENDPOINT_AGENDA = 'app/cir-api/endpoints/agenda-e.php';

let currentWeekOffset = 0;
let currentMonthOffset = 0;
let currentView = 'week';     // 'week' | 'month'
let diaSeleccionado = null;
let idTipoTurnoPreq = null;
let cuposCache = {};

// Config activa: { sin_config, dias_activos[], cupos_total, hora_inicio }
let configActiva = { sin_config: true, dias_activos: [], cupos_total: 0, hora_inicio: null };

/* Mapa: número de getDay() => clave en dias_activos */
const DAY_KEY_MAP = { 1: 'lunes', 2: 'martes', 3: 'miercoles', 4: 'jueves', 5: 'viernes', 6: 'sabado', 0: 'domingo' };

function diaHabilitado(dateObj) {
    if (configActiva.sin_config) return true;   // sin config => mostrar todo
    var key = DAY_KEY_MAP[dateObj.getDay()];
    return configActiva.dias_activos.indexOf(key) !== -1;
}

function cargarConfigActiva(callback) {
    if (!idTipoTurnoPreq) { callback(); return; }
    // Usar el ÚLTIMO día del período visible como fecha_ref y el PRIMERO como fecha_inicio
    // Así una config que empiece el miércoles se encuentra aunque el lunes sea antes
    var fechaRef, fechaInicio;
    if (currentView === 'week') {
        // Lunes → Domingo de la semana visible
        var weekStart = getWeekStart(currentWeekOffset);
        var sunday = new Date(weekStart);
        sunday.setDate(weekStart.getDate() + 6);
        fechaInicio = weekStart.toISOString().split('T')[0];
        fechaRef    = sunday.toISOString().split('T')[0];
    } else {
        // Primer y último día del mes visible
        var ref = getMonthReference(currentMonthOffset);
        var firstDay = new Date(ref.getFullYear(), ref.getMonth(), 1);
        var lastDay  = new Date(ref.getFullYear(), ref.getMonth() + 1, 0);
        fechaInicio = firstDay.toISOString().split('T')[0];
        fechaRef    = lastDay.toISOString().split('T')[0];
    }
    var url = ENDPOINT_AGENDA + '?action=obtener_config_activa'
            + '&id_tipo_turno=' + idTipoTurnoPreq
            + '&fecha=' + fechaRef
            + '&fecha_inicio=' + fechaInicio;
    fetch(url)
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (data.ok) {
                configActiva.sin_config   = data.sin_config  || false;
                configActiva.dias_activos = data.dias_activos || [];
                configActiva.cupos_total  = data.cupos_total  || 0;
                configActiva.hora_inicio  = data.hora_inicio  || null;
            }
        })
        .catch(function(){ /* mantener config anterior */ })
        .finally(callback);
}

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

/* ── Helpers de fecha ── */
function getWeekStart(offset) {
    const now = new Date();
    const monday = new Date(now);
    monday.setDate(now.getDate() - now.getDay() + 1 + offset * 7);
    monday.setHours(0, 0, 0, 0);
    return monday;
}

function getMonthReference(offset) {
    // Devuelve un Date apuntando al primer día del mes con el offset aplicado
    const now = new Date();
    const ref = new Date(now.getFullYear(), now.getMonth() + offset, 1);
    return ref;
}

/* ── Render principal ── */
function renderCalendar() {
    if (currentView === 'week') {
        renderCalendarSemana();
    } else {
        renderCalendarMes();
    }
}

/* ════════════════════════════════
   VISTA SEMANAL
════════════════════════════════ */
function renderCalendarSemana() {
    const weekStart = getWeekStart(currentWeekOffset);
    cargarFeriados(weekStart.getFullYear(), function (feriados) {
        _renderSemanaConFeriados(feriados);
    });
}

function _renderSemanaConFeriados(feriados) {
    const grid = document.getElementById('calGrid');
    const label = document.getElementById('weekLabel');
    const weekStart = getWeekStart(currentWeekOffset);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    const today = new Date(); today.setHours(0, 0, 0, 0);

    label.textContent = weekStart.getDate() + ' ' + MONTHS[weekStart.getMonth()] +
        ' – ' + weekEnd.getDate() + ' ' + MONTHS[weekEnd.getMonth()] + ' ' + weekEnd.getFullYear();
    grid.innerHTML = '<div class="text-center text-secondary py-4 small"><div class="spinner-border spinner-border-sm text-primary me-1"></div> Cargando...</div>';

    var todasFechas = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        if (d.getDay() !== 0 && d.getDay() !== 6) {
            todasFechas.push(d);
        }
    }
    var fechasStr = todasFechas.map(function(d){ return d.toISOString().split('T')[0]; });

    var pendientes = fechasStr.length;

    fechasStr.forEach(function (fecha) {
        var url = ENDPOINT_AGENDA + '?action=turnos_del_dia&fecha=' + fecha;
        if (idTipoTurnoPreq) url += '&id_tipo_turno=' + idTipoTurnoPreq;

        fetch(url, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                cuposCache[fecha] = {
                    ocupados:    data.ok ? (data.ocupados ?? data.turnos.length) : 0,
                    cupos_total: data.ok ? data.cupos_total : 0,
                    activo:      data.ok ? data.cupo_activo : false,
                    sin_config:  data.ok ? (data.sin_config || false) : false
                };
            })
            .catch(function () {
                cuposCache[fecha] = { ocupados: 0, cupos_total: 0, activo: false, sin_config: false };
            })
            .finally(function () {
                pendientes--;
                if (pendientes === 0) {
                    var fechasHabilitadas = todasFechas.filter(function(d){
                        var fStr = d.toISOString().split('T')[0];
                        return cuposCache[fStr] && cuposCache[fStr].activo;
                    });
                    if (fechasHabilitadas.length === 0) {
                        grid.innerHTML = '<div class="alert alert-warning m-3"><i class="bi bi-exclamation-triangle me-2"></i>No hay días habilitados en la configuración de cupos. <a href="index.php?modulo=menu_circuitoquirurgico&accion=config_cupos" class="alert-link">Configurar cupos</a></div>';
                        return;
                    }
                    _pintarSemana(fechasHabilitadas, today, feriados);
                }
            });
    });
}

function _pintarSemana(diasHabilitados, today, feriados) {
    const grid = document.getElementById('calGrid');
    grid.innerHTML = '';

    var cols = diasHabilitados.length;
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(' + cols + ', 1fr)';
    grid.style.gap = '8px';

    diasHabilitados.forEach(function(d) {
        const isToday = d.getTime() === today.getTime();
        const isPast  = d < today;
        const dateStr = d.toISOString().split('T')[0];
        const esFeriado = feriados[dateStr];
        const dataCupo  = cuposCache[dateStr] || { ocupados: 0, cupos_total: configActiva.cupos_total, activo: true };
        const ocupados   = dataCupo.ocupados;
        const cupos_total = dataCupo.cupos_total;
        const esSeleccionado = dateStr === diaSeleccionado;

        var borderClass = '';
        if (esSeleccionado) borderClass = 'border-primary border-2';
        else if (isToday)   borderClass = 'border-primary';
        else if (esFeriado) borderClass = 'border-warning';

        var isFull     = cupos_total > 0 && ocupados >= cupos_total;
        var barColor   = isFull ? 'bg-danger' : (ocupados / cupos_total > 0.7 ? 'bg-warning' : 'bg-success');
        var barPercent = cupos_total > 0 ? Math.min(100, Math.round((ocupados / cupos_total) * 100)) : 0;
        var textCupos  = cupos_total > 0
            ? ('<span class="fw-bold">' + ocupados + '</span> / ' + cupos_total + ' turnos')
            : (ocupados + ' turno' + (ocupados !== 1 ? 's' : ''));
        var colorCupos = isFull ? 'text-danger' : (cupos_total > 0 && ocupados / cupos_total > 0.7 ? 'text-warning' : 'text-primary');
        var badgeOcupados = '<div class="small fw-semibold ' + colorCupos + '" style="font-size:11px;">' + textCupos + '</div>';
        var progressBar = cupos_total > 0
            ? '<div class="progress mt-2 mx-auto" style="height:6px;width:80%;"><div class="progress-bar ' + barColor + '" style="width:' + barPercent + '%"></div></div>'
            : '';
        var llenaLabel = isFull ? '<div class="badge bg-danger-subtle text-danger mt-1" style="font-size:9px;">CUPO LLENO</div>' : '';

        const col = document.createElement('div');
        col.innerHTML =
            '<div class="card border ' + borderClass + ' h-100 dia-calendario' + (isPast ? ' opacity-50' : '') + '" ' +
            'data-fecha="' + dateStr + '" ' +
            'style="border-radius:0.65rem;min-height:110px;cursor:pointer;transition:box-shadow .15s,transform .15s;' +
            (esSeleccionado ? 'box-shadow:0 0 0 3px rgba(13,110,253,.25);' : '') + '">' +
            '<div class="card-body p-2 text-center">' +
            '<div class="text-secondary" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">' + DAYS[d.getDay()] + '</div>' +
            (isToday
                ? '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto my-1 fw-bold" style="width:32px;height:32px;font-size:15px;">' + d.getDate() + '</div>'
                : '<div class="fw-bold fs-5 my-1 ' + (esFeriado ? 'text-warning' : '') + '">' + d.getDate() + '</div>') +
            (esFeriado ? '<div class="badge bg-warning-subtle text-warning mb-1" style="font-size:9px;white-space:normal;">' + esFeriado + '</div>' : '') +
            '<div class="mt-1">' + badgeOcupados + progressBar + llenaLabel + '</div>' +
            '</div></div>';

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
    });
}

/* ════════════════════════════════
   VISTA MENSUAL
════════════════════════════════ */
function renderCalendarMes() {
    const ref = getMonthReference(currentMonthOffset);
    cargarFeriados(ref.getFullYear(), function (feriados) {
        _renderMesConFeriados(ref, feriados);
    });
}

function _renderMesConFeriados(ref, feriados) {
    const label = document.getElementById('weekLabel');
    label.textContent = MONTHS_LONG[ref.getMonth()] + ' ' + ref.getFullYear();

    // Primer y último día del mes
    const primerDia = new Date(ref.getFullYear(), ref.getMonth(), 1);
    const ultimoDia = new Date(ref.getFullYear(), ref.getMonth() + 1, 0);

    // Fechas del mes
    var fechas = [];
    for (let d = 1; d <= ultimoDia.getDate(); d++) {
        const fecha = new Date(ref.getFullYear(), ref.getMonth(), d);
        fechas.push(fecha.toISOString().split('T')[0]);
    }

    // Cargar cupos en paralelo
    var pendientes = fechas.length;

    fechas.forEach(function (fecha) {
        var url = ENDPOINT_AGENDA + '?action=turnos_del_dia&fecha=' + fecha;
        if (idTipoTurnoPreq) url += '&id_tipo_turno=' + idTipoTurnoPreq;

        fetch(url, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                cuposCache[fecha] = {
                    ocupados: data.ok ? (data.ocupados ?? data.turnos.length) : 0,
                    cupos_total: data.ok ? data.cupos_total : 0,
                    activo: data.ok ? data.cupo_activo : true
                };
            })
            .catch(function () { 
                cuposCache[fecha] = { ocupados: 0, cupos_total: 0, activo: true };
            })
            .finally(function () {
                pendientes--;
                if (pendientes === 0) _pintarMes(ref, primerDia, ultimoDia, feriados);
            });
    });
}

function _pintarMes(ref, primerDia, ultimoDia, feriados) {
    const grid = document.getElementById('calGrid');
    const today = new Date(); today.setHours(0, 0, 0, 0);

    grid.innerHTML = '';
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(5, 1fr)';
    grid.style.gap = '4px';

    // Encabezados
    const DAYS_ORDER = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'];
    DAYS_ORDER.forEach(function (d) {
        const hdr = document.createElement('div');
        hdr.className = 'text-center text-secondary pb-1';
        hdr.style.cssText = 'font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;';
        hdr.textContent = d;
        grid.appendChild(hdr);
    });

    var primerDiaHabil = new Date(primerDia);
    while (primerDiaHabil.getDay() === 0 || primerDiaHabil.getDay() === 6) {
        primerDiaHabil.setDate(primerDiaHabil.getDate() + 1);
    }
    var startDow = 0;
    if (primerDiaHabil.getMonth() === primerDia.getMonth()) {
        startDow = primerDiaHabil.getDay() - 1;
    }

    for (let i = 0; i < startDow; i++) {
        grid.appendChild(document.createElement('div'));
    }

    for (let d = 1; d <= ultimoDia.getDate(); d++) {
        const fecha = new Date(ref.getFullYear(), ref.getMonth(), d);
        if (fecha.getDay() === 0 || fecha.getDay() === 6) continue;

        const dateStr = fecha.toISOString().split('T')[0];
        const isToday = fecha.getTime() === today.getTime();
        const isPast = fecha < today;
        const esFeriado = feriados[dateStr];
        const dataCupo = cuposCache[dateStr] || { ocupados: 0, cupos_total: 0, activo: false };
        const activo = dataCupo.activo;
        const ocupados = dataCupo.ocupados;
        const cupos_total = dataCupo.cupos_total;
        const esSeleccionado = dateStr === diaSeleccionado;

        const cell = document.createElement('div');

        if (!activo) {
            cell.innerHTML =
                '<div class="card border border-light bg-light opacity-40" style="border-radius:0.55rem;height:90px;">' +
                '<div class="card-body p-1 text-center text-secondary d-flex flex-column justify-content-center">' +
                '<div class="fw-semibold" style="font-size:14px;">' + d + '</div>' +
                '</div></div>';
        } else {
            var borderClass = '';
            if (esSeleccionado) borderClass = 'border-primary border-2';
            else if (isToday) borderClass = 'border-primary';
            else if (esFeriado) borderClass = 'border-warning';

            var isFull = cupos_total > 0 && ocupados >= cupos_total;
            var barColor = isFull ? 'bg-danger' : (cupos_total > 0 && ocupados / cupos_total > 0.7 ? 'bg-warning' : 'bg-success');
            var barPercent = cupos_total > 0 ? Math.min(100, Math.round((ocupados / cupos_total) * 100)) : 0;
            var textCupos = cupos_total > 0 ? (ocupados + '/' + cupos_total) : (ocupados > 0 ? ocupados : '-');
            var badgeOcupados = cupos_total > 0 || ocupados > 0
                ? '<span class="badge ' + (isFull ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary') + ' mt-1" style="font-size:9px;">' + textCupos + '</span>'
                : '';
            var progressBar = cupos_total > 0 ?
                '<div class="progress mt-1 mx-auto" style="height:3px;width:60%;"><div class="progress-bar ' + barColor + '" style="width:' + barPercent + '%"></div></div>' : '';

            cell.innerHTML =
                '<div class="card border ' + borderClass + ' dia-calendario' + (isPast ? ' opacity-50' : '') + '" ' +
                'data-fecha="' + dateStr + '" ' +
                'style="border-radius:0.55rem;height:90px;overflow:hidden;cursor:pointer;transition:box-shadow .15s;' +
                (esSeleccionado ? 'box-shadow:0 0 0 3px rgba(13,110,253,.25);' : '') + '">' +
                '<div class="card-body p-1 text-center">' +
                (isToday
                    ? '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mt-1 fw-bold" style="width:26px;height:26px;font-size:13px;">' + d + '</div>'
                    : '<div class="fw-semibold ' + (esFeriado ? 'text-warning' : '') + '" style="font-size:14px;">' + d + '</div>'
                ) +
                (esFeriado
                    ? '<div style="font-size:8px;color:var(--bs-warning);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;" class="mb-1">' + esFeriado + '</div>'
                    : '') +
                '<div>' + badgeOcupados + progressBar + '</div>' +
                '</div></div>';

            cell.querySelector('.dia-calendario').addEventListener('click', function () {
                var fecha = this.dataset.fecha;
                if (diaSeleccionado === fecha) {
                    diaSeleccionado = null;
                    cerrarPanelDia();
                    if (typeof expandirCalendario === 'function') expandirCalendario();
                    renderCalendar();
                } else {
                    diaSeleccionado = fecha;
                    renderCalendar();
                    abrirPanelDia(fecha);
                    if (typeof colapsarCalendario === 'function') colapsarCalendario();
                }
            });
        }
        grid.appendChild(cell);
    }
}


/* ── Panel de turnos del día ── */
function abrirPanelDia(fecha) {
    var panel = document.getElementById('panelDia');
    if (!panel) return;

    var d = new Date(fecha + 'T00:00:00');
    var titulo = DAYS[d.getDay()] + ' ' + d.getDate() + ' de ' + MONTHS_LONG[d.getMonth()];

    document.getElementById('panelDiaTitulo').textContent = titulo;
    panel.classList.remove('d-none');
    document.getElementById('modalFechaOculta').value = fecha;

    // Lógica para deshabilitar botón Nuevo Turno
    var btnNuevo = document.querySelector('#panelDia button[data-bs-target="#modalNuevoTurno"]');
    var msgLleno = document.getElementById('msg-cupo-lleno');
    if (!msgLleno) {
        msgLleno = document.createElement('span');
        msgLleno.id = 'msg-cupo-lleno';
        msgLleno.className = 'text-danger small fw-semibold me-3';
        btnNuevo.parentNode.insertBefore(msgLleno, btnNuevo);
    }

    // Indicador de cupos en el panel
    var indicadorCupos = document.getElementById('indicador-cupos-panel');
    if (!indicadorCupos) {
        indicadorCupos = document.createElement('span');
        indicadorCupos.id = 'indicador-cupos-panel';
        indicadorCupos.style.fontSize = '12px';
        btnNuevo.parentNode.insertBefore(indicadorCupos, msgLleno);
    }

    var dataCupo    = cuposCache[fecha] || { ocupados: 0, cupos_total: 0, activo: false };
    var ocupados    = dataCupo.ocupados;
    var cupos_total = dataCupo.cupos_total;
    var diaActivo   = dataCupo.activo;
    var cupoLleno   = cupos_total > 0 && ocupados >= cupos_total;

    // Mostrar indicador X/Y si hay config de cupo
    if (cupos_total > 0) {
        var colorBadge = cupoLleno
            ? 'badge border fw-semibold me-2 bg-danger-subtle text-danger'
            : (ocupados / cupos_total > 0.7
                ? 'badge border fw-semibold me-2 bg-warning-subtle text-warning'
                : 'badge border fw-semibold me-2 bg-success-subtle text-success');
        indicadorCupos.className = colorBadge;
        indicadorCupos.textContent = ocupados + ' / ' + cupos_total + ' turnos asignados';
        indicadorCupos.classList.remove('d-none');
    } else {
        indicadorCupos.className = 'd-none';
    }

    const todayObj = new Date(); todayObj.setHours(0,0,0,0);
    const isPastObj = d < todayObj;

    if (isPastObj) {
        btnNuevo.disabled = true;
        msgLleno.textContent = 'No se pueden asignar turnos en días pasados';
        msgLleno.classList.remove('d-none');
    } else if (!diaActivo) {
        btnNuevo.disabled = true;
        msgLleno.textContent = 'Día no habilitado en la configuración de cupos';
        msgLleno.classList.remove('d-none');
    } else if (cupoLleno) {
        btnNuevo.disabled = true;
        msgLleno.textContent = 'Cupo lleno (' + ocupados + '/' + cupos_total + ' turnos)';
        msgLleno.classList.remove('d-none');
    } else {
        btnNuevo.disabled = false;
        msgLleno.classList.add('d-none');
    }

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

    var url = ENDPOINT_AGENDA + '?action=turnos_del_dia&fecha=' + fecha;
    if (idTipoTurnoPreq) url += '&id_tipo_turno=' + idTipoTurnoPreq;

    fetch(url, { cache: 'no-store' })
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

/* ── Toggle vista Semana / Mes ── */
document.getElementById('btnVistaSemana').addEventListener('click', function () {
    if (currentView === 'week') return;
    currentView = 'week';
    currentWeekOffset = 0;
    diaSeleccionado = null;
    cerrarPanelDia();
    document.getElementById('btnVistaSemana').classList.replace('btn-outline-secondary', 'btn-primary');
    document.getElementById('btnVistaMes').classList.replace('btn-primary', 'btn-outline-secondary');
    document.getElementById('calTitulo').textContent = 'Calendario Semanal';
    renderCalendar();
});

document.getElementById('btnVistaMes').addEventListener('click', function () {
    if (currentView === 'month') return;
    currentView = 'month';
    currentMonthOffset = 0;
    diaSeleccionado = null;
    cerrarPanelDia();
    document.getElementById('btnVistaMes').classList.replace('btn-outline-secondary', 'btn-primary');
    document.getElementById('btnVistaSemana').classList.replace('btn-primary', 'btn-outline-secondary');
    document.getElementById('calTitulo').textContent = 'Calendario Mensual';
    renderCalendar();
});

/* ── Navegación (prev / next / hoy) ── */
document.getElementById('btnPrevWeek').addEventListener('click', function () {
    diaSeleccionado = null;
    cerrarPanelDia();
    if (currentView === 'week') { currentWeekOffset--; }
    else { currentMonthOffset--; }
    cargarConfigActiva(function() { renderCalendar(); });
});

document.getElementById('btnNextWeek').addEventListener('click', function () {
    diaSeleccionado = null;
    cerrarPanelDia();
    if (currentView === 'week') { currentWeekOffset++; }
    else { currentMonthOffset++; }
    cargarConfigActiva(function() { renderCalendar(); });
});

document.getElementById('btnToday').addEventListener('click', function () {
    currentWeekOffset = 0;
    currentMonthOffset = 0;
    diaSeleccionado = null;
    cerrarPanelDia();
    cargarConfigActiva(function() { renderCalendar(); });
});

/* ── Modal Nuevo Turno ── */
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
                document.getElementById('modalNombre').value = data.nombre || '';
                document.getElementById('modalApellido').value = data.apellido || '';
                document.getElementById('modalMedico').value = data.medico || '';
                document.getElementById('modalIdOrden').value = data.id_orden || '';
                document.getElementById('modalIdPA').value = data.id_persona_asiste || '';
                document.getElementById('btnGuardarTurnoModal').disabled = false;
                feedback.innerHTML = '<span class="text-success small"><i class="bi bi-check-circle me-1"></i>Paciente encontrado.</span>';
            } else {
                if (data.motivo === 'sin_admision') {
                    feedback.innerHTML =
                        '<div class="border border-danger-subtle bg-danger-subtle rounded-3 p-3 mt-2">' +
                        '<div class="d-flex align-items-start gap-2 mb-2">' +
                        '<i class="bi bi-person-x-fill text-danger fs-5 flex-shrink-0 mt-1"></i>' +
                        '<div><div class="fw-semibold text-danger small">Paciente sin admisión</div>' +
                        '<div class="text-secondary small">' + data.mensaje + '</div></div></div>' +
                        '<a href="index.php?modulo=menu_circuitoquirurgico&accion=admision" ' +
                        'class="btn btn-danger btn-sm w-100 fw-semibold mt-1" style="border-radius:0.5rem;">' +
                        '<i class="bi bi-box-arrow-right me-1"></i> Ir a Admisión</a></div>';
                } else if (data.motivo === 'turno_duplicado') {
                    feedback.innerHTML =
                        '<div class="border border-warning-subtle bg-warning-subtle rounded-3 p-3 mt-2">' +
                        '<div class="d-flex align-items-center gap-2">' +
                        '<i class="bi bi-calendar-x-fill text-warning fs-5 flex-shrink-0"></i>' +
                        '<div><div class="fw-semibold text-warning small">Turno duplicado</div>' +
                        '<div class="text-secondary small">' + data.mensaje + '</div></div></div></div>';
                } else {
                    feedback.innerHTML =
                        '<div class="border border-danger-subtle bg-danger-subtle rounded-3 p-3 mt-2">' +
                        '<div class="d-flex align-items-center gap-2">' +
                        '<i class="bi bi-x-circle-fill text-danger fs-5 flex-shrink-0"></i>' +
                        '<div class="text-secondary small">' + data.mensaje + '</div></div></div>';
                }
            }
        })
        .catch(function () {
            feedback.innerHTML = '<span class="text-danger small"><i class="bi bi-x-circle me-1"></i>Error de conexión.</span>';
        });
}, true);

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

document.getElementById('modalNuevoTurno').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalDni').value = '';
    document.getElementById('modalDniFeedback').innerHTML = '';
    limpiarDatosPacienteModal();
});

document.getElementById('modalNuevoTurno').addEventListener('shown.bs.modal', function () {
    if (!dniDesdeUrl) return;
    var input = document.getElementById('modalDni');
    input.value = dniDesdeUrl;
    input.dispatchEvent(new Event('blur'));
    dniDesdeUrl = '';
});

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
var paginaActual = 1;

// Mensaje inicial (igual que gestion_usuarios.js)
(function initBuscador() {
    var tbody = document.getElementById('tbodyBuscador');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-secondary">' +
                          '<i class="bi bi-search me-2"></i> Ingrese un criterio de búsqueda para listar los turnos.' +
                          '</td></tr>';
    }
})();

function ejecutarBusquedaTurnos() {
    paginaActual = 1;   // siempre volver a página 1 al buscar
    var filtro = document.getElementById('filtroTipoTurno').value;
    var valor = document.getElementById('valorBuscadorTurnos').value.trim();
    var limite = document.getElementById('filtroLimite').value;
    var orden = document.getElementById('filtroOrden').value;

    buscarTurnos(filtro, valor, limite, orden, paginaActual);
}

function buscarTurnos(filtro, valor, limite, orden, pagina) {
    var tbody = document.getElementById('tbodyBuscador');
    var empty = document.getElementById('emptyBuscador');

    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-secondary">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Buscando...</td></tr>';
    empty.classList.add('d-none');

    var url = ENDPOINT_AGENDA +
        '?action=buscar_turnos' +
        '&filtro=' + encodeURIComponent(filtro) +
        '&q=' + encodeURIComponent(valor) +
        '&limite=' + encodeURIComponent(limite) +
        '&orden=' + encodeURIComponent(orden) +
        '&pagina=' + encodeURIComponent(pagina);

    fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            tbody.innerHTML = '';

            if (!data.ok || !data.turnos || data.turnos.length === 0) {
                empty.classList.remove('d-none');
                document.getElementById('infoPaginacion').textContent = 'Sin resultados';
                document.getElementById('paginacionBuscador').innerHTML = '';
                return;
            }

            empty.classList.add('d-none');

            // Filas
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

            // Info "Mostrando X a Y de Z registros"
            var desde = (data.pagina - 1) * data.limite + 1;
            var hasta = Math.min(data.pagina * data.limite, data.total);
            document.getElementById('infoPaginacion').textContent =
                'Mostrando ' + desde + ' a ' + hasta + ' de ' + data.total + ' registros';

            // Paginación
            renderPaginacion(data.pagina, data.total_paginas, filtro, valor, limite, orden);
        })
        .catch(function () {
            tbody.innerHTML = '';
            empty.classList.remove('d-none');
        });
}

function renderPaginacion(paginaActualLocal, totalPaginas, filtro, valor, limite, orden) {
    var ul = document.getElementById('paginacionBuscador');
    ul.innerHTML = '';

    if (totalPaginas <= 1) return;

    // Anterior
    var liPrev = document.createElement('li');
    liPrev.className = 'page-item' + (paginaActualLocal === 1 ? ' disabled' : '');
    liPrev.innerHTML = '<a class="page-link" href="#">Anterior</a>';
    liPrev.addEventListener('click', function (e) {
        e.preventDefault();
        if (paginaActualLocal > 1) {
            paginaActual = paginaActualLocal - 1;
            buscarTurnos(filtro, valor, limite, orden, paginaActual);
        }
    });
    ul.appendChild(liPrev);

    // Números
    for (var i = 1; i <= totalPaginas; i++) {
        (function (num) {
            var li = document.createElement('li');
            li.className = 'page-item' + (num === paginaActualLocal ? ' active' : '');
            li.innerHTML = '<a class="page-link" href="#">' + num + '</a>';
            li.addEventListener('click', function (e) {
                e.preventDefault();
                paginaActual = num;
                buscarTurnos(filtro, valor, limite, orden, paginaActual);
            });
            ul.appendChild(li);
        })(i);
    }

    // Siguiente
    var liNext = document.createElement('li');
    liNext.className = 'page-item' + (paginaActualLocal === totalPaginas ? ' disabled' : '');
    liNext.innerHTML = '<a class="page-link" href="#">Siguiente</a>';
    liNext.addEventListener('click', function (e) {
        e.preventDefault();
        if (paginaActualLocal < totalPaginas) {
            paginaActual = paginaActualLocal + 1;
            buscarTurnos(filtro, valor, limite, orden, paginaActual);
        }
    });
    ul.appendChild(liNext);
}

/* ── DNI desde URL (proveniente de admisión) ── */
var dniDesdeUrl = (function() {
    var params = new URLSearchParams(window.location.search);
    var dni = params.get('dni') || '';
    if (dni) {
        params.delete('dni');
        var newUrl = window.location.pathname + '?' + params.toString() + window.location.hash;
        newUrl = newUrl.replace(/\?$/, '');
        window.history.replaceState({}, '', newUrl);
    }
    return dni;
})();

/* ── Inicialización ── */
fetch(ENDPOINT_AGENDA + '?action=obtener_tipo_turno_preq')
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok && data.id_tipo_turno) {
            idTipoTurnoPreq = data.id_tipo_turno;
        }
        // Cargar config activa ANTES de renderizar
        cargarConfigActiva(function() {
            renderCalendar();
        });
    })
    .catch(function () {
        renderCalendar();
    });