<!-- El formulario obtendrá sus opciones dinámicamente vía API (JSON) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid mt-4 mb-5">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Cargar Nuevo Documento</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Ingrese los datos del documento a digitalizar:</p>

                    <form id="formDocumento" enctype="multipart/form-data">
                        
                        <div class="row">
                            <div class="form-group col-md-12 mb-3">
                                <label for="titulo">Título del Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ingrese el título del documento" required>
                            </div>
                        </div>

                        <h5 class="mt-3 mb-3 border-bottom pb-2 text-primary">Datos de la Persona</h5>
                        <div class="row align-items-end mb-3">
                            <div class="form-group col-md-3 mb-3">
                                <label for="id_tipo_identificacion">Tipo <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_tipo_identificacion" name="id_tipo_identificacion" required>
                                </select>
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="numero_identificacion">Nro Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="numero_identificacion" name="numero_identificacion" placeholder="Ingrese el Número" required>
                            </div>
                            <div class="form-group col-md-2 mb-3 d-none" id="div_genero">
                                <label for="genero">Género <span class="text-danger">*</span></label>
                                <select class="form-control" id="genero" name="genero">
                                    <option value="">Seleccione</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Binario">Binario</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2 mb-3">
                                <button type="button" class="btn btn-secondary w-100" id="btnBuscarDni">
                                    <i class="fa-solid fa-search me-1"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div id="mensaje_persona" class="small mt-2"></div>
                            </div>
                        </div>


                        <input type="hidden" id="id_persona" name="id_persona" value="">

                        <div id="datos_persona" class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="nombre">Nombres <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" maxlength="20" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="apellido">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" maxlength="20" required>
                            </div>
                            <div class="form-group col-md-6 mb-3 d-none" id="div_email">
                                <label for="email">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com">
                            </div>
                            <div class="form-group col-md-6 mb-3 d-none" id="div_telefono">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Número de celular sin símbolos">
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Clasificación</h5>
                        <div class="row">
                            <div class="form-group col-md-12 mb-3">
                                <label for="id_tipo_documento">Tipo de Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_tipo_documento" name="id_tipo_documento" required>
                                    <option value="">-- Seleccionar Tipo --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="id_servicio">Sector / Servicio <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_servicio" name="id_servicio" required>
                                    <option value="">-- Seleccionar Sector --</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="prioridad">Prioridad <span class="text-danger">*</span></label>
                                <select class="form-control" id="prioridad" name="prioridad" required>
                                    <option value="Baja">Baja</option>
                                    <option value="Media" selected>Media</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Urgente">Urgente</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="estado_documento">Estado <span class="text-danger">*</span></label>
                                <select class="form-control" id="estado_documento" name="estado_documento" required>
                                    <option value="Activo" selected>Activo</option>
                                    <option value="En Revisión">En Revisión</option>
                                    <option value="Archivado">Archivado</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="tag_id">Etiquetas</label>
                                <select class="form-control" id="tag_id" name="tag_id[]" multiple>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12 mb-3">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Añada una descripción o comentario..."></textarea>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Archivo Adjunto</h5>
                        <div class="row">
                            <div class="form-group col-md-12 mb-3">
                                <label for="archivo">Seleccionar Archivo <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted d-block mt-1">Formatos permitidos: PDF, JPG, PNG. Tamaño máximo: 5MB</small>
                            </div>
                        </div>

                        <!-- Contenedor Vista Previa -->
                        <div id="preview-container" class="mb-4 d-none">
                            <label class="form-label fw-bold">Vista Previa</label>
                            <div class="border d-flex justify-content-center align-items-center bg-light" style="height: 400px; position: relative;">
                                <div id="preview-content" class="w-100 h-100"></div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3 text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-save me-1"></i> Guardar Documento
                            </button>
                        </div>
                    </form>

                    <div id="respuesta" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmacion -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmacionLabel">Resumen de Carga</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Por favor, confirme que los datos a guardar son correctos:</p>
        <ul class="list-group list-group-flush border mb-3">
            <li class="list-group-item"><strong>Documento de Identidad:</strong> <span id="res_identificacion"></span></li>
            <li class="list-group-item"><strong>Nombre:</strong> <span id="res_nombre"></span></li>
            <li class="list-group-item"><strong>Tipo Documento:</strong> <span id="res_tipo_doc"></span></li>
            <li class="list-group-item"><strong>Sector:</strong> <span id="res_sector"></span></li>
            <li class="list-group-item"><strong>Prioridad:</strong> <span id="res_prioridad"></span></li>
            <li class="list-group-item"><strong>Archivo:</strong> <span id="res_archivo" class="text-truncate d-inline-block" style="max-width: 200px; vertical-align: bottom;"></span></li>
        </ul>
        <div id="res_alerta" class="alert alert-warning d-none">
            <i class="fa-solid fa-triangle-exclamation me-1"></i><span id="res_alerta_msg"></span>
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-warning text-dark" id="btnForzarSubida">Forzar Subida</button>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnConfirmarSubida">Confirmar y Subir</button>
      </div>
    </div>
  </div>
</div>

<script>
let tagChoices;
let modalConfirmacion;

document.addEventListener("DOMContentLoaded", async function() {
    // Inicializar modal
    modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));

    // Inicializar Choices.js para etiquetas
    const element = document.getElementById('tag_id');
    tagChoices = new Choices(element, {
        removeItemButton: true,
        placeholderValue: 'Seleccione etiquetas',
        searchPlaceholderValue: 'Buscar...',
        noResultsText: 'No se encontraron etiquetas',
        itemSelectText: 'Click para seleccionar'
    });

    try {
        const response = await fetch("api/digitalizar/get_opciones.php");
        const json = await response.json();
        
        if (json.status === 'success') {
            const selectTipoIdentificacion = document.getElementById("id_tipo_identificacion");
            json.data.tipos_identificacion.forEach(tipo => {
                const option = document.createElement("option");
                option.value = tipo.id;
                option.textContent = tipo.nombre;
                selectTipoIdentificacion.appendChild(option);
            });

            const selectTipo = document.getElementById("id_tipo_documento");
            json.data.tipos_documentos.forEach(tipo => {
                const option = document.createElement("option");
                option.value = tipo.id;
                option.textContent = tipo.nombre;
                selectTipo.appendChild(option);
            });
            
            const selectServicio = document.getElementById("id_servicio");
            json.data.servicios.forEach(servicio => {
                const option = document.createElement("option");
                option.value = servicio.id;
                option.textContent = servicio.nombre;
                selectServicio.appendChild(option);
            });
            
            const etiquetasArr = json.data.etiquetas.map(e => ({ value: e.id, label: e.name }));
            tagChoices.setChoices(etiquetasArr, 'value', 'label', true);
        }
    } catch(error) {
        console.error("Error al cargar las opciones del formulario:", error);
    }
});

// Validación de Número de Identificación (Opcional según tipo, por ahora dejamos libre para pasaportes, etc.)
// Si es DNI podríamos agregar validación estricta, pero lo delegamos al backend o al evento onchange del select
document.getElementById('numero_identificacion').addEventListener('input', function(e) {
    const tipoSelected = document.getElementById('id_tipo_identificacion');
    const tipoText = tipoSelected.options[tipoSelected.selectedIndex]?.text.toUpperCase();
    if (tipoText === 'DNI' || tipoText === 'LC' || tipoText === 'LE') {
        this.value = this.value.replace(/[^0-9]/g, '');
    } else {
        this.value = this.value.toUpperCase(); // Para pasaportes, ID extranjero, etc.
    }
});

document.getElementById('id_tipo_identificacion').addEventListener('change', function(e) {
    const tipoSelected = this.options[this.selectedIndex]?.text.toUpperCase();
    
    document.getElementById('numero_identificacion').value = '';
    document.getElementById('numero_identificacion').focus();

    // Mostrar/ocultar género si es LC o LE
    const divGenero = document.getElementById('div_genero');
    const selectGenero = document.getElementById('genero');
    
    if (tipoSelected === 'LC' || tipoSelected === 'LE') {
        divGenero.classList.remove('d-none');
        selectGenero.setAttribute('required', 'required');
    } else {
        divGenero.classList.add('d-none');
        selectGenero.removeAttribute('required');
        selectGenero.value = '';
    }
});

// Vista previa de Archivo
document.getElementById('archivo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('preview-container');
    const previewContent = document.getElementById('preview-content');
    
    if(!file) {
        previewContainer.classList.add('d-none');
        return;
    }
    
    if(file.size > 5 * 1024 * 1024) {
        alert("El archivo excede los 5MB permitidos.");
        this.value = '';
        previewContainer.classList.add('d-none');
        return;
    }
    
    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if(!allowedExtensions.includes(fileExtension)) {
        alert("Formato no permitido. Solo se aceptan archivos PDF, JPG o PNG.");
        this.value = '';
        previewContainer.classList.add('d-none');
        return;
    }
    
    previewContainer.classList.remove('d-none');
    const fileURL = URL.createObjectURL(file);
    
    if(file.type.startsWith('image/')) {
        previewContent.innerHTML = `<img src="${fileURL}" class="w-100 h-100" style="object-fit: contain;">`;
    } else if(file.type === 'application/pdf') {
        previewContent.innerHTML = `<iframe src="${fileURL}" class="w-100 h-100" frameborder="0"></iframe>`;
    } else {
        previewContent.innerHTML = `<div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center text-muted"><i class="fa-solid fa-file-lines fa-3x mb-2"></i><span>Vista previa no disponible</span></div>`;
    }
});

// Buscar Persona
document.getElementById("btnBuscarDni").addEventListener("click", async function() {
    const tipo_identificacion = document.getElementById("id_tipo_identificacion").value;
    const numero_identificacion = document.getElementById("numero_identificacion").value;
    const genero = document.getElementById("genero").value;
    
    if(!tipo_identificacion) {
        Swal.fire({icon: 'warning', title: 'Atención', text: 'Por favor seleccione un Tipo de Identificación.'});
        return;
    }
    if(!numero_identificacion) {
        Swal.fire({icon: 'warning', title: 'Atención', text: 'Por favor ingrese el número de documento primero.'});
        return;
    }
    
    const divGenero = document.getElementById('div_genero');
    if (!divGenero.classList.contains('d-none') && !genero) {
        Swal.fire({icon: 'warning', title: 'Atención', text: 'Por favor seleccione el género.'});
        return;
    }

    const btnBuscar = this;
    const originalText = btnBuscar.innerHTML;
    btnBuscar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Buscando...';
    btnBuscar.disabled = true;

    try {
        const urlParams = new URLSearchParams({
            tipo: tipo_identificacion,
            numero: numero_identificacion,
            genero: genero
        });
        const response = await fetch(`api/digitalizar/buscar_persona.php?${urlParams.toString()}`);
        const data = await response.json();

        const inputNombre = document.getElementById("nombre");
        const inputApellido = document.getElementById("apellido");
        const idPersona = document.getElementById("id_persona");
        const divEmail = document.getElementById("div_email");
        const divTelefono = document.getElementById("div_telefono");
        const mensajePersona = document.getElementById("mensaje_persona");

        if(data.status === 'success') {
            inputNombre.value = data.data.nombre;
            inputApellido.value = data.data.apellido;
            idPersona.value = data.data.id; // Guardamos el ID
            
            inputNombre.readOnly = true;
            inputApellido.readOnly = true;
            
            divEmail.classList.add("d-none");
            divTelefono.classList.add("d-none");
            
            mensajePersona.innerHTML = '<span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i>Personal encontrado en la base de datos.</span>';
        } else {
            inputNombre.value = "";
            inputApellido.value = "";
            idPersona.value = ""; // Vaciamos el ID porque es nuevo
            inputNombre.readOnly = false;
            inputApellido.readOnly = false;
            
            divEmail.classList.remove("d-none");
            divTelefono.classList.remove("d-none");
            
            mensajePersona.innerHTML = '<span class="text-warning text-dark fw-bold"><i class="fa-solid fa-info-circle me-1"></i>No encontrado. Ingrese los datos manualmente.</span>';
        }
    } catch(error) {
        console.error(error);
        alert("Error al buscar la persona.");
    } finally {
        btnBuscar.innerHTML = originalText;
        btnBuscar.disabled = false;
    }
});

// Submit Formulario - Mostrar Resumen primero
document.getElementById("formDocumento").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const numero = document.getElementById("numero_identificacion").value;
    if(!numero) {
        Swal.fire({icon: 'warning', title: 'Falta Número de Identificación'});
        return;
    }
    
    // Rellenar resumen
    const nombre = document.getElementById("nombre").value + " " + document.getElementById("apellido").value;
    
    const selectTipoDoc = document.getElementById("id_tipo_documento");
    const tipoDoc = selectTipoDoc.options[selectTipoDoc.selectedIndex].text;
    
    const selectSector = document.getElementById("id_servicio");
    const sectorDoc = selectSector.options[selectSector.selectedIndex].text;
    
    const prioridad = document.getElementById("prioridad").value;
    const archivo = document.getElementById("archivo").files[0].name;
    
    const selectTipoIdentificacion = document.getElementById("id_tipo_identificacion");
    const tipoIdentificacionText = selectTipoIdentificacion.options[selectTipoIdentificacion.selectedIndex].text;

    document.getElementById("res_identificacion").textContent = tipoIdentificacionText + ' ' + numero;
    document.getElementById("res_nombre").textContent = nombre;
    document.getElementById("res_tipo_doc").textContent = tipoDoc;
    document.getElementById("res_sector").textContent = sectorDoc;
    document.getElementById("res_prioridad").textContent = prioridad;
    document.getElementById("res_archivo").textContent = archivo;
    
    // Ocultar alertas previas
    document.getElementById("res_alerta").classList.add("d-none");
    document.getElementById("btnConfirmarSubida").classList.remove("d-none");

    modalConfirmacion.show();
});

// Enviar realmente
let forzarSubida = false;

document.getElementById("btnForzarSubida").addEventListener("click", function() {
    forzarSubida = true;
    enviarFormulario();
});

document.getElementById("btnConfirmarSubida").addEventListener("click", function() {
    forzarSubida = false;
    enviarFormulario();
});

async function enviarFormulario() {
    const form = document.getElementById("formDocumento");
    const formData = new FormData(form);
    
    if (forzarSubida) {
        formData.append("forzar_subida", "1");
    }

    const btnConfirmar = document.getElementById("btnConfirmarSubida");
    const originalBtnText = btnConfirmar.innerHTML;
    btnConfirmar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';
    btnConfirmar.disabled = true;

    try {
        const response = await fetch("api/digitalizar/upload_dig.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json().catch(() => ({ status: 'error', message: 'Respuesta no válida del servidor.' }));

        if(data.status === 'ok' || data.status === 'success') {
            modalConfirmacion.hide();
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                html: `Documento guardado correctamente.<br>Código de Seguimiento: <strong>${data.data?.documento?.codigo_unico || ''}</strong>`,
                confirmButtonText: 'Aceptar'
            });
            form.reset();
            tagChoices.removeActiveItems();
            document.getElementById('preview-container').classList.add('d-none');
            document.getElementById("id_persona").value = "";
            document.getElementById("mensaje_persona").innerHTML = "";
            document.getElementById("respuesta").innerHTML = "";
        } else if (data.status === 'duplicate') {
            // Mostrar alerta de duplicado en el modal
            document.getElementById("res_alerta").classList.remove("d-none");
            document.getElementById("res_alerta_msg").innerHTML = data.message;
            document.getElementById("btnConfirmarSubida").classList.add("d-none");
            btnConfirmar.innerHTML = originalBtnText;
            btnConfirmar.disabled = false;
            return; // No escondemos el modal
        } else {
            modalConfirmacion.hide();
            Swal.fire({
                icon: 'error',
                title: 'Error al Guardar',
                text: data.message || 'Error al procesar el documento.',
                confirmButtonText: 'Cerrar'
            });
            document.getElementById("respuesta").innerHTML = "";
        }

    } catch (error) {
        modalConfirmacion.hide();
        Swal.fire({
            icon: 'error',
            title: 'Error de Conexión',
            text: 'Ocurrió un error al intentar comunicarse con el servidor.',
            confirmButtonText: 'Cerrar'
        });
        document.getElementById("respuesta").innerHTML = "";
    } finally {
        btnConfirmar.innerHTML = originalBtnText;
        btnConfirmar.disabled = false;
        forzarSubida = false;
    }
}
</script>
