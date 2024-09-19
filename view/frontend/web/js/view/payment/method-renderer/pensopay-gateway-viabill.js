define(
    [
        'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway',
        'jquery'
    ],
    function (Component, $) {
        'use strict';
        return Component.extend({
            getCode: function () {
                return 'pensopay_gateway_viabill';
            },
            getPaymentMethodExtra: function () {
                return $('.checkout-viabill-logos').html() + $('.checkout-viabill').html();
            }
        });
    }
);
