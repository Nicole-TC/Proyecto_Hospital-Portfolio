var paginaActual = 1;
var limitePorPagina = 10;

function initGestionOrdenes() {
    cargarOrdenesAPI();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGestionOrdenes);
} else {
    initGestionOrdenes();
}

function badgeEstado(codigo, descripcion) {
    var map = {
        'PEND': 'bg-warning text-dark',
        'PROC': 'bg-primary',
        'FIN':  'bg-success',
        'CANC': 'bg-secondary',
        'RECH': 'bg-danger'
    };
    var cls = map[codigo] || 'bg-secondary';
    return '<span class="badge ' + cls + '">' + (descripcion || codigo || '—') + '</span>';
}

function ejecutarBusqueda() {
    paginaActual = 1;
    cargarOrdenesAPI();
}

function cargarOrdenesAPI() {
    var tbody = document.getElementById('tablaOrdenesBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-secondary">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Buscando...</td></tr>';

    var filtro = document.getElementById('filtroTipo').value;
    var valor  = document.getElementById('filtroValor').value;
    var incluir = document.getElementById('incluirCanceladas').checked ? '1' : '0';

    var url = 'app/cir-api/endpoints/ordenes_medicas-e.php?pagina=' + paginaActual + '&limite=' + limitePorPagina + '&incluir_canceladas=' + incluir;
    if (filtro && valor) {
        url += '&filtro=' + encodeURIComponent(filtro) + '&valor=' + encodeURIComponent(valor);
    }

    fetch(url, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            tbody.innerHTML = '';
            if (data.status !== 'success') {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">' + (data.message || 'Error') + '</td></tr>';
                return;
            }

            if (data.ordenes && data.ordenes.length > 0) {
                data.ordenes.forEach(function(o) {
                    var tr = document.createElement('tr');
                    tr.setAttribute('data-id', o.id_orden);
                    var paciente = ((o.paciente_nombre || '') + ' ' + (o.paciente_apellido || '')).trim();
                    var puedeCancelar = o.estado_codigo !== 'CANC' && o.estado_codigo !== 'FIN';
                    var btnCancelar = puedeCancelar
                        ? '<button class="btn btn-sm btn-link danger" title="Cancelar orden" onclick="cancelarOrden(' + o.id_orden + ')"><i class="bi bi-x-circle"></i></button>'
                        : '';

                    tr.innerHTML =
                        '<td class="ps-4 py-3 text-center fw-medium">#' + o.id_orden + '</td>' +
                        '<td class="py-3 text-center">' + (o.fecha_orden || '—') + '</td>' +
                        '<td class="py-3 text-center">' + paciente + '</td>' +
                        '<td class="py-3 text-center">' + (o.dni || '—') + '</td>' +
                        '<td class="py-3 text-center">' + (o.cirugia || '—') + '</td>' +
                        '<td class="py-3 text-center">' + (o.cirujano || '—') + '</td>' +
                        '<td class="py-3 text-center">' + badgeEstado(o.estado_codigo, o.estado_descripcion) + '</td>' +
                        '<td class="py-3 pe-4 text-center">' +
                            '<button class="btn btn-sm btn-link blue me-1" title="Editar" onclick="editarOrden(' + o.id_orden + ')"><i class="bi bi-pencil-square"></i></button>' +
                            btnCancelar +
                        '</td>';
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
    cargarOrdenesAPI();
}

function editarOrden(id) {
    window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=form_orden&id=' + id;
}

function cancelarOrden(id) {
    Swal.fire({
        title: '¿Cancelar orden?',
        text: 'La orden quedará en estado Cancelada (baja lógica).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff6b81',
        cancelButtonColor: '#e9ecef',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        fetch('app/cir-api/endpoints/ordenes_medicas-e.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'eliminar', id_orden: parseInt(id, 10) })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Cancelada', text: data.message, confirmButtonColor: '#00cec9' });
                cargarOrdenesAPI();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#ff6b81' });
            }
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#ff6b81' });
        });
    });
}
