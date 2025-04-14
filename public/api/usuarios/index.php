<?php
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https: example.com; script-src 'self' 'nonce-$nonce' cdn.example.com; style-src 'self' 'nonce-$nonce' cdn.example.com; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Access-Control-Allow-Origin: https://factumconsultora.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
require_once dirname(__DIR__, 3) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
$charset = "utf8mb4";
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}
mysqli_set_charset($mysqli, "utf8mb4");

// Obtener usuarios
$sql = "SELECT u.idusuario, u.nombre, u.area, LOWER(u.activo) AS activo, u.puesto, u.modificacion, u.mail, 
        u.verificador, u.cod_verificador, u.idtipousuario, t.tipo, u.firma, 
        u.mi_cfg, u.idLTYcliente, l.cliente 
        FROM usuario u
        INNER JOIN LTYcliente l ON l.idLTYcliente = u.idLTYcliente
        INNER JOIN tipousuario t ON t.idtipousuario = u.idtipousuario 
        ORDER BY u.idusuario ASC";
$result = $mysqli->query($sql);

// Obtener tipos de usuario
$sqlTipos = "SELECT idtipousuario, tipo FROM tipousuario ORDER BY idtipousuario ASC";
$resultTipos = $mysqli->query($sqlTipos);
$tiposUsuarios = [];
while ($row = $resultTipos->fetch_assoc()) {
  $tiposUsuarios[] = $row;
}

// Obtener clientes
$sqlClientes = "SELECT idLTYcliente, cliente FROM LTYcliente ORDER BY idLTYcliente ASC";
$resultClientes = $mysqli->query($sqlClientes);
$clientes = [];
while ($row = $resultClientes->fetch_assoc()) {
  $clientes[] = $row;
}
$favicon = BASE_URL . "/img/favicon.ico";
$cssUrl = BASE_URL . "/api/usuarios/update_usuario.css?v=" . time();
$jsUrl = BASE_URL . "/api/usuarios/update_usuario.js?v=" . time();
?>
<!DOCTYPE html>
<html>

<head>
  <title>Gestión de Usuarios</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <h1>Gestión de Usuarios</h1>
  <input type="text" id="searchInput" placeholder="Buscar por id, nombre, email o cliente" />
  <div class="div-sadmin-buttons">
    <button type="button" class="button-selector-sadmin" id="cerrarVentanaBtn">🚪 Cerrar</button>
  </div>
  <table id="usuariosTable">
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Área</th>
      <th>Activo</th>
      <th>Puesto</th>
      <th>Mail</th>
      <th>Verificador</th>
      <th>Código Verificador</th>
      <th>Tipo de Usuario</th>
      <th>Cliente</th>
      <th>Acciones</th>
    </tr>
    <?php while ($usuario = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $usuario['idusuario'] ?></td>
        <td><?= $usuario['nombre'] ?></td>
        <td><?= $usuario['area'] ?></td>
        <td><?= $usuario['activo'] ?></td>
        <td><?= $usuario['puesto'] ?></td>
        <td><?= $usuario['mail'] ?></td>
        <td><?= $usuario['verificador'] ?></td>
        <td><?= $usuario['cod_verificador'] ?></td>
        <td><?= $usuario['tipo'] ?></td>
        <td><?= $usuario['cliente'] ?></td>
        <td>
          <button class="btn-edit"
            data-id="<?= $usuario['idusuario'] ?>"
            data-nombre="<?= htmlspecialchars($usuario['nombre'], ENT_QUOTES) ?>"
            data-area="<?= htmlspecialchars($usuario['area'], ENT_QUOTES) ?>"
            data-activo="<?= $usuario['activo'] ?>"
            data-puesto="<?= htmlspecialchars($usuario['puesto'], ENT_QUOTES) ?>"
            data-mail="<?= htmlspecialchars($usuario['mail'], ENT_QUOTES) ?>"
            data-verificador="<?= $usuario['verificador'] ?>"
            data-codver="<?= htmlspecialchars($usuario['cod_verificador']) ?>"
            data-tipousuario="<?= $usuario['idtipousuario'] ?>"
            data-cliente="<?= $usuario['idLTYcliente'] ?>"> <!-- ✅ AHORA SÍ DENTRO DEL BOTÓN -->
            Editar
          </button>
        </td>


      </tr>
    <?php endwhile; ?>
  </table>
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" id="cerrarModalBtn">&times;</span>

      <h2>Editar Usuario</h2>
      <form id="formEditarUsuario">
        <input type="hidden" id="edit_idusuario" name="idusuario">
        <label>Nombre: <input type="text" id="edit_nombre" name="edit_nombre"></label><br>
        <label>Área: <input type="text" id="edit_area" name="area"></label><br>
        <label>Activo: <select id="edit_activo" name="activo">
            <option value="s">s</option>
            <option value="n">n</option>
          </select></label><br>
        <label>Puesto: <input type="text" id="edit_puesto" name="puesto"></label><br>
        <label>Mail: <input type="text" id="edit_mail" name="mail"></label><br>
        <label>Verificador: <select id="edit_verificador" name="verificador">
            <option value="1">1</option>
            <option value="0">0</option>
          </select></label><br>
        <label>Código Verificador: <input type="text" id="edit_cod_verificador" name="cod_verificador"></label><br>
        <label>Tipo Usuario: <select id="edit_idtipousuario" name="idtipousuario">
            <?php foreach ($tiposUsuarios as $tipo) {
              echo "<option value='{$tipo['idtipousuario']}'>{$tipo['tipo']}</option>";
            } ?>
          </select></label><br>
        <label>Cliente: <select id="edit_idLTYcliente" name="idLTYcliente">
            <?php foreach ($clientes as $cliente) {
              echo "<option value='{$cliente['idLTYcliente']}'>{$cliente['cliente']}</option>";
            } ?>
          </select></label><br>
        <button type="button" class="btn-guardar" id="btnGuardarUsuario">Guardar</button>



      </form>
    </div>
  </div>


  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>