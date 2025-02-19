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

        function display_value(value_str, td) {
            if (typeof value_str == "string") {
                var dotIndex = value_str.lastIndexOf('.');
                var ext = value_str.substring(dotIndex);


                if (dotIndex != -1 && ext != value_str && dotIndex != value_str.length - 1) {
                    if (ext != value_str && ext == ".png" || ext != value_str && ext == ".jpeg" || ext != value_str && ext == ".jpg") {
                        const img = document.createElement('img');
                        img.src = `http://localhost:8080/uploads/${value_str}`;
                        img.width = 64;
                        td.appendChild(img);
                    } else {
                        const fileLink = document.createElement('a');
                        fileLink.href = `http://localhost:8080/uploads/${value_str}`;
                        fileLink.download = value_str;
                        fileLink.innerHTML = value_str;
                        td.appendChild(fileLink);
                    }
                }
                else {
                    td.textContent = value_str;
                }
            }
            else {
                td.textContent = value_str;
            }
        }


        function createPagination(totalItems, itemsPerPage) {
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const paginationContainer = document.createElement("div");
            paginationContainer.className = "pagination";

            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement("button");
                pageButton.innerText = i;
                pageButton.onclick = function () {
                    console.log("clicked " + i);
                };
                paginationContainer.appendChild(pageButton);
            }

            return paginationContainer;
        }

        // let sort_asc = true;
        // let previous_table = null;
        // let previous_key = null;
        let previous_arrow = document.createElement('inline');
        function sortTable(table, key, arrow, tableName) {
            let t = table;

            let limit = t.getAttribute("limit");
            let sort_asc = t.getAttribute("sort");
            let previous_key = t.getAttribute("column");;

            const tbody = t.querySelector('tbody');
            const rows = Array.from(tbody.rows);
            const keyIndex = Array.from(table.querySelectorAll('th')).findIndex(th => th.textContent.includes(key));

            if (previous_key == key) {
                if (sort_asc == "asc") {
                    t.setAttribute("sort", "desc");
                    sort_asc = "desc";
                    arrow.innerHTML = "🞁";
                } else {
                    t.setAttribute("sort", "asc")
                    sort_asc = "asc";
                    arrow.innerHTML = "🞃";
                }
            } else {
                t.setAttribute("sort", "asc");
                t.setAttribute("column", key);
            }

            if (limit == 'All') {
                limit = "NULL";
            }

            $.ajax({
                url: "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/fetch/" + key + "/" + limit + "/" + sort_asc,
                type: "get",
                processData: false,
                contentType: false,
                success: function (response) {
                    let result = JSON.parse(response);
                    let container = document.getElementById(tableName);
                    let table = createTable(tableName, result, key, sort_asc);
                    document.getElementById(tableName + "_table").remove();
                    table.setAttribute("limit", limit);
                    table.setAttribute("column", key);
                    table.setAttribute("sort", sort_asc);
                    container.appendChild(table);
                }
            });

            // if (previous_table == table && previous_key == key) {
            //     sort_asc = !sort_asc;
            // } else {
            //     sort_asc = true;

            //     previous_arrow.innerHTML = " ";

            //     // previous_table = table;
            //     t.setAttribute("column", key);
            //     previous_arrow = arrow;
            // }

            // arrow.innerHTML = sort_asc ? "🞃" : "🞁";

            // rows.sort((a, b) => {
            //     const aText = a.cells[keyIndex].innerText.trim();
            //     const bText = b.cells[keyIndex].innerText.trim();

            //     const aNum = parseFloat(aText);
            //     const bNum = parseFloat(bText);

            //     if (!isNaN(aNum) && !isNaN(bNum)) {
            //         return sort_asc ? aNum - bNum : bNum - aNum;
            //     } else {
            //         return sort_asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            //     }
            // });

            // tbody.innerHTML = '';
            // rows.forEach(row => tbody.appendChild(row));
        }

        function createTable(tableName, data, old_key, old_sort) {
            const table = document.createElement('table');
            table.id = tableName + "_table";
            // const caption = document.createElement('caption');
            // caption.textContent = tableName;
            // table.appendChild(caption);

            if (data.length > 0) {
                // Create table header
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');

                Object.keys(data[0]).forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;

                    const sort_char = document.createElement('inline');
                    sort_char.style.float = 'right';
                    if (old_key == key) {
                        sort_char.innerHTML = (old_sort == "asc") ? "🞃" : "🞁";
                    }


                    th.onclick = function () { sortTable(table, key, sort_char, tableName) }

                    th.appendChild(sort_char);
                    headerRow.appendChild(th);
                });

                const actionsTh = document.createElement('th');
                const addButton = document.createElement('a');
                addButton.textContent = 'Add';
                addButton.href = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/add";
                actionsTh.appendChild(addButton);
                headerRow.appendChild(actionsTh);

                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Create table body
                const tbody = document.createElement('tbody');
                data.forEach((item, index) => {
                    const row = document.createElement('tr');
                    Object.values(item).forEach(value => {
                        const td = document.createElement('td');


                        if (Array.isArray(value)) {
                            value.forEach(value_str => {
                                display_value(value_str, td);
                            });
                        } else {
                            display_value(value, td);
                        }


                        row.appendChild(td);
                    });

                    const actionsTd = document.createElement('td');
                    const editButton = document.createElement('a');
                    editButton.textContent = 'Edit ';
                    editButton.href = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/edit/" + item.id;



                    const deleteButton = document.createElement('a');
                    deleteButton.textContent = 'Delete ';
                    deleteButton.href = "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/delete/" + item.id;

                    actionsTd.appendChild(editButton);
                    actionsTd.appendChild(deleteButton);
                    row.appendChild(actionsTd);



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

        let schema_template = {

        };

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
                        key: tableName + "_limit",
                        onChange: function () {
                            let limit = $('[name="' + tableName + '_limit"] option:selected').val();
                            let t = document.getElementById(tableName + "_table");
                            t.setAttribute("limit", limit);
                            let sort_column = t.getAttribute("column");
                            let sort_type = t.getAttribute("sort");

                            if (limit == 'All') {
                                limit = "NULL";
                            }

                            console.log("http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/fetch/" + sort_column + "/" + limit + "/" + sort_type);

                            $.ajax({
                                url: "http://" + "<?php echo $_SERVER['HTTP_HOST'] ?>/forms/" + tableName + "/fetch/" + sort_column + "/" + limit + "/" + sort_type,
                                type: "get",
                                processData: false,
                                contentType: false,
                                success: function (response) {
                                    let result = JSON.parse(response);
                                    let container = document.getElementById(tableName);
                                    let table = createTable(tableName, result, sort_column, sort_type);
                                    document.getElementById(tableName + "_table").remove();
                                    table.setAttribute("limit", limit);
                                    table.setAttribute("column", sort_column);
                                    table.setAttribute("sort", sort_type);
                                    container.appendChild(table);
                                }
                            });

                        }
                    },
                    {
                        key: tableName + "_page"
                    },
                    {
                        id: tableName,
                        type: 'section'
                    },
                ],
            };
            form_template.items[0].items.push(json);

            schema_template[tableName + "_limit"] = {
                type: "select",
                title: "Show",
                enum: ["All", 5, 10, 20]
            };

            schema_template[tableName + "_page"] = {
                type: "integer",
                title: "Page",
                default: 1
            };

        }

        $("#form-tables").jsonForm({
            schema: schema_template,
            form: form_template
        });


        for (let [tableName, tableData] of Object.entries(tables)) {
            let container = document.getElementById(tableName);
            let table = createTable(tableName, tableData.data, "id", "asc");
            table.setAttribute("limit", 5);
            table.setAttribute("column", "id");
            table.setAttribute("sort", "asc");
            container.appendChild(table);
        }

    </script>
</body>

</html>