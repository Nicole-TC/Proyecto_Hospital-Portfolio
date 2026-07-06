<?php
// Vista: Historial de Conversaciones (OmniBot Hospital)
// Subtarea 1: Cabecera de la vista (Título principal y texto de "6 conversaciones en total")
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
                        <!-- Las tarjetas de la lista se insertarán aquí (Subtarea 4) -->
                        <div class="p-4 text-center text-muted">
                            <i class="fa-solid fa-spinner fa-spin fa-2x mb-3 text-primary"></i>
                            <p class="mb-0 small">Cargando lista de chats...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Detalle y mensajes del chat activo -->
            <div class="col-12 col-md-7 col-lg-8">
                <div class="card border shadow-sm" style="height: 600px;">
                    <!-- Cabecera del chat activo (Subtarea 5) -->
                    <div id="chat-activo-header" class="card-header bg-transparent py-3 border-bottom">
                        <!-- Cabecera y métricas se insertarán aquí (Subtarea 5) -->
                        <div class="text-muted small">Seleccione una conversación de la lista para ver sus detalles</div>
                    </div>
                    <!-- Área de mensajes (Subtarea 6) -->
                    <div class="card-body p-3 overflow-auto" id="chat-activo-mensajes" style="height: calc(100% - 56px); background-color: #f8f9fa;">
                        <!-- Los mensajes de Usuario y Bot se insertarán aquí (Subtarea 6) -->
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                            <i class="fa-solid fa-comments fa-3x mb-3" style="opacity: 0.4;"></i>
                            <p class="mb-0 small fw-semibold">No hay ninguna conversación seleccionada</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Las siguientes subtareas se irán agregando aquí abajo de forma iterativa -->
    </div>
</div>
