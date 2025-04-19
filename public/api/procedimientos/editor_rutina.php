<?php
session_start();
require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
$dbname = $_GET['dbName'];
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
$mysqli->set_charset("utf8mb4");
$cliente = $_SESSION['selected_client_name'];
$clienteId = $_SESSION['selected_client_id'];
// Cargar nombres de rutinas para el selector
$rutinas = $mysqli->query("
  SELECT SPECIFIC_NAME, ROUTINE_TYPE 
  FROM information_schema.ROUTINES 
  WHERE ROUTINE_SCHEMA = '$dbname'
  ORDER BY ROUTINE_TYPE, SPECIFIC_NAME
");
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>🔧 Editor de Rutinas</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/api/procedimientos/rutinas.css?v=<?= time() ?>">
  <style>
    .editor-form {
      background: #000;
      color: #0f0;
      padding: 1.5rem;
      border: 1px solid #0f0;
      border-radius: 8px;
      margin-bottom: 2rem;
    }

    .editor-form label,
    .editor-form input,
    .editor-form select {
      display: block;
      width: 100%;
      margin-bottom: 1rem;
    }

    .editor-form input[type="text"] {
      background: #111;
      color: #0f0;
      border: 1px solid #0f0;
      padding: 8px;
    }

    #resultado {
      white-space: pre-wrap;
      background: #111;
      padding: 1rem;
      border: 1px solid #0f0;
      border-radius: 6px;
    }
  </style>
</head>

<body class="hacker-mode">
  <div class="datos-cabecera">
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
    <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
    ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  </div>
  <h1>🧪 Editor Visual de Rutinas</h1>

  <form id="formEditor" class="editor-form">
    <label>Rutina:
      <select name="nombre" required>
        <option value="">-- Seleccionar rutina --</option>
        <?php while ($r = $rutinas->fetch_assoc()): ?>
          <option value="<?= $r['SPECIFIC_NAME'] ?>"><?= $r['ROUTINE_TYPE'] ?>: <?= $r['SPECIFIC_NAME'] ?></option>
        <?php endwhile; ?>
      </select>
    </label>

    <label>Tipo:
      <select name="tipo">
        <option value="PROCEDURE">PROCEDURE</option>
        <option value="FUNCTION">FUNCTION</option>
      </select>
    </label>

    <label>Parámetros (separados por coma):</label>
    <input type="text" name="parametros" placeholder="Ej: 2023-01-01,2023-02-01" />

    <label>Formato de salida:</label>
    <select name="formato">
      <option value="json">JSON</option>
      <option value="csv">CSV</option>
    </select>

    <button type="submit">▶ Ejecutar</button>
  </form>

  <div id="resultado"></div>

  <script>
    document.getElementById('formEditor').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target;
      const nombre = form.nombre.value.trim();
      const tipo = form.tipo.value;
      const formato = form.formato.value;
      const parametros = form.parametros.value.split(',').map(p => p.trim()).filter(Boolean);
      const cadena = document.getElementById('cliente-id').textContent;
      const match = cadena.match(/mc\d{4}/);
      const dbName = match ? match[0] : null;
      const query = new URLSearchParams({
        nombre,
        tipo,
        formato,
        dbName
      });
      parametros.forEach(p => query.append('params[]', p));

      const url = `api_rutina.php?${query.toString()}`;

      const resultado = document.getElementById('resultado');
      resultado.textContent = '⏳ Ejecutando...';

      try {
        const res = await fetch(url);
        const isJson = formato === 'json';
        const text = await res.text();
        resultado.textContent = isJson ? JSON.stringify(JSON.parse(text), null, 2) : text;
      } catch (err) {
        resultado.textContent = '❌ Error al ejecutar rutina';
      }
    });
  </script>
</body>

</html>