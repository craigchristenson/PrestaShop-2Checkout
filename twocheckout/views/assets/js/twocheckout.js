window.addEventListener('load', function () {
    let jsPaymentClient = new TwoPayClient(sellerId),
        style = $('#tco-payment-form').data('json'),
        component = jsPaymentClient.components.create('card', style);

    component.mount('#card-element');

    $('body').on('click', '.ps-shown-by-js', function (e) {
        if ($('#conditions-to-approve').serialize() === '') {
            $('#placeOrderTco').attr('disabled', 'disabled');
        } else {
            $('#placeOrderTco').attr('disabled', false);
        }
    });


    $('body').on('click', '#placeOrderTco', function (e) {
        e.preventDefault();
        if ($('.tco-error').length) {
            $('.tco-error').remove();
        }
        let billingDetails,
            selectedBillingAddressId = $('input[name=\'id_address_delivery\']:checked').val(),
            form = $('#tco-payment-form');

        if (prestashop.customer.hasOwnProperty('addresses') &&
            selectedBillingAddressId &&
            prestashop.customer.addresses[selectedBillingAddressId]) {
            billingDetails = {
                name: prestashop.customer.addresses[selectedBillingAddressId].firstname + ' ' +
                    prestashop.customer.addresses[selectedBillingAddressId].lastname
            };
        } else {
            billingDetails = {name: prestashop.customer.firstname + ' ' + prestashop.customer.lastname};
        }
        if (billingDetails.name.length < 3) {
            $('.validation-message').show();
            $('#tco_name').addClass('tco-error');
        } else {

            $('#placeOrderTco').attr('disabled', 'disabled');
            $('#tcoWait').show();

            jsPaymentClient.tokens.generate(component, billingDetails).then(function (response) {
                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: {ess_token: response.token}
                }).done(function (response) {
                    let result = JSON.parse(response);
                    if (result.status && result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        $('#tcoWait').hide();
                        $('#placeOrderTco').attr('disabled', false);
                        $('#tcoApiForm').prepend('<div class="tco-error">' + result.error + '</div>');
                    }
                }).error(function (response) {
                    alert('Your payment could not be processed. Please refresh the page and try again!');
                    console.error(response);
                });
            }).catch(function (error) {
                alert(error);
                console.error(error);
                $('#placeOrderTco').attr('disabled', false);
                $('#tcoWait').hide();
            });
        }
    });

});
