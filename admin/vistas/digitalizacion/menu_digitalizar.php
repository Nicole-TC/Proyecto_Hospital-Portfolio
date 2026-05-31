<?php
// Obtener tags de la base de datos
?>

<div class="container-fluid px-4 px-xl-5 mt-4 mb-5">
    <div class="row justify-content-center">
        <!-- Tarjeta Cargar Nuevo Documento -->
        <div class="col-md-6 col-lg-4 col-xl-custom-5 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4 p-xl-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-cloud-arrow-up text-primary" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Cargar Nuevo Documento</h3>
                    <p class="card-text text-muted mb-4">Sube un nuevo documento al sistema de digitalización. Asigna sus datos y etiquetas correspondientes para un fácil acceso posterior.</p>
                    <a href="index.php?modulo=menu_digitalizar&accion=nuevo_documento" class="btn btn-primary btn-lg rounded-pill mt-auto align-self-center shadow btn-menu">
                        <i class="fa-solid fa-plus me-2"></i> Nuevo Documento
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta Administrar Etiquetas -->
        <div class="col-md-6 col-lg-4 col-xl-custom-5 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4 p-xl-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-tags text-warning" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Administrar Etiquetas</h3>
                    <p class="card-text text-muted mb-4">Gestiona las etiquetas disponibles en el sistema para organizar y categorizar los documentos de forma eficiente.</p>
                    <a href="index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar" class="btn btn-warning btn-lg rounded-pill mt-auto align-self-center shadow text-white btn-menu">
                        <i class="fa-solid fa-tag me-2"></i> Administrar Etiquetas
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta Administrar Tipos de Documentos -->
        <div class="col-md-6 col-lg-4 col-xl-custom-5 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4 p-xl-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-file-signature text-success" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Tipos de Documentos</h3>
                    <p class="card-text text-muted mb-4">Administra los diferentes tipos de documentos que se pueden subir al sistema (Recetas, Informes, Historias Clínicas, etc).</p>
                    <a href="index.php?modulo=menu_digitalizar&accion=tipos_documentos" class="btn btn-success btn-lg rounded-pill mt-auto align-self-center shadow text-white btn-menu">
                        <i class="fa-solid fa-list me-2"></i> Administrar Tipos
                    </a>
                </div>
            </div>
        </div>

                <!-- Tarjeta Buscar Documento -->
        <div class="col-md-6 col-lg-4 col-xl-custom-5 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4 p-xl-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-magnifying-glass text-info" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Buscar Documento</h3>
                    <p class="card-text text-muted mb-4">Encuentra documentos utilizando palabras clave y filtros.</p>
                    <a href="index.php?modulo=menu_digitalizar&accion=buscar_documentos" class="btn btn-info btn-lg rounded-pill mt-auto align-self-center shadow text-white btn-menu">
                        <i class="fa-solid fa-search me-2"></i> Buscar Documento
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
        <!-- Tarjeta Administrar Servicios -->
        <div class="col-md-6 col-lg-4 col-xl-custom-5 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4 p-xl-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-hospital text-secondary" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Servicios</h3>
                    <p class="card-text text-muted mb-4">Administra los servicios (áreas) del hospital, asocia responsables y gestiona su estado para la carga de documentos.</p>
                    <a href="index.php?modulo=menu_digitalizar&accion=servicios_digitalizar" class="btn btn-secondary btn-lg rounded-pill mt-auto align-self-center shadow text-white btn-menu">
                        <i class="fa-solid fa-layer-group me-2"></i> Administrar Servicios
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media (min-width: 1200px) {
    .col-xl-custom-5 {
        flex: 0 0 20%;
        max-width: 20%;
    }
}
.rounded-4 { border-radius: 1rem !important; }
.bg-light { background-color: #f8f9fa !important; }
.menu-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.menu-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important; }
.btn-menu {
    width: 100%;
    min-height: 64px;
    padding-top: 12px !important;
    padding-bottom: 12px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: normal;
    text-align: center;
    font-size: 1rem;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    try {
        const response = await fetch("api/digitalizar/get_opciones.php");
        const json = await response.json();
        
        if (json.status === 'success') {
            const selectTag = document.getElementById("tag_id");
            if(json.data.etiquetas.length === 0) {
                const option = document.createElement("option");
                option.value = "";
                option.disabled = true;
                option.textContent = "No hay etiquetas cargadas";
                selectTag.appendChild(option);
            } else {
                json.data.etiquetas.forEach(etiqueta => {
                    const option = document.createElement("option");
                    option.value = etiqueta.id;
                    option.textContent = etiqueta.name;
                    selectTag.appendChild(option);
                });
            }
        }
    } catch(error) {
        console.error("Error al cargar las etiquetas:", error);
    }
});
</script>