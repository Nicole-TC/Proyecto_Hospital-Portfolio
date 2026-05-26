<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasUsers {
    
    // Método para verificar si un DNI ya existe en la tabla persona
    public static function existeDNI($dni) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT COUNT(*) as total FROM persona WHERE dni = :dni";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    // Método para crear una persona en nuestra base de datos PostgreSQL
    public static function crearPersona($datos) {
        $pdo = conexionCircuitoQuirurgico();
        
        $sql = "INSERT INTO persona (dni, nombre, apellido, fecha_nacimiento, email, telefono, direccion, codigo_postal, id_rol) 
                VALUES (:dni, :nombre, :apellido, :fecha_nacimiento, :email, :telefono, :direccion, :codigo_postal, :id_rol)
                RETURNING id_persona";
                
        $stmt = $pdo->prepare($sql);
        
        // Protegiendo contra inyecciones SQL usando bindParam (PDO)
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido', $datos['apellido']);
        $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':codigo_postal', $datos['codigo_postal']);
        $stmt->bindParam(':id_rol', $datos['id_rol']);
        
        if ($stmt->execute()) {
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$resultado['id_persona'];
        }
        return false;
    }

    // Método para crear un registro en la tabla profesional (solo id_persona según README)
    public static function crearProfesional($id_persona) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "INSERT INTO profesional (id_persona) VALUES (:id_p) RETURNING id_profesional";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_p', $id_persona);
        if ($stmt->execute()) {
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$resultado['id_profesional'];
        }
        return false;
    }

    // Método para la tabla intermedia profesional_especialidad
    public static function vincularEspecialidad($id_profesional, $id_especialidad) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "INSERT INTO profesional_especialidad (id_profesional, id_especialidad) VALUES (:id_p, :id_e)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_p', $id_profesional);
        $stmt->bindParam(':id_e', $id_especialidad);
        return $stmt->execute();
    }

    // Método para crear un registro en la tabla matricula (incluye id_especialidad según README)
    public static function crearMatricula($id_profesional, $id_especialidad, $numero, $tipo) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "INSERT INTO matricula (id_profesional, id_especialidad, numero, tipo) VALUES (:id_prof, :id_esp, :num, :tipo)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_prof', $id_profesional);
        $stmt->bindParam(':id_esp', $id_especialidad);
        $stmt->bindParam(':num', $numero);
        $stmt->bindParam(':tipo', $tipo);
        return $stmt->execute();
    }

    // Método para listar todas las personas registradas (con su rol) con paginación
    // Se excluyen los usuarios con rol 'Paciente' y se permite filtrar por DNI, Nombre o Apellido
    public static function listarPersonas($filtro = null, $valor = null, $pagina = 1, $limite = 10) {
        $pdo = conexionCircuitoQuirurgico();
        
        $offset = ($pagina - 1) * $limite;
        
        // Base de la consulta: JOIN con la tabla rol y exclusión de 'Paciente'
        $where = "WHERE (r.nombre IS NULL OR r.nombre != 'Paciente')";
        
        $params = [];
        if (!empty($filtro) && !empty($valor)) {
            $valorBusqueda = "%" . trim($valor) . "%";
            if ($filtro === 'dni') {
                $where .= " AND p.dni LIKE :valor";
            } elseif ($filtro === 'apellido') {
                $where .= " AND p.apellido ILIKE :valor";
            } elseif ($filtro === 'nombre') {
                $where .= " AND p.nombre ILIKE :valor";
            } elseif ($filtro === 'email') {
                $where .= " AND p.email ILIKE :valor";
            }
            $params[':valor'] = $valorBusqueda;
        }

        // 1. Contar total para el paginador
        $sqlCount = "SELECT COUNT(*) FROM persona p LEFT JOIN rol r ON p.id_rol = r.id_rol $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $key => $val) {
            $stmtCount->bindValue($key, $val);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        // 2. Obtener los registros de la página actual
        $sql = "SELECT p.id_persona, p.dni, p.nombre, p.apellido, p.email, p.telefono, 
                       r.nombre AS rol_nombre
                FROM persona p
                LEFT JOIN rol r ON p.id_rol = r.id_rol
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
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "usuarios" => $usuarios,
            "total" => $total
        ];
    }

    // Obtener datos completos de una persona para el formulario de edición
    public static function obtenerPersonaCompleta($id_persona) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT p.id_persona, p.dni, p.nombre, p.apellido, p.fecha_nacimiento,
                       p.email, p.telefono, p.direccion, p.codigo_postal, p.id_rol,
                       r.nombre AS rol_nombre,
                       prof.id_profesional,
                       pe.id_especialidad,
                       m.numero AS numero_matricula,
                       m.tipo   AS tipo_matricula
                FROM persona p
                LEFT JOIN rol r ON p.id_rol = r.id_rol
                LEFT JOIN profesional prof ON prof.id_persona = p.id_persona
                LEFT JOIN profesional_especialidad pe ON pe.id_profesional = prof.id_profesional
                LEFT JOIN matricula m ON m.id_profesional = prof.id_profesional
                WHERE p.id_persona = :id_persona
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar datos de una persona existente
    /**
     * Actualización parcial de una persona (solo los campos que cambiaron)
     * @param int $id_persona
     * @param array $campos Array asociativo ['columna' => 'valor']
     * @return bool
     */
    public static function actualizarPersonaParcial($id_persona, $campos) {
        if (empty($campos)) return true; // Nada que actualizar
        
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

    // Eliminar todos los datos de cirujano para una persona
    public static function eliminarDatosCirujano($id_persona) {
        $pdo = conexionCircuitoQuirurgico();

        // 1. Obtener id_profesional
        $stmt = $pdo->prepare("SELECT id_profesional FROM profesional WHERE id_persona = :id");
        $stmt->bindParam(':id', $id_persona, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return true; // No era cirujano, nada que desactivar

        $idProf = $row['id_profesional'];
        $idEstadoIna = id_estado('USUARIO', 'INA');

        // 2. Realizar la baja lógica id_estado
        $stmtUpdate = $pdo->prepare("UPDATE profesional SET id_estado = :id_estado WHERE id_profesional = :id_prof");
        $stmtUpdate->bindParam(':id_estado', $idEstadoIna, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':id_prof', $idProf, PDO::PARAM_INT);
        return $stmtUpdate->execute();
    }
    public static function desactivarPersona($id_persona) {
        $pdo = conexionCircuitoQuirurgico();

        // Buscar el estado INA de entidad USUARIO usando la función utilitaria
        $idEstadoIna = id_estado('USUARIO', 'INA');

        $stmt = $pdo->prepare(
            "UPDATE persona SET id_estado = :id_estado WHERE id_persona = :id_persona"
        );
        $stmt->bindParam(':id_estado',  $idEstadoIna,  PDO::PARAM_INT);
        $stmt->bindParam(':id_persona', $id_persona,   PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Método para obtener estadísticas de usuarios (excluyendo Pacientes)
    public static function obtenerEstadisticas() {
        $pdo = conexionCircuitoQuirurgico();
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(*) FILTER (WHERE r.nombre ILIKE '%admin%') as admins,
                    COUNT(*) FILTER (WHERE r.nombre ILIKE '%cirujano%') as cirujanos,
                    COUNT(*) FILTER (WHERE r.nombre ILIKE '%recepcionista%' OR r.nombre ILIKE '%admision%') as recepcionistas
                FROM persona p
                LEFT JOIN rol r ON p.id_rol = r.id_rol
                WHERE (r.nombre IS NULL OR r.nombre != 'Paciente')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
