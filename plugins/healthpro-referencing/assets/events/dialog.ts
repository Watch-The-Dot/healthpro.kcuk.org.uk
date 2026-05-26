/**
 * The modal works based on Document Events since it is the best way to
 * return information back to a caller asynchronously.
 */

export const OPEN_DIALOG_EVENT_NAME = "healthpro/referencing/openModal";
export const CLOSE_DIALOG_EVENT_NAME = "healthpro/referencing/closeModal";

type Response = {
  shortcode: string;
  inline: boolean;
};

declare global {
  interface DocumentEventMap {
    "healthpro/referencing/openModal": CustomEvent;
    "healthpro/referencing/closeModal": CustomEvent<Response>;
  }
}

/**
 * Opens the "New Reference" Modal.
 *
 * This promises to return a:
 * - `Response` if the user creates a reference
 * - `null` if the user cancels
 */
export async function openDialog(): Promise<Response | null> {
  return new Promise((res, rej) => {
    const event = new Event(OPEN_DIALOG_EVENT_NAME);
    document.dispatchEvent(event);

    document.addEventListener(
      CLOSE_DIALOG_EVENT_NAME,
      (event) => res(event.detail),
      { once: true }
    );
  });
}

/**
 * This function doesn't actually close the modal however it does fire
 * the return event to finish the promise from before.
 *
 * This function should be called even if the shortcode wasn't created
 * since it cleans up the hanging event listener and avoids adding the same
 * reference multiple times.
 */
export function closeDialog(
  shortcode: string | null = null,
  inline: boolean = true
) {
  const event = new CustomEvent<Response | null>(CLOSE_DIALOG_EVENT_NAME, {
    detail: shortcode === null ? null : { shortcode, inline },
  });

  document.dispatchEvent(event);
}
