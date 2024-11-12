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
        <h1><?= esc($name) ?></h1>
    </div>
    <div>
        <form action="" id="test-form"></form>
    </div>
    <script>
        let schema = JSON.parse("<?= esc($schema) ?>".replace(/&quot;/g, '"'));
        let form = JSON.parse("<?= esc($form) ?>".replace(/&quot;/g, '"'));
        let value = JSON.parse("<?= esc($values) ?>".replace(/&quot;/g, '"'));

        console.log(value[0]);

        $("#test-form").jsonForm({
            schema: schema,
            form: form,
            value: value[0],
            "onSubmitValid": function (values) {
                $.ajax({
                    url: "http://localhost:8080/forms/" + "<?= esc($link) ?>",
                    type: "post",
                    data: JSON.stringify(values),
                    processData: false,
                    contentType: false,
                    success: function (response) { console.log("succsess") },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error(jqXHR);
                    },
                });
            }
        })
    </script>
</body>

</html>