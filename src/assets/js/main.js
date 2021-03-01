// Initialization dom elements 
let modal = document.getElementById("modal-table");
    table = document.getElementById("table"),
    showTableBtn = document.getElementById("show-table"),
    closeTableBtn = document.getElementById("close-table"),
    numberPages = document.getElementById("number-pages"),

showTableBtn.addEventListener("click", showFunc);
closeTableBtn.addEventListener("click", hideFunc);

function showFunc() {
    modal.style.display = "block";
    document.body.style.overflowY = "auto";
}

function hideFunc() {
    modal.style.display = "none";
    document.body.style.overflowY = "hidden";
}

// Main Slider
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

// Custom fields
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

// Ajax request
//function ajaxRequest(event) {
document.querySelector('form').addEventListener('submit', event => {
    event.preventDefault();

    const requestURL = 'http://localhost:6363/parser';

    function sendRequest(method, url, body = null) {
        const headers = {
            'Content-Type': 'application/json'
        };

        return fetch(url, {
            method: method,
            body: JSON.stringify(body),
            headers: headers
        }).then(response => {
            if (response.ok) {
                return response.json();
            }

            return response.json().then(error => {
                const e = new Error('Error');
                e.data = error;
                throw e;
            })
        });
    }

    // Form fields
    let target = event.currentTarget;
    let form = document.querySelector('form');
    let serializeForm = function (form) {
	let obj = {};
	let formData = new FormData(form);
	for (let key of formData.keys()) {
		obj[key] = formData.get(key);
	}
	return obj;
    };

    const body = serializeForm(target); 

    sendRequest('POST', requestURL, body)
        .then(data => initTable(data))
        .catch(err => console.error(err))

});

function initTable(data) {
    const table = document.createElement('table');
    table.setAttribute('id', 'table');
    table.innerHTML = `<thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Code</th>
            <th>Price</th>
            <th>Description</th>
            <th>Image</th>
        <tr>
    </thead>`;

    const trs = data.map((item, index) => (
        `<tr>
            <td>${index + 1}</td>
            <td>${item.name}</td>
            <td>${item.code}</td>
            <td>${item.price}</td>
            <td>${item.description}</td>
            <td>${item.photo}</td>
        </tr>`
    ))

    showTableBtn.style.display = 'inline-block';

    table.innerHTML += trs.join("\n");

    modal.appendChild(table);
}
