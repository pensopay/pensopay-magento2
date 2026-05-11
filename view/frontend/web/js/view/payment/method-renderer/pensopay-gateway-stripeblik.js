define(
    [
        'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway',
        'jquery'
    ],
    function (Component, $) {
        'use strict';
        return Component.extend({
            getCode: function () {
                return 'pensopay_gateway_stripeblik';
            },
            getPaymentMethodExtra: function () {
                return $('.checkout-stripeblik-logos').html();
            }
        });
    }
);
