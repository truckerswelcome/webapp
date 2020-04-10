var map;
var markers = [];
var infoWindows = [];
var infoWindow;
var locationSelect;
var latLng;
let doGeolocationSearch = true;
let coordinates = false;
let queryOptions = false;

const searchForm = document.querySelector('#search form');
const navIcon = document.querySelector('.nav-icon');
const navCloseIcon = document.querySelector('.nav-close-icon');
const sidenav = document.querySelector('.sidenav');
const overlay = document.querySelector('.overlay');
const searchLocation = searchForm.querySelector('input[name=location]');
const hiddenLatitudeInput = searchForm.querySelector('input[name=lat]');
const hiddenLongitudeInput = searchForm.querySelector('input[name=lng]');
const hiddenOptionsInput = searchForm.querySelector('input[name=options]');
const startForm = document.querySelector('#start-form');
const startLocation = startForm ? startForm.elements['start-location'] : false;
const useMyLocation = startForm ? startForm.elements['use-my-location'] : false;
const searchThisArea = document.querySelector('#search-this-area');
const searchThisAreaButton = searchThisArea.querySelector('#search-this-area > button');

const toronto = {
    lat: 43.6532,
    lng: -79.3832
};

function closeAllInfoWindows() {
    for (var i = 0; i < infoWindows.length; i++) {
        infoWindows[i].close();
    }
}

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: toronto,
        zoom: 9,
        mapTypeId: 'roadmap',
        mapTypeControl: false,
        mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
        streetViewControl: false,
        fullscreenControl: false
    });
    infoWindow = new google.maps.InfoWindow();

    searchForm.onsubmit = function () {
        if (searchLocation.value.length > 0) {
            hiddenLatitudeInput.value = '';
            hiddenLongitudeInput.value = '';
            doSearch();
        } else {
            alert('Please enter a location');
        }
        return false;
    };

    geolocate();
}

function geolocate() {
    // try to geolocate on the browser
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                if (doGeolocationSearch) {
                    let coordinates = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    // set the hidden lat/lng inputs to the given coordinates
                    hiddenLatitudeInput.value = coordinates.lat;
                    hiddenLongitudeInput.value = coordinates.lng;
                    map.setCenter(coordinates);
                    useMyLocation.checked = true;
                    startLocation.disabled = true;
                }
            },
            function (err) {
                // geolocate failure
                map.setCenter(toronto);
                console.warn(`Geolocate Error(${err.code}): ${err.message}`);
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }
}

function doSearch() {
    let form = document.querySelector('#search form');
    infoWindow.close();
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            xhr.onreadystatechange = function () { };
            let data = JSON.parse(xhr.responseText);
            let pos = { lat: parseFloat(data.lat), lng: parseFloat(data.lng) };
            map.setCenter(pos);
            for (let i = 0; i < data.results.length; i++) {
                let result = data.results[i];
                createMarker(result);
                createResult(result, i);
            }
            let x = 1;
        }
    };
    xhr.open('POST', 'search.php', true);
    xhr.send(new FormData(form));
    return false;
}

function createMarker(result) {
    let lat = parseFloat(result.lat);
    let lng = parseFloat(result.lng);
    let latlng = new google.maps.LatLng(lat, lng);

    // create a marker for each result
    let mapsurl = `https://maps.google.com/maps?q=${result.address},${result.city},${result.province_state},${result.postal}`;
    let name = result.website.length > 0 ? `<a href=${result.website}>${result.name}</a>` : result.name;

    let content = `<div id="content"><form action="addsite.php" method="post"><B>${name}</B>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="hidden" name="entryid" value="${result.id}"><button type="submit" name="(Edit)" value="(Edit)" class="btn-link">(Edit)</button></form><br/>${result.address},${result.city},${result.province_state},${result.postal}<br/>Phone: <a href="tel:${result.phone}">${result.phone}</a><br/><B>Services:</B>${result.services_list}<br/><a href="${mapsurl}">Open in Google Maps</a><BR><BR>Problems with this site? <A HREF="mailto:help@truckerswelcome.ca?subject=Mail from TruckersWelcome">Email us</A></div>`;

    let marker = new google.maps.Marker({
        map: map,
        position: latlng,
        title: result.name
    });
    //markers.push(marker);

    google.maps.event.addListener(marker, 'click', function () {
        infoWindow.setContent(content);
        infoWindow.open(map, marker);
    });
}

function createResult(result, num) {
    // maybe this is used to create a list of results for an alternate view
}

overlay.addEventListener('click', closeSidenav);
navCloseIcon.addEventListener('click', closeSidenav);

navIcon.addEventListener('click', function () {
    sidenav.classList.add('sidenav-open');
    overlay.classList.add('overlay-open');
});

function closeSidenav() {
    sidenav.classList.remove('sidenav-open');
    overlay.classList.remove('overlay-open');
}

if (useMyLocation) {
    useMyLocation.addEventListener('click', () => {
        startLocation.disabled = useMyLocation.checked;
    });
}

searchThisAreaButton.addEventListener('click', () => {
    // get new map center
    let center = map.getCenter();
    searchLocation.value = '';
    hiddenLatitudeInput.value = center.lat();
    hiddenLongitudeInput.value = center.lng();
    doSearch();
});

$('#start-modal').modal('show');
document.querySelector('#search-button').addEventListener('click', () => {
    if (startForm === null)
        return;

    const options = {
        washroom: startForm.elements.washroom.checked,
        shower: startForm.elements.shower.checked,
        reststop: startForm.elements.reststop.checked,
        coffee: startForm.elements.coffee.checked,
        snacks: startForm.elements.snacks.checked,
        meal: startForm.elements.meal.checked,
        drivethrough: startForm.elements.drivethrough.checked,
        walkthrough: startForm.elements.walkthrough.checked
    }
    let tmp = [];
    for (let i in options) {
        if (options[i])
            tmp.push(i);
    }
    hiddenOptionsInput.value = tmp.join(',');

    if (useMyLocation.checked && hiddenLatitudeInput.value.length > 0 && hiddenLongitudeInput.value.length > 0) {
        doSearch();
    } else {
        if (startLocation.value.length == 0) {
            alert('Please enter your location, or check the use my location box');
            return false;
        }
        searchLocation.value = startLocation.value;
        doSearch();
    }
    $('#start-modal').modal('hide');
});