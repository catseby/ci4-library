<!DOCTYPE html>
<html>

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
    <div class="container">
        <h2>Create Database Table</h2>
        <div id="form"></div>
    </div>

    <script>
        $('#form').jsonForm({
            "schema": {
                "table_name": {
                    "type": "string",
                    "title": "Table Name"
                },
                "columns": {
                    "type": "array",
                    "items": {
                        "type": "object",
                        "title": "Column",
                        "properties": {
                            "column_name": {
                                "type": "string",
                                "title": "Column Name"
                            },
                            "data_type": {
                                "type": "string",
                                "title": "Data Type"
                            }
                        }
                    }
                }
            },
            "form": [
                { "key": "table_name" },
                {
                    "type": "array",
                    "title": "Columns",
                    "items": {
                        "type": "section",
                        "items": [
                            "columns[].column_name",

                            "columns[].data_type"

                        ]
                    }
                },
                {
                    "type": "submit",
                    "title": "Create Table"
                }
            ]
        });
    </script>
</body>

</html>