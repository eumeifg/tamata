/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
/*global alert:true*/
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, $t, alert) {
    'use strict';

    $.widget('rb.commonMethods', {
        /**
         * Button creation
         * @protected
         */
        options: {
            uniqueSkuCheckUrl: '',
            sellerSkuEle: '',
            isCheckuniqueSku: false,
            isCheckManufacturerUniqueSku: false,
            manufacturerSkuEle: '',
            uniqueManufacturerSkuCheckUrl: ''
        },
        _init: function () {
            if (this.options.isCheckuniqueSku) {
                this._checkuniqueSku();
            }
            if (this.options.isCheckManufacturerUniqueSku) {
                this._checkuniquemanufacturerSku();
            }

        },
        _create: function () {

        },
        _checkuniqueSku: function () {
            var $widget = this,
                    ele = this.options.sellerSkuEle,
                    skuval = $(ele).val();
            $.ajax({
                url: this.options.uniqueSkuCheckUrl,
                type: 'POST',
                data: {"sku": skuval},
                success: function (data, textStatus, xhr) {                    
                    if (xhr.responseText != false) {                       
                        $widget._error(xhr.responseText);
                        $(ele).val('').focus();
                    }
                }

            });
        },
        _checkuniquemanufacturerSku: function () {
          var $widget = this,
                    ele = this.options.manufacturerSkuEle,
                    skuval = $(ele).val();
            
            $.ajax({
                url: this.options.uniqueManufacturerSkuCheckUrl,
                type: 'POST',
                data: {"manufacturer_sku": skuval},
                success: function (data, textStatus, xhr) {
                    if (xhr.responseText == "Manufacturer Sku already exist.") {                        
                        $widget._error(xhr.responseText);
                        $(ele).val('').focus();
                    }
                }
            });  
        },
        /**
         * Show alert message
         * @param {String} message
         */
        _error: function (message) {
            alert({
                content: message
            });
        },
    });

    return $.rb.commonMethods;
});
