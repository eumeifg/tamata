/**
 * Copyright 2020 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            advocateShareData: {}
        },

        /**
         * Update data of component
         *
         * @param {Object} advocateShareData
         */
        updateData: function (advocateShareData) {
            this.advocateShareData = advocateShareData;
            this.prepareServiceData();
        },

        /**
         * Is allowed to show data
         * @returns {Boolean}
         */
        isAllowed: function () {
            return this.enabled;
        }
    });
});