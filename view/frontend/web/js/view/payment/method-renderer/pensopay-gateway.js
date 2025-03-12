define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Pensopay_Gateway/js/action/redirect-on-success',
        'jquery'
    ],
    function (Component, pensopayRedirect, $) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Pensopay_Gateway/payment/form',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,

            /**
             * @return {exports}
             */
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },

            /**
             * @return {*}
             */
            isPaymentReady: function () {
                return this.paymentReady();
            },

            getCode: function () {
                return 'pensopay_gateway';
            },
            getData: function () {
                return {
                    'method': this.item.method,
                };
            },
            afterPlaceOrder: function () {
                pensopayRedirect.execute(this.getCode());
            },
            getPaymentMethodExtra: function () {
                return $('.checkout-pensopaygw-logos').html();
            }
        });
    }
);
