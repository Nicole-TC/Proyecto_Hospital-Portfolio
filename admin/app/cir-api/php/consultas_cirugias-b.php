<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasCirugias {

    /**
     * Listar todos los estudios disponibles para mostrar en el paso 2 del formulario.
     */
    public static function listarEstudios() {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $sql = "SELECT id_estudio AS id, nombre, descripcion, observacion AS observaciones FROM estudio WHERE id_estado = :id_estado ORDER BY nombre";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_estado' => $idActivo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $sql = "SELECT id_estudio AS id, nombre, descripcion, observacion AS observaciones FROM estudio ORDER BY nombre";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Verificar si ya existe una cirugía con ese nombre.
     * @param string   $nombre
     * @param int|null $excluirId  ID a excluir (para edición)
     */
    public static function existeNombreCirugia(string $nombre, ?int $excluirId = null): bool {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $sql = "SELECT COUNT(*) FROM catalogo_cirugia WHERE nombre = :nombre AND id_estado = :id_estado";
            if ($excluirId) $sql .= " AND id_cirugia_catalogo != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            $stmt->bindValue(':id_estado', $idActivo, PDO::PARAM_INT);
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $sql = "SELECT COUNT(*) FROM catalogo_cirugia WHERE nombre = :nombre";
            if ($excluirId) $sql .= " AND id_cirugia_catalogo != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        }
    }

    /**
     * Verificar si ya existe un estudio con ese nombre.
     * @param string   $nombre
     * @param int|null $excluirId  ID a excluir (para edición)
     * @return int|false  ID del estudio si existe, false si no
     */
    public static function existeNombreEstudio(string $nombre, ?int $excluirId = null) {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $sql = "SELECT id_estudio FROM estudio WHERE nombre = :nombre AND id_estado = :id_estado";
            if ($excluirId) $sql .= " AND id_estudio != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            $stmt->bindValue(':id_estado', $idActivo, PDO::PARAM_INT);
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            $sql = "SELECT id_estudio FROM estudio WHERE nombre = :nombre";
            if ($excluirId) $sql .= " AND id_estudio != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        }
    }

    /**
     * Crear un nuevo estudio prequirúrgico de forma independiente.
     */
    public static function crearEstudio($nombre, $descripcion, $observacion) {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("INSERT INTO estudio (nombre, descripcion, observacion, id_estado) VALUES (:n, :d, :o, :est) RETURNING id_estudio");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':o' => trim($observacion), ':est' => $idActivo]) ? (int)$stmt->fetchColumn() : false;
        } catch (Exception $e) {
            $stmt = $pdo->prepare("INSERT INTO estudio (nombre, descripcion, observacion) VALUES (:n, :d, :o) RETURNING id_estudio");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':o' => trim($observacion)]) ? (int)$stmt->fetchColumn() : false;
        }
    }

    /**
     * Crear cirugía en catálogo (sin estudios).
     */
    public static function crearCirugiaCatalogo(string $nombre, string $descripcion) {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("INSERT INTO catalogo_cirugia (nombre, descripcion, id_estado) VALUES (:n, :d, :est) RETURNING id_cirugia_catalogo");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':est' => $idActivo]) ? (int)$stmt->fetchColumn() : false;
        } catch (Exception $e) {
            $stmt = $pdo->prepare("INSERT INTO catalogo_cirugia (nombre, descripcion) VALUES (:n, :d) RETURNING id_cirugia_catalogo");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion)]) ? (int)$stmt->fetchColumn() : false;
        }
    }

    /**
     * Actualizar datos de una cirugía.
     */
    public static function actualizarCirugia(int $id, string $nombre, string $descripcion): bool {
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE catalogo_cirugia SET nombre = :n, descripcion = :d WHERE id_cirugia_catalogo = :id");
        return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':id' => $id]);
    }

    /**
     * Actualizar datos de un estudio.
     */
    public static function actualizarEstudio(int $id, string $nombre, string $descripcion, string $observacion): bool {
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE estudio SET nombre = :n, descripcion = :d, observacion = :o WHERE id_estudio = :id");
        return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':o' => trim($observacion), ':id' => $id]);
    }

    /**
     * Listar cirugías con búsqueda y paginación.
     */
    public static function listarCirugias(string $buscar, string $orden, int $pagina, int $limite): array {
        $pdo    = conexionCircuitoQuirurgico();
        $offset = ($pagina - 1) * $limite;
        $idActivo = id_estado('USUARIO', 'ACT');
        $params = [':id_estado' => $idActivo];
        $where  = 'WHERE id_estado = :id_estado';

        if ($buscar !== '') {
            $where .= " AND (nombre ILIKE :buscar OR descripcion ILIKE :buscar)";
            $params[':buscar'] = '%' . $buscar . '%';
        }

        $orderBy = "ORDER BY nombre ASC";
        if ($orden === 'nombre_desc') $orderBy = "ORDER BY nombre DESC";
        if ($orden === 'id_desc') $orderBy = "ORDER BY id_cirugia_catalogo DESC";

        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM catalogo_cirugia $where");
        foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
        $stmtC->execute();
        $total = (int)$stmtC->fetchColumn();

        $sql  = "SELECT id_cirugia_catalogo AS id, nombre, descripcion FROM catalogo_cirugia $where $orderBy LIMIT :limite OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite',  $limite,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'registros' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'     => $total,
            'pagina'    => $pagina,
            'limite'    => $limite,
        ];
    }

    /**
     * Listar estudios con búsqueda y paginación (para el CRUD).
     */
    public static function listarEstudiosCrud(string $buscar, string $orden, int $pagina, int $limite): array {
        $pdo    = conexionCircuitoQuirurgico();
        $offset = ($pagina - 1) * $limite;
        $idActivo = id_estado('USUARIO', 'ACT');
        $params = [':id_estado' => $idActivo];
        $where  = 'WHERE id_estado = :id_estado';

        if ($buscar !== '') {
            $where .= " AND (nombre ILIKE :buscar OR descripcion ILIKE :buscar)";
            $params[':buscar'] = '%' . $buscar . '%';
        }

        $orderBy = "ORDER BY nombre ASC";
        if ($orden === 'nombre_desc') $orderBy = "ORDER BY nombre DESC";
        if ($orden === 'id_desc') $orderBy = "ORDER BY id_estudio DESC";

        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM estudio $where");
        foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
        $stmtC->execute();
        $total = (int)$stmtC->fetchColumn();

        $sql  = "SELECT id_estudio AS id, nombre, descripcion, observacion FROM estudio $where $orderBy LIMIT :limite OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite',  $limite,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'registros' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'     => $total,
            'pagina'    => $pagina,
            'limite'    => $limite,
        ];
    }

    /**
     * Listar combos con búsqueda y filtro de cirugía.
     */
    public static function listarCombos(string $buscar, ?int $idCirugia, int $pagina, int $limite): array {
        $pdo    = conexionCircuitoQuirurgico();
        $offset = ($pagina - 1) * $limite;
        $idActivo = id_estado('USUARIO', 'ACT');
        $params = [':id_estado' => $idActivo];
        $where  = 'WHERE c.id_estado = :id_estado AND e.id_estado = :id_estado';

        if ($idCirugia) {
            $where .= " AND cc.id_cirugia_catalogo = :id_cirugia";
            $params[':id_cirugia'] = $idCirugia;
        }
        if ($buscar !== '') {
            $where .= " AND (e.nombre ILIKE :buscar OR c.nombre ILIKE :buscar)";
            $params[':buscar'] = '%' . $buscar . '%';
        }

        $sqlBase = "FROM combo_cirugia cc
                    JOIN catalogo_cirugia c ON c.id_cirugia_catalogo = cc.id_cirugia_catalogo
                    JOIN estudio e ON e.id_estudio = cc.id_estudio
                    $where";

        $stmtC = $pdo->prepare("SELECT COUNT(*) $sqlBase");
        foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
        $stmtC->execute();
        $total = (int)$stmtC->fetchColumn();

        $sql  = "SELECT c.id_cirugia_catalogo, c.nombre AS cirugia,
                        e.id_estudio, e.nombre AS estudio, e.observacion
                 $sqlBase
                 ORDER BY c.nombre, e.nombre
                 LIMIT :limite OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite',  $limite,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'registros' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'     => $total,
            'pagina'    => $pagina,
            'limite'    => $limite,
        ];
    }

    /**
     * Listar todas las cirugías para el select de combos.
     */
    public static function listarCirugiasSelect(): array {
        $pdo  = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("SELECT id_cirugia_catalogo AS id, nombre FROM catalogo_cirugia WHERE id_estado = :id_estado ORDER BY nombre");
            $stmt->execute([':id_estado' => $idActivo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stmt = $pdo->prepare("SELECT id_cirugia_catalogo AS id, nombre FROM catalogo_cirugia ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Listar cirugías que aún NO tienen estudios vinculados (para el Select de combos).
     */
    public static function listarCirugiasSinComboSelect(): array {
        $pdo  = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("SELECT id_cirugia_catalogo AS id, nombre FROM catalogo_cirugia WHERE id_estado = :id_estado AND id_cirugia_catalogo NOT IN (SELECT id_cirugia_catalogo FROM combo_cirugia) ORDER BY nombre");
            $stmt->execute([':id_estado' => $idActivo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stmt = $pdo->prepare("SELECT id_cirugia_catalogo AS id, nombre FROM catalogo_cirugia WHERE id_cirugia_catalogo NOT IN (SELECT id_cirugia_catalogo FROM combo_cirugia) ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Guardar la vinculación de estudios para una cirugía.
     */
    public static function guardarCombo(int $idCirugia, array $estudios): bool {
        $pdo = conexionCircuitoQuirurgico();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO combo_cirugia (id_cirugia_catalogo, id_estudio) VALUES (:id_cirugia, :id_estudio)");
            $stmt->bindValue(':id_cirugia', $idCirugia, PDO::PARAM_INT);
            foreach ($estudios as $idEstudio) {
                $stmt->bindValue(':id_estudio', $idEstudio, PDO::PARAM_INT);
                $stmt->execute();
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /* ----------------------------------------------------------------
       VERIFICACIONES RELACIONALES PARA BORRADO
       ---------------------------------------------------------------- */

    private static function existeRegistro(string $tabla, string $columna, int $id): bool {
        $stmt = conexionCircuitoQuirurgico()->prepare("SELECT 1 FROM $tabla WHERE $columna = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return (bool)$stmt->fetchColumn();
    }

    public static function cirugiaEnUsoCombos(int $id): bool { return self::existeRegistro('combo_cirugia', 'id_cirugia_catalogo', $id); }
    public static function cirugiaEnUsoOrdenes(int $id): bool { return self::existeRegistro('orden_medica', 'id_cirugia_catalogo', $id); }
    public static function estudioEnUsoCombos(int $id): bool { return self::existeRegistro('combo_cirugia', 'id_estudio', $id); }
    public static function estudioEnUsoOrdenes(int $id): bool { return self::existeRegistro('orden_estudio', 'id_estudio', $id); }

    /**
     * Borrado lógico de cirugía del catálogo.
     */
    public static function eliminarCirugia(int $id): bool {
        $idElim = id_estado('DATO', 'ELIM');
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE catalogo_cirugia SET id_estado = :est WHERE id_cirugia_catalogo = :id");
        return $stmt->execute([':est' => $idElim, ':id' => $id]);
    }

    /**
     * Borrado lógico de estudio del catálogo.
     */
    public static function eliminarEstudio(int $id): bool {
        $idElim = id_estado('DATO', 'ELIM');
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE estudio SET id_estado = :est WHERE id_estudio = :id");
        return $stmt->execute([':est' => $idElim, ':id' => $id]);
    }

}
?>
