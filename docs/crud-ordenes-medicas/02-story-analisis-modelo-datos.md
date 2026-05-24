# Story 2 — Análisis del modelo de datos (completa)

**Epic:** CRUD Órdenes Médicas · **Estado:** ✅ Documentación cerrada  
**Base:** PostgreSQL `circuito_hospital` · **Revisión:** 2026-05-21

---

## Subtareas Jira

| ID | Tarea | Documento | Commit |
|----|-------|-----------|--------|
| 2.1 | Columnas `orden_medica` y campos editables | [02-1-orden-medica-columnas.md](./02-1-orden-medica-columnas.md) | `5cd8741` |
| 2.2 | Relaciones flujo principal | [02-2-relaciones-flujo-principal.md](./02-2-relaciones-flujo-principal.md) | `7d3396c` |
| 2.3 | `evento_clinico` y `verificacion_pre_cirugia` | [02-3-relaciones-eventos-verificacion.md](./02-3-relaciones-eventos-verificacion.md) | (ver log) |
| 2.4 | Estados ORDEN | [02-4-estados-orden.md](./02-4-estados-orden.md) | (ver log) |
| 2.5 | Trigger `fn_orden` | [02-5-trigger-fn-orden.md](./02-5-trigger-fn-orden.md) | (ver log) |
| 2.6 | Consulta del listado | [02-6-consulta-listado.md](./02-6-consulta-listado.md) | (ver log) |

---

## Hallazgos clave para siguientes stories

1. **No usar DELETE** en `orden_medica` — hijos con CASCADE (`admision`, `evento_clinico`, etc.).
2. **Baja lógica = `CANC`** — ya implementada en API.
3. **Editar cirugía** no regenera `orden_estudio` — definir regla en Story 4.
4. **Cancelar orden** no cancela turnos en Agenda — definir regla en Story 4.
5. **Listado** ya alineado a modelo con JOINs documentados en 2.6.

---

## Criterios de aceptación Story 2

- [x] Columnas y editabilidad documentadas (2.1)
- [x] Relaciones hijas mapeadas (2.2, 2.3)
- [x] Estados ORDEN documentados (2.4)
- [x] Trigger de creación documentado (2.5)
- [x] Consulta de listado documentada (2.6)
- [x] Un commit por subtarea entregable

**Siguiente recomendada en Jira:** Story 3 (baja lógica) o Story 4 (reglas de negocio detalladas).
