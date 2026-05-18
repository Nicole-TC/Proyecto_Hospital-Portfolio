<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Tarjeta de Filtros de Búsqueda -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title fw-bold text-info"><i class="fa-solid fa-magnifying-glass me-2"></i> Buscar Documentos</h3>
                    <p class="text-muted">Utilizá los filtros para encontrar documentos específicos.</p>
                </div>
                <div class="card-body p-4">
                    <form id="form-buscar-documentos">
                        <div class="row g-3">
                            <!-- Filtro por DNI -->
                            <div class="col-md-4">
                                <label for="buscar_dni" class="form-label fw-bold">DNI del Paciente</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-0" id="buscar_dni" name="dni" placeholder="Ej: 12345678">
                                </div>
                            </div>
                            
                            <!-- Filtro por Etiquetas -->
                            <div class="col-md-4">
                                <label for="buscar_tag" class="form-label fw-bold">Etiqueta (Tag)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-tags text-muted"></i></span>
                                    <select class="form-select bg-light border-0" id="buscar_tag" name="tag_id">
                                        <option value="">-- Todas las etiquetas --</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Filtro por Rango de Fechas -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Subido entre (Fechas)</label>
                                <div class="input-group">
                                    <input type="date" class="form-control bg-light border-0" id="fecha_desde" name="fecha_desde" title="Fecha Desde">
                                    <span class="input-group-text bg-light border-0 px-2">y</span>
                                    <input type="date" class="form-control bg-light border-0" id="fecha_hasta" name="fecha_hasta" title="Fecha Hasta">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="button" id="btn-limpiar" class="btn btn-light rounded-pill px-4 me-2">Limpiar</button>
                                <button type="submit" class="btn btn-info text-white rounded-pill px-5 shadow-sm fw-bold">
                                    <i class="fa-solid fa-search me-2"></i> Buscar
                                </button>
                            </div>
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
.table-hover tbody tr:hover { background-color: #f8f9fa; }
</style>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    try {
        const response = await fetch("api/digitalizar/get_opciones.php");
        const json = await response.json();
        
        if (json.status === 'success' && json.data.etiquetas) {
            const selectTag = document.getElementById("buscar_tag");
            json.data.etiquetas.forEach(etiqueta => {
                const option = document.createElement("option");
                option.value = etiqueta.id;
                option.textContent = etiqueta.name;
                selectTag.appendChild(option);
            });
        }
    } catch(error) {
        console.error("Error al cargar etiquetas:", error);
    }
});
</script>
