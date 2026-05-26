import React, { useState } from "react";
import { createRoot } from "@wordpress/element";
import { Button, Modal, TextControl } from "@wordpress/components";

import "./styles.css";

function App({ nonce, page }: { nonce: string; page: string }) {
  const [modalOpen, setModalOpen] = useState(false);
  const openModal = () => setModalOpen(true);
  const closeModal = () => setModalOpen(false);

  const [values, setValues] = useState<{ title?: string; url?: string }>({});

  return (
    <>
      <button type="button" className="page-title-action" onClick={openModal}>
        Add New Feed
      </button>
      {modalOpen && (
        <Modal title="Add New Feed" onRequestClose={closeModal}>
          <form method="post">
            <TextControl
              value={values.title || ""}
              onChange={(value) =>
                setValues((values) => ({ ...values, title: value }))
              }
              type="text"
              name="feed_title"
              required={true}
              label="Title"
            />

            <TextControl
              value={values.url || ""}
              onChange={(value) =>
                setValues((values) => ({ ...values, url: value }))
              }
              type="url"
              name="feed_url"
              required={true}
              label="URL"
            />

            <p>
              <Button variant="primary" type="submit">
                Add New
              </Button>

              <input type="hidden" name="action" value="add-new-feed" />
              <input type="hidden" name="page" value={page} />
              <input type="hidden" name="subpage" value="feeds" />
              <input type="hidden" name="_wpnonce" value={nonce} />
            </p>
          </form>
        </Modal>
      )}
    </>
  );
}

document.addEventListener("DOMContentLoaded", function () {
  const el = document.querySelector<HTMLSpanElement>(
    "span#add-new-feed-container"
  );
  if (!el) return;

  const nonce = el.dataset.nonce;
  const page = el.dataset.page;
  if (!nonce || !page) return;

  const root = createRoot(el);
  root.render(
    <React.StrictMode>
      <App nonce={nonce} page={page} />
    </React.StrictMode>
  );
});
