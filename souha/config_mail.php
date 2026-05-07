<?php
$__localMailConfig = __DIR__ . '/config_mail.local.php';
if (is_readable($__localMailConfig)) {
    require $__localMailConfig;
}
if (!defined('MAIL_HOST'))      define('MAIL_HOST', 'smtp.gmail.com');
if (!defined('MAIL_PORT'))      define('MAIL_PORT', 587);
if (!defined('MAIL_USERNAME'))  define('MAIL_USERNAME', getenv('BARCHATHON_MAIL_USER') ?: '');
if (!defined('MAIL_PASSWORD'))  define('MAIL_PASSWORD', getenv('BARCHATHON_MAIL_PASS') ?: '');
if (!defined('MAIL_FROM'))      define('MAIL_FROM', MAIL_USERNAME);
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'BarchaThon');
