/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
require([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){
    $.validator.addMethod(
        'brand-logo-validation',
        function (v) {
            if(v){
                var logoSize = 2097152;
                if ($('#brand_logo').get(0).files.length) {
                    var fileSize = $('#brand_logo').get(0).files[0].size; /* in bytes */
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
        $.mage.__('Brand file size is more than 2MB')
    );

   

    $.validator.addMethod(
        'validate-name',
        function (v) {
            
            return /(^[a-zA-Z ]{1,50}$)/.test(v);
        },
        $.mage.__('Please enter less than 50 character for Name.')
    );

    

    $.validator.addMethod(
        'validate-meta-desc',
        function (v) {
            return /(^.{0,600}$)/.test(v);
        },
        $.mage.__('Please enter less than 600 character for Meta Description.')
    );

    

    $.validator.addMethod(
        'validate-sort-order',
        function (v) {
            return /(^\d{0,4}$)/.test(v);
        },
        $.mage.__('Please enter less than 9999 numbers for Sort Order.')
    );

});
