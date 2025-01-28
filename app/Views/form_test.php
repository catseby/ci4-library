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
        let links = <?php echo $links; ?>;
        let file_arr = [];

        function display_images(file, reset = true) {

            if (reset) {
                document.getElementById("image-display").innerHTML = " ";
            }
            let container = document.createElement("div");
            container.style.position = 'relative';
            container.style.display = 'inline-block';
            container.style.margin = '10px';

            let image = document.createElement("img");
            image.width = 128;
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

            closeButton.onclick = function (e) {
                e.preventDefault();
                let parent = container.parentNode
                let index = Array.prototype.indexOf.call(
                    parent.children,
                    container
                );
                file_arr.splice(index, 1);
                container.remove();
            };

            container.appendChild(image);
            container.appendChild(closeButton);

            const reader = new FileReader();
            reader.onload = function (e) {
                image.src = e.target.result;
                document.getElementById("image-display").appendChild(container);
            };
            reader.readAsDataURL(file);
        }

        function display_selects(fetch_link, f) {
            $.ajax({
                url: fetch_link,
                type: "get",
                processData: false,
                contentType: false,
                success: function (response) {
                    let result = JSON.parse(response);

                    let dropdown = $('[name="' + f.key + '"]');
                    if (f.select.multiple) {
                        dropdown.attr("multiple", "multiple").select2();
                    }
                    dropdown.empty();
                    for (let i = 0; i < result.length; i++) {
                        // console.log(result[i]);
                        let option = $("<option>", {
                            value: parseInt(result[i].id),
                            text: result[i].item,
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
                    dropdown.trigger('change');
                },
                error: function (jqXHR, textStatus, errorThrown) { console.error(jqXHR); },
            });
        }

        function form_configure(f) {
            if (f.hasOwnProperty('image')) {
                async function createFileFromUrl(url, fileName) {
                    const resp = await fetch(url);
                    const blob = await resp.blob();
                    const file = new File([blob], fileName, {
                        type: blob.type,
                    });
                    return file;
                }


                for (let j = 0; j < value.length; j++) {
                    let filename = value[j][f.key];
                    createFileFromUrl("http://localhost:8080/uploads/" + filename, filename)
                        .then((new_file) => {
                            file_arr.push(new_file);
                            display_images(new_file, false);
                        })
                        .catch((error) => console.error("Error creating file:", error));
                }
            }

            if (f.hasOwnProperty('select')) {
                console.log(f.key);
                display_selects("http://localhost:8080/forms/" + f.select.table + "/fetch/" + f.select.column, f);

                if (f.select.dynamic_fetch == true) {
                    console.log(f.select);
                    let parent_select = $('[name="' + f.select.ref_fetch_key + '"]');

                    parent_select.on('change', function () {
                        let selected_text = $('[name="' + f.select.ref_fetch_key + '"] option:selected').text();

                        display_selects("http://localhost:8080/forms/" + f.select.table + "/fetch/" + f.select.column + "/" + f.select.ref_fetch_target + "/" + selected_text, f);
                    });
                }
            }
        }

        let multi_keys = [];
        function form_prepare(f) {
            if (f.hasOwnProperty('image')) {
                f.onChange = function () {
                    let files = document.getElementsByName(f.key)[0].files;
                    for (let j = 0; j < files.length; j++) {
                        let file = files[j];
                        file_arr.push(file);
                        display_images(file, false)
                    }
                }
                if (f.image.multiple) {
                    multi_keys.push(f.key);
                }
            }
        }

        function submit(i, values, extra, fk) {
            let link = links[i];

            let formData = new FormData();

            if (fk != null) {
                formData.append(link.param, fk);
            }

            for (let i = 0; i < Object.keys(schema.properties).length; i++) {
                let key = Object.keys(schema.properties)[i];

                if (link.keys.includes(key)) {
                    if (schema.properties[key].type == "file") {
                        // let files = document.getElementsByName(key)[0].files;
                        for (let j = 0; j < file_arr.length; j++) {
                            formData.append("files[]", file_arr[j]);

                            formData.append(key, "?filename");
                        }
                    }
                    else if (schema.properties[key].type == "select") {
                        let mult = $('[name="' + key + '"]').attr('multiple');
                        if (mult != undefined) {
                            let select_value = $('[name="' + key + '"]').val().map(Number);
                            formData.append(key, JSON.stringify(select_value));
                        } else {
                            let select_value = $('[name="' + key + '"] option:selected').text();
                            formData.append(key, select_value);
                        }
                    }
                    else if (schema.properties[key].type == "array") {
                        formData.append(key, JSON.stringify(values[key]));
                    }
                    else {
                        let value = values[key];
                        formData.append(key, value);
                    }
                }
            }

            $.ajax({
                url: "http://localhost:8080/forms/" + link.table + "/" + link.type + extra,
                type: "post",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    let resp = JSON.parse(response);
                    if (links.length - 1 > i) {
                        let x = "";
                        if (link.type != "add") {
                            x = "/" + resp.id + "/" + links[i + 1].param;
                        }
                        submit(i + 1, values, x, resp.id);
                        // console.log(extra);
                    }
                    console.log(resp);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error(jqXHR);
                    console.error(textStatus);
                    console.error(errorThrown);
                },
            });
        }
        // Pirms formas noformatēšana=================
        //====================================

        for (let i = 0; i < form.length; i++) {
            let f = form[i];

            if (f.type == 'fieldset') {
                for (let j = 0; j < f.items[0].items.length; j++) {
                    for (let k = 0; k < f.items[0].items[j].items.length; k++) {
                        let n_f = f.items[0].items[j].items[k];
                        form_prepare(n_f);
                    }
                }
            }
            else {
                form_prepare(f);
            }
        }

        //Formas ģenerēšana ==============================
        //===========================================
        $("#test-form").jsonForm({
            schema: schema,
            form: form,
            value: value[0],
            "onSubmitValid": function (values) {
                submit(0, values, "", null);
            }
        });

        // ģenerētās formas papildināšana ================
        // =====================================

        for (let i = 0; i < multi_keys.length; i++) {
            $('[name="' + multi_keys[i] + '"]').attr("multiple", "multiple");
        }

        for (let i = 0; i < form.length; i++) {
            let f = form[i];

            if (f.type == 'fieldset') {
                for (let j = 0; j < f.items[0].items.length; j++) {
                    for (let k = 0; k < f.items[0].items[j].items.length; k++) {
                        let n_f = f.items[0].items[j].items[k];
                        form_configure(n_f);
                    }
                }
            }
            else {
                form_configure(f);
            }
            // console.log(file_arr);
        }
    </script>
</body>

</html>