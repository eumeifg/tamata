define(
    [
        'jquery',
        'ko',
        'uiComponent',        
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery/ui'
    ],
    function ($, ko, Component, quote,  urlBuilder, errorProcessor, urlFormatter, fullScreenLoader) {
        'use strict';

       
        return Component.extend({
            defaults: {
                template: 'MDC_GetItTogether/get_it_together'
            },

            initObservable: function () {

                var quoteId = quote.getQuoteId();

                var getUrl = urlBuilder.createUrl('/carts/mine/get-order-getittogether', {});
                var getResult = false;
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
                            getResult = true;
                        }                        
                    }
                ).fail(
                    function (response) {
                        getResult = false;
                    }
                );
            var checkValue = getResult;

            this._super()
                .observe({
                    saveGetItTogether: ko.observable(checkValue)        
                });

          

            this.saveGetItTogether.subscribe(function (newValue) {
                 if(newValue){
                    var selectGetItTogether = 1;
                }else{
                    var selectGetItTogether = 0;
                }
                
                var url;

                url = urlBuilder.createUrl('/carts/mine/set-order-getittogether', {});

                var result = true;

                var payload = {
                    cartId: quoteId,
                    getItTogether: {
                        getItTogether: selectGetItTogether
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