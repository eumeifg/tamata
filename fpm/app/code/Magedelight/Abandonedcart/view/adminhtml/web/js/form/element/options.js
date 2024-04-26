define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'jquery'
], function (_, uiRegistry, select,jQuery) {

    'use strict';

    return select.extend({

        /**
         * Array of field names that depend on the value of
         * this UI component.
         */
        dependentFieldNames: [
            'coupon_amount',
            'amount_type'
        ],

        /**
         * Reference storage for dependent fields. We're caching this
         * because we don't want to query the UI registry so often.
         */
        dependentFields : [],

        /**
         * Initialize field component, and store a reference to the dependent fields.
         */
        initialize: function () {
            this._super();
                // Set the initial visibility of our fields.
                this.processDependentFieldVisibility(parseInt(this.initialValue));
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            // We're calling parseInt, because in JS "0" evaluates to True
            this.processDependentFieldVisibility(parseInt(value));
            return this._super();
        },

        /**
         * Shows or hides dependent fields.
         *
         * @param visibility
         */
        processDependentFieldVisibility: function (visibility) {
            //console.log(visibility);
            var field1 = uiRegistry.get('index = coupon_amount');
            var field2 = uiRegistry.get('index = amount_type');
            if (visibility==1) {
                field1.show();
                field2.show();
            } else {
                field1.hide();
                field2.hide();
            }
        }
    });
});

