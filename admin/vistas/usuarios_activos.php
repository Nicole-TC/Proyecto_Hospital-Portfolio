<?php
//require_once "includes/validar_permiso.php";
//validar_permiso("admin");

//$q_usuarios = $bd->query("SELECT * FROM admin WHERE activo = 1;");
$q_usuarios = $bd->query("SELECT * FROM admin;");
$usuarios = $q_usuarios->fetchAll(PDO::FETCH_OBJ);
?>
<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            <a href="index.php?modulo=nuevo_usuario" class="btn btn-success float-right">
                <i class="fa-solid fa-user-plus fa-bounce white fs-3"></i>
                <span class="float-right fs-6 text-white ml-1"> Nuevo usuario</span>
            </a>
            Usuarios activos: <?php echo count($usuarios);?>
        </div>
        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-usuarios">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="d-none d-md-table-cell text-center">ID</th>
                            <th scope="col">Usuario</th>
                            <!-- <th scope="col">Clave</th> -->
                            <th scope="col" class="text-center">DNI</th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Nombre</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    foreach ($usuarios as $usuario) {
                    ?>

                        <tr>
                            <td class="text-center">
                                <?php if($usuario->activo == "1"){ ?>
                                    <i class="fa-solid fa-circle-check success"><span class="invisible d-none">1</span></i>
                                    <?php }else{?>
                                    <i class="fa-solid fa-circle-xmark danger"></i><span class="invisible d-none">0</span>
                                <?php } ?>
                            </td>
                            <td class="d-none d-md-table-cell text-center"><?php echo $usuario->id; ?></td>
                            <td><?php echo $usuario->username; ?></td>
                            <!-- <td><?php echo $usuario->password; ?></td> -->
                            <td class="text-center"><?php echo $usuario->dni; ?></td>
                            <td><?php echo $usuario->apellido; ?></td>
                            <td><?php echo $usuario->nombre; ?></td>
                            <td class="text-center">
                                <a class="text-success" href="index.php?modulo=editar_usuario&id=<?php echo $usuario->id; ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a onclick="return confirm('Estas seguro de eliminar?');" class="text-danger" href="app/usuarios/eliminar.php?id=<?php echo $usuario->id; ?>">
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

<!-- DATATABLES no soporte COLSPAN ni COLROW-->
<script>
    $(document).ready(function() {
        $('#tabla-usuarios').DataTable({
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