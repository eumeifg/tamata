/**
* Magedelight
* Copyright (C) 2019 Magedelight <info@magedelight.com>
*
* @category Magedelight
* @package Magedelight_Elasticsearch
* @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
 */
define([
    'underscore',
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, $, uiRegistry, select) {
    'use strict';

    return select.extend({
        initialize: function () {
            this._super();

            this.showHideFields(this.value(), 'initialize');

            return this;
        },

        onUpdate: function (value) {
            this.showHideFields(value, 'update');

            return this._super();
        },

        showHideFields: function(value, action) {
            var self = this;
            var customerGroup = uiRegistry.get('index = send_to_customer_group');
            var customer = uiRegistry.get('index = send_to_customer');

            if (customerGroup && customer) {
                switch(value) {
                    case 'all':
                        customerGroup.hide();
                        customer.hide();
                        break;
                    case 'customer_group':
                        customerGroup.show();
                        customer.hide();
                        break;
                    case 'customer':
                        customerGroup.hide();
                        customer.show();
                        break;
                    default:
                        break;
                }
            } else {
                setTimeout(function() {
                    self.showHideFields(value, action);
                }, 1000);
            }
        }
    });
});
