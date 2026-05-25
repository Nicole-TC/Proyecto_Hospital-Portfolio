function setFieldError(inputId, feedbackId, msg) {
    var el = document.getElementById(inputId);
    var fb = document.getElementById(feedbackId);
    if (el) { el.classList.remove('is-valid'); el.classList.add('is-invalid'); }
    if (fb) fb.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>' + msg + '</span>';
}
function setFieldOk(inputId, feedbackId) {
    var el = document.getElementById(inputId);
    var fb = document.getElementById(feedbackId);
    if (el) { el.classList.remove('is-invalid'); el.classList.add('is-valid'); }
    if (fb) fb.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Correcto</span>';
}
function clearField(inputId, feedbackId) {
    var el = document.getElementById(inputId);
    var fb = document.getElementById(feedbackId);
    if (el) { el.classList.remove('is-invalid', 'is-valid'); }
    if (fb) fb.innerHTML = '';
}

var dniVerificado = false;
var camposFieldset = null;
var btnGuardar = null;
var dniInput = null;
var icoDni = null;
var dniTimer = null;

function bloquearFormulario() {
    dniVerificado = false;
    if (camposFieldset) camposFieldset.disabled = true;
    if (btnGuardar) btnGuardar.disabled = true;
    mostrarAviso('bloqueo');
}
function desbloquearFormulario() {
    dniVerificado = true;
    if (camposFieldset) camposFieldset.disabled = false;
    if (btnGuardar) btnGuardar.disabled = false;
    mostrarAviso('ok');
}
function mostrarAviso(tipo) {
    var avisoBloqueo = document.getElementById('dni-aviso-bloqueo');
    var avisoOk = document.getElementById('dni-aviso-ok');
    var avisoExiste = document.getElementById('dni-aviso-existe');
    [avisoBloqueo, avisoOk, avisoExiste].forEach(function (a) {
        if (a) a.style.setProperty('display', 'none', 'important');
    });
    if (tipo === 'bloqueo' && avisoBloqueo) avisoBloqueo.style.setProperty('display', 'flex', 'important');
    if (tipo === 'ok' && avisoOk) avisoOk.style.setProperty('display', 'flex', 'important');
    if (tipo === 'existe' && avisoExiste) avisoExiste.style.setProperty('display', 'flex', 'important');
}

function bindDni() {
    dniInput = document.getElementById('dni');
    icoDni = document.getElementById('ico-dni');
    if (!dniInput) return;

    dniInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        clearField('dni', 'dni-feedback');

        if (this.value.length === 0) {
            bloquearFormulario();
            if (icoDni) icoDni.className = 'bi bi-search text-secondary';
            return;
        }
        if (this.value.length < 6) {
            setFieldError('dni', 'dni-feedback', 'Mínimo 6 dígitos.');
            if (icoDni) icoDni.className = 'bi bi-x-circle text-danger';
            bloquearFormulario();
            return;
        }
        if (icoDni) icoDni.className = 'bi bi-hourglass-split text-warning';
        clearTimeout(dniTimer);
        dniTimer = setTimeout(function () { verificarDNI(dniInput.value); }, 600);
    });

    dniInput.addEventListener('blur', function () {
        clearTimeout(dniTimer);
        var val = this.value;
        if (val.length === 0) return;
        if (val.length < 6 || val.length > 8) {
            setFieldError('dni', 'dni-feedback', 'El DNI debe tener entre 6 y 8 dígitos.');
            if (icoDni) icoDni.className = 'bi bi-x-circle text-danger';
            bloquearFormulario();
            return;
        }
        verificarDNI(val);
    });

    dniInput.addEventListener('paste', function (e) {
        e.preventDefault();
        var texto = (e.clipboardData || window.clipboardData).getData('text');
        this.value = texto.replace(/[^0-9]/g, '').slice(0, 8);
        this.dispatchEvent(new Event('input'));
    });
}

async function verificarDNI(val) {
    if (val.length < 6 || val.length > 8) {
        setFieldError('dni', 'dni-feedback', 'El DNI debe tener entre 6 y 8 dígitos.');
        bloquearFormulario();
        if (icoDni) icoDni.className = 'bi bi-x-circle text-danger';
        return;
    }
    if (icoDni) icoDni.className = 'bi bi-hourglass-split text-warning';
    try {
        var response = await fetch('app/cir-api/endpoints/usuarios-e.php?accion=verificar_dni&dni=' + val);
        var result = await response.json();
        if (result.status === 'success') {
            if (result.existe === true) {
                setFieldError('dni', 'dni-feedback', 'Este DNI ya está registrado en el sistema.');
                if (icoDni) icoDni.className = 'bi bi-x-circle-fill text-danger';
                bloquearFormulario();
                mostrarAviso('existe');
            } else {
                var fb = document.getElementById('dni-feedback');
                if (fb) fb.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>DNI disponible</span>';
                var el = document.getElementById('dni');
                if (el) { el.classList.remove('is-invalid'); el.classList.add('is-valid'); }
                if (icoDni) icoDni.className = 'bi bi-check-circle-fill text-success';
                desbloquearFormulario();
            }
        } else {
            setFieldError('dni', 'dni-feedback', result.message || 'Error al verificar el DNI.');
            bloquearFormulario();
            if (icoDni) icoDni.className = 'bi bi-exclamation-triangle text-warning';
        }
    } catch (err) {
        setFieldError('dni', 'dni-feedback', 'No se pudo verificar el DNI. Revisá tu conexión.');
        if (icoDni) icoDni.className = 'bi bi-wifi-off text-danger';
    }
}

var LETRAS_OK = 'A-Z\u00C1\u00C9\u00CD\u00D3\u00DA\u00DC\u00D1';
var RE_NOMBRE_VALIDO = new RegExp('^[' + LETRAS_OK + ']+( [' + LETRAS_OK + ']+)*$');
var RE_CHAR_INVALIDO = new RegExp('[^' + LETRAS_OK + ' ]', 'g');

function validarNombreApellido(inputId, feedbackId, etiqueta) {
    var el = document.getElementById(inputId);
    if (!el) return true;
    var val = el.value;

    if (val.trim().length === 0) {
        setFieldError(inputId, feedbackId, 'El campo ' + etiqueta + ' es obligatorio.');
        return false;
    }
    if (val.trim().length < 2) {
        setFieldError(inputId, feedbackId, 'Mínimo 2 caracteres.');
        return false;
    }
    if (/\s{2,}/.test(val)) {
        setFieldError(inputId, feedbackId, 'No se permiten múltiples espacios seguidos.');
        return false;
    }
    if (val.startsWith(' ') || val.endsWith(' ')) {
        setFieldError(inputId, feedbackId, 'No puede empezar ni terminar con espacio.');
        return false;
    }
    if (!RE_NOMBRE_VALIDO.test(val)) {
        setFieldError(inputId, feedbackId, 'Solo letras mayúsculas, acentos y Ñ. Sin números ni símbolos.');
        return false;
    }
    setFieldOk(inputId, feedbackId);
    return true;
}

function bindNombreApellido(inputId, feedbackId, etiqueta) {
    var el = document.getElementById(inputId);
    if (!el) return;

    el.style.textTransform = 'uppercase';

    el.addEventListener('input', function () {
        var pos = this.selectionStart;
        var newVal = this.value.toUpperCase().replace(RE_CHAR_INVALIDO, '').replace(/ {2,}/g, ' ');
        if (newVal !== this.value) {
            this.value = newVal;
            this.setSelectionRange(Math.min(pos, newVal.length), Math.min(pos, newVal.length));
        }
        if (this.value.length > 0) {
            validarNombreApellido(inputId, feedbackId, etiqueta);
        } else {
            clearField(inputId, feedbackId);
        }
    });

    el.addEventListener('blur', function () {
        validarNombreApellido(inputId, feedbackId, etiqueta);
    });

    el.addEventListener('paste', function (e) {
        e.preventDefault();
        var texto = (e.clipboardData || window.clipboardData).getData('text');
        this.value = texto.toUpperCase().replace(RE_CHAR_INVALIDO, '').replace(/ {2,}/g, ' ').trim();
        this.dispatchEvent(new Event('input'));
    });
}

function configurarFecha() {
    var el = document.getElementById('fecha_nacimiento');
    if (!el) return;

    var hoy = new Date();
    var toISO = function (d) { return d.toISOString().split('T')[0]; };
    el.setAttribute('max', toISO(hoy));

    el.addEventListener('change', validarFecha);
    el.addEventListener('blur', validarFecha);
}

function validarFecha() {
    var el = document.getElementById('fecha_nacimiento');
    if (!el) return true;
    var val = el.value;

    if (!val) {
        setFieldError('fecha_nacimiento', 'fecha-feedback', 'La fecha de nacimiento es obligatoria.');
        return false;
    }

    var fecha = new Date(val + 'T00:00:00');
    var hoy = new Date();

    if (fecha > hoy) {
        setFieldError('fecha_nacimiento', 'fecha-feedback', 'La fecha no puede ser en el futuro.');
        return false;
    }
    setFieldOk('fecha_nacimiento', 'fecha-feedback');
    return true;
}

function validarEmail() {
    var el = document.getElementById('email');
    if (!el) return true;
    var val = el.value.trim();
    if (val.length === 0) {
        setFieldError('email', 'email-feedback', 'El correo electrónico es obligatorio.');
        return false;
    }
    var re = /^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,}$/;
    if (!re.test(val)) {
        setFieldError('email', 'email-feedback', 'Formato inválido. Use solo minúsculas, números y símbolos permitidos (._-).');
        return false;
    }
    setFieldOk('email', 'email-feedback');
    return true;
}

function bindEmail() {
    var el = document.getElementById('email');
    if (!el) return;
    el.addEventListener('input', function () {
        var pos = this.selectionStart;
        var valOriginal = this.value;
        var valFiltrado = valOriginal.toLowerCase().replace(/[^a-z0-9@._-]/g, '');
        if (valOriginal !== valFiltrado) {
            this.value = valFiltrado;
            this.setSelectionRange(pos, pos);
        }
        if (this.value.length > 0) validarEmail();
        else clearField('email', 'email-feedback');
    });
    el.addEventListener('blur', validarEmail);
    el.addEventListener('paste', function (e) {
        e.preventDefault();
        var texto = (e.clipboardData || window.clipboardData).getData('text');
        this.value = texto.toLowerCase().replace(/[^a-z0-9@._-]/g, '');
        this.dispatchEvent(new Event('input'));
    });
}

function validarTelefono() {
    var el = document.getElementById('telefono');
    if (!el) return true;
    var val = el.value;
    if (val.trim().length === 0) {
        setFieldError('telefono', 'telefono-feedback', 'El teléfono es obligatorio.');
        return false;
    }
    if (!/^\d+$/.test(val)) {
        setFieldError('telefono', 'telefono-feedback', 'Solo se permiten números. Sin guiones ni símbolos.');
        return false;
    }
    if (val.length < 7) {
        setFieldError('telefono', 'telefono-feedback', 'Mínimo 7 dígitos. Ej: 1134567890');
        return false;
    }
    if (val.length > 15) {
        setFieldError('telefono', 'telefono-feedback', 'Máximo 15 dígitos.');
        return false;
    }
    setFieldOk('telefono', 'telefono-feedback');
    return true;
}

function bindTelefono() {
    var el = document.getElementById('telefono');
    if (!el) return;
    el.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 0) validarTelefono();
        else clearField('telefono', 'telefono-feedback');
    });
    el.addEventListener('blur', validarTelefono);
    el.addEventListener('paste', function (e) {
        e.preventDefault();
        var texto = (e.clipboardData || window.clipboardData).getData('text');
        this.value = texto.replace(/[^0-9]/g, '').slice(0, 15);
        this.dispatchEvent(new Event('input'));
    });
}

function validarCodigoPostal() {
    var el = document.getElementById('codigo_postal');
    if (!el || el.value.length === 0) return true;
    var val = el.value;
    if (/\s/.test(val)) {
        setFieldError('codigo_postal', 'cp-feedback', 'No se permiten espacios.');
        return false;
    }
    if (!/^[A-Z0-9]+$/.test(val)) {
        setFieldError('codigo_postal', 'cp-feedback', 'Solo letras y números. Sin símbolos.');
        return false;
    }
    if (val.length < 4) {
        setFieldError('codigo_postal', 'cp-feedback', 'Mínimo 4 caracteres. Ej: 1414');
        return false;
    }
    setFieldOk('codigo_postal', 'cp-feedback');
    return true;
}

function bindCodigoPostal() {
    var el = document.getElementById('codigo_postal');
    if (!el) return;
    el.style.textTransform = 'uppercase';
    el.addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (this.value.length > 0) validarCodigoPostal();
        else clearField('codigo_postal', 'cp-feedback');
    });
    el.addEventListener('blur', function () {
        if (this.value.length > 0) validarCodigoPostal();
    });
    el.addEventListener('paste', function (e) {
        e.preventDefault();
        var texto = (e.clipboardData || window.clipboardData).getData('text');
        this.value = texto.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);
        this.dispatchEvent(new Event('input'));
    });
}

function bindDireccion() {
    var el = document.getElementById('direccion');
    if (!el) return;
    el.style.textTransform = 'uppercase';
    el.addEventListener('input', function () {
        var pos = this.selectionStart;
        var newVal = this.value.toUpperCase();
        if (newVal !== this.value) {
            this.value = newVal;
            this.setSelectionRange(Math.min(pos, newVal.length), Math.min(pos, newVal.length));
        }
    });
    el.addEventListener('paste', function (e) {
        e.preventDefault();
        var texto = (e.clipboardData || window.clipboardData).getData('text');
        this.value = texto.toUpperCase();
        this.dispatchEvent(new Event('input'));
    });
}

function init() {
    camposFieldset = document.getElementById('campos-principal');
    btnGuardar = document.getElementById('btn-guardar');

    var form = document.getElementById('formPaciente');
    var modoEditar = form && form.getAttribute('data-modo') === 'editar';

    if (modoEditar) {
        dniVerificado = true;
        var idPersona = form.getAttribute('data-id');
        if (idPersona) preLlenarFormulario(idPersona);
    } else {
        mostrarAviso('bloqueo');
        bindDni();
    }

    configurarFecha();
    bindNombreApellido('nombre', 'nombre-feedback', 'nombre');
    bindNombreApellido('apellido', 'apellido-feedback', 'apellido');
    bindEmail();
    bindTelefono();
    bindDireccion();
    bindCodigoPostal();
    bindSubmit(modoEditar);
}

async function preLlenarFormulario(idPersona) {
    try {
        var res = await fetch('app/cir-api/endpoints/gestion_pacientes-e.php?id=' + idPersona);
        var result = await res.json();

        if (result.status !== 'success' || !result.persona) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos del paciente.',
                confirmButtonColor: '#ff6b81'
            });
            return;
        }

        var p = result.persona;

        var setVal = function (id, val) {
            var el = document.getElementById(id);
            if (el && val != null) el.value = val;
        };

        setVal('dni', p.dni);
        setVal('nombre', (p.nombre || '').toUpperCase());
        setVal('apellido', (p.apellido || '').toUpperCase());
        setVal('fecha_nacimiento', p.fecha_nacimiento);
        setVal('email', p.email);
        setVal('telefono', p.telefono);
        setVal('direccion', (p.direccion || '').toUpperCase());
        setVal('codigo_postal', (p.codigo_postal || '').toUpperCase());

        ['nombre', 'apellido', 'direccion', 'codigo_postal'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.textTransform = 'uppercase';
        });

        validarNombreApellido('nombre', 'nombre-feedback', 'nombre');
        validarNombreApellido('apellido', 'apellido-feedback', 'apellido');
        validarFecha();
        validarEmail();
        validarTelefono();
        if (p.codigo_postal) validarCodigoPostal();

    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar los datos del paciente.',
            confirmButtonColor: '#ff6b81'
        });
    }
}

function bindSubmit(modoEditar) {
    var form = document.getElementById('formPaciente');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!modoEditar && !dniVerificado) {
            mostrarAviso('bloqueo');
            if (dniInput) dniInput.focus();
            return;
        }

        var ok = true;
        if (!modoEditar && (!dniInput || !dniInput.classList.contains('is-valid'))) ok = false;
        if (!validarNombreApellido('nombre', 'nombre-feedback', 'nombre')) ok = false;
        if (!validarNombreApellido('apellido', 'apellido-feedback', 'apellido')) ok = false;
        if (!validarFecha()) ok = false;
        if (!validarEmail()) ok = false;
        if (!validarTelefono()) ok = false;
        if (!validarCodigoPostal()) ok = false;

        if (!ok) {
            var primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        var formData = new FormData(form);
        var data = Object.fromEntries(formData.entries());

        if (modoEditar) {
            data.id_persona = form.getAttribute('data-id');
        }

        var btnText = btnGuardar.textContent;
        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando...';

        var endpoint = 'app/cir-api/endpoints/gestion_pacientes-e.php';

        try {
            var res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            var result = await res.json();

            if (result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: result.message,
                    confirmButtonColor: '#00cec9'
                }).then(function () {
                    window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=gestion_pacientes';
                });
            } else {
                var msg = result.message;
                if (result.errores && result.errores.length) {
                    msg += '\n' + result.errores.join('\n');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg,
                    confirmButtonColor: '#ff6b81'
                });
                btnGuardar.disabled = false;
                btnGuardar.textContent = btnText;
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#ff6b81'
            });
            btnGuardar.disabled = false;
            btnGuardar.textContent = btnText;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
