
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

// Start with an initial value of 20 seconds
const TIME_LIMIT = 60;

// Initially, no time has passed, but this will count up
// and subtract from the TIME_LIMIT
let timePassed = 0;
let timeLeft = TIME_LIMIT;

function formatTimeLeft(time) {
    // The largest round integer less than or equal to the result of time divided being by 60.
    const minutes = Math.floor(time / 60);

    // Seconds are the remainder of the time divided by 60 (modulus operator)
    let seconds = time % 60;

    // If the value of seconds is less than 10, then display seconds with a leading zero
    if (seconds < 10) {
        seconds = `0${seconds}`;
    }

    // The output in MM:SS format
    //return `${minutes}:${seconds}`;

    $('#base-timer-label').html(""+minutes+":"+seconds+"");
}

let timerInterval = null;

function startTimer() {
    timerInterval = setInterval(() => {
        timePassed = timePassed += 1;
        timeLeft = TIME_LIMIT - timePassed;
        formatTimeLeft(timeLeft);

        setCircleDasharray();
    }, 1000);
}

// Divides time left by the defined time limit.
function calculateTimeFraction() {
    return timeLeft / TIME_LIMIT;
}

const FULL_DASH_ARRAY = 283;

// Update the dasharray value as time passes, starting with 283
function setCircleDasharray() {
    const circleDasharray = `${(
        calculateTimeFraction() * FULL_DASH_ARRAY
    ).toFixed(0)} 283`;
    document
        .getElementById("base-timer-path-remaining")
        .setAttribute("stroke-dasharray", circleDasharray);
}

const COLOR_CODES = {
    info: {
        color: "green"
    }
};

let remainingPathColor = COLOR_CODES.info.color;

startTimer();

jQuery(document).ready(function($){
    var $timeline_block = $('.cd-timeline-block');

    //hide timeline blocks which are outside the viewport
    $timeline_block.each(function(){
        if($(this).offset().top > $(window).scrollTop()+$(window).height()*0.75) {
            $(this).find('.cd-timeline-img, .cd-timeline-content').addClass('is-hidden');
        }
    });

    //on scolling, show/animate timeline blocks when enter the viewport
    $(window).on('scroll', function(){
        $timeline_block.each(function(){
            if( $(this).offset().top <= $(window).scrollTop()+$(window).height()*0.75 && $(this).find('.cd-timeline-img').hasClass('is-hidden') ) {
                $(this).find('.cd-timeline-img, .cd-timeline-content').removeClass('is-hidden').addClass('bounce-in');
            }
        });
    });
});




