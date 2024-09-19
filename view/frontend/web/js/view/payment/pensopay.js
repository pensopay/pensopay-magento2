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
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay'
            },
            {
                type: 'pensopay_viabill',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-viabill'
            },
            {
                type: 'pensopay_anyday',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-anyday'
            },
            {
                type: 'pensopay_mobilepay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-mobilepay'
            },
            {
                type: 'pensopay_applepay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-applepay'
            },
            {
                type: 'pensopay_swish',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-swish'
            },
            {
                type: 'pensopay_klarna',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-klarna'
            }
        );
        return Component.extend({});
    }
);
