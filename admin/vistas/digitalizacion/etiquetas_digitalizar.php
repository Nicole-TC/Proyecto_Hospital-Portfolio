<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <button id="btn-ver-inactivos" class="btn btn-info">
                <i class="fa-solid fa-eye"></i>
                <span class="text-white ml-1">Ver inactivos</span>
            </button>
            <a href="index.php?modulo=menu_digitalizar&accion=nueva_etiqueta" class="btn btn-success float-right">
                <i class="fa-solid fa-tag"></i>
                <span class="text-white ml-1">Nueva etiqueta</span>
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
                render: function(data, type, row) {
                    let html = `
                        <a class="text-success" href="index.php?modulo=menu_digitalizar&accion=editar_etiqueta&id=${data}">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    `;
                    if (row.estado == 1) {
                        html += `
                            <a onclick="eliminarEtiqueta(${data})" class="text-danger" href="#">
                                <i class="bi bi-trash ml-1"></i>
                            </a>
                        `;
                    } else {
                        html += `
                            <a onclick="activarEtiqueta(${data})" class="text-primary" href="#">
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
});
</script>
