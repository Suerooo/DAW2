const display = document.getElementById("result")
const buttons = document.querySelectorAll('input[type="button"]')

const action_buttons = ["+", "-", "x", "/", ".", "^"]
const number_buttons = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"]

buttons.forEach((button) => {
  button.addEventListener("click", () => {
    const button_pressed = button.value

    if (button_pressed === "C") {
      display.value = ""
    } else if (button_pressed === "<") {
      display.value = display.value.slice(0, -1)
    } else if (button_pressed === "=") {
    } else {
      display.value += button_pressed
    }

    if (number_buttons.includes(button_pressed)) {
      buttons
    }
  })
})
