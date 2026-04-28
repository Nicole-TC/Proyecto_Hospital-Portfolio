<?php
include '../../model/conexion.php';

$termino = $_POST['termino'] ?? '';

if (strlen($termino) < 2) {
    echo '';
    exit;
}

// Buscar coincidencias en la base de datos
$consulta = $bd->prepare("SELECT p.*, s.servicio 
                          FROM personal p 
                          LEFT JOIN servicios s ON p.id_servicio = s.id
                          WHERE p.activo = TRUE 
                          AND p.apellido IS NOT NULL
                          AND (p.apellido LIKE :termino 
                               OR p.nombre LIKE :termino 
                               OR p.dni LIKE :termino 
                               OR s.servicio LIKE :termino)
                          ORDER BY p.apellido ASC");

$consulta->execute([':termino' => "%$termino%"]);
$resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

if ($resultados) {
    foreach ($resultados as $resultado) {
        echo '<div class="sugerencia" data-id="' . htmlspecialchars($resultado['id']) . '">'
           . htmlspecialchars($resultado['apellido'] . ', ' . $resultado['nombre'] . ' (' . $resultado['dni'] . ')')
           . '</div>';
    }
} else {
    echo '<div>No se encontraron resultados</div>';
}
?>
