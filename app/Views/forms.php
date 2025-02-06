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

        function createTable(tableName, data) {
            const table = document.createElement('table');
            const caption = document.createElement('caption');
            caption.textContent = tableName;
            table.appendChild(caption);

            if (data.length > 0) {
                // Create table header
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');

                Object.keys(data[0]).forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;
                    headerRow.appendChild(th);
                });
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Create table body
                const tbody = document.createElement('tbody');
                data.forEach(item => {
                    const row = document.createElement('tr');
                    Object.values(item).forEach(value => {
                        const td = document.createElement('td');
                        td.textContent = value;
                        row.appendChild(td);
                    });
                    tbody.appendChild(row);
                });
                table.appendChild(tbody);
            } else {
                const emptyRow = document.createElement('tr');
                const emptyCell = document.createElement('td');
                emptyCell.colSpan = "100%";
                emptyCell.textContent = "No data available";
                emptyRow.appendChild(emptyCell);
                table.appendChild(emptyRow);
            }

            return table;
        }

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
            schema: {},
            form: form_template
        });


        for (let [tableName, tableData] of Object.entries(tables)) {
            let container = document.getElementById(tableName);
            let table = createTable(tableName, tableData.data);
            container.appendChild(table);
        }

    </script>
</body>

</html>