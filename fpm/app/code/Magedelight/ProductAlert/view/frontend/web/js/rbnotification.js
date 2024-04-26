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
define([
    "jquery",
    "underscore",
    "mage/template",
    "priceUtils",
    "Magento_ConfigurableProduct/js/configurable",
    "priceBox",
    "jquery/ui",
    "jquery/jquery.parsequery",
    "mage/mage",
    "mage/validation",
], function ($, _, mageTemplate, utils, Component) {

    $.widget('mage.rbnotification', {
        configurableStatus: null,
        spanElement: null,
        options: {},
        _create: function () {
            this._rewritePrototypeFunction();
            this.spanElement = $('.stock.available span')[0];
            this.settings = $('.swatch-option');
            $(document).ready($.proxy(function () {
                var val = jQuery('.super-attribute-select:last').val();
                if (val) {
                    jQuery('.super-attribute-select:last').change();
                }
            }, this));
        },
        _removeStockStatus: function () {
            if ($('#rbstockstatus-status').legth) {
                $('#rbstockstatus-status').remove();
            }
        },
        _hideStockAlert: function () {
            if ($('#rbstockstatus-stockalert').length) {
                $('#rbstockstatus-stockalert').remove();
            }
        },
        _reloadDefaultContent: function (key) {
            if (this.spanElement) {
                this.spanElement.innerHTML = this.configurableStatus;
            }
            $('.box-tocart').each(function (index, elem) {
                $(elem).show();
            });
        },
        showStockAlert: function (code) {
            var wrapper = $('.product-add-form')[0];
            var div = document.createElement('div');
            div.id = 'rbstockstatus-stockalert';
            div.innerHTML = code;
            $(div).insertBefore($(wrapper));
            $('#form-validate-stock').mage('validation');

        },
        onConfigure: function ($this,$widget) {
            $proxy = $.proxy($widget._OnClickSuper,$widget);
            $proxy($this,$widget);

            var settings = this.settings;
            this._hideStockAlert();
            this._removeStockStatus();
            if (null == this.configurableStatus && this.spanElement) {
                this.configurableStatus = this.spanElement.innerHTML;
            }
            var selectedKey = "";
            for (var i = 0; i < settings.length; i++) {
                if ($(settings[i]).hasClass('selected')) {
                    selectedKey += $(settings[i]).attr('option-id') + ',';
                }
            }
            var trimSelectedKey = selectedKey.substr(0, selectedKey.length - 1);
            var countKeys = selectedKey.split(",").length - 1;

            if ('undefined' != typeof(this.options.productalert[trimSelectedKey])) {
                this._reloadContent(trimSelectedKey);
            }
            else {
                this._reloadDefaultContent(trimSelectedKey);
            }

            if(this.options.productalert[trimSelectedKey]){
                jQuery('#form-validate-price input[name="product_id"]').val(this.options.productalert[trimSelectedKey]['product_id']);
            }else{
                jQuery('#form-validate-price input[name="product_id"]').val(jQuery('#form-validate-price input[name="parent_id"]').val());
            }

            var a = 1;
        },
        _reloadContent: function (key) {

            if ('undefined' != typeof(this.options.productalert.changeConfigurableStatus) && this.options.productalert.changeConfigurableStatus && this.spanElement) {
                if (this.options.productalert[key] && this.options.productalert[key]['custom_status']) {
                    if (this.options.productalert[key]['custom_status_icon_only'] == 1) {
                        this.spanElement.innerHTML = this.options.productalert[key]['custom_status_icon'];
                    } else {
                        this.spanElement.innerHTML = this.options.productalert[key]['custom_status_icon'] + this.options.productalert[key]['custom_status'];
                    }
                } else {
                    this.spanElement.innerHTML = this.configurableStatus;
                }
            }

            if ('undefined' != typeof(this.options.productalert[key]) && this.options.productalert[key] && 0 == this.options.productalert[key]['is_in_stock']) {
                $('.box-tocart').each(function (index, elem) {
                    $(elem).hide();
                });
                if (this.options.productalert[key]['stockalert']) {
                    this.showStockAlert(this.options.productalert[key]['stockalert']);
                }
            } else {
                $('.box-tocart').each(function (index, elem) {
                    $(elem).show();
                });
            }
        },

        _rewritePrototypeFunction: function () {
        }
    });
});