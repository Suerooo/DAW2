const contenedor = document.getElementById("productos")

async function cargarProductos() {
  try {
    const respuesta = await fetch("JS/products.json")

    if (!respuesta.ok) {
      throw new Error("No se pudo cargar el JSON")
    }

    const productos = await respuesta.json()

    contenedor.replaceChildren()

    productos.forEach((producto) => {
      const article = document.createElement("article")
      article.classList.add("article")

      const img = document.createElement("img")
      img.src = producto.imagen
      img.alt = producto.nombre
      img.loading = "lazy"

      const info = document.createElement("div")
      info.classList.add("article-info")

      const nombre = document.createElement("h3")
      nombre.textContent = producto.nombre

      const precio = document.createElement("p")
      precio.textContent = new Intl.NumberFormat("es-ES", {
        style: "currency",
        currency: "EUR",
      }).format(producto.precio)

      const boton = document.createElement("button")
      boton.type = "button"
      boton.textContent = "Añadir al carrito"

      info.append(nombre, precio, boton)
      article.append(img, info)

      contenedor.appendChild(article)
    })
  } catch (error) {
    console.error(error)
    contenedor.textContent = "No se pudieron cargar los productos."
  }
}

cargarProductos()
