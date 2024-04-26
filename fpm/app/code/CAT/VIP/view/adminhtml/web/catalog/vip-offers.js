define([
    'jquery',
    'underscore',
    'uiRegistry',
    'jquery/ui'
], function ($, _, registry) {
    'use strict';

    $.widget('mage.vipOffers', {
        _create: function () {
            this._on({
                'click': '_showPopup'
            });
        },

        _initModal: function () {
            var self = this;
            var title = 'New VIP Offer';
            if(this.options.mode == 'edit'){
                title = this.options.title;
            }
            this.modal = $('<div id="create_new_vip_offer"/>').modal({
                 title: $.mage.__(title),
                type: 'slide',
                buttons: [],
                opened: function () {
                    $(this).parent().addClass('modal-content-vip-offer');
                    self.iframe = $('<iframe id="create_new_vip-offer_container">').attr({
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

    return $.mage.vipOffers;
});
