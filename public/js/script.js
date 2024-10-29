console.log("loaded");

document.getElementById("add-button").onclick = function (evt) {
  evt.preventDefault();

  let add = document.getElementById("add-modal");
  add.classList.remove("hidden");

  let x = add.querySelector(".x-button");
  x.onclick = function () {
    x.parentElement.parentElement.classList.add("hidden");
  };
};

$("#popup-form").jsonForm({
  schema: {
    book_desc: {
      type: "object",
      title: " ",
      properties: {
        isbn: {
          type: "string",
          title: "Book ISBN",
          required: true
        },
        title: {
          type: "string",
          title: "Book Title",
          required: true
        },
        author: {
          type: "string",
          title: "Book Author",
          required: true
        },
        category: {
          type: "string",
          title: "Book Category",
          required: true
        },
      }
    },
    favorite_tv: {
      type: "string",
      title: "Favorite TV Series",
      enum: [
        "The Big Bang Theory",
        "Friends",
        "Grey's Anatomy",
        "Babylon 5",
        "Firefly",
        "The Flinstones",
      ],
    },
    actor_male: {
      type: "string",
      title: "Favorite Male Actor",
      enum: [
        "Tom Hanks",
        "Sean Connery",
        "Mark Harmon",
        "Client Eastwood",
        "Neil Patrick Harris",
      ],
    },
    actor_female: {
      type: "string",
      title: "Favorite Female Actor",
      enum: ["Emily Blunt", "Julie Andrews", "Meryl Streep", "Helen Mirren"],
    },
  },
  form: [
    {
      type: "fieldset",
      title: "Example of Tabs",
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
                  key: "book_desc"
                },
              ],
            },
            {
              title: "TV Series",
              type: "tab",
              items: [
                {
                  key: "favorite_tv",
                  type: "radiobuttons",
                  activeClass: "btn-success",
                },
              ],
            },
            {
              title: "Actors",
              type: "tab",
              items: [
                {
                  key: "actor_male",
                  type: "radiobuttons",
                  activeClass: "btn-success",
                },
                {
                  key: "actor_female",
                  type: "radiobuttons",
                  activeClass: "btn-success",
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
  ],
  onSubmit : function (errors,values) {
    if (errors) {
      console.log(errors);
    } else{
      console.log(values);
    }
  },
});
console.log("added");
