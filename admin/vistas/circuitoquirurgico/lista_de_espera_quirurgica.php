<?php
$pacientes = [
    [
        'dni' => '41.234.567',
        'paciente' => 'López, Ana',
        'cirugia' => 'Apendicectomía',
        'apto_desde' => '14/04/2026',
        'estado' => 'Apto Quirúrgico',
    ],
    [
        'dni' => '33.789.456',
        'paciente' => 'González, Roberto',
        'cirugia' => 'Hernioplastia',
        'apto_desde' => '13/04/2026',
        'estado' => 'Apto Quirúrgico',
    ],
    [
        'dni' => '37.123.789',
        'paciente' => 'Sánchez, Patricia',
        'cirugia' => 'Colecistectomía',
        'apto_desde' => '12/04/2026',
        'estado' => 'Apto Quirúrgico',
    ],
    [
        'dni' => '25.456.789',
        'paciente' => 'Martínez, Carlos',
        'cirugia' => 'Prostatectomía',
        'apto_desde' => '10/04/2026',
        'estado' => 'Pendiente',
    ],
    [
        'dni' => '28.765.432',
        'paciente' => 'Fernández, Elena',
        'cirugia' => 'Histerectomía',
        'apto_desde' => '09/04/2026',
        'estado' => 'Rechazado',
    ],
];

function renderEstadoBadge($estado)
{
    $estadoNormalizado = trim($estado);
    
    $estadoColorMap = [
        'Apto Quirúrgico' => 'bg-success',
        'Pendiente' => 'bg-warning text-dark',
        'Rechazado' => 'bg-danger',
        'En Evaluación' => 'bg-info',
        'No Apto' => 'bg-secondary',
    ];
    
    $badgeClass = isset($estadoColorMap[$estadoNormalizado]) 
        ? $estadoColorMap[$estadoNormalizado] 
        : 'bg-secondary';
    
    $text = htmlspecialchars($estadoNormalizado, ENT_QUOTES, 'UTF-8');
    return '<span class="badge ' . $badgeClass . ' px-3 py-2 rounded-pill">' . $text . '</span>';
}
?>

<div class="col-12 mb-5 px-3">
    <div class="card shadow-sm border-0" style="border-radius: 1rem;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="fw-bold mb-2">Lista de Espera Quirúrgica</h1>
                    <p class="text-secondary mb-0">Pacientes con apto médico listos para programación</p>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 0.85rem;">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary small text-uppercase fw-semibold">DNI</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Paciente</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Cirugía</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Apto Desde</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Estado</th>
                                    <th class="text-secondary small text-uppercase fw-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pacientes as $paciente) : ?>
                                    <tr>
                                        <td class="align-middle text-secondary"><?php echo htmlspecialchars($paciente['dni'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($paciente['paciente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($paciente['cirugia'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($paciente['apto_desde'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="align-middle"><?php echo renderEstadoBadge($paciente['estado']); ?></td>
                                        <td class="align-middle">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="programarCirugia('<?php echo addslashes($paciente['paciente']); ?>')">
                                                Programar Cirugía
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="listaEsperaEmpty" class="text-center text-secondary py-4 small" style="display: none;">
                        <i class="bi bi-clipboard-x fs-2 d-block mb-2 opacity-25"></i>
                        No hay pacientes en lista de espera.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function programarCirugia(paciente) {
    alert('Preparando programación para ' + paciente + '.');
}
</script>
