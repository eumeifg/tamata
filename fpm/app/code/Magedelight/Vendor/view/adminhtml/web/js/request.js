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
        'jquery'
    ],
    function ($) {
        'use strict';

        return function (config) {
            var REQUEST_STATUS_DENIED = config.deniedStatusValue;
            var statusDescriptionContainer = $('.field-status_description');
            var statusDescriptionElement = $('#request_status_description');
            var statusElement = $('#request_status');
            var statusDescriptionClasses = 'required-entry _required';
            $(function() {
                
                if (statusElement.val() != REQUEST_STATUS_DENIED) {
                    statusDescriptionContainer.hide();
                } else if(statusElement.val() == REQUEST_STATUS_DENIED)
                {
                    statusDescriptionContainer.show();
                }
                
                // $('#edit_form').on('change', '#request_status', function (event) {
                //     if (statusElement.val() != REQUEST_STATUS_DENIED) {
                //         statusDescriptionElement.removeClass(statusDescriptionClasses);
                //         statusDescriptionContainer.hide();
                //     } else if(statusElement.val() == REQUEST_STATUS_DENIED)
                //     {
                //         statusDescriptionElement.addClass(statusDescriptionClasses);
                //         statusDescriptionContainer.show();
                //     }
                // });
            });
        }
    }
);
