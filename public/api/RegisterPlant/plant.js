import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

// eslint-disable-next-line no-unused-vars
let idClienteActual;
// eslint-disable-next-line no-unused-vars
let nameClienteActual;

function validarEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

async function subirImagenes(img, planta, baseUrl) {
  if (
    !img ||
    !img.src?.length ||
    !img.extension?.length ||
    !img.fileName?.length
  ) {
    // eslint-disable-next-line no-console
    console.warn('⚠️ Imagen inválida o vacía');
    return null;
  }

  const payload = {
    fileName: [String(img.fileName?.[0] || '')],
    src: [String(img.src?.[0] || '')],
    extension: [String(img.extension?.[0] || '')],
    plant: [String(planta)],
    carpeta: ['Logos/'],
  };

  const formData = new FormData();
  formData.append('ruta', '/subirImagen'); // 👈 obligatorio para router
  formData.append('imgBase64', JSON.stringify(payload));

  try {
    const res = await fetch(`${baseUrl}/api/router.php`, {
      method: 'POST',
      body: formData,
    });

    try {
      const result = await res.json();

      return result;
    } catch (e) {
      console.error('❌ Error al subir imagen:', e);
      return null;
    }
  } catch (error) {
    console.error('❌ Error al subir imagen:', error);
    return null;
  }
}

function checaRequeridos() {
  const cliente = document.getElementById('cliente');
  cliente.classList.remove('input-plant-requerido');
  cliente.classList.add('input-plant');
  if (cliente.value === '') {
    cliente.classList.remove('input-plant');
    cliente.classList.add('input-plant-requerido');
    return false;
  }

  const contacto = document.getElementById('contacto');
  contacto.classList.remove('input-plant-requerido');
  contacto.classList.add('input-plant');
  if (contacto.value === '') {
    contacto.classList.remove('input-plant');
    contacto.classList.add('input-plant-requerido');
    return false;
  }

  const email = document.getElementById('email');
  email.classList.remove('input-plant-requerido');
  email.classList.add('input-plant');
  if (email.value === '') {
    email.classList.remove('input-plant');
    email.classList.add('input-plant-requerido');
    return false;
  }

  if (!validarEmail(email.value)) {
    email.classList.remove('input-plant');
    email.classList.add('input-plant-requerido');
    return false;
  }

  // 🖼️ Obtener el logo si está presente y completo
  const imagenLogo = document.getElementById('idImgLogo');
  let objetoImagen = null;

  if (
    imagenLogo &&
    imagenLogo.getAttribute('fileName') &&
    imagenLogo.getAttribute('extension') &&
    imagenLogo.src &&
    imagenLogo.src.startsWith('data:image/')
  ) {
    objetoImagen = {
      fileName: [imagenLogo.getAttribute('fileName')],
      src: [imagenLogo.src],
      extension: [imagenLogo.getAttribute('extension')],
      plant: [],
      carpeta: ['Logos/'],
    };
  }

  // 🧩 Armar objeto de datos
  const objeto = {
    cliente: cliente.value,
    detalle: '',
    contacto: contacto.value,
    email: email.value,
    activo: '',
    ...(objetoImagen ? { objetoImagen } : {}),
  };

  return { add: true, objeto };
}

async function nuevaCompania() {
  try {
    const envia = checaRequeridos();
    if (envia.add) {
      // eslint-disable-next-line no-unused-vars
      const { baseUrl, routes } = await getConfig();
      const detalle = document.getElementById('detalle');
      envia.objeto.detalle = detalle.value;
      envia.objeto.activo = 's';
      const objetoLimpio = { ...envia.objeto };
      // delete objetoLimpio.objetoImagen;
      const obj = {
        ruta: '/addCompania',
        objeto: objetoLimpio,
      };
      const datos = JSON.stringify(obj);
      const response = await fetch(`${baseUrl}/api/router.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: datos,
      });
      const data = await response.json();
      if (data.success) {
        const newPlant = {
          name: envia.objeto.cliente,
          num: data.id,
        };

        const obj1 = {
          ruta: '/escribeJSON',
          objeto: newPlant,
        };
        const obj2 = {
          ruta: '/creaJSONapp',
          objeto: newPlant,
        };
        const [res1, res2] = await Promise.all([
          fetch(`${baseUrl}/api/router.php`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(obj1),
          }),
          fetch(`${baseUrl}/api/router.php`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(obj2),
          }),
        ]);

        // 🔄 Leer el JSON de cada respuesta
        const [r1, r2] = await Promise.all([res1.json(), res2.json()]);

        if (r1.success && r2.success) {
          const mailPayload = {
            ruta: '/alertaEmail',
            cliente: envia.objeto.cliente,
            contacto: envia.objeto.contacto,
            email: envia.objeto.email,
          };

          try {
            const res3 = await fetch(`${baseUrl}/api/router.php`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(mailPayload),
            });
            const r3 = await res3.json(); // ✅ leer body una sola vez
            // eslint-disable-next-line no-console
            console.log('📧 Email enviado:', r3);
            // if (envia.objeto.objetoImagen) {
            //   const idClienteNuevo = data.id; // El ID devuelto por addCompania
            //   await subirImagenes(
            //     envia.objeto.objetoImagen,
            //     idClienteNuevo,
            //     baseUrl,
            //   );
            // }
            if (envia.objeto.objetoImagen) {
              const idClienteNuevo = data.id;
              await subirImagenes(
                envia.objeto.objetoImagen,
                idClienteNuevo,
                baseUrl,
              );
            }
            // ✅ Mostrar mensaje de éxito
            mostrarMensaje('✅ Compañía registrada con éxito.', 'ok');

            // ✅ Cerrar formulario (o redirigir / cerrar modal)
            setTimeout(() => {
              window.close(); // O cerrar modal si aplica
            }, 3000); // Espera 3 segundos
          } catch (e) {
            // eslint-disable-next-line no-console
            console.warn('⚠️ Error al enviar email:', e);
          }
        } else {
          // eslint-disable-next-line no-console
          console.warn('❌ No se completaron las tareas previas:', { r1, r2 });
        }
      }
    }
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
  }
}

function cargarImagen() {
  const inputLogo = document.getElementById('logo');
  const imgPreview = document.getElementById('idImgLogo');
  const spanLogo = document.getElementById('idSpanLogo');

  inputLogo.click(); // Dispara el selector

  inputLogo.addEventListener(
    'change',
    (e) => {
      const file = e.target.files[0];

      if (!file) return;
      // 🧠 Validar imagen (solo PNG o JPG por ejemplo)
      const validTypes = ['image/png', 'image/jpeg'];
      if (!validTypes.includes(file.type)) {
        spanLogo.textContent = '⚠️ Solo se permiten imágenes PNG o JPG.';
        inputLogo.value = '';
        imgPreview.src = '#';
        return;
      }

      const extension = file.type.split('/')[1]; // ej. "png" o "jpeg"
      const reader = new FileReader();
      reader.onload = function mostrarPreview(ev) {
        const base64 = ev.target.result;
        const fileName = `logo.${extension}`;
        imgPreview.src = base64;
        imgPreview.style.display = 'block';
        spanLogo.textContent = `📂 Archivo cargado: ${fileName}`;

        // 🧠 Opcional: guardar en atributos si los querés reutilizar luego
        imgPreview.setAttribute('fileName', fileName);
        imgPreview.setAttribute('extension', extension);

        // 🧠 Guardar en objeto global si todavía no tenés `envia`
        window.logoImagen = {
          src: [base64],
          fileName: [fileName],
          extension: [extension],
        };
      };

      reader.readAsDataURL(file);
    },
    { once: true },
  );
}

function extraerDatosCliente() {
  const h1 = document.querySelector('h1');
  const p = document.querySelector('p');

  // Extraer nombre del cliente desde el H1
  const nombreCliente = h1.textContent.replace('🎛️ Panel de ', '').trim();

  // Extraer ID desde el P usando expresión regular
  const match = p.textContent.match(/ID:\s*(\d+)/);
  const idCliente = match ? match[1] : null;
  h1.style.display = 'none';
  p.style.display = 'none';
  return { nombreCliente, idCliente };
}

document.addEventListener('DOMContentLoaded', () => {
  const { nombreCliente, idCliente } = extraerDatosCliente();
  // console.log('🏢 Cliente:', nombreCliente);
  // console.log('🆔 ID:', idCliente);
  idClienteActual = idCliente;
  nameClienteActual = nombreCliente;
  document.addEventListener('keydown', (e) => {
    if (e.target.matches('.input-plant, .textarea-plant')) {
      if (e.key === ',' || e.key === ':' || e.key === "'" || e.key === '"') {
        e.preventDefault();
      }
    }
  });
  const idRegisterButton = document.getElementById('idRegisterButton');
  idRegisterButton.addEventListener('click', (e) => {
    const clase = e.target.className;
    if (clase === 'button-plant') {
      nuevaCompania();
    }
  });
  const logoBtn = document.getElementById('idLogo');
  logoBtn.addEventListener('click', (e) => {
    e.preventDefault();
    cargarImagen();
  });
});
