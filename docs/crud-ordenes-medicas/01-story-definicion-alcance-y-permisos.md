# Story 1 — Definir alcance y permisos (CQ)

**Vistas:** `admin/vistas/circuitoquirurgico/gestion_ordenes.php`, `form_orden.php`  
**URLs:** `accion=gestion_ordenes`, `accion=form_orden&id=`  
**Permiso:** `menu_circuitoquirurgico` (sin columna MySQL nueva)  
**Alta de orden:** solo Admisión (`admision-b.php`)  
**Baja lógica:** `estado` ORDEN → `CANC`  
**API:** `app/cir-api/endpoints/ordenes_medicas-e.php`

Subtareas 1.1–1.5 cerradas en documentación. Validación equipo: checklist pendiente en reunión.
