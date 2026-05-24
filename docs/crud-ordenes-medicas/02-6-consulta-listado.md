# Story 2.6 — Consulta base del listado (JOINs)

Implementación de referencia: `ConsultasOrdenesMedicas::listarOrdenes()` en  
`admin/app/cir-api/php/ordenes_medicas-b.php`.

---

## Objetivo del listado

Mostrar por fila: Nº orden, fecha, paciente (DNI + nombre), cirugía, cirujano, estado, acciones.

---

## FROM y JOINs

```sql
FROM orden_medica om
INNER JOIN estado e
        ON e.id_estado = om.id_estado
INNER JOIN persona_asiste pa
        ON pa.id_persona_asiste = om.id_persona_asiste
INNER JOIN persona p
        ON p.id_persona = pa.id_persona
LEFT JOIN catalogo_cirugia cc
        ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
LEFT JOIN profesional pr
        ON pr.id_profesional = om.id_profesional
LEFT JOIN persona pc
        ON pc.id_persona = pr.id_persona
```

| Alias | Rol |
|-------|-----|
| `om` | Orden médica |
| `e` | Código y descripción de estado (`PEND`, `PROC`, …) |
| `pa` / `p` | Paciente (DNI, nombre, apellido) |
| `cc` | Nombre de la cirugía del catálogo |
| `pr` / `pc` | Nombre del cirujano (`apellido, nombre`) |

`LEFT JOIN` en cirugía y profesional por si hubiera datos históricos incompletos.

---

## SELECT (columnas expuestas al frontend)

```sql
SELECT om.id_orden,
       om.fecha_orden,
       om.observaciones,
       e.codigo AS estado_codigo,
       e.descripcion AS estado_descripcion,
       p.dni,
       p.nombre AS paciente_nombre,
       p.apellido AS paciente_apellido,
       cc.nombre AS cirugia,
       cc.id_cirugia_catalogo,
       pr.id_profesional,
       TRIM(COALESCE(pc.apellido, '') || ', ' || COALESCE(pc.nombre, '')) AS cirujano
```

---

## Filtros WHERE

| Parámetro API | Condición |
|---------------|-----------|
| (default) | `om.id_estado != id(CANC)` |
| `incluir_canceladas=1` | Sin excluir `CANC` |
| `filtro=dni` | `p.dni LIKE %valor%` |
| `filtro=apellido` | `p.apellido ILIKE %valor%` |
| `filtro=estado` | `e.codigo = valor` (mayúsculas) |
| `filtro=id_orden` | `om.id_orden = valor` |

---

## Orden y paginación

- `ORDER BY om.id_orden DESC`
- `LIMIT` / `OFFSET` según `pagina` y `limite` (default 10).

---

## Consulta detalle (edición)

`obtenerOrden($id_orden)` — mismos JOINs de paciente, estado y cirugía; sin join de cirujano en nombre (usa IDs para el formulario).

Endpoint: `GET ordenes_medicas-e.php?id={id_orden}`.

---

## Diagrama de datos del listado

```
orden_medica ── estado
      │
      └── persona_asiste ── persona (paciente)
      │
      ├── catalogo_cirugia (cirugía)
      └── profesional ── persona (cirujano)
```
