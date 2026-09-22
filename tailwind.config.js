import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                display: ['Cormorant Garamond', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                rotary: {
                    navy: '#062b52',
                    blue: '#17458f',
                    sky: '#55ace0',
                    gold: '#c8942f',
                    ivory: '#fffaf0',
                },
            },
        },
    },

    plugins: [forms],
};
