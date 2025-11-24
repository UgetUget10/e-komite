// tailwind.config.js
import preset from "./vendor/filament/filament/tailwind.config.preset.js";

export default {
    presets: [preset], // WAJIB – ini yang bikin Filament tetap punya styling

    content: [
        // File Laravel biasa
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",

        // Semua file Filament (resources, pages, widgets, dll)
        "./app/Filament/**/*.php",
        "./vendor/filament/**/*.blade.php",

        // Custom theme Anda (ganti kalau nama foldernya beda)
        "./resources/css/filament/adminlte/theme.css", // ← INI YANG PALING PENTING
        // kalau nanti pakai Nord atau theme lain, tambahkan juga di sini
    ],

    theme: {
        extend: {
            // contoh kalau mau tambah warna khusus AdminLTE
            colors: {
                adminlte: {
                    blue: "#3c8dbc",
                    green: "#00a65a",
                    red: "#dd4b39",
                },
            },
        },
    },

    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};
