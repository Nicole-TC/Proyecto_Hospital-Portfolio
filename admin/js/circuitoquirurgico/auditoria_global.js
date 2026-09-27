// Auditoria Global - consulta del historial contra auditoria-e.php.
// El endpoint ya devuelve los eventos traducidos al lenguaje del circuito
// (accion realizada, paciente afectado y detalle), asi que aca solo se arman
// los filtros, se pinta la tabla y se manejan los estados de carga, vacio y
// error.

// La paginacion se conecta en CIR-808. Se declara igual porque los selects de
// orden y limite de la vista la reinician al cambiar.
var paginaAuditoria = 1;

// Antiguedad maxima que se deja consultar. El backend aplica el mismo tope,
// esto solo evita que el usuario elija fechas que despues se van a recortar.
var MESES_MAX_AUDITORIA = 3;

function initAuditoria() {
    ajustarRangoAuditoria();
    // Un historial arranca mostrando lo ultimo que paso, sin pedir filtros.
    ejecutarBusquedaAuditoria();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuditoria);
} else {
    initAuditoria();
}

// El detalle y el nombre del paciente son texto libre cargado por usuarios,
// asi que se escapan antes de armar la fila.
function escaparAuditoria(valor) {
    if (valor === null || valor === undefined) return '';
    return String(valor)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Fecha local en YYYY-MM-DD. No se usa toISOString() porque pasa a UTC y
// despues de las 21 hs ya devuelve el dia siguiente.
function fechaIsoAuditoria(d) {
    var mes = String(d.getMonth() + 1).padStart(2, '0');
    var dia = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + mes + '-' + dia;
}

function limitesFechaAuditoria() {
    var hoy = new Date();
    var minimo = new Date(hoy.getFullYear(), hoy.getMonth() - MESES_MAX_AUDITORIA, hoy.getDate());
    return { min: fechaIsoAuditoria(minimo), max: fechaIsoAuditoria(hoy) };
}

// Mantiene los calendarios dentro de los ultimos 3 meses y encadenados entre
// si: HASTA no ofrece dias anteriores a DESDE ni DESDE posteriores a HASTA.
function ajustarRangoAuditoria() {
    var desde = document.getElementById('filtroDesdeAuditoria');
    var hasta = document.getElementById('filtroHastaAuditoria');
    if (!desde || !hasta) return;

    var lim = limitesFechaAuditoria();
    desde.min = lim.min;
    desde.max = hasta.value || lim.max;
    hasta.min = desde.value || lim.min;
    hasta.max = lim.max;
}

// El min/max del input no impide tipear la fecha a mano, asi que el rango se
// revisa otra vez antes de consultar. Devuelve el mensaje de error o ''.
function validarRangoAuditoria(desde, hasta) {
    var lim = limitesFechaAuditoria();
    if ((desde !== '' && desde < lim.min) || (hasta !== '' && hasta < lim.min)) {
        return 'Solo se pueden consultar registros de hasta ' + MESES_MAX_AUDITORIA + ' meses de antigüedad.';
    }
    if ((desde !== '' && desde > lim.max) || (hasta !== '' && hasta > lim.max)) {
        return 'Las fechas no pueden ser posteriores al día de hoy.';
    }
    if (desde !== '' && hasta !== '' && hasta < desde) {
        return 'La fecha HASTA no puede ser anterior a la fecha DESDE.';
    }
    return '';
}

function ejecutarBusquedaAuditoria() {
    var tbody = document.getElementById('tbodyAuditoria');
    var vacio = document.getElementById('emptyAuditoria');
    var info  = document.getElementById('infoAuditoria');
    if (!tbody) return;

    var tipo  = document.getElementById('filtroTipoAuditoria').value;
    var valor = document.getElementById('valorBuscadorAuditoria').value.trim();
    var desde = document.getElementById('filtroDesdeAuditoria').value;
    var hasta = document.getElementById('filtroHastaAuditoria').value;

    var errorRango = validarRangoAuditoria(desde, hasta);
    if (errorRango !== '') {
        mostrarErrorAuditoria(errorRango);
        return;
    }

    var url = 'app/cir-api/endpoints/auditoria-e.php?tipo=' + encodeURIComponent(tipo);
    if (valor !== '') url += '&valor=' + encodeURIComponent(valor);
    if (desde !== '') url += '&desde=' + encodeURIComponent(desde);
    if (hasta !== '') url += '&hasta=' + encodeURIComponent(hasta);

    vacio.classList.add('d-none');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-secondary">' +
                      '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>' +
                      'Buscando registros...</td></tr>';

    fetch(url, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        // El endpoint responde HTTP 200 aun con la sesion vencida, asi que el
        // resultado se mira en el cuerpo y no en el codigo de estado.
        if (!data || data.status !== 'success') {
            mostrarErrorAuditoria(data && data.message ? data.message : 'No se pudo consultar la auditoría.');
            return;
        }

        var registros = data.registros || [];
        tbody.innerHTML = '';

        if (registros.length === 0) {
            vacio.classList.remove('d-none');
            info.textContent = 'Mostrando 0 registros';
            return;
        }

        registros.forEach(function (r) {
            var guion = '<span class="text-secondary">—</span>';
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="ps-4 py-3 text-nowrap">' + escaparAuditoria(r.fecha_hora) + '</td>' +
                '<td class="py-3">' + escaparAuditoria(r.usuario) + '</td>' +
                '<td class="py-3">' + escaparAuditoria(r.accion_realizada) + '</td>' +
                '<td class="py-3">' + (r.paciente ? escaparAuditoria(r.paciente) : guion) + '</td>' +
                '<td class="py-3">' + (r.detalle ? escaparAuditoria(r.detalle) : guion) + '</td>';
            tbody.appendChild(tr);
        });

        info.textContent = 'Mostrando ' + registros.length + ' de ' + data.total + ' registros';
    })
    .catch(function () {
        mostrarErrorAuditoria('Error al conectar con el servicio.');
    });
}

function mostrarErrorAuditoria(mensaje) {
    document.getElementById('emptyAuditoria').classList.add('d-none');
    document.getElementById('tbodyAuditoria').innerHTML =
        '<tr><td colspan="5" class="text-center py-4 text-danger">' +
        '<i class="bi bi-exclamation-triangle me-2"></i>' + escaparAuditoria(mensaje) +
        '</td></tr>';
    document.getElementById('infoAuditoria').textContent = 'Mostrando 0 registros';
}
