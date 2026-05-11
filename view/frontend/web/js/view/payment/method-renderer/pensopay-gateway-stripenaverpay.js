define(
    [
        'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway',
        'jquery'
    ],
    function (Component, $) {
        'use strict';
        return Component.extend({
            getCode: function () {
                return 'pensopay_gateway_stripenaverpay';
            },
            getPaymentMethodExtra: function () {
                return $('.checkout-stripenaverpay-logos').html();
            }
        });
    }
);
