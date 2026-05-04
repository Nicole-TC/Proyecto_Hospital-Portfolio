  <!-- Contenido principal -->
  <main class="p-4">
    <div class="card shadow bg-white">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0 fw-bold">Admisión de Pacientes</h3>
      </div>
      <div class="card-body">
        <h5 class="mb-3">Formulario de Ingreso</h5>
        <p class="text-muted">Ingreso de pacientes al circuito administrativo quirúrgico</p>

        <form>
          <!-- Nombre y Apellido -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="nombre" class="form-label">Nombre*</label>
              <input type="text" class="form-control" id="nombre" placeholder="Ej: María" required>
            </div>
            <div class="col-md-6">
              <label for="apellido" class="form-label">Apellido*</label>
              <input type="text" class="form-control" id="apellido" placeholder="Ej: García" required>
            </div>
          </div>

          <!-- DNI y Fecha de nacimiento -->
          <div class="row mb-3">
           <div class="col-md-6">
             <label for="dni" class="form-label">DNI*</label>
             <input type="text" class="form-control" id="dni" placeholder="Ej: 34.567.890" required>
           </div>
           <div class="col-md-6">
             <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento*</label>
             <input type="date" class="form-control" id="fechaNacimiento" required>
           </div>
           </div>

          <!-- Dirección -->
          <div class="mb-3">
            <label for="direccion" class="form-label">Dirección*</label>
            <input type="text" class="form-control" id="direccion" placeholder="Ej: Av. Siempre Viva 742" required>
          </div>

          <!-- Código Postal y Email -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="codigoPostal" class="form-label">Código Postal*</label>
              <input type="text" class="form-control" id="codigoPostal" placeholder="Ej: 1406" required>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label">Email*</label>
              <input type="email" class="form-control" id="email" placeholder="Ej: paciente@mail.com" required>
            </div>
          </div>

          <!-- Teléfono -->
          <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono*</label>
            <input type="text" class="form-control" id="telefono" placeholder="Ej: 11-2345-6789" required>
          </div>

          <!-- Tipo de cirugía -->
          <div class="mb-3">
            <label for="tipoCirugia" class="form-label fw-bolder fs-4 text-dark" style="border-bottom:2px solid #000;">
              
            </label>
            <select class="select-boton" id="tipoCirugia" required>
              <option value="">Seleccione tipo de cirugía</option>
              <option>Cardíaca</option>
              <option>Ortopédica</option>
              <option>Neurológica</option>
              <option>General</option>
            </select>
          </div>

          <div class="alert alert-info mt-3">
            <strong>Importante:</strong> Al confirmar el ingreso, el paciente será registrado en el sistema y se generarán automáticamente las órdenes para todas las postas del circuito quirúrgico (Laboratorio, Cardiología, Imágenes y Pre-Anestesia).
          </div>

          <button type="submit" class="btn btn-primary">Confirmar Ingreso al Circuito</button>
        </form>
      </div>
    </div>
  </main>