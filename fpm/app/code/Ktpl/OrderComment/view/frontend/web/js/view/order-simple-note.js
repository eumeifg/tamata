define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Ktpl_OrderComment/js/action/save-order-simple-note',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, ko, Component, saveOrderComment, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ktpl_OrderComment/order_simple_note',
                orderComment: ''
            },

            initObservable: function () {
                return this._super().observe(['orderComment'])
            },

            saveOrderComment: function () {
                var orderComment = this.orderComment();
                saveOrderComment.save(orderComment);
            }
        });
    }
);