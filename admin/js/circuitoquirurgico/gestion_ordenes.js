var paginaActual = 1;
var limitePorPagina = 10;
var listarTodos = false;
var estadosOrdenCargados = false;
var reglasOrden = null;

function initGestionOrdenes() {
    var tbody = document.getElementById('tablaOrdenesBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-secondary">' +
            '<i class="bi bi-search me-2"></i> Ingrese un criterio de b&uacute;squeda o use &laquo;Ver listado completo&raquo;.' +
            '</td></tr>';
    }
    var filtroTipo = document.getElementById('filtroTipo');
    if (filtroTipo) {
        filtroTipo.addEventListener('change', actualizarControlFiltroValor);
        actualizarControlFiltroValor();
    }
    cargarEstadosFiltro();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGestionOrdenes);
} else {
    initGestionOrdenes();
}

function cargarEstadosFiltro() {
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
            opt.value = e.id_estado;
            opt.textContent = e.descripcion || e.codigo;
            select.appendChild(opt);
        });
        reglasOrden = data.reglas || null;
        estadosOrdenCargados = true;
    })
    .catch(function() { /* el filtro por estado quedará vacío hasta recargar */ });
}

function actualizarControlFiltroValor() {
    var filtro = document.getElementById('filtroTipo').value;
    var input = document.getElementById('filtroValor');
    var select = document.getElementById('filtroEstado');
    var label = document.getElementById('filtroValorLabel');
    if (!input || !select) return;

    if (filtro === 'estado') {
        input.classList.add('d-none');
        select.classList.remove('d-none');
        if (label) label.textContent = 'ESTADO';
    } else {
        select.classList.add('d-none');
        input.classList.remove('d-none');
        if (label) label.textContent = 'VALOR A BUSCAR';
        if (filtro === 'id_orden') {
            input.placeholder = 'Número de orden';
            input.type = 'number';
        } else {
            input.placeholder = 'Ingrese el dato...';
            input.type = 'text';
        }
    }
}

function obtenerValorFiltro() {
    var filtro = document.getElementById('filtroTipo').value;
    if (filtro === 'estado') {
        return document.getElementById('filtroEstado').value.trim();
    }
    return document.getElementById('filtroValor').value.trim();
}

function badgeEstado(descripcion) {
    return '<span class="badge bg-secondary">' + (descripcion || '—') + '</span>';
}

function ejecutarBusqueda() {
    var filtro = document.getElementById('filtroTipo').value;
    var valor  = obtenerValorFiltro();
    if (!valor) {
        var msg = filtro === 'estado'
            ? 'Seleccione un estado para buscar o use el botón Ver listado completo.'
            : 'Ingrese un valor para buscar o use el botón Ver listado completo.';
        Swal.fire({
            icon: 'info',
            title: 'Búsqueda requerida',
            text: msg,
            confirmButtonColor: '#00cec9'
        });
        return;
    }
    if (filtro === 'estado' && !estadosOrdenCargados) {
        Swal.fire({
            icon: 'info',
            title: 'Espere un momento',
            text: 'Los estados aún se están cargando. Intente de nuevo.',
            confirmButtonColor: '#00cec9'
        });
        return;
    }
    listarTodos = false;
    paginaActual = 1;
    cargarOrdenesAPI(filtro, valor);
}

function listarTodasOrdenes() {
    listarTodos = true;
    paginaActual = 1;
    cargarOrdenesAPI(null, null);
}

function cargarOrdenesAPI(filtro, valor) {
    var tbody = document.getElementById('tablaOrdenesBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-secondary">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Buscando...</td></tr>';
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
                    var btnVer = o.puede_ver
                        ? '<button class="btn btn-sm btn-link blue me-1" title="Ver orden" onclick="verOrden(' + o.id_orden + ')"><i class="bi bi-eye"></i></button>'
                        : '';
                    var btnCancelar = o.puede_cancelar
                        ? '<button class="btn btn-sm btn-link warning me-1" title="Cancelar orden" onclick="cancelarOrden(' + o.id_orden + ')"><i class="bi bi-x-circle"></i></button>'
                        : '';
                    var btnEliminar = o.puede_eliminar
                        ? '<button class="btn btn-sm btn-link danger" title="Eliminar orden" onclick="eliminarOrden(' + o.id_orden + ')"><i class="bi bi-trash"></i></button>'
                        : '';

                    tr.innerHTML =
                        '<td class="ps-4 py-3 text-center fw-medium">#' + o.id_orden + '</td>' +
                        '<td class="py-3 text-center">' + (o.fecha_orden || '—') + '</td>' +
                        '<td class="py-3 text-center">' + paciente + '</td>' +
                        '<td class="py-3 text-center">' + (o.dni || '—') + '</td>' +
                        '<td class="py-3 text-center">' + (o.cirugia || '—') + '</td>' +
                        '<td class="py-3 text-center">' + (o.cirujano || '—') + '</td>' +
                        '<td class="py-3 text-center">' + badgeEstado(o.estado_descripcion) + '</td>' +
                        '<td class="py-3 pe-4 text-center">' + btnVer + btnCancelar + btnEliminar + '</td>';
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

function cancelarOrden(id) {
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
                else initGestionOrdenes();
            }
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#ff6b81' });
        }
    })
    .catch(function() {
        Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#ff6b81' });
    });
}
