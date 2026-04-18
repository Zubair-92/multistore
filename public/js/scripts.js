/*!
* Start Bootstrap - Shop Homepage v5.0.6 (https://startbootstrap.com/template/shop-homepage)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-shop-homepage/blob/master/LICENSE)
*/
// This file is intentionally blank
// Use this file to add JavaScript to your project

$(document).ready(function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    function handleExpiredSession(xhr) {
        if (xhr.status === 419) {
            alert('Your session expired. The page will refresh now.');
            window.location.reload();
            return true;
        }

        return false;
    }

    function renderCheckoutMessage(type, message) {
        $('.checkout-container').html(
            `<div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`
        );
    }

    //add to cart
    $('.add-to-cart-btn').click(function(e) {
        e.preventDefault();

        let product_id = $(this).data('product-id');
        //let url = window.Laravel.routes.cartAdd.replace('ID_PLACEHOLDER', product_id);
        let url = window.Laravel.routes.cartAdd;

        $.ajax({
            url: url,
            type: "POST",
            data: {
                product_id: product_id,
                quantity: 1,
                _token: window.Laravel.csrfToken
            },
            success: function(res) {
                console.log(res);
                $('#cart-count').text(res.cart_count);
            },
            error: function(xhr) {
                if (!handleExpiredSession(xhr)) {
                    alert('Unable to add item to cart right now.');
                }
            }
        });

    });
    //remove from cart
    $('.rmv-fm-c').click(function(e) {
        e.preventDefault();

        let product_id = $(this).data('pro-id');
        let url = window.Laravel.routes.cartRemove.replace('ID_PLACEHOLDER', product_id);

        $.ajax({
            url: url,
            type: "POST",
            data: {
                product_id: product_id,
                _token: window.Laravel.csrfToken
            },
            success: function(res) {
                //console.log(res);
                //return;
                $('#cart-count').text(res.cart_count);
                // Replace the cart items area
                $('#cart-items-wrapper').html(res.html);
            },
            error: function(xhr) {
                if (!handleExpiredSession(xhr)) {
                    alert('Unable to remove the item right now.');
                }
            }
        });

    });
    //update quantity of product from cart
    $('.upt-qty-pro-frm-c').on('change', function(e) {
        e.preventDefault();

        let product_id = $(this).data('pro-id');
        let quantity = $(this).val(); // always the new input value
        let url = window.Laravel.routes.cartUpdate.replace('ID_PLACEHOLDER', product_id);

        $.ajax({
            url: url,
            type: "POST",
            data: {
                id: product_id,
                qty: quantity,
                _token: window.Laravel.csrfToken
            },
            success: function(res) {
                //console.log(res);
                //return;
                $('#cart-count').text(res.cart_count);
                // Replace the cart items area
                $('#cart-items-wrapper').html(res.html);
            },
            error: function(xhr) {
                if (!handleExpiredSession(xhr)) {
                    alert('Unable to update the cart right now.');
                }
            }
        });

    });

    //checkout
    $('#payment_method').on('change', function() {
        const isBankTransfer = $(this).val() === 'bank_transfer';
        const isDemoCard = $(this).val() === 'demo_card';
        $('#transaction-reference-group').toggleClass('d-none', !isBankTransfer);
        $('#transaction-reference-group input').prop('required', isBankTransfer);
        $('#demo-card-group').toggleClass('d-none', !isDemoCard);
        $('#demo-card-group .demo-card-input').prop('required', isDemoCard);
    }).trigger('change');

    $('.checkout').on('click', function(e) {
        e.preventDefault();

        let url = window.Laravel.routes.checkout;
        let form = $('#checkout-form');
        let payload = form.serialize();

        $.ajax({
            url: url,
            type: "POST",
            data: payload,
            success: function(res) {

                if (res.success) {
                    renderCheckoutMessage('success', `<strong>Success!</strong> ${res.message}`);

                    // Redirect after 1.5 seconds
                    setTimeout(function() {
                        window.location.href = res.redirect;
                    }, 1500);
                }
            },

            error: function(xhr) {
                if (!handleExpiredSession(xhr)) {
                    const message = xhr.responseJSON?.message || 'Something went wrong while placing the order.';
                    renderCheckoutMessage('danger', message);
                }
            }
        });

    });

});
