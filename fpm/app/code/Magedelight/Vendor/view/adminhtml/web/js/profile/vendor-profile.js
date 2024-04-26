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
        'validate-alpha-with-spaces-name',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z \-_.\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF ]{1,150}$/.test(v);
                },
                $.mage.__('Please use less than 150 characters and only letters (a-z or A-Z)  or spaces only in this field.')
                );

    $.validator.addMethod(
        'validate-alpha-with-spaces-name-50',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z \-_.\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF ]{1,150}$/.test(v);
                },
                $.mage.__('Please use less than 50 characters and only letters (a-z or A-Z)  or spaces only in this field.')
                );

                $.validator.addMethod(
                'validate-alpha-with-name-20',
                function (v) {
                        if(v){
                            return /(^[A-Za-z0-9]{10,20}$)/.test(v);
                        }else{
                            return true;
                        }
                    },
                    $.mage.__('Please use minimum 10 and maximum 20 character')
                );

                $.validator.addMethod(
                'validate-alpha-with-spaces-spl-150',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{1,150}$/.test(v);
                },
                $.mage.__('Please use less than 150 characters  in this field.')
                );

                $.validator.addMethod(
                'validate-no-of-chars-300',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{0,300}$/.test(v);
                },
                $.mage.__('Please use less than 300 characters  in this field.')
                );

               
                $.validator.addMethod(
                'validate-alpha-with-spaces-spl-150-address2',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{0,150}$/.test(v);
                },
                $.mage.__('Please use less than 150 characters  in this field.')
                );

                $.validator.addMethod(
                'validate-alpha-with-spaces-spl-50',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{1,50}$/.test(v);
                },
                $.mage.__('Please use less than 50 characters  in this field.')
                );

                $.validator.addMethod(
                'validate-alpha-with-num-10',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9]{1,50}$/.test(v);
                },
                $.mage.__('Please use less than 50 characters  in this field.')
                );

                $.validator.addMethod(
                'validate-alpha-with-spaces-spl-255',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{0,255}$/.test(v);
                },
                $.mage.__('Please use less than 255 characters  in this field.')
                );

                $.validator.addMethod(
                    'validate-bank-account-number',
                    function (v) {
                        return (v)? (/(^\d{10,30}$)/.test(v)) : true;
                    },
                    $.mage.__('Please enter only number, Limit minimum 10 and maximum 30.')
                );

                $.validator.addMethod(
                        'validate-mobile-number',
                        function (v) {
                            return /(^\d{10,10}$)/.test(v);
                        },
                        $.mage.__('Please enter only number, Limit 10 digits.')
                    );



                $.validator.addMethod(
                    'validate-ifsc-code',
                    function (v) {
                        return (v)? (/(^[A-Za-z]{4}[0-9]{7}$)/.test(v)) : true;
                    },
                    $.mage.__('Please enter valid IFSC Code. For e.g. {ABCD1234567}.')
                );

                $.validator.addMethod(
                    'validate-pin-code',
                    function (v) {
                       return /(^[A-Za-z0-9]{1,10}$)/.test(v);
                    },
                    $.mage.__('Please enter valid Pincode Code. For e.g. {ABCD1234567}, Limit only 10 digits .')
                );

                

                 $.validator.addMethod(
                    'validate-zip-code',
                    function (v) {
                        return Validation.get('IsEmpty').test(v) || /^[a-zA-Z0-9 ]+$/.test(v)
                    },
                    $.mage.__('Please enter valid Zipcode Code. For e.g. {ABCD1234567}.')
                );
    $.validator.addMethod(
        'logo-size-validation',
        function (v) {
            if(v){
                var logoSize = 524288;
                if ($('#vendor_logo').get(0).files.length) {
                    var fileSize = $('#vendor_logo').get(0).files[0].size; /* in bytes */
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
        $.mage.__('Logo file size is more than 512KB')
    );

    $.validator.addMethod(
        'vat-size-validation',
        function (v) {
            if(v){
                var logoSize = 524288;
                if ($('#vendor_vat_doc').get(0).files.length) {
                    var fileSize = $('#vendor_vat_doc').get(0).files[0].size; /* in bytes */
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
        $.mage.__('Vat file size is more than 512KB')
    );
    
    $.validator.addMethod(
        'validate-image-type',
        function (value, element) {
            if(value){
                if ($(element).get(0).files.length) {
                    if (/\.(jpe?g|png)$/i.test($(element).get(0).files[0].name) === false) {
                        return false;
                    }else{
                        return true;
                    }
                }
            }else{
                return true;
            }
        },
        $.mage.__('Please use following file types (JPG,JPEG,PNG).')
    );
});
