<?php
// Cargar variables de entorno antes de llamar al archivo de conexión
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

    // Método para listar todas las personas registradas (con su rol)
    public static function listarPersonas() {
        $pdo = conexionCircuitoQuirurgico();
        
        // JOIN con la tabla rol para obtener el nombre del rol asignado
        $sql = "SELECT p.id_persona, p.dni, p.nombre, p.apellido, p.email, p.telefono, 
                       r.nombre AS rol_nombre
                FROM persona p
                LEFT JOIN rol r ON p.id_rol = r.id_rol
                ORDER BY p.id_persona DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>
