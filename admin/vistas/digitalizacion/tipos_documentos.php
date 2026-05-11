<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <button class="btn btn-success float-right" onclick="abrirModalNuevo()">
                <i class="fa-solid fa-plus fa-bounce white fs-3"></i>
                <span class="float-right fs-6 text-white ml-1">Nuevo Tipo</span>
            </button>
        </div>

        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-tipos">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Estado</th>
                            <th scope="col" class="text-center">ID</th>
                            <th scope="col" class="text-center">Nombre</th>
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
<div class="modal fade" id="modalTipoDoc" tabindex="-1" aria-labelledby="modalTipoDocLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formTipoDoc">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTipoDocLabel">Tipo de Documento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="tipo_id" name="tipo_id">
            
            <div class="mb-3">
                <label for="tipo_nombre" class="form-label">Nombre del Tipo <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="tipo_nombre" name="nombre" required maxlength="100">
            </div>
            
            <div class="mb-3">
                <label for="tipo_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                <select class="form-control" id="tipo_estado" name="estado" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarTipo">Guardar</button>
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
let tablaTipos;
let modalInstancia;

$(document).ready(function() {
    modalInstancia = new bootstrap.Modal(document.getElementById('modalTipoDoc'));

    tablaTipos = $('#tabla-tipos').DataTable({
        ajax:{ 
            url:'api/digitalizar/api_tipo_documentos_dig.php',
            dataSrc: ''
        },
        columns: [
            {
                data: 'estado',
                className: 'text-center',
                render: function(data) {
                    if (data == '1') {
                        return '<i class="fa-solid fa-circle-check text-success"></i>';
                    } else {
                        return '<i class="fa-solid fa-circle-xmark text-danger"></i>';
                    }
                }
            },
            {
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'nombre',
                className: 'text-center'
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <a class="text-primary me-2" href="#" onclick="abrirModalEditar(${row.id}, '${row.nombre}', ${row.estado})">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a class="text-danger" href="#" onclick="eliminarTipo(${row.id})">
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
    document.getElementById("formTipoDoc").addEventListener("submit", function(e) {
        e.preventDefault();
        
        const id = document.getElementById("tipo_id").value;
        const nombre = document.getElementById("tipo_nombre").value;
        const estado = document.getElementById("tipo_estado").value;
        
        const method = id ? 'PUT' : 'POST';
        const bodyData = {
            nombre: nombre,
            estado: parseInt(estado)
        };
        
        if (id) {
            bodyData.id = parseInt(id);
        }

        const btnGuardar = document.getElementById("btnGuardarTipo");
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

        fetch('api/digitalizar/api_tipo_documentos_dig.php', {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(bodyData)
        })
        .then(r => r.json())
        .then(r => {
            if (r.success) {
                Swal.fire({icon: 'success', title: '¡Guardado!', text: 'El tipo de documento se guardó correctamente.', timer: 1500, showConfirmButton: false});
                modalInstancia.hide();
                tablaTipos.ajax.reload();
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
    document.getElementById("formTipoDoc").reset();
    document.getElementById("tipo_id").value = "";
    document.getElementById("modalTipoDocLabel").innerText = "Nuevo Tipo de Documento";
    modalInstancia.show();
};

window.abrirModalEditar = function(id, nombre, estado) {
    document.getElementById("formTipoDoc").reset();
    document.getElementById("tipo_id").value = id;
    document.getElementById("tipo_nombre").value = nombre;
    document.getElementById("tipo_estado").value = estado;
    document.getElementById("modalTipoDocLabel").innerText = "Editar Tipo de Documento";
    modalInstancia.show();
};

window.eliminarTipo = function(id) {
    Swal.fire({
        title: '¿Estás seguro de eliminar el tipo de documento?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('api/digitalizar/api_tipo_documentos_dig.php', {
                method: 'DELETE',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
            .then(r => r.json())
            .then(r => {
                if (r.success) {
                    Swal.fire({icon:'success', title:'Desactivado', text:'El tipo de documento ha sido inhabilitado.', timer:1500, showConfirmButton:false});
                    tablaTipos.ajax.reload();
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
