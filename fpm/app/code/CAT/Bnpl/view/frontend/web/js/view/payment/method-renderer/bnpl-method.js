define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'CAT_Bnpl/payment/bnpl'
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.bnpl.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
            /** Returns payable to info */
            /*getPayableTo: function() {
            return window.checkoutConfig.payment.checkmo.payableTo;
            }*/
        });
    }
);
