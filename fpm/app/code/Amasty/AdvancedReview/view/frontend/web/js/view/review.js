define([
    'jquery'
], function ($) {
    'use strict';

    var mixin = {
        initialize: function () {
            $('[data-role="product-review-form"]').on('submit', function () {
                if ($(this).find('.mage-error:visible').length == 0) {
                    $('[data-role="product-review-form"] .action.submit').prop('disabled', true);
                }
            });

            return this._super();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
