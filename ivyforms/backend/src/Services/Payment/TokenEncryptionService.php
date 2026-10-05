<?php

namespace IvyForms\Services\Payment;

use RuntimeException;

/**
 * Encrypts/decrypts sensitive tokens at rest in WordPress options.
 */
class TokenEncryptionService
{
    private const CIPHER = 'AES-256-CBC';

    /**
     * @throws RuntimeException When OpenSSL cannot encrypt the token.
     */
    public function encrypt(string $plainText): string
    {
        if ($plainText === '') {
            return '';
        }

        $key = $this->getKey();
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($plainText, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new RuntimeException('Unable to encrypt payment token.');
        }

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $cipherText): string
    {
        if ($cipherText === '') {
            return '';
        }

        $decoded = base64_decode($cipherText, true);
        if ($decoded === false) {
            return '';
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);
        $plain = openssl_decrypt($encrypted, self::CIPHER, $this->getKey(), OPENSSL_RAW_DATA, $iv);

        return $plain !== false ? $plain : '';
    }

    private function getKey(): string
    {
        return hash('sha256', wp_salt('auth'), true);
    }
}
