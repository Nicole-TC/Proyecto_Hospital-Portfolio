  <!-- Contenido principal -->
  <main class="p-4">
    <div class="card shadow bg-white">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0 fw-bold">Admisión de Pacientes</h3>
      </div>
      <div class="card-body">
        <h5 class="mb-3">Formulario de Ingreso</h5>
        <p class="text-muted">Ingreso de pacientes al circuito administrativo quirúrgico</p>

        <form id="form-admision">
          <!-- DNI y Apellido -->
          <div class="row mb-3">
           <div class="col-md-6">
              <label for="dni" class="form-label">DNI*</label>
              <input type="text" class="form-control" id="dni" name="dni" placeholder="Ej: 34.567.890" required>
            </div>
             <div class="col-md-6">
               <label for="apellido" class="form-label">Apellido*</label>
               <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ej: García" required disabled>
             </div>
           </div>
           
           <!-- Nombre y Fecha de nacimiento -->
           <div class="row mb-3">
             <div class="col-md-6">
               <label for="nombre" class="form-label">Nombre*</label>
               <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: María" required disabled>
             </div>
            <div class="col-md-6">
              <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento*</label>
              <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" required disabled>
            </div>
            </div>

          <!-- Dirección -->
          <div class="mb-3">
            <label for="direccion" class="form-label">Dirección*</label>
            <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Ej: Av. Siempre Viva 742" required disabled>
          </div>

          <!-- Código Postal y Email -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="codigoPostal" class="form-label">Código Postal*</label>
              <input type="text" class="form-control" id="codigoPostal" name="codigoPostal" placeholder="Ej: 1406" required disabled>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label">Email*</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Ej: paciente@mail.com" required disabled>
            </div>
          </div>

          <!-- Teléfono -->
          <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono*</label>
            <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Ej: 11-2345-6789" required disabled>
          </div>

          <!-- Tipo de cirugía y Tipo de cirujano -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="tipoCirugia" class="form-label fw-bolder fs-5 text-dark border-bottom border-2 border-dark pb-1 w-100 d-block">
                Tipo de cirugía
              </label>
              <select class="form-select select-boton" id="tipoCirugia" name="tipoCirugia" required disabled>
                <option value="">Seleccione tipo de cirugía</option>
                <option>Cardíaca</option>
                <option>Ortopédica</option>
                <option>Neurológica</option>
                <option>General</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="tipoCirujano" class="form-label fw-bolder fs-5 text-dark border-bottom border-2 border-dark pb-1 w-100 d-block">
                Tipo de cirujano
              </label>
              <select class="form-select select-boton" id="tipoCirujano" name="tipoCirujano" required disabled>
                <option value="">Seleccione tipo de cirujano</option>
                <option>Cirujano General</option>
                <option>Cirujano Cardiovascular</option>
                <option>Neurocirujano</option>
              </select>
            </div>
          </div>

          <div class="alert alert-info mt-3">
            <strong>Importante:</strong> Al confirmar el ingreso, el paciente será registrado en el sistema y se generarán automáticamente las órdenes para todas las postas del circuito quirúrgico (Laboratorio, Cardiología, Imágenes y Pre-Anestesia).
          </div>

          <button type="submit" class="btn btn-primary">Confirmar Ingreso al Circuito</button>
        </form>
      </div>
    </div>
  </main>

<script>
const ENDPOINT = 'app/circuitoquirurgico/admision-b.php';

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
</script>