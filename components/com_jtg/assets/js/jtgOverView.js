/*
 functions used to setup a clustered layer of marker with popup
 This is used in the overview of J!TrackGallery
*/
function addMarkers() {
	arrayOfMarkers = [];
	for (i=0; i<markers.length; i++)
	{
		ll = ol.proj.fromLonLat([markers[i].lon, markers[i].lat], olview.getProjection());
		var f= new ol.Feature( {
			 geometry: new ol.geom.Point(ll,
				{description:'Marker_'+i}) ,
                         description: markers[i].description,
                         name: markers[i].link
		} );
                // MvL check: Somehow you cannot set the style in the constructor. Not sure why
                f.setStyle( new ol.style.Style( {
                            image: markers[i].iconStyle } ));
		arrayOfMarkers.push(f);
	}
}

function addDPCalLocs(locations, iconfile) {
	for (i=0; i<locations.length; i++)
	{
		ll = ol.proj.fromLonLat([locations[i].lon, locations[i].lat], olview.getProjection());
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
		arrayOfMarkers.push(f);
	}
}

// MvL: could move this to a separate file
function addPopup() {
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
    };

    container.onmouseover = function() {
        popupHover = true;
    };

    container.onmouseout = function() {
        popupHover = false;
    };

    olmap.addOverlay(overlay);

    function displayClusterInfo(pixel) {
        var clusters = [];
        olmap.forEachFeatureAtPixel(pixel, function(feature) {
            if (feature.get('features')) { // only take clusters of features
               clusters.push(feature);
            }
        });
        if (clusters.length > 0) {
            features = clusters[0].get('features');
            var info = [];
            var i, ii;
            for (i = 0, ii = features.length; i < ii; ++i) {
               info.push(features[i].get('name'));
            }
            content.innerHTML = info.join('<br>\n') || '(unknown)';
            if (features.length == 1) {
               content.innerHTML += features[0].get('description');
            }
				return clusters[0].getGeometry().getClosestPoint(pixel);
            //olmap.getTarget().style.cursor = 'pointer';
        } else {
            content.innerHTML = '&nbsp;';
            //olmap.getTarget().style.cursor = '';
           return false;
       }
    }

    /**
     * Add a click handler to the map to render the popup.
     */
    olmap.on('singleclick', function(evt) {
       if ((coord = displayClusterInfo(evt.pixel))) {
           overlay.setPosition(coord);
           popupActive = true;
       }
       else {
           overlay.setPosition(undefined);
           popupActive = false;
       }
    });

    // Handler for pointer movement
    olmap.on('pointermove', function(evt) {
       if (evt.dragging || popupActive || popupHover) {
           return;
       }

       if ((coord = displayClusterInfo(evt.pixel))) {
           overlay.setPosition(coord);
       }
       else {
           overlay.setPosition(undefined);
       }
    });
}

// Clustering
//--------------------
//original source: http://acuriousanimal.com/code/animatedCluster/
function addClusteredLayerOfMarkers(){
            // Define three colors that will be used to style the cluster features
            // depending on the number of features they contain.
            var colors = {
                low: [255,153,51],
                middle: [255,128,0],
                high: [204,102,0]
            };

            // Define three rules to style the cluster features.
            /*
            var lowRule = new OpenLayers.Rule({
                filter: new OpenLayers.Filter.Comparison({
                    type: OpenLayers.Filter.Comparison.BETWEEN,
                    property: "count",
                    lowerBoundary: 2,
                    upperBoundary: 5
                }),
                symbolizer: {
                    fillColor: colors.low,
                    fillOpacity: 0.9,
                    strokeColor: colors.low,
                    strokeOpacity: 0.5,
                    strokeWidth: 12,
                    pointRadius: 10,
                    label: "${count}",
                    labelOutlineWidth: 1,
                    fontColor: "#ffffff",
                    fontOpacity: 0.8,
                    fontSize: "12px"
                }
            });
            var middleRule = new OpenLayers.Rule({
                filter: new OpenLayers.Filter.Comparison({
                    type: OpenLayers.Filter.Comparison.BETWEEN,
                    property: "count",
                    lowerBoundary: 5,
                    upperBoundary: 20
                }),
                symbolizer: {
                    fillColor: colors.middle,
                    fillOpacity: 0.9,
                    strokeColor: colors.middle,
                    strokeOpacity: 0.5,
                    strokeWidth: 12,
                    pointRadius: 15,
                    label: "${count}",
                    labelOutlineWidth: 1,
                    fontColor: "#ffffff",
                    fontOpacity: 0.8,
                    fontSize: "12px"
                }
            });
            var highRule = new OpenLayers.Rule({
                filter: new OpenLayers.Filter.Comparison({
                    type: OpenLayers.Filter.Comparison.GREATER_THAN,
                    property: "count",
                    value: 20
                }),
                symbolizer: {
                    fillColor: colors.high,
                    fillOpacity: 0.9,
                    strokeColor: colors.high,
                    strokeOpacity: 0.5,
                    strokeWidth: 12,
                    pointRadius: 20,
                    label: "${count}",
                    labelOutlineWidth: 1,
                    fontColor: "#ffffff",
                    fontOpacity: 0.8,
                    fontSize: "12px"
                }
            });

            // Create a Style that uses the three previous rules
            var style = new OpenLayers.Style(null, {
                rules: [lowRule, middleRule, highRule]
            });
            */
            // Create a vector layers and add markers
            addMarkers();
			// TODO: pass these as arguments instead of global vars
			if (typeof DPCalLocs !== 'undefined') { addDPCalLocs(DPCalLocs, DPCalIconFile); }
            // Create a vector layers
            var source = new ol.source.Vector({
                features: arrayOfMarkers
            });
            var styleCache = {};
            var layerVectorForMarkers = new ol.layer.Vector({name: "Features",
                source: new ol.source.Cluster({ source: source }),
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
            /*
            var layerVectorForMarkers = new ol.layer.Vector("Features", {
                renderers: ['Canvas','SVG'],
                displayInLayerSwitcher: false,
            strategies: [
			new OpenLayers.Strategy.Cluster({distance: 15, threshold: 2})],
                styleMap:  new OpenLayers.StyleMap(style)
            });
*/
	olmap.addLayer(layerVectorForMarkers);

        // addPopup(); // Now done in main javascript slippymap_init()

}


