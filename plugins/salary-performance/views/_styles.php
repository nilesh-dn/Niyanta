<?php /* Plugin-scoped styles for Salary & Performance. */ ?>
<style>
.grade-badge { display:inline-flex; align-items:center; justify-content:center; min-width:2.4rem; padding:.2rem .5rem; border-radius:8px; font-weight:700; }
.grade-aplus, .grade-a { background: color-mix(in srgb, #16a34a 20%, transparent); color:#16a34a; }
.grade-bplus, .grade-b { background: color-mix(in srgb, var(--brand) 18%, transparent); color: var(--brand); }
.grade-c { background: color-mix(in srgb, #dc2626 18%, transparent); color:#dc2626; }
.slip-box { border:1px solid var(--border); border-radius: var(--radius-sm); }
.slip-row { display:flex; justify-content:space-between; padding:.5rem .9rem; border-bottom:1px solid var(--border); }
.slip-row:last-child { border-bottom:0; }
.slip-total { font-size:1.15rem; font-weight:700; }
.meter { height:8px; border-radius:6px; background: var(--surface-muted); overflow:hidden; }
.meter > span { display:block; height:100%; background: var(--brand); }
</style>
<?php
/** @var callable $gradeClass */
if (!function_exists('salperf_grade_class')) {
    function salperf_grade_class(string $g): string
    {
        return 'grade-' . strtolower(str_replace('+', 'plus', $g));
    }
}
if (!function_exists('salperf_status_class')) {
    function salperf_status_class(string $s): string
    {
        return ['pending' => 'warning', 'processed' => 'info', 'paid' => 'success'][$s] ?? 'secondary';
    }
}
