import React, {
  useState,
  useRef,
  useEffect,
  ComponentProps,
  ReactElement,
} from "react";
import { useImmer } from "use-immer";
import { createRoot } from "react-dom/client";
import { __, sprintf } from "@wordpress/i18n";
import AddNewDialog from "./AddNewDialog";
import ListTable from "./ListTable";
import apiFetch from "@wordpress/api-fetch";

import "./styles.css";

type RowStatus = "pending" | "syncing" | "saved";

export type Item = {
  id: string;
  post_id?: string;
  image: string;
  title: string;
  backorders: boolean;
  quantity: number;
  url: string;
};

export type RowItem = {
  status?: RowStatus;
} & Item;

function TableRow({
  item,
  checkbox,
}: {
  item: RowItem;
  checkbox: ReactElement;
}) {
  return (
    <tr style={{ opacity: item.status === "saved" ? "1" : "0.8" }}>
      <td>{checkbox}</td>
      <td>
        <img
          src={item.image}
          alt={sprintf("%s Featured Image", item.title)}
          style={{ maxWidth: "100px" }}
        />
      </td>
      <td>
        {item.title}
        {item.status === "syncing" && (
          <>
            <br />
            <span
              className="spinner"
              style={{
                opacity: 1,
                visibility: "visible",
                float: "none",
                margin: 0,
              }}
            ></span>
          </>
        )}
      </td>
      <td>
        {item.quantity}
        {item.backorders && (
          <>
            <br />
            <strong>(Backorders Allowed)</strong>
          </>
        )}
      </td>
      <td>
        <a href={item.url} target={"_blank"}>
          View on KCUK
          <span className="dashicons dashicons-external"></span>
        </a>
      </td>
    </tr>
  );
}

function ImportBtn(props: ComponentProps<"button">) {
  return (
    <button
      className="button"
      {...props}
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: "4px",
        ...props.style,
      }}
    >
      <span className="dashicons dashicons-plus-alt2"></span> Import New
    </button>
  );
}

function SyncBtn(props: ComponentProps<"button">) {
  return (
    <button
      className="button"
      {...props}
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: "4px",
        ...props.style,
      }}
    >
      <span className="dashicons dashicons-update-alt"></span> Sync All
    </button>
  );
}

function App({ items: initialItems = [] }: { items: Item[] }) {
  const [items, setItems] = useImmer<Record<any, RowItem>>(
    initialItems
      .map((item) => ({ ...item, status: "saved" }))
      .reduce((obj, curr) => {
        obj[curr.id] = curr;
        return obj;
      }, {})
  );

  const [isSyncing, setIsSyncing] = useState(false);
  const [addNewOpen, setAddNewOpen] = useState(false);

  const addNewItem = (item: Item) => {
    const p = { ...item, status: "syncing" } as RowItem;
    setItems((items) => {
      items[p.id] = p;
    });

    apiFetch<{ success: boolean; post_id: string }>({
      method: "PUT",
      path: "/healthpro/resources/v1/resource",
      data: {
        post: p,
      },
    }).then((response) => {
      setItems((items) => {
        items[p.id].status = "saved";
        items[p.id].post_id = response.post_id;
      });
    });

    setAddNewOpen(false);
  };

  const syncAllItems = async () => {
    setIsSyncing(true);

    setItems((items) => {
      for (const k in items) {
        items[k].status = "pending";
      }
    });

    for (const k in items) {
      const item = items[k];
      setItems((items) => {
        items[k].status = "syncing";
      });

      try {
        const response = await apiFetch<{ data: Item }>({
          method: "GET",
          path: `/healthpro/resources/v1/resource/${item.post_id}/sync`,
        });

        setItems((items) => {
          items[k] = { ...items[k], ...response.data };
          items[k].status = "saved";
        });
      } catch (e) {
        console.error(e);
        setItems((items) => {
          items[k].status = "saved";
        });
      }
    }

    setIsSyncing(false);
  };

  const deleteItem = async (id) => {
    setItems((items) => {
      if (items[id]) items[id].status = "pending";
    });

    try {
      if (items[id].post_id) {
        await apiFetch({
          method: "DELETE",
          path: `/healthpro/resources/v1/resource/${items[id].post_id}`,
        });
      }

      setItems((items) => {
        delete items[id];
      });
    } catch (e) {
      setItems((items) => {
        items[id].status = "saved";
      });
    }
  };

  return (
    <>
      <ListTable
        rows={Object.values(items)}
        primaryKey={"id"}
        displayRow={TableRow}
        actions={{
          delete: __("Delete", "healthpro-resources"),
        }}
        doBulkAction={(action, ids) => {
          if (action === "delete") {
            ids.forEach(deleteItem);
          }
        }}
        extraButtons={
          <>
            <ImportBtn
              style={{ marginRight: "4px" }}
              onClick={() => setAddNewOpen(true)}
            />
            {Object.values(items).length > 0 && (
              <SyncBtn onClick={syncAllItems} disabled={isSyncing} />
            )}
          </>
        }
      />
      {addNewOpen && (
        <AddNewDialog
          addItem={addNewItem}
          closeModal={() => setAddNewOpen(false)}
        />
      )}
    </>
  );
}

document.addEventListener("DOMContentLoaded", function () {
  const app = document.querySelector("#app") as HTMLDivElement;
  const root = createRoot(app);

  root.render(
    <React.StrictMode>
      <App items={JSON.parse(app.dataset.resources || "")} />
    </React.StrictMode>
  );
});
