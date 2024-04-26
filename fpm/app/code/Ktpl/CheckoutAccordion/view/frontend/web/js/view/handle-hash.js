/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator'
], function ($, ko, Component, stepNavigator) {
    'use strict';

    return Component.extend({

        initialize: function () {
            var stepsValue;

            this._super();
            window.addEventListener('hashchange', _.bind(stepNavigator.handleHash, stepNavigator));

            if (!window.location.hash) {
                stepsValue = stepNavigator.steps();

                if (stepsValue.length) {
                    stepNavigator.setHash(stepsValue.sort(stepNavigator.sortItems)[0].code);
                }
            }

            stepNavigator.handleHash();
        }
    });
});
