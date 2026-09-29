(function() {
    var contenedor = document.getElementById('formOrden');
    if (!contenedor) return;

    var idOrden = parseInt(contenedor.getAttribute('data-id'), 10);
    if (!idOrden) return;

    var ENDPOINT = 'app/cir-api/endpoints/ordenes_medicas-e.php';
    var ordenActual = null;
    var puedeEditar = false;

    var bloqueCargando = document.getElementById('formOrdenCargando');
    var bloqueContenido = document.getElementById('formOrdenContenido');
    var inputCirugia = document.getElementById('cirugia_input');
    var inputCirujano = document.getElementById('cirujano_input');
    var hiddenIdProfesional = document.getElementById('cirujano_id_profesional');
    var textareaObs = document.getElementById('observaciones');
    var btnGuardar = document.getElementById('btnGuardarOrden');
    var alertaSoloLectura = document.getElementById('alertaSoloLectura');
    var bloqueHistorial = document.getElementById('historialModificaciones');

    var etiquetasCampo = {
        cirugia: 'Cirugía',
        cirujano: 'Cirujano',
        observaciones: 'Observaciones'
    };

    function escaparHtml(texto) {
        var div = document.createElement('div');
        div.textContent = texto || '';
        return div.innerHTML;
    }

    function resaltarCoincidencia(texto, term) {
        var safe = escaparHtml(texto || '');
        if (!term) return safe;
        var re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return safe.replace(re, '<mark class="sugerencia-match">$1</mark>');
    }

    function mostrarContenido() {
        if (bloqueCargando) bloqueCargando.classList.add('d-none');
        if (bloqueContenido) bloqueContenido.classList.remove('d-none');
    }

    // Postgres devuelve los timestamp como 'YYYY-MM-DD HH:MM:SS[.ffffff]' en hora
    // local: la sesion de la conexion fija America/Argentina/Buenos_Aires
    // (model/cir_conexion.php). Se arma el texto desde el string crudo en vez de
    // usar new Date() + toLocale* porque el navegador aplica su propia zona
    // horaria y su propio formato, y la hora puede correr.
    function formatearFechaHistorial(fecha) {
        if (!fecha) return '—';
        var m = String(fecha).match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
        if (!m) return fecha;
        return m[3] + '/' + m[2] + '/' + m[1] + ' ' + m[4] + ':' + m[5];
    }

    function textoHistorial(valor) {
        var t = (valor || '').trim();
        return t !== '' ? t : '(vacío)';
    }

    function renderHistorialModificaciones(modificaciones) {
        if (!bloqueHistorial) return;

        if (!modificaciones || modificaciones.length === 0) {
            bloqueHistorial.innerHTML =
                '<p class="text-secondary small mb-0">Sin modificaciones registradas.</p>';
            return;
        }

        bloqueHistorial.innerHTML = modificaciones.map(function(m) {
            var campo = etiquetasCampo[m.campo] || m.campo || 'Campo';
            var usuario = (m.nombre_usuario || '').trim() || 'Usuario del sistema';
            return '<div class="historial-modificaciones-item">' +
                '<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">' +
                '<strong class="small">' + escaparHtml(campo) + '</strong>' +
                '<span class="historial-modificaciones-fecha">' + escaparHtml(formatearFechaHistorial(m.fecha_modificacion)) + '</span>' +
                '</div>' +
                '<div class="historial-modificaciones-usuario small text-secondary mb-1">' +
                '<i class="bi bi-person me-1"></i>' + escaparHtml(usuario) +
                '</div>' +
                '<div class="historial-modificaciones-cambio small">' +
                '<span class="text-secondary">Antes:</span> ' + escaparHtml(textoHistorial(m.valor_anterior)) +
                '<br><span class="text-secondary">Ahora:</span> ' + escaparHtml(textoHistorial(m.valor_nuevo)) +
                '</div></div>';
        }).join('');
    }

    function aplicarModoEdicion() {
        var editable = !!puedeEditar;
        if (inputCirugia) inputCirugia.disabled = !editable;
        if (inputCirujano) inputCirujano.disabled = !editable;
        if (textareaObs) textareaObs.disabled = !editable;
        if (btnGuardar) btnGuardar.classList.toggle('d-none', !editable);
        if (alertaSoloLectura) alertaSoloLectura.classList.toggle('d-none', editable);
    }

    function initAutocompleteCatalogo(cfg) {
        var input = document.getElementById(cfg.inputId);
        var box = document.getElementById(cfg.boxId);
        var wrapper = document.getElementById(cfg.wrapperId);
        if (!input || !box || !wrapper) return;

        var timer = null;
        var reqId = 0;

        function ocultar() {
            box.innerHTML = '';
            box.classList.add('d-none');
            wrapper.classList.remove('is-open');
        }

        function mostrar() {
            wrapper.classList.add('is-open');
        }

        function cargarSugerencias(term) {
            var currentReq = ++reqId;
            var url = ENDPOINT + '?sugerencias_catalogo=1' +
                '&tipo=' + encodeURIComponent(cfg.tipo) +
                '&term=' + encodeURIComponent(term) +
                '&limite=12';

            fetch(url, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (currentReq !== reqId) return;
                    if (data.status !== 'success') {
                        ocultar();
                        return;
                    }
                    var items = data.sugerencias || [];
                    if (items.length === 0) {
                        box.innerHTML = '<div class="sugerencia-vacia">' +
                            '<i class="bi bi-search"></i><span>Sin coincidencias para «' + escaparHtml(term) + '»</span></div>';
                        box.classList.remove('d-none');
                        mostrar();
                        return;
                    }
                    var icono = cfg.tipo === 'cirugia' ? 'bi-heart-pulse' : 'bi-person-badge';
                    box.innerHTML = '<div class="sugerencias-titulo">Sugerencias</div>' +
                        items.map(function(item) {
                            var titulo = item.nombre || item.valor || '';
                            var meta = item.meta || '';
                            var extra = cfg.tipo === 'cirujano' && item.id_profesional
                                ? ' data-id-profesional="' + item.id_profesional + '"'
                                : '';
                            return '<button type="button" class="sugerencia-item" role="option"' +
                                ' data-valor="' + escaparHtml(item.valor) + '"' + extra + '>' +
                                '<span class="sugerencia-ico"><i class="bi ' + icono + '"></i></span>' +
                                '<span class="sugerencia-cuerpo">' +
                                '<span class="sugerencia-nombre">' + resaltarCoincidencia(titulo, term) + '</span>' +
                                (meta ? '<small class="sugerencia-meta">' + escaparHtml(meta) + '</small>' : '') +
                                '</span></button>';
                        }).join('');
                    box.classList.remove('d-none');
                    mostrar();
                    box.querySelectorAll('.sugerencia-item').forEach(function(btn) {
                        btn.addEventListener('mousedown', function(ev) { ev.preventDefault(); });
                        btn.addEventListener('click', function() {
                            input.value = btn.getAttribute('data-valor') || '';
                            if (cfg.tipo === 'cirujano' && hiddenIdProfesional) {
                                hiddenIdProfesional.value = btn.getAttribute('data-id-profesional') || '';
                            }
                            ocultar();
                        });
                    });
                })
                .catch(function() {
                    if (currentReq === reqId) ocultar();
                });
        }

        input.addEventListener('input', function() {
            if (input.disabled) return;
            clearTimeout(timer);
            var term = this.value.trim();
            if (term.length < 1) {
                ocultar();
                if (cfg.tipo === 'cirujano' && hiddenIdProfesional) {
                    hiddenIdProfesional.value = '';
                }
                return;
            }
            timer = setTimeout(function() { cargarSugerencias(term); }, 280);
        });

        input.addEventListener('blur', function() {
            setTimeout(ocultar, 180);
        });

        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) ocultar();
        });

        return { ocultar: ocultar };
    }

    function poblarFormulario(o, modificaciones) {
        ordenActual = o;
        puedeEditar = !!o.puede_editar;

        document.getElementById('id_orden_display').value = '#' + o.id_orden;
        document.getElementById('estado_display').value = (o.estado_descripcion || o.estado_codigo || '');
        document.getElementById('fecha_orden_display').value = o.fecha_orden || '';
        document.getElementById('dni_paciente').value = o.dni || '';
        document.getElementById('nombre_paciente').value =
            ((o.paciente_nombre || '') + ' ' + (o.paciente_apellido || '')).trim();

        if (inputCirugia) {
            inputCirugia.value = (o.cirugia && o.cirugia !== '—') ? o.cirugia : '';
        }
        if (inputCirujano) {
            inputCirujano.value = (o.cirujano && o.cirujano !== '—') ? o.cirujano : '';
        }
        if (hiddenIdProfesional) {
            hiddenIdProfesional.value = o.id_profesional ? String(o.id_profesional) : '';
        }
        if (textareaObs) {
            textareaObs.value = o.observaciones || '';
        }

        aplicarModoEdicion();
        renderHistorialModificaciones(modificaciones);
        mostrarContenido();
    }

    function cargarOrden() {
        fetch(ENDPOINT + '?id=' + idOrden, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status !== 'success' || !data.orden) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Orden no encontrada'
                });
                return;
            }
            poblarFormulario(data.orden, data.modificaciones || []);
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error de conexión' });
        });
    }

    function guardarCambios() {
        if (!puedeEditar || !ordenActual) return;

        var tipoCirugia = inputCirugia ? inputCirugia.value.trim() : '';
        var idProfesional = hiddenIdProfesional ? parseInt(hiddenIdProfesional.value, 10) : 0;
        var observaciones = textareaObs ? textareaObs.value.trim() : '';

        if (!tipoCirugia) {
            Swal.fire({
                icon: 'warning',
                title: 'Cirugía requerida',
                text: 'Seleccione una cirugía de las sugerencias o escriba el nombre exacto del catálogo.',
                confirmButtonColor: '#00cec9'
            });
            return;
        }
        if (!idProfesional) {
            Swal.fire({
                icon: 'warning',
                title: 'Cirujano requerido',
                text: 'Seleccione un cirujano de las sugerencias al escribir.',
                confirmButtonColor: '#00cec9'
            });
            return;
        }

        if (btnGuardar) btnGuardar.disabled = true;

        fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion: 'actualizar',
                id_orden: idOrden,
                tipoCirugia: tipoCirugia,
                id_profesional: idProfesional,
                observaciones: observaciones
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (btnGuardar) btnGuardar.disabled = false;
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: data.message || 'Orden actualizada correctamente.',
                    confirmButtonColor: '#00cec9'
                }).then(function() {
                    cargarOrden();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo guardar.'
                });
            }
        })
        .catch(function() {
            if (btnGuardar) btnGuardar.disabled = false;
            Swal.fire({ icon: 'error', title: 'Error de conexión' });
        });
    }

    initAutocompleteCatalogo({
        inputId: 'cirugia_input',
        wrapperId: 'cirugiaWrapper',
        boxId: 'sugerenciasCirugia',
        tipo: 'cirugia'
    });
    initAutocompleteCatalogo({
        inputId: 'cirujano_input',
        wrapperId: 'cirujanoWrapper',
        boxId: 'sugerenciasCirujano',
        tipo: 'cirujano'
    });

    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarCambios);
    }

    cargarOrden();
})();
