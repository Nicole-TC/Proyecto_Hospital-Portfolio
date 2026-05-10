<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <a href="index.php?modulo=menu_digitalizar&accion=nueva_etiqueta" class="btn btn-success float-right">
                <i class="fa-solid fa-user-plus fa-bounce white fs-3"></i>
                <span class="float-right fs-6 text-white ml-1">Nueva etiqueta</span>
            </a>
        </div>

        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-etiquetas">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">ID</th>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<script>
$(document).ready(function() {
    $('#tabla-etiquetas').DataTable({
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
                data: 'id',
                className: 'text-center'
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
                data: 'id',
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `
                        <a class="text-success" href="index.php?modulo=menu_digitalizar&accion=editar_etiqueta&id=${data}">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a onclick="eliminarEtiqueta(${data})" class="text-danger" href="#">
                            <i class="bi bi-trash ml-1"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send?text=Te comparto la página de SITT. Entra en https://sitt.ar/admin">
                            <i class="bi bi-whatsapp ml-1"></i>
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
});
</script>
