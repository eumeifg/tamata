/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 &&
            $('#co-shipping-method-form .fieldset.rates :checked').length === 0
        ) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
        if ($('.page-main .cart-empty').length) {
            $('.page-main').css('padding', '0 15px 0 15px');
        }
    }

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });


    if ($("body").hasClass("catalog-product-view")) {
        $('.review-right a.add, .reviews-actions a.add').on("click", function () {
            $('.form-review').show();
            $('#tab-label-reviews').trigger('click');
        });
        var reviewString = 'review-form';
        var currentURL = window.location.href;
        if (currentURL.indexOf(reviewString) != -1) {
            $('.form-review').show();
            $('#tab-label-reviews').trigger('click');
            var reviewlist = $('.review-list');
            if (reviewlist.length) {
                $('html, body').animate({
                    scrollTop: $(".review-list").offset().top
                }, 1);
            }
        }
    }


    $("#toggle").click(function () {
        var elem = $("#toggle").text();
        if (elem == "Read More") {
            //Stuff to do when btn is in the read more state
            $("#toggle").text("Read Less");
            $("#hide").show();
        } else {
            //Stuff to do when btn is in the read less state
            $("#toggle").text("Read More");
            $("#hide").hide();
        }
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');

    /* Mobile menu script */
    $('.navigation .parent > .link-arrow .mobile').on('click', function (e) {
        e.preventDefault();
        var menuLevels = ["level0", "level1", "level2", "level3"];
        var outerThis = this;
        menuLevels.forEach(function (item) {
            if ($(outerThis).closest('li').hasClass(item)) {
                $('.' + item).not($(outerThis).closest('li')).removeClass('open');
            }
        });
        $(this).parent().parent().toggleClass('open');
    });

    keyboardHandler.apply();

    $('.megamenu-content .megamenu-container ul.level0 > li.level1').each(function () {
        $(this).parent().find('li.level1:first-child').addClass('hover');

        $(this).hover(function () {
            $(this).parent().find('li.level1').removeClass('hover');
            $(this).addClass('hover');
        });
    });

    /* Layered Navigation */

    if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
        || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
        $('html').click(function () {
            $('.toolbar-sorter').removeClass('active');
            $('.filter-overlay').removeClass('active');
            $('body').removeClass('filter-visible');
        })

        $('.toolbar-sorter').click(function (e) {
            e.stopPropagation();
        });

        $('.sorter-data:before').click(function () {
            $('.toolbar-sorter').removeClass('active');
        });

        $('.sorter-label').click(function (e) {
            $(this).parent().toggleClass('active');
            $('.filter-overlay').addClass('active');
            $('body').addClass('filter-visible');
        });

        $(document).on('click', '.sorter-label', function () {
            $(this).parent().toggleClass('active');
            $('.filter-overlay').addClass('active');
            $('body').addClass('filter-visible');
        });

        $(document).on("click touch", ".sorter-label", function (e) {
            e.stopPropagation();
            $(this).parent().toggleClass('active');
            $('.filter-overlay').addClass('active');
            $('.toolbar-sorter').addClass('active');
            $('body').addClass('filter-visible');
        });
    }

    // Hide layered navigation block if category doesn't have any product.
    if ($('.catalog-category-view').length && $('.message.info.empty').length) {
        $('#layered-filter-block').css('display', 'none');
    }

   $(window).ready(function() {
       $(window).trigger('resize');
   });
});
