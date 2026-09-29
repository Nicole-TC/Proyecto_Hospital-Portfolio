<div class="col-12 mb-5 px-3">
    <div class="card shadow-sm border-0" style="border-radius: 1rem;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <span class="badge bg-light-blue blue mb-2">Circuito Quirúrgico</span>
                    <h1 class="fw-bold mb-2">Dashboard de Gestión</h1>
                    <p class="text-secondary mb-0">Monitoreo en tiempo real del Circuito Quirúrgico</p>
                </div>
                <div class="text-end">
                    <span id="clockTime" class="fw-bold d-block fs-3"></span>
                    <small id="clockDate" class="text-secondary fs-6"></small>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 0.85rem;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="text-secondary small mb-1">Total Pacientes</p>
                                    <h3 id="pacientes-circuito" class="fw-bold mb-0">—</h3>
                                </div>
                                <div class="bg-light-blue text-blue rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-people-fill fs-5"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-secondary small">Personas registradas en el sistema con rol paciente.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 0.85rem;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="text-secondary small mb-1">Aptos para Operar</p>
                                    <h3 id="aptos-operar" class="fw-bold mb-0">—</h3>
                                </div>
                                <div class="bg-light-success text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-secondary small">Pacientes con Apto Físico OK esperando programación de cirugía.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 0.85rem;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="text-secondary small mb-1">Alertas de Demora</p>
                                    <h3 id="alertas-demora" class="fw-bold mb-0">—</h3>
                                </div>
                                <div class="bg-light-warning text-warning rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-secondary small">Cirugías programadas cuya fecha ya venció sin iniciarse.</p>
                        </div>
                    </div>
                </div>

            </div><!-- /row g-3 mb-4 -->

            <!-- Funcionalidad del Circuito Quirúrgico -->
            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                <div class="func-header-icon"><i class="bi bi-diagram-3-fill"></i></div>
                <div>
                    <h5 class="fw-bold mb-0">Funcionalidad del Circuito Quirúrgico</h5>
                    <p class="text-secondary small mb-0">Cada etapa del proceso prequirúrgico y sus herramientas de gestión.</p>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-blue"><i class="bi bi-person-plus-fill"></i></div>
                            <h6 class="fw-bold mb-0">Admisión de Pacientes</h6>
                        </div>
                        <p class="small mb-0">Ingreso del paciente al circuito con búsqueda automática por DNI. Registra al paciente y genera las órdenes de todas las postas prequirúrgicas.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-green"><i class="bi bi-clipboard2-pulse"></i></div>
                            <h6 class="fw-bold mb-0">Seguimiento de Postas</h6>
                        </div>
                        <p class="small mb-0">Control del estado de cada posta (Admisión, Laboratorio, Cardiología, Imágenes y Pre-Anestesia), con expediente clínico y línea de tiempo por paciente.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-orange"><i class="bi bi-hourglass-split"></i></div>
                            <h6 class="fw-bold mb-0">Lista de Espera</h6>
                        </div>
                        <p class="small mb-0">Pacientes con Apto Físico OK listos para operar. Permite filtrar por fecha y estado, y programar la cirugía directamente desde la tabla.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-cyan"><i class="bi bi-calendar-week"></i></div>
                            <h6 class="fw-bold mb-0">Agenda de Turnos</h6>
                        </div>
                        <p class="small mb-0">Calendario semanal con panel de turnos por día, buscador por paciente y creación de turnos con verificación de DNI en tiempo real.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-gray"><i class="bi bi-people-fill"></i></div>
                            <h6 class="fw-bold mb-0">Gestión de Usuarios</h6>
                        </div>
                        <p class="small mb-0">CRUD completo con estadísticas por rol, tabla paginada con buscador y datos profesionales para cirujanos (especialidad y matrícula).</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-indigo"><i class="bi bi-person-check-fill"></i></div>
                            <h6 class="fw-bold mb-0">Gestión de Pacientes</h6>
                        </div>
                        <p class="small mb-0">CRUD con estadísticas, búsqueda por DNI, nombre o teléfono, edición con DNI bloqueado y baja lógica de pacientes.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-violet"><i class="bi bi-heart-pulse-fill"></i></div>
                            <h6 class="fw-bold mb-0">Especialidades</h6>
                        </div>
                        <p class="small mb-0">Catálogo de especialidades y subespecialidades con relación padre-hijo, CRUD completo, búsqueda y eliminación con confirmación.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-red"><i class="bi bi-prescription2"></i></div>
                            <h6 class="fw-bold mb-0">Órdenes Médicas</h6>
                        </div>
                        <p class="small mb-0">Visualización, creación y seguimiento de las órdenes generadas durante la admisión y las postas prequirúrgicas.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-teal"><i class="bi bi-clipboard-check-fill"></i></div>
                            <h6 class="fw-bold mb-0">Asistencia</h6>
                        </div>
                        <p class="small mb-0">Control de asistencia a los turnos programados. Búsqueda por apellido, nombre o DNI y registro de presentes y ausentes.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="func-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="func-icon fi-pink"><i class="bi bi-scissors"></i></div>
                            <h6 class="fw-bold mb-0">Cirugías y Estudios</h6>
                        </div>
                        <p class="small mb-0">Administración de catálogos: cirugías, estudios prequirúrgicos y combos cirugía-estudio utilizados en la admisión.</p>
                    </div>
                </div>
            </div>

        </div><!-- /card-body p-4 -->
    </div><!-- /card -->
</div><!-- /col-12 mb-5 px-3 -->

<style>
/* Dark Mode — Dashboard */
body.modo-oscuro .bg-light-blue {
    background-color: rgba(33, 150, 243, 0.25) !important;
}
body.modo-oscuro .bg-light-success {
    background-color: rgba(22, 211, 154, 0.25) !important;
}
body.modo-oscuro .bg-light-warning {
    background-color: rgba(255, 168, 125, 0.25) !important;
}
body.modo-oscuro .text-secondary {
    color: #adb5bd !important;
}

/* Sección Funcionalidad */
.func-header-icon {
    width: 46px;
    height: 46px;
    border-radius: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    color: #0f8b8f;
    background-color: rgba(15, 139, 143, 0.12);
    flex-shrink: 0;
}
.func-card {
    background-color: #f8f9fa;
    border: 1px solid rgba(0, 0, 0, 0.04);
    border-radius: 0.85rem;
    padding: 1rem;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.func-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.08);
}
.func-card p {
    color: #6c757d;
}
.func-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.fi-blue   { background-color: rgba(33, 150, 243, 0.14);  color: #1976d2; }
.fi-green  { background-color: rgba(22, 163, 120, 0.15);  color: #149d74; }
.fi-orange { background-color: rgba(255, 152, 0, 0.16);   color: #ef6c00; }
.fi-cyan   { background-color: rgba(3, 169, 244, 0.14);   color: #0288d1; }
.fi-gray   { background-color: rgba(108, 117, 125, 0.15); color: #5a6268; }
.fi-indigo { background-color: rgba(63, 81, 181, 0.13);   color: #3f51b5; }
.fi-violet { background-color: rgba(142, 36, 170, 0.13);  color: #8e24aa; }
.fi-red    { background-color: rgba(239, 83, 80, 0.14);   color: #d32f2f; }
.fi-teal   { background-color: rgba(15, 139, 143, 0.13);  color: #0f8b8f; }
.fi-pink   { background-color: rgba(216, 27, 96, 0.12);   color: #d81b60; }

/* Dark Mode — Funcionalidad */
body.modo-oscuro .func-header-icon {
    color: #4dc3cf;
    background-color: rgba(54, 169, 192, 0.2);
}
body.modo-oscuro .func-card {
    background-color: #2b2b2b;
    border-color: rgba(255, 255, 255, 0.06);
}
body.modo-oscuro .func-card:hover {
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.45);
}
body.modo-oscuro .func-card p {
    color: #adb5bd;
}
body.modo-oscuro .fi-gray {
    background-color: rgba(173, 181, 189, 0.15);
    color: #b8bfc4;
}
</style>

<script src="js/circuitoquirurgico/dashboard.js"></script>
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