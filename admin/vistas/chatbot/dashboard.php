<?php
// Vista: Dashboard (OmniBot Hospital)
// Frontend: métricas de ejemplo. El backend luego reemplaza estos arrays por consultas reales.
$metricas = [
    ['titulo' => 'Total Mensajes Enviados',         'valor' => '14.250', 'detalle' => 'Acumulado del período actual',           'alerta' => false],
    ['titulo' => 'Chats Finalizados Correctamente',  'valor' => '1.120',  'detalle' => 'Pacientes que completaron el flujo SI/NO', 'alerta' => false],
    ['titulo' => 'Chats Incompletos / Abandonados',  'valor' => '340',    'detalle' => 'Conversaciones interrumpidas a medias',    'alerta' => true],
    ['titulo' => 'Total Pacientes Únicos Atendidos', 'valor' => '890',    'detalle' => 'Identificados por número de teléfono',     'alerta' => false],
];

$interacciones = [
    ['palabra' => '#ayuda',    'usos' => '3.842', 'tasa' => 91],
    ['palabra' => '#turnos',   'usos' => '2.615', 'tasa' => 87],
    ['palabra' => '#si',       'usos' => '1.890', 'tasa' => 96],
    ['palabra' => '#no',       'usos' => '430',   'tasa' => 62],
    ['palabra' => '#cancelar', 'usos' => '318',   'tasa' => 44],
    ['palabra' => '#urgencia', 'usos' => '155',   'tasa' => 78],
];
?>
<div class="container-fluid py-3">
    <div class="row g-4">
        <!-- Menú lateral interno del módulo -->
        <aside class="col-12 col-md-3 col-lg-2 config-sidebar">
            <div class="card shadow-sm p-3 h-100" style="min-height: 400px;">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item mb-2">
                        <!-- El item activo lleva el color verde agua -->
                        <a href="#" class="nav-link text-white d-flex align-items-center fw-bold" style="background-color: #20c997; border-radius: 6px;">
                            <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-secondary d-flex align-items-center">
                            <i class="fa-solid fa-file-lines me-2"></i> Plantillas
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-secondary d-flex align-items-center">
                            <i class="fa-solid fa-link me-2"></i> Configuración de Conexión
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link text-secondary d-flex align-items-center">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Auditoría
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Contenedor Base Principal Adaptativo -->
        <main class="col-12 col-md-9 col-lg-10">
            <div class="card p-4 shadow-sm h-100" style="min-height: 400px;">
                <!-- Cabecera de la Vista -->
                <div class="mb-4 pb-3 border-bottom">
                    <h4 class="fw-bold text-primary"><i class="fa-solid fa-chart-line me-2"></i> Dashboard</h4>
                    <p class="text-muted mb-0">Métricas y estadísticas del sistema de mensajería hospitalaria</p>
                </div>

                <!-- Tarjetas de métricas -->
                <div class="row g-3 mb-4">
                    <?php foreach ($metricas as $m): ?>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm h-100 border rounded-3 bg-white">
                            <div class="card-body">
                                <p class="text-muted small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;"><?php echo $m['titulo']; ?></p>
                                <h2 class="fw-bold mb-2" style="color: <?php echo $m['alerta'] ? '#dc3545' : '#20c997'; ?>;"><?php echo $m['valor']; ?></h2>
                                <p class="mb-0 text-muted small"><?php echo $m['detalle']; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resumen de interacciones por palabra clave -->
                <div class="card shadow-sm border rounded-3 bg-white">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Resumen de Interacciones por Palabra Clave</h5>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th scope="col">Palabra Clave Trigger</th>
                                        <th scope="col">Cantidad de Usos</th>
                                        <th scope="col">Tasa de Finalización %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($interacciones as $i): ?>
                                    <?php $colorBarra = $i['tasa'] >= 70 ? '#20c997' : '#ffc107'; ?>
                                    <tr>
                                        <td class="fw-semibold" style="color: #20c997;"><?php echo $i['palabra']; ?></td>
                                        <td><?php echo $i['usos']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; max-width: 180px;">
                                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $i['tasa']; ?>%; background-color: <?php echo $colorBarra; ?>;" aria-valuenow="<?php echo $i['tasa']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="small text-muted"><?php echo $i['tasa']; ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
/* Efectos de hover sutiles para los links del menú lateral interno */
.config-sidebar .nav-link {
    transition: background-color 0.2s ease, color 0.2s ease;
    border-radius: 6px;
}
.config-sidebar .nav-link:not([style*="background-color"]):hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: #212529 !important;
}
body.modo-oscuro .config-sidebar .nav-link:not([style*="background-color"]):hover {
    background-color: rgba(255, 255, 255, 0.07);
    color: #ffffff !important;
}
</style>
