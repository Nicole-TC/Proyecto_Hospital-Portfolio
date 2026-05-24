# Story 2.2 — Relaciones: admisión, turno, orden_estudio, lista_espera, cirugía

**Tabla central:** `orden_medica` (`id_orden`)

---

## Diagrama de relaciones (flujo principal)

```
persona → persona_asiste → orden_medica ← admision (1:1 típico en CQ)
                              │
         ┌────────────────────┼────────────────────┐
         ▼                    ▼                    ▼
   orden_estudio          turno              lista_espera
   (N estudios)         (agenda CQ)          (lista espera)
         │                    │
         │                    └──► cirugia (programación quirúrgica)
         └──► resultado_estudio
```

---

## `admision`

| Campo clave | Relación |
|-------------|----------|
| `id_orden` | FK → `orden_medica`, **ON DELETE CASCADE** |
| `fecha_inicio`, `id_estado` | Estado del ingreso al circuito |

**Rol:** Se crea en el mismo flujo que la orden (Admisión). En dev: 25 admisiones / 25 órdenes (relación 1:1).

**Impacto baja lógica (`CANC`):** No se borra la fila de admisión; la orden sigue existiendo con estado cancelado. CASCADE solo aplica si se hace `DELETE` físico (no usado en el CRUD).

---

## `orden_estudio`

| Campo clave | Relación |
|-------------|----------|
| PK compuesta | `(id_orden, id_estudio)` |
| `id_estado` | Estado de cada estudio prequirúrgico |

**Rol:** Generada automáticamente por el trigger `fn_orden` al **insertar** la orden, según `combo_cirugia` del catálogo elegido.

**Impacto edición:** Si se cambia `id_cirugia_catalogo` en el CRUD, **no** se regeneran estudios automáticamente (el trigger solo corre en INSERT). Considerar regla futura en Story 4.

En dev: ~102 filas `orden_estudio` para ~25 órdenes.

---

## `turno`

| Campo clave | Relación |
|-------------|----------|
| `id_orden` | FK → `orden_medica`, **ON DELETE SET NULL** |
| `id_persona_asiste` | Paciente del turno |
| `cancelado`, `id_estado` | Control en Agenda |

**Rol:** Turnos prequirúrgicos / agenda. Agenda puede pasar la orden a `PROC` al programar (`agenda-b.php`).

En dev: 11 turnos vinculados a orden.

**Impacto cancelar orden:** Turnos existentes pueden quedar huérfanos de sentido de negocio; el CRUD no los cancela automáticamente (pendiente Story 4).

---

## `lista_espera`

| Campo clave | Relación |
|-------------|----------|
| `id_orden` | FK, **ON DELETE CASCADE** |
| `prioridad`, `fecha_ingreso`, `id_estado` | Cola quirúrgica |

**Rol:** Pacientes aptos en espera de programación. Trigger `trg_lista` / `fn_cirugia` en INSERT de lista.

---

## `cirugia`

| Campo clave | Relación |
|-------------|----------|
| `id_orden` | FK, **ON DELETE CASCADE** |
| `id_turno`, `id_quirofano` | Cirugía programada en quirófano |

**Rol:** Registro de cirugía efectiva (posterior al circuito prequirúrgico). No gestionada por el CRUD de órdenes.

---

## Resumen para el desarrollador

| Tabla hija | ON DELETE respecto a orden | ¿Tocada por CRUD órdenes? |
|------------|----------------------------|---------------------------|
| admision | CASCADE | No (solo lectura indirecta) |
| orden_estudio | — (PK compuesta) | No |
| turno | SET NULL | No |
| lista_espera | CASCADE | No |
| cirugia | CASCADE | No |

**Riesgo principal:** Evitar `DELETE` en `orden_medica`; usar solo `id_estado = CANC`.
