/**
 * Runs function when document is ready.
 *
 * @param {function} fn The function to execute.
 */
function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

const getProductIds = async () => {
    const res = await fetch("/recommendation_config?" + new URLSearchParams({
        req: 'getProductIds'
    }));
    if (!res.ok) {
        const message = `An error has occured: ${res.status}`;
        throw new Error(message);
    }
    const data = await res.json();
    return data;
}

const getCurrentCategory = async () => {
    const res = await fetch("/recommendation_config?" + new URLSearchParams({
        req: 'getCurrentCategory'
    }));
    if(!res.ok){
        const message = `An error has occured: ${res.status}`;
        throw new Error(message);
    }
    const data = await res.json();
    return data;
}

const getSortOrder = async () => {
    const res = await fetch("/recommendation_config?" + new URLSearchParams({
        req: 'getSortOrder'
    }));
    if(!res.ok){
        const message = `An error has occured: ${res.status}`;
        throw new Error(message);
    }
    const data = await res.json();
    return data;
}

const hideMessages = () => {
    const messages = document.querySelectorAll('.messageBox');
    messages.forEach(messageBox => {
        messageBox.remove();
    });
}

const showMessage = (message, isPositive) => {
    hideMessages();
    const messageBox = document.createElement('div');
    messageBox.classList.add('messageBox');
    messageBox.innerHTML = message;
    const closeButton = document.createElement('div');
    closeButton.innerHTML = '✖';
    closeButton.classList.add('closeButton');
    messageBox.appendChild(closeButton);
    if(isPositive){
        messageBox.classList.add('positive');
    }
    else{
        messageBox.classList.add('negative');
    }
    const hideInterval = 5000;
    const hideTimer = setTimeout(hideMessages, hideInterval);
    closeButton.addEventListener('mousedown', () => {
        hideMessages();
        clearTimeout(hideTimer);
    });
    document.body.appendChild(messageBox);
}

const handleDelete = async event => {
    const eventCaller = event.target;
    const elementToDelete = eventCaller.getAttribute('productId');
    const requestHeaders = new Headers();
    requestHeaders.append('Content-Type', 'application/x-www-form-urlencoded');
    const requestBody = new URLSearchParams({
        productId: elementToDelete,
        req: 'removeProductId'
    });
    fetch('/recommendation_config', {
        method: 'POST',
        headers: requestHeaders,
        body: requestBody
    }).then(resp => {
        return resp.json();
    }).then(data => {
        if (data.isCorrect) {
            showMessage(data.message, true);
            eventCaller.parentElement.remove();
        } else {
            showMessage(data.message, false);
        }
    });
}

const addProductEntry = productId => {
    const productConfiguration = document.querySelector(".currentProducts");
    const configProduct = document.createElement('div');
    configProduct.classList.add('configProduct');
    const idContainer = document.createElement('div');
    idContainer.classList.add('idContainer');
    idContainer.innerHTML = productId;
    configProduct.appendChild(idContainer);
    const deleteProduct = document.createElement('div');
    deleteProduct.classList.add('deleteProduct');
    deleteProduct.setAttribute('productId', productId);
    deleteProduct.addEventListener('mousedown', handleDelete);
    configProduct.appendChild(deleteProduct);
    productConfiguration.appendChild(configProduct);
}

const loadConfiguration = async () => {
    const productIds = [];
    await getProductIds().then(resp => {
        resp.data.forEach(product => {
            productIds.push(product.id_product);
        });
    }).then(() => {
        if (productIds.length) {
            productIds.forEach(productId => {
                addProductEntry(productId);
            });
        }
    });
    await getCurrentCategory().then(resp => {
        const categoryChangeInput = document.querySelector(".recommendedCategoryConfig > input");
        if(categoryChangeInput){
            categoryChangeInput.value = resp.data;
        }
    });
    await getSortOrder().then(resp => {
        const sortChangeSelect = document.querySelector(".categorySortOrder > select");
        if(sortChangeSelect){
            sortChangeSelect.value = resp.data;
        }
    });
}

const clearProductEntries = () => {
    const productConfiguration = document.querySelector(".currentProducts");
    while(productConfiguration.lastChild){
        productConfiguration.removeChild(productConfiguration.lastChild);
    }
}

const handleRefresh = () => {
    clearProductEntries();
    loadConfiguration();
}

const handleAdd = async event => {
    const eventCaller = event.target;
    const newProductContainer = eventCaller.parentElement;
    const inputValue = newProductContainer.querySelector('input[name=productId]').value;
    const isValid = /^\d*$/.test(inputValue);
    if (isValid) {
        const requestHeaders = new Headers();
        requestHeaders.append('Content-Type', 'application/x-www-form-urlencoded');
        const requestBody = new URLSearchParams({
            productId: inputValue,
            req: 'addProductId'
        });
        await fetch('/recommendation_config', {
            method: 'POST',
            headers: requestHeaders,
            body: requestBody
        }).then(resp => {
            return resp.json();
        }).then(data => {
            if (data.isCorrect) {
                showMessage(data.message, true);
                addProductEntry(inputValue);
                newProductContainer.querySelector('input[name=productId]').value = '';
            } else {
                showMessage(data.message, false);
            }
        })
    } else {
        showMessage('Niepoprawne id', false);
    }
}

const handleChangeCategory = async event => {
    const eventCaller = event.target;
    const categoryChangeContainer = eventCaller.parentElement;
    const inputValue = categoryChangeContainer.querySelector('input[name=categoryId]').value;
    const isValid = /^[\d,]*$/.test(inputValue);
    if(isValid){
        const requestHeaders = new Headers();
        requestHeaders.append('Content-Type', 'application/x-www-form-urlencoded');
        const requestBody = new URLSearchParams({
            categoryId: inputValue,
            req: 'changeCategory'
        });
        await fetch('/recommendation_config', {
            method: 'POST',
            headers: requestHeaders,
            body: requestBody
        }).then(resp => {
            return resp.json();
        }).then(data => {
            if (data.isCorrect) {
                showMessage(data.message, true);
                categoryChangeContainer.querySelector('input[name=categoryId]').value = data.categoryValue;
            } else {
                showMessage(data.message, false);
                if('categoryValue' in data){
                    categoryChangeContainer.querySelector('input[name=categoryId]').value = data.categoryValue;
                }
            }
        })
    } else {
        showMessage('Niepoprawny format. Pole kategorii może zawierać tylko liczby i przecinki.', false);
    }
}

const handleChangeSort = async event => {
    const eventCaller = event.target;
    const newValue = eventCaller.value;
    const isValid = /^[a-zA-Z]*$/.test(newValue);
    if(isValid){
        const requestHeaders = new Headers();
        requestHeaders.append('Content-Type', 'application/x-www-form-urlencoded');
        const requestBody = new URLSearchParams({
            sortOrder: newValue,
            req: 'changeSortOrder'
        });
        await fetch('/recommendation_config', {
            method: 'POST',
            headers: requestHeaders,
            body: requestBody
        }).then(resp => {
            return resp.json();
        }).then(data => {
            if (data.isCorrect) {
                showMessage(data.message, true);
                eventCaller.value = data.sortValue;
            } else {
                showMessage(data.message, false);
                if('sortValue' in data){
                    eventCaller.value = data.sortValue;
                }
            }
        });
    }
    else{
        showMessage('Niepoprawna wartość', false);
    }
}

const attachEvents = () => {
    const refreshButton = document.querySelector(".reload");
    refreshButton.addEventListener('mousedown', handleRefresh);
    const addButton = document.querySelector(".addNewProduct");
    addButton.addEventListener('mousedown', handleAdd);
    const changeCategoryButton = document.querySelector(".changeCategory");
    changeCategoryButton.addEventListener('mousedown', handleChangeCategory);
    const changeSortSelect = document.querySelector(".categorySortOrder > select");
    changeSortSelect.addEventListener('change', handleChangeSort);
}

docReady(() => {
    loadConfiguration();
    attachEvents();
});