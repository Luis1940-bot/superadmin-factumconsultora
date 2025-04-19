<?php
mb_internal_encoding('UTF-8');

require_once dirname(__DIR__) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__) . '/logs/error.log');

if (isset($_SESSION['timezone']) && is_string($_SESSION['timezone'])) {
  date_default_timezone_set($_SESSION['timezone']);
} else {
  date_default_timezone_set('America/Argentina/Buenos_Aires');
}
$baseDir = str_replace('\\', '/', dirname(__DIR__));
define('BASE_DIR', $baseDir);

/**
 * Detecta si estás en localhost
 */
function isLocalhost(): bool
{
  if (php_sapi_name() === 'cli' || php_sapi_name() === 'cli-server') {
    return true;
  }

  $host = $_SERVER['HTTP_HOST'] ?? '';
  $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

  return in_array($host, ['localhost', '127.0.0.1'], true)
    || in_array($remoteAddr, ['127.0.0.1', '::1'], true);
}

$currentHost = $_SERVER['HTTP_HOST'] ?? '';

if (isLocalhost()) {
  define('BASE_URL', 'http://localhost:8000');
} elseif (strpos($currentHost, 'test.factumconsultora.com') !== false) {
  define('BASE_URL', 'https://test.factumconsultora.com');
} else {
  define('BASE_URL', 'https://sadmin.factumconsultora.com');
}
// === Datos del sistema
define('APP_NAME', 'SuperAdmin Factum');
define('APP_AUTHOR', 'Luis Gimenez');
define('APP_LOGO', 'factum');
define('APP_LINKEDIN', 'https://linkedin.com/in/luisergimenez');
