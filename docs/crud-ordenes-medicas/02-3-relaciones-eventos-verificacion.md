# Story 2.3 — Relaciones: `evento_clinico` y `verificacion_pre_cirugia`

---

## `evento_clinico`

Registro de hitos clínicos ligados a una orden y opcionalmente a un turno.

| Columna | Tipo | Uso |
|---------|------|-----|
| `id_evento` | PK | Identificador |
| `id_tipo_evento` | FK → `tipo_evento` | Tipo de evento (catálogo) |
| `id_orden` | FK → `orden_medica` | **ON DELETE CASCADE** |
| `id_turno` | FK → `turno` | Turno asociado (opcional) |
| `fecha_evento` | timestamp | Cuándo ocurrió |
| `descripcion` | text | Texto libre |

### Tipos en `tipo_evento` (catálogo)

| Código | Uso previsto |
|--------|----------------|
| `TURNO_PROGRAMADO` | Agenda al programar turno |
| `ESTUDIO_SOLICITADO` | Solicitud de estudio |
| `RESULTADO_ESTUDIO` | Resultado cargado |
| `CIRUGIA_PROGRAMADA` | Cirugía agendada |
| `CIRUGIA_REALIZADA` | Cirugía hecha |
| `CANCELACION` | Cancelaciones |
| `NO_ASISTIO` | Inasistencia |

### Quién escribe eventos hoy (código)

| Origen | Acción |
|--------|--------|
| Trigger `fn_orden` | INSERT al crear orden: tipo 1, descripción «Orden creada» |
| `agenda-b.php` | INSERT al programar turno prequirúrgico |

### Quién **no** escribe aún

- CRUD `ordenes_medicas` al editar o cancelar (mejora opcional Story 9.9).
- Admisión (solo vía trigger en INSERT).

**Datos dev:** ~36 eventos; predominio `TURNO_PROGRAMADO`.

**Impacto `CANC`:** Los eventos históricos se conservan; no se borran con baja lógica (solo con DELETE físico de la orden).

---

## `verificacion_pre_cirugia`

Checklist / verificación prequirúrgica por profesional.

| Columna | Tipo | Uso |
|---------|------|-----|
| `id_verificacion` | PK | |
| `id_orden` | FK → `orden_medica` | Sin ON DELETE CASCADE en FK |
| `id_profesional` | FK | Quién verifica |
| `checklist` | text | Contenido del checklist |
| `id_estado` | FK | Estado de la verificación |
| `fecha` | timestamp | |

**Datos dev:** 0 registros (tabla preparada, sin uso visible en UI actual).

**Relación con CRUD órdenes:** Ninguna directa; pantalla futura de pre-cirugía podría consumir `id_orden`.

---

## Tablas de auditoría relacionadas (referencia)

| Tabla | Relación con orden |
|-------|-------------------|
| `auditoria` | Solo triggers en `persona` / `orden` INSERT (tabla genérica, no FK a orden) |
| `evento_clinico` | FK directa a `id_orden` |

La pantalla **Auditoría Global** aún no consulta estas tablas (ver diagnóstico previo).
