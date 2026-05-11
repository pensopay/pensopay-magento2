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
            },
            {
                type: 'pensopay_gateway_paypal',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-paypal'
            },
            {
                type: 'pensopay_gateway_klarna',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-klarna'
            },
            {
                type: 'pensopay_gateway_stripepayco',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripepayco'
            },
            {
                type: 'pensopay_gateway_stripekakaopay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripekakaopay'
            },
            {
                type: 'pensopay_gateway_stripenaverpay',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripenaverpay'
            },
            {
                type: 'pensopay_gateway_stripebillie',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripebillie'
            },
            {
                type: 'pensopay_gateway_stripetwint',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripetwint'
            },
            {
                type: 'pensopay_gateway_stripebancontact',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripebancontact'
            },
            {
                type: 'pensopay_gateway_stripeprzelewy24',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripeprzelewy24'
            },
            {
                type: 'pensopay_gateway_stripeblik',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripeblik'
            },
            {
                type: 'pensopay_gateway_stripesepa',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripesepa'
            },
            {
                type: 'pensopay_gateway_stripembway',
                component: 'Pensopay_Gateway/js/view/payment/method-renderer/pensopay-gateway-stripembway'
            },
        );
        return Component.extend({});
    }
);
