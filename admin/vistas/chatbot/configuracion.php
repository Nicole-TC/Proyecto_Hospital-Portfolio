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

                    <!-- Formulario de Configuración -->
                    <form id="form-configuracion-conexion" class="needs-validation" novalidate>
                        <!-- Contenedor para tarjetas de credenciales (Telegram y WhatsApp) -->
                        <div class="row g-4" id="tarjetas-credenciales-container">
                            <?php
                            // Valores de estado iniciales (se vincularán con el backend en pasos posteriores)
                            $telegram_token = "";
                            $telegram_chat_id = "";
                            $whatsapp_phone = "";
                            $whatsapp_token = "";

                            // Tarjeta Telegram
                            renderTarjetaCredencial(
                                "Telegram",
                                "fa-brands fa-telegram text-info",
                                "Token del Bot",
                                "telegram_token",
                                $telegram_token,
                                "ID del Chat",
                                "telegram_chat_id",
                                $telegram_chat_id
                            );

                            // Tarjeta WhatsApp
                            renderTarjetaCredencial(
                                "WhatsApp",
                                "fa-brands fa-whatsapp text-success",
                                "Número de Teléfono",
                                "whatsapp_phone",
                                $whatsapp_phone,
                                "Token de API",
                                "whatsapp_token",
                                $whatsapp_token,
                                "password"
                            );
                            ?>
                        </div>

                        <!-- Footer de las tarjetas (Estado de conexión y botón de guardar) -->
                        <div class="mt-4 pt-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                            <!-- Estado de conexión (A la izquierda wifi verde + texto) -->
                            <div class="d-flex align-items-center text-success">
                                <i class="fa-solid fa-wifi me-2 fa-pulse"></i>
                                <span class="fw-semibold small">Estado: Conectado con el servicio de Backend</span>
                            </div>

                            <!-- Botón Guardar (A la derecha botón verde agua con icono de disquete) -->
                            <button type="submit" class="btn text-white fw-bold d-flex align-items-center px-4 py-2" style="background-color: #20c997; border-radius: 6px; transition: opacity 0.2s ease;">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Conexión
                            </button>
                        </div>
                    </form>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("form-configuracion-conexion");
    if (form) {
        form.addEventListener("submit", async function(e) {
            e.preventDefault();
            
            // Capturar datos del formulario
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value.trim();
            });

            // Validación de campos vacíos en el Frontend
            if (!data.telegram_token || !data.telegram_chat_id || !data.whatsapp_phone || !data.whatsapp_token) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Por favor, completa todas las credenciales de Telegram y WhatsApp antes de guardar.',
                    confirmButtonColor: '#20c997'
                });
                return;
            }

            // Cambiar estado del botón de guardar (Loading)
            const btnSubmit = form.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...';
            btnSubmit.disabled = true;

            try {
                // Simulación de latencia de red para demostrar el estado de carga
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Mensaje en consola con los datos listos para el backend
                console.log("Payload listo para enviar al backend:", data);

                // API Fetch real (ejemplo preparado para conectar en el futuro):
                /*
                const response = await fetch('api/chatbot/guardar_conexion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.status !== 'success') throw new Error(result.message);
                */

                Swal.fire({
                    icon: 'success',
                    title: 'Configuración Guardada',
                    text: 'Las credenciales de conexión se han registrado exitosamente.',
                    confirmButtonColor: '#20c997'
                });
            } catch (error) {
                console.error("Error al guardar credenciales:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo guardar la configuración. Intenta nuevamente.',
                    confirmButtonColor: '#20c997'
                });
            } finally {
                // Restaurar estado del botón
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            }
        });
    }
});
</script>

