<?php
// Enrutador interno para el grupo Circuito Quirúrgico
// Todas las URLs de este grupo serán de la forma: index.php?modulo=menu_circuitoquirurgico&accion=lo_que_sea

$modal = FALSE; // Por defecto no mostramos el botón de ayuda modal en el menú superior

switch (@$_GET["accion"]) {
    case 'gestion_usuarios':
        $menu = 'Gestión de Usuarios (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/gestion_usuarios.php"; // Archivo físico a cargar
        break;
    case 'dashboard':
        $menu = 'Dashboard de Gestión';
        $require = "vistas/circuitoquirurgico/dashboard.php";
        break;

    case 'admision':
        $menu = 'Admision (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/admision.php"; // Archivo físico a cargar
        break;

    case 'agenda':
        $menu = 'Agenda  (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/agenda.php"; // Archivo físico a cargar
        break;
    case 'lista_espera':
        $menu = 'Lista de Espera Quirúrgica';
        $require = "vistas/circuitoquirurgico/lista_de_espera_quirurgica.php";
        break;

    case 'auditoria_global':
        $menu = 'Auditoría Global (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/auditoria_global.php"; // Archivo físico a cargar
        break;

    case 'seguimiento_postas':
        $menu = 'Seguimientos Control de Postas (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/seguimiento_postas.php"; // Archivo físico a cargar
        break;

    case 'form_users':
        $menu = 'Formulario de Usuarios (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/form_users.php"; // Archivo físico a cargar
        break;

    case 'gestion_pacientes':
        $menu = 'Gestion de Pacientes (CQ)'; // Título en la barra de navegación superior
        $require = "vistas/circuitoquirurgico/gestion_pacientes.php"; // Archivo físico a cargar
        break;

    case 'form_pacientes':
        $menu = 'Formulario de Pacientes (CQ)';
        $require = "vistas/circuitoquirurgico/form_pacientes.php";
        break;

    case 'especialidades':
        $menu    = 'Especialidades y Subespecialidades (CQ)';
        $require = "vistas/circuitoquirurgico/especialidad.php";
        break;

    case 'gestion_ordenes':
        $menu = 'Gestión de Órdenes Médicas (CQ)';
        $require = "vistas/circuitoquirurgico/gestion_ordenes.php";
        break;

    case 'form_orden':
        $menu = 'Orden Médica (CQ)';
        $require = "vistas/circuitoquirurgico/form_orden.php";
        break;

    case 'gestion_cirugias':
        $menu    = 'Administración de Catálogos (CQ)';
        $require = 'vistas/circuitoquirurgico/crud_cirugiasEst.php';
        break;


    case 'form_cirugias':
        $menu    = 'Gestión de Cirugías y Estudios Prequirúrgicos (CQ)';
        $require = 'vistas/circuitoquirurgico/form_cirugias.php';
        break;

    case 'asistencia':
        $menu = 'Control de Asistencia (CQ)';
        $require = 'vistas/circuitoquirurgico/asistencia.php';
        break;

    // Aquí se pueden agregar más 'cases' libremente sin tocar admin/template/rutas.php
    /*
    case 'programar_cirugia':
        $menu = 'Programar Cirugía';
        $require = "vistas/circuitoquirurgico/programar.php";
        break;
    */

    default:
        // Si no hay acción, o la acción no existe, mostramos el menú principal del grupo
        $menu = 'Menú Circuito Quirúrgico';
        $require = "vistas/circuitoquirurgico/menu_circuitoquirurgico.php";
        break;
}
?>
