const ENDPOINT = 'app/cir-api/endpoints/admision-e.php';

const camposPaciente = [
  'apellido', 'nombre', 'fechaNacimiento',
  'direccion', 'codigoPostal', 'email', 'telefono', 'obraSocial'
];

const inputDni = document.getElementById('dni');
const selectCirugia = document.getElementById('tipoCirugia');
const form = document.getElementById('form-admision');

var estudiosPorCirugia = {};

function el(id) {
  return document.getElementById(id);
}

function deshabilitarCamposPaciente(disabled) {
  camposPaciente.forEach(function (id) {
    var e = el(id);
    if (e) e.disabled = disabled;
  });
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
  limpiarCamposPaciente();
  inputDni.value = '';
  selectCirugia.value = '';
  var s = el('cirujano');
  if (s) s.value = '';
  deshabilitarCamposPaciente(true);
  habilitarSelects(true);
}

function buscarPaciente(dni) {
  if (!dni || dni.length < 8) {
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

  var datos = serializarFormulario();

  fetch(ENDPOINT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datos)
  })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.ok) {
        Swal.fire({
          icon: 'success',
          title: 'Operación exitosa',
          text: data.mensaje,
          confirmButtonText: 'Ir a agenda',
          confirmButtonColor: '#3085d6',
        }).then(function (result) {
          if (result.isConfirmed) {
            // ── Guardar datos del paciente para Agenda ──
            var datos = serializarFormulario();
            sessionStorage.setItem('pacienteAgenda', JSON.stringify({
              dni: datos.dni,
              nombre: datos.nombre,
              apellido: datos.apellido,
              email: datos.email
            }));
            reiniciarFormulario();
            window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=agenda';
          }
        });
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
});

selectCirugia.addEventListener('change', function () {
  mostrarEstudios();
});

inputDni.addEventListener('blur', function () {
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
  var val = this.value.trim();
  if (val === '') {
    limpiarCamposPaciente();
    deshabilitarCamposPaciente(true);
    habilitarSelects(true);
    var s = el('cirujano');
    if (s) s.value = '';
  }
});

el('obraSocial').addEventListener('change', actualizarLabelObraSocial);
