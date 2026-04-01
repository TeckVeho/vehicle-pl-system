<?php

namespace App\Services\Vpl;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for VPL (Vehicle P&L System) API.
 *
 * Handles JWT authentication, token caching, and retry logic.
 * Reference: docs/external-integration-spec.md §2
 */
class VplClient
{
    protected string $baseUrl;
    protected string $logChannel;
    protected int $retryTimes;
    protected int $retrySleep;

    public function __construct()
    {
        $this->baseUrl    = rtrim(config('vpl.base_url'), '/');
        $this->logChannel = config('vpl.log_channel', 'vpl-sync');
        $this->retryTimes = config('vpl.retry.times', 3);
        $this->retrySleep = config('vpl.retry.sleep', 500);
    }

    // ─── Authentication ───────────────────────────────────────────

    /**
     * Get a valid JWT token (cached until near-expiry).
     */
    public function getToken(): string
    {
        return Cache::remember('vpl_jwt_token', $this->tokenCacheTtl(), function () {
            return $this->login();
        });
    }

    /**
     * Perform login against VPL and return raw JWT string.
     */
    protected function login(): string
    {
        $identifier = config('vpl.auth.identifier');
        $password   = config('vpl.auth.password');

        if (!$identifier || !$password) {
            throw new \RuntimeException('VPL credentials not configured (VPL_AUTH_IDENTIFIER / VPL_AUTH_PASSWORD).');
        }

        $payload = ['password' => $password];

        if (str_contains($identifier, '@')) {
            $payload['email'] = $identifier;
        } else {
            $payload['userId'] = $identifier;
        }

        $response = Http::post("{$this->baseUrl}/api/auth/login", $payload);

        if ($response->failed()) {
            $this->log('error', 'VPL login failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException("VPL login failed: HTTP {$response->status()}");
        }

        $token = $response->json('token') ?? $response->json('accessToken');
        
        // If not in JSON, extract from Set-Cookie header (VPL auth returns auth-token cookie)
        if (!$token) {
            $headers = $response->headers()['Set-Cookie'] ?? [];
            if (is_string($headers)) {
                $headers = [$headers];
            }
            foreach ($headers as $header) {
                if (preg_match('/auth-token=([^;]+)/', $header, $matches)) {
                    $token = urldecode($matches[1]);
                    break;
                }
            }
        }

        if (!$token) {
            throw new \RuntimeException('VPL login response did not contain a token (checked JSON and auth-token cookie).');
        }

        $this->log('info', 'VPL login successful');

        return $token;
    }

    /**
     * Flush the cached JWT so the next call re-authenticates.
     */
    public function forgetToken(): void
    {
        Cache::forget('vpl_jwt_token');
    }

    /**
     * Cache TTL in seconds — refresh well before the 7-day expiry.
     */
    protected function tokenCacheTtl(): int
    {
        // Refresh 1 hour before actual expiry
        return max(config('vpl.token_ttl', 604800) - 3600, 60);
    }

    // ─── HTTP helpers ─────────────────────────────────────────────

    /**
     * POST JSON to a VPL endpoint with auth + retry.
     *
     * @return array Decoded JSON response
     */
    public function post(string $path, array $payload): array
    {
        return $this->request('post', $path, $payload);
    }

    /**
     * GET from a VPL endpoint with auth + retry.
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    /**
     * Core request method with retry + 401 re-auth logic.
     */
    protected function request(string $method, string $path, array $data = []): array
    {
        $url   = "{$this->baseUrl}{$path}";
        $token = $this->getToken();

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryTimes) {
            $attempt++;

            try {
                $pending = Http::withToken($token)
                    ->timeout(120)
                    ->acceptJson();

                $response = $method === 'get'
                    ? $pending->get($url, $data)
                    : $pending->post($url, $data);

                // Token expired — re-auth once then retry
                if ($response->status() === 401 && $attempt === 1) {
                    $this->forgetToken();
                    $token = $this->getToken();
                    continue;
                }

                if ($response->successful()) {
                    return $response->json() ?? [];
                }

                // Non-retryable client errors
                if ($response->clientError()) {
                    $this->log('error', "VPL {$method} {$path} client error", [
                        'status' => $response->status(),
                        'body'   => $response->json(),
                    ]);
                    return [
                        '_error'  => true,
                        '_status' => $response->status(),
                        '_body'   => $response->json(),
                    ];
                }

                // Server error — retry
                $this->log('warning', "VPL {$method} {$path} server error (attempt {$attempt})", [
                    'status' => $response->status(),
                ]);
            } catch (\Exception $e) {
                $lastException = $e;
                $this->log('warning', "VPL {$method} {$path} exception (attempt {$attempt})", [
                    'message' => $e->getMessage(),
                ]);
            }

            if ($attempt < $this->retryTimes) {
                usleep($this->retrySleep * 1000 * $attempt); // exponential-ish back-off
            }
        }

        $this->log('error', "VPL {$method} {$path} failed after {$this->retryTimes} attempts");

        if ($lastException) {
            throw $lastException;
        }

        return ['_error' => true, '_status' => 500, '_body' => 'Max retries exceeded'];
    }

    // ─── Logging ──────────────────────────────────────────────────

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->{$level}("[VplClient] {$message}", $context);
    }
}
