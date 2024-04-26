/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
var config = {
    map: {
        '*': {
            amnotification                      : 'Magedelight_ProductAlert/js/amnotification',
            'productSummary'                    : 'Magedelight_ProductAlert/js/bundle/product-summary',
            'magento-bundle.product-summary'    : 'Magento_Bundle/js/product-summary'
        }
    },
    deps: [
        'Magento_ConfigurableProduct/js/configurable'
    ]
};
