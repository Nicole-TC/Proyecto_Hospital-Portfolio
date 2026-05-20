<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasEspecialidades {

    /**
     * Obtener estadísticas: total, especialidades raíz, subespecialidades (sólo activas)
     */
    public static function obtenerEstadisticas() {
        $pdo = conexionCircuitoQuirurgico();
        $id_act = id_estado('USUARIO', 'ACT');
        $sql = "SELECT
                    COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE id_especialidad_padre IS NULL)     AS especialidades,
                    COUNT(*) FILTER (WHERE id_especialidad_padre IS NOT NULL)  AS subespecialidades
                FROM especialidad
                WHERE id_estado = :id_act";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_act', $id_act, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Listar especialidades o subespecialidades con búsqueda por nombre y paginación (sólo activas).
     * @param string $tipo       'especialidad' | 'subespecialidad'
     * @param string $nombre     Texto a buscar (puede estar vacío)
     * @param int    $pagina
     * @param int    $limite
     */
    public static function listar($tipo, $nombre, $pagina = 1, $limite = 10) {
        $pdo    = conexionCircuitoQuirurgico();
        $offset = ($pagina - 1) * $limite;
        $id_act = id_estado('USUARIO', 'ACT');

        // Condición base según tipo
        if ($tipo === 'subespecialidad') {
            $whereBase = "e.id_especialidad_padre IS NOT NULL AND e.id_estado = :id_act";
        } else {
            $whereBase = "e.id_especialidad_padre IS NULL AND e.id_estado = :id_act";
        }

        $params = [':id_act' => $id_act];
        $whereNombre = '';
        if (!empty($nombre)) {
            $whereNombre = " AND e.nombre ILIKE :nombre";
            $params[':nombre'] = '%' . trim($nombre) . '%';
        }

        $where = "WHERE $whereBase $whereNombre";

        // Contar total
        $sqlCount = "SELECT COUNT(*) FROM especialidad e $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            if ($k === ':id_act') {
                $stmtCount->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmtCount->bindValue($k, $v);
            }
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        // Obtener registros
        $sql = "SELECT
                    e.id_especialidad,
                    e.nombre,
                    e.id_especialidad_padre,
                    p.nombre AS nombre_padre
                FROM especialidad e
                LEFT JOIN especialidad p ON p.id_especialidad = e.id_especialidad_padre AND p.id_estado = :id_act
                $where
                ORDER BY e.nombre ASC
                LIMIT :limite OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            if ($k === ':id_act') {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v);
            }
        }
        $stmt->bindValue(':limite',  (int)$limite,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  (int)$offset,  PDO::PARAM_INT);
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['registros' => $registros, 'total' => $total];
    }

    /**
     * Crear una especialidad o subespecialidad.
     */
    public static function crear($nombre, $id_padre = null) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "INSERT INTO especialidad (nombre, id_especialidad_padre)
                VALUES (:nombre, :padre)
                RETURNING id_especialidad";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
        $stmt->bindValue(':padre',  $id_padre, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['id_especialidad'];
        }
        return false;
    }

    /**
     * Verificar si ya existe una especialidad activa con el mismo nombre y mismo padre.
     */
    public static function existeNombre($nombre, $id_padre = null, $excluirId = null) {
        $pdo = conexionCircuitoQuirurgico();
        $id_act = id_estado('USUARIO', 'ACT');
        if ($id_padre === null) {
            $sql = "SELECT COUNT(*) FROM especialidad WHERE UPPER(nombre) = UPPER(:nombre) AND id_especialidad_padre IS NULL AND id_estado = :id_act";
        } else {
            $sql = "SELECT COUNT(*) FROM especialidad WHERE UPPER(nombre) = UPPER(:nombre) AND id_especialidad_padre = :padre AND id_estado = :id_act";
        }
        if ($excluirId) $sql .= " AND id_especialidad != :excluir";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre', trim($nombre));
        $stmt->bindValue(':id_act', $id_act, PDO::PARAM_INT);
        if ($id_padre !== null) $stmt->bindValue(':padre', $id_padre, PDO::PARAM_INT);
        if ($excluirId)         $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Actualizar nombre (y opcionalmente padre) de una especialidad.
     */
    public static function actualizar($id, $nombre, $id_padre = null) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "UPDATE especialidad SET nombre = :nombre, id_especialidad_padre = :padre WHERE id_especialidad = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
        $stmt->bindValue(':padre',  $id_padre, PDO::PARAM_INT);
        $stmt->bindValue(':id',     $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Verificar si una especialidad tiene subespecialidades activas (para bloquear eliminación).
     */
    public static function tieneHijos($id) {
        $pdo  = conexionCircuitoQuirurgico();
        $id_act = id_estado('USUARIO', 'ACT');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM especialidad WHERE id_especialidad_padre = :id AND id_estado = :id_act");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':id_act', $id_act, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Verificar si la especialidad está siendo usada en profesional_especialidad o matricula.
     */
    public static function estaEnUso($id) {
        $pdo = conexionCircuitoQuirurgico();

        $s1 = $pdo->prepare("SELECT COUNT(*) FROM profesional_especialidad WHERE id_especialidad = :id");
        $s1->bindValue(':id', $id, PDO::PARAM_INT);
        $s1->execute();
        if ((int)$s1->fetchColumn() > 0) return true;

        $s2 = $pdo->prepare("SELECT COUNT(*) FROM matricula WHERE id_especialidad = :id");
        $s2->bindValue(':id', $id, PDO::PARAM_INT);
        $s2->execute();
        return (int)$s2->fetchColumn() > 0;
    }

    /**
     * Eliminar (inactivar lógicamente) una especialidad.
     */
    public static function eliminar($id) {
        $pdo  = conexionCircuitoQuirurgico();
        $id_ina = id_estado('USUARIO', 'INA');
        $stmt = $pdo->prepare("UPDATE especialidad SET id_estado = :id_estado WHERE id_especialidad = :id");
        $stmt->bindValue(':id_estado', $id_ina, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
