/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'underscore',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'jquery/ui'
], function ($, $t, _, idsResolver) {
    'use strict';

    $.widget('mage.catalogAddToCart', {
        options: {
            processStart: null,
            processStop: null,
            bindSubmit: true,
            minicartSelector: '[data-block="minicart"]',
            messagesSelector: '[data-placeholder="messages"]',
            productStatusSelector: '.stock.available',
            addToCartButtonSelector: '.action.tocart',
            addToCartButtonDisabledClass: 'disabled',
            addToCartButtonTextWhileAdding: '',
            addToCartButtonTextAdded: '',
            addToCartButtonTextDefault: '',
            newaddToCartButtonSelector: '#product-addtobag-button',

        },

        /** @inheritdoc */
        _create: function () {
            if (this.options.bindSubmit) {
                this._bindSubmit();
            }
        },

        /**
         * @private
         */
        _bindSubmit: function () {
            var self = this;

            if (this.element.data('catalog-addtocart-initialized')) {
                return;
            }

            this.element.data('catalog-addtocart-initialized', 1);
            this.element.on('submit', function (e) {
                e.preventDefault();
                self.submitForm($(this));
            });
        },

        /**
         * @private
         */
        _redirect: function (url) {
            var urlParts, locationParts, forceReload;

            urlParts = url.split('#');
            locationParts = window.location.href.split('#');
            forceReload = urlParts[0] === locationParts[0];

            window.location.assign(url);

            if (forceReload) {
                window.location.reload();
            }
        },

        /**
         * @return {Boolean}
         */
        isLoaderEnabled: function () {
            return this.options.processStart && this.options.processStop;
        },

        /**
         * Handler for the form 'submit' event
         *
         * @param {jQuery} form
         */
        submitForm: function (form) {
            this.ajaxSubmit(form);
        },

        /**
         * @param {jQuery} form
         */
        ajaxSubmit: function (form) {
            var self = this,
                productIds = idsResolver(form),
                formData;

            $(self.options.minicartSelector).trigger('contentLoading');
            $("#product-addtobag-button").click(function() {
                self.disableAddToCartButton(form);
            });
            formData = new FormData(form[0]);

            $.ajax({
                url: form.attr('action'),
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                showLoader: true,

                /** @inheritdoc */
                beforeSend: function () {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },

                /** @inheritdoc */
                success: function (res) {
                    var eventData, parameters;

                    $(document).trigger('ajax:addToCart', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'form': form,
                        'response': res
                    });

                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStop);
                    }

                    if (res.backUrl) {
                        eventData = {
                            'form': form,
                            'redirectParameters': []
                        };
                        // trigger global event, so other modules will be able add parameters to redirect url
                        $('body').trigger('catalogCategoryAddToCartRedirect', eventData);

                        if (eventData.redirectParameters.length > 0) {
                            parameters = res.backUrl.split('#');
                            parameters.push(eventData.redirectParameters.join('&'));
                            res.backUrl = parameters.join('#');
                        }

                        self._redirect(res.backUrl);

                        return;
                    }

                    if (res.messages) {
                        $(self.options.messagesSelector).html(res.messages);
                    }

                    if (res.minicart) {
                        $(self.options.minicartSelector).replaceWith(res.minicart);
                        $(self.options.minicartSelector).trigger('contentUpdated');
                    }

                    if (res.product && res.product.statusText) {
                        $(self.options.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }
                    self.enableAddToCartButton(form);
                },

                /** @inheritdoc */
                error: function (res) {
                    $(document).trigger('ajax:addToCart:error', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'form': form,
                        'response': res
                    });
                },

                /** @inheritdoc */
                complete: function (res) {
                    if (res.state() === 'rejected') {
                        location.reload();
                    }
                }
            });
        },

        /**
         * @param {String} form
         */
        disableAddToCartButton: function (form) {
            var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Adding...'),
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
            addToCartButton.find('span').text(addToCartButtonTextWhileAdding);
            addToCartButton.attr('title', addToCartButtonTextWhileAdding);

            // custom code for new add to cart button START
            var newaddToCartButton = $('.product.media').find(this.options.newaddToCartButtonSelector);
            newaddToCartButton.addClass(this.options.addToCartButtonDisabledClass);
            newaddToCartButton.find('span').text(addToCartButtonTextWhileAdding);
            newaddToCartButton.attr('title', addToCartButtonTextWhileAdding);
            // custom code for new add to cart button STOP
        },

        /**
         * @param {String} form
         */
        enableAddToCartButton: function (form) {
            var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added'),
                self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

                var newaddToCartButton = $('.product.media').find(this.options.newaddToCartButtonSelector);

                addToCartButton.find('span').text(addToCartButtonTextAdded);
                addToCartButton.attr('title', addToCartButtonTextAdded);

                // custom code for new add to cart button START
                newaddToCartButton.find('span').text(addToCartButtonTextAdded);
                newaddToCartButton.attr('title', addToCartButtonTextAdded);
                // custom code for new add to cart button STOP

            setTimeout(function () {
                var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');

                addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(addToCartButtonTextDefault);
                addToCartButton.attr('title', addToCartButtonTextDefault);

                // custom code for new add to cart button START
                newaddToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                newaddToCartButton.find('span').text(addToCartButtonTextDefault);
                newaddToCartButton.attr('title', addToCartButtonTextDefault);
                // custom code for new add to cart button STOP

            }, 1000);
        }
    });

    return $.mage.catalogAddToCart;
});
