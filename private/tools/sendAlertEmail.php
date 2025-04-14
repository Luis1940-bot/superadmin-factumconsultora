<?php
require_once realpath(__DIR__ . '/../lib/Mailer.php');

require_once dirname(__DIR__) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__) . '/logs/error.log');
header('Content-Type: application/json; charset=utf-8');
/** 
 * @var array{timezone?: string} $_SESSION 
 */
if (isset($_SESSION['timezone']) && is_string($_SESSION['timezone'])) {
  date_default_timezone_set($_SESSION['timezone']);
} else {
  date_default_timezone_set('America/Argentina/Buenos_Aires');
}
// error_log(dirname(__DIR__));
$datos = file_get_contents("php://input");
$data = json_decode($datos, true);

if (
  empty($data['cliente']) ||
  empty($data['contacto']) ||
  empty($data['email'])
) {
  echo json_encode(['success' => false, 'message' => 'Faltan datos']);
  exit;
}

$templatePath = realpath(__DIR__ . '/../lib/emails/nuevoCliente.html');
if (!$templatePath || !file_exists($templatePath)) {
  error_log("❌ Plantilla HTML no encontrada: nuevoCliente.html");
  echo json_encode(['success' => false, 'message' => 'Plantilla no encontrada']);
  exit;
}
$template = file_get_contents($templatePath);


$html = strtr($template, [
  '{cliente}' => $data['cliente'],
  '{contacto}' => $data['contacto'],
  '{email}' => $data['email'],
]);

$asunto = "🔔 Nuevo cliente registrado: {$data['cliente']}";

Mailer::enviarAlerta($asunto, $html, $data['email']); // a contacto
Mailer::enviarAlerta($asunto, $html);                 // a admin

echo json_encode(['success' => true, 'message' => 'Correo enviado']);
