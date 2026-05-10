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
    
    // Método para crear una persona en nuestra base de datos PostgreSQL
    public static function crearPersona($datos) {
        $pdo = conexionCircuitoQuirurgico();
        
        $sql = "INSERT INTO persona (dni, nombre, apellido, fecha_nacimiento, email, telefono, direccion) 
                VALUES (:dni, :nombre, :apellido, :fecha_nacimiento, :email, :telefono, :direccion)
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
        
        if ($stmt->execute()) {
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['id_persona'];
        }
        return false;
    }

    // Método para listar todas las personas registradas
    public static function listarPersonas() {
        $pdo = conexionCircuitoQuirurgico();
        
        // Seleccionamos los datos principales para mostrarlos en la tabla
        $sql = "SELECT id_persona, nombre, apellido, email, telefono FROM persona ORDER BY id_persona DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>
