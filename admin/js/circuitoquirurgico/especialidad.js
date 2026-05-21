// ============================================================
// VARIABLES GLOBALES
// ============================================================
var paginaActual    = 1;
var limitePorPagina = 10;
var ultimoFiltro    = 'especialidad';
var ultimoNombre    = '';

// ============================================================
// INICIALIZACIÓN
// ============================================================
function initEspecialidades() {
    cargarEstadisticas();
    actualizarPlaceholder();

    var tbody = document.getElementById('tablaEspecialidadesBody');
    if (tbody) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center py-5 text-secondary">' +
            '<i class="bi bi-search me-2"></i>' +
            'Ingrese un criterio de búsqueda para listar los registros.' +
            '</td></tr>';
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEspecialidades);
} else {
    initEspecialidades();
}

// ============================================================
// PLACEHOLDER DINÁMICO SEGÚN EL TIPO SELECCIONADO
// ============================================================
function actualizarPlaceholder() {
    var tipo  = document.getElementById('filtroTipo').value;
    var input = document.getElementById('filtroNombre');
    if (!input) return;
    if (tipo === 'especialidad') {
        input.placeholder = 'Ingrese nombre de especialidad...';
    } else {
        input.placeholder = 'Ingrese nombre de subespecialidad...';
    }
}

// ============================================================
// ESTADÍSTICAS
// ============================================================
function cargarEstadisticas() {
    fetch('app/cir-api/endpoints/especialidades-e.php?accion=stats', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data && data.stats) {
            var el = function(id) { return document.getElementById(id); };
            if (el('stat-total'))            el('stat-total').textContent            = data.stats.total            || 0;
            if (el('stat-especialidades'))   el('stat-especialidades').textContent   = data.stats.especialidades   || 0;
            if (el('stat-subespecialidades')) el('stat-subespecialidades').textContent = data.stats.subespecialidades || 0;
        }
    })
    .catch(function(err) { console.error('Error al cargar estadísticas:', err); });
}

// ============================================================
// EJECUTAR BÚSQUEDA
// ============================================================
function ejecutarBusqueda() {
    paginaActual  = 1;
    ultimoFiltro  = document.getElementById('filtroTipo').value;
    ultimoNombre  = document.getElementById('filtroNombre').value.trim();
    cargarTabla(ultimoFiltro, ultimoNombre, paginaActual);
}

// ============================================================
// CARGAR TABLA
// ============================================================
function cargarTabla(tipo, nombre, pagina) {
    var tbody = document.getElementById('tablaEspecialidadesBody');
    if (!tbody) return;

    tbody.innerHTML =
        '<tr><td colspan="5" class="text-center py-5 text-secondary">' +
        '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>' +
        'Buscando registros...</td></tr>';

    var url = 'app/cir-api/endpoints/especialidades-e.php' +
              '?accion=listar' +
              '&tipo=' + encodeURIComponent(tipo) +
              '&nombre=' + encodeURIComponent(nombre) +
              '&pagina=' + pagina +
              '&limite=' + limitePorPagina;

    fetch(url, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        tbody.innerHTML = '';

        if (data && data.registros && data.registros.length > 0) {
            data.registros.forEach(function(e, idx) {
                var tr   = document.createElement('tr');
                var num  = ((pagina - 1) * limitePorPagina) + idx + 1;
                var esPadre = !e.id_especialidad_padre;
                var badgeTipo = esPadre
                    ? '<span class="badge bg-light-success success rounded-pill px-2 py-1 fw-normal"><i class="bi bi-layers me-1"></i>Especialidad</span>'
                    : '<span class="badge bg-light-info info rounded-pill px-2 py-1 fw-normal"><i class="bi bi-diagram-3 me-1"></i>Subespecialidad</span>';
                var nombrePadre = e.nombre_padre ? escapeHTML(e.nombre_padre) : '<span class="text-secondary">—</span>';

                tr.setAttribute('data-id', e.id_especialidad);
                tr.innerHTML =
                    '<td class="ps-4 py-3 text-center text-secondary small">' + num + '</td>' +
                    '<td class="py-3 fw-medium">' + escapeHTML(e.nombre || '—') + '</td>' +
                    '<td class="py-3 text-center">' + badgeTipo + '</td>' +
                    '<td class="py-3 text-center">' + nombrePadre + '</td>' +
                    '<td class="py-3 pe-4 text-center">' +
                        '<button class="btn btn-sm btn-link blue me-2" title="Editar" ' +
                            'onclick="abrirModalEditar(' + e.id_especialidad + ', \'' + escJsAttr(e.nombre) + '\', ' + (e.id_especialidad_padre || 'null') + ')">' +
                            '<i class="bi bi-pencil-square"></i></button>' +
                        '<button class="btn btn-sm btn-link danger" title="Eliminar" ' +
                            'data-id="'     + e.id_especialidad + '" ' +
                            'data-nombre="' + escJsAttr(e.nombre) + '" ' +
                            'data-tipo="'   + (esPadre ? 'Especialidad' : 'Subespecialidad') + '" ' +
                            'onclick="abrirModalEliminar(this)">' +
                            '<i class="bi bi-trash"></i></button>' +
                    '</td>';
                tbody.appendChild(tr);
            });

            var desde = ((pagina - 1) * limitePorPagina) + 1;
            var hasta = desde + data.registros.length - 1;
            var infoPag = document.getElementById('infoPaginacion');
            if (infoPag) infoPag.textContent = 'Mostrando ' + desde + ' a ' + hasta + ' de ' + data.total + ' registros';

            renderPaginador(data.total, pagina, limitePorPagina);
        } else {
            tbody.innerHTML =
                '<tr><td colspan="5" class="text-center py-4 text-secondary">No se encontraron registros con ese criterio.</td></tr>';
            var infoPag = document.getElementById('infoPaginacion');
            if (infoPag) infoPag.textContent = 'Mostrando 0 de 0 registros';
            var pag = document.getElementById('paginador');
            if (pag) pag.innerHTML = '';
        }
    })
    .catch(function() {
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error al conectar con el servicio.</td></tr>';
    });
}

// ============================================================
// PAGINADOR
// ============================================================
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
    cargarTabla(ultimoFiltro, ultimoNombre, paginaActual);
}

// ============================================================
// MODAL NUEVO
// ============================================================
function abrirModalNuevo() {
    // Resetear
    document.getElementById('modalModo').value    = 'crear';
    document.getElementById('modalId').value      = '';
    document.getElementById('modalNombre').value  = '';
    document.getElementById('modalEspLabel').textContent    = 'Nueva Especialidad / Subespecialidad';
    document.getElementById('modalEspSubtitulo').textContent = 'Complete los datos del registro';

    // Mostrar selector de tipo
    document.getElementById('bloqueTipo').style.display = '';
    document.getElementById('radioEspecialidad').checked = true;
    cambiarTipoRegistro();

    // Limpiar validaciones
    limpiarValidacionesModal();

    var modal = new bootstrap.Modal(document.getElementById('modalEspecialidad'));
    modal.show();
}

// ============================================================
// MODAL EDITAR
// ============================================================
function abrirModalEditar(id, nombre, idPadre) {
    document.getElementById('modalModo').value   = 'editar';
    document.getElementById('modalId').value     = id;
    document.getElementById('modalNombre').value = nombre;
    document.getElementById('modalEspLabel').textContent     = 'Editar registro';
    document.getElementById('modalEspSubtitulo').textContent = 'Modifique los datos necesarios';

    // Ocultar selector de tipo en modo edición
    document.getElementById('bloqueTipo').style.display = 'none';
    limpiarValidacionesModal();

    if (idPadre) {
        // Es subespecialidad
        document.getElementById('radioSubespecialidad').checked = true;
        document.getElementById('bloqueEspecialidadPadre').style.display = '';
        document.getElementById('labelNombreModal').textContent = 'NOMBRE DE LA SUBESPECIALIDAD';
        cargarEspecialidadesPadre(idPadre);
    } else {
        // Es especialidad
        document.getElementById('radioEspecialidad').checked = true;
        document.getElementById('bloqueEspecialidadPadre').style.display = 'none';
        document.getElementById('labelNombreModal').textContent = 'NOMBRE DE LA ESPECIALIDAD';
    }

    var modal = new bootstrap.Modal(document.getElementById('modalEspecialidad'));
    modal.show();
}

// ============================================================
// CAMBIAR TIPO EN MODAL (radio buttons)
// ============================================================
function cambiarTipoRegistro() {
    var tipo = document.querySelector('input[name="tipoRegistro"]:checked').value;
    var bloqueP = document.getElementById('bloqueEspecialidadPadre');
    var label   = document.getElementById('labelNombreModal');

    if (tipo === 'subespecialidad') {
        bloqueP.style.display = '';
        label.textContent = 'NOMBRE DE LA SUBESPECIALIDAD';
        cargarEspecialidadesPadre(null);
    } else {
        bloqueP.style.display = 'none';
        label.textContent = 'NOMBRE DE LA ESPECIALIDAD';
    }
    document.getElementById('modalNombre').value = '';
    limpiarValidacionesModal();
}

// ============================================================
// CARGAR ESPECIALIDADES PADRE EN SELECT
// ============================================================
function cargarEspecialidadesPadre(seleccionado) {
    var sel = document.getElementById('modalEspecialidadPadre');
    sel.innerHTML = '<option value="">Cargando...</option>';

    fetch('app/cir-api/endpoints/especialidades-e.php?accion=listar&tipo=especialidad&nombre=&pagina=1&limite=500', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        sel.innerHTML = '<option value="">Seleccione la especialidad padre...</option>';
        if (data && data.registros) {
            data.registros.forEach(function(e) {
                var opt = document.createElement('option');
                opt.value = e.id_especialidad;
                opt.textContent = e.nombre;
                if (seleccionado && parseInt(seleccionado) === parseInt(e.id_especialidad)) {
                    opt.selected = true;
                }
                sel.appendChild(opt);
            });
        }
    })
    .catch(function() {
        sel.innerHTML = '<option value="">Error al cargar especialidades</option>';
    });
}

// ============================================================
// GUARDAR (CREAR / EDITAR)
// ============================================================
function guardarEspecialidad() {
    var modo   = document.getElementById('modalModo').value;
    var id     = document.getElementById('modalId').value;
    var nombre = document.getElementById('modalNombre').value.trim();
    var tipo   = document.querySelector('input[name="tipoRegistro"]:checked')
                    ? document.querySelector('input[name="tipoRegistro"]:checked').value
                    : (document.getElementById('bloqueEspecialidadPadre').style.display === '' ? 'subespecialidad' : 'especialidad');
    var padre  = document.getElementById('modalEspecialidadPadre').value || null;

    limpiarValidacionesModal();
    var valido = true;

    if (!nombre) {
        mostrarError('modalNombre', 'errorNombre', 'El nombre es obligatorio.');
        valido = false;
    }
    if (tipo === 'subespecialidad' && !padre) {
        mostrarError('modalEspecialidadPadre', 'errorPadre', 'Debe seleccionar la especialidad padre.');
        valido = false;
    }
    if (!valido) return;

    var body = {
        accion: modo === 'crear' ? 'crear' : 'editar',
        nombre: nombre,
        id_especialidad_padre: (tipo === 'subespecialidad' ? parseInt(padre) : null)
    };
    if (modo === 'editar') body.id = parseInt(id);

    var url    = 'app/cir-api/endpoints/especialidades-e.php';
    var method = 'POST';

    var btn = document.getElementById('btn-guardar-especialidad');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Guardando...';

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        var modalEl = document.getElementById('modalEspecialidad');
        var modal   = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        if (result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: result.message,
                confirmButtonColor: '#0984e3'
            }).then(function() {
                cargarEstadisticas();
                if (ultimoNombre || ultimoFiltro) {
                    cargarTabla(ultimoFiltro, ultimoNombre, paginaActual);
                }
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: result.message, confirmButtonColor: '#ff6b81' });
        }
    })
    .catch(function() {
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#ff6b81' });
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy me-1"></i> Guardar';
    });
}

// ============================================================
// MODAL ELIMINAR
// ============================================================
function abrirModalEliminar(btn) {
    document.getElementById('modal-elim-id').value         = btn.getAttribute('data-id');
    document.getElementById('modal-elim-nombre').textContent = btn.getAttribute('data-nombre');
    document.getElementById('modal-elim-tipo').textContent  = btn.getAttribute('data-tipo');

    var modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
    modal.show();
}

async function confirmarEliminar() {
    var id = document.getElementById('modal-elim-id').value;
    if (!id) return;

    var btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled = true;
    btn.textContent = 'Procesando...';

    try {
        var res    = await fetch('app/cir-api/endpoints/especialidades-e.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'eliminar', id: parseInt(id) })
        });
        var result = await res.json();

        var modalEl = document.getElementById('modalConfirmarEliminar');
        var modal   = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        if (result.status === 'success') {
            Swal.fire({ icon: 'success', title: '¡Eliminado!', text: result.message, confirmButtonColor: '#0984e3' });
            cargarEstadisticas();
            var fila = document.querySelector('tr[data-id="' + id + '"]');
            if (fila) {
                fila.style.transition = 'opacity 0.4s';
                fila.style.opacity    = '0';
                setTimeout(function() { fila.remove(); }, 400);
            }
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: result.message, confirmButtonColor: '#ff6b81' });
        }
    } catch(err) {
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#ff6b81' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash me-1"></i> Confirmar eliminación';
    }
}

// ============================================================
// UTILIDADES
// ============================================================
function escapeHTML(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escJsAttr(str) {
    return String(str || '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '&quot;');
}

function mostrarError(inputId, errorId, msg) {
    var input = document.getElementById(inputId);
    var err   = document.getElementById(errorId);
    if (input) input.classList.add('is-invalid');
    if (err)   { err.textContent = msg; err.style.display = 'block'; }
}

function limpiarValidacionesModal() {
    ['modalNombre', 'modalEspecialidadPadre'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    });
    ['errorNombre', 'errorPadre'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.textContent = ''; el.style.display = 'none'; }
    });
}
