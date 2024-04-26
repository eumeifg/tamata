define(
[
    'ko',
    'jquery',   
    'Magento_Customer/js/model/customer',
    'mage/translate'
],
function (ko,jquery, customer,$t) {
    'use strict';
    return Component.extend({        
        isCustomerLoggedIn: customer.isLoggedIn, /*return  boolean true/false */
        initialize: function() {
            console.log(isCustomerLoggedIn);
            this._super();
            var isLoggedIn = this.isCustomerLoggedIn();
        }
    });
});
