import React, { ComponentProps } from "react";
import { TextControl } from "@wordpress/components";
import { useRef } from "react";
import { useState } from "react";
import { __experimentalText as Text } from "@wordpress/components";

type Props = {} & ComponentProps<typeof TextControl>;
export default function Input({ ...props }: Props) {
  const validityMessageMap: Partial<Record<keyof ValidityState, string>> = {
    valueMissing: "Please enter a value",
    typeMismatch: {
      url: "Please enter a URL, including https://",
    }[props.type || ""],
  };

  const inputRef = useRef<HTMLInputElement>(null);

  const [isValid, setValid] = useState(true);
  const [validityMessage, setValidityMessage] = useState<string | null>(null);

  const onChange = (value: string) => {
    setValid(true);
    props.onChange(value);
  };

  const checkValidity = () => {
    const inputField = inputRef.current;
    if (!inputField) return;

    setValid(inputField.checkValidity());

    setValidityMessage(null);
    const validity = inputField.validity;
    for (const k of Object.keys(validityMessageMap)) {
      if (validity[k]) {
        setValidityMessage(validityMessageMap[k]);
        break;
      }
    }
  };

  return (
    <TextControl
      {...props}
      ref={inputRef}
      onChange={onChange}
      onBlur={checkValidity}
      help={
        isValid || props.value === "" ? (
          props.help
        ) : (
          <Text color="red">{validityMessage}</Text>
        )
      }
    />
  );
}
