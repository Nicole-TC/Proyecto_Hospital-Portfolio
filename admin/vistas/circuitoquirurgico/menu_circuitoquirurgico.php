<?php
// Archivo base para el menú del Circuito Quirúrgico
?>
<div class="grey-bg container-fluid mb-5">
    <section id="minimal-statistics">
        <div class="row">
            <div class="col-12 mb-4 mt-2">
                <h4 class="text-primary">Módulo: Circuito Quirúrgico</h4>
                <p class="text-muted">Desarrollo del grupo de Circuito Quirúrgico.</p>
            </div>
            
            <!-- Dashboard -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Dashboard", "Métricas y vista general", "fa-solid fa-table-cells-large", "primary", "index.php?modulo=menu_circuitoquirurgico&accion=dashboard"); ?>

            <!-- Admisión -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Admisión", "Ingreso de pacientes", "fa-solid fa-user-plus", "info", "index.php?modulo=menu_circuitoquirurgico&accion=admision"); ?>

            <!-- Órdenes médicas -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Órdenes médicas", "Listado y gestión", "fa-solid fa-file-medical", "info", "index.php?modulo=menu_circuitoquirurgico&accion=gestion_ordenes"); ?>

            <!-- Seguimiento de Postas -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Seguimiento", "Control de postas", "fa-solid fa-list-check", "warning", "index.php?modulo=menu_circuitoquirurgico&accion=seguimiento_postas"); ?>

            <!-- Lista de Espera -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Lista de Espera", "Pacientes aguardando", "fa-solid fa-clipboard-list", "secondary", "index.php?modulo=menu_circuitoquirurgico&accion=lista_espera"); ?>

            <!-- Agenda Quirúrgica -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Agenda", "Programación de turnos", "fa-solid fa-calendar-days", "danger", "index.php?modulo=menu_circuitoquirurgico&accion=agenda"); ?>

            <!-- Usuarios -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Usuarios", "Gestión y roles", "fa-solid fa-users", "success", "index.php?modulo=menu_circuitoquirurgico&accion=gestion_usuarios"); ?>

            <!-- Auditoría Global -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Auditoría Global", "Registros de sistema", "fa-solid fa-shield-halved", "dark", "index.php?modulo=menu_circuitoquirurgico&accion=auditoria_global"); ?>

            <!-- Gestion Pacientes -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Pacientes", "Gestion y edicion", "fa-solid fa-users", "blue", "index.php?modulo=menu_circuitoquirurgico&accion=gestion_pacientes"); ?>

            <!-- Especialidades y Subespecialidades -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Especialidades", "Especialidades y subespecialidades", "fa-solid fa-stethoscope", "purple", "index.php?modulo=menu_circuitoquirurgico&accion=especialidades"); ?>
        </div>
    </section>
</div>
