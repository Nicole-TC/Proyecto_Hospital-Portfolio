(function() {
    var form = document.getElementById('formOrden');
    if (!form) return;

    var idOrden = parseInt(form.getAttribute('data-id'), 10);
    if (!idOrden) return;

    var selectCirugia = document.getElementById('id_cirugia_catalogo');
    var selectCirujano = document.getElementById('id_profesional');

    function cargarCombos(callback) {
        fetch('app/cir-api/endpoints/ordenes_medicas-e.php?combos=1', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status !== 'success') {
                throw new Error(data.message || 'Error al cargar combos');
            }
            selectCirugia.innerHTML = '<option value="">Seleccione cirugía</option>';
            (data.cirugias || []).forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id_cirugia_catalogo;
                opt.textContent = c.nombre;
                selectCirugia.appendChild(opt);
            });
            selectCirujano.innerHTML = '<option value="">Seleccione cirujano</option>';
            (data.cirujanos || []).forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.id_profesional;
                opt.textContent = (p.apellido || '') + ', ' + (p.nombre || '');
                selectCirujano.appendChild(opt);
            });
            if (callback) callback();
        })
        .catch(function(err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'No se pudieron cargar los datos.' });
        });
    }

    function cargarOrden() {
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
            if (o.estado_codigo === 'CANC' || o.estado_codigo === 'FIN') {
                var titulo = o.estado_codigo === 'CANC' ? 'Orden cancelada' : 'Orden finalizada';
                var texto = o.estado_codigo === 'CANC'
                    ? 'No se puede editar una orden cancelada.'
                    : 'No se puede editar una orden finalizada.';
                Swal.fire({
                    icon: 'warning',
                    title: titulo,
                    text: texto
                });
                form.querySelectorAll('input, select, textarea, button[type=submit]').forEach(function(el) {
                    el.disabled = true;
                });
            }

            document.getElementById('id_orden_display').value = '#' + o.id_orden;
            document.getElementById('estado_display').value = (o.estado_descripcion || o.estado_codigo || '');
            document.getElementById('fecha_orden_display').value = o.fecha_orden || '';
            document.getElementById('dni_paciente').value = o.dni || '';
            document.getElementById('nombre_paciente').value =
                ((o.paciente_nombre || '') + ' ' + (o.paciente_apellido || '')).trim();
            document.getElementById('observaciones').value = o.observaciones || '';

            cargarCombos(function() {
                if (o.id_cirugia_catalogo) selectCirugia.value = String(o.id_cirugia_catalogo);
                if (o.id_profesional) selectCirujano.value = String(o.id_profesional);
            });
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error de conexión' });
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var payload = {
            id_orden: idOrden,
            id_cirugia_catalogo: parseInt(selectCirugia.value, 10),
            id_profesional: parseInt(selectCirujano.value, 10),
            observaciones: document.getElementById('observaciones').value
        };

        fetch('app/cir-api/endpoints/ordenes_medicas-e.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: data.message,
                    confirmButtonColor: '#00cec9'
                }).then(function() {
                    window.location.href = 'index.php?modulo=menu_circuitoquirurgico&accion=gestion_ordenes';
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error de conexión' });
        });
    });

    cargarOrden();
})();
