import type { Config } from "tailwindcss";

const config: Config = {
  darkMode: ["class"],
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
        sidebar: {
          DEFAULT: "hsl(var(--sidebar))",
          hover: "hsl(var(--sidebar-hover))",
        },
        excel: {
          header: "hsl(var(--excel-header))",
          "header-foreground": "hsl(var(--excel-header-foreground))",
          grid: "hsl(var(--excel-grid))",
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "max(0px, calc(var(--radius) - 2px))",
        sm: "max(0px, calc(var(--radius) - 4px))",
      },
      keyframes: {
        "loading-dot": {
          "0%, 80%, 100%": { opacity: "0.35", transform: "scale(0.85)" },
          "40%": { opacity: "1", transform: "scale(1)" },
        },
        "check-draw": {
          "0%": { opacity: "0", transform: "scale(0.5)" },
          "15%": { opacity: "1", transform: "scale(1.05)" },
          "25%": { opacity: "1", transform: "scale(1)" },
          "85%": { opacity: "1", transform: "scale(1)" },
          "100%": { opacity: "0", transform: "scale(0.5)" },
        },
        "pen-move": {
          "0%": { left: "8px" },
          "100%": { left: "calc(100% - 8px)" },
        },
        "scan-down": {
          "0%": { top: "12px" },
          "100%": { top: "calc(100% - 12px)" },
        },
      },
      animation: {
        "loading-dot": "loading-dot 1.2s ease-in-out infinite",
        "check-draw": "check-draw 2.5s ease-in-out infinite",
        "pen-move": "pen-move 1.5s ease-in-out infinite",
        "scan-down": "scan-down 2s ease-in-out infinite",
      },
    },
  },
  plugins: [require("tailwindcss-animate")],
};

export default config;
