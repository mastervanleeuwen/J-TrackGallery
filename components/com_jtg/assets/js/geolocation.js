

function displayGeolocError(error) {
	// Show error/status message on page when geolocation failed
	// TODO: Use language settings/translations here
  var msgElement = document.getElementById('geo-msg');
  switch(error.code) {
    case error.PERMISSION_DENIED:
      msgElement.innerHTML = "User denied the request for Geolocation."
      break;
    case error.POSITION_UNAVAILABLE:
      msgElement.innerHTML = "Location information is unavailable."
      break;
    case error.TIMEOUT:
      msgElement.innerHTML = "The request to get user location timed out."
      break;
    case error.UNKNOWN_ERROR:
      msgElement.innerHTML = "An unknown error occurred."
      break;
  }
}

// from: https://openlayers.org/en/latest/examples/custom-controls.html
// Need to load style sheet for google icon font with
// <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

class CenterOnGeoControl extends ol.control.Control {
  constructor(opt_options) {
    var options = opt_options || {};

    var button = document.createElement('button');
    button.innerHTML = 'my_location';
    button.className = 'material-icons'; // Use google icon font

    var element = document.createElement('div');
    element.className = 'jtg-geolocate ol-unselectable ol-control';
    element.appendChild(button);

    super({
      element: element,
      target: options.target,
    });

    button.addEventListener('click', this.handleCenterOnGeo.bind(this), false);
  }

  centerOnLocation(position) {
	olview.setCenter(ol.proj.fromLonLat([position.coords.longitude, position.coords.latitude], olview.getProjection()));
	olview.setZoom(jtgMapZoomLevel); // jtgMapZoomLevel is set in the html page
  }
  
  handleCenterOnGeo () {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(this.centerOnLocation.bind(), displayGeolocError);
    } else { 
      document.getElementById('geo-msg').innerHTML = "Geolocation is not supported by this browser.";
    }
  }
}


var geoPosLayer;

class ShowLocationControl extends ol.control.Control {
  geoWatch = undefined;
  posLayer = undefined;
  
  constructor(opt_options) {
    var options = opt_options || {};

    var button = document.createElement('button');
    button.innerHTML = 'my_location';
    button.className = 'material-icons'; // Use google icon font

    var element = document.createElement('div');
    element.className = 'jtg-geolocate ol-unselectable ol-control';
    element.appendChild(button);

    super({
      element: element,
      target: options.target,
    });

    button.addEventListener('click', this.handleShowPosition.bind(this), false);
  }

  showCurrentPosition(position) {
    var ll = ol.proj.fromLonLat([position.coords.longitude, position.coords.latitude], olview.getProjection());
    if (!geoPosLayer.getSource().getFeatureById('geopos')) {
      var marker = new ol.Feature({
          geometry: new ol.geom.Point(ll)});
      marker.setId('geopos');
      marker.setStyle(new ol.style.Style({image: new ol.style.Circle({
                      radius: 4,
                      stroke: new ol.style.Stroke({
                         color: '#fff',
                         width: 2
                      }),
                      fill: new ol.style.Fill({
                        color: 'rgba(255, 0, 0, .7)' 
                      }) 
                      })}));
      geoPosLayer.getSource().addFeature(marker);
    }
    else {
        var marker = geoPosLayer.getSource().getFeatureById('geopos');
        marker.getGeometry().setCoordinates(ll);
    }
  }

  handleShowPosition () {
    if ( ! ("geolocation" in navigator && "watchPosition" in navigator.geolocation) ) {
       document.getElementById('geo-msg').innerHTML = "Geolocation is not supported by this browser.";  // TODO: change to popup?
       return;
    }

    if (!this.geoWatch) { // start showing location
       if (!geoPosLayer) geoPosLayer = new ol.layer.Vector({source: new ol.source.Vector()});
       this.geoWatch = navigator.geolocation.watchPosition( this.showCurrentPosition.bind(), displayGeolocError, { 
                                enableHighAccuracy: false, timeout: 15000, maximumAge: 0 
            } ); 
        olmap.addLayer(geoPosLayer);
    } else { // stop showing location
      navigator.geolocation.clearWatch( this.geoWatch ); 
      olmap.removeLayer(geoPosLayer);
      this.geoWatch = undefined;
    }
  }
}
