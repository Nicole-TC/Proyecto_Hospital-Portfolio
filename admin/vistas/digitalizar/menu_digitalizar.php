<?php
// Obtener tags de la base de datos
$tags = [];
try {
    $stmt = $bd->prepare("SELECT id, nombre AS name FROM etiquetas WHERE activo = 1 ORDER BY nombre ASC");
    if ($stmt) {
        $stmt->execute();
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    // Si la tabla no existe o hay error, continuamos silenciosamente
}
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <!-- Tarjeta Cargar Nuevo Documento -->
        <div class="col-md-5 col-lg-5 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body text-center d-flex flex-column justify-content-center p-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-cloud-arrow-up text-primary" style="font-size: 4.5rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Cargar Nuevo Documento</h3>
                    <p class="card-text text-muted mb-4">Sube un nuevo documento al sistema de digitalización. Asigna sus datos y etiquetas correspondientes para un fácil acceso posterior.</p>
                    <a href="#" class="btn btn-primary btn-lg rounded-pill px-4 py-2 mt-auto align-self-center shadow">
                        <i class="fa-solid fa-plus me-2"></i> Nuevo Documento
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta Buscar Documento -->
        <div class="col-md-7 col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 menu-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-magnifying-glass text-info" style="font-size: 3rem;"></i>
                        <h3 class="card-title fw-bold mt-3 mb-2">Buscar Documento</h3>
                        <p class="card-text text-muted">Encuentra documentos almacenados utilizando palabras clave y filtros avanzados.</p>
                    </div>
                    
                    <form action="index.php" method="GET">
                        <!-- Redirigir a un futuro modulo de busqueda -->
                        <input type="hidden" name="modulo" value="buscar_documentos">
                        
                        <div class="mb-4">
                            <label for="palabra_clave" class="form-label fw-bold"><i class="fa-solid fa-keyboard me-2 text-muted"></i>Palabra Clave</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" id="palabra_clave" name="q" placeholder="Ej: informe, receta, paciente...">
                        </div>
                        
                        <div class="mb-4">
                            <label for="tag_id" class="form-label fw-bold"><i class="fa-solid fa-tags me-2 text-muted"></i>Etiqueta (Tag)</label>
                            <select class="form-select form-select-lg bg-light border-0" id="tag_id" name="tag_id">
                                <option value="">-- Seleccionar Etiqueta --</option>
                                <?php if (!empty($tags)): ?>
                                    <?php foreach($tags as $tag): ?>
                                        <option value="<?php echo htmlspecialchars($tag['id']); ?>">
                                            <?php echo htmlspecialchars($tag['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay etiquetas cargadas</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="d-grid mt-4 pt-2">
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