/** @type {import('tailwindcss').Config} */
module.exports = {
  // Daftar file yang akan di-scan oleh Tailwind
  content: [
    './*.php',
    './pages/**/*.{html,js,php}',
    './admin/**/*.{html,js,php}',
    './src/**/*.{html,js,php}',
    './dist/**/*.{html,js,php}'
  ],

  // Konfigurasi tema (bisa dikustomisasi)
  theme: {
    extend: {}, // Ekstensi tema default (opsional)
  },

  // Plugin Tailwind yang digunakan
  plugins: [
    require('daisyui'), // Plugin DaisyUI
  ],

  // Konfigurasi khusus DaisyUI
  daisyui: {
    themes: ['light', 'dark'], // Daftar tema yang digunakan
  },
};