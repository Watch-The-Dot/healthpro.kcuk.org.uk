import React, { useState } from "react";
import {
  Button,
  Modal,
  TabPanel,
  TextareaControl,
  TextControl,
} from "@wordpress/components";
import { RichText } from "@wordpress/block-editor";
import Steps from "rc-steps";

import "rc-steps/assets/index.css";

function MetaStep() {
  return (
    <>
      <TextControl onChange={() => false} value={""} label="Title" />
      <TextControl onChange={() => false} value={""} label="Publisher" />
      <TextControl onChange={() => false} value={""} label="Published" />

      <TextareaControl onChange={() => false} value={""} label="Excerpt" />
      <TextareaControl onChange={() => false} value={""} label="Author(s)" />
    </>
  );
}

function ContentStep() {
  return (
    <>
      <TextControl onChange={() => false} value={""} label="Selector" />
      <hr />
      <TextareaControl onChange={() => false} value={""} label="Content" />
    </>
  );
}

function PreviewStep() {
  return "";
}

export default function App() {
  const [isModalOpen, setModalOpen] = useState(true);
  const openModal = () => setModalOpen(true);
  const closeModal = () => setModalOpen(false);

  const steps = [
    { title: "Meta", content: <MetaStep /> },
    { title: "Content", content: <ContentStep /> },
    { title: "Preview", content: <PreviewStep /> },
  ];

  const [currentStep, setCurrentStep] = useState(0);
  const nextStep = () =>
    setCurrentStep(Math.min(currentStep + 1, steps.length - 1));
  const prevStep = () => setCurrentStep(Math.max(currentStep - 1, 0));

  return (
    isModalOpen && (
      <Modal
        title={"Import"}
        onRequestClose={closeModal}
        shouldCloseOnClickOutside={false}
        shouldCloseOnEsc={true}
        size="medium"
        __experimentalHideHeader={true}
      >
        <Steps current={currentStep} items={steps} />

        <div style={{ marginBottom: "16px" }}>{steps[currentStep].content}</div>

        <footer style={{ display: "flex", justifyContent: "space-between" }}>
          <Button
            variant="secondary"
            onClick={currentStep === 0 ? closeModal : prevStep}
          >
            {currentStep === 0 ? "Close" : "Previous"}
          </Button>

          <Button variant="primary" onClick={nextStep}>
            Next
          </Button>
        </footer>
      </Modal>
    )
  );
}
