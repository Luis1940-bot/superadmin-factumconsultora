<?php
$url = 'https://factumconsultora.com/scg-mccain/models/App/1/app.json';
$context = stream_context_create([
  "ssl" => [
    "verify_peer" => false,
    "verify_peer_name" => false,
  ]
]);

$data = @file_get_contents($url, false, $context);

if ($data === false) {
  $error = error_get_last();
  echo "🔥 Error: " . ($error['message'] ?? 'Sin mensaje');
} else {
  echo "✅ Cargó el JSON con éxito:<br><br>";
  echo "<pre>" . htmlspecialchars($data) . "</pre>";
}
