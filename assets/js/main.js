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

// Slider
let slideIndex = 1;
let next = document.getElementsByClassName("next")[0];
let previous = document.getElementsByClassName("previous")[0];
showSlides(slideIndex);

function nextSlide() {
    showSlides(slideIndex += 1);
}

function previousSlide() {
    showSlides(slideIndex -= 1);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    let slides = document.getElementsByClassName("item");

    if (n > slides.length) {
        slideIndex = 1;
    }
    if (n < 1) {
        slideIndex = slides.length;
    }

    for (let slide of slides) {
        slide.style.display = "none";
    }
    slides[slideIndex - 1].style.display = "block";
}
