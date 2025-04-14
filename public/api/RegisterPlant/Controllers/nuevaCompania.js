import baseUrl from '../../../config.js';

const SERVER = baseUrl;

export default function addCompania(objeto, ruta) {
  // eslint-disable-next-line no-console
  console.time('addCompania');
  return new Promise((resolve, reject) => {
    const rax = `&new=${new Date()}`;
    const obj = {
      ruta,
      rax,
      objeto,
    };
    const datos = JSON.stringify(obj);
    // console.log(datos)

    const url = `${SERVER}/Routes/index.php`;
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        // 'Access-Control-Allow-Origin': '*',
      },
      body: datos,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Network response was not ok ${response.statusText}`);
        }
        return response.json();
      })
      .then((data) => {
        // console.log(data)
        resolve(data);
        // eslint-disable-next-line no-console
        console.timeEnd('addCompania');
        return data;
      })
      .catch((error) => {
        console.timeEnd('addCompania');
        console.error('Error en la solicitud:', error);
        reject(error);
        alert('No se pudo establecer conexión con el servidor');
      });
  });
}
