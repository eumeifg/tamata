/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator'
], function ($, _, ko, Component, stepNavigator) {
    'use strict';

    var steps = stepNavigator.steps;

    return Component.extend({
        defaults: {
            template: 'Ktpl_CheckoutAccordion/view/step-title',
            code: null
        },
        steps: steps,

        initialize: function () {
            this._super();
        },

        getStep: function() {
            var code = this.code;

            var match = ko.utils.arrayFirst(this.steps(), function(item) {
                return item.code == code;
            });

            if(match)
                return match;

            return stepNavigator.steps()[0];
        },

        navigateTo: function() {
            stepNavigator.navigateTo(this.getStep().code);
        },

        isProcessed: function () {
            return stepNavigator.isProcessed(this.getStep().code);
        }
    });
});
