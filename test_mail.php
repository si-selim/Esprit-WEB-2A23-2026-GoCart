<?php
require_once __DIR__ . '/config_mail.php';

$host = MAIL_HOST;
$port = MAIL_PORT;
$user = MAIL_USERNAME;
$pass = MAIL_PASSWORD;
$from = MAIL_FROM;
$to = 'sasougmati@gmail.com';

echo "Host: $host, Port: $port, User: $user\n";

$errno = 0;
$errstr = '';
$fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 30);
if (!$fp) {
    die("Failed to connect: $errstr ($errno)\n");
}
echo "Connected\n";
stream_set_timeout($fp, 30);

function expect_debug($fp, $code) {
    $line = '';
    do {
        $line = fgets($fp, 8192);
        if ($line === false) { echo "EOF\n"; return false; }
        echo "S: " . rtrim($line) . "\n";
    } while (strlen($line) >= 4 && $line[3] === '-');
    return substr($line, 0, 3) == (string)$code;
}
function cmd_debug($fp, $c) {
    echo "C: $c\n";
    fputs($fp, $c . "\r\n");
}

if (!expect_debug($fp, 220)) die("Failed 220\n");
cmd_debug($fp, "EHLO localhost");
if (!expect_debug($fp, 250)) die("Failed EHLO\n");

if ($port == 587) {
    cmd_debug($fp, "STARTTLS");
    if (!expect_debug($fp, 220)) die("Failed STARTTLS\n");
    if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        die("Failed to enable crypto\n");
    }
    cmd_debug($fp, "EHLO localhost");
    if (!expect_debug($fp, 250)) die("Failed EHLO 2\n");
}

cmd_debug($fp, "AUTH LOGIN");
if (!expect_debug($fp, 334)) die("Failed AUTH LOGIN\n");
cmd_debug($fp, base64_encode($user));
if (!expect_debug($fp, 334)) die("Failed USER\n");
cmd_debug($fp, base64_encode($pass));
if (!expect_debug($fp, 235)) die("Failed PASS\n");

echo "Success!\n";
