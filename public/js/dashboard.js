import createButton from './modules/createElement/createButtons.js';
import readJSON from './modules/utils/read-JSON.js';

const objButtons = {};
let SERVER = '';
const cliente = document.getElementById('cliente-nombre')?.dataset?.cliente;
const clienteId = document.getElementById('cliente-id')?.dataset?.id;

async function getBaseUrl() {
  const response = await fetch('../load-config-js.php');
  if (!response.ok) {
    throw new Error('No se pudo obtener la configuración');
  }

  const data = await response.json();
  return data.baseUrl;
}

getBaseUrl().then((baseUrl) => {
  // console.log('🌐 Base URL obtenida:', baseUrl);
  SERVER = baseUrl;
});

function ejecutarAccion(type, ruta, name, jsonPath = null) {
  // const fullPath = `${SERVER}/${ruta}`;
  const queryParams = new URLSearchParams({
    cliente,
    id: clienteId,
    jsonPath,
    name,
  });
  const connector = ruta.includes('?') ? '&' : '?';
  const fullPath = `${SERVER}/${ruta}${connector}${queryParams.toString()}`;
  switch (type) {
    case 'popup':
      window.open(fullPath, '_blank');
      break;
    case 'navigate':
    default:
      window.location.href = fullPath;
  }
}

function asignarEventos(buttonsData) {
  const buttons = document.querySelectorAll('.button-selector-sadmin');

  // Solo asignar eventos a los botones dinámicos (los que vienen del JSON)
  buttonsData.forEach((item, index) => {
    const button = buttons[index];
    if (!button) return;
    button.addEventListener('click', () => {
      ejecutarAccion(item.type, item.ruta, item.name, item.jsonPath || null);
    });
  });
}

function completaButtons(clave) {
  const divButtons = document.getElementById('div-sadmin-buttons');
  divButtons.innerHTML = '';

  const botones = objButtons[clave];
  botones.forEach((item) => {
    const params = {
      text: item.name || '',
      name: item.name,
      class: 'button-selector-sadmin',
    };
    const newButton = createButton(params);
    divButtons.appendChild(newButton);
  });

  asignarEventos(botones);
}

function leeApp(json) {
  readJSON(json)
    .then((data) => {
      Object.assign(objButtons, data);
      completaButtons('Sad');
    })
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error('Error al cargar el archivo:', error);
    });
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    SERVER = await getBaseUrl();
    // console.log('🌐 Base URL obtenida:', SERVER);
    leeApp('app');
  } catch (err) {
    console.error('❌ Error al obtener base URL:', err);
  }
});
