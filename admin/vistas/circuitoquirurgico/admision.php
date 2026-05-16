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
              <input type="text" class="form-control" id="codigoPostal" name="codigoPostal" placeholder="Ej: 1406A" required disabled>
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

          <!-- Tipo de cirugía y Cirujano -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="tipoCirugia" class="form-label fw-bolder fs-5 text-dark border-bottom border-2 border-dark pb-1 w-100 d-block">
                Tipo de cirugía
              </label>
              <select class="form-select select-boton" id="tipoCirugia" name="tipoCirugia" required disabled>
                <option value="">Seleccione tipo de cirugía</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="cirujano" class="form-label fw-bolder fs-5 text-dark border-bottom border-2 border-dark pb-1 w-100 d-block">
                Cirujano
              </label>
              <select class="form-select select-boton" id="cirujano" name="cirujano" required disabled>
                <option value="">Seleccione cirujano</option>
              </select>
            </div>
          </div>

          <!-- Estudios asociados -->
          <div class="row mb-3" id="contenedorEstudios" style="display:none;">
            <div class="col-md-12">
              <label class="form-label fw-bolder fs-5 text-dark border-bottom border-2 border-dark pb-1 w-100 d-block">
                Estudios requeridos
              </label>
              <div id="listaEstudios" class="d-flex flex-wrap gap-2"></div>
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

<script src="js/circuitoquirurgico/admision.js"></script>