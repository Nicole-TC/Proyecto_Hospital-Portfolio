<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasCupos {

    // ── Validar y limpiar datos del formulario ─────────────────────────
    private static function validarDatos($datos) {
        $errores = [];
        $id_tipo_turno = filter_var($datos['id_tipo_turno'] ?? null, FILTER_VALIDATE_INT);
        $fecha_desde   = htmlspecialchars(trim($datos['fecha_desde'] ?? ''));
        $hora_inicio   = htmlspecialchars(trim($datos['hora_inicio'] ?? ''));
        $cupos_total   = filter_var($datos['cupos_total'] ?? 0, FILTER_VALIDATE_INT);
        $fecha_hasta   = !empty($datos['fecha_hasta']) ? htmlspecialchars(trim($datos['fecha_hasta'])) : null;

        if (!$id_tipo_turno) $errores[] = 'Tipo de turno inválido.';
        if (empty($fecha_desde)) $errores[] = 'La fecha de inicio es obligatoria.';
        if (empty($hora_inicio)) $errores[] = 'La hora de inicio es obligatoria.';
        if (!$cupos_total || $cupos_total < 1) $errores[] = 'Los cupos deben ser mayor a 0.';

        if (!empty($errores)) {
            return ['ok' => false, 'error' => implode(' ', $errores)];
        }

        // Convertimos los días a booleanos reales
        return [
            'ok'            => true,
            'id_tipo_turno' => $id_tipo_turno,
            'fecha_desde'   => $fecha_desde,
            'fecha_hasta'   => $fecha_hasta,
            'hora_inicio'   => $hora_inicio,
            'cupos_total'   => $cupos_total,
            'lunes'         => isset($datos['lunes']) && $datos['lunes'] == '1' ? 'true' : 'false',
            'martes'        => isset($datos['martes']) && $datos['martes'] == '1' ? 'true' : 'false',
            'miercoles'     => isset($datos['miercoles']) && $datos['miercoles'] == '1' ? 'true' : 'false',
            'jueves'        => isset($datos['jueves']) && $datos['jueves'] == '1' ? 'true' : 'false',
            'viernes'       => isset($datos['viernes']) && $datos['viernes'] == '1' ? 'true' : 'false',
        ];
    }

    // ── READ: Obtener los tipos de turno desde la BD ───────────────────
    public static function obtenerTiposTurno() {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT id_tipo_turno, codigo, descripcion FROM tipo_turno ORDER BY id_tipo_turno";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo tipos de turno: " . $e->getMessage());
            return [];
        }
    }

    // ── CREATE: Guardar un nuevo cupo ──────────────────────────────────
    public static function guardarCupo($datos) {
        $v = self::validarDatos($datos);
        if (!$v['ok']) return $v;

        // Obtenemos la conexión de forma limpia
        $pdo = conexionCircuitoQuirurgico();
        
        // Obtener el ID de estado Activo usando la función
        $id_estado_act = id_estado('USUARIO', 'ACT');
        if (!$id_estado_act) {
            // Si no existe, podemos usar un default o fallar. Aquí lanzamos un error claro.
            return ['ok' => false, 'error' => "No se encontró el estado ACTIVO para AGENDA_CUPOS en la tabla estado."];
        }

        try {
            $pdo->beginTransaction();

            // Auto-asignar fecha_hasta si hay un ciclo futuro activo y no se envió fecha de fin
            if (empty($v['fecha_hasta'])) {
                $sqlNext = "SELECT fecha_desde FROM agenda_cupos
                            WHERE id_tipo_turno = :id_tipo_turno
                              AND id_estado = :id_estado_act
                              AND fecha_desde > :fecha_desde
                            ORDER BY fecha_desde ASC LIMIT 1";
                $stmtNext = $pdo->prepare($sqlNext);
                $stmtNext->bindParam(':id_tipo_turno', $v['id_tipo_turno'], PDO::PARAM_INT);
                $stmtNext->bindParam(':id_estado_act', $id_estado_act, PDO::PARAM_INT);
                $stmtNext->bindParam(':fecha_desde', $v['fecha_desde']);
                $stmtNext->execute();
                $nextCycle = $stmtNext->fetch(PDO::FETCH_ASSOC);

                if ($nextCycle) {
                    // El ciclo abierto choca con uno futuro, lo cerramos el día anterior
                    $v['fecha_hasta'] = date('Y-m-d', strtotime($nextCycle['fecha_desde'] . ' -1 day'));
                }
            }

            // Validar que la nueva fecha_desde no caiga dentro de un ciclo CERRADO existente
            $sqlOverlap = "SELECT id_cupo FROM agenda_cupos 
                           WHERE id_tipo_turno = :id_tipo_turno
                             AND id_estado = :id_estado_act
                             AND fecha_desde <= :fecha_desde
                             AND fecha_hasta >= :fecha_desde_2
                           LIMIT 1";
            $stmtOverlap = $pdo->prepare($sqlOverlap);
            $stmtOverlap->bindParam(':id_tipo_turno', $v['id_tipo_turno'], PDO::PARAM_INT);
            $stmtOverlap->bindParam(':id_estado_act', $id_estado_act, PDO::PARAM_INT);
            $stmtOverlap->bindParam(':fecha_desde', $v['fecha_desde']);
            $stmtOverlap->bindParam(':fecha_desde_2', $v['fecha_desde']);
            $stmtOverlap->execute();
            if ($stmtOverlap->fetch()) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'La fecha de inicio ingresada recae dentro de un ciclo de cupos ya existente.'];
            }

            // Buscar si hay un ciclo anterior activo y sin fecha_hasta para este tipo de turno
            $sqlAnterior = "SELECT id_cupo FROM agenda_cupos 
                            WHERE id_tipo_turno = :id_tipo_turno 
                              AND fecha_hasta IS NULL 
                              AND id_estado = :id_estado_act 
                            ORDER BY creado_en DESC LIMIT 1";
            $stmtAnterior = $pdo->prepare($sqlAnterior);
            $stmtAnterior->bindParam(':id_tipo_turno', $v['id_tipo_turno'], PDO::PARAM_INT);
            $stmtAnterior->bindParam(':id_estado_act', $id_estado_act, PDO::PARAM_INT);
            $stmtAnterior->execute();
            $anterior = $stmtAnterior->fetch(PDO::FETCH_ASSOC);

            if ($anterior) {
                // Actualizar la fecha_hasta del ciclo anterior al día anterior del inicio del nuevo
                $nuevaFechaHasta = date('Y-m-d', strtotime($v['fecha_desde'] . ' -1 day'));
                $sqlUpdate = "UPDATE agenda_cupos SET fecha_hasta = :nueva_fecha_hasta WHERE id_cupo = :id_cupo";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':nueva_fecha_hasta', $nuevaFechaHasta);
                $stmtUpdate->bindParam(':id_cupo', $anterior['id_cupo'], PDO::PARAM_INT);
                $stmtUpdate->execute();
            }

            $sql = "INSERT INTO agenda_cupos
                        (id_tipo_turno, fecha_desde, fecha_hasta, hora_inicio, cupos_total,
                         lunes, martes, miercoles, jueves, viernes, creado_en, id_estado)
                    VALUES
                        (:id_tipo_turno, :fecha_desde, :fecha_hasta, :hora_inicio, :cupos_total,
                         :lunes, :martes, :miercoles, :jueves, :viernes, CURRENT_TIMESTAMP, :id_estado)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_tipo_turno', $v['id_tipo_turno'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_desde',   $v['fecha_desde']);
            $stmt->bindParam(':fecha_hasta',   $v['fecha_hasta']);
            $stmt->bindParam(':hora_inicio',   $v['hora_inicio']);
            $stmt->bindParam(':cupos_total',   $v['cupos_total'], PDO::PARAM_INT);
            $stmt->bindParam(':lunes',         $v['lunes']);
            $stmt->bindParam(':martes',        $v['martes']);
            $stmt->bindParam(':miercoles',     $v['miercoles']);
            $stmt->bindParam(':jueves',        $v['jueves']);
            $stmt->bindParam(':viernes',       $v['viernes']);
            $stmt->bindParam(':id_estado',     $id_estado_act, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $pdo->commit();
            return ['ok' => true, 'message' => 'Configuración guardada correctamente.'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() == '23505') {
                return ['ok' => false, 'error' => 'Ya existe un ciclo (incluso si está eliminado) que inicia exactamente en esa fecha. Por favor, elige un día de inicio distinto o solicita al administrador que limpie la base de datos.'];
            }
            error_log("Error guardando cupo: " . $e->getMessage());
            return ['ok' => false, 'error' => "Error DB: " . $e->getMessage()];
        }
    }

    // ── READ: Listar todos los cupos activos ───────────────────────────
    public static function listarCupos() {
        $pdo = conexionCircuitoQuirurgico();
        
        // Obtener el ID de estado Inactivo para filtrarlo usando la función
        $id_estado_ina = id_estado('DATO', 'ELIM');
        if (!$id_estado_ina) $id_estado_ina = 0; // Prevenir fallo en consulta SQL si la entidad es incorrecta

        $sql = "SELECT
                    ac.id_cupo,
                    ac.fecha_desde,
                    ac.fecha_hasta,
                    ac.hora_inicio,
                    ac.cupos_total,
                    ac.lunes,
                    ac.martes,
                    ac.miercoles,
                    ac.jueves,
                    ac.viernes,
                    ac.id_tipo_turno,
                    ac.creado_en,
                    tt.descripcion AS tipo_turno_nombre
                FROM agenda_cupos ac
                JOIN tipo_turno tt ON tt.id_tipo_turno = ac.id_tipo_turno
                WHERE ac.id_estado != :id_estado_ina
                ORDER BY ac.creado_en DESC";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_estado_ina', $id_estado_ina, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['ok' => true, 'data' => $datos];
        } catch (PDOException $e) {
            error_log("Error listando cupos: " . $e->getMessage());
            return ['ok' => false, 'error' => "Error al obtener los cupos."];
        }
    }

    // ── UPDATE: Actualizar un cupo existente ───────────────────────────
    public static function actualizarCupo($datos) {
        $id_cupo = filter_var($datos['id_cupo'] ?? null, FILTER_VALIDATE_INT);
        if (!$id_cupo) return ['ok' => false, 'error' => 'ID de cupo inválido.'];

        $v = self::validarDatos($datos);
        if (!$v['ok']) return $v;

        $pdo = conexionCircuitoQuirurgico();
        $id_estado_ina = id_estado('DATO', 'ELIM');
        if (!$id_estado_ina) $id_estado_ina = 0;

        $sql = "UPDATE agenda_cupos SET
                    id_tipo_turno = :id_tipo_turno,
                    fecha_desde   = :fecha_desde,
                    fecha_hasta   = :fecha_hasta,
                    hora_inicio   = :hora_inicio,
                    cupos_total   = :cupos_total,
                    lunes         = :lunes,
                    martes        = :martes,
                    miercoles     = :miercoles,
                    jueves        = :jueves,
                    viernes       = :viernes
                WHERE id_cupo = :id_cupo AND id_estado != :id_estado_ina";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_cupo',        $id_cupo, PDO::PARAM_INT);
            $stmt->bindParam(':id_tipo_turno',  $v['id_tipo_turno'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_desde',    $v['fecha_desde']);
            $stmt->bindParam(':fecha_hasta',    $v['fecha_hasta']);
            $stmt->bindParam(':hora_inicio',    $v['hora_inicio']);
            $stmt->bindParam(':cupos_total',    $v['cupos_total'], PDO::PARAM_INT);
            $stmt->bindParam(':lunes',          $v['lunes']);
            $stmt->bindParam(':martes',         $v['martes']);
            $stmt->bindParam(':miercoles',      $v['miercoles']);
            $stmt->bindParam(':jueves',         $v['jueves']);
            $stmt->bindParam(':viernes',        $v['viernes']);
            $stmt->bindParam(':id_estado_ina',  $id_estado_ina, PDO::PARAM_INT);
            
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['ok' => false, 'error' => 'No se encontró el cupo o ya fue eliminado.'];
            }
            return ['ok' => true, 'message' => 'Cupo actualizado correctamente.'];
        } catch (PDOException $e) {
            error_log("Error actualizando cupo: " . $e->getMessage());
            return ['ok' => false, 'error' => "Error al actualizar en la base de datos."];
        }
    }

    // ── DELETE: Eliminar (Borrado lógico) de un cupo ───────────────────
    public static function eliminarCupo($id_cupo) {
        $id_cupo = filter_var($id_cupo, FILTER_VALIDATE_INT);
        if (!$id_cupo) return ['ok' => false, 'error' => 'ID de cupo inválido.'];

        $pdo = conexionCircuitoQuirurgico();

        try {
            // Verificar si hay turnos asignados
            $sqlInfo = "SELECT id_tipo_turno, fecha_desde, fecha_hasta FROM agenda_cupos WHERE id_cupo = :id_cupo";
            $stmtInfo = $pdo->prepare($sqlInfo);
            $stmtInfo->bindParam(':id_cupo', $id_cupo, PDO::PARAM_INT);
            $stmtInfo->execute();
            $cupoInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            if (!$cupoInfo) return ['ok' => false, 'error' => 'No se encontró el cupo.'];

            $hasFechaHasta = !empty($cupoInfo['fecha_hasta']);
            $sqlTurnos = "SELECT COUNT(*) FROM turno t 
                          WHERE t.id_tipo_turno = :id_tipo_turno 
                            AND DATE(t.fecha_hora) >= :fecha_desde";
            
            if ($hasFechaHasta) {
                $sqlTurnos .= " AND DATE(t.fecha_hora) <= :fecha_hasta";
            }
            $sqlTurnos .= " AND (t.cancelado IS NULL OR t.cancelado = false)";
            
            $stmtTurnos = $pdo->prepare($sqlTurnos);
            $stmtTurnos->bindParam(':id_tipo_turno', $cupoInfo['id_tipo_turno'], PDO::PARAM_INT);
            $stmtTurnos->bindParam(':fecha_desde', $cupoInfo['fecha_desde']);
            if ($hasFechaHasta) {
                $stmtTurnos->bindParam(':fecha_hasta', $cupoInfo['fecha_hasta']);
            }
            $stmtTurnos->execute();
            
            $countTurnos = (int)$stmtTurnos->fetchColumn();
            if ($countTurnos > 0) {
                return ['ok' => false, 'error' => "No se puede eliminar la configuración porque existen {$countTurnos} turno(s) asignado(s) en este período."];
            }

            // Proceder con el borrado lógico
            $id_estado_ina = id_estado('DATO', 'ELIM');
            if (!$id_estado_ina) $id_estado_ina = 0;
            
            $sql = "UPDATE agenda_cupos SET id_estado = :id_estado_ina WHERE id_cupo = :id_cupo AND id_estado != :id_estado_ina";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_cupo', $id_cupo, PDO::PARAM_INT);
            $stmt->bindParam(':id_estado_ina', $id_estado_ina, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['ok' => false, 'error' => 'No se encontró el cupo o ya fue eliminado.'];
            }
            return ['ok' => true, 'message' => 'Cupo eliminado correctamente.'];
        } catch (PDOException $e) {
            error_log("Error eliminando cupo: " . $e->getMessage());
            return ['ok' => false, 'error' => "Error al eliminar en la base de datos."];
        }
    }
}
