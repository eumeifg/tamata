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
    'underscore',
    'uiRegistry',
    'jquery/ui'
], function ($, _, registry) {
    'use strict';

    $.widget('mage.vendorOffers', {
        _create: function () {
            this._on({
                'click': '_showPopup'
            });
        },

        _initModal: function () {
            var self = this;
            var title = 'New Vendor Offer';
            if(this.options.mode == 'edit'){
                title = this.options.title;
            }
            this.modal = $('<div id="create_new_vendor_offer"/>').modal({
                 title: $.mage.__(title),
                type: 'slide',
                buttons: [],
                opened: function () {
                    $(this).parent().addClass('modal-content-vendor-offer');
                    self.iframe = $('<iframe id="create_new_vendor-offer_container">').attr({
                        src: self._prepareUrl(),
                        frameborder: 0
                    });
                    self.modal.append(self.iframe);
                    self._changeIframeSize();
                    $(window).off().on('resize.modal', _.debounce(self._changeIframeSize.bind(self), 400));
                },
                closed: function () {
                    var doc = self.iframe.get(0).document;

                    if (doc && $.isFunction(doc.execCommand)) {
                        //IE9 break script loading but not execution on iframe removing
                        doc.execCommand('stop');
                        self.iframe.remove();
                    }
                    self.modal.data('modal').modal.remove();
                    $(window).off('resize.modal');
                }
            });
        },

        _getHeight: function () {
            var modal = this.modal.data('modal').modal,
                modalHead = modal.find('header'),
                modalHeadHeight = modalHead.outerHeight(),
                modalHeight = modal.outerHeight(),
                modalContentPadding = this.modal.parent().outerHeight() - this.modal.parent().height();

            return modalHeight - modalHeadHeight - modalContentPadding;
        },

        _getWidth: function () {
            return this.modal.width();
        },

        _changeIframeSize: function () {
            this.modal.parent().outerHeight(this._getHeight());
            this.iframe.outerHeight(this._getHeight());
            this.iframe.outerWidth(this._getWidth());

        },

        _prepareUrl: function () {
            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?');
        },

        _showPopup: function () {
            this._initModal();
            this.modal.modal('openModal');
        }
    });

    return $.mage.vendorOffers;
});
