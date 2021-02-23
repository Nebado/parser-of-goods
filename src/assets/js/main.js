// Modal Window
let modal = document.getElementById("modalTable");
let btn = document.getElementById("btn");
let table = document.getElementById("table");
let span = document.getElementsByClassName("close")[0];
let paginationRange = document.getElementById("pagination-range"),
    numberPages = document.getElementById("number-pages");

btn.addEventListener("click", showFunc);
span.addEventListener("click", hideFunc);

function showFunc() {
    modal.style.display = "block";
    document.body.style.overflowY = "auto";
}

function hideFunc() {
    modal.style.display = "none";
    document.body.style.overflowY = "hidden";
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

// Add more fields
let i = 0;
function addField() {
    i++;
    let containerId = document.getElementById('form-fields'),
        inputField  = document.createElement('input'),
        removeBtn   = document.createElement('div');

    inputField.setAttribute('type', 'text');
    inputField.setAttribute('name', 'field[]');
    inputField.setAttribute('id', 'field'+i);
    inputField.setAttribute('class', 'input');
    inputField.setAttribute('placeholder', 'Enter selector of custom field');

    removeBtn.setAttribute('id', 'btnRem'+i);
    removeBtn.innerHTML = '-';
    removeBtn.setAttribute("onclick", "removeField('field"+i+"', 'btnRem"+i+"')");
    removeBtn.setAttribute('class', 'btn btn-remove');

    containerId.appendChild(inputField);
    containerId.appendChild(removeBtn);
}

function removeField(fieldId, btnId) {
    let field = document.getElementById(fieldId),
        btn = document.getElementById(btnId);
    field.parentNode.removeChild(field);
    btn.parentNode.removeChild(btn);
}

// Pagination
function pagination() {
    let checkPagination = document.getElementById('pagination-checkbox'),
        pagination      = document.getElementById('pagination');

    if (checkPagination.checked == true) {
        pagination.style.display = 'block';
    } else {
        pagination.style.display = 'none';
    }
}

// Background
(function() {
    var canvas = document.createElement('canvas'),
        ctx = canvas.getContext('2d'),
        w = canvas.width = innerWidth,
        h = canvas.height = innerHeight,
        particles = [],
        properties = {
            bgColor             : 'rgba(17, 17, 19, 1)',
            particleColor       : 'rgba(128, 128, 128, 1)',
            particleRadius      : 3,
            particleCount       : 100,
            particleMaxVelocity : 0.5,
            lineLength          : 150,
            particleLife        : 12
        };

    document.querySelector('body').appendChild(canvas);

    window.onresize = function() {
        w = canvas.width = innerWidth,
        h = canvas.height = innerHeight;
    }

    class Particle {
        constructor() {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.velocityX = Math.random() * (properties.particleMaxVelocity*2) - properties.particleMaxVelocity;
            this.velocityY = Math.random() * (properties.particleMaxVelocity*2) - properties.particleMaxVelocity;
            this.life = Math.random() * properties.particleLife * 60;
        }

        position() {
            this.x + this.velocityX > w && this.velocityX > 0 || this.x + this.velocityX < 0 && this.velocityX < 0 ? this.velocityX *= -1 : this.velocityX;
            this.y + this.velocityY > h && this.velocityY > 0 || this.y + this.velocityY < 0 && this.velocityY < 0 ? this.velocityY *= -1 : this.velocityY;
            this.x += this.velocityX;
            this.y += this.velocityY;
        }

        reDraw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, properties.particleRadius, 0, Math.PI*2);
            ctx.closePath();
            ctx.fillStyle = properties.particleColor;
            ctx.fill();
        }

        reCalculateLife() {
            if (this.life < 1) {
                this.x = Math.random() * w;
                this.y = Math.random() * h;
                this.velocityX = Math.random() * (properties.particleMaxVelocity*2) - properties.particleMaxVelocity;
                this.velocityY = Math.random() * (properties.particleMaxVelocity*2) - properties.particleMaxVelocity;
                this.life = Math.random() * properties.particleLife * 60;
            }
            this.life--;
        }
    }

    function reDrawBackground() {
        ctx.fillStyle = properties.bgColor;
        ctx.fillRect(0, 0, w, h);
    }

    function drawLines() {
        var x1, y1, x2, y2, length, opacity;

        for (var i in particles) {
            for (var j in particles) {
                x1 = particles[i].x;
                y1 = particles[i].y;
                x2 = particles[j].x;
                y2 = particles[j].y;
                length = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));

                if (length < properties.lineLength) {
                    opacity = 1 - length / properties.lineLength;
                    ctx.lineWidth = '0,5';
                    ctx.strokeStyle = 'rgba(128, 255, 128, '+opacity+')';
                    ctx.beginPath();
                    ctx.moveTo(x1, y1);
                    ctx.lineTo(x2, y2);
                    ctx.closePath();
                    ctx.stroke();
                }
            }
        }
    }

    function reDrawParticles() {
        for (var i in particles) {
            particles[i].reCalculateLife();
            particles[i].position();
            particles[i].reDraw();
        }
    }

    function loop() {
        reDrawBackground();
        reDrawParticles();
        drawLines();
        requestAnimationFrame(loop);
    }

    function init() {
        for (var i = 0; i < properties.particleCount; i++) {
            particles.push(new Particle);
        }

        loop();
    }

    init();
}())
