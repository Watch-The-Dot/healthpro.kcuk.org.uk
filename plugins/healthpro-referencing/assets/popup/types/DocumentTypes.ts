export type ReferenceField = {
  type: string;
  label: string;
  name: string;
  description?: string;
  required: boolean;
};

export type DocumentType = {
  name: string;
  description?: string;
  fields: ReferenceField[];
};

export type DocumentTypes = Record<string, DocumentType>;
