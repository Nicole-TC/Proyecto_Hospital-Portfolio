// Variables globales de paginación
var paginaActual     = 1;
var limitePorPagina  = 10;

// Ejecutar al estar listo el DOM
function initGestion() {
    // Cargamos las estadísticas iniciales (conteo por roles)
    cargarEstadisticas();

    // No cargamos la tabla de entrada por rendimiento
    var tbody = document.getElementById('tablaUsuariosBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-secondary">' +
                          '<i class="bi bi-search me-2"></i> Ingrese un criterio de búsqueda para listar los usuarios.' +
                          '</td></tr>';
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGestion);
} else {
    initGestion();
}

// ============================================================
// ACTUALIZAR ESTADÍSTICAS
// ============================================================
function cargarEstadisticas() {
    fetch('app/cir-api/endpoints/listar_users-e.php', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data && data.stats) {
            if (document.getElementById('stat-total')) document.getElementById('stat-total').textContent = data.stats.total || 0;
            if (document.getElementById('stat-cirujanos')) document.getElementById('stat-cirujanos').textContent = data.stats.cirujanos || 0;
            if (document.getElementById('stat-recepcionistas')) document.getElementById('stat-recepcionistas').textContent = data.stats.recepcionistas || 0;
            if (document.getElementById('stat-admins')) document.getElementById('stat-admins').textContent = data.stats.admins || 0;
        }
    })
    .catch(function(err) { console.error('Error al cargar estadísticas:', err); });
}

// ============================================================
// EJECUTAR BÚSQUEDA
// ============================================================
function ejecutarBusqueda() {
    paginaActual = 1; // Al buscar de nuevo, volvemos a la página 1
    var filtro = document.getElementById('filtroTipo').value;
    var valor  = document.getElementById('filtroValor').value;

    // Aunque el usuario dijo "si o si tiene que ingresar algo", 
    // también dijo "si no ingresa nada se muestra todo".
    // Interpretamos que debe interactuar con el botón de búsqueda.
    cargarUsuariosAPI(filtro, valor);
}

// ============================================================
// OBTENER ESTILO DE ROL (Usando recursos de admin/css/colors.min.css)
// ============================================================
function getRolBadgeClass(rol) {
    if (!rol) return 'bg-light-secondary text-secondary';
    var r = rol.toLowerCase();
    
    // El sistema actualmente solo maneja estos 3 roles
    if (r.includes('admin')) return 'bg-light-primary primary';
    if (r.includes('cirujano')) return 'bg-light-success success';
    if (r.includes('recepcionista') || r.includes('admision')) return 'bg-light-info info';
    
    return 'bg-light-secondary text-secondary';
}

// ============================================================
// CARGAR TABLA DE USUARIOS
// ============================================================
function cargarUsuariosAPI(filtro, valor) {
    var tbody = document.getElementById('tablaUsuariosBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-secondary">' +
                      '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>' +
                      'Buscando usuarios...</td></tr>';

    var url = 'app/cir-api/endpoints/listar_users-e.php?pagina=' + paginaActual + '&limite=' + limitePorPagina;
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
        
        // Actualizar estadísticas también al buscar
        if (data && data.stats) {
            if (document.getElementById('stat-total')) document.getElementById('stat-total').textContent = data.stats.total || 0;
            if (document.getElementById('stat-cirujanos')) document.getElementById('stat-cirujanos').textContent = data.stats.cirujanos || 0;
            if (document.getElementById('stat-recepcionistas')) document.getElementById('stat-recepcionistas').textContent = data.stats.recepcionistas || 0;
            if (document.getElementById('stat-admins')) document.getElementById('stat-admins').textContent = data.stats.admins || 0;
        }

        if (data && data.usuarios && data.usuarios.length > 0) {
            data.usuarios.forEach(function(u) {
                var tr = document.createElement('tr');
                tr.setAttribute('data-id', u.id_persona);
                var rolNombre    = u.rol_nombre  || 'Sin rol';
                var nombreCompleto = ((u.nombre || '') + ' ' + (u.apellido || '')).trim();
                var badgeClass   = getRolBadgeClass(rolNombre);

                // Escapar comillas para data-attributes
                var safe = function(s) { return String(s).replace(/"/g, '&quot;'); };

                tr.innerHTML =
                    '<td class="ps-4 py-3 fw-medium text-center">' + nombreCompleto + '</td>' +
                    '<td class="py-3 text-center">' + (u.email || 'Sin email') + '</td>' +
                    '<td class="py-3 text-center"><span class="badge ' + badgeClass + ' rounded-pill px-2 py-1 fw-normal">' + rolNombre + '</span></td>' +
                    // '<td class="py-3 text-center"><span class="badge bg-light-success success rounded-pill px-2 py-1 fw-normal"><i class="fa-solid fa-circle" style="font-size:0.5rem;vertical-align:middle;"></i> Registrado</span></td>' +
                    // '<td class="py-3 text-secondary text-center">Hoy</td>' +
                    '<td class="py-3 pe-4 text-center">' +
                        '<button class="btn btn-sm btn-link blue me-2" title="Editar usuario" onclick="editarUsuario(' + u.id_persona + ')"><i class="bi bi-pencil-square"></i></button>' +
                        '<button class="btn btn-sm btn-link danger" title="Dar de baja usuario"' +
                            ' data-id="'     + u.id_persona + '"' +
                            ' data-dni="'    + safe(u.dni) + '"' +
                            ' data-nombre="' + safe(nombreCompleto) + '"' +
                            ' data-rol="'    + safe(rolNombre) + '"' +
                            ' onclick="abrirModalBaja(this)"><i class="bi bi-trash"></i></button>' +
                    '</td>';
                tbody.appendChild(tr);
            });
            
            // Actualizar info y controles de paginación
            if (document.getElementById('infoPaginacion')) {
                var desde = ((paginaActual - 1) * limitePorPagina) + 1;
                var hasta = desde + data.usuarios.length - 1;
                document.getElementById('infoPaginacion').textContent = 'Mostrando ' + desde + ' a ' + hasta + ' de ' + data.total + ' registros';
            }
            renderPaginador(data.total, paginaActual, limitePorPagina);

        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-secondary">No se encontraron usuarios con esos criterios.</td></tr>';
            if (document.getElementById('infoPaginacion')) document.getElementById('infoPaginacion').textContent = 'Mostrando 0 de 0 registros';
            if (document.getElementById('paginador')) document.getElementById('paginador').innerHTML = '';
        }
    })
    .catch(function(err) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error al conectar con el servicio.</td></tr>';
    });
}

// ============================================================
// RENDERIZAR PAGINADOR
// ============================================================
function renderPaginador(total, pagina, limite) {
    var paginador = document.getElementById('paginador');
    if (!paginador) return;
    paginador.innerHTML = '';

    var totalPaginas = Math.ceil(total / limite);
    if (totalPaginas <= 1) return;

    // Botón Anterior
    var liPrev = document.createElement('li');
    liPrev.className = 'page-item ' + (pagina === 1 ? 'disabled' : '');
    liPrev.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(' + (pagina - 1) + ')">Anterior</a>';
    paginador.appendChild(liPrev);

    // Páginas numéricas (limitadas a 5 alrededor de la actual por simplicidad)
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

    // Botón Siguiente
    var liNext = document.createElement('li');
    liNext.className = 'page-item ' + (pagina === totalPaginas ? 'disabled' : '');
    liNext.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(' + (pagina + 1) + ')">Siguiente</a>';
    paginador.appendChild(liNext);
}

// ============================================================
// CAMBIAR PÁGINA
// ============================================================
function cambiarPagina(nuevaPagina) {
    if (nuevaPagina < 1) return;
    paginaActual = nuevaPagina;
    
    var filtro = document.getElementById('filtroTipo').value;
    var valor  = document.getElementById('filtroValor').value;
    cargarUsuariosAPI(filtro, valor);
    
    // Hacer scroll suave hacia la tabla
    document.querySelector('.card-body p-0').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ============================================================
// EDITAR USUARIO
// ============================================================
function editarUsuario(idPersona) {
    window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=form_users&modo=editar&id=' + idPersona;
}

// ============================================================
// CONFIRMACION DE BAJA (funcion global llamada por onclick del boton)
// ============================================================
async function confirmarBaja() {
    var idPersona = document.getElementById('modal-baja-id').value;
    if (!idPersona) return;

    var btnConfirmar = document.getElementById('btn-confirmar-baja');
    if (btnConfirmar) {
        btnConfirmar.disabled   = true;
        btnConfirmar.textContent = 'Procesando...';
    }

    try {
        var res    = await fetch('app/cir-api/endpoints/desactivar_user-e.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_persona: parseInt(idPersona) })
        });
        var result = await res.json();

        // Cerrar modal
        var modalEl = document.getElementById('modalConfirmarBaja');
        var modal   = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        if (result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: result.message,
                confirmButtonColor: '#00cec9'
            });

            // Actualizar estadísticas tras la baja
            cargarEstadisticas();

            // Fade-out y eliminación de la fila
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
                text: result.message,
                confirmButtonColor: '#ff6b81'
            });
        }
    } catch(err) {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor.',
            confirmButtonColor: '#ff6b81'
        });
    } finally {
        if (btnConfirmar) {
            btnConfirmar.disabled  = false;
            btnConfirmar.innerHTML = '<i class="bi bi-person-dash me-1"></i> Confirmar baja';
        }
    }
}


function abrirModalBaja(btn) {
    document.getElementById('modal-baja-id').value         = btn.getAttribute('data-id');
    document.getElementById('modal-baja-dni').textContent  = btn.getAttribute('data-dni');
    document.getElementById('modal-baja-nombre').textContent = btn.getAttribute('data-nombre');
    document.getElementById('modal-baja-rol').textContent  = btn.getAttribute('data-rol');

    var modal = new bootstrap.Modal(document.getElementById('modalConfirmarBaja'));
    modal.show();
}


