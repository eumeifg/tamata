/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
var config = {
    map: {
        '*': {
            googleMapAddress: 'Ktpl_GoogleMapAddress/js/google_address',
            'Magento_Checkout/js/view/shipping': 'Ktpl_GoogleMapAddress/js/view/shipping',
            'Ktpl_CheckoutAccordion/template/module-checkout/shipping.html': 'Ktpl_GoogleMapAddress/template/shipping.html',
            autoComplete: 'Ktpl_GoogleMapAddress/js/autocomplete'
        }
    },
    shim: {
        'googleMapAddress': {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            'Ktpl_GoogleMapAddress/js/view/shipping': {
                'Ktpl_GoogleMapAddress/js/mixin/shipping-mixin': true
            }
        }
    }
};
