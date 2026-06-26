<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title fw-bold text-danger">
                        <i class="fa-solid fa-trash-can me-2"></i> Papelera de Documentos
                    </h3>
                    <p class="text-muted">Documentos desactivados. Podés restaurarlos o eliminarlos definitivamente.</p>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabla-papelera" class="table table-hover table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Paciente (DNI)</th>
                                    <th>Tipo Documento</th>
                                    <th>Servicio</th>
                                    <th>Comentario</th>
                                    <th>Fecha</th>
                                    <th>Etiquetas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div id="papelera-vacia" class="text-center py-5 d-none">
                        <i class="fa-solid fa-trash-can text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">No hay documentos en la papelera</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rounded-4 { border-radius: 1rem !important; }
.bg-light { background-color: #f8f9fa !important; }
.table-hover tbody tr:hover { background-color: #f8f9fa; }

body.modo-oscuro .bg-light {
    background-color: #2a2a2a !important;
    color: #f1f1f1 !important;
}
body.modo-oscuro .card-header.bg-white {
    background-color: transparent !important;
}
body.modo-oscuro .table-hover tbody tr:hover {
    background-color: #333 !important;
    color: #fff !important;
}
body.modo-oscuro .table-light {
    background-color: #333 !important;
    color: #fff !important;
}
body.modo-oscuro .text-muted {
    color: #adb5bd !important;
}
</style>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function capitalizarTexto(str) {
        if (!str) return str;
        const limpio = String(str).trim();
        if (limpio.length === 0) return limpio;
        return limpio.charAt(0).toUpperCase() + limpio.slice(1).toLowerCase();
    }

    function capitalizarPorPalabra(str) {
        if (!str) return str;
        return String(str).trim().toLowerCase()
            .split(' ')
            .map(p => p.length ? p.charAt(0).toUpperCase() + p.slice(1) : p)
            .join(' ');
    }

    function formatComentario(comentario) {
        if (!comentario) return '-';
        const matchTitulo = comentario.match(/T[ií]tulo:\s*([^\n]*)/i);
        const matchDesc = comentario.match(/Descripci[oó]n:\s*([\s\S]*)/i);
        const titulo = matchTitulo ? matchTitulo[1].trim() : '';
        const descripcion = matchDesc ? matchDesc[1].trim() : '';
        if (!titulo && !descripcion) return escapeHtml(comentario);
        let html = '';
        if (titulo) html += `<strong>${escapeHtml(capitalizarTexto(titulo))}</strong>`;
        if (descripcion) html += `<br><small class="text-muted">${escapeHtml(capitalizarTexto(descripcion))}</small>`;
        return html || '-';
    }

    const mapaColores = new Map();

    // Cargar colores de etiquetas
    (async function() {
        try {
            const res = await fetch("api/digitalizar/get_opciones.php");
            const json = await res.json();
            if (json.status === 'success' && json.data.etiquetas) {
                json.data.etiquetas.forEach(e => {
                    mapaColores.set(e.name.toLowerCase(), e.color_hex || '#6c757d');
                });
            }
        } catch (err) {
            console.error('Error al cargar colores de etiquetas:', err);
        }
    })();

    let tabla = $('#tabla-papelera').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        columns: [
            { data: "id", visible: false },
            {
                data: null,
                render: function(data) {
                    const nombre = data.paciente ? capitalizarPorPalabra(data.paciente) : 'Desconocido';
                    return `<strong>${escapeHtml(nombre)}</strong> <br><small class="text-muted">DNI: ${escapeHtml(data.dni)}</small>`;
                }
            },
            {
                data: "tipo_documento",
                defaultContent: "Sin Tipo",
                render: function(data) {
                    return data ? escapeHtml(capitalizarTexto(data)) : 'Sin Tipo';
                }
            },
            {
                data: "servicio",
                defaultContent: '<span class="text-muted">Sin servicio</span>',
                render: function(data) {
                    return data ? escapeHtml(capitalizarTexto(data)) : '<span class="text-muted">Sin servicio</span>';
                }
            },
            {
                data: "comentario",
                render: function(data) {
                    return formatComentario(data);
                }
            },
            {
                data: "fecha_subido",
                render: function(data) {
                    if (!data) return '-';
                    const [fecha, hora] = data.split(' ');
                    const partes = fecha.split('-');
                    if (partes.length !== 3) return data;
                    const [anio, mes, dia] = partes;
                    let r = `${dia}/${mes}/${anio}`;
                    if (hora) {
                        const [hh, mm] = hora.split(':');
                        r += ` ${hh}:${mm}`;
                    }
                    return r;
                }
            },
            {
                data: "etiquetas",
                render: function(data) {
                    if (!data) return '<span class="badge bg-secondary">Sin etiquetas</span>';
                    return data.split(', ').map(tag => {
                        const color = mapaColores.get(tag.trim().toLowerCase()) || '#6c757d';
                        return `<span class="badge text-white me-1" style="background-color: ${color} !important;">${escapeHtml(capitalizarTexto(tag))}</span>`;
                    }).join('');
                }
            },
            {
                data: null,
                render: function(data) {
                    return `<a href="${data.ruta_archivo}" target="_blank" class="btn btn-sm btn-info text-white me-1"><i class="fa-solid fa-eye"></i> Ver</a>` +
                           `<button type="button" class="btn btn-sm btn-success btn-restaurar me-1" data-id="${data.id}"><i class="fa-solid fa-rotate-left"></i> Restaurar</button>` +
                           `<button type="button" class="btn btn-sm btn-danger btn-eliminar-def" data-id="${data.id}"><i class="fa-solid fa-trash-can"></i> Eliminar</button>`;
                },
                orderable: false
            }
        ]
    });

    function cargarPapelera() {
        fetch('api/digitalizar/papelera_digitalizar.php')
            .then(r => r.json())
            .then(json => {
                if (json.status === 'success') {
                    const docs = json.data.documentos;
                    tabla.clear();
                    if (docs.length === 0) {
                        document.getElementById('papelera-vacia').classList.remove('d-none');
                    } else {
                        document.getElementById('papelera-vacia').classList.add('d-none');
                        tabla.rows.add(docs).draw();
                    }
                }
            })
            .catch(err => console.error('Error al cargar papelera:', err));
    }

    // Restaurar
    $('#tabla-papelera').on('click', '.btn-restaurar', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Restaurar documento?',
            text: 'El documento volverá a estar visible en las búsquedas.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('api/digitalizar/papelera_digitalizar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, action: 'restore' })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restaurado',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        cargarPapelera();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.' });
                });
            }
        });
    });

    // Eliminar definitivo
    $('#tabla-papelera').on('click', '.btn-eliminar-def', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar permanentemente?',
            text: 'Esta acción es irreversible. El archivo físico también se borrará.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: '¿Estás completamente seguro?',
                    text: 'No podrás recuperar este documento ni su archivo.',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Eliminar definitivamente',
                    cancelButtonText: 'Cancelar'
                }).then(result2 => {
                    if (result2.isConfirmed) {
                        fetch('api/digitalizar/papelera_digitalizar.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id, action: 'hard_delete' })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                cargarPapelera();
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.' });
                        });
                    }
                });
            }
        });
    });

    cargarPapelera();
});
</script>
