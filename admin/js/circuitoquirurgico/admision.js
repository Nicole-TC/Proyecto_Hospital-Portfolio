const ENDPOINT = 'app/cir-api/endpoints/admision-e.php';

const camposPaciente = [
  'apellido', 'nombre', 'fechaNacimiento',
  'direccion', 'codigoPostal', 'email', 'telefono', 'obraSocial'
];

const inputDni = document.getElementById('dni');
const selectCirugia = document.getElementById('tipoCirugia');
const form = document.getElementById('form-admision');

var estudiosPorCirugia = {};

// ─── VALIDACIÓN EN TIEMPO REAL ───────────────────

var validators = {
  dni: function (v) {
    if (!v) return 'El DNI es obligatorio.';
    return /^\d{5,8}$/.test(v) ? '' : 'El DNI debe tener entre 5 y 8 dígitos.';
  },
  apellido: function (v) {
    if (!v) return 'El apellido es obligatorio.';
    return /^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,100}$/.test(v) ? '' : 'Solo letras, mínimo 2 caracteres.';
  },
  nombre: function (v) {
    if (!v) return 'El nombre es obligatorio.';
    return /^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,100}$/.test(v) ? '' : 'Solo letras, mínimo 2 caracteres.';
  },
  fechaNacimiento: function (v) {
    if (!v) return 'La fecha de nacimiento es obligatoria.';
    if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return 'Formato YYYY-MM-DD.';
    var p = v.split('-');
    var d = new Date(+p[0], +p[1] - 1, +p[2]);
    if (d.getFullYear() !== +p[0] || d.getMonth() + 1 !== +p[1] || d.getDate() !== +p[2]) return 'Fecha no válida.';
    if (+p[0] < 1900) return 'El año debe ser 1900 o posterior.';
    if (v > new Date().toISOString().split('T')[0]) return 'No puede ser posterior a hoy.';
    return '';
  },
  direccion: function (v) {
    if (!v) return 'La dirección es obligatoria.';
    return /^[a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ\s\.,#\-\/]{5,255}$/.test(v) ? '' : 'Mín. 5 caracteres.';
  },
  codigoPostal: function (v) {
    if (!v) return 'El código postal es obligatorio.';
    return /^[a-zA-Z0-9]{1,8}$/.test(v) ? '' : 'Hasta 8 caracteres alfanuméricos.';
  },
  email: function (v) {
    if (!v) return 'El email es obligatorio.';
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v) ? '' : 'Email inválido.';
  },
  telefono: function (v) {
    if (!v) return 'El teléfono es obligatorio.';
    return /^[\d\s\-\(\)\+]{7,20}$/.test(v) ? '' : 'Mín. 7 dígitos.';
  },
  tipoCirugia: function (v) {
    if (!v) return 'Seleccione un tipo de cirugía.';
    return '';
  },
  cirujano: function (v) {
    if (!v) return 'Seleccione un cirujano.';
    return '';
  }
};

function validarCampo(id) {
  var el = document.getElementById(id);
  if (!el || el.disabled) { limpiarValidacion(id); return true; }
  var val = el.value.trim();
  var fn = validators[id];
  if (!fn) return true;
  var error = fn(val);
  var fb = el.parentElement.querySelector('.invalid-feedback');
  if (error) {
    el.classList.add('is-invalid');
    el.classList.remove('is-valid');
    if (fb) fb.textContent = error;
    return false;
  } else {
    el.classList.remove('is-invalid');
    el.classList.add('is-valid');
    if (fb) fb.textContent = '';
    return true;
  }
}

function limpiarValidacion(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('is-invalid', 'is-valid');
  var fb = el.parentElement.querySelector('.invalid-feedback');
  if (fb) fb.textContent = '';
}

function validarTodo() {
  var ok = true;
  var primero = null;
  var ids = ['dni', 'apellido', 'nombre', 'fechaNacimiento', 'direccion', 'codigoPostal', 'email', 'telefono', 'tipoCirugia', 'cirujano'];
  ids.forEach(function (id) {
    var e = document.getElementById(id);
    if (e && !e.disabled) {
      if (!validarCampo(id)) { ok = false; if (!primero) primero = e; }
    }
  });
  if (primero) primero.focus();
  return ok;
}

// ─── VARIABLES DEL ESCÁNER ──────────────────────

var scanBuffer = '';
var scanTimeout = null;
var lastKeyTime = 0;
var scanDetected = false;
var scanFastCount = 0;
var SCAN_KEY_GAP_MS = 50;
var SCAN_MIN_FAST_KEYS = 3;
var pacienteCargado = false;
var dniAntesDeEscanear = '';

function el(id) {
  return document.getElementById(id);
}

function deshabilitarCamposPaciente(disabled) {
  camposPaciente.forEach(function (id) {
    var e = el(id);
    if (e) e.disabled = disabled;
  });
  var btn = el('btnObraSocial');
  if (btn) btn.disabled = disabled;
}

function limpiarCamposPaciente() {
  camposPaciente.forEach(function (id) {
    var e = el(id);
    if (e) {
      if (e.type === 'date') e.value = '';
      else if (e.type === 'checkbox') e.checked = false;
      else e.value = '';
    }
  });
}

function habilitarCirugia(disabled) {
  selectCirugia.disabled = disabled;
}

function habilitarCirujano(disabled) {
  var s = el('cirujano');
  if (s) s.disabled = disabled;
}

function mostrarEstudios() {
  var cont = el('contenedorEstudios');
  var lista = el('listaEstudios');
  if (!cont || !lista) return;
  var cirugia = selectCirugia.value;
  if (!cirugia) {
    cont.style.display = 'none';
    lista.innerHTML = '';
    return;
  }
  var estudios = estudiosPorCirugia[cirugia] || [];
  lista.innerHTML = '';
  estudios.forEach(function (est) {
    var badge = document.createElement('span');
    badge.className = 'badge bg-info fs-6 px-3 py-2';
    badge.textContent = est.nombre;
    lista.appendChild(badge);
  });

  var conIndicaciones = estudios.filter(function (e) { return e.observacion && e.observacion.trim() !== ''; });
  if (conIndicaciones.length > 0) {
    var btn = document.createElement('button');
    btn.className = 'btn btn-outline-info rounded-pill px-4 ms-3';
    btn.textContent = 'Ver indicaciones';
    btn.type = 'button';
    btn.addEventListener('click', function () {
      verIndicaciones(conIndicaciones);
    });
    lista.appendChild(btn);
  }

  cont.style.display = '';
}

function verIndicaciones(lista) {
  var html = '';
  lista.forEach(function (est, i) {
    if (i > 0) html += '<hr>';
    html += '<strong>' + est.nombre + '</strong><br>';
    html += est.observacion.replace(/\n/g, '<br>');
  });
  Swal.fire({
    icon: 'info',
    title: 'Indicaciones generales',
    html: html,
    confirmButtonColor: '#3085d6'
  });
}

function habilitarSelects(disabled) {
  habilitarCirugia(disabled);
  habilitarCirujano(disabled);
  mostrarEstudios();
}

function reiniciarFormulario() {
  pacienteCargado = false;
  limpiarCamposPaciente();
  inputDni.value = '';
  selectCirugia.value = '';
  var s = el('cirujano');
  if (s) s.value = '';
  deshabilitarCamposPaciente(true);
  habilitarSelects(true);
}

function buscarPaciente(dni) {
  if (!dni || dni.length < 5) {
    limpiarCamposPaciente();
    deshabilitarCamposPaciente(true);
    habilitarSelects(true);
    return;
  }

  fetch(ENDPOINT + '?action=buscar_dni&dni=' + encodeURIComponent(dni))
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.ok && data.encontrado && data.paciente) {
        var p = data.paciente;
        el('apellido').value = p.apellido || '';
        el('nombre').value = p.nombre || '';
        el('fechaNacimiento').value = p.fecha_nacimiento || '';
        el('direccion').value = p.direccion || '';
        el('codigoPostal').value = p.codigo_postal || '';
        el('email').value = p.email || '';
        el('telefono').value = p.telefono || '';
        el('obraSocial').checked = p.obra_social == 1 || p.obra_social === true;
        actualizarLabelObraSocial();
        deshabilitarCamposPaciente(true);
        habilitarSelects(false);
      } else {
        limpiarCamposPaciente();
        deshabilitarCamposPaciente(false);
        habilitarSelects(false);
      }
    })
    .catch(function () {
      limpiarCamposPaciente();
      deshabilitarCamposPaciente(true);
      habilitarSelects(true);
    });
}

function serializarFormulario() {
  var datos = {};
  datos.dni = inputDni.value.trim();
  camposPaciente.forEach(function (id) {
    var e = el(id);
    datos[id] = e ? (e.type === 'checkbox' ? e.checked : e.value) : '';
  });
  datos.tipoCirugia = selectCirugia.value;
  var s = el('cirujano');
  datos.cirujano = s ? s.value : '';
  var estudios = estudiosPorCirugia[selectCirugia.value] || [];
  datos.tipoEstudio = estudios.map(function (e) { return e.nombre; }).join(', ');
  return datos;
}

form.addEventListener('submit', function (e) {
  e.preventDefault();
  if (!validarTodo()) return;

  var datos = serializarFormulario();

  fetch(ENDPOINT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datos)
  })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.ok) {
        var datos = serializarFormulario();
        if (data.ordenReusada) {
          Swal.fire({
            icon: 'info',
            title: 'Orden existente',
            text: data.mensaje,
            confirmButtonText: 'Volver',
            confirmButtonColor: '#3085d6',
          }).then(function () {
            reiniciarFormulario();
          });
        } else {
          Swal.fire({
            icon: 'success',
            title: 'Operación exitosa',
            html:
              '<p style="margin:5px 0 0;color:#666;">DNI del paciente:</p>' +
              '<p style="font-size:2rem;font-weight:bold;color:#0d6efd;letter-spacing:3px;margin:5px 0 0;">' +
                datos.dni +
              '</p>',
            confirmButtonText: 'Ir a agenda',
            confirmButtonColor: '#3085d6',
          }).then(function (result) {
            if (result.isConfirmed) {
              reiniciarFormulario();
              window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=agenda&dni=' + datos.dni;
            }
          });
        }
      } else {
        var msg = data.errores ? data.errores.join('\n') : (data.error || 'Error desconocido');
        Swal.fire({
          icon: 'error',
          title: 'Error de validación',
          text: msg,
          confirmButtonColor: '#d33'
        });
      }
    })
    .catch(function () {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.',
        confirmButtonColor: '#d33'
      });
    });
});

function cargarCatalogos() {
  fetch(ENDPOINT + '?action=obtener_catalogos')
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.ok) {
        console.error('Error al cargar catálogos:', data.error);
        return;
      }
      if (data.estudiosPorCirugia) {
        estudiosPorCirugia = data.estudiosPorCirugia;
      }
      if (data.cirugias && data.cirugias.length > 0) {
        selectCirugia.innerHTML = '<option value="">Seleccione tipo de cirugía</option>';
        data.cirugias.forEach(function (nombre) {
          var opt = document.createElement('option');
          opt.value = nombre;
          opt.textContent = nombre;
          selectCirugia.appendChild(opt);
        });
      }

    })
    .catch(function (err) {
      console.error('Error en fetch de catálogos:', err);
    });
}

function cargarCirujanos() {
  fetch(ENDPOINT + '?action=obtener_cirujanos')
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.ok) {
        console.error('Error al cargar cirujanos:', data.error);
        return;
      }
      var s = el('cirujano');
      if (!s) return;
      if (data.cirujanos && data.cirujanos.length > 0) {
        s.innerHTML = '<option value="">Seleccione cirujano</option>';
        data.cirujanos.forEach(function (c) {
          var opt = document.createElement('option');
          opt.value = c.id_persona;
          opt.textContent = c.apellido + ', ' + c.nombre;
          s.appendChild(opt);
        });
      }
    })
    .catch(function (err) {
      console.error('Error en fetch de cirujanos:', err);
    });
}

document.addEventListener('DOMContentLoaded', function () {
  cargarCatalogos();
  cargarCirujanos();
  var hoy = new Date().toISOString().split('T')[0];
  var fechaInput = document.getElementById('fechaNacimiento');
  if (fechaInput) fechaInput.setAttribute('max', hoy);

  function filtrarInput(e) {
    if (scanDetected || scanBuffer.length > 0) return;

    if (e.target.id === 'dni') {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 8);
    }
    if (e.target.id === 'nombre' || e.target.id === 'apellido') {
      e.target.value = e.target.value.replace(/[0-9]/g, '').toUpperCase();
    }
    if (e.target.id === 'direccion') {
      e.target.value = e.target.value.toUpperCase();
    }
    if (e.target.id === 'codigoPostal') {
      e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').slice(0, 8);
    }
    if (e.target.id === 'telefono') {
      e.target.value = e.target.value.replace(/\D/g, '');
    }
  }

  document.getElementById('dni').addEventListener('input', filtrarInput);
  document.getElementById('nombre').addEventListener('input', filtrarInput);
  document.getElementById('apellido').addEventListener('input', filtrarInput);
  document.getElementById('direccion').addEventListener('input', filtrarInput);
  document.getElementById('codigoPostal').addEventListener('input', filtrarInput);
  document.getElementById('telefono').addEventListener('input', filtrarInput);

  // ─── Validación en tiempo real ─────────────────────
  var camposAValidar = ['dni', 'apellido', 'nombre', 'fechaNacimiento', 'direccion', 'codigoPostal', 'email', 'telefono'];
  camposAValidar.forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function () {
      if (el.classList.contains('is-invalid') || el.classList.contains('is-valid')) {
        validarCampo(id);
      }
    });
    el.addEventListener('blur', function () { validarCampo(id); });
  });

  document.getElementById('tipoCirugia').addEventListener('change', function () { validarCampo('tipoCirugia'); });
  document.getElementById('cirujano').addEventListener('change', function () { validarCampo('cirujano'); });
});

selectCirugia.addEventListener('change', function () {
  mostrarEstudios();
});

inputDni.addEventListener('blur', function () {
  if (window._scanJustCompleted) {
    window._scanJustCompleted = false;
    return;
  }
  buscarPaciente(this.value.trim());
});

function actualizarLabelObraSocial() {
  var cb = el('obraSocial');
  var btn = document.getElementById('btnObraSocial');
  if (!cb || !btn) return;
  btn.className = cb.checked
    ? 'btn btn-success rounded-pill px-4 w-auto'
    : 'btn btn-danger rounded-pill px-4 w-auto';
}

document.getElementById('btnObraSocial').addEventListener('click', function () {
  var cb = el('obraSocial');
  cb.checked = !cb.checked;
  actualizarLabelObraSocial();
});

inputDni.addEventListener('input', function () {
  if (scanDetected || scanBuffer.length > 0) return;

  var val = this.value.trim();
  if (val === '') {
    pacienteCargado = false;
    limpiarCamposPaciente();
    deshabilitarCamposPaciente(true);
    habilitarSelects(true);
    var s = el('cirujano');
    if (s) s.value = '';
  }
});

el('obraSocial').addEventListener('change', actualizarLabelObraSocial);

// ─── PARSER DEL QR ────────────────────────────────

function normalizarQR(texto) {
  texto = (texto || '').trim().replace(/"/g, '@');
  if (texto.indexOf('@') === -1) {
    texto = texto.replace(/[qQ]/g, '@');
  }
  return texto;
}

function parsearQRCode(texto) {
  texto = normalizarQR(texto);
  var fechaMatch = texto.match(/(\d{2})[-\/](\d{2})[-\/](\d{4})/);
  var fechaFormateada = '';
  if (fechaMatch) {
    fechaFormateada = fechaMatch[3] + '-' + fechaMatch[2] + '-' + fechaMatch[1];
  }

  var dni = null;

  var sexoMatch = texto.match(/[MF]/i);
  if (sexoMatch) {
    var sexoPos = sexoMatch.index;
    var afterSexo = texto.substring(sexoPos + 1).replace(/^[^0-9]*/, '');
    var digitMatch = afterSexo.match(/^(\d{5,10})/);
    if (digitMatch) {
      var digits = digitMatch[1];
      if (digits.length > 8) {
        dni = digits.slice(-8);
      } else if (/^\d{5,8}$/.test(digits)) {
        dni = digits;
      }
    }
  }

  if (!dni) {
    var digitos = texto.match(/\d{5,9}/g);
    if (digitos && digitos.length > 0) {
      dni = digitos[digitos.length - 1];
      if (dni.length > 8 && dni[0] === '2') {
        dni = dni.slice(1);
      }
    }
  }

  if (!dni || !/^\d{5,8}$/.test(dni)) return null;

  var partes = texto.split('@').filter(function (p) { return p.length > 0; });
  var apellido = '', nombre = '';

  var nameIdx = 0;
  if (partes.length >= 3) {
    var tramite = (partes[0] || '').replace(/\D/g, '');
    if ((tramite.length >= 9 && tramite.length <= 11) || /^\d{1,3}$/.test(partes[0]) || partes[0].length <= 1) {
      nameIdx = 1;
    }
  }
  if (nameIdx + 1 < partes.length && /^\d{5,8}$/.test(partes[nameIdx])) {
    nameIdx++;
  }

  if (partes.length >= nameIdx + 2) {
    apellido = partes[nameIdx].replace(/[^a-záéíóúüñA-Z\søØ]/g, '').trim().toUpperCase().replace(/Ø/g, 'O');
    nombre = partes[nameIdx + 1].replace(/[^a-záéíóúüñA-Z\søØ]/g, '').trim().toUpperCase().replace(/Ø/g, 'O');
  }

  var sexoIdx = nameIdx + 2;
  if (partes.length >= sexoIdx + 1 && nombre.length > 2) {
    var sexo = partes[sexoIdx].replace(/[^MF]/gi, '').toUpperCase();
    if ((sexo === 'M' || sexo === 'F') && nombre.slice(-1) === sexo) {
      nombre = nombre.slice(0, -1).trim();
    }
  }

  if (!apellido || !nombre) {
    var nameMatches = texto.match(/[A-Za-z\sáéíóúüñ]{5,}/g);
    if (nameMatches && nameMatches.length >= 2) {
      apellido = nameMatches[0].trim().toUpperCase();
      nombre = nameMatches.slice(1).join(' ').trim().toUpperCase();
    }
  }

  if (!apellido || !nombre) {
    if (partes.length >= nameIdx + 3) {
      var primerCampo = partes[nameIdx > 0 ? nameIdx - 1 : 0];
      var nombApell = primerCampo.replace(/^[\d&]+/, '');
      var nomPartes = nombApell.split(/\d/).filter(function (p) { return p.trim().length > 0; });
      if (nomPartes.length >= 2) {
        apellido = nomPartes[0].trim().toUpperCase();
        nombre = nomPartes.slice(1).join(' ').trim().toUpperCase();
      } else if (partes.length >= nameIdx + 2) {
        apellido = partes[nameIdx].trim().toUpperCase();
        nombre = partes[nameIdx + 1].trim().toUpperCase();
      }
    }
  }

  if (!apellido || !nombre) return null;

  var fechaIdx = nameIdx + 5;
  if (partes.length >= fechaIdx + 1) {
    var fNacMatch = partes[fechaIdx].match(/(\d{2})[-\/](\d{2})[-\/](\d{4})/);
    if (fNacMatch) {
      fechaFormateada = fNacMatch[3] + '-' + fNacMatch[2] + '-' + fNacMatch[1];
    }
  }

  return {
    dni: dni,
    apellido: apellido,
    nombre: nombre,
    fechaNacimiento: fechaFormateada
  };
}

// ─── PROCESADOR DE ESCANEO ────────────────────────

function procesarEscaneo(texto) {
  var datos = parsearQRCode(texto);
  if (!datos) {
    Swal.fire({
      icon: 'warning',
      title: 'QR no reconocido',
      html: 'Texto recibido:<br><code style="font-size:0.8em;word-break:break-all;">' + texto + '</code>',
      confirmButtonColor: '#3085d6'
    });
    return;
  }

  if (pacienteCargado && datos.dni !== dniAntesDeEscanear) {
    inputDni.value = dniAntesDeEscanear;
    Swal.fire({
      icon: 'warning',
      title: 'Paciente ya cargado',
      text: 'Ya hay un paciente cargado. Complete la admisión actual o reinicie la página para escanear otro DNI.',
      showDenyButton: true,
      confirmButtonText: 'Seguir con el actual',
      denyButtonText: 'Recargar página',
      confirmButtonColor: '#3085d6',
      denyButtonColor: '#d33',
    }).then(function (result) {
      if (result.isDenied) location.reload();
    });
    return;
  }

  pacienteCargado = true;
  window._scanJustCompleted = true;
  inputDni.value = datos.dni;

  fetch(ENDPOINT + '?action=buscar_dni&dni=' + encodeURIComponent(datos.dni))
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.ok && data.encontrado && data.paciente) {
        var p = data.paciente;
        el('apellido').value = p.apellido || '';
        el('nombre').value = p.nombre || '';
        el('fechaNacimiento').value = p.fecha_nacimiento || '';
        el('direccion').value = p.direccion || '';
        el('codigoPostal').value = p.codigo_postal || '';
        el('email').value = p.email || '';
        el('telefono').value = p.telefono || '';
        el('obraSocial').checked = p.obra_social == 1 || p.obra_social === true;
        actualizarLabelObraSocial();
        deshabilitarCamposPaciente(true);
        habilitarSelects(false);
      } else {
        el('apellido').value = datos.apellido;
        el('nombre').value = datos.nombre;
        el('fechaNacimiento').value = datos.fechaNacimiento;
        deshabilitarCamposPaciente(false);
        habilitarSelects(false);
        validarCampo('apellido');
        validarCampo('nombre');
        validarCampo('fechaNacimiento');
      }
    })
    .catch(function () {
      el('apellido').value = datos.apellido;
      el('nombre').value = datos.nombre;
      el('fechaNacimiento').value = datos.fechaNacimiento;
      deshabilitarCamposPaciente(false);
      habilitarSelects(false);
      validarCampo('apellido');
      validarCampo('nombre');
      validarCampo('fechaNacimiento');
    });
}

function cancelarEscaneo() {
  if (scanDetected && scanBuffer.length > 0 && scanBuffer.length < 30 && !/["@]/.test(scanBuffer)) {
    var restaurado = scanBuffer.replace(/\D/g, '').slice(0, 8);
    if (restaurado) inputDni.value = restaurado;
  }
  scanBuffer = '';
  scanDetected = false;
  scanFastCount = 0;
  if (scanTimeout) { clearTimeout(scanTimeout); scanTimeout = null; }
}

// ─── DETECTOR DE ESCANER USB ──────────────────────

document.addEventListener('keydown', function (e) {
  var now = Date.now();
  var timeSinceLast = lastKeyTime ? now - lastKeyTime : 999;
  lastKeyTime = now;

  if (!scanDetected) {
    if (e.key && e.key.length === 1) {
      if (timeSinceLast < SCAN_KEY_GAP_MS) {
        scanFastCount++;
      } else {
        scanFastCount = 1;
      }
      if (scanFastCount >= SCAN_MIN_FAST_KEYS) {
        scanDetected = true;
        scanBuffer = (e.target && e.target.id === 'dni') ? inputDni.value : '';
        dniAntesDeEscanear = inputDni.value;
        if (e.target.id !== 'dni') inputDni.focus();
      }
    } else if (e.key !== 'Shift' && e.key !== 'Control' && e.key !== 'Alt' && e.key !== 'Meta') {
      scanFastCount = 0;
    }
  }

  if (!scanDetected) return;

  if (scanTimeout) clearTimeout(scanTimeout);

  if (e.key === 'Enter') {
    e.preventDefault();
    if (scanBuffer.length > 10) procesarEscaneo(scanBuffer);
    cancelarEscaneo();
    return;
  }

  if (e.key && e.key.length === 1) {
    e.preventDefault();
    scanBuffer += e.key;
    inputDni.value = '';
    scanTimeout = setTimeout(function () {
      if (scanBuffer.length > 30) procesarEscaneo(scanBuffer);
      else cancelarEscaneo();
    }, 300);
    return;
  }

  if (e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt' || e.key === 'Meta') {
    e.preventDefault();
    scanTimeout = setTimeout(function () {
      if (scanBuffer.length > 30) procesarEscaneo(scanBuffer);
      else cancelarEscaneo();
    }, 300);
  }
});


