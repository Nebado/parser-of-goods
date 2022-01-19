/* Initialization dom elements */
const modal = document.getElementById("modal-table"),
      table = document.getElementById("table"),
      showTableBtn = document.getElementById("show-table"),
      closeTableBtn = document.getElementById("close-table"),
      numberPages = document.getElementById("number-pages"),
      form = document.querySelector("form"),
      downloadExcel = document.getElementById("download_excel"),
      downloadImage = document.getElementById("download_image");

showTableBtn.addEventListener("click", show);
closeTableBtn.addEventListener("click", hide);

function show() {
    modal.style.display = "block";
    document.body.style.overflowY = "auto";
}

function hide() {
    modal.style.display = "none";
    document.body.style.overflowY = "hidden";
}

/* Slider */
let slideIndex = 1;
const next = document.getElementsByClassName("next")[0];
const previous = document.getElementsByClassName("previous")[0];
showSlide(slideIndex);

function nextSlide() {
    showSlide(slideIndex += 1);
}

function previousSlide() {
    showSlide(slideIndex -= 1);
}

function currentSlide(n) {
    showSlide(slideIndex = n);
}

function showSlide(n) {
    const slides = document.getElementsByClassName("item");

    if (n > slides.length) {
        slideIndex = 1;
    }
    
    if (n < 1) {
        slideIndex = slides.length;
    }

    for (let slide of slides) {
        slide.classList.remove("active");
    }
    
    slides[slideIndex - 1].classList.add("active");
}

/* Additional fields in form */
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

/* Pagination */
function showPagination() {
    let checkboxPagination   = document.getElementById('pagination-checkbox'),
        paginationBlock      = document.getElementById('pagination');

    if (checkboxPagination.checked == true) {
        paginationBlock.style.display = 'block';
    } else {
        paginationBlock.style.display = 'none';
    }
}

/* Loader */
const loader = document.getElementById('loader');

function showLoader() {
    loader.className = "show";
}

function hideLoader() {
    loader.className = loader.className.replace("show", "");
}

/* Ajax request */
let controller = new AbortController();

function abortFetching() {
    hideLoader();
    controller.abort();
}

form.addEventListener('submit', event => {
    event.preventDefault();
    const requestURL = window.location.href + 'parser';

    function sendRequest(method, url, body = null) {
        controller = new AbortController();
        const headers = {
            'Content-Type': 'application/json'
        };

        showLoader();
        previousSlide();

        return fetch(url, {
            method: method,
            signal: controller.signal,
            body: JSON.stringify(body),
            headers: headers
        }).then(response => {
            hideLoader();
            if (response.ok) {
                return response.json();
            }

            return response.json().then(error => {
                hideLoader();
                const e = new Error('Error');
                e.data = error;
                throw e;
            });
        });
    }

    let target = event.currentTarget;
    let form = document.querySelector('form');
    let formData = new FormData(form);

    function serializeForm(formData) {
	    let obj = {};
	    for (let [key, value] of formData) {
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

    const body = serializeForm(formData);

    let tableEx = document.getElementById('table');
    if (tableEx) {
        tableEx.remove();
    }

    sendRequest('POST', requestURL, body)
        .then(data => {
            if (data['errors']) {
                errorHandle(data['errors']);
            } else {
                if (data['zipFile']) {
                    downloadImage.style.display = "inline-block";
                } else {
                    downloadImage.style.display = "none";
                }

                if (data['excelFile']) {
                    downloadExcel.style.display = "inline-block";
                } else {
                    downloadExcel.style.display = "none";
                }
                
                initTable(data['products']);
            }
        })
        .catch(err => console.error(err))
});

/* Table */
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

/* Error box */
function errorHandle(errors) {
    const errorsBox = document.querySelector('.errors');
    const errorsContainer = document.createElement('div');

    for (let i = 0; i < errors.length; ++i) {
        let error = document.createElement('div');
        error.innerHTML = errors[i];
        errorsContainer.appendChild(error);
    }

    errorsBox.appendChild(errorsContainer);

    setTimeout(() => {
        errorsContainer.remove();
    }, 10000);
}
