<?php
require_once __DIR__ . '/../config_mail.php';

class Mailer {
    public static function send($to, $subject, $htmlBody) {
        $host = MAIL_HOST;
        $port = MAIL_PORT;
        $user = MAIL_USERNAME;
        $pass = MAIL_PASSWORD;
        $from = MAIL_FROM;
        $fromName = MAIL_FROM_NAME;

        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 30);
        if (!$fp) {
            return false;
        }
        stream_set_timeout($fp, 30);

        if (!self::expect($fp, 220)) { fclose($fp); return false; }
        self::cmd($fp, "EHLO localhost");
        if (!self::expect($fp, 250)) { fclose($fp); return false; }

        if ($port == 587) {
            self::cmd($fp, "STARTTLS");
            if (!self::expect($fp, 220)) { fclose($fp); return false; }
            if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                return false;
            }
            self::cmd($fp, "EHLO localhost");
            if (!self::expect($fp, 250)) { fclose($fp); return false; }
        }

        self::cmd($fp, "AUTH LOGIN");
        if (!self::expect($fp, 334)) { fclose($fp); return false; }
        self::cmd($fp, base64_encode($user));
        if (!self::expect($fp, 334)) { fclose($fp); return false; }
        self::cmd($fp, base64_encode($pass));
        if (!self::expect($fp, 235)) { fclose($fp); return false; }

        self::cmd($fp, "MAIL FROM:<$from>");
        if (!self::expect($fp, 250)) { fclose($fp); return false; }
        self::cmd($fp, "RCPT TO:<$to>");
        if (!self::expect($fp, 250)) { fclose($fp); return false; }
        self::cmd($fp, "DATA");
        if (!self::expect($fp, 354)) { fclose($fp); return false; }

        $headers  = "From: $fromName <$from>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: " . self::encodeHeader($subject) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        $body = preg_replace('/^\./m', '..', $htmlBody);
        fputs($fp, $headers . "\r\n" . $body . "\r\n.\r\n");
        if (!self::expect($fp, 250)) { fclose($fp); return false; }

        self::cmd($fp, "QUIT");
        fclose($fp);
        return true;
    }

    private static function cmd($fp, $c) {
        fputs($fp, $c . "\r\n");
    }

    private static function expect($fp, $code) {
        $line = '';
        do {
            $line = fgets($fp, 8192);
            if ($line === false) return false;
        } while (strlen($line) >= 4 && $line[3] === '-');
        return substr($line, 0, 3) == (string)$code;
    }

    private static function encodeHeader($text) {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
}
