/**
 * Copyright 2020 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'uiElement'
], function (Element) {
    'use strict';

    return Element.extend({

        /**
         * Ment to preprocess data associated with a current columns' field
         *
         * @param {Object} record - Data to be preprocessed
         * @returns {String}
         */
        getLabel: function (record) {
            return record[this.index];
        }
    });
});
