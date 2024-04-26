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

define([
        'jquery',
        'Magento_Ui/js/lib/validation/rules',
        'mage/translate',
	'jquery/validate'
    ],
    function ($) {
        'use strict';

        return function (config) {
            
            /* Image dimension validation starts. */
            $.validator.addMethod(
                'validate-image-width-height',
                function () {
                },
                $.mage.__('Image should be in mentioned dimension only!')
            );
    
            $.validator.addMethod(
                 'validate-no-of-chars',
                 function (v) {
                
                        return $.mage.isEmptyNoTrim(v) || /^.{0,150}$/.test(v);
                    },
                 $.mage.__('Please enter less than 150 characters for this field.')
            );
     
            function validateImageDimensions(elementId){
                $(elementId).change(function (e) {
                    var file, img;
                    if ((file = this.files[0])) {
                        img = new Image();
                        img.onload = function () {
                            if ($(elementId).data("m-width") != this.width || $(elementId).data("m-height") != this.height) { 
                                $(elementId).addClass("validate-image-width-height");
                            }else {
                                $(elementId).removeClass("validate-image-width-height");
                            }
                        };
                        img.src = window.URL.createObjectURL(file);
                    }
                });
            }
            /* Image dimension validation ends. */

            validateImageDimensions('#company-logo');
            
            $.validator.addMethod(
                'validate-vat',
                function (v) {
                    return /(^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{0,15}$)/.test(v);
                },
                $.mage.__('Please enter valid VAT. For e.g. {24ABCDE2345F6ZM}.')
            );

            $.validator.addMethod(
                'validate-bank-account-number',
                function (v) {
                    return (v)? (/^[0-9]{10,20}$/.test(v)) : true;
                },
                $.mage.__('Bank account number should be numeric value ranging from 10 to 20 digits.')
            );

            $.validator.addMethod(
                'validate-cbank-account-number',
                function () {
                    var conf = $('#cbank-account-number').length > 0 ? $('#cbank-account-number') : $($('.validate-cbank-account-number')[0]);
                    var pass = false;
                    if ($('#bank-account-number')) {
                        pass = $('#bank-account-number');
                    }
                    var passwordElements = $('.validate-bank-account-number');
                    for (var i = 0; i < passwordElements.length; i++) {
                        var passwordElement = $(passwordElements[i]);
                        if (passwordElement.closest('form').attr('id') === conf.closest('form').attr('id')) {
                            pass = passwordElement;
                        }
                    }
                    return (pass.val() === conf.val());
                },
                $.mage.__('Please make sure your bank account numbers match.')
            );

            $.validator.addMethod(
                'validate-ifsc-code',
                function (v) {
                    return (v)? (/(^[A-Za-z]{4}[0-9]{7}$)/.test(v)) : true;
                },
                $.mage.__('Please enter valid IFSC Code. For e.g. {ABCD1234567}.')
            );
    
            $.validator.addMethod(
                'validate-image-size',
                function (value, element) {
                    if(value){
                        if ($(element).get(0).files.length) {
                            if ($(element).get(0).files[0].size > $(element).data('max-size')) { /* in bytes */
                                return false;
                            }else{
                                return true;
                            }
                        }
                    }else{
                        return true;
                    }
                },
                $.mage.__('Image size is more than mentioned size.')
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
    
            $.validator.addMethod(
                'validate-file-types',
                function (value, element) {
                    if(value){
                        if ($(element).get(0).files.length) {
                            if (/\.(pdf|doc|docx)$/i.test($(element).get(0).files[0].name) === false) {
                                return false;
                            }else{
                                return true;
                            }
                        }
                    }else{
                        return true;
                    }
                },
                $.mage.__('Please use following file types (PDF, DOC, DOCX).')
            );
    
            $.validator.addMethod(
                'validate-vat-number',
                function (v) {
                    if(v){
                        return /(^[A-Za-z0-9]{10,20}$)/.test(v);    
                    }
                    return true;
                },
                $.mage.__('Allow only alpha numeric value without space, not allowed special character and minimum length 10 and maximum length 20.')
            );
            $.validator.addMethod(
                'validate-digits',
                function (v) {
                    return /(^[0-9]$)/.test(v);
                },
                $.mage.__('Please enter Only Numeric Value.')
            );
    
            $.validator.addMethod(
                'validate-domain-150',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{0,150}$/.test(v) && /\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i.test(v);

                },
                $.mage.__('Please enter valid site URL only, character limit is 150.')
            );
    
            $.validator.addMethod(
                'validate-alpha-with-spaces-spl-150-address2',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{0,150}$/.test(v);
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
            'validate-alpha-with-spaces-spl-50',
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^.{1,50}$/.test(v);
            },
            $.mage.__('Please use less than 50 characters  in this field.')
            );

            $.validator.addMethod(
                'validate-alpha-with-spaces-50',
                function (v) {

                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z ]{1,50}$/.test(v);
                },
                $.mage.__('Please use less than 50 characters  in this field, allow only alphabets.')
            );

            $.validator.addMethod(
                'validate-alpha-with-spaces-spl-10',
                function (v) {

                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9 ]{4,12}$/.test(v);
                },
                $.mage.__('Allow alphanumeric value with space, character limit 12, not allowed special character Also, it should take minimum 4 characters.')
            );
        }
    }
);
