document.addEventListener("DOMContentLoaded", function () { 
    let wasAjaxPrice = false;
    let wasAjaxRocznik = false;

    const marka = document.getElementById('marka');
    const model = document.getElementById('model');
    const kolor = document.getElementById('kolor');
    const type  = document.getElementById('type');
    
    paginationWrapper();

    let lastSelectedValues = {
        marka: "",
        kolor: "",
        type: "",
        priceMin: "",
        priceMax: "",
        rocznikMin: "",
        rocznikMax: "",
    };
    let FilteredValuesinPanel = null;
    function settingFilteredValuesinPanel() {
        if (FilteredValuesinPanel !== null) {
            console.log('aborted');
            FilteredValuesinPanel.abort();
        }
        let lastSentValues = {
            marka: "",
            kolor: "",
            type: "",
            priceMin: sessionStorage.getItem('priceMinValue') || "",
            priceMax: sessionStorage.getItem('priceMaxValue') || "",
            rocznikMin: sessionStorage.getItem('rocznikMinValue') || "",
            rocznikMax: sessionStorage.getItem('rocznikMaxValue') || "",
        };
    
        let selectedValues = {
            marka: marka.value,
            priceMin: sessionStorage.getItem('priceMinValue') || "",
            priceMax: sessionStorage.getItem('priceMaxValue') || "",
            rocznikMin: sessionStorage.getItem('rocznikMinValue') || "",
            rocznikMax: sessionStorage.getItem('rocznikMaxValue') || "",
            product_type: type.value,
            product_color: kolor.value,
        };
            console.log('selected',selectedValues.marka);
            console.log('last',lastSentValues.marka);

        if (selectedValues.marka === lastSentValues.marka &&
            selectedValues.priceMin === lastSentValues.priceMin &&
            selectedValues.priceMax === lastSentValues.priceMax &&
            selectedValues.rocznikMin === lastSentValues.rocznikMin &&
            selectedValues.rocznikMax === lastSentValues.rocznikMax &&
            selectedValues.product_type === lastSentValues.product_type &&
            selectedValues.product_color === lastSentValues.product_color) {
            console.log("No changes detected, skipping AJAX.");
            return;
        }
    
        console.log("Changes detected, sending AJAX.");
        jQuery.ajax({
            url: psCodesAjax.ajaxurl,
            type: 'post',
            data: {
                action: 'filter_callback',
                nonce: psCodesAjax.nonce,
                ...selectedValues
            },
   
            success: function (response) {
                //console.log("AJAX Response:", response); 
                if (response.success) {
                    let data = response.data;
                    //console.log('data',data);

                    if(data.markas) {
                        let markaSelect = jQuery('marka');
                        markaSelect.html('<option value="">Marka</option>');
                        jQuery.each(data.markas, function (name, id) {
                            markaSelect.append('<option value="' + name + '" data-id="' + id + '">' + name + '</option>');
                            
                        });
                    }
                    if (data.models) {
                        let modelSelect = jQuery('#model');
                        modelSelect.html('<option value="">Model</option>');
                        
                        jQuery.each(data.models, function (name, id) {
                            modelSelect.append('<option value="' + name + '" data-id="' + id.term_id + '">' + name + '</option>');
                        });
                    }

                    if (data.price) {
                        wasAjaxPrice = true;
                        jQuery('#price-block').html(data.price);
                    }
                                        
                    if (data.rocznik) {
                        wasAjaxRocznik = true;
                        jQuery('#rocznik-block').html(data.rocznik);
                    }
                    
                    if(data.colors) {
                        let colorSelect = jQuery('#kolor');
                        colorSelect.html('<option value="">Kolor</option>');
                        jQuery.each(data.colors, function (index, color) {
                            console.log('data.colors', color); 
                            colorSelect.append('<option value="' + color.name + '" data-id="' + color.term_id + '">' + color.name + '</option>');
                        });
                    }
                
                    if(data.types) {
                        let typeSelect = jQuery('#type');
                        typeSelect.html('<option value="">Nadwozie</option>');
                        jQuery.each(data.types, function (index, type) {
                            console.log('data.type', type); 
                            typeSelect.append('<option value="' + type.name + '" data-id="' + type.term_id + '">' + type.name + '</option>');
                        });
                    }

                    if (wasAjaxPrice) {

                        createSlider({
                            strapSelector: ".strap-price",
                            part1Selector: ".part-price-1",
                            part2Selector: ".part-price-2",
                            minBannerSelector: ".minimum_price",
                            maxBannerSelector: ".maximum_price",
                            minDataValue: document.querySelector(".minimum_price").getAttribute('data-value'),
                            maxDataValue: document.querySelector(".maximum_price").getAttribute('data-value'),
                            dataType: 'price'
                        });
                    }
                    if(wasAjaxRocznik) {
                        createSlider({
                            strapSelector: ".strap-rocznik",
                            part1Selector: ".part-rocznik-1",
                            part2Selector: ".part-rocznik-2",
                            minBannerSelector: ".minimum_rocznik",
                            maxBannerSelector: ".maximum_rocznik",
                            minDataValue: parseInt(document.querySelector(".minimum_rocznik").getAttribute('data-value')),
                            maxDataValue: parseInt(document.querySelector(".maximum_rocznik").getAttribute('data-value')),
                            dataType: 'rocznik'
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }

    let isHandleRequestInProgress = false;
    function handleSelectChange(event) {
        const { id, value } = event.target;

        if (lastSelectedValues[id] !== value) {
            lastSelectedValues[id] = value;
            console.log(`Changed: ${id}, Selected Value: ${value}`);

            sessionStorage.setItem(`${id}Value`,value)
            
            settingFilteredValuesinPanel();

            const selectElement = document.getElementById(id);
            const options = selectElement.querySelectorAll('option');
            options.forEach(option => {
                option.removeAttribute('selected');
            });
    
            if (value) {
                const selectedOption = Array.from(options).find(option => option.value === value);
                if (selectedOption) {
                    selectedOption.setAttribute('selected', 'selected');
                }
            }

            if (!isHandleRequestInProgress) {
                isHandleRequestInProgress = true;

            sendValueToAjax();

        }
        }
        wasAjaxPrice = false;
        wasAjaxRocznik = false;
    }

    function createSlider({
        strapSelector,
        part1Selector,
        part2Selector,
        minBannerSelector,
        maxBannerSelector,
        minDataValue,
        maxDataValue,
        dataType
    }) {
        function isPriceFormatted(price) {
            return /\d \d/.test(price);
        }

        function formatNumberWithSpaces(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }


        var strap = document.querySelector(strapSelector);
        var part1 = document.querySelector(part1Selector);
        var part2 = document.querySelector(part2Selector);
        var minBanner = document.querySelector(minBannerSelector);
        var maxBanner = document.querySelector(maxBannerSelector);

        marka.addEventListener("change", handleSelectChange);
        model.addEventListener("change", handleSelectChange);
        kolor.addEventListener("change", handleSelectChange);
        type.addEventListener("change", handleSelectChange);


        var isDragging1 = false;
        var isDragging2 = false;
        var offsetX1 = 0;
        var offsetX2 = 0;

        let formattedMinPrice = minDataValue;
        let formattedMaxPrice = maxDataValue;

        if (!isPriceFormatted(minDataValue)) {
            formattedMinPrice = formatNumberWithSpaces(parseFloat(minDataValue));
        }
        if (!isPriceFormatted(maxDataValue)) {
            formattedMaxPrice = formatNumberWithSpaces(parseFloat(maxDataValue));
        }
        
        var minText = formattedMinPrice;
        var maxText = formattedMaxPrice;

        var minPriceParagraph = document.createElement("p");
        var maxPriceParagraph = document.createElement("p");

        console.log('minText before formatting:', minText);
        console.log('maxText before formatting:', maxText);

        minPriceParagraph.textContent = minText.toLocaleString().replace(/,/g, ' ');
        maxPriceParagraph.textContent = maxText.toLocaleString().replace(/,/g, ' ');

        minBanner.appendChild(minPriceParagraph);
        maxBanner.appendChild(maxPriceParagraph);

        const options = { passive: false };

        part1.addEventListener("touchstart", startDragging, options);
        part2.addEventListener("touchstart", startDragging, options);
        part1.addEventListener("mousedown", startDragging, options);
        part2.addEventListener("mousedown", startDragging, options);

        document.addEventListener("mousemove", moveSlider);
        document.addEventListener("touchmove", moveSlider);

        document.addEventListener("mouseup", stopDragging);
        document.addEventListener("touchend", stopDragging);

        function startDragging(e) {
            if (e.button === 0 || e.type === "touchstart") {    
                e.preventDefault();
                if (e.target === part1) {
                    isDragging1 = true;
                    offsetX1 = e.type === "mousedown" ? e.clientX - part1.getBoundingClientRect().left : e.touches[0].clientX - part1.getBoundingClientRect().left;
                } else if (e.target === part2) {
                    isDragging2 = true;
                    offsetX2 = e.type === "mousedown" ? e.clientX - part2.getBoundingClientRect().left : e.touches[0].clientX - part2.getBoundingClientRect().left;
                }
            }
        }

        function moveSlider(e) {
            if (isDragging1 || isDragging2) {
                console.log('was clicked)')
                e.preventDefault();
                if (isDragging1) {
                    var newX1 = e.type === "mousemove" ? e.clientX - strap.getBoundingClientRect().left - offsetX1 : e.touches[0].clientX - strap.getBoundingClientRect().left - offsetX1;
                    var maxX1 = part2.offsetLeft - part1.offsetWidth;
                    newX1 = Math.max(0, Math.min(newX1, maxX1));
                    part1.style.left = newX1 + "px";
                    minBanner.style.left = newX1 + "px";
                }

                if (isDragging2) {
                    var newX2 = e.type === "mousemove" ? e.clientX - strap.getBoundingClientRect().left - offsetX2 : e.touches[0].clientX - strap.getBoundingClientRect().left - offsetX2;
                    var minX2 = part1.offsetLeft + part1.offsetWidth;
                    var maxX2 = strap.offsetWidth - part2.offsetWidth;
                    newX2 = Math.max(minX2, Math.min(newX2, maxX2));
                    part2.style.left = newX2 + "px";
                    maxBanner.style.left = (newX2 - 30) + "px";
                }

                updatePriceValues();
                updateStrapColor();
            }
        }

        function stopDragging() {
            isDragging1 = false;
            isDragging2 = false;
            
            sendValueToAjax();
        }

        function updatePriceValues() {
            var strapWidth = strap.offsetWidth;
            var part1Left  = part1.offsetLeft;
            var part2Left  = part2.offsetLeft + 20;

            if (dataType === "rocznik") {
                var maxPrice = parseFloat(maxText.replace(/\s/g, ''));
                var minPrice = parseFloat(minText.replace(/\s/g, ''));
            
    
                var newMinPrice = (part1Left / strapWidth) * (maxPrice - minPrice) + minPrice;
                var maxPriceRange = maxPrice - minPrice;
    
                var remainingPriceRange = ((strapWidth - part2Left) / strapWidth) * maxPriceRange;
                var newMaxPrice = maxPrice - remainingPriceRange;

                newMinPrice = Math.round(newMinPrice);
                newMaxPrice = Math.round(newMaxPrice);

            } else {

                var maxPrice = parseFloat(maxText.replace(/\s/g, ''));
                var minPrice = parseFloat(minText.replace(/\s/g, ''));
            
    
                var newMinPrice = (part1Left / strapWidth) * (maxPrice - minPrice) + minPrice;
                var maxPriceRange = maxPrice - minPrice;
    
                var remainingPriceRange = ((strapWidth - part2Left) / strapWidth) * maxPriceRange;
                var newMaxPrice = maxPrice - remainingPriceRange;
                newMinPrice = Math.round(newMinPrice / 1000) * 1000;
                newMaxPrice = Math.round(newMaxPrice / 1000) * 1000;
            }

            minPriceParagraph.textContent = newMinPrice.toLocaleString().replace(/,/g, ' ');
            maxPriceParagraph.textContent = newMaxPrice.toLocaleString().replace(/,/g, ' ');

            updateAttribute(minBanner, 'data-value', newMinPrice);
            updateAttribute(maxBanner, 'data-value', newMaxPrice);

            sessionStorage.setItem(dataType + 'MinValue', newMinPrice);
            sessionStorage.setItem(dataType + 'MaxValue', newMaxPrice);
        }

        function updateStrapColor() {
            var strapWidth = strap.offsetWidth;
            var part1Left = part1.offsetLeft;
            var part2Left = part2.offsetLeft;
            var part2Width = part2.offsetWidth;
            var greenWidth2 = strapWidth - (part2Left + part2Width);

            strap.style.background = `linear-gradient(90deg, #F21109 ${part1Left}px, transparent ${part1Left}px, transparent ${part2Left}px, #F3F3F3 ${part2Left}px, #F3F3F3 ${part2Left - 40}px, transparent ${part2Left - 40}px, transparent ${strapWidth - greenWidth2}px, #F21109 ${strapWidth - greenWidth2}px)`;
        }

        function updateAttribute(element, attributeName, attributeValue) {
            element.setAttribute(attributeName, attributeValue);
        }
    }

    createSlider({
        strapSelector: ".strap-price",
        part1Selector: ".part-price-1",
        part2Selector: ".part-price-2",
        minBannerSelector: ".minimum_price",
        maxBannerSelector: ".maximum_price",
        minDataValue: document.querySelector(".minimum_price").getAttribute('data-value'),
        maxDataValue: document.querySelector(".maximum_price").getAttribute('data-value'),
        dataType: 'price'
    });

    createSlider({
        strapSelector: ".strap-rocznik",
        part1Selector: ".part-rocznik-1",
        part2Selector: ".part-rocznik-2",
        minBannerSelector: ".minimum_rocznik",
        maxBannerSelector: ".maximum_rocznik",
        minDataValue: parseInt(document.querySelector(".minimum_rocznik").getAttribute('data-value')),
        maxDataValue: parseInt(document.querySelector(".maximum_rocznik").getAttribute('data-value')),
        dataType: 'rocznik'
    });
});



let currentValueToAjax = null;
function sendValueToAjax() {


    if (currentValueToAjax !== null) {
        currentValueToAjax.abort();
    }

    console.log('sendValueToAjax triggered');

    let lastSentValues = {
        marka: "",
        product_type: "",
        product_color: "",
        priceMin: "",
        priceMax: "",
        rocznikMin: "",
        rocznikMax: "",
        paged: ""
    };

    let selectedValues = {
        marka: sessionStorage.getItem('markaValue') || "",
        product_type: sessionStorage.getItem('typeValue') || "",
        product_color: sessionStorage.getItem('kolorValue') || "",
        priceMin: sessionStorage.getItem('priceMinValue') || "",
        priceMax: sessionStorage.getItem('priceMaxValue') || "",
        rocznikMin: sessionStorage.getItem('rocznikMinValue') || "",
        rocznikMax: sessionStorage.getItem('rocznikMaxValue') || "",
        paged: sessionStorage.getItem('pageNumber') || ""
    };

    console.log('selected', selectedValues);
    console.log('last', lastSentValues);

    const valuesAreEqual = Object.keys(selectedValues).every(key => selectedValues[key] === lastSentValues[key]);

    if (valuesAreEqual || (selectedValues.marka === '' && selectedValues.product_type === '' && selectedValues.product_color === '' && selectedValues.priceMin === '' && selectedValues.priceMax === '' && selectedValues.rocznikMin === '' && selectedValues.rocznikMax === '' && selectedValues.paged === '')) {
        console.log("No changes detected, skipping AJAX.");
        return;
    }

    console.log('selected for ajax', selectedValues);

    currentValueToAjax = jQuery.ajax({
    url: psCodesAjax.ajaxurl,
    type: 'post',
    data: {
        action: 'auto_callback',
        nonce: psCodesAjax.nonce,
        ...selectedValues,
    },
    success: function(response) {
        console.log('response', response);

        sessionStorage.setItem('markaValue', selectedValues.marka);
        sessionStorage.setItem('typeValue', selectedValues.product_type);
        sessionStorage.setItem('kolorValue', selectedValues.product_color);
        sessionStorage.setItem('priceMinValue', selectedValues.priceMin);
        sessionStorage.setItem('priceMaxValue', selectedValues.priceMax);
        sessionStorage.setItem('rocznikMinValue', selectedValues.rocznikMin);
        sessionStorage.setItem('rocznikMaxValue', selectedValues.rocznikMax);
        sessionStorage.setItem('pageNumber', selectedValues.paged);

        const pagination_target = document.querySelector('.pagination-frame');
        const target = document.querySelector('#initial');
        
        if (target) {
            target.innerHTML = response.data.auto_product;
        }
        
        if (pagination_target) {
            pagination_target.innerHTML = response.data.auto_pagination;
        }
        paginationWrapper();
    }
});
}

function paginationWrapper() {
    const paginationAnchor = document.querySelector('.pagination-frame');
    //console.log(paginationAnchor );
    if(paginationAnchor ) {
        const paginationContainer = paginationAnchor.querySelector('.custom-pagination');

        //console.log(paginationContainer );
        if(paginationContainer ) {

            const pageBtns = paginationContainer.querySelectorAll('.page-item');// 

            pageBtns.forEach((button)=>{
                button.addEventListener('click', ()=> {
                    sessionStorage.removeItem('pageNumber');
                    paginationFn(button);
                })
            });
    
            const savedPageNumber = sessionStorage.getItem('pageNumber');
            if (savedPageNumber) {
                const pageButton = paginationContainer.querySelector(`.page-link[data-page="${savedPageNumber}"]`);
                if (pageButton) {
                    paginationFn(pageButton);
                }
            }

        }
    }
}

function paginationFn(clickedButton) {
    let currentPage = document.querySelector('.custom-pagination .page-item.active .page-link');
    if (currentPage) {
        currentPage.classList.remove('current');
        currentPage.closest('.page-item').classList.remove('active');
    }
    clickedButton.querySelector('.page-link').classList.add('current');
    clickedButton.closest('.page-item').classList.add('active');

    let currentPageNumber = pageNumber(clickedButton);
    
    console.log("Current Page Number:", currentPageNumber);

    sessionStorage.setItem('pageNumber', currentPageNumber);
    sendValueToAjax();
}

function pageNumber(clickedButton) {

    const url = clickedButton.querySelector('.page-link').getAttribute('data-page');
    if (url) {
        const match = url.match(/\/(\d+)$/);
        if (match) {
            return match[1];
        }
    }

    console.log("Defaulting to page 1");
    return 1;
}

