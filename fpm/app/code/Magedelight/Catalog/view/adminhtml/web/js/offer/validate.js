/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
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
            
            /* Image dimension validation starts. */
            $.validator.addMethod(
                'validate-special-price',
                function (v) {
                    if(v && parseFloat(v) >= parseFloat($("#vendor_product_price").val())){
                        return false;
                    }
                    return true;
                },
                $.mage.__('Please enter special price less than unit price.')
            );
        }
    }
);
