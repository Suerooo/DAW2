const container = document.getElementById("products")

async function loadProducts() {
  try {
    const answer = await fetch("JS/products.json")

    if (!answer.ok) {
      throw new Error("No se pudo cargar el JSON")
    }

    const products = await answer.json()

    products.forEach((product) => {
      const article = document.createElement("article")
      article.classList.add("article")

      const img = document.createElement("img")
      img.src = product.imagen
      img.alt = product.nombre

      const info = document.createElement("div")
      info.classList.add("article-info")

      const name = document.createElement("h3")
      name.textContent = product.name

      const price = document.createElement("p")
      price.textContent = product.price + "€"

      const button = document.createElement("button")
      button.type = "button"
      button.textContent = "Añadir al carrito"

      info.append(name, price, button)
      article.append(img, info)

      container.appendChild(article)
    })
  } catch (error) {
    console.error(error)
    container.textContent = "No se pudieron cargar los productos."
  }
}

loadProducts()
