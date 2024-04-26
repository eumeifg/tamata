/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
define([
    'jquery',
    'Magedelight_Sales/sales/order/create/scripts'
], function (jQuery) {
    'use strict';

    var $el = jQuery('#edit_form'),
        config,
        baseUrl,
        order,
        payment;

    if (!$el.length || !$el.data('order-config')) {
        return;
    }

    config = $el.data('order-config');
    baseUrl = $el.data('load-base-url');

    order = new AdminOrder(config);
    order.setLoadBaseUrl(baseUrl);

    payment = {
        switchMethod: order.switchPaymentMethod.bind(order)
    };

    window.order = order;
    window.payment = payment;
});