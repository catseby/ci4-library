console.log("loaded");

document.getElementById("add-button").onclick = function (evt) {
  evt.preventDefault();

  let add = document.getElementById("add-modal");
  add.classList.remove("hidden");
  document.getElementById("popup-title").innerHTML = "Add Book";

  $("#popup-form").empty();
  $("#popup-form").jsonForm({
    schema,
    form,
    onSubmit: function (errors, values) {
      addBook(values);
    },
  });

  seedCategory([]);

  let x = add.querySelector(".x-button");
  x.onclick = function () {
    x.parentElement.parentElement.classList.add("hidden");
  };
};

var file_array = [];

var schema = {
  book_desc: {
    type: "object",
    title: " ",
    properties: {
      isbn: {
        type: "integer",
        title: "Book ISBN",
        required: true,
      },
      title: {
        type: "string",
        title: "Book Title",
        required: true,
      },
      author: {
        type: "string",
        title: "Book Author",
        required: true,
      },
      category: {
        type: "string",
        title: "Book Category",
        enum: [],
      },
    },
  },
  image: {
    type: "object",
    title: " ",
    properties: {
      file: {
        type: "file",
        title: "Book Images",
        format: "file",
        accept: ".jpg,.png",
      },
    },
  },
  tags: {
    type: "object",
    title: " ",
  },
};

var form = [
  {
    type: "fieldset",
    items: [
      {
        type: "tabs",
        id: "navtabs",
        items: [
          {
            title: "Book Description",
            type: "tab",
            items: [
              {
                key: "book_desc",
              },
            ],
          },
          {
            title: "Images",
            type: "tab",
            items: [
              {
                key: "image",
                onChange: function () {
                  let file =
                    document.getElementsByName("image.file")[0].files[0];
                  console.log(
                    document.getElementsByName("image.file")[0].files
                  );

                  //file.file_name = file.name.replace(/\.[^/.]+$/, "");

                  file_array.push(file);

                  let new_div = document.createElement("div");
                  let image = document.createElement("img");
                  let input = document.createElement("input");
                  let button = document.createElement("button");

                  image.width = 100;

                  input.type = "text";
                  input.value = file.name.replace(/\.[^/.]+$/, "");
                  input.placeholder = "Image Title";
                  input.onchange = function () {
                    //file.file_name = input.value;
                    console.log(file);
                  };

                  button.innerHTML = "Remove";

                  button.onclick = function () {
                    let parent = new_div.parentNode;
                    let index = Array.prototype.indexOf.call(
                      parent.children,
                      new_div
                    );
                    file_array.splice(index, 1);
                    new_div.remove();
                  };

                  new_div.appendChild(image);
                  new_div.appendChild(input);
                  new_div.appendChild(button);

                  const reader = new FileReader();
                  reader.onload = function (e) {
                    image.src = e.target.result;
                    document.getElementById("image-div").appendChild(new_div);
                  };
                  reader.readAsDataURL(file);
                },
              },
              {
                type: "section",
                id: "image-div",
              },
            ],
          },
          {
            title: "Tags",
            type: "tab",
            items: [
              {
                key: "tags",
              },
            ],
          },
        ],
      },
    ],
  },
  {
    type: "actions",
    items: [
      {
        type: "submit",
        value: "Submit",
      },
    ],
  },
];

function addBook(values) {
  values.book_desc.category = $('[name="book_desc.category"]')
    .val()
    .map(Number);

  let formData = new FormData();
  formData.append("isbn", values.book_desc.isbn);
  formData.append("title", values.book_desc.title);
  formData.append("author", values.book_desc.author);
  formData.append("category", JSON.stringify(values.book_desc.category));

  for (let i = 0; i < file_array.length; i++){
    formData.append("files[]", file_array[i]);
  }

  file_array = [];

  $.ajax({
    url: "http://localhost:8080/books",
    type: "post",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      console.log("saved");
      location.reload();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error(jqXHR);
    },
  });
}

function editBook(id, values) {
  values.book_desc.category = $('[name="book_desc.category"]')
    .val()
    .map(Number);

  let formData = new FormData();
  formData.append("isbn", values.book_desc.isbn);
  formData.append("title", values.book_desc.title);
  formData.append("author", values.book_desc.author);
  formData.append("category", JSON.stringify(values.book_desc.category));

  $.ajax({
    url: "http://localhost:8080/books/" + id,
    type: "post",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      console.log("updated");
      location.reload();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error(jqXHR);
    },
  });
}

function updateBook(values) {}

function fetchBooks() {
  var categories = fetchCategories();
  $.ajax({
    url: "http://localhost:8080/books",
    method: "get",
    contentType: false,
    processData: false,
    success: function (data) {
      for (let i = 0; i < data.books.length; i++) {
        let book = data.books[i];

        console.log(book.category);

        let parseBooks = JSON.parse(book.category);

        let book_categories = [];
        for (let j = 0; j < parseBooks.length; j++) {
          let category_name = "?";
          for (let k = 0; k < categories.length; k++) {
            if (String(parseBooks[j] + 1) == categories[k].id) {
              category_name = categories[k].category_name;
              break;
            }
          }
          book_categories.push(category_name);
        }

        let tr = document.createElement("tr");
        tr.innerHTML =
          "<td>" +
          book.id +
          "</td>" +
          "<td>" +
          book.isbn +
          "</td>" +
          "<td>" +
          book.title +
          "</td>" +
          "<td>" +
          book.author +
          "</td>" +
          "<td>" +
          book_categories +
          "</td>" +
          "<td>No Images</td>";

        let actions = document.createElement("td");

        let edit_button = document.createElement("button");
        edit_button.innerHTML = "Edit";

        edit_button.onclick = function (evt) {
          evt.preventDefault();

          let add = document.getElementById("add-modal");
          add.classList.remove("hidden");
          document.getElementById("popup-title").innerHTML = "Edit Book";

          let x = add.querySelector(".x-button");
          x.onclick = function () {
            x.parentElement.parentElement.classList.add("hidden");
          };

          $("#popup-form").empty();
          $("#popup-form").jsonForm({
            schema,
            form,
            value: {
              book_desc: {
                isbn: book.isbn,
                title: book.title,
                author: book.author,
              },
            },
            onSubmit: function (errors, values) {
              editBook(book.id, values);
            },
          });

          seedCategory(JSON.parse(book.category));
        };

        let delete_button = document.createElement("button");
        delete_button.innerHTML = "Delete";
        delete_button.onclick = function (evt) {
          $.ajax({
            url: "http://localhost:8080/books/" + book.id,
            method: "delete",
            contentType: false,
            processData: false,
            success: function (response) {
              location.reload();
            },
            error: function (error) {
              console.log(error);
            },
          });
        };

        actions.appendChild(edit_button);
        actions.appendChild(delete_button);

        tr.appendChild(actions);

        document.getElementById("table").appendChild(tr);
      }
    },
  });
}

function seedCategory(selectedCategory) {
  $.ajax({
    url: "http://localhost:8080/categories",
    method: "get",
    contentType: false,
    processData: false,
    success: function (data) {
      var categoryDropdown = $('[name="book_desc.category"]');
      categoryDropdown.attr("multiple", "multiple").select2();
      categoryDropdown.empty();

      for (let i = 0; i < data.categories.length; i++) {
        let category = data.categories[i];
        var option = $("<option>", {
          value: i,
          text: category.category_name,
        });
        if (
          Array.isArray(selectedCategory) &&
          selectedCategory.includes(JSON.parse(category.id) - 1)
        ) {
          option.attr("selected", "selected");
        }
        categoryDropdown.append(option);
      }

      document
        .getElementById("jsonform-1-elt-book_desc.category")
        .classList.add("hidden");
      categoryDropdown.trigger("change");
      categoryDropdown
        .select2()
        .next(".select2-container")
        .css("width", "100%");
    },
  });
}

function fetchCategories() {
  let arr = [];
  $.ajax({
    url: "http://localhost:8080/categories",
    method: "get",
    contentType: false,
    processData: false,
    success: function (data) {
      for (let i = 0; i < data.categories.length; i++) {
        arr.push(data.categories[i]);
      }
    },
  });
  return arr;
}

fetchBooks();
