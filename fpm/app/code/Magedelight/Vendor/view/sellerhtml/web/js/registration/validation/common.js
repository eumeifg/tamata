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
    'mage/translate',
    'jquery/validate',
    'Magento_Checkout/js/model/postcode-validator'
],
    function ($, $t, $validator, postcodeValidator) {
        'use strict';
        var $zipcodeMessage = $t('Provided Zip/Postal Code seems to be invalid.');
        var $pickupzipcodeMessage = $t('Provided Zip/Postal Code seems to be invalid.');
        return function (config) {
            
            $.validator.addMethod(
                'validate-file-size',
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
                $.mage.__('File size should be less than mentioned size.')
            );
    
            $.validator.addMethod(
                'validate-alpha-with-spaces',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z ]+$/.test(v);
                },
                $t('Please use only letters (a-z or A-Z) or spaces only in this field.')
            );

            $.validator.addMethod(
                'zipcode-by-country',
                function (v) {
                    var countryId = $('select[name="country_id"]').val(),
                    validationResult
                    ;

                    if ($.mage.isEmptyNoTrim(v)) {
                        return true;
                    }
                    
                    validationResult = postcodeValidator.validate(v, countryId);
                    if (!validationResult) {

                        if (postcodeValidator.validatedPostCodeExample.length) {
                            $zipcodeMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                        }
                    }
                    return validationResult;
                },
                $t($zipcodeMessage)
            );
    
        $.validator.addMethod(
                'pickup-zipcode-by-country',
                function (v) {
                    var pickupcountryId = $('select[name="pickup_country_id"]').val(),
                    validationResult
                    ;

                    if ($.mage.isEmptyNoTrim(v)) {
                        return true;
                    }
                    
                    validationResult = postcodeValidator.validate(v, pickupcountryId);
                    if (!validationResult) {

                        if (postcodeValidator.validatedPostCodeExample.length) {
                            $pickupzipcodeMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                        }
                    }
                    return validationResult;
                },
                $t($zipcodeMessage)
            );
        }
    }
);
