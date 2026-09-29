<?php
// ── Vista activa ────────────────────────────────────────────────────────
$vistasPermitidas = ['prequirurgico', 'entrega', 'cirugias'];
$vista = $_GET['vista'] ?? 'prequirurgico';
if (!in_array($vista, $vistasPermitidas)) $vista = 'prequirurgico';

// ── URL base para los tabs ──────────────────────────────────────────────
$baseUrl = 'index.php?modulo=menu_circuitoquirurgico&accion=agenda';
?>

<div class="col-12 mb-5 px-3">
  <div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">

      <!-- ── Encabezado ── -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="fw-bold mb-2">Agenda de Turnos</h1>
          <p class="text-secondary mb-0">Seleccioná el tipo de agenda</p>
        </div>
        <div class="d-flex flex-column align-items-end gap-1">
          <div class="fw-bold fs-4 font-monospace" id="clockTime">--:--:--</div>
          <small class="text-secondary" id="clockDate">--</small>
        </div>
      </div>

      <!-- ── Tabs de vista ── -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <ul class="nav nav-pills gap-2" id="agendaTabs">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 <?= $vista === 'prequirurgico' ? 'active' : '' ?>"
               href="<?= $baseUrl ?>&vista=prequirurgico"
               style="<?= $vista === 'prequirurgico' ? 'background:#185FA5;' : 'color:#185FA5;border:1px solid #B5D4F4;background:transparent;' ?> border-radius:0.65rem;">
              <i class="bi bi-activity"></i> Pre-quirúrgico
            </a>
          </li>
        </ul>
        
        <div class="d-flex gap-2">
          <a href="index.php?modulo=menu_circuitoquirurgico&accion=config_cupos&tipo=pre" class="btn btn-outline-primary fw-semibold" style="border-radius:0.65rem;">
            <i class="bi bi-gear me-1"></i> Configurar Cupos
          </a>
          <a href="index.php?modulo=menu_circuitoquirurgico&accion=asistencia" class="btn btn-outline-success fw-semibold" style="border-radius:0.65rem;">
            <i class="bi bi-clipboard-check me-1"></i> Asistencia
          </a>
        </div>
      </div>

      <!-- ── Contenido de la vista activa ── -->
      <?php include __DIR__ . "/agenda_{$vista}.php"; ?>

    </div>
  </div>
</div>

<!-- Reloj: inline para evitar problemas de ruta -->
<script>
(function () {
    function updateClock() {
        var el = document.getElementById('clockTime');
        var el2 = document.getElementById('clockDate');
        if (!el || !el2) return;
        var now = new Date();
        el.textContent = now.toLocaleTimeString('es-AR', { hourCycle: 'h23' });
        el2.textContent = now.toLocaleDateString('es-AR', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }
    updateClock();
    setInterval(updateClock, 1000);
})();


</script>