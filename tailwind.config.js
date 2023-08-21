/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{html,js}"],
  theme: {
    themes: ["light", "dark"],
    extend: {},
  },
  plugins: [require("daisyui")],
}

