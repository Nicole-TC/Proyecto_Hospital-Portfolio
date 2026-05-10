<!-- El formulario obtendrá sus opciones dinámicamente vía API (JSON) -->

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-cloud-arrow-up text-primary" style="font-size: 3rem;"></i>
                        <h3 class="card-title fw-bold mt-3 mb-2">Cargar Nuevo Documento</h3>
                        <p class="card-text text-muted">Sube un nuevo documento al sistema de digitalización.</p>
                        <p class="card-text text-muted">Asigna sus datos y etiquetas correspondientes.</p>
                    </div>

                    <form id="formDocumento" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-bold"><i class="fa-solid fa-pen me-2 text-muted"></i>Título</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" id="titulo" name="titulo" placeholder="Ingrese el título del documento" required>
                        </div>
                        
                        <!-- Persona Section -->
                        <div class="card bg-light border-0 mb-4 rounded-4 p-3 shadow-sm">
                            <h5 class="fw-bold text-muted mb-3"><i class="fa-solid fa-user me-2"></i>Datos de la Persona</h5>
                            <div class="row align-items-end mb-3">
                                <div class="col-md-8">
                                    <label for="dni" class="form-label fw-bold">DNI</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                                        <input type="number" class="form-control form-control-lg border-0" id="dni" name="dni" placeholder="Ingrese el DNI" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0 d-grid">
                                    <button type="button" class="btn btn-secondary btn-lg rounded-pill shadow-sm" id="btnBuscarDni">
                                        <i class="fa-solid fa-search me-2"></i>Buscar
                                    </button>
                                </div>
                            </div>
                            
                            <div id="datos_persona" class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre</label>
                                    <input type="text" class="form-control border-0 shadow-sm" id="nombre" name="nombre" placeholder="Nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label fw-bold">Apellido</label>
                                    <input type="text" class="form-control border-0 shadow-sm" id="apellido" name="apellido" placeholder="Apellido" required>
                                </div>
                                <div class="col-md-6 mb-3 d-none" id="div_email">
                                    <label for="email" class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control border-0 shadow-sm" id="email" name="email" placeholder="Opcional">
                                </div>
                                <div class="col-md-6 mb-3 d-none" id="div_telefono">
                                    <label for="telefono" class="form-label fw-bold">Teléfono de Contacto</label>
                                    <input type="text" class="form-control border-0 shadow-sm" id="telefono" name="telefono" placeholder="Opcional">
                                </div>
                            </div>
                            <div id="mensaje_persona" class="small mt-1"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="id_tipo_documento" class="form-label fw-bold"><i class="fa-solid fa-file-lines me-2 text-muted"></i>Tipo de Documento</label>
                                <select class="form-select form-select-lg bg-light border-0" id="id_tipo_documento" name="id_tipo_documento" required>
                                    <option value="">-- Seleccionar Tipo --</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="tag_id" class="form-label fw-bold"><i class="fa-solid fa-tags me-2 text-muted"></i>Etiquetas <span class="text-muted fw-normal fs-6">(Ctrl+Click para varias)</span></label>
                                <select class="form-select form-select-lg bg-light border-0" id="tag_id" name="tag_id[]" multiple style="min-height: 120px;">
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-bold"><i class="fa-solid fa-align-left me-2 text-muted"></i>Descripción</label>
                            <textarea class="form-control form-control-lg" id="descripcion" name="descripcion" rows="3" placeholder="Añada una descripción o comentario..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="archivo" class="form-label fw-bold"><i class="fa-solid fa-file-arrow-up me-2 text-muted"></i>Archivo</label>
                            <input type="file" class="form-control form-control-lg bg-light border-0" id="archivo" name="archivo" required>
                        </div>

                        <div class="d-grid mt-4 pt-2">
                            <button type="submit" class="btn btn-primary btn-lg text-white rounded-pill shadow-sm">
                                <i class="fa-solid fa-upload me-2"></i> Subir Documento
                            </button>
                        </div>
                    </form>

                    <div id="respuesta" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rounded-4 { border-radius: 1rem !important; }
.bg-light { background-color: #f8f9fa !important; }
</style>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    try {
        const response = await fetch("api/digitalizar/get_opciones.php");
        const json = await response.json();
        
        if (json.status === 'success') {
            const selectTipo = document.getElementById("id_tipo_documento");
            json.data.tipos_documentos.forEach(tipo => {
                const option = document.createElement("option");
                option.value = tipo.id;
                option.textContent = tipo.nombre;
                selectTipo.appendChild(option);
            });
            
            const selectTag = document.getElementById("tag_id");
            json.data.etiquetas.forEach(etiqueta => {
                const option = document.createElement("option");
                option.value = etiqueta.id;
                option.textContent = etiqueta.name;
                selectTag.appendChild(option);
            });
        }
    } catch(error) {
        console.error("Error al cargar las opciones del formulario:", error);
    }
});

document.getElementById("btnBuscarDni").addEventListener("click", async function() {
    const dni = document.getElementById("dni").value;
    if(!dni) {
        alert("Por favor ingrese un DNI primero.");
        return;
    }

    const btnBuscar = this;
    const originalText = btnBuscar.innerHTML;
    btnBuscar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Buscando...';
    btnBuscar.disabled = true;

    try {
        const response = await fetch(`api/digitalizar/buscar_persona.php?dni=${dni}`);
        const data = await response.json();

        const inputNombre = document.getElementById("nombre");
        const inputApellido = document.getElementById("apellido");
        const divEmail = document.getElementById("div_email");
        const divTelefono = document.getElementById("div_telefono");
        const mensajePersona = document.getElementById("mensaje_persona");

        if(data.status === 'success') {
            inputNombre.value = data.data.nombre;
            inputApellido.value = data.data.apellido;
            
            // Bloqueamos los campos si se encuentra en la BD
            inputNombre.readOnly = true;
            inputApellido.readOnly = true;
            
            // Ocultar campos adicionales
            divEmail.classList.add("d-none");
            divTelefono.classList.add("d-none");
            
            mensajePersona.innerHTML = '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Personal encontrado.</span>';
        } else {
            // Permitimos editar
            inputNombre.value = "";
            inputApellido.value = "";
            inputNombre.readOnly = false;
            inputApellido.readOnly = false;
            
            // Mostrar campos adicionales
            divEmail.classList.remove("d-none");
            divTelefono.classList.remove("d-none");
            
            mensajePersona.innerHTML = '<span class="text-warning text-dark" style="font-weight: 500;"><i class="fa-solid fa-info-circle me-1"></i>No encontrado. Si es paciente, ingrese los datos manualmente.</span>';
        }
    } catch(error) {
        console.error(error);
        alert("Error al buscar la persona.");
    } finally {
        btnBuscar.innerHTML = originalText;
        btnBuscar.disabled = false;
    }
});

document.getElementById("formDocumento").addEventListener("submit", async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const btnSubmit = form.querySelector('button[type="submit"]');
    const originalBtnText = btnSubmit.innerHTML;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Subiendo...';
    btnSubmit.disabled = true;

    try {
        // Enviar datos al endpoint real
        const response = await fetch("api/digitalizar/upload_dig.php", {
            method: "POST",
            body: formData
        });

        // Intentamos parsear JSON asumiendo que el endpoint responde en JSON
        const data = await response.json().catch(() => ({ status: 'error', message: 'Respuesta no válida del servidor.' }));

        if(data.status === 'ok') {
            document.getElementById("respuesta").innerHTML = `
                <div class="alert alert-success rounded-3 shadow-sm border-0 alert-dismissible fade show">
                    <i class="fa-solid fa-circle-check me-2"></i> ${data.message || 'Documento subido correctamente.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            form.reset();
        } else {
            document.getElementById("respuesta").innerHTML = `
                <div class="alert alert-danger rounded-3 shadow-sm border-0 alert-dismissible fade show">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> ${data.message || 'Error al procesar el documento.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

    } catch (error) {
        document.getElementById("respuesta").innerHTML = `
            <div class="alert alert-danger rounded-3 shadow-sm border-0 alert-dismissible fade show">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> Error de conexión o endpoint no configurado.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    } finally {
        btnSubmit.innerHTML = originalBtnText;
        btnSubmit.disabled = false;
    }
});
</script>