<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasAuditoria {

    /**
     * Nombre de cada tabla auditada en el lenguaje del circuito. La funcion
     * fn_auditoria_cq() guarda el evento crudo (nombre de tabla y codigo de
     * estado) y deja la traduccion para el backend, que es lo que se hace aca.
     */
    private static $entidades = [
        'orden_medica'             => 'orden médica',
        'orden_estudio'            => 'estudio prequirúrgico',
        'verificacion_pre_cirugia' => 'apto físico',
        'turno'                    => 'turno',
        'cirugia'                  => 'cirugía',
        'lista_espera'             => 'lista de espera',
        'persona'                  => 'paciente',
        'profesional'              => 'profesional',
    ];

    /**
     * Entidades de la tabla estado en las que buscar los codigos que aparecen
     * en el detalle de cada tabla. DATO va en todas porque ELIM (borrado logico)
     * se usa en todo el circuito.
     */
    private static $entidadesEstado = [
        'orden_medica'             => ['ORDEN', 'DATO'],
        'orden_estudio'            => ['ESTUDIO', 'DATO'],
        'verificacion_pre_cirugia' => ['VERIFICACION', 'DATO'],
        'turno'                    => ['TURNO', 'DATO'],
        'cirugia'                  => ['CIRUGIA', 'DATO'],
        'lista_espera'             => ['LISTA', 'DATO'],
        'persona'                  => ['USUARIO', 'DATO'],
        'profesional'              => ['USUARIO', 'DATO'],
    ];

    private static $tiposBusqueda = ['todo', 'paciente', 'usuario', 'accion'];

    private static $limitesPermitidos = [5, 10, 20, 50];

    /**
     * Antiguedad maxima que se deja consultar. Sin este tope una busqueda sin
     * fechas recorre la auditoria completa, que crece con cada cambio del
     * circuito.
     */
    private static $mesesMaximos = 3;

    /**
     * Historial de auditoria con filtros, orden y paginado.
     *
     * @param array $filtros tipo, valor, desde, hasta, orden, limite, pagina
     * @return array registros, total, pagina, limite, total_paginas
     */
    public static function listarAuditoria(array $filtros): array {
        $pdo = conexionCircuitoQuirurgico();

        $tipo   = strtolower(trim((string)($filtros['tipo'] ?? 'todo')));
        $valor  = trim((string)($filtros['valor'] ?? ''));
        $desde  = trim((string)($filtros['desde'] ?? ''));
        $hasta  = trim((string)($filtros['hasta'] ?? ''));
        $orden  = strtolower(trim((string)($filtros['orden'] ?? 'desc')));
        $limite = (int)($filtros['limite'] ?? 10);
        $pagina = (int)($filtros['pagina'] ?? 1);

        if (!in_array($tipo, self::$tiposBusqueda, true))        $tipo   = 'todo';
        if (!in_array($limite, self::$limitesPermitidos, true))  $limite = 10;
        if ($pagina < 1)                                         $pagina = 1;
        $orden = ($orden === 'asc') ? 'ASC' : 'DESC';

        [$desde, $hasta] = self::acotarRango($desde, $hasta);

        // Rango invertido: no hay registros posibles, se responde vacio sin
        // consultar la base.
        if ($hasta < $desde) {
            return [
                'registros'     => [],
                'total'         => 0,
                'pagina'        => $pagina,
                'limite'        => $limite,
                'total_paginas' => 0,
            ];
        }

        // Se traduce en SQL para que el buscador filtre por el mismo texto que
        // el usuario ve en la columna "Acción realizada".
        $expAccion = "(CASE a.accion
                            WHEN 'INSERT' THEN 'Alta'
                            WHEN 'UPDATE' THEN 'Modificación'
                            WHEN 'DELETE' THEN 'Baja'
                            ELSE a.accion
                       END || ' de ' || " . self::sqlEntidad() . ")";

        $expPaciente = "(COALESCE(p.apellido, '') || ' ' || COALESCE(p.nombre, '') || ' ' || COALESCE(p.dni, ''))";

        $from = "
            FROM auditoria a
            LEFT JOIN persona p ON p.id_persona = a.id_persona
        ";

        $where  = "WHERE 1=1";
        $params = [];

        // Rango en vez de a.fecha::date para poder usar idx_auditoria_fecha.
        // Va siempre, asi ninguna busqueda sale de la ventana permitida.
        $where .= " AND a.fecha >= :fecha_desde::date
                    AND a.fecha <  (:fecha_hasta::date + INTERVAL '1 day')";
        $params[':fecha_desde'] = $desde;
        $params[':fecha_hasta'] = $hasta;

        if ($valor !== '') {
            if ($tipo === 'paciente') {
                $where .= " AND $expPaciente ILIKE :valor";
            } elseif ($tipo === 'usuario') {
                $where .= " AND COALESCE(a.nombre_usuario, '') ILIKE :valor";
            } elseif ($tipo === 'accion') {
                $where .= " AND ($expAccion ILIKE :valor OR COALESCE(a.detalle, '') ILIKE :valor)";
            } else {
                $where .= " AND ($expPaciente ILIKE :valor
                             OR COALESCE(a.nombre_usuario, '') ILIKE :valor
                             OR $expAccion ILIKE :valor
                             OR COALESCE(a.detalle, '') ILIKE :valor)";
            }
            $params[':valor'] = '%' . $valor . '%';
        }

        // Total sin paginar, para armar la paginacion en la vista.
        $stmtTotal = $pdo->prepare("SELECT COUNT(*) $from $where");
        foreach ($params as $k => $v) $stmtTotal->bindValue($k, $v);
        $stmtTotal->execute();
        $total = (int)$stmtTotal->fetchColumn();

        $sql = "
            SELECT
                a.id_auditoria,
                TO_CHAR(a.fecha, 'DD/MM/YYYY HH24:MI') AS fecha_hora,
                a.tabla_afectada,
                a.accion,
                a.id_registro,
                a.id_orden,
                a.id_usuario,
                COALESCE(NULLIF(TRIM(a.nombre_usuario), ''), 'SISTEMA') AS usuario,
                a.ip_origen,
                $expAccion AS accion_realizada,
                a.id_persona,
                p.dni      AS paciente_dni,
                p.nombre   AS paciente_nombre,
                p.apellido AS paciente_apellido,
                a.detalle
            $from
            $where
            ORDER BY a.fecha $orden, a.id_auditoria $orden
            LIMIT :limite OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($pagina - 1) * $limite, PDO::PARAM_INT);
        $stmt->execute();

        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $estados = self::mapaEstados($pdo);
        foreach ($registros as $i => $fila) {
            $registros[$i]['paciente'] = self::armarPaciente($fila);
            $registros[$i]['detalle']  = self::traducirDetalle(
                $fila['detalle'],
                (string)$fila['tabla_afectada'],
                $estados
            );
        }

        return [
            'registros'     => $registros,
            'total'         => $total,
            'pagina'        => $pagina,
            'limite'        => $limite,
            'total_paginas' => (int)ceil($total / $limite),
        ];
    }

    /**
     * Deja DESDE y HASTA dentro de los ultimos $mesesMaximos meses. Una fecha
     * vacia o mal formada toma el extremo de la ventana, y una fuera de ella
     * se recorta al limite. No se corrige un rango invertido: eso lo resuelve
     * quien llama devolviendo un resultado vacio.
     *
     * @return array [desde, hasta] en formato YYYY-MM-DD
     */
    private static function acotarRango(string $desde, string $hasta): array {
        // El endpoint se llama directo, sin pasar por index.php, que es donde
        // se fija la zona horaria del sistema.
        $zona   = new DateTimeZone('America/Argentina/Buenos_Aires');
        $hoy    = (new DateTime('today', $zona))->format('Y-m-d');
        $minimo = (new DateTime('today', $zona))
            ->modify('-' . self::$mesesMaximos . ' months')
            ->format('Y-m-d');

        $desde = self::fechaValida($desde) ? $desde : $minimo;
        $hasta = self::fechaValida($hasta) ? $hasta : $hoy;

        // Las fechas en YYYY-MM-DD se comparan bien como texto.
        $desde = min(max($desde, $minimo), $hoy);
        $hasta = min(max($hasta, $minimo), $hoy);

        return [$desde, $hasta];
    }

    private static function fechaValida(string $fecha): bool {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $m)) {
            return false;
        }
        return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
    }

    /** CASE que traduce el nombre de la tabla auditada. */
    private static function sqlEntidad(): string {
        $sql = "CASE a.tabla_afectada";
        foreach (self::$entidades as $tabla => $etiqueta) {
            $sql .= " WHEN '$tabla' THEN '$etiqueta'";
        }
        return $sql . " ELSE REPLACE(a.tabla_afectada, '_', ' ') END";
    }

    /** Paciente afectado, listo para mostrar. */
    private static function armarPaciente(array $fila): string {
        $apellido = trim((string)($fila['paciente_apellido'] ?? ''));
        $nombre   = trim((string)($fila['paciente_nombre'] ?? ''));
        $dni      = trim((string)($fila['paciente_dni'] ?? ''));

        if ($apellido === '' && $nombre === '') {
            return '';
        }

        $paciente = trim($apellido . ', ' . $nombre, ', ');
        return $dni !== '' ? $paciente . ' — DNI ' . $dni : $paciente;
    }

    /** Codigos de estado agrupados por entidad, una sola vez por request. */
    private static function mapaEstados(PDO $pdo): array {
        static $mapa = null;

        if ($mapa !== null) {
            return $mapa;
        }

        $mapa = [];
        $stmt = $pdo->query("SELECT entidad, codigo, descripcion FROM estado");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $mapa[$fila['entidad']][$fila['codigo']] = $fila['descripcion'];
        }

        return $mapa;
    }

    /**
     * Pasa el detalle crudo del trigger al lenguaje del circuito: nombre de la
     * tabla por el de la entidad y codigos de estado por su descripcion.
     * Solo se reemplazan los codigos conocidos de esa tabla para no tocar el
     * texto libre que puede venir en un motivo de cancelacion.
     */
    private static function traducirDetalle($detalle, string $tabla, array $estados): string {
        $detalle = trim((string)$detalle);

        if ($detalle === '') {
            return '';
        }

        if (isset(self::$entidades[$tabla])) {
            $detalle = str_replace($tabla, self::$entidades[$tabla], $detalle);
        }

        $codigos = [];
        foreach (self::$entidadesEstado[$tabla] ?? [] as $entidad) {
            $codigos += $estados[$entidad] ?? [];
        }

        if (!empty($codigos)) {
            $patron = '/\b(' . implode('|', array_map('preg_quote', array_keys($codigos))) . ')\b/';
            $detalle = preg_replace_callback($patron, function ($m) use ($codigos) {
                return $codigos[$m[1]];
            }, $detalle);
        }

        return str_replace(
            ['Modificacion', 'Cancelacion', 'Reprogramacion', 'Verificacion'],
            ['Modificación', 'Cancelación', 'Reprogramación', 'Verificación'],
            $detalle
        );
    }
}
?>
