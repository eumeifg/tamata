define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/modal'
    ], function ($, _, modal) {
    "use strict";

    return function (config) {
        if (config.zoomType == 'hover') {
            $(document).ready(function() {
                $('.fotorama__stage').live('mouseleave', function() {
                    $('.magnify-lens').addClass('magnify-hidden');
                    $('.magnifier-preview').addClass('magnify-hidden');
                });
            });
        }

        $(document).ready(function() {
            var documentPadding = 25;
            var firstAttempt = true;
            var documentHeight = 0;
            var lastHeight = 0, curHeight = 0;
            var parentBody = window.parent.document.body;
            $('.mfp-preloader', parentBody).css('display', 'none');
            $('.mfp-iframe-holder .mfp-content', parentBody).css('width', '100%');
            $('.mfp-iframe-scaler iframe', parentBody).animate({'opacity': 1}, 2000);
            $('.reviews-actions a').attr('target', '_parent');
            $('.product-social-links a').attr('target', '_parent');
            $('body').css('overflow', 'hidden');

            setInterval(function(){
                if (firstAttempt) {
                    curHeight =  $('.page-wrapper').outerHeight(true) + documentPadding;
                } else {
                    curHeight =  $('.page-wrapper').outerHeight(true);
                }
                documentHeight =  curHeight + "px";
                if ( curHeight != lastHeight ) {
                    $('.mfp-iframe-holder .mfp-content', parentBody).animate({
                        'height': documentHeight
                    }, 500);
                    lastHeight = curHeight;
                    firstAttempt = false;
                }
            }, 500);
        });

        $(document).on('ajaxComplete', function (event, xhr, settings) {
            var parentBody = window.parent.document.body;
            var cartMessage = false;
            var error = false;
            var closeSeconds = parseInt(window.ktpl_quickview.closeSeconds);
            var baseUrl = window.ktpl_quickview.baseUrl;
            var showShoppingCheckoutButtons = parseInt(window.ktpl_quickview.showShoppingCheckoutButtons);
            if (settings.type.match(/get/i) && _.isObject(xhr.responseJSON)) {
                var result = xhr.responseJSON;
                if (_.isObject(result.messages)) {
                    var messageLength = result.messages.messages.length;
                    var message = result.messages.messages[0];
                    if (messageLength) {
                        cartMessage = message.text;
                    }
                }
                if (_.isObject(result.cart) && _.isObject(result.messages)) {
                    var messageLength = result.messages.messages.length;
                    if(messageLength){
                        var selectedMessage = messageLength - 1;
                        var message = result.messages.messages[selectedMessage];
                        var type = message.type;
                        cartMessage = message.text;
                    }
                }

                if (cartMessage) {
                    window.parent.ktpl_quickview.showMiniCartFlag = true;
                }

                if (type == 'error') {
                    error = true;
                    window.parent.ktpl_quickview.showMiniCartFlag = false;
                }

                if (showShoppingCheckoutButtons && cartMessage) {
                    $('<div />').html('').modal({
                        title: cartMessage,
                        autoOpen: true,
                        buttons: [{
                            text: $.mage.__('Continue Shopping'),
                            attr: {
                                'data-action': 'confirm'
                            },
                            'class': 'action primary',
                            click: function () {
                                this.closeModal();
                                $('.mfp-close', parentBody).trigger('click');
                            }
                        },
                        {
                            text: $.mage.__('Go To Checkout'),
                            attr: {
                                'data-action': 'cancel'
                            },
                            'class': 'action primary',
                            click: function () {
                                var checkoutUrl = baseUrl + 'checkout';
                                parent.window.location = checkoutUrl;
                            }
                        }]
                    });
                    $('.modal-popup a').attr('target', '_parent');
                }

                if (!error && closeSeconds && cartMessage) {
                    setTimeout(function(){
                        $('.mfp-close', parentBody).trigger('click');
                    }, closeSeconds * 1000);
                }
            }
        });
    };
});
