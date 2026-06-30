<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-gear me-2"></i> Configuraciones del Sistema
        </div>

        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-configuraciones" style="width: 100%;">
                    <thead>
                        <tr>
                             <th scope="col" class="text-center">Nombre</th>
                            <th scope="col" class="text-center">Clave</th>
                           
                            <th scope="col" class="text-center">Valor</th>
                            <th scope="col" class="text-center">Descripción</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Configuración -->
<div class="modal fade" id="modalConfig" tabindex="-1" aria-labelledby="modalConfigLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formConfig">
          <div class="modal-header">
            <h5 class="modal-title" id="modalConfigLabel">Editar Configuración</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="config_id" name="id">

            <div class="mb-3">
                <label for="config_clave" class="form-label">Clave</label>
                <input type="text" class="form-control" id="config_clave" readonly disabled>
            </div>

            <div class="mb-3">
                <label for="config_valor" class="form-label">Valor <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="config_valor" name="valor" required>
                <small class="form-text text-muted" id="config_valor_help"></small>
            </div>

            <div class="mb-3">
                <label for="config_descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="config_descripcion" rows="2" readonly disabled></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarConfig">Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let modalInstancia;

$(document).ready(function() {
    modalInstancia = new bootstrap.Modal(document.getElementById('modalConfig'));

    $('#tabla-configuraciones').DataTable({
        ajax: {
            url: 'api/digitalizar/api_configuraciones.php',
            dataSrc: '',
            error: function(xhr, error, thrown) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las configuraciones.' });
            }
        },
        columns: [
            {
                data: null,
                className: 'text-center',
                render: function(data) {
                    const aliases = {
                        'formatos_permitidos': 'Formatos permitidos',
                        'max_peso_archivo_mb': 'Límite de peso de archivos'
                    };
                    return aliases[data.clave] || data.clave;
                }
            },
            {
                data: 'clave',
                className: 'text-center fw-bold'
            },
            
            {
                data: 'valor',
                className: 'text-center'
            },
            {
                data: 'descripcion',
                className: 'text-center text-muted',
                render: function(data) {
                    return data || '-';
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<button class="btn btn-warning btn-sm btn-editar" data-id="${row.id}" data-clave="${row.clave}" data-valor="${row.valor}" data-descripcion="${row.descripcion || ''}"><i class="fa-solid fa-pen me-1"></i> Editar</button>`;
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

    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        const clave = $(this).data('clave');
        const valor = $(this).data('valor');
        const descripcion = $(this).data('descripcion');

        $('#config_id').val(id);
        $('#config_clave').val(clave);
        $('#config_valor').val(valor);
        $('#config_descripcion').val(descripcion);

        let helpText = '';
        if (clave === 'max_peso_archivo_mb') {
            helpText = 'Límite en megabytes (ej: 10)';
        } else if (clave === 'formatos_permitidos') {
            helpText = 'Separados por coma (ej: pdf,jpg,jpeg,png)';
        }
        $('#config_valor_help').text(helpText);

        modalInstancia.show();
    });

    document.getElementById("formConfig").addEventListener("submit", function(e) {
        e.preventDefault();

        const id = $('#config_id').val();
        const valor = $('#config_valor').val().trim();

        const btnGuardar = document.getElementById("btnGuardarConfig");
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

        $.ajax({
            url: 'api/digitalizar/api_configuraciones.php',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ id: parseInt(id), valor: valor }),
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Guardado', text: 'Configuración actualizada.', timer: 1500, showConfirmButton: false });
                    modalInstancia.hide();
                    $('#tabla-configuraciones').DataTable().ajax.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.error || 'No se pudo guardar.' });
                }
            },
            error: function(xhr) {
                let msg = 'Error de conexión con el servidor.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            },
            complete: function() {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = 'Guardar';
            }
        });
    });
});
</script>
