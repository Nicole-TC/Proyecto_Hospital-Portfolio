const ENDPOINT_ASISTENCIA = 'app/cir-api/endpoints/asistencia-e.php';
let paginaActual = 1;

(function initAsistencia() {
    var tbody = document.getElementById('tbodyAsistencia');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-secondary">' +
            '<i class="bi bi-search me-2"></i> Ingrese un criterio de búsqueda para listar los turnos.' +
            '</td></tr>';
    }
})();

function ejecutarBusqueda() {
    paginaActual = 1;
    var filtro = document.getElementById('filtroTipo').value;
    var valor  = document.getElementById('valorBuscador').value.trim();
    var limite = document.getElementById('filtroLimite').value;
    var orden  = document.getElementById('filtroOrden').value;

    buscarTurnos(filtro, valor, limite, orden, paginaActual);
}

function buscarTurnos(filtro, valor, limite, orden, pagina) {
    var tbody = document.getElementById('tbodyAsistencia');
    var empty = document.getElementById('emptyAsistencia');

    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-secondary">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Buscando...</td></tr>';
    empty.classList.add('d-none');

    var url = ENDPOINT_ASISTENCIA +
        '?filtro=' + encodeURIComponent(filtro) +
        '&valor=' + encodeURIComponent(valor) +
        '&limite=' + encodeURIComponent(limite) +
        '&pagina=' + encodeURIComponent(pagina) +
        '&orden=' + encodeURIComponent(orden);

    fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            tbody.innerHTML = '';

            if (data.status !== 'success' || !data.turnos || data.turnos.length === 0) {
                empty.classList.remove('d-none');
                document.getElementById('infoPaginacion').textContent = 'Sin resultados';
                document.getElementById('paginacionAsistencia').innerHTML = '';
                return;
            }

            empty.classList.add('d-none');

            data.turnos.forEach(function (t) {
                var dt = new Date(t.fecha_hora);
                var fecha = dt.toLocaleDateString('es-AR');
                var hora  = dt.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

                var badgeClass = t.estado_codigo === 'PROG' ? 'bg-primary-subtle text-primary'
                    : t.estado_codigo === 'CONF' ? 'bg-success text-white'
                        : t.estado_codigo === 'AUS' ? 'bg-warning-subtle text-warning'
                            : t.estado_codigo === 'CANC' ? 'bg-danger-subtle text-danger'
                                : t.estado_codigo === 'FIN' ? 'bg-secondary text-white'
                                    : 'bg-secondary-subtle text-secondary';

                var acciones = generarAcciones(t.id_turno, t.estado_codigo, t.fecha_hora, t.dni);

                var tr = document.createElement('tr');
                tr.id = 'turno-' + t.id_turno;
                tr.innerHTML =
                    '<td class="ps-4 fw-semibold">' + t.apellido + ', ' + t.nombre + '</td>' +
                    '<td class="text-secondary font-monospace">' + t.dni + '</td>' +
                    '<td class="text-secondary small">' + (t.cirugia || '—') + '</td>' +
                    '<td class="text-secondary small">' + (t.medico || '—') + '</td>' +
                    '<td><span class="badge bg-light text-dark border">' + t.tipo_turno + '</span></td>' +
                    '<td><span class="badge ' + badgeClass + '">' + t.estado + '</span></td>' +
                    '<td class="text-end pe-4 text-nowrap">' + acciones + '</td>';
                tbody.appendChild(tr);
            });

            var desde = (data.pagina - 1) * data.limite + 1;
            var hasta = Math.min(data.pagina * data.limite, data.total);
            document.getElementById('infoPaginacion').textContent =
                'Mostrando ' + desde + ' a ' + hasta + ' de ' + data.total + ' registros';

            var totalPaginas = Math.ceil(data.total / data.limite);
            renderPaginacion(data.pagina, totalPaginas, filtro, valor, data.limite, orden);
        })
        .catch(function () {
            tbody.innerHTML = '';
            empty.classList.remove('d-none');
        });
}

function generarAcciones(id_turno, estado_codigo, fecha_hora, dni) {
    var detalleBtn = '<button class="btn btn-sm btn-outline-secondary ms-1" onclick="verDetalles(' + id_turno + ')" title="Ver detalles">' +
        '<i class="bi bi-eye"></i></button>';

    var turnoPasado = false;
    if (fecha_hora) {
        var hoy = new Date();
        var fechaTurno = new Date(fecha_hora);
        hoy.setHours(0,0,0,0);
        fechaTurno.setHours(0,0,0,0);
        turnoPasado = fechaTurno < hoy;
    }

    if ((estado_codigo === 'PROG' || estado_codigo === 'AUS') && !turnoPasado) {
        return '<div style="white-space:nowrap;">' +
            '<button class="btn btn-sm btn-success me-1" onclick="marcarAsistencia(' + id_turno + ')" title="Asistió">' +
            '<i class="bi bi-check-lg"></i> Asistió</button>' +
            '<button class="btn btn-sm btn-danger me-1" onclick="cancelarTurno(' + id_turno + ', \'' + dni + '\')" title="Cancelar">' +
            '<i class="bi bi-x-lg"></i> Cancelar</button>' +
            detalleBtn +
            '</div>';
    } else if (estado_codigo === 'CONF') {
        return '<span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Asistió</span>' + detalleBtn;
    } else if (estado_codigo === 'CANC') {
        var reprogramarBtn = '<a href="index.php?modulo=menu_circuitoquirurgico&accion=agenda&dni=' + dni + '" class="btn btn-sm btn-outline-info ms-1" title="Reprogramar turno">' +
            '<i class="bi bi-calendar-plus"></i> Reprogramar</a>';
        return '<span class="text-danger small fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Cancelado</span>' + reprogramarBtn + detalleBtn;
    }
    return '<span class="text-warning small fw-semibold"><i class="bi bi-exclamation-circle-fill me-1"></i>Ausente</span>' + detalleBtn;
}

function verDetalles(id_turno) {
    var url = ENDPOINT_ASISTENCIA + '?id_turno=' + id_turno;
    fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status !== 'success' || !data.turno) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron obtener los detalles.' });
                return;
            }
            var t = data.turno;
            var badgeEstado = t.estado_codigo === 'CONF' ? 'bg-success'
                : t.estado_codigo === 'AUS' ? 'bg-warning text-dark'
                    : t.estado_codigo === 'CANC' ? 'bg-danger'
                        : 'bg-primary';

            var html =
                '<div class="text-start" style="font-size:14px;">' +
                '<div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">' +
                '  <div>' +
                '    <div style="font-size:18px;font-weight:700;">' + t.apellido + ', ' + t.nombre + '</div>' +
                '    <div class="text-secondary">' + t.dni + '</div>' +
                '  </div>' +
                '  <span class="badge ' + badgeEstado + ' fs-6 px-3 py-2">' + t.estado_descripcion + '</span>' +
                '</div>' +

                '<div class="row mb-2">' +
                '  <div class="col-5 text-secondary">Cirugía</div>' +
                '  <div class="col-7 fw-semibold">' + (t.cirugia || '—') + '</div>' +
                '</div>' +
                '<div class="row mb-2">' +
                '  <div class="col-5 text-secondary">Médico</div>' +
                '  <div class="col-7 fw-semibold">' + (t.medico || '—') + '</div>' +
                '</div>' +
                '<div class="row mb-2">' +
                '  <div class="col-5 text-secondary">Tipo turno</div>' +
                '  <div class="col-7 fw-semibold">' + t.tipo_turno + '</div>' +
                '</div>' +
                '<div class="row mb-2">' +
                '  <div class="col-5 text-secondary">Fecha / Hora</div>' +
                '  <div class="col-7 fw-semibold">' + new Date(t.fecha_hora).toLocaleString('es-AR') + '</div>' +
                '</div>';

            if (t.ausente && t.fecha_ausencia) {
                html +=
                '<hr>' +
                '<div class="row mb-0">' +
                '  <div class="col-5 text-secondary">Ausente desde</div>' +
                '  <div class="col-7 fw-semibold">' + new Date(t.fecha_ausencia).toLocaleString('es-AR') + '</div>' +
                '</div>';
            }

            if (t.motivo_cancelacion) {
                html +=
                '<hr>' +
                '<div class="row mb-2">' +
                '  <div class="col-5 text-secondary">Motivo cancelación</div>' +
                '  <div class="col-7 fw-semibold text-danger">' + t.motivo_cancelacion + '</div>' +
                '</div>' +
                '<div class="row mb-0">' +
                '  <div class="col-5 text-secondary">Fecha cancelación</div>' +
                '  <div class="col-7 fw-semibold">' + (t.fecha_cancelacion ? new Date(t.fecha_cancelacion).toLocaleString('es-AR') : '—') + '</div>' +
                '</div>';
            }

            html += '</div>';

            Swal.fire({
                title: 'Detalles del Turno #' + id_turno,
                html: html,
                icon: null,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#3085d6',
                width: '520px',
            });
        })
        .catch(function () {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo obtener la información.' });
        });
}

function marcarAsistencia(id_turno) {
    Swal.fire({
        title: 'Confirmar asistencia',
        text: '¿El paciente asistió al turno?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, asistió',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
    }).then(function (result) {
        if (result.isConfirmed) {
            enviarAccion(id_turno, 'marcar_asistencia', 'Asistencia registrada correctamente.');
        }
    });
}


function cancelarTurno(id_turno, dni) {
    Swal.fire({
        title: 'Cancelar turno',
        text: 'Indique el motivo de la cancelación:',
        icon: 'question',
        input: 'text',
        inputPlaceholder: 'Ej: Problemas con el estudio, suspensión médica...',
        inputAttributes: { maxlength: 255 },
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#dc3545',
        preConfirm: function (motivo) {
            if (!motivo || motivo.trim() === '') {
                Swal.showValidationMessage('Debe ingresar un motivo');
            }
            return motivo;
        }
    }).then(function (result) {
        if (result.isConfirmed && result.value) {
            enviarAccion(id_turno, 'cancelar_turno', 'Turno cancelado correctamente.', result.value.trim(), true, dni);
        }
    });
}

function enviarAccion(id_turno, accion, mensaje_exito, motivo, showReprogramar, dni) {
    var body = { id_turno: id_turno, accion: accion };
    if (motivo) { body.motivo = motivo; }
    fetch(ENDPOINT_ASISTENCIA, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.status === 'success') {
            if (showReprogramar) {
                Swal.fire({
                    icon: 'success',
                    title: 'Turno cancelado',
                    text: mensaje_exito,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-calendar-plus me-1"></i> Reprogramar Turno',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#0dcaf0',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=agenda&dni=' + (dni || '');
                    } else {
                        ejecutarBusqueda();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Operación exitosa',
                    text: mensaje_exito,
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: true,
                }).then(function () {
                    ejecutarBusqueda();
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo completar la operación.',
                confirmButtonColor: '#d33',
            });
        }
    })
    .catch(function () {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor.',
            confirmButtonColor: '#d33',
        });
    });
}

function renderPaginacion(paginaActualLocal, totalPaginas, filtro, valor, limite, orden) {
    var ul = document.getElementById('paginacionAsistencia');
    ul.innerHTML = '';
    if (totalPaginas <= 1) return;

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
