<?php
// Obtener tags de la base de datos
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <!-- Tarjeta Cargar Nuevo Documento -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4 p-xl-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-cloud-arrow-up text-primary" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Cargar Nuevo Documento</h3>
                    <p class="card-text text-muted mb-4">Sube un nuevo documento al sistema de digitalización. Asigna sus datos y etiquetas correspondientes para un fácil acceso posterior.</p>
                    <a href="index.php?modulo=menu_digitalizar&accion=nuevo_documento" class="btn btn-primary btn-lg rounded-pill px-4 py-2 mt-auto align-self-center shadow">
                        <i class="fa-solid fa-plus me-2"></i> Nuevo Documento
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta Administrar Etiquetas -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4 p-xl-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-tags text-warning" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Administrar Etiquetas</h3>
                    <p class="card-text text-muted mb-4">Gestiona las etiquetas disponibles en el sistema para organizar y categorizar los documentos de forma eficiente.</p>
                    <a href="index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar" class="btn btn-warning btn-lg rounded-pill px-4 py-2 mt-auto align-self-center shadow text-white">
                        <i class="fa-solid fa-tag me-2"></i> Administrar Etiquetas
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta Buscar Documento -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body p-4 p-xl-5 d-flex flex-column">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-magnifying-glass text-info" style="font-size: 3rem;"></i>
                        <h3 class="card-title fw-bold mt-3 mb-2">Buscar Documento</h3>
                        <p class="card-text text-muted">Encuentra documentos utilizando palabras clave y filtros.</p>
                    </div>
                    
                    <form action="index.php" method="GET" class="mt-auto">
                        <!-- Redirigir a un futuro modulo de busqueda -->
                        <input type="hidden" name="modulo" value="buscar_documentos">
                        
                        <div class="mb-3">
                            <label for="palabra_clave" class="form-label fw-bold"><i class="fa-solid fa-keyboard me-2 text-muted"></i>Palabra Clave</label>
                            <input type="text" class="form-control bg-light border-0" id="palabra_clave" name="q" placeholder="Ej: informe, receta...">
                        </div>
                        
                        <div class="mb-4">
                            <label for="tag_id" class="form-label fw-bold"><i class="fa-solid fa-tags me-2 text-muted"></i>Etiqueta (Tag)</label>
                            <select class="form-select bg-light border-0" id="tag_id" name="tag_id">
                                <option value="">-- Seleccionar Etiqueta --</option>
                            </select>
                        </div>
                        
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-info btn-lg text-white rounded-pill shadow-sm">
                                <i class="fa-solid fa-search me-2"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rounded-4 { border-radius: 1rem !important; }
.bg-light { background-color: #f8f9fa !important; }
.menu-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.menu-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important; }
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