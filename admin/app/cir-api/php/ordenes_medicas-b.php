<?php
$envPath = __DIR__ . '/../../../../.env';
if (empty(getenv('DB_HOST')) && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasOrdenesMedicas
{
    private static function idEstadoOrden($codigo)
    {
        return id_estado('ORDEN', $codigo);
    }

    public static function listarOrdenes($filtro = null, $valor = null, $pagina = 1, $limite = 10, $incluirCanceladas = false)
    {
        $pdo = conexionCircuitoQuirurgico();
        $offset = ($pagina - 1) * $limite;
        $idCanc = self::idEstadoOrden('CANC');

        $where = 'WHERE 1=1';
        $params = [];

        if (!$incluirCanceladas && $idCanc) {
            $where .= ' AND om.id_estado != :id_canc';
            $params[':id_canc'] = $idCanc;
        }

        if (!empty($filtro) && $valor !== null && $valor !== '') {
            $v = '%' . trim($valor) . '%';
            if ($filtro === 'dni') {
                $where .= ' AND p.dni LIKE :valor';
                $params[':valor'] = $v;
            } elseif ($filtro === 'apellido') {
                $where .= ' AND p.apellido ILIKE :valor';
                $params[':valor'] = $v;
            } elseif ($filtro === 'estado') {
                $where .= ' AND e.codigo = :valor_exacto';
                $params[':valor_exacto'] = strtoupper(trim($valor));
            } elseif ($filtro === 'id_orden') {
                $where .= ' AND om.id_orden = :id_orden';
                $params[':id_orden'] = (int)$valor;
            }
        }

        $from = "
            FROM orden_medica om
            INNER JOIN estado e ON e.id_estado = om.id_estado
            INNER JOIN persona_asiste pa ON pa.id_persona_asiste = om.id_persona_asiste
            INNER JOIN persona p ON p.id_persona = pa.id_persona
            LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
            LEFT JOIN profesional pr ON pr.id_profesional = om.id_profesional
            LEFT JOIN persona pc ON pc.id_persona = pr.id_persona
        ";

        $sqlCount = "SELECT COUNT(*) $from $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $val) {
            $stmtCount->bindValue($k, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $sql = "
            SELECT om.id_orden, om.fecha_orden, om.observaciones,
                   e.codigo AS estado_codigo, e.descripcion AS estado_descripcion,
                   p.dni, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                   cc.nombre AS cirugia, cc.id_cirugia_catalogo,
                   pr.id_profesional,
                   TRIM(COALESCE(pc.apellido, '') || ', ' || COALESCE(pc.nombre, '')) AS cirujano
            $from
            $where
            ORDER BY om.id_orden DESC
            LIMIT :limite OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $val) {
            $stmt->bindValue($k, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'ordenes' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
        ];
    }

    public static function obtenerOrden($id_orden)
    {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "
            SELECT om.id_orden, om.id_persona_asiste, om.id_profesional, om.id_cirugia_catalogo,
                   om.fecha_orden, om.observaciones, om.id_estado,
                   e.codigo AS estado_codigo, e.descripcion AS estado_descripcion,
                   p.dni, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                   cc.nombre AS cirugia
            FROM orden_medica om
            INNER JOIN estado e ON e.id_estado = om.id_estado
            INNER JOIN persona_asiste pa ON pa.id_persona_asiste = om.id_persona_asiste
            INNER JOIN persona p ON p.id_persona = pa.id_persona
            LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
            WHERE om.id_orden = :id_orden
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_orden', $id_orden, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function obtenerCatalogoCirugias()
    {
        $pdo = conexionCircuitoQuirurgico();
        $stmt = $pdo->query('SELECT id_cirugia_catalogo, nombre FROM catalogo_cirugia ORDER BY nombre');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerCirujanos()
    {
        $pdo = conexionCircuitoQuirurgico();
        $stmtRol = $pdo->query("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'cirujano' LIMIT 1");
        $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);
        if (!$idRol) {
            return [];
        }

        $idEstado = id_estado('USUARIO', 'ACT');
        $sql = "SELECT pr.id_profesional, p.nombre, p.apellido
                FROM persona p
                INNER JOIN profesional pr ON p.id_persona = pr.id_persona
                WHERE p.id_rol = :id_rol";
        $params = [':id_rol' => $idRol];
        if ($idEstado) {
            $sql .= ' AND pr.id_estado = :id_estado';
            $params[':id_estado'] = $idEstado;
        }
        $sql .= ' ORDER BY p.apellido, p.nombre';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function actualizarOrden($id_orden, $datos)
    {
        $orden = self::obtenerOrden($id_orden);
        if (!$orden) {
            throw new Exception('La orden médica no existe.');
        }
        if ($orden['estado_codigo'] === 'CANC') {
            throw new Exception('No se puede editar una orden cancelada.');
        }

        $idCanc = self::idEstadoOrden('CANC');
        if ($idCanc && (int)$orden['id_estado'] === $idCanc) {
            throw new Exception('No se puede editar una orden cancelada.');
        }

        $id_profesional = filter_var($datos['id_profesional'] ?? '', FILTER_VALIDATE_INT);
        $id_cirugia     = filter_var($datos['id_cirugia_catalogo'] ?? '', FILTER_VALIDATE_INT);
        $fecha_orden    = trim($datos['fecha_orden'] ?? '');
        $observaciones  = trim($datos['observaciones'] ?? '');

        if (!$id_profesional || !$id_cirugia) {
            throw new Exception('Cirujano y cirugía son obligatorios.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_orden)) {
            throw new Exception('La fecha de la orden no es válida.');
        }

        $pdo = conexionCircuitoQuirurgico();

        $stmtProf = $pdo->prepare('SELECT id_profesional FROM profesional WHERE id_profesional = :id LIMIT 1');
        $stmtProf->execute([':id' => $id_profesional]);
        if (!$stmtProf->fetchColumn()) {
            throw new Exception('El profesional seleccionado no es válido.');
        }

        $stmtCir = $pdo->prepare('SELECT id_cirugia_catalogo FROM catalogo_cirugia WHERE id_cirugia_catalogo = :id LIMIT 1');
        $stmtCir->execute([':id' => $id_cirugia]);
        if (!$stmtCir->fetchColumn()) {
            throw new Exception('La cirugía seleccionada no es válida.');
        }

        $sql = "UPDATE orden_medica
                SET id_profesional = :id_profesional,
                    id_cirugia_catalogo = :id_cirugia_catalogo,
                    fecha_orden = :fecha_orden,
                    observaciones = :observaciones
                WHERE id_orden = :id_orden";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_profesional'      => $id_profesional,
            ':id_cirugia_catalogo' => $id_cirugia,
            ':fecha_orden'         => $fecha_orden,
            ':observaciones'       => $observaciones,
            ':id_orden'            => $id_orden,
        ]);
    }

    public static function cancelarOrden($id_orden)
    {
        $orden = self::obtenerOrden($id_orden);
        if (!$orden) {
            throw new Exception('La orden médica no existe.');
        }
        if ($orden['estado_codigo'] === 'CANC') {
            throw new Exception('La orden ya está cancelada.');
        }
        if ($orden['estado_codigo'] === 'FIN') {
            throw new Exception('No se puede cancelar una orden finalizada.');
        }

        $idCanc = self::idEstadoOrden('CANC');
        if (!$idCanc) {
            throw new Exception('No se encontró el estado CANC para órdenes.');
        }

        $pdo = conexionCircuitoQuirurgico();
        $stmt = $pdo->prepare('UPDATE orden_medica SET id_estado = :id_estado WHERE id_orden = :id_orden');
        return $stmt->execute([
            ':id_estado' => $idCanc,
            ':id_orden'  => $id_orden,
        ]);
    }
}
