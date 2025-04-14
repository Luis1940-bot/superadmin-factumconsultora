<?php
// === Configuración de correo SMTP para alertas o notificaciones

return [
  'host'       => 'smtp.factumconsultora.com',
  'port'       => 587,
  'encryption' => 'tls', // ⚠️ TLS para puerto 587
  'username'   => 'alerta.factum@factumconsultora.com',
  'password'   => 'Factum2017admin',
  'from'       => 'alerta.factum@factumconsultora.com',
  'from_name'  => 'FACTUM Alerta',
  'bcc'        => 'luis@factumconsultora.com',
];
