<?php 
if( $_SESSION['debug'] == TRUE){?>
    <div class="modal fade bd-example-modal-xl" id="debug" tabindex="-1" aria-labelledby="#debug" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="#debug">Reporte <?php echo date('l jS \of F Y h:i:s A');?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body border">
                    <p class="fs-6">Todas las variables</p>
                    <p class="fs-6"><b>DEBUGGER:</b></p>
                    <div class="fs-6 bg-light border-auto col-12 p-1"><?php echo @$_SESSION['debugger']; unset($_SESSION["debugger"]);?></div>
                    <p class="fs-6"><b>Consulta realizada</b></p>
                    <div class="fs-6 bg-light border-auto col-12 p-1"><?php @print("<pre>".print_r($_SESSION["query"],true)."</pre>");?></div>
                    <p class="fs-6"><b>Resultado consulta</b></p>
                    <div class="fs-6 bg-light border-auto col-12 p-1"><?php //print("<pre>".print_r($_SESSION["resultado"],true)."</pre>");?></div>
                    <p class="fs-6"><b>GET:</b></p>
                    <div class="fs-6 bg-light border-auto col-12 p-1"><?php @print("<pre>".print_r($_GET,true)."</pre>");?></div>
                    <p class="fs-6"><b>POST:</b></p>
                    <div class="fs-6 bg-light border col-12 p-1"><?php @print("<pre>".print_r($_POST,true)."</pre>");?></div>
                    <p class="fs-6"><b>SESSIONS:</b></p>
                    <div class="fs-6 bg-light border-auto col-12 p-1"><?php @print("<pre>".print_r($_SESSION,true)."</pre>"); //var_dump($_SESSION);?></div>
                    <p class="fs-6"><b>Archivos requeridos:</b></p>
                    <div class="fs-6 bg-light border-auto col-12 p-1"><?php $archivos_incluidos = get_included_files(); @print("<pre>".print_r($archivos_incluidos,true)."</pre>"); //var_dump($_SESSION);?></div>

                    <?php
                    /*
                    <p class="fs-6"><b>VARS</b></p>
                    <?php $vars = get_defined_vars();?>
                    <div class="fs-6 bg-light border-auto col-11 ml-2"><?php print("<pre>".print_r($vars,true)."</pre>");?></div>
                    */
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>