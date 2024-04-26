/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
define(
    [
        'jquery',
        'mage/template',
        'magento-bundle.product-summary'
    ],
    function ($, mageTemplate ) {
        'use strict';

        $.widget('Magedelight_ProductAlert.productSummary', $.mage.productSummary, {
            _renderSummaryBox: function (event, data) {
                this._super(event, data);
                this._checkAddToCartButton();
            },

            _renderOptionRow: function (key, optionIndex) {
                var template;

                template = this.element
                    .closest(this.options.summaryContainer)
                    .find(this.options.templates.optionBlock)
                    .html();

                var item = this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex];

                template = mageTemplate($.trim(template), {
                    data: {
                        _quantity_: item.qty,
                        _label_: item.name
                    }
                });
                /*rb functionality for showing stock alert*/
                template += this._getSubscribtionHtml(item.optionId);

                this.cache.summaryContainer
                    .find(this.options.optionSelector)
                    .append(template);
            },

            _getSubscribtionHtml: function (optionId) {
                var html = '';
                var config = window.md_json_config;

                if(config[optionId]
                   && config[optionId].is_salable == 0
                ){
                    html += window.md_json_config[optionId].alert;
                }

                return html;
            },

            _checkAddToCartButton: function () {
                var status = $('form.rb-block').length;
                status = status? true: false;
                $('#product-addtocart-button').attr('disabled', status);
            }

        });
    }
);
