<!-- Listado de notas  -->
<div class="container-fluid mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm" style="height:100%;">
                
                <div class="card-header">
                    <a href="#" class="btn btn-success float-right" data-bs-toggle="modal" data-bs-target="#registrar_nuevo">
                        <i class="fa-solid fa-square-plus fa-bounce white fs-3"></i>
                        <span class="float-right fs-6 text-white ml-1">Registrar nueva nota</span>
                    </a>

                    <?php
                    // Consulta SQL para obtener los datos
                    $consulta = $bd->prepare("SELECT d.*, p.dni, p.apellido, p.nombre, a.nombre AS nombre_agente_carga, a.apellido AS apellido_agente_carga
                    FROM documentos_notas d
                    LEFT JOIN personal p ON d.dni_personal = p.dni
                    LEFT JOIN admin a ON d.id_usuario_carga = a.id
                    ORDER BY d.fecha_recibido DESC");

                    $consulta->execute();
                    $notas = $consulta->fetchAll(PDO::FETCH_OBJ);
                    ?>
                    Hay <b><?php echo count($notas); ?></b> notas registradas.
                </div>

                <!-- Pantallas grandes -->
                <div class="col-12 d-none d-lg-block p-1">
                    <div class="table-responsive">
                        <table class="table align-middle fs-7" id="tabla-notas-grande">
                            <thead>
                                <tr>
                                    <th  scope="col">Asunto</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Observaciones</th>
                                    <th class="text-center" scope="col">Agente</th>
                                    <th class="text-center" scope="col">Recibido</th>
                                    <th class="text-center" scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas as $nota): ?>
                                    <tr>
                                        <td><?php echo $nota->asunto; ?></td>
                                        <td><?php echo $nota->descripcion; ?></td>
                                        <td><?php echo $nota->observaciones; ?></td>
                                        <td class="text-center">
                                            <?php echo "<i>$nota->apellido, $nota->nombre</i>"; ?>
                                            <?php echo "<br>DNI: $nota->dni"; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $nota->fecha_recibido; ?>
                                            <?php echo "<br><small><i>$nota->nombre_agente_carga $nota->apellido_agente_carga</i></small>"; ?>
                                        </td>
                                        <td class="text-center">
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalVerArchivo" 
                                                data-archivo="<?php echo $nota->ruta; ?>">
                                                Ver documento
                                            </button>
                                            <?php if($_SESSION["admin"]==TRUE):?>
                                                <a class="btn btn-sm btn-danger center text-white ml-1" data-bs-toggle="modal" data-bs-target="#eliminar_documento_<?php echo $nota->id;?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                                <div class="modal fade" id="eliminar_documento_<?php echo $nota->id;?>" tabindex="-1" aria-labelledby="#eliminar_documento_<?php echo $nota->id;?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-md">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="#eliminar_documento_<?php echo $nota->id;?>"><b>Eliminar nota</b></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>

                                                            <form action="app/documentos/eliminar_nota.php" method="post" onsubmit="mostrarModalCargaRespuesta()">

                                                                <div class="modal-body">
                                                                    <div class="form-row">
                                                                        <p>
                                                                            ¿Estás seguro de eliminar la nota de <i>"<?php echo htmlspecialchars($nota->asunto); ?>"</i> de <b><?php echo htmlspecialchars($nota->apellido . ", " . $nota->nombre); ?></b>?
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="id_nota" value="<?php echo $nota->id;?>">
                                                                    <a class="btn btn-danger" data-bs-dismiss="modal" role="button">Cancelar</a>
                                                                    <button type="submit" class="btn btn-primary ml-1">Si, eliminar nota</button>
                                                                </div>

                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pantallas pequeñas -->
                <div class="col-12 d-lg-none p-1">
                    <div class="table-responsive">
                        <table class="table align-middle fs-7" id="tabla-notas-grande">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">Asunto</th>
                                    <th class="text-center" scope="col">Agente</th>
                                    <th class="text-center" scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas as $nota): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            echo "<b>$nota->asunto</b>"; 
                                            echo "<br><i>$nota->descripcion</i>"; 
                                            echo "<br><i>$nota->observaciones</i>"; 
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo "<i>$nota->apellido, $nota->nombre</i>"; ?>
                                            <?php echo "<br>DNI: $nota->dni"; ?>
                                        </td>
                                        <td class="text-center">
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalVerArchivo" 
                                                data-archivo="<?php echo $nota->ruta; ?>">
                                                Ver documento
                                            </button>

                                            <?php if($_SESSION["admin"]==TRUE):?>
                                                <a class="btn btn-sm btn-danger center text-white ml-1" data-bs-toggle="modal" data-bs-target="#eliminar_documento_p_<?php echo $nota->id;?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                                
                                                <div class="modal fade" id="eliminar_documento_p_<?php echo $nota->id;?>" tabindex="-1" aria-labelledby="#eliminar_documento_p_<?php echo $nota->id;?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-md">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="#eliminar_documento_p_<?php echo $nota->id;?>"><b>Eliminar nota</b></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>

                                                            <form action="app/documentos/eliminar_nota.php" method="post" onsubmit="mostrarModalCargaRespuesta()">

                                                                <div class="modal-body">
                                                                    <div class="form-row">
                                                                        <p>
                                                                            ¿Estás seguro de eliminar la nota de <i>"<?php echo htmlspecialchars($nota->asunto); ?>"</i> de <b><?php echo htmlspecialchars($nota->apellido . ", " . $nota->nombre); ?></b>?
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="id_nota" value="<?php echo $nota->id;?>">
                                                                    <a class="btn btn-danger" data-bs-dismiss="modal" role="button">Cancelar</a>
                                                                    <button type="submit" class="btn btn-primary ml-1">Si, eliminar nota</button>
                                                                </div>

                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif;?>

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
</div>
<div class="modal fade" id="modalVerArchivo" tabindex="-1" aria-labelledby="modalVerArchivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerArchivoLabel">Previsualizar Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="archivoPreview">
                    <!-- Aquí se carga el contenido dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Datatables grande-->
<script>
    $(document).ready(function() {
        $('#tabla-notas-grande').DataTable({
            "paging": true,
            "order": [
                [4, 'desc']
            ],
            'language': {
                'lengthMenu': "Mostrar " +
                    `<select class ="custom-SELECT custom-SELECT-sm form-control form-control-sm">
                <option value = '10'>10</option>
                <option value = '25'>25</option>
                <option value = '50'>50</option>
                <option value = '100'>100</option>
                <option value = '-1'>Todos</option>
             </select>` +
                    ' registros por página',
                'zeroRecords': 'Sin resultados',
                'info': 'Se encontraron _TOTAL_ coincidencias',
                //'info': 'Mostrando _START_ a _END_ de _TOTAL_ coincidencias',
                'infoEmpty': 'No hay registros por mostrar',
                'infoFiltered': '[de un total de _MAX_]',
                'search': 'Buscar',
                'paginate': {
                    'next': 'Siguiente',
                    'previous': 'Anterior'
                }
            }

        });
    });
</script>
<!-- Datatables pequeña-->
<script>
    $(document).ready(function() {
        $('#tabla-notas-peque').DataTable({
            "paging": false,
            "order": [
                [2, 'desc']
            ],
            'language': {
                'lengthMenu': "Mostrar " +
                    `<select class ="custom-SELECT custom-SELECT-sm form-control form-control-sm">
                <option value = '10'>10</option>
                <option value = '25'>25</option>
                <option value = '50'>50</option>
                <option value = '100'>100</option>
                <option value = '-1'>Todos</option>
             </select>` +
                    ' registros por página',
                'zeroRecords': 'Sin resultados',
                'info': 'Se encontraron _TOTAL_ coincidencias',
                //'info': 'Mostrando _START_ a _END_ de _TOTAL_ coincidencias',
                'infoEmpty': 'No hay registros por mostrar',
                'infoFiltered': '[de un total de _MAX_]',
                'search': 'Buscar',
                'paginate': {
                    'next': 'Siguiente',
                    'previous': 'Anterior'
                }
            }

        });
    });
</script>

<!-- Leer archivos -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalVerArchivo = document.getElementById('modalVerArchivo');
    const archivoPreview = document.getElementById('archivoPreview');

    modalVerArchivo.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const archivo = button.getAttribute('data-archivo');
        const extension = archivo.split('.').pop().toLowerCase();

        archivoPreview.innerHTML = ''; // Limpiar contenido previo

        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            archivoPreview.innerHTML = `<img src="${archivo}" alt="Archivo" class="img-fluid">`;
        } else if (extension === 'pdf') {
            archivoPreview.innerHTML = `<iframe src="${archivo}" width="100%" height="500px" frameborder="0"></iframe>`;
        } else if (['doc', 'docx'].includes(extension)) {
            archivoPreview.innerHTML = `
                <iframe src="https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(archivo)}" 
                        width="100%" height="500px" frameborder="0"></iframe>`;
        } else {
            archivoPreview.innerHTML = `<p class="text-danger">No se puede previsualizar este tipo de archivo.</p>`;
        }
    });
});
</script>


<!-- Modal registrar nuevo -->
<div class="modal fade" id="registrar_nuevo" tabindex="-1" aria-labelledby="registrar_nuevo" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrar_nuevo">Registrar nueva nota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="registrar_nota" action="app/documentos/registrar_nota.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Agente:</label>
                            <select class="form-control" id="dni_personal" name="dni_personal" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <?php
                                $consulta = $bd->query("SELECT * FROM personal ORDER BY apellido ASC");
                                $agentes = $consulta->fetchAll(PDO::FETCH_OBJ);
                                foreach ($agentes as $agente): ?>
                                    <option value="<?php echo htmlspecialchars($agente->dni); ?>">
                                        <?php echo htmlspecialchars($agente->apellido . ", " .   $agente->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label">Fecha:</label>
                            <input type="date" class="form-control" id="fecha" autocomplete="off" name="fecha" value="<?php echo date("Y-m-d");?>" autofocus required>
                        </div>

                        <div class="form-group col-md-3">
                            <label class="form-label">Hora:</label>
                            <input type="time" class="form-control" id="hora" autocomplete="off" name="hora" value="<?php echo date("H:i:s");?>" autofocus required>
                        </div>
                        <!-- Buscador -->
                        <!-- <div class="form-group col-md-12">
                            <label for="buscador" class="form-label">Buscar internado <i class="fa-solid fa-hospital-user info ml-1 fs-4"></i></label>
                            <input type="text" id="buscador" class="form-control" placeholder="Escribir DNI, apellido o servicio" autocomplete="off" required>
                            <div id="sugerencias" class="form-label" style="display: none; border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow-y: auto;"></div>
                        </div>

                        <div id="resultado-container" class="col-12 mb-1 p-0">
                            <div id="resultado" class="form-label"></div>
                        </div> -->

                        <div class="form-group col-12 mt-1">
                            <label for="asunto" class="form-label">Asunto de la nota:</label>
                            <input type="text" id="asunto" name="asunto" class="form-control" required>
                        </div>
                        <div class="form-group col-12 mt-1">
                            <label for="descripcion" class="form-label">Descripción:</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" style="height: 50"></textarea>
                            <span class="fw-lighter p-0">Descripción del nota.</span>
                        </div>
                        <div class="form-group col-12 mt-1">
                            <label for="observaciones" class="form-label">Observaciones:</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" style="height: 50"></textarea>
                            <span class="fw-lighter p-0">Cualquier otro dato relevante.</span>
                        </div>
                        <div class="col-md-12">
                            <label for="nota" class="form-label">Archivo:</label>
                            <input type="file" name="nota" id="nota" class="form-control" accept=".pdf,.jpg,.jpeg" required>
                            <span class="fw-lighter p-0">Solo archivos PDF, JPEG, JPG y PNG.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary">Registrar nota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AJAX Buscador -->
<!-- <script>
    document.getElementById('buscador').addEventListener('input', function() {
        const termino = this.value;

        if (termino.length < 2) {
            document.getElementById('sugerencias').style.display = 'none';
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'app/documentos/obtener_sugerencias.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const sugerenciasDiv = document.getElementById('sugerencias');
                sugerenciasDiv.innerHTML = xhr.responseText;
                sugerenciasDiv.style.display = 'block';

                // Añadir funcionalidad a las sugerencias
                const opciones = sugerenciasDiv.querySelectorAll('.sugerencia');
                opciones.forEach(opcion => {
                    opcion.addEventListener('click', function() {
                        document.getElementById('buscador').value = this.textContent;
                        sugerenciasDiv.style.display = 'none';

                        // Enviar datos para cargar la información completa
                        cargarDatos(this.dataset.id);
                    });
                });
            }
        };
        xhr.send('termino=' + encodeURIComponent(termino));
    });

    function cargarDatos(id) {
        const contenedor = document.getElementById('resultado-container');
        const resultadoDiv = document.getElementById('resultado');
        const xhr = new XMLHttpRequest();

        xhr.open('POST', 'app/documentos/cargar_datos.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText.trim()) {
                    // Mostrar el contenedor completo con los datos
                    contenedor.style.display = 'block';
                    resultadoDiv.innerHTML = xhr.responseText;
                } else {
                    // Ocultar el contenedor si no hay datos
                    contenedor.style.display = 'none';
                    resultadoDiv.innerHTML = '';
                }
            }
        };
        xhr.send('id=' + encodeURIComponent(id));
    }


    document.getElementById('buscador').addEventListener('input', function() {
        const termino = this.value;
        const contenedor = document.getElementById('resultado-container');

        if (termino.length < 2) {
            document.getElementById('sugerencias').style.display = 'none';
            contenedor.style.display = 'none'; // Oculta el contenedor
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'forms/obtener_sugerencias.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const sugerenciasDiv = document.getElementById('sugerencias');
                sugerenciasDiv.innerHTML = xhr.responseText;
                sugerenciasDiv.style.display = 'block';

                const opciones = sugerenciasDiv.querySelectorAll('.sugerencia');
                opciones.forEach(opcion => {
                    opcion.addEventListener('click', function() {
                        document.getElementById('buscador').value = this.textContent;
                        document.getElementById('sugerencias').style.display = 'none';
                        cargarDatos(this.dataset.id);
                    });
                });
            }
        };
        xhr.send('termino=' + encodeURIComponent(termino));
    });
</script> -->


<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<!-- MODAL AYUDA REGISTRO DE NOTAS -->
<div class="modal fade" id="ayudamenu" tabindex="-1" aria-labelledby="ayudamenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="ayudamenuLabel">Ayuda sobre el registro de notas/documentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="fs-6">
                    Esta sección permite registrar, visualizar y gestionar notas internas o notas presentados por agentes del hospital.
                </p>
                <ul class="fs-6">
                    <li><strong>Registrar nota:</strong> botón verde ubicado arriba a la derecha. Al hacer clic se abre un nota donde se debe seleccionar el agente, indicar la fecha, hora, asunto, descripción y observaciones. También es obligatorio adjuntar un archivo (PDF o imagen).</li>
                    <li><strong>Listado:</strong> se muestran todas las notas registradas, con los datos del agente, fecha de recepción y opciones para ver el archivo adjunto o eliminarlo (solo administradores).</li>
                    <li><strong>Ver documento:</strong> el botón "Ver documento" abre una previsualización del archivo adjunto directamente en pantalla, soportando PDF, JPG, PNG y Word.</li>
                    <li><strong>Eliminar:</strong> los usuarios con permisos de administrador pueden eliminar notas individuales mediante un botón con ícono de papelera.</li>
                </ul>

                <hr>
                <p class="fs-6">
                    Este módulo facilita el seguimiento documental de presentaciones formales realizadas por el personal, garantizando trazabilidad, fecha de recepción y archivo digital.
                </p>
                <p class="fs-6">
                    Todos los registros incluyen la identidad del agente que realizó la carga y se ordenan cronológicamente.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
