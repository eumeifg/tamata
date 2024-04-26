define([    
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!Magedelight_Commissions/templates/grid/cells/payandadjust.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, sendmailPreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        }
    });
});