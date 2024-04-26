/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
define([
    "jquery",
    "jquery/ui",
    'Magento_Ui/js/modal/modal',
    "mage/translate"
], function($){
    "use strict";
    $.widget('mage.orderEditDialog', {
        options: {
            url:     null,
            message: null,
            modal:  null
        },

        /**
         * @protected
         */
        _create: function () {
            this._prepareDialog();
        },

        /**
         * Show modal
         */
        showDialog: function() {
            this.options.dialog.html(this.options.message).modal('openModal');
        },

        /**
         * Redirect to edit page
         */
        redirect: function() {
            window.location = this.options.url;
        },

        /**
         * Prepare modal
         * @protected
         */
        _prepareDialog: function() {
            var self = this;

            this.options.dialog = $('<div class="ui-dialog-content ui-widget-content"></div>').modal({
                type: 'popup',
                modalClass: 'edit-order-popup',
                title: $.mage.__('Edit Order'),
                buttons: [{
                    text: $.mage.__('Ok'),
                    'class': 'action-primary',
                    click: function(){
                        self.redirect();
                    }
                }]
            });
        }
    });

    return $.mage.orderEditDialog;
});
