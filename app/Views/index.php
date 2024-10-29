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
</head>

<body>
    <div class="main">
        <h1>Library</h1>

        <button id="add-button" class="add-button">Add Book</button>

        <table>
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
            <h2>Add Book</h2>
            <form action="" id="popup-form"></form>
            </div>
        </div>
    </div>

    <script src="./js/script.js"></script>
</body>

</html>