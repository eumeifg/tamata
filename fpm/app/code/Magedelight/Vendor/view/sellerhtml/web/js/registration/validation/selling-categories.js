/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'mage/translate',
	'jquery/validate'
    ],
    function ($) {
        'use strict';

        return function (config) {
    
            $.validator.addMethod(
                'validate-alphanumb-with-spl-255',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^.{0,255}$/.test(v);
                },
                $.mage.__('Please use less than 255 characters in this field.')
                );

            $.validator.addMethod(
                    'validate-alphanumb-with-space-255',
                    function (v) {
                        return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9 ]{0,255}$/.test(v);
                    },
                    $.mage.__('Allow only alphanumeric value with space, not allowed special characters and character limit is 255.')
                    );

            $.validator.addMethod(
                'validate-alphanum-with-spaces-comma',
                function (v) {
                    return $.mage.isEmptyNoTrim(v) || /^[A-Za-z0-9, ]+$/.test(v);
                },
                $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9), spaces or comma(,) only in this field.')
            );
        }
    }
);
