import React, { useState } from "react";
import { createRoot } from "@wordpress/element";
import { Button, Modal, TextControl } from "@wordpress/components";

import App from "./App";

document.addEventListener("DOMContentLoaded", function () {
  const el = document.querySelector<HTMLDivElement>("div#import-new-post")!;
  const root = createRoot(el);

  root.render(<App />);

  const importLinks =
    document.querySelectorAll<HTMLAnchorElement>("a[data-import-id]");
  importLinks.forEach((el) =>
    el.addEventListener("click", (event) => {
      event.preventDefault();
      console.log("Load ");
    })
  );
});
