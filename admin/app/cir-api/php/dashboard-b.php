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
            'pacientes_circuito'        => self::contarPacientesEnCircuito($pdo),
            'aptos_operar'              => self::contarAptosParaOperar($pdo),
            'alertas_demora'            => self::contarAlertasDemora($pdo),
            'tiempo_promedio'           => self::calcularTiempoPromedioJornada($pdo),
        ];
    }

    private static function contarPacientesEnCircuito(PDO $pdo): int
    {
        $sql = "
        SELECT COUNT(*)
        FROM cirugia c
        INNER JOIN estado e ON c.id_estado = e.id_estado
        WHERE e.codigo NOT IN ('FINALIZADA','CANCELADA')
    ";

        return (int) $pdo->query($sql)->fetchColumn();
    }

    private static function contarAptosParaOperar(PDO $pdo): int
    {
        $sql = "
        SELECT COUNT(*)
        FROM verificacion_pre_cirugia v
        INNER JOIN estado e ON v.id_estado = e.id_estado
        WHERE e.codigo IN ('APTO','APROBADO')
    ";

        return (int) $pdo->query($sql)->fetchColumn();
    }

    private static function contarAlertasDemora(PDO $pdo): int
    {
        $sql = "
        SELECT COUNT(*)
        FROM evento_clinico
        WHERE id_tipo_evento IN (6,7)
    ";

        return (int) $pdo->query($sql)->fetchColumn();
    }

    private static function calcularTiempoPromedioJornada(PDO $pdo): string
    {
        $sql = "
        SELECT AVG(fecha_fin::date - fecha_inicio::date)
        FROM cirugia c
        INNER JOIN estado e ON c.id_estado = e.id_estado
        WHERE e.codigo = 'FINALIZADA'
        AND fecha_fin IS NOT NULL
    ";

        $promedio = $pdo->query($sql)->fetchColumn();

        return $promedio !== null
            ? round((float)$promedio, 1) . 'd'
            : 'N/A';
    }
}
