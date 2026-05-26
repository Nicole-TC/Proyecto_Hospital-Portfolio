(function() {
    var contenedor = document.getElementById('formOrden');
    if (!contenedor) return;

    var idOrden = parseInt(contenedor.getAttribute('data-id'), 10);
    if (!idOrden) return;

    fetch('app/cir-api/endpoints/ordenes_medicas-e.php?id=' + idOrden, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status !== 'success' || !data.orden) {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Orden no encontrada' });
            return;
        }
        var o = data.orden;

        document.getElementById('id_orden_display').value = '#' + o.id_orden;
        document.getElementById('estado_display').value = (o.estado_descripcion || o.estado_codigo || '');
        document.getElementById('fecha_orden_display').value = o.fecha_orden || '';
        document.getElementById('dni_paciente').value = o.dni || '';
        document.getElementById('nombre_paciente').value =
            ((o.paciente_nombre || '') + ' ' + (o.paciente_apellido || '')).trim();
        document.getElementById('cirugia_display').value = o.cirugia || '—';
        document.getElementById('cirujano_display').value = o.cirujano || '—';
        document.getElementById('observaciones').value = o.observaciones || '';
    })
    .catch(function() {
        Swal.fire({ icon: 'error', title: 'Error de conexión' });
    });
})();
