<?php

namespace App\Utilities;

use Carbon\Carbon;

class HmacSignature
{
    /**
     * Generate HMAC-SHA256 signature for the given payload.
     *
     * @param  string  $apiSecret  Shared secret from ApiClient
     * @param  string  $timestamp  ISO 8601 timestamp (e.g. "2026-06-19T10:30:00Z")
     * @param  int  $cameraId  Camera ID
     */
    public static function generate(
        string $apiSecret,
        string $timestamp,
        int $cameraId,
    ): string {
        $message = implode("\n", [$timestamp, $cameraId]);

        return hash_hmac('sha256', $message, $apiSecret);
    }

    /**
     * Build the three auth headers ready for an HTTP request.
     *
     * @param  string  $apiKey  Public key from ApiClient
     * @param  string  $apiSecret  Shared secret from ApiClient
     * @param  int  $cameraId  Camera ID
     * @param  string|null  $timestamp  Pass your own or leave null to use now()
     * @return array<string, string> Headers keyed for HTTP client use
     */
    public static function headers(
        string $apiKey,
        string $apiSecret,
        int $cameraId,
        ?string $timestamp = null,
    ): array {
        $timestamp ??= Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z');

        return [
            'X-Api-Key' => $apiKey,
            'X-Timestamp' => $timestamp,
            'X-Signature' => self::generate($apiSecret, $timestamp, $cameraId),
        ];
    }

    /**
     * Verify a signature against the expected value (timing-safe).
     */
    public static function verify(
        string $apiSecret,
        string $timestamp,
        int $cameraId,
        string $signature,
    ): bool {
        return hash_equals(
            self::generate($apiSecret, $timestamp, $cameraId),
            $signature,
        );
    }
}
