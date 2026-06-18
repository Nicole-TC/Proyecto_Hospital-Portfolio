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
        // IMPORTANTE: Asegúrate de que 'AGENDA_CUPOS' y 'ACT' sean correctos para tu tabla estado
        $id_estado_act = id_estado('USUARIO', 'ACT');
        if (!$id_estado_act) {
            // Si no existe, podemos usar un default o fallar. Aquí lanzamos un error claro.
            return ['ok' => false, 'error' => "No se encontró el estado ACTIVO para AGENDA_CUPOS en la tabla estado."];
        }

        $sql = "INSERT INTO agenda_cupos
                    (id_tipo_turno, fecha_desde, fecha_hasta, hora_inicio, cupos_total,
                     lunes, martes, miercoles, jueves, viernes, creado_en, id_estado)
                VALUES
                    (:id_tipo_turno, :fecha_desde, :fecha_hasta, :hora_inicio, :cupos_total,
                     :lunes, :martes, :miercoles, :jueves, :viernes, CURRENT_TIMESTAMP, :id_estado)";

        try {
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
            return ['ok' => true, 'message' => 'Configuración guardada correctamente.'];
        } catch (PDOException $e) {
            error_log("Error guardando cupo: " . $e->getMessage());
            return ['ok' => false, 'error' => "Error al guardar en la base de datos."];
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
        
        // Uso de la función para no hardcodear el 42 (borrado lógico)
        $id_estado_ina = id_estado('AGENDA_CUPOS', 'INA');
        
        $sql = "UPDATE agenda_cupos SET id_estado = :id_estado_ina WHERE id_cupo = :id_cupo AND id_estado != :id_estado_ina";

        try {
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
