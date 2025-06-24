import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Karena __dirname tidak tersedia di ESM, kita buat manual
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Path sumber dan tujuan
const sourceCSS = path.resolve(__dirname, '../../node_modules/bootstrap-icons/font/bootstrap-icons.css');
const sourceFonts = path.resolve(__dirname, '../../node_modules/bootstrap-icons/font/fonts');
const destCSS = path.resolve(__dirname, '../../public/icons/bootstrap-icons.css');
const destFonts = path.resolve(__dirname, '../../public/icons/fonts');

// Buat folder tujuan jika belum ada
if (!fs.existsSync(path.dirname(destCSS))) {
    fs.mkdirSync(path.dirname(destCSS), { recursive: true });
}

// Salin file CSS
fs.copyFileSync(sourceCSS, destCSS);
console.log('✔ CSS berhasil disalin ke public/icons/bootstrap-icons.css');

// Salin folder fonts
if (!fs.existsSync(destFonts)) {
    fs.mkdirSync(destFonts, { recursive: true });
}

fs.readdirSync(sourceFonts).forEach(file => {
    const src = path.join(sourceFonts, file);
    const dest = path.join(destFonts, file);
    fs.copyFileSync(src, dest);
});
console.log('✔ Fonts berhasil disalin ke public/icons/fonts/');
