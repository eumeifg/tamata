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
require(['jquery'], function ($) {
    $('#btn-reset, #reset-category').click(function () {
        $('.items li:nth-child(1)').nextAll().remove();
        $('#category_browse_card_1').find('.active').removeClass(' active');
        $('#category_browse_card_1').nextAll().remove();
    });

    $('#next').bind('click', function (event) {
        event.preventDefault();
        if ($("#category_browse").scrollLeft() == 0) {
            $("#prev").removeClass("disabled");
            $("#next").addClass("disabled");
        }
        var $anchor = $('#category_browse_card_slider').children().last();

        $("#category_browse").stop().animate({
            scrollLeft: $($anchor).offset().left
        }, 500);
    });
    $('#prev').bind('click', function (event) {
        event.preventDefault();
        var $anchor = $('#category_browse_card_slider').children().first();
        $("#next").removeClass("disabled");
        $("#prev").addClass("disabled");
        $("#category_browse").stop().animate({
            scrollLeft: $($anchor).offset().left
        }, 500);
    });
    $(function() {
            $('#category_browse_card_slider').on('click', '.a-spacing-mini', function () {

            categoryId = $(this).attr('category-id');
            dataLevel = $(this).attr('data-level');

            $('.items #level' + dataLevel).nextAll().remove();
            $('.items #level' + dataLevel).remove();
            $('<li id="level' + dataLevel + '" class="item" data-level="' + dataLevel + '"></li>').html('<span>' + $(this).attr('data-browse-path') + '</span>').appendTo('.items');

            $('#category_browse_card_' + (dataLevel - 1)).nextAll().remove();
            $(this).siblings().removeClass(' active');
            $(this).addClass(' active');
            url = $('#data-url').val();
            updateContent($, url, categoryId, dataLevel);
        });
        
            function updateContent($,url,categoryId,dataLevel) {
                var brandId = null;                
                if($('#brand-id').val()) {
                    brandId = $('#brand-id').val();
                }
                
                $.ajax({
                    type: "GET",
                    url: url,
                    data: {"id": categoryId, "level": dataLevel, "brand_id": brandId},
                    dataType: 'json',
                    showLoader: true,
                    success: function (data) {
                        $('#category_browse_card_slider').append(data);
                        var width = $("#category_browse").width();
                        if ($("#category_browse").scrollLeft() == 0) {
                            $("#prev").addClass("disabled");
                        } else {
                            $("#prev").removeClass("disabled");
                        }
                        if((navigator.userAgent.indexOf("MSIE") != -1 ) || (!!document.documentMode == true )){
                            var numItems = $('#category_browse .a-box').length;
                            if (numItems >= 3){
                                $("#category_browse").animate({scrollLeft: 300}, 1500);
                            }
                        }else{
                        $("#category_browse").animate({scrollLeft: width}, 1500);
                    }
                    }
                });
            }
    });
});
