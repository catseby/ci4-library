function createDynamicTable(tableName, columns, server_side, add, edit) {
  let tableId = tableName + "_table";

  if (!$(`#${tableId}`).length) {
    console.error(`Table with ID '${tableId}' not found in DOM.`);
    return;
  }

  if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
    $(`#${tableId}`).DataTable().destroy();
  }

  let raw_columns = [];
  for (let i = 0; i < columns.length; i++) {
    let c = columns[i];
    if (c.data != "data_table_tools") {
      raw_columns.push(c);
    }
  }

  if (server_side) {
    $(`#${tableId}`).DataTable({
      columns: columns,
      processing: true,
      serverSide: true,
      ajax: {
        url: "http://" + http_host + "/tables/" + tableName + "/fetch",
        type: "POST",
      },
      autoWidth: true,
    });
  } else {
    $.ajax({
      url: "http://" + http_host + "/tables/" + tableName + "/fetch",
      method: "POST",
      data: {
        columns: raw_columns,
      },
      success: function (response) {
        $(`#${tableId}`).DataTable({
          columns: columns,
          data: response.data,
          processing: true,
          serverSide: false,
          autoWidth: true,
        });
      },
      error: function (error) {
        console.error(error);
      },
    });
  }
}

let schema_template = {};
let form_template = {
  type: "fieldset",
  items: [
    {
      type: "tabs",
      id: "navtabs",
      items: [],
    },
  ],
};

for (let [tableName] of Object.entries(tables)) {
  let json = {
    type: "tab",
    title: tableName,
    items: [
      {
        id: tableName,
        type: "section",
      },
    ],
  };
  form_template.items[0].items.push(json);
}

$("#form-tables").jsonForm({
  schema: schema_template,
  form: form_template,
});

for (let [tableName, data] of Object.entries(tables)) {
  let container = document.getElementById(tableName);
  let table = document.createElement("table");
  table.id = tableName + "_table";
  table.class = "display";

  container.appendChild(table);

  createDynamicTable(
    tableName,
    data.columns,
    data.server_side,
    data.add,
    data.edit
  );
}
