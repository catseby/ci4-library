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


        console.log(value);
        function display_images(file) {
            document.getElementById("image-display").innerHTML = " ";

            let image = document.createElement("img");
            const reader = new FileReader();
            image.width = 320;
            reader.onload = function (e) {
                image.src = e.target.result;
                document.getElementById("image-display").appendChild(image);
            };
            reader.readAsDataURL(file);
        }

        // // console.log(schema);
        // console.log(form);
        // console.log(value[0]);

        let multi_keys = [];
        for (let i = 0; i < form.length; i++) {
            let f = form[i];

            if (f.image) {
                // let file = document.getElementsByName(f.key)[0].files[0];
                // display_images(file);

                f.onChange = function () {
                    console.log("change");
                    let file = document.getElementsByName(f.key)[0].files[0];
                    display_images(file);
                }
                if (f.multiple) {
                    multi_keys.push(f.key);
                }
            }
        }


        $("#test-form").jsonForm({
            schema: schema,
            form: form,
            value: value[0],
            "onSubmitValid": function (values) {
                let formData = new FormData();

                for (let i = 0; i < Object.keys(schema.properties).length; i++) {
                    let key = Object.keys(schema.properties)[i];

                    if (schema.properties[key].image) {
                        let files = document.getElementsByName(key)[0].files[0];
                        // console.log(files);
                        formData.append("files[]", files);

                        formData.append(key, "?filename");
                    } else {
                        let value = values[key];
                        formData.append(key, value);
                    }
                }

                // console.log(formData.getAll());

                $.ajax({
                    url: "http://localhost:8080/forms/" + "<?= esc($link) ?>",
                    type: "post",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) { console.log(values) },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error(jqXHR);
                        console.log(values);
                    },
                });
            }
        });

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

                let filename = value[0][f.key];
                createFileFromUrl("http://localhost:8080/uploads/" + filename, filename)
                    .then((new_file) => {
                            // var reader = new FileReader();
                            // reader.onload = function() {
                            //     document.getElementsByName(f.key)[0].value = reader.result;
                            //     display_images(f.key);
                            // }
                            // reader.readAsDataURL(file);
                        
                        display_images(new_file);
                        // console.log("success");
                    })
                    .catch((error) => console.error("Error creating file:", error));
            }
        }
    </script>
</body>

</html>