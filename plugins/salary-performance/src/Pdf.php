<?php
namespace SalaryPerf;

/**
 * Tiny, dependency-free PDF writer for single-page A4 documents (text + lines).
 * Enough to render a salary slip without any external library — keeping the
 * module shared-hosting friendly. Coordinates are given from the top-left in
 * points (1/72 inch); A4 is 595 x 842 pt.
 */
class Pdf
{
    private const PAGE_W = 595.28;
    private const PAGE_H = 841.89;

    /** @var string[] content-stream operators */
    private array $ops = [];

    public function text(float $x, float $yTop, float $size, string $s, bool $bold = false): void
    {
        $font = $bold ? '/F2' : '/F1';
        $y = self::PAGE_H - $yTop;
        $this->ops[] = sprintf('BT %s %.2F Tf %.2F %.2F Td (%s) Tj ET', $font, $size, $x, $y, $this->esc($s));
    }

    /** Right-align text ending at $xRight. */
    public function textRight(float $xRight, float $yTop, float $size, string $s, bool $bold = false): void
    {
        $width = $this->textWidth($s, $size, $bold);
        $this->text($xRight - $width, $yTop, $size, $s, $bold);
    }

    public function line(float $x1, float $yTop1, float $x2, float $yTop2, float $width = 0.6): void
    {
        $y1 = self::PAGE_H - $yTop1;
        $y2 = self::PAGE_H - $yTop2;
        $this->ops[] = sprintf('%.2F w %.2F %.2F m %.2F %.2F l S', $width, $x1, $y1, $x2, $y2);
    }

    public function rectFill(float $x, float $yTop, float $w, float $h, float $r, float $g, float $b): void
    {
        $y = self::PAGE_H - $yTop - $h;
        $this->ops[] = sprintf('%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f 0 0 0 rg', $r, $g, $b, $x, $y, $w, $h);
    }

    public function output(): string
    {
        $content = implode("\n", $this->ops);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] '
                . '/Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>',
                self::PAGE_W,
                self::PAGE_H
            ),
            4 => "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            6 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= 6; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= "{$i} 0 obj\n" . $objects[$i] . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 7\n0000000000 65535 f \n";
        for ($i = 1; $i <= 6; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        return $pdf;
    }

    private function esc(string $s): string
    {
        // Map to WinAnsi-safe ASCII and escape PDF string delimiters.
        $s = (string) @iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $s);
    }

    /** Approximate Helvetica text width (average glyph metrics). */
    private function textWidth(string $s, float $size, bool $bold): float
    {
        $factor = $bold ? 0.55 : 0.52;
        return strlen($s) * $size * $factor;
    }
}
