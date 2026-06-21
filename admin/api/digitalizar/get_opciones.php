<?php
session_start();
require_once '../../model/conexion_dig.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado. Por favor inicie sesión."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    // Obtener tipos de documentos
    $tipos_documentos = [];

    $stmtTipos = $bd_dig->prepare("
        SELECT id, nombre
        FROM tipos_documentos
        WHERE estado = true
        ORDER BY nombre ASC
    ");

    if ($stmtTipos) {

        $stmtTipos->execute();

        $tipos_documentos = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener etiquetas
    $etiquetas = [];

    $stmtEtiquetas = $bd_dig->prepare("
        SELECT id, nombre_etiqueta AS name, color_hex
        FROM etiquetas
        WHERE estado = true
        ORDER BY nombre_etiqueta ASC
    ");

    if ($stmtEtiquetas) {

        $stmtEtiquetas->execute();

        $etiquetas = $stmtEtiquetas->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener servicios (sectores)
    $servicios = [];
    $stmtServicios = $bd_dig->prepare("
        SELECT id, nombre
        FROM servicios
        WHERE estado = true
        ORDER BY nombre ASC
    ");
    
    if ($stmtServicios) {
        $stmtServicios->execute();
        $servicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);
    }

    $responsables = [];
    
    try {
        // Admins
        $stmtAdmin = $bd_dig->prepare("SELECT id, nombre || ' ' || apellido AS nombre, 'admin' AS tipo FROM admin WHERE activo = true ORDER BY nombre ASC");
        if ($stmtAdmin) {
            $stmtAdmin->execute();
            $admins = $stmtAdmin->fetchAll(PDO::FETCH_ASSOC);
            foreach($admins as $a) {
                $responsables[] = $a;
            }
        }
    } catch (Exception $e) {
        error_log("Error al obtener admins para responsables: " . $e->getMessage());
    }

    try {
        // Personal (excluyendo Pacientes)
        $stmtPersonal = $bd_dig->prepare("SELECT id, apellidoynombre AS nombre, 'personal' AS tipo FROM personal WHERE activo = true AND LOWER(encasillamiento) != 'paciente' AND LOWER(cargo) != 'paciente' ORDER BY apellidoynombre ASC");
        if ($stmtPersonal) {
            $stmtPersonal->execute();
            $personalList = $stmtPersonal->fetchAll(PDO::FETCH_ASSOC);
            foreach($personalList as $p) {
                $responsables[] = $p;
            }
        }
    } catch (Exception $e) {
        error_log("Error al obtener personal para responsables: " . $e->getMessage());
    }

    // Obtener tipos de identificacion (personas)
    $tipos_identificacion = [];
    try {
        $stmtTiposIden = $bd_dig->prepare("
            SELECT id, nombre
            FROM tipos_identificacion
            WHERE estado = true
            ORDER BY id ASC
        ");
        
        if ($stmtTiposIden) {
            $stmtTiposIden->execute();
            $tipos_identificacion = $stmtTiposIden->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // En caso de error de permisos, devolver array vacío para no romper el resto
        error_log("Error al obtener tipos de identificacion: " . $e->getMessage());
    }
    // Obtener configuraciones
    $configuraciones = [];
    try {
        $stmtConf = $bd_dig->prepare("
            SELECT clave, valor
            FROM configuraciones
        ");
        if ($stmtConf) {
            $stmtConf->execute();
            $confResults = $stmtConf->fetchAll(PDO::FETCH_ASSOC);
            foreach ($confResults as $c) {
                $configuraciones[$c['clave']] = $c['valor'];
            }
        }
    } catch (PDOException $e) {
        error_log("Error al obtener configuraciones: " . $e->getMessage());
    }
    
    // Fallback por defecto si no existe
    if (!isset($configuraciones['max_peso_archivo_mb'])) {
        $configuraciones['max_peso_archivo_mb'] = '5';
    }

    http_response_code(200);

    echo json_encode([
        "status" => "success",
        "data" => [
            "tipos_documentos" => $tipos_documentos,
            "etiquetas" => $etiquetas,
            "servicios" => $servicios,
            "responsables" => $responsables,
            "tipos_identificacion" => $tipos_identificacion,
            "configuraciones" => $configuraciones
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener opciones: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}