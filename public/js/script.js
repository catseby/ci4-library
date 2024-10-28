console.log("loaded");

document.getElementById("add-button").onclick = function (evt) {
    evt.preventDefault();

    let add = document.getElementById("add-modal")
    add.classList.remove("hidden");

    let x = add.querySelector(".x-button");
    x.onclick = function (){
        x.parentElement.parentElement.classList.add("hidden");
    }

};

console.log("added");