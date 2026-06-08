(function() {
    var css = 
        'body.modo-oscuro .bg-light { background-color: #1e1e1e !important; color: #f1f1f1 !important; }' +
        'body.modo-oscuro .form-control.bg-light, body.modo-oscuro .form-select.bg-light { background-color: #1e1e1e !important; color: #f1f1f1 !important; border: 1px solid #444 !important; }' +
        'body.modo-oscuro .form-control.bg-light::placeholder { color: #888 !important; }' +
        'body.modo-oscuro .card-footer.bg-white, body.modo-oscuro .card-footer { background-color: #2a2a2a !important; border-top: 1px solid #444 !important; }' +
        'body.modo-oscuro .pagination .page-item .page-link { background-color: #1f1f1f !important; color: #ccc !important; border: 1px solid #444 !important; }' +
        'body.modo-oscuro .pagination .page-item.active .page-link { background-color: #375a7f !important; color: #fff !important; border-color: #2e4d6d !important; }' +
        'body.modo-oscuro .pagination .page-item.disabled .page-link { background-color: #1f1f1f !important; color: #555 !important; border: 1px solid #444 !important; }' +
        'body.modo-oscuro .pagination .page-item .page-link:hover { background-color: #333 !important; color: #fff !important; }' +
        'body.modo-oscuro .btn-outline-secondary { color: #ccc !important; border-color: #555 !important; }' +
        'body.modo-oscuro .btn-outline-secondary:hover { background-color: #444 !important; color: #fff !important; }' +
        'body.modo-oscuro .form-label.text-secondary { color: #bbb !important; }' +
        'body.modo-oscuro .text-secondary { color: #bbb !important; }' +
        'body.modo-oscuro .text-dark { color: #e0e0e0 !important; }' +
        '.modal-header-custom-blue { background: #eaf4ff; }' +
        'body.modo-oscuro .modal-header-custom-blue { background: #1a2a3a !important; }' +
        '.modal-header-custom-red { background: #fff3f5; }' +
        'body.modo-oscuro .modal-header-custom-red { background: #3a1a1a !important; }' +
        '.info-box-custom { background: #f8f9fa; border: 1px solid #e9ecef; }' +
        'body.modo-oscuro .info-box-custom { background: #1e1e1e !important; border-color: #444 !important; }' +
        /* Botones y pills de navegación */
        '.nav-pills .nav-link { color: #555; background-color: rgba(0, 0, 0, 0.03) !important; border: 1px solid rgba(0, 0, 0, 0.05) !important; transition: all 0.2s ease; margin-right: 4px; }' +
        '.nav-pills .nav-link:hover { background-color: rgba(0, 0, 0, 0.08) !important; color: #000 !important; }' +
        '.nav-pills .nav-link.active { background-color: #0984e3 !important; color: #fff !important; border-color: #0984e3 !important; }' +
        'body.modo-oscuro .nav-pills .nav-link { color: #ccc; background-color: rgba(255, 255, 255, 0.05) !important; border-color: rgba(255, 255, 255, 0.05) !important; }' +
        'body.modo-oscuro .nav-pills .nav-link:hover { background-color: rgba(255, 255, 255, 0.12) !important; color: #fff !important; }' +
        'body.modo-oscuro .nav-pills .nav-link.active { background-color: #375a7f !important; color: #fff !important; border-color: #375a7f !important; }' +
        /* Prevenir borde anidado feo en card-body bg-light */
        'body.modo-oscuro .card .card-body.bg-light { background-color: transparent !important; }' +
        /* Forzar transparencia de tab-content para que no encierre a los encabezados con bordes feos */
        'body.modo-oscuro #tabsContent.tab-content { background-color: transparent !important; border: none !important; box-shadow: none !important; }' +
        '.btn-link.blue { color: #0984e3; text-decoration: none; }' +
        '.btn-link.blue:hover { color: #076bba; }' +
        '.btn-link.danger { color: #ff6b81; text-decoration: none; }' +
        '.btn-link.danger:hover { color: #e04d65; }' +
        '.cursor-pointer { cursor: pointer; }';

    var style = document.createElement('style');
    style.type = 'text/css';
    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.head.appendChild(style);
})();

var API_CRUD = 'app/cir-api/endpoints/crud_catalogos-e.php';
var RGX_NOMBRE = /^[A-ZÁÉÍÓÚÜÑ0-9\s\-\/\(\)\.]{3,150}$/;

var paginaCirugias = 1;
var paginaEstudios = 1;
var paginaCombos   = 1;

var timerValNombreCir = null;
var timerValNombreEst = null;

/* ---------------------------------------------------------
   UTILIDADES
   --------------------------------------------------------- */
function esc(str) {
    return String(str || '')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}

function truncar(str, limite) {
    if (!str) return '—';
    if (str.length <= limite) return esc(str);
    return esc(str.substring(0, limite)) + '...';
}

function escJsStr(str) {
    return String(str || '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '&quot;');
}

function feedback(idEl, ok, msg) {
    var el = document.getElementById(idEl);
    if (!el) return;
    if (!msg) { el.innerHTML = ''; return; }
    var color = ok ? '#00b894' : '#e17055';
    var ico   = ok ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
    el.innerHTML = '<span style="color:' + color + ';"><i class="bi ' + ico + ' me-1"></i>' + esc(msg) + '</span>';
}

function apiPost(accion, cuerpo) {
    cuerpo.accion = accion;
    return fetch(API_CRUD, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cuerpo)
    }).then(function(r) { return r.json(); });
}

function apiGet(params) {
    return fetch(`${API_CRUD}?${new URLSearchParams(params).toString()}`).then(r => r.json());
}

function toastOk(msg) {
    Swal.fire({ icon: 'success', title: msg, showConfirmButton: false, timer: 1500, toast: true, position: 'top-end' });
}

function toastErr(msg) {
    Swal.fire({ icon: 'error', title: msg, showConfirmButton: false, timer: 2000, toast: true, position: 'top-end' });
}

function confirmarAccion(titulo, texto, onConfirm) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff6b81',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) onConfirm();
    });
}

function renderPaginador(elId, infoId, totalReg, limite, pagAct, fnCargarName) {
    var pagContainer = document.getElementById(elId);
    var infoContainer = document.getElementById(infoId);
    if (!pagContainer) return;

    var totalPags = Math.ceil(totalReg / limite) || 1;
    if (pagAct > totalPags) pagAct = totalPags;

    if (infoContainer) {
        var ini = totalReg === 0 ? 0 : (pagAct - 1) * limite + 1;
        var fin = pagAct * limite;
        if (fin > totalReg) fin = totalReg;
        infoContainer.textContent = 'Mostrando ' + ini + ' a ' + fin + ' de ' + totalReg + ' registros';
    }

    var html = '';
    
    // Botón Anterior
    var disPrev = pagAct === 1 ? 'disabled' : '';
    html += '<li class="page-item ' + disPrev + '"><a class="page-link" href="#" onclick="event.preventDefault(); ' + (pagAct > 1 ? fnCargarName + '(' + (pagAct - 1) + ')' : '') + '">Anterior</a></li>';

    // Rango de páginas (máx 5 a mostrar)
    var start = Math.max(1, pagAct - 2);
    var end = Math.min(totalPags, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);

    for (var p = start; p <= end; p++) {
        var act = p === pagAct ? 'active' : '';
        html += '<li class="page-item ' + act + '"><a class="page-link" href="#" onclick="event.preventDefault(); ' + fnCargarName + '(' + p + ')">' + p + '</a></li>';
    }

    // Botón Siguiente
    var disNext = pagAct === totalPags ? 'disabled' : '';
    html += '<li class="page-item ' + disNext + '"><a class="page-link" href="#" onclick="event.preventDefault(); ' + (pagAct < totalPags ? fnCargarName + '(' + (pagAct + 1) + ')' : '') + '">Siguiente</a></li>';

    pagContainer.innerHTML = html;
}

/* =========================================================
   TAB CIRUGÍAS
   ========================================================= */
window.cirugiasListadas = false;

window.listarTodosCirugias = function() {
    var elBuscar = document.getElementById('buscar-cirugia');
    if (elBuscar) elBuscar.value = '';
    window.cirugiasListadas = true;
    cargarCirugias(1);
};

window.cargarCirugias = function(pagina) {
    paginaCirugias = pagina || 1;
    var elBuscar = document.getElementById('buscar-cirugia');
    var buscar = elBuscar ? elBuscar.value.trim() : '';
    var tbody = document.getElementById('tabla-cirugias-body');
    var orden = document.getElementById('orden-cirugia') ? document.getElementById('orden-cirugia').value : 'nombre_asc';

    if (buscar === '' && !window.cirugiasListadas) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-secondary"><i class="bi bi-search fs-3 d-block mb-2"></i>Ingrese un criterio de búsqueda o haga clic en "TODOS"</td></tr>';
        return;
    }

    window.cirugiasListadas = true;
    
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Cargando...</td></tr>';
    }

    apiGet({ accion: 'listar_cirugias', buscar: buscar, orden: orden, pagina: paginaCirugias, limite: 10 })
        .then(function(data) {
            if (data.status !== 'success') { toastErr(data.message); return; }
            renderTablaCirugias(data.registros);
            var info = data.total + ' cirugía' + (data.total !== 1 ? 's' : '') + ' encontrada' + (data.total !== 1 ? 's' : '');
            var elInfo = document.getElementById('info-cirugias');
            if (elInfo) elInfo.textContent = info;
            renderPaginador('pag-cirugias', 'pag-info-cirugias', data.total, 10, paginaCirugias, 'cargarCirugias');
        })
        .catch(function(err) { console.error(err); toastErr('Error al cargar cirugías.'); });
};

function renderTablaCirugias(registros) {
    var tbody = document.getElementById('tabla-cirugias-body');
    if (!tbody) return;
    if (!registros || registros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-5 text-secondary"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No se encontraron cirugías.</td></tr>';
        return;
    }
    
    tbody.innerHTML = registros.map(c => `
        <tr>
            <td class="fw-semibold text-center" style="font-size:0.92rem;">${esc(c.nombre)}</td>
            <td class="text-secondary text-center" style="font-size:0.88rem;">${truncar(c.descripcion, 60)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-link text-info me-2" title="Ver Detalles" onclick="verDetalleCirugia('${escJsStr(c.nombre)}','${escJsStr(c.descripcion)}')"><i class="bi bi-eye"></i></button>
                <button class="btn btn-sm btn-link blue me-2" title="Editar" onclick="abrirModalEditarCirugia(${c.id},'${escJsStr(c.nombre)}','${escJsStr(c.descripcion)}')"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-link danger" title="Eliminar" onclick="confirmarEliminar('cirugia',${c.id},'${escJsStr(c.nombre)}')"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

window.verDetalleCirugia = function(nombre, descripcion) {
    var desc = descripcion ? esc(descripcion) : '<em class="text-muted">Sin descripción registrada.</em>';
    document.getElementById('detalles-contenido').innerHTML = `
        <div class="mb-3"><h5 class="fw-bold text-primary">${esc(nombre)}</h5></div>
        <div class="info-box-custom p-3 rounded-3 mb-2">
            <span class="text-secondary d-block small fw-bold mb-1">DESCRIPCIÓN DE LA CIRUGÍA</span>
            <p class="mb-0 text-dark" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.5;">${desc}</p>
        </div>
    `;
    var modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
    modal.show();
};

window.abrirModalNuevaCirugia = function() {
    document.getElementById('mc-id').value = '';
    document.getElementById('mc-nombre').value = '';
    document.getElementById('mc-descripcion').value = '';
    document.getElementById('mc-contador-desc').textContent = '0';
    document.getElementById('modalCirugiaLabel').textContent = 'Nueva Cirugía';
    document.getElementById('modal-cirugia-subtitulo').textContent = 'Complete los datos del catálogo';
    feedback('mc-nombre-feedback', false, '');
    var modal = new bootstrap.Modal(document.getElementById('modalCirugia'));
    modal.show();
};

window.abrirModalEditarCirugia = function(id, nombre, descripcion) {
    document.getElementById('mc-id').value = id;
    document.getElementById('mc-nombre').value = nombre;
    document.getElementById('mc-descripcion').value = descripcion;
    document.getElementById('mc-contador-desc').textContent = descripcion.length;
    document.getElementById('modalCirugiaLabel').textContent = 'Editar Cirugía';
    document.getElementById('modal-cirugia-subtitulo').textContent = 'Modificando: ' + nombre;
    feedback('mc-nombre-feedback', true, '');
    var modal = new bootstrap.Modal(document.getElementById('modalCirugia'));
    modal.show();
};

window.guardarCirugia = function() {
    var id          = document.getElementById('mc-id').value;
    var nombre      = document.getElementById('mc-nombre').value.trim();
    var descripcion = document.getElementById('mc-descripcion').value.trim();

    if (!nombre || !RGX_NOMBRE.test(nombre)) {
        feedback('mc-nombre-feedback', false, 'Nombre inválido. Solo letras mayúsculas, números y Ñ. Mín. 3 caracteres.');
        return;
    }

    var accion = id ? 'editar_cirugia' : 'crear_cirugia';
    var cuerpo = { nombre: nombre, descripcion: descripcion };
    if (id) cuerpo.id = parseInt(id);
    
    var btn  = document.getElementById('btn-guardar-cirugia');
    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    apiPost(accion, cuerpo)
        .then(function(data) {
            if (data.status === 'success') {
                var modalEl = document.getElementById('modalCirugia');
                var modalInst = bootstrap.Modal.getInstance(modalEl);
                if (modalInst) modalInst.hide();
                toastOk(data.message);
                cargarCirugias(paginaCirugias);
            } else {
                feedback('mc-nombre-feedback', false, data.message);
            }
        })
        .catch(function() { toastErr('Error de red al guardar cirugía.'); })
        .finally(function() { btn.disabled = false; btn.innerHTML = orig; });
};

/* =========================================================
   TAB ESTUDIOS
   ========================================================= */
window.estudiosListados = false;

window.listarTodosEstudios = function() {
    var elBuscar = document.getElementById('buscar-estudio');
    if (elBuscar) elBuscar.value = '';
    window.estudiosListados = true;
    cargarEstudios(1);
};

window.cargarEstudios = function(pagina) {
    paginaEstudios = pagina || 1;
    var elBuscar = document.getElementById('buscar-estudio');
    var buscar = elBuscar ? elBuscar.value.trim() : '';
    var tbody = document.getElementById('tabla-estudios-body');
    var orden = document.getElementById('orden-estudio') ? document.getElementById('orden-estudio').value : 'nombre_asc';
    
    if (buscar === '' && !window.estudiosListados) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-secondary"><i class="bi bi-search fs-3 d-block mb-2"></i>Ingrese un criterio de búsqueda o haga clic en "TODOS"</td></tr>';
        return;
    }

    window.estudiosListados = true;
    
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Cargando...</td></tr>';
    }

    apiGet({ accion: 'listar_estudios', buscar: buscar, orden: orden, pagina: paginaEstudios, limite: 10 })
        .then(function(data) {
            if (data.status !== 'success') { toastErr(data.message); return; }
            renderTablaEstudios(data.registros);
            renderPaginador('pag-estudios', 'pag-info-estudios', data.total, 10, paginaEstudios, 'cargarEstudios');
        })
        .catch(function() { toastErr('Error al cargar estudios.'); });
};

function renderTablaEstudios(registros) {
    var tbody = document.getElementById('tabla-estudios-body');
    if (!tbody) return;
    if (!registros || registros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-secondary"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No se encontraron estudios.</td></tr>';
        return;
    }
    
    tbody.innerHTML = registros.map(e => `
        <tr>
            <td class="fw-semibold text-center" style="font-size:0.92rem;">${esc(e.nombre)}</td>
            <td class="text-secondary text-center" style="font-size:0.88rem;">${truncar(e.descripcion, 60)}</td>
            <td class="text-secondary text-center" style="font-size:0.88rem;">${truncar(e.observacion, 60)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-link text-info me-2" title="Ver Detalles" onclick="verDetalleEstudio('${escJsStr(e.nombre)}','${escJsStr(e.descripcion)}','${escJsStr(e.observacion)}')"><i class="bi bi-eye"></i></button>
                <button class="btn btn-sm btn-link blue me-2" title="Editar" onclick="abrirModalEditarEstudio(${e.id},'${escJsStr(e.nombre)}','${escJsStr(e.descripcion)}','${escJsStr(e.observacion)}')"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-link danger" title="Eliminar" onclick="confirmarEliminar('estudio',${e.id},'${escJsStr(e.nombre)}')"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

window.verDetalleEstudio = function(nombre, descripcion, observacion) {
    var desc = descripcion ? esc(descripcion) : '<em class="text-muted">Sin descripción registrada.</em>';
    var obs = observacion ? esc(observacion) : '<em class="text-muted">Sin observaciones o requisitos registrados.</em>';
    document.getElementById('detalles-contenido').innerHTML = `
        <div class="mb-3"><h5 class="fw-bold text-primary">${esc(nombre)}</h5></div>
        <div class="info-box-custom p-3 rounded-3 mb-3">
            <span class="text-secondary d-block small fw-bold mb-1">DESCRIPCIÓN / DEFINICIÓN</span>
            <p class="mb-0 text-dark" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.5;">${desc}</p>
        </div>
        <div class="info-box-custom p-3 rounded-3 mb-2" style="border-left: 4px solid #0984e3 !important;">
            <span class="text-secondary d-block small fw-bold mb-1">REQUISITOS / OBSERVACIONES AL PACIENTE</span>
            <p class="mb-0 text-dark" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.5;">${obs}</p>
        </div>
    `;
    var modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
    modal.show();
};

window.abrirModalNuevoEstudio = function() {
    document.getElementById('me-id').value = '';
    document.getElementById('me-nombre').value = '';
    document.getElementById('me-descripcion').value = '';
    document.getElementById('me-observacion').value = '';
    document.getElementById('me-contador-desc').textContent = '0';
    document.getElementById('me-contador-obs').textContent = '0';
    document.getElementById('modalEstudioLabel').textContent = 'Nuevo Estudio';
    document.getElementById('modal-estudio-subtitulo').textContent = 'Complete los datos del catálogo';
    feedback('me-nombre-feedback', false, '');
    var modal = new bootstrap.Modal(document.getElementById('modalEstudio'));
    modal.show();
};

window.abrirModalEditarEstudio = function(id, nombre, descripcion, observacion) {
    document.getElementById('me-id').value = id;
    document.getElementById('me-nombre').value = nombre;
    document.getElementById('me-descripcion').value = descripcion;
    document.getElementById('me-observacion').value = observacion;
    document.getElementById('me-contador-desc').textContent = descripcion.length;
    document.getElementById('me-contador-obs').textContent = observacion.length;
    document.getElementById('modalEstudioLabel').textContent = 'Editar Estudio';
    document.getElementById('modal-estudio-subtitulo').textContent = 'Modificando: ' + nombre;
    feedback('me-nombre-feedback', true, '');
    var modal = new bootstrap.Modal(document.getElementById('modalEstudio'));
    modal.show();
};

window.guardarEstudio = function() {
    var id          = document.getElementById('me-id').value;
    var nombre      = document.getElementById('me-nombre').value.trim();
    var descripcion = document.getElementById('me-descripcion').value.trim();
    var observacion = document.getElementById('me-observacion').value.trim();

    if (!nombre || !RGX_NOMBRE.test(nombre)) {
        feedback('me-nombre-feedback', false, 'Nombre inválido. Solo letras mayúsculas, números y Ñ. Mín. 3 caracteres.');
        return;
    }

    var accion = id ? 'editar_estudio' : 'crear_estudio';
    var cuerpo = { nombre: nombre, descripcion: descripcion, observacion: observacion };
    if (id) cuerpo.id = parseInt(id);

    var btn  = document.getElementById('btn-guardar-estudio');
    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    apiPost(accion, cuerpo)
        .then(function(data) {
            if (data.status === 'success') {
                var modalEl = document.getElementById('modalEstudio');
                var modalInst = bootstrap.Modal.getInstance(modalEl);
                if (modalInst) modalInst.hide();
                toastOk(data.message);
                cargarEstudios(paginaEstudios);
            } else {
                feedback('me-nombre-feedback', false, data.message);
            }
        })
        .catch(function() { toastErr('Error de red al guardar estudio.'); })
        .finally(function() { btn.disabled = false; btn.innerHTML = orig; });
};

/* =========================================================
   TAB COMBOS
   ========================================================= */
window.combosListados = false;

window.listarTodosCombos = function() {
    var elBuscar = document.getElementById('buscar-combo');
    if (elBuscar) elBuscar.value = '';
    var elFiltro = document.getElementById('filtro-cirugia-combo');
    if (elFiltro) elFiltro.value = '';
    window.combosListados = true;
    cargarCombos(1);
};

window.cargarCombos = function(pagina) {
    paginaCombos = pagina || 1;
    var elBuscar = document.getElementById('buscar-combo');
    var buscar = elBuscar ? elBuscar.value.trim() : '';
    var idCirugia = document.getElementById('filtro-cirugia-combo').value;
    var tbody = document.getElementById('tabla-combos-body');
    
    if (buscar === '' && !idCirugia && !window.combosListados) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="3" class="text-center py-5 text-secondary"><i class="bi bi-search fs-3 d-block mb-2"></i>Ingrese un criterio de búsqueda o haga clic en "TODOS"</td></tr>';
        return;
    }

    window.combosListados = true;
    
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Cargando...</td></tr>';
    }

    var params = { accion: 'listar_combos', buscar: buscar, pagina: paginaCombos, limite: 10 };
    if (idCirugia) params.id_cirugia = idCirugia;

    apiGet(params)
        .then(function(data) {
            if (data.status !== 'success') { toastErr(data.message); return; }
            renderTablaCombos(data.registros);
            renderPaginador('pag-combos', 'pag-info-combos', data.total, 10, paginaCombos, 'cargarCombos');
        })
        .catch(function() { toastErr('Error al cargar combos.'); });
};

function renderTablaCombos(registros) {
    var tbody = document.getElementById('tabla-combos-body');
    if (!tbody) return;
    if (!registros || registros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-5 text-secondary"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No se encontraron combinaciones.</td></tr>';
        return;
    }
    
    // Agrupar registros por id_cirugia_catalogo
    var grouped = [];
    var map = {};
    for (var i = 0; i < registros.length; i++) {
        var r = registros[i];
        var idCir = r.id_cirugia_catalogo;
        if (map[idCir] === undefined) {
            map[idCir] = grouped.length;
            grouped.push({ id_cirugia: idCir, cirugia: r.cirugia, estudios: [] });
        }
        grouped[map[idCir]].estudios.push({ id_estudio: r.id_estudio, nombre: r.estudio, observacion: r.observacion });
    }

    tbody.innerHTML = grouped.map(g => {
        var badgesHtml = g.estudios.map(est => `
            <span class="badge bg-secondary text-white me-2 mb-1 px-2 py-1 align-items-center d-inline-flex" style="font-size: 0.82rem; font-weight: 500;">
                ${esc(est.nombre)}
            </span>
        `).join('');

        var gJsonStr = escJsStr(JSON.stringify(g));

        return `
            <tr>
                <td class="fw-semibold text-center" style="font-size:0.92rem; width: 40%;">${esc(g.cirugia)}</td>
                <td class="text-center" style="width: 45%;">${badgesHtml}</td>
                <td class="text-center" style="width: 15%;">
                    <button class="btn btn-sm btn-link text-info me-1" title="Ver Detalles" onclick="verDetalleCombo('${gJsonStr}')">
                        <i class="bi bi-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

window.verDetalleCombo = function(gJsonStr) {
    var div = document.createElement('div');
    div.innerHTML = gJsonStr;
    var gData = JSON.parse(div.innerText || div.textContent);

    var rows = gData.estudios.map(est => {
        var obs = est.observacion ? esc(est.observacion) : '<em class="text-muted">Sin observaciones o requisitos registrados.</em>';
        return `
            <tr>
                <td class="fw-semibold text-dark" style="font-size: 0.9rem;">${esc(est.nombre)}</td>
                <td class="text-secondary" style="font-size: 0.88rem; line-height: 1.4;">${obs}</td>
            </tr>
        `;
    }).join('');

    document.getElementById('detalles-contenido').innerHTML = `
        <div class="mb-3"><h5 class="fw-bold text-primary">Cirugía: ${esc(gData.cirugia)}</h5></div>
        <div class="mb-3"><span class="text-secondary small fw-bold">ESTUDIOS VINCULADOS Y REQUISITOS</span></div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40%;">Estudio</th>
                        <th style="width: 60%;">Observaciones / Requisitos al Paciente</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
    
    var modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
    modal.show();
};

window.poblarSelectCirugias = function() {
    apiGet({ accion: 'listar_cirugias_select' })
        .then(function(data) {
            if (data.status !== 'success') return;
            var sel = document.getElementById('filtro-cirugia-combo');
            if (!sel) return;
            var html = '<option value="">— Todas las cirugías —</option>';
            for (var i = 0; i < data.cirugias.length; i++) {
                var c = data.cirugias[i];
                html += '<option value="' + c.id + '">' + esc(c.nombre) + '</option>';
            }
            sel.innerHTML = html;
        });
};

/* =========================================================
   MODAL ELIMINAR GENÉRICO
   ========================================================= */
window.confirmarEliminar = function(tipo, id, nombre) {
    document.getElementById('modal-elim-id').value    = id;
    document.getElementById('modal-elim-tipo').value  = tipo;
    document.getElementById('modal-elim-nombre').textContent = nombre;
    
    var msgs = {
        cirugia: '¿Está seguro de eliminar esta cirugía? Solo es posible si no tiene estudios vinculados ni órdenes médicas.',
        estudio: '¿Está seguro de eliminar este estudio? Solo es posible si no pertenece a ningún combo ni orden médica.'
    };
    document.getElementById('modal-elim-msg').textContent = msgs[tipo] || '¿Está seguro de eliminar este registro?';
    
    var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
};

window.confirmarEliminarCombo = function(idCirugia, idEstudio, nombre) {
    document.getElementById('modal-elim-id').value    = idCirugia + '|' + idEstudio;
    document.getElementById('modal-elim-tipo').value  = 'combo';
    document.getElementById('modal-elim-nombre').textContent = nombre;
    document.getElementById('modal-elim-msg').textContent = '¿Está seguro de eliminar esta vinculación Cirugía–Estudio?';
    
    var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
};

window.ejecutarEliminar = function() {
    var tipo  = document.getElementById('modal-elim-tipo').value;
    var idRaw = document.getElementById('modal-elim-id').value;
    var btn   = document.getElementById('btn-confirmar-eliminar');
    var orig  = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Eliminando...';

    var cuerpo = {};
    if (tipo === 'combo') {
        var partes = idRaw.split('|');
        cuerpo = { id_cirugia: parseInt(partes[0]), id_estudio: parseInt(partes[1]) };
    } else {
        cuerpo = { id: parseInt(idRaw) };
    }

    apiPost('eliminar_' + tipo, cuerpo)
        .then(function(data) {
            var modalEl = document.getElementById('modalEliminar');
            var modalInst = bootstrap.Modal.getInstance(modalEl);
            if (modalInst) modalInst.hide();
            
            if (data.status === 'success') {
                toastOk(data.message);
                if (tipo === 'cirugia') cargarCirugias(paginaCirugias);
                else if (tipo === 'estudio') cargarEstudios(paginaEstudios);
                else if (tipo === 'combo') cargarCombos(paginaCombos);
            } else {
                Swal.fire({ icon:'error', title:'No se puede eliminar', text: data.message,
                    confirmButtonColor:'#ff6b81', confirmButtonText:'Entendido' });
            }
        })
        .catch(function() { toastErr('Error de red al eliminar.'); })
        .finally(function() { btn.disabled = false; btn.innerHTML = orig; });
};

/* =========================================================
   VALIDACIÓN EN VIVO CON DEBOUNCE
   ========================================================= */
function validarNombreEnVivo(inputId, feedbackId, accion, excluirId, currentTimer) {
    var el = document.getElementById(inputId);
    var val = el ? el.value.trim().toUpperCase() : '';
    if (el) el.value = val;

    if (!val || !RGX_NOMBRE.test(val)) {
        feedback(feedbackId, false, val ? 'Solo letras mayúsculas, números y Ñ. Mín. 3 caracteres.' : '');
        return null;
    }

    feedback(feedbackId, true, 'Verificando disponibilidad...');
    clearTimeout(currentTimer);
    
    return setTimeout(function() {
        var params = { accion: accion, nombre: val };
        if (excluirId) params.excluir = excluirId;
        
        apiGet(params).then(function(data) {
            feedback(feedbackId, data.status === 'success', data.message);
        }).catch(function() { 
            feedback(feedbackId, false, 'Error al validar.'); 
        });
    }, 500);
}

/* =========================================================
   INICIALIZACIÓN DE EVENTOS
   ========================================================= */
document.addEventListener('DOMContentLoaded', function() {
    
    // Carga inicial select, las tablas esperan acción
    poblarSelectCirugias();

    // Botones principales
    var btnNuevaCirugia = document.getElementById('btn-nueva-cirugia');
    if (btnNuevaCirugia) btnNuevaCirugia.onclick = abrirModalNuevaCirugia;
    
    var btnNuevoEstudio = document.getElementById('btn-nuevo-estudio');
    if (btnNuevoEstudio) btnNuevoEstudio.onclick = abrirModalNuevoEstudio;
    
    var btnGuardarCirugia = document.getElementById('btn-guardar-cirugia');
    if (btnGuardarCirugia) btnGuardarCirugia.onclick = guardarCirugia;
    
    var btnGuardarEstudio = document.getElementById('btn-guardar-estudio');
    if (btnGuardarEstudio) btnGuardarEstudio.onclick = guardarEstudio;
    
    var btnConfirmarElim = document.getElementById('btn-confirmar-eliminar');
    if (btnConfirmarElim) btnConfirmarElim.onclick = ejecutarEliminar;

    // Buscadores (Debounce manual para no saturar peticiones)
    var tbuscarCir, tbuscarEst, tbuscarCombo;
    
    var elBuscarCirugia = document.getElementById('buscar-cirugia');
    if (elBuscarCirugia) elBuscarCirugia.onkeyup = function() {
        clearTimeout(tbuscarCir);
        tbuscarCir = setTimeout(function(){ cargarCirugias(1); }, 400);
    };

    var elBuscarEstudio = document.getElementById('buscar-estudio');
    if (elBuscarEstudio) elBuscarEstudio.onkeyup = function() {
        clearTimeout(tbuscarEst);
        tbuscarEst = setTimeout(function(){ cargarEstudios(1); }, 400);
    };

    var elBuscarCombo = document.getElementById('buscar-combo');
    if (elBuscarCombo) elBuscarCombo.onkeyup = function() {
        clearTimeout(tbuscarCombo);
        tbuscarCombo = setTimeout(function(){ cargarCombos(1); }, 400);
    };

    var elFiltroCir = document.getElementById('filtro-cirugia-combo');
    if (elFiltroCir) elFiltroCir.onchange = function() { cargarCombos(1); };

    // Validaciones en vivo
    var inputCir = document.getElementById('mc-nombre');
    if (inputCir) inputCir.oninput = function() {
        var excluirId = document.getElementById('mc-id').value || null;
        timerValNombreCir = validarNombreEnVivo('mc-nombre', 'mc-nombre-feedback', 'validar_nombre_cirugia', excluirId, timerValNombreCir);
    };

    var inputEst = document.getElementById('me-nombre');
    if (inputEst) inputEst.oninput = function() {
        var excluirId = document.getElementById('me-id').value || null;
        timerValNombreEst = validarNombreEnVivo('me-nombre', 'me-nombre-feedback', 'validar_nombre_estudio', excluirId, timerValNombreEst);
    };

    var inputDesc = document.getElementById('mc-descripcion');
    if (inputDesc) inputDesc.oninput = function() {
        var counter = document.getElementById('mc-contador-desc');
        if (counter) counter.textContent = this.value.length;
    };

    // Cambios de Tab
    var tabEstudiosBtn = document.getElementById('tab-estudios-btn');
    if (tabEstudiosBtn) tabEstudiosBtn.addEventListener('shown.bs.tab', function() { if(window.estudiosListados) cargarEstudios(1); });
    
    var tabCombosBtn = document.getElementById('tab-combos-btn');
    if (tabCombosBtn) tabCombosBtn.addEventListener('shown.bs.tab', function() { if(window.combosListados) cargarCombos(1); poblarSelectCirugias(); });
});
