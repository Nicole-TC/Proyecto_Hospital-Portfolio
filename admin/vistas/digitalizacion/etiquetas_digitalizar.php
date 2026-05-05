<?php
$stmt = $bd->prepare("SELECT * FROM etiquetas");
$stmt->execute();
$etiquetas = $stmt->fetchAll(PDO::FETCH_OBJ);
?>
<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <a href="index.php?modulo=nueva_etiqueta" class="btn btn-success float-right">
                <i class="fa-solid fa-user-plus fa-bounce white fs-3"></i>
                <span class="float-right fs-6 text-white ml-1"> Nuevo etiqueta</span>
            </a>
            etiquetas activos: <?php echo count($etiquetas);?>
        </div>
        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-etiquetas">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">ID</th>
                            <th scope="col" class="text-center">nombre</th>
                            <th scope="col" class="text-center">color hex</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    foreach ($etiquetas as $etiqueta) {
                    ?>

                        <tr>
                            <td class="text-center">
                                <?php if($etiqueta->estado == "1"){ ?>
                                    <i class="fa-solid fa-circle-check success"><span class="invisible d-none">1</span></i>
                                    <?php }else{?>
                                    <i class="fa-solid fa-circle-xmark danger"></i><span class="invisible d-none">0</span>
                                <?php } ?>
                            </td>
                            <td class="d-none d-md-table-cell text-center"><?php echo $etiqueta->id; ?></td>
                            <td class="d-none d-md-table-cell text-center" ><?php echo $etiqueta->nombre_etiqueta; ?></td>
                            <td class="d-none d-md-table-cell text-center">
    <span style="
        background-color: <?php echo $etiqueta->color_hex; ?>;
        display: inline-block;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 1px solid #ccc;
        vertical-align: middle;
    "></span>
    <small class="ml-1"><?php echo $etiqueta->color_hex; ?></small>
</td>
                            <td class="d-none d-md-table-cell text-center">
                                <a class="text-success" href="index.php?modulo=editar_etiqueta&id=<?php echo $etiqueta->id; ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a onclick="return confirm('Estas seguro de eliminar?');" class="text-danger" href="app/etiquetas/eliminar.php?id=<?php echo $etiqueta->id; ?>">
                                    <i class="bi bi-trash ml-1"></i>
                                </a>
                                <a href="https://api.whatsapp.com/send?text=Te comparto la página de SITT. Entra en https://sitt.ar/admin">
                                    <i class="bi bi-whatsapp ml-1"></i>
                                </a>
                            </td>
                        </tr>

                    <?php
                    }
                    ?>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<script>
    $(document).ready(function() {
        $('#tabla-etiquetas').DataTable({
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
                'search': 'Filtrar',
                'paginate': {
                    'next': 'Siguiente',
                    'previous': 'Anterior'
                }
            }

        });
    });
</script>
