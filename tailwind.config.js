/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./*.php",
      "./pages/**/*.{html, js, php}",
      "./admin/**/*.{html, js, php}",
      "./src/**/*.{html,js,php}",
      "./dist/**/*.{html,js,php}"
    ],
    theme: {
      extend: {},
    },
    plugins: [
      require('daisyui'),
    ],
    daisyui: {
      themes: ["light", "dark"],
    },
  }