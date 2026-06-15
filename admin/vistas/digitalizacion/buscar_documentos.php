<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Tarjeta de Filtros de Búsqueda -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title fw-bold text-info"><i class="fa-solid fa-magnifying-glass me-2"></i> Buscar Documentos</h3>
                    <p class="text-muted">Utilizá los filtros para encontrar documentos específicos.</p>
                </div>
                <div class="card-body p-4">
                    <form id="form-buscar-documentos">
                        <div class="row g-3">
                            <!-- Filtro por DNI -->
                            <div class="col-md-4">
                                <label for="buscar_dni" class="form-label fw-bold">DNI del Paciente</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-0" id="buscar_dni" name="dni" placeholder="Ej: 12345678">
                                </div>
                            </div>
                            
                            <!-- Filtro por Etiquetas -->
                            <div class="col-md-4">
                                <label for="buscar_tag" class="form-label fw-bold">Etiqueta (Tag)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-tags text-muted"></i></span>
                                    <select class="form-select bg-light border-0" id="buscar_tag" name="tag_id">
                                        <option value="">-- Todas las etiquetas --</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Filtro por Rango de Fechas -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Subido entre (Fechas)</label>
                                <div class="input-group">
                                    <input type="date" class="form-control bg-light border-0" id="fecha_desde" name="fecha_desde" title="Fecha Desde">
                                    <span class="input-group-text bg-light border-0 px-2">y</span>
                                    <input type="date" class="form-control bg-light border-0" id="fecha_hasta" name="fecha_hasta" title="Fecha Hasta">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div id="alerta-sin-filtros" class="alert alert-warning d-none mb-0" role="alert">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    Ingresá al menos un filtro (DNI, etiqueta o fecha) para poder buscar.
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="button" id="btn-limpiar" class="btn btn-light rounded-pill px-4 me-2">Limpiar</button>
                                <button type="submit" class="btn btn-info text-white rounded-pill px-5 shadow-sm fw-bold">
                                    <i class="fa-solid fa-search me-2"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjeta de Resultados -->
            <div class="card shadow-sm border-0 rounded-4 mt-4" id="card-resultados" style="display: none;">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabla-busqueda" class="table table-hover table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Paciente (DNI)</th>
                                    <th>Documento</th>
                                    <th>Comentario</th>
                                    <th>Fecha</th>
                                    <th>Etiquetas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables llenará esto -->
                            </tbody>
                        </table>
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

/* === Modo Oscuro === */
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
body.modo-oscuro .text-muted {
    color: #adb5bd !important;
}
body.modo-oscuro .table-hover tbody tr:hover {
    background-color: #333 !important;
    color: #fff !important;
}
body.modo-oscuro .table-light {
    background-color: #333 !important;
    color: #fff !important;
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

<!-- Librerías DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    
    // 1. Cargar dinámicamente el selector de Etiquetas (Tag)
    try {
        const response = await fetch("api/digitalizar/get_opciones.php");
        const json = await response.json();
        
        if (json.status === 'success' && json.data.etiquetas) {
            const selectTag = document.getElementById("buscar_tag");
            if(selectTag) {
                json.data.etiquetas.forEach(etiqueta => {
                    const option = document.createElement("option");
                    option.value = etiqueta.id;
                    option.textContent = etiqueta.name;
                    selectTag.appendChild(option);
                });
            }
        }
    } catch(error) {
        console.error("Error al cargar etiquetas:", error);
    }

// 2. Inicializar DataTables sin ajax
    let tablaBusqueda = $('#tabla-busqueda').DataTable({
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
                render: function(data, type, row) { 
                    const nombre = row.paciente ? row.paciente : 'Desconocido';
                    return `<strong>${nombre}</strong> <br><small class="text-muted">DNI: ${row.dni}</small>`; 
                } 
            },
            { data: "tipo_documento", defaultContent: "Sin Tipo" },
            { data: "comentario", defaultContent: "-" }, 
            { 
                data: "fecha_subido",
                render: function(data, type, row) {
                    if (type === 'display') {
                        if (!data) return '-';
                        const [fecha, hora] = data.split(' ');
                        const partes = fecha.split('-');
                        if (partes.length !== 3) return data;
                        const [anio, mes, dia] = partes;
                        let resultado = `${dia}/${mes}/${anio}`;
                        if (hora) {
                            const [hh, mm] = hora.split(':');
                            resultado += ` ${hh}:${mm}`;
                        }
                        return resultado;
                    }
                    return data; // usar valor ISO original para ordenar/filtrar correctamente
                }
            },
            { 
                data: "etiquetas",
                render: function(data, type, row) {
                    if (!data) return '<span class="badge bg-secondary">Sin etiquetas</span>';
                    return data.split(', ').map(tag => `<span class="badge bg-info text-white me-1">${tag}</span>`).join('');
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `<a href="${row.ruta_archivo}" target="_blank" class="btn btn-sm btn-info text-white"><i class="fa-solid fa-eye"></i> Ver</a>`;
                }
            }
        ]
    });

    // Función que llama a la API solo cuando el usuario busca
    function ejecutarBusqueda() {
        const params = new URLSearchParams();
        const dni = $('#buscar_dni').val();
        const tag = $('#buscar_tag').val();
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();

        if (dni) params.append('dni', dni);
        if (tag) params.append('tag_id', tag);
        if (desde) params.append('fecha_inicio', desde);
        if (hasta) params.append('fecha_fin', hasta);

        fetch('api/digitalizar/buscar_documentos.php?' + params.toString())
            .then(r => r.json())
            .then(json => {
                tablaBusqueda.clear();
                if (json.status === 'success') {
                    tablaBusqueda.rows.add(json.data.documentos).draw();
                } else {
                    alert('Error: ' + json.message);
                }
            })
            .catch(err => console.error('Error en búsqueda:', err));
    }

    // 3. Programar el Botón Limpiar
    const btnLimpiar = document.getElementById("btn-limpiar");
    if(btnLimpiar) {
        btnLimpiar.addEventListener("click", function() {
            document.getElementById("form-buscar-documentos").reset();
            const cardRes = document.getElementById("card-resultados");
            if(cardRes) cardRes.style.display = "none";
            tablaBusqueda.clear().draw();
        });
    }

    // 4. Programar el Botón Buscar
    $('#form-buscar-documentos').on('submit', function(e) {
        e.preventDefault();

        const dni = $('#buscar_dni').val().trim();
        const tag = $('#buscar_tag').val();
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();

        const alerta = document.getElementById("alerta-sin-filtros");

        if (!dni && !tag && !desde && !hasta) {
            alerta.classList.remove("d-none");
            document.getElementById("card-resultados").style.display = "none";
            return;
        }

        alerta.classList.add("d-none");
        const cardRes = document.getElementById("card-resultados");
        if(cardRes) cardRes.style.display = "block";
        ejecutarBusqueda();
    });
});
</script>
