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

  private static function idEstadoEliminado()
  {
    return id_estado('DATO', 'ELIM');
  }

  /** Códigos de ORDEN que permiten cancelar (se resuelven a id_estado desde la BD). */
  private static function codigosCancelables()
  {
    return ['PEND', 'PROC'];
  }

  /** Códigos de ORDEN que permiten baja lógica (se resuelven a id_estado desde la BD). */
  private static function codigosEliminables()
  {
    return ['PEND', 'PROC', 'CANC'];
  }

  private static function idsEstadoOrdenPorCodigos(array $codigos)
  {
    $ids = [];
    foreach ($codigos as $codigo) {
      $id = self::idOrden($codigo);
      if ($id) {
        $ids[] = $id;
      }
    }
    return $ids;
  }

  public static function obtenerReglasAccionesOrden()
  {
    $estados = self::obtenerEstadosOrden();
    $idCanc = self::idOrden('CANC');
    $idElim = self::idEstadoEliminado();
    $descripcionCanc = null;

    foreach ($estados as $e) {
      if ($idCanc && (int)$e['id_estado'] === $idCanc) {
        $descripcionCanc = $e['descripcion'];
        break;
      }
    }

    return [
      'estados'  => $estados,
      'cancelar' => [
        'id_estado_destino'     => $idCanc,
        'descripcion_destino'  => $descripcionCanc,
        'ids_origen_permitidos' => self::idsEstadoOrdenPorCodigos(self::codigosCancelables()),
      ],
      'eliminar' => [
        'id_estado_destino'     => $idElim,
        'ids_origen_permitidos' => self::idsEstadoOrdenPorCodigos(self::codigosEliminables()),
      ],
    ];
  }

  private static function puedeCancelarPorId($idEstado)
  {
    $reglas = self::obtenerReglasAccionesOrden();
    return in_array((int)$idEstado, $reglas['cancelar']['ids_origen_permitidos'], true);
  }

  private static function puedeEliminarPorId($idEstado)
  {
    $reglas = self::obtenerReglasAccionesOrden();
    return in_array((int)$idEstado, $reglas['eliminar']['ids_origen_permitidos'], true);
  }

  public static function obtenerEstadosOrden()
  {
    $pdo = conexionCircuitoQuirurgico();
    $stmt = $pdo->query(
      "SELECT id_estado, codigo, descripcion
       FROM estado
       WHERE entidad = 'ORDEN'
       ORDER BY descripcion"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function listarOrdenes($filtro = null, $valor = null, $pagina = 1, $limite = 10, $listarTodos = false)
  {
    if (!$listarTodos && (empty($filtro) || $valor === null || trim((string)$valor) === '')) {
      throw new Exception('Debe ingresar un criterio de búsqueda o solicitar el listado completo.');
    }

    $pdo = conexionCircuitoQuirurgico();
    $offset = ($pagina - 1) * $limite;
    $idElim = self::idEstadoEliminado();

    $where = 'WHERE 1=1';
    $params = [];

    if ($idElim) {
      $where .= ' AND om.id_estado != :id_elim';
      $params[':id_elim'] = $idElim;
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
        $idEstadoFiltro = filter_var($valor, FILTER_VALIDATE_INT);
        if (!$idEstadoFiltro) {
          throw new Exception('El estado seleccionado no es válido.');
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
            SELECT om.id_orden, om.id_estado, om.fecha_orden, om.observaciones,
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

    $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $idElim = self::idEstadoEliminado();
    foreach ($ordenes as &$orden) {
      $idEstado = (int)$orden['id_estado'];
      $orden['puede_ver'] = !$idElim || $idEstado !== $idElim;
      $orden['puede_cancelar'] = self::puedeCancelarPorId($idEstado);
      $orden['puede_eliminar'] = self::puedeEliminarPorId($idEstado);
    }
    unset($orden);

    return [
      'ordenes' => $ordenes,
      'total'   => $total,
    ];
  }

  public static function obtenerOrden($id_orden)
  {
    $pdo = conexionCircuitoQuirurgico();
    $idElim = self::idEstadoEliminado();

    $sql = "
            SELECT om.id_orden, om.id_persona_asiste, om.id_profesional, om.id_cirugia_catalogo,
                   om.fecha_orden, om.observaciones, om.id_estado,
                   e.codigo AS estado_codigo, e.descripcion AS estado_descripcion,
                   p.dni, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                   cc.nombre AS cirugia,
                   TRIM(COALESCE(pc.apellido, '') || ', ' || COALESCE(pc.nombre, '')) AS cirujano
            FROM orden_medica om
            INNER JOIN estado e ON e.id_estado = om.id_estado
            INNER JOIN persona_asiste pa ON pa.id_persona_asiste = om.id_persona_asiste
            INNER JOIN persona p ON p.id_persona = pa.id_persona
            LEFT JOIN catalogo_cirugia cc ON cc.id_cirugia_catalogo = om.id_cirugia_catalogo
            LEFT JOIN profesional pr ON pr.id_profesional = om.id_profesional
            LEFT JOIN persona pc ON pc.id_persona = pr.id_persona
            WHERE om.id_orden = :id_orden
        ";
    if ($idElim) {
      $sql .= ' AND om.id_estado != :id_elim';
    }
    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_orden', $id_orden, PDO::PARAM_INT);
    if ($idElim) {
      $stmt->bindValue(':id_elim', $idElim, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public static function cancelarOrden($id_orden)
  {
    $orden = self::obtenerOrden($id_orden);
    if (!$orden) {
      throw new Exception('La orden médica no existe o no está disponible.');
    }

    $reglas = self::obtenerReglasAccionesOrden();
    $idCanc = $reglas['cancelar']['id_estado_destino'];
    if (!$idCanc) {
      throw new Exception('No se encontró el estado de cancelación (ORDEN / CANC) en la base de datos.');
    }

    $idEstado = (int)$orden['id_estado'];
    if ($idEstado === $idCanc) {
      $desc = $reglas['cancelar']['descripcion_destino'] ?: 'cancelada';
      throw new Exception('La orden ya está ' . strtolower($desc) . '.');
    }
    if (!self::puedeCancelarPorId($idEstado)) {
      throw new Exception('No se puede cancelar una orden en el estado actual.');
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

    $reglas = self::obtenerReglasAccionesOrden();
    $idElim = $reglas['eliminar']['id_estado_destino'];
    if (!$idElim) {
      throw new Exception('No se encontró el estado de eliminación (DATO / ELIM) en la base de datos.');
    }

    $idEstado = (int)$orden['id_estado'];
    if ($idEstado === $idElim) {
      throw new Exception('La orden ya fue eliminada.');
    }
    if (!self::puedeEliminarPorId($idEstado)) {
      throw new Exception('No se puede eliminar una orden en el estado actual.');
    }

    $pdo = conexionCircuitoQuirurgico();
    $stmt = $pdo->prepare('UPDATE orden_medica SET id_estado = :id_estado WHERE id_orden = :id_orden');
    return $stmt->execute([
      ':id_estado' => $idElim,
      ':id_orden'  => $id_orden,
    ]);
  }
}
