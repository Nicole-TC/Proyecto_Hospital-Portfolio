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

    var fechas = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        fechas.push(d.toISOString().split('T')[0]);
    }

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
            (esFeriado ? '<div class="badge bg-warning-subtle text-warning mb-1" style="font-size:9px;white-space:normal;">' + esFeriado + '</div>' : '') +
            dispTexto +
            (cupos.total > 0 ? '<div class="progress mt-1" style="height:4px;border-radius:99px;"><div class="progress-bar ' + barColor + '" style="width:' + pct + '%"></div></div>' : '') +
            '</div></div>';
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
            .then(function (data) { cupos[fecha] = data.ok ? data.total : CUPOS_DEFAULT; })
            .catch(function () { cupos[fecha] = CUPOS_DEFAULT; })
            .finally(function () { pendientes--; if (pendientes === 0) callback(cupos); });
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
            '<i class="bi bi-people me-1"></i>' + total + ' personas como máximo</div>' +
            (dia !== 'Miércoles' ? '<hr class="mt-3">' : '');
        panel.appendChild(div);
    });
}

function abrirPanelCupos() {
    var panel = document.getElementById('panelDropdownCupos');
    panel.style.display = 'block';
    panel.getBoundingClientRect();
    panel.style.opacity = '1';
    panel.style.transform = 'translateY(0)';
    document.getElementById('panelCupos').innerHTML = '<div class="text-center text-secondary small py-3"><div class="spinner-border spinner-border-sm me-2"></div>Cargando cupos...</div>';
    getCuposDesdeDB(function (cuposDB) { renderPanelCupos(cuposDB); });
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
    if (panel.style.display === 'none' || panel.style.display === '') abrirPanelCupos();
    else cerrarPanelCupos();
});

/* ── Programación Detallada ── */
function cargarTurnos(fecha) {
    var url = ENDPOINT_AGENDA + '?action=obtener_turnos';
    if (fecha) url += '&fecha=' + fecha;

    fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var tbody = document.getElementById('tbodyCirugias');
            var empty = document.getElementById('emptyCirugias');
            tbody.innerHTML = '';

            if (!data.ok || !data.turnos || data.turnos.length === 0) {
                empty.style.display = '';
                return;
            }
            empty.style.display = 'none';

            data.turnos.forEach(function (t) {
                var dt = new Date(t.fecha_hora);
                var fecha = dt.toLocaleDateString('es-AR');
                var hora = dt.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

                var badgeClass = t.estado_codigo === 'PROG' ? 'bg-primary-subtle text-primary'
                    : t.estado_codigo === 'CONF' ? 'bg-success-subtle text-success'
                        : t.estado_codigo === 'AUS' ? 'bg-warning-subtle text-warning'
                            : t.estado_codigo === 'CANC' ? 'bg-danger-subtle text-danger'
                                : t.estado_codigo === 'FIN' ? 'bg-secondary-subtle text-secondary'
                                    : 'bg-secondary-subtle text-secondary';

                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="fw-semibold">' + t.apellido + ', ' + t.nombre + '</td>' +
                    '<td class="text-secondary font-monospace">' + t.dni + '</td>' +
                    '<td>' + fecha + '</td>' +
                    '<td>' + hora + '</td>' +
                    '<td class="text-secondary small">' + t.tipo_turno + '</td>' +
                    '<td><span class="badge ' + badgeClass + '">' + t.estado + '</span></td>';
                tbody.appendChild(tr);
            });
        })
        .catch(function () {
            document.getElementById('emptyCirugias').style.display = '';
        });
}

/* ── Modal Nuevo Turno ── */
function cargarTiposTurno() {
    fetch(ENDPOINT_AGENDA + '?action=obtener_tipos_turno')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.ok) return;
            var select = document.getElementById('tTipoTurno');
            select.innerHTML = '<option value="" disabled selected>Seleccione el tipo de turno</option>';
            data.tipos.forEach(function (t) {
                var opt = document.createElement('option');
                opt.value = t.id_tipo_turno;
                opt.textContent = t.descripcion;
                select.appendChild(opt);
            });
        });
}

/* ── Solo números en DNI ── */
document.addEventListener('input', function (e) {
    if (e.target.id === 'tDni') {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 8);
    }
});

/* ── Fecha mínima = hoy ── */
document.addEventListener('focus', function (e) {
    if (e.target.id === 'tFecha') {
        e.target.setAttribute('min', new Date().toISOString().split('T')[0]);
    }
}, true);

/* ── Autorelleno por DNI al perder foco ── */
document.addEventListener('blur', function (e) {
    if (e.target.id !== 'tDni') return;

    var dni = e.target.value.replace(/\D/g, '').trim();
    var feedback = document.getElementById('dniFeedback');
    var infoPaciente = document.getElementById('infoPaciente');

    document.getElementById('id_orden').value = '';
    document.getElementById('id_persona_asiste').value = '';
    document.getElementById('tNombre').value = '';
    document.getElementById('tApellido').value = '';
    document.getElementById('tEmail').value = '';
    document.getElementById('tEmail').readOnly = true;  // FIX: siempre readonly al limpiar
    infoPaciente.classList.add('d-none');
    document.getElementById('btnGuardarTurno').disabled = true;

    if (!dni || dni.length < 7) { feedback.innerHTML = ''; return; }

    feedback.innerHTML = '<span class="text-secondary"><span class="spinner-border spinner-border-sm me-1"></span>Buscando paciente...</span>';

    fetch(ENDPOINT_AGENDA + '?action=buscar_paciente&dni=' + dni)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok && data.encontrado) {
                var p = data.paciente;
                document.getElementById('id_orden').value = p.id_orden;
                document.getElementById('id_persona_asiste').value = p.id_persona_asiste;
                document.getElementById('tNombre').value = p.nombre || '';
                document.getElementById('tApellido').value = p.apellido || '';
                document.getElementById('tEmail').value = p.email || '';
                document.getElementById('tEmail').readOnly = true;
                document.getElementById('infoPacienteNombre').textContent = p.apellido + ', ' + p.nombre;
                document.getElementById('infoPacienteCirugia').textContent = 'Cirugía: ' + p.tipo_cirugia + ' — ' + p.estado_orden;
                infoPaciente.classList.remove('d-none');
                feedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Paciente encontrado.</span>';
                if (document.getElementById('tFecha').value) {
                    document.getElementById('btnGuardarTurno').disabled = false;
                }
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'DNI no registrado',
                    text: 'El paciente no está registrado o no tiene una orden médica.',
                    confirmButtonText: 'Ir a Admisión',
                    confirmButtonColor: '#3085d6',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    cancelButtonColor: '#6c757d'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=admision';
                    }
                });
                feedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>DNI no registrado.</span>';
            }
        })
        .catch(function () {
            feedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Error al conectar con el servidor.</span>';
        });
}, true);

/* ── Cupos al cambiar fecha ── */
document.addEventListener('change', function (e) {
    if (e.target.id !== 'tFecha') return;
    fetch(ENDPOINT_AGENDA + '?action=obtener_cupos&fecha=' + e.target.value)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var infoCupos = document.getElementById('infoCupos');
            var badge = document.getElementById('badgeCuposLibres');
            var btn = document.getElementById('btnGuardarTurno');
            if (data.ok) {
                infoCupos.classList.remove('d-none');
                if (data.libres === 0) {
                    badge.className = 'badge bg-danger-subtle text-danger';
                    badge.textContent = 'Sin cupos disponibles para este día';
                    btn.disabled = true;
                } else {
                    badge.className = 'badge bg-success-subtle text-success';
                    badge.textContent = data.libres + ' cupos disponibles para este día';
                    if (document.getElementById('id_persona_asiste').value) btn.disabled = false;
                }
            } else {
                infoCupos.classList.add('d-none');
            }
        });
});

/* ── Limpiar modal al cerrar ── */
document.getElementById('modalNuevoTurno').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formNuevoTurno').reset();
    document.getElementById('infoPaciente').classList.add('d-none');
    document.getElementById('infoCupos').classList.add('d-none');
    document.getElementById('dniFeedback').innerHTML = '';
    document.getElementById('turnoFeedback').classList.add('d-none');
    document.getElementById('tEmail').readOnly = true;
    document.getElementById('tEmail').value = '';
    document.getElementById('tNombre').value = '';
    document.getElementById('tApellido').value = '';
    document.getElementById('btnGuardarTurno').disabled = true;
    document.getElementById('id_orden').value = '';
    document.getElementById('id_persona_asiste').value = '';
});

/* ── Delegación de eventos panel cupos ── */
document.addEventListener('click', function (e) {
    var wrapper = document.getElementById('wrapperCupos');
    if (wrapper && !wrapper.contains(e.target)) cerrarPanelCupos();

    if (e.target.id === 'btnCerrarCupos') cerrarPanelCupos();

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
            cupos[diasMap[dia]] = parseInt(document.getElementById('cupo-' + dia).value) || 0;
        });
        fetch(ENDPOINT_AGENDA + '?action=guardar_cupos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cupos: cupos })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var alerta = document.getElementById('alertCuposGuardados');
                alerta.className = 'alert ' + (data.ok ? 'alert-success' : 'alert-danger') + ' small mt-3';
                alerta.innerHTML = '<i class="bi bi-' + (data.ok ? 'check' : 'x') + '-circle me-1"></i> ' + (data.ok ? data.mensaje : (data.error || 'Error al guardar.'));
                alerta.classList.remove('d-none');
                if (data.ok) renderCalendar();
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

/* ── Submit nuevo turno ── */
document.addEventListener('submit', function (e) {
    if (e.target.id !== 'formNuevoTurno') return;
    e.preventDefault();

    var idPA = document.getElementById('id_persona_asiste').value;
    var idOrden = document.getElementById('id_orden').value;
    var email = document.getElementById('tEmail').value.trim();
    var fecha = document.getElementById('tFecha').value;
    var hora = document.getElementById('tHora').value;
    var tipo = document.getElementById('tTipoTurno').value;

    var errores = [];
    if (!idPA || !idOrden) errores.push('El paciente no fue encontrado o no tiene orden médica.');
    if (!fecha) errores.push('La fecha es obligatoria.');
    if (!hora) errores.push('La hora es obligatoria.');
    if (!tipo) errores.push('El tipo de turno es obligatorio.');
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) errores.push('El email no es válido.');

    if (errores.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Completá los siguientes campos',
            html: '<ul class="text-start mt-2">' +
                errores.map(function (e) { return '<li class="mb-1">' + e + '</li>'; }).join('') +
                '</ul>',
            confirmButtonColor: '#d33'
        });
        return;
    }

    var btn = document.getElementById('btnGuardarTurno');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    fetch(ENDPOINT_AGENDA + '?action=programar_turno', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_persona_asiste: idPA,
            id_orden: idOrden,
            id_tipo_turno: tipo,
            fecha_hora: fecha + ' ' + hora
        })
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalNuevoTurno')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Turno guardado',
                    text: 'El turno fue programado correctamente.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                }).then(function () { renderCalendar(); });
            } else {
                var msg = data.errores
                    ? '<ul class="text-start mt-2">' + data.errores.map(function (e) { return '<li class="mb-1">' + e + '</li>'; }).join('') + '</ul>'
                    : data.error || 'Error desconocido.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    html: msg,
                    confirmButtonColor: '#d33'
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-calendar-check me-1"></i> Guardar Turno';
            }
        })
        .catch(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#d33'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-calendar-check me-1"></i> Guardar Turno';
        });
});

/* ── sessionStorage desde Admisión ── */
var pacienteGuardado = sessionStorage.getItem('pacienteAgenda');
if (pacienteGuardado) {
    var p = JSON.parse(pacienteGuardado);
    sessionStorage.removeItem('pacienteAgenda');
    window.addEventListener('DOMContentLoaded', function () {

        // Mostrar datos visuales inmediatamente
        document.getElementById('tDni').value = p.dni || '';
        document.getElementById('tNombre').value = p.nombre || '';
        document.getElementById('tApellido').value = p.apellido || '';
        document.getElementById('tEmail').readOnly = true;
        document.getElementById('tEmail').value = p.email || '';
        document.getElementById('infoPacienteNombre').textContent = (p.apellido || '') + ', ' + (p.nombre || '');
        document.getElementById('infoPacienteCirugia').textContent = '';
        document.getElementById('infoPaciente').classList.remove('d-none');

        // Buscar en DB para obtener id_orden e id_persona_asiste
        fetch(ENDPOINT_AGENDA + '?action=buscar_paciente&dni=' + p.dni)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok && data.encontrado) {
                    var pac = data.paciente;
                    document.getElementById('id_orden').value = pac.id_orden;
                    document.getElementById('id_persona_asiste').value = pac.id_persona_asiste;
                    document.getElementById('infoPacienteCirugia').textContent =
                        'Cirugía: ' + pac.tipo_cirugia + ' — ' + pac.estado_orden;
                    document.getElementById('btnGuardarTurno').disabled = false;
                } else {
                    // La orden todavía no está disponible
                    document.getElementById('dniFeedback').innerHTML =
                        '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>No se encontró una orden médica para este paciente.</span>';
                }
            })
            .catch(function () {
                document.getElementById('dniFeedback').innerHTML =
                    '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Error al conectar con el servidor.</span>';
            });

        new bootstrap.Modal(document.getElementById('modalNuevoTurno')).show();
    });
}
cargarTiposTurno();
cargarTurnos();