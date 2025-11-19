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
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-viabill'
            },
            {
                type: 'pensopay_gateway_anyday',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-anyday'
            },
            {
                type: 'pensopay_gateway_mobilepay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-mobilepay'
            },
            {
                type: 'pensopay_gateway_applepay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-applepay'
            },
            {
                type: 'pensopay_gateway_googlepay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-googlepay'
            },
            {
                type: 'pensopay_gateway_swish',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-swish'
            },
            {
                type: 'pensopay_gateway_stripeklarna',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripeklarna'
            },
            {
                type: 'pensopay_gateway_stripeideal',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripeideal'
            },
            {
                type: 'pensopay_gateway_vippspsp',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-vippspsp'
            }
        );
        return Component.extend({});
    }
);
