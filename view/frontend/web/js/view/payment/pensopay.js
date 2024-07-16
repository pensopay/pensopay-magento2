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
                type: 'pensopay_anyday',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-anyday'
            },
            {
                type: 'pensopay_mobilepay',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-mobilepay'
            },
            {
                type: 'pensopay_applepay',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-applepay'
            },
            {
                type: 'pensopay_swish',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-swish'
            },
            {
                type: 'pensopay_klarna',
                component: 'PensoPay_Gateway/js/view/payment/method-renderer/pensopay-klarna'
            }
        );
        return Component.extend({});
    }
);