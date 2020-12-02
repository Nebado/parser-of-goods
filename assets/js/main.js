// Modal Window
var modal = document.getElementById("modalTable");
var btn = document.getElementById("btn");
var table = document.getElementById("table");
var span = document.getElementsByClassName("close")[0];

btn.addEventListener("click", showFunc);
span.addEventListener("click", hideFunc);

function showFunc() {
    modal.style.display = "block";
}

function hideFunc() {
    modal.style.display = "none";
}

// Show Table
if (table) {
    btn.style.display = "inline-block";
} else {
    btn.style.display = "none";
}
