<?php
// Vista: Historial de Conversaciones (OmniBot Hospital)
// Los datos se cargan desde el backend del bot vía bot-panel-e.php (proxy PHP).
?>
<div class="hc-wrapper container-fluid py-4">
    <div class="hc-main-card">

        <!-- ══════ CABECERA ══════ -->
        <div class="hc-page-header">
            <div class="hc-title-row">
                <div class="hc-icon-wrap">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h4 class="hc-title">Historial de Conversaciones</h4>
                    <p class="hc-subtitle">Listado de chats y auditoría de interacciones con el bot.</p>
                </div>
            </div>
            <span class="hc-badge-total" id="total-conversaciones">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size:.72rem;"></i>&nbsp;Cargando...
            </span>
        </div>

        <div class="hc-divider"></div>

        <!-- ══════ BÚSQUEDA + FILTROS ══════ -->
        <div class="hc-controls-row">
            <div class="hc-search-wrap">
                <div class="hc-search-inner">
                    <i class="fa-solid fa-magnifying-glass hc-search-icon"></i>
                    <input type="text" class="hc-search-input"
                           placeholder="Buscar por contacto, ID o mensaje..."
                           id="input-busqueda" autocomplete="off">
                </div>
            </div>
            <div class="hc-chips-wrap" id="filtros-chips">
                <button class="hc-chip hc-chip--active" data-filtro="todos">
                    <i class="fa-solid fa-layer-group"></i> Todos
                </button>
                <button class="hc-chip" data-filtro="whatsapp">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </button>
                <button class="hc-chip" data-filtro="telegram">
                    <i class="fa-brands fa-telegram"></i> Telegram
                </button>
                <button class="hc-chip" data-filtro="identificados">
                    <i class="fa-solid fa-user-check"></i> Identificados
                </button>
                <button class="hc-chip" data-filtro="anonimos">
                    <i class="fa-solid fa-user-secret"></i> Anónimos
                </button>
            </div>
        </div>

        <!-- ══════ LAYOUT DOS COLUMNAS ══════ -->
        <div class="hc-columns-row">

            <!-- ── Col. Izq.: Lista de chats ── -->
            <div class="hc-col-left">
                <div class="hc-panel">
                    <div class="hc-panel-header">
                        <div class="hc-panel-header-dot"></div>
                        <i class="fa-solid fa-comments hc-panel-header-icon"></i>
                        <span class="hc-panel-header-title">Chats Recientes</span>
                        <div class="hc-panel-header-line"></div>
                    </div>
                    <div class="hc-list-scroll" id="lista-conversaciones">
                        <div id="lista-chats-group">
                            <div class="hc-loading-state" id="cargando-chats">
                                <div class="hc-spinner"><i class="fa-solid fa-spinner fa-spin"></i></div>
                                <span>Cargando conversaciones...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Col. Der.: Visor de chat ── -->
            <div class="hc-col-right">
                <div class="hc-panel hc-panel--chat">

                    <!-- Cabecera del chat activo -->
                    <div id="chat-activo-header" class="hc-chat-header">
                        <div class="hc-chat-header-main">
                            <div class="hc-chat-header-avatar" id="chat-header-avatar">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <div class="hc-chat-header-info">
                                <h5 class="hc-chat-title" id="chat-header-nombre">Seleccioná un chat</h5>
                                <span class="hc-chat-ticket">
                                    Ticket:&nbsp;<span class="hc-chat-ticket-val" id="chat-header-ticket-val">—</span>
                                </span>
                            </div>
                            <div id="chat-header-tags" class="hc-chat-tags"></div>
                        </div>
                        <!-- Barra de métricas -->
                        <div class="hc-metrics-bar" id="chat-header-metricas">
                            <div class="hc-metric-pill">
                                <i class="fa-solid fa-message"></i>
                                <span id="chat-metric-mensajes">0</span>
                                <span class="hc-metric-label">mensajes</span>
                            </div>
                            <div class="hc-metric-sep"></div>
                            <div class="hc-metric-pill">
                                <i class="fa-solid fa-robot"></i>
                                <span class="hc-metric-label">Agente:</span>
                                <span id="chat-metric-agente" class="hc-metric-val">OmniBot</span>
                            </div>
                        </div>
                    </div>

                    <!-- Área de mensajes -->
                    <div class="hc-messages-area" id="chat-activo-mensajes">
                        <div class="hc-empty-state" id="chat-vacio">
                            <div class="hc-empty-icon-wrap">
                                <i class="fa-regular fa-comments"></i>
                            </div>
                            <p class="hc-empty-title">Sin conversación activa</p>
                            <p class="hc-empty-sub">Seleccioná un chat de la lista para ver el historial.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div><!-- /hc-columns-row -->

    </div><!-- /hc-main-card -->
</div><!-- /hc-wrapper -->

<!-- ══════════════════ JAVASCRIPT ══════════════════ -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const API_URL           = 'app/cir-api/endpoints/bot-panel-e.php';
    const messagesContainer = document.getElementById("chat-activo-mensajes");
    const listaGroup        = document.getElementById("lista-chats-group");
    const searchInput       = document.getElementById("input-busqueda");
    const chips             = document.querySelectorAll(".hc-chip");

    let conversaciones = [];
    let chatActivo     = null;
    let filtroActual   = "todos";
    let busquedaActual = "";
    let firmaMensajesActivos = "";
    let firmaListaChats = "";

    /* ── Utilidades ── */
    function escapeHTML(str) {
        return String(str).replace(/[&<>'"]/g,
            tag => ({ '&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;' }[tag] || tag)
        );
    }

    /** Quita etiquetas HTML para previews / búsqueda */
    function textoPlano(str) {
        return String(str ?? "")
            .replace(/<[^>]+>/g, " ")
            .replace(/\s+/g, " ")
            .trim();
    }

    /** Formatea el mensaje para verlo como en un chat (negrita, saltos, links). */
    function formatearContenidoMensaje(str) {
        let s = escapeHTML(String(str ?? ""));
        s = s.replace(/\r\n|\r|\n/g, "<br>");
        s = s.replace(/&lt;(\/?)(b|strong|i|em|u|s|code|pre)&gt;/gi, "<$1$2>");
        s = s.replace(/&lt;br\s*\/?&gt;/gi, "<br>");
        s = s.replace(
            /&lt;a\s+href=&quot;(https?:\/\/[^&]+)&quot;.*?&gt;/gi,
            '<a href="$1" target="_blank" rel="noopener noreferrer">'
        );
        s = s.replace(/&lt;\/a&gt;/gi, "</a>");
        s = s.replace(/(?:<br>\s*)?• /g, "<br>• ");
        return s;
    }

    function estaCercaDelFinal(el, umbralPx) {
        umbralPx = umbralPx == null ? 100 : umbralPx;
        return el.scrollHeight - el.scrollTop - el.clientHeight <= umbralPx;
    }

    function badgeClase(estado) {
        return estado === "identificado" ? "hc-tag hc-tag--identified" : "hc-tag hc-tag--anon";
    }
    function plataformaIcono(canal) {
        return canal === "whatsapp"
            ? '<i class="fa-brands fa-whatsapp hc-platform-icon hc-platform-icon--wa"></i>'
            : '<i class="fa-brands fa-telegram hc-platform-icon hc-platform-icon--tg"></i>';
    }
    function initials(nombre) {
        if (!nombre) return "?";
        const parts = nombre.trim().split(/\s+/);
        return parts.length >= 2
            ? (parts[0][0] + parts[1][0]).toUpperCase()
            : nombre.slice(0, 2).toUpperCase();
    }

    /* ── Render lista ── */
    function renderConversaciones(animarEntrada) {
        const visibles = conversaciones.filter(chat => {
            const matchesFiltro =
                filtroActual === "todos" ||
                (filtroActual === "whatsapp"      && chat.canal  === "whatsapp") ||
                (filtroActual === "telegram"      && chat.canal  === "telegram") ||
                (filtroActual === "identificados" && chat.estado === "identificado") ||
                (filtroActual === "anonimos"      && chat.estado === "anonimo");
            const texto = (
                chat.nombre + " " + (chat.telegram_chat_id || "") + " " +
                textoPlano(chat.ultimo_mensaje || "")
            ).toLowerCase();
            return matchesFiltro && texto.includes(busquedaActual);
        });

        const totalEl = document.getElementById("total-conversaciones");
        if (conversaciones.length) {
            totalEl.innerHTML = `<i class="fa-solid fa-comments" style="font-size:.72rem;opacity:.8"></i>&nbsp;${conversaciones.length} conversaciones`;
        } else {
            totalEl.innerHTML = `<i class="fa-solid fa-inbox" style="font-size:.72rem;opacity:.8"></i>&nbsp;Sin conversaciones`;
        }

        const firma = JSON.stringify(visibles.map(c => [
            c.id, c.nombre, c.ultima_fecha, c.estado, c.estado_label,
            c.n_mensajes, textoPlano(c.ultimo_mensaje || "")
        ]));
        if (!animarEntrada && firma === firmaListaChats) {
            return;
        }
        firmaListaChats = firma;

        const listaScroll = document.getElementById("lista-conversaciones");
        const scrollListaPrev = listaScroll ? listaScroll.scrollTop : 0;

        listaGroup.innerHTML = "";

        if (visibles.length === 0) {
            const div = document.createElement("div");
            div.className = "hc-empty-list";
            div.innerHTML = `<i class="fa-solid fa-filter-circle-xmark"></i><span>No hay resultados para este filtro.</span>`;
            listaGroup.appendChild(div);
            return;
        }

        visibles.forEach((chat, idx) => {
            const isActive = chatActivo && chatActivo.id === chat.id;
            const div = document.createElement("div");
            div.className = "hc-chat-item" + (isActive ? " hc-chat-item--active" : "") +
                (animarEntrada ? "" : " hc-no-anim");
            div.setAttribute("data-id", chat.id);
            if (animarEntrada) {
                div.style.animationDelay = (idx * 40) + "ms";
            }

            const canalKey = chat.canal === "whatsapp" ? "wa" : "tg";
            div.innerHTML = `
                <div class="hc-ci-avatar hc-ci-avatar--${canalKey}${isActive ? " hc-ci-avatar--active" : ""}">
                    ${escapeHTML(initials(chat.nombre))}
                </div>
                <div class="hc-ci-body">
                    <div class="hc-ci-top">
                        <span class="hc-ci-name">${escapeHTML(chat.nombre)}</span>
                        <span class="hc-ci-time">${escapeHTML(chat.ultima_fecha || "")}</span>
                    </div>
                    <div class="hc-ci-mid">
                        ${plataformaIcono(chat.canal)}
                        <span class="hc-ci-id">ID&nbsp;${escapeHTML(chat.telegram_chat_id || chat.id)}</span>
                        <span class="${badgeClase(chat.estado)}">${escapeHTML(chat.estado_label || chat.estado)}</span>
                        <span class="hc-ci-msgs">${chat.n_mensajes}&nbsp;msgs</span>
                    </div>
                    <p class="hc-ci-preview">${escapeHTML(textoPlano(chat.ultimo_mensaje) || "Sin mensajes todavía")}</p>
                </div>
                <div class="hc-ci-arrow"><i class="fa-solid fa-chevron-right"></i></div>`;

            div.addEventListener("click", () => seleccionarChat(chat.id));
            listaGroup.appendChild(div);
        });

        if (listaScroll) listaScroll.scrollTop = scrollListaPrev;
    }

    /* ── Seleccionar chat ── */
    async function seleccionarChat(id, esRefresco) {
        document.querySelectorAll(".hc-chat-item").forEach(i => i.classList.remove("hc-chat-item--active"));
        document.querySelectorAll(".hc-ci-avatar").forEach(a => a.classList.remove("hc-ci-avatar--active"));

        const item = document.querySelector(`.hc-chat-item[data-id="${CSS.escape(String(id))}"]`);
        if (item) {
            item.classList.add("hc-chat-item--active");
            item.querySelector(".hc-ci-avatar")?.classList.add("hc-ci-avatar--active");
        }

        const chat = conversaciones.find(c => c.id === id);
        if (!chat) return;
        chatActivo = chat;

        // Actualizar cabecera
        document.getElementById("chat-header-nombre").innerText  = chat.nombre;
        document.getElementById("chat-header-ticket-val").innerText = "ID " + (chat.telegram_chat_id || chat.id);
        document.getElementById("chat-metric-mensajes").innerText = chat.n_mensajes;
        document.getElementById("chat-metric-agente").innerText  = "OmniBot";

        const avatarEl = document.getElementById("chat-header-avatar");
        avatarEl.innerHTML = escapeHTML(initials(chat.nombre));

        const tagsContainer = document.getElementById("chat-header-tags");
        tagsContainer.innerHTML = `<span class="${badgeClase(chat.estado)} hc-tag--lg">${escapeHTML(chat.estado_label)}</span>`;

        // Loader de puntos (solo al abrir, no en refresh)
        if (!esRefresco) {
            firmaMensajesActivos = "";
            messagesContainer.innerHTML = `
                <div class="hc-msg-loading">
                    <div class="hc-msg-loading-dots"><span></span><span></span><span></span></div>
                    <span class="hc-msg-loading-text">Cargando mensajes...</span>
                </div>`;
        }

        try {
            // El canal viaja para que el proxy sepa a qué bot preguntarle.
            const res  = await fetch(
                API_URL + "?action=mensajes&chat_id=" + id +
                "&canal=" + encodeURIComponent(chat.canal || "")
            );
            const json = await res.json();
            if (json.status !== "success") throw new Error(json.message || "Error al cargar mensajes.");
            renderMensajes(json.mensajes || [], !!esRefresco);
        } catch (error) {
            console.error("Error cargando mensajes:", error);
            if (esRefresco) return;
            firmaMensajesActivos = "";
            messagesContainer.innerHTML = `
                <div class="hc-empty-state">
                    <div class="hc-empty-icon-wrap hc-empty-icon-wrap--error">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <p class="hc-empty-title" style="color:var(--hc-danger)">Error al cargar</p>
                    <p class="hc-empty-sub">No se pudieron obtener los mensajes de esta conversación.</p>
                </div>`;
        }
    }

    /* ── Render mensajes ── */
    function renderMensajes(mensajes, esRefresco) {
        const firma = JSON.stringify(
            (mensajes || []).map(m => [m.fecha, m.remitente, m.contenido])
        );
        if (esRefresco && firma === firmaMensajesActivos) {
            return;
        }
        firmaMensajesActivos = firma;

        const seguirAbajo = !esRefresco || estaCercaDelFinal(messagesContainer);
        const scrollPrev = messagesContainer.scrollTop;
        const alturaPrev = messagesContainer.scrollHeight;

        messagesContainer.innerHTML = "";

        if (mensajes.length === 0) {
            messagesContainer.innerHTML = `
                <div class="hc-empty-state">
                    <div class="hc-empty-icon-wrap">
                        <i class="fa-regular fa-comment-dots"></i>
                    </div>
                    <p class="hc-empty-title">Sin mensajes</p>
                    <p class="hc-empty-sub">Esta conversación no tiene mensajes registrados.</p>
                </div>`;
            return;
        }

        mensajes.forEach((msg, idx) => {
            const div = document.createElement("div");
            if (!esRefresco) {
                div.style.animationDelay = Math.min(idx * 30, 300) + "ms";
            }

            if (msg.remitente === "bot") {
                div.className = "hc-msg hc-msg--bot" + (esRefresco ? " hc-no-anim" : "");
                div.innerHTML = `
                    <div class="hc-msg-avatar--bot"><i class="fa-solid fa-robot"></i></div>
                    <div class="hc-msg-content">
                        <div class="hc-bubble hc-bubble--bot">${formatearContenidoMensaje(msg.contenido)}</div>
                        <span class="hc-msg-time">${escapeHTML(msg.fecha || "")}</span>
                    </div>`;
            } else {
                div.className = "hc-msg hc-msg--user" + (esRefresco ? " hc-no-anim" : "");
                div.innerHTML = `
                    <div class="hc-msg-content">
                        <div class="hc-bubble hc-bubble--user">${formatearContenidoMensaje(msg.contenido)}</div>
                        <span class="hc-msg-time">${escapeHTML(msg.fecha || "")}</span>
                    </div>`;
            }
            messagesContainer.appendChild(div);
        });

        if (seguirAbajo) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } else {
            messagesContainer.scrollTop = scrollPrev + (messagesContainer.scrollHeight - alturaPrev);
        }
    }

    /* ── Cargar conversaciones ── */
    async function cargarConversaciones(esRefresco) {
        try {
            const res  = await fetch(API_URL + "?action=conversaciones");
            const json = await res.json();
            if (json.status !== "success") throw new Error(json.message || "Error al cargar conversaciones.");
            conversaciones = json.conversaciones || [];
            renderConversaciones(!esRefresco);
        } catch (error) {
            console.error("Error cargando conversaciones:", error);
            firmaListaChats = "";
            listaGroup.innerHTML = `
                <div class="hc-empty-list hc-empty-list--error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Sin conexión con el backend.</span>
                    <small>Verificá que el servicio esté activo.</small>
                </div>`;
            document.getElementById("total-conversaciones").innerHTML =
                `<i class="fa-solid fa-wifi" style="font-size:.72rem"></i>&nbsp;Sin conexión`;
        }
    }

    /* ── Búsqueda ── */
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            busquedaActual = this.value.toLowerCase().trim();
            firmaListaChats = "";
            renderConversaciones(false);
        });
    }

    /* ── Chips de filtro ── */
    chips.forEach(chip => {
        chip.addEventListener("click", function () {
            chips.forEach(c => c.classList.remove("hc-chip--active"));
            this.classList.add("hc-chip--active");
            filtroActual = this.getAttribute("data-filtro");
            firmaListaChats = "";
            renderConversaciones(false);
        });
    });

    /* ── Carga inicial + auto-refresh cada 5s ── */
    cargarConversaciones(false);
    setInterval(function () {
        cargarConversaciones(true);
        if (chatActivo) seleccionarChat(chatActivo.id, true);
    }, 5000);
});
</script>

<!-- ══════════════════ ESTILOS ══════════════════ -->
<style>
/* ─── Variables de color y sombra ─────────────────── */
:root {
    --hc-teal:         #20c997;
    --hc-teal-dark:    #1aab82;
    --hc-teal-darker:  #13856a;
    --hc-teal-glow:    rgba(32,201,151,.35);
    --hc-teal-soft:    rgba(32,201,151,.12);
    --hc-teal-softer:  rgba(32,201,151,.06);
    --hc-danger:       #ef4444;

    --hc-bg:           #ffffff;
    --hc-bg-2:         #f8fafc;
    --hc-bg-3:         #f1f5f9;
    --hc-card:         #ffffff;
    --hc-border:       rgba(0,0,0,.07);
    --hc-border-s:     rgba(0,0,0,.12);

    --hc-t1: #0f172a;
    --hc-t2: #475569;
    --hc-t3: #94a3b8;

    --hc-r-sm: 8px; --hc-r-md: 14px; --hc-r-lg: 18px; --hc-r-xl: 24px;

    --hc-sh-sm:   0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.05);
    --hc-sh-md:   0 4px 16px rgba(0,0,0,.08),0 2px 6px rgba(0,0,0,.05);
    --hc-sh-teal: 0 4px 20px var(--hc-teal-glow);
    --hc-sh-card: 0 0 0 1px var(--hc-border),0 8px 32px rgba(0,0,0,.06);
}

/* ─── Keyframes ───────────────────────────────────── */
@keyframes hcFadeUp  { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:none} }
@keyframes hcFadeIn  { from{opacity:0} to{opacity:1} }
@keyframes hcGrad    { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
@keyframes hcPulse   { 0%,100%{box-shadow:0 0 0 0 var(--hc-teal-glow)} 50%{box-shadow:0 0 0 7px transparent} }
@keyframes hcDot     { 0%,80%,100%{transform:scale(0);opacity:.3} 40%{transform:scale(1);opacity:1} }

/* ─── Wrapper / Card principal ────────────────────── */
.hc-wrapper   { padding: 1.25rem 1rem; }
.hc-main-card {
    background: var(--hc-card);
    border-radius: var(--hc-r-xl);
    box-shadow: var(--hc-sh-card);
    padding: 1.75rem 2rem 1.5rem;
    animation: hcFadeIn .4s ease both;
}

/* ─── Cabecera de página ──────────────────────────── */
.hc-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.2rem;
}
.hc-title-row { display: flex; align-items: center; gap: .9rem; }
.hc-icon-wrap {
    width: 46px; height: 46px; border-radius: 14px;
    background: linear-gradient(135deg, var(--hc-teal), var(--hc-teal-darker));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.1rem;
    box-shadow: var(--hc-sh-teal); flex-shrink: 0;
}
.hc-title {
    margin: 0 0 2px; font-size: 1.3rem; font-weight: 800;
    background: linear-gradient(120deg, var(--hc-teal-dark), #0ea5e9, var(--hc-teal));
    background-size: 220% 220%;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: hcGrad 5s ease infinite;
    letter-spacing: -.025em;
}
.hc-subtitle  { margin: 0; font-size: .8rem; color: var(--hc-t3); }
.hc-badge-total {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .45rem 1.05rem; border-radius: 99px;
    font-size: .78rem; font-weight: 700; letter-spacing: .03em;
    background: var(--hc-teal-soft); color: var(--hc-teal-dark);
    border: 1.5px solid rgba(32,201,151,.25);
    backdrop-filter: blur(8px); white-space: nowrap;
}
.hc-divider {
    height: 1.5px;
    background: linear-gradient(90deg, var(--hc-teal-soft), transparent);
    margin-bottom: 1.5rem; border-radius: 2px;
}

/* ─── Buscador ────────────────────────────────────── */
.hc-controls-row { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; margin-bottom: 1.6rem; }
.hc-search-wrap  { flex: 1 1 250px; min-width: 200px; max-width: 360px; }
.hc-search-inner { position: relative; display: flex; align-items: center; }
.hc-search-icon  {
    position: absolute; left: .9rem; color: var(--hc-t3);
    font-size: .85rem; pointer-events: none; transition: color .25s; z-index: 2;
}
.hc-search-input {
    width: 100%; padding: .58rem 1rem .58rem 2.5rem;
    border-radius: var(--hc-r-lg); border: 1.5px solid var(--hc-border-s);
    background: var(--hc-bg-2); font-size: .875rem; color: var(--hc-t1);
    outline: none; transition: border-color .25s, box-shadow .25s, background .25s;
}
.hc-search-input::placeholder { color: var(--hc-t3); }
.hc-search-input:focus {
    border-color: var(--hc-teal); background: var(--hc-bg);
    box-shadow: 0 0 0 3px var(--hc-teal-glow);
}
.hc-search-inner:focus-within .hc-search-icon { color: var(--hc-teal); }

/* ─── Chips ───────────────────────────────────────── */
.hc-chips-wrap {
    display: flex; flex-wrap: wrap; gap: .5rem;
    flex: 1 1 auto; justify-content: flex-end;
}
.hc-chip {
    display: inline-flex; align-items: center; gap: .38rem;
    padding: .45rem .95rem; border-radius: 99px;
    font-size: .78rem; font-weight: 600;
    border: 1.5px solid var(--hc-border-s);
    background: var(--hc-bg-2); color: var(--hc-t2);
    cursor: pointer; outline: none; user-select: none;
    transition: all .22s cubic-bezier(.34,1.56,.64,1);
    letter-spacing: .01em;
}
.hc-chip:hover {
    background: var(--hc-teal-soft); border-color: rgba(32,201,151,.35);
    color: var(--hc-teal-dark); transform: translateY(-1px) scale(1.04);
}
.hc-chip:active { transform: scale(.95); }
.hc-chip--active {
    background: linear-gradient(135deg, var(--hc-teal), var(--hc-teal-dark));
    color: #fff !important; border-color: transparent !important;
    box-shadow: var(--hc-sh-teal);
}
.hc-chip--active:hover {
    background: linear-gradient(135deg, var(--hc-teal-dark), var(--hc-teal-darker));
    transform: translateY(-1px) scale(1.04);
}

/* ─── Grid de columnas ────────────────────────────── */
.hc-columns-row {
    display: grid; grid-template-columns: 320px 1fr;
    gap: 1.25rem; min-height: 540px;
}
@media (max-width: 880px) { .hc-columns-row { grid-template-columns: 1fr; } }
.hc-col-left, .hc-col-right { min-width: 0; }

/* ─── Panel genérico ──────────────────────────────── */
.hc-panel {
    background: var(--hc-card);
    border-radius: var(--hc-r-lg);
    border: 1.5px solid var(--hc-border);
    overflow: hidden; display: flex; flex-direction: column;
    height: 580px; box-shadow: var(--hc-sh-sm);
    transition: box-shadow .3s;
}
.hc-panel:hover { box-shadow: var(--hc-sh-md); }

/* ─── Header panel izquierdo ──────────────────────── */
.hc-panel-header {
    display: flex; align-items: center; gap: .55rem;
    padding: .8rem 1.1rem;
    background: linear-gradient(135deg, var(--hc-teal-softer), transparent);
    border-bottom: 1.5px solid var(--hc-border); flex-shrink: 0;
}
.hc-panel-header-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--hc-teal); box-shadow: 0 0 0 3px var(--hc-teal-soft);
    animation: hcPulse 2.5s ease-in-out infinite;
}
.hc-panel-header-icon  { color: var(--hc-teal); font-size: .88rem; }
.hc-panel-header-title {
    font-size: .75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .09em; color: var(--hc-t2);
}
.hc-panel-header-line { flex: 1; height: 1px; background: var(--hc-border); }

/* ─── Lista scroll ────────────────────────────────── */
.hc-list-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; }
.hc-list-scroll::-webkit-scrollbar { width: 4px; }
.hc-list-scroll::-webkit-scrollbar-track { background: transparent; }
.hc-list-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, var(--hc-teal), var(--hc-teal-dark));
    border-radius: 4px;
}

/* ─── Chat item en lista ──────────────────────────── */
.hc-chat-item {
    display: flex; align-items: center; gap: .7rem;
    padding: .8rem 1rem; border-bottom: 1px solid var(--hc-border);
    cursor: pointer; border-left: 3px solid transparent;
    transition: background .18s, border-left-color .18s, transform .15s;
    animation: hcFadeUp .3s ease both;
}
.hc-chat-item.hc-no-anim,
.hc-msg.hc-no-anim {
    animation: none !important;
}
.hc-chat-item:last-child  { border-bottom: none; }
.hc-chat-item:hover       { background: var(--hc-teal-softer); transform: translateX(2px); }
.hc-chat-item--active     { background: var(--hc-teal-soft) !important; border-left-color: var(--hc-teal) !important; }
.hc-chat-item--active:hover { transform: none; }

/* Avatar en lista */
.hc-ci-avatar {
    width: 42px; height: 42px; border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem; font-weight: 800; flex-shrink: 0;
    background: var(--hc-bg-3); color: var(--hc-t2);
    border: 2px solid var(--hc-border); transition: all .22s;
}
.hc-ci-avatar--wa     { background: rgba(37,211,102,.1);  color: #25d366; border-color: rgba(37,211,102,.2); }
.hc-ci-avatar--tg     { background: rgba(0,136,204,.1);   color: #0088cc; border-color: rgba(0,136,204,.2);  }
.hc-ci-avatar--active {
    background: linear-gradient(135deg, var(--hc-teal), var(--hc-teal-dark)) !important;
    color: #fff !important; border-color: transparent !important;
    box-shadow: var(--hc-sh-teal);
}

.hc-ci-body    { flex: 1; min-width: 0; }
.hc-ci-top     { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px; }
.hc-ci-name    { font-size: .86rem; font-weight: 700; color: var(--hc-t1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.hc-ci-time    { font-size: .67rem; color: var(--hc-t3); white-space: nowrap; flex-shrink: 0; }
.hc-ci-mid     { display: flex; align-items: center; gap: .32rem; margin-bottom: 4px; flex-wrap: wrap; }
.hc-ci-id      { font-size: .68rem; color: var(--hc-t3); font-weight: 600; }
.hc-ci-msgs    { font-size: .67rem; color: var(--hc-t3); margin-left: auto; white-space: nowrap; }
.hc-ci-preview { margin: 0; font-size: .74rem; color: var(--hc-t3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.hc-platform-icon     { font-size: .82rem; }
.hc-platform-icon--wa { color: #25d366; }
.hc-platform-icon--tg { color: #0088cc; }

.hc-ci-arrow {
    color: var(--hc-t3); font-size: .7rem;
    opacity: 0; transition: opacity .2s, transform .2s; flex-shrink: 0;
}
.hc-chat-item:hover .hc-ci-arrow,
.hc-chat-item--active .hc-ci-arrow { opacity: 1; transform: translateX(2px); }

/* ─── Tags / Badges ───────────────────────────────── */
.hc-tag {
    display: inline-flex; align-items: center;
    padding: .18rem .55rem; border-radius: 99px;
    font-size: .63rem; font-weight: 700; letter-spacing: .04em; white-space: nowrap;
}
.hc-tag--identified { background: rgba(22,163,74,.12); color: #16a34a; border: 1px solid rgba(22,163,74,.2); }
.hc-tag--anon       { background: var(--hc-bg-3); color: var(--hc-t3); border: 1px solid var(--hc-border-s); }
.hc-tag--lg         { font-size: .71rem; padding: .25rem .7rem; }

/* ─── Estados vacíos lista ────────────────────────── */
.hc-loading-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .55rem; padding: 2.5rem 1rem; color: var(--hc-t3); font-size: .8rem;
}
.hc-spinner { font-size: 1.35rem; color: var(--hc-teal); opacity: .7; }
.hc-empty-list {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .4rem; padding: 2.5rem 1rem; color: var(--hc-t3); font-size: .8rem; text-align: center;
}
.hc-empty-list i          { font-size: 1.55rem; margin-bottom: .2rem; opacity: .5; }
.hc-empty-list--error     { color: #ef4444; }
.hc-empty-list--error i   { color: #ef4444; opacity: 1; }
.hc-empty-list small      { font-size: .7rem; opacity: .7; }

/* ─── Panel chat derecho ──────────────────────────── */
.hc-panel--chat { height: 580px; }

.hc-chat-header {
    padding: .95rem 1.25rem;
    border-bottom: 1.5px solid var(--hc-border);
    background: linear-gradient(135deg, var(--hc-bg-2), var(--hc-bg));
    backdrop-filter: blur(10px); flex-shrink: 0;
}
.hc-chat-header-main {
    display: flex; align-items: center; gap: .8rem;
    margin-bottom: .7rem; flex-wrap: wrap;
}
.hc-chat-header-avatar {
    width: 40px; height: 40px; border-radius: 12px;
    background: var(--hc-bg-3); color: var(--hc-t2);
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 800;
    border: 2px solid var(--hc-border); flex-shrink: 0; transition: all .3s;
}
.hc-chat-header-info { flex: 1; min-width: 0; }
.hc-chat-title      { margin: 0 0 2px; font-size: .98rem; font-weight: 800; color: var(--hc-t1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hc-chat-ticket     { font-size: .73rem; color: var(--hc-t3); }
.hc-chat-ticket-val { font-weight: 700; color: var(--hc-t2); }
.hc-chat-tags       { display: flex; gap: .4rem; flex-wrap: wrap; }

/* Métricas */
.hc-metrics-bar {
    display: flex; align-items: center; gap: .5rem;
    padding: .5rem .8rem; background: var(--hc-bg-3);
    border-radius: var(--hc-r-md); border: 1px solid var(--hc-border); flex-wrap: wrap;
}
.hc-metric-pill      { display: flex; align-items: center; gap: .32rem; font-size: .76rem; }
.hc-metric-pill i    { color: var(--hc-teal); font-size: .78rem; }
.hc-metric-pill span { font-weight: 700; color: var(--hc-t1); }
.hc-metric-label     { font-weight: 400 !important; color: var(--hc-t3) !important; }
.hc-metric-val       { color: var(--hc-teal-dark) !important; }
.hc-metric-sep       { width: 1px; height: 14px; background: var(--hc-border-s); }

/* ─── Área de mensajes ────────────────────────────── */
.hc-messages-area {
    flex: 1; overflow-y: auto; overflow-x: hidden;
    padding: 1.2rem 1.1rem;
    display: flex; flex-direction: column; gap: .8rem;
    background:
        radial-gradient(ellipse at 20% 20%, var(--hc-teal-softer) 0%, transparent 55%),
        var(--hc-bg-2);
}
.hc-messages-area::-webkit-scrollbar { width: 4px; }
.hc-messages-area::-webkit-scrollbar-track { background: transparent; }
.hc-messages-area::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, var(--hc-teal), var(--hc-teal-dark));
    border-radius: 4px;
}

/* ─── Estado vacío (visor) ────────────────────────── */
.hc-empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .5rem; text-align: center; margin: auto; padding: 2rem;
    animation: hcFadeIn .4s ease both;
}
.hc-empty-icon-wrap {
    width: 62px; height: 62px; border-radius: 18px;
    background: var(--hc-teal-soft); color: var(--hc-teal);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.55rem; margin-bottom: .4rem;
    border: 2px solid rgba(32,201,151,.2);
}
.hc-empty-icon-wrap--error { background: rgba(239,68,68,.1); color: #ef4444; border-color: rgba(239,68,68,.2); }
.hc-empty-title { margin: 0; font-size: .93rem; font-weight: 700; color: var(--hc-t2); }
.hc-empty-sub   { margin: 0; font-size: .77rem; color: var(--hc-t3); max-width: 230px; }

/* ─── Mensajes ────────────────────────────────────── */
.hc-msg {
    display: flex; align-items: flex-end; gap: .55rem;
    max-width: 86%; animation: hcFadeUp .26s ease both;
}
.hc-msg--bot  { align-self: flex-end;   flex-direction: row-reverse; }
.hc-msg--user { align-self: flex-start; }

.hc-msg-avatar--bot {
    width: 28px; height: 28px; border-radius: 9px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--hc-teal), var(--hc-teal-dark));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .7rem;
    box-shadow: var(--hc-sh-teal);
}
.hc-msg-content               { display: flex; flex-direction: column; gap: .22rem; }
.hc-msg--bot  .hc-msg-content { align-items: flex-end; }
.hc-msg--user .hc-msg-content { align-items: flex-start; }

.hc-bubble {
    padding: .65rem .95rem; border-radius: var(--hc-r-md);
    font-size: .86rem; line-height: 1.5; word-break: break-word; max-width: 100%;
}
.hc-bubble--bot {
    background: linear-gradient(135deg, var(--hc-teal), var(--hc-teal-dark));
    color: #fff; border-bottom-right-radius: 4px;
    box-shadow: 0 4px 14px var(--hc-teal-glow);
}
.hc-bubble--user {
    background: var(--hc-bg); color: var(--hc-t1);
    border: 1.5px solid var(--hc-border-s);
    border-bottom-left-radius: 4px; box-shadow: var(--hc-sh-sm);
}
.hc-bubble a {
    color: inherit;
    text-decoration: underline;
    word-break: break-word;
}
.hc-msg-time { font-size: .63rem; color: var(--hc-t3); letter-spacing: .02em; }

/* ─── Loader de mensajes (3 puntos) ───────────────── */
.hc-msg-loading {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: .55rem; margin: auto;
}
.hc-msg-loading-dots { display: flex; gap: .28rem; }
.hc-msg-loading-dots span {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--hc-teal); animation: hcDot 1.4s ease-in-out infinite;
}
.hc-msg-loading-dots span:nth-child(2) { animation-delay: .16s; }
.hc-msg-loading-dots span:nth-child(3) { animation-delay: .32s; }
.hc-msg-loading-text { font-size: .78rem; color: var(--hc-t3); }

/* ══════════════════════════════════════════════════
   MODO OSCURO
   ══════════════════════════════════════════════════ */
body.modo-oscuro {
    --hc-bg:       #111827;
    --hc-bg-2:     #1a2236;
    --hc-bg-3:     #1e2a3a;
    --hc-card:     #151e2d;
    --hc-border:   rgba(255,255,255,.07);
    --hc-border-s: rgba(255,255,255,.12);
    --hc-t1: #f1f5f9;
    --hc-t2: #94a3b8;
    --hc-t3: #475569;
    --hc-sh-sm:   0 1px 3px rgba(0,0,0,.3),0 1px 2px rgba(0,0,0,.2);
    --hc-sh-md:   0 4px 20px rgba(0,0,0,.4),0 2px 8px rgba(0,0,0,.3);
    --hc-sh-card: 0 0 0 1px rgba(255,255,255,.06),0 8px 40px rgba(0,0,0,.4);
}
body.modo-oscuro .hc-search-input {
    background: var(--hc-bg-3); color: var(--hc-t1); border-color: var(--hc-border-s);
}
body.modo-oscuro .hc-search-input:focus {
    background: var(--hc-bg-2); box-shadow: 0 0 0 3px rgba(32,201,151,.18);
}
body.modo-oscuro .hc-chip { background: var(--hc-bg-3); color: var(--hc-t2); border-color: var(--hc-border-s); }
body.modo-oscuro .hc-chip:hover { background: rgba(32,201,151,.1); color: var(--hc-teal); }
body.modo-oscuro .hc-ci-avatar     { background: var(--hc-bg-3); color: var(--hc-t2); border-color: var(--hc-border-s); }
body.modo-oscuro .hc-ci-avatar--wa { background: rgba(37,211,102,.08); border-color: rgba(37,211,102,.15); }
body.modo-oscuro .hc-ci-avatar--tg { background: rgba(0,136,204,.08);  border-color: rgba(0,136,204,.15); }
body.modo-oscuro .hc-chat-item:hover   { background: rgba(255,255,255,.03); }
body.modo-oscuro .hc-chat-item--active { background: rgba(32,201,151,.08) !important; }
body.modo-oscuro .hc-tag--identified   { background: rgba(22,163,74,.15); color: #4ade80; border-color: rgba(74,222,128,.2); }
body.modo-oscuro .hc-tag--anon         { background: rgba(255,255,255,.05); color: var(--hc-t3); }
body.modo-oscuro .hc-metrics-bar       { background: var(--hc-bg-3); border-color: var(--hc-border); }
body.modo-oscuro .hc-messages-area {
    background:
        radial-gradient(ellipse at 20% 20%, rgba(32,201,151,.04) 0%, transparent 55%),
        var(--hc-bg);
}
body.modo-oscuro .hc-bubble--user {
    background: var(--hc-bg-3); color: var(--hc-t1); border-color: var(--hc-border-s);
}
body.modo-oscuro .hc-empty-icon-wrap   { background: rgba(32,201,151,.08); border-color: rgba(32,201,151,.15); }
body.modo-oscuro .hc-panel-header      { background: linear-gradient(135deg, rgba(32,201,151,.05), transparent); }
body.modo-oscuro .hc-chat-header       { background: linear-gradient(135deg, var(--hc-bg-2), var(--hc-bg)); }
body.modo-oscuro .hc-chat-header-avatar { background: var(--hc-bg-3); border-color: var(--hc-border); }
body.modo-oscuro .hc-list-scroll::-webkit-scrollbar-thumb,
body.modo-oscuro .hc-messages-area::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, var(--hc-teal-dark), var(--hc-teal-darker));
}
</style>
