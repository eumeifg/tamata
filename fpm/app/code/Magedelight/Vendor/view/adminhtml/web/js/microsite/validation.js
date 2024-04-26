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

require([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){

    $.validator.addMethod(
        'data_md_banner_image-validation',
        function (v) {
            if(v){
                var logoSize = 524288;
                if ($('#data_md_banner_image').get(0).files.length) {
                    var fileSize = $('#data_md_banner_image').get(0).files[0].size; /* in bytes */
                    if (fileSize > logoSize) {
                        alert("iini");
                        return false;
                    }else{
                        return true;
                    }
                }
            }else{
                return true;
            }
        },
        $.mage.__('Microsite Banner file size is more than 512KB')
    );

   
    $.validator.addMethod(
        'brand-banner-validation',
        function (v) {
            if(v){
                var logoSize = 524288;
                if ($('#brand_banner').get(0).files.length) {
                    var fileSize = $('#brand_banner').get(0).files[0].size; /* in bytes */
                    if (fileSize > logoSize) {
                        return false;
                    }else{
                        return true;
                    }
                }
            }else{
                return true;
            }
        },
        $.mage.__('Brand Banner file size is more than 512KB')
    );

    $.validator.addMethod(
        'microsite_banner-validation',
        function (v) {
            if(v){
                var logoSize = 524288;
                if ($('#microsite_banner').get(0).files.length) {
                    var fileSize = $('#microsite_banner').get(0).files[0].size; /* in bytes */
                    if (fileSize > logoSize) {
                        return false;
                    }else{
                        return true;
                    }
                }
            }else{
                return true;
            }
        },
        $.mage.__('Microsite Banner file size is more than 512KB')
    );

    $.validator.addMethod(
        'validate-brand-name',
        function (v) {
            return /(^.{1,100}$)/.test(v);
        },
        $.mage.__('Please enter less than 100 character for Brand Name.')
    );

    $.validator.addMethod(
        'validate-meta-keyword',
        function (v) {
            return /(^.{0,255}$)/.test(v);
        },
        $.mage.__('Please enter less than 255 character for Meta keyword.')
    );

    $.validator.addMethod(
        'validate-meta-desc',
        function (v) {
            return /(^.{0,600}$)/.test(v);
        },
        $.mage.__('Please enter less than 600 character for Meta Description.')
    );

    $.validator.addMethod(
        'validate-desc',
        function (v) {
            return /(^.{0,600}$)/.test(v);
        },
        $.mage.__('Please enter less than 600 character for Description.')
    );


    $.validator.addMethod(
        'validate-sort-order',
        function (v) {
            return /(^\d{0,4}$)/.test(v);
        },
        $.mage.__('Please enter less than 9999 numbers for Sort Order.')
    );

});
