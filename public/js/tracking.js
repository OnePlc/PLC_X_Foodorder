var mymap = L.map('mapid').setView([47.6195337, 8.6131155], 10);
L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoicHJhZXNpZGlhcml1cyIsImEiOiJjanYydTBmd2cxbXN2NDRvNDcxczJ5YWdpIn0.iVDK1LTomjl9nvli0srASw'
}).addTo(mymap);


var geocoder = L.Control.Geocoder.nominatim();
var waypoints = [];

function loadWaypointStart() {
    geocoder.geocode('Bahnhofstrasse 11, 8212 Neuhausen', function(a, b) {
        // depending on geocoder results may be either in a or b
        // choose the best result here. probably the first one in array
        // create waypoint object
        var wpt = L.latLng(a[0].bbox._northEast.lat, a[0].bbox._northEast.lng)
        waypoints.push(wpt);

        loadWayPointEnd();
    });
}

function loadWayPointEnd() {
    geocoder.geocode(window.order_location, function(a, b) {
        // depending on geocoder results may be either in a or b
        // choose the best result here. probably the first one in array
        // create waypoint object
        //var wpt = L.Routing.waypoint(L.latLng(a[0].bbox._northEast.lat, a[0].bbox._northEast.lng), name)
        var wpt = L.latLng(a[0].bbox._northEast.lat, a[0].bbox._northEast.lng)
        waypoints.push(wpt);

        L.Routing.control({
            geocoder: geocoder,
            waypoints: waypoints,
            routeWhileDragging: false
        }).addTo(mymap);
    });
}

loadWaypointStart();




