/**
 * form_users.js — Validación en tiempo real para el formulario de nuevo usuario
 * Módulo: Circuito Quirúrgico
 */

// ============================================================
// UTILIDADES DE UI — mostrar/ocultar mensajes por campo
// ============================================================
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

// ============================================================
// ESTADO GLOBAL
// ============================================================
var dniVerificado = false;
var camposFieldset = null;
var btnGuardar = null;
var dniInput = null;
var icoDni = null;
var dniTimer = null;

// ============================================================
// BLOQUEO / DESBLOQUEO DEL FORMULARIO
// ============================================================
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

// ============================================================
// BIND DNI — validación y verificación en servidor
// ============================================================
function bindDni() {
    dniInput = document.getElementById('dni');
    icoDni = document.getElementById('ico-dni');
    if (!dniInput) return;

    dniInput.addEventListener('input', function () {
        // Filtrar solo dígitos
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
        var response = await fetch('app/cir-api/endpoints/verificar_dni-e.php?dni=' + val);
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

// ============================================================
// VALIDACIÓN NOMBRE / APELLIDO
// ============================================================
// Letras mayúsculas permitidas (unicode escapes para evitar problemas de encoding)
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



// ============================================================
// VALIDACIÓN FECHA DE NACIMIENTO (mayor de 18 años)
// ============================================================
function configurarFecha() {
    var el = document.getElementById('fecha_nacimiento');
    if (!el) return;

    // Limitar el input: máximo = hace 18 años, mínimo = hace 110 años
    var hoy = new Date();
    var max18 = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());
    var min110 = new Date(hoy.getFullYear() - 110, hoy.getMonth(), hoy.getDate());

    var toISO = function (d) { return d.toISOString().split('T')[0]; };
    el.setAttribute('max', toISO(max18));
    el.setAttribute('min', toISO(min110));

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
    var limite18 = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());
    var limite110 = new Date(hoy.getFullYear() - 110, hoy.getMonth(), hoy.getDate());

    if (fecha > hoy) {
        setFieldError('fecha_nacimiento', 'fecha-feedback', 'La fecha no puede ser en el futuro.');
        return false;
    }
    if (fecha > limite18) {
        setFieldError('fecha_nacimiento', 'fecha-feedback', 'El usuario debe ser mayor de 18 años.');
        return false;
    }
    if (fecha < limite110) {
        setFieldError('fecha_nacimiento', 'fecha-feedback', 'Fecha fuera de rango válido (máx. 110 años).');
        return false;
    }
    setFieldOk('fecha_nacimiento', 'fecha-feedback');
    return true;
}

// ============================================================
// VALIDACIÓN EMAIL
// ============================================================
function validarEmail() {
    var el = document.getElementById('email');
    if (!el) return true;
    var val = el.value.trim();
    if (val.length === 0) {
        setFieldError('email', 'email-feedback', 'El correo electrónico es obligatorio.');
        return false;
    }
    // Regex fuerte: letras minúsculas, números, punto, guion y guion bajo
    // Formato: usuario@dominio.tld
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
        // Forzar minúsculas y filtrar caracteres permitidos: a-z, 0-9, @, ., _, -
        var pos = this.selectionStart;
        var valOriginal = this.value;
        var valFiltrado = valOriginal.toLowerCase().replace(/[^a-z0-9@._-]/g, '');
        
        if (valOriginal !== valFiltrado) {
            this.value = valFiltrado;
            // Intentar mantener la posición del cursor
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

// ============================================================
// VALIDACIÓN TELÉFONO (solo dígitos, 7-15)
// ============================================================
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

// ============================================================
// VALIDACIÓN ROL
// ============================================================
function validarRol() {
    var el = document.getElementById('rol');
    if (!el) return true;
    if (!el.value) {
        setFieldError('rol', 'rol-feedback', 'Debe seleccionar un rol.');
        return false;
    }
    setFieldOk('rol', 'rol-feedback');
    return true;
}

// ============================================================
// VALIDACIÓN CÓDIGO POSTAL (alfanumérico mayúsculas, 4-8, opcional)
// ============================================================
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
        // Convertir a mayúsculas y eliminar caracteres inválidos en tiempo real
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

// ============================================================
// DIRECCIÓN — mayúsculas en tiempo real (campo libre, sin validación estricta)
// ============================================================
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

// ============================================================
// VALIDACIONES CAMPOS CIRUJANO
// ============================================================
function validarEspecialidad() {
    var el = document.getElementById('especialidad');
    if (!el) return true;
    if (!el.value) {
        setFieldError('especialidad', 'especialidad-feedback', 'La especialidad es obligatoria para cirujano.');
        return false;
    }
    setFieldOk('especialidad', 'especialidad-feedback');
    return true;
}
function validarNumeroMatricula() {
    var el = document.getElementById('numero_matricula');
    if (!el) return true;
    var val = el.value.trim();
    if (!val) {
        setFieldError('numero_matricula', 'matricula-feedback', 'El número de matrícula es obligatorio.');
        return false;
    }
    setFieldOk('numero_matricula', 'matricula-feedback');
    return true;
}
function validarTipoMatricula() {
    var el = document.getElementById('tipo_matricula');
    if (!el) return true;
    if (!el.value) {
        setFieldError('tipo_matricula', 'tipo-matricula-feedback', 'El tipo de matrícula es obligatorio.');
        return false;
    }
    setFieldOk('tipo_matricula', 'tipo-matricula-feedback');
    return true;
}

// ============================================================
// LÓGICA ROL — mostrar sección cirujano + validaciones obligatorias
// ============================================================
function bindRol() {
    var rolSelect = document.getElementById('rol');
    var datosCirujano = document.getElementById('datos_cirujano');
    if (!rolSelect || !datosCirujano) return;

    rolSelect.addEventListener('change', function () {
        validarRol();
        var textoRol = this.options[this.selectedIndex].text.toLowerCase();
        var esCirujano = textoRol.indexOf('cirujano') !== -1;

        if (esCirujano) {
            datosCirujano.style.display = 'block';

            // Hacer campos obligatorios y agregarles validación en tiempo real
            var esp = document.getElementById('especialidad');
            var mat = document.getElementById('numero_matricula');
            var tip = document.getElementById('tipo_matricula');

            if (esp) {
                esp.setAttribute('required', 'required');
                esp.addEventListener('change', validarEspecialidad);
                esp.addEventListener('blur', validarEspecialidad);
            }
            if (mat) {
                mat.setAttribute('required', 'required');
                mat.addEventListener('input', function () {
                    if (this.value.trim().length > 0) validarNumeroMatricula();
                    else clearField('numero_matricula', 'matricula-feedback');
                });
                mat.addEventListener('blur', validarNumeroMatricula);
            }
            if (tip) {
                tip.setAttribute('required', 'required');
                tip.addEventListener('change', validarTipoMatricula);
                tip.addEventListener('blur', validarTipoMatricula);
            }
        } else {
            datosCirujano.style.display = 'none';
            ['especialidad', 'numero_matricula', 'tipo_matricula'].forEach(function (id) {
                var campo = document.getElementById(id);
                if (campo) {
                    campo.removeAttribute('required');
                    campo.value = '';
                    campo.classList.remove('is-valid', 'is-invalid');
                }
            });
            ['especialidad-feedback', 'matricula-feedback', 'tipo-matricula-feedback'].forEach(function (fbId) {
                var fb = document.getElementById(fbId);
                if (fb) fb.innerHTML = '';
            });
        }
    });
}


// ============================================================
// CARGAR SELECTS DESDE LA API
// ============================================================
async function cargarSelects() {
    try {
        var response = await fetch('app/cir-api/endpoints/obtener_roles_especialidades-e.php');
        var result = await response.json();
        if (result.status === 'success') {
            var rolSelect = document.getElementById('rol');
            var espSelect = document.getElementById('especialidad');
            if (rolSelect && result.roles) {
                result.roles.forEach(function (rol) {
                    var opt = document.createElement('option');
                    opt.value = rol.id_rol;
                    opt.text = rol.nombre;
                    rolSelect.add(opt);
                });
            }
            if (espSelect && result.especialidades) {
                result.especialidades.forEach(function (esp) {
                    var opt = document.createElement('option');
                    opt.value = esp.id_especialidad;
                    opt.text = esp.nombre;
                    espSelect.add(opt);
                });
            }
        }
    } catch (err) {
        // Error silencioso en UI
    }
}

// ============================================================
// SUBMIT — validar todo antes de enviar al servidor
// ============================================================


// ============================================================
// INIT — punto de entrada principal
// Detecta si el formulario está en modo 'editar' o 'nuevo'
// ============================================================
function init() {
    camposFieldset = document.getElementById('campos-principal');
    btnGuardar = document.getElementById('btn-guardar');

    var form = document.getElementById('formUsuario');
    var modoEditar = form && form.getAttribute('data-modo') === 'editar';

    if (modoEditar) {
        // En modo editar: el formulario ya está desbloqueado desde el PHP
        // Marcamos DNI como verificado para que el submit funcione sin el flujo de bloqueo
        dniVerificado = true;

        // Cargar selects primero, luego pre-llenar con los datos del usuario
        cargarSelects().then(function () {
            var idPersona = form.getAttribute('data-id');
            if (idPersona) preLlenarFormulario(idPersona);
        });
    } else {
        // Modo nuevo: flujo normal con bloqueo por DNI
        mostrarAviso('bloqueo');
        bindDni();
        cargarSelects();
    }

    // Bind de validaciones (siempre, en ambos modos)
    configurarFecha();
    bindNombreApellido('nombre', 'nombre-feedback', 'nombre');
    bindNombreApellido('apellido', 'apellido-feedback', 'apellido');
    bindEmail();
    bindTelefono();
    bindDireccion();
    bindCodigoPostal();
    bindRol();
    bindSubmit(modoEditar);
}

// Pre-llenar formulario con datos del usuario a editar
async function preLlenarFormulario(idPersona) {
    try {
        var res = await fetch('app/cir-api/endpoints/obtener_usuario-e.php?id=' + idPersona);
        var result = await res.json();

        if (result.status !== 'success' || !result.persona) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos del usuario.',
                confirmButtonColor: '#ff6b81'
            });
            return;
        }

        var p = result.persona;

        // Campos simples
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


        // Aplicar estilo uppercase a nombre y apellido
        ['nombre', 'apellido', 'direccion', 'codigo_postal'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.textTransform = 'uppercase';
        });

        // Seleccionar el rol
        var rolSelect = document.getElementById('rol');
        if (rolSelect && p.id_rol) {
            rolSelect.value = p.id_rol;
            // Disparar el evento change para mostrar sección cirujano si corresponde
            rolSelect.dispatchEvent(new Event('change'));
        }

        // Si tenía datos de cirujano, pre-llenar esos campos también
        if (p.id_profesional) {
            var datosCirujano = document.getElementById('datos_cirujano');
            if (datosCirujano) datosCirujano.style.display = 'block';
            setVal('especialidad', p.id_especialidad);
            setVal('numero_matricula', p.numero_matricula);
            setVal('tipo_matricula', p.tipo_matricula);
        }

        // Validar cada campo después de pre-llenar para establecer estado correcto
        // (esto evita que un blur posterior los muestre como inválidos)
        validarNombreApellido('nombre', 'nombre-feedback', 'nombre');
        validarNombreApellido('apellido', 'apellido-feedback', 'apellido');
        validarFecha();
        validarEmail();
        validarTelefono();
        validarRol();
        if (p.codigo_postal) validarCodigoPostal();

    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar los datos del usuario.',
            confirmButtonColor: '#ff6b81'
        });
    }
}

// Submit adaptado a modo nuevo o editar
function bindSubmit(modoEditar) {
    var form = document.getElementById('formUsuario');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // En modo nuevo verificar que el DNI fue validado
        if (!modoEditar && !dniVerificado) {
            mostrarAviso('bloqueo');
            if (dniInput) dniInput.focus();
            return;
        }

        // Ejecutar todas las validaciones
        var ok = true;
        if (!modoEditar && (!dniInput || !dniInput.classList.contains('is-valid'))) ok = false;
        if (!validarNombreApellido('nombre', 'nombre-feedback', 'nombre')) ok = false;
        if (!validarNombreApellido('apellido', 'apellido-feedback', 'apellido')) ok = false;
        if (!validarFecha()) ok = false;
        if (!validarEmail()) ok = false;
        if (!validarTelefono()) ok = false;
        if (!validarRol()) ok = false;
        if (!validarCodigoPostal()) ok = false;

        // Validar campos de cirujano si están visibles
        var datosCirujano = document.getElementById('datos_cirujano');
        if (datosCirujano && datosCirujano.style.display !== 'none') {
            var esp = document.getElementById('especialidad');
            var mat = document.getElementById('numero_matricula');
            var tip = document.getElementById('tipo_matricula');
            if (esp && !esp.value) { setFieldError('especialidad', 'especialidad-feedback', 'Seleccioná una especialidad.'); ok = false; }
            if (mat && !mat.value.trim()) { setFieldError('numero_matricula', 'matricula-feedback', 'El número de matrícula es obligatorio.'); ok = false; }
            if (tip && !tip.value) { setFieldError('tipo_matricula', 'tipo-matricula-feedback', 'Seleccioná el tipo de matrícula.'); ok = false; }
        }

        if (!ok) {
            var primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Preparar datos
        var formData = new FormData(form);
        var data = Object.fromEntries(formData.entries());

        // En modo editar: agregar id_persona al payload
        if (modoEditar) {
            data.id_persona = form.getAttribute('data-id');
        }

        var btnText = btnGuardar.textContent;
        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando...';

        // Elegir endpoint según modo
        var endpoint = modoEditar
            ? 'app/cir-api/endpoints/editar_users-e.php'
            : 'app/cir-api/endpoints/crear_users-e.php';

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
                }).then(() => {
                    if (modoEditar) {
                        window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=gestion_usuarios';
                    }
                });

                if (!modoEditar) {
                    // Modo nuevo: resetear el formulario
                    form.reset();
                    form.querySelectorAll('.is-valid, .is-invalid').forEach(function (el) {
                        el.classList.remove('is-valid', 'is-invalid');
                    });
                    form.querySelectorAll('[id$="-feedback"]').forEach(function (fb) { fb.innerHTML = ''; });
                    bloquearFormulario();
                    mostrarAviso('bloqueo');
                    clearField('dni', 'dni-feedback');
                    if (icoDni) icoDni.className = 'bi bi-search text-secondary';
                    var dc = document.getElementById('datos_cirujano');
                    if (dc) dc.style.display = 'none';
                }
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
                text: 'No se pudo conectar con el servidor. Intentá nuevamente.',
                confirmButtonColor: '#ff6b81'
            });
            btnGuardar.disabled = false;
            btnGuardar.textContent = btnText;
        }
    });
}

// Ejecutar init: inmediatamente si el DOM ya cargó, o esperar DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

