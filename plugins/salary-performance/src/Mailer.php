<?php
namespace SalaryPerf;

use Niyanta\Core\Branding;

/**
 * Minimal MIME emailer for sending a salary slip with a PDF attachment.
 * Uses PHP's mail() (available on most shared hosting). No external deps.
 */
class Mailer
{
    public static function sendSlip(string $to, string $subject, string $body, string $pdf, string $filename): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $company = Branding::companyName();
        $fromEmail = (string) Branding::setting('mail_from', 'no-reply@' . self::host());
        $boundary = '=_niyanta_' . bin2hex(random_bytes(10));

        $headers = [];
        $headers[] = 'From: ' . self::encodeName($company) . ' <' . $fromEmail . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

        $msg  = "--{$boundary}\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $msg .= $body . "\r\n\r\n";
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: application/pdf; name=\"{$filename}\"\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
        $msg .= chunk_split(base64_encode($pdf)) . "\r\n";
        $msg .= "--{$boundary}--";

        return @mail($to, self::encodeName($subject), $msg, implode("\r\n", $headers));
    }

    private static function encodeName(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }

    private static function host(): string
    {
        $host = $_SERVER['SERVER_NAME'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return preg_replace('/[^a-z0-9.\-]/i', '', (string) $host) ?: 'localhost';
    }
}
