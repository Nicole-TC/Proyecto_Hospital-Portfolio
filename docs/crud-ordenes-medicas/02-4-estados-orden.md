# Story 2.4 — Estados de la entidad ORDEN

Tabla catálogo: `estado` donde `entidad = 'ORDEN'`.

---

## Catálogo vigente

| id_estado | codigo | descripcion | Significado operativo |
|-----------|--------|-------------|------------------------|
| 4 | `PEND` | Pendiente | Orden creada; circuito no avanzado o sin turnos en proceso |
| 5 | `PROC` | En proceso | Orden con actividad (ej. turno programado — Agenda actualiza) |
| 6 | `FIN` | Finalizada | Circuito/cirugía cerrado |
| 7 | `CANC` | Cancelada | **Baja lógica** del CRUD de órdenes |
| 8 | `RECH` | Orden rechazada | Rechazo médico/administrativo |

---

## Distribución en desarrollo (muestra)

| codigo | Cantidad órdenes |
|--------|------------------|
| PEND | 20 |
| PROC | 3 |
| CANC | 2 |
| FIN | 0 |
| RECH | 0 |

---

## Transiciones usadas en código

| Desde | Hacia | Disparador |
|-------|-------|------------|
| — | `PEND` | INSERT en Admisión (`admision-b.php`) |
| `PEND` | `PROC` | Programar turno en Agenda si orden estaba pendiente (`agenda-b.php`) |
| cualquiera excepto `CANC`/`FIN` | `CANC` | POST `accion: eliminar` en `ordenes_medicas-e.php` |

---

## Reglas implementadas en el CRUD

| Estado | Listado por defecto | Editable | Cancelable |
|--------|---------------------|----------|------------|
| `PEND` | Sí | Sí | Sí |
| `PROC` | Sí | Sí | Sí |
| `FIN` | Sí | Sí | **No** (API rechaza) |
| `CANC` | No (filtro opcional) | **No** | No (ya cancelada) |
| `RECH` | Sí | Según misma regla que PEND* | Sí* |

\*No hay flujo UI específico para `RECH`; se trata como orden activa salvo `CANC`/`FIN`.

---

## Consulta de estado en PHP

```php
id_estado('ORDEN', 'CANC');  // helper en app/cir-api/php/funciones.php
```

Usado en `ordenes_medicas-b.php` para filtrar canceladas y aplicar baja lógica.
