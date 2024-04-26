require([
    'jquery',    
], function ($) {
    'use strict';

    $(document).ready(function(){
         
        jQuery('div[data-component="ktpl_pushnotification_form_customer_view.ktpl_pushnotification_form_customer_view"]').parent().addClass('customer_view_notification_form');

        jQuery('.customer_view_notification_form').hide();

        var promotionType = "";

            jQuery('body').on('click', 'ul.admin__page-nav-items.items li a', function() {
                
                var tabLinkId = $(this).attr('id');
               
                if(tabLinkId === "tab_block_customer_edit_send_pushnotification"){

                    jQuery('.customer_view_notification_form').show();

                    jQuery(".fieldset-wrapper.customer-information.send-pushnotification").appendTo(".customer_view_notification_form");

                    $('select[name="type_promotion"]').change(function () {
                        promotionType =  $(this).val();
                        
                    });

                }else{
                    jQuery('.customer_view_notification_form').hide();
                }

            });



        jQuery('body').on('click', '#send_notification', function() {

            var actionUrl = $("input[name=sendnotificationurl]").val();
            var notificaionTitle = $("input[name=title]").val();
            var notificationDescription =  $("textarea[name='description']").val();
            var promotionId = $("input[name=promotion_id]").val();
            var sendToCustomer = $("input[name=send_to_customer]").val();
            var imageUrl = $(".preview-image").attr('src');
                

            var $inputs =  $('input[name=title],select[name="type_promotion"],textarea[name="description"]');
            var isValid = true;
             
            $inputs.each(function() {
                if ($(this).val() == '' || $(this).val() === 'none') {
                     isValid = false;
                }
            });

            if(!isValid){
                alert("Please enter/select a value for the Required fields.");
            }else{

                $.ajax({
                    url: actionUrl, 
                    showLoader: true,
                    data: { form_key: window.FORM_KEY,title: notificaionTitle,promotion_id: promotionId,description: notificationDescription,send_to_customer: sendToCustomer, send_to :'customer', image_url: imageUrl, type_promotion: promotionType },
                    type: "POST",
                    dataType: 'json',
                        success: function(result){   
                             
                            if (result.status) {
                                location.reload();
                            }
                            
                        },
                        error: function(error) {
                           if (!error.status) {
                                location.reload();
                            }
                        }
                });
            }                        
        });          
    });
});