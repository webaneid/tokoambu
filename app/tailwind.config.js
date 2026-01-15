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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#F17B0D',
                'primary-hover': '#DD5700',
                'primary-light': '#FFF5F0',
                blue: '#0D36AA',
                'blue-light': '#075AC2',
                'blue-light-bg': '#F0F4FF',
                pink: '#D00086',
                'pink-light': '#D836A5',
                'gray-50': '#F9FAFB',
                'gray-200': '#E5E7EB',
                'gray-500': '#6B7280',
                'gray-900': '#1F2937',
            },
        },
    },

    plugins: [forms],
};
