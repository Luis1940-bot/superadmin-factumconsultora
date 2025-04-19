<?php
session_start();

header('Content-Type: text/html; charset=utf-8');
$nonce = base64_encode(random_bytes(16));

header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; script-src 'self' 'nonce-$nonce'; style-src 'self' 'nonce-$nonce'; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

header("Access-Control-Allow-Origin: https://factumconsultora.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Incluir rutas absolutas desde raíz del proyecto
require_once dirname(dirname(dirname(__DIR__))) . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(__DIR__))) . '/private/logs/error.log');
require_once dirname(dirname(dirname(__DIR__))) . '/private/config/config.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Calculadora de Hash</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style nonce="<?= $nonce ?>">
    body {
      background: #000;
      color: #0f0;
      font-family: 'Courier New', Courier, monospace;
      padding: 2rem;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    h2 {
      font-size: 1.6rem;
      margin-bottom: 1rem;
      text-align: center;
    }

    form,
    .hash-result {
      width: 100%;
      max-width: 600px;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    label {
      font-weight: bold;
    }

    input[type="text"] {
      padding: 12px 24px;
      background-color: #000;
      color: #0f0;
      border: 1px solid #0f0;
      border-radius: 5px;
      font-family: 'Courier New', Courier, monospace;
      font-size: 1rem;
      outline: none;
    }

    input[type="text"]:focus {
      background-color: #111;
      box-shadow: 0 0 5px #0f0;
    }

    button {
      padding: 12px 24px;
      font-family: inherit;
      font-size: 1rem;
      background-color: #111;
      color: #0f0;
      border: 1px solid #0f0;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.2s ease-in-out;
    }

    button:hover {
      background-color: #0f0;
      color: #000;
    }

    .hash-output {
      background-color: #111;
      border: 1px solid #0f0;
      border-radius: 5px;
      padding: 1rem;
      font-size: 1.2rem;
      word-break: break-all;
    }

    .button-group {
      width: 100%;
      max-width: 600px;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 1rem;
    }
  </style>
</head>

<body>
  <h2>🔐 Calculadora de Hash (RIPEMD-160)</h2>
  <form method="post">
    <label for="inputText">Ingresa el texto:</label>
    <input type="text" id="inputText" name="inputText" required placeholder="Escribe algo para hashear...">
    <button type="submit">⚙️ Calcular</button>
  </form>

  <?php if ($_SERVER["REQUEST_METHOD"] === "POST") :
    $inputText = $_POST["inputText"] ?? '';
    if (is_string($inputText) || is_numeric($inputText)) :
      $hash = hash('ripemd160', strval($inputText)); ?>
      <div class="hash-result">
        <label for="hashValue">Resultado del Hash:</label>
        <div class="hash-output" id="hashValue"><?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?></div>
        <button type="button" onclick="copiarHash()">📋 Copiar Hash</button>
      </div>
    <?php else : ?>
      <p style="color: red;">❌ El valor ingresado no es válido.</p>
  <?php endif;
  endif; ?>

  <div class="button-group" style="margin-top: 2rem;">
    <form action="../../dashboard.php" method="post">
      <button type="submit">🚪 Cerrar</button>
    </form>
  </div>

  <script nonce="<?= $nonce ?>">
    function copiarHash() {
      const hash = document.getElementById('hashValue').textContent;
      navigator.clipboard.writeText(hash).then(() => {
        alert("✅ Hash copiado al portapapeles");
      }).catch(err => {
        alert("❌ Error al copiar el hash");
        console.error(err);
      });
    }
  </script>
</body>

</html>