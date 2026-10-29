import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  darkMode: ["class"],
  theme: {
    extend: {
      colors: {
        background: "#F8FAFC",
        surface: "#FFFFFF",
        "surface-alt": "#F1F5F9",
        "surface-elevated": "#FFFFFF",
        "main-text": "#0F172A",
        "secondary-text": "#475569",
        "muted-text": "#64748B",
        border: "#E2E8F0",
        brand: {
          blue: "#2563EB",
          "blue-hover": "#1D4ED8",
          cyan: "#06B6D4",
          purple: "#7C3AED",
          green: "#16A34A",
          whatsapp: "#25D366",
          "whatsapp-hover": "#128C7E",
          surface: "#EFF6FF",
          gold: "#D97706",
        },
      },
      fontFamily: {
        sans: ["var(--font-inter)", "system-ui", "-apple-system", "BlinkMacSystemFont", "Segoe UI", "sans-serif"],
        mono: ["var(--font-mono)", "monospace"],
      },
      boxShadow: {
        "soft-sm": "0 1px 2px 0 rgba(15, 23, 42, 0.05)",
        "soft-md": "0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.05)",
        "soft-lg": "0 10px 15px -3px rgba(15, 23, 42, 0.06), 0 4px 6px -4px rgba(15, 23, 42, 0.04)",
        "soft-xl": "0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04)",
        "blue-glow": "0 8px 24px -4px rgba(37, 99, 235, 0.25)",
        "whatsapp-glow": "0 8px 24px -4px rgba(37, 211, 102, 0.28)",
        "glow-cyan": "0 4px 20px -2px rgba(6, 182, 212, 0.15)",
        "glow-blue": "0 4px 20px -2px rgba(37, 99, 235, 0.2)",
        "glow-purple": "0 4px 20px -2px rgba(124, 58, 237, 0.15)",
        "glow-green": "0 4px 20px -2px rgba(22, 163, 74, 0.15)",
      },
      backgroundImage: {
        "grid-pattern": "linear-gradient(to right, rgba(15, 23, 42, 0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(15, 23, 42, 0.05) 1px, transparent 1px)",
      },
    },
  },
  plugins: [],
};

export default config;
