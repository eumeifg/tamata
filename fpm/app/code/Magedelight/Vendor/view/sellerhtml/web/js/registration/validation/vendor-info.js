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
	'jquery/validate'
    ],
    function ($) {
        'use strict';

        return function (config) {
            
            /* Vendor information tab validation starts. */
            $(":password").keydown(function (e) {
                if (e.which == 32) {
                    e.preventDefault();
                }
            });
            
            $.validator.addMethod(
                'validate-custom-pswd',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=]).{6,30}$/.test(v);
                },
                $.mage.__('Password must be at least 6 characters and no more than 30 characters, also it must include alphanumeric lower and upper case letters with at least one special character.')
            );

            $.validator.addMethod(
                'validate-custom-spl',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^(?=.*[a-z A-Z 0-9][@#$%^&+=]).$/.test(v);
                },
                $.mage.__('Password must be at least 6 characters, no more than 30 characters, and must include at least one upper case letter, one lower case letter, and one numeric digit.')
            );

            $.validator.addMethod(
                'validate-alpha-with-spaces-name',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z \-_.\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF ]{1,150}$/.test(v);
                },
                $.mage.__('Please use less than 150 characters and only letters (a-z or A-Z)  or spaces only in this field.')
            );

            $.validator.addMethod(
                'validate-alpha-with-spaces-spl-150',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{1,150}$/.test(v);
                },
                $.mage.__('Please use less than 150 characters  in this field.')
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
                'validate-alpha-with-spaces-50',
                function (v) {

                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z \-_.\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF ]{1,50}$/.test(v);
                },
                $.mage.__('Please use less than 50 characters  in this field, allow only alphabets.')
            );

            $.validator.addMethod(
                'validate-alphanum-with-spaces-12',
                function (v) {

                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9 \-_.\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF ]{1,12}$/.test(v);
                },
                $.mage.__('Allow alphanumeric value with space, character limit 12, not allowed special character.')
            );

            $.validator.addMethod(
                'validate-alpha-with-spaces-spl-10',
                function (v) {

                    return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9 ]{4,12}$/.test(v);
                },
                $.mage.__('Allow alphanumeric value with space, character limit 12, not allowed special character Also, it should take minimum 4 characters.')
            );
        
            /* Vendor information tab validation ends. */
        };
    }
);
