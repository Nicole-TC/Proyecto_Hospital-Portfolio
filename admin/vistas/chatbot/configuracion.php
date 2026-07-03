<?php
// Vista: Configuración de Conexión (OmniBot Hospital)
// Subtarea 1: Maquetado del Layout Principal (Sidebar y contenedor base) que se adapta al modo claro/oscuro

/**
 * Renderiza la estructura visual de una Tarjeta de Credencial reutilizable
 * Subtarea 3: Estructura visual base, con soporte para inputs adaptables y estilos coherentes
 */
function renderTarjetaCredencial($canal, $icono, $label1, $name1, $val1, $label2, $name2, $val2, $type2 = "text") {
    ?>
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100 border rounded-3 bg-white">
            <!-- Cabecera de la Tarjeta -->
            <div class="card-header bg-transparent border-0 pt-4 pb-2">
                <h5 class="fw-bold d-flex align-items-center mb-0">
                    <i class="<?php echo $icono; ?> me-2 fs-4"></i>
                    <?php echo $canal; ?>
                </h5>
            </div>
            <!-- Cuerpo con Inputs -->
            <div class="card-body pt-2">
                <!-- Primer Campo -->
                <div class="mb-3">
                    <label for="<?php echo $name1; ?>" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <?php echo $label1; ?>
                    </label>
                    <input type="text" class="form-control" id="<?php echo $name1; ?>" name="<?php echo $name1; ?>" value="<?php echo htmlspecialchars($val1); ?>" placeholder="Ingrese <?php echo strtolower($label1); ?>">
                </div>
                <!-- Segundo Campo -->
                <div class="mb-3">
                    <label for="<?php echo $name2; ?>" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <?php echo $label2; ?>
                    </label>
                    <input type="<?php echo $type2; ?>" class="form-control" id="<?php echo $name2; ?>" name="<?php echo $name2; ?>" value="<?php echo htmlspecialchars($val2); ?>" placeholder="Ingrese <?php echo strtolower($label2); ?>">
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<div class="container-fluid py-3">
    <div class="row g-4">
        <!-- Sidebar Interno Adaptativo (Usa .card para integrarse con el modo oscuro automático) -->
        <aside class="col-12 col-md-3 col-lg-2 config-sidebar">
            <div class="card shadow-sm p-3 h-100" style="min-height: 400px;">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-secondary d-flex align-items-center">
                            <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-secondary d-flex align-items-center">
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
                        <a href="#" class="nav-link text-secondary d-flex align-items-center">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Auditoría
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Contenedor Base Principal Adaptativo (Usa .card) -->
        <main class="col-12 col-md-9 col-lg-10">
            <div class="card p-4 shadow-sm h-100" style="min-height: 400px;">
                <!-- El contenido de la vista se inyectará aquí en las siguientes subtareas -->
                <div id="vista-configuracion-contenido">
                    <!-- Cabecera de la Vista -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold text-primary"><i class="fa-solid fa-gears me-2"></i> Configuración de Conexión</h4>
                        <p class="text-muted mb-0">Establece y administra las credenciales de conexión para los canales de comunicación de OmniBot Hospital.</p>
                    </div>

                    <!-- Contenedor para tarjetas de credenciales (se poblará en subtareas posteriores) -->
                    <div class="row g-4" id="tarjetas-credenciales-container">
                        <div class="col-12">
                            <p class="text-muted fst-italic text-center">Las tarjetas de credenciales se configurarán en el siguiente paso...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
/* Efectos de hover sutiles y premium para los links del sidebar interno */
.config-sidebar .nav-link {
    transition: background-color 0.2s ease, color 0.2s ease;
    border-radius: 6px;
}
.config-sidebar .nav-link:not([style*="background-color"]):hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: #212529 !important;
}
body.modo-oscuro .config-sidebar .nav-link:not([style*="background-color"]):hover {
    background-color: rgba(255, 255, 255, 0.07);
    color: #ffffff !important;
}
</style>

