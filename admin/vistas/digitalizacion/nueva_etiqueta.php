<div class="col-sm-12 mb-5">
    <div class="card">
        <div class="card-header">
            Ingresar datos de la etiqueta:
        </div>

        <form class="p-1" id="form-nueva-etiqueta">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" autocomplete="off" name="nombre_etiqueta" autofocus required>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Color hex</label>
                    <input type="color" class="form-control" name="color_hex" value="#000000" required>
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Estado</label>
                    <select class="form-control" name="estado" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="d-grid mt-2">
                <p class="text-center">
                    <a class="btn btn-danger" href="index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar" role="button">Cancelar</a>
                    <input type="submit" class="btn btn-primary ml-2" value="Guardar">
                </p>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('form-nueva-etiqueta').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    fetch('api/digitalizar/api_etiquetas_dig.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            Swal.fire({icon:'success', title:'Creada', text:'Etiqueta creada correctamente', timer:1500, showConfirmButton:false})
            .then(() => window.location.href = 'index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar');
        } else {
            const msg = r.errores ? r.errores.join('<br>') : 'No se pudo crear la etiqueta';
            Swal.fire({icon:'error', title:'Error', html: msg});
        }
    })
    .catch(() => Swal.fire({icon:'error', title:'Error', text:'Error de conexión'}));
});
</script>
