<li>
    <a class="dropdown-item" href="index.php?modulo=menu_digitalizar">
        <i class="fa-solid fa-chevron-right ml-1 mr-1"></i> Menú Principal
    </a>
</li>
<?php if (@$_SESSION["dig_nuevo_documento"] == 1 || @$_SESSION["admin"] == 1) { ?>
<li>
    <a class="dropdown-item" href="index.php?modulo=menu_digitalizar&accion=nuevo_documento">
        <i class="fa-solid fa-chevron-right ml-1 mr-1"></i> Nuevo Documento
    </a>
</li>
<?php } ?>
<?php if (@$_SESSION["dig_admin_etiquetas"] == 1 || @$_SESSION["admin"] == 1) { ?>
<li>
    <a class="dropdown-item" href="index.php?modulo=menu_digitalizar&accion=etiquetas_digitalizar">
        <i class="fa-solid fa-chevron-right ml-1 mr-1"></i> Etiquetas
    </a>
</li>
<?php } ?>
<?php if (@$_SESSION["dig_tipos_documentos"] == 1 || @$_SESSION["admin"] == 1) { ?>
<li>
    <a class="dropdown-item" href="index.php?modulo=menu_digitalizar&accion=tipos_documentos">
        <i class="fa-solid fa-chevron-right ml-1 mr-1"></i> Tipos de Documentos
    </a>
</li>
<?php } ?>
<?php if (@$_SESSION["dig_buscar_documentos"] == 1 || @$_SESSION["admin"] == 1) { ?>
<li>
    <a class="dropdown-item" href="index.php?modulo=menu_digitalizar&accion=buscar_documentos">
        <i class="fa-solid fa-chevron-right ml-1 mr-1"></i> Buscar Documento
    </a>
</li>
<?php } ?>
<?php if (@$_SESSION["dig_servicios"] == 1 || @$_SESSION["admin"] == 1) { ?>
<li>
    <a class="dropdown-item" href="index.php?modulo=menu_digitalizar&accion=servicios_digitalizar">
        <i class="fa-solid fa-chevron-right ml-1 mr-1"></i> Administrar Servicios
    </a>
</li>
<?php } ?>