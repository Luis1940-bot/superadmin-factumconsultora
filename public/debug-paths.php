<?php
require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: text/plain');

echo "🔍 DEBUG DE RUTAS\n";
echo "==================\n\n";

echo "📂 __DIR__:            " . __DIR__ . "\n";
echo "📂 BASE_DIR:          " . BASE_DIR . "\n";
echo "🌍 BASE_URL:          " . BASE_URL . "\n";

$targetFiles = ['app', 'log', 'config'];

foreach ($targetFiles as $file) {
  $path = BASE_DIR . "/config/{$file}.json";
  echo "\n🔎 Verificando: {$file}.json\n";
  echo "🛣️  Ruta completa:     {$path}\n";

  if (file_exists($path)) {
    echo "✅ Archivo encontrado\n";
  } else {
    echo "❌ Archivo NO encontrado\n";
  }
}
