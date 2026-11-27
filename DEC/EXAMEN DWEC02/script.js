const calcular = document.getElementById("calcular")
const limpiar = document.getElementById("limpiar")

const resultado = document.getElementById("resultado")
const entradas = document.getElementById("entradas")
const error = document.getElementById("error")

const nombre = document.getElementById("nombre")
const edad = document.getElementById("edad")
const tipo_entrada = document.getElementById("tipo")
const cantidad = document.getElementById("cantidad")
const estudiante = document.getElementById("estudiante")

const precio_general = 15
const precio_premium = 25
const precio_vip = 40

calcular.addEventListener("click", () => {
  resultado.replaceChildren()
  entradas.replaceChildren()

  error.textContent = ""

  if (campos_validos(nombre.value, edad.value, tipo_entrada.value, cantidad.value, error)) {
    const precio_unitario = obtener_precio_unitario(tipo_entrada.value)
    const subtotal = calcular_subtotal(precio_unitario, cantidad.value)
    const descuento_cantidad = obtener_descuento_cantidad(cantidad.value)
    const descuento_personal = obtener_descuento_personal(edad.value, estudiante.value)
    const descuento_total = obtener_descuento_total(descuento_personal, descuento_cantidad)
    const importe_descontado = calcular_importe_descontado(descuento_total, subtotal)
    const precio_final = calcular_precio_final(subtotal, importe_descontado)
    mostrar_resultado(precio_unitario, subtotal, descuento_cantidad, descuento_personal, descuento_total, importe_descontado, precio_final)

    mostrar_entradas(cantidad.value, tipo_entrada.value)
  }
})

limpiar.addEventListener("click", () => {
  resultado.replaceChildren()
  entradas.replaceChildren()

  error.textContent = ""

  nombre.value = ""
  edad.value = ""
  tipo_entrada.value = ""
  cantidad.value = ""
  estudiante.value = ""
})

function campos_validos(nombre, edad, tipo_entrada, cantidad, error) {
  if (nombre == "") {
    error.textContent = "El nombre no puede estar vacío"
    return false
  }

  if (edad < 18 || edad > 120) {
    error.textContent = "La edad tiene que estar entre 18 y 120"
    return false
  }

  if (tipo_entrada != "general" && tipo_entrada != "premium" && tipo_entrada != "vip") {
    error.textContent = "Selecciona un tipo de entrada valido"
    return false
  }

  if (cantidad < 1 || cantidad > 10) {
    error.textContent = "La cantidad tiene que estar entre 1 y 10"
    return false
  }

  return true
}

function mostrar_resultado(precio_unitario, subtotal, descuento_cantidad, descuento_personal, descuento_total, importe_descontado, precio_final) {
  const parrafo_nombre_edad = document.createElement("p")
  agregar_parrafo(parrafo_nombre_edad, resultado, "Nombre y edad: " + nombre.value + ", " + edad.value + " años")

  const parrafo_modalidad_cantidad = document.createElement("p")
  agregar_parrafo(parrafo_modalidad_cantidad, resultado, "Modalidad y cantidad: " + tipo_entrada.value + ", " + cantidad.value + " entradas")

  const parrafo_precio_unitario = document.createElement("p")
  agregar_parrafo(parrafo_precio_unitario, resultado, "Precio unitario: " + precio_unitario + " €")

  const parrafo_subtotal = document.createElement("p")
  agregar_parrafo(parrafo_subtotal, resultado, "Subtotal: " + subtotal + " €")

  const parrafo_descuento_cantidad = document.createElement("p")
  agregar_parrafo(parrafo_descuento_cantidad, resultado, "Descuento cantidad: " + descuento_cantidad + " %")

  const parrafo_descuento_personal = document.createElement("p")
  agregar_parrafo(parrafo_descuento_personal, resultado, "Descuento personal: " + descuento_personal + " %")

  const parrafo_descuento_total = document.createElement("p")
  agregar_parrafo(parrafo_descuento_total, resultado, "Descuento total: " + descuento_total + " %")

  const parrafo_importe_descontado = document.createElement("p")
  agregar_parrafo(parrafo_importe_descontado, resultado, "Importe descontado: " + importe_descontado + " €")

  const parrafo_precio_final = document.createElement("p")
  parrafo_precio_final.style.fontWeight = "bold"
  agregar_parrafo(parrafo_precio_final, resultado, "Precio final: " + precio_final + " €")
}

function mostrar_entradas(cantidad, tipo_entrada) {
  for (let index = 1; index <= cantidad; index++) {
    const parrafo_entrada = document.createElement("p")
    agregar_parrafo(parrafo_entrada, entradas, "Entrada " + index + " - " + tipo_entrada)
  }
}

function agregar_parrafo(parrafo, container, texto) {
  parrafo.textContent = texto
  container.appendChild(parrafo)
}

function calcular_subtotal(precio_unitario, cantidad) {
  return precio_unitario * cantidad
}

function calcular_importe_descontado(descuento_total, subtotal) {
  return subtotal * (descuento_total / 100)
}

function calcular_precio_final(subtotal, importe_descontado) {
  return subtotal - importe_descontado
}

function obtener_precio_unitario(tipo_entrada) {
  if (tipo_entrada === "general") {
    return precio_general
  }

  if (tipo_entrada === "premium") {
    return precio_premium
  }

  if (tipo_entrada === "vip") {
    return precio_vip
  }
}

function obtener_descuento_personal(edad, estudiante) {
  if (edad >= 65) {
    return 10
  }

  if (estudiante === "true") {
    return 5
  }

  return 0
}

function obtener_descuento_cantidad(cantidad) {
  if (cantidad >= 1 && cantidad <= 2) {
    return 0
  }

  if (cantidad >= 3 && cantidad <= 4) {
    return 10
  }

  if (cantidad >= 5 && cantidad <= 10) {
    return 15
  }
}

function obtener_descuento_total(descuento_personal, descuento_cantidad) {
  return descuento_cantidad + descuento_personal
}
