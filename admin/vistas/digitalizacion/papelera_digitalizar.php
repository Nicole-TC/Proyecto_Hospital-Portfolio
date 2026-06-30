<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Tarjeta de Filtros -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title fw-bold text-danger">
                        <i class="fa-solid fa-trash-can me-2"></i> Papelera de Documentos
                    </h3>
                    <p class="text-muted">Documentos desactivados. Podés restaurarlos o eliminarlos definitivamente.</p>
                </div>
                <div class="card-body p-4">
                    <form id="form-filtros-papelera">
                        <div class="row g-3">
                            <!-- Filtro por DNI -->
                            <div class="col-md-4">
                                <label for="filtro_dni" class="form-label fw-bold">DNI del Paciente</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-0" id="filtro_dni" name="dni" placeholder="Ej: 12345678">
                                </div>
                            </div>

                            <!-- Filtro por Etiquetas -->
                            <div class="col-md-4">
                                <label for="filtro_tag" class="form-label fw-bold">Etiqueta (Tag)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-tags text-muted"></i></span>
                                    <input type="hidden" id="filtro_tag" name="tag_id" value="">
                                    <div class="dropdown flex-grow-1">
                                        <button class="btn btn-light border-0 bg-light dropdown-toggle w-100 text-start"
                                                type="button" id="filtro_tag_dropdown" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                            <span id="filtro_tag_texto">-- Todas las etiquetas --</span>
                                        </button>
                                        <ul class="dropdown-menu w-100" id="filtro_tag_menu">
                                            <li>
                                                <a class="dropdown-item active" href="#" data-value="">
                                                    <span class="d-inline-block rounded-circle me-2" style="width:12px;height:12px;background-color:#6c757d;"></span>
                                                    Todas las etiquetas
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtro por Servicio -->
                            <div class="col-md-4">
                                <label for="filtro_servicio" class="form-label fw-bold">Servicio</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-hospital text-muted"></i></span>
                                    <select class="form-select bg-light border-0" id="filtro_servicio" name="id_servicio">
                                        <option value="">-- Todos los servicios --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <!-- Filtro por Rango de Fechas -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Subido entre (Fechas)</label>
                                <div class="input-group">
                                    <input type="date" class="form-control bg-light border-0" id="fecha_desde" name="fecha_desde" title="Fecha Desde" max="">
                                    <span class="input-group-text bg-light border-0 px-2">y</span>
                                    <input type="date" class="form-control bg-light border-0" id="fecha_hasta" name="fecha_hasta" title="Fecha Hasta" max="">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div id="alerta-sin-filtros" class="alert alert-warning d-none mb-0" role="alert">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    <span id="alerta-sin-filtros-texto">Ingresá al menos un filtro (DNI, etiqueta, servicio o fecha) para poder buscar.</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="button" id="btn-limpiar-papelera" class="btn btn-light rounded-pill px-4 me-2">Limpiar</button>
                                <button type="submit" class="btn btn-danger text-white rounded-pill px-5 shadow-sm fw-bold">
                                    <i class="fa-solid fa-search me-2"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjeta de Resultados -->
            <div id="card-resultados-papelera" class="card shadow-sm border-0 rounded-4 d-none">
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
body.modo-oscuro .form-control,
body.modo-oscuro .form-select {
    background-color: #2a2a2a !important;
    color: #fff !important;
}
body.modo-oscuro .form-control.border-0,
body.modo-oscuro .form-select.border-0,
body.modo-oscuro .input-group-text.border-0 {
    border: 1px solid #444 !important;
}
body.modo-oscuro .form-control:focus,
body.modo-oscuro .form-select:focus {
    background-color: #333 !important;
    color: #fff !important;
    border-color: #80bdff !important;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}
body.modo-oscuro .input-group-text {
    background-color: #333 !important;
    color: #fff !important;
    border-color: #444 !important;
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
body.modo-oscuro ::placeholder {
    color: #888 !important;
}
body.modo-oscuro .btn-light {
    background-color: #3a3a3a !important;
    color: #fff !important;
    border-color: #444 !important;
}
body.modo-oscuro .btn-light:hover {
    background-color: #4a4a4a !important;
    color: #fff !important;
}
</style>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async function() {
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

    function fillSelect(id, data, textKey = 'nombre') {
        const el = document.getElementById(id);
        if (!el || !data) return;
        data.forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.id;
            opt.textContent = item[textKey];
            el.appendChild(opt);
        });
    }

    const mapaColores = new Map();

    // Cargar etiquetas y servicios para filtros
    try {
        const res = await fetch("api/digitalizar/get_opciones.php");
        const json = await res.json();
        if (json.status === 'success') {
            if (json.data.etiquetas) {
                json.data.etiquetas.forEach(e => {
                    mapaColores.set(e.name.toLowerCase(), e.color_hex || '#6c757d');
                    const menuTag = document.getElementById("filtro_tag_menu");
                    if (menuTag) {
                        const li = document.createElement("li");
                        const color = e.color_hex || '#6c757d';
                        li.innerHTML = `<a class="dropdown-item" href="#" data-value="${e.id}">
                            <span class="d-inline-block rounded-circle me-2" style="width:12px;height:12px;background-color:${color};"></span>
                            ${escapeHtml(e.name)}
                        </a>`;
                        menuTag.appendChild(li);
                    }
                });
            }
            if (json.data.servicios) {
                fillSelect("filtro_servicio", json.data.servicios);
            }
        }
    } catch (err) {
        console.error('Error al cargar opciones:', err);
    }

    // Establecer fecha máxima actual en inputs de fecha
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_desde').max = hoy;
    document.getElementById('fecha_hasta').max = hoy;

    // Dropdown de etiquetas
    const filtroTagMenu = document.getElementById("filtro_tag_menu");
    const filtroTagInput = document.getElementById("filtro_tag");
    const filtroTagTexto = document.getElementById("filtro_tag_texto");

    function resetDropdownTag() {
        if (filtroTagInput) filtroTagInput.value = '';
        if (filtroTagTexto) filtroTagTexto.innerHTML = '-- Todas las etiquetas --';
        if (filtroTagMenu) {
            filtroTagMenu.querySelectorAll('.dropdown-item').forEach((item, i) => {
                item.classList.toggle('active', i === 0);
            });
        }
    }

    if (filtroTagMenu) {
        filtroTagMenu.addEventListener("click", function(e) {
            const item = e.target.closest('.dropdown-item');
            if (!item) return;
            e.preventDefault();
            filtroTagInput.value = item.dataset.value;
            filtroTagTexto.innerHTML = item.innerHTML;
            filtroTagMenu.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
        });
    }

    let tabla = $('#tabla-papelera').DataTable({
        paging: true,
        lengthChange: true,
        searching: false,
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

    const cardResultados = document.getElementById('card-resultados-papelera');

    function cargarPapelera(params = {}) {
        const dni = params.dni || '';
        const tag = params.tag || '';
        const servicio = params.servicio || '';
        const desde = params.desde || '';
        const hasta = params.hasta || '';

        if (!dni && !tag && !servicio && !desde && !hasta) {
            cardResultados.classList.add('d-none');
            tabla.clear().draw();
            document.getElementById('papelera-vacia').classList.remove('d-none');
            return;
        }

        const urlParams = new URLSearchParams();
        if (dni) urlParams.append('dni', dni);
        if (tag) urlParams.append('tag_id', tag);
        if (servicio) urlParams.append('id_servicio', servicio);
        if (desde) urlParams.append('fecha_inicio', desde);
        if (hasta) urlParams.append('fecha_fin', hasta);

        fetch('api/digitalizar/papelera_digitalizar.php?' + urlParams.toString())
            .then(r => r.json())
            .then(json => {
                const alerta = document.getElementById("alerta-sin-filtros");
                const alertaTexto = document.getElementById("alerta-sin-filtros-texto");
                alerta.classList.add("d-none");
                if (json.status === 'success') {
                    const docs = json.data.documentos;
                    tabla.clear();
                    if (docs.length === 0) {
                        cardResultados.classList.remove('d-none');
                        document.getElementById('papelera-vacia').classList.remove('d-none');
                    } else {
                        cardResultados.classList.remove('d-none');
                        document.getElementById('papelera-vacia').classList.add('d-none');
                        tabla.rows.add(docs).draw();
                    }
                } else {
                    alertaTexto.textContent = json.message || 'Error al buscar documentos.';
                    alerta.classList.remove("d-none");
                }
            })
            .catch(err => console.error('Error al cargar papelera:', err));
    }

    // Formulario de filtros
    document.getElementById('form-filtros-papelera').addEventListener('submit', function(e) {
        e.preventDefault();
        const dni = document.getElementById('filtro_dni').value.trim();
        const tag = filtroTagInput.value;
        const servicio = document.getElementById('filtro_servicio').value;
        const desde = document.getElementById('fecha_desde').value;
        const hasta = document.getElementById('fecha_hasta').value;

        const alerta = document.getElementById("alerta-sin-filtros");
        const alertaTexto = document.getElementById("alerta-sin-filtros-texto");

        if (!dni && !tag && !servicio && !desde && !hasta) {
            alertaTexto.textContent = "Ingresá al menos un filtro (DNI, etiqueta, servicio o fecha) para poder buscar.";
            alerta.classList.remove("d-none");
            return;
        }

        const hoy = new Date().toISOString().split('T')[0];
        if (desde && desde > hoy) {
            alertaTexto.textContent = "La fecha 'desde' no puede ser futura.";
            alerta.classList.remove("d-none");
            return;
        }
        if (hasta && hasta > hoy) {
            alertaTexto.textContent = "La fecha 'hasta' no puede ser futura.";
            alerta.classList.remove("d-none");
            return;
        }
        if (desde && hasta && desde > hasta) {
            alertaTexto.textContent = "La fecha 'desde' no puede ser posterior a la fecha 'hasta'. Revisá el rango de fechas.";
            alerta.classList.remove("d-none");
            return;
        }

        alerta.classList.add("d-none");
        cargarPapelera({ dni, tag, servicio, desde, hasta });
    });

    // Botón Limpiar
    document.getElementById('btn-limpiar-papelera').addEventListener('click', function() {
        document.getElementById('form-filtros-papelera').reset();
        resetDropdownTag();
        document.getElementById("alerta-sin-filtros").classList.add("d-none");
        cardResultados.classList.add('d-none');
        tabla.clear().draw();
    });

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
