function createButton(params) {
  // console.log(params)
  const nuevoBoton = document.createElement('button');
  params.text !== null && params.text !== undefined
    ? (nuevoBoton.textContent = params.text)
    : null;
  params.id !== null && params.id !== undefined
    ? (nuevoBoton.id = params.id)
    : null;
  params.class !== null && params.class !== undefined
    ? nuevoBoton.setAttribute('class', params.class)
    : null;
  params.name !== null && params.name !== undefined
    ? nuevoBoton.setAttribute('name', params.name)
    : null;
  params.tipo !== null && params.tipo !== undefined
    ? nuevoBoton.setAttribute('tipo', params.tipo)
    : null;
  params.procedure !== null && params.procedure !== undefined
    ? nuevoBoton.setAttribute('procedure', params.procedure)
    : null;
  params.confecha !== null && params.confecha !== undefined
    ? nuevoBoton.setAttribute('confecha', params.confecha)
    : null;
  params.operation !== null && params.operation !== undefined
    ? nuevoBoton.setAttribute('operation', params.operation)
    : null;
  params.ini !== null && params.ini !== undefined
    ? nuevoBoton.setAttribute('ini', params.ini)
    : null;
  params.outi !== null && params.outi !== undefined
    ? nuevoBoton.setAttribute('outi', params.outi)
    : null;
  params.innerHTML !== null && params.innerHTML !== undefined
    ? (nuevoBoton.innerHTML = params.innerHTML)
    : null;
  params.height !== null && params.height !== undefined
    ? (nuevoBoton.style.height = params.height)
    : null;
  params.width !== null && params.width !== undefined
    ? (nuevoBoton.style.width = params.width)
    : null;
  params.borderRadius !== null && params.borderRadius !== undefined
    ? (nuevoBoton.style.borderRadius = params.borderRadius)
    : null;
  params.border !== null && params.border !== undefined
    ? (nuevoBoton.style.border = params.border)
    : null;
  params.textAlign !== null && params.textAlign !== undefined
    ? (nuevoBoton.style.textAlign = params.textAlign)
    : null;
  params.marginLeft !== null && params.marginLeft !== undefined
    ? (nuevoBoton.style.marginLeft = params.marginLeft)
    : null;
  params.marginRight !== null && params.marginRight !== undefined
    ? (nuevoBoton.style.marginRight = params.marginRight)
    : null;
  params.marginTop !== null && params.marginTop !== undefined
    ? (nuevoBoton.style.marginTop = params.marginTop)
    : null;
  params.marginBotton !== null && params.marginBotton !== undefined
    ? (nuevoBoton.style.marginBotton = params.marginBotton)
    : null;
  params.paddingLeft !== null && params.paddingLeft !== undefined
    ? (nuevoBoton.style.paddingLeft = params.paddingLeft)
    : null;
  params.paddingRight !== null && params.paddingRight !== undefined
    ? (nuevoBoton.style.paddingRight = params.paddingRight)
    : null;
  params.paddingTop !== null && params.paddingTop !== undefined
    ? (nuevoBoton.style.paddingTop = params.paddingTop)
    : null;
  params.paddingBotton !== null && params.paddingBotton !== undefined
    ? (nuevoBoton.style.paddingBotton = params.paddingBotton)
    : null;
  params.background !== null && params.background !== undefined
    ? (nuevoBoton.style.backgroundColor = params.background)
    : null;
  params.onClick !== null && params.onClick !== undefined
    ? nuevoBoton.addEventListener('click', params.onClick)
    : null;
  return nuevoBoton;
}

export default createButton;
