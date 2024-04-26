/**
 * Copyright 2020 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'uiElement',
    'Aheadworks_Raf/js/model/friend/manager',
    'Aheadworks_Raf/js/widget/popup'
], function (UiElement, friendManager, awRafPopup) {
    'use strict';

    return UiElement.extend({
        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super();

            this.displayPopup();

            return this;
        },

        /**
         * Display popup
         */
        displayPopup: function () {
            if (this.isSetPopupContent && friendManager.canDisplayWelcomePopup()) {
                awRafPopup();
                friendManager.welcomePopupViewed();
            }
        }
    });
});
