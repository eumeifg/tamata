/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

define([
        'jquery'
    ],
    function ($) {
        'use strict';

        return function (config) {
            
        /* For indeterminate property start */
            function checkSiblings(el, checked) {
                var parent = el.parent().parent(),
                all = true;
                el.siblings().each(function() {
                    return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
                });
                if (all && checked) {
                    parent.children('input[type="checkbox"]').prop({
                        indeterminate: false,
                        checked: checked
                    });
                    checkSiblings(parent, checked);

                } else if (all && !checked) {
                    parent.children('input[type="checkbox"]').prop("checked", checked);
                    parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
                    checkSiblings(parent, checked);
                } else {
                    el.parents("li").children('input[type="checkbox"]').prop({
                        indeterminate: true,
                        checked: false
                    });
                }
            }
            
            $(function() {
                $('#product-selling-categories').find('.selectall').each(function () {
                    var check; check = 0;
                    $(this).parent().find('input[type=checkbox]').each(function () {
                        if (!$(this).hasClass('selectall') && !$(this).is(':checked')) {
                            check = 1;
                        }
                    });
                    if(check == 0) { $(this).prop('checked', true); }
                });
                
                $('#product-selling-categories').find('.selectall').each(function () {
                    $(this).parent().find('input[type=checkbox]').each(function () {
                        if ($(this).hasClass('selectall') && !$(this).prop('checked')) {
                            $(this).prop('indeterminate', ($(this).parent().find('ul > li').children('input[type="checkbox"]').filter(':checked').length != $(this).children('input[type="checkbox"]').length));
                        }
                    });
                });
                
                $('#product-selling-categories').find('input[type="checkbox"]').change(function(e) {
                    var checked = $(this).prop("checked"),
                    container = $(this).parent(),
                    siblings = container.siblings();
                    container.find('input[type="checkbox"]:not(:disabled)').prop({
                      indeterminate: false,
                      checked: checked
                    });
                    checkSiblings(container, checked);
                });
                
                $('#product-selling-categories .category-ul.submenu').css('display', 'none');
                $(document).on('click', '.category-ul li.has-children > label.cat-collapse, .selectall-cat-toggle-btn', function () {
                    $(this).parent('li').toggleClass('expand');
                    $(this).siblings('.category-ul').slideToggle(800);
                });
            });
            /* For indeterminate property end */
        }
    }
);
