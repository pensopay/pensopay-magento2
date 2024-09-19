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
                type: 'pensopay_gateway',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway'
            },
            {
                type: 'pensopay_gateway_viabill',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-viabill.js'
            },
            {
                type: 'pensopay_gateway_anyday',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-anyday.js'
            },
            {
                type: 'pensopay_gateway_mobilepay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-mobilepay.js'
            },
            {
                type: 'pensopay_gateway_applepay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-applepay.js'
            },
            {
                type: 'pensopay_gateway_swish',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-swish.js'
            },
            {
                type: 'pensopay_gateway_klarna',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-klarna.js'
            }
        );
        return Component.extend({});
    }
);
