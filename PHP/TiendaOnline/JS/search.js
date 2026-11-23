const search = document.getElementById("product-search")
const results = document.getElementById("search-results")
const searchStatus = document.getElementById("search-status")

let timer
let pending = Promise.resolve()

async function showSuggestions(term) {
  try {
    const response = await fetch("index.php?search=" + encodeURIComponent(term))
    if (!response.ok) {
      throw new Error("No se pudo hacer la búsqueda")
    }
    const products = await response.json()

    if (search.value.trim() !== term) {
      return
    }
    if (products.length === 0) {
      searchStatus.textContent = "No se encontraron productos."
    }

    products.forEach((product) => {
      const item = document.createElement("li")
      const link = document.createElement("a")
      link.href = "index.php?details-product=" + product.id
      link.textContent = product.name
      item.appendChild(link)
      results.appendChild(item)
    })
  } catch (error) {
    if (search.value.trim() === term) {
      searchStatus.textContent = "No se pudo hacer la búsqueda."
    }
  }
}

if (search) {
  search.addEventListener("input", () => {
    clearTimeout(timer)
    results.textContent = ""
    searchStatus.textContent = ""
    const term = search.value.trim()

    if (term.length >= 2) {
      timer = setTimeout(() => {
        pending = pending.then(() => {
          if (search.value.trim() === term) {
            return showSuggestions(term)
          }
        })
      }, 250)
    }
  })
}
