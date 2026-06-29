<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <button id="btn-ver-inactivos" class="btn btn-info">
                <i class="fa-solid fa-eye"></i>
                <span class="text-white ml-1">Ver inactivos</span>
            </button>
            <button class="btn btn-success float-right" onclick="abrirModalNuevo()">
                <i class="fa-solid fa-tag"></i>
                <span class="text-white ml-1">Nueva etiqueta</span>
            </button>
        </div>

        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-etiquetas">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Nombre</th>
                            <th scope="col" class="text-center">Color hex</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Etiqueta -->
<div class="modal fade" id="modalEtiqueta" tabindex="-1" aria-labelledby="modalEtiquetaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEtiqueta">
          <div class="modal-header">
            <h5 class="modal-title" id="modalEtiquetaLabel">Etiqueta</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="etiqueta_id" name="id">

            <div class="mb-3">
                <label for="etiqueta_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="etiqueta_nombre" name="nombre_etiqueta" required maxlength="100">
            </div>

            <div class="mb-3">
                <label for="etiqueta_color" class="form-label">Color hex <span class="text-danger">*</span></label>
                <input type="color" class="form-control" id="etiqueta_color" name="color_hex" value="#000000" required>
            </div>

            <div class="mb-3">
                <label for="etiqueta_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                <select class="form-control" id="etiqueta_estado" name="estado" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarEtiqueta">Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<script>
let tablaEtiquetas;
let modalInstancia;

$(document).ready(function() {
    modalInstancia = new bootstrap.Modal(document.getElementById('modalEtiqueta'));

    tablaEtiquetas = $('#tabla-etiquetas').DataTable({
        ajax:{ url:'api/digitalizar/api_etiquetas_dig.php',
        dataSrc: ''},
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
                data: 'nombre_etiqueta',
                className: 'text-center'
            },
            {
                data: 'color_hex',
                className: 'text-center',
                render: function(data) {
                    return `
                        <span style="
                            background-color: ${data};
                            display: inline-block;
                            width: 24px;
                            height: 24px;
                            border-radius: 50%;
                            border: 1px solid #ccc;
                            vertical-align: middle;
                        "></span>
                        <small class="ml-1">${data}</small>
                    `;
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let html = `
                        <a class="text-success" href="#" onclick="abrirModalEditar(${row.id}, '${row.nombre_etiqueta}', '${row.color_hex}', ${row.estado})">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    `;
                    if (row.estado == 1) {
                        html += `
                            <a onclick="eliminarEtiqueta(${row.id})" class="text-danger" href="#">
                                <i class="bi bi-trash ml-1"></i>
                            </a>
                        `;
                    } else {
                        html += `
                            <a onclick="activarEtiqueta(${row.id})" class="text-primary" href="#">
                                <i class="bi bi-check-circle ml-1"></i>
                            </a>
                        `;
                    }
                    return html;
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

    $('#btn-ver-inactivos').on('click', function() {
        const table = $('#tabla-etiquetas').DataTable();
        const mostrandoInactivos = table.ajax.url().includes('mostrar=inactivos');
        if (mostrandoInactivos) {
            table.ajax.url('api/digitalizar/api_etiquetas_dig.php').load();
            $(this).html('<i class="fa-solid fa-eye"></i> <span class="text-white ml-1">Ver inactivos</span>');
        } else {
            table.ajax.url('api/digitalizar/api_etiquetas_dig.php?mostrar=inactivos').load();
            $(this).html('<i class="fa-solid fa-eye-slash"></i> <span class="text-white ml-1">Ver activos</span>');
        }
    });

    document.getElementById("formEtiqueta").addEventListener("submit", function(e) {
        e.preventDefault();

        const id = document.getElementById("etiqueta_id").value;
        const nombre = document.getElementById("etiqueta_nombre").value;
        const color = document.getElementById("etiqueta_color").value;
        const estado = document.getElementById("etiqueta_estado").value;

        const method = id ? 'PUT' : 'POST';
        const bodyData = {
            nombre_etiqueta: nombre,
            color_hex: color,
            estado: parseInt(estado)
        };

        if (id) {
            bodyData.id = parseInt(id);
        }

        const btnGuardar = document.getElementById("btnGuardarEtiqueta");
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

        fetch('api/digitalizar/api_etiquetas_dig.php', {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(bodyData)
        })
        .then(r => r.json())
        .then(r => {
            if (r.success) {
                Swal.fire({icon: 'success', title: '¡Guardado!', text: 'La etiqueta se guardó correctamente.', timer: 1500, showConfirmButton: false});
                modalInstancia.hide();
                tablaEtiquetas.ajax.reload();
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

window.activarEtiqueta = function(id) {
    Swal.fire({
        title: '¿Activar etiqueta?',
        text: 'Se activará la etiqueta',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('api/digitalizar/api_etiquetas_dig.php', {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
                .then(r => r.json())
                .then(r => {
                    if (r.success) {
                        Swal.fire({icon:'success', title:'Activada', text:'Etiqueta activada', timer:1500, showConfirmButton:false});
                        $('#tabla-etiquetas').DataTable().ajax.reload();
                    } else {
                        Swal.fire({icon:'error', title:'Error', text: r.error || 'No se pudo activar'});
                    }
                });
        }
    });
};

window.eliminarEtiqueta = function(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se desactivará la etiqueta',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('api/digitalizar/api_etiquetas_dig.php', {
                method: 'DELETE',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
                .then(r => r.json())
                .then(r => {
                    if (r.success) {
                        Swal.fire({icon:'success', title:'Eliminada', text:'Etiqueta desactivada', timer:1500, showConfirmButton:false});
                        $('#tabla-etiquetas').DataTable().ajax.reload();
                    } else {
                        Swal.fire({icon:'error', title:'Error', text: r.error || 'No se pudo eliminar'});
                    }
                });
        }
    });
};

window.abrirModalNuevo = function() {
    document.getElementById("formEtiqueta").reset();
    document.getElementById("etiqueta_id").value = "";
    document.getElementById("etiqueta_color").value = "#000000";
    document.getElementById("modalEtiquetaLabel").innerText = "Nueva Etiqueta";
    modalInstancia.show();
};

window.abrirModalEditar = function(id, nombre, color, estado) {
    document.getElementById("formEtiqueta").reset();
    document.getElementById("etiqueta_id").value = id;
    document.getElementById("etiqueta_nombre").value = nombre;
    document.getElementById("etiqueta_color").value = color;
    const estadoInt = (estado === true || estado === 1 || estado === '1' || estado === 't') ? 1 : 0;
    document.getElementById("etiqueta_estado").value = estadoInt;
    document.getElementById("modalEtiquetaLabel").innerText = "Editar Etiqueta";
    modalInstancia.show();
};
</script>
