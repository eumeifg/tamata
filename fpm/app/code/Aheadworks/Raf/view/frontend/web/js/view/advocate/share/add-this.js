/**
 * Copyright 2020 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'Aheadworks_Raf/js/view/advocate/share/abstract-service'
], function (AbstractService) {
    'use strict';

    return AbstractService.extend({

        /**
         * Prepare global window object with 'add this' data
         */
        prepareServiceData: function () {
            if (this.isAllowed()) {
                window.addthis_share = {
                    url: this.advocateShareData.referralUrl,
                    title: this.advocateShareData.invitationMessage
                };
            }
        }
    });
});