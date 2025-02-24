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

        function createDynamicTable(tableId, jsonData) {
            // Ensure the table exists in the DOM
            if (!$(`#${tableId}`).length) {
                console.error(`Table with ID '${tableId}' not found in DOM.`);
                return;
            }

            if (jsonData.length == 0) {
                return;
            }

            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
                $(`#${tableId}`).DataTable().destroy();
            }

            // Extract column names from JSON keys
            const columns = Object.keys(jsonData[0]).map(key => ({ title: key, data: key }));

            // Initialize DataTable
            $(`#${tableId}`).DataTable({
                data: jsonData,
                columns: columns,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableId + "/fetch/datatables",
                    type: "GET"
                },
                responsive: true,
                autoWidth: false,
                searching: true,
                paging: true,
                ordering: true,
                info: true,
            });
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


        for (let [tableName, tableData] of Object.entries(tables)) {
            let container = document.getElementById(tableName);
            let table = document.createElement("table");
            table.id = tableName + "_table";
            table.class = "display";

            container.appendChild(table);


            createDynamicTable(tableName + "_table", tableData.data);
        }

    </script>
</body>

</html>