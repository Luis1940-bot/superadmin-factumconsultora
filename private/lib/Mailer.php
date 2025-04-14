<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/config/config.php';
/** @var string $baseUrl */
$baseUrl = BASE_DIR;
require_once $baseUrl . '/vendor/autoload.php';

class Mailer
{
  private static array $config;

  public static function init(): void
  {
    self::$config = require dirname(__DIR__) . '/config/mail.php';
  }


  public static function enviarAlerta(string $asunto, string $htmlCuerpo, ?string $destinatario = null): bool
  {
    self::init();
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->CharSet      = 'UTF-8';
      $mail->Encoding     = 'quoted-printable';
      $mail->SMTPAuth     = true;
      $mail->Host         = self::$config['host'];
      $mail->Username     = self::$config['username'];
      $mail->Password     = self::$config['password'];
      $mail->Port         = self::$config['port'];
      $mail->SMTPSecure   = self::$config['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Timeout      = 60;
      $mail->SMTPKeepAlive = true;

      // Agregado: Permitir certificados autofirmados si hace falta
      $mail->SMTPOptions = [
        'ssl' => [
          'verify_peer'       => false,
          'verify_peer_name'  => false,
          'allow_self_signed' => true,
        ],
      ];

      $mail->setFrom(self::$config['from'], self::$config['from_name']);

      $to = $destinatario ?? self::$config['from'];
      $mail->addAddress($to);

      if (!empty(self::$config['bcc'])) {
        $mail->addBCC(self::$config['bcc']);
      }

      $mail->isHTML(true);
      $mail->Subject = $asunto;
      $mail->Body    = $htmlCuerpo;

      $mail->send();
      return true;
    } catch (Exception $e) {
      error_log("❌ Error al enviar correo: {$mail->ErrorInfo}");
      return false;
    }
  }
}
