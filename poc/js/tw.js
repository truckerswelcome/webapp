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
const startSearchButton = document.querySelector('#search-button');
const startLocation = startForm ? startForm.elements['start-location'] : false;
const searchThisArea = document.querySelector('#search-this-area');
const searchThisAreaButton = searchThisArea.querySelector('#search-this-area > button');
const addBusinessButton = document.querySelector('#add-business > button');
const siteForm = document.querySelector('#add-business-form');
const countrySelect = siteForm.querySelector('#country');
const provinceSelect = siteForm.querySelector('#province');
const provinceSelectDefault = siteForm.querySelector('#province-default');
const postalInput = siteForm.querySelector('#postal');
const businessModalTitle = document.querySelector('#business-modal-title');
const servicesError = document.querySelector('#services-error');
const siteModalMsg = document.querySelector('#modal-msg');
const siteModalMsg2 = document.querySelector('#modal-msg2');
const submitSiteButton = document.querySelector('#submit-site-button');
const deleteSiteButton = document.querySelector('#delete-site-button');

const toronto = {
    lat: 43.6532,
    lng: -79.3832
};

function initPage() {
    // set up various event listeners

    overlay.addEventListener('click', closeSidenav);
    navCloseIcon.addEventListener('click', closeSidenav);

    navIcon.addEventListener('click', function () {
        sidenav.classList.add('sidenav-open');
        overlay.classList.add('overlay-open');
    });

    searchThisAreaButton.addEventListener('click', () => {
        // get new map center
        let center = map.getCenter();
        searchLocation.value = '';
        hiddenLatitudeInput.value = center.lat();
        hiddenLongitudeInput.value = center.lng();
        doSearch();
    });

    addBusinessButton.addEventListener('click', () => {
        openBusinessForm();
    });

    countrySelect.addEventListener('change', (e) => {
        const val = e.target.value;
        setCountrySelectOptions(val);
    });

    if (startLocation) {
        startLocation.addEventListener('keyup', (e) => {
            if (e.keyCode === 13) {
                if (startLocation.value.length == 0) {
                    alert('Please enter your location');
                    return false;
                }
                searchLocation.value = startLocation.value;
                doSearch();
                $('#start-modal').modal('hide');
            }
        })
    }

    if (submitSiteButton) {
        submitSiteButton.addEventListener('click', () => {
            clearBusinessFormErrors();

            let xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    xhr.onreadystatechange = function () { };
                    let data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        siteModalMsg.innerText = 'Success';
                        // should the modal be closed now?
                        submitSiteButton.style.display = 'none';
                        deleteSiteButton.style.display = 'none';
                    } else {
                        // generate another recaptcha token
                        generateRecaptcha();

                        siteModalMsg.innerText = 'Error updating business';
                        if (data.sqlerror) {
                            siteModalMsg2.innerText = data.sqlerror.join('<br/>');
                        }

                        // handle each error from data.form_errors
                        for (let i = 0; i < data.formErrors.length; i++) {
                            const idx = data.formErrors[i];
                            if (idx == 'services') {
                                servicesError.style.display = 'block';
                            } else {
                                const el = siteForm.elements[data.formErrors[i]];
                                if (el)
                                    el.classList.add('is-invalid');
                            }
                        }
                    }
                }
            };
            xhr.open('POST', 'site.php', true);
            xhr.send(new FormData(siteForm));
            return false;
        });

        if (deleteSiteButton) {
            deleteSiteButton.addEventListener('click', () => {
                if (confirm("Are you sure you want to delete this business?") === true) {
                    siteForm.delete.value = 1;
                    let xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4) {
                            xhr.onreadystatechange = function () { };
                            let data = JSON.parse(xhr.responseText);
                            // only show the close button now
                            if (data.success) {
                                siteModalMsg.innerText = 'Success';
                                // should the modal be closed now?
                                submitSiteButton.style.display = 'none';
                                deleteSiteButton.style.display = 'none';
                            } else {
                                siteModalMsg.innerText = 'Error deleting business';
                                if (data.sqlerror) {
                                    siteModalMsg2.innerText = data.sqlerror.join('<br/>');
                                }
                            }
                        }
                    };
                    xhr.open('POST', 'site.php', true);
                    xhr.send(new FormData(siteForm));
                }
                return false;
            });
        }
    }

    startSearchButton.addEventListener('click', () => {
        if (startForm === null)
            return;
        /*
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
        */
        if (startLocation.value.length == 0) {
            alert('Please enter your location');
            return false;
        }
        searchLocation.value = startLocation.value;
        doSearch();

        $('#start-modal').modal('hide');
    });
}

function openBusinessForm() {
    businessModalTitle.innerText = 'Add a Business';
    clearBusinessForm();
    generateRecaptcha();
    submitSiteButton.style.display = 'block';
    deleteSiteButton.style.display = 'none';
    businessModalTitle.innerText = 'Add a Business';
    $('#add-modal').modal('show');
    closeSidenav();
    return false;
}

function openAboutModal() {
    $('#about-modal').modal('show');
    closeSidenav();
    return false;
}

function openTermsOfServiceModal() {
    $('#tos-modal').modal('show');
    closeSidenav();
    return false;
}

$('div.modal').on('show.bs.modal', function () {
    var modal = this;
    var hash = modal.id;
    window.location.hash = hash;
    window.onhashchange = function () {
        if (!location.hash) {
            $(modal).modal('hide');
        }
    }
});

$('div.modal').on('hide', function () {
    var hash = this.id;
    history.pushState('', document.title, window.location.pathname);
});

function generateRecaptcha() {
    grecaptcha.ready(function () {
        grecaptcha.execute('6LeujegUAAAAAImDheP5SG6ph54m55PIU1gLfkKT', { action: 'contact' }).then(function (token) {
            const recaptchaResponse = document.getElementById('recaptchaResponse');
            recaptchaResponse.value = token;
        });
    });
}

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
        fullscreenControl: false,
        scaleControl: true,
        zoomControl: false
    });
    map.addListener('center_changed', () => {
        if (document.activeElement == searchLocation) {
            searchLocation.blur();
        }
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

    // try to geolocate on the browser
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                // success
                if (doGeolocationSearch) {
                    let coordinates = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    // set the hidden lat/lng inputs to the given coordinates
                    hiddenLatitudeInput.value = coordinates.lat;
                    hiddenLongitudeInput.value = coordinates.lng;
                    hiddenOptionsInput.value = 'all';
                    map.setCenter(coordinates);
                    doSearch();
                }
            },
            function (err) {
                // failure
                map.setCenter(toronto);
                console.warn(`Geolocate Error(${err.code}): ${err.message}`);
                hiddenOptionsInput.value = 'all';
                $('#start-modal').modal('show');
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
    searchLocation.blur();
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

function setCountrySelectOptions(country) {
    switch (country) {
        case 'Canada':
            provinceSelect.innerHTML = `
                <option id="province-default" value="" disabled selected>Province</option>
                <option value="AB">Alberta</option>
                <option value="BC">British Columbia</option>
                <option value="MB">Manitoba</option>
                <option value="NB">New Brunswick</option>
                <option value="NL">Newfoundland and Labrador</option>
                <option value="NS">Nova Scotia</option>
                <option value="ON">Ontario</option>
                <option value="PE">Prince Edward Island</option>
                <option value="QC">Quebec</option>
                <option value="SK">Saskatchewan</option>
                <option value="NT">Northwest Territories</option>
                <option value="NU">Nunavut</option>
                <option value="YT">Yukon</option>
`;
            postalInput.placeholder = 'Postal Code';
            break;
        case 'USA':
            provinceSelect.innerHTML = `
                <option id="province-default" value="" disabled selected>State</option>
                <option value="AL">Alabama</option>
                <option value="AK">Alaska</option>
                <option value="AZ">Arizona</option>
                <option value="AR">Arkansas</option>
                <option value="CA">California</option>
                <option value="CO">Colorado</option>
                <option value="CT">Connecticut</option>
                <option value="DE">Delaware</option>
                <option value="DC">District Of Columbia</option>
                <option value="FL">Florida</option>
                <option value="GA">Georgia</option>
                <option value="HI">Hawaii</option>
                <option value="ID">Idaho</option>
                <option value="IL">Illinois</option>
                <option value="IN">Indiana</option>
                <option value="IA">Iowa</option>
                <option value="KS">Kansas</option>
                <option value="KY">Kentucky</option>
                <option value="LA">Louisiana</option>
                <option value="ME">Maine</option>
                <option value="MD">Maryland</option>
                <option value="MA">Massachusetts</option>
                <option value="MI">Michigan</option>
                <option value="MN">Minnesota</option>
                <option value="MS">Mississippi</option>
                <option value="MO">Missouri</option>
                <option value="MT">Montana</option>
                <option value="NE">Nebraska</option>
                <option value="NV">Nevada</option>
                <option value="NH">New Hampshire</option>
                <option value="NJ">New Jersey</option>
                <option value="NM">New Mexico</option>
                <option value="NY">New York</option>
                <option value="NC">North Carolina</option>
                <option value="ND">North Dakota</option>
                <option value="OH">Ohio</option>
                <option value="OK">Oklahoma</option>
                <option value="OR">Oregon</option>
                <option value="PA">Pennsylvania</option>
                <option value="RI">Rhode Island</option>
                <option value="SC">South Carolina</option>
                <option value="SD">South Dakota</option>
                <option value="TN">Tennessee</option>
                <option value="TX">Texas</option>
                <option value="UT">Utah</option>
                <option value="VT">Vermont</option>
                <option value="VA">Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
`;
            postalInput.placeholder = 'ZIP Code';
            break;
    }
}

function getSite(id) {
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            xhr.onreadystatechange = function () { };
            let data = JSON.parse(xhr.responseText);
            // populate the business form
            businessModalTitle.innerText = 'Edit Business';
            clearBusinessFormErrors();
            setCountrySelectOptions(data.country);
            siteForm.bizname.value = data.name;
            siteForm.street.value = data.address;
            siteForm.city.value = data.city;
            siteForm.province.value = data.province_state;
            siteForm.country.value = data.country;
            siteForm.postal.value = data.postal;
            siteForm.bemail.value = data.email;
            siteForm.phone.value = data.phone;
            siteForm.website.value = data.website;
            siteForm.entryid.value = id;
            siteForm.delete.value = 0;
            siteForm.diesel.checked = data.diesel == 1;
            siteForm.washroom.checked = data.washroom == 1;
            siteForm.shower.checked = data.shower == 1;
            siteForm.coffee.checked = data.coffee == 1;
            siteForm.snacks.checked = data.snacks == 1;
            siteForm.meal.checked = data.meal == 1;
            siteForm.drivethrough.checked = data.drivethrough == 1;
            siteForm.walkup.checked = data.walkthrough == 1;
            siteForm.other.value = data.otherservices;
            generateRecaptcha();

            submitSiteButton.style.display = 'block';
            deleteSiteButton.style.display = 'block';

            $('#add-modal').modal('show');
        }
    };
    xhr.open('GET', `site.php?entryid=${id}`, true);
    xhr.send();
    return false;
}

function createMarker(result) {
    let lat = parseFloat(result.lat);
    let lng = parseFloat(result.lng);
    let latlng = new google.maps.LatLng(lat, lng);

    // create a marker for each result
    let mapsurl = `https://maps.google.com/maps?q=${result.address},${result.city},${result.province_state},${result.postal}`;
    let name = result.website.length > 0 ? `<a href=${result.website}>${result.name}</a>` : result.name;

    let content = `
<div id="content">
    <form>
        <B>${name}</B>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="hidden" name="entryid" value="${result.id}">
        <button type="button" name="(Edit)" value="(Edit)" class="btn-link" onclick="getSite(${result.id});">(Edit)</button>
    </form>
    <br/>
    ${result.address},${result.city},${result.province_state},${result.postal}
    <br/>
    Phone: <a href="tel:${result.phone}">${result.phone}</a>
    <br/>
    <B>Services:</B>
    ${result.services_list}
    <br/>
    <a href="${mapsurl}">Open in Google Maps</a>
    <BR><BR>
    Problems with this site? <A HREF="mailto:help@truckerswelcome.ca?subject=Mail from TruckersWelcome">Email us</A>
    <BR>
    <A HREF=tos.html>Terms Of Service</A>
</div>`;

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

function closeSidenav() {
    sidenav.classList.remove('sidenav-open');
    overlay.classList.remove('overlay-open');
}

function clearBusinessFormErrors() {
    siteModalMsg.innerText = '';
    siteModalMsg2.innerText = '';
    siteModalMsg2.style.display = 'none';

    // clear the error fields
    siteForm.querySelectorAll("input.is-invalid, select.is-invalid").forEach((el) => {
        el.classList.remove('is-invalid');
    });
    servicesError.style.display = 'none';
}

function clearBusinessForm() {
    clearBusinessFormErrors();
    siteForm.uname.value = '';
    siteForm.whoareyou.value = '';
    siteForm.bizname.value = '';
    siteForm.street.value = '';
    siteForm.city.value = '';
    siteForm.province.value = '';
    siteForm.country.value = '';
    siteForm.postal.value = '';
    siteForm.bemail.value = '';
    siteForm.phone.value = '';
    siteForm.website.value = '';
    siteForm.entryid.value = '';
    siteForm.delete.value = 0;
    siteForm.diesel.checked = false;
    siteForm.washroom.checked = false;
    siteForm.shower.checked = false;
    siteForm.coffee.checked = false;
    siteForm.snacks.checked = false;
    siteForm.meal.checked = false;
    siteForm.drivethrough.checked = false;
    siteForm.walkup.checked = false;
    siteForm.other.value = '';
}

function checkWho() {
    var e = document.querySelector("#whoareyou");
    var strUser = e.options[e.selectedIndex].text;
    document.querySelector('#modpindiv').style.display = (strUser == 'Moderator') ? 'block' : 'none';
}

initPage();