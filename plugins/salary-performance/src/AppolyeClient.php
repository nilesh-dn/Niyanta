<?php
namespace SalaryPerf;

use Niyanta\Core\Branding;

/**
 * Integration layer for the Apploye time-tracking API.
 * Reference: https://apploye-com.s3.amazonaws.com/time-tracking-api/index.html
 *
 * This is a self-contained, dependency-free REST client (uses cURL if present,
 * otherwise falls back to file_get_contents). Configuration lives in the core
 * `settings` table under the `appolye_*` keys, managed from the plugin's
 * Integration page.
 *
 * The exact endpoint paths and JSON field names of the Apploye API may need to
 * be adjusted to match your account/plan — they are centralised in
 * endpoint()/mapMonthly() so no other code needs to change. When the API is not
 * configured, callers receive null and can fall back to manual entry, keeping
 * the whole module usable without the integration.
 */
class AppolyeClient
{
    public function __construct(
        private string $baseUrl,
        private string $token,
        private bool $enabled
    ) {
    }

    public static function fromSettings(): self
    {
        return new self(
            rtrim((string) Branding::setting('appolye_base_url', 'https://api.apploye.com'), '/'),
            (string) Branding::setting('appolye_api_token', ''),
            (bool) Branding::setting('appolye_enabled', false)
        );
    }

    public function isConfigured(): bool
    {
        return $this->enabled && $this->token !== '' && $this->baseUrl !== '';
    }

    /** Aggregated monthly hours for a member. Returns null if unavailable. */
    public function getMonthlyHours(string $memberRef, string $month): ?array
    {
        [$from, $to] = $this->monthRange($month);
        $data = $this->request('GET', $this->endpoint('monthly'), [
            'member'    => $memberRef,
            'startDate' => $from,
            'endDate'   => $to,
        ]);
        return $data === null ? null : $this->mapMonthly($data);
    }

    /** Daily hours breakdown for a member within a month. */
    public function getDailyHours(string $memberRef, string $month): ?array
    {
        [$from, $to] = $this->monthRange($month);
        return $this->request('GET', $this->endpoint('daily'), [
            'member' => $memberRef, 'startDate' => $from, 'endDate' => $to,
        ]);
    }

    /** Weekly hours breakdown for a member within a month. */
    public function getWeeklyHours(string $memberRef, string $month): ?array
    {
        [$from, $to] = $this->monthRange($month);
        return $this->request('GET', $this->endpoint('weekly'), [
            'member' => $memberRef, 'startDate' => $from, 'endDate' => $to,
        ]);
    }

    /** Endpoint paths — adjust here to match your Apploye API version. */
    private function endpoint(string $kind): string
    {
        return match ($kind) {
            'daily'   => '/v1/time-tracking/daily',
            'weekly'  => '/v1/time-tracking/weekly',
            default   => '/v1/time-tracking/summary',
        };
    }

    /**
     * Normalise an Apploye monthly payload into the fields this module stores.
     * Apploye typically reports seconds; adjust the keys/divisor to your plan.
     */
    private function mapMonthly(array $data): array
    {
        $node = $data['data'] ?? $data;
        $toHours = static fn($v) => round(((float) $v) / 3600, 2);
        // Prefer explicit hour fields; fall back to seconds-based fields.
        $worked = $node['workedHours'] ?? ($node['trackedSeconds'] ?? null);
        $productive = $node['productiveHours'] ?? ($node['activeSeconds'] ?? null);
        $overtime = $node['overtimeHours'] ?? ($node['overtimeSeconds'] ?? 0);
        $leave = $node['leaveHours'] ?? ($node['leaveSeconds'] ?? 0);

        return [
            'worked'     => isset($node['workedHours']) ? (float) $worked : $toHours((float) ($worked ?? 0)),
            'productive' => isset($node['productiveHours']) ? (float) $productive : $toHours((float) ($productive ?? 0)),
            'overtime'   => isset($node['overtimeHours']) ? (float) $overtime : $toHours((float) $overtime),
            'leave'      => isset($node['leaveHours']) ? (float) $leave : $toHours((float) $leave),
        ];
    }

    private function monthRange(string $month): array
    {
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        return [$start, $end];
    }

    /** Perform an authenticated request. Returns decoded JSON or null on error. */
    private function request(string $method, string $path, array $query = []): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }
        $url = $this->baseUrl . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $raw = null;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->token,
                    'Accept: application/json',
                ],
            ]);
            $raw = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($raw === false || $code < 200 || $code >= 300) {
                return null;
            }
        } else {
            $ctx = stream_context_create(['http' => [
                'method'  => $method,
                'header'  => "Authorization: Bearer {$this->token}\r\nAccept: application/json\r\n",
                'timeout' => 15,
                'ignore_errors' => true,
            ]]);
            $raw = @file_get_contents($url, false, $ctx);
            if ($raw === false) {
                return null;
            }
        }

        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : null;
    }
}
