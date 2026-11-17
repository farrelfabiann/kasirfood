import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#fff5f2',
                    100: '#ffe6df',
                    200: '#ffcabd',
                    300: '#ffa38c',
                    400: '#ff6f4e',
                    500: '#f14722',
                    600: '#d92d0b',
                    700: '#b32108',
                    800: '#931f0d',
                    900: '#7a1e10',
                },
                sun: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    300: '#fcd34d',
                    400: '#fbbf24',
                    500: '#f59e0b',
                },
            },
            boxShadow: {
                kiosk: '0 24px 60px -20px rgba(120, 53, 15, 0.35)',
                card: '0 10px 30px -18px rgba(28, 25, 23, 0.45)',
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'slide-up': {
                    '0%': { opacity: '0', transform: 'translateY(18px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'pop-in': {
                    '0%': { opacity: '0', transform: 'scale(0.94)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                'soft-pulse': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.55' },
                },
            },
            animation: {
                'fade-in': 'fade-in 180ms ease-out both',
                'slide-up': 'slide-up 240ms ease-out both',
                'pop-in': 'pop-in 220ms ease-out both',
                'soft-pulse': 'soft-pulse 2s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
