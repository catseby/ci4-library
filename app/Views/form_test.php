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
        <h1 style="text-transform:capitalize"><?= esc($name) ?></h1>
        <form action="" id="test-form"></form>
    </div>
    <script>
        let schema = <?php echo $schema; ?>;
        let form = <?php echo $form; ?>;
        let value = <?php echo $values; ?>;

        function display_images(file, reset = true) {

            if (reset) {
                document.getElementById("image-display").innerHTML = " ";
            }
            let container = document.createElement("div");
            container.style.position = 'relative';
            container.style.display = 'inline-block';
            container.style.margin = '10px';
            
            let image = document.createElement("img");
            image.width = 320;
            image.style.display = 'block';

            let closeButton = document.createElement("button");
            closeButton.textContent = 'X';

            closeButton.style.position = 'absolute';
            closeButton.style.top = '5px';
            closeButton.style.right = '5px';
            closeButton.style.backgroundColor = 'red';
            closeButton.style.color = 'white';
            closeButton.style.border = 'none';
            closeButton.style.borderRadius = '50%';
            closeButton.style.opacity = '0.7';
            // closeButton.style.width = '20px';
            // closeButton.style.height = '20px';
            closeButton.style.textAlign = 'center';
            closeButton.style.cursor = 'pointer';
            closeButton.style.fontSize = '14px';
            closeButton.style.lineHeight = '20px';

            container.appendChild(image);
            container.appendChild(closeButton);

            const reader = new FileReader();
            reader.onload = function (e) {
                image.src = e.target.result;
                document.getElementById("image-display").appendChild(container);
            };
            reader.readAsDataURL(file);
        }

        // Pirms formas noformatēšana=================
        //====================================

        let multi_keys = [];
        for (let i = 0; i < form.length; i++) {
            let f = form[i];

            if (f.image) {
                f.onChange = function () {
                    console.log("change");
                    let files = document.getElementsByName(f.key)[0].files;
                    for (let j = 0; j < files.length; j++) {
                        let file = files[j];
                        if (j == 0) {
                            display_images(file);
                        }
                        else {
                            display_images(file, false);
                        }
                    }
                }
                if (f.multiple) {
                    multi_keys.push(f.key);
                }
            }
        }

        //Formas ģenerēšana ==============================
        //===========================================

        $("#test-form").jsonForm({
            schema: schema,
            form: form,
            value: value[0],
            "onSubmitValid": function (values) {
                let formData = new FormData();

                for (let i = 0; i < Object.keys(schema.properties).length; i++) {
                    let key = Object.keys(schema.properties)[i];

                    if (schema.properties[key].type == "file") {
                        let files = document.getElementsByName(key)[0].files;
                        for (let j = 0; j < files.length; j++) {
                            formData.append("files[]", files[j]);

                            formData.append(key, "?filename");
                        }
                    }
                    else if (schema.properties[key].type == "select") {
                        let select_value = $('[name="' + key + '"]').val().map(Number);
                        formData.append(key, JSON.stringify(select_value));
                    }
                    else if (schema.properties[key].type == "array") {
                        console.log(values[key]);
                        formData.append(key, JSON.stringify(values[key]));
                    }
                    else {
                        let value = values[key];
                        formData.append(key, value);
                    }
                }


                $.ajax({
                    url: "http://localhost:8080/forms/" + "<?= esc($link) ?>",
                    type: "post",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) { console.log(values) },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error(jqXHR);
                        console.error(textStatus);
                        console.error(errorThrown);
                    },
                });
            }
        });

        // ģenerētās formas papildināšana ================
        // =====================================

        for (let i = 0; i < multi_keys.length; i++) {
            $('[name="' + multi_keys[i] + '"]').attr("multiple", "multiple");
        }

        for (let i = 0; i < form.length; i++) {
            let f = form[i];

            if (f.image) {
                async function createFileFromUrl(url, fileName) {
                    const resp = await fetch(url);
                    const blob = await resp.blob();
                    const file = new File([blob], fileName, {
                        type: blob.type,
                    });
                    return file;
                }

                console.log(value);

                for (let j = 0; j < value.length; j++) {
                    let filename = value[j][f.key];
                    createFileFromUrl("http://localhost:8080/uploads/" + filename, filename)
                        .then((new_file) => {
                            display_images(new_file, false);
                        })
                        .catch((error) => console.error("Error creating file:", error));
                }
            }

            if (f.select) {
                $.ajax({
                    url: "http://localhost:8080/forms/" + f.table + "/fetch",
                    type: "get",
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        let result = JSON.parse(response);

                        let dropdown = $('[name="' + f.key + '"]');
                        dropdown.attr("multiple", "multiple").select2();
                        dropdown.empty();

                        for (let i = 0; i < result.length; i++) {
                            let option = $("<option>", {
                                value: parseInt(result[i].id),
                                text: result[i].name,
                            });

                            if (Object.keys(value).length > 0) {
                                for (let j = 0; j < value[0][f.key].length; j++) {

                                    if (value[0][f.key][j] == parseInt(result[i].id)) {
                                        option.attr("selected", "selected");
                                    }
                                }
                            }
                            dropdown.append(option);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) { console.error(jqXHR); },
                });
            }
        }
    </script>
</body>

</html>