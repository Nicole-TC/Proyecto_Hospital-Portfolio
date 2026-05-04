<div class="col-12 mb-5 px-3">
  <div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
 
      <!-- ── Encabezado ── -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="fw-bold mb-2">Agenda Quirúrgica</h1>
          <p class="text-secondary mb-0">Programación semanal y gestión de notificaciones</p>
        </div>
        <div class="text-end">
          <div class="fw-bold fs-4 font-monospace" id="clockTime">--:--:--</div>
          <small class="text-secondary" id="clockDate">--</small>
        </div>
      </div>
 
      <!-- ── Calendario Semanal ── -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
        <div class="card-body p-4">
 
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-3">
            <div>
              <h5 class="fw-bold mb-1">Calendario Semanal</h5>
              <p class="text-secondary small mb-0">Navegación por semana de cirugías programadas.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-sm btn-outline-secondary" id="btnPrevWeek" title="Semana anterior">
                <i class="bi bi-chevron-left"></i>
              </button>
              <span class="small fw-semibold text-secondary text-center" style="min-width: 160px;" id="weekLabel"></span>
              <button class="btn btn-sm btn-outline-secondary" id="btnNextWeek" title="Semana siguiente">
                <i class="bi bi-chevron-right"></i>
              </button>
              <button class="btn btn-sm btn-outline-primary" id="btnToday">Hoy</button>
            </div>
          </div>
 
          <div class="row g-2" id="calGrid"></div>
 
        </div>
      </div>
 
      <!-- ── Programación Detallada ── -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.85rem;">
        <div class="card-body p-4">
 
          <div class="mb-3">
            <h5 class="fw-bold mb-1">Programación Detallada</h5>
            <p class="text-secondary small mb-0">Listado de cirugías por fecha y quirófano.</p>
          </div>
 
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="text-secondary small text-uppercase fw-semibold">Paciente</th>
                  <th class="text-secondary small text-uppercase fw-semibold">DNI</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Cirugía</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Fecha</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Hora</th>
                  <th class="text-secondary small text-uppercase fw-semibold">Quirófano</th>
                </tr>
              </thead>
              <tbody id="tbodyCirugias"></tbody>
            </table>
          </div>
 
          <div id="emptyCirugias" class="text-center text-secondary py-4 small">
            <i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-25"></i>
            Sin cirugías programadas
          </div>
 
        </div>
      </div>
    </div>
  </div>
</div>
 
<script>
/* ── Reloj en vivo ── */
function updateClock() {
  const now = new Date();
  document.getElementById('clockTime').textContent = now.toLocaleTimeString('es-AR');
  document.getElementById('clockDate').textContent = now.toLocaleDateString('es-AR', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  });
}
updateClock();
setInterval(updateClock, 1000);
 
/* ── Calendario semanal ── */
const DAYS   = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
const MONTHS = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
let currentWeekOffset = 0;
 
function getWeekStart(offset) {
  const now = new Date();
  const monday = new Date(now);
  monday.setDate(now.getDate() - now.getDay() + 1 + offset * 7);
  monday.setHours(0, 0, 0, 0);
  return monday;
}
 
function renderCalendar() {
  const grid      = document.getElementById('calGrid');
  const label     = document.getElementById('weekLabel');
  const weekStart = getWeekStart(currentWeekOffset);
  const weekEnd   = new Date(weekStart);
  weekEnd.setDate(weekStart.getDate() + 6);
  const today = new Date(); today.setHours(0, 0, 0, 0);
 
  label.textContent = weekStart.getDate() + ' ' + MONTHS[weekStart.getMonth()] + ' – ' + weekEnd.getDate() + ' ' + MONTHS[weekEnd.getMonth()] + ' ' + weekEnd.getFullYear();
  grid.innerHTML = '';
 
  for (let i = 0; i < 7; i++) {
    const d = new Date(weekStart);
    d.setDate(weekStart.getDate() + i);
    const isToday = d.getTime() === today.getTime();
    const isPast  = d < today;
 
    const col = document.createElement('div');
    col.className = 'col';
    col.innerHTML =
      '<div class="card border ' + (isToday ? 'border-primary' : '') + ' h-100" style="border-radius:0.65rem;min-height:100px;opacity:' + (isPast ? '.55' : '1') + ';">' +
        '<div class="card-body p-2 text-center">' +
          '<div class="text-secondary" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">' + DAYS[d.getDay()] + '</div>' +
          (isToday
            ? '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto my-1 fw-bold" style="width:32px;height:32px;font-size:15px;">' + d.getDate() + '</div>'
            : '<div class="fw-bold fs-5 my-1">' + d.getDate() + '</div>'
          ) +
          '<div class="text-secondary mt-1" style="font-size:10px;">Sin cirugías</div>' +
        '</div>' +
      '</div>';
    grid.appendChild(col);
  }
}
 
document.getElementById('btnPrevWeek').addEventListener('click', function() { currentWeekOffset--; renderCalendar(); });
document.getElementById('btnNextWeek').addEventListener('click', function() { currentWeekOffset++; renderCalendar(); });
document.getElementById('btnToday').addEventListener('click',   function() { currentWeekOffset = 0; renderCalendar(); });
 
renderCalendar();
</script>
 