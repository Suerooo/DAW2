const container = document.getElementById("products")
const cartSummary = document.getElementById("cart-summary")
const emptyCart = document.getElementById("empty-cart")

let cartQuantity = 0
let cartTotal = 0 

function updateCart() {
  const cartIsEmpty = cartQuantity === 0
  emptyCart.disabled = cartIsEmpty

  if (cartIsEmpty) {
    cartSummary.textContent = "El carrito está vacío."
  } else {
    const total = cartTotal.toFixed(2).replace(".", ",")
    cartSummary.textContent = "Unidades: " + cartQuantity + " · Total: " + total + " €"
  }
}

emptyCart.addEventListener("click", () => {
  cartQuantity = 0
  cartTotal = 0
  updateCart()
})

function loadProducts() {
  try {
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
      button.addEventListener("click", () => {
        cartQuantity += 1
        cartTotal += product.price
        updateCart()
      })

      info.append(name, price, button)
      article.append(img, info)

      container.appendChild(article)
    })
  } catch (error) {
    console.error(error)
    container.textContent = "No se pudieron cargar los productos."
  }
}

updateCart()
loadProducts()
