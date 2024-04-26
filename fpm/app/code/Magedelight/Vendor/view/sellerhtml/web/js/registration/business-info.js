/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'Magedelight_Theme/js/fancybox',
        'jquery/validate',
        'mage/translate'
    ],
    function ($) {
        'use strict';

        return function (config) {
            
            
            $('.terms-condition').fancybox({
                maxWidth: 800,
                maxHeight: 475,
                fitToView: false,
                width: '70%',
                height: '70%',
                autoSize: false,
                closeClick: false,
                openEffect: 'elastic',
                closeEffect: 'none',
                showCloseButton: true,
                afterShow: function () {
                    $('.fancybox-skin').append('<a title="Close" class="fancybox-item fancybox-close" href="javascript:jQuery.fancybox.close();"></a>');
                }
            });
            
            /* Business related code starts. */
            
            if(config.useVendorAddressAsPickupAddress == 1){
                $('#use-as-vendor').prop('checked', 'checked').trigger('change');
            }
            
            $('#use-as-vendor').change(function () {
                var element = $(this);
                var fields = config.pickupAddressFields;
                if (element.prop('checked')) {
                    for (var i = 0; i < fields.length; i++) {
                        if(fields[i] == 'country'){
                            $('#pickup_' + fields[i]).val($('#' + fields[i]).val()).trigger("change").attr('readonly', true);
                            $('#pickup_region').val($('#region').val()).attr('readonly', true);
                            $('#pickup_region_id').val($('#region_id').val()).attr('readonly', true);
                        }else{
                            $('#pickup_' + fields[i]).val($('#' + fields[i]).val());
                        }
                        $('#pickup_' + fields[i]).attr('readonly', true);
                    }
                } else {
                    for (i = 0; i < fields.length; i++) {
                        $('#pickup_region').val(null);
                        $('#pickup_region').attr('readonly', false);
                        $('#pickup_region_id').val(null);
                        $('#pickup_' + fields[i]).val(null);
                        $('#pickup_' + fields[i]).attr('readonly', false);
                    }
                }
            });
            
            $("#business-name").blur(function(){
            var msg = $.mage.__('This Business Name is Available.');
            var businessnameValue = $("#business-name").val();
            
            if ((!businessnameValue.replace(/\s/g, '').length)){
                    $.mage.__('')
                    return false;        
                }else{
                    if(businessnameValue.length > 150){
                        $.mage.__('')
                        return false;
                    }
                    $('.btn-submit').attr('disabled', true);
                    $('#advice-required-entry-business-name').remove();
                    $('#business-name-error').remove();
                    $.ajax({
                      type: "POST",
                       data: ({business_name: businessnameValue}),
                       url: config.ajaxUrlForUniqueBussinessName,
                        success: function (result) {
                            if (result == 'ok') {
                                $.validator.addMethod(
                                    'vendor-bussiness-unique',
                                    function (v) {
                                        return true;
                                    },
                                    $.mage.__('')
                                );
                                var msg = $.mage.__('This Business Name is Available.');
                                var successHtml = '<div class="mage-success" id="advice-required-entry-business-name" style="display:block;color:green">'+msg +' </div>';
                                $("#business-name").after(successHtml);
                            }else if (result == 'error') {
                                var msg = $.mage.__('This Business Name already exists. Please try another.');
                                $.validator.addMethod(
                                    'vendor-bussiness-unique',
                                    function (v) {
                                        return false;
                                    },
                                    $.mage.__('')
                                );
                                var errorHtml = '<div class="mage-error" id="advice-required-entry-business-name" style="display:block;color:red">'+msg +' </div>';
                                $("#business-name").after(errorHtml); 
                            }
                            $('.btn-submit').attr('disabled', false);
                        }, error: function (error) {
                            $('.btn-submit').attr('disabled', false);
                        }
                    });
                }
                if(businessnameValue){
                    
                  }     
            });
            
                        
            $('#bank-account-number, #cbank-account-number').bind("cut copy paste", function (e) {
                    e.preventDefault();
            });
            /* Business related code ends. */
        }
    }
);
