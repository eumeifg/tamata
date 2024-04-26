/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Magedelight_Shippingmatrix/js/model/shipping-rates-validator',
        'Magedelight_Shippingmatrix/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        matrixrateShippingRatesValidator,
        matrixrateShippingRatesValidationRules
        ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('rbmatrixrate', matrixrateShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('rbmatrixrate', matrixrateShippingRatesValidationRules);
        return Component;
    }
);
