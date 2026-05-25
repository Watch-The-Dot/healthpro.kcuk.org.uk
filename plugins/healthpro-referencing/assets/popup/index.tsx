import React, { createRoot } from "@wordpress/element";
import App from "./App";
import { type DocumentTypes } from "./types/DocumentTypes";

import "./styles.css";

function main() {
  const element = document.querySelector<HTMLDivElement>(
    "#hp-cite-helper-popup"
  );
  if (!element) return;

  const documentTypes = JSON.parse(
    element.dataset.types || ""
  ) as DocumentTypes;

  createRoot(element).render(
    <React.StrictMode>
      <App documentTypes={documentTypes} />
    </React.StrictMode>
  );
}

document.addEventListener("DOMContentLoaded", main);
