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
const DAYS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
let currentWeekOffset = 0;

function getWeekStart(offset) {
    const now = new Date();
    const monday = new Date(now);
    monday.setDate(now.getDate() - now.getDay() + 1 + offset * 7);
    monday.setHours(0, 0, 0, 0);
    return monday;
}

function renderCalendar() {
    const grid = document.getElementById('calGrid');
    const label = document.getElementById('weekLabel');
    const weekStart = getWeekStart(currentWeekOffset);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    const today = new Date(); today.setHours(0, 0, 0, 0);

    label.textContent = weekStart.getDate() + ' ' + MONTHS[weekStart.getMonth()] + ' – ' + weekEnd.getDate() + ' ' + MONTHS[weekEnd.getMonth()] + ' ' + weekEnd.getFullYear();
    grid.innerHTML = '';

    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        const isToday = d.getTime() === today.getTime();
        const isPast = d < today;

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
            '<div class="text-secondary mt-1" style="font-size:10px;">Sin turnos programados</div>' +
            '</div>' +
            '</div>';
        grid.appendChild(col);
    }
}

document.getElementById('btnPrevWeek').addEventListener('click', function () { currentWeekOffset--; renderCalendar(); });
document.getElementById('btnNextWeek').addEventListener('click', function () { currentWeekOffset++; renderCalendar(); });
document.getElementById('btnToday').addEventListener('click', function () { currentWeekOffset = 0; renderCalendar(); });

renderCalendar();