<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasCirugias {

    /** Cadena de caracteres acentuados para comparación sin acentos en SQL. */
    private const CADENAS_ACENTUADAS = 'ÁÉÍÓÚÜÑáéíóúüñ';
    /** Correspondencia sin acentos (misma posición que CADENAS_ACENTUADAS). */
    private const CADENAS_SIN_ACENTO = 'AEIOUUNaeiouun';

    /**
     * Genera una condición SQL que compara dos columnas/parámetros ignorando
     * acentos y mayúsculas (evita duplicados que solo difieren en acentuación).
     */
    private static function igualSinAcentos(string $columna1, string $columna2): string {
        return "translate(lower($columna1), '" . self::CADENAS_ACENTUADAS . "', '" . self::CADENAS_SIN_ACENTO . "')"
             . " = "
             . "translate(lower($columna2), '" . self::CADENAS_ACENTUADAS . "', '" . self::CADENAS_SIN_ACENTO . "')";
    }

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
            $sql = "SELECT COUNT(*) FROM catalogo_cirugia WHERE " . self::igualSinAcentos('nombre', ':nombre') . " AND id_estado = :id_estado";
            if ($excluirId) $sql .= " AND id_cirugia_catalogo != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            $stmt->bindValue(':id_estado', $idActivo, PDO::PARAM_INT);
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $sql = "SELECT COUNT(*) FROM catalogo_cirugia WHERE " . self::igualSinAcentos('nombre', ':nombre');
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
            $sql = "SELECT id_estudio FROM estudio WHERE " . self::igualSinAcentos('nombre', ':nombre') . " AND id_estado = :id_estado";
            if ($excluirId) $sql .= " AND id_estudio != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            $stmt->bindValue(':id_estado', $idActivo, PDO::PARAM_INT);
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            $sql = "SELECT id_estudio FROM estudio WHERE " . self::igualSinAcentos('nombre', ':nombre');
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
    public static function crearEstudio($nombre, $descripcion, $observacion, $id_categoria_estudio = null) {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("INSERT INTO estudio (nombre, descripcion, observacion, id_categoria_estudio, id_estado) VALUES (:n, :d, :o, :c, :est) RETURNING id_estudio");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':o' => trim($observacion), ':c' => $id_categoria_estudio ?: null, ':est' => $idActivo]) ? (int)$stmt->fetchColumn() : false;
        } catch (Exception $e) {
            $stmt = $pdo->prepare("INSERT INTO estudio (nombre, descripcion, observacion, id_categoria_estudio) VALUES (:n, :d, :o, :c) RETURNING id_estudio");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':o' => trim($observacion), ':c' => $id_categoria_estudio ?: null]) ? (int)$stmt->fetchColumn() : false;
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
    public static function actualizarEstudio(int $id, string $nombre, string $descripcion, string $observacion, ?int $id_categoria_estudio = null): bool {
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE estudio SET nombre = :n, descripcion = :d, observacion = :o, id_categoria_estudio = :c WHERE id_estudio = :id");
        return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':o' => trim($observacion), ':c' => $id_categoria_estudio ?: null, ':id' => $id]);
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
        $where  = 'WHERE e.id_estado = :id_estado';

        if ($buscar !== '') {
            $where .= " AND (e.nombre ILIKE :buscar OR e.descripcion ILIKE :buscar OR c.nombre ILIKE :buscar)";
            $params[':buscar'] = '%' . $buscar . '%';
        }

        $orderBy = "ORDER BY e.nombre ASC";
        if ($orden === 'nombre_desc') $orderBy = "ORDER BY e.nombre DESC";
        if ($orden === 'id_desc') $orderBy = "ORDER BY e.id_estudio DESC";

        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM estudio e LEFT JOIN categoria_estudio c ON e.id_categoria_estudio = c.id_categoria_estudio $where");
        foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
        $stmtC->execute();
        $total = (int)$stmtC->fetchColumn();

        $sql  = "SELECT e.id_estudio AS id, e.nombre, e.descripcion, e.observacion, c.nombre AS categoria, e.id_categoria_estudio FROM estudio e LEFT JOIN categoria_estudio c ON e.id_categoria_estudio = c.id_categoria_estudio $where $orderBy LIMIT :limite OFFSET :offset";
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

        $where .= " AND cc.id_estado = :id_estado";

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
     * También excluye cirugías cuyo nombre solo difiere en acentos de una que ya tiene combo.
     */
    public static function listarCirugiasSinComboSelect(): array {
        $pdo  = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $cmp = self::igualSinAcentos('c.nombre', 'c2.nombre');
            $sql = "SELECT c.id_cirugia_catalogo AS id, c.nombre
                    FROM catalogo_cirugia c
                    WHERE c.id_estado = :id_estado
                      AND NOT EXISTS (
                          SELECT 1
                          FROM combo_cirugia cc
                          JOIN catalogo_cirugia c2 ON c2.id_cirugia_catalogo = cc.id_cirugia_catalogo
                          WHERE cc.id_estado = :id_estado
                            AND $cmp
                      )
                    ORDER BY c.nombre";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_estado' => $idActivo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $cmp = self::igualSinAcentos('c.nombre', 'c2.nombre');
            $sql = "SELECT c.id_cirugia_catalogo AS id, c.nombre
                    FROM catalogo_cirugia c
                    WHERE NOT EXISTS (
                          SELECT 1
                          FROM combo_cirugia cc
                          JOIN catalogo_cirugia c2 ON c2.id_cirugia_catalogo = cc.id_cirugia_catalogo
                          WHERE $cmp
                    )
                    ORDER BY c.nombre";
            $stmt = $pdo->query($sql);
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
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("INSERT INTO combo_cirugia (id_cirugia_catalogo, id_estudio, id_estado) VALUES (:id_cirugia, :id_estudio, :id_estado) ON CONFLICT (id_cirugia_catalogo, id_estudio) DO UPDATE SET id_estado = :id_estado");
            $stmt->bindValue(':id_cirugia', $idCirugia, PDO::PARAM_INT);
            $stmt->bindValue(':id_estado', $idActivo, PDO::PARAM_INT);
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

    private static function existeRegistroActivo(string $tabla, string $columna, int $id, int $idActivo): bool {
        $stmt = conexionCircuitoQuirurgico()->prepare("SELECT 1 FROM $tabla WHERE $columna = :id AND id_estado = :id_estado LIMIT 1");
        $stmt->execute([':id' => $id, ':id_estado' => $idActivo]);
        return (bool)$stmt->fetchColumn();
    }

    public static function cirugiaEnUsoCombos(int $id): bool {
        $idActivo = id_estado('USUARIO', 'ACT');
        return self::existeRegistroActivo('combo_cirugia', 'id_cirugia_catalogo', $id, $idActivo);
    }
    public static function cirugiaEnUsoOrdenes(int $id): bool { return self::existeRegistro('orden_medica', 'id_cirugia_catalogo', $id); }
    public static function estudioEnUsoCombos(int $id): bool {
        $idActivo = id_estado('USUARIO', 'ACT');
        return self::existeRegistroActivo('combo_cirugia', 'id_estudio', $id, $idActivo);
    }
    public static function estudioEnUsoOrdenes(int $id): bool { return self::existeRegistro('orden_estudio', 'id_estudio', $id); }
    public static function categoriaEnUsoEstudios(int $id): bool { return self::existeRegistro('estudio', 'id_categoria_estudio', $id); }

    /**
     * Verifica si la cirugía tiene órdenes médicas activas (no canceladas, rechazadas ni eliminadas).
     */
    public static function cirugiaEnUsoOrdenesActivas(int $id): bool {
        $pdo = conexionCircuitoQuirurgico();
        $idCanc = id_estado('ORDEN', 'CANC');
        $idRech = id_estado('ORDEN', 'RECH');
        $idElim = id_estado('DATO', 'ELIM');

        $sql = "SELECT 1 FROM orden_medica WHERE id_cirugia_catalogo = :id";
        $params = [':id' => $id];

        $excluir = [];
        if ($idCanc) $excluir[] = $idCanc;
        if ($idRech) $excluir[] = $idRech;
        if ($idElim) $excluir[] = $idElim;

        if (!empty($excluir)) {
            $placeholders = [];
            foreach ($excluir as $i => $eid) {
                $pk = ':excluir_' . $i;
                $placeholders[] = $pk;
                $params[$pk] = $eid;
            }
            $sql .= " AND id_estado NOT IN (" . implode(',', $placeholders) . ")";
        }

        $sql .= " LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Eliminar una vinculación individual de combo (borrado lógico).
     */
    public static function eliminarCombo(int $idCirugia, int $idEstudio): bool {
        $idElim = id_estado('DATO', 'ELIM');
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE combo_cirugia SET id_estado = :est WHERE id_cirugia_catalogo = :id_c AND id_estudio = :id_e");
        return $stmt->execute([':est' => $idElim, ':id_c' => $idCirugia, ':id_e' => $idEstudio]);
    }

    /**
     * Eliminar todas las vinculaciones de un combo por cirugía (borrado lógico).
     */
    public static function eliminarCombosPorCirugia(int $idCirugia): bool {
        $idElim = id_estado('DATO', 'ELIM');
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE combo_cirugia SET id_estado = :est WHERE id_cirugia_catalogo = :id");
        return $stmt->execute([':est' => $idElim, ':id' => $idCirugia]);
    }

    /**
     * Actualizar combo completo: actualiza nombre de cirugía, elimina vinculaciones existentes e inserta nuevas.
     */
    public static function actualizarComboCompleto(int $idCirugia, string $nombreCirugia, array $estudios): bool {
        $pdo = conexionCircuitoQuirurgico();
        $pdo->beginTransaction();
        try {
            $stmt1 = $pdo->prepare("UPDATE catalogo_cirugia SET nombre = :n WHERE id_cirugia_catalogo = :id");
            $stmt1->execute([':n' => mb_strtoupper(trim($nombreCirugia), 'UTF-8'), ':id' => $idCirugia]);

            $idElim = id_estado('DATO', 'ELIM');
            $stmt2 = $pdo->prepare("UPDATE combo_cirugia SET id_estado = :est WHERE id_cirugia_catalogo = :id");
            $stmt2->execute([':est' => $idElim, ':id' => $idCirugia]);

            if (!empty($estudios)) {
                $idActivo = id_estado('USUARIO', 'ACT');
                $stmt3 = $pdo->prepare("INSERT INTO combo_cirugia (id_cirugia_catalogo, id_estudio, id_estado) VALUES (:id_c, :id_e, :id_est) ON CONFLICT (id_cirugia_catalogo, id_estudio) DO UPDATE SET id_estado = :id_est");
                $stmt3->bindValue(':id_c', $idCirugia, PDO::PARAM_INT);
                $stmt3->bindValue(':id_est', $idActivo, PDO::PARAM_INT);
                foreach ($estudios as $idEstudio) {
                    $stmt3->bindValue(':id_e', (int)$idEstudio, PDO::PARAM_INT);
                    $stmt3->execute();
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

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

    /**
     * Borrado lógico de categoría.
     */
    public static function eliminarCategoria(int $id): bool {
        $idElim = id_estado('DATO', 'ELIM');
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE categoria_estudio SET id_estado = :est WHERE id_categoria_estudio = :id");
        return $stmt->execute([':est' => $idElim, ':id' => $id]);
    }

    /* ----------------------------------------------------------------
       CRUD DE CATEGORIAS
       ---------------------------------------------------------------- */

    public static function listarCategorias() {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("SELECT id_categoria_estudio AS id, nombre, descripcion FROM categoria_estudio WHERE id_estado = :id_estado ORDER BY nombre");
            $stmt->execute([':id_estado' => $idActivo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stmt = $pdo->query("SELECT id_categoria_estudio AS id, nombre, descripcion FROM categoria_estudio ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public static function existeNombreCategoria(string $nombre, ?int $excluirId = null): bool {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $sql = "SELECT COUNT(*) FROM categoria_estudio WHERE " . self::igualSinAcentos('nombre', ':nombre') . " AND id_estado = :id_estado";
            if ($excluirId) $sql .= " AND id_categoria_estudio != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            $stmt->bindValue(':id_estado', $idActivo, PDO::PARAM_INT);
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $sql = "SELECT COUNT(*) FROM categoria_estudio WHERE " . self::igualSinAcentos('nombre', ':nombre');
            if ($excluirId) $sql .= " AND id_categoria_estudio != :excluir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            if ($excluirId) $stmt->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        }
    }

    public static function crearCategoria(string $nombre, string $descripcion) {
        $pdo = conexionCircuitoQuirurgico();
        try {
            $idActivo = id_estado('USUARIO', 'ACT');
            $stmt = $pdo->prepare("INSERT INTO categoria_estudio (nombre, descripcion, id_estado) VALUES (:n, :d, :est) RETURNING id_categoria_estudio");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':est' => $idActivo]) ? (int)$stmt->fetchColumn() : false;
        } catch (Exception $e) {
            $stmt = $pdo->prepare("INSERT INTO categoria_estudio (nombre, descripcion) VALUES (:n, :d) RETURNING id_categoria_estudio");
            return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion)]) ? (int)$stmt->fetchColumn() : false;
        }
    }

    public static function actualizarCategoria(int $id, string $nombre, string $descripcion): bool {
        $stmt = conexionCircuitoQuirurgico()->prepare("UPDATE categoria_estudio SET nombre = :n, descripcion = :d WHERE id_categoria_estudio = :id");
        return $stmt->execute([':n' => mb_strtoupper(trim($nombre), 'UTF-8'), ':d' => trim($descripcion), ':id' => $id]);
    }

}
?>
