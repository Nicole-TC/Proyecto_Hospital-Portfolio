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

class ConsultasAdmision {

    public static function buscarPaciente($dni) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT * FROM persona WHERE dni = :dni";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: false;
    }

    public static function validarDatos($input, $paciente_existente) {
        $errores = [];

        $dni = $input['dni'] ?? '';
        if (!preg_match('/^\d{7,8}$/', $dni)) {
            $errores[] = 'El DNI debe contener entre 7 y 8 dígitos numéricos.';
        }
        $tipoCirugia = $input['tipoCirugia'] ?? '';
        if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{3,100}$/', $tipoCirugia)) {
            $errores[] = 'El tipo de cirugía no es válido.';
        }

        if (!$paciente_existente) {
            $apellido = $input['apellido'] ?? '';
            $nombre = $input['nombre'] ?? '';
            $fechaNacimiento = $input['fechaNacimiento'] ?? '';
            $direccion = $input['direccion'] ?? '';
            $codigoPostal = $input['codigoPostal'] ?? '';
            $email = $input['email'] ?? '';
            $telefono = $input['telefono'] ?? '';

            $solo_letras = '/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,100}$/';

            if (!preg_match($solo_letras, $apellido)) {
                $errores[] = 'El apellido solo debe contener letras (mín. 2).';
            }

            if (!preg_match($solo_letras, $nombre)) {
                $errores[] = 'El nombre solo debe contener letras (mín. 2).';
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
                $errores[] = 'El formato de la fecha de nacimiento no es válido.';
            } else {
                $parts = explode('-', $fechaNacimiento);
                if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
                    $errores[] = 'La fecha de nacimiento no es una fecha real.';
                } elseif ($fechaNacimiento > date('Y-m-d')) {
                    $errores[] = 'La fecha de nacimiento no puede ser posterior al día de hoy.';
                }
            }

            if (!preg_match('/^[a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ\s\.,#\-\/]{5,255}$/', $direccion)) {
                $errores[] = 'El formato de la dirección no es válido.';
            }

            if (!preg_match('/^\d{4}$/', $codigoPostal)) {
                $errores[] = 'El código postal debe tener 4 dígitos.';
            }

            if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
                $errores[] = 'El formato del email no es válido.';
            }

            if (!preg_match('/^[\d\s\-\(\)\+]{7,20}$/', $telefono)) {
                $errores[] = 'El formato del teléfono no es válido.';
            }
        }

        return $errores;
    }

    public static function obtenerCatalogoCirugias() {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT nombre FROM catalogo_cirugia ORDER BY nombre";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function obtenerCirujanos() {
        $pdo = conexionCircuitoQuirurgico();

        $sqlRol = "SELECT id_rol FROM rol WHERE LOWER(nombre) = 'cirujano' LIMIT 1";
        $stmtRol = $pdo->query($sqlRol);
        $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);

        if (!$idRol) {
            return [];
        }

        $sqlPersonas = "SELECT id_persona, nombre, apellido FROM persona WHERE id_rol = :id_rol ORDER BY apellido, nombre";
        $stmtPersonas = $pdo->prepare($sqlPersonas);
        $stmtPersonas->bindParam(':id_rol', $idRol, PDO::PARAM_INT);
        $stmtPersonas->execute();
        return $stmtPersonas->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function registrarAdmision($datos) {
        $pdo = conexionCircuitoQuirurgico();

        $sql = "INSERT INTO persona (dni, nombre, apellido, fecha_nacimiento, email, telefono, direccion, codigo_postal) 
                VALUES (:dni, :nombre, :apellido, :fecha_nacimiento, :email, :telefono, :direccion, :codigo_postal)
                RETURNING id_persona";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido', $datos['apellido']);
        $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':codigo_postal', $datos['codigo_postal']);

        if ($stmt->execute()) {
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['id_persona'];
        }
        return false;
    }
}
