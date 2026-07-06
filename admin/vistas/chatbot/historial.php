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

        <!-- Las siguientes subtareas se irán agregando aquí abajo de forma iterativa -->
    </div>
</div>
