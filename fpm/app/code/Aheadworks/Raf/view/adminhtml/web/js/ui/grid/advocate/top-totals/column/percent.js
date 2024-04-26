/**
 * Copyright 2020 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'Aheadworks_Raf/js/ui/grid/advocate/top-totals/column'
], function (Column) {
    'use strict';

    return Column.extend({

        /**
         * Meant to preprocess data associated with a current columns' field
         *
         * @param {Object} row
         * @returns {String}
         */
        getLabel: function (row) {
            var number = this._super(row);

            return String(number) + '%';
        }
    });
});