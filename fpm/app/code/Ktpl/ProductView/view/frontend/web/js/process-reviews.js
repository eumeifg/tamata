/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'jquery/ui'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     * @param {*} fromPages
     */
    function processReviews(url, fromPages) {
        // start loader
        $('body').trigger('processStart');
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html',
            showLoader: true,
            loaderContext: $('.product.data.items')
        }).done(function (data) {
            //stop loader
            $('body').trigger('processStop');
            $('#product-review-container').html(data).trigger('contentUpdated');
            $('[data-role="product-review"] .pages a').each(function (index, element) {
                $(element).click(function (event) { //eslint-disable-line max-nested-callbacks
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
        }).complete(function () {
            if (fromPages == true) { //eslint-disable-line eqeqeq
                $('html, body').animate({
                    scrollTop: $('#customer-reviews').offset().top - 50
                }, 300);
            }
        });
    }

    return function (config) {
        // var reviewTab = $(config.reviewsTabSelector),
        //     requiredReviewTabRole = 'tab';

        // if (reviewTab.attr('role') === requiredReviewTabRole && reviewTab.hasClass('active')) {
        //     processReviews(config.productReviewUrl, location.hash === '#reviews');
        // } else {
        //     reviewTab.one('beforeOpen', function () {
        //         processReviews(config.productReviewUrl);
        //     });
        // }
         processReviews(config.productReviewUrl);

        $(function () {
            $('.product-info-main .reviews-actions a').click(function (event) {
                var anchor, addReviewBlock;

                event.preventDefault();
                anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
                addReviewBlock = $('#' + anchor);

                if (addReviewBlock.length) {
                    $('.product.data.items [data-role="content"]').each(function (index) { //eslint-disable-line
                        if (this.id == 'reviews') { //eslint-disable-line eqeqeq
                            $('.product.data.items').tabs('activate', index);
                        }
                    });
                    $('html, body').animate({
                        scrollTop: addReviewBlock.offset().top - 50
                    }, 300);
                }

            });
        });
    };
});
