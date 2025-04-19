async function readJSON(json, retries = 4, delay = 500) {
  const ruta = `/load-app-json.php?file=${json}&v=${Date.now()}`;

  let lastError;

  for (let i = 0; i < retries; i++) {
    try {
      // eslint-disable-next-line no-await-in-loop
      const response = await fetch(ruta);

      if (!response.ok) {
        throw new Error(`Error al cargar app.json: ${response.statusText}`);
      }
      // eslint-disable-next-line no-await-in-loop
      return await response.json();
    } catch (error) {
      // console.warn(`Intento ${i + 1} fallido:`, error);
      lastError = error;
      if (i < retries - 1) {
        // eslint-disable-next-line no-await-in-loop
        await new Promise((res) => {
          setTimeout(res, delay);
        });
      }
    }
  }

  throw lastError;
}

export default readJSON;
