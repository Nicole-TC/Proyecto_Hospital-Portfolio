<?php
// Vista: Dashboard del bot (OmniBot Hospital)
// Frontend: métricas de ejemplo.
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
    <div class="card p-4 shadow-sm">
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
</div>
