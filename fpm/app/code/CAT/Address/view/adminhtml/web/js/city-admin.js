define([
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'jquery/ui'
], function ($) {
    'use strict';
    return function () {

        var string = JSON.stringify($eaCitiesJson),
            obj = JSON.parse(string),
            cityInput = $("[name*='city']").val();

        $(document).ready(function (){

            $.each($("[name*='region_id']"),function(){
                var region_id = $(this).val();
                var regionName = this.name;                var cityInputName = regionName.replace("region_id", "city");
                var region = [];

                if (region_id) {
                    $.each(obj, function (index, value) {
                        if (value.region_id == region_id) {
                            region.push(value.city_name);
                        }
                    });
                    var city = $("[name*='" + cityInputName + "']");
                    if(!city.is("select")){

                        var selectCity = city.replaceWith("<select class='required-entry select admin__control-select' name='"+cityInputName+"' id='city'>") + '</select>';
                    }
                    var htmlSelect = '',
                        options;

                    $.each(region, function (index, value) {
                        if ( value == cityInput) {
                            options = '<option value="' + value + '" selected>' + value + '</option>';
                        } else {
                            options = '<option value="' + value + '">' + value + '</option>';
                        }

                        htmlSelect += options;
                    });

                    $("select[name*='" + cityInputName + "']").html(htmlSelect);
                }
            })

        });

        $(document).on('change', "[name*='region_id']", function () {

            var region_id = $(this).val(),
                regionName = this.name,
                cityInputName = regionName.replace("region_id", "city"),
                region = [];

            if (region_id) {
                $.each(obj, function (index, value) {
                    if (value.region_id == region_id) {
                        region.push(value.city_name);
                    }
                });
                var city = $("[name*='" + cityInputName + "']");
                if(!city.is("select")){

                    var selectCity = city.replaceWith("<select class='required-entry select admin__control-select' name='"+cityInputName+"' id='city'>") + '</select>';
                }
                var htmlSelect = '',
                    options;

                $.each(region, function (index, value) {
                    options = '<option value="' + value + '">' + value + '</option>';
                    htmlSelect += options;
                });

                $("select[name*='" + cityInputName + "']").html(htmlSelect);
            }

        });
    };
});
