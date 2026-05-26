import apiFetch from "@wordpress/api-fetch";
import { sprintf } from "@wordpress/i18n";
import { addQueryArgs } from "@wordpress/url";
import React, { useEffect, useRef, useState } from "react";
import { useImmer } from "use-immer";
import type { Item } from ".";
import {
  Button,
  Card,
  CardBody,
  CardMedia,
  Modal,
  SelectControl,
  Spinner,
  TextControl,
  __experimentalHeading as Heading,
} from "@wordpress/components";
import useFetch from "../hooks/useFetch";

type CategoryResponse = {
  id: string;
  name: string;
  count: number;
}[];

type SearchResponse = {
  id: string;
  title: string;
  url: string;
  backorders: boolean;
  quantity: number;
  image: string;
  already_imported: boolean;
}[];

type Props = {
  addItem: (item: Item) => void;
  closeModal: () => void;
};
export default function AddNewDialog({ addItem, closeModal }: Props) {
  const [filters, setFilters] = useImmer({
    search: "",
    category: "0",
  });

  const categoryList = useFetch<CategoryResponse>([]);
  const searchResults = useFetch<SearchResponse>([]);

  const loadCategories = () => {
    categoryList.fetch(() =>
      apiFetch<CategoryResponse>({
        method: "GET",
        path: "/healthpro/resources/v1/categories",
      })
    );
  };
  useEffect(loadCategories, []);

  const search = () => {
    searchResults.fetch(() =>
      apiFetch<SearchResponse>({
        method: "GET",
        path: addQueryArgs("/healthpro/resources/v1/search", filters),
      })
    );
  };

  return (
    <Modal title="Add New Resource" onRequestClose={closeModal}>
      {categoryList.loading ? (
        <Spinner />
      ) : (
        <>
          <TextControl
            label="Search"
            value={filters.search}
            onChange={(value) =>
              setFilters((filter) => {
                filter.search = value;
              })
            }
          />
          <SelectControl
            label="Category"
            value={filters.category}
            onChange={(value) =>
              setFilters((filter) => {
                filter.category = value;
              })
            }
            options={[
              {
                disabled: true,
                label: "Select an Option",
                value: "",
              },
              ...categoryList.data.map((category) => ({
                label: `${category.name} (${category.count})`,
                value: category.id,
              })),
            ]}
          />
          <Button onClick={search} variant="primary">
            Search
          </Button>
        </>
      )}
      <hr />
      <div>
        {searchResults.loading ? (
          <Spinner />
        ) : (
          searchResults.data.map((product) => (
            <Card key={product.id}>
              <div style={{ display: "flex", gap: "4px" }}>
                <CardMedia>
                  <img
                    src={product.image}
                    style={{ width: "100px" }}
                    alt={sprintf("%s Featured Image", product.title)}
                  />
                </CardMedia>
                <CardBody>
                  <Heading level={4}>{product.title}</Heading>
                  <Button
                    variant={product.already_imported ? "secondary" : "primary"}
                    disabled={product.already_imported}
                    onClick={() => addItem(product)}
                  >
                    {product.already_imported ? "Imported" : "Add"}
                  </Button>
                </CardBody>
              </div>
            </Card>
          ))
        )}
      </div>
    </Modal>
  );
}
