<?php
// Vista: Configuración de Conexión (OmniBot Hospital)
// Subtarea 1: Maquetado del Layout Principal (Sidebar y contenedor base)
?>
<div class="container-fluid py-3">
    <div class="row g-4">
        <!-- Sidebar Interno Oscuro -->
        <aside class="col-12 col-md-3 col-lg-2">
            <div class="bg-dark text-white rounded-3 p-3 shadow-sm d-flex flex-column h-100" style="min-height: 400px;">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-white-50 d-flex align-items-center">
                            <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-white-50 d-flex align-items-center">
                            <i class="fa-solid fa-file-lines me-2"></i> Plantillas
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <!-- El item activo lleva el color verde agua -->
                        <a href="#" class="nav-link text-white d-flex align-items-center fw-bold" style="background-color: #20c997; border-radius: 6px;">
                            <i class="fa-solid fa-link me-2"></i> Configuración de Conexión
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-white-50 d-flex align-items-center">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Auditoría
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Contenedor Base Principal (Gris Claro) -->
        <main class="col-12 col-md-9 col-lg-10">
            <div class="p-4 rounded-3 shadow-sm border h-100" style="background-color: #f8f9fa; min-height: 400px;">
                <!-- El contenido de la vista se inyectará aquí en las siguientes subtareas -->
                <div id="vista-configuracion-contenido">
                    <p class="text-muted fst-italic">El contenido principal se cargará aquí.</p>
                </div>
            </div>
        </main>
    </div>
</div>
