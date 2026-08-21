<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';
require_once __DIR__ . '/funciones.php';

class ConsultasDashboard
{
    private static function getPDO(): PDO
    {
        return conexionCircuitoQuirurgico();
    }

    public static function obtenerMetricas(): array
    {
        $pdo = self::getPDO();

        return [
            'total_pacientes' => self::contarTotalPacientes($pdo),
            'aptos_operar'    => self::contarAptosParaOperar($pdo),
            'alertas_demora'  => self::contarAlertasDemora($pdo),
        ];
    }

    /**
     * Total de personas con rol 'paciente' (excluye bajas lógicas).
     */
    private static function contarTotalPacientes(PDO $pdo): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM persona p
            INNER JOIN rol r ON r.id_rol = p.id_rol
            WHERE LOWER(r.nombre) = 'paciente'
              AND (
                  p.id_estado IS NULL
                  OR p.id_estado NOT IN (
                      SELECT e2.id_estado FROM estado e2
                      WHERE e2.entidad = 'USUARIO' AND e2.codigo = 'INA'
                  )
              )
        ";
        return (int) $pdo->query($sql)->fetchColumn();
    }

    /**
     * Pacientes aptos para operar: órdenes no eliminadas/canceladas cuya
     * última verificación pre-quirúrgica (Apto Físico) esté en estado OK.
     * Replica el filtro por defecto de la Lista de Espera Quirúrgica.
     */
    private static function contarAptosParaOperar(PDO $pdo): int
    {
        $idElim = id_estado('DATO', 'ELIM') ?: 0;
        $idCanc = id_estado('ORDEN', 'CANC') ?: 0;

        $sql = "
            SELECT COUNT(*)
            FROM orden_medica om
            WHERE om.id_estado NOT IN (:id_elim, :id_canc)
              AND (
                  SELECT ev.codigo
                  FROM verificacion_pre_cirugia v2
                  INNER JOIN estado ev ON ev.id_estado = v2.id_estado
                  WHERE v2.id_orden = om.id_orden
                  ORDER BY v2.fecha DESC NULLS LAST, v2.id_verificacion DESC
                  LIMIT 1
              ) = 'OK'
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_elim', $idElim, PDO::PARAM_INT);
        $stmt->bindValue(':id_canc', $idCanc, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cirugías activas con fecha_inicio anterior a hoy (demoras reales).
     * Una cirugía programada que no arrancó en fecha es una alerta concreta.
     */
    private static function contarAlertasDemora(PDO $pdo): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM cirugia c
            INNER JOIN estado e ON c.id_estado = e.id_estado
            WHERE e.entidad = 'CIRUGIA'
              AND e.codigo = 'PROG'
              AND c.fecha_inicio < NOW()
        ";
        return (int) $pdo->query($sql)->fetchColumn();
    }

    /**
     * Tiempo promedio en días de cirugías finalizadas (fecha_fin - fecha_inicio).
     */
    private static function calcularTiempoPromedio(PDO $pdo): string
    {
        $sql = "
            SELECT AVG(EXTRACT(EPOCH FROM (fecha_fin - fecha_inicio)) / 3600)
            FROM cirugia c
            INNER JOIN estado e ON c.id_estado = e.id_estado
            WHERE e.entidad = 'CIRUGIA'
              AND e.codigo = 'FIN'
              AND c.fecha_fin IS NOT NULL
              AND c.fecha_inicio IS NOT NULL
        ";
        $promedio = $pdo->query($sql)->fetchColumn();

        if ($promedio === null || $promedio === false) {
            return 'N/A';
        }

        $horas = round((float) $promedio, 1);
        return $horas >= 24
            ? round($horas / 24, 1) . 'd'
            : $horas . 'h';
    }
}