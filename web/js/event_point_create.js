/**
 * Created by AlexanderC <alex@mitocgroup.com> on 11/23/14.
 */

(function($) {
    window.Mapper = new (function(mapSelector) {
        var __map;
        var __area;

        return {
            drawMap: function(lat, lng) {
                __map.panTo({lat: lat || 0, lng: lng || 0});
            },
            drawMapArea: function(lat, lng) {
                if(__area) {
                    __area.setMap(null);
                }

                __area = new google.maps.Circle({
                    strokeColor: '#666f77',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#629DD1',
                    fillOpacity: 0.35,
                    map: __map,
                    center: {lat: lat || 0, lng: lng || 0},
                    radius: 250000,
                    draggable: true,
                    geodesic: true,
                    editable: true
                });
            },
            updateFormWithMapArea: function(selector) {
                if(!__area) {
                    throw Error("There is no area drown on the map");
                }

                var $form = $(selector);

                var latitude = __area.getCenter().lat();
                var longitude = __area.getCenter().lng();
                var distance = __area.getRadius() / 1000;

                $form.find("input[name=latitude]").val(latitude);
                $form.find("input[name=longitude]").val(longitude);
                $form.find("input[name=distance]").val(distance);
            },
            init: function() {
                __map = new google.maps.Map($(mapSelector)[0], {
                    center: {lat: 0, lng: 0},
                    zoom: 5
                });
            }
        };
    })('#hazard-map');

    google.maps.event.addDomListener(window, 'load', function() {
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                window.Mapper.drawMap(position.coords.latitude, position.coords.longitude);
                window.Mapper.drawMapArea(position.coords.latitude, position.coords.longitude);
            }, function(){}, {timeout:60000});
        }

        window.Mapper.init();
        window.Mapper.drawMapArea(0, 0);
    });
})(jQuery);