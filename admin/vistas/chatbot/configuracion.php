<?php
// Vista: Configuración de Conexión (OmniBot Hospital)
// Subtarea 1: Maquetado del Layout Principal (Sidebar y contenedor base) que se adapta al modo claro/oscuro

/**
 * Renderiza la estructura visual de una Tarjeta de Credencial reutilizable
 * Subtarea 3: Estructura visual base, con soporte para inputs adaptables y estilos coherentes
 */
function renderTarjetaCredencial($canal, $icono, $label1, $name1, $val1, $label2, $name2, $val2, $type2 = "text", $conFoto = false, $placeholder2 = "") {
    $textoPlaceholder2 = $placeholder2 !== "" ? $placeholder2 : ("Ingrese " . strtolower($label2));
    ?>
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100 border rounded-3 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 pb-2">
                <h5 class="fw-bold d-flex align-items-center mb-0">
                    <i class="<?php echo $icono; ?> me-2 fs-4"></i>
                    <?php echo $canal; ?>
                </h5>
            </div>
            <div class="card-body pt-2">
                <?php if ($conFoto) { ?>
                <div class="cfg-foto">
                    <p class="cfg-foto-titulo mb-2">Foto de perfil del bot</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="cfg-foto-preview" id="telegram-foto-preview" aria-hidden="true">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <label class="btn btn-outline-primary cfg-foto-btn mb-0" for="telegram_foto_perfil">
                            <i class="fa-solid fa-image me-2"></i>Seleccionar imagen
                        </label>
                        <input type="file" class="d-none" id="telegram_foto_perfil" name="telegram_foto_perfil" accept="image/jpeg,image/png,.jpg,.jpeg,.png,.mp4">
                    </div>
                </div>
                <?php } ?>
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
                    <input type="<?php echo $type2; ?>" class="form-control" id="<?php echo $name2; ?>" name="<?php echo $name2; ?>" value="<?php echo htmlspecialchars($val2); ?>" placeholder="<?php echo htmlspecialchars($textoPlaceholder2); ?>">
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
        <main class="col-12">
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
                                "Nombre del bot",
                                "telegram_chat_id",
                                $telegram_chat_id,
                                "text",
                                true,
                                "Ingrese el nombre del bot"
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
                            <!-- Estado del bot (izquierda: indicador vivo/muerto) -->
                            <div class="d-flex align-items-center" id="estado-bot">
                                <i class="fa-solid fa-wifi me-2" id="estado-bot-icono"></i>
                                <span class="fw-semibold small" id="estado-bot-texto">Verificando estado del bot...</span>
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
                if (typeof value === "string") {
                    data[key] = value.trim();
                }
            });

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

    // --- Estado vivo/muerto del bot desde el backend ---
    const API_URL = 'app/cir-api/endpoints/bot-panel-e.php';

    async function cargarEstadoBot() {
        const contenedor = document.getElementById("estado-bot");
        const icono = document.getElementById("estado-bot-icono");
        const texto = document.getElementById("estado-bot-texto");
        if (!contenedor) return;

        try {
            const res = await fetch(API_URL + '?action=estado');
            const json = await res.json();
            if (json.status !== 'success') throw new Error(json.message || 'Error');

            const vivo = !!(json.bot && json.servidor);

            contenedor.classList.remove('text-success', 'text-danger', 'text-warning');
            contenedor.classList.add(vivo ? 'text-success' : 'text-danger');
            icono.classList.remove('fa-pulse');

            if (vivo) {
                texto.innerText = 'Estado: Bot en línea';
                icono.className = 'fa-solid fa-wifi me-2 fa-pulse';
            } else {
                const partes = [];
                if (!json.servidor && !json.db) partes.push('backend caído');
                if (!json.bot) partes.push('bot sin inicializar');
                if (!json.polling) partes.push('polling inactivo');
                if (!json.db) partes.push('base de datos caída');
                texto.innerText = 'Estado: Bot caído (' + (partes.join(', ') || 'sin detalle') + ')';
                icono.className = 'fa-solid fa-wifi me-2';
            }
        } catch (error) {
            console.error("Error consultando estado del bot:", error);
            contenedor.classList.remove('text-success');
            contenedor.classList.add('text-danger');
            icono.className = 'fa-solid fa-wifi me-2';
            texto.innerText = 'Estado: Bot caído (backend inaccesible)';
        }
    }

    cargarEstadoBot();

    const inputFoto = document.getElementById("telegram_foto_perfil");
    const previewFoto = document.getElementById("telegram-foto-preview");
    if (inputFoto && previewFoto) {
        inputFoto.addEventListener("change", async function() {
            const archivo = this.files && this.files[0];
            if (!archivo) return;

            const extension = (archivo.name.split(".").pop() || "").toLowerCase();
            if (!["jpg", "jpeg", "png", "mp4"].includes(extension)) {
                this.value = "";
                Swal.fire({
                    icon: "error",
                    title: "Formato no soportado",
                    text: "Usá JPG, PNG o MP4.",
                    confirmButtonColor: "#20c997"
                });
                return;
            }
            if (archivo.size > 10 * 1024 * 1024) {
                this.value = "";
                Swal.fire({
                    icon: "error",
                    title: "Archivo demasiado grande",
                    text: "La imagen supera los 10 MB.",
                    confirmButtonColor: "#20c997"
                });
                return;
            }

            const lector = new FileReader();
            lector.onload = function(ev) {
                const etiqueta = extension === "mp4" ? "video" : "img";
                previewFoto.innerHTML = '<' + etiqueta + ' src="' + ev.target.result + '" alt="Foto de perfil del bot"' + (extension === "mp4" ? ' muted' : '') + '></' + etiqueta + '>';
            };
            lector.readAsDataURL(archivo);

            const datos = new FormData();
            datos.append("foto", archivo);
            try {
                const res = await fetch("app/cir-api/endpoints/bot-panel-e.php?action=config_imagen", {
                    method: "POST",
                    body: datos
                });
                const json = await res.json();
                const detalle = json.message || json.detail || json.mensaje;
                if (!res.ok || json.status === "error") {
                    Swal.fire({
                        icon: "error",
                        title: "No se pudo cambiar la foto",
                        text: typeof detalle === "string" ? detalle : "El bot no aceptó la imagen.",
                        confirmButtonColor: "#20c997"
                    });
                    return;
                }
                Swal.fire({
                    icon: "success",
                    title: "Foto actualizada",
                    text: typeof detalle === "string" ? detalle : "Foto de perfil actualizada.",
                    confirmButtonColor: "#20c997"
                });
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "No se pudo cambiar la foto",
                    text: "No hubo respuesta del servidor.",
                    confirmButtonColor: "#20c997"
                });
            }
        });
    }
});
</script>

<style>
.cfg-foto {
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}
.cfg-foto-titulo { font-weight: 700; }
.cfg-foto-preview {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #e7f3ff;
    color: #0d6efd;
    font-size: 1.7rem;
    border: 1px solid rgba(13, 110, 253, 0.2);
}
.cfg-foto-preview img,
.cfg-foto-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cfg-foto-btn { border-radius: 0.4rem; }
body.modo-oscuro .cfg-foto { border-bottom-color: #444; }
body.modo-oscuro .cfg-foto-preview {
    background: #1a3348;
    color: #6cb6ff;
    border-color: #375a7f;
}
</style>

