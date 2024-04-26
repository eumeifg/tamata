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
                type: 'bnpl',
                component: 'CAT_Bnpl/js/view/payment/method-renderer/bnpl-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
