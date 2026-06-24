-- =============================================================
-- Índices para optimización de búsqueda de documentos
-- Archivo: admin/api/digitalizar/sql/indices_busqueda.sql
--
-- Instrucciones de ejecución:
--   Con psql:
--     psql -U dev_dig -d digitalizar -h 149.50.133.15 -p 9432 -f indices_busqueda.sql
--   Con Docker:
--     docker exec -i cliente_proyecto psql -U dev_dig -d digitalizar < indices_busqueda.sql
--
-- IMPORTANTE: ejecutar con un usuario con permisos de CREATE INDEX.
-- Los índices se crean con IF NOT EXISTS para poder re-ejecutar sin errores.
-- =============================================================

-- Índice en personal_identificaciones para filtro por DNI
CREATE INDEX IF NOT EXISTS idx_personal_identificaciones_numero
    ON personal_identificaciones (numero_identificacion);

CREATE INDEX IF NOT EXISTS idx_personal_identificaciones_tipo
    ON personal_identificaciones (id_tipo_identificacion);

-- Índice en documento_etiquetas para filtro por etiqueta
CREATE INDEX IF NOT EXISTS idx_documento_etiquetas_id_etiqueta
    ON documento_etiquetas (id_etiqueta);

CREATE INDEX IF NOT EXISTS idx_documento_etiquetas_id_documento
    ON documento_etiquetas (id_documento);

-- Índice en documentos para filtros frecuentes y ORDER BY
CREATE INDEX IF NOT EXISTS idx_documentos_fecha_subido_id
    ON documentos (fecha_subido DESC, id DESC);

CREATE INDEX IF NOT EXISTS idx_documentos_id_tipo_documento
    ON documentos (id_tipo_documento);

CREATE INDEX IF NOT EXISTS idx_documentos_id_servicio
    ON documentos (id_servicio);

CREATE INDEX IF NOT EXISTS idx_documentos_estado_documento
    ON documentos (estado_documento);

CREATE INDEX IF NOT EXISTS idx_documentos_codigo_unico
    ON documentos (codigo_unico);

CREATE INDEX IF NOT EXISTS idx_documentos_estado
    ON documentos (estado);