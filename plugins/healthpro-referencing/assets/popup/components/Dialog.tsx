import {
  Button,
  CheckboxControl,
  Flex,
  FlexItem,
  Modal,
  SelectControl,
} from "@wordpress/components";
import React, { ComponentProps, useMemo, useRef, useState } from "react";
import { closeDialog } from "../../events/dialog";
import {
  type DocumentTypes,
  type ReferenceField,
} from "../types/DocumentTypes";
import Input from "./Input";

type Props = {
  documentTypes: DocumentTypes;
} & Omit<ComponentProps<typeof Modal>, "children">;

export default function Dialog({ documentTypes, ...props }: Props) {
  const [documentType, setDocumentType] = useState("default");
  const [isInlineCitation, setIsInlineCitation] = useState(true);
  const [values, setValues] = useState({});

  const generatedShortcode = useRef<string | null>(null);

  const closeModal = () => {
    closeDialog(generatedShortcode.current, isInlineCitation);
    props.onRequestClose();
  };

  /**
   * Sort the fields so that they are in alphabetical order
   * with the required fields at the top of the list
   */
  const sortFields = (a: ReferenceField, b: ReferenceField) => {
    // If both fields have the same required status
    if (a.required === b.required) {
      return a.name.localeCompare(b.name);
    }

    // Else, the fields don't have the same required status therefore put the
    // required field higher
    return a.required ? -1 : 1;
  };

  // Cache the fields for the document type selected until the document type changes
  const fields = useMemo(
    () => documentTypes[documentType]?.fields.sort(sortFields),
    [documentType]
  );

  const isFieldValid = (field: ReferenceField) => {
    const value = values[field.name];
    if (field.required && !value) {
      return false;
    }

    if (!field.required && value === "") {
      return true;
    }

    if (field.type === "url") {
      try {
        new URL(value);
      } catch (err) {
        return false;
      }
    }

    return true;
  };

  const isFormValid = useMemo(() => {
    return documentTypes[documentType].fields.every(isFieldValid);
  }, [values]);

  const submit = () => {
    if (!isFormValid) {
      return;
    }

    const properties = Object.keys(values)
      .map((v) => v.trim())
      .filter(Boolean)
      .map((name) => `${name}="${values[name]}"`)
      .join(" ");

    const shortcode = `[${isInlineCitation ? "cite" : "bib"} ${
      documentType === "default" ? "" : documentType
    } ${properties}]`;

    generatedShortcode.current = shortcode;
    closeModal();
  };

  return (
    <Modal {...props} onRequestClose={closeModal}>
      <section>
        <main>
          <header>
            <SelectControl
              label="Document Type"
              value={documentType}
              onChange={setDocumentType}
            >
              {Object.keys(documentTypes).map((key) => (
                <option key={key} value={key}>
                  {documentTypes[key].name}
                </option>
              ))}
            </SelectControl>
            <CheckboxControl
              label="Inline Citation"
              checked={isInlineCitation}
              onChange={setIsInlineCitation}
            />
          </header>
          <hr />
          {fields?.map(({ name, label, type, description, required }) => (
            <Input
              key={name}
              value={values[name] || ""}
              onChange={(value) =>
                setValues((values) => ({
                  ...values,
                  [name]: value,
                }))
              }
              type={type as "text" | "url"}
              label={label + (required ? " *" : "")}
              help={description}
              required={required}
            />
          ))}
        </main>
        <Flex as="footer">
          <FlexItem>
            <Button variant="secondary" onClick={props.onRequestClose}>
              Close
            </Button>
          </FlexItem>
          <FlexItem>
            <Button variant="primary" onClick={submit} disabled={!isFormValid}>
              Insert
            </Button>
          </FlexItem>
        </Flex>
      </section>
    </Modal>
  );
}
