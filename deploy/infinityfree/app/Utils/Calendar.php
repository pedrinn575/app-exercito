<?php
/**
 * Utilitários de calendário / feriados simples (Brasil).
 */

namespace App\Utils;

final class Calendar
{
    /**
     * Fim de semana ou feriado nacional fixo → escala vermelha (24h).
     */
    public static function isEscalaVermelha(string $dateYmd): bool
    {
        $ts = strtotime($dateYmd);
        $dow = (int) date('N', $ts); // 6=sáb, 7=dom
        if ($dow >= 6) {
            return true;
        }

        $fixed = [
            '01-01', // Ano Novo
            '04-21', // Tiradentes
            '05-01', // Trabalho
            '09-07', // Independência
            '10-12', // N. Sra. Aparecida
            '11-02', // Finados
            '11-15', // Proclamação
            '12-25', // Natal
        ];

        return in_array(date('m-d', $ts), $fixed, true);
    }

    public static function formatBr(string $dateYmd): string
    {
        return date('d/m/Y', strtotime($dateYmd));
    }

    public static function weekdayBr(string $dateYmd): string
    {
        $map = [
            'Monday' => 'Segunda', 'Tuesday' => 'Terça', 'Wednesday' => 'Quarta',
            'Thursday' => 'Quinta', 'Friday' => 'Sexta', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
        ];
        $en = date('l', strtotime($dateYmd));
        return $map[$en] ?? $en;
    }

    public static function monthBr(string $anoMes): string
    {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        [$ano, $mes] = explode('-', $anoMes);
        return ($meses[(int) $mes] ?? $mes) . ' ' . $ano;
    }

    public static function shiftMonth(string $anoMes, int $delta): string
    {
        $ts = strtotime($anoMes . '-01 ' . ($delta >= 0 ? '+' : '') . $delta . ' month');
        return date('Y-m', $ts);
    }
}
