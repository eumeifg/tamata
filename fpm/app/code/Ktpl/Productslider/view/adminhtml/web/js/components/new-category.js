/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ktpl.com license that is
 * available through the world-wide-web at this URL:
 * https://www.ktpl.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://www.ktpl.com/)
 * @license     https://www.ktpl.com/LICENSE.txt
 */

define([
    'underscore',
    'Magento_Catalog/js/components/new-category'
], function (_, Category) {
    'use strict';

    /**
     * Processing options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Array} created - list to add new options
     *
     * @return {Array} Plain options list
     */
    function flattenCollection(array, separator, created) {
        var i = 0,
            length,
            childCollection;

        array = _.compact(array);
        length = array.length;
        created = created || [];

        for (i; i < length; i++) {
            created.push(array[i]);

            if (array[i].hasOwnProperty(separator)) {
                childCollection = array[i][separator];
                delete array[i][separator];
                flattenCollection.call(this, childCollection, separator, created);
            }
        }

        return created;
    }

    return Category.extend({
        /**
         * Set option to options array.
         *
         * @param {Object} option
         * @param {Array} options
         */
        setOption: function (option, options) {
            var parent = parseInt(option.parent);
            if (_.contains([0, 1], parent)) {
                options = options || this.cacheOptions.tree;
                options.push(option);

                var copyOptionsTree = JSON.parse(JSON.stringify(this.cacheOptions.tree));
                this.cacheOptions.plain = flattenCollection(copyOptionsTree, this.separator);
                this.options(this.cacheOptions.tree);
            } else {
                this._super(option, options);
            }
        }
    });
});
