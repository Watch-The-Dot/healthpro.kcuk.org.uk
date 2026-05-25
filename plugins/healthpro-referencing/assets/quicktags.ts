import { openDialog } from "./events/dialog";

// This is included globally from WordPress
// No types exist for this so let's quickly make them
declare const QTags: {
  addButton: (
    id: string,
    display: string,
    callback: string | (() => any)
  ) => any;
  insertContent: (content: string) => boolean;
};

declare global {
  interface Window {
    wpActiveEditor: string;
  }
}

// Add button to Text Editor Toolbar
QTags.addButton("hp-cite", "cite", async function () {
  const response = await openDialog();
  if (!response) {
    return;
  }

  const { shortcode, inline } = response;

  if (inline) {
    QTags.insertContent(shortcode);
  } else {
    const editor = document.getElementById(
      window.wpActiveEditor
    ) as HTMLTextAreaElement;
    if (!editor) {
      return;
    }

    editor.value += shortcode + "\n";
  }
});
