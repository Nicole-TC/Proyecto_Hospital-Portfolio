/* =========================================================
   CATÁLOGOS MOCK (se reemplazarán por llamadas AJAX reales)
   ========================================================= */

const ESTUDIOS_MOCK = [
    { id: 1, nombre: 'Hemograma Completo', descripcion: 'Análisis de componentes sanguíneos', observaciones: 'Ayuno de 8 horas' },
    { id: 2, nombre: 'Electrocardiograma (ECG)', descripcion: 'Evaluación de la actividad cardíaca', observaciones: 'No requiere preparación especial' },
    { id: 3, nombre: 'Radiografía de Tórax', descripcion: 'Imagen radiológica del tórax', observaciones: 'Quitar objetos metálicos' },
    { id: 4, nombre: 'Coagulograma', descripcion: 'Evaluación de coagulación sanguínea', observaciones: 'Ayuno de 4 horas' },
    { id: 5, nombre: 'Grupo y Factor Sanguíneo', descripcion: 'Determinación del tipo de sangre', observaciones: 'No requiere ayuno' },
    { id: 6, nombre: 'Glucemia en Ayunas', descripcion: 'Medición de glucosa en sangre', observaciones: 'Ayuno de 8 horas estricto' },
    { id: 7, nombre: 'Urea y Creatinina', descripcion: 'Función renal', observaciones: 'Hidratación previa' },
    { id: 8, nombre: 'Ecografía Abdominal', descripcion: 'Imagen ecográfica del abdomen', observaciones: 'Ayuno de 6 horas, vejiga llena' },
];

/* =========================================================
   ESTADO GLOBAL
   ========================================================= */
let estudiosDisponibles = [...ESTUDIOS_MOCK];  // Catálogo completo
let estudiosSeleccionados = new Set();           // IDs marcados
let pasoActual = 1;

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

function validarNombreCirugia() {
    const val = document.getElementById('nombre_cirugia').value.trim();
    if (!val) {
        mostrarFeedback('nombre-cirugia-feedback', false, 'El nombre de la cirugía es obligatorio.');
        return false;
    }
    if (!RGX_NOMBRE_CIR.test(val)) {
        mostrarFeedback('nombre-cirugia-feedback', false, 'Solo mayúsculas, números, acentos y Ñ. Mín. 3 caracteres.');
        return false;
    }
    mostrarFeedback('nombre-cirugia-feedback', true, 'Nombre válido.');
    return true;
}

function validarDescripcion() {
    const val = document.getElementById('descripcion_cirugia').value;
    if (val.length > 500) {
        mostrarFeedback('descripcion-feedback', false, 'Máximo 500 caracteres.');
        return false;
    }
    mostrarFeedback('descripcion-feedback', '', '');
    return true;
}

function validarFormularioPaso1() {
    const r1 = validarNombreCirugia();
    const r2 = validarDescripcion();
    return r1 && r2;
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
    const nombreCir = document.getElementById('nombre_cirugia').value.trim();
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
    const nombreCir = document.getElementById('nombre_cirugia').value.trim();

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
    // Verificar duplicados
    const existe = estudiosDisponibles.some(e => e.nombre.toLowerCase() === val.toLowerCase());
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

    const nombre = document.getElementById('nuevo-nombre-estudio').value.trim();
    const desc = document.getElementById('nuevo-descripcion-estudio').value.trim();
    const obs = document.getElementById('nuevo-obs-estudio').value.trim();

    const nuevoId = Date.now(); // ID temporal hasta guardar en backend
    const nuevoEstudio = { id: nuevoId, nombre, descripcion: desc, observaciones: obs, esNuevo: true };

    estudiosDisponibles.push(nuevoEstudio);
    estudiosSeleccionados.add(nuevoId);

    cerrarPanelNuevoEstudio();
    renderizarEstudios(document.getElementById('buscador-estudios').value);
    actualizarBadgeYBoton();
}

/* =========================================================
   GUARDAR (submit final)
   ========================================================= */
function guardarVinculacion() {
    if (estudiosSeleccionados.size === 0) return;

    const payload = {
        nombre_cirugia: document.getElementById('nombre_cirugia').value.trim(),
        descripcion: document.getElementById('descripcion_cirugia').value.trim(),
        estudios: [...estudiosSeleccionados],
        estudios_nuevos: estudiosDisponibles.filter(e => e.esNuevo && estudiosSeleccionados.has(e.id)),
    };

    // TODO: reemplazar console.log por fetch POST a la API
    console.log('[form_cirugias] Payload a enviar:', payload);

    Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        html: `Se vincularon <strong>${estudiosSeleccionados.size}</strong> estudios a <strong>${escapeHtml(payload.nombre_cirugia)}</strong>.`,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#00b894',
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

    // Lista estudios
    const lista = document.getElementById('lista-estudios');
    if (lista) lista.style.borderColor = '#444';
}

/* =========================================================
   INICIALIZACIÓN
   ========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    aplicarSanitizacion();
    renderizarEstudios();
    aplicarAjustesModoOscuro();

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
