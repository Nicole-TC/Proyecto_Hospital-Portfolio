# Story 2.1 — Tabla `orden_medica` y campos editables

**Epic:** CRUD Órdenes Médicas · **Módulo:** Circuito Quirúrgico  
**Fuente:** PostgreSQL `circuito_hospital` (revisión 2026-05-21)

---

## Estructura de la tabla

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id_orden` | integer (PK, serial) | NO | Identificador único de la orden |
| `id_persona_asiste` | integer (FK) | Sí | Paciente en el circuito → `persona_asiste` |
| `id_profesional` | integer (FK) | Sí | Cirujano responsable → `profesional` |
| `id_cirugia_catalogo` | integer (FK) | Sí | Tipo de cirugía → `catalogo_cirugia` |
| `fecha_orden` | date | Sí | Fecha de emisión de la orden |
| `observaciones` | text | Sí | Notas libres |
| `id_estado` | integer (FK) | Sí | Estado del flujo → `estado` (entidad `ORDEN`) |

## Claves foráneas (salida)

| FK | Tabla referenciada |
|----|-------------------|
| `id_persona_asiste` | `persona_asiste(id_persona_asiste)` |
| `id_profesional` | `profesional(id_profesional)` |
| `id_cirugia_catalogo` | `catalogo_cirugia(id_cirugia_catalogo)` |
| `id_estado` | `estado(id_estado)` |

## Uso en el CRUD implementado

| Columna | Listado | Edición | Baja lógica |
|---------|---------|---------|-------------|
| `id_orden` | Sí (solo lectura) | Solo lectura | — |
| `id_persona_asiste` | Vía join paciente | **No editable** | — |
| `id_profesional` | Sí (nombre cirujano) | **Editable** | — |
| `id_cirugia_catalogo` | Sí (nombre cirugía) | **Editable** | — |
| `fecha_orden` | Sí | **Editable** | — |
| `observaciones` | Opcional en detalle | **Editable** | — |
| `id_estado` | Sí (badge) | No manual en UI | **→ `CANC`** |

## Creación de registros

El **único** `INSERT` en PHP del repositorio está en `admin/app/cir-api/php/admision-b.php` al confirmar una admisión. Estado inicial típico: `PEND`.

## Datos de referencia (entorno dev)

- ~25 órdenes en base de desarrollo al momento del análisis.
