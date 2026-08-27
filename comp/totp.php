<?php

/**
 * TOTP (RFC 6238) — одноразовые коды, совместимые с Microsoft Authenticator,
 * Google Authenticator и любым другим стандартным приложением.
 * SHA1, 6 цифр, период 30 секунд.
 */
class totp {

    const PERIOD = 30;
    const DIGITS = 6;
    const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret($bytes = 20) {
        return self::base32Encode(random_bytes($bytes));
    }

    public static function base32Encode($data) {
        $result = '';
        $buffer = 0;
        $bits = 0;
        for ($i = 0; $i < strlen($data); $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bits += 8;
            while ($bits >= 5) {
                $bits -= 5;
                $result .= self::ALPHABET[($buffer >> $bits) & 0x1F];
            }
        }
        if ($bits > 0) {
            $result .= self::ALPHABET[($buffer << (5 - $bits)) & 0x1F];
        }
        return $result;
    }

    public static function base32Decode($b32) {
        $b32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $b32));
        $result = '';
        $buffer = 0;
        $bits = 0;
        for ($i = 0; $i < strlen($b32); $i++) {
            $pos = strpos(self::ALPHABET, $b32[$i]);
            if ($pos === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $pos;
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $result .= chr(($buffer >> $bits) & 0xFF);
            }
        }
        return $result;
    }

    public static function getCode($secret, $slot) {
        $key = self::base32Decode($secret);
        $binSlot = pack('NN', 0, $slot);
        $hash = hash_hmac('sha1', $binSlot, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
        return str_pad($value % pow(10, self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Проверяет код с допуском ±$window интервалов (компенсация рассинхрона часов).
     * Возвращает номер интервала, которым сгенерирован код, либо false.
     */
    public static function verify($secret, $code, $window = 1) {
        $code = preg_replace('/\s+/', '', (string) $code);
        if (!preg_match('/^\d{' . self::DIGITS . '}$/', $code)) {
            return false;
        }
        $slot = (int) floor(time() / self::PERIOD);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::getCode($secret, $slot + $i), $code)) {
                return $slot + $i;
            }
        }
        return false;
    }

    public static function buildUri($issuer, $login, $secret) {
        return "otpauth://totp/" . rawurlencode($issuer) . ":" . rawurlencode($login)
                . "?secret=" . $secret
                . "&issuer=" . rawurlencode($issuer)
                . "&algorithm=SHA1&digits=" . self::DIGITS . "&period=" . self::PERIOD;
    }

    public static function formatSecret($secret) {
        return trim(chunk_split($secret, 4, ' '));
    }

}
