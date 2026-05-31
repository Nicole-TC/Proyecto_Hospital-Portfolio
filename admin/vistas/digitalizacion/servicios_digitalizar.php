<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <button class="btn btn-success float-right" onclick="abrirModalNuevo()">
                <i class="fa-solid fa-plus fa-bounce white fs-3"></i>
                <span class="float-right fs-6 text-white ml-1">Nuevo Servicio</span>
            </button>
        </div>

        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-servicios" style="width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Estado</th>
                            <th scope="col" class="text-center">Nombre</th>
                            <th scope="col" class="text-center">Área</th>
                            <th scope="col" class="text-center">Responsable</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar -->
<div class="modal fade" id="modalServicio" tabindex="-1" aria-labelledby="modalServicioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formServicio">
          <div class="modal-header">
            <h5 class="modal-title" id="modalServicioLabel">Servicio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="servicio_id" name="servicio_id">
            
            <div class="mb-3">
                <label for="servicio_nombre" class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="servicio_nombre" name="nombre" required maxlength="100">
            </div>
            
            <div class="mb-3">
                <label for="servicio_area" class="form-label">Área</label>
                <input type="text" class="form-control" id="servicio_area" name="area" maxlength="100" placeholder="Ej: Pediatría, Guardia...">
            </div>

            <div class="mb-3">
                <label for="servicio_responsable" class="form-label">Responsable</label>
                <select class="form-control" id="servicio_responsable" name="responsable">
                    <option value="">-- Sin Asignar --</option>
                </select>
                <small class="form-text text-muted">Seleccione el encargado de este servicio.</small>
            </div>
            
            <div class="mb-3">
                <label for="servicio_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                <select class="form-control" id="servicio_estado" name="estado" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarServicio">Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let tablaServicios;
let modalInstancia;

$(document).ready(function() {
    modalInstancia = new bootstrap.Modal(document.getElementById('modalServicio'));

    // Cargar opciones de responsables
    fetch('api/digitalizar/get_opciones.php')
        .then(response => response.json())
        .then(json => {
            if (json.status === 'success' && json.data.responsables) {
                const selectResp = document.getElementById("servicio_responsable");
                
                // Agrupar por tipo
                const optGroupAdmin = document.createElement("optgroup");
                optGroupAdmin.label = "Administradores (Sistema)";
                
                const optGroupPersonal = document.createElement("optgroup");
                optGroupPersonal.label = "Personal (Empleados)";

                json.data.responsables.forEach(resp => {
                    const option = document.createElement("option");
                    option.value = resp.tipo + "_" + resp.id;
                    option.textContent = resp.nombre;
                    
                    if (resp.tipo === 'admin') {
                        optGroupAdmin.appendChild(option);
                    } else if (resp.tipo === 'personal') {
                        optGroupPersonal.appendChild(option);
                    }
                });

                if (optGroupAdmin.children.length > 0) selectResp.appendChild(optGroupAdmin);
                if (optGroupPersonal.children.length > 0) selectResp.appendChild(optGroupPersonal);
            }
        })
        .catch(error => console.error("Error al cargar opciones de responsables:", error));

    tablaServicios = $('#tabla-servicios').DataTable({
        ajax:{ 
            url:'api/digitalizar/api_servicios_dig.php',
            dataSrc: ''
        },
        columns: [
            {
                data: 'estado',
                className: 'text-center',
                render: function(data) {
                    if (data == '1' || data === true || data === 1) {
                        return '<i class="fa-solid fa-circle-check text-success"></i>';
                    } else {
                        return '<i class="fa-solid fa-circle-xmark text-danger"></i>';
                    }
                }
            },
            {
                data: 'nombre',
                className: 'text-center fw-bold'
            },
            {
                data: 'area',
                className: 'text-center',
                render: function(data) {
                    return data ? data : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'responsable_nombre',
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.tipo_responsable === 'admin') {
                        return `<span class="badge bg-primary"><i class="fa-solid fa-user-shield me-1"></i> ${data}</span>`;
                    } else if (row.tipo_responsable === 'personal') {
                        return `<span class="badge bg-info text-dark"><i class="fa-solid fa-user-nurse me-1"></i> ${data}</span>`;
                    } else {
                        return '<span class="text-muted">Sin Asignar</span>';
                    }
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const respValue = row.tipo_responsable ? `${row.tipo_responsable}_${row.responsable_id}` : '';
                    return `
                        <a class="text-primary me-2" href="#" onclick="abrirModalEditar(${row.id}, '${row.nombre}', '${row.area || ''}', '${respValue}', ${row.estado})">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a class="text-danger" href="#" onclick="eliminarServicio(${row.id})">
                            <i class="bi bi-trash"></i>
                        </a>
                    `;
                }
            }
        ],
        language: {
            lengthMenu: "Mostrar " +
                `<select class="custom-select custom-select-sm form-control form-control-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="-1">Todos</option>
                </select>` +
                " registros por página",
            zeroRecords: 'Sin resultados',
            info: 'Se encontraron _TOTAL_ coincidencias',
            infoEmpty: 'No hay registros por mostrar',
            infoFiltered: '[de un total de _MAX_]',
            search: 'Filtrar',
            paginate: {
                next: 'Siguiente',
                previous: 'Anterior'
            }
        }
    });

    // Formulario Submit
    document.getElementById("formServicio").addEventListener("submit", function(e) {
        e.preventDefault();
        
        const id = document.getElementById("servicio_id").value;
        const nombre = document.getElementById("servicio_nombre").value;
        const area = document.getElementById("servicio_area").value;
        const responsable = document.getElementById("servicio_responsable").value;
        const estado = document.getElementById("servicio_estado").value;
        
        const method = id ? 'PUT' : 'POST';
        const bodyData = {
            nombre: nombre,
            area: area,
            responsable: responsable,
            estado: parseInt(estado)
        };
        
        if (id) {
            bodyData.id = parseInt(id);
        }

        const btnGuardar = document.getElementById("btnGuardarServicio");
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

        fetch('api/digitalizar/api_servicios_dig.php', {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(bodyData)
        })
        .then(r => r.json())
        .then(r => {
            if (r.success) {
                Swal.fire({icon: 'success', title: '¡Guardado!', text: 'El servicio se guardó correctamente.', timer: 1500, showConfirmButton: false});
                modalInstancia.hide();
                tablaServicios.ajax.reload();
            } else {
                let errorMsg = r.errores ? r.errores.join('<br>') : 'Error desconocido al guardar';
                Swal.fire({icon: 'error', title: 'Error', html: errorMsg});
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({icon: 'error', title: 'Error', text: 'Error de conexión con el servidor.'});
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = 'Guardar';
        });
    });

});

// Funciones globales para botones
window.abrirModalNuevo = function() {
    document.getElementById("formServicio").reset();
    document.getElementById("servicio_id").value = "";
    document.getElementById("modalServicioLabel").innerText = "Nuevo Servicio";
    modalInstancia.show();
};

window.abrirModalEditar = function(id, nombre, area, responsable, estado) {
    document.getElementById("formServicio").reset();
    document.getElementById("servicio_id").value = id;
    document.getElementById("servicio_nombre").value = nombre;
    document.getElementById("servicio_area").value = area;
    document.getElementById("servicio_responsable").value = responsable;
    
    // El estado puede venir como bool true/false o int 1/0
    const estadoInt = (estado === true || estado === 1 || estado === '1' || estado === 't') ? 1 : 0;
    document.getElementById("servicio_estado").value = estadoInt;
    
    document.getElementById("modalServicioLabel").innerText = "Editar Servicio";
    modalInstancia.show();
};

window.eliminarServicio = function(id) {
    Swal.fire({
        title: '¿Estás seguro de desactivar el servicio?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('api/digitalizar/api_servicios_dig.php', {
                method: 'DELETE',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
            .then(r => r.json())
            .then(r => {
                if (r.success) {
                    Swal.fire({icon:'success', title:'Desactivado', text:'El servicio ha sido inhabilitado.', timer:1500, showConfirmButton:false});
                    tablaServicios.ajax.reload();
                } else {
                    Swal.fire({icon:'error', title:'Error', text: r.error || 'No se pudo desactivar el registro.'});
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({icon:'error', title:'Error', text:'Fallo en la comunicación con el servidor.'});
            });
        }
    });
};
</script>
