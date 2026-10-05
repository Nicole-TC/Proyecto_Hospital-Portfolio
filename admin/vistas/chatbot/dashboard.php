<?php
// Vista: Dashboard del bot (OmniBot Hospital)
// Datos desde bot-panel-e.php: estado + conversaciones + auditoría + comandos.
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
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="btn-group btn-group-sm" role="group" id="dash-canal-toggle">
                    <button type="button" class="btn btn-outline-primary active" data-canal="telegram">
                        <i class="fa-brands fa-telegram me-1"></i> Telegram
                    </button>
                    <button type="button" class="btn btn-outline-success" data-canal="whatsapp">
                        <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                    </button>
                </div>
                <span class="badge bg-secondary align-self-center" id="dash-estado-general">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando...
                </span>
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

        <div id="dash-bloque-comandos">
        <h6 class="fw-bold text-muted text-uppercase mb-3 mt-4" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            Comandos
        </h6>
        <div class="card shadow-sm border rounded-3 bg-white">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Comandos más utilizados</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th scope="col">Comandos</th>
                                <th scope="col">Cantidad de usos</th>
                                <th scope="col">Proporción</th>
                            </tr>
                        </thead>
                        <tbody id="dash-comandos-tbody">
                            <tr>
                                <td colspan="3" class="text-muted">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
    // Dashboard: pide datos al backend y los muestra en las tarjetas.
    document.addEventListener("DOMContentLoaded", function() {

        var API_URL = "app/cir-api/endpoints/bot-panel-e.php";
        var canalActual = "telegram";

        // Guardamos la ultima respuesta buena, por si el bot se cae
        var datosConversacionesOk = null;
        var datosAuditoriaOk = null;
        var datosComandosOk = null;

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
            var canalPedido = canalActual;
            fetch(API_URL + "?action=estado")
                .then(function(respuesta) {
                    return respuesta.json();
                })
                .then(function(datos) {
                    var badge = document.getElementById("dash-estado-general");

                    if (canalPedido !== canalActual) {
                        return;
                    }
                    if (datos.status !== "success") {
                        throw new Error("No se pudo leer el estado");
                    }

                    var info = datos;
                    if (canalPedido === "whatsapp") {
                        info = (datos.canales && datos.canales.whatsapp) ? datos.canales.whatsapp : {};
                    }
                    var enLinea = info.bot && info.servidor && info.polling && info.db;
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
                .catch(function() {
                    var badge = document.getElementById("dash-estado-general");
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
            var canalPedido = canalActual;
            fetch(API_URL + "?action=conversaciones&limite=500")
                .then(function(respuesta) {
                    return respuesta.json();
                })
                .then(function(datos) {
                    if (canalPedido !== canalActual) {
                        return;
                    }
                    if (datos.status !== "success") {
                        throw new Error("No se pudieron leer las conversaciones");
                    }

                    var lista = filtrarPorCanal(datos.conversaciones || []);
                    var total = lista.length;

                    // Guardamos por si despues falla el pedido
                    datosConversacionesOk = {
                        lista: lista,
                        total: total
                    };
                    mostrarConversaciones(lista, total);
                })
                .catch(function() {
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
            var canalPedido = canalActual;
            fetch(API_URL + "?action=auditoria&limite=100&canal=" + canalPedido)
                .then(function(respuesta) {
                    return respuesta.json();
                })
                .then(function(datos) {
                    if (canalPedido !== canalActual) {
                        return;
                    }
                    if (datos.status !== "success") {
                        throw new Error("No se pudo leer la auditoria");
                    }

                    var resumen = datos.resumen || {};
                    datosAuditoriaOk = resumen;
                    mostrarAuditoria(resumen);
                })
                .catch(function() {
                    if (datosAuditoriaOk) {
                        mostrarAuditoria(datosAuditoriaOk);
                    }
                });
        }

        // ----- 4) Comandos mas usados -----
        function mostrarComandos(lista) {
            var tbody = document.getElementById("dash-comandos-tbody");
            var i;
            var maxUsos;
            var totalUsos;
            var item;
            var porcentaje;
            var anchoBarra;
            var filas = 0;
            var tr;
            var tdComando;
            var tdCantidad;
            var tdBarra;
            var filaBarra;
            var cajaProgress;
            var barra;
            var textoPct;

            if (!tbody) {
                return;
            }

            tbody.innerHTML = "";

            if (!lista || lista.length === 0) {
                tr = document.createElement("tr");
                tdComando = document.createElement("td");
                tdComando.colSpan = 3;
                tdComando.className = "text-muted";
                tdComando.textContent = "Sin usos de comandos registrados";
                tr.appendChild(tdComando);
                tbody.appendChild(tr);
                return;
            }

            // Total y máximo, solo de los comandos visibles
            totalUsos = 0;
            maxUsos = 0;
            for (i = 0; i < lista.length; i++) {
                item = lista[i];
                // Ocultar /comando@nombre_bot
                if (String(item.comando || "").indexOf("@") !== -1) {
                    continue;
                }
                totalUsos = totalUsos + (Number(item.cantidad) || 0);
                if (Number(item.cantidad) > maxUsos) {
                    maxUsos = Number(item.cantidad);
                }
            }

            for (i = 0; i < lista.length; i++) {
                item = lista[i];
                if (String(item.comando || "").indexOf("@") !== -1) {
                    continue;
                }

                // Porcentaje real sobre el total (el texto)
                porcentaje = 0;
                if (totalUsos > 0) {
                    porcentaje = Math.round((Number(item.cantidad) / totalUsos) * 100);
                }

                // Ancho de la barra relativo al más usado (para que se vea bien)
                anchoBarra = 0;
                if (maxUsos > 0) {
                    anchoBarra = Math.round((Number(item.cantidad) / maxUsos) * 100);
                }

                tr = document.createElement("tr");

                tdComando = document.createElement("td");
                tdComando.className = "fw-semibold";
                tdComando.style.color = "#20c997";
                tdComando.textContent = String(item.comando || "");
                tr.appendChild(tdComando);

                tdCantidad = document.createElement("td");
                tdCantidad.textContent = String(item.cantidad || 0);
                tr.appendChild(tdCantidad);

                // Barra de proporcion
                barra = document.createElement("div");
                barra.className = "progress-bar";
                barra.setAttribute("role", "progressbar");
                barra.style.width = anchoBarra + "%";
                barra.style.backgroundColor = "#20c997";

                cajaProgress = document.createElement("div");
                cajaProgress.className = "progress flex-grow-1";
                cajaProgress.style.height = "6px";
                cajaProgress.style.maxWidth = "180px";
                cajaProgress.appendChild(barra);

                textoPct = document.createElement("span");
                textoPct.className = "small text-muted";
                textoPct.textContent = porcentaje + "%";

                filaBarra = document.createElement("div");
                filaBarra.className = "d-flex align-items-center gap-2";
                filaBarra.appendChild(cajaProgress);
                filaBarra.appendChild(textoPct);

                tdBarra = document.createElement("td");
                tdBarra.appendChild(filaBarra);
                tr.appendChild(tdBarra);

                tbody.appendChild(tr);
                filas = filas + 1;
            }

            if (filas === 0) {
                tr = document.createElement("tr");
                tdComando = document.createElement("td");
                tdComando.colSpan = 3;
                tdComando.className = "text-muted";
                tdComando.textContent = "Sin usos de comandos registrados";
                tr.appendChild(tdComando);
                tbody.appendChild(tr);
            }
        }

        function cargarComandos() {
            var canalPedido = canalActual;
            fetch(API_URL + "?action=comandos")
                .then(function(respuesta) {
                    return respuesta.json();
                })
                .then(function(datos) {
                    if (canalPedido !== canalActual || canalActual !== "telegram") {
                        return;
                    }
                    if (datos.status !== "success") {
                        throw new Error("No se pudieron leer los comandos");
                    }

                    var lista = datos.comandos || [];
                    datosComandosOk = lista;
                    mostrarComandos(lista);
                })
                .catch(function() {
                    if (datosComandosOk) {
                        mostrarComandos(datosComandosOk);
                    } else {
                        mostrarComandos([]);
                    }
                });
        }

        // Deja solo las conversaciones del canal elegido
        function filtrarPorCanal(lista) {
            var salida = [];
            var i;
            var canal;
            for (i = 0; i < lista.length; i++) {
                canal = lista[i].canal || "telegram";
                if (canal === canalActual) {
                    salida.push(lista[i]);
                }
            }
            return salida;
        }

        // WhatsApp no trae comandos: se oculta ese bloque
        function mostrarComandosSegunCanal() {
            var bloque = document.getElementById("dash-bloque-comandos");
            if (!bloque) {
                return;
            }
            if (canalActual === "whatsapp") {
                bloque.style.display = "none";
            } else {
                bloque.style.display = "";
            }
        }

        // Carga todo el dashboard
        function cargarDashboard() {
            mostrarComandosSegunCanal();
            cargarEstado();
            cargarConversaciones();
            cargarAuditoria();
            if (canalActual === "telegram") {
                cargarComandos();
            }
        }

        var toggleCanal = document.getElementById("dash-canal-toggle");
        var botonesCanal = toggleCanal ? toggleCanal.querySelectorAll("button[data-canal]") : [];
        var iBoton;
        for (iBoton = 0; iBoton < botonesCanal.length; iBoton++) {
            botonesCanal[iBoton].addEventListener("click", function () {
                var i;
                if (this.classList.contains("active")) {
                    return;
                }
                for (i = 0; i < botonesCanal.length; i++) {
                    botonesCanal[i].classList.remove("active");
                }
                this.classList.add("active");
                canalActual = this.getAttribute("data-canal") || "telegram";
                datosConversacionesOk = null;
                datosAuditoriaOk = null;
                datosComandosOk = null;
                mostrarConversaciones([], 0);
                mostrarAuditoria({});
                cargarDashboard();
            });
        }

        // Primera carga
        cargarDashboard();

        // Cada 15 segundos vuelve a pedir los datos
        setInterval(function() {
            cargarDashboard();
        }, 15000);

    });
</script>