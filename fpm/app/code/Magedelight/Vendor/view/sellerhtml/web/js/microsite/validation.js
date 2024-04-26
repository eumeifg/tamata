/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'mage/translate',
        'jquery/validate'   
    ],
    function ($) {
        'use strict';

        return function (config) {
            
            $.validator.addMethod(
                'validate-image-size',
                function (value, element) {
                    if(value){
                        if ($(element).get(0).files.length) {
                            if ($(element).get(0).files[0].size > $(element).data('max-size')) { /* in bytes */
                                return false;
                            }else{
                                return true;
                            }
                        }
                    }else{
                        return true;
                    }
                },
                $.mage.__('Image size is more than 500KB.')
            );

            $.validator.addMethod(
                'validate-image-type',
                function (value, element) {
                    if(value){
                        if ($(element).get(0).files.length) {
                            if (/\.(jpe?g|png)$/i.test($(element).get(0).files[0].name) === false) {
                                return false;
                            }else{
                                return true;
                            }
                        }
                    }else{
                        return true;
                    }
                },
                $.mage.__('Please use following image types (JPG,JPEG,PNG).')
            );
            
            var _URL = window.URL;
            $("#banner").change(function (e) {
                var file, img;
                if ((file = this.files[0])) {
                    img = new Image();
                    img.onload = function () {
                        if ($( "#banner" ).data( "m-width" ) != this.width || $( "#banner" ).data( "m-height" ) != this.height) { 
                            $( "#banner" ).addClass( "microsite-validate-image-width-height" );
                        }else {
                            $( "#banner" ).removeClass( "microsite-validate-image-width-height" );
                        }
                    };
                    img.src = _URL.createObjectURL(file);
                }
            });
            
            $("#mission-banner").change(function (e) {
                var file, img;
                if ((file = this.files[0])) {
                    img = new Image();
                    img.onload = function () {
                        if ($( "#mission-banner" ).data( "m-width" ) != this.width || $( "#mission-banner" ).data( "m-height" ) != this.height) { 
                            $( "#mission-banner" ).addClass( "microsite-validate-image-width-height" );
                        }else {
                            $( "#mission-banner" ).removeClass( "microsite-validate-image-width-height" );
                        }
                    };
                    img.src = _URL.createObjectURL(file);
                }
            });
                        
            $.validator.addMethod(
                'microsite-validate-image-width-height',
                function () {
                },
                $.mage.__('Image should be in mentioned dimension only!')
            );
    
            $.validator.addMethod(
                'validate-page-title-size',
                function (v) {
                    return /(^.{1,255}$)/.test(v);
                },
                $.mage.__('Please enter less than 255 character for Page Title')
            );
            $.validator.addMethod(
                'validate-twitter-page',
                function (v) {
                    return /(^.{0,255}$)/.test(v);
                },
                $.mage.__('Please enter less than 255 character for Twitter Page.')
            );

            $.validator.addMethod(
                'validate-facebook-page',
                function (v) {
                    return /(^.{0,255}$)/.test(v);
                },
                $.mage.__('Please enter less than 255 character for Facebook Page.')
            );

            $.validator.addMethod(
                'validate-tumblr-page',
                function (v) {
                    return /(^.{0,255}$)/.test(v);
                },
                $.mage.__('Please enter less than 255 character for Tumblr Page.')
            );

            $.validator.addMethod(
                'validate-google-page',
                function (v) {
                    return /(^.{0,255}$)/.test(v);
                },
                $.mage.__('Please enter less than 255 character for Google Page.')
            );

            $.validator.addMethod(
                'validate-insta-page',
                function (v) {
                    return /(^.{0,255}$)/.test(v);
                },
                $.mage.__('Please enter less than 255 character for Instagram Page.')
            );
    
        }
    }
);
