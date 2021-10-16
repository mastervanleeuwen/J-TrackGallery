// ************************************************
// Animated cursor on track
// Design and javascript Code inspired from http://www.utagawavtt.com/

function hover_profil_graph(longitude, latitude, originalIdx, autocenter)
{
	// enable moving cursor
	animatedCursorLayer.setVisible(true); // for opera broken in Firefox
	//animatedCursorIcon.opacity = 0.7; // for Firefox
        
	animatedCursorIcon.setCoordinates(ol.proj.fromLonLat([longitude, latitude], jtgView.getProjection()));
	//animatedCursorIcon.redraw();

	// ************************************************
	// highlight the track depending on the position
	// create the gpxHover feature if it doesn't exist yet
        animatedCursorLayer.getSource().getFeatureById('cursorTrack').getGeometry().setCoordinates(animatedCursorLayer.gpxPoints.slice(1,originalIdx));
	// ************************************************

	// if the point is out of the map, center on it
        /*
	if (autocenter) {
		if (animatedCursorIcon.markers.length > 0 && !animatedCursorIcon.markers[0].onScreen()) {
		olmap.setCenter(lonlat);
		}
	}
        */

}

function out_profil_graph()
{
	animatedCursorLayer.setVisible(false);  // for opera broken in Firefox

}

var animated_cursor_style = function( feature ) {
  
      var style = {
        'Point': new ol.style.Style({
          image: new ol.style.Icon({
              src: jtgBaseUrl + 'components/com_jtg/assets/images/orange-dot.png',
              anchorOrigin: 'bottom-left',
              anchor: [0.5,0]
          })
        }),
        'LineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: '#f00',
            width: 2
          })
        }),
        'MultiLineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: '#0f0',
            width: 2
          })
        })
      };

    return style[feature.getGeometry().getType()];
}
