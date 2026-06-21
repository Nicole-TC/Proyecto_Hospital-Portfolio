<?php
$envPath = __DIR__ . '/../../../../.env';
if (empty(getenv('DB_HOST')) && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) putenv(trim($parts[0]) . '=' . trim($parts[1]));
    }
}

require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasAgenda {

    // ── Obtener turnos por fecha ────────────────────────────────────────
    public static function obtenerTurnosPorFecha($fecha, $id_tipo_turno = null) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "
            SELECT
                t.id_turno,
                p.nombre,
                p.apellido,
                p.dni,
                t.fecha_hora,
                tt.descripcion  AS tipo_turno,
                e.descripcion   AS estado,
                e.codigo        AS estado_codigo,
                pr.nombre       AS medico_nombre,
                pr.apellido     AS medico_apellido
            FROM turno t
            JOIN persona_asiste pa  ON pa.id_persona_asiste = t.id_persona_asiste
            JOIN persona p          ON p.id_persona         = pa.id_persona
            JOIN tipo_turno tt      ON tt.id_tipo_turno     = t.id_tipo_turno
            JOIN estado e           ON e.id_estado          = t.id_estado
            LEFT JOIN orden_medica om ON om.id_orden        = t.id_orden
            LEFT JOIN profesional prf ON prf.id_profesional = om.id_profesional
            LEFT JOIN persona pr      ON pr.id_persona      = prf.id_persona
            WHERE DATE(t.fecha_hora) = :fecha " . ($id_tipo_turno ? "AND t.id_tipo_turno = :id_tipo_turno" : "") . "
            ORDER BY t.fecha_hora ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        if ($id_tipo_turno) {
            $stmt->bindParam(':id_tipo_turno', $id_tipo_turno, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Obtener configuración de cupo ──────────────────────────────────
    public static function obtenerConfiguracionCupo($fecha, $id_tipo_turno) {
        $pdo = conexionCircuitoQuirurgico();

        $diaSemanaNum = date('N', strtotime($fecha));
        $mapaDias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes'];
        $columnaDia = $mapaDias[$diaSemanaNum] ?? null;

        $id_estado_ina = id_estado('DATO', 'ELIM');
        if (!$id_estado_ina) $id_estado_ina = 0;

        // Toma la config más reciente cuya vigencia incluye la fecha pedida
        $sql = "SELECT cupos_total, lunes, martes, miercoles, jueves, viernes
                FROM agenda_cupos
                WHERE id_tipo_turno = :id_tipo_turno
                  AND id_estado != :id_estado_ina
                  AND fecha_desde <= :fecha
                  AND (fecha_hasta IS NULL OR fecha_hasta >= :fecha2)
                ORDER BY creado_en DESC LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_tipo_turno', $id_tipo_turno, PDO::PARAM_INT);
        $stmt->bindParam(':id_estado_ina', $id_estado_ina, PDO::PARAM_INT);
        $stmt->bindParam(':fecha',  $fecha);
        $stmt->bindParam(':fecha2', $fecha);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        // Sin config: si no hay cupo configurado, el día es inactivo
        if (!$config) return ['activo' => false, 'cupos_total' => 0, 'sin_config' => true];

        // Fin de semana (sáb/dom): nunca activo en config semanal L-V
        if (!$columnaDia) {
            return ['activo' => false, 'cupos_total' => 0, 'sin_config' => false];
        }

        $val    = $config[$columnaDia] ?? null;
        $activo = ($val === true || $val === 't' || $val === '1' || $val === 1);

        return ['activo' => $activo, 'cupos_total' => (int)$config['cupos_total'], 'sin_config' => false];
    }

    // ── Obtener config activa: días habilitados y cupo del período ─────
    // $fecha_ref: último día del período (domingo de la semana o último del mes)
    // $fecha_inicio: primer día del período (lunes de la semana o 1º del mes)
    public static function obtenerConfigActiva($id_tipo_turno, $fecha_ref = null, $fecha_inicio = null) {
        $pdo = conexionCircuitoQuirurgico();
        if (!$fecha_ref)    $fecha_ref    = date('Y-m-d');
        if (!$fecha_inicio) $fecha_inicio = $fecha_ref;

        $id_estado_ina = id_estado('DATO', 'ELIM');
        if (!$id_estado_ina) $id_estado_ina = 0;

        // Busca la config más reciente cuya vigencia se superpone con el período visible
        $sql = "SELECT cupos_total, hora_inicio, lunes, martes, miercoles, jueves, viernes
                FROM agenda_cupos
                WHERE id_tipo_turno = :id_tipo_turno
                  AND id_estado != :id_estado_ina
                  AND fecha_desde <= :fecha_ref
                  AND (fecha_hasta IS NULL OR fecha_hasta >= :fecha_inicio)
                ORDER BY creado_en DESC LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_tipo_turno', $id_tipo_turno, PDO::PARAM_INT);
        $stmt->bindParam(':id_estado_ina', $id_estado_ina, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_ref',     $fecha_ref);
        $stmt->bindParam(':fecha_inicio',  $fecha_inicio);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            return ['ok' => true, 'sin_config' => true, 'dias_activos' => [], 'cupos_total' => 0, 'hora_inicio' => null];
        }

        $boolVal = function($v) { return ($v === true || $v === 't' || $v === '1' || $v === 1); };
        $diasActivos = [];
        foreach (['lunes','martes','miercoles','jueves','viernes'] as $dia) {
            if ($boolVal($config[$dia])) $diasActivos[] = $dia;
        }

        return [
            'ok'           => true,
            'sin_config'   => false,
            'dias_activos' => $diasActivos,
            'cupos_total'  => (int)$config['cupos_total'],
            'hora_inicio'  => $config['hora_inicio'],
        ];
    }

    // ── Verificar si quedan cupos disponibles en una fecha ─────────────
    public static function verificarCupoDisponible($fecha, $id_tipo_turno) {
        $config = self::obtenerConfiguracionCupo($fecha, $id_tipo_turno);
        if ($config['sin_config']) return ['disponible' => true, 'ocupados' => 0, 'cupos_total' => 0];
        if (!$config['activo'])    return ['disponible' => false, 'motivo' => 'dia_no_habilitado'];

        $turnos  = self::obtenerTurnosPorFecha($fecha, $id_tipo_turno);
        $ocupados = count($turnos);
        $maximo   = $config['cupos_total'];

        if ($maximo > 0 && $ocupados >= $maximo) {
            return ['disponible' => false, 'motivo' => 'cupo_lleno', 'ocupados' => $ocupados, 'cupos_total' => $maximo, 'hora_inicio' => $config['hora_inicio']];
        }
        return ['disponible' => true, 'ocupados' => $ocupados, 'cupos_total' => $maximo, 'hora_inicio' => $config['hora_inicio']];
    }

   public static function buscarTurnos($filtro, $query, $limite = 10, $orden = 'desc', $pagina = 1) {
    $pdo = conexionCircuitoQuirurgico();

    $mapCampos = [
        'apellido' => 'LOWER(p.apellido)',
        'nombre'   => 'LOWER(p.nombre)',
        'dni'      => 'p.dni',
    ];
    $campo  = $mapCampos[$filtro] ?? 'LOWER(p.apellido)';
    $limite = in_array((int)$limite, [5, 10, 20]) ? (int)$limite : 10;
    $orden  = strtoupper($orden) === 'ASC' ? 'ASC' : 'DESC';
    $pagina = max(1, (int)$pagina);
    $offset = ($pagina - 1) * $limite;

    // Filtro solo pre-quirúrgico
    $where  = "WHERE LOWER(tt.codigo) = 'pre'";
    $params = [];

    if (!empty($query) && strlen(trim($query)) >= 2) {
        $like = '%' . strtolower(trim($query)) . '%';
        $where .= " AND $campo LIKE :like";
        $params[':like'] = $like;
    }

    // Total para paginación
    $sqlTotal = "
        SELECT COUNT(*) FROM turno t
        JOIN persona_asiste pa ON pa.id_persona_asiste = t.id_persona_asiste
        JOIN persona p          ON p.id_persona         = pa.id_persona
        JOIN tipo_turno tt      ON tt.id_tipo_turno     = t.id_tipo_turno
        JOIN estado e           ON e.id_estado          = t.id_estado
        $where
    ";
    $stmtTotal = $pdo->prepare($sqlTotal);
    foreach ($params as $k => $v) $stmtTotal->bindValue($k, $v);
    $stmtTotal->execute();
    $total = (int)$stmtTotal->fetchColumn();

    // Registros de la página
    $sql = "
        SELECT
            t.id_turno,
            p.nombre,
            p.apellido,
            p.dni,
            t.fecha_hora,
            tt.descripcion AS tipo_turno,
            e.descripcion  AS estado,
            e.codigo       AS estado_codigo
        FROM turno t
        JOIN persona_asiste pa ON pa.id_persona_asiste = t.id_persona_asiste
        JOIN persona p          ON p.id_persona         = pa.id_persona
        JOIN tipo_turno tt      ON tt.id_tipo_turno     = t.id_tipo_turno
        JOIN estado e           ON e.id_estado          = t.id_estado
        $where
        ORDER BY t.fecha_hora $orden
        LIMIT :limite OFFSET :offset
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'turnos'       => $turnos,
        'total'        => $total,
        'pagina'       => $pagina,
        'limite'       => $limite,
        'total_paginas' => (int)ceil($total / $limite),
    ];
}
    // ── Buscar paciente y validar circuito ──────────────────────

    public static function buscarPacienteConValidacion($dni, $id_tipo_turno) {
        $pdo = conexionCircuitoQuirurgico();

        // 1. Verificar que la persona existe y tiene admisión (persona_asiste)
        $sqlPersona = "
            SELECT
                p.id_persona,
                p.dni,
                p.nombre,
                p.apellido,
                pa.id_persona_asiste
            FROM persona p
            JOIN persona_asiste pa ON pa.id_persona = p.id_persona
            WHERE p.dni = :dni
            LIMIT 1
        ";
        $stmtP = $pdo->prepare($sqlPersona);
        $stmtP->bindParam(':dni', $dni);
        $stmtP->execute();
        $persona = $stmtP->fetch(PDO::FETCH_ASSOC);

        if (!$persona) {
            return [
                'ok'         => true,
                'encontrado' => false,
                'motivo'     => 'sin_admision',
                'mensaje'    => 'El paciente no tiene admisión registrada. Debe pasar primero por Admisión.'
            ];
        }

        // 2. Verificar turno duplicado ANTES de validar estado de orden
        $sqlTurno = "
            SELECT t.id_turno
            FROM turno t
            JOIN estado et ON et.id_estado = t.id_estado
            WHERE t.id_persona_asiste = :id_persona_asiste
              AND t.id_tipo_turno     = :id_tipo_turno
              AND et.codigo IN ('CONF', 'AUS')
            LIMIT 1
        ";
        $stmtT = $pdo->prepare($sqlTurno);
        $stmtT->bindParam(':id_persona_asiste', $persona['id_persona_asiste'], PDO::PARAM_INT);
        $stmtT->bindParam(':id_tipo_turno', $id_tipo_turno, PDO::PARAM_INT);
        $stmtT->execute();
        $turnoActivo = $stmtT->fetch(PDO::FETCH_ASSOC);

        if ($turnoActivo) {
            return [
                'ok'         => true,
                'encontrado' => false,
                'motivo'     => 'turno_duplicado',
                'mensaje'    => 'El paciente ya tiene un turno prequirúrgico activo. No se puede asignar otro.'
            ];
        }

        // 3. Buscar la orden médica más reciente y validar su estado
        $sqlOrden = "
            SELECT
                om.id_orden,
                om.id_estado        AS id_estado_orden,
                eo.codigo           AS codigo_estado_orden,
                eo.descripcion      AS desc_estado_orden,
                pr.nombre           AS medico_nombre,
                pr.apellido         AS medico_apellido
            FROM orden_medica om
            JOIN estado eo            ON eo.id_estado        = om.id_estado
            LEFT JOIN profesional prf ON prf.id_profesional  = om.id_profesional
            LEFT JOIN persona pr      ON pr.id_persona       = prf.id_persona
            WHERE om.id_persona_asiste = :id_persona_asiste
            ORDER BY om.id_orden DESC
            LIMIT 1
        ";
        $stmtO = $pdo->prepare($sqlOrden);
        $stmtO->bindParam(':id_persona_asiste', $persona['id_persona_asiste'], PDO::PARAM_INT);
        $stmtO->execute();
        $orden = $stmtO->fetch(PDO::FETCH_ASSOC);

        if (!$orden) {
            return [
                'ok'         => true,
                'encontrado' => false,
                'motivo'     => 'sin_admision',
                'mensaje'    => 'El paciente no tiene una orden médica registrada. Debe pasar primero por Admisión.'
            ];
        }

        $codigosBloqueo = ['FIN', 'CANC', 'RECH', 'ELIM'];
        if (in_array($orden['codigo_estado_orden'], $codigosBloqueo)) {
            $descripciones = [
                'FIN'  => 'La orden médica del paciente ya fue finalizada.',
                'CANC' => 'La orden médica del paciente fue cancelada.',
                'RECH' => 'La orden médica del paciente fue rechazada.',
                'ELIM' => 'La orden médica del paciente fue eliminada.',
            ];
            return [
                'ok'         => true,
                'encontrado' => false,
                'motivo'     => 'orden_bloqueada',
                'mensaje'    => $descripciones[$orden['codigo_estado_orden']] . ' No es posible asignar un nuevo turno.'
            ];
        }

        // 4. Todo OK
        return [
            'ok'                => true,
            'encontrado'        => true,
            'id_persona_asiste' => (int)$persona['id_persona_asiste'],
            'id_orden'          => (int)$orden['id_orden'],
            'nombre'            => $persona['nombre'],
            'apellido'          => $persona['apellido'],
            'dni'               => $persona['dni'],
            'medico'            => trim($orden['medico_apellido'] . ', ' . $orden['medico_nombre']),
        ];
    }

    // Programar turno con validación completa ────────────────
    public static function programarTurno($datos) {
        $pdo = conexionCircuitoQuirurgico();

        // Validación básica
        $errores = [];
        if (empty($datos['id_persona_asiste']) || !is_numeric($datos['id_persona_asiste']))
            $errores[] = 'Paciente inválido.';
        if (empty($datos['id_orden']) || !is_numeric($datos['id_orden']))
            $errores[] = 'Orden médica inválida.';
        if (empty($datos['id_tipo_turno']) || !is_numeric($datos['id_tipo_turno']))
            $errores[] = 'Tipo de turno inválido.';
        if (empty($datos['fecha_hora']))
            $errores[] = 'La fecha es obligatoria.';
        if (!empty($errores))
            return ['ok' => false, 'errores' => $errores];

        // Re-validar circuito en backend
        $validacion = self::buscarPacienteConValidacion(
            self::_getDniPorPersonaAsiste((int)$datos['id_persona_asiste'], $pdo),
            (int)$datos['id_tipo_turno']
        );
        if (!$validacion['encontrado']) {
            return ['ok' => false, 'errores' => [$validacion['mensaje']]];
        }

        // Validar cupo disponible en la fecha solicitada
        $fechaSolo = substr($datos['fecha_hora'], 0, 10);
        $cupoCheck = self::verificarCupoDisponible($fechaSolo, (int)$datos['id_tipo_turno']);
        if (!$cupoCheck['disponible']) {
            if ($cupoCheck['motivo'] === 'cupo_lleno') {
                return ['ok' => false, 'errores' => [
                    'El cupo para ese día está completo (' . $cupoCheck['ocupados'] . '/' . $cupoCheck['cupos_total'] . ' turnos). No se pueden agregar más turnos.'
                ]];
            }
            if ($cupoCheck['motivo'] === 'dia_no_habilitado') {
                return ['ok' => false, 'errores' => [
                    'El día seleccionado no está habilitado en la configuración de cupos activa.'
                ]];
            }
        }

        // Si tenemos la hora_inicio de la configuración, actualizar fecha_hora
        if (isset($cupoCheck['hora_inicio']) && !empty($cupoCheck['hora_inicio'])) {
            $datos['fecha_hora'] = $fechaSolo . ' ' . $cupoCheck['hora_inicio'];
        }

        // Validar cupo disponible en la fecha solicitada
        $fechaSolo = substr($datos['fecha_hora'], 0, 10);
        $cupoCheck = self::verificarCupoDisponible($fechaSolo, (int)$datos['id_tipo_turno']);
        if (!$cupoCheck['disponible']) {
            if ($cupoCheck['motivo'] === 'cupo_lleno') {
                return ['ok' => false, 'errores' => [
                    'El cupo para ese día está completo (' . $cupoCheck['ocupados'] . '/' . $cupoCheck['cupos_total'] . ' turnos). No se pueden agregar más turnos.'
                ]];
            }
            if ($cupoCheck['motivo'] === 'dia_no_habilitado') {
                return ['ok' => false, 'errores' => [
                    'El día seleccionado no está habilitado en la configuración de cupos activa.'
                ]];
            }
        }

        // Si tenemos la hora_inicio de la configuración, actualizar fecha_hora
        if (isset($cupoCheck['hora_inicio']) && !empty($cupoCheck['hora_inicio'])) {
            $datos['fecha_hora'] = $fechaSolo . ' ' . $cupoCheck['hora_inicio'];
        }

        // Obtener estado AUS para turno (por defecto ausente hasta que asista)
        $sqlEstado = "SELECT id_estado FROM estado WHERE codigo = 'AUS' AND entidad = 'TURNO' LIMIT 1";
        $id_estado = $pdo->query($sqlEstado)->fetchColumn();
        if (!$id_estado) {
            return ['ok' => false, 'errores' => ['No se encontró el estado AUS para turno.']];
        }

        // Insertar turno
        $sqlTurno = "
            INSERT INTO turno (id_persona_asiste, id_tipo_turno, id_orden, id_estado, fecha_hora, ausente)
            VALUES (:id_persona_asiste, :id_tipo_turno, :id_orden, :id_estado, :fecha_hora, true)
            RETURNING id_turno
        ";
        $stmt = $pdo->prepare($sqlTurno);
        $stmt->bindParam(':id_persona_asiste', $datos['id_persona_asiste'], PDO::PARAM_INT);
        $stmt->bindParam(':id_tipo_turno',     $datos['id_tipo_turno'],     PDO::PARAM_INT);
        $stmt->bindParam(':id_orden',          $datos['id_orden'],          PDO::PARAM_INT);
        $stmt->bindParam(':id_estado',         $id_estado,                  PDO::PARAM_INT);
        $stmt->bindParam(':fecha_hora',        $datos['fecha_hora']);
        $stmt->execute();
        $id_turno = $stmt->fetch(PDO::FETCH_ASSOC)['id_turno'];

        // Actualizar orden a PROC si estaba en PEND
        $sqlActOrden = "
            UPDATE orden_medica
            SET id_estado = (SELECT id_estado FROM estado WHERE codigo = 'PROC' AND entidad = 'ORDEN' LIMIT 1)
            WHERE id_orden = :id_orden
              AND id_estado = (SELECT id_estado FROM estado WHERE codigo = 'PEND' AND entidad = 'ORDEN' LIMIT 1)
        ";
        $stmtAct = $pdo->prepare($sqlActOrden);
        $stmtAct->bindParam(':id_orden', $datos['id_orden'], PDO::PARAM_INT);
        $stmtAct->execute();

        // Registrar evento clínico
        $sqlEvento = "
            INSERT INTO evento_clinico (id_tipo_evento, id_orden, id_turno, fecha_evento, descripcion)
            VALUES (
                (SELECT id_tipo_evento FROM tipo_evento WHERE LOWER(evento) = 'turno programado' LIMIT 1),
                :id_orden, :id_turno, NOW(), 'Turno prequirúrgico programado desde Agenda'
            )
        ";
        $stmtEv = $pdo->prepare($sqlEvento);
        $stmtEv->bindParam(':id_orden',  $datos['id_orden'],  PDO::PARAM_INT);
        $stmtEv->bindParam(':id_turno',  $id_turno,           PDO::PARAM_INT);
        $stmtEv->execute();
            /*
|--------------------------------------------------------------------------
| ENVÍO DE MAIL (service-mail)
|--------------------------------------------------------------------------
*/

try {

    // 1. Obtener email del paciente
    $sqlEmail = "
        SELECT p.email
        FROM persona p
        JOIN persona_asiste pa ON pa.id_persona = p.id_persona
        WHERE pa.id_persona_asiste = :id
        LIMIT 1
    ";

    $stmtEmail = $pdo->prepare($sqlEmail);
    $stmtEmail->bindParam(':id', $datos['id_persona_asiste'], PDO::PARAM_INT);
    $stmtEmail->execute();

    $emailPaciente = $stmtEmail->fetchColumn();

    // 2. Solo enviar si hay email
    error_log("DEBUG EMAIL PACIENTE: " . $emailPaciente);
    error_log("DEBUG ID TURNO: " . $id_turno);
    
    if ($emailPaciente) {


         // ==========================
        // PASO 1 - PLANTILLA
        // ==========================

        $sqlPlantilla = "
            SELECT asunto, cuerpo, id_plantilla
            FROM plantilla_mensaje
            WHERE tipo_evento = 'TURNO_PROGRAMADO'
            LIMIT 1
        ";

        $stmtPlantilla = $pdo->query($sqlPlantilla);
        $plantilla = $stmtPlantilla->fetch(PDO::FETCH_ASSOC);

        $asunto = $plantilla['asunto'] ?? 'Turno Prequirúrgico Confirmado';
        $cuerpo = $plantilla['cuerpo'] ?? '';

         // ==========================
        // PASO 2 - PACIENTE
        // ==========================

        $sqlPaciente = "
            SELECT p.nombre, p.apellido
            FROM persona p
            JOIN persona_asiste pa
                ON pa.id_persona = p.id_persona
            WHERE pa.id_persona_asiste = :id
        ";

        $stmtPaciente = $pdo->prepare($sqlPaciente);
        $stmtPaciente->bindParam(':id', $datos['id_persona_asiste']);
        $stmtPaciente->execute();

        $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

        $nombreCompleto =
            trim($paciente['nombre'] . ' ' . $paciente['apellido']);
        
          // ==========================
        // PASO 3 - ESTUDIOS
        // ==========================

        $sqlEstudios = "
            SELECT
                cc.nombre AS cirugia,
                e.nombre AS estudio,
                e.observacion
            FROM orden_medica om
            JOIN catalogo_cirugia cc
                ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
            JOIN combo_cirugia c
                ON c.id_cirugia_catalogo = cc.id_cirugia_catalogo
            JOIN estudio e
                ON e.id_estudio = c.id_estudio
            WHERE om.id_orden = :id_orden
            ORDER BY e.nombre
        ";

        $stmtEstudios = $pdo->prepare($sqlEstudios);
        $stmtEstudios->bindParam(':id_orden', $datos['id_orden']);
        $stmtEstudios->execute();

        $estudios = $stmtEstudios->fetchAll(PDO::FETCH_ASSOC);

         // ==========================
        // PASO 4 - ARMAR TEXTO
        // ==========================

        $cirugia = '';
        $textoEstudios = '';

        foreach ($estudios as $fila) {

        if (!$cirugia) {
        $cirugia = $fila['cirugia'];
       }

        $textoEstudios .= "• " . $fila['estudio'] . "\n";

        if (!empty($fila['observacion'])) {
        $textoEstudios .= "  Indicaciones:\n";
        $textoEstudios .= "  " . trim($fila['observacion']) . "\n";
       }

         $textoEstudios .= "\n";
       }
        
         // ==========================
        // PASO 5 - COMPLETAR PLANTILLA
        // ==========================
         $cuerpo = str_replace('{PACIENTE}', $nombreCompleto, $cuerpo);

        $cuerpo = str_replace(
         '{FECHA}',
          date('d/m/Y', strtotime($datos['fecha_hora'])),
          $cuerpo
        );

        $cuerpo = str_replace(
         '{HORA}',
         date('H:i', strtotime($datos['fecha_hora'])),
         $cuerpo
        );

         $cuerpo .= "\n\n";
         $cuerpo .= "====================================\n";
         $cuerpo .= "CIRUGÍA PROGRAMADA\n";
         $cuerpo .= "====================================\n\n";

         $cuerpo .= $cirugia . "\n\n";

         $cuerpo .= "ESTUDIOS REQUERIDOS\n";
         $cuerpo .= "------------------------------------\n\n";

         $cuerpo .= $textoEstudios;

         $cuerpo .= "IMPORTANTE\n";
         $cuerpo .= "------------------------------------\n";
         $cuerpo .= "• Los estudios se realizan por orden de llegada.\n";
         $cuerpo .= "• Concurra con DNI.\n";
         $cuerpo .= "• Respete las indicaciones de cada estudio.\n\n";

         $cuerpo .= "Saludos.\n";
         $cuerpo .= "Circuito Quirúrgico.\n";

        error_log("MAIL ASUNTO: " . $asunto);
        error_log("MAIL BODY: " . $cuerpo);   

          // ==========================
        // PASO 6 - PAYLOAD
        // ==========================

        $payload = [
            'to' => $emailPaciente,
            'subject' => $asunto,
            'body' => $cuerpo
        ];


        $ch = curl_init('http://149.50.137.127:3080/public/index.php');

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => 'cirugia:Cirugia2026',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 5
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // 3. LOGS (no interrumpe flujo)
        error_log("MAIL RESPONSE: " . $response);
        error_log("MAIL ERROR: " . $curlError);
        error_log("MAIL HTTP CODE: " . $httpCode);

        // 4. fallback silencioso
        if ($curlError || $httpCode !== 200) {
            error_log("MAIL FAIL - turno {$id_turno}");
        }

    } else {
        error_log("MAIL SKIP - sin email paciente, turno {$id_turno}");
    }

       } catch (Exception $e) {
    error_log("MAIL EXCEPTION: " . $e->getMessage());
       }

        return ['ok' => true, 'id_turno' => $id_turno];
    }

    // ── Helper interno ─────────────────────────────────────────────────
    private static function _getDniPorPersonaAsiste($id_persona_asiste, $pdo) {
        $sql = "
            SELECT p.dni FROM persona p
            JOIN persona_asiste pa ON pa.id_persona = p.id_persona
            WHERE pa.id_persona_asiste = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_persona_asiste, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // ── Obtener tipo_turno prequirúrgico ───────────────────────────────
    public static function obtenerIdTipoTurnoPrequirurgico() {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT id_tipo_turno FROM tipo_turno WHERE LOWER(codigo) = 'pre' LIMIT 1";
        $id = $pdo->query($sql)->fetchColumn();
        // fallback: primer tipo_turno disponible
        if (!$id) {
            $id = $pdo->query("SELECT id_tipo_turno FROM tipo_turno ORDER BY id_tipo_turno LIMIT 1")->fetchColumn();
        }
        return $id ?: null;
    }
}