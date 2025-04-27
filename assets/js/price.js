jQuery(document).ready(function () {
    const Selectors = {
        qtyInput: ".cart .quantity input.qty",
        priceContainer: ".justb2b_product",
        variationForm: "form.variations_form",
    };

    let isRequestInProgress = false;

    function getPriceContainer() {
        return jQuery(Selectors.priceContainer);
    }

    function setPriceContainerLoading(isLoading) {
        const $priceContainer = getPriceContainer();
        $priceContainer.css({
            opacity: isLoading ? "0.5" : "1",
            pointerEvents: isLoading ? "none" : "auto",
        });
    }

    function updatePrice() {
        if (isRequestInProgress) return;

        const $priceContainer = getPriceContainer();
        const $qtyInput = jQuery(Selectors.qtyInput);

        const quantity = $qtyInput.length ? parseInt($qtyInput.val(), 10) : 1;

        if (!$priceContainer.length || quantity < 1) return;

        const productId = $priceContainer.data("product_id");

        isRequestInProgress = true;
        setPriceContainerLoading(true);

        jQuery
            .ajax({
                url: justb2b.ajax_url,
                method: "POST",
                // credentials: 'include',
                // headers: {
                    // 'Content-Type': 'application/x-www-form-urlencoded',
                // },
                data: {
                    action: "justb2b_calculate_price",
                    nonce: justb2b.nonce,
                    product_id: productId,
                    qty: quantity,
                },
            })
            .done(function (response) {
                if (response.success) {
                    $priceContainer.html(response.data.price);
                } else {
                    $priceContainer.text("Error calculating price");
                }
            })
            .fail(function (xhr) {
                $priceContainer.text("Error calculating price");
                console.error("AJAX Error:", xhr.responseText);
            })
            .always(function () {
                isRequestInProgress = false;
                setPriceContainerLoading(false);
            });
    }

    function debounce(func, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    }

    const debouncedUpdatePrice = debounce(updatePrice, 700);

    function initializeEventListeners() {
        const $variationForm = jQuery(Selectors.variationForm);

        jQuery(document.body).on("input change", Selectors.qtyInput, debouncedUpdatePrice);

        if ($variationForm.length) {
            $variationForm.on("show_variation", updatePrice);
        } else {
            updatePrice();
        }
    }

    initializeEventListeners();
});