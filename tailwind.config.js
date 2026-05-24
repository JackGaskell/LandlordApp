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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: {
                    50: '#eef4ff',
                    100: '#dce8fc',
                    200: '#c1d4f7',
                    300: '#96b8f0',
                    400: '#6493e6',
                    500: '#3f6fd9',
                    600: '#2f54c7',
                    700: '#2744a1',
                    800: '#253b82',
                    900: '#0f1a2e',
                    950: '#0a1222',
                    DEFAULT: '#0a1222',
                },
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
                accent: {
                    teal: '#2dd4bf',
                    green: '#84cc16',
                    cyan: '#22d3ee',
                },
                surface: {
                    DEFAULT: '#ffffff',
                    muted: '#f8fafc',
                    elevated: '#ffffff',
                    border: 'rgba(15, 26, 46, 0.08)',
                },
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(135deg, #3b82f6 0%, #2dd4bf 52%, #84cc16 100%)',
                'brand-gradient-soft': 'linear-gradient(135deg, rgba(59, 130, 246, 0.12) 0%, rgba(45, 212, 191, 0.1) 50%, rgba(132, 204, 22, 0.08) 100%)',
                'brand-gradient-glow': 'radial-gradient(ellipse 80% 60% at 50% -20%, rgba(59, 130, 246, 0.25), transparent)',
                'navy-gradient': 'linear-gradient(180deg, #0f1a2e 0%, #0a1222 100%)',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(10, 18, 34, 0.04), 0 4px 16px rgba(10, 18, 34, 0.06)',
                card: '0 0 0 1px rgba(10, 18, 34, 0.04), 0 2px 8px rgba(10, 18, 34, 0.04), 0 12px 32px rgba(10, 18, 34, 0.06)',
                'card-dark': '0 0 0 1px rgba(255, 255, 255, 0.06), 0 8px 32px rgba(0, 0, 0, 0.35)',
                glow: '0 0 40px rgba(59, 130, 246, 0.15)',
            },
            borderRadius: {
                xl: '0.875rem',
                '2xl': '1rem',
                '3xl': '1.25rem',
            },
            transitionTimingFunction: {
                smooth: 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },

    safelist: [
        'bg-orange-500/10',
        'text-orange-400',
    ],

    plugins: [forms],
};
