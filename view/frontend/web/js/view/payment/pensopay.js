define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'pensopay',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay'
            },
            {
                type: 'pensopay_viabill',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-viabill'
            },
            {
                type: 'pensopay_expressbank',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-expressbank'
            },
            {
                type: 'pensopay_paypal',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-paypal'
            },
            {
                type: 'pensopay_anyday',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-anyday'
            }
        );
        return Component.extend({});
    }
);