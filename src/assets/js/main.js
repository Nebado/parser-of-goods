// Initialization dom elements
let modal = document.getElementById("modal-table");
    table = document.getElementById("table"),
    showTableBtn = document.getElementById("show-table"),
    closeTableBtn = document.getElementById("close-table"),
    numberPages = document.getElementById("number-pages"),
    form = document.querySelector("form");

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
    inputField.setAttribute('name', 'field["custom"]');
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

// Spinner
const spinner = document.getElementById('spinner');

function showSpinner() {
    spinner.className = "show";
    setTimeout(() => {
        spinner.className = spinner.className.replace("show", "");
    }, 5000);
}

// Ajax request
form.addEventListener('submit', event => {
    event.preventDefault();
    const requestURL = window.location.href + 'parser';

    function sendRequest(method, url, body = null) {
        const headers = {
            'Content-Type': 'application/json'
        };

        showSpinner();

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
    let data = new FormData(form);

    function serializeForm(data) {
	    let obj = {};
	    for (let [key, value] of data) {
		    if (obj[key] !== undefined) {
			    if (!Array.isArray(obj[key])) {
				    obj[key] = [obj[key]];
			    }
			    obj[key].push(value);
		    } else {
			    obj[key] = value;
		    }
	    }
	    return obj;
    };

    const body = serializeForm(data);

    let tableEx = document.getElementById('table');
    if (tableEx) {
        tableEx.remove();
    }

    sendRequest('POST', requestURL, body)
        .then(data => initTable(data))
        .catch(err => console.error(err))
});

// Table
function initTable(data) {
    const fields = ['#', 'Name', 'Code', 'Price', 'Description', 'Image'];
    const table = document.createElement('table');
    const customFields = data[0].fields;

    table.setAttribute('id', 'table');

    let row = table.insertRow(-1);
    for (let i = 0; i < fields.length; ++i) {
        let headerCell = document.createElement('th');
        headerCell.innerHTML = fields[i];
        row.appendChild(headerCell);
    }

    if (customFields.length > 0) {
        for (let i = 0; i < customFields.length; ++i) {
            let headerCell = document.createElement('th');
            headerCell.innerHTML = "Custom Field"
            row.appendChild(headerCell);
        }
    }

    let trs = '';
    let counter = 1;
    for (let i = 0; i < data.length; ++i) {
        trs += "<tr>";
        trs += `<td>${counter}</td>
                <td>${data[i].name}</td>
                <td>${data[i].code}</td>
                <td>${data[i].price}</td>
                <td>${data[i].description}</td>
                <td>${data[i].photo}</td>`;

        if (customFields.length > 0) {
            for (let j = 0; j < customFields.length; ++j) {
                trs += `<td>${data[i].fields[j]}</td>`;
            }
        }

        trs += "</tr>";
        counter++;
    }

    showTableBtn.style.display = 'inline-block';
    table.innerHTML += trs;
    modal.appendChild(table);
}
