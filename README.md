# SuperAdmin - Factum

Herramientas internas para asistencia remota, administración y control de sistemas en producción.

## Funcionalidades

- Forzar recarga remota
- Selección de cliente desde JSON
- Acceso con token seguro
- Visualización de herramientas de monitoreo (en desarrollo)

## Instalación

Este es un módulo frontend estático. Puede deployarse como:

- Subdominio (ej: sadmin.factumconsultora.com)
- Carpeta protegida dentro del dominio principal

superadmin-factumconsultora/
├── public/ # Contenido accesible por navegador
│ ├── index.php # Punto de entrada principal
│ ├── css/
│ │ └── main.css
│ ├── js/
│ │ └── app.js
│ ├── img/
│ ├── api/ # API accesible desde frontend
│ │ ├── getLogData.php
│ │ ├── router.php # 🔁 Nuevo concentrador de rutas (tipo backend central)
│ │ ├── AuthUser/
│ │ │ ├── index.php
│ │ │ ├── auth.js
│ │ │ └── css/
│ │ │ └── auth.css
│ └── favicon.ico
│
├── tools/ # Lógica PHP privada (no accesible desde navegador)
│ ├── getLogData.php
│ ├── reload-flag.php
│ └── ... (más controladores y lógica de backend)
│
├── config/
│ ├── auth_token.php # Ignorado por Git
│ ├── config.php
│ └── config.js # Config compartida si es expuesta
│
├── models/ # Datos en crudo (mock JSON, estructuras, etc.)
│ └── log.json
│
├── core/ # Helpers y lógica común
│ ├── helpers.php
│ └── utils.js
│
├── .vscode/
│ └── settings.json
├── .eslintrc.js
├── .prettierrc
├── .eslintignore
├── .prettierignore
├── phpstan.neon
├── composer.json
├── package.json
├── README.md
└── start.bat # Script local para desarrollo
