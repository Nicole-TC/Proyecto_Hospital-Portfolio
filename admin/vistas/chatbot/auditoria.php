<?php
// Vista: Auditoría del bot (OmniBot Hospital)
// Historial de eventos de conectividad: caídas del polling y recuperaciones.
// Los datos se cargan desde el backend del bot vía bot-panel-e.php (proxy PHP).
?>
<div class="container-fluid py-3">
    <div class="card p-4 shadow-sm">
        <!-- Cabecera de la Vista -->
        <div class="mb-4 pb-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-primary"><i class="fa-solid fa-clipboard-list me-2"></i> Auditoría del Bot</h4>
                <p class="text-muted mb-0">Historial de caídas y recuperaciones del servicio para control administrativo.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small text-uppercase fw-semibold">Canal</span>
                    <div class="btn-group btn-group-sm" role="group" id="bot-canal-toggle">
                        <button type="button" class="btn btn-outline-primary active" data-canal="telegram">
                            <i class="fa-brands fa-telegram me-1"></i> Telegram
                        </button>
                        <button type="button" class="btn btn-outline-success" data-canal="whatsapp">
                            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                        </button>
                    </div>
                </div>
                <small class="text-muted" id="canal-info">Mostrando auditoría de Telegram</small>
                <span class="badge bg-secondary fs-6" id="total-eventos">Cargando...</span>
            </div>
        </div>

        <!-- Tarjetas de resumen del estado del bot -->
        <div class="row g-3 mb-4">
            <!-- Estado actual del bot -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            <i class="fa-solid fa-wifi me-1"></i> Estado Actual
                        </p>
                        <div class="d-flex align-items-center gap-2 mb-2" id="resumen-estado-bot">
                            <i class="fa-solid fa-circle-notch fa-pulse text-muted" id="resumen-estado-icono"></i>
                            <span class="fw-bold" id="resumen-estado-texto">Verificando...</span>
                        </div>
                        <p class="mb-0 text-muted small" id="resumen-estado-detalle">Consultando backend del bot...</p>
                    </div>
                </div>
            </div>

            <!-- Total de caídas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Total de Caídas
                        </p>
                        <h2 class="fw-bold mb-2" style="color: #dc3545;" id="resumen-total-caidas">—</h2>
                        <p class="mb-0 text-muted small">Eventos registrados de interrupción del servicio</p>
                    </div>
                </div>
            </div>

            <!-- Total de recuperaciones -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            <i class="fa-solid fa-arrow-rotate-right me-1"></i> Recuperaciones
                        </p>
                        <h2 class="fw-bold mb-2" style="color: #20c997;" id="resumen-total-recuperaciones">—</h2>
                        <p class="mb-0 text-muted small">Veces que el bot volvió a estar funcional</p>
                    </div>
                </div>
            </div>

            <!-- Última caída -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            <i class="fa-solid fa-clock me-1"></i> Última Caída
                        </p>
                        <h2 class="fw-bold mb-2" style="color: #dc3545; font-size: 1.4rem;" id="resumen-ultima-caida">—</h2>
                        <p class="mb-0 text-muted small text-truncate" id="resumen-ultima-caida-detalle" title="">Sin caídas registradas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de auditoría -->
        <div class="card shadow-sm border rounded-3 bg-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0">Historial de Conectividad</h5>
                    <span class="text-muted small" id="texto-actualizacion">
                        <i class="fa-solid fa-rotate me-1"></i> Actualizado al cargar
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-muted small text-uppercase">
                                <th scope="col">Fecha y Hora</th>
                                <th scope="col">Evento</th>
                                <th scope="col">Detalle Técnico</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-auditoria-body">
                            <tr>
                                <td colspan="3" class="text-center text-muted small py-4">
                                    <i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando eventos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const API_URL = 'app/cir-api/endpoints/bot-panel-e.php';
            const REFRESH_MS = 30000;

            let canalActual = 'telegram';
            let eventos = [];

            const toggle = document.getElementById('bot-canal-toggle');
            const btns = toggle ? toggle.querySelectorAll('button[data-canal]') : [];
            const canalInfo = document.getElementById('canal-info');
            const estadoTexto = document.getElementById('resumen-estado-texto');
            const tituloEstado = "Estado Actual";

            if (btns.length) {
                btns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (this.classList.contains('active')) return;
                        btns.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        canalActual = this.getAttribute('data-canal') || 'telegram';
                        if (canalInfo) {
                            canalInfo.innerText = canalActual === 'whatsapp'
                                ? 'Mostrando auditoría de WhatsApp'
                                : 'Mostrando auditoría de Telegram';
                        }
                        // Forzar recarga inmediata
                        cargarAuditoria();
                    });
                });
            }

            function escapeHTML(str) {
                return String(str).replace(/[&<>'"]/g,
                    tag => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#39;',
                        '"': '&quot;'
                    }[tag] || tag)
                );
            }

            function badgeEvento(tipo) {
                const config = {
                    'caida':         ['bg-danger-subtle text-danger border-danger-subtle', 'fa-solid fa-circle-xmark', 'CAÍDA'],
                    'recuperacion':  ['bg-success-subtle text-success border-success-subtle', 'fa-solid fa-circle-check', 'RECUPERACIÓN'],
                    'inicio':        ['bg-info-subtle text-info border-info-subtle', 'fa-solid fa-power-off', 'INICIO'],
                    'detencion':     ['bg-secondary-subtle text-secondary border-secondary-subtle', 'fa-solid fa-circle-stop', 'DETENCIÓN']
                };
                const c = config[tipo] || ['bg-light text-muted border-secondary-subtle', 'fa-solid fa-circle-question', tipo.toUpperCase()];
                return `<span class="badge rounded-pill px-3 py-1 fw-semibold border ${c[0]}">
                            <i class="${c[1]} me-1"></i> ${c[2]}
                        </span>`;
            }

            function renderResumen(datos) {
                const est = datos.estado_actual || {};
                const res = datos.resumen || {};
                const enLinea = !!est.en_linea;

                const icono = document.getElementById("resumen-estado-icono");
                const texto = document.getElementById("resumen-estado-texto");
                const detalle = document.getElementById("resumen-estado-detalle");
                const card = document.getElementById("resumen-estado-bot");

                card.classList.remove("text-success", "text-danger", "text-warning");
                if (enLinea) {
                    card.classList.add("text-success");
                    icono.className = "fa-solid fa-circle-check";
                    const nombreCanal = canalActual === 'whatsapp' ? 'WhatsApp' : 'Telegram';
                    texto.innerText = "Bot " + nombreCanal + " en línea";
                    detalle.innerText = "Servidor, polling y base de datos activos.";
                } else {
                    card.classList.add("text-danger");
                    icono.className = "fa-solid fa-circle-xmark";
                    const nombreCanal = canalActual === 'whatsapp' ? 'WhatsApp' : 'Telegram';
                    texto.innerText = "Bot " + nombreCanal + " caído";
                    const partes = [];
                    if (!est.servidor) partes.push("backend");
                    if (!est.bot) partes.push("bot");
                    if (!est.polling) partes.push("polling");
                    if (!est.db) partes.push("base de datos");
                    detalle.innerText = "Fuera de servicio: " + (partes.join(", ") || "sin detalle");
                }

                document.getElementById("resumen-total-caidas").innerText = res.total_caidas ?? "—";
                document.getElementById("resumen-total-recuperaciones").innerText = res.total_recuperaciones ?? "—";

                const ultimaCaida = res.ultima_caida;
                const elFecha = document.getElementById("resumen-ultima-caida");
                const elDetalle = document.getElementById("resumen-ultima-caida-detalle");
                if (ultimaCaida) {
                    elFecha.innerText = ultimaCaida.fecha || "—";
                    elDetalle.innerText = ultimaCaida.descripcion || "";
                    elDetalle.title = ultimaCaida.descripcion || "";
                } else {
                    elFecha.innerText = "—";
                    elDetalle.innerText = "Sin caídas registradas";
                    elDetalle.title = "";
                }
            }

            function renderTabla() {
                const body = document.getElementById("tabla-auditoria-body");
                document.getElementById("total-eventos").innerText = eventos.length
                    ? (eventos.length + " eventos registrados")
                    : "Sin eventos";

                if (eventos.length === 0) {
                    body.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-muted small py-4">
                                <i class="fa-regular fa-clipboard me-1"></i>
                                Aún no hay eventos de conectividad registrados.
                            </td>
                        </tr>`;
                    return;
                }

                body.innerHTML = eventos.map(ev => `
                    <tr>
                        <td class="font-monospace text-muted small">${escapeHTML(ev.fecha || "")}</td>
                        <td>${badgeEvento(ev.tipo)}</td>
                        <td class="small">${escapeHTML(ev.descripcion || "")}</td>
                    </tr>`).join("");
            }

            async function cargarAuditoria() {
                try {
                    const url = API_URL + "?action=auditoria&limite=100&canal=" + encodeURIComponent(canalActual);
                    const res = await fetch(url);
                    const json = await res.json();
                    if (json.status !== "success") throw new Error(json.message || "Error al cargar la auditoría.");

                    eventos = json.eventos || [];
                    renderResumen(json);
                    renderTabla();
                    document.getElementById("texto-actualizacion").innerHTML =
                        `<i class="fa-solid fa-rotate me-1"></i> Actualizado ${new Date().toLocaleTimeString("es-AR")}`;
                } catch (error) {
                    console.error("Error cargando auditoría:", error);
                    document.getElementById("total-eventos").innerText = "Error de conexión";
                    const nombreCanalErr = canalActual === 'whatsapp' ? 'WhatsApp' : 'Telegram';
                    document.getElementById("tabla-auditoria-body").innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-danger small py-4">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                No se pudo conectar con el backend del bot (${escapeHTML(nombreCanalErr)}).<br>
                                <span class="text-muted">Verificá que el servicio esté activo.</span>
                            </td>
                        </tr>`;
                }
            }

            cargarAuditoria();
            setInterval(cargarAuditoria, REFRESH_MS);
        });
        </script>

        <style>
        /* === Dark Mode Compatibility Overrides === */
        body.modo-oscuro .table,
        body.modo-oscuro .card.bg-white {
            background-color: #1e1e1e !important;
            color: #f1f1f1 !important;
        }
        body.modo-oscuro .card.shadow-sm.bg-white {
            background-color: #1e1e1e !important;
            color: #f1f1f1 !important;
        }
        body.modo-oscuro thead.table-light {
            background-color: #2a2a2a !important;
            color: #bbb !important;
        }
        body.modo-oscuro thead.table-light th {
            background-color: #2a2a2a !important;
            color: #bbb !important;
        }
        body.modo-oscuro .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }
        body.modo-oscuro .table-striped > tbody > tr:nth-of-type(even) > * {
            background-color: #1e1e1e !important;
        }
        body.modo-oscuro .bg-danger-subtle {
            background-color: rgba(248, 139, 139, 0.15) !important;
            color: #f28b8b !important;
            border-color: rgba(248, 139, 139, 0.3) !important;
        }
        body.modo-oscuro .bg-success-subtle {
            background-color: rgba(107, 216, 141, 0.15) !important;
            color: #6bd88d !important;
            border-color: rgba(107, 216, 141, 0.3) !important;
        }
        body.modo-oscuro .bg-info-subtle {
            background-color: rgba(102, 217, 239, 0.15) !important;
            color: #66d9ef !important;
            border-color: rgba(102, 217, 239, 0.3) !important;
        }
        body.modo-oscuro .bg-secondary-subtle {
            background-color: rgba(170, 170, 170, 0.15) !important;
            color: #aaa !important;
            border-color: rgba(170, 170, 170, 0.3) !important;
        }
        body.modo-oscuro .badge.bg-secondary {
            background-color: #3a3a3a !important;
            color: #e0e0e0 !important;
        }
        </style>
    </div>
</div>