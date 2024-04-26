/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

var config = {
    paths: {
        'ktpl/bannermanagement/jquery/popup': 'Ktpl_BannerManagement/js/jquery.magnific-popup.min',
        'ktpl/bannermanagement/owl.carousel': 'Ktpl_BannerManagement/js/owl.carousel.min',
        'ktpl/bannermanagement/bootstrap': 'Ktpl_BannerManagement/js/bootstrap.min',
        IonRangeSlider: 'Ktpl_BannerManagement/js/ion.rangeSlider.min',
        touchPunch: 'Ktpl_BannerManagement/js/jquery.ui.touch-punch.min',
        DevbridgeAutocomplete: 'Ktpl_BannerManagement/js/jquery.autocomplete.min'
    },
    shim: {
        "ktpl/bannermanagement/jquery/popup": ["jquery"],
        "ktpl/bannermanagement/owl.carousel": ["jquery"],
        "ktpl/bannermanagement/bootstrap": ["jquery"],
        IonRangeSlider: ["jquery"],
        DevbridgeAutocomplete: ["jquery"],
        touchPunch: ['jquery', 'jquery/ui']
    }
};
