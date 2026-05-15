// Ejecutar al estar listo el DOM (parcial PHP — usar readyState)
function initGestion() {
    cargarUsuariosAPI();
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGestion);
} else {
    initGestion();
}

// ============================================================
// CARGAR TABLA DE USUARIOS
// ============================================================
function cargarUsuariosAPI() {
    var tbody = document.getElementById('tablaUsuariosBody');
    if (!tbody) return;

    fetch('app/cir-api/endpoints/listar_users-e.php', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        tbody.innerHTML = '';
        if (data && data.usuarios && data.usuarios.length > 0) {
            data.usuarios.forEach(function(u) {
                var tr = document.createElement('tr');
                tr.setAttribute('data-id', u.id_persona);
                var rolNombre    = u.rol_nombre  || 'Sin rol';
                var nombreCompleto = (u.nombre || '') + ' ' + (u.apellido || '');

                // Escapar comillas para data-attributes
                var safe = function(s) { return String(s).replace(/"/g, '&quot;'); };

                tr.innerHTML =
                    '<td class="ps-4 py-3 fw-medium">' + nombreCompleto + '</td>' +
                    '<td class="py-3">' + (u.email || 'Sin email') + '</td>' +
                    '<td class="py-3"><span class="badge bg-light-blue blue rounded-pill px-2 py-1 fw-normal">' + rolNombre + '</span></td>' +
                    '<td class="py-3"><span class="badge bg-light-success success rounded-pill px-2 py-1 fw-normal"><i class="fa-solid fa-circle" style="font-size:0.5rem;vertical-align:middle;"></i> Registrado</span></td>' +
                    '<td class="py-3 text-secondary">Hoy</td>' +
                    '<td class="py-3 pe-4 text-center">' +
                        '<button class="btn btn-sm btn-link blue me-2" title="Editar usuario" onclick="editarUsuario(' + u.id_persona + ')"><i class="bi bi-pencil-square"></i></button>' +
                        '<button class="btn btn-sm btn-link danger" title="Dar de baja usuario"' +
                            ' data-id="'     + u.id_persona + '"' +
                            ' data-dni="'    + safe(u.dni) + '"' +
                            ' data-nombre="' + safe(nombreCompleto.trim()) + '"' +
                            ' data-rol="'    + safe(rolNombre) + '"' +
                            ' onclick="abrirModalBaja(this)"><i class="bi bi-trash"></i></button>' +
                    '</td>';
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-secondary">No hay usuarios registrados todavía.</td></tr>';
        }
    })
    .catch(function(err) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error al conectar con el servicio.</td></tr>';
    });
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


