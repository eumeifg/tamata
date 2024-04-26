/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Ktpl_OrderComment/js/model/payment/order-comment-validator'
], function (Component, additionalValidators, commentValidator) {
    'use strict';

    additionalValidators.registerValidator(commentValidator);
    return Component.extend({});
});