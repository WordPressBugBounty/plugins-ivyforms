<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\Common;

/**
 * Enhanced IP Detection Service
 *
 * Provides comprehensive IP address detection with support for various proxy
 * configurations, CDN services (Cloudflare), and security filtering options.
 *
 * @package IvyForms\Services\Security
 * @SuppressWarnings(PHPMD)
 */
class IpDetectionService
{
    /**
     * IP source priority order for detection
     */
    private const IP_SOURCES = [
        'HTTP_CF_CONNECTING_IP',    // Cloudflare connecting IP (highest priority if valid)
        'HTTP_CLIENT_IP',           // Shared internet IP
        'HTTP_X_REAL_IP',           // Real IP header (Nginx proxy)
        'HTTP_X_FORWARDED_FOR',     // Proxy forwarded IP
        'HTTP_X_FORWARDED',         // Alternative proxy header
        'HTTP_X_CLUSTER_CLIENT_IP', // Cluster client IP
        'HTTP_FORWARDED_FOR',       // Another proxy variant
        'HTTP_FORWARDED',           // Standard forwarded header
        'REMOTE_ADDR'               // Direct connection IP (fallback)
    ];

    /**
     * Cloudflare IP ranges for validation
     */
    private const CLOUDFLARE_IP_RANGES = [
        '199.27.128.0/21',
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/12',
        '172.64.0.0/13',
        '131.0.72.0/22'
    ];

    /**
     * Required Cloudflare headers for validation
     */
    private const CLOUDFLARE_HEADERS = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CF_IPCOUNTRY',
        'HTTP_CF_RAY',
        'HTTP_CF_VISITOR'
    ];

    /**
     * Get user IP address with enhanced detection
     *
     * @param bool $allowPrivate Whether to allow private IP addresses (default: false)
     * @param bool $validateCloudflare Whether to validate Cloudflare IPs (default: true)
     * @return string The detected IP address or empty string if none found
     */
    public static function getUserIpAddress(bool $allowPrivate = false, bool $validateCloudflare = true): string
    {
        // Try Cloudflare IP first if detected and validation is enabled
        if ($validateCloudflare) {
            $cloudflareIp = self::getCloudflareIp();
            if ($cloudflareIp !== null) {
                return $cloudflareIp;
            }
        }

        // Check all IP sources in priority order
        foreach (self::IP_SOURCES as $source) {
            $ip = self::getServerVariable($source);

            if (empty($ip)) {
                continue;
            }

            // Handle comma-separated IPs (common in X-Forwarded-For)
            if (strpos($ip, ',') !== false) {
                $ips = array_map('trim', explode(',', $ip));
                foreach ($ips as $candidateIp) {
                    $validIp = self::validateAndCleanIp($candidateIp, $allowPrivate);
                    if ($validIp !== '') {
                        return $validIp;
                    }
                }
            } else {
                $validIp = self::validateAndCleanIp($ip, $allowPrivate);
                if ($validIp !== '') {
                    return $validIp;
                }
            }
        }

        return '';
    }

    /**
     * Get the most reliable IP address (prefers direct connection)
     *
     * @param bool $allowPrivate Whether to allow private IP addresses
     * @return string The most reliable IP address
     */
    public static function getReliableIpAddress(bool $allowPrivate = false): string
    {
        // First try direct connection (most reliable)
        $directIp = self::getServerVariable('REMOTE_ADDR');
        if (!empty($directIp)) {
            $validIp = self::validateAndCleanIp($directIp, $allowPrivate);
            if ($validIp !== '') {
                return $validIp;
            }
        }

        // Fallback to full detection
        return self::getUserIpAddress($allowPrivate, true);
    }

    /**
     * Check if the current request is from Cloudflare
     *
     * @return bool True if request appears to be from Cloudflare
     */
    public static function isCloudflareRequest(): bool
    {
        return self::isCloudflare();
    }

    /**
     * Get detailed IP information for debugging
     *
     * @return array<string, mixed> Array containing all IP detection details
     */
    public static function getIpDebugInfo(): array
    {
        $debugInfo = [
            'detected_ip' => self::getUserIpAddress(),
            'reliable_ip' => self::getReliableIpAddress(),
            'is_cloudflare' => self::isCloudflare(),
            'cloudflare_ip' => self::getCloudflareIp(),
            'sources' => []
        ];

        foreach (self::IP_SOURCES as $source) {
            $value = self::getServerVariable($source);
            if (!empty($value)) {
                $debugInfo['sources'][$source] = $value;
            }
        }

        return $debugInfo;
    }

    /**
     * Get Cloudflare IP if request is from Cloudflare
     *
     * @return string|null The Cloudflare connecting IP or null if not valid
     */
    private static function getCloudflareIp(): ?string
    {
        if (!self::isCloudflare()) {
            return null;
        }

        $cfIP = self::getServerVariable('HTTP_CF_CONNECTING_IP');
        if (empty($cfIP)) {
            return null;
        }

        // Sanitize and validate the IP
        $sanitizedIp = sanitize_text_field(wp_unslash($cfIP));
        return filter_var($sanitizedIp, FILTER_VALIDATE_IP) ? $sanitizedIp : null;
    }

    /**
     * Check if the request is from Cloudflare
     *
     * @return bool True if request is from Cloudflare
     */
    private static function isCloudflare(): bool
    {
        // Check required Cloudflare headers first
        foreach (self::CLOUDFLARE_HEADERS as $header) {
            if (!isset($_SERVER[$header])) {
                return false;
            }
        }

        // Get the source IP for validation
        $sourceIp = self::getSourceIpForValidation();
        if (empty($sourceIp)) {
            return false;
        }

        // Validate if the source IP is from Cloudflare's ranges
        return self::isCloudflareIp($sourceIp);
    }

    /**
     * Get source IP for Cloudflare validation
     *
     * @return string The source IP address
     */
    private static function getSourceIpForValidation(): string
    {
        $sources = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($sources as $source) {
            $ip = self::getServerVariable($source);
            if (!empty($ip)) {
                return sanitize_text_field(wp_unslash($ip));
            }
        }

        return '';
    }

    /**
     * Validate if IP is from Cloudflare's IP ranges
     *
     * @param string $ip The IP address to validate
     * @return bool True if IP is from Cloudflare
     */
    private static function isCloudflareIp(string $ip): bool
    {
        foreach (self::CLOUDFLARE_IP_RANGES as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate and clean an IP address
     *
     * @param string $ip The IP address to validate
     * @param bool $allowPrivate Whether to allow private IP addresses
     * @return string The cleaned IP address or empty string if invalid
     */
    private static function validateAndCleanIp(string $ip, bool $allowPrivate): string
    {
        // Remove any surrounding whitespace
        $ip = trim($ip);

        // Remove port number if present (e.g., "192.168.1.1:8080")
        if (strpos($ip, ':') !== false && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = explode(':', $ip)[0];
        }

        // Validate IP format
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return '';
        }

        // Check for private IP addresses if not allowed
        if (!$allowPrivate && self::isPrivateIp($ip)) {
            return '';
        }

        return $ip;
    }

    /**
     * Check if an IP address is private/local
     *
     * @param string $ip The IP address to check
     * @return bool True if the IP is private
     */
    private static function isPrivateIp(string $ip): bool
    {
        // Use filter_var for private IP detection
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Check if an IP address is within a given CIDR range
     *
     * @param string $ip The IP address to check
     * @param string $range The IP range in CIDR notation
     * @return bool True if the IP is in the range
     */
    private static function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            $range .= '/32';
        }

        [$subnet, $netmask] = explode('/', $range, 2);
        $rangeDecimal = ip2long($subnet);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - (int)$netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;

        return ($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal);
    }

    /**
     * Safely get server variable
     *
     * @param string $key The server variable key
     * @return string The server variable value or empty string
     */
    private static function getServerVariable(string $key): string
    {
        return $_SERVER[$key] ?? '';
    }
}
