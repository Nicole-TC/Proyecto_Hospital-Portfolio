var paginaActual     = 1;
var limitePorPagina  = 10;

function initGestion() {
    cargarEstadisticas();

    var tbody = document.getElementById('tablaPacientesBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-secondary">' +
                          '<i class="bi bi-search me-2"></i> Ingrese un criterio de b&uacute;squeda para listar los pacientes.' +
                          '</td></tr>';
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGestion);
} else {
    initGestion();
}

function cargarEstadisticas() {
    fetch('app/cir-api/endpoints/gestion_pacientes-e.php?limite=1', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data && data.stats) {
            if (document.getElementById('stat-total')) document.getElementById('stat-total').textContent = data.stats.total || 0;
            if (document.getElementById('stat-en-espera')) document.getElementById('stat-en-espera').textContent = data.stats.en_espera || 0;
            if (document.getElementById('stat-en-curso')) document.getElementById('stat-en-curso').textContent = data.stats.en_curso || 0;
            if (document.getElementById('stat-completados')) document.getElementById('stat-completados').textContent = data.stats.completados || 0;
        }
    })
    .catch(function(err) { console.error('Error al cargar estad&iacute;sticas:', err); });
}

function ejecutarBusqueda() {
    paginaActual = 1;
    var filtro = document.getElementById('filtroTipo').value;
    var valor  = document.getElementById('filtroValor').value;
    cargarPacientesAPI(filtro, valor);
}

function cargarPacientesAPI(filtro, valor) {
    var tbody = document.getElementById('tablaPacientesBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-secondary">' +
                      '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>' +
                      'Buscando pacientes...</td></tr>';

    var url = 'app/cir-api/endpoints/gestion_pacientes-e.php?pagina=' + paginaActual + '&limite=' + limitePorPagina;
    if (filtro && valor) {
        url += '&filtro=' + encodeURIComponent(filtro) + '&valor=' + encodeURIComponent(valor);
    }

    fetch(url, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        tbody.innerHTML = '';

        if (data && data.stats) {
            if (document.getElementById('stat-total')) document.getElementById('stat-total').textContent = data.stats.total || 0;
            if (document.getElementById('stat-en-espera')) document.getElementById('stat-en-espera').textContent = data.stats.en_espera || 0;
            if (document.getElementById('stat-en-curso')) document.getElementById('stat-en-curso').textContent = data.stats.en_curso || 0;
            if (document.getElementById('stat-completados')) document.getElementById('stat-completados').textContent = data.stats.completados || 0;
        }

        if (data && data.pacientes && data.pacientes.length > 0) {
            data.pacientes.forEach(function(p) {
                var tr = document.createElement('tr');
                tr.setAttribute('data-id', p.id_persona);
                var nombreCompleto = ((p.nombre || '') + ' ' + (p.apellido || '')).trim();
                var safe = function(s) { return String(s).replace(/"/g, '&quot;'); };

                tr.innerHTML =
                    '<td class="ps-4 py-3 fw-medium text-center">' + nombreCompleto + '</td>' +
                    '<td class="py-3 text-center">' + (p.dni || '—') + '</td>' +
                    '<td class="py-3 text-center">' + (p.email || '—') + '</td>' +
                    '<td class="py-3 pe-4 text-center">' +
                        '<button class="btn btn-sm btn-link blue me-2" title="Editar paciente" onclick="editarPaciente(' + p.id_persona + ')"><i class="bi bi-pencil-square"></i></button>' +
                        '<button class="btn btn-sm btn-link danger" title="Eliminar paciente" onclick="eliminarPaciente(' + p.id_persona + ')"><i class="bi bi-trash"></i></button>' +
                    '</td>';
                tbody.appendChild(tr);
            });

            if (document.getElementById('infoPaginacion')) {
                var desde = ((paginaActual - 1) * limitePorPagina) + 1;
                var hasta = desde + data.pacientes.length - 1;
                document.getElementById('infoPaginacion').textContent = 'Mostrando ' + desde + ' a ' + hasta + ' de ' + data.total + ' registros';
            }
            renderPaginador(data.total, paginaActual, limitePorPagina);

        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-secondary">No se encontraron pacientes con esos criterios.</td></tr>';
            if (document.getElementById('infoPaginacion')) document.getElementById('infoPaginacion').textContent = 'Mostrando 0 de 0 registros';
            if (document.getElementById('paginador')) document.getElementById('paginador').innerHTML = '';
        }
    })
    .catch(function(err) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error al conectar con el servicio.</td></tr>';
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
        } else if (i === pagina - 3 || i === pagina + 3) {
            var liDots = document.createElement('li');
            liDots.className = 'page-item disabled';
            liDots.innerHTML = '<span class="page-link">...</span>';
            paginador.appendChild(liDots);
        }
    }

    var liNext = document.createElement('li');
    liNext.className = 'page-item ' + (pagina === totalPaginas ? 'disabled' : '');
    liNext.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(' + (pagina + 1) + ')">Siguiente</a>';
    paginador.appendChild(liNext);
}

function cambiarPagina(nuevaPagina) {
    if (nuevaPagina < 1) return;
    paginaActual = nuevaPagina;

    var filtro = document.getElementById('filtroTipo').value;
    var valor  = document.getElementById('filtroValor').value;
    cargarPacientesAPI(filtro, valor);

    document.querySelector('.card-body p-0').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function editarPaciente(idPersona) {
    window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=form_pacientes&modo=editar&id=' + idPersona;
}

function eliminarPaciente(idPersona) {
    Swal.fire({
        title: '¿Eliminar paciente?',
        text: 'Esta acci&oacute;n no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff6b81',
        cancelButtonColor: '#e9ecef',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch('app/cir-api/endpoints/gestion_pacientes-e.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'eliminar', id_persona: parseInt(idPersona) })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: data.message,
                        confirmButtonColor: '#00cec9'
                    });
                    cargarEstadisticas();
                    var fila = document.querySelector('tr[data-id="' + idPersona + '"]');
                    if (fila) {
                        fila.style.transition = 'opacity 0.4s';
                        fila.style.opacity    = '0';
                        setTimeout(function() { fila.remove(); }, 400);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#ff6b81'
                    });
                }
            })
            .catch(function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexi&oacute;n',
                    text: 'No se pudo conectar con el servidor.',
                    confirmButtonColor: '#ff6b81'
                });
            });
        }
    });
}
