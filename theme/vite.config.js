import { defineConfig } from "vite";
import { resolve } from "node:path";
import { writeFileSync, rmSync, readFileSync } from "node:fs";

const SRC = resolve(__dirname, "assets/src");
const OUT = resolve(__dirname, "assets/dist");
const HOT = resolve(OUT, "hot");

const DEV_HOST = "localhost";
const DEV_PORT = 5173;

/**
 * Writes assets/dist/hot while the dev server runs so the theme's PHP knows to
 * load modules from the dev server (HMR / live reload) instead of the manifest.
 */
function wpHotFile() {
  return {
    name: "compass-wp-hot-file",
    apply: "serve",
    configureServer(server) {
      const url = `http://${DEV_HOST}:${DEV_PORT}`;
      const write = () => writeFileSync(HOT, url);
      write();
      const clean = () => {
        try {
          rmSync(HOT);
        } catch {
          /* already gone */
        }
      };
      server.httpServer?.once("close", clean);
      ["SIGINT", "SIGTERM", "exit"].forEach((sig) => process.on(sig, clean));
    },
  };
}

/**
 * Writes assets/dist/critical.css: design tokens + the hand-authored critical
 * layer, comments and whitespace stripped. PHP inlines it in <head>. It lives
 * in dist (not assets/src) because the deploy artifact excludes assets/src.
 */
function criticalCss() {
  return {
    name: "compass-critical-css",
    apply: "build",
    closeBundle() {
      const css = [resolve(SRC, "css/tokens.css"), resolve(__dirname, "assets/critical.css")]
        .map((f) => readFileSync(f, "utf8"))
        .join("\n")
        .replace(/\/\*[\s\S]*?\*\//g, "")
        .replace(/\s+/g, " ")
        .replace(/\s*([{};,>])\s*/g, "$1")
        .replace(/;}/g, "}")
        .trim();
      writeFileSync(resolve(OUT, "critical.css"), css);
    },
  };
}

export default defineConfig(({ command }) => ({
  root: SRC,
  // Relative base so built CSS url() references resolve against the dist folder,
  // wherever WordPress serves the theme from.
  base: command === "serve" ? "/" : "./",
  publicDir: false,
  plugins: [wpHotFile(), criticalCss()],
  server: {
    host: DEV_HOST,
    port: DEV_PORT,
    strictPort: true,
    cors: true,
    origin: `http://${DEV_HOST}:${DEV_PORT}`,
  },
  build: {
    outDir: OUT,
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: resolve(SRC, "js/main.js"),
    },
    target: "es2020",
    cssMinify: true,
  },
}));
