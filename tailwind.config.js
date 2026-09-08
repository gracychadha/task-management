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
        },
    },

    safelist: [
        'bg-purple-100',
        'bg-purple-600',
        'text-purple-600',
        'text-purple-800',
        'bg-blue-100',
        'bg-blue-500',
        'bg-blue-600',
        'text-blue-600',
        'text-blue-700',
        'text-blue-800',
        'bg-gray-100',
        'bg-gray-400',
        'bg-gray-500',
        'bg-gray-600',
        'text-gray-600',
        'text-gray-700',
        'text-gray-800',
        'bg-green-100',
        'bg-green-400',
        'bg-green-500',
        'bg-green-600',
        'text-green-600',
        'text-green-700',
        'text-green-800',
        'bg-red-100',
        'bg-red-400',
        'bg-red-500',
        'bg-red-600',
        'text-red-600',
        'text-red-700',
        'text-red-800',
        'bg-yellow-100',
        'bg-yellow-400',
        'bg-yellow-500',
        'text-yellow-700',
        'text-yellow-800',
        'bg-orange-100',
        'bg-orange-500',
        'text-orange-600',
        'text-orange-700',
        'text-orange-800',
    ],

    plugins: [forms],
};
