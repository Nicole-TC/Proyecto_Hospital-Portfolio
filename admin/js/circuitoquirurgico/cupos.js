// ────────────────────────────────────────────────────────────────
//  Estado global del formulario
// ────────────────────────────────────────────────────────────────
let modoEdicion = false;
let cuposExistentes = [];

// ────────────────────────────────────────────────────────────────
//  Selección de cards de tipo de turno
// ────────────────────────────────────────────────────────────────
function selectCard(card) {
    document.querySelectorAll('[id^="card-tipo-"]').forEach(c => {
        c.classList.remove('border-primary', 'bg-white', 'shadow-sm');
        c.classList.add('bg-light');
        c.querySelector('input').checked = false;
        const icon = c.querySelector('i');
        if (icon) {
            icon.classList.remove('text-primary');
            icon.classList.add('text-secondary');
        }
    });
    
    card.classList.add('border-primary', 'bg-white', 'shadow-sm');
    card.classList.remove('bg-light');
    card.querySelector('input').checked = true;
    const icon = card.querySelector('i');
    if (icon) {
        icon.classList.remove('text-secondary');
        icon.classList.add('text-primary');
    }
    
    validateDates(); // Validar fechas en vivo al cambiar el tipo de turno
    checkFormValidity();
}

// ────────────────────────────────────────────────────────────────
//  Helpers de validación inline
// ────────────────────────────────────────────────────────────────
function showFieldError(fieldId, msg) {
    const el = document.getElementById('error-' + fieldId);
    if (!el) return;
    el.querySelector('span').textContent = msg;
    el.classList.remove('d-none');
}

function clearFieldError(fieldId) {
    const el = document.getElementById('error-' + fieldId);
    if (!el) return;
    el.querySelector('span').textContent = '';
    el.classList.add('d-none');
}

// ────────────────────────────────────────────────────────────────
//  Validación de fechas (en tiempo real)
// ────────────────────────────────────────────────────────────────
function validateDates() {
    const d = new Date();
    const year  = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day   = String(d.getDate()).padStart(2, '0');
    const today = `${year}-${month}-${day}`;

    const fd = document.getElementById('fecha_desde');
    const fh = document.getElementById('fecha_hasta');
    let valid = true;

    clearFieldError('fecha_desde');
    clearFieldError('fecha_hasta');

    if (fd.value && fd.value < today) {
        fd.value = '';
        showFieldError('fecha_desde', 'No se permiten fechas anteriores a hoy.');
        valid = false;
    } else if (fd.value) {
        // Validar solapamiento con ciclos existentes
        const selectedRadio = document.querySelector('input[name="id_tipo_turno"]:checked');
        const idTipoTurno = selectedRadio ? parseInt(selectedRadio.value) : null;
        
        if (idTipoTurno) {
            const currentEditId = document.getElementById('id_cupo_edit').value;
            const overlapCupo = cuposExistentes.find(cupo => {
                // Excluir el cupo que se está editando
                if (modoEdicion && currentEditId && String(cupo.id_cupo) === String(currentEditId)) {
                    return false;
                }
                // Filtrar por el mismo tipo de turno
                if (parseInt(cupo.id_tipo_turno) !== idTipoTurno) {
                    return false;
                }
                
                const entered = fd.value;
                if (cupo.fecha_hasta) {
                    // Rango cerrado: la fecha ingresada cae dentro de [fecha_desde, fecha_hasta]
                    return (entered >= cupo.fecha_desde && entered <= cupo.fecha_hasta);
                } else {
                    // Rango abierto: la fecha ingresada es menor o igual a la fecha_desde del ciclo abierto
                    return (entered <= cupo.fecha_desde);
                }
            });

            if (overlapCupo) {
                fd.value = '';
                const rangeStr = overlapCupo.fecha_hasta 
                    ? `del ${formatFecha(overlapCupo.fecha_desde)} al ${formatFecha(overlapCupo.fecha_hasta)}`
                    : `desde el ${formatFecha(overlapCupo.fecha_desde)} (sin fecha de fin)`;
                showFieldError('fecha_desde', `La fecha se solapa con un ciclo existente ${rangeStr}.`);
                valid = false;
            }
        }
    }

    if (fh.value && fh.value < today) {
        fh.value = '';
        showFieldError('fecha_hasta', 'No se permiten fechas anteriores a hoy.');
        valid = false;
    } else if (fd.value && fh.value && fh.value < fd.value) {
        fh.value = '';
        showFieldError('fecha_hasta', 'La fecha "Hasta" no puede ser menor a la fecha "Desde".');
        valid = false;
    }

    checkFormValidity();
    return valid;
}

// ────────────────────────────────────────────────────────────────
//  Validación de cupos (en tiempo real)
// ────────────────────────────────────────────────────────────────
function validateCupos() {
    const input  = document.getElementById('cupos_total');
    const val    = parseInt(input.value);
    const errorEl = document.getElementById('error-cupos');
    const textEl  = document.getElementById('text-cupos');

    if (input.value !== '' && (isNaN(val) || val < 1)) {
        input.value = '';
        errorEl.querySelector('span').textContent = 'El valor debe ser un número mayor a 0.';
        errorEl.classList.remove('d-none');
        textEl.classList.add('d-none');
    } else {
        errorEl.querySelector('span').textContent = '';
        errorEl.classList.add('d-none');
        textEl.classList.remove('d-none');
    }

    updateGlobalInfo();
}

// ────────────────────────────────────────────────────────────────
//  Panel informativo global
// ────────────────────────────────────────────────────────────────
function updateGlobalInfo() {
    const hora  = document.getElementById('hora_inicio').value;
    const cupos = document.getElementById('cupos_total').value;
    
    document.getElementById('info-hora').textContent  = hora  ? formatTime(hora)  : '--:--';
    document.getElementById('info-cupos').textContent = cupos ? cupos + ' pacientes' : '-- pacientes';
    
    document.querySelectorAll('.day-row').forEach(row => {
        const cupoSpan = row.querySelector('.status-cupo');
        const horaSpan = row.querySelector('.status-hora');
        if (cupoSpan) cupoSpan.textContent = cupos || '--';
        if (horaSpan) horaSpan.textContent = hora ? formatTime(hora) : '--:--';
    });

    checkFormValidity();
}

function formatTime(timeStr) {
    if (!timeStr) return '--:--';
    let parts = timeStr.split(':');
    let hours = parseInt(parts[0]);
    let ampm  = hours >= 12 ? 'p. m.' : 'a. m.';
    hours = hours % 12;
    hours = hours ? hours : 12;
    return (hours < 10 ? '0' + hours : hours) + ':' + parts[1] + ' ' + ampm;
}

// ────────────────────────────────────────────────────────────────
//  Toggle de días semanales
// ────────────────────────────────────────────────────────────────
function toggleDay(dayKey) {
    const checkbox = document.getElementById(dayKey);
    const row      = document.getElementById('row-' + dayKey);
    const text     = document.getElementById('text-' + dayKey);
    const status   = document.getElementById('status-' + dayKey);
    const badge    = document.getElementById('badge-' + dayKey);
    
    if (checkbox.checked) {
        row.classList.remove('bg-light');
        row.classList.add('bg-white', 'border-primary', 'shadow-sm');
        badge.classList.remove('bg-secondary');
        badge.classList.add('bg-primary');
        text.classList.add('d-none');
        status.classList.remove('d-none');
    } else {
        row.classList.add('bg-light');
        row.classList.remove('bg-white', 'border-primary', 'shadow-sm');
        badge.classList.remove('bg-primary');
        badge.classList.add('bg-secondary');
        text.classList.remove('d-none');
        status.classList.add('d-none');
    }
    
    updateGlobalInfo();
    updateActiveDaysCount();
    checkFormValidity();
}

function updateActiveDaysCount() {
    const count = document.querySelectorAll('input[type="checkbox"]:checked:not([disabled])').length;
    document.getElementById('active-days-count').textContent = count + ' día(s) activo(s)';
}

// ────────────────────────────────────────────────────────────────
//  Habilitación del botón submit
// ────────────────────────────────────────────────────────────────
function checkFormValidity() {
    const form = document.getElementById('form-cupos');
    const btn  = document.getElementById('btn-submit');
    
    const hasActiveDay = document.querySelectorAll('input[type="checkbox"]:checked:not([disabled])').length > 0;
    
    if (form.checkValidity() && hasActiveDay) {
        btn.disabled = false;
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-primary');
    } else {
        btn.disabled = true;
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
    }
}

// ────────────────────────────────────────────────────────────────
//  Reset del formulario (modo creación)
// ────────────────────────────────────────────────────────────────
function resetUI() {
    document.querySelectorAll('[id^="card-tipo-"]').forEach(c => {
        c.classList.remove('border-primary', 'bg-white', 'shadow-sm');
        c.classList.add('bg-light');
        const icon = c.querySelector('i');
        if (icon) {
            icon.classList.remove('text-primary');
            icon.classList.add('text-secondary');
        }
    });

    const days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    days.forEach(day => {
        const checkbox = document.getElementById(day);
        if (checkbox) {
            // Reset visual del switch manualmente
            checkbox.checked = false;
            checkbox.style.backgroundColor    = '#adb5bd';
            checkbox.style.backgroundPosition = 'left center';
            toggleDay(day);
        }
    });
    
    updateGlobalInfo();
    updateActiveDaysCount();
    checkFormValidity();
}

// ────────────────────────────────────────────────────────────────
//  CRUD — Crear / Actualizar
// ────────────────────────────────────────────────────────────────
async function saveConfig() {
    const form = document.getElementById('form-cupos');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const idCupo   = document.getElementById('id_cupo_edit').value;
    const action   = (modoEdicion && idCupo) ? 'actualizar_cupo' : 'guardar_cupos';

    try {
        const response = await fetch('app/cir-api/endpoints/cupos-e.php?action=' + action, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.ok) {
            Swal.fire({
                title: modoEdicion ? '¡Actualizado!' : '¡Guardado!',
                text: result.message || 'La configuración se ha guardado correctamente.',
                icon: 'success',
                confirmButtonColor: '#0d6efd'
            });
            resetFormulario();
            cargarTabla();
            
            // Volver a la pestaña de gestión
            const listTab = document.querySelector('#list-tab');
            if (listTab) {
                bootstrap.Tab.getInstance(listTab)?.show() || new bootstrap.Tab(listTab).show();
            }
        } else {
            Swal.fire({
                title: 'Error',
                text: result.error || 'Hubo un error al guardar.',
                icon: 'error',
                confirmButtonColor: '#0d6efd'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error de conexión con el servidor.',
            icon: 'error',
            confirmButtonColor: '#0d6efd'
        });
    }
}

// ────────────────────────────────────────────────────────────────
//  CRUD — Editar (carga datos en el formulario)
// ────────────────────────────────────────────────────────────────
function editarCupo(cupo) {
    modoEdicion = true;

    // Mostrar alerta de modo edición
    const alertEdicion = document.getElementById('alert-modo-edicion');
    alertEdicion.classList.remove('d-none');
    alertEdicion.classList.add('d-flex');
    document.getElementById('edit-id-label').textContent = '#' + cupo.id_cupo;

    // ID oculto
    document.getElementById('id_cupo_edit').value = cupo.id_cupo;

    // Tipo de turno
    const cardId = 'card-tipo-' + cupo.id_tipo_turno;
    const card = document.getElementById(cardId);
    if (card) selectCard(card);

    // Fechas
    document.getElementById('fecha_desde').value = cupo.fecha_desde || '';
    document.getElementById('fecha_hasta').value = cupo.fecha_hasta || '';

    // Hora y cupos
    document.getElementById('hora_inicio').value = cupo.hora_inicio ? cupo.hora_inicio.substring(0, 5) : '';
    document.getElementById('cupos_total').value = cupo.cupos_total || '';

    // Días
    const mapaDias = { lunes: 'lunes', martes: 'martes', miercoles: 'miercoles', jueves: 'jueves', viernes: 'viernes' };
    Object.keys(mapaDias).forEach(day => {
        const checkbox = document.getElementById(day);
        if (checkbox) {
            const activo = cupo[day] === true || cupo[day] === 't' || cupo[day] === '1';
            checkbox.checked = activo;
            checkbox.style.backgroundColor    = activo ? '#0dcaf0' : '#adb5bd';
            checkbox.style.backgroundPosition = activo ? 'right center' : 'left center';
            toggleDay(day);
        }
    });

    // Cambiar texto del botón
    document.getElementById('btn-submit-label').innerHTML = '<i class="bi bi-floppy me-1"></i>Guardar Cambios';

    updateGlobalInfo();
    checkFormValidity();
    validateDates(); // Validar al cargar datos para edición

    // Cambiar a la pestaña del formulario
    const formTab = document.querySelector('#form-tab');
    if (formTab) {
        bootstrap.Tab.getInstance(formTab)?.show() || new bootstrap.Tab(formTab).show();
    }

    // Scroll al formulario
    document.getElementById('form-cupos').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ────────────────────────────────────────────────────────────────
//  Cancelar edición / Limpiar formulario
// ────────────────────────────────────────────────────────────────
function cancelarEdicion() {
    Swal.fire({
        title: modoEdicion ? '¿Cancelar edición?' : '¿Limpiar formulario?',
        text: 'Se borrará todo el progreso y tendrás que empezar de cero.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Volver'
    }).then((result) => {
        if (result.isConfirmed) {
            resetFormulario();
        }
    });
}

function resetFormulario() {
    modoEdicion = false;

    document.getElementById('form-cupos').reset();
    document.getElementById('id_cupo_edit').value = '';
    
    const alertEdicion = document.getElementById('alert-modo-edicion');
    alertEdicion.classList.remove('d-flex');
    alertEdicion.classList.add('d-none');
    alertEdicion.style.display = 'none'; // Asegurar que se oculte
    
    document.getElementById('btn-submit-label').innerHTML = 'Aplicar Configuración <i class="bi bi-arrow-right ms-1"></i>';

    clearFieldError('fecha_desde');
    clearFieldError('fecha_hasta');
    resetUI();
}

// ────────────────────────────────────────────────────────────────
//  CRUD — Ver Detalles (Modal)
// ────────────────────────────────────────────────────────────────
function verCupo(cupo) {
    document.getElementById('ver-tipo-turno').textContent = cupo.tipo_turno_nombre || '—';
    document.getElementById('ver-fecha-desde').textContent = cupo.fecha_desde ? formatFecha(cupo.fecha_desde) : '—';
    document.getElementById('ver-fecha-hasta').textContent = cupo.fecha_hasta ? formatFecha(cupo.fecha_hasta) : 'Indefinido';
    document.getElementById('ver-hora-inicio').textContent = cupo.hora_inicio ? formatTime(cupo.hora_inicio.substring(0, 5)) : '--:--';
    document.getElementById('ver-cupos-total').textContent = cupo.cupos_total + ' pacientes/día';
    
    document.getElementById('ver-dias-activos').innerHTML = buildDiasBadges(cupo);

    const modalVer = new bootstrap.Modal(document.getElementById('modalVerCupo'));
    modalVer.show();
}

// ────────────────────────────────────────────────────────────────
//  CRUD — Eliminar (borrado lógico)
// ────────────────────────────────────────────────────────────────
async function eliminarCupo(idCupo, descripcion) {
    const confirm = await Swal.fire({
        title: '¿Eliminar cupo?',
        html: `¿Estás segura de que querés eliminar la configuración <strong>${descripcion}</strong>? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirm.isConfirmed) return;

    try {
        const formData = new FormData();
        formData.append('id_cupo', idCupo);

        const response = await fetch('app/cir-api/endpoints/cupos-e.php?action=eliminar_cupo', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.ok) {
            Swal.fire({
                title: 'Eliminado',
                text: result.message || 'El cupo fue eliminado correctamente.',
                icon: 'success',
                confirmButtonColor: '#0d6efd',
                timer: 2000,
                showConfirmButton: false
            });
            cargarTabla();
        } else {
            Swal.fire({
                title: 'Error',
                text: result.error || 'No se pudo eliminar el cupo.',
                icon: 'error',
                confirmButtonColor: '#0d6efd'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error de conexión con el servidor.',
            icon: 'error',
            confirmButtonColor: '#0d6efd'
        });
    }
}

// ────────────────────────────────────────────────────────────────
//  CRUD — Leer / Renderizar tabla
// ────────────────────────────────────────────────────────────────
async function cargarTabla() {
    const loading = document.getElementById('tabla-loading');
    const empty   = document.getElementById('tabla-empty');
    const error   = document.getElementById('tabla-error');
    const wrapper = document.getElementById('tabla-wrapper');
    const tbody   = document.getElementById('tabla-body');

    // Estado inicial: cargando
    loading.classList.remove('d-none');
    empty.classList.add('d-none');
    error.classList.add('d-none');
    wrapper.classList.add('d-none');
    tbody.innerHTML = '';

    try {
        const response = await fetch('app/cir-api/endpoints/cupos-e.php?action=listar_cupos');
        const result   = await response.json();

        loading.classList.add('d-none');

        if (!result.ok) {
            document.getElementById('tabla-error-msg').textContent = result.error || 'Error al cargar los datos.';
            error.classList.remove('d-none');
            return;
        }

        const datos = result.data || [];
        cuposExistentes = datos; // Guardar para validación real-time

        if (datos.length === 0) {
            empty.classList.remove('d-none');
            return;
        }

        // Renderizar filas
        datos.forEach((cupo, index) => {
            const diasBadges = buildDiasBadges(cupo);
            const vigencia   = buildVigencia(cupo);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4 text-muted small">${cupo.id_cupo}</td>
                <td>
                    <span class="badge bg-white text-primary border border-primary fw-semibold px-2 py-1 shadow-sm" style="font-size:12px;">
                        ${escapeHtml(cupo.tipo_turno_nombre || '—')}
                    </span>
                </td>
                <td class="small text-dark">${vigencia}</td>
                <td class="small fw-semibold">${formatTime(cupo.hora_inicio ? cupo.hora_inicio.substring(0,5) : '')}</td>
                <td class="text-center">
                    <span class="badge bg-white text-success border border-success fw-bold px-2 py-1 shadow-sm" style="font-size:13px;">
                        ${cupo.cupos_total} pacientes
                    </span>
                </td>
                <td>${diasBadges}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-info me-1" 
                            onclick='verCupo(${JSON.stringify(cupo)})' 
                            title="Ver Detalles">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary me-1" 
                            onclick='editarCupo(${JSON.stringify(cupo)})' 
                            title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="eliminarCupo(${cupo.id_cupo}, '${escapeHtml(cupo.tipo_turno_nombre || '')} - ${escapeHtml(formatFecha(cupo.fecha_desde))}')" 
                            title="Eliminar">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        wrapper.classList.remove('d-none');

    } catch (err) {
        loading.classList.add('d-none');
        console.error('Error cargando tabla:', err);
        document.getElementById('tabla-error-msg').textContent = 'Error de conexión con el servidor.';
        error.classList.remove('d-none');
    }
}

function buildDiasBadges(cupo) {
    const mapa = [
        { key: 'lunes',     label: 'LUN' },
        { key: 'martes',    label: 'MAR' },
        { key: 'miercoles', label: 'MIÉ' },
        { key: 'jueves',    label: 'JUE' },
        { key: 'viernes',   label: 'VIE' },
    ];

    return mapa.map(d => {
        const activo = cupo[d.key] === true || cupo[d.key] === 't' || cupo[d.key] === '1';
        if (!activo) return ''; // No mostrar días inactivos
        return `<span class="badge bg-primary me-1" style="font-size:10px;">${d.label}</span>`;
    }).join('');
}

function buildVigencia(cupo) {
    const desde = cupo.fecha_desde ? formatFecha(cupo.fecha_desde) : '—';
    const hasta = cupo.fecha_hasta ? formatFecha(cupo.fecha_hasta) : 'Indefinido';
    return `<span class="text-dark">${desde}</span> <span class="text-muted">→</span> <span class="${cupo.fecha_hasta ? 'text-dark' : 'text-muted fst-italic'}">${hasta}</span>`;
}

function formatFecha(fechaStr) {
    if (!fechaStr) return '—';
    const [y, m, d] = fechaStr.split('-');
    return `${d}/${m}/${y}`;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ────────────────────────────────────────────────────────────────
//  Inicialización
// ────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    checkFormValidity();
    cargarTabla();
});
