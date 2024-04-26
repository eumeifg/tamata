define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'jquery',
    'mage/storage',
    'Magento_Ui/js/modal/modal',
    'ko',
    'mage/mage'
], function (Component, customerData, $t, $, storage, modal, ko) {
    'use strict';
    var wishListDataPost = 'test';
    return Component.extend({
        defaults: {
            productId: 0,
            loginUrl: '',
            activeClass: '',

        },

        /** @inheritdoc */
        initialize: function (config) {
            this._super();
            this.productId = config.productId;
            this.loginUrl = config.loginUrl;
            var component = this;
            this.activeClass = ko.pureComputed(function() {
                return component._checkWishlistItem()? 'active': '';
            }).extend({ notify: 'always' });
        },

        /** @inheritdoc */
        _callApi: function() {
            var component = this;

            var serviceUrl = 'rest/V1/ktpl/extendedwishlist/';
            var isAddedToWishlist = component._checkWishlistItem();

            $('body').trigger('processStart');
            storage.post(
                isAddedToWishlist? serviceUrl + 'remove': serviceUrl + 'add',
                JSON.stringify({ val: component.productId })
            ).done(function (response) {
                $('body').trigger('processStop');
                response = JSON.parse(response);
                wishListDataPost = response.referer;
                if(response.status == 'LOGIN_REQUIRED') {
                    component._modelPopup();
                    $("#popup-wishlist-message").modal("openModal");
                }
                else if(response.status == 'SUCCESS')
                {
                    this.activeClass = ko.pureComputed(function() {
                        return isAddedToWishlist? 'active': '';
                    }).extend({ notify: 'always' });
                }
                else
                {
                    customerData.set('messages', {
                        messages: [{
                            type: 'error',
                            text: response.message
                        }]
                    });
                    // alert(response.message);
                }
            }).fail(function (response) {
                $('body').trigger('processStop');
                customerData.set('messages', {
                    messages: [{
                        type: 'error',
                        text: $t('There was error during saving data')
                    }]
                });
                // console.log($t('There was error during saving data'));
            });
        },

        /** @inheritdoc */
        _modelPopup: function() {
            var component = this;
            if($("#popup-wishlist-message").attr("id") == undefined) {
                var component = this;
                var options = {
                    type: 'popup',
                    title: $.mage.__('Wishlist Alert'),
                    modalClass: 'custom-wishlist-modal',
                    responsive: true,
                    innerScroll: false,
                    buttons: []
                };
                $("body").append(
                    $("<div></div>").attr({"id": "popup-wishlist-message"})
                        .html($.mage.__('Please login to your account to access your wishlist')+"<div class='wishlist-message-buttons-div'><a style='width: 145px;margin: 20px 0px 0;background:#fa0028;padding:6px 25px;font-size: 17px;font-weight: bold;color: #fff;display: block;text-transform: uppercase;float:right;text-decoration:none;' href='#' data-post='"+wishListDataPost+"'>"+$.mage.__('Go to Login')+"</a></div>")
                );
                var popup = modal(options, $('#popup-wishlist-message'));
            }
        },
        closeModal : function () {
            console.log("clicked");
            $("#popup-wishlist-message").modal("closeModal");
        },
        _checkWishlistItem: function() {
            var component = this;

            // customerData.init();
            var wishlist = customerData.get("wishlist-ids")();

            var returnValue = false;
            $(wishlist.ids).each(function(i, e) {
                if(!returnValue && e.product_id == component.productId)
                    returnValue = true;
            });

            return returnValue;
        }
    });
});
