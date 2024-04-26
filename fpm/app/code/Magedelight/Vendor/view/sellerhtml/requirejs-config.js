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
var config = {
    map: {
        '*': {
            rbCustomScroll:     'Magedelight_Vendor/js/jquery.mCustomScrollbar.min',
            datatable:          'Magedelight_Vendor/js/jquery.dataTables.min'
        }
    },
    shim: {
        'rbCustomScroll': {
            deps: ['jquery']
        },
        'datatable': {
            deps: ['jquery']
        }
    }  
};
