const ENDPOINT = 'app/cir-api/endpoints/admision-e.php';

const camposPaciente = [
  'apellido', 'nombre', 'fechaNacimiento',
  'direccion', 'codigoPostal', 'email', 'telefono'
];

const inputDni = document.getElementById('dni');
const selectCirugia = document.getElementById('tipoCirugia');
const selectCirujano = document.getElementById('tipoCirujano');
const form = document.getElementById('form-admision');

function deshabilitarCamposPaciente(disabled) {
  camposPaciente.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.disabled = disabled;
  });
}

function limpiarCamposPaciente() {
  camposPaciente.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      if (el.type === 'date') el.value = '';
      else el.value = '';
    }
  });
}

function habilitarCirugia(disabled) {
  selectCirugia.disabled = disabled;
}

function habilitarCirujano(disabled) {
  selectCirujano.disabled = disabled;
}

function reiniciarFormulario() {
  limpiarCamposPaciente();
  inputDni.value = '';
  selectCirugia.value = '';
  selectCirujano.value = '';
  deshabilitarCamposPaciente(true);
  habilitarCirugia(true);
  habilitarCirujano(true);
}

function buscarPaciente(dni) {
  if (!dni || dni.length < 8) {
    limpiarCamposPaciente();
    deshabilitarCamposPaciente(true);
    habilitarCirugia(true);
    habilitarCirujano(true);
    return;
  }

  fetch(ENDPOINT + '?action=buscar_dni&dni=' + encodeURIComponent(dni))
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.ok && data.encontrado && data.paciente) {
        var p = data.paciente;
        document.getElementById('apellido').value = p.apellido || '';
        document.getElementById('nombre').value = p.nombre || '';
        document.getElementById('fechaNacimiento').value = p.fecha_nacimiento || '';
        document.getElementById('direccion').value = p.direccion || '';
        document.getElementById('codigoPostal').value = p.codigo_postal || '';
        document.getElementById('email').value = p.email || '';
        document.getElementById('telefono').value = p.telefono || '';
        deshabilitarCamposPaciente(true);
        habilitarCirugia(false);
        habilitarCirujano(false);
      } else {
        limpiarCamposPaciente();
        deshabilitarCamposPaciente(false);
        habilitarCirugia(false);
        habilitarCirujano(false);
      }
    })
    .catch(function() {
      limpiarCamposPaciente();
      deshabilitarCamposPaciente(true);
      habilitarCirugia(true);
      habilitarCirujano(true);
    });
}

function serializarFormulario() {
  var datos = {};
  datos.dni = inputDni.value.trim();
  camposPaciente.forEach(function(id) {
    datos[id] = document.getElementById(id).value;
  });
  datos.tipoCirugia = selectCirugia.value;
  datos.tipoCirujano = selectCirujano.value;
  return datos;
}

form.addEventListener('submit', function(e) {
  e.preventDefault();

  var datos = serializarFormulario();

  fetch(ENDPOINT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datos)
  })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.ok) {
        Swal.fire({
          icon: 'success',
          title: 'Operación exitosa',
          text: data.mensaje,
          confirmButtonColor: '#3085d6'
        });
        reiniciarFormulario();
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
    .catch(function() {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.',
        confirmButtonColor: '#d33'
      });
    });
});

inputDni.addEventListener('blur', function() {
  buscarPaciente(this.value.trim());
});

inputDni.addEventListener('input', function() {
  var val = this.value.trim();
  if (val === '') {
    limpiarCamposPaciente();
    deshabilitarCamposPaciente(true);
    habilitarCirugia(true);
    habilitarCirujano(true);
  }
});