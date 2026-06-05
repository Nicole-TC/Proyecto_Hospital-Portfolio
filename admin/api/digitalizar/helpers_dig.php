<?php
/*
 * Helpers reutilizables para los endpoints de digitalizar.
 */

/**
 * Sanitiza y normaliza los parámetros GET de búsqueda de documentos.
 *
 * @param array $get  Normalmente $_GET
 * @return array {
 *   string    $dni_paciente
 *   string    $codigo_unico
 *   int|false $id_tipo_documento
 *   int|false $id_tipo_identificacion
 *   int|false $id_servicio
 *   string|null $estado_documento
 *   string    $fecha_inicio
 *   string    $fecha_fin
 *   int[]     $etiquetas
 *   int       $pagina
 *   int       $limite
 * }
 */
function sanitizar_params_busqueda(array $get): array
{
    $dni_paciente           = trim($get['dni'] ?? '');
    $codigo_unico           = trim($get['codigo_unico'] ?? '');
    $id_tipo_documento      = filter_var($get['id_tipo_documento'] ?? '', FILTER_VALIDATE_INT);
    $id_tipo_identificacion = filter_var($get['id_tipo_identificacion'] ?? '', FILTER_VALIDATE_INT);
    $id_servicio            = filter_var($get['id_servicio'] ?? '', FILTER_VALIDATE_INT);
    $estados_permitidos = ['pendiente', 'revisado', 'archivado'];
    $estado_documento = isset($get['estado_documento']) && in_array($get['estado_documento'], $estados_permitidos)
        ? $get['estado_documento']
        : null;
    $fecha_inicio           = trim($get['fecha_inicio'] ?? '');
    $fecha_fin              = trim($get['fecha_fin'] ?? '');

    // Etiquetas: array, string separado por comas o tag_id como alias
    $etiquetas = [];
    if (isset($get['etiquetas'])) {
        $etiquetas = is_array($get['etiquetas'])
            ? $get['etiquetas']
            : explode(',', $get['etiquetas']);
    } elseif (isset($get['tag_id']) && $get['tag_id'] !== '') {
        $etiquetas = [$get['tag_id']];
    }
    $etiquetas = array_filter(array_map('intval', $etiquetas));

    $pagina = max(1, filter_var($get['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
    $limite = max(1, min(100, filter_var($get['limite'] ?? 50, FILTER_VALIDATE_INT) ?: 50));

    return compact(
        'dni_paciente',
        'codigo_unico',
        'id_tipo_documento',
        'id_tipo_identificacion',
        'id_servicio',
        'estado_documento',
        'fecha_inicio',
        'fecha_fin',
        'etiquetas',
        'pagina',
        'limite'
    );
}
