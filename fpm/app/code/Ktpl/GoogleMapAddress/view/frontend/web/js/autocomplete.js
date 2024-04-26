define([
    'jquery',
    'uiComponent',
    'Ktpl_GoogleMapAddress/js/google_maps_loader',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/url'
], function (
    $,
    Component,
    GoogleMapsLoader,
    checkoutData,
    uiRegistry
) {

    var componentForm = {
        subpremise: 'short_name',
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'long_name',
        country: 'short_name',
        postal_code: 'short_name',
        postal_code_suffix: 'short_name',
        postal_town: 'short_name',
        sublocality_level_1: 'short_name'
    };

    var lookupElement = {
        street_number: 'street_1',
        route: 'street_2',
        locality: 'city',
        administrative_area_level_1: 'region',
        country: 'country_id',
        postal_code: 'postcode'
    };

    var marker = '';
    var map = '';
    var markers = [];

    var googleMapError = false;
    window.gm_authFailure = function() {
        googleMapError = true;
    };


    GoogleMapsLoader.done(function () {

        var enabled = window.checkoutConfig.google_map_address.active;

        var geocoder = new google.maps.Geocoder();
        setTimeout(function () {
            if(!googleMapError) {
                if (enabled == '1') {
                    var domID = "location";
                    var location = $("#" + domID);
                    //SHQ18-260
                    var observer = new MutationObserver(function () {
                        observer.disconnect();
                        $("#" + domID).attr("autocomplete", "new-password");
                    });

                    location.each(function () {
                        var element = this;

                        observer.observe(element, {
                            attributes: true,
                            attributeFilter: ['autocomplete']
                        });

                        autocomplete = new google.maps.places.Autocomplete(
                            /** @type {!HTMLInputElement} */(this),
                            {types: ['geocode']}
                        );

                        autocomplete.setComponentRestrictions(
                            {'country': ['iq']});

                        autocomplete.addListener('place_changed', fillInAddress);

                    });
                }
            }

            /*get pickup points locations added by admin and diisplay multiple locations on Map */
            
            var pickupLocationsObject = window.checkoutConfig.pickup_points_provider.locations;

            var pikcupIcon = window.checkoutConfig.pickup_points_provider.tamta_icon;

            var icon = {
                url: pikcupIcon, // url
                scaledSize: new google.maps.Size(25, 25), // scaled size
                origin: new google.maps.Point(0,0), // origin
                anchor: new google.maps.Point(0, 0) // anchor
            };
           
            var pickupLocations = Object.values(pickupLocationsObject);

            if(pickupLocations.length > 0){

                 
                // Add multiple markers to map
                var infoWindow = new google.maps.InfoWindow(), marker, i;
                var bounds = new google.maps.LatLngBounds();
                var mapOptions = {
                    mapTypeId: 'roadmap',
                    zoom: 16
                };

                map = new google.maps.Map(
                    document.getElementById('google_map'), mapOptions);
                map.setTilt(50);
                for (var i = 0; i < pickupLocations.length; i++) {
                    
                    var lats = pickupLocations[i].lat;
                    var longs = pickupLocations[i].long;

                    var position = new google.maps.LatLng(lats, longs);
                    bounds.extend(position);
                    marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        icon: icon
                    });
                    

                    // Add info window to marker    
                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                        return function() {
                            // infoWindow.setContent(infoWindowContent[i][0]);
                            var contentInfo = '<div class="info_content">' +
                            '<h3>'+pickupLocations[i].location+'</h3>' +
                            '<p>'+pickupLocations[i].addressline+'</p>' + '</div>';
                            infoWindow.setContent(contentInfo);  

                            $("input[name='location_shipping']").val(pickupLocations[i].location);
                            // $("input[name='street[0]']").val(pickupLocations[i].location).keyup();
                            // $("input[name='street[1]']").val(pickupLocations[i].addressline);
                            var pickupAddressLine = pickupLocations[i].location+','+pickupLocations[i].addressline;
                            $("input[name='street[0]']").val(pickupAddressLine).keyup();                            
                            
                            var pickupCity = pickupLocations[i].city;
                            

                            var cityDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.city').uid;
                                if ($('#'+cityDomID)) {
                                    
                                    $('#'+cityDomID).val(pickupCity);
                                    $('#'+cityDomID +' option')
                                        .filter(function () {
                                            return $.trim($(this).text()) == pickupCity;
                                        })
                                        .attr('selected',true);
                                    $('#'+cityDomID).trigger('change');
                                }


                            /*Update Lat long of slected pikcup point marker*/

                            /* Updates lat and lng position of the marker. */
                                // marker.position = marker.getPosition();

                                // Get lat and lng coordinates.
                                var lat = pickupLocations[i].lat;
                                var lng = pickupLocations[i].long;
                                
                                var latDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.latitude').uid;
                                if ($('#'+latDomID)) {
                                    $('#'+latDomID).val(lat);
                                    $('#'+latDomID).trigger('change');
                                }
                                var lngDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.longitude').uid;
                                if ($('#'+lngDomID)) {
                                    $('#'+lngDomID).val(lng);
                                    $('#'+lngDomID).trigger('change');
                                }

                                // var isAddresPickupLocationID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.is_pickup_address').uid;
                                // if ($('#'+isAddresPickupLocationID)) {
                                //     $('#'+isAddresPickupLocationID).val(true);
                                //     $('#'+isAddresPickupLocationID).trigger('change');
                                // }

                                $("input[name='custom_attributes[is_pickup_address]']").prop('checked', true).triggerHandler('click');

                                /* Update lat and lng values into text boxes. */

                                // getCoords(lat, lng);
                            /*Update Lat long of slected pikcup point marker*/
                            
                            infoWindow.open(map, marker);
                        }

                    })(marker, i));

                    // Center the map to fit all markers on the screen
                    map.fitBounds(bounds);
                }
                /*get pickup points locations added by admin and diisplay multiple locations on Map */
            }else{

                var baghdad = {lat: 33.312805, lng: 44.361488};
                map = new google.maps.Map(
                    document.getElementById('google_map'), {zoom: 16, center: baghdad});
                marker = new google.maps.Marker({position: baghdad, map: map});

                markers.push(marker);

                map.addListener('click', function(e) {
                    setMapOnAll(null);
                    placeMarker(e.latLng, map);
                });

            }
            
        }, 5000);

    }).fail(function () {
        console.error("ERROR: Google maps library failed to load");
    });

    var fillInAddress = function () {

        // $("input[name='custom_attributes[is_pickup_address]']").prop('checked', false).trigger('change');
        // $("input[name='custom_attributes[is_pickup_address]']").removeAttr('checked').trigger('change');
        $("input[name='custom_attributes[is_pickup_address]']").prop('checked', false).triggerHandler('click');

        // var isAddresPickupLocationID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.is_pickup_address').uid;
        // if ($('#'+isAddresPickupLocationID)) {
        //     $('#'+isAddresPickupLocationID).val(false);
        //     $('#'+isAddresPickupLocationID).triggerHandler('click');
        // }

        var place = autocomplete.getPlace();
        var street = [];
        var region  = '';
        var streetNumber = '';
        var city = '';
        var postcode = '';
        var postcodeSuffix = '';
        var lat = '';
        var lng = '';
        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var value = place.address_components[i][componentForm[addressType]];
                if (addressType == 'subpremise') {
                    streetNumber = value + '/';
                } else if (addressType == 'street_number') {
                    streetNumber = streetNumber + value;
                } else if (addressType == 'route') {
                    street[1] = value;
                } else if (addressType == 'administrative_area_level_1') {
                    region = value;
                } else if (addressType == 'locality') {
                    city = value;
                } else if (addressType == 'postal_town') {
                    city = value;
                } else if (addressType == 'sublocality_level_1' && city == '') {
                    //ignore if we are using one of other city values already
                    city = value;
                } else if (addressType == 'postal_code') {
                    postcode = value;
                    var thisDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode').uid
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(postcode + postcodeSuffix);
                        $('#'+thisDomID).trigger('change');
                    }
                } else if (addressType == 'postal_code_suffix') {
                    postcodeSuffix = '-' + value;
                    var thisDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode').uid
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(postcode + postcodeSuffix);
                        $('#'+thisDomID).trigger('change');
                    }
                } else {
                    var elementId = lookupElement[addressType];
                    var thisDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.'+ elementId).uid;
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(value);
                        $('#'+thisDomID).trigger('change');
                    }
                }
            }
        }
        $('#note').show();
        if (street.length > 0) {
            street[0] = streetNumber;
            var domID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.street').elems()[0].uid;
            var streetString = street.join(' ');
            if ($('#'+domID)) {
                $('#'+domID).val(streetString);
                $('#'+domID).trigger('change');
            }
        }
        var cityDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.city').uid;
        if ($('#'+cityDomID)) {
            $('#'+cityDomID).val(city);
            $('#'+cityDomID +' option')
                .filter(function () {
                    return $.trim($(this).text()) == city;
                })
                .attr('selected',true);
            $('#'+cityDomID).trigger('change');
        }
        var latDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.latitude').uid;
        if ($('#'+latDomID)) {
            var latValue = place.geometry.location.lat().toFixed(4);
            $('#'+latDomID).val(latValue);
            $('#'+latDomID).trigger('change');
        }
        var lngDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.longitude').uid;
        if ($('#'+lngDomID)) {
            var lngValue = place.geometry.location.lng().toFixed(4);
            $('#'+lngDomID).val(lngValue);
            $('#'+lngDomID).trigger('change');
        }

        createMarker(place.geometry.location.lat(), place.geometry.location.lng());

        var cityDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.city').uid;
        if ($('#'+cityDomID)) {
            $('#'+cityDomID).val(city);
            $('#'+cityDomID).trigger('change');
        }
        if (region != '') {
            if (uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
                var regionDomId = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id').uid;
                if ($('#'+regionDomId)) {
                    //search for and select region using text
                    $('#'+regionDomId +' option')
                        .filter(function () {
                            return $.trim($(this).text()) == region;
                        })
                        .attr('selected',true);
                    $('#'+regionDomId).trigger('change');
                }
            }
            if (uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id_input')) {
                var regionDomId = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id_input').uid;
                if ($('#'+regionDomId)) {
                    $('#'+regionDomId).val(region);
                    $('#'+regionDomId).trigger('change');
                }
            }
        }
    }

    createMarker = function (lat, lng) {
        // The purpose is to create a single marker, so
        // check if there is already a marker on the map.
        // With a new click on the map the previous
        // marker is removed and a new one is created.

        // If the marker variable contains a value
        if (marker) {
            // remove that marker from the map
            marker.setMap(null);
            // empty marker variable
            marker = "";
        }

        // Set marker variable with new location
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(lat, lng),
            draggable: true, // Set draggable option as true
            map: map
        });

        markers.push(marker);


        // This event detects the drag movement of the marker.
        // The event is fired when left button is released.
        google.maps.event.addListener(marker, 'dragend', function() {

            // Updates lat and lng position of the marker.
            marker.position = marker.getPosition();

            // Get lat and lng coordinates.
            var lat = marker.position.lat().toFixed(4);
            var lng = marker.position.lng().toFixed(4);
            var latDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.latitude').uid;
            if ($('#'+latDomID)) {
                $('#'+latDomID).val(lat);
                $('#'+latDomID).trigger('change');
            }
            var lngDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.longitude').uid;
            if ($('#'+lngDomID)) {
                $('#'+lngDomID).val(lng);
                $('#'+lngDomID).trigger('change');
            }

            // Update lat and lng values into text boxes.
            getCoords(lat, lng);

        });
        map.setZoom(16);
        map.setCenter(new google.maps.LatLng(lat, lng));

        var latDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.latitude').uid;
        if ($('#'+latDomID)) {
            $('#'+latDomID).val(lat.toFixed(4));
            $('#'+latDomID).trigger('change');
        }
        var lngDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.longitude').uid;
        if ($('#'+lngDomID)) {
            $('#'+lngDomID).val(lng.toFixed(4));
            $('#'+lngDomID).trigger('change');
        }
    }

    placeMarker = function (position, map) {
        marker = new google.maps.Marker({
            position: position,
            map: map
        });
        map.panTo(position);
        markers.push(marker);
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'latLng': position }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    alert("Location: " + results[1].formatted_address);
                }
            }
        });
    }

    setMapOnAll = function (map) {
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(map);
        }
    }

    return Component;

});
