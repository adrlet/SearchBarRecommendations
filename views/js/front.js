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

const searchProducts = async searchedPhrase => {
    const res = await fetch("/search_products?" + new URLSearchParams({
        q: searchedPhrase
    }));
    if (!res.ok) {
        const message = `An error has occured: ${res.status}`;
        throw new Error(message);
    }
    const data = await res.json();
    return data;
}

const renderProducts = productsArray => {
    const searchContainer = document.querySelector("#search_widget").parentElement;
    if (searchContainer) {
        if (searchContainer.querySelector(".recommendationsContainer")) {
            searchContainer.querySelector(".recommendationsContainer").remove();
        }

        const recommendationsContainer = document.createElement('div');
        recommendationsContainer.classList.add('recommendationsContainer');
        const recommendationsTitle = document.createElement('div');
        recommendationsTitle.classList.add('recommendationsTitle');
        recommendationsTitle.innerHTML = "Nasze rekomendacje";
        recommendationsTitle.setAttribute('withResults', 'Nasze rekomendacje');
        recommendationsTitle.setAttribute('withoutResults', 'Nie znaleźliśmy czego szukasz, ale mamy ...');
        recommendationsContainer.appendChild(recommendationsTitle);
        productsArray.forEach(product => {
            const productContainer = document.createElement('a');
            productContainer.setAttribute('href', product.link);
            productContainer.classList.add("productContainer");
            const image = document.createElement('img');
            image.setAttribute('src', 'https://' + product.image);
            productContainer.appendChild(image);
            const title = document.createElement('div');
            title.classList.add('productTitle');
            title.innerHTML = product.name;
            productContainer.appendChild(title);
            const price = document.createElement('div');
            price.classList.add('productPrice');
            price.innerHTML = product.price;
            productContainer.appendChild(price);
            recommendationsContainer.appendChild(productContainer);
        });
        searchContainer.appendChild(recommendationsContainer);
    }
}

const handleKeyup = async event => {
    const searchedInput = `${event.target.value}`;
    const productsData = [];
    searchProducts(searchedInput).then(data => {
        data.forEach(product => {
            productsData.push(product);
        });
    }).then(() => {
        if (productsData.length) {
            renderProducts(productsData);
        }
    });
}

const searchbarWithResults = document.querySelector('.pk_search .dd_container .indent');
var observerReference;

function outOfBox(event){
    const withinSearch = event.composedPath().includes(searchbarWithResults);
    if(withinSearch){
        window.addEventListener('mousedown', outOfBox);
    }
    else{
        searchbarObserver.disconnect();
        searchbarWithResults.querySelector('.pk_search #search_widget ~ ul').style.display = 'none';
        searchbarWithResults.querySelector('.recommendationsContainer');
        observer.observe(document.querySelector("#search_widget").parentElement, {
            subtree: true,
            attributes: true,
            childList: true
        });
    }
}

const searchbarObserver = new MutationObserver((mutations, observer) => {
    observer.disconnect();
    const defaultSearchResults = document.querySelector('.pk_search #search_widget ~ ul');
    const searchBar = document.querySelector('#search_widget #sisearch');
    if (defaultSearchResults) {
        const recommendationsContainer = document.querySelector('.recommendationsContainer');
        if (recommendationsContainer) {
                const recommendationsTitle = recommendationsContainer.querySelector('.recommendationsTitle');
                if (defaultSearchResults.style.display === "none") {
                    if (document.querySelector('.search-no-result.pk_search_result')) {
                        if (!recommendationsContainer.classList.contains('noResults'))
                            recommendationsContainer.classList.add('noResults');
                        recommendationsContainer.style.display = '';
                        recommendationsTitle.innerHTML = recommendationsTitle.getAttribute('withoutResults');
                    }
                    else {
                        if (recommendationsContainer.classList.contains('noResults'))
                            recommendationsContainer.classList.remove('noResults');
                        recommendationsContainer.style.display = '';
                        recommendationsTitle.innerHTML = recommendationsTitle.getAttribute('withResults');
                        defaultSearchResults.style.display = 'block';
                    }
                } else {
                    if (recommendationsContainer.classList.contains('noResults'))
                        recommendationsContainer.classList.remove('noResults');
                    recommendationsContainer.style.display = "";
                    recommendationsTitle.innerHTML = recommendationsTitle.getAttribute('withResults');
                }

                if(searchBar.value.length < 3 && !searchBar.activeElement){
                    if (recommendationsContainer.classList.contains('noResults'))
                        recommendationsContainer.classList.remove('noResults');
                    recommendationsContainer.style.display = 'none';
                    recommendationsTitle.innerHTML = recommendationsTitle.getAttribute('withResults');
                    defaultSearchResults.style.display = 'none';
                }
        } else {
            console.log('no container');
        }
    }
    observer.observe(document.querySelector("#search_widget").parentElement, {
        subtree: true,
        attributes: true,
        childList: true
    });
});

docReady(() => {
    const searchBar = document.querySelector("#sisearch");
    const searchContainer = document.querySelector("#search_widget").parentElement;
    if (searchBar && searchContainer && window.innerWidth > 1024) {
        var typingTimer;
        const doneTypingInterval = 500;
        searchBar.addEventListener('keyup', async event => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                handleKeyup(event)
            }, doneTypingInterval);
        });
        searchBar.addEventListener('keydown', () => {
            clearTimeout(typingTimer);
        });

        searchbarObserver.observe(searchContainer, {
            subtree: true,
            attributes: true,
            childList: true
        });
    }
});