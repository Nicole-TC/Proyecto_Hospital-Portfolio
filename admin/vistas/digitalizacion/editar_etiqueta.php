<div class="col-sm-12 mb-5">
    <div class="card">
        <div class="card-header">
            Editar etiqueta:
        </div>

        <form class="p-1" id="form-editar-etiqueta">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" autocomplete="off" name="nombre_etiqueta" id="input-nombre" required>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Color hex</label>
                    <input type="color" class="form-control" name="color_hex" id="input-color" required>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Estado</label>
                    <select class="form-control" name="estado" id="input-estado" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="d-grid mt-2">
                <p class="text-center">
                    <input type="hidden" name="id" id="input-id">
                    <a class="btn btn-danger" href="index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar" role="button">Cancelar</a>
                    <input type="submit" class="btn btn-primary ml-2" value="Actualizar">
                </p>
            </div>
        </form>
    </div>
</div>
<script>
const params = new URLSearchParams(window.location.search);
const id = params.get('id');

fetch('api/digitalizar/api_etiquetas_dig.php?mostrar=todos')
    .then(r => r.json())
    .then(etiquetas => {
        
        const etiqueta = etiquetas.find(e => e.id == id);
        if (!etiqueta) {
            Swal.fire({icon:'error', title:'Error', text:'La etiqueta no existe'})
            .then(() => window.location.href = 'index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar');
            return;
        }
        document.getElementById('input-id').value = etiqueta.id;
        document.getElementById('input-nombre').value = etiqueta.nombre_etiqueta;
        document.getElementById('input-color').value = etiqueta.color_hex;
        document.getElementById('input-estado').value = etiqueta.estado;
    })
    .catch(() => {
        Swal.fire({icon:'error', title:'Error', text:'Error al cargar la etiqueta'})
        .then(() => window.location.href = 'index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar');
    });

document.getElementById('form-editar-etiqueta').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/digitalizar/api_etiquetas_dig.php', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            Swal.fire({icon:'success', title:'Actualizada', text:'Etiqueta actualizada correctamente', timer:1500, showConfirmButton:false})
            .then(() => window.location.href = 'index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar');
        } else {
            const msg = r.errores ? r.errores.join('<br>') : 'No se pudo actualizar la etiqueta';
            Swal.fire({icon:'error', title:'Error', html: msg});
        }
    })
    .catch(() => Swal.fire({icon:'error', title:'Error', text:'Error de conexión'}));
});
</script>
