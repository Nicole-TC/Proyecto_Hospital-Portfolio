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

class ConsultasPacientes {

    public static function obtenerIdRolPaciente() {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT id_rol FROM rol WHERE LOWER(nombre) = 'paciente' LIMIT 1";
        $stmt = $pdo->query($sql);
        $id = $stmt->fetch(PDO::FETCH_COLUMN);
        return $id ? (int)$id : 4;
    }

    public static function obtenerIdEstadoInactivo() {
        $pdo = conexionCircuitoQuirurgico();
        $stmt = $pdo->prepare("SELECT id_estado FROM estado WHERE entidad = 'USUARIO' AND codigo = 'INA' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_estado'] : null;
    }

    public static function listarPacientes($filtro = null, $valor = null, $pagina = 1, $limite = 10) {
        $pdo = conexionCircuitoQuirurgico();
        $idRolPaciente = self::obtenerIdRolPaciente();
        $idEstadoIna = self::obtenerIdEstadoInactivo();
        $offset = ($pagina - 1) * $limite;

        $where = "WHERE p.id_rol = :id_rol";
        $params = [':id_rol' => $idRolPaciente];

        if ($idEstadoIna) {
            $where .= " AND (p.id_estado IS NULL OR p.id_estado != :id_estado_ina)";
            $params[':id_estado_ina'] = $idEstadoIna;
        }

        if (!empty($filtro) && !empty($valor)) {
            $valorBusqueda = "%" . trim($valor) . "%";
            if ($filtro === 'dni') {
                $where .= " AND p.dni LIKE :valor";
            } elseif ($filtro === 'apellido') {
                $where .= " AND p.apellido ILIKE :valor";
            } elseif ($filtro === 'nombre') {
                $where .= " AND p.nombre ILIKE :valor";
            } elseif ($filtro === 'telefono') {
                $where .= " AND p.telefono LIKE :valor";
            }
            $params[':valor'] = $valorBusqueda;
        }

        $sqlCount = "SELECT COUNT(*) FROM persona p $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $key => $val) {
            $stmtCount->bindValue($key, $val);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $sql = "SELECT p.id_persona, p.dni, p.nombre, p.apellido, p.email, p.telefono,
                       p.fecha_nacimiento, p.direccion, p.codigo_postal, p.id_estado
                FROM persona p
                $where
                ORDER BY p.id_persona DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "pacientes" => $pacientes,
            "total" => $total
        ];
    }

    public static function obtenerEstadisticas() {
        $pdo = conexionCircuitoQuirurgico();
        $idRolPaciente = self::obtenerIdRolPaciente();
        $idEstadoIna = self::obtenerIdEstadoInactivo();

        $where = "id_rol = :id_rol";
        $params = [':id_rol' => $idRolPaciente];
        if ($idEstadoIna) {
            $where .= " AND (id_estado IS NULL OR id_estado != :id_estado_ina)";
            $params[':id_estado_ina'] = $idEstadoIna;
        }

        $sql = "SELECT COUNT(*) as total FROM persona WHERE $where";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $total = (int)$stmt->fetchColumn();

        return [
            "total" => $total,
            "en_espera" => 0,
            "en_curso" => 0,
            "completados" => 0
        ];
    }

    public static function obtenerPacienteCompleto($id_persona) {
        $pdo = conexionCircuitoQuirurgico();
        $idRolPaciente = self::obtenerIdRolPaciente();

        $sql = "SELECT p.id_persona, p.dni, p.nombre, p.apellido, p.fecha_nacimiento,
                       p.email, p.telefono, p.direccion, p.codigo_postal, p.id_rol
                FROM persona p
                WHERE p.id_persona = :id_persona AND p.id_rol = :id_rol
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_persona', $id_persona, PDO::PARAM_INT);
        $stmt->bindValue(':id_rol', $idRolPaciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function actualizarPacienteParcial($id_persona, $campos) {
        if (empty($campos)) return true;

        $pdo = conexionCircuitoQuirurgico();
        $sets = [];
        $params = [':id_persona' => $id_persona];

        foreach ($campos as $columna => $valor) {
            $sets[] = "$columna = :$columna";
            $params[":$columna"] = $valor;
        }

        $sql = "UPDATE persona SET " . implode(", ", $sets) . " WHERE id_persona = :id_persona";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function desactivarPaciente($id_persona) {
        $pdo = conexionCircuitoQuirurgico();
        $idRolPaciente = self::obtenerIdRolPaciente();

        $stmt = $pdo->prepare(
            "SELECT id_estado FROM estado WHERE entidad = 'USUARIO' AND codigo = 'INA' LIMIT 1"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new Exception("No se encontró el estado INACTIVO en la tabla estado.");
        }

        $idEstadoIna = (int)$row['id_estado'];

        $sql = "UPDATE persona SET id_estado = :id_estado WHERE id_persona = :id_persona AND id_rol = :id_rol";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_estado', $idEstadoIna, PDO::PARAM_INT);
        $stmt->bindValue(':id_persona', $id_persona, PDO::PARAM_INT);
        $stmt->bindValue(':id_rol', $idRolPaciente, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
