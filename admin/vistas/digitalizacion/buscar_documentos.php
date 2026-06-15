<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Tarjeta de Filtros de Búsqueda -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title fw-bold text-info"><i class="fa-solid fa-magnifying-glass me-2"></i> Buscar Documentos</h3>
                    <p class="text-muted">Utilizá los filtros para encontrar documentos específicos.</p>
                </div>
                <div class="card-body p-4">
                    <form id="form-buscar-documentos">
                        <div class="row g-3">
                            <!-- Filtro por DNI -->
                            <div class="col-md-4">
                                <label for="buscar_dni" class="form-label fw-bold">DNI del Paciente</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-0" id="buscar_dni" name="dni" placeholder="Ej: 12345678">
                                </div>
                            </div>
                            
                            <!-- Filtro por Etiquetas -->
                            <div class="col-md-4">
                                <label for="buscar_tag" class="form-label fw-bold">Etiqueta (Tag)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-tags text-muted"></i></span>
                                    <select class="form-select bg-light border-0" id="buscar_tag" name="tag_id">
                                        <option value="">-- Todas las etiquetas --</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Filtro por Servicio -->
                            <div class="col-md-4">
                                <label for="buscar_servicio" class="form-label fw-bold">Servicio</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-hospital text-muted"></i></span>
                                    <select class="form-select bg-light border-0" id="buscar_servicio" name="id_servicio">
                                        <option value="">-- Todos los servicios --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <!-- Filtro por Rango de Fechas -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Subido entre (Fechas)</label>
                                <div class="input-group">
                                    <input type="date" class="form-control bg-light border-0" id="fecha_desde" name="fecha_desde" title="Fecha Desde">
                                    <span class="input-group-text bg-light border-0 px-2">y</span>
                                    <input type="date" class="form-control bg-light border-0" id="fecha_hasta" name="fecha_hasta" title="Fecha Hasta">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div id="alerta-sin-filtros" class="alert alert-warning d-none mb-0" role="alert">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    <span id="alerta-sin-filtros-texto">Ingresá al menos un filtro (DNI, etiqueta o fecha) para poder buscar.</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="button" id="btn-limpiar" class="btn btn-light rounded-pill px-4 me-2">Limpiar</button>
                                <button type="submit" class="btn btn-info text-white rounded-pill px-5 shadow-sm fw-bold">
                                    <i class="fa-solid fa-search me-2"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjeta de Resultados -->
            <div class="card shadow-sm border-0 rounded-4 mt-4" id="card-resultados" style="display: none;">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabla-busqueda" class="table table-hover table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Paciente (DNI)</th>
                                    <th>Documento</th>
                                    <th>Servicio</th>
                                    <th>Comentario</th>
                                    <th>Fecha</th>
                                    <th>Etiquetas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables llenará esto -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Editar Documento -->
<div class="modal fade" id="modalEditarDocumento" tabindex="-1" aria-labelledby="modalEditarDocumentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-info" id="modalEditarDocumentoLabel"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarDocumento" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="edit_id_documento" name="id">
                    
                    <div class="row">
                        <div class="form-group col-md-12 mb-3">
                            <label for="edit_titulo" class="form-label fw-bold">Título del Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" id="edit_titulo" name="titulo" placeholder="Ingrese el título del documento" required>
                            <div class="invalid-feedback">Por favor, ingrese el título del documento.</div>
                        </div>
                    </div>

                    <h5 class="mt-3 mb-3 border-bottom pb-2 text-info fw-bold">Datos de la Persona</h5>
                    <div class="row mb-3 align-items-end">
                        <div class="form-group col-md-4 mb-3">
                            <label for="edit_id_tipo_identificacion" class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select bg-light border-0" id="edit_id_tipo_identificacion" name="id_tipo_identificacion" required>
                            </select>
                            <div class="invalid-feedback">Seleccione un tipo.</div>
                        </div>
                        <div class="form-group col-md-5 mb-3">
                            <label for="edit_numero_identificacion" class="form-label fw-bold">Nro Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" id="edit_numero_identificacion" name="numero_identificacion" placeholder="Ingrese el Número" required>
                            <div class="invalid-feedback">Ingrese el número.</div>
                        </div>
                        <div class="form-group col-md-3 mb-3">
                            <button type="button" class="btn btn-secondary w-100" id="btnBuscarDniEdit">
                                <i class="fa-solid fa-search me-1"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div id="edit_mensaje_persona" class="small mt-2"></div>
                        </div>
                    </div>

                    <input type="hidden" id="edit_id_persona" name="id_persona" value="">

                    <div id="edit_datos_persona" class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="edit_nombre" class="form-label fw-bold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" id="edit_nombre" name="nombre" placeholder="Nombre" maxlength="20" required>
                            <div class="invalid-feedback">Por favor, ingrese el nombre.</div>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label for="edit_apellido" class="form-label fw-bold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" id="edit_apellido" name="apellido" placeholder="Apellido" maxlength="20" required>
                            <div class="invalid-feedback">Por favor, ingrese el apellido.</div>
                        </div>
                        <div class="form-group col-md-6 mb-3 d-none" id="edit_div_email">
                            <label for="edit_email" class="form-label fw-bold">Correo electrónico</label>
                            <input type="email" class="form-control bg-light border-0" id="edit_email" name="email" placeholder="ejemplo@correo.com">
                            <div class="invalid-feedback">Por favor, ingrese un correo válido (debe contener @).</div>
                        </div>
                        <div class="form-group col-md-6 mb-3 d-none" id="edit_div_telefono">
                            <label for="edit_telefono" class="form-label fw-bold">Teléfono</label>
                            <input type="tel" class="form-control bg-light border-0" id="edit_telefono" name="telefono" placeholder="Número" pattern="[0-9]{7,15}" maxlength="15">
                            <div class="invalid-feedback">Por favor, ingrese un teléfono válido (solo números, entre 7 y 15 dígitos).</div>
                        </div>
                        <div class="form-group col-md-12 mb-3 d-none" id="edit_div_genero">
                            <label for="edit_genero" class="form-label fw-bold">Género <span class="text-danger">*</span></label>
                            <select class="form-select bg-light border-0" id="edit_genero" name="genero">
                                <option value="">Seleccione</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Binario">Binario</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div class="invalid-feedback">Seleccione el género.</div>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 border-bottom pb-2 text-info fw-bold">Clasificación</h5>
                    <div class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="edit_id_tipo_documento" class="form-label fw-bold">Tipo de Documento <span class="text-danger">*</span></label>
                            <select class="form-select bg-light border-0" id="edit_id_tipo_documento" name="id_tipo_documento" required>
                                <option value="">-- Seleccionar Tipo --</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un tipo de documento.</div>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label for="edit_id_servicio" class="form-label fw-bold">Sector / Servicio <span class="text-danger">*</span></label>
                            <select class="form-select bg-light border-0" id="edit_id_servicio" name="id_servicio" required>
                                <option value="">-- Seleccionar Sector --</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un sector o servicio.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12 mb-3">
                            <label class="form-label fw-bold">Etiquetas</label>
                            <div id="edit_tags_container" class="border rounded p-3 bg-light" style="max-height: 150px; overflow-y: auto;">
                                <!-- Se llenará dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12 mb-3">
                            <label for="edit_descripcion" class="form-label fw-bold">Descripción / Comentario</label>
                            <textarea class="form-control bg-light border-0" id="edit_descripcion" name="descripcion" rows="3" placeholder="Añada una descripción o comentario..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-5 shadow-sm fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rounded-4 { border-radius: 1rem !important; }
.bg-light { background-color: #f8f9fa !important; }
.table-hover tbody tr:hover { background-color: #f8f9fa; }

/* === Modo Oscuro === */
body.modo-oscuro .bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .card-header.bg-white {
    background-color: transparent !important;
}
body.modo-oscuro .form-control,
body.modo-oscuro .form-select {
    background-color: #2a2a2a !important;
    color: #fff !important;
}
body.modo-oscuro .form-control.border-0,
body.modo-oscuro .form-select.border-0,
body.modo-oscuro .input-group-text.border-0 {
    border: 1px solid #444 !important;
}
body.modo-oscuro .form-control:focus,
body.modo-oscuro .form-select:focus {
    background-color: #333 !important;
    color: #fff !important;
    border-color: #80bdff !important;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}
body.modo-oscuro .input-group-text {
    background-color: #333 !important;
    color: #fff !important;
    border-color: #444 !important;
}
body.modo-oscuro .text-muted {
    color: #adb5bd !important;
}
body.modo-oscuro .table-hover tbody tr:hover {
    background-color: #333 !important;
    color: #fff !important;
}
body.modo-oscuro .table-light {
    background-color: #333 !important;
    color: #fff !important;
}
body.modo-oscuro ::placeholder {
    color: #888 !important;
}
body.modo-oscuro .btn-light {
    background-color: #3a3a3a !important;
    color: #fff !important;
    border-color: #444 !important;
}
body.modo-oscuro .btn-light:hover {
    background-color: #4a4a4a !important;
    color: #fff !important;
}
</style>

<!-- Librerías DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    
    // Escapa HTML para evitar inyección al pintar datos del servidor
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Convierte un texto a "Oración": primera letra en mayúscula, resto en minúscula
    function capitalizarTexto(str) {
        if (!str) return str;
        const limpio = String(str).trim();
        if (limpio.length === 0) return limpio;
        return limpio.charAt(0).toUpperCase() + limpio.slice(1).toLowerCase();
    }

    // Convierte un texto a "Título por palabra": cada palabra empieza con mayúscula
    function capitalizarPorPalabra(str) {
        if (!str) return str;
        return String(str).trim().toLowerCase()
            .split(' ')
            .map(palabra => palabra.length ? palabra.charAt(0).toUpperCase() + palabra.slice(1) : palabra)
            .join(' ');
    }

    // Parsea el comentario con formato "Título: X\nDescripción: Y"
    // y lo normaliza igual que se muestra en el formulario de Nuevo Documento.
    function formatComentario(comentario) {
        if (!comentario) return '-';
        const matchTitulo = comentario.match(/T[ií]tulo:\s*([^\n]*)/i);
        const matchDesc = comentario.match(/Descripci[oó]n:\s*([\s\S]*)/i);

        const titulo = matchTitulo ? matchTitulo[1].trim() : '';
        const descripcion = matchDesc ? matchDesc[1].trim() : '';

        if (!titulo && !descripcion) {
            return escapeHtml(comentario);
        }

        let html = '';
        if (titulo) html += `<strong>${escapeHtml(capitalizarTexto(titulo))}</strong>`;
        if (descripcion) html += `<br><small class="text-muted">${escapeHtml(capitalizarTexto(descripcion))}</small>`;
        return html || '-';
    }

    // 1. Cargar dinámicamente los selectores de Etiquetas (Tag) y Servicio
    try {
        const response = await fetch("api/digitalizar/get_opciones.php");
        const json = await response.json();
        
        if (json.status === 'success') {
            if (json.data.etiquetas) {
                const selectTag = document.getElementById("buscar_tag");
                if(selectTag) {
                    json.data.etiquetas.forEach(etiqueta => {
                        const option = document.createElement("option");
                        option.value = etiqueta.id;
                        option.textContent = etiqueta.name;
                        selectTag.appendChild(option);
                    });
                }
            }

            if (json.data.servicios) {
                const selectServicio = document.getElementById("buscar_servicio");
                if(selectServicio) {
                    json.data.servicios.forEach(servicio => {
                        const option = document.createElement("option");
                        option.value = servicio.id;
                        option.textContent = servicio.nombre;
                        selectServicio.appendChild(option);
                    });
                }
            }

            // Cargar opciones para el modal de edición
            if (json.data.tipos_identificacion) {
                const selectTipoIdent = document.getElementById("edit_id_tipo_identificacion");
                if(selectTipoIdent) {
                    json.data.tipos_identificacion.forEach(tipo => {
                        const option = document.createElement("option");
                        option.value = tipo.id;
                        option.textContent = tipo.nombre;
                        selectTipoIdent.appendChild(option);
                    });
                }
            }

            if (json.data.tipos_documentos) {
                const selectTipoDoc = document.getElementById("edit_id_tipo_documento");
                if(selectTipoDoc) {
                    json.data.tipos_documentos.forEach(tipo => {
                        const option = document.createElement("option");
                        option.value = tipo.id;
                        option.textContent = tipo.nombre;
                        selectTipoDoc.appendChild(option);
                    });
                }
            }

            if (json.data.servicios) {
                const selectServicioEdit = document.getElementById("edit_id_servicio");
                if(selectServicioEdit) {
                    json.data.servicios.forEach(servicio => {
                        const option = document.createElement("option");
                        option.value = servicio.id;
                        option.textContent = servicio.nombre;
                        selectServicioEdit.appendChild(option);
                    });
                }
            }

            if (json.data.etiquetas) {
                const editTagsContainer = document.getElementById("edit_tags_container");
                if (editTagsContainer) {
                    editTagsContainer.innerHTML = '';
                    json.data.etiquetas.forEach(etiqueta => {
                        const div = document.createElement("div");
                        div.className = "form-check form-check-inline mb-2 me-3";
                        const color = etiqueta.color_hex || '#6c757d';
                        div.innerHTML = `
                            <input class="form-check-input" type="checkbox" name="tag_id[]" id="edit_tag_${etiqueta.id}" value="${etiqueta.id}">
                            <label class="form-check-label badge text-white" for="edit_tag_${etiqueta.id}" style="background-color: ${color} !important; font-size: 0.9rem; padding: 0.4em 0.6em;">
                                ${escapeHtml(etiqueta.name)}
                            </label>
                        `;
                        editTagsContainer.appendChild(div);
                    });
                }
            }
        }
    } catch(error) {
        console.error("Error al cargar opciones (etiquetas/servicios):", error);
    }

// 2. Inicializar DataTables sin ajax
    let tablaBusqueda = $('#tabla-busqueda').DataTable({
        paging: true,
        lengthChange: true,
        searching: false,
        ordering: true,
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        columns: [
            { data: "id", visible: false },
            { 
                data: null, 
                render: function(data, type, row) { 
                    const nombre = row.paciente ? capitalizarPorPalabra(row.paciente) : 'Desconocido';
                    return `<strong>${escapeHtml(nombre)}</strong> <br><small class="text-muted">DNI: ${escapeHtml(row.dni)}</small>`; 
                } 
            },
            { 
                data: "tipo_documento", 
                defaultContent: "Sin Tipo",
                render: function(data, type, row) {
                    if (type !== 'display') return data || '';
                    return data ? escapeHtml(capitalizarTexto(data)) : 'Sin Tipo';
                }
            },
            { 
                data: "servicio", 
                defaultContent: '<span class="text-muted">Sin servicio</span>',
                render: function(data, type, row) {
                    if (type !== 'display') return data || '';
                    return data ? escapeHtml(capitalizarTexto(data)) : '<span class="text-muted">Sin servicio</span>';
                }
            },
            { 
                data: "comentario",
                render: function(data, type, row) {
                    if (type !== 'display') return data || '';
                    return formatComentario(data);
                }
            },
            { 
                data: "fecha_subido",
                render: function(data, type, row) {
                    if (type === 'display') {
                        if (!data) return '-';
                        const [fecha, hora] = data.split(' ');
                        const partes = fecha.split('-');
                        if (partes.length !== 3) return data;
                        const [anio, mes, dia] = partes;
                        let resultado = `${dia}/${mes}/${anio}`;
                        if (hora) {
                            const [hh, mm] = hora.split(':');
                            resultado += ` ${hh}:${mm}`;
                        }
                        return resultado;
                    }
                    return data; // valor ISO original para ordenar/filtrar
                }
            },
            { 
                data: "etiquetas",
                render: function(data, type, row) {
                    if (type !== 'display') return data || '';
                    if (!data) return '<span class="badge bg-secondary">Sin etiquetas</span>';
                    return data.split(', ').map(tag => `<span class="badge bg-info text-white me-1">${escapeHtml(capitalizarTexto(tag))}</span>`).join('');
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `<a href="${row.ruta_archivo}" target="_blank" class="btn btn-sm btn-info text-white me-1"><i class="fa-solid fa-eye"></i> Ver</a>` +
                           `<button type="button" class="btn btn-sm btn-warning text-white btn-editar" data-id="${row.id}"><i class="fa-solid fa-pen-to-square"></i> Editar</button>`;
                }
            }
        ]
    });

    // Función que llama a la API solo cuando el usuario busca
    function ejecutarBusqueda() {
        const params = new URLSearchParams();
        const dni = $('#buscar_dni').val();
        const tag = $('#buscar_tag').val();
        const servicio = $('#buscar_servicio').val();
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();

        if (dni) params.append('dni', dni);
        if (tag) params.append('tag_id', tag);
        if (servicio) params.append('id_servicio', servicio);
        if (desde) params.append('fecha_inicio', desde);
        if (hasta) params.append('fecha_fin', hasta);

        fetch('api/digitalizar/buscar_documentos.php?' + params.toString())
            .then(r => r.json())
            .then(json => {
                tablaBusqueda.clear();
                if (json.status === 'success') {
                    tablaBusqueda.rows.add(json.data.documentos).draw();
                } else {
                    alert('Error: ' + json.message);
                }
            })
            .catch(err => console.error('Error en búsqueda:', err));
    }

    // 3. Programar el Botón Limpiar
    const btnLimpiar = document.getElementById("btn-limpiar");
    if(btnLimpiar) {
        btnLimpiar.addEventListener("click", function() {
            document.getElementById("form-buscar-documentos").reset();
            const cardRes = document.getElementById("card-resultados");
            if(cardRes) cardRes.style.display = "none";
            tablaBusqueda.clear().draw();
        });
    }

    // 4. Programar el Botón Buscar
    $('#form-buscar-documentos').on('submit', function(e) {
        e.preventDefault();

        const dni = $('#buscar_dni').val().trim();
        const tag = $('#buscar_tag').val();
        const servicio = $('#buscar_servicio').val();
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();

        const alerta = document.getElementById("alerta-sin-filtros");
        const alertaTexto = document.getElementById("alerta-sin-filtros-texto");

        if (!dni && !tag && !servicio && !desde && !hasta) {
            alertaTexto.textContent = "Ingresá al menos un filtro (DNI, etiqueta, servicio o fecha) para poder buscar.";
            alerta.classList.remove("d-none");
            document.getElementById("card-resultados").style.display = "none";
            return;
        }

        // Validar que la fecha "desde" no sea posterior a la fecha "hasta"
        if (desde && hasta && desde > hasta) {
            alertaTexto.textContent = "La fecha 'desde' no puede ser posterior a la fecha 'hasta'. Revisá el rango de fechas.";
            alerta.classList.remove("d-none");
            document.getElementById("card-resultados").style.display = "none";
            return;
        }

        alerta.classList.add("d-none");
        const cardRes = document.getElementById("card-resultados");
        if(cardRes) cardRes.style.display = "block";
        ejecutarBusqueda();
    });

    // 5. Lógica del modal de Edición
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarDocumento'));

    // Evento click para abrir modal
    $('#tabla-busqueda').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        const rowData = tablaBusqueda.row($(this).parents('tr')).data();
        if (rowData) {
            abrirModalEditar(rowData);
        }
    });

    function abrirModalEditar(row) {
        const form = document.getElementById("formEditarDocumento");
        form.classList.remove('was-validated');
        form.reset();
        document.getElementById("edit_mensaje_persona").innerHTML = '';
        
        // Cargar campos básicos
        document.getElementById("edit_id_documento").value = row.id;
        
        // Parsear comentario
        let commentText = row.comentario || '';
        let titulo = '';
        let descripcion = commentText;

        const matchTitulo = commentText.match(/T[ií]tulo:\s*([^\n]*)/i);
        const matchDesc = commentText.match(/Descripci[oó]n:\s*([\s\S]*)/i);

        if (matchTitulo) {
            titulo = matchTitulo[1].trim();
            if (matchDesc) {
                descripcion = matchDesc[1].trim();
            } else {
                descripcion = commentText.replace(/T[ií]tulo:\s*[^\n]*/i, '').trim();
            }
        }
        
        document.getElementById("edit_titulo").value = titulo;
        document.getElementById("edit_descripcion").value = descripcion;
        
        // Cargar paciente
        document.getElementById("edit_id_tipo_identificacion").value = 1; // DNI por defecto
        document.getElementById("edit_numero_identificacion").value = row.dni || '';
        document.getElementById("edit_id_persona").value = row.id_persona || '';
        
        document.getElementById("edit_nombre").value = row.paciente_nombre || '';
        document.getElementById("edit_apellido").value = row.paciente_apellido || '';
        
        if (row.id_persona) {
            document.getElementById("edit_nombre").readOnly = true;
            document.getElementById("edit_apellido").readOnly = true;
            document.getElementById("edit_mensaje_persona").innerHTML = '<span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i>Personal encontrado en la base de datos.</span>';
        } else {
            document.getElementById("edit_nombre").readOnly = false;
            document.getElementById("edit_apellido").readOnly = false;
        }

        // Cargar clasificación
        document.getElementById("edit_id_tipo_documento").value = row.id_tipo_documento || '';
        document.getElementById("edit_id_servicio").value = row.id_servicio || '';

        // Reset check status of all tag checkboxes
        const tagCheckboxes = document.querySelectorAll('#edit_tags_container input[type="checkbox"]');
        tagCheckboxes.forEach(cb => cb.checked = false);

        if (row.etiqueta_ids) {
            const ids = row.etiqueta_ids.split(',').map(id => id.trim());
            ids.forEach(id => {
                const cb = document.getElementById(`edit_tag_${id}`);
                if (cb) {
                    cb.checked = true;
                }
            });
        }

        modalEditar.show();
    }

    // Buscar Persona en Modal de Edición
    document.getElementById("btnBuscarDniEdit").addEventListener("click", async function() {
        const tipo_identificacion = document.getElementById("edit_id_tipo_identificacion").value;
        const numero_identificacion = document.getElementById("edit_numero_identificacion").value;
        
        if(!tipo_identificacion) {
            Swal.fire({icon: 'warning', title: 'Atención', text: 'Por favor seleccione un Tipo de Identificación.'});
            return;
        }
        if(!numero_identificacion) {
            Swal.fire({icon: 'warning', title: 'Atención', text: 'Por favor ingrese el número de documento primero.'});
            return;
        }

        const btnBuscar = this;
        const originalText = btnBuscar.innerHTML;
        btnBuscar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        btnBuscar.disabled = true;

        try {
            const urlParams = new URLSearchParams({
                tipo: tipo_identificacion,
                dni: numero_identificacion
            });
            const response = await fetch(`api/digitalizar/buscar_persona.php?${urlParams.toString()}`);
            const data = await response.json();

            const inputNombre = document.getElementById("edit_nombre");
            const inputApellido = document.getElementById("edit_apellido");
            const idPersona = document.getElementById("edit_id_persona");
            const divEmail = document.getElementById("edit_div_email");
            const divTelefono = document.getElementById("edit_div_telefono");
            const mensajePersona = document.getElementById("edit_mensaje_persona");

            if(data.status === 'success') {
                inputNombre.value = data.data.nombre;
                inputApellido.value = data.data.apellido;
                idPersona.value = data.data.id;
                
                inputNombre.readOnly = true;
                inputApellido.readOnly = true;
                
                divEmail.classList.add("d-none");
                divTelefono.classList.add("d-none");
                
                mensajePersona.innerHTML = '<span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i>Personal encontrado en la base de datos.</span>';
            } else {
                inputNombre.value = "";
                inputApellido.value = "";
                idPersona.value = "";
                inputNombre.readOnly = false;
                inputApellido.readOnly = false;
                
                divEmail.classList.remove("d-none");
                divTelefono.classList.remove("d-none");
                
                mensajePersona.innerHTML = '<span class="text-warning text-dark fw-bold"><i class="fa-solid fa-info-circle me-1"></i>No encontrado. Ingrese los datos manualmente.</span>';
            }
        } catch(error) {
            console.error(error);
            Swal.fire({icon: 'error', title: 'Error', text: 'Error al buscar la persona.'});
        } finally {
            btnBuscar.innerHTML = originalText;
            btnBuscar.disabled = false;
        }
    });

    // Auto-búsqueda DNI al escribir en modal
    document.getElementById('edit_numero_identificacion').addEventListener('input', function(e) {
        const tipoSelected = document.getElementById('edit_id_tipo_identificacion');
        const tipoText = tipoSelected.options[tipoSelected.selectedIndex]?.text.toUpperCase();
        if (tipoText === 'DNI' || tipoText === 'LC' || tipoText === 'LE') {
            this.value = this.value.replace(/[^0-9]/g, '');
        } else {
            this.value = this.value.toUpperCase();
        }

        clearTimeout(window.editDniTimeout);
        if (this.value.length >= 7) {
            window.editDniTimeout = setTimeout(() => {
                document.getElementById("btnBuscarDniEdit").click();
            }, 500);
        }
    });

    document.getElementById('edit_id_tipo_identificacion').addEventListener('change', function(e) {
        const tipoSelected = this.options[this.selectedIndex]?.text.toUpperCase();
        document.getElementById('edit_numero_identificacion').value = '';
        document.getElementById('edit_numero_identificacion').focus();

        const divGenero = document.getElementById('edit_div_genero');
        const selectGenero = document.getElementById('edit_genero');
        
        if (tipoSelected === 'LC' || tipoSelected === 'LE') {
            divGenero.classList.remove('d-none');
            selectGenero.setAttribute('required', 'required');
        } else {
            divGenero.classList.add('d-none');
            selectGenero.removeAttribute('required');
            selectGenero.value = '';
        }
    });

    // Envío del formulario de edición
    document.getElementById("formEditarDocumento").addEventListener("submit", async function(e) {
        e.preventDefault();
        
        const form = this;
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            Swal.fire({icon: 'error', title: 'Faltan datos', text: 'Por favor, complete correctamente los campos marcados en rojo.'});
            return;
        }

        const formData = new FormData(form);
        const btnSubmit = form.querySelector('button[type="submit"]');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';
        btnSubmit.disabled = true;

        try {
            const response = await fetch("api/digitalizar/editar_documento.php", {
                method: "POST",
                body: formData
            });

            const data = await response.json();

            if (data.status === 'success') {
                modalEditar.hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'El documento se actualizó correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                ejecutarBusqueda();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Guardar',
                    text: data.message || 'Error al procesar el documento.',
                    confirmButtonText: 'Cerrar'
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'Ocurrió un error al intentar comunicarse con el servidor.',
                confirmButtonText: 'Cerrar'
            });
        } finally {
            btnSubmit.innerHTML = originalText;
            btnSubmit.disabled = false;
        }
    });
});
</script>
