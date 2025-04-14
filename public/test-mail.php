<?php
// test-mail.php
require_once dirname(__DIR__) . '/config/config.php';
/** @var string $baseUrl */
$baseUrl = BASE_DIR;

require_once $baseUrl . '/lib/ErrorLogger.php';
ErrorLogger::initialize($baseUrl . '/logs/error.log');
require_once $baseUrl . '/lib/Mailer.php'; // ajusta si está en otro nivel

$asunto = '🧪 Test desde SuperAdmin';
$mensaje = '
  <h2>Hola Luis 👋</h2>
  <p>Este es un mensaje de prueba para verificar que la clase <strong>Mailer</strong> funciona correctamente.</p>
  <p><em>Enviado el ' . date('d/m/Y H:i') . '</em></p>
';

$enviado = Mailer::enviarAlerta($asunto, $mensaje);

if ($enviado) {
  echo '✅ Correo enviado correctamente a tu buzón.';
} else {
  echo '❌ Error al enviar el correo. Revisá el log o configuración.';
}
