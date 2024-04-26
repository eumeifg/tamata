/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'jquery/ui',
    'mage/translate'
], function (_, registry, Select, defaultPostCodeResolver, $) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.country_id:value',
                update2: '${ $.parentName }.region_id:value'
            }
        },

        /**
         * @param {String} value
         */
        update2: function (regionId) {
            var string = JSON.stringify($eaCitiesJson),
                obj = JSON.parse(string),
                romania = obj.RO,
                romanianRegions,
                parentCity,
                currentRegionCities,
                regions = JSON.parse(window.checkoutConfig.cities),
                romanianRegions = obj.city;

            if(romanianRegions === undefined){

                this.hide();

                return romanianRegions;
            }

            if (regions && regions[regionId] && regions[regionId].length) {
                var cities = regions[regionId];

                var options = cities.map(function (city) {
                    return {title: city, value: city, labeltitle: city, label: city}
                })
            }
            console.log(options);
            parentCity = $("[name ='shippingAddress.city']");

            if (!options || !options.length) {
                this.visible(false);
                this.value(null);
            }

            if (options && options.length) {
                options = [{title: "", value: "", label: $.mage.__('Select the city')}].concat(options);
                this.visible(true);

                if(this.imports.city){

                    var cityValue = registry.get(this.imports.city).value();
                    if (!this.value() && cityValue) {
                        this.value(cityValue)
                    }
                }
            }

            this.options(options);
        },
        update: function (regionId) {
            var string = JSON.stringify($eaCitiesJson),
                obj = JSON.parse(string),
                romania = obj.RO,
                romanianRegions,
                parentCity,
                currentRegionCities,
                regions = JSON.parse(window.checkoutConfig.cities),
                romanianRegions = obj.city;
            regionId = $("[name ='shippingAddress.region_id']").val();
            if(regionId === undefined){
             regionId = Object.keys(regions)[0];
             $("[name ='shippingAddress.region_id']").val(regionId);
             console.log(regionId);
            }
            if(romanianRegions === undefined){

                this.hide();

                return romanianRegions;
            }

            if (regions && regions[regionId] && regions[regionId].length) {
                var cities = regions[regionId];

                var options = cities.map(function (city) {
                    return {title: city, value: city, labeltitle: city, label: city}
                })
            }
            console.log(options);
            parentCity = $("[name ='shippingAddress.city']");

            if (!options || !options.length) {
                this.visible(false);
                this.value(null);
            }

            if (options && options.length) {
                options = [{title: "", value: "", label: $.mage.__('Select the city')}].concat(options);
                this.visible(true);

                if(this.imports.city){

                    var cityValue = registry.get(this.imports.city).value();
                    if (!this.value() && cityValue) {
                        this.value(cityValue)
                    }
                }
            }

            this.setOptions(options);
        }
    });
});
