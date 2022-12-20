var jtgTemplateUrl = "";
var jtgBaseUrl = "";
var jtgView;
var jtgMap;
var allpoints = [];

// TODO: add waypoint, POI icons?

function jtgMapInit(mapLayersTitle='Map layers', targetid='jtg_map') {
	jtgView = new ol.View( {
		center: [0, 0],
		units: "m"  // TODO: use units from config
	} );

	jtgMap = new ol.Map ( { target: targetid,
		controls:[
			new ol.control.Attribution() 
		],
		view: jtgView } );
	var fullscreenToolbar = new ol.control.FullScreen();
	jtgMap.addControl(fullscreenToolbar);
	jtgMap.getInteractions().forEach(function(interaction) {   if (interaction instanceof ol.interaction.KeyboardPan) { interaction.setActive(false); } }, this);
	jtgMap.getInteractions().forEach(function(interaction) {   if (interaction instanceof ol.interaction.KeyboardZoom) { interaction.setActive(false); } }, this);
	mapLayerGroup = new ol.layer.Group({ title: mapLayersTitle });
	jtgMap.addLayer(mapLayerGroup);
}

function jtgAddMapLayer(mapType = 0, mapOpt = '', apiKey = '', mapName = '', isVisible = true) {
	// map types:
	// 0: OSM - mapopt is tile URL (optional)
	// 1: IGN - needs APIkey
	// 2: Bing - needs APIkey; mapopt is imagerySet
	// Need to call mapsource init script
	switch (mapType) {
		case 0: // OSM
			if ( mapOpt.length ) {
				mapUrl = mapOpt;
				if (apiKey.length) {
					mapUrl += '?apikey='+apiKey;
				}
				mapLayer = new ol.layer.Tile({ source: new ol.source.OSM({url: mapUrl}), title: mapName, type: 'base', visible: isVisible });
			}
			else {
				mapLayer = new ol.layer.Tile({ source: new ol.source.OSM(), title: mapName, type: 'base', visible: isVisible });
			}
			break;
		case 1: // IGN
			mapLayer = new ol.layer.Tile({source: new ol.source.WMTS({
					url: "https://wxs.ign.fr/"+apiKey+"/geoportail/wmts",
					layer: "GEOGRAPHICALGRIDSYSTEMS.PLANIGNV2",
					matrixSet: "PM",
					format: "image/png", 
					projection: "EPSG:3857",
					tileGrid: getIGNTileGrid(),
					style: 'normal',
					attributions: '<a href="https://www.ign.fr/" target="_blank">' +
					'<img src="https://wxs.ign.fr/static/logos/IGN/IGN.gif" title="Institut national de l\'' +
						'information géographique et forestière" alt="IGN"></a>' 
					}),
				title: mapName,
				type: 'base',
				visible: isVisible
				});
			break;	
		case 2: // Bing
			mapLayer = new ol.layer.Tile({source: new ol.source.BingMaps({
					key: apiKey,
					imagerySet: mapOpt }),
				title: mapName,
				type: 'base',
				visible: isVisible,
				});
			break;
	}
	mapLayerGroup.getLayers().push(mapLayer);
}

function drawTrack(trackData, addStartMarker = true, animatedCursor = false, colors = ['#ff00ff'] ) {
	allpoints = [];
	var gpsTracks = new ol.layer.Vector({ 
		source: new ol.source.Vector(),
		style: new ol.style.Style({
		stroke: new ol.style.Stroke({
		color: colors[colors.length-1], width: 4 }) })
	});
  	jtgMap.addLayer(gpsTracks);
	var startMarkerStyle = new ol.style.Style({
     	image: new ol.style.Icon({ src: jtgTemplateUrl+'/images/trackStart.png',
			anchorOrigin: 'bottom-left', anchor: [0.15,0] }) 
	}); 
	var endMarkerStyle = new ol.style.Style({
     	image: new ol.style.Icon({ src: jtgTemplateUrl+'/images/trackDest.png',
			anchorOrigin: 'bottom-right', anchor: [0.15,0] }) 
	});
	var iCol = 0;
	for (var itrk = 0; itrk < trackData.length; itrk++) {
		var nseg = trackData[itrk].coords.length;
		var lastidx = 0;
		for (var iseg = 0; iseg < nseg; iseg++) {
			var curCoords = trackData[itrk].coords[iseg];
			for (var ipt = 1; ipt < curCoords.length; ipt++) {
				if (Math.sign(curCoords[ipt][0]) != Math.sign(curCoords[ipt-1][0]) &&
						Math.abs(curCoords[ipt][0]-curCoords[ipt-1][0]) > 180) {
               // crossing date line: interpolate and cut track
					var x1 = curCoords[ipt-1][0];
					var x2 = curCoords[ipt][0];
					if (x1 < 0) x1 += 360;
					if (x2 < 0) x2 += 360;
					var lat = curCoords[ipt-1][1] + (180-x1) * (curCoords[ipt][1]-curCoords[ipt-1][1])/(x2-x1);
					var tmpCoords = curCoords.slice(lastidx,ipt);
					allpoints.push(...tmpCoords);
					tmpCoords.push([Math.sign(curCoords[ipt-1][0])*180.0,lat]);
					var trkGeom = new ol.geom.LineString(tmpCoords);
					trkGeom.transform('EPSG:4326',jtgView.getProjection());
					var segFeat = new ol.Feature({geometry: trkGeom, name: trackData[itrk].name});
					if (iCol < colors.length)
					{
						segFeat.setStyle(new ol.style.Style({
				      	stroke: new ol.style.Stroke({
      					color: colors[iCol], width: 4 }) }));
						iCol++;
					}
					gpsTracks.getSource().addFeature(segFeat);
					curCoords[ipt-1][0] = Math.sign(curCoords[ipt][0])*180.0;
					curCoords[ipt-1][1] = lat;
					lastidx = ipt-1;
				}
			}
			tmpCoords = curCoords.slice(lastidx,curCoords.length);
			allpoints.push(...tmpCoords);
			trkGeom = new ol.geom.LineString(tmpCoords);
			trkGeom.transform('EPSG:4326',jtgView.getProjection());
			var segFeat = new ol.Feature({geometry: trkGeom, name: trackData[itrk].name});
			if (iCol < colors.length)
			{
				segFeat.setStyle(new ol.style.Style({
			     	stroke: new ol.style.Stroke({
      			color: colors[iCol], width: 4 }) }));
				iCol++;
			}
			gpsTracks.getSource().addFeature(segFeat);
		}
		if (addStartMarker) {
			var startMarker = new ol.Feature( {
				geometry: new ol.geom.Point(ol.proj.fromLonLat(trackData[itrk].coords[0][0], jtgView.getProjection())),
				name: 'Start: '+trackData[itrk].name
			});
			startMarker.setStyle(startMarkerStyle);
			gpsTracks.getSource().addFeature(startMarker);

			var endMarker = new ol.Feature( {
				geometry: new ol.geom.Point(ol.proj.fromLonLat(trackData[itrk].coords[nseg-1][trackData[itrk].coords[nseg-1].length-1], jtgView.getProjection())),
				name: 'End: '+trackData[itrk].name
			});
			endMarker.setStyle(endMarkerStyle);
			gpsTracks.getSource().addFeature(endMarker);
		}
	}
	jtgView.fit( gpsTracks.getSource().getExtent(), {padding: [50, 50, 50, 75]} );
	jtgMap.addControl( new ol.control.ZoomToExtent( {extent: jtgView.calculateExtent()} ) );

	if (animatedCursor) {
		animatedCursorLayer = new ol.layer.Vector({
			source: new ol.source.Vector(),
			style: animated_cursor_style,
			visible: false
		});
		cursGeom = new ol.geom.LineString(allpoints);
		cursGeom.transform('EPSG:4326',jtgView.getProjection());
		animatedCursorLineFeature = new ol.Feature({ 
			geometry: cursGeom });
		animatedCursorLineFeature.setId('cursorTrack');
		animatedCursorLayer.getSource().addFeature(animatedCursorLineFeature);
		animatedCursorIcon = new ol.geom.Point( ol.proj.fromLonLat([3.79273,50.29782], jtgView.getProjection()));
		animatedCursorLayer.getSource().addFeature( new ol.Feature( { geometry: animatedCursorIcon } ) );

		jtgMap.addLayer(animatedCursorLayer);
	}
	addPopup(jtgMap);
}

function addGeoPhotos(imagelist) {
	photoIcon = new ol.style.Icon({src: jtgTemplateUrl+'/images/foto.png'} ); // TODO: check anchorpoint
	geoImgLayer = new ol.layer.Vector({title: "Geotagged Images", source: new ol.source.Vector(), style: new ol.style.Style( { image: photoIcon} ) });
	jtgMap.addLayer(geoImgLayer);

	imagelist.forEach( function (image) {
		var lonLatImg = new ol.proj.fromLonLat([image.long, image.lat],jtgView.getProjection());
		photoFeat = new ol.Feature( {geometry: new ol.geom.Point(lonLatImg), name: image.imghtml} );
		geoImgLayer.getSource().addFeature(photoFeat);
	} );
}

function addWPs(wpInfo, wpIcons) {
	wps = new ol.source.Vector();
	wpLayer = new ol.layer.Vector({title: "Waypoints", source: wps});
	jtgMap.addLayer(wpLayer);

	wpInfo.forEach( function(wp) {
		wpll = ol.proj.fromLonLat([wp.lon, wp.lat], jtgView.getProjection());
		if (wpIcons.has(wp.icon)) {
			icon = wpIcons.get(wp.icon);
		}
		else {
			icon = wpIcons.get('unknown');
		}
		var wpf = new ol.Feature({ geometry: new ol.geom.Point(wpll), name: wp.html });
		wpf.setStyle(new ol.style.Style({image: icon}));
		wps.addFeature(wpf);
	} );
}

function addPreviewTrigger() {
	prevwidth = 1080;
	prevheight = 640;
	jtgMap.once('rendercomplete', function () {
		var origSize = jtgMap.getSize();
		var origResolution = jtgMap.getView().getResolution();
		//makePreview(prevwidth, prevheight, origSize, origResolution);
		makePreview(origSize[0], origSize[1], origSize, origResolution);
		// Set preview size
		var printSize = [prevwidth, prevheight];
		//jtgMap.setSize(printSize); // Changes centering, without changing aspect ratio
		var scaling = Math.min(printSize[0] / origSize[0], printSize[1] / origSize[1]);
		//jtgMap.getView().setResolution(origResolution / scaling);
	} );
}

function getAvgTime(speed_str, length, decimal_separator)  {

		// Speed format is with decimal separator or . or ,
		var speed = speed_str.replace(decimal_separator, '.');
		speed = speed.replace(',', '.');
		if (speed === 0)
		{
			// set speed to 1 when null!
			document.getElementById('speed').value = '1';
			speed = 1;
		}
		var time = length / speed;
		var timestring = time.toString();
		var parts = timestring.split(".");
		if (!parts[1]) parts[1] = 0;
		var m1 = 0+"."+parts[1].toString();
		var m2 = m1 / 10 * 6;
		m2 = runde(m2,2);
		var m = m2.split(".");
		var time2 = parts[0] + "h " + m[1] + "m";
		document.getElementById('time').value = time2;
		//document.getElementById('pace').value = '';
}

function getAvgTimeFromPace(pace_str, length, decimal_separator) {

	// check pace format
	//var n = pace_str.indexOf(":");
	if (pace_str.indexOf(":")>=0)
	{
		// Pace format is time format mm:ss
		var pace_parts = pace_str.split(":");
		if (!pace_parts[1]) pace_parts[1] = 0;
		if (!pace_parts[0]) pace_parts[0] = 0;
		var pace = pace_parts[1]/60 + pace_parts[0]/1;
	}
	else
	{
		// Pace format is decimal separator or . or ,
		var pace_str = pace_str.replace(decimal_separator, '.');
		pace = pace.replace(',', '.');
	}

	var time = length * pace / 60;
	var timestring = time.toString();
	var parts = timestring.split(".");
	if (!parts[1]) parts[1] = 0;
	var m1 = 0+"."+parts[1].toString();
	var m2 = m1 / 10 * 6;
	m2 = runde(m2,2);
	var m = m2.split(".");
	var time2 = parts[0] + "h " + m[1] + "m";
	document.getElementById('time').value = time2;
	document.getElementById('speed').value = '';
}

function runde(x, n) {
  if (n < 1 || n > 14) return false;
  var e = Math.pow(10, n);
  var k = (Math.round(x * e) / e).toString();
  if (k.indexOf('.') == -1) k += '.';
  k += e.toString().substring(1);
  return k.substring(0, k.indexOf('.') + n+1);
}


function submitform(pressbutton){
	if (pressbutton) {
		document.adminForm.task.value=pressbutton;
	}
	if (typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	}
	document.adminForm.submit();
}

function getCycleTileURL(bounds) {
   var res = this.map.getResolution();
   var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
   var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
   var z = this.map.getZoom();
   var limit = Math.pow(2, z);

   if (y < 0 || y >= limit)
   {
     return null;
   }
   else
   {
     x = ((x % limit) + limit) % limit;

     return this.url + z + "/" + x + "/" + y + "." + this.type;
   }
}

/*
 * Funktion zum Zerlegen der URL um die Parameter zu erhalten (für den Permalink)
 * Splits the URL in its parameters
 */
function get_parameters() {
 // erzeugt für jeden in der url übergebenen parameter einen wert
 // bsp: x.htm?nachname=Munch&vorname=Alex&bildfile=wasserfall.jpg  erzeugt
 // variable nachname mit wert Munch  und
 // variable vorname mit wert Alex
 // variable bildfile mit wert wasserfall.jpg
 var hier = document.URL;
 var parameterzeile = hier.substr((hier.indexOf("?")+1));
 var trennpos;
 var endpos;
 var paramname;
 var paramwert;
 var parameters = new Object();
 while (parameterzeile != "") {
  trennpos = parameterzeile.indexOf("=");
  endpos = parameterzeile.indexOf("&");
  if (endpos < 0) { endpos = 500000; }
  paramname = parameterzeile.substr(0,trennpos);
  paramwert = parameterzeile.substring(trennpos+1,endpos);
  parameters[paramname] = paramwert;
  //eval (paramname + " = \"" + paramwert + "\"");
  parameterzeile = parameterzeile.substr(endpos+1);
 }
 return parameters;
}

/*
 * Wie der Name schon sagt ebenfalls für den Permalink, überprüft ob die Parameter in der URL gefunden wurden und überschreibt
 * sie gegebenenfalls.
 * Checks the url for parameters of the permalink and overwrites the default values if necessary.
 */
function checkForPermalink() {
	var parameters = get_parameters();

	if (parameters['zoom'] != null)
		zoom = parseInt(parameters['zoom']);
	if (parameters['lat'] != null)
		lat = parseFloat(parameters['lat']);
	if (parameters['lon'] != null)
		lon = parseFloat(parameters['lon']);
}

/*
 * Für den Layer-Switcher mit Buttons
 */
function setLayer(id) {
	if (document.getElementById("layer") != null) {
		for (var i=0;i<layers.length;++i)
			document.getElementById(layers[i][1]).className = "";
	}
	varName = layers[id][0];
	name = layers[id][1];
	map.setBaseLayer(varName);
	if (document.getElementById("layer") != null)
		document.getElementById(name).className = "active";
}
/*
 * Schaltet die Beschreibung der Karte an- und aus.
 * Toggles the description of the map.
 */
function toggleInfo() {
	var state = document.getElementById('description').className;
	if (state == 'hide') {
		// Info anzeigen
		document.getElementById('description').className = '';
		document.getElementById('descriptionToggle').innerHTML = text[1];
	}
	else {
		// Info verstecken
		document.getElementById('description').className = 'hide';
		document.getElementById('descriptionToggle').innerHTML = text[0];
	}
}

/*
 * Gibt eine Fehlermeldung aus, wenn die Version der JavaScript Datei nicht mit der erforderlichen übereinstimmt
 * Outputs an error if the version of the JavaScript-File does not match the required one
 */

function checkUtilVersion(version) {
	var thisFileVersion = 4;
	if (version != thisFileVersion) {
		alert("map.html and util.js versions do not match.\n\nPlease reload the page using your browsers 'reload' feature.\n\nIf the problem persists and you are the owner of this site, you may need to update the map's files . ");
	}
}

// MvL: could move this to a separate file ?; similar function used in jtgOverview.js, but no clusterig here?
function addPopup(olmap) {
    /**
     * Elements that make up the popup.
     */
    var container = document.getElementById('popup');
    var closer = document.getElementById('popup-closer');
    if (container == null || closer == null)
       return;

    var popupActive = false;
    /**
     * Create an overlay to anchor the popup to the map.
     */
    overlay = new ol.Overlay({
        element: container,
        position: undefined,
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
        return false;
    };

    olmap.addOverlay(overlay);
 
    /**
     * Add a click handler to the map to render the popup.
     * MvL: change this to add handler to points
     */
    olmap.on('singleclick', function(evt) {
		var point = 0;
		olmap = evt.map;
		pixel = olmap.getEventPixel(evt.originalEvent);
		olmap.forEachFeatureAtPixel(evt.pixel, function(feature) {
			if (feature.getGeometry().getType() == 'Point') { 
				point = feature;
			}
		});
		if (point) {
			overlay.setPosition(undefined);
			overlay.setMap(evt.map);
			overlay.setPosition(evt.coordinate);
			// Set content of popup
			var content = document.getElementById('popup-content');
			content.innerHTML = point.get('name');
			popupActive = true;
		}
		else {
			overlay.setPosition(undefined);
			popupActive = false;
		}
	});
}

function makePreview(width, height, origSize, origResolution) {
	var mapCanvas = document.createElement('canvas');
	mapCanvas.width = width;
	mapCanvas.height = height;
	var mapContext = mapCanvas.getContext('2d');
	Array.prototype.forEach.call(
		document.querySelectorAll('.ol-layer canvas'),
		function (canvas) {
			if (canvas.width > 0) {
				var opacity = canvas.parentNode.style.opacity;
				mapContext.globalAlpha = opacity === '' ? 1 : Number(opacity);
				var transform = canvas.style.transform;
				// Get the transform parameters from the style's transform matrix
				var matrix = transform
					.match(/^matrix\(([^\(]*)\)$/)[1]
					.split(',')
					.map(Number);
				// Apply the transform to the export map context

				CanvasRenderingContext2D.prototype.setTransform.apply(
					mapContext,
					matrix
				);
				mapContext.drawImage(canvas, 0, 0);
			}
		}
	);

	dataUrl = mapCanvas.toDataURL('image/png');
	imgdata = dataUrl.match(/data:(image\/.+);base64,(.+)/);
	document.getElementById('mappreview').value=imgdata[2];

	// Reset original map size
	jtgMap.setSize(origSize);
	jtgMap.getView().setResolution(origResolution);
}

function makeGraph(elementid, axes, series, distanceLabel, distanceUnits, clickLabel, bgColor='#FFFFFF', autocenter=false, animatedCursor=true, animatedCursorLayer, animatedCursorIcon, allpoints) {
	// TODO: make animated cursor switchable
	document.addEventListener('DOMContentLoaded', function () {
		const chart = Highcharts.chart(elementid, {
			chart: {
				type: 'line',
				zoomType: 'xy',
				backgroundColor: bgColor
			},
			credits: {
				enabled: 'false'
			},
			plotOptions: {
				area: {
					stacking: 'normal',
					lineColor: '#FFFFFF',
					lineWidth: 1,
					marker: {
						lineWidth: 1,
						lineColor: '#FFFFFF'
					}
				},
				series: {
					fillOpacity: 0.1
				}
			},
			title: { text: null },
			xAxis: [{
				labels: {
					formatter: function() {
						return this.value + ' ' + distanceUnits;
					}
				},
				tooltip: {
					valueDecimals: 2,
					valueSuffix: distanceUnits
				}
			}],
			yAxis: axes,
			plotOptions: {
				series: {
					point: {
						events: {
							mouseOver: function () {
								if (animatedCursor) {
									var index = this.series.processedXData.indexOf(this.x);
									hover_profil_graph(animatedCursorLayer, animatedCursorIcon, allpoints, index, autocenter);
								}
							}
						}
					},
					events: {
						mouseOut: function () {
							if (animatedCursor) {
								out_profil_graph(animatedCursorLayer);
							}
						}
					}
				}
			},
			tooltip: {
				valueDecimals: 2,
				formatter: function() {
					var s = distanceLabel+' : '
						+ this.x + ' '
						+ distanceUnits;
					this.points.forEach( function(point) {
						s += '<br/>'+ point.series.name +': '+
						point.y + ' ' + point.series.options.unit;
					});
					return s;
				},
				shared: true
			},
			legend: {
				layout: 'vertical',
				align: 'left',
				x: 120,
				verticalAlign: 'top',
				y: 0,
				floating: true,
				labelFormatter: function() {
					if (clickLabel.length) 
						return this.name + ' (' + clickLabel + ')';
					else
						return this.name;
				}
			},
			series: series
		});
	});
}

/*
 *   Function needed for the IGN geoportail maps from
 *        the French national geographic institute
 *   Taken from the example code: https://openlayers.org/en/latest/examples/wmts-ign.html
 *
 *   For information about apiKeys: https://geoservices.ign.fr/blog/2017/06/28/geoportail_sans_compte.html
 */

function getIGNTileGrid() {
  var resolutions = [];
  var matrixIds = [];
  var maxResolution = ol.extent.getWidth(ol.proj.get('EPSG:3857').getExtent()) / 256;

  for (var i = 0; i < 18; i++) {
    matrixIds[i] = i.toString();
    resolutions[i] = maxResolution / Math.pow(2, i);
  }

  return new ol.tilegrid.WMTS({
    origin: [-20037508, 20037508],
    resolutions: resolutions,
    matrixIds: matrixIds
  });
}
