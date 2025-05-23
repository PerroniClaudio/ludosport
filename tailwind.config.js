import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    future: {
        hoverOnlyWhenSupported: true, // Abilita il supporto per dispositivi con hover
    },

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                text: {
                    50: "var(--text-50)",
                    100: "var(--text-100)",
                    200: "var(--text-200)",
                    300: "var(--text-300)",
                    400: "var(--text-400)",
                    500: "var(--text-500)",
                    600: "var(--text-600)",
                    700: "var(--text-700)",
                    800: "var(--text-800)",
                    900: "var(--text-900)",
                    950: "var(--text-950)",
                },
                background: {
                    50: "var(--background-50)",
                    100: "var(--background-100)",
                    200: "var(--background-200)",
                    300: "var(--background-300)",
                    400: "var(--background-400)",
                    500: "var(--background-500)",
                    600: "var(--background-600)",
                    700: "var(--background-700)",
                    800: "var(--background-800)",
                    900: "var(--background-900)",
                    950: "var(--background-950)",
                },
                primary: {
                    50: "var(--primary-50)",
                    100: "var(--primary-100)",
                    200: "var(--primary-200)",
                    300: "var(--primary-300)",
                    400: "var(--primary-400)",
                    500: "var(--primary-500)",
                    600: "var(--primary-600)",
                    700: "var(--primary-700)",
                    800: "var(--primary-800)",
                    900: "var(--primary-900)",
                    950: "var(--primary-950)",
                },
                secondary: {
                    50: "var(--secondary-50)",
                    100: "var(--secondary-100)",
                    200: "var(--secondary-200)",
                    300: "var(--secondary-300)",
                    400: "var(--secondary-400)",
                    500: "var(--secondary-500)",
                    600: "var(--secondary-600)",
                    700: "var(--secondary-700)",
                    800: "var(--secondary-800)",
                    900: "var(--secondary-900)",
                    950: "var(--secondary-950)",
                },
                accent: {
                    50: "var(--accent-50)",
                    100: "var(--accent-100)",
                    200: "var(--accent-200)",
                    300: "var(--accent-300)",
                    400: "var(--accent-400)",
                    500: "var(--accent-500)",
                    600: "var(--accent-600)",
                    700: "var(--accent-700)",
                    800: "var(--accent-800)",
                    900: "var(--accent-900)",
                    950: "var(--accent-950)",
                },
                success: {
                    100: "var(--success-100)",
                    200: "var(--success-200)",
                    300: "var(--success-300)",
                    400: "var(--success-400)",
                    500: "var(--success-500)",
                    600: "var(--success-600)",
                    700: "var(--success-700)",
                },
                warning: {
                    100: "var(--warning-100)",
                    200: "var(--warning-200)",
                    300: "var(--warning-300)",
                    400: "var(--warning-400)",
                    500: "var(--warning-500)",
                    600: "var(--warning-600)",
                    700: "var(--warning-700)",
                },
                error: {
                    100: "var(--error-100)",
                    200: "var(--error-200)",
                    300: "var(--error-300)",
                    400: "var(--error-400)",
                    500: "var(--error-500)",
                    600: "var(--error-600)",
                    700: "var(--error-700)",
                },
                info: {
                    100: "var(--info-100)",
                    200: "var(--info-200)",
                    300: "var(--info-300)",
                    400: "var(--info-400)",
                    500: "var(--info-500)",
                    600: "var(--info-600)",
                    700: "var(--info-700)",
                },
            },
        },
    },

    plugins: [forms],
};
