// Flat ESLint config (ESLint 9). Lints the theme's front-end JS source.
const js = require("@eslint/js");

module.exports = [
  {
    ignores: [
      "**/node_modules/**",
      "theme/assets/dist/**",
      "vendor/**",
    ],
  },
  {
    files: ["theme/assets/src/js/**/*.js"],
    ...js.configs.recommended,
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: {
        window: "readonly",
        document: "readonly",
        navigator: "readonly",
        localStorage: "readonly",
        console: "readonly",
      },
    },
    rules: {
      "no-unused-vars": "warn",
      "no-console": ["warn", { allow: ["warn", "error"] }],
    },
  },
];
