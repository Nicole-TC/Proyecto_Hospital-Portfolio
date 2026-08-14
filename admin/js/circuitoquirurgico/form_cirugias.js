/* =========================================================
   ESTADO GLOBAL
   ========================================================= */
let estudiosDisponibles = [];  // Catálogo completo desde API
let estudiosSeleccionados = new Set();           // IDs marcados
let pasoActual = 1;
let creandoNuevaCirugia = false;

let nombreCirugiaValido = false;
let timeoutValidacionNombre = null;

/* =========================================================
   UTILIDADES
   ========================================================= */
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function sinAcentos(str) {
    return String(str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function mostrarFeedback(idDiv, esValido, mensaje) {
    const el = document.getElementById(idDiv);
    if (!el) return;
    if (mensaje === '') { el.innerHTML = ''; return; }
    const color = esValido ? '#00b894' : '#e17055';
    const icono = esValido ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
    el.innerHTML = `<span style="color:${color};"><i class="bi ${icono} me-1"></i>${escapeHtml(mensaje)}</span>`;
}

/* =========================================================
   VALIDACIONES – PASO 1
   ========================================================= */
const RGX_NOMBRE_CIR = /^[A-ZÁÉÍÓÚÜÑ0-9\s\-\/\(\)\.]{3,150}$/;

function validarSelectCirugia() {
    const val = document.getElementById('select_cirugia_existente').value;
    if (!val) {
        mostrarFeedback('select-cirugia-feedback', false, 'Debe seleccionar una cirugía existente.');
        return false;
    }
    mostrarFeedback('select-cirugia-feedback', '', '');
    return true;
}

function validarNombreCirugia() {
    if (!creandoNuevaCirugia) return true;
    const val = document.getElementById('nombre_cirugia').value.trim();
    if (!val) {
        mostrarFeedback('nombre-cirugia-feedback', false, 'El nombre de la cirugía es obligatorio.');
        nombreCirugiaValido = false;
        return false;
    }
    if (!RGX_NOMBRE_CIR.test(val)) {
        mostrarFeedback('nombre-cirugia-feedback', false, 'Solo mayúsculas, números, acentos y Ñ. Mín. 3 caracteres.');
        nombreCirugiaValido = false;
        return false;
    }
    
    mostrarFeedback('nombre-cirugia-feedback', true, 'Validando disponibilidad...');
    nombreCirugiaValido = false;

    clearTimeout(timeoutValidacionNombre);
    timeoutValidacionNombre = setTimeout(() => {
        fetch(`app/cir-api/endpoints/cirugias-e.php?accion=validar_nombre_cirugia&nombre=${encodeURIComponent(val)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    mostrarFeedback('nombre-cirugia-feedback', true, 'Nombre disponible.');
                    nombreCirugiaValido = true;
                } else {
                    mostrarFeedback('nombre-cirugia-feedback', false, data.message);
                    nombreCirugiaValido = false;
                }
            })
            .catch(() => {
                mostrarFeedback('nombre-cirugia-feedback', false, 'Error al validar nombre.');
                nombreCirugiaValido = false;
            });
    }, 500);

    return true;
}

function validarDescripcion() {
    if (!creandoNuevaCirugia) return true;
    const val = document.getElementById('descripcion_cirugia').value;
    if (val.length > 500) {
        mostrarFeedback('descripcion-feedback', false, 'Máximo 500 caracteres.');
        return false;
    }
    mostrarFeedback('descripcion-feedback', '', '');
    return true;
}

function validarFormularioPaso1() {
    if (!creandoNuevaCirugia) {
        return validarSelectCirugia();
    }

    const r2 = validarDescripcion();
    
    if (!nombreCirugiaValido) {
        const val = document.getElementById('nombre_cirugia').value.trim();
        if (val && RGX_NOMBRE_CIR.test(val)) {
             mostrarFeedback('nombre-cirugia-feedback', false, 'Nombre no disponible o validación pendiente.');
        } else {
             validarNombreCirugia();
        }
        return false;
    }

    return r2;
}

/* =========================================================
   SANITIZACIÓN EN TIEMPO REAL
   ========================================================= */
function sanitizarMayusculas(e) {
    const start = e.target.selectionStart;
    const end = e.target.selectionEnd;
    e.target.value = e.target.value.toUpperCase();
    e.target.setSelectionRange(start, end);
}

function aplicarSanitizacion() {
    const elNombre = document.getElementById('nombre_cirugia');
    const elNuevoEstudio = document.getElementById('nuevo-nombre-estudio');

    ['input', 'paste'].forEach(ev => {
        elNombre.addEventListener(ev, (e) => {
            if (ev === 'paste') {
                e.preventDefault();
                const texto = (e.clipboardData || window.clipboardData).getData('text');
                const sanitizado = texto.toUpperCase().replace(/[^A-ZÁÉÍÓÚÜÑ0-9\s\-\/\(\)\.]/g, '');
                document.execCommand('insertText', false, sanitizado);
            } else {
                sanitizarMayusculas(e);
            }
            validarNombreCirugia();
        });

        if (elNuevoEstudio) {
            elNuevoEstudio.addEventListener(ev, (e) => {
                if (ev === 'paste') {
                    e.preventDefault();
                    const texto = (e.clipboardData || window.clipboardData).getData('text');
                    const sanitizado = texto.toUpperCase().replace(/[^A-ZÁÉÍÓÚÜÑ0-9\s\-\/\(\)\.]/g, '');
                    document.execCommand('insertText', false, sanitizado);
                } else {
                    sanitizarMayusculas(e);
                }
                validarNuevoNombreEstudio();
            });
        }
    });

    // Contadores de caracteres
    document.getElementById('descripcion_cirugia').addEventListener('input', function () {
        document.getElementById('contador-desc').textContent = this.value.length;
        validarDescripcion();
    });
}

/* =========================================================
   NAVEGACIÓN ENTRE PASOS
   ========================================================= */
function obtenerNombreCirugiaSeleccionada() {
    if (creandoNuevaCirugia) {
        return document.getElementById('nombre_cirugia').value.trim();
    } else {
        const sel = document.getElementById('select_cirugia_existente');
        return sel.options[sel.selectedIndex]?.text || '';
    }
}

function irAPaso2() {
    if (!validarFormularioPaso1()) {
        // Scroll al primer campo con error
        const primerError = document.querySelector('[id$="-feedback"] span');
        if (primerError) primerError.closest('.mb-3, .col-md-12')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    pasoActual = 2;

    // Actualizar indicadores visuales
    document.getElementById('circulo-paso1').style.background = '#00b894';
    document.getElementById('circulo-paso1').innerHTML = '<i class="bi bi-check-lg" style="font-size:0.9rem;"></i>';
    document.getElementById('txt-paso1').style.color = '#00b894';
    document.getElementById('sep-pasos').style.background = '#00b894';
    document.getElementById('circulo-paso2').style.background = '#00cec9';
    document.getElementById('circulo-paso2').style.color = '#fff';
    document.getElementById('txt-paso2').style.color = '#00cec9';

    // Mostrar nombre de cirugía en paso 2
    const nombreCir = obtenerNombreCirugiaSeleccionada();
    document.getElementById('nombre-cirugia-display').textContent = nombreCir;

    document.getElementById('seccion-paso1').style.display = 'none';
    document.getElementById('seccion-paso2').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function volverAPaso1() {
    pasoActual = 1;
    document.getElementById('circulo-paso1').style.background = '#00cec9';
    document.getElementById('circulo-paso1').innerHTML = '1';
    document.getElementById('txt-paso1').style.color = '#00cec9';
    document.getElementById('sep-pasos').style.background = '#dee2e6';
    document.getElementById('circulo-paso2').style.background = '#dee2e6';
    document.getElementById('circulo-paso2').style.color = '#6c757d';
    document.getElementById('txt-paso2').style.color = '#6c757d';

    document.getElementById('seccion-paso2').style.display = 'none';
    document.getElementById('seccion-paso1').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* =========================================================
   LISTA DE ESTUDIOS (PASO 2)
   ========================================================= */
function renderizarEstudios(filtro = '') {
    const contenedor = document.getElementById('lista-estudios');
    const msgSin = document.getElementById('msg-sin-estudios');
    const filtroLow = filtro.toLowerCase().trim();

    const visibles = estudiosDisponibles.filter(e =>
        e.nombre.toLowerCase().includes(filtroLow) ||
        e.descripcion.toLowerCase().includes(filtroLow)
    );

    // Limpiar items anteriores (no el msg-sin-estudios)
    [...contenedor.querySelectorAll('.item-estudio')].forEach(el => el.remove());

    if (visibles.length === 0) {
        msgSin.style.display = 'block';
        return;
    }
    msgSin.style.display = 'none';

    visibles.forEach(est => {
        const seleccionado = estudiosSeleccionados.has(est.id);
        const div = document.createElement('div');
        div.dataset.id = est.id;

        // Estilos inline completos sin clase Bootstrap para evitar
        // el float:left / margin-left negativo de .form-check-input
        div.style.cssText = `
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.15s;
            background: ${seleccionado ? '#f0f9ff' : 'transparent'};
        `;
        div.className = 'item-estudio';

        // Checkbox: SIN form-check-input para que no use float de Bootstrap
        const chkWrapper = document.createElement('div');
        chkWrapper.style.cssText = 'flex-shrink:0; padding-top:2px;';

        const chk = document.createElement('input');
        chk.type = 'checkbox';
        chk.id = `estudio-${est.id}`;
        chk.dataset.id = est.id;
        chk.className = 'estudio-checkbox';
        chk.checked = seleccionado;
        // Estilos inline: posición normal en el flujo, sin float
        chk.style.cssText = `
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #0984e3;
            flex-shrink: 0;
            display: block;
            margin: 0;
            float: none;
        `;
        chkWrapper.appendChild(chk);

        // Contenido textual
        const label = document.createElement('label');
        label.htmlFor = `estudio-${est.id}`;
        label.style.cssText = 'cursor:pointer; flex:1; margin:0; display:flex; flex-direction:column; gap:2px;';

        const spanNombre = document.createElement('span');
        spanNombre.style.cssText = `font-size:0.93rem; font-weight:600; color:${seleccionado ? '#0984e3' : 'inherit'};`;
        spanNombre.textContent = est.nombre;

        const spanDesc = document.createElement('span');
        spanDesc.className = 'text-secondary';
        spanDesc.style.fontSize = '0.82rem';
        spanDesc.textContent = est.descripcion;

        const spanReq = document.createElement('span');
        spanReq.style.fontSize = '0.8rem';
        spanReq.innerHTML = `<strong>Requisitos:</strong> <span class="text-secondary">${escapeHtml(est.observaciones)}</span>`;

        label.appendChild(spanNombre);
        label.appendChild(spanDesc);
        label.appendChild(spanReq);

        div.appendChild(chkWrapper);
        div.appendChild(label);

        // Eventos
        div.addEventListener('click', (e) => {
            if (e.target.tagName === 'INPUT') return;
            chk.checked = !chk.checked;
            toggleEstudio(est.id, chk.checked, div, spanNombre);
        });

        chk.addEventListener('change', () => {
            toggleEstudio(est.id, chk.checked, div, spanNombre);
        });

        contenedor.insertBefore(div, msgSin);
    });
}

function toggleEstudio(id, seleccionado, divEl, spanNombre) {
    if (seleccionado) {
        estudiosSeleccionados.add(id);
        divEl.style.background = '#f0f9ff';
        if (spanNombre) spanNombre.style.color = '#0984e3';
    } else {
        estudiosSeleccionados.delete(id);
        divEl.style.background = 'transparent';
        if (spanNombre) spanNombre.style.color = '';
    }
    actualizarBadgeYBoton();
}

function actualizarBadgeYBoton() {
    const count = estudiosSeleccionados.size;
    const badge = document.getElementById('badge-seleccionados');
    const btnGuardar = document.getElementById('btn-guardar-vinculacion');
    const hint = document.getElementById('txt-guardar-hint');
    const nombreCir = obtenerNombreCirugiaSeleccionada();

    badge.style.display = count > 0 ? 'inline-block' : 'none';
    badge.textContent = `${count} seleccionado${count !== 1 ? 's' : ''}`;

    if (count > 0) {
        btnGuardar.disabled = false;
        btnGuardar.style.opacity = '1';
        btnGuardar.style.cursor = 'pointer';
        hint.textContent = `Se vincularán ${count} estudio${count !== 1 ? 's' : ''} a ${escapeHtml(nombreCir)}`;
    } else {
        btnGuardar.disabled = true;
        btnGuardar.style.opacity = '0.6';
        btnGuardar.style.cursor = 'not-allowed';
        hint.textContent = 'Seleccione al menos 1 estudio para continuar.';
    }
}

/* =========================================================
   PANEL: NUEVO ESTUDIO PREQUIRÚRGICO
   ========================================================= */
function validarNuevoNombreEstudio() {
    const val = document.getElementById('nuevo-nombre-estudio').value.trim();
    if (!val) {
        mostrarFeedback('nuevo-nombre-feedback', false, 'El nombre del estudio es obligatorio.');
        return false;
    }
    if (!RGX_NOMBRE_CIR.test(val)) {
        mostrarFeedback('nuevo-nombre-feedback', false, 'Solo mayúsculas, números, acentos y Ñ. Mín. 3 caracteres.');
        return false;
    }
    // Verificar duplicados (ignorando acentos y mayúsculas)
    const existe = estudiosDisponibles.some(e => sinAcentos(e.nombre) === sinAcentos(val));
    if (existe) {
        mostrarFeedback('nuevo-nombre-feedback', false, 'Ya existe un estudio con ese nombre.');
        return false;
    }
    mostrarFeedback('nuevo-nombre-feedback', true, 'Nombre disponible.');
    return true;
}

function validarNuevoObsEstudio() {
    const val = document.getElementById('nuevo-obs-estudio').value;
    if (val.length > 300) {
        mostrarFeedback('nuevo-obs-feedback', false, 'Máximo 300 caracteres.');
        return false;
    }
    mostrarFeedback('nuevo-obs-feedback', '', '');
    return true;
}

function abrirPanelNuevoEstudio() {
    document.getElementById('panel-nuevo-estudio').style.display = 'block';
    document.getElementById('btn-abrir-nuevo-estudio').style.display = 'none';
    document.getElementById('nuevo-nombre-estudio').focus();
}

function cerrarPanelNuevoEstudio() {
    document.getElementById('panel-nuevo-estudio').style.display = 'none';
    document.getElementById('btn-abrir-nuevo-estudio').style.display = 'flex';
    limpiarFormularioNuevoEstudio();
}

function limpiarFormularioNuevoEstudio() {
    document.getElementById('formNuevoEstudio').reset();
    ['nuevo-nombre-feedback', 'nuevo-obs-feedback'].forEach(id => mostrarFeedback(id, true, ''));
    document.getElementById('contador-nuevo-desc').textContent = '0';
    document.getElementById('contador-nuevo-obs').textContent = '0';
}

function agregarNuevoEstudio() {
    const okNombre = validarNuevoNombreEstudio();
    const okObs = validarNuevoObsEstudio();
    if (!okNombre || !okObs) return;

    const btnBtn = document.getElementById('btn-agregar-nuevo-estudio');
    const origText = btnBtn.innerHTML;
    btnBtn.disabled = true;
    btnBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Guardando...';

    const nombre = document.getElementById('nuevo-nombre-estudio').value.trim();
    const desc = document.getElementById('nuevo-descripcion-estudio').value.trim();
    const obs = document.getElementById('nuevo-obs-estudio').value.trim();

    fetch('app/cir-api/endpoints/cirugias-e.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            accion: 'crear_estudio',
            nombre: nombre,
            descripcion: desc,
            observaciones: obs
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const nuevoEstudio = data.estudio;
            estudiosDisponibles.push(nuevoEstudio);
            estudiosSeleccionados.add(nuevoEstudio.id);

            cerrarPanelNuevoEstudio();
            renderizarEstudios(document.getElementById('buscador-estudios').value);
            actualizarBadgeYBoton();

            Swal.fire({
                icon: 'success',
                title: 'Estudio agregado',
                text: 'El estudio se guardó en el catálogo y fue seleccionado automáticamente.',
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo crear el estudio.',
                confirmButtonColor: '#ff6b81'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error de red',
            text: 'Ocurrió un error al procesar la solicitud. Intente nuevamente.',
            confirmButtonColor: '#ff6b81'
        });
    })
    .finally(() => {
        btnBtn.disabled = false;
        btnBtn.innerHTML = origText;
    });
}

/* =========================================================
   GUARDAR (submit final)
   ========================================================= */
function guardarVinculacion() {
    if (estudiosSeleccionados.size === 0) return;

    const payload = {
        accion: 'crear',
        estudios: [...estudiosSeleccionados]
    };
    if (creandoNuevaCirugia) {
        payload.nombre_cirugia = document.getElementById('nombre_cirugia').value.trim();
        payload.descripcion = document.getElementById('descripcion_cirugia').value.trim();
    } else {
        payload.id_cirugia = document.getElementById('select_cirugia_existente').value;
    }

    const btnGuardar = document.getElementById('btn-guardar-vinculacion');
    const originalText = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Guardando...';

    fetch('app/cir-api/endpoints/cirugias-e.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                html: `Se vinculó correctamente la cirugía <strong>${escapeHtml(payload.nombre_cirugia)}</strong>.`,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#00cec9',
            }).then(() => {
                window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=gestion_cirugias';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al guardar la cirugía',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#ff6b81'
            });
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error de red',
            text: 'Ocurrió un error al procesar la solicitud. Intente nuevamente.',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#ff6b81'
        });
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalText;
    });
}

/* =========================================================
   MODO OSCURO: ajustes de estilos específicos del formulario
   ========================================================= */
function aplicarAjustesModoOscuro() {
    if (!document.body.classList.contains('modo-oscuro')) return;

    // Panel nuevo estudio
    const panelHeader = document.querySelector('#panel-nuevo-estudio > div:first-child');
    if (panelHeader) panelHeader.style.background = '#1a2a3a';

    const panelBody = document.querySelector('#panel-nuevo-estudio .p-3');
    if (panelBody) panelBody.style.background = '#2a2a2a';

    // Panel nueva cirugía
    const panelCirHeader = document.querySelector('#panel-nueva-cirugia > div:first-child');
    if (panelCirHeader) panelCirHeader.style.background = '#1a2a3a';

    const panelCirBody = document.querySelector('#panel-nueva-cirugia .p-3');
    if (panelCirBody) panelCirBody.style.background = '#2a2a2a';

    // Lista estudios
    const lista = document.getElementById('lista-estudios');
    if (lista) lista.style.borderColor = '#444';
}

/* =========================================================
   INICIALIZACIÓN Y CARGA DE DATOS
   ========================================================= */
function cargarEstudios() {
    fetch('app/cir-api/endpoints/cirugias-e.php?accion=listar_estudios')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                estudiosDisponibles = data.estudios || [];
                renderizarEstudios();
            } else {
                console.error("Error al cargar estudios:", data.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar',
                    text: 'No se pudieron cargar los estudios disponibles.',
                    confirmButtonColor: '#ff6b81'
                });
            }
        })
        .catch(err => {
            console.error("Error de red al cargar estudios:", err);
        });
}

/* =========================================================
   NUEVA CIRUGÍA (PASO 1)
   ========================================================= */
function abrirPanelNuevaCirugia() {
    creandoNuevaCirugia = true;
    document.getElementById('panel-nueva-cirugia').style.display = 'block';
    document.getElementById('btn-abrir-nueva-cirugia').style.display = 'none';
    document.getElementById('seccion-select-cirugia').style.display = 'none';
    
    document.getElementById('nombre_cirugia').value = '';
    document.getElementById('descripcion_cirugia').value = '';
    document.getElementById('contador-desc').textContent = '0';
    mostrarFeedback('nombre-cirugia-feedback', '', '');
    mostrarFeedback('descripcion-feedback', '', '');
    mostrarFeedback('select-cirugia-feedback', '', '');
    nombreCirugiaValido = false;
    document.getElementById('nombre_cirugia').focus();
}

function cerrarPanelNuevaCirugia() {
    creandoNuevaCirugia = false;
    document.getElementById('panel-nueva-cirugia').style.display = 'none';
    document.getElementById('btn-abrir-nueva-cirugia').style.display = 'flex';
    document.getElementById('seccion-select-cirugia').style.display = 'block';
    mostrarFeedback('nombre-cirugia-feedback', '', '');
    mostrarFeedback('descripcion-feedback', '', '');
}

function cargarCirugiasSelect() {
    fetch('app/cir-api/endpoints/cirugias-e.php?accion=listar_cirugias_sin_combo')
        .then(res => res.json())
        .then(data => {
            const sel = document.getElementById('select_cirugia_existente');
            sel.innerHTML = '<option value="">-- Seleccione una Cirugía --</option>';
            if (data.status === 'success' && data.cirugias && data.cirugias.length > 0) {
                data.cirugias.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.nombre;
                    sel.appendChild(opt);
                });
            } else {
                sel.innerHTML = '<option value="">No hay cirugías sin combo disponibles</option>';
            }
        })
        .catch(err => {
            console.error(err);
            const sel = document.getElementById('select_cirugia_existente');
            sel.innerHTML = '<option value="">Error al cargar</option>';
        });
}

document.addEventListener('DOMContentLoaded', function () {

    aplicarSanitizacion();
    cargarEstudios();
    cargarCirugiasSelect();
    aplicarAjustesModoOscuro();

    // Nueva Cirugía (Paso 1)
    document.getElementById('btn-abrir-nueva-cirugia').addEventListener('click', abrirPanelNuevaCirugia);
    document.getElementById('btn-cerrar-nueva-cirugia').addEventListener('click', cerrarPanelNuevaCirugia);
    document.getElementById('select_cirugia_existente').addEventListener('change', validarSelectCirugia);

    const btnNuevaCir = document.getElementById('btn-abrir-nueva-cirugia');
    if (btnNuevaCir) {
        btnNuevaCir.addEventListener('mouseenter', () => { btnNuevaCir.style.background = '#e0f7fa'; });
        btnNuevaCir.addEventListener('mouseleave', () => { btnNuevaCir.style.background = 'transparent'; });
    }

    // Botón: continuar a paso 2
    document.getElementById('btn-paso2').addEventListener('click', irAPaso2);

    // Botón: volver a paso 1
    document.getElementById('btn-volver-paso1').addEventListener('click', volverAPaso1);

    // Buscador de estudios
    document.getElementById('buscador-estudios').addEventListener('input', function () {
        renderizarEstudios(this.value);
    });

    // Panel nuevo estudio
    document.getElementById('btn-abrir-nuevo-estudio').addEventListener('click', abrirPanelNuevoEstudio);
    document.getElementById('btn-cerrar-nuevo-estudio').addEventListener('click', cerrarPanelNuevoEstudio);
    document.getElementById('btn-cancelar-nuevo-estudio').addEventListener('click', cerrarPanelNuevoEstudio);
    document.getElementById('btn-agregar-nuevo-estudio').addEventListener('click', agregarNuevoEstudio);

    // Guardar vinculación
    document.getElementById('btn-guardar-vinculacion').addEventListener('click', guardarVinculacion);

    // Contadores formulario nuevo estudio
    document.getElementById('nuevo-descripcion-estudio').addEventListener('input', function () {
        document.getElementById('contador-nuevo-desc').textContent = this.value.length;
    });
    document.getElementById('nuevo-obs-estudio').addEventListener('input', function () {
        document.getElementById('contador-nuevo-obs').textContent = this.value.length;
        validarNuevoObsEstudio();
    });
    document.getElementById('nuevo-nombre-estudio').addEventListener('input', validarNuevoNombreEstudio);

    // Hover en botón "Agregar Nuevo Estudio"
    const btnAgregar = document.getElementById('btn-abrir-nuevo-estudio');
    btnAgregar.addEventListener('mouseenter', () => {
        btnAgregar.style.background = '#eaf4ff';
    });
    btnAgregar.addEventListener('mouseleave', () => {
        btnAgregar.style.background = 'transparent';
    });
});
