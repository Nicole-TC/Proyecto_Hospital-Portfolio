var paginaActual = 1;
var limitePorPagina = 10;
var listarTodos = false;
var estadosOrdenCargados = false;
var reglasOrden = null;
var sugerenciasTimer = null;
var sugerenciasReqId = 0;
var filtrosConSugerencias = ['todo', 'dni', 'apellido', 'nombre', 'cirugia', 'cirujano'];

function initGestionOrdenes() {
    var filtroTipo = document.getElementById('filtroTipo');
    if (filtroTipo) {
        filtroTipo.addEventListener('change', actualizarControlFiltroValor);
        actualizarControlFiltroValor();
    }
    initAutocompleteBusqueda();
    initDelegacionAgendar();
    mostrarMensajeTablaInicial();
}

function initDelegacionAgendar() {
    var tbody = document.getElementById('tablaOrdenesBody');
    if (!tbody) return;
    tbody.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-agendar-turno');
        if (!btn) return;
        agendarTurnoOrden({
            dni: btn.getAttribute('data-dni') || '',
            nombre: btn.getAttribute('data-nombre') || '',
            apellido: btn.getAttribute('data-apellido') || '',
            cirugia: btn.getAttribute('data-cirugia') || '',
            cirujano: btn.getAttribute('data-cirujano') || ''
        });
    });
}

function agendarTurnoOrden(datos) {
    var p = datos || {};
    sessionStorage.setItem('pacienteAgenda', JSON.stringify({
        dni: p.dni || '',
        nombre: p.nombre || '',
        apellido: p.apellido || '',
        cirugia: p.cirugia || '',
        cirujano: p.cirujano || '',
        desde_lista_espera: true
    }));
    window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=agenda&vista=prequirurgico';
}

function mostrarMensajeTablaInicial() {
    var tbody = document.getElementById('tablaOrdenesBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-secondary">' +
        '<i class="bi bi-search me-2"></i> Use <strong>BUSCAR</strong> con un criterio o <strong>Ver listado completo</strong> para cargar las órdenes.' +
        '</td></tr>';
    var info = document.getElementById('infoPaginacion');
    if (info) info.textContent = 'Mostrando 0 de 0 registros';
    var pag = document.getElementById('paginador');
    if (pag) pag.innerHTML = '';
}

function listarTodasOrdenes() {
    cargarListadoInicial();
}

function asegurarEstadosFiltroCargados(callback) {
    if (estadosOrdenCargados) {
        if (callback) callback();
        return;
    }
    cargarEstadosFiltro(callback);
}

function cargarListadoInicial() {
    listarTodos = true;
    paginaActual = 1;
    cargarOrdenesAPI(null, null);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGestionOrdenes);
} else {
    initGestionOrdenes();
}

function cargarEstadosFiltro(callback) {
    fetch('app/cir-api/endpoints/ordenes_medicas-e.php?estados=1', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var select = document.getElementById('filtroEstado');
        if (!select || data.status !== 'success') return;
        select.innerHTML = '<option value="">Seleccione un estado...</option>';
        (data.estados || []).forEach(function(e) {
            var opt = document.createElement('option');
            opt.value = String(e.id_estado);
            opt.textContent = e.descripcion || e.codigo;
            select.appendChild(opt);
        });
        reglasOrden = data.reglas || null;
        estadosOrdenCargados = true;
        if (callback) callback();
    })
    .catch(function() { /* el filtro por estado quedará vacío hasta recargar */ });
}

function actualizarControlFiltroValor() {
    var filtro = document.getElementById('filtroTipo').value;
    var input = document.getElementById('filtroValor');
    var select = document.getElementById('filtroEstado');
    var label = document.getElementById('filtroValorLabel');
    var wrapper = document.getElementById('filtroValorWrapper');
    if (!input || !select) return;

    ocultarSugerencias();
    input.value = '';

    if (filtro === 'estado') {
        input.classList.add('d-none');
        if (wrapper) wrapper.classList.add('d-none');
        select.classList.remove('d-none');
        if (label) label.textContent = 'ESTADO';
        asegurarEstadosFiltroCargados();
    } else {
        select.classList.add('d-none');
        input.classList.remove('d-none');
        if (wrapper) wrapper.classList.remove('d-none');
        if (label) label.textContent = 'VALOR A BUSCAR';
        if (filtro === 'todo') {
            input.placeholder = 'DNI, Nombre o Apellido...';
            input.type = 'text';
        } else if (filtro === 'id_orden') {
            input.placeholder = 'Número de orden';
            input.type = 'number';
        } else if (filtro === 'dni') {
            input.placeholder = 'Ej: 47589326';
            input.type = 'text';
        } else if (filtro === 'apellido') {
            input.placeholder = 'Ej: Frezzio';
            input.type = 'text';
        } else if (filtro === 'nombre') {
            input.placeholder = 'Ej: Paola';
            input.type = 'text';
        } else if (filtro === 'cirugia') {
            input.placeholder = 'Ej: Apendicectomía (busca con una letra)';
            input.type = 'search';
        } else if (filtro === 'cirujano') {
            input.placeholder = 'Ej: García (busca con una letra)';
            input.type = 'search';
        } else {
            input.placeholder = 'Ingrese el dato...';
            input.type = 'text';
        }
    }
}

function filtroPermiteSugerencias() {
    return filtrosConSugerencias.indexOf(document.getElementById('filtroTipo').value) !== -1;
}

function ocultarSugerencias() {
    var box = document.getElementById('sugerenciasBusqueda');
    var wrapper = document.getElementById('filtroValorWrapper');
    if (box) {
        box.innerHTML = '';
        box.classList.add('d-none');
    }
    if (wrapper) wrapper.classList.remove('is-open');
}

function mostrarPanelSugerencias() {
    var wrapper = document.getElementById('filtroValorWrapper');
    if (wrapper) wrapper.classList.add('is-open');
}

function formatearNombrePaciente(apellido, nombre) {
    var partes = [];
    if (apellido) partes.push(apellido);
    if (nombre) partes.push(nombre);
    return partes.join(', ');
}

function resaltarCoincidencia(texto, term) {
    var safe = escaparHtml(texto || '');
    if (!term) return safe;
    var re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return safe.replace(re, '<mark class="sugerencia-match">$1</mark>');
}

function htmlItemSugerencia(item, term) {
    var filtro = document.getElementById('filtroTipo').value;
    if (filtro === 'cirugia' || filtro === 'cirujano') {
        var titulo = item.nombre || item.valor || '';
        var meta = item.meta || (filtro === 'cirugia' ? 'Cirugía' : 'Cirujano');
        var icono = filtro === 'cirugia' ? 'bi-heart-pulse' : 'bi-person-badge';
        return '<button type="button" class="sugerencia-item" role="option"' +
            ' data-valor="' + escaparHtml(item.valor) + '">' +
            '<span class="sugerencia-ico"><i class="bi ' + icono + '"></i></span>' +
            '<span class="sugerencia-cuerpo">' +
            '<span class="sugerencia-nombre">' + resaltarCoincidencia(titulo, term) + '</span>' +
            '<small class="sugerencia-meta">' + escaparHtml(meta) + '</small>' +
            '</span></button>';
    }
    var nombreVisible = formatearNombrePaciente(item.apellido, item.nombre);
    var meta = item.dni ? 'DNI ' + item.dni : 'Paciente';
    return '<button type="button" class="sugerencia-item" role="option"' +
        ' data-valor="' + escaparHtml(item.valor) + '">' +
        '<span class="sugerencia-ico"><i class="bi bi-person-fill"></i></span>' +
        '<span class="sugerencia-cuerpo">' +
        '<span class="sugerencia-nombre">' + resaltarCoincidencia(nombreVisible, term) + '</span>' +
        '<small class="sugerencia-meta">' + resaltarCoincidencia(meta, term) + '</small>' +
        '</span></button>';
}

function escaparHtml(texto) {
    var div = document.createElement('div');
    div.textContent = texto || '';
    return div.innerHTML;
}

function initAutocompleteBusqueda() {
    var input = document.getElementById('filtroValor');
    if (!input) return;

    input.addEventListener('input', function() {
        var filtro = document.getElementById('filtroTipo').value;
        if (filtro === 'dni') {
            this.value = this.value.replace(/[^0-9]/g, '');
        }
        if (!filtroPermiteSugerencias()) {
            ocultarSugerencias();
            return;
        }
        clearTimeout(sugerenciasTimer);
        var term = this.value.trim();
        var minChars = (filtro === 'dni' || (filtro === 'todo' && /^\d+$/.test(term))
            || filtro === 'cirugia' || filtro === 'cirujano') ? 1 : 2;
        if (term.length < minChars) {
            ocultarSugerencias();
            return;
        }
        sugerenciasTimer = setTimeout(function() {
            cargarSugerenciasBusqueda(term);
        }, 300);
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            ocultarSugerencias();
            ejecutarBusqueda();
            return;
        }
        if (e.key === 'Escape') {
            ocultarSugerencias();
        }
    });

    input.addEventListener('blur', function() {
        setTimeout(ocultarSugerencias, 180);
    });

    document.addEventListener('click', function(e) {
        var wrapper = document.getElementById('filtroValorWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            ocultarSugerencias();
        }
    });
}

function cargarSugerenciasBusqueda(term) {
    var filtro = document.getElementById('filtroTipo').value;
    var box = document.getElementById('sugerenciasBusqueda');
    if (!box || !filtroPermiteSugerencias()) return;

    var reqId = ++sugerenciasReqId;
    var url = 'app/cir-api/endpoints/ordenes_medicas-e.php?sugerencias=1' +
        '&filtro=' + encodeURIComponent(filtro) +
        '&term=' + encodeURIComponent(term) +
        '&limite=8';

    fetch(url, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (reqId !== sugerenciasReqId) return;
            if (data.status !== 'success') {
                ocultarSugerencias();
                return;
            }
            var items = data.sugerencias || [];
            if (items.length === 0) {
                box.innerHTML = '<div class="sugerencia-vacia">' +
                    '<i class="bi bi-search"></i><span>Sin coincidencias para «' + escaparHtml(term) + '»</span></div>';
                box.classList.remove('d-none');
                mostrarPanelSugerencias();
                return;
            }
            box.innerHTML = '<div class="sugerencias-titulo">Sugerencias</div>' +
                items.map(function(item) { return htmlItemSugerencia(item, term); }).join('');
            box.classList.remove('d-none');
            mostrarPanelSugerencias();
            box.querySelectorAll('.sugerencia-item').forEach(function(btn) {
                btn.addEventListener('mousedown', function(ev) {
                    ev.preventDefault();
                });
                btn.addEventListener('click', function() {
                    seleccionarSugerencia(this.getAttribute('data-valor'));
                });
            });
        })
        .catch(function() {
            if (reqId === sugerenciasReqId) ocultarSugerencias();
        });
}

function seleccionarSugerencia(valor) {
    var input = document.getElementById('filtroValor');
    if (!input) return;
    input.value = valor || '';
    ocultarSugerencias();
    ejecutarBusqueda();
}

function obtenerValorFiltro() {
    var filtro = document.getElementById('filtroTipo').value;
    if (filtro === 'estado') {
        return document.getElementById('filtroEstado').value.trim();
    }
    return document.getElementById('filtroValor').value.trim();
}

function botonAccionOrden(tipo, icono, etiqueta, onclick, deshabilitado, titulo) {
    var clases = {
        ver: 'btn-outline-primary',
        cancelar: 'btn-outline-warning',
        eliminar: 'btn-outline-danger'
    };
    var cls = clases[tipo] || 'btn-outline-secondary';
    var disabled = deshabilitado ? ' disabled' : '';
    var title = titulo || etiqueta;
    return '<button type="button" class="btn btn-sm btn-accion-orden ' + cls + disabled + '"' +
        ' title="' + title + '" onclick="' + onclick + '">' +
        '<i class="bi ' + icono + ' me-1"></i><span>' + etiqueta + '</span></button>';
}

function badgeEstado(codigo, descripcion) {
    var c = String(codigo || '').trim().toUpperCase();
    var map = {
        'PEND': 'bg-warning text-dark',
        'PROC': 'bg-primary',
        'FIN':  'bg-success',
        'CANC': 'bg-secondary',
        'RECH': 'bg-danger',
        'ELIM': 'bg-danger'
    };
    var cls = map[c] || 'bg-secondary';
    var texto = descripcion || c || '—';
    return '<span class="badge ' + cls + ' px-3 py-2 rounded-pill">' + texto + '</span>';
}

function ejecutarBusqueda() {
    var filtro = document.getElementById('filtroTipo').value;
    var valor  = obtenerValorFiltro();
    if (!valor) {
        var msg = filtro === 'estado'
            ? 'Seleccione un estado para buscar.'
            : 'Ingrese un valor para buscar.';
        Swal.fire({
            icon: 'info',
            title: 'Búsqueda requerida',
            text: msg,
            confirmButtonColor: '#00cec9'
        });
        return;
    }
    if (filtro === 'estado' && !estadosOrdenCargados) {
        asegurarEstadosFiltroCargados(function() {
            ejecutarBusqueda();
        });
        return;
    }
    listarTodos = false;
    paginaActual = 1;
    cargarOrdenesAPI(filtro, valor);
}

function cargarOrdenesAPI(filtro, valor) {
    var tbody = document.getElementById('tablaOrdenesBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-secondary">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando...</td></tr>';
    var url = 'app/cir-api/endpoints/ordenes_medicas-e.php?pagina=' + paginaActual +
        '&limite=' + limitePorPagina;

    if (listarTodos) {
        url += '&listar_todos=1';
    } else if (filtro && valor) {
        url += '&filtro=' + encodeURIComponent(filtro) + '&valor=' + encodeURIComponent(valor);
    }

    fetch(url, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            tbody.innerHTML = '';
            if (data.status !== 'success') {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">' +
                    (data.message || 'Error') + '</td></tr>';
                document.getElementById('infoPaginacion').textContent = 'Mostrando 0 de 0 registros';
                document.getElementById('paginador').innerHTML = '';
                return;
            }

            if (data.ordenes && data.ordenes.length > 0) {
                data.ordenes.forEach(function(o) {
                    var tr = document.createElement('tr');
                    tr.setAttribute('data-id', o.id_orden);
                    var paciente = ((o.paciente_nombre || '') + ' ' + (o.paciente_apellido || '')).trim();
                    var acciones = '<div class="acciones-orden d-flex justify-content-center flex-wrap gap-1">';
                    if (o.puede_ver) {
                        acciones += botonAccionOrden(
                            'ver', 'bi-eye-fill', 'Ver',
                            'verOrden(' + o.id_orden + ')', false, 'Ver detalle de la orden'
                        );
                    }
                    if (o.puede_programar) {
                        acciones += '<button type="button" class="btn btn-sm btn-primary btn-agendar-turno"' +
                            ' data-dni="' + escaparHtml(o.dni || '') + '"' +
                            ' data-nombre="' + escaparHtml(o.paciente_nombre || '') + '"' +
                            ' data-apellido="' + escaparHtml(o.paciente_apellido || '') + '"' +
                            ' data-cirugia="' + escaparHtml(o.cirugia || '') + '"' +
                            ' data-cirujano="' + escaparHtml(o.cirujano || '') + '"' +
                            ' title="Agendar turno prequirúrgico">' +
                            '<i class="bi bi-calendar-plus me-1"></i>Agendar</button>';
                    }
                    acciones += botonAccionOrden(
                        'cancelar', 'bi-slash-circle-fill', 'Cancelar',
                        'cancelarOrden(' + o.id_orden + ',' + (o.puede_cancelar ? 'true' : 'false') + ')',
                        !o.puede_cancelar,
                        o.puede_cancelar ? 'Cancelar orden' : 'No se puede cancelar en este estado'
                    );
                    if (o.puede_eliminar) {
                        acciones += botonAccionOrden(
                            'eliminar', 'bi-trash3-fill', 'Eliminar',
                            'eliminarOrden(' + o.id_orden + ')', false, 'Eliminar orden'
                        );
                    }
                    acciones += '</div>';

                    tr.innerHTML =
                        '<td class="ps-4 py-3 text-center fw-medium">#' + o.id_orden + '</td>' +
                        '<td class="py-3 text-center">' + (o.fecha_orden || '—') + '</td>' +
                        '<td class="py-3 text-center">' + paciente + '</td>' +
                        '<td class="py-3 text-center">' + (o.dni || '—') + '</td>' +
                        '<td class="py-3 text-center">' + (o.cirugia || '—') + '</td>' +
                        '<td class="py-3 text-center">' + (o.cirujano || '—') + '</td>' +
                        '<td class="py-3 text-center">' + badgeEstado(o.estado_codigo, o.estado_descripcion) + '</td>' +
                        '<td class="py-3 pe-4 text-center">' + acciones + '</td>';
                    tbody.appendChild(tr);
                });

                var desde = ((paginaActual - 1) * limitePorPagina) + 1;
                var hasta = desde + data.ordenes.length - 1;
                document.getElementById('infoPaginacion').textContent =
                    'Mostrando ' + desde + ' a ' + hasta + ' de ' + data.total + ' registros';
                renderPaginador(data.total, paginaActual, limitePorPagina);
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-secondary">No se encontraron órdenes.</td></tr>';
                document.getElementById('infoPaginacion').textContent = 'Mostrando 0 de 0 registros';
                document.getElementById('paginador').innerHTML = '';
            }
            if (!estadosOrdenCargados) {
                asegurarEstadosFiltroCargados();
            }
        })
        .catch(function() {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Error al conectar con el servicio.</td></tr>';
        });
}

function renderPaginador(total, pagina, limite) {
    var paginador = document.getElementById('paginador');
    if (!paginador) return;
    paginador.innerHTML = '';
    var totalPaginas = Math.ceil(total / limite);
    if (totalPaginas <= 1) return;

    var liPrev = document.createElement('li');
    liPrev.className = 'page-item ' + (pagina === 1 ? 'disabled' : '');
    liPrev.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(' + (pagina - 1) + ')">Anterior</a>';
    paginador.appendChild(liPrev);

    for (var i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= pagina - 2 && i <= pagina + 2)) {
            var li = document.createElement('li');
            li.className = 'page-item ' + (i === pagina ? 'active' : '');
            li.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(' + i + ')">' + i + '</a>';
            paginador.appendChild(li);
        }
    }

    var liNext = document.createElement('li');
    liNext.className = 'page-item ' + (pagina === totalPaginas ? 'disabled' : '');
    liNext.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(' + (pagina + 1) + ')">Siguiente</a>';
    paginador.appendChild(liNext);
}

function cambiarPagina(n) {
    if (n < 1) return;
    paginaActual = n;
    if (listarTodos) {
        cargarOrdenesAPI(null, null);
    } else {
        cargarOrdenesAPI(document.getElementById('filtroTipo').value, obtenerValorFiltro());
    }
}

function verOrden(id) {
    window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=form_orden&id=' + id;
}

function cancelarOrden(id, permitido) {
    if (permitido === false) {
        Swal.fire({
            icon: 'info',
            title: 'Acción no disponible',
            text: 'No se puede cancelar una orden en el estado actual.',
            confirmButtonColor: '#00cec9'
        });
        return;
    }
    var descEstado = (reglasOrden && reglasOrden.cancelar && reglasOrden.cancelar.descripcion_destino)
        ? reglasOrden.cancelar.descripcion_destino
        : 'cancelada';
    Swal.fire({
        title: '¿Cancelar esta orden?',
        text: 'La orden pasará al estado «' + descEstado + '». Podrá consultarla filtrando por ese estado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff6b81',
        cancelButtonColor: '#e9ecef',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        enviarAccionOrden('cancelar', id, 'Orden cancelada correctamente.');
    });
}

function eliminarOrden(id) {
    Swal.fire({
        title: '¿Eliminar esta orden?',
        text: 'La orden se dará de baja y dejará de mostrarse en el listado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff6b81',
        cancelButtonColor: '#e9ecef',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        enviarAccionOrden('eliminar', id, 'Orden eliminada correctamente.');
    });
}

function enviarAccionOrden(accion, id, msgOk) {
    fetch('app/cir-api/endpoints/ordenes_medicas-e.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: accion, id_orden: parseInt(id, 10) })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Listo', text: data.message || msgOk, confirmButtonColor: '#00cec9' });
            if (listarTodos) {
                cargarOrdenesAPI(null, null);
            } else {
                var filtro = document.getElementById('filtroTipo').value;
                var valor  = obtenerValorFiltro();
                if (valor) cargarOrdenesAPI(filtro, valor);
                else cargarListadoInicial();
            }
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#ff6b81' });
        }
    })
    .catch(function() {
        Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#ff6b81' });
    });
}
