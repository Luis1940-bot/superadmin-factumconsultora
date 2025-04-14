<?php

$root = __DIR__; // Cambiá si querés escanear desde otro lugar

$includedPatterns = [
  '/(require|include)(_once)?\s*\(?\s*[\'"](.+?)[\'"]\s*\)?\s*;/',
];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

$errors = [];


foreach ($rii as $file) {
  if ($file->isDir()) continue;
  if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
  if (strpos($file->getPathname(), 'vendor/') !== false) continue;

  $lines = file($file->getPathname());

  foreach ($lines as $num => $line) {
    $trimmed = trim($line);
    if (strpos($trimmed, '//') === 0 || strpos($trimmed, '#') === 0 || strpos($trimmed, '/*') === 0) continue;

    foreach ($includedPatterns as $pattern) {
      if (preg_match($pattern, $line, $matches)) {
        $includedPath = $matches[3];

        // Evita rutas dinámicas
        if (strpos($includedPath, '__DIR__') !== false || strpos($includedPath, 'realpath') !== false) continue;

        $dirOfCurrentFile = dirname($file->getPathname());
        $fullPath = realpath($dirOfCurrentFile . DIRECTORY_SEPARATOR . $includedPath);

        if ($fullPath === false || !file_exists($fullPath)) {
          $errors[] = [
            'file' => $file->getPathname(),
            'line' => $num + 1,
            'badPath' => $includedPath
          ];
        }
      }
    }
  }
}

if (empty($errors)) {
  echo "🎉 Todos los includes parecen correctos.\n";
} else {
  echo "🚨 Includes rotos encontrados:\n\n";
  foreach ($errors as $error) {
    echo "- Archivo: {$error['file']}\n";
    echo "  Línea: {$error['line']}\n";
    echo "  Ruta rota: {$error['badPath']}\n\n";
  }
}
