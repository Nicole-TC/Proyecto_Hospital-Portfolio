<?php
// Vista principal del módulo Chat Bot
?>
<div class="grey-bg container-fluid mb-5">
    <section id="minimal-statistics">
        <div class="row">
            <div class="col-12 mb-4 mt-2">
                <h4 class="text-primary">Módulo: Chat Bot</h4>
                <p class="text-muted">Panel de control interno del Bot.</p>
            </div>

            <!-- Dashboard -->
            <?php HTML::tarjeta("menu_bot", "Dashboard", "Métricas del bot", "fa-solid fa-chart-line", "success", "index.php?modulo=menu_bot&accion=dashboard"); ?>

            <!-- Configuración -->
            <?php HTML::tarjeta("menu_bot", "Configuración", "Conexión del bot", "fa-solid fa-gear", "primary", "index.php?modulo=menu_bot&accion=configuracion"); ?>

            <!-- Historial -->
            <?php HTML::tarjeta("menu_bot", "Historial", "Auditoría del bot", "fa-solid fa-clock-rotate-left", "info", "index.php?modulo=menu_bot&accion=historial"); ?>
        </div>
    </section>
</div>
