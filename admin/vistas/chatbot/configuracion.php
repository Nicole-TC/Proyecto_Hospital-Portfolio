<?php
// Vista: Configuración de Conexión (OmniBot Hospital)
// Subtarea 1: Maquetado del Layout Principal (Sidebar y contenedor base) que se adapta al modo claro/oscuro

/**
 * Renderiza la estructura visual de una Tarjeta de Credencial reutilizable
 * Subtarea 3: Estructura visual base, con soporte para inputs adaptables y estilos coherentes
 */
function renderTarjetaCredencial($canal, $icono, $label1, $name1, $val1, $label2, $name2, $val2, $type2 = "text", $conFoto = false, $placeholder2 = "", $placeholder1 = "")
{
    $textoPlaceholder1 = $placeholder1 !== "" ? $placeholder1 : ("Ingrese " . strtolower($label1));
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
                    <input type="text" class="form-control" id="<?php echo $name1; ?>" name="<?php echo $name1; ?>" value="<?php echo htmlspecialchars($val1); ?>" placeholder="<?php echo htmlspecialchars($textoPlaceholder1); ?>">
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
        <main class="col-12">
            <div class="card p-4 shadow-sm h-100" style="min-height: 400px;">
                <div id="vista-configuracion-contenido">
                    <!-- Cabecera de la Vista -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold text-primary"><i class="fa-solid fa-gears me-2"></i> Configuración de Conexión</h4>
                        <p class="text-muted mb-0">Establece y administra las credenciales de conexión del bot de Telegram de OmniBot Hospital.</p>
                    </div>

                    <form id="form-configuracion-conexion" class="needs-validation" novalidate>
                        <div class="row" id="tarjetas-credenciales-container">
                            <div class="col-12">
                                <div class="card shadow-sm h-100 border rounded-3">
                                    <div class="card-header bg-transparent border-0 pt-4 pb-2">
                                        <h5 class="fw-bold d-flex align-items-center mb-0">
                                            <i class="fa-brands fa-telegram text-info me-2 fs-4"></i>
                                            Telegram
                                        </h5>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="row g-4 align-items-center">

                                            <!-- Columna izquierda: foto de perfil -->
                                            <div class="col-12 col-md-4">
                                                <div class="cfg-foto">
                                                    <p class="cfg-foto-titulo mb-3">Foto de perfil del bot</p>
                                                    <div class="cfg-foto-preview mb-3" id="telegram-foto-preview" aria-hidden="true">
                                                        <i class="fa-solid fa-robot"></i>
                                                    </div>
                                                    <label class="btn btn-outline-primary cfg-foto-btn mb-0" for="telegram_foto_perfil">
                                                        <i class="fa-solid fa-image me-2"></i>Seleccionar imagen
                                                    </label>
                                                    <input type="file" class="d-none" id="telegram_foto_perfil" name="telegram_foto_perfil" accept="image/jpeg,image/png,.jpg,.jpeg,.png,.mp4">
                                                </div>
                                            </div>

                                            <!-- Columna derecha: campos -->
                                            <div class="col-12 col-md-8">
                                                <div class="mb-3">
                                                    <label for="nombre_bot" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                                        Nombre del bot
                                                    </label>
                                                    <input type="text" class="form-control" id="nombre_bot" name="nombre_bot" value="" placeholder="Ingrese el nombre del bot">
                                                </div>
                                                <div class="mb-0">
                                                    <label for="telegram_token" class="form-label fw-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                                        Token del bot
                                                    </label>
                                                    <input type="text" class="form-control" id="telegram_token" name="telegram_token" value="" placeholder="Dejar vacío para no cambiar el token">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer (Estado de conexión y botón de guardar) -->
                        <div class="mt-4 pt-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center" id="estado-bot">
                                <i class="fa-solid fa-wifi me-2" id="estado-bot-icono"></i>
                                <span class="fw-semibold small" id="estado-bot-texto">Verificando estado del bot...</span>
                            </div>

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
        const API_URL = 'app/cir-api/endpoints/bot-panel-e.php';

        // --- Estado vivo/muerto del bot desde el backend ---

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

<script>
    // Nombre y token del bot: $.get para leer, $.ajax para guardar.
    $(document).ready(function() {
        var API_URL = "app/cir-api/endpoints/bot-panel-e.php";

        function cargarNombreBot() {
            $.get(API_URL + "?action=configuracion", function(response, status) {
                console.log(response);
                console.log(status);
                if (status == "success") {
                    var datos = response;
                    if (typeof response == "string") {
                        datos = JSON.parse(response);
                    }
                    $("#nombre_bot").val(datos.nombre_bot);
                }
            });
        }

        $("#form-configuracion-conexion").submit(function(e) {
            e.preventDefault();

            var nombre = $("#nombre_bot").val().trim();
            var token = $("#telegram_token").val().trim();

            if (nombre == "") {
                Swal.fire({
                    icon: "warning",
                    title: "Nombre vacío",
                    text: "Ingresá el nombre del bot.",
                    confirmButtonColor: "#20c997"
                });
                return;
            }

            var datosEnviar = {
                nombre_bot: nombre
            };
            if (token != "") {
                datosEnviar.token = token;
            }

            var btn = $(this).find("button[type='submit']");
            var textoBoton = btn.html();
            btn.html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...');
            btn.prop("disabled", true);

            $.ajax({
                url: API_URL + "?action=configuracion",
                type: "PUT",
                contentType: "application/json",
                data: JSON.stringify(datosEnviar),
                success: function(response, status) {
                    console.log(response);
                    console.log(status);
                    if (status == "success") {
                        var datos = response;
                        if (typeof response == "string") {
                            datos = JSON.parse(response);
                        }

                        if (datos.status != "success") {
                            Swal.fire({
                                icon: "error",
                                title: "No se pudo guardar",
                                text: datos.message || "No se pudo guardar la configuración.",
                                confirmButtonColor: "#20c997"
                            });
                            return;
                        }

                        if (datos.validacion && datos.validacion.nombre && datos.validacion.nombre.ok == false) {
                            Swal.fire({
                                icon: "error",
                                title: "No se pudo cambiar el nombre",
                                text: datos.validacion.nombre.mensaje || "Telegram no aceptó el nombre.",
                                confirmButtonColor: "#20c997"
                            });
                            cargarNombreBot();
                            return;
                        }

                        if (datos.validacion && datos.validacion.token && datos.validacion.token.mensaje) {
                            Swal.fire({
                                icon: "warning",
                                title: "Nombre actualizado",
                                text: datos.validacion.token.mensaje,
                                confirmButtonColor: "#20c997"
                            });
                        } else {
                            Swal.fire({
                                icon: "success",
                                title: "Configuración guardada",
                                text: "El nombre del bot se actualizó.",
                                confirmButtonColor: "#20c997"
                            });
                        }

                        $("#telegram_token").val("");
                        cargarNombreBot();
                    }
                },
                error: function(xhr, status) {
                    console.log(status);
                    var mensaje = "No se pudo guardar la configuración. Intenta nuevamente.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: "error",
                        title: "No se pudo guardar",
                        text: mensaje,
                        confirmButtonColor: "#20c997"
                    });
                },
                complete: function() {
                    btn.html(textoBoton);
                    btn.prop("disabled", false);
                }
            });
        });

        cargarNombreBot();
    });
</script>

<style>
    .cfg-foto {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    }

    @media (min-width: 768px) {
        .cfg-foto {
            padding-bottom: 0;
            border-bottom: 0;
            padding-right: 1.5rem;
            border-right: 1px solid rgba(0, 0, 0, 0.12);
        }

        body.modo-oscuro .cfg-foto {
            border-right-color: #444;
        }
    }

    .cfg-foto-preview {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #e7f3ff;
        color: #0d6efd;
        font-size: 2.6rem;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }

    .cfg-foto-preview img,
    .cfg-foto-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cfg-foto-btn {
        border-radius: 0.4rem;
    }

    body.modo-oscuro .cfg-foto {
        border-bottom-color: #444;
    }

    body.modo-oscuro .cfg-foto-preview {
        background: #1a3348;
        color: #6cb6ff;
        border-color: #375a7f;
    }
</style>