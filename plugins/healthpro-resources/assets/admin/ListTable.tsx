import React, { Key, ReactElement, ReactNode, useState } from "react";
import { __, sprintf } from "@wordpress/i18n";
import { useImmer } from "use-immer";

type BulkActionsProps = {
  actions: { [key: string]: string };
  doAction: (action: string) => void;
};
function BulkActions({ actions, doAction }: BulkActionsProps) {
  const [action, setAction] = useState("");

  return (
    <div className="alignleft actions bulkactions">
      <label htmlFor="bulk-action-selector-top" className="screen-reader-text">
        Select bulk action
      </label>
      <select
        name="action"
        id="bulk-action-selector-top"
        onChange={(event) => setAction(event.target.value)}
        value={action}
      >
        <option value="">Bulk actions</option>
        {Object.keys(actions).map((key) => (
          <option key={key} value={key}>
            {actions[key]}
          </option>
        ))}
      </select>
      <input
        type="submit"
        id="doaction"
        className="button action"
        value="Apply"
        onClick={() => {
          doAction(action);
          setAction("");
        }}
      />
    </div>
  );
}

type ListTableProps<
  TRow extends Record<string, any>,
  TAction extends string
> = {
  doBulkAction?: (action: TAction, ids: any[]) => void;
  actions?: Record<TAction, string>;
  extraButtons?: ReactNode;
  rows: TRow[];
  primaryKey: keyof TRow;
  displayRow: React.ComponentType<{
    item: TRow;
    checkbox: ReactElement;
  }>;
};
export default function ListTable<
  TRow extends Record<string, any>,
  TAction extends string
>(props: ListTableProps<TRow, TAction>) {
  const { actions, doBulkAction, extraButtons, rows, primaryKey } = props;

  const [selectedRows, setSelectedRows] = useImmer<any[]>([]);

  const handleAction = (action: TAction) => {
    if (!doBulkAction) return;

    doBulkAction(action, selectedRows);
    setSelectedRows([]);
  };

  return (
    <>
      <div className="tablenav top">
        {actions && <BulkActions actions={actions} doAction={handleAction} />}
        {extraButtons}
      </div>
      <table className="wp-list-table widefat fixed striped table-view-list">
        <thead>
          <tr>
            <td id="cb" className="manage-column column-cb check-column">
              <input
                type="checkbox"
                onChange={(event) => {
                  if (event.target.checked) {
                    setSelectedRows(rows.map((row) => row[primaryKey]));
                  } else {
                    setSelectedRows([]);
                  }
                }}
              />
              <label htmlFor="cb-select-all-1">
                <span className="screen-reader-text">
                  {__("Select all", "wordpress")}
                </span>
              </label>
            </td>
            <th>Featured Image</th>
            <th>Title</th>
            <th>Quantity</th>
            <th>View</th>
          </tr>
        </thead>

        <tbody id="the-list">
          {rows.map((item) => (
            <props.displayRow
              key={item[primaryKey]}
              item={item}
              checkbox={
                <input
                  type="checkbox"
                  checked={selectedRows.includes(item[primaryKey])}
                  onChange={(event) => {
                    if (event.target.checked) {
                      setSelectedRows([...selectedRows, item[primaryKey]]);
                    } else {
                      setSelectedRows(
                        selectedRows.filter((a) => a !== item[primaryKey])
                      );
                    }
                  }}
                />
              }
            />
          ))}
        </tbody>
      </table>
    </>
  );
}
