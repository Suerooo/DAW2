function showHideData() {
  const button_show_data = document.getElementById("button-show-data")
  const button_hidden_data = document.getElementById("button-hidden-data")
  const data_display = document.getElementById("data-display")

  button_show_data.addEventListener("click", () => {
    const language_info = navigator.language
    const title_info = document.title
    const url_info = window.location.href
    const path_info = window.location.pathname
    const inner_width_info = window.innerWidth
    const inner_height_info = window.innerHeight

    const data = [
      ["Idioma:", language_info],
      ["Título:", title_info],
      ["URL:", url_info],
      ["Ruta:", path_info],
      ["Tamaño interior:", `${inner_width_info}x${inner_height_info} px`],
    ]

    data.forEach(([label, value]) => {
      const div_data = document.createElement("div")
      div_data.classList.add("data-div")
      data_display.appendChild(div_data)

      const heading = document.createElement("h2")
      heading.textContent = label
      const paragraph = document.createElement("p")
      paragraph.textContent = value
      div_data.appendChild(heading)
      div_data.appendChild(paragraph)
    })

    button_show_data.style.display = "none"
    button_hidden_data.style.display = ""
  })

  button_hidden_data.addEventListener("click", () => {
    data_display.replaceChildren()
    button_show_data.style.display = ""
    button_hidden_data.style.display = "none"
  })
}

function changeTitle() {
  const input_change_title = document.getElementById("input-change-title")
  const button_change_title = document.getElementById("button-change-title")

  button_change_title.addEventListener("click", () => {
    document.title = input_change_title.value
  })
}

showHideData()
changeTitle()
