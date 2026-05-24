# Story 2.5 — Trigger `trg_orden` / función `fn_orden`

**Cuándo se ejecuta:** `AFTER INSERT` en `orden_medica` (solo al **crear** una orden, típicamente desde Admisión).

---

## Código en base de datos (resumen)

```sql
BEGIN
    -- 1) Estudios prequirúrgicos según combo de la cirugía
    INSERT INTO orden_estudio (id_orden, id_estudio, id_estado)
    SELECT NEW.id_orden, c.id_estudio,
           (SELECT id_estado FROM estado WHERE codigo = 'PEND' LIMIT 1)
    FROM combo_cirugia c
    WHERE c.id_cirugia_catalogo = NEW.id_cirugia_catalogo;

    -- 2) Evento clínico inicial
    INSERT INTO evento_clinico (id_tipo_evento, id_orden, fecha_evento, descripcion)
    VALUES (1, NEW.id_orden, NOW(), 'Orden creada');

    RETURN NEW;
END;
```

---

## Efectos automáticos al admitir un paciente

| Paso | Tabla afectada | Qué se crea |
|------|----------------|------------|
| 1 | `orden_medica` | Fila nueva (PHP Admisión) |
| 2 | `orden_estudio` | Una fila por cada `estudio` en `combo_cirugia` de la cirugía elegida |
| 3 | `evento_clinico` | Un evento con `id_tipo_evento = 1` («Orden creada») |
| 4 | `admision` | Fila en transacción PHP (después de la orden) |

---

## Lo que **no** hace el trigger

- No inserta en `turno`, `lista_espera`, `cirugia`.
- No escribe en `auditoria` (el trigger `trg_orden` en auditoría es otro flujo).
- **No se ejecuta** en `UPDATE` de `orden_medica`.

---

## Implicancias para el CRUD de órdenes

| Acción del CRUD | ¿Dispara `fn_orden`? |
|-----------------|----------------------|
| Editar cirugía / cirujano / fecha | **No** — estudios no se recalculan solos |
| Cancelar (`CANC`) | **No** |
| Listar | **No** |

Si se cambia la cirugía en `form_orden`, conviene definir en Story 4 si hay que sincronizar `orden_estudio` manualmente.

---

## Auditoría en INSERT de orden

Existe `trg_orden` en tabla `orden_medica` que llama a `fn_orden()` (estudios + evento).

La tabla `auditoria` del sistema registra cambios en `persona` vía `fn_auditoria`, no en cada UPDATE de orden.
