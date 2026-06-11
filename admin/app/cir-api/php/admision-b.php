<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

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
        if (!preg_match('/^\d{5,8}$/', $dni)) {
            $errores[] = 'El DNI debe contener entre 5 y 8 dígitos numéricos.';
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
                } elseif ((int)$parts[0] < 1900) {
                    $errores[] = 'El año debe ser 1900 o posterior.';
                } elseif ($fechaNacimiento > date('Y-m-d')) {
                    $errores[] = 'La fecha de nacimiento no puede ser posterior al día de hoy.';
                }
            }

            if (!preg_match('/^[a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ\s\.,#\-\/]{5,255}$/', $direccion)) {
                $errores[] = 'El formato de la dirección no es válido.';
            }

            if (!preg_match('/^[a-zA-Z0-9]{1,8}$/', $codigoPostal)) {
                $errores[] = 'El código postal debe tener hasta 8 caracteres (letras y números).';
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

    public static function obtenerEstudiosPorCirugia() {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT cc.nombre AS cirugia, e.id_estudio, e.nombre AS estudio, e.observacion
                FROM catalogo_cirugia cc
                JOIN combo_cirugia co ON co.id_cirugia_catalogo = cc.id_cirugia_catalogo
                JOIN estudio e ON e.id_estudio = co.id_estudio
                ORDER BY cc.nombre, e.nombre";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $estudiosPorCirugia = [];
        foreach ($rows as $row) {
            $cirugia = $row['cirugia'];
            $estudiosPorCirugia[$cirugia][] = [
                'id_estudio' => $row['id_estudio'],
                'nombre' => $row['estudio'],
                'observacion' => $row['observacion']
            ];
        }
        return $estudiosPorCirugia;
    }

    public static function obtenerCirujanos() {
        $pdo = conexionCircuitoQuirurgico();

        $sqlRol = "SELECT id_rol FROM rol WHERE LOWER(nombre) = 'cirujano' LIMIT 1";
        $stmtRol = $pdo->query($sqlRol);
        $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);

        if (!$idRol) {
            return [];
        }

        $idEstado = id_estado('USUARIO', 'ACT');

        $sqlPersonas = "SELECT p.id_persona, prof.id_profesional, p.nombre, p.apellido 
                        FROM persona p
                        INNER JOIN profesional prof ON p.id_persona = prof.id_persona
                        WHERE p.id_rol = :id_rol";
        $params = [':id_rol' => $idRol];

        if ($idEstado) {
            $sqlPersonas .= " AND prof.id_estado = :id_estado";
            $params[':id_estado'] = $idEstado;
        }

        $sqlPersonas .= " ORDER BY p.apellido, p.nombre";
        $stmtPersonas = $pdo->prepare($sqlPersonas);
        $stmtPersonas->execute($params);
        return $stmtPersonas->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function procesarAdmision($datos, $paciente_existente) {
        $pdo = conexionCircuitoQuirurgico();
        $pdo->beginTransaction();

        try {
            // 1. Obtener id_rol de 'Paciente'
            $stmtRol = $pdo->query("SELECT id_rol FROM rol WHERE nombre = 'Paciente' LIMIT 1");
            $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);

            if (!$idRol) {
                throw new Exception("No se encontró el rol de paciente.");
            }

            $id_persona = null;

            // 2. Insertar o actualizar paciente
            if ($paciente_existente) {
                $id_persona = $paciente_existente['id_persona'];
                $stmtUpdate = $pdo->prepare("UPDATE persona SET obra_social = :obra_social WHERE id_persona = :id_persona");
                $stmtUpdate->execute([
                    ':obra_social' => $datos['obra_social'],
                    ':id_persona' => $id_persona
                ]);
            } else {
                $sqlPersona = "INSERT INTO persona (dni, nombre, apellido, fecha_nacimiento, email, telefono, direccion, codigo_postal, obra_social, id_rol) 
                               VALUES (:dni, :nombre, :apellido, :fecha_nacimiento, :email, :telefono, :direccion, :codigo_postal, :obra_social, :id_rol)
                               RETURNING id_persona";
                $stmtPersona = $pdo->prepare($sqlPersona);
                $stmtPersona->execute([
                    ':dni' => $datos['dni'],
                    ':nombre' => $datos['nombre'],
                    ':apellido' => $datos['apellido'],
                    ':fecha_nacimiento' => $datos['fecha_nacimiento'],
                    ':email' => $datos['email'],
                    ':telefono' => $datos['telefono'],
                    ':direccion' => $datos['direccion'],
                    ':codigo_postal' => $datos['codigo_postal'],
                    ':obra_social' => $datos['obra_social'],
                    ':id_rol' => $idRol
                ]);
                $id_persona = $stmtPersona->fetch(PDO::FETCH_COLUMN);
            }

            // 3. Obtener o crear persona_asiste
            $stmtAsiste = $pdo->prepare("SELECT id_persona_asiste FROM persona_asiste WHERE id_persona = :id_persona");
            $stmtAsiste->execute([':id_persona' => $id_persona]);
            $id_persona_asiste = $stmtAsiste->fetch(PDO::FETCH_COLUMN);

            if (!$id_persona_asiste) {
                $stmtInsertAsiste = $pdo->prepare("INSERT INTO persona_asiste (id_persona) VALUES (:id_persona) RETURNING id_persona_asiste");
                $stmtInsertAsiste->execute([':id_persona' => $id_persona]);
                $id_persona_asiste = $stmtInsertAsiste->fetch(PDO::FETCH_COLUMN);
            }

            // 4. Obtener id_estado para ORDEN (PEND)
            $id_estado_orden = id_estado('ORDEN', 'PEND');
            if (!$id_estado_orden) {
                throw new Exception("No se encontró el estado PEND para ORDEN.");
            }

            // 5. Obtener id_cirugia_catalogo
            $stmtCirugia = $pdo->prepare("SELECT id_cirugia_catalogo FROM catalogo_cirugia WHERE nombre = :nombre");
            $stmtCirugia->execute([':nombre' => $datos['tipoCirugia']]);
            $id_cirugia = $stmtCirugia->fetch(PDO::FETCH_COLUMN);
            if (!$id_cirugia) {
                throw new Exception("No se encontró la cirugía en el catálogo.");
            }

            // 6. Obtener id_profesional del cirujano
            $stmtProf = $pdo->prepare("SELECT id_profesional FROM profesional WHERE id_persona = :id_persona");
            $stmtProf->execute([':id_persona' => $datos['cirujano']]);
            $id_profesional = $stmtProf->fetch(PDO::FETCH_COLUMN);
            if (!$id_profesional) {
                throw new Exception("El profesional seleccionado no es válido.");
            }

            // 7. Insertar orden_medica
            $sqlOrden = "INSERT INTO orden_medica (id_persona_asiste, id_profesional, id_cirugia_catalogo, id_estado, fecha_orden)
                         VALUES (:id_persona_asiste, :id_profesional, :id_cirugia, :id_estado, CURRENT_DATE)
                         RETURNING id_orden";
            $stmtInsertOrden = $pdo->prepare($sqlOrden);
            $stmtInsertOrden->execute([
                ':id_persona_asiste' => $id_persona_asiste,
                ':id_profesional' => $id_profesional,
                ':id_cirugia' => $id_cirugia,
                ':id_estado' => $id_estado_orden
            ]);
            $id_orden = $stmtInsertOrden->fetch(PDO::FETCH_COLUMN);

            // 8. Obtener id_estado para ADMISION (EN_ADMISION)
            $id_estado_admision = id_estado('ADMISION', 'EN_ADMISION');
            if (!$id_estado_admision) {
                throw new Exception("No se encontró el estado EN_ADMISION para ADMISION.");
            }

            // 9. Insertar admision
            $sqlAdmision = "INSERT INTO admision (id_orden, id_estado, fecha_inicio)
                            VALUES (:id_orden, :id_estado, CURRENT_TIMESTAMP)
                            RETURNING id_admision";
            $stmtInsertAdmision = $pdo->prepare($sqlAdmision);
            $stmtInsertAdmision->execute([
                ':id_orden' => $id_orden,
                ':id_estado' => $id_estado_admision
            ]);
            
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
