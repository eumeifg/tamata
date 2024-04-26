require([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'mage/calendar',
    'mage/mage'
], function($,modal,urlBuilder){
$(document).ready(function() {

    // var dataForm = $('#credit_settle_form');
    // dataForm.mage('validation', {});
    // $("#credit_settle_form").attr("data-mage-init", '{"validation":{}}');

     var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Payment Details',
            closeText: 'Close',
            buttons: [

             {
                text: $.mage.__('Cancel'),
                class: 'action-secondary action-dismiss',
                click: function () {
                    this.closeModal();
                }
            },
            
            {
                text: $.mage.__('Proceed'),
                class: 'action-primary modal_proceed',
                click: function () {
                    
                    var settlePoUrl = $("input[name=dosettlepourl]").val();

                    var debit_checked = [];
                    $("input[name='selected_debit_ids[]']:checked").each(function ()
                    {
                        debit_checked.push(parseInt($(this).val()));
                    })
                    
                    var credit_payment_id = $("input[name=credit_payment_id]").val(); 
                    var credit_purchase_order_id = $("input[name=credit_purchase_order_id]").val(); 
                    var vendor_id = $("input[name=vendor_id]").val(); 

                    var bank_transaction_id = $("input[name=bank_transaction_id]").val(); 
                    var settled_transction_amount = $("input[name=settled_transction_amount]").val(); 
                    var bank_transaction_date = $("input[name=bank_transaction_date]").val(); 
                    
                    /* validation */
                    var $inputs =  $('#bank_transaction_id, #settled_transction_amount, #bank_transaction_date');

                   // filter the inputs that have value and get length of result
                   var hasValueCount = $inputs.filter(function(){
                          return $(this).val();
                       }).length;

                   // only valid if none selected or all selected
                   var isValid = !hasValueCount || hasValueCount === $inputs.length;
                    /* validation */

                   if(!isValid){
                       
                       alert(" Please enter the valid values for all the field of Bank Transaction Detail.");
                   } else{
                        
                        //$('#credit_settle_form').submit();

                    $.ajax({
                        url: settlePoUrl, 
                        showLoader: true,
                        data: { form_key: window.FORM_KEY, credit_payment_id: credit_payment_id,credit_purchase_order_id: credit_purchase_order_id,vendor_id: vendor_id, selected_debit_ids: debit_checked, bank_transaction_id: bank_transaction_id, settled_transction_amount : settled_transction_amount,  bank_transaction_date: bank_transaction_date},
                        type: "POST",
                        dataType: 'json',
                            success: function(result){   
                                $("#vendor_modal_container").modal("closeModal");
                                location.reload();
                  
                            },
                            error: function(error) {
                               $("#vendor_modal_container").modal("closeModal");
                               location.reload();
                            }
                    });

                   }
                
                }
            },
                    
            ]
        };

         $("body").delegate("#bank_transaction_date", "focusin", function(){           
                $('#bank_transaction_date').calendar({
                    maxDate: new Date(),
                    // show date
                    dateFormat:'Y-m-d',
                    changeYear: true,
                    changeMonth: true,
                    buttonText: 'Select Date',                    
                    // show time as well
                    timeInput: true,
                    timeFormat: 'HH:mm:ss',
                    showsTime: true,                    
                });
        });

    var popup = modal(options, $('#vendor_modal_container'));

        $('body').on('click', 'td.load_payment a', function() {
            
            var ajaxUrl = $("input[name=markpayadjusturl]").val();
            var payment_link = $(this).attr('href').split('#');
            var vndr_pymnt_id = payment_link[1]; 
            var tt_type = $.trim($(this).closest('tr').find('.transaction_type').text());  
             
            $.ajax({
                url: ajaxUrl, 
                showLoader: true,
                data: { payment_id : vndr_pymnt_id, transaction_type : tt_type},
                type: "POST",
                dataType: 'json',
                success: function(result){
                    $(".heading_content").html(result);                    
                    $("#vendor_modal_container").modal("openModal");
                },
                complete: function(data) {
                   
                }
            });
            
            //$("#vendor_modal_container").modal("openModal");

            return false;
                
        });         

        $('body').on('change', '.debit_check', function() {
            
            var total_payable = parseFloat( $("input[name=total_payable]").val(),10 );
           
            var totalDebit = 0;

            $('.debit_check:checked').each(function () {
                totalDebit += parseFloat($(this).data('price'), 10);
            });
            
            var updated_payable = (total_payable + totalDebit).toFixed(2); 

            var final_payable = updated_payable.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
 
            $('#changed_payable').html(final_payable);
            $('input[name=settled_transction_amount]').val(updated_payable);

        });
        
       
    // $('body').on('click', 'button.action-primary.modal_proceed', function() {
      
             
    //      var $inputs =  $('#bank_transaction_id, #settled_transction_amount, #bank_transaction_date');

    //        // filter the inputs that have value and get length of result
    //        var hasValueCount = $inputs.filter(function(){
    //               return $(this).val();
    //            }).length;

    //        // only valid if none selected or all selected
    //        var isValid = !hasValueCount || hasValueCount === $inputs.length;

    //        if(!isValid){
               
    //            alert(" Please enter all the fields for Bank Transaction Detail.");
    //        } else{
                
    //             $('#credit_settle_form').submit();
    //        }
            
    //     }); 

    });
});