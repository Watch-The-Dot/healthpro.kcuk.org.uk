import TinyMCE from "tinymce";
import { openDialog } from "./events/dialog";

// Include Styles for Button Icon
import "./tinymce.css";

// This is included globally from WordPress
declare const tinymce: typeof TinyMCE;

const pluginManager = tinymce.PluginManager;

pluginManager.add("hp-cite", function (editor, url) {
  // Add Button to Visual Editor Toolbar
  editor.addButton("hp-cite", {
    title: "Cite",
    icon: "hp-cite",
    onclick: async function () {
      const response = await openDialog();
      if (!response) {
        return;
      }

      const { shortcode, inline } = response;

      if (inline) {
        editor.insertContent(shortcode);
      } else {
        let bookmark = editor.selection.getBookmark();

        const lastChild = editor.getBody().lastChild;
        if (lastChild) {
          editor.selection.setCursorLocation(lastChild);
          editor.insertContent(shortcode);
        }

        editor.selection.moveToBookmark(bookmark);
      }
    },
  });
});
