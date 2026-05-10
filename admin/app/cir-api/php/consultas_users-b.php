<?php
require_once __DIR__ . '/../../../model/circuitoquirurgico.php';

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
