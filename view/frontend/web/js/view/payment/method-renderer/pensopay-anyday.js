define(
    [
        'PensoPay_Gateway/js/view/payment/method-renderer/pensopay',
        'jquery'
    ],
    function (Component, $) {
        'use strict';
        return Component.extend({
            getCode: function() {
                return 'pensopay_anyday';
            },
            getPaymentMethodExtra: function() {
                return $('.checkout-anyday-logos').html();
            }
        });
    }
);