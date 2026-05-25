import { v4wp } from "@kucrut/vite-for-wp";
import { wp_scripts } from "@kucrut/vite-for-wp/plugins";
import react from "@vitejs/plugin-react";

/** @type {import('vite').UserConfig} */
export default {
  plugins: [
    v4wp({
      input: {
        tinymce: "assets/tinymce.ts",
        quicktags: "assets/quicktags.ts",
        popup: "assets/popup/index.tsx",
      },
      outDir: "dist",
    }),
    wp_scripts(),
    react({
      jsxRuntime: "classic",
    }),
  ],
};
