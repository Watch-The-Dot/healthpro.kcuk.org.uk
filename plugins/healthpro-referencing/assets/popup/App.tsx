import React, { useEffect, useState } from "react";
import { OPEN_DIALOG_EVENT_NAME } from "../events/dialog";
import Dialog from "./components/Dialog";
import { type DocumentTypes } from "./types/DocumentTypes";

type Props = {
  documentTypes: DocumentTypes;
};
export default function App({ documentTypes }: Props) {
  const [isOpen, setIsOpen] = useState(false);

  const openModal = () => setIsOpen(true);
  const closeModal = () => setIsOpen(false);

  // Listen for the dialog open event.
  // When the component is destroyed, remove the event listener
  useEffect(() => {
    document.addEventListener(OPEN_DIALOG_EVENT_NAME, openModal);

    return () => {
      document.removeEventListener(OPEN_DIALOG_EVENT_NAME, openModal);
    };
  }, []);

  return (
    isOpen && (
      <Dialog
        title="New Reference"
        onRequestClose={closeModal}
        documentTypes={documentTypes}
        __experimentalHideHeader={true}
      />
    )
  );
}
