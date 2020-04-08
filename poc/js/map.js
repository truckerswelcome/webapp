let doGeolocationSearch = true;

function centerOnCoordinates(latlng) {
    infoWindow.close();
    map.setCenter(latlng);
}

function closeAllInfoWindows() {
  for (var i=0;i<infoWindows.length;i++) {
     infoWindows[i].close();
  }
}
