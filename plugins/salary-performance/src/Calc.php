<?php
namespace SalaryPerf;

/**
 * Pure calculation helpers for salary and performance. No I/O — easy to reason
 * about and reuse from both controllers and the dashboard widgets.
 */
class Calc
{
    public const GRADES = ['A+', 'A', 'B+', 'B', 'C'];

    /**
     * Compute the money components of a salary slip.
     *   base     = hours worked x hourly rate
     *   overtime = overtime hours x overtime rate
     *   gross    = base + overtime
     *   net      = gross + bonuses - deductions
     *
     * @return array{base_pay:float,overtime_pay:float,gross_pay:float,net_pay:float}
     */
    public static function salary(
        float $hoursWorked,
        float $hourlyRate,
        float $overtimeHours,
        float $overtimeRate,
        float $bonuses,
        float $deductions
    ): array {
        $base = round($hoursWorked * $hourlyRate, 2);
        $overtime = round($overtimeHours * $overtimeRate, 2);
        $gross = round($base + $overtime, 2);
        $net = round($gross + $bonuses - $deductions, 2);
        return [
            'base_pay'     => $base,
            'overtime_pay' => $overtime,
            'gross_pay'    => $gross,
            'net_pay'      => $net,
        ];
    }

    /** Attendance percentage, capped at 100. */
    public static function attendancePct(float $worked, float $expected): float
    {
        if ($expected <= 0) {
            return 0.0;
        }
        return round(min(100, ($worked / $expected) * 100), 2);
    }

    /** Map a performance score (0-100) to a letter grade. */
    public static function grade(float $score): string
    {
        return match (true) {
            $score >= 95 => 'A+',
            $score >= 85 => 'A',
            $score >= 75 => 'B+',
            $score >= 65 => 'B',
            default      => 'C',
        };
    }

    /**
     * Performance score blends attendance with productivity: attendance is the
     * backbone, lightly boosted by the share of productive hours.
     */
    public static function performanceScore(float $attendancePct, float $productive, float $worked): float
    {
        $productivityRatio = $worked > 0 ? min(1, $productive / $worked) : 0;
        $score = ($attendancePct * 0.8) + ($productivityRatio * 100 * 0.2);
        return round(min(100, $score), 2);
    }

    public static function money(float $v): string
    {
        return number_format($v, 2);
    }

    public static function currentMonth(): string
    {
        return date('Y-m');
    }

    public static function monthLabel(string $month): string
    {
        $ts = strtotime($month . '-01');
        return $ts ? date('F Y', $ts) : $month;
    }
}
