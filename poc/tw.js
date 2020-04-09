var map;
var markers = [];
var infoWindows = [];
var infoWindow;
var locationSelect;
var latLng;
let doGeolocationSearch = true;
const searchForm = document.querySelector('#search form');


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
        mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU }
    });
    infoWindow = new google.maps.InfoWindow();

    searchForm.onsubmit = function () {
        doSearch();
        return false;
    };

    if (navigator.geolocation) {
        // try to geolocate and load search results
        navigator.geolocation.getCurrentPosition(
            function (position) {
                if (doGeolocationSearch) {
                    doSearch({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    }, true);
                }
            },
            function (err) {
                // geolocate failure
                doSearch(toronto);
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

function doSearch(coordinates) {
    let form = document.querySelector('#search form');
    const searchLocation = form.querySelector('input[name=location]').value;
    if (searchLocation.length > 0) {
        form.querySelector('input[name=lat]').value = '';
        form.querySelector('input[name=lng]').value = '';
    } else {
        form.querySelector('input[name=lat]').value = coordinates.lat;
        form.querySelector('input[name=lng]').value = coordinates.lng;
        map.setCenter(coordinates);
    }

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
    xhr.open('POST', '/search.php', true);
    xhr.send(new FormData(form));
    return false;
}

function createMarker(result) {
    let lat = parseFloat(result.lat);
    let lng = parseFloat(result.lng);
    let latlng = new google.maps.LatLng(lat, lng);

    // create a marker for each result
    //let pageUrl = `/${page.tag}/${page.id}/${page.name}`;
    //let phone = page.phone;
    //let formattedPhone = formatPhoneNumber(phone);
    let mapsurl = `https://maps.google.com/maps?q=${result.address},${result.city},${result.province_state},${result.postal}`;
    let name = result.website.length > 0 ? `<a href=${result.website}>${result.name}</a>` : result.name;

    let content = `<div id="content"><B>${name}</B><br/>${result.address},${result.city},${result.province_state},${result.postal}<br/>Phone: <a href="tel:${result.phone}">${result.phone}</a><br/><B>Services:</B>${result.services_list}<br/><a href="${mapsurl}">Open in Google Maps</a></div>`;

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
