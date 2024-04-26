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
            
            /* Vendor related code starts. */
            
           $("#business-name").blur(function(){
                var msg = "<?php echo __('This Display Name is Available.');?>";
        	var businessnameValue = $("#business-name").val();
                if(businessnameValue != ''){
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
                                                            var successHtml = '<div class="mage-success" id="advice-required-entry-business-name" style="display:block;color:green">'+msg +' </div>';
                                                            $("#business-name").after(successHtml);
                            }else if (result == 'error') {
                                $.validator.addMethod(
                                    'vendor-bussiness-unique',
                                    function (v) {
                                        return false;
                                    },
                                    $.mage.__('This Business Name is already exist. Please try another.')
                                );
                            }
                            $('.btn-submit').attr('disabled', false);
                        }, error: function (error) {
                            $('.btn-submit').attr('disabled', false);
                        }
                    });
                  }     
            });
            
            $('#password, #cpassword').bind("cut copy paste", function (e) {
                    e.preventDefault();
            });
            /* Vendor related code ends. */
        }
    }
);
