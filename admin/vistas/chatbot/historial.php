<?php
// Vista: Historial de Conversaciones (OmniBot Hospital)
// Los datos se cargan desde el backend del bot vía bot-panel-e.php (proxy PHP).
?>
<div class="container-fluid py-3">
    <div class="card p-4 shadow-sm">
        <!-- Cabecera de la Vista -->
        <div class="mb-4 pb-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-primary mb-1">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de Conversaciones
                </h4>
                <p class="text-muted mb-0">Listado de chats y auditoría de interacciones con el bot.</p>
            </div>
            <div>
                <span class="badge bg-secondary fs-6" id="total-conversaciones">Cargando...</span>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="row g-3 mb-4 align-items-center">
            <!-- Buscador -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group shadow-sm rounded">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0 bg-white" placeholder="Buscar por contacto, ID o mensaje..." id="input-busqueda">
                </div>
            </div>
            <!-- Filtros (Pills/Chips) -->
            <div class="col-12 col-md-6 col-lg-8">
                <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end" id="filtros-chips">
                    <button class="btn btn-sm rounded-pill px-3 py-2 fw-semibold chip-filtro active shadow-sm" data-filtro="todos" style="background-color: #20c997; color: white; border: none;">
                        Todos
                    </button>
                    <button class="btn btn-sm rounded-pill px-3 py-2 fw-semibold chip-filtro bg-white text-muted border shadow-sm" data-filtro="whatsapp" style="border-color: #dee2e6;">
                        <i class="fa-brands fa-whatsapp me-1 text-success"></i> WhatsApp
                    </button>
                    <button class="btn btn-sm rounded-pill px-3 py-2 fw-semibold chip-filtro bg-white text-muted border shadow-sm" data-filtro="telegram" style="border-color: #dee2e6;">
                        <i class="fa-brands fa-telegram me-1 text-info"></i> Telegram
                    </button>
                    <button class="btn btn-sm rounded-pill px-3 py-2 fw-semibold chip-filtro bg-white text-muted border shadow-sm" data-filtro="identificados" style="border-color: #dee2e6;">
                        Identificados
                    </button>
                    <button class="btn btn-sm rounded-pill px-3 py-2 fw-semibold chip-filtro bg-white text-muted border shadow-sm" data-filtro="anonimos" style="border-color: #dee2e6;">
                        Anónimos
                    </button>
                </div>
            </div>
        </div>

        <!-- Layout de Dos Columnas -->
        <div class="row g-4">
            <!-- Columna Izquierda: Lista de chats -->
            <div class="col-12 col-md-5 col-lg-4">
                <div class="card border shadow-sm" style="height: 600px;">
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-muted text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">
                            <i class="fa-solid fa-list me-1"></i> Chats Recientes
                        </h6>
                    </div>
                    <div class="card-body p-0 overflow-auto" id="lista-conversaciones" style="height: calc(100% - 56px);">
                        <div class="list-group list-group-flush" id="lista-chats-group">
                            <div class="p-4 text-center text-muted small" id="cargando-chats">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando conversaciones...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Detalle y mensajes del chat activo -->
            <div class="col-12 col-md-7 col-lg-8">
                <div class="card border shadow-sm" style="height: 600px;">
                    <!-- Cabecera del chat activo -->
                    <div id="chat-activo-header" class="card-header bg-transparent py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" id="chat-header-nombre">Seleccioná un chat</h5>
                                <span class="text-muted small">Ticket: <span class="fw-semibold text-secondary" id="chat-header-ticket-val">—</span></span>
                            </div>
                            <div id="chat-header-tags" class="d-flex gap-1 flex-wrap"></div>
                        </div>
                        <!-- Franja gris sutil de métricas -->
                        <div class="d-flex align-items-center gap-3 px-3 py-2 bg-light border rounded text-muted small flex-wrap" id="chat-header-metricas" style="font-size: 0.8rem;">
                            <div>
                                <i class="fa-solid fa-message me-1 text-primary"></i> <span id="chat-metric-mensajes" class="fw-semibold">0</span> mensajes
                            </div>
                            <div class="vr d-none d-sm-block" style="height: 14px;"></div>
                            <div>
                                <i class="fa-solid fa-user-tie me-1 text-success"></i> Agente: <span id="chat-metric-agente" class="fw-semibold">OmniBot</span>
                            </div>
                        </div>
                    </div>
                    <!-- Área de mensajes -->
                    <div class="card-body p-3 overflow-auto d-flex flex-column gap-3" id="chat-activo-mensajes" style="height: calc(100% - 56px); background-color: #f8f9fa;">
                        <div class="text-center text-muted small align-self-center" style="margin-top: auto; margin-bottom: auto;" id="chat-vacio">
                            <i class="fa-regular fa-comments me-1"></i> Elegí una conversación para ver el historial.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const API_URL = 'app/cir-api/endpoints/bot-panel-e.php';

            const messagesContainer = document.getElementById("chat-activo-mensajes");
            const listaGroup = document.getElementById("lista-chats-group");
            const searchInput = document.getElementById("input-busqueda");
            const chips = document.querySelectorAll(".chip-filtro");

            let conversaciones = [];
            let chatActivo = null;
            let filtroActual = "todos";
            let busquedaActual = "";

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

            function badgeEstado(estado) {
                if (estado === "identificado") {
                    return 'class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.65rem;"';
                }
                return 'class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1" style="font-size: 0.65rem;"';
            }

            function plataformaIcono(canal) {
                return canal === "whatsapp"
                    ? "fa-brands fa-whatsapp text-success"
                    : "fa-brands fa-telegram text-info";
            }

            function renderConversaciones() {
                const visibles = conversaciones.filter(chat => {
                    const matchesFiltro =
                        filtroActual === "todos" ||
                        (filtroActual === "whatsapp" && chat.canal === "whatsapp") ||
                        (filtroActual === "telegram" && chat.canal === "telegram") ||
                        (filtroActual === "identificados" && chat.estado === "identificado") ||
                        (filtroActual === "anonimos" && chat.estado === "anonimo");

                    const textoBusqueda = (
                        chat.nombre + " " + (chat.telegram_chat_id || "") + " " +
                        (chat.ultimo_mensaje || "")
                    ).toLowerCase();
                    const matchesBusqueda = textoBusqueda.includes(busquedaActual);

                    return matchesFiltro && matchesBusqueda;
                });

                document.getElementById("total-conversaciones").innerText = conversaciones.length
                    ? conversaciones.length + " conversaciones en total"
                    : "Sin conversaciones";

                listaGroup.innerHTML = "";

                if (visibles.length === 0) {
                    const div = document.createElement("div");
                    div.className = "p-4 text-center text-muted small";
                    div.innerHTML = "No hay conversaciones que coincidan.";
                    listaGroup.appendChild(div);
                    return;
                }

                visibles.forEach(chat => {
                    const badge = badgeEstado(chat.estado);
                    const div = document.createElement("div");
                    div.className = "list-group-item list-group-item-action p-3 chat-item border-bottom" +
                        (chatActivo && chatActivo.id === chat.id ? " active-chat" : "");
                    div.setAttribute("data-id", chat.id);
                    div.style.cursor = "pointer";

                    div.innerHTML = `
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar rounded-circle d-flex align-items-center justify-content-center fw-bold bg-light text-secondary border shadow-sm"
                                 style="width: 42px; height: 42px; flex-shrink: 0; font-size: 0.9rem;">
                                ${escapeHTML(chat.iniciales || "?")}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-truncate text-dark" style="font-size: 0.9rem;">
                                        ${escapeHTML(chat.nombre)}
                                    </h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        ${escapeHTML(chat.ultima_fecha || "")}
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="text-muted small fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.02em;">
                                        ID ${escapeHTML(chat.telegram_chat_id || chat.id)}
                                    </span>
                                    <i class="${plataformaIcono(chat.canal)}" style="font-size: 0.85rem;"></i>
                                    <span ${badge}>${escapeHTML(chat.estado_label || chat.estado)}</span>
                                    <span class="ms-auto text-muted small" style="font-size: 0.7rem;">
                                        ${chat.n_mensajes} msgs
                                    </span>
                                </div>
                                <p class="mb-0 text-muted text-truncate small" style="font-size: 0.8rem;">
                                    ${escapeHTML(chat.ultimo_mensaje || "Sin mensajes todavía")}
                                </p>
                            </div>
                        </div>`;

                    div.addEventListener("click", () => seleccionarChat(chat.id));
                    listaGroup.appendChild(div);
                });
            }

            async function seleccionarChat(id, esRefresco) {
                // Limpiar selección visual
                document.querySelectorAll(".chat-item").forEach(i => i.classList.remove("active-chat"));
                const item = document.querySelector(`.chat-item[data-id="${CSS.escape(String(id))}"]`);
                if (item) item.classList.add("active-chat");

                const chat = conversaciones.find(c => c.id === id);
                if (!chat) return;
                chatActivo = chat;

                // Cabecera
                document.getElementById("chat-header-nombre").innerText = chat.nombre;
                document.getElementById("chat-header-ticket-val").innerText = "ID " + (chat.telegram_chat_id || chat.id);
                document.getElementById("chat-metric-mensajes").innerText = chat.n_mensajes;
                document.getElementById("chat-metric-agente").innerText = "OmniBot";

                const tagsContainer = document.getElementById("chat-header-tags");
                tagsContainer.innerHTML = "";
                const tag = document.createElement("span");
                tag.className = "badge " + (chat.estado === "identificado"
                    ? "bg-success-subtle text-success"
                    : "bg-secondary-subtle text-secondary") +
                    " rounded-pill border px-2 py-1";
                tag.style.fontSize = "0.75rem";
                tag.innerText = chat.estado_label;
                tagsContainer.appendChild(tag);

                // Mensajes (spinner solo al abrir el chat, no en el refresh automático)
                if (!esRefresco) {
                    messagesContainer.innerHTML = `
                        <div class="text-center text-muted small align-self-center" style="margin-top: auto; margin-bottom: auto;">
                            <i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando mensajes...
                        </div>`;
                }

                try {
                    const res = await fetch(API_URL + "?action=mensajes&chat_id=" + id);
                    const json = await res.json();
                    if (json.status !== "success") throw new Error(json.message || "Error al cargar mensajes.");
                    renderMensajes(json.mensajes || []);
                } catch (error) {
                    console.error("Error cargando mensajes:", error);
                    if (esRefresco) return;
                    messagesContainer.innerHTML = `
                        <div class="text-center text-danger small align-self-center" style="margin-top: auto; margin-bottom: auto;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> No se pudieron cargar los mensajes.
                        </div>`;
                }
            }

            function renderMensajes(mensajes) {
                messagesContainer.innerHTML = "";

                if (mensajes.length === 0) {
                    messagesContainer.innerHTML = `
                        <div class="text-center text-muted small align-self-center" style="margin-top: auto; margin-bottom: auto;">
                            <i class="fa-regular fa-comments me-1"></i> Sin mensajes en esta conversación.
                        </div>`;
                    return;
                }

                mensajes.forEach(msg => {
                    const div = document.createElement("div");
                    if (msg.remitente === "bot") {
                        div.className = "d-flex flex-row-reverse align-items-start gap-2 msg-container";
                        div.setAttribute("data-remitente", "bot");
                        div.innerHTML = `
                            <div class="avatar rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                 style="width: 32px; height: 32px; flex-shrink: 0; background-color: #20c997;">
                                <i class="fa-solid fa-robot" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                                <div class="p-3 rounded-3 text-white shadow-sm msg-bubble"
                                     style="background-color: #20c997; max-width: 85%; font-size: 0.9rem; border-top-right-radius: 0px !important;">
                                    ${escapeHTML(msg.contenido)}
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">${escapeHTML(msg.fecha || "")}</small>
                            </div>`;
                    } else {
                        div.className = "d-flex align-items-start gap-2 msg-container";
                        div.setAttribute("data-remitente", "usuario");
                        div.innerHTML = `
                            <div class="d-flex flex-column">
                                <div class="p-3 rounded-3 text-dark border shadow-sm bg-white msg-bubble"
                                     style="max-width: 85%; font-size: 0.9rem; border-top-left-radius: 0px !important;">
                                    ${escapeHTML(msg.contenido)}
                                </div>
                                <small class="text-muted ps-1" style="font-size: 0.7rem; margin-top: 2px;">${escapeHTML(msg.fecha || "")}</small>
                            </div>`;
                    }
                    messagesContainer.appendChild(div);
                });

                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            async function cargarConversaciones() {
                try {
                    const res = await fetch(API_URL + "?action=conversaciones");
                    const json = await res.json();
                    if (json.status !== "success") {
                        throw new Error(json.message || "Error al cargar conversaciones.");
                    }
                    conversaciones = json.conversaciones || [];
                    renderConversaciones();
                } catch (error) {
                    console.error("Error cargando conversaciones:", error);
                    listaGroup.innerHTML = `
                        <div class="p-4 text-center text-danger small">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            No se pudo conectar con el backend del bot.<br>
                            <span class="text-muted">Verificá que el servicio esté activo.</span>
                        </div>`;
                    document.getElementById("total-conversaciones").innerText = "Error de conexión";
                }
            }

            // Búsqueda
            if (searchInput) {
                searchInput.addEventListener("input", function() {
                    busquedaActual = this.value.toLowerCase().trim();
                    renderConversaciones();
                });
            }

            // Chips de filtro
            chips.forEach(chip => {
                chip.addEventListener("click", function() {
                    chips.forEach(c => {
                        c.classList.remove("active");
                        c.classList.add("bg-white", "text-muted", "border");
                        c.style.backgroundColor = "";
                        c.style.color = "";
                        c.style.border = "";
                    });

                    this.classList.add("active");
                    this.classList.remove("bg-white", "text-muted", "border");
                    this.style.backgroundColor = "#20c997";
                    this.style.color = "white";
                    this.style.border = "none";

                    filtroActual = this.getAttribute("data-filtro");
                    renderConversaciones();
                });
            });

            // Carga inicial
            cargarConversaciones();

            // Actualización automática de la lista y del chat abierto (sin recargar la página)
            setInterval(function () {
                cargarConversaciones();
                if (chatActivo) {
                    seleccionarChat(chatActivo.id, true);
                }
            }, 5000);
        });
        </script>

        <style>
        .chat-item {
            cursor: pointer;
            transition: background-color 0.15s ease, border-left-color 0.15s ease;
            border-left: 3px solid transparent;
        }
        .chat-item:hover {
            background-color: rgba(32, 201, 151, 0.03) !important;
        }
        .chat-item.active-chat {
            background-color: rgba(32, 201, 151, 0.08) !important;
            border-left-color: #20c997 !important;
        }
        /* Custom scrollbar para la lista y mensajes */
        #lista-conversaciones::-webkit-scrollbar,
        #chat-activo-mensajes::-webkit-scrollbar {
            width: 6px;
        }
        #lista-conversaciones::-webkit-scrollbar-track,
        #chat-activo-mensajes::-webkit-scrollbar-track {
            background: transparent;
        }
        #lista-conversaciones::-webkit-scrollbar-thumb,
        #chat-activo-mensajes::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        #lista-conversaciones::-webkit-scrollbar-thumb:hover,
        #chat-activo-mensajes::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* === Dark Mode Compatibility Overrides === */
        body.modo-oscuro .input-group-text.bg-white,
        body.modo-oscuro #input-busqueda.bg-white {
            background-color: #1e1e1e !important;
            color: #f1f1f1 !important;
            border-color: #444 !important;
        }
        body.modo-oscuro .form-control:focus {
            background-color: #1e1e1e !important;
            color: #fff !important;
            border-color: #20c997 !important;
            box-shadow: 0 0 0 0.25rem rgba(32, 201, 151, 0.25) !important;
        }
        body.modo-oscuro .chip-filtro.bg-white {
            background-color: #1e1e1e !important;
            color: #bbb !important;
            border-color: #444 !important;
        }
        body.modo-oscuro .chip-filtro.bg-white:hover {
            background-color: #2a2a2a !important;
            color: #fff !important;
            border-color: #555 !important;
        }
        body.modo-oscuro .chat-item.border-bottom,
        body.modo-oscuro .border-bottom {
            border-bottom-color: #444 !important;
        }
        body.modo-oscuro .chat-item .avatar.bg-light {
            background-color: #1e1e1e !important;
            color: #aaa !important;
            border-color: #444 !important;
        }
        body.modo-oscuro .chat-item:hover {
            background-color: #323232 !important;
        }
        body.modo-oscuro .chat-item.active-chat {
            background-color: #383838 !important;
            border-left-color: #20c997 !important;
        }
        body.modo-oscuro #chat-header-metricas {
            background-color: #1e1e1e !important;
            border-color: #444 !important;
        }
        body.modo-oscuro #chat-activo-mensajes {
            background-color: #121212 !important;
        }
        body.modo-oscuro .msg-bubble.bg-white {
            background-color: #2a2a2a !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }
        body.modo-oscuro .msg-container[data-remitente="bot"] .msg-bubble,
        body.modo-oscuro .msg-container[data-remitente="bot"] .avatar {
            background-color: #1b9a74 !important;
        }
        body.modo-oscuro #lista-conversaciones::-webkit-scrollbar-thumb,
        body.modo-oscuro #chat-activo-mensajes::-webkit-scrollbar-thumb {
            background: #444;
        }
        body.modo-oscuro #lista-conversaciones::-webkit-scrollbar-thumb:hover,
        body.modo-oscuro #chat-activo-mensajes::-webkit-scrollbar-thumb:hover {
            background: #555;
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
        body.modo-oscuro .vr {
            background-color: #444 !important;
            opacity: 0.5 !important;
        }
        </style>
    </div>
</div>