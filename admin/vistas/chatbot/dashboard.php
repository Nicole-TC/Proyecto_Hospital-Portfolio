<?php
// Vista: Dashboard del bot (OmniBot Hospital)
// Datos desde bot-panel-e.php: estado + conversaciones + auditoría.
?>
<div class="container-fluid py-3">
    <div class="card p-4 shadow-sm">
        <div class="mb-4 pb-3 border-bottom d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-bold text-primary mb-1">
                    <i class="fa-solid fa-chart-line me-2"></i>Dashboard
                </h4>
                <p class="text-muted mb-0">Estado del bot y métricas de conversaciones.</p>
            </div>
            <span class="badge bg-secondary align-self-center" id="dash-estado-general">
                <i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando...
            </span>
        </div>

        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            Estado del servicio
        </h6>
        <div class="row g-3 mb-4" id="dash-cards-estado">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Servidor API</p>
                        <h2 class="fw-bold mb-2 text-muted" id="dash-servidor">—</h2>
                        <p class="mb-0 text-muted small">Respuesta del backend del bot</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Bot Telegram</p>
                        <h2 class="fw-bold mb-2 text-muted" id="dash-bot">—</h2>
                        <p class="mb-0 text-muted small">Instancia del bot inicializada</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Polling</p>
                        <h2 class="fw-bold mb-2 text-muted" id="dash-polling">—</h2>
                        <p class="mb-0 text-muted small">Recepción de mensajes activa</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Base de datos</p>
                        <h2 class="fw-bold mb-2 text-muted" id="dash-db">—</h2>
                        <p class="mb-0 text-muted small">Conexión a la BD del bot</p>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            Conversaciones
        </h6>
        <div class="row g-3" id="dash-cards-conversaciones">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Total chats</p>
                        <h2 class="fw-bold mb-2" style="color: #20c997;" id="dash-total-chats">—</h2>
                        <p class="mb-0 text-muted small">Conversaciones registradas</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Identificados</p>
                        <h2 class="fw-bold mb-2" style="color: #20c997;" id="dash-identificados">—</h2>
                        <p class="mb-0 text-muted small">Chats con DNI vinculado</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Anónimos</p>
                        <h2 class="fw-bold mb-2" style="color: #20c997;" id="dash-anonimos">—</h2>
                        <p class="mb-0 text-muted small">Chats sin identificar</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Mensajes</p>
                        <h2 class="fw-bold mb-2" style="color: #20c997;" id="dash-total-mensajes">—</h2>
                        <p class="mb-0 text-muted small">Suma de mensajes en esos chats</p>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="fw-bold text-muted text-uppercase mb-3 mt-4" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            Auditoría
        </h6>
        <div class="row g-3" id="dash-cards-auditoria">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Caídas</p>
                        <h2 class="fw-bold mb-2" style="color: #dc3545;" id="dash-total-caidas">—</h2>
                        <p class="mb-0 text-muted small">Eventos de caída registrados</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Recuperaciones</p>
                        <h2 class="fw-bold mb-2" style="color: #20c997;" id="dash-total-recuperaciones">—</h2>
                        <p class="mb-0 text-muted small">Eventos de recuperación</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Última caída</p>
                        <h2 class="fw-bold mb-0 fs-5" style="color: #dc3545;" id="dash-ultima-caida">—</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 border rounded-3 bg-white">
                    <div class="card-body">
                        <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Última recuperación</p>
                        <h2 class="fw-bold mb-0 fs-5" style="color: #20c997;" id="dash-ultima-recuperacion">—</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard: pide datos al backend y los muestra en las tarjetas.
document.addEventListener("DOMContentLoaded", function () {

    var API_URL = "app/cir-api/endpoints/bot-panel-e.php";

    // Guardamos la ultima respuesta buena, por si el bot se cae
    var datosConversacionesOk = null;
    var datosAuditoriaOk = null;

    // Escribe un texto en un elemento por su id
    function escribir(id, texto, color) {
        var el = document.getElementById(id);
        if (!el) {
            return;
        }
        el.textContent = texto;
        if (color) {
            el.style.color = color;
        }
    }

    // ----- 1) Estado del bot -----
    function cargarEstado() {
        fetch(API_URL + "?action=estado")
            .then(function (respuesta) {
                return respuesta.json();
            })
            .then(function (datos) {
                var badge = document.getElementById("dash-estado-general");

                if (datos.status !== "success") {
                    throw new Error("No se pudo leer el estado");
                }

                // Verde si esta bien, rojo si no
                escribir("dash-servidor", datos.servidor ? "Online" : "Offline", datos.servidor ? "#20c997" : "#dc3545");
                escribir("dash-bot", datos.bot ? "Activo" : "Inactivo", datos.bot ? "#20c997" : "#dc3545");
                escribir("dash-polling", datos.polling ? "Activo" : "Inactivo", datos.polling ? "#20c997" : "#dc3545");
                escribir("dash-db", datos.db ? "OK" : "Caída", datos.db ? "#20c997" : "#dc3545");

                var enLinea = datos.bot && datos.servidor && datos.polling && datos.db;
                if (badge) {
                    if (enLinea) {
                        badge.className = "badge align-self-center bg-success";
                        badge.innerHTML = '<i class="fa-solid fa-wifi me-1"></i> Bot en línea';
                    } else {
                        badge.className = "badge align-self-center bg-danger";
                        badge.innerHTML = '<i class="fa-solid fa-wifi me-1"></i> Bot caído';
                    }
                }
            })
            .catch(function () {
                var badge = document.getElementById("dash-estado-general");
                escribir("dash-servidor", "Error", "#dc3545");
                escribir("dash-bot", "Error", "#dc3545");
                escribir("dash-polling", "Error", "#dc3545");
                escribir("dash-db", "Error", "#dc3545");
                if (badge) {
                    badge.className = "badge bg-danger align-self-center";
                    badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Sin conexión';
                }
            });
    }

    // ----- 2) Conversaciones -----
    function mostrarConversaciones(lista, total) {
        var identificados = 0;
        var anonimos = 0;
        var mensajes = 0;
        var i;

        for (i = 0; i < lista.length; i++) {
            if (lista[i].estado === "identificado") {
                identificados = identificados + 1;
            } else {
                anonimos = anonimos + 1;
            }
            mensajes = mensajes + (Number(lista[i].n_mensajes) || 0);
        }

        escribir("dash-total-chats", String(total), "#20c997");
        escribir("dash-identificados", String(identificados), "#20c997");
        escribir("dash-anonimos", String(anonimos), "#20c997");
        escribir("dash-total-mensajes", String(mensajes), "#20c997");
    }

    function cargarConversaciones() {
        fetch(API_URL + "?action=conversaciones&limite=500")
            .then(function (respuesta) {
                return respuesta.json();
            })
            .then(function (datos) {
                if (datos.status !== "success") {
                    throw new Error("No se pudieron leer las conversaciones");
                }

                var lista = datos.conversaciones || [];
                var total = datos.cantidad;
                if (typeof total !== "number") {
                    total = lista.length;
                }

                // Guardamos por si despues falla el pedido
                datosConversacionesOk = {
                    lista: lista,
                    total: total
                };
                mostrarConversaciones(lista, total);
            })
            .catch(function () {
                // Si falla, mostramos lo ultimo que habia salido bien
                if (datosConversacionesOk) {
                    mostrarConversaciones(datosConversacionesOk.lista, datosConversacionesOk.total);
                }
            });
    }

    // ----- 3) Auditoria -----
    function mostrarAuditoria(resumen) {
        var caida = resumen.ultima_caida;
        var recup = resumen.ultima_recuperacion;

        escribir("dash-total-caidas", String(resumen.total_caidas || 0), "#dc3545");
        escribir("dash-total-recuperaciones", String(resumen.total_recuperaciones || 0), "#20c997");

        if (caida && caida.fecha) {
            escribir("dash-ultima-caida", caida.fecha, "#dc3545");
        } else {
            escribir("dash-ultima-caida", "—", "#dc3545");
        }

        if (recup && recup.fecha) {
            escribir("dash-ultima-recuperacion", recup.fecha, "#20c997");
        } else {
            escribir("dash-ultima-recuperacion", "—", "#20c997");
        }
    }

    function cargarAuditoria() {
        fetch(API_URL + "?action=auditoria&limite=100")
            .then(function (respuesta) {
                return respuesta.json();
            })
            .then(function (datos) {
                if (datos.status !== "success") {
                    throw new Error("No se pudo leer la auditoria");
                }

                var resumen = datos.resumen || {};
                datosAuditoriaOk = resumen;
                mostrarAuditoria(resumen);
            })
            .catch(function () {
                if (datosAuditoriaOk) {
                    mostrarAuditoria(datosAuditoriaOk);
                }
            });
    }

    // Carga todo el dashboard
    function cargarDashboard() {
        cargarEstado();
        cargarConversaciones();
        cargarAuditoria();
    }

    // Primera carga
    cargarDashboard();

    // Cada 15 segundos vuelve a pedir los datos
    setInterval(function () {
        cargarDashboard();
    }, 15000);

});
</script>
