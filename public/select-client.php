<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
/** @var string $baseUrl */
$baseUrl = BASE_URL;
if (!isset($_SESSION['superadmin_authenticated'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Seleccionar cliente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      background: #000;
      color: #0f0;
      font-family: 'Courier New', Courier, monospace;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2rem;
    }

    select,
    button {
      padding: 10px;
      margin-top: 20px;
      background: #111;
      color: #0f0;
      border: 1px solid #0f0;
      font-size: 1em;
    }
  </style>
  <link rel='shortcut icon' type='image/x-icon' href='<?php echo $baseUrl ?>/img/favicon.ico'>
</head>

<body>
  <h2>Seleccioná una planta</h2>
  <select id="clienteSelect">
    <option value="">-- Seleccioná --</option>
  </select>
  <button id="btnContinuar">✅ Continuar</button>

  <script>
    const select = document.getElementById('clienteSelect');
    const btn = document.getElementById('btnContinuar');

    fetch('https://factumconsultora.com/scg-mccain/models/log.json')
      // fetch("https://sadmin.factumconsultora.com/api/proxy.php?file=models/log.json")
      .then(res => res.json())
      .then(data => {
        data.plantas.forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.num;
          opt.textContent = p.name;
          select.appendChild(opt);
        });
      });

    btn.addEventListener('click', () => {
      const id = select.value;
      const name = select.options[select.selectedIndex].text;

      if (!id) {
        alert('Seleccioná una planta válida.');
        return;
      }

      fetch('set-client.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id,
            name
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            window.location.href = 'dashboard.php';
          } else {
            alert('Error al guardar cliente.');
          }
        });
    });
  </script>
</body>

</html>