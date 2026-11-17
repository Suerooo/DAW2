const numero1 = document.getElementById("numero1")
const numero2 = document.getElementById("numero2")
const resultado = document.getElementById("resultado")

const botonSumar = document.getElementById("sumar")
const botonRestar = document.getElementById("restar")
const botonMultiplicar = document.getElementById("multiplicar")
const botonDividir = document.getElementById("dividir")
const botonLimpiar = document.getElementById("limpiar")

function sumar(a, b) {
  return a + b
}

function restar(a, b) {
  return a - b
}

function multiplicar(a, b) {
  return a * b
}

function dividir(a, b) {
  return a / b
}

function obtenerNumeros() {
  const valor1 = numero1.value
  const valor2 = numero2.value

  if (valor1 === "" || valor2 === "") {
    resultado.textContent = "Introduce los dos números."
    return null
  }

  const a = Number(valor1)
  const b = Number(valor2)

  if (!Number.isFinite(a) || !Number.isFinite(b)) {
    resultado.textContent = "Los valores introducidos no son válidos."
    return null
  }

  return [a, b]
}

function mostrarResultado(operacion) {
  if (!Number.isFinite(operacion)) {
    resultado.textContent = "El resultado es demasiado grande."
    return
  }

  resultado.textContent = operacion
}

botonSumar.addEventListener("click", () => {
  const numeros = obtenerNumeros()

  if (numeros === null) {
    return
  }

  mostrarResultado(sumar(numeros[0], numeros[1]))
})

botonRestar.addEventListener("click", () => {
  const numeros = obtenerNumeros()

  if (numeros === null) {
    return
  }

  mostrarResultado(restar(numeros[0], numeros[1]))
})

botonMultiplicar.addEventListener("click", () => {
  const numeros = obtenerNumeros()

  if (numeros === null) {
    return
  }

  mostrarResultado(multiplicar(numeros[0], numeros[1]))
})

botonDividir.addEventListener("click", () => {
  const numeros = obtenerNumeros()

  if (numeros === null) {
    return
  }

  if (numeros[1] === 0) {
    resultado.textContent = "No se puede dividir entre cero."
    return
  }

  mostrarResultado(dividir(numeros[0], numeros[1]))
})

botonLimpiar.addEventListener("click", () => {
  numero1.value = ""
  numero2.value = ""
  resultado.textContent = "Esperando operación..."
})
