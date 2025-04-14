<?php
// === Configuración de correo SMTP para alertas o notificaciones

return [
  'host'       => 'mail.tenkiweb.com',
  'port'       => 587,
  'encryption' => 'tls', // ⚠️ TLS para puerto 587
  'username'   => 'alerta.tenki@tenkiweb.com',
  'password'   => ']SDGGL}#p.Ba',
  'from'       => 'alerta.tenki@tenkiweb.com',
  'from_name'  => 'TENKI Alerta',
  'bcc'        => 'luisglogista@gmail.com',
];
