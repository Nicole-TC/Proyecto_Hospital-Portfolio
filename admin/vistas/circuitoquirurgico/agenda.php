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
        el.textContent = now.toLocaleTimeString('es-AR');
        el2.textContent = now.toLocaleDateString('es-AR', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }
    updateClock();
    setInterval(updateClock, 1000);
})();

// ─── Popup paciente admitido desde admisión ──
(function() {
    var raw = sessionStorage.getItem('pacienteAgenda');
    if (!raw) return;
    sessionStorage.removeItem('pacienteAgenda');
    try {
        var p = JSON.parse(raw);
        Swal.fire({
            icon: 'success',
            title: 'Paciente admitido correctamente',
            html:
                '<div style="text-align:center;">' +
                    '<p style="margin:10px 0 5px;color:#666;">Paciente:</p>' +
                    '<p style="font-size:1.3rem;font-weight:bold;margin:0 0 15px;">' +
                        p.apellido + ', ' + p.nombre +
                    '</p>' +
                    '<hr style="margin:15px 0;">' +
                    '<p style="margin:5px 0 0;color:#666;">DNI:</p>' +
                    '<p style="font-size:2rem;font-weight:bold;color:#0d6efd;letter-spacing:3px;margin:5px 0 0;">' +
                        p.dni +
                    '</p>' +
                '</div>',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Copiar DNI',
            confirmButtonColor: '#0d6efd',
        }).then(function (r) {
            if (r.isConfirmed) copiarAlPortapapeles(p.dni);
        });
    } catch (e) {}

    function copiarAlPortapapeles(texto) {
        var hacer = function () {
            Swal.fire({ icon: 'success', title: 'DNI copiado', text: 'El DNI fue copiado al portapapeles.', confirmButtonText: 'Aceptar', confirmButtonColor: '#3085d6' });
        };
        if (navigator.clipboard) {
            navigator.clipboard.writeText(texto).then(hacer).catch(function () { fallback(texto, hacer); });
        } else {
            fallback(texto, hacer);
        }
    }
    function fallback(texto, cb) {
        var ta = document.createElement('textarea');
        ta.value = texto;
        ta.style.cssText = 'position:fixed;opacity:0;';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        cb();
    }
})();
</script>