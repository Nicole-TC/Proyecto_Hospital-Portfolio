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
                                    <p class="text-secondary small mb-1">Pacientes en Circuito</p>
                                    <h3 id="pacientes-circuito" class="fw-bold mb-0">24</h3>
                                </div>
                                <div class="bg-light-blue text-blue rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-people-fill fs-5"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-secondary small">Flujo de pacientes dentro del circuito.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 0.85rem;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="text-secondary small mb-1">Aptos para Operar</p>
                                    <h3 id="aptos-operar" class="fw-bold mb-0">8</h3>
                                </div>
                                <div class="bg-light-success text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-secondary small">Pacientes listos para cirugía.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 0.85rem;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="text-secondary small mb-1">Alertas de Demora</p>
                                    <h3 id="alertas-demora" class="fw-bold mb-0">5</h3>
                                </div>
                                <div class="bg-light-warning text-warning rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-secondary small">Señales de retrasos en la ruta.</p>
                        </div>
                    </div>
                </div>

            </div><!-- /row g-3 mb-4 -->
        </div><!-- /card-body p-4 -->
    </div><!-- /card -->
</div><!-- /col-12 mb-5 px-3 -->
<script src="js/circuitoquirurgico/dashboard.js"></script>
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
</script>