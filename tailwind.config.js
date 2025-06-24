/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./*.php",
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