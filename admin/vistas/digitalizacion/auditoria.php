<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Filtros -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title fw-bold text-info"><i class="fa-solid fa-history me-2"></i> Auditoría</h3>
                    <p class="text-muted">Filtrá las acciones realizadas en el sistema de digitalización.</p>
                </div>
                <div class="card-body p-4">
                    <form id="form-auditoria">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="filtro_tipo" class="form-label fw-bold">Tipo de acción</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-list-check text-muted"></i></span>
                                    <select class="form-select bg-light border-0" id="filtro_tipo" name="tipo">
                                        <option value="subidos">Subidos</option>
                                        <option value="eliminados">Eliminados</option>
                                        <option value="actualizados">Actualizados</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="filtro_tabla" class="form-label fw-bold">Tabla afectada</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-table text-muted"></i></span>
                                    <select class="form-select bg-light border-0" id="filtro_tabla" name="tabla_afectada">
                                        <option value="">-- Todas --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Rango de fechas</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-calendar-days text-muted"></i></span>
                                    <input type="date" class="form-control bg-light border-0" id="filtro_fecha_desde" name="fecha_desde" title="Fecha Desde">
                                    <span class="input-group-text bg-light border-0 px-2">y</span>
                                    <input type="date" class="form-control bg-light border-0" id="filtro_fecha_hasta" name="fecha_hasta" title="Fecha Hasta">
                                </div>
                            </div>

                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="button" id="btn-limpiar" class="btn btn-light rounded-pill px-4 me-2">Limpiar</button>
                                <button type="submit" class="btn btn-info text-white rounded-pill px-5 shadow-sm fw-bold">
                                    <i class="fa-solid fa-search me-2"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alerta de error -->
            <div id="auditoria-alerta" class="alert rounded-4 shadow-sm" style="display:none;" role="alert"></div>

            <!-- Resultados -->
            <div class="card shadow-sm border-0 rounded-4 mt-4" id="card-resultados" style="display: none;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3 gap-3">
                        <div class="d-flex align-items-center">
                            <label for="ordenar-auditoria" class="form-label fw-bold mb-0 me-2">Ordenar por:</label>
                            <select id="ordenar-auditoria" class="form-select form-select-sm w-auto bg-light">
                                <option value="6,desc">Fecha (más reciente)</option>
                                <option value="6,asc">Fecha (más antiguo)</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tabla-auditoria" class="table table-hover table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Tabla afectada</th>
                                    <th>Tipo Doc.</th>
                                    <th>IP Origen</th>
                                    <th>Fecha</th>
                                    <th>Ver</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-detalle-auditoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info fw-bold">
                    <i class="fa-solid fa-eye me-2"></i><span id="modal-titulo">Detalle</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3 p-3 modal-context rounded-3" id="modal-contexto"></div>
                <div id="modal-detalle-body"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --aud-bg: #f8f9fa;
    --aud-border: #e9ecef;
    --aud-text: #212529;
    --aud-text-muted: #6c757d;
    --aud-text-placeholder: #6c757d;
    --aud-input-bg: #f8f9fa;
    --aud-table-head: #f1f3f5;
    --aud-diff-changed: #fff8e1;
    --aud-diff-changed-border: #ffe082;
    --aud-modal-bg: #ffffff;
    --aud-modal-border: transparent;
    --aud-focus-shadow: rgba(0, 123, 255, 0.25);
    --aud-focus-border: #80bdff;
}
body.modo-oscuro {
    --aud-bg: #2a2a2a;
    --aud-border: #444;
    --aud-text: #f1f1f1;
    --aud-text-muted: #adb5bd;
    --aud-text-placeholder: #888;
    --aud-input-bg: #2a2a2a;
    --aud-table-head: #333;
    --aud-diff-changed: #3d3520;
    --aud-diff-changed-border: #5a4d2a;
    --aud-modal-bg: #2a2a2a;
    --aud-modal-border: #444;
}

.rounded-4 { border-radius: 1rem !important; }
.bg-light { background-color: var(--aud-bg) !important; color: var(--aud-text) !important; }
.table-hover tbody tr:hover { background-color: var(--aud-bg); }

td.btn-detalle { text-align: center; }
.btn-detalle-modal {
    border-radius: 50%;
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform .15s, background-color .15s;
}
.btn-detalle-modal:hover { transform: scale(1.08); }

.modal-context { background-color: var(--aud-bg); border: 1px solid var(--aud-border); }
.modal-context .ctx-item small { display: block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: .5px; color: var(--aud-text-muted); }
.modal-context .ctx-item .ctx-value { font-weight: 600; color: var(--aud-text); font-size: 0.95rem; }

.diff-card { border-radius: 0.75rem; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: 1px solid var(--aud-border); }
.diff-card-header { background-color: var(--aud-bg); padding: 0.75rem 1rem; font-weight: 600; border-bottom: 1px solid var(--aud-border); color: var(--aud-text); }

.diff-table { margin: 0; color: var(--aud-text); }
.diff-table thead th { background-color: var(--aud-table-head); font-size: 0.75rem; text-transform: uppercase; letter-spacing: .5px; color: var(--aud-text-muted); border-bottom: 2px solid var(--aud-border); padding: 0.6rem 0.75rem; }
.diff-table tbody td { vertical-align: middle; padding: 0.65rem 0.75rem; font-size: 0.9rem; border-color: var(--aud-border); }
.diff-table tbody tr.diff-changed { background-color: var(--aud-diff-changed); }
.diff-table tbody tr.diff-changed td { border-color: var(--aud-diff-changed-border); }
.diff-table .diff-campo { font-family: 'SFMono-Regular', Menlo, Consolas, monospace; font-size: 0.85rem; color: var(--aud-text-muted); min-width: 140px; }
.diff-table .diff-valor { word-break: break-word; }

/* Overrides puntuales para clases Bootstrap no tematizables por variable */
body.modo-oscuro .card-header.bg-white { background-color: transparent !important; }
body.modo-oscuro .form-control,
body.modo-oscuro .form-select { background-color: var(--aud-input-bg) !important; color: #fff !important; }
body.modo-oscuro .form-control.border-0,
body.modo-oscuro .form-select.border-0,
body.modo-oscuro .input-group-text.border-0,
body.modo-oscuro .form-control:not(.border-0),
body.modo-oscuro .form-select:not(.border-0) { border: 1px solid var(--aud-border) !important; }
body.modo-oscuro .form-control:focus,
body.modo-oscuro .form-select:focus { background-color: #333 !important; color: #fff !important; border-color: var(--aud-focus-border) !important; outline: 0; box-shadow: 0 0 0 0.2rem var(--aud-focus-shadow) !important; }
body.modo-oscuro .input-group-text { background-color: #333 !important; color: #fff !important; border-color: var(--aud-border) !important; }
body.modo-oscuro .text-muted { color: var(--aud-text-muted) !important; }
body.modo-oscuro .table-hover tbody tr:hover { background-color: #333 !important; color: #fff !important; }
body.modo-oscuro .table-light { background-color: var(--aud-table-head) !important; color: #fff !important; }
body.modo-oscuro ::placeholder { color: var(--aud-text-placeholder) !important; }
body.modo-oscuro .btn-light { background-color: #3a3a3a !important; color: #fff !important; border-color: var(--aud-border) !important; }
body.modo-oscuro .btn-light:hover { background-color: #4a4a4a !important; color: #fff !important; }
body.modo-oscuro .modal-content { background-color: var(--aud-modal-bg); color: var(--aud-text); border: 1px solid var(--aud-modal-border); }
body.modo-oscuro .modal-header,
body.modo-oscuro .modal-footer { border-color: var(--aud-border); }
body.modo-oscuro .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
</style>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
const ACCION_META = {
    INSERT: { badge: 'bg-success',           icon: 'fa-plus-circle',            color: 'text-success', header: 'Datos del registro creado' },
    DELETE: { badge: 'bg-danger',            icon: 'fa-trash-can',              color: 'text-danger',  header: 'Datos del registro eliminado' },
    UPDATE: { badge: 'bg-warning text-dark', icon: 'fa-arrow-right-arrow-left', color: 'text-warning', header: 'Cambios detectados' }
};
const metaAccion = a => ACCION_META[(a||'').toUpperCase()] || { badge: 'bg-secondary', icon: 'fa-circle', color: 'text-secondary', header: 'Detalle' };
const EMPTY = '<span class="text-muted">—</span>';
const ESC_MAP = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
const escapeHtml = s => s == null ? '-' : String(s).replace(/[&<>"']/g, c => ESC_MAP[c]);

document.addEventListener("DOMContentLoaded", async function() {

    // 0. Setear max en input fecha_hasta (hoy) para impedir fechas futuras nativamente
    document.getElementById("filtro_fecha_hasta").max = new Date().toISOString().slice(0, 10);

    // 1. Tablas
    try {
        const response = await fetch("api/digitalizar/get_tablas_afectadas.php");
        const json = await response.json();
        if (json.status === 'success' && json.data.tablas_afectadas) {
            const selectTabla = document.getElementById("filtro_tabla");
            if (selectTabla) {
                json.data.tablas_afectadas.forEach(t => {
                    const option = document.createElement("option");
                    option.value = t.value;
                    option.textContent = t.label;
                    selectTabla.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error("Error al cargar tablas afectadas:", error);
    }

    // 2. Helpers
    const fmtDate = s => {
        if (!s) return undefined;
        const [y, m, d] = s.split('-');
        return (y && m && d) ? `${d}-${m}-${y}` : undefined;
    };
    const safeParse = v => {
        if (v == null) return null;
        if (typeof v === 'object') return v;
        try { return JSON.parse(v); } catch { return null; }
    };
    const formatValue = v => {
        if (v === null || v === undefined || v === '') return EMPTY;
        if (typeof v === 'boolean') {
            return v
                ? '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Activo</span>'
                : '<span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>Inactivo</span>';
        }
        if (typeof v === 'object') return '<code>' + escapeHtml(JSON.stringify(v)) + '</code>';
        const s = String(v);
        if (s.match(/^\d{4}-\d{2}-\d{2}/)) {
            return '<span class="text-muted"><i class="fa-regular fa-calendar me-1"></i>' + escapeHtml(s) + '</span>';
        }
        return escapeHtml(s);
    };
    const fmtDisplayDate = s => {
        if (!s) return '-';
        const [fecha, hora] = s.split(' ');
        const partes = fecha.split('-');
        if (partes.length !== 3) return s;
        const [anio, mes, dia] = partes;
        let r = `${dia}/${mes}/${anio}`;
        if (hora) {
            const [hh, mm] = hora.split(':');
            r += ` ${hh}:${mm}`;
        }
        return r;
    };
    const mostrarAlerta = (tipo, msg) => {
        const el = document.getElementById("auditoria-alerta");
        el.className = `alert alert-${tipo} rounded-4 shadow-sm`;
        el.innerHTML = msg ? `<i class="fa-solid fa-triangle-exclamation me-2"></i>${escapeHtml(msg)}` : '';
        el.style.display = msg ? "block" : "none";
    };

    // 3. Diff
    function buildDiffView(accion, anteriorRaw, nuevoRaw, row) {
        const antObj = safeParse(anteriorRaw);
        const nueObj = safeParse(nuevoRaw);
        const meta = metaAccion(accion);

        [antObj, nueObj].forEach(obj => {
            if (!obj) return;
            Object.keys(obj).forEach(k => { if (k === 'id' || k.startsWith('id_')) delete obj[k]; });
            if (row && row.tabla_afectada === 'documentos') {
                obj.servicio = row.servicio;
                obj.personal = row.personal_documento;
                obj.tipo_documento = row.tipo_documento;
                obj.dni = row.dni;
            }
        });

        if (!antObj && !nueObj) {
            return '<div class="text-center text-muted py-4"><i class="fa-solid fa-circle-info me-2"></i>Sin información de cambios registrados.</div>';
        }

        const headerHtml = `<div class="diff-card-header"><i class="fa-solid ${meta.icon} ${meta.color} me-2"></i>${meta.header}`;

        // INSERT o DELETE: solo un lado
        if (!antObj || !nueObj) {
            const data = nueObj || antObj;
            const rows = Object.keys(data).sort().map(k => {
                const raw = data[k];
                const isObj = typeof raw === 'object' && raw !== null;
                return `<tr><td class="diff-campo"><strong>${escapeHtml(k)}</strong></td><td class="diff-valor">${isObj ? '<code>' + escapeHtml(JSON.stringify(raw, null, 2)) + '</code>' : formatValue(raw)}</td></tr>`;
            }).join('');
            return `<div class="diff-card">${headerHtml}</div>
                <div class="diff-card mt-0" style="border-top:0;">
                <table class="table diff-table mb-0">
                    <thead><tr><th style="width: 30%;">Campo</th><th>Valor</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
        }

        // UPDATE: 1 sola pasada
        let changedCount = 0;
        const rows = Object.keys({...antObj, ...nueObj}).sort().map(key => {
            const a = antObj[key], n = nueObj[key];
            const changed = JSON.stringify(a) !== JSON.stringify(n);
            if (changed) changedCount++;
            const badge = changed ? ' <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">modificado</span>' : '';
            return `<tr class="${changed ? 'diff-changed' : ''}"><td class="diff-campo"><strong>${escapeHtml(key)}</strong>${badge}</td><td class="diff-valor">${formatValue(a)}</td><td class="diff-valor">${formatValue(n)}</td></tr>`;
        }).join('');

        const summary = changedCount > 0
            ? `<span class="badge bg-warning text-dark ms-2">${changedCount} campo${changedCount === 1 ? '' : 's'} modificado${changedCount === 1 ? '' : 's'}</span>`
            : '<span class="badge bg-secondary ms-2">sin cambios</span>';

        return `<div class="diff-card">${headerHtml}${summary}</div>
            <div class="diff-card mt-0" style="border-top:0;">
            <table class="table diff-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 25%;">Campo</th>
                        <th style="width: 37.5%;"><i class="fa-solid fa-clock-rotate-left me-1 text-danger"></i>Valor anterior</th>
                        <th style="width: 37.5%;"><i class="fa-solid fa-circle-check me-1 text-success"></i>Valor nuevo</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;
    }

    // Búsqueda sin tildes
    function sinTildes(s) {
        if (s === null || s === undefined) return '';
        return String(s).normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
    $.fn.dataTable.ext.type.search.string = function(data) {
        return sinTildes(data);
    };

    // 4. DataTable
    const tabla = $('#tabla-auditoria').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        responsive: true,
        initComplete: function() {
            var api = this.api();
            $(api.table().container()).find('.dataTables_filter input').on('input', function() {
                var pos = this.selectionStart;
                var norm = sinTildes(this.value);
                this.value = norm;
                this.setSelectionRange(pos, pos);
                api.search(norm).draw();
            });
        },
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        drawCallback: function() {
            const data = this.api().data();
            const hasDocs = data.toArray().some(r => r.tabla_afectada === 'documentos');
            this.api().column(4).visible(hasDocs);
        },
        ajax: {
            url: 'api/digitalizar/api_auditoria_dig.php',
            type: 'GET',
            dataSrc: 'data.auditoria',
            data: function(d) {
                d.tipo = $('#filtro_tipo').val();
                d.tabla_afectada = $('#filtro_tabla').val() || undefined;
                d.fecha_desde = fmtDate($('#filtro_fecha_desde').val());
                d.fecha_hasta = fmtDate($('#filtro_fecha_hasta').val());
            },
            error: function(xhr) {
                let msg = 'Error desconocido al consultar la auditoría.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                mostrarAlerta('danger', msg);
                tabla.clear().draw();
                $('#card-resultados').show();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'usuario' },
            { data: 'accion', render: (d, type) => type === 'display' ? `<span class="badge ${metaAccion(d).badge}">${(d || '-').toUpperCase()}</span>` : (d || '-') },
            { data: 'tabla_afectada' },
            { data: 'tipo_documento', render: (d, t, row) => row.tabla_afectada === 'documentos' ? d : '' },
            { data: 'ip_origen' },
            { data: 'fecha_accion', render: (data, type) => type === 'display' ? fmtDisplayDate(data) : data },
            {
                data: null,
                className: 'btn-detalle',
                orderable: false,
                render: () => '<button type="button" class="btn btn-sm btn-outline-info btn-detalle-modal" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>'
            }
        ],
        deferLoading: 0
    });

    // 5. Selector de ordenamiento
    document.getElementById('ordenar-auditoria').addEventListener('change', function() {
        const [col, dir] = this.value.split(',');
        tabla.order([[parseInt(col), dir]]).draw();
    });

    // 6. Click ojo
    let modalInstancia = null;
    $('#tabla-auditoria tbody').on('click', '.btn-detalle-modal', function() {
        const row = tabla.row($(this).closest('tr')).data();
        if (!row) return;

        const accion = (row.accion || '').toUpperCase();
        const meta = metaAccion(accion);

        $('#modal-titulo').text('Detalle #' + row.id);
        $('#modal-contexto').html(`
            <div class="col-md-3 col-6 ctx-item"><small>Usuario</small><div class="ctx-value">${escapeHtml(row.usuario)}</div></div>
            <div class="col-md-2 col-6 ctx-item"><small>Acción</small><div class="ctx-value"><span class="badge ${meta.badge}">${escapeHtml(accion)}</span></div></div>
            <div class="col-md-3 col-6 ctx-item"><small>Tabla</small><div class="ctx-value">${escapeHtml(row.tabla_afectada)}</div></div>
            <div class="col-md-2 col-6 ctx-item"><small>IP</small><div class="ctx-value"><code>${escapeHtml(row.ip_origen)}</code></div></div>
            <div class="col-md-2 col-12 ctx-item"><small>Fecha</small><div class="ctx-value">${escapeHtml(fmtDisplayDate(row.fecha_accion))}</div></div>
        `);
        $('#modal-detalle-body').html(buildDiffView(accion, row.valor_anterior, row.valor_nuevo, row));

        const modalEl = document.getElementById('modal-detalle-auditoria');
        if (!modalInstancia) modalInstancia = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstancia.show();
    });

    // 6. Limpiar
        document.getElementById("btn-limpiar").addEventListener("click", function() {
        document.getElementById("form-auditoria").reset();
        document.getElementById("filtro_fecha_hasta").max = new Date().toISOString().slice(0, 10);
        document.getElementById("card-resultados").style.display = "none";
        mostrarAlerta('danger', '');
        tabla.clear().draw();
    });

    // 7. Submit
    $('#form-auditoria').on('submit', function(e) {
        e.preventDefault();
        const desde = $('#filtro_fecha_desde').val();
        const hasta = $('#filtro_fecha_hasta').val();
        const tablaAfectada = $('#filtro_tabla').val();
        const hoy = new Date().toISOString().slice(0, 10);
        if (hasta && hasta > hoy) {
            mostrarAlerta('danger', 'La fecha "hasta" no puede ser una fecha futura.');
        } else if (desde && hasta && desde > hasta) {
            mostrarAlerta('danger', 'La fecha "desde" no puede ser mayor que la fecha "hasta".');
        } else if (!tablaAfectada && !desde && !hasta) {
            mostrarAlerta('danger', 'Debe seleccionar al menos un filtro adicional: Tabla afectada o Rango de fechas.');
        } else {
            mostrarAlerta('danger', '');
            document.getElementById("card-resultados").style.display = "block";
            tabla.ajax.reload();
            return;
        }
        tabla.clear().draw();
        document.getElementById("card-resultados").style.display = "block";
    });
});
</script>
