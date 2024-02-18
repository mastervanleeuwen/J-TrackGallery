/*
 functions used to setup a clustered layer of marker with popup
 This is used in the overview of J!TrackGallery
*/
function addTrackMarkers(tracks, catIcons) {
	iconStyles = [];
	for (i=0; i < catIcons.length; i++)
	{
		iconStyles.push(new ol.style.Style( {
			image: new ol.style.Icon( { src: jtgBaseUrl+'images/jtrackgallery/cats/'+catIcons[i],anchorOrigin: 'bottom-left', anchor: [0.5,0] } )
		} ) );
	}
	var arrayOfMarkers = [];
	for (i=0; i<tracks.length; i++)
	{
		ll = ol.proj.fromLonLat([tracks[i].lon, tracks[i].lat], jtgView.getProjection());
		var f= new ol.Feature( {
			 geometry: new ol.geom.Point(ll,
				{description:'Marker_'+i}) ,
                         description: tracks[i].description,
                         name: tracks[i].link
		} );
		f.setStyle( iconStyles[tracks[i].catIdx] );
		arrayOfMarkers.push(f);
	}
	return arrayOfMarkers;
}

function addDPCalLocs(markers, locations, iconfile) {
	for (i=0; i<locations.length; i++)
	{
		ll = ol.proj.fromLonLat([locations[i].lon, locations[i].lat], jtgView.getProjection());
		var f= new ol.Feature( {
			 geometry: new ol.geom.Point(ll,
				{description:'Location_'+i}) ,
					name: '<a href="'+locations[i].url+'">'+locations[i].title+'</a>',
					description: ''
		} );
		f.setStyle( new ol.style.Style( { 
			image: new ol.style.Icon( { 
				src: iconfile, 
				color: '#'+locations[i].color 
		}) }) );
		markers.push(f);
	}
}

function addPopupOverview() {
    /**
     * Elements that make up the popup.
     */
    var container = document.getElementById('popup');
    var content = document.getElementById('popup-content');
    var closer = document.getElementById('popup-closer');

    var popupActive = false;
    var popupHover = false;
    /**
     * Create an overlay to anchor the popup to the map.
     */
    var overlay = new ol.Overlay({
        element: container,
        autoPan: true,
        autoPanAnimation: {
            duration: 250
        }
    });


    /**
     * Add a click handler to hide the popup.
     * @return {boolean} Don't follow the href.
     */
	closer.onclick = function() {
		overlay.setPosition(undefined);
		closer.blur();
		popupActive = false;
		popupHover = false;
	};

	container.onmouseover = function() {
		popupHover = true;
	};

	container.onmouseout = function() {
		popupHover = false;
	};

	jtgMap.addOverlay(overlay);

	function displayClusterInfo(evt) {
		var clusters = [];
		var mindist = 1e30;
		var imin = -1;
		// Could use cluster.getClosestFeatureToCoordinate instead?
		jtgMap.forEachFeatureAtPixel(evt.pixel, function(feature) {
			if (feature.get('features')) { // only take clusters of features
				clusters.push(feature);
				pos = feature.getGeometry().getClosestPoint(evt.coordinate);
				dist = (evt.coordinate[0]-pos[0])*(evt.coordinate[0]-pos[0])+(evt.coordinate[1]-pos[1])*(evt.coordinate[1]-pos[1]);
				if (dist < mindist) { mindist = dist; imin = clusters.length; }
			}
		});
		if (clusters.length > 0 && imin > 0) {
			features = clusters[imin-1].get('features');
			var info = [];
			var i, ii;
			for (i = 0, ii = features.length; i < ii; ++i) {
				info.push(features[i].get('name'));
			}
			content.innerHTML = info.join('<br>\n') || '(unknown)';
			if (features.length == 1) {
				content.innerHTML += features[0].get('description');
			}
			return clusters[imin-1].getGeometry().getClosestPoint(evt.coordinate);
		} else {
			content.innerHTML = '&nbsp;';
			popupHover = false;
			return false;
		}
	}

    /**
     * Add a click handler to the map to render the popup.
     */
    jtgMap.on('singleclick', function(evt) {
       if ((coord = displayClusterInfo(evt))) {
           overlay.setPosition(coord);
           popupActive = true;
       }
       else {
           overlay.setPosition(undefined);
           popupActive = false;
       }
    });

    // Handler for pointer movement
    jtgMap.on('pointermove', function(evt) {
       if (evt.dragging || popupActive || popupHover) {
           return;
       }

       if ((coord = displayClusterInfo(evt))) {
           overlay.setPosition(coord);
       }
       else {
           overlay.setPosition(undefined);
       }
    });
}

// Clustering
//--------------------

function addTracksOverviewLayer(tracks, catIcons){
	// Define three colors that will be used to style the cluster features
	// depending on the number of features they contain.
	var colors = {
		low: [255,153,51],
		middle: [255,128,0],
		high: [204,102,0]
	};

	// Create a vector layers and add markers
	markers = addTrackMarkers(tracks, catIcons);
	// TODO: pass these as arguments instead of global vars
	if (typeof DPCalLocs !== 'undefined') { addDPCalLocs(markers, DPCalLocs, DPCalIconFile); }

	// Create a vector layers
	var source = new ol.source.Vector({
		features: markers
	});
	if (markers.length > 1) {
		jtgView.fit( source.getExtent(), {padding: [50, 50, 50, 75]} );
	}
	else {
		jtgView.setCenter(ol.proj.fromLonLat([tracks[0].lon, tracks[0].lat], jtgView.getProjection()));
		jtgView.setZoom(8); 
	}
	var styleCache = {};
	var layerVectorForMarkers = new ol.layer.Vector({name: "Features",
		source: new ol.source.Cluster({ source: source, distance: 38 }),
		style: function(feature) {
			var size = feature.get('features').length;
			if (size == 1) {
				return feature.get('features')[0].getStyle();
			}
			var style = styleCache[size];
			if (!style) {
				var fillColor = colors.low;
				var pointRadius = 10;
				if (size > 5 && size < 20) {
					fillColor = colors.middle;
					pointRadius = 15;
				}
				else if (size >= 20) {
					fillColor = colors.high;
					pointRadius = 20;
				}

				style = new ol.style.Style({
					image: new ol.style.Circle({
						radius: pointRadius,
						stroke: new ol.style.Stroke({
							color: fillColor.concat([0.5]), //'#fff'
							width: 12
						}),
						fill: new ol.style.Fill({
							color: fillColor.concat([0.9]) //'#3399CC'
						})
					}),
					text: new ol.style.Text({
						text: size.toString(),
						fill: new ol.style.Fill({
							color: '#fff'
						})
					})
				});
				styleCache[size] = style;
			}
			return style;
		}
	});
	jtgMap.addLayer(layerVectorForMarkers);

	addPopupOverview(); // Now done in main javascript slippymap_init()
}
