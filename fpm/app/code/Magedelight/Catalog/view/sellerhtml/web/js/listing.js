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
/*jshint browser:true, jquery:true*/
/*global define, clearTimeout, setTimeout*/
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'rbpopup',
    'mage/calendar',
    'mage/translate',
    'domReady!'
], function ($, alert, confirmation) {
    'use strict';

    return function(config){
        
        function toggleCheckBoxes(checkedBoxes, allCheckBoxes, selectAllElement){
            console.log(checkedBoxes.length +'//'+allCheckBoxes.length);
            if (checkedBoxes.length < allCheckBoxes.length){
                selectAllElement.prop('checked', false);
            }else if(checkedBoxes.length === allCheckBoxes.length){
                selectAllElement.prop('checked', true);
            }
        }
        
        $(function() {
            $('.fancyboxnonlivevendor').fancybox({
                type: 'iframe',
                fitToView: false,
                autoSize: false,
                closeClick: false,
                openEffect: 'elastic',
                closeEffect: 'none',
                showCloseButton: true,
                afterShow: function () {
                    $('.fancybox-skin').append('<a title="Close" class="fancybox-item fancybox-close" href="javascript:jQuery.fancybox.close();"></a>');
                }
            });
        });
        
        $(function() {
            $('.fancybox-listing-quick-edit').fancybox({
                type: 'iframe',
                fitToView: false,
                autoSize: false,
                closeClick: false,
                openEffect: 'elastic',
                closeEffect: 'none',
                showCloseButton: true,
                afterShow: function () {
                    $('.fancybox-skin').append('<a title="Close" class="fancybox-item fancybox-close" href="javascript:jQuery.fancybox.close();"></a>');
                }
            });
        });
        
        $("#vendor_mass_list_checkall").change(function () {
            $(".vendor_mass_list").prop('checked', $(this).prop("checked"));
        });
        
        $("#vendor_mass_unlist_checkall").change(function () {
            $(".vendor_mass_unlist").prop('checked', $(this).prop("checked"));
        });
            
        $(".vendor_mass_unlist").change(function () {
            toggleCheckBoxes(
                $('#my-mylisting-table .vendor_mass_unlist:checked'),
                $('#my-mylisting-table .vendor_mass_unlist'),
                $("#vendor_mass_unlist_checkall")
            );
        });
        
        $(".vendor_mass_list").change(function () {
            toggleCheckBoxes(
                $('#my-mylisting-table-nl .vendor_mass_list:checked'),
                $('#my-mylisting-table-nl .vendor_mass_list'),
                $("#vendor_mass_list_checkall")
            );
        });
        
        $(".reset-nonlive").click(function () {
            $('#session-clear-product-nonlive').val(1);
            $('#search-catalog-input').val('');
            $('#non-live-search-form').submit();
        });
        
        $(".reset-live").click(function () {
            $('#session-clear-product-live').val(1);
            $('#vendor_search_catalog').val('');
            $('#live-search-form').submit();
        });
        
        function performMassAction(tableId, massActionElement, formId, alertMessage, checkBoxesClass){
            if ($(tableId+' '+checkBoxesClass+':checked').length === 0) {
                alert({
                    title: '',
                    content: $.mage.__('You have not selected any items!'),
                    actions: {
                        always: function () {
                            $(massActionElement).val("");
                        }
                    }
                });
            } else if ($(massActionElement).val() != undefined && $(massActionElement).val() != "") {
                confirmation({
                    title: '',
                    content: alertMessage,
                    actions: {
                        confirm: function () {
                            $(formId).submit();
                        },
                        cancel: function () {
                            $(massActionElement).val("");
                        }
                    },
                    buttons: [{
                            text: $.mage.__('OK'),
                            class: 'action secondary action-accept',
                            /**
                             * Click handler.
                             */
                            click: function (event) {
                                this.closeModal(event, true);
                            }
                        }, {
                            text: $.mage.__('Cancel'),
                            class: 'action secondary action-dismiss',
                            /**
                             * Click handler.
                             */
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }]
                });
            }
        }
        
        $(".mass-list-live").change(function (e) {
            e.preventDefault();
            performMassAction('#my-mylisting-table-nl', '.mass-list-live', "#mass_list_form", config.nonlive_alertMessage, '.vendor_mass_list');
            return false;
        });
        
        $(".mass-unlist-live").change(function (e) {
            e.preventDefault();
            performMassAction('#my-mylisting-table', '.mass-unlist-live', "#mass_unlist_form", config.live_alertMessage, '.vendor_mass_unlist');
            return false;
        });
    }
});