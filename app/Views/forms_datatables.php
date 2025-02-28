<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library</title>

    <link rel="stylesheet" href="http://localhost:8080/css/style.css">
    <link rel="stylesheet" type="text/css" href="http://localhost:8080/assets/jsonform/deps/opt/bootstrap.css" />
    <script type="text/javascript" src="http://localhost:8080/assets/jsonform/deps/jquery.min.js"></script>
    <script type="text/javascript" src="http://localhost:8080/assets/jsonform/deps/underscore.js"></script>
    <script type="text/javascript" src="http://localhost:8080/assets/jsonform/deps/opt/jsv.js"></script>
    <script type="text/javascript" src="http://localhost:8080/assets/jsonform/lib/jsonform.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body>
    <div>
        <form action="" id="form-tables"></form>
        <div id="tables-container"></div>
    </div>
    <script>
        let tables = <?php echo $tables; ?>;


        function createDynamicTable(tableName, columns, server_side) {
            let tableId = tableName + "_table";
            // Ensure the table exists in the DOM

            console.log(1);
            if (!$(`#${tableId}`).length) {
                console.error(`Table with ID '${tableId}' not found in DOM.`);
                return;
            }
            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
                $(`#${tableId}`).DataTable().destroy();
            }

            let addLink = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/add";

            if (server_side) {
                // Initialize DataTable
                $(`#${tableId}`).DataTable({
                    columns: [
                        ...columns,
                        {
                            data: null,
                            title: '<a href="' + addLink + '" class="add-link">Add</a>',
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                let editLink = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/edit/" + data.id;
                                let deleteLink = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/delete/" + data.id;

                                return `
                    <a href="${editLink}" class="edit-link">Edit</a> |
                    <a href="${deleteLink}" class="delete-link">Delete</a>
                `;
                            }
                        }
                    ],
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/fetch/datatables",
                        type: "POST"
                    },
                    autoWidth: true,
                });
            } else {

                $.ajax({
                    url: "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/fetch/datatables",
                    method: "POST", // First change type to method here    
                    data: {
                        columns: columns
                    },
                    success: function (response) {
                        console.log(response);

                        $(`#${tableId}`).DataTable({
                            columns: [
                                ...columns,
                                {
                                    data: null,
                                    title: '<a href="' + addLink + '" class="add-link">Add</a>',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        let editLink = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/edit/" + data.id;
                                        let deleteLink = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/delete/" + data.id;

                                        return `
                    <a href="${editLink}" class="edit-link">Edit</a> |
                    <a href="${deleteLink}" class="delete-link">Delete</a>
                `;
                                    }
                                }
                            ],
                            data: response.data,
                            processing: true,
                            serverSide: false,
                            autoWidth: true,
                        });
                    },
                    error: function (error) {
                        console.error(error);
                    }
                });
            }
        }

        let schema_template = {};

        let form_template = {
            type: 'fieldset',
            items: [{
                type: 'tabs',
                id: 'navtabs',
                items: []
            }],
        };

        for (let [tableName] of Object.entries(tables)) {

            let json = {
                type: "tab",
                title: tableName,
                items: [
                    {
                        id: tableName,
                        type: 'section'
                    },
                ],
            };
            form_template.items[0].items.push(json);
        }

        $("#form-tables").jsonForm({
            schema: schema_template,
            form: form_template
        });


        for (let [tableName, data] of Object.entries(tables)) {
            let container = document.getElementById(tableName);
            let table = document.createElement("table");
            table.id = tableName + "_table";
            table.class = "display";

            container.appendChild(table);

            mappedColumns = data.columns.map(name => ({ data: name, title: name }));
            console.log(mappedColumns);

            createDynamicTable(tableName, mappedColumns, data.server_side);
        }

    </script>
</body>

</html>