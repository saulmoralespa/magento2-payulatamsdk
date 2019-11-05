define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'payulatamsdk_cards',
                component: 'Saulmoralespa_PayuLatamSDK/js/view/payment/method-renderer/payulatamsdk-cards'
            }
        );
        return Component.extend({});
    }
);