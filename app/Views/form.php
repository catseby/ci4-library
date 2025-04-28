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
    <div class="form-container-div">
        <h1 style="text-transform:capitalize"><?= esc($name) ?></h1>
        <form action="" id="test-form"></form><br>
        <p id="message" style="color: green;"><?= esc($message) ?></p>
    </div>
    <script>
        let schema = <?php echo $schema; ?>;
        let form = <?php echo $form; ?>;
        let value = <?php echo $values; ?>;
        let link = "<?php echo $link; ?>";
    </script>
    <script src="http://localhost:8080/js/form.js"></script>
</body>

</html>