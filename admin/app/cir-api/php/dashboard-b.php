<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';

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
            'pacientes_circuito' => self::contarPacientesEnCircuito($pdo),
            'aptos_operar'       => self::contarAptosParaOperar($pdo),
            'alertas_demora'     => self::contarAlertasDemora($pdo),
        ];
    }

    /**
     * Órdenes activas: excluyendo FIN, CANC, RECH.
     */
    private static function contarPacientesEnCircuito(PDO $pdo): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM orden_medica om
            INNER JOIN estado e ON om.id_estado = e.id_estado
            WHERE e.entidad = 'ORDEN'
              AND e.codigo NOT IN ('FIN', 'CANC', 'RECH')
        ";
        return (int) $pdo->query($sql)->fetchColumn();
    }

    /**
     * Pacientes en lista_espera (el trigger fn_resultados los inserta
     * automáticamente cuando todos los estudios están validados).
     * Se excluyen los que ya tienen cirugía programada.
     */
    private static function contarAptosParaOperar(PDO $pdo): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM lista_espera le
            INNER JOIN estado e ON le.id_estado = e.id_estado
            WHERE e.entidad = 'LISTA'
        ";
        return (int) $pdo->query($sql)->fetchColumn();
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