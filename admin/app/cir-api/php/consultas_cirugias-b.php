<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasCirugias {

    /**
     * Listar todos los estudios disponibles para mostrar en el paso 2 del formulario.
     */
    public static function listarEstudios() {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT id_estudio AS id, nombre, descripcion, observacion AS observaciones FROM estudio ORDER BY nombre";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si ya existe una cirugía con ese nombre en el catálogo.
     */
    public static function existeNombreCirugia($nombre) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT COUNT(*) FROM catalogo_cirugia WHERE UPPER(nombre) = UPPER(:nombre)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre', trim($nombre));
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Verificar si ya existe un estudio con ese nombre. Retorna su ID si existe.
     */
    public static function existeNombreEstudio($nombre) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "SELECT id_estudio FROM estudio WHERE UPPER(nombre) = UPPER(:nombre)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre', trim($nombre));
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Crear un nuevo estudio prequirúrgico de forma independiente.
     */
    public static function crearEstudio($nombre, $descripcion, $observacion) {
        $pdo = conexionCircuitoQuirurgico();
        $sql = "INSERT INTO estudio (nombre, descripcion, observacion) VALUES (:nombre, :descripcion, :observacion) RETURNING id_estudio";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
        $stmt->bindValue(':descripcion', trim($descripcion));
        $stmt->bindValue(':observacion', trim($observacion));
        if ($stmt->execute()) {
            return (int)$stmt->fetchColumn();
        }
        return false;
    }

    /**
     * Guardar cirugía y vincular estudios seleccionados.
     */
    public static function guardarCirugia($nombre, $descripcion, $estudios) {
        $pdo = conexionCircuitoQuirurgico();
        
        try {
            $pdo->beginTransaction();
            
            // 1. Insertar cirugía
            $sqlCirugia = "INSERT INTO catalogo_cirugia (nombre, descripcion) VALUES (:nombre, :descripcion) RETURNING id_cirugia_catalogo";
            $stmtCirugia = $pdo->prepare($sqlCirugia);
            $stmtCirugia->bindValue(':nombre', mb_strtoupper(trim($nombre), 'UTF-8'));
            $stmtCirugia->bindValue(':descripcion', trim($descripcion));
            $stmtCirugia->execute();
            $id_cirugia = (int)$stmtCirugia->fetchColumn();
            
            // 2. Vincular estudios
            if (!empty($estudios) && is_array($estudios)) {
                $sqlCombo = "INSERT INTO combo_cirugia (id_cirugia_catalogo, id_estudio) VALUES (:id_cirugia, :id_estudio)";
                $stmtCombo = $pdo->prepare($sqlCombo);
                
                $vinculados = [];
                
                foreach ($estudios as $id_estudio) {
                    // Evitar insertar duplicados en combo_cirugia si el array tiene IDs repetidos
                    if (!in_array($id_estudio, $vinculados)) {
                        $stmtCombo->bindValue(':id_cirugia', $id_cirugia, PDO::PARAM_INT);
                        $stmtCombo->bindValue(':id_estudio', $id_estudio, PDO::PARAM_INT);
                        $stmtCombo->execute();
                        $vinculados[] = $id_estudio;
                    }
                }
            }
            
            $pdo->commit();
            return $id_cirugia;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
?>
