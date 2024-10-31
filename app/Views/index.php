<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" type="text/css" href="./assets/jsonform/deps/opt/bootstrap.css" />
    <script type="text/javascript" src="./assets/jsonform/deps/jquery.min.js"></script>
    <script type="text/javascript" src="./assets/jsonform/deps/underscore.js"></script>
    <script type="text/javascript" src="./assets/jsonform/deps/opt/jsv.js"></script>
    <script type="text/javascript" src="./assets/jsonform/lib/jsonform.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    
</head>

<body>
    <div class="main">
        <h1>Library</h1>

        <button id="add-button" class="add-button">Add Book</button>

        <table id="table">
            <tr>
                <th>ID</th>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </table>
    </div>

    <div class="popup-container hidden" id="add-modal">
        <div class="popup-box">
            <button class="x-button">x</button>
            <div class="popup-box-contents">
            <h2 id="popup-title">Add Book</h2>
            <form action="" id="popup-form"></form>
            </div>
        </div>
    </div>


    <script src="./js/script.js"></script>
</body>

</html>