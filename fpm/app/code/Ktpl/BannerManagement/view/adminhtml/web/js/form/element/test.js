define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {

    'use strict';

    return select.extend({

        /**
         * Array of field names that depend on the value of 
         * this UI component.
         */
        dependentFieldNames: [
            'my_field_name1',
            'my_field_name2'
        ],

        /**
         * Reference storage for dependent fields. We're caching this
         * because we don't want to query the UI registry so often.
         */
        dependentFields : [],

        /**
         * Initialize field component, and store a reference to the dependent fields.
         */
        initialize: function() {
            this._super();

            // We're creating a promise that resolves when we're sure that all our dependent
            // UI components have been loaded. We're also binding our callback because
            // we're making use of `this`
            uiRegistry.promise(this.dependentFieldNames).done(_.bind(function() {

                // Let's store the arguments (the UI Components we queried for) in our object
                this.dependentFields = arguments;

                // Set the initial visibility of our fields.
                this.processDependentFieldVisibility(parseInt(this.initialValue));
            }, this));
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
            var method = 'hide';
            if (visibility) {
                method = 'show';
            }

            // Underscore's invoke, calls the passed method on all the objects in our array
            _.invoke(this.dependentFields, method);
        }
    });
});