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
require_once __DIR__ . '/admision-b.php';

class ConsultasOrdenesMedicas
{
  private static $reglasAccionesCache = null;
  private static $idsEstadosOcultosCache = null;

  private static function idOrden($codigo)
  {
    return id_estado('ORDEN', $codigo);
  }

  private static function idEstadoEliminado()
  {
    return id_estado('DATO', 'ELIM');
  }

  /** IDs de estado que no deben listarse ni consultarse (ELIM + RECH legacy). */
  private static function idsEstadosOcultosListado()
  {
    if (self::$idsEstadosOcultosCache !== null) {
      return self::$idsEstadosOcultosCache;
    }

    $ids = [];
    $idElim = self::idEstadoEliminado();
    if ($idElim) {
      $ids[] = $idElim;
    }
    $idRech = self::idOrden('RECH');
    if ($idRech) {
      $ids[] = $idRech;
    }
    self::$idsEstadosOcultosCache = $ids;
    return self::$idsEstadosOcultosCache;
  }

  private static function appendExcluirEstadosOcultos(&$where, &$params)
  {
    foreach (self::idsEstadosOcultosListado() as $i => $id) {
      $key = ':id_oculto_' . $i;
      $where .= " AND om.id_estado != $key";
      $params[$key] = $id;
    }
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
    $codigos = array_values(array_unique(array_filter(array_map(function ($c) {
      return strtoupper(trim((string)$c));
    }, $codigos))));

    if ($codigos === []) {
      return [];
    }

    $pdo = conexionCircuitoQuirurgico();
    $params = [':entidad' => 'ORDEN'];
    $placeholders = [];
    foreach ($codigos as $i => $codigo) {
      $key = ':codigo_' . $i;
      $placeholders[] = $key;
      $params[$key] = $codigo;
    }

    $sql = 'SELECT id_estado FROM estado WHERE entidad = :entidad AND codigo IN (' .
      implode(', ', $placeholders) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
  }

  public static function obtenerReglasAccionesOrden()
  {
    if (self::$reglasAccionesCache !== null) {
      return self::$reglasAccionesCache;
    }

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

    self::$reglasAccionesCache = [
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

    return self::$reglasAccionesCache;
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

  /** Mismos estados que permiten cancelar: edición de cirugía, cirujano y observaciones. */
  private static function puedeEditarDetallePorId($idEstado)
  {
    return self::puedeCancelarPorId($idEstado);
  }

  public static function obtenerEstadosOrden()
  {
    $pdo = conexionCircuitoQuirurgico();
    $stmt = $pdo->query(
      "SELECT id_estado, codigo, descripcion
       FROM estado
       WHERE entidad = 'ORDEN'
         AND codigo != 'RECH'
       ORDER BY descripcion"
    );
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $estados[] = [
      'id_estado'   => 'aprobados',
      'codigo'      => 'APROB',
      'descripcion' => 'Aprobados',
    ];
    usort($estados, function ($a, $b) {
      return strcasecmp($a['descripcion'] ?? '', $b['descripcion'] ?? '');
    });
    return $estados;
  }

  /** Condición SQL: postas completas y verificación prequirúrgica OK (apto quirúrgico). */
  private static function sqlCondicionOrdenAprobada(): string
  {
    return "
      EXISTS (
        SELECT 1
        FROM (
          SELECT oe.id_orden,
                 COUNT(*)::int AS total_estudios,
                 COUNT(*) FILTER (WHERE ee.codigo = 'REAL')::int AS estudios_realizados
          FROM orden_estudio oe
          INNER JOIN estado ee ON ee.id_estado = oe.id_estado
          WHERE oe.id_orden = om.id_orden
          GROUP BY oe.id_orden
        ) est
        WHERE est.total_estudios > 0
          AND est.estudios_realizados = est.total_estudios
      )
      AND EXISTS (
        SELECT 1
        FROM (
          SELECT ev.codigo
          FROM verificacion_pre_cirugia v
          INNER JOIN estado ev ON ev.id_estado = v.id_estado
          WHERE v.id_orden = om.id_orden
          ORDER BY v.fecha DESC NULLS LAST, v.id_verificacion DESC
          LIMIT 1
        ) ult
        WHERE ult.codigo = 'OK'
      )
    ";
  }

  private static function appendFiltroOrdenesAprobadas(&$where)
  {
    $where .= ' AND ' . self::sqlCondicionOrdenAprobada();
  }

  public static function verificarConfiguracion()
  {
    $pdo = conexionCircuitoQuirurgico();
    $idElim = self::idEstadoEliminado();
    $idCanc = self::idOrden('CANC');

    $stmtRech = $pdo->prepare(
      "SELECT COUNT(*) FROM orden_medica om
       INNER JOIN estado e ON e.id_estado = om.id_estado
       WHERE e.entidad = 'ORDEN' AND e.codigo = 'RECH'"
    );
    $stmtRech->execute();
    $ordenesRechLegacy = (int)$stmtRech->fetchColumn();

    return [
      'elim_configurado'    => (bool)$idElim,
      'cancel_configurado'  => (bool)$idCanc,
      'ordenes_rech_legacy' => $ordenesRechLegacy,
      'listo'               => (bool)$idElim && (bool)$idCanc,
    ];
  }

  public static function listarOrdenes($filtro = null, $valor = null, $pagina = 1, $limite = 10, $listarTodos = false)
  {
    if (!$listarTodos && (empty($filtro) || $valor === null || trim((string)$valor) === '')) {
      throw new Exception('Debe ingresar un criterio de búsqueda o solicitar el listado completo.');
    }

    $pdo = conexionCircuitoQuirurgico();
    $offset = ($pagina - 1) * $limite;
    $idsOcultos = self::idsEstadosOcultosListado();

    $where = 'WHERE 1=1';
    $params = [];

    self::appendExcluirEstadosOcultos($where, $params);

    if (!$listarTodos && !empty($filtro) && $valor !== null && trim((string)$valor) !== '') {
      $v = '%' . trim($valor) . '%';
      if ($filtro === 'dni') {
        $where .= ' AND p.dni LIKE :valor';
        $params[':valor'] = $v;
      } elseif ($filtro === 'apellido') {
        $where .= ' AND p.apellido ILIKE :valor';
        $params[':valor'] = $v;
      } elseif ($filtro === 'nombre') {
        $where .= ' AND p.nombre ILIKE :valor';
        $params[':valor'] = $v;
      } elseif ($filtro === 'todo') {
        $term = trim((string)$valor);
        $condiciones = ['p.apellido ILIKE :valor_todo', 'p.nombre ILIKE :valor_todo'];
        $params[':valor_todo'] = $v;
        $termDni = preg_replace('/\D/', '', $term);
        if ($termDni !== '') {
          $condiciones[] = 'p.dni LIKE :valor_dni';
          $params[':valor_dni'] = '%' . $termDni . '%';
        }
        if (ctype_digit($term)) {
          $condiciones[] = 'om.id_orden = :id_orden_todo';
          $params[':id_orden_todo'] = (int)$term;
        }
        $where .= ' AND (' . implode(' OR ', $condiciones) . ')';
      } elseif ($filtro === 'estado') {
        if (strtolower(trim((string)$valor)) === 'aprobados') {
          self::appendFiltroOrdenesAprobadas($where);
        } else {
          $idEstadoFiltro = filter_var($valor, FILTER_VALIDATE_INT);
          if (!$idEstadoFiltro) {
            throw new Exception('El estado seleccionado no es válido.');
          }
          $where .= ' AND om.id_estado = :id_estado_filtro';
          $params[':id_estado_filtro'] = $idEstadoFiltro;
        }
      } elseif ($filtro === 'id_orden') {
        $where .= ' AND om.id_orden = :id_orden';
        $params[':id_orden'] = (int)$valor;
      } elseif ($filtro === 'cirugia') {
        $where .= ' AND cc.nombre ILIKE :valor';
        $params[':valor'] = $v;
      } elseif ($filtro === 'cirujano') {
        $where .= " AND TRIM(COALESCE(pc.apellido, '') || ', ' || COALESCE(pc.nombre, '')) ILIKE :valor";
        $params[':valor'] = $v;
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
                   TRIM(COALESCE(pc.apellido, '') || ', ' || COALESCE(pc.nombre, '')) AS cirujano,
                   (" . self::sqlCondicionOrdenAprobada() . ") AS puede_programar
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
    $reglas = self::obtenerReglasAccionesOrden();
    $idsCancelables = $reglas['cancelar']['ids_origen_permitidos'];
    $idsEliminables = $reglas['eliminar']['ids_origen_permitidos'];

    foreach ($ordenes as &$orden) {
      $idEstado = (int)$orden['id_estado'];
      $orden['puede_ver'] = !in_array($idEstado, $idsOcultos, true);
      $orden['puede_cancelar'] = in_array($idEstado, $idsCancelables, true);
      $orden['puede_eliminar'] = in_array($idEstado, $idsEliminables, true);
      $orden['puede_programar'] = filter_var($orden['puede_programar'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
    unset($orden);

    return [
      'ordenes' => $ordenes,
      'total'   => $total,
    ];
  }

  /**
   * Sugerencias del buscador (pacientes, cirugía o cirujano según filtro).
   */
  public static function obtenerSugerencias($filtro, $term, $limite = 8)
  {
    if (in_array($filtro, ['cirugia', 'cirujano'], true)) {
      return self::obtenerSugerenciasCatalogo($filtro, $term, $limite);
    }
    return self::obtenerSugerenciasPacientes($filtro, $term, $limite);
  }

  /** Sugerencias de cirugías o cirujanos del catálogo (coincidencia parcial, desde 1 carácter). */
  public static function obtenerSugerenciasCatalogo($tipo, $term, $limite = 10)
  {
    $term = trim((string)$term);
    if (mb_strlen($term) < cq_minimo_caracteres_sugerencia($tipo, $term)) {
      return [];
    }

    $limite = max(1, min(20, (int)$limite));
    $pdo = conexionCircuitoQuirurgico();
    $like = '%' . $term . '%';
    $sugerencias = [];

    if ($tipo === 'cirugia') {
      $idActivo = id_estado('USUARIO', 'ACT');
      $stmt = $pdo->prepare(
        'SELECT nombre FROM catalogo_cirugia
         WHERE nombre ILIKE :term AND id_estado = :id_estado
         ORDER BY nombre
         LIMIT :limite'
      );
      $stmt->bindValue(':term', $like, PDO::PARAM_STR);
      $stmt->bindValue(':id_estado', $idActivo, PDO::PARAM_INT);
      $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
      $stmt->execute();
      foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $nombre) {
        $nombre = trim((string)$nombre);
        if ($nombre === '') {
          continue;
        }
        $sugerencias[] = [
          'valor'  => $nombre,
          'nombre' => $nombre,
          'meta'   => 'Cirugía',
        ];
      }
      return $sugerencias;
    }

    if ($tipo === 'cirujano') {
      $stmtRol = $pdo->query("SELECT id_rol FROM rol WHERE LOWER(nombre) = 'cirujano' LIMIT 1");
      $idRol = $stmtRol->fetch(PDO::FETCH_COLUMN);
      if (!$idRol) {
        return [];
      }

      $idEstado = id_estado('USUARIO', 'ACT');
      $sql = "SELECT prof.id_profesional, p.nombre, p.apellido
              FROM persona p
              INNER JOIN profesional prof ON p.id_persona = prof.id_persona
              WHERE p.id_rol = :id_rol
                AND (
                  p.apellido ILIKE :term
                  OR p.nombre ILIKE :term
                  OR TRIM(COALESCE(p.apellido, '') || ', ' || COALESCE(p.nombre, '')) ILIKE :term
                )";
      $params = [':id_rol' => $idRol, ':term' => $like];
      if ($idEstado) {
        $sql .= ' AND prof.id_estado = :id_estado';
        $params[':id_estado'] = $idEstado;
      }
      $sql .= ' ORDER BY p.apellido, p.nombre LIMIT :limite';

      $stmt = $pdo->prepare($sql);
      foreach ($params as $k => $val) {
        $stmt->bindValue($k, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
      }
      $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
      $stmt->execute();

      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $etiqueta = trim(($fila['apellido'] ?? '') . ', ' . ($fila['nombre'] ?? ''));
        $etiqueta = trim($etiqueta, ', ');
        if ($etiqueta === '') {
          continue;
        }
        $sugerencias[] = [
          'valor'            => $etiqueta,
          'nombre'           => $etiqueta,
          'meta'             => 'Cirujano',
          'id_profesional'   => (int)$fila['id_profesional'],
        ];
      }
    }

    return $sugerencias;
  }

  /** Sugerencias de pacientes con órdenes visibles (autocompletado del buscador). */
  public static function obtenerSugerenciasPacientes($filtro, $term, $limite = 8)
  {
    $filtrosPermitidos = ['dni', 'apellido', 'nombre', 'todo'];
    if (!in_array($filtro, $filtrosPermitidos, true)) {
      return [];
    }

    $term = trim((string)$term);
    if (mb_strlen($term) < cq_minimo_caracteres_sugerencia($filtro, $term)) {
      return [];
    }

    $limite = max(1, min(15, (int)$limite));
    $pdo = conexionCircuitoQuirurgico();

    $where = 'WHERE 1=1';
    $params = [];
    self::appendExcluirEstadosOcultos($where, $params);

    if ($filtro === 'dni') {
      $termLimpio = preg_replace('/\D/', '', $term);
      if (strlen($termLimpio) < 1) {
        return [];
      }
      $where .= ' AND p.dni LIKE :valor';
      $params[':valor'] = $termLimpio . '%';
    } elseif ($filtro === 'apellido') {
      $where .= ' AND p.apellido ILIKE :valor';
      $params[':valor'] = $term . '%';
    } elseif ($filtro === 'nombre') {
      $where .= ' AND p.nombre ILIKE :valor';
      $params[':valor'] = $term . '%';
    } else {
      $termDni = preg_replace('/\D/', '', $term);
      $condiciones = ['p.apellido ILIKE :valor_todo', 'p.nombre ILIKE :valor_todo'];
      $params[':valor_todo'] = $term . '%';
      if ($termDni !== '') {
        $condiciones[] = 'p.dni LIKE :valor_dni';
        $params[':valor_dni'] = $termDni . '%';
      }
      $where .= ' AND (' . implode(' OR ', $condiciones) . ')';
    }

    $from = "
            FROM orden_medica om
            INNER JOIN estado e ON e.id_estado = om.id_estado
            INNER JOIN persona_asiste pa ON pa.id_persona_asiste = om.id_persona_asiste
            INNER JOIN persona p ON p.id_persona = pa.id_persona
        ";

    $sql = "
            SELECT DISTINCT ON (p.id_persona) p.dni, p.nombre, p.apellido
            $from
            $where
            ORDER BY p.id_persona, p.apellido, p.nombre
            LIMIT :limite
        ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $val) {
      $stmt->bindValue($k, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    usort($filas, function ($a, $b) {
      $cmp = strcasecmp($a['apellido'] ?? '', $b['apellido'] ?? '');
      if ($cmp !== 0) {
        return $cmp;
      }
      return strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? '');
    });

    $sugerencias = [];
    foreach ($filas as $fila) {
      $dni = trim((string)($fila['dni'] ?? ''));
      $nombre = trim((string)($fila['nombre'] ?? ''));
      $apellido = trim((string)($fila['apellido'] ?? ''));
      $etiqueta = trim($apellido . ', ' . $nombre);
      if ($dni !== '') {
        $etiqueta .= ' — DNI ' . $dni;
      }

      if ($filtro === 'dni') {
        $valor = $dni;
      } elseif ($filtro === 'apellido') {
        $valor = $apellido;
      } elseif ($filtro === 'nombre') {
        $valor = $nombre;
      } else {
        $termDni = preg_replace('/\D/', '', $term);
        if ($termDni !== '' && $dni !== '' && strpos($dni, $termDni) !== false) {
          $valor = $dni;
        } elseif ($apellido !== '' && mb_stripos($apellido, $term) !== false) {
          $valor = $apellido;
        } else {
          $valor = $nombre !== '' ? $nombre : $dni;
        }
      }

      if ($valor === '') {
        continue;
      }

      $sugerencias[] = [
        'valor'   => $valor,
        'etiqueta' => $etiqueta,
        'dni'     => $dni,
        'nombre'  => $nombre,
        'apellido' => $apellido,
      ];
    }

    return $sugerencias;
  }

  public static function obtenerOrden($id_orden)
  {
    $pdo = conexionCircuitoQuirurgico();

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
    $paramsOrden = [':id_orden' => $id_orden];
    $whereExtra = '';
    self::appendExcluirEstadosOcultos($whereExtra, $paramsOrden);
    $sql .= $whereExtra . ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    foreach ($paramsOrden as $k => $val) {
      $stmt->bindValue($k, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($orden) {
      $orden['puede_editar'] = self::puedeEditarDetallePorId((int)$orden['id_estado']);
    }
    return $orden ?: null;
  }

  private static function asegurarTablaModificacionesOrden(PDO $pdo): void
  {
    static $verificado = false;
    if ($verificado) {
      return;
    }

    $pdo->exec("
      CREATE TABLE IF NOT EXISTS orden_medica_modificacion (
        id_modificacion SERIAL PRIMARY KEY,
        id_orden INTEGER NOT NULL,
        campo VARCHAR(50) NOT NULL,
        valor_anterior TEXT,
        valor_nuevo TEXT,
        id_usuario INTEGER,
        nombre_usuario VARCHAR(200),
        fecha_modificacion TIMESTAMP NOT NULL DEFAULT NOW()
      )
    ");
    $pdo->exec("
      CREATE INDEX IF NOT EXISTS idx_orden_medica_modificacion_orden
      ON orden_medica_modificacion (id_orden, fecha_modificacion DESC)
    ");
    $verificado = true;
  }

  public static function obtenerModificacionesOrden($id_orden)
  {
    $pdo = conexionCircuitoQuirurgico();
    self::asegurarTablaModificacionesOrden($pdo);

    $stmt = $pdo->prepare("
      SELECT id_modificacion, campo, valor_anterior, valor_nuevo,
             nombre_usuario, fecha_modificacion
      FROM orden_medica_modificacion
      WHERE id_orden = :id_orden
      ORDER BY fecha_modificacion DESC, id_modificacion DESC
    ");
    $stmt->execute([':id_orden' => (int)$id_orden]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  private static function registrarModificacionOrden(
    PDO $pdo,
    $id_orden,
    $campo,
    $valorAnterior,
    $valorNuevo,
    $id_usuario,
    $nombre_usuario
  ) {
    $stmt = $pdo->prepare("
      INSERT INTO orden_medica_modificacion
        (id_orden, campo, valor_anterior, valor_nuevo, id_usuario, nombre_usuario)
      VALUES
        (:id_orden, :campo, :valor_anterior, :valor_nuevo, :id_usuario, :nombre_usuario)
    ");
    $stmt->execute([
      ':id_orden'        => (int)$id_orden,
      ':campo'           => $campo,
      ':valor_anterior'  => $valorAnterior !== '' ? $valorAnterior : null,
      ':valor_nuevo'     => $valorNuevo !== '' ? $valorNuevo : null,
      ':id_usuario'      => $id_usuario ? (int)$id_usuario : null,
      ':nombre_usuario'  => $nombre_usuario ?: null,
    ]);
  }

  private static function normalizarTextoOrden($valor)
  {
    $texto = trim((string)$valor);
    if ($texto === '' || $texto === '—') {
      return '';
    }
    return $texto;
  }

  public static function obtenerCatalogosEdicion()
  {
    return [
      'cirugias'  => ConsultasAdmision::obtenerCatalogoCirugias(),
      'cirujanos' => ConsultasAdmision::obtenerCirujanos(),
    ];
  }

  public static function actualizarDetalleOrden(
    $id_orden,
    $nombreCirugia,
    $id_profesional,
    $observaciones,
    $id_usuario = null,
    $nombre_usuario = null
  ) {
    $orden = self::obtenerOrden($id_orden);
    if (!$orden) {
      throw new Exception('La orden médica no existe o no está disponible.');
    }

    if (!self::puedeEditarDetallePorId((int)$orden['id_estado'])) {
      throw new Exception('No se puede modificar una orden en el estado actual.');
    }

    $nombreCirugia = trim((string)$nombreCirugia);
    if ($nombreCirugia === '') {
      throw new Exception('Debe seleccionar un tipo de cirugía.');
    }

    $id_profesional = filter_var($id_profesional, FILTER_VALIDATE_INT);
    if (!$id_profesional || $id_profesional <= 0) {
      throw new Exception('Debe seleccionar un cirujano válido.');
    }

    $observaciones = trim((string)$observaciones);
    if (mb_strlen($observaciones) > 2000) {
      throw new Exception('Las observaciones no pueden superar los 2000 caracteres.');
    }

    $pdo = conexionCircuitoQuirurgico();
    self::asegurarTablaModificacionesOrden($pdo);

    $idActivo = id_estado('USUARIO', 'ACT');
    $stmtCirugia = $pdo->prepare(
      'SELECT id_cirugia_catalogo FROM catalogo_cirugia WHERE nombre = :nombre AND id_estado = :id_estado LIMIT 1'
    );
    $stmtCirugia->execute([':nombre' => $nombreCirugia, ':id_estado' => $idActivo]);
    $id_cirugia = $stmtCirugia->fetch(PDO::FETCH_COLUMN);
    if (!$id_cirugia) {
      throw new Exception('No se encontró la cirugía seleccionada en el catálogo.');
    }

    $stmtProf = $pdo->prepare(
      'SELECT pr.id_profesional,
              TRIM(COALESCE(p.apellido, \'\') || \', \' || COALESCE(p.nombre, \'\')) AS cirujano
       FROM profesional pr
       INNER JOIN persona p ON p.id_persona = pr.id_persona
       WHERE pr.id_profesional = :id_profesional
       LIMIT 1'
    );
    $stmtProf->execute([':id_profesional' => $id_profesional]);
    $prof = $stmtProf->fetch(PDO::FETCH_ASSOC);
    if (!$prof) {
      throw new Exception('El cirujano seleccionado no es válido.');
    }

    $cirugiaAnterior = self::normalizarTextoOrden($orden['cirugia'] ?? '');
    $cirujanoAnterior = self::normalizarTextoOrden($orden['cirujano'] ?? '');
    $obsAnterior = self::normalizarTextoOrden($orden['observaciones'] ?? '');
    $cirujanoNuevo = self::normalizarTextoOrden($prof['cirujano'] ?? '');
    $obsNuevo = self::normalizarTextoOrden($observaciones);

    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare(
        'UPDATE orden_medica
         SET id_cirugia_catalogo = :id_cirugia,
             id_profesional = :id_profesional,
             observaciones = :observaciones
         WHERE id_orden = :id_orden'
      );
      $stmt->execute([
        ':id_cirugia'      => $id_cirugia,
        ':id_profesional'  => $id_profesional,
        ':observaciones'   => $obsNuevo !== '' ? $obsNuevo : null,
        ':id_orden'        => $id_orden,
      ]);

      if ($cirugiaAnterior !== self::normalizarTextoOrden($nombreCirugia)) {
        self::registrarModificacionOrden(
          $pdo, $id_orden, 'cirugia', $cirugiaAnterior, $nombreCirugia, $id_usuario, $nombre_usuario
        );
      }
      if ($cirujanoAnterior !== $cirujanoNuevo) {
        self::registrarModificacionOrden(
          $pdo, $id_orden, 'cirujano', $cirujanoAnterior, $cirujanoNuevo, $id_usuario, $nombre_usuario
        );
      }
      if ($obsAnterior !== $obsNuevo) {
        self::registrarModificacionOrden(
          $pdo, $id_orden, 'observaciones', $obsAnterior, $obsNuevo, $id_usuario, $nombre_usuario
        );
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
