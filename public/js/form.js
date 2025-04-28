let file_arr = [];

function display_images(file, reset = true) {
  if (reset) {
    document.getElementById("image-display").innerHTML = " ";
  }

  let container = document.createElement("div");
  container.style.position = "relative";
  container.style.display = "block";
  container.style.margin = "10px";

  let closeButton = document.createElement("button");
  closeButton.textContent = "X";

  closeButton.style.position = "absolute";
  closeButton.style.top = "5px";
  closeButton.style.right = "5px";
  closeButton.style.backgroundColor = "red";
  closeButton.style.color = "white";
  closeButton.style.border = "none";
  closeButton.style.borderRadius = "50%";
  closeButton.style.opacity = "0.7";
  // closeButton.style.width = '20px';
  // closeButton.style.height = '20px';
  closeButton.style.textAlign = "center";
  closeButton.style.cursor = "pointer";
  closeButton.style.fontSize = "14px";
  closeButton.style.lineHeight = "20px";

  closeButton.onclick = function (e) {
    e.preventDefault();
    let parent = container.parentNode;
    let index = Array.prototype.indexOf.call(parent.children, container);
    file_arr.splice(index, 1);
    container.remove();
  };

  const reader = new FileReader();

  switch (file.type) {
    case "image/jpeg":
    case "image/png":
      let image = document.createElement("img");
      image.width = 312;
      image.style.display = "block";

      closeButton.onclick = function (e) {
        e.preventDefault();
        let parent = container.parentNode;
        let index = Array.prototype.indexOf.call(parent.children, container);
        file_arr.splice(index, 1);
        container.remove();
      };

      container.appendChild(image);
      container.appendChild(closeButton);

      reader.onload = function (e) {
        image.src = e.target.result;
        document.getElementById("image-display").appendChild(container);
      };

      reader.readAsDataURL(file);
      break;

    default:
      let icon = document.createElement("img");
      icon.width = 18;
      icon.style.display = "block";
      icon.src = "https://www.svgrepo.com/download/89356/blank-file.svg";

      switch (file.type) {
        case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
          icon.src =
            "https://www.svgrepo.com/show/48996/docx-file-format-symbol.svg";
          break;
        case "application/pdf":
          icon.src =
            "https://upload.wikimedia.org/wikipedia/commons/thumb/3/38/Icon_pdf_file.svg/502px-Icon_pdf_file.svg.png";
          break;
        case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
          icon.src =
            "https://www.svgrepo.com/show/42291/xlsx-file-format-extension.svg";
          break;
      }

      let file_name = document.createElement("p");
      file_name.innerHTML = file.name;

      container.appendChild(icon);
      container.appendChild(file_name);
      container.appendChild(closeButton);

      reader.onload = function (e) {
        // image.src = e.target.result;
        document.getElementById("file-display").appendChild(container);
      };

      reader.readAsDataURL(file);
      break;
  }
}

function display_selects(fetch_link, f, dyn = false) {
  console.log(fetch_link);
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
          if (Array.isArray(value[0][f.key]) == false) {
            value[0][f.key] = [value[0][f.key]];
          }
          for (let j = 0; j < value[0][f.key].length; j++) {
            // console.log(value[0][f.key]);
            if (
              value[0][f.key][j] == parseInt(result[i].id) ||
              value[0][f.key] == result[i].item
            ) {
              option.attr("selected", "selected");
            }
          }
        }
        dropdown.append(option);
      }
      dropdown.trigger("change");
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error(jqXHR);
    },
  });
}

function form_configure(f) {
  if (f.hasOwnProperty("image") || f.hasOwnProperty("file")) {
    async function createFileFromUrl(url, fileName) {
      const resp = await fetch(url);
      const blob = await resp.blob();
      const file = new File([blob], fileName, {
        type: blob.type,
      });
      return file;
    }

    console.log(value.length);
    for (let j = 0; j < value.length; j++) {
      if (value[j][f.key] != null) {
        let filename = value[j][f.key];
        createFileFromUrl("http://localhost:8080/uploads/" + filename, filename)
          .then((new_file) => {
            file_arr.push(new_file);
            display_images(new_file, false);
          })
          .catch((error) => console.error("Error creating file:", error));
      }
    }
  }

  if (f.hasOwnProperty("select")) {
    display_selects(
      "http://localhost:8080/forms/" +
        f.select.table +
        "/fetch/" +
        f.select.column,
      f,
      false
    );

    if (f.select.dynamic_fetch == true) {
      console.log(f.select);
      let parent_select = $('[name="' + f.select.ref_fetch_key + '"]');

      parent_select.on("change", function () {
        console.log("change");
        let selected_text = $(
          '[name="' + f.select.ref_fetch_key + '"] option:selected'
        ).text();

        display_selects(
          "http://localhost:8080/forms/" +
            f.select.table +
            "/fetch/" +
            f.select.column +
            "/" +
            f.select.ref_fetch_target +
            "/" +
            selected_text,
          f
        );
      });
    }
  }
}

let multi_keys = [];
function form_prepare(f) {
  if (f.hasOwnProperty("image") || f.hasOwnProperty("file")) {
    f.onChange = function () {
      let files = document.getElementsByName(f.key)[0].files;
      for (let j = 0; j < files.length; j++) {
        let file = files[j];
        file_arr.push(file);
        display_images(file, false);
      }
    };
    if (
      (f.image != undefined && f.image.multiple) ||
      (f.file != undefined && f.file.multiple)
    ) {
      multi_keys.push(f.key);
    }
  }
}

function submit(values) {
  let data = [];
  let formData = new FormData();

  for (let i = 0; i < Object.keys(schema.properties).length; i++) {
    let key = Object.keys(schema.properties)[i];

    let segment = { key: key, value: [], files: true };
    if (schema.properties[key].type == "file") {
      for (let j = 0; j < file_arr.length; j++) {
        formData.append("files[]", file_arr[j]);
        segment.value.push(file_arr[j].name);
      }
      data.push(segment);
    } else if (schema.properties[key].type == "select") {
      let mult = $('[name="' + key + '"]').attr("multiple");

      if (mult != undefined) {
        if ($('[name="' + key + '"]').val() != null) {
          let select_value = $('[name="' + key + '"]')
            .val()
            .map(Number);
          data.push({
            key: key,
            value: JSON.stringify(select_value),
          });
        }
      } else {
        let select_value = $('[name="' + key + '"] option:selected').text();
        data.push({ key: key, value: select_value });
      }
    } else if (schema.properties[key].type == "array") {
      data.push({ key: key, value: JSON.stringify(values[key]) });
    } else {
      data.push({ key: key, value: values[key] });
    }
  }

  console.log(data);
  formData.append("data", JSON.stringify(data));

  $.ajax({
    url: link,
    type: "post",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      let mesg = document.getElementById("message");
      mesg.innerHTML = JSON.parse(response).message;
      mesg.style.color = "green";
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error(jqXHR);
      console.error(textStatus);
      console.error(errorThrown);

      let mesg = document.getElementById("message");
      mesg.innerHTML = "Someting went wrong.";
      mesg.style.color = "red";
    },
  });
}

// // Pirms formas noformatēšana=================
// //====================================
for (let i = 0; i < form.length; i++) {
  let f = form[i];

  if (f.type == "fieldset") {
    for (let j = 0; j < f.items[0].items.length; j++) {
      for (let k = 0; k < f.items[0].items[j].items.length; k++) {
        let n_f = f.items[0].items[j].items[k];
        form_prepare(n_f);
      }
    }
  } else {
    form_prepare(f);
  }
}

if (value.length > 0) {
  for (let i = 0; i < Object.keys(schema.properties).length; i++) {
    let key = Object.keys(schema.properties)[i];
    let f = schema.properties[key];

    if (f.type == "boolean") {
      let flag = value[0][key];

      console.log(flag);
      if (flag == "t") {
        value[0][key] = true;
      } else if (flag == "f") {
        value[0][key] = false;
      }
    }
  }
}

//Formas ģenerēšana ==============================
//===========================================
$("#test-form").jsonForm({
  schema: schema,
  form: form,
  value: value[0],
  onSubmitValid: function (values) {
    submit(values);
  },
});

// ģenerētās formas papildināšana ================
// =====================================
for (let i = 0; i < multi_keys.length; i++) {
  $('[name="' + multi_keys[i] + '"]').attr("multiple", "multiple");
}

for (let i = 0; i < form.length; i++) {
  let f = form[i];

  if (f.type == "fieldset") {
    for (let j = 0; j < f.items[0].items.length; j++) {
      for (let k = 0; k < f.items[0].items[j].items.length; k++) {
        let n_f = f.items[0].items[j].items[k];
        form_configure(n_f);
      }
    }
  } else {
    form_configure(f);
  }
}
