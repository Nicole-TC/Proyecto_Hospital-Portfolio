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
  private static function idOrden($codigo)
  {
    return id_estado('ORDEN', $codigo);
  }

  private static function ordenTieneEstado($idEstadoOrden, $codigo)
  {
    $id = self::idOrden($codigo);
    return $id !== null && (int)$idEstadoOrden === $id;
  }

  private static function idEstadoEstudioPendiente(PDO $pdo)
  {
    $stmt = $pdo->query("SELECT id_estado FROM estado WHERE codigo = 'PEND' LIMIT 1");
    $id = $stmt->fetchColumn();
    return $id ? (int)$id : null;
  }

  public static function listarOrdenes($filtro = null, $valor = null, $pagina = 1, $limite = 10, $listarTodos = false, $incluirCanceladas = false)
  {
    if (!$listarTodos && (empty($filtro) || $valor === null || trim((string)$valor) === '')) {
      throw new Exception('Debe ingresar un criterio de búsqueda o solicitar el listado completo.');
    }

    $pdo = conexionCircuitoQuirurgico();
    $offset = ($pagina - 1) * $limite;
    $idRech = self::idOrden('RECH');
    $idCanc = self::idOrden('CANC');

    $where = 'WHERE 1=1';
    $params = [];

    if ($idRech) {
      $where .= ' AND om.id_estado != :id_rech';
      $params[':id_rech'] = $idRech;
    }

    if (!$incluirCanceladas && $idCanc) {
      $where .= ' AND om.id_estado != :id_canc';
      $params[':id_canc'] = $idCanc;
    }

    if (!$listarTodos && !empty($filtro) && $valor !== null && trim((string)$valor) !== '') {
      $v = '%' . trim($valor) . '%';
      if ($filtro === 'dni') {
        $where .= ' AND p.dni LIKE :valor';
        $params[':valor'] = $v;
      } elseif ($filtro === 'apellido') {
        $where .= ' AND p.apellido ILIKE :valor';
        $params[':valor'] = $v;
      } elseif ($filtro === 'estado') {
        $idEstadoFiltro = id_estado('ORDEN', strtoupper(trim($valor)));
        if (!$idEstadoFiltro) {
          throw new Exception('El código de estado ingresado no es válido.');
        }
        $where .= ' AND om.id_estado = :id_estado_filtro';
        $params[':id_estado_filtro'] = $idEstadoFiltro;
      } elseif ($filtro === 'id_orden') {
        $where .= ' AND om.id_orden = :id_orden';
        $params[':id_orden'] = (int)$valor;
      } else {
        throw new Exception('El tipo de filtro no es válido.');
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
    $idRech = self::idOrden('RECH');

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
        ";
    if ($idRech) {
      $sql .= ' AND om.id_estado != :id_rech';
    }
    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_orden', $id_orden, PDO::PARAM_INT);
    if ($idRech) {
      $stmt->bindValue(':id_rech', $idRech, PDO::PARAM_INT);
    }
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

  private static function validarOrdenEditable(array $orden)
  {
    if (self::ordenTieneEstado($orden['id_estado'], 'RECH')) {
      throw new Exception('La orden no está disponible.');
    }
    if (self::ordenTieneEstado($orden['id_estado'], 'CANC')) {
      throw new Exception('No se puede editar una orden cancelada.');
    }
    if (self::ordenTieneEstado($orden['id_estado'], 'FIN')) {
      throw new Exception('No se puede editar una orden finalizada.');
    }
  }

  private static function sincronizarOrdenEstudio(PDO $pdo, $id_orden, $id_cirugia_catalogo)
  {
    $idEstadoPend = self::idEstadoEstudioPendiente($pdo);
    if (!$idEstadoPend) {
      throw new Exception('No se encontró el estado pendiente para estudios.');
    }

    $sqlDelete = "
            DELETE FROM orden_estudio oe
            WHERE oe.id_orden = :id_orden
              AND oe.id_estudio NOT IN (
                  SELECT c.id_estudio FROM combo_cirugia c
                  WHERE c.id_cirugia_catalogo = :id_cirugia
              )
              AND NOT EXISTS (
                  SELECT 1 FROM resultado_estudio re
                  WHERE re.id_orden = oe.id_orden AND re.id_estudio = oe.id_estudio
              )
        ";
    $stmtDel = $pdo->prepare($sqlDelete);
    $stmtDel->execute([
      ':id_orden'   => $id_orden,
      ':id_cirugia' => $id_cirugia_catalogo,
    ]);

    $sqlInsert = "
            INSERT INTO orden_estudio (id_orden, id_estudio, id_estado)
            SELECT :id_orden, c.id_estudio, :id_estado
            FROM combo_cirugia c
            WHERE c.id_cirugia_catalogo = :id_cirugia
              AND NOT EXISTS (
                  SELECT 1 FROM orden_estudio oe
                  WHERE oe.id_orden = :id_orden_chk AND oe.id_estudio = c.id_estudio
              )
        ";
    $stmtIns = $pdo->prepare($sqlInsert);
    $stmtIns->execute([
      ':id_orden'     => $id_orden,
      ':id_orden_chk' => $id_orden,
      ':id_cirugia'   => $id_cirugia_catalogo,
      ':id_estado'    => $idEstadoPend,
    ]);
  }

  public static function actualizarOrden($id_orden, $datos)
  {
    $orden = self::obtenerOrden($id_orden);
    if (!$orden) {
      throw new Exception('La orden médica no existe o no está disponible.');
    }
    self::validarOrdenEditable($orden);

    $id_profesional = filter_var($datos['id_profesional'] ?? '', FILTER_VALIDATE_INT);
    $id_cirugia     = filter_var($datos['id_cirugia_catalogo'] ?? '', FILTER_VALIDATE_INT);
    $observaciones  = trim($datos['observaciones'] ?? '');

    if (!$id_profesional || !$id_cirugia) {
      throw new Exception('Cirujano y cirugía son obligatorios.');
    }
    if (mb_strlen($observaciones) > 2000) {
      throw new Exception('Las observaciones no pueden superar los 2000 caracteres.');
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

    $cirugiaCambio = (int)$orden['id_cirugia_catalogo'] !== (int)$id_cirugia;

    $pdo->beginTransaction();
    try {
      $sql = "UPDATE orden_medica
                    SET id_profesional = :id_profesional,
                        id_cirugia_catalogo = :id_cirugia_catalogo,
                        observaciones = :observaciones
                    WHERE id_orden = :id_orden";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':id_profesional'      => $id_profesional,
        ':id_cirugia_catalogo' => $id_cirugia,
        ':observaciones'       => $observaciones,
        ':id_orden'            => $id_orden,
      ]);

      if ($cirugiaCambio) {
        self::sincronizarOrdenEstudio($pdo, $id_orden, $id_cirugia);
      }

      $pdo->commit();
      return true;
    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function cancelarOrden($id_orden)
  {
    $orden = self::obtenerOrden($id_orden);
    if (!$orden) {
      throw new Exception('La orden médica no existe o no está disponible.');
    }
    if (self::ordenTieneEstado($orden['id_estado'], 'CANC')) {
      throw new Exception('La orden ya está cancelada.');
    }
    if (self::ordenTieneEstado($orden['id_estado'], 'FIN')) {
      throw new Exception('No se puede cancelar una orden finalizada.');
    }

    $idCanc = self::idOrden('CANC');
    if (!$idCanc) {
      throw new Exception('No se encontró el estado de cancelación para órdenes.');
    }

    $pdo = conexionCircuitoQuirurgico();
    $stmt = $pdo->prepare('UPDATE orden_medica SET id_estado = :id_estado WHERE id_orden = :id_orden');
    return $stmt->execute([
      ':id_estado' => $idCanc,
      ':id_orden'  => $id_orden,
    ]);
  }

  public static function eliminarOrden($id_orden)
  {
    $orden = self::obtenerOrden($id_orden);
    if (!$orden) {
      throw new Exception('La orden médica no existe o no está disponible.');
    }
    if (self::ordenTieneEstado($orden['id_estado'], 'FIN')) {
      throw new Exception('No se puede eliminar una orden finalizada.');
    }

    $idRech = self::idOrden('RECH');
    if (!$idRech) {
      throw new Exception('No se encontró el estado para dar de baja la orden.');
    }

    $pdo = conexionCircuitoQuirurgico();
    $stmt = $pdo->prepare('UPDATE orden_medica SET id_estado = :id_estado WHERE id_orden = :id_orden');
    return $stmt->execute([
      ':id_estado' => $idRech,
      ':id_orden'  => $id_orden,
    ]);
  }
}
