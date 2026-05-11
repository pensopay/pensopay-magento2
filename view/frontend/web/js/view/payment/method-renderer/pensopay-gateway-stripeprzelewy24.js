define(
    [
        'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway',
        'jquery'
    ],
    function (Component, $) {
        'use strict';
        return Component.extend({
            getCode: function () {
                return 'pensopay_gateway_stripeprzelewy24';
            },
            getPaymentMethodExtra: function () {
                return $('.checkout-stripeprzelewy24-logos').html();
            }
        });
    }
);
