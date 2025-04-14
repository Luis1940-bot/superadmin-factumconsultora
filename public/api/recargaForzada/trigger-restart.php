<?php
// ruta: /api/trigger-restart.php

// Crear archivo .reload-flag
$flagFile = __DIR__ . '/.reload-flag';

if (!file_exists($flagFile)) {
  file_put_contents($flagFile, 'reload'); // contenido irrelevante
  echo '⚠️ Reinicio activado.';
} else {
  echo '✔️ Ya estaba activado.';
}
