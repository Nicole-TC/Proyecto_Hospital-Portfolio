<?php
// Vista: Historial de Conversaciones (OmniBot Hospital)
// Subtarea 1: Cabecera de la vista (Título principal y texto de "6 conversaciones en total")

// Datos simulados de conversaciones (Subtarea 4)
$conversaciones = [
    [
        'id' => 1,
        'ticket' => '#TK-8472',
        'nombre' => 'Juan Pérez',
        'iniciales' => 'JP',
        'plataforma' => 'whatsapp',
        'hora' => '15:30',
        'ultimo_mensaje' => 'Hola, quería consultar sobre el turno de cardiología para mañana.',
        'estado' => 'Resuelto',
        'tags' => ['soporte', 'turno', 'resuelto'],
        'mensajes' => [
            ['remitente' => 'usuario', 'texto' => 'Hola, quería consultar sobre el turno de cardiología para mañana.', 'hora' => '15:28'],
            ['remitente' => 'bot', 'texto' => '¡Hola, Juan! Claro, tu turno de cardiología está confirmado para mañana a las 09:30 hs con el Dr. Rossi en el Consultorio 4.', 'hora' => '15:29'],
            ['remitente' => 'usuario', 'texto' => 'Excelente, muchas gracias por la información.', 'hora' => '15:30'],
            ['remitente' => 'bot', 'texto' => '¡De nada! Si tienes otra duda, aquí estaré. Que tengas un buen día.', 'hora' => '15:30'],
        ],
        'mensajes_count' => 4,
        'duracion' => '2 min',
        'agente' => 'OmniBot'
    ],
    [
        'id' => 2,
        'ticket' => '#TK-9102',
        'nombre' => 'María Gómez',
        'iniciales' => 'MG',
        'plataforma' => 'telegram',
        'hora' => '14:15',
        'ultimo_mensaje' => 'Necesito reprogramar mi ecografía.',
        'estado' => 'Escalado',
        'tags' => ['imágenes', 'reprogramación', 'escalado'],
        'mensajes' => [
            ['remitente' => 'usuario', 'texto' => 'Hola, buenas tardes. Necesito reprogramar mi ecografía.', 'hora' => '14:10'],
            ['remitente' => 'bot', 'texto' => 'Hola María, entiendo. Por favor ingresa tu número de documento.', 'hora' => '14:11'],
            ['remitente' => 'usuario', 'texto' => 'Mi DNI es 12.345.678.', 'hora' => '14:11'],
            ['remitente' => 'bot', 'texto' => 'Lo siento, no encuentro turnos disponibles para reprogramación automática esta semana. Te derivo con un agente del área de imágenes.', 'hora' => '14:12'],
            ['remitente' => 'usuario', 'texto' => 'Bueno, aguardo que me contacten.', 'hora' => '14:15'],
        ],
        'mensajes_count' => 5,
        'duracion' => '5 min',
        'agente' => 'Humano (Soporte)'
    ],
    [
        'id' => 3,
        'ticket' => '#TK-2281',
        'nombre' => 'Carlos López',
        'iniciales' => 'CL',
        'plataforma' => 'whatsapp',
        'hora' => 'Ayer',
        'ultimo_mensaje' => '¿Tienen los resultados del laboratorio?',
        'estado' => 'Resuelto',
        'tags' => ['laboratorio', 'resultados', 'resuelto'],
        'mensajes' => [
            ['remitente' => 'usuario', 'texto' => 'Hola, ¿tienen los resultados del laboratorio del análisis de sangre del lunes?', 'hora' => 'Ayer 10:00'],
            ['remitente' => 'bot', 'texto' => 'Hola Carlos, ingresando a tu portal de paciente puedes descargarlos directamente.', 'hora' => 'Ayer 10:01'],
            ['remitente' => 'usuario', 'texto' => '¡Ahí los vi! Buenísimo.', 'hora' => 'Ayer 10:05'],
        ],
        'mensajes_count' => 3,
        'duracion' => '5 min',
        'agente' => 'OmniBot'
    ],
    [
        'id' => 4,
        'ticket' => '#TK-7741',
        'nombre' => 'Ana Martínez',
        'iniciales' => 'AM',
        'plataforma' => 'whatsapp',
        'hora' => 'Ayer',
        'ultimo_mensaje' => 'Quiero cancelar el turno de pediatría.',
        'estado' => 'Resuelto',
        'tags' => ['pediatría', 'cancelación', 'resuelto'],
        'mensajes' => [
            ['remitente' => 'usuario', 'texto' => 'Quiero cancelar el turno de pediatría.', 'hora' => 'Ayer 18:30'],
            ['remitente' => 'bot', 'texto' => 'Turno cancelado correctamente. ¿Deseas solicitar otro?', 'hora' => 'Ayer 18:31'],
            ['remitente' => 'usuario', 'texto' => 'No por el momento, gracias.', 'hora' => 'Ayer 18:32'],
        ],
        'mensajes_count' => 3,
        'duracion' => '2 min',
        'agente' => 'OmniBot'
    ],
    [
        'id' => 5,
        'ticket' => '#TK-4431',
        'nombre' => 'Pedro Sánchez',
        'iniciales' => 'PS',
        'plataforma' => 'telegram',
        'hora' => '02 Jul',
        'ultimo_mensaje' => '¿Cuál es el horario de farmacia?',
        'estado' => 'Resuelto',
        'tags' => ['farmacia', 'información', 'resuelto'],
        'mensajes' => [
            ['remitente' => 'usuario', 'texto' => 'Hola, ¿cuál es el horario de farmacia?', 'hora' => '02 Jul 09:12'],
            ['remitente' => 'bot', 'texto' => 'Hola Pedro, la farmacia de guardia atiende las 24 horas del día.', 'hora' => '02 Jul 09:13'],
        ],
        'mensajes_count' => 2,
        'duracion' => '1 min',
        'agente' => 'OmniBot'
    ],
    [
        'id' => 6,
        'ticket' => '#TK-1092',
        'nombre' => 'Lucía Torres',
        'iniciales' => 'LT',
        'plataforma' => 'whatsapp',
        'hora' => '30 Jun',
        'ultimo_mensaje' => 'El bot me da error al sacar turno.',
        'estado' => 'Escalado',
        'tags' => ['error', 'turnos', 'escalado'],
        'mensajes' => [
            ['remitente' => 'usuario', 'texto' => 'El bot me da error al sacar turno.', 'hora' => '30 Jun 16:40'],
            ['remitente' => 'bot', 'texto' => 'Disculpe las molestias, voy a derivar su consulta con soporte técnico.', 'hora' => '30 Jun 16:41'],
        ],
        'mensajes_count' => 2,
        'duracion' => '1 min',
        'agente' => 'Humano (Soporte)'
    ]
];
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
                <span class="badge bg-secondary fs-6">6 conversaciones en total</span>
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
                    <button class="btn btn-sm rounded-pill px-3 py-2 fw-semibold chip-filtro bg-white text-muted border shadow-sm" data-filtro="resueltos" style="border-color: #dee2e6;">
                        Resueltos
                    </button>
                    <button class="btn btn-sm rounded-pill px-3 py-2 fw-semibold chip-filtro bg-white text-muted border shadow-sm" data-filtro="escalados" style="border-color: #dee2e6;">
                        Escalados
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
                            <?php foreach ($conversaciones as $chat): ?>
                            <?php
                            // Colores de los badges según estado
                            $badge_class = 'bg-success-subtle text-success border border-success-subtle';
                            if ($chat['estado'] == 'Escalado') {
                                $badge_class = 'bg-danger-subtle text-danger border border-danger-subtle';
                            } elseif ($chat['estado'] == 'Pendiente') {
                                $badge_class = 'bg-info-subtle text-info border border-info-subtle';
                            }
                            // Icono de plataforma
                            $platform_icon = 'fa-brands fa-whatsapp text-success';
                            if ($chat['plataforma'] == 'telegram') {
                                $platform_icon = 'fa-brands fa-telegram text-info';
                            }
                            ?>
                            <div class="list-group-item list-group-item-action p-3 chat-item border-bottom <?php echo $chat['id'] == 1 ? 'active-chat' : ''; ?>" 
                                 data-id="<?php echo $chat['id']; ?>" 
                                 id="chat-item-<?php echo $chat['id']; ?>"
                                 data-plataforma="<?php echo $chat['plataforma']; ?>"
                                 data-estado="<?php echo strtolower($chat['estado']); ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Avatar Circular -->
                                    <div class="avatar rounded-circle d-flex align-items-center justify-content-center fw-bold bg-light text-secondary border shadow-sm" 
                                         style="width: 42px; height: 42px; flex-shrink: 0; font-size: 0.9rem;">
                                        <?php echo $chat['iniciales']; ?>
                                    </div>
                                    
                                    <!-- Detalles -->
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold mb-0 text-truncate text-dark" style="font-size: 0.9rem;">
                                                <?php echo $chat['nombre']; ?>
                                            </h6>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <?php echo $chat['hora']; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="text-muted small fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.02em;">
                                                <?php echo $chat['ticket']; ?>
                                            </span>
                                            <i class="<?php echo $platform_icon; ?>" style="font-size: 0.85rem;"></i>
                                            <span class="badge <?php echo $badge_class; ?> rounded-pill ms-auto px-2 py-1" style="font-size: 0.65rem;">
                                                <?php echo $chat['estado']; ?>
                                            </span>
                                        </div>
                                        
                                        <p class="mb-0 text-muted text-truncate small" style="font-size: 0.8rem;">
                                            <?php echo $chat['ultimo_mensaje']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Detalle y mensajes del chat activo -->
            <div class="col-12 col-md-7 col-lg-8">
                <div class="card border shadow-sm" style="height: 600px;">
                    <!-- Cabecera del chat activo (Subtarea 5) -->
                    <div id="chat-activo-header" class="card-header bg-transparent py-3 border-bottom">
                        <?php
                        $primer_chat = $conversaciones[0];
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" id="chat-header-nombre"><?php echo $primer_chat['nombre']; ?></h5>
                                <span class="text-muted small" id="chat-header-ticket">Ticket: <span class="fw-semibold text-secondary" id="chat-header-ticket-val"><?php echo $primer_chat['ticket']; ?></span></span>
                            </div>
                            <div id="chat-header-tags" class="d-flex gap-1 flex-wrap">
                                <?php foreach ($primer_chat['tags'] as $tag): ?>
                                    <?php
                                    $tag_badge = 'bg-secondary-subtle text-secondary';
                                    if ($tag == 'resueltos' || $tag == 'resuelto') $tag_badge = 'bg-success-subtle text-success border-success-subtle';
                                    if ($tag == 'escalados' || $tag == 'escalado') $tag_badge = 'bg-danger-subtle text-danger border-danger-subtle';
                                    ?>
                                    <span class="badge <?php echo $tag_badge; ?> rounded-pill border px-2 py-1" style="font-size: 0.75rem;"><?php echo $tag; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Franja gris sutil de métricas -->
                        <div class="d-flex align-items-center gap-3 px-3 py-2 bg-light border rounded text-muted small flex-wrap" id="chat-header-metricas" style="font-size: 0.8rem;">
                            <div>
                                <i class="fa-solid fa-message me-1 text-primary"></i> <span id="chat-metric-mensajes" class="fw-semibold"><?php echo $primer_chat['mensajes_count']; ?></span> mensajes
                            </div>
                            <div class="vr d-none d-sm-block" style="height: 14px;"></div>
                            <div>
                                <i class="fa-solid fa-clock me-1 text-warning"></i> Duración: <span id="chat-metric-duracion" class="fw-semibold"><?php echo $primer_chat['duracion']; ?></span>
                            </div>
                            <div class="vr d-none d-sm-block" style="height: 14px;"></div>
                            <div>
                                <i class="fa-solid fa-user-tie me-1 text-success"></i> Agente: <span id="chat-metric-agente" class="fw-semibold"><?php echo $primer_chat['agente']; ?></span>
                            </div>
                        </div>
                    </div>
                    <!-- Área de mensajes (Subtarea 6) -->
                    <div class="card-body p-3 overflow-auto d-flex flex-column gap-3" id="chat-activo-mensajes" style="height: calc(100% - 56px); background-color: #f8f9fa;">
                        <?php foreach ($primer_chat['mensajes'] as $msg): ?>
                            <?php if ($msg['remitente'] == 'bot'): ?>
                                <!-- Mensaje del Bot (Alineado a la derecha con Avatar) -->
                                <div class="d-flex flex-row-reverse align-items-start gap-2 msg-container" data-remitente="bot">
                                    <div class="avatar rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" 
                                         style="width: 32px; height: 32px; flex-shrink: 0; background-color: #20c997;">
                                        <i class="fa-solid fa-robot" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div class="d-flex flex-column align-items-end">
                                        <div class="p-3 rounded-3 text-white shadow-sm msg-bubble" style="background-color: #20c997; max-width: 85%; font-size: 0.9rem; border-top-right-radius: 0px !important;">
                                            <?php echo htmlspecialchars($msg['texto']); ?>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">
                                            <?php echo $msg['hora']; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Mensaje del Usuario (Alineado a la izquierda) -->
                                <div class="d-flex align-items-start gap-2 msg-container" data-remitente="usuario">
                                    <div class="d-flex flex-column">
                                        <div class="p-3 rounded-3 text-dark border shadow-sm bg-white msg-bubble" style="max-width: 85%; font-size: 0.9rem; border-top-left-radius: 0px !important;">
                                            <?php echo htmlspecialchars($msg['texto']); ?>
                                        </div>
                                        <small class="text-muted ps-1" style="font-size: 0.7rem; margin-top: 2px;">
                                            <?php echo $msg['hora']; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Cargar datos codificados desde PHP
            const conversaciones = <?php echo json_encode($conversaciones); ?>;
            
            const chatItems = document.querySelectorAll(".chat-item");
            const searchInput = document.getElementById("input-busqueda");
            const chips = document.querySelectorAll(".chip-filtro");
            const messagesContainer = document.getElementById("chat-activo-mensajes");
            
            // Hacer scroll al fondo en el chat inicial al cargar
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // --- Lógica del Estado Activo de Chats (Subtarea 7) ---
            chatItems.forEach(item => {
                item.addEventListener("click", function() {
                    const chatId = parseInt(this.getAttribute("data-id"));
                    const chat = conversaciones.find(c => c.id === chatId);
                    
                    if (!chat) return;

                    // 1. Cambiar estado visual en la lista
                    chatItems.forEach(i => i.classList.remove("active-chat"));
                    this.classList.add("active-chat");

                    // 2. Actualizar Cabecera de la columna derecha
                    document.getElementById("chat-header-nombre").innerText = chat.nombre;
                    document.getElementById("chat-header-ticket-val").innerText = chat.ticket;
                    document.getElementById("chat-metric-mensajes").innerText = chat.mensajes_count;
                    document.getElementById("chat-metric-duracion").innerText = chat.duracion;
                    document.getElementById("chat-metric-agente").innerText = chat.agente;

                    // Reconstruir Tags
                    const tagsContainer = document.getElementById("chat-header-tags");
                    tagsContainer.innerHTML = "";
                    chat.tags.forEach(tag => {
                        let tagBadge = "bg-secondary-subtle text-secondary";
                        if (tag === "resueltos" || tag === "resuelto") tagBadge = "bg-success-subtle text-success border-success-subtle";
                        if (tag === "escalados" || tag === "escalado") tagBadge = "bg-danger-subtle text-danger border-danger-subtle";
                        
                        const span = document.createElement("span");
                        span.className = `badge ${tagBadge} rounded-pill border px-2 py-1`;
                        span.style.fontSize = "0.75rem";
                        span.innerText = tag;
                        tagsContainer.appendChild(span);
                    });

                    // 3. Reconstruir Burbujas de Mensajes
                    messagesContainer.innerHTML = "";
                    chat.mensajes.forEach(msg => {
                        if (msg.remitente === "bot") {
                            const div = document.createElement("div");
                            div.className = "d-flex flex-row-reverse align-items-start gap-2 msg-container";
                            div.setAttribute("data-remitente", "bot");
                            div.innerHTML = `
                                <div class="avatar rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" 
                                     style="width: 32px; height: 32px; flex-shrink: 0; background-color: #20c997;">
                                    <i class="fa-solid fa-robot" style="font-size: 0.8rem;"></i>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    <div class="p-3 rounded-3 text-white shadow-sm msg-bubble" style="background-color: #20c997; max-width: 85%; font-size: 0.9rem; border-top-right-radius: 0px !important;">
                                        ${escapeHTML(msg.texto)}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">
                                        ${msg.hora}
                                    </small>
                                </div>
                            `;
                            messagesContainer.appendChild(div);
                        } else {
                            const div = document.createElement("div");
                            div.className = "d-flex align-items-start gap-2 msg-container";
                            div.setAttribute("data-remitente", "usuario");
                            div.innerHTML = `
                                <div class="d-flex flex-column">
                                    <div class="p-3 rounded-3 text-dark border shadow-sm bg-white msg-bubble" style="max-width: 85%; font-size: 0.9rem; border-top-left-radius: 0px !important;">
                                        ${escapeHTML(msg.texto)}
                                    </div>
                                    <small class="text-muted ps-1" style="font-size: 0.7rem; margin-top: 2px;">
                                        ${msg.hora}
                                    </small>
                                </div>
                            `;
                            messagesContainer.appendChild(div);
                        }
                    });

                    // Hacer scroll al fondo de la conversación seleccionada
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
            });

            // --- Lógica de Filtros por Chips y Búsqueda ---
            let filtroActual = "todos";
            let busquedaActual = "";

            function aplicarFiltros() {
                chatItems.forEach(item => {
                    const id = parseInt(item.getAttribute("data-id"));
                    const chat = conversaciones.find(c => c.id === id);
                    if (!chat) return;

                    const matchesFiltro = 
                        filtroActual === "todos" ||
                        (filtroActual === "whatsapp" && chat.plataforma === "whatsapp") ||
                        (filtroActual === "telegram" && chat.plataforma === "telegram") ||
                        (filtroActual === "resueltos" && chat.estado.toLowerCase() === "resuelto") ||
                        (filtroActual === "escalados" && chat.estado.toLowerCase() === "escalado");

                    const matchesBusqueda = 
                        chat.nombre.toLowerCase().includes(busquedaActual) ||
                        chat.ticket.toLowerCase().includes(busquedaActual) ||
                        chat.ultimo_mensaje.toLowerCase().includes(busquedaActual);

                    if (matchesFiltro && matchesBusqueda) {
                        item.style.setProperty("display", "block", "important");
                    } else {
                        item.style.setProperty("display", "none", "important");
                    }
                });
            }

            // Evento input para la búsqueda
            if (searchInput) {
                searchInput.addEventListener("input", function() {
                    busquedaActual = this.value.toLowerCase().trim();
                    aplicarFiltros();
                });
            }

            // Evento click para los chips
            chips.forEach(chip => {
                chip.addEventListener("click", function() {
                    // Actualizar estilos activos de los chips
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
                    aplicarFiltros();
                });
            });

            // Función de escape HTML para evitar vulnerabilidades XSS
            function escapeHTML(str) {
                return str.replace(/[&<>'"]/g, 
                    tag => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#39;',
                        '"': '&quot;'
                    }[tag] || tag)
                );
            }
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
        </style>
    </div>
</div>
