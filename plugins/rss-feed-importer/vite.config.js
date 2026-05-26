import { v4wp } from "@kucrut/vite-for-wp";
import { wp_scripts } from "@kucrut/vite-for-wp/plugins";
import react from "@vitejs/plugin-react";

export default {
  plugins: [
    v4wp({
      input: {
        adminFeeds: "assets/admin/feeds/index.tsx",
        adminPosts: "assets/admin/posts/index.tsx",
      },
      outDir: "dist",
    }),
    wp_scripts(),
    react({
      jsxRuntime: "classic",
    }),
  ],
};
