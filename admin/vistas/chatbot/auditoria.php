<?php
// Vista: Auditoría del bot (OmniBot Hospital)
// Frontend: historial de cambios del sistema (datos de ejemplo).

$registros = [
    [
        'fecha'   => '2026-06-26 10:42:17',
        'usuario' => 'admin@hospital.com',
        'modulo'  => 'Plantillas',
        'accion'  => 'Modificó Plantilla de Bienvenida',
        'detalle' => 'TPL-001 · texto actualizado · canal: Ambos',
    ],
    [
        'fecha'   => '2026-06-26 09:15:03',
        'usuario' => 'sys@hospital.com',
        'modulo'  => 'Conexión',
        'accion'  => 'Cambió Token de WhatsApp',
        'detalle' => 'WhatsApp · token renovado · estado: activo',
    ],
    [
        'fecha'   => '2026-06-25 18:30:44',
        'usuario' => 'admin@hospital.com',
        'modulo'  => 'Dashboard',
        'accion'  => 'Exportó reporte de métricas',
        'detalle' => 'Período: junio 2026 · formato: CSV',
    ],
    [
        'fecha'   => '2026-06-25 14:22:10',
        'usuario' => 'operador@hospital.com',
        'modulo'  => 'Plantillas',
        'accion'  => 'Creó plantilla Respuesta Sí/No',
        'detalle' => 'TPL-002 · canal: Telegram · estado: activo',
    ],
    [
        'fecha'   => '2026-06-24 11:05:55',
        'usuario' => 'sys@hospital.com',
        'modulo'  => 'Sistema',
        'accion'  => 'Reinició servicio de mensajería',
        'detalle' => 'Backend · reinicio programado · sin incidencias',
    ],
    [
        'fecha'   => '2026-06-23 16:48:31',
        'usuario' => 'admin@hospital.com',
        'modulo'  => 'Conexión',
        'accion'  => 'Actualizó credenciales de Telegram',
        'detalle' => 'Telegram · chat_id modificado · webhook validado',
    ],
];

$badgeModulo = [
    'Plantillas' => 'background-color: rgba(32, 201, 151, 0.15); color: #0f766e;',
    'Conexión'   => 'background-color: rgba(13, 202, 240, 0.15); color: #087990;',
    'Dashboard'  => 'background-color: rgba(111, 66, 193, 0.12); color: #6f42c1;',
    'Sistema'    => 'background-color: rgba(255, 193, 7, 0.18); color: #997404;',
];
?>
<div class="container-fluid py-3">
    <div class="row g-4">
        <!-- Contenedor Base Principal -->
        <main class="col-12">
            <div class="card p-4 shadow-sm h-100" style="min-height: 400px;">
                <div id="vista-auditoria-contenido">
                    <!-- Cabecera de la Vista -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold text-primary"><i class="fa-solid fa-clipboard-list me-2"></i> Auditoría</h4>
                        <p class="text-muted mb-0">Historial completo de cambios del sistema para control administrativo.</p>
                    </div>

                    <!-- Tabla de auditoría -->
                    <div class="card shadow-sm border rounded-3 bg-white">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-muted small text-uppercase">
                                            <th scope="col">Fecha y Hora</th>
                                            <th scope="col">Usuario / Operador</th>
                                            <th scope="col">Módulo</th>
                                            <th scope="col">Acción Realizada</th>
                                            <th scope="col">Detalle Técnico</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registros as $r): ?>
                                        <?php $badgeStyle = $badgeModulo[$r['modulo']] ?? 'background-color: #f8f9fa; color: #6c757d;'; ?>
                                        <tr>
                                            <td class="font-monospace text-muted small"><?php echo htmlspecialchars($r['fecha']); ?></td>
                                            <td><?php echo htmlspecialchars($r['usuario']); ?></td>
                                            <td>
                                                <span class="badge rounded-pill px-3 py-1 border-0 fw-semibold" style="<?php echo $badgeStyle; ?>">
                                                    <?php echo htmlspecialchars($r['modulo']); ?>
                                                </span>
                                            </td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($r['accion']); ?></td>
                                            <td class="font-monospace text-muted small"><?php echo htmlspecialchars($r['detalle']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
