define(
    [
        'jquery',
        'ko',
        'uiComponent',        
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery/ui',
    ],
    function ($, ko, Component, quote, rateReg, urlBuilder, errorProcessor, urlFormatter, fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MDC_ProvinceCenter/address_type'
            },


        initObservable: function () {

            /*As address_type can be changed any time by cutomer, Have to refresh shipping rate incase address selection changed after address_type updated, Because magento only refresh address wise shipping rates first time only after that it applies from cache.*/
            $(document).on("click", 'button.action.action-select-shipping-item', function() {
                var address = quote.shippingAddress();
                rateReg.set(address.getKey(), null);
                rateReg.set(address.getCacheKey(), null);
                quote.shippingAddress(address); 
            });


            var quoteId = quote.getQuoteId();
            var getUrl = urlBuilder.createUrl('/carts/mine/get-addresstype', {});
                var getResult = 1;
                var getPayload = {
                        cartId: quoteId,                
                    };

                $.ajax({
                    showLoader: true,
                    url: urlFormatter.build(getUrl),
                    data: JSON.stringify(getPayload),
                    global: false,
                    contentType: 'application/json',
                    type: 'GET',
                    async: false,
                }).done(
                    function (response) {
                        if(response){
                            getResult = "1";
                        }else{
                            getResult = "0";
                        }                        
                    }
                ).fail(
                    function (response) {
                        getResult = "0";
                    }
                );

            var checkValue = getResult;

            this._super()
                .observe({
                    saveAddressType: ko.observable(checkValue)    
                });

            this.saveAddressType.subscribe(function (newValue) {
               
                var addressTypeValue = newValue;
                var url;
                url = urlBuilder.createUrl('/carts/mine/set-addresstype', {});
                var result = true;
                var payload = {
                    cartId: quoteId,
                    addressType: {
                        addressType: addressTypeValue
                    }
                };

                fullScreenLoader.startLoader();
                $.ajax({
                    showLoader: true,
                    url: urlFormatter.build(url),
                    data: JSON.stringify(payload),
                    global: false,
                    contentType: 'application/json',
                    type: 'PUT',
                    async: false,
                }).done(
                    function (response) { 

                    var address = quote.shippingAddress();
                    rateReg.set(address.getKey(), null);
                    rateReg.set(address.getCacheKey(), null);
                    quote.shippingAddress(address); 

                    fullScreenLoader.stopLoader();
                    result = true;

                    }
                ).fail(
                    function (response) {
                        result = false;
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
                return result;
            });
            return this;
        }

    });
    }
);