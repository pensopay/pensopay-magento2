define(
    [
        'PensoPay_Gateway/js/view/payment/method-renderer/pensopay',
        'jquery'
    ],
    function (Component, $) {
        'use strict';
        return Component.extend({
            getCode: function() {
                return 'pensopay_applepay';
            },
            getPaymentMethodExtra: function() {
                return $('.checkout-applepay-logos').html();
            }
        });
    }
);