import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // EAJ Primary Pink
                brand: {
                    50: '#FFF0F6',
                    100: '#FFE0EC',
                    200: '#FFC2D9',
                    300: '#FF94BB',
                    400: '#FD5391',
                    500: '#FC0F5D', // Primary
                    600: '#E20A53', // Hover
                    700: '#BE0846',
                    800: '#9C0A3C',
                    900: '#820A34',
                    950: '#4C0219',
                },
                // EAJ Dark Navy
                navy: {
                    50: '#F0F3FB',
                    100: '#DDE4F4',
                    200: '#C1CCEA',
                    300: '#96A9DA',
                    400: '#647FC6',
                    500: '#425FB2',
                    600: '#314896',
                    700: '#25377A',
                    800: '#132258', // Card / secondary surface
                    900: '#09143D', // Background
                    950: '#050C26',
                },
                success: '#22C55E',
                warning: '#F59E0B',
                danger: '#EF4444',
                info: '#3B82F6',
            },

            borderRadius: {
                card: '18px',
            },

            boxShadow: {
                soft: '0 1px 2px 0 rgb(9 20 61 / 0.04), 0 4px 16px -2px rgb(9 20 61 / 0.06)',
                lift: '0 4px 8px -2px rgb(9 20 61 / 0.08), 0 16px 32px -8px rgb(9 20 61 / 0.14)',
                'glow-pink': '0 0 24px -4px rgb(252 15 93 / 0.45)',
                'glow-pink-sm': '0 4px 14px -2px rgb(252 15 93 / 0.35)',
                // Emerald marks the one School Form module that is live today.
                'glow-emerald': '0 0 24px -4px rgb(52 211 153 / 0.45)',
                'glow-emerald-sm': '0 4px 14px -2px rgb(52 211 153 / 0.35)',
            },

            keyframes: {
                'fade-in': {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                'slide-up': {
                    from: { opacity: '0', transform: 'translateY(16px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'scale-in': {
                    from: { opacity: '0', transform: 'scale(0.96)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-12px)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                },
                'gradient-pan': {
                    '0%, 100%': { 'background-position': '0% 50%' },
                    '50%': { 'background-position': '100% 50%' },
                },
                blob: {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '33%': { transform: 'translate(24px, -32px) scale(1.06)' },
                    '66%': { transform: 'translate(-18px, 22px) scale(0.95)' },
                },
            },

            animation: {
                'fade-in': 'fade-in 0.5s ease-out both',
                'slide-up': 'slide-up 0.5s cubic-bezier(0.16, 1, 0.3, 1) both',
                'slide-up-slow': 'slide-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) both',
                'scale-in': 'scale-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) both',
                float: 'float 6s ease-in-out infinite',
                'float-slow': 'float 9s ease-in-out infinite',
                'gradient-pan': 'gradient-pan 8s ease infinite',
                blob: 'blob 14s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
