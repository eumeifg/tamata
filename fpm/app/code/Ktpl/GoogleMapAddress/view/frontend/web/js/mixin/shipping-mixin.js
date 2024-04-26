define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    return function (target) {
        return target.extend({

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                var shippingMethod = quote.shippingMethod();
                /*....To validate location field on checkout page....*/
                if ($("#location").val() == '' && $('#location').is(":visible") ) {
                    this.focusInvalid();
                    $( ".input-text.location" ).focus();
                    $( "#location" ).addClass("_error");
                    $("#custom_validation_msg").html('<div id="location_error" class="mage-error" generated="true">'+$.mage.__('This is a required field.')+'</div>');
                    /*var parentContainer = $('#location').parent('div');
                    $('<div/>', {
                            id: 'location_error',
                            "class": 'mage-error',
                            generated: 'true',
                            html: $.mage.__('This is a required field.')
                        }).appendTo(parentContainer);*/

                    return false;
                } else {
                    $( "#location" ).removeClass("_error");
                    $('#location_error').remove();
                }

                /*....To validate city field on checkout page....*/
                if($('select[name=city]').val() == null) {
                    $( "select[name=city]" ).focus();
                    return false;
                } else {
                    $( "select[name=city]" ).removeClass("_error");
                    //return true;
                }
                this._super();
            },

            /**
             * Save new shipping address
             */
            saveNewAddress: function () {
                var addressData,
                    newShippingAddress;

                /*....To validate location field on logged-in add new address popup....*/
                if ($("#location").val() == '' && $('#location').is(":visible") ) {
                    this.focusInvalid();
                    $( ".input-text.location" ).focus();
                    $( "#location" ).addClass("_error");
                    $("#custom_validation_msg").html('<div id="location_error" class="mage-error" generated="true">'+$.mage.__('This is a required field.')+'</div>');
                    /*var parentContainer = $('#location').parent('div');
                    $('<div/>', {
                            id: 'location_error',
                            "class": 'mage-error',
                            generated: 'true',
                            html: $.mage.__('This is a required field.')
                        }).appendTo(parentContainer);*/

                    return false;
                } else {
                    $( "#location" ).removeClass("_error");
                    $('#location_error').remove();
                }

                /*....To validate city field on logged-in add new address popup....*/
                if($('select[name=city]').val() == null) {
                    $( "select[name=city]" ).focus();
                    return false;
                } else {
                    $( "select[name=city]" ).removeClass("_error");
                    //return true;
                }
                this._super();
            }
        });
    }
});