var wpseo_directions = [];
var wpseo_maps = [];
var markers = new Array();

function wpseo_show_map(location_data, counter, center_lat, center_long, zoom, map_style, scrollable, draggable) {
    var bounds = new google.maps.LatLngBounds();
    var center = new google.maps.LatLng(center_lat, center_long);
    var mobileBreakpoint = 480;

    var wpseo_map_options = {
        zoom: zoom,
        mapTypeControl: true,
        zoomControl: scrollable,
        streetViewControl: true,
        mapTypeId: google.maps.MapTypeId[ map_style.toUpperCase() ],
        draggable: draggable && window.innerWidth > mobileBreakpoint,
        scrollwheel: scrollable && window.innerWidth > mobileBreakpoint,
    };

    // Set center
    if (zoom == -1) {
        for (var i = 0; i < location_data.length; i++) {
            var latLong = new google.maps.LatLng(location_data[i]["lat"], location_data[i]["long"]);
            bounds.extend(latLong);
        }

        center = bounds.getCenter();
    }
    wpseo_map_options.center = center;

    var map = new google.maps.Map(document.getElementById("map_canvas" + ( counter != 0 ? '_' + counter : '' )), wpseo_map_options);

    if (zoom == -1) {
        map.fitBounds(bounds);
    }

    // Set markers + info
    var infoWindow = new google.maps.InfoWindow({
        content: infoWindowHTML
    });

    for (var i = 0; i < location_data.length; i++) {
        // Create info window HTML
        var infoWindowHTML = getInfoBubbleText(location_data[i]["name"], location_data[i]["zip_city"], location_data[i]["country"], location_data[i]["show_country"], location_data[i]["phone"], location_data[i]["phone_2nd"], location_data[i]["url"], location_data[i]['show_url'], location_data[i]["email"], location_data[i]['show_email']);

        var latLong = new google.maps.LatLng(location_data[i]["lat"], location_data[i]["long"]);
        var icon = location_data[ i ]["custom_marker"];

        markers[i] = new google.maps.Marker({
            position: latLong,
            center: center,
            map: map,
            html: infoWindowHTML,
            draggable: Boolean( draggable ),
            icon: typeof(icon) !== 'undefined' && icon || '',
        });
    }
    for (var i = 0; i < markers.length; i++) {
        var marker = markers[i];
        google.maps.event.addListener(marker, "click", function () {
            infoWindow.setContent(this.html);
            infoWindow.open(map, this);
        });

		google.maps.event.addListener(infoWindow, 'closeclick', function () {
			map.setCenter(this.getPosition());
		});

        google.maps.event.addListener(marker,'dragend',function(event) {
            document.getElementById('wpseo_coordinates_lat').value = event.latLng.lat();
            document.getElementById('wpseo_coordinates_long').value = event.latLng.lng();
        });
    }

    return map;
}

function wpseo_get_directions( map, location_data, counter, show_route ) {
    var directionsDisplay = '';

    if (show_route && location_data.length >= 1) {
        directionsDisplay = new google.maps.DirectionsRenderer();
        directionsDisplay.setMap(map);
        directionsDisplay.setPanel(document.getElementById("directions" + ( counter != 0 ? '_' + counter : '' )));
    }

    return directionsDisplay;
}

function getInfoBubbleText(business_name, business_city_zip, business_country, show_country, business_phone, business_phone_2nd, business_url, show_url, business_email, show_email) {
    var infoWindowHTML = '<div class="wpseo-info-window-wrapper">';

    var showLink = false;
    if (business_url != undefined && wpseo_local_data.has_multiple_locations != '' && business_url != window.location.href )
        showLink = true;

    if( showLink )
        infoWindowHTML += '<a href="' + business_url + '">';
    infoWindowHTML += '<strong>' + business_name + '</strong>';
    if( showLink )
        infoWindowHTML += '</a>';
    infoWindowHTML += '<br>';
    infoWindowHTML += business_city_zip + '<br>';
    if (show_country && business_country != '')
        infoWindowHTML += business_country + '<br>';

    if (show_url && business_url != '')
        infoWindowHTML += '<a href="' + business_url + '">' + business_url + '</a><br>';

    if (show_email && business_email != '')
        infoWindowHTML += '<a href="mailto:' + business_email + '">' + business_email + '</a><br>';

    if (business_phone != '')
        infoWindowHTML += '<a href="tel:' + business_phone + '">' + business_phone + '<br>';

    if (business_phone_2nd != '')
        infoWindowHTML += '<a href="tel:' + business_phone_2nd + '">' + business_phone_2nd + '<br>';

    infoWindowHTML += '</div>';

    return infoWindowHTML;
}

function wpseo_calculate_route(map, dirDisplay, coords_lat, coords_long, counter) {
    if( document.getElementById( 'wpseo-sl-coords-lat' ) != null )
        coords_lat = document.getElementById( 'wpseo-sl-coords-lat' ).value;
    if( document.getElementById( 'wpseo-sl-coords-long' ) != null )
        coords_long = document.getElementById( 'wpseo-sl-coords-long' ).value;

    var start = document.getElementById("origin" + ( counter != 0 ? "_" + counter : "" )).value + ' ' + wpseo_local_data.default_country;
    var unit_system = google.maps.UnitSystem.METRIC;
    if (wpseo_local_data.unit_system == 'IMPERIAL')
        unit_system = google.maps.UnitSystem.IMPERIAL;

    // Clear all markers from the map, only show A and B
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }

    // Change button to link to Google Maps. iPhones and Android phones will automatically open them in Maps app, when available.
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        var url = 'https://www.google.com/maps?saddr=' + escape( start ) + '&daddr=' + coords_lat + ',' + coords_long;
        window.open( url, '_blank' );

        return false;
    }
    else {
        var latlng = new google.maps.LatLng( coords_lat, coords_long );

		var request = {
			origin: start,
			destination: latlng,
			provideRouteAlternatives: true,
			optimizeWaypoints: true,
			travelMode: google.maps.DirectionsTravelMode.DRIVING,
			unitSystem: unit_system
		};

        var directionsService = new google.maps.DirectionsService();

        directionsService.route(request, function (response, status2) {
            if (status2 == google.maps.DirectionsStatus.OK) {
                dirDisplay.setDirections(response);
            }
            else if (status2 == google.maps.DirectionsStatus.ZERO_RESULTS) {
                var noroute = document.getElementById( 'wpseo-noroute' );
                noroute.setAttribute( 'style', 'clear: both; display: block;' );
            }
        });
    }
}

function wpseo_sl_show_route( obj, coords_lat, coords_long ) {
    $ = jQuery;

    // Create hidden inputs to pass through the lat/long coordinates for which is needed for calculating the route.
    $('.wpseo-sl-coords').remove();
    var inputs = '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-lat" value="' + coords_lat + '">';
    inputs += '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-long" value="' + coords_long + '">';

    $('#wpseo-directions-form').append( inputs ).submit();
    $('#wpseo-directions-wrapper').slideUp( function() {
       $( this ).insertAfter( $(obj).parents('.wpseo-result') ).slideDown(); 
    });
}
