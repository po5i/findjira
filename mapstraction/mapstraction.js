/*
   Copyright (c) 2006, Tom Carden, Steve Coast
   All rights reserved.

   Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * Neither the name of the Mapstraction nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */


// Use http://jsdoc.sourceforge.net/ to generate documentation

//////////////////////////// 
//
// utility to functions, TODO namespace or remove before release
//
///////////////////////////

/**
 * $, the dollar function, elegantising getElementById()
 * @returns an element
 */
function $() {
  var elements = new Array();
  for (var i = 0; i < arguments.length; i++) {
    var element = arguments[i];
    if (typeof element == 'string')
      element = document.getElementById(element);
    if (arguments.length == 1)
      return element;
    elements.push(element);
  }
  return elements;
}

/**
 * loadScript is a  JSON data fetcher 
 */
function loadScript(src,callback) {
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = src;
  if (callback) {
    var evl=new Object();
    evl.handleEvent=function (e){callback();};
    script.addEventListener('load',evl,true);
  }
  document.getElementsByTagName("head")[0].appendChild(script);
  return;
}

function convertLatLonXY_Yahoo(point,level){ //Mercator
  var size = 1 << (26 - level);
  var pixel_per_degree = size / 360.0;
  var pixel_per_radian = size / (2 * Math.PI)
    var origin = new YCoordPoint(size / 2 , size / 2)
    var answer = new YCoordPoint();
  answer.x = Math.floor(origin.x + point.lon * pixel_per_degree)
    var sin = Math.sin(point.lat * Math.PI / 180.0)
    answer.y = Math.floor(origin.y + 0.5 * Math.log((1 + sin) / (1 - sin)) * -pixel_per_radian)
    return answer;
}


/**
 *
 */
function loadStyle(href) {
  var link = document.createElement('link');
  link.type = 'text/css';
  link.rel = 'stylesheet';
  link.href = href;
  document.getElementsByTagName("head")[0].appendChild(link);
  return;
}

///////////////////////////// 
//
// Mapstraction proper begins here
//
/////////////////////////////

/**
 * Mapstraction instantiates a map with some API choice into the HTML element given
 * @param {String} element The HTML element to replace with a map
 * @param {String} api The API to use, one of 'google', 'yahoo' or 'microsoft'
 * @constructor
 */
function Mapstraction(element,api) {
  this.api = api; // could detect this from imported scripts?
  this.map = undefined;
  this.mapElement = $(element);
  this.eventListeners = new Array();
  this.markers = new Array();

  me = this;
  switch (this.api) {
    case 'yahoo':
      if (YMap) {
        this.map = new YMap(this.mapElement);
        YEvent.Capture(this.map,EventsList.MouseClick,function(event,location) { me.clickHandler(location.Lat,location.Lon,location,me) });
        YEvent.Capture(this.map,EventsList.changeZoom,function() { me.moveendHandler(me) });
        YEvent.Capture(this.map,EventsList.endPan,function() { me.moveendHandler(me) });

      }
      else {
        alert('Yahoo map script not imported');
      }
      break;
    case 'google':
      if (GMap2) {
        if (GBrowserIsCompatible()) {
          this.map = new GMap2(this.mapElement);
          GEvent.addListener(this.map, 'click', function(marker,location) {me.clickHandler(location.y,location.x,location,me)});
          GEvent.addListener(this.map, 'moveend', function() {me.moveendHandler(me)});
        }
        else {
          alert('browser not compatible with Google Maps');
        }
      }
      else {
        alert('Google map script not imported');
      }
      break;
    case 'microsoft':
      if (VEMap) {
        this.mapElement.style.position='relative';
        this.map = new VEMap(this.mapElement.id);
        this.map.LoadMap();
        this.map.AttachEvent("onclick", function(e) { me.clickHandler(e.view.LatLong.Latitude, e.view.LatLong.Longitude, me); });
        this.map.AttachEvent("onchangeview", function(e) {me.moveendHandler(me)});
      }
      else {
        alert('Virtual Earth script not imported');
      }
      break;
    case 'openlayers':
      this.map = new OpenLayers.Map(this.mapElement.id);
      break;
    default:
      alert(this.api + ' not supported by mapstraction');
  }
}

/////////////////////////
// 
// Event Handling
//
///////////////////////////

Mapstraction.prototype.clickHandler = function(lat,lon, me) { //FIXME need to consolidate some of these handlers... 
  for(var i = 0; i < this.eventListeners.length; i++) {
    if(this.eventListeners[i][1] == 'click') {
      this.eventListeners[i][0](new LatLonPoint(lat,lon));
    }
  }
}

Mapstraction.prototype.moveendHandler = function(me) {
  for(var i = 0; i < this.eventListeners.length; i++) {
    if(this.eventListeners[i][1] == 'moveend') {
      this.eventListeners[i][0]();
    }
  }
}

Mapstraction.prototype.addEventListener = function(type, func) {
  var listener = new Array();
  listener.push(func);
  listener.push(type);
  this.eventListeners.push(listener);
}

////////////////////
//
// map manipulation
//
/////////////////////

/**
 * addSmallControls adds a small map panning control and zoom buttons to the map
 */
Mapstraction.prototype.addSmallControls = function() {

  switch (this.api) {
    case 'yahoo':
      this.map.addPanControl();
      this.map.addZoomShort();
      break;
    case 'google':
      this.map.addControl(new GSmallMapControl());
      break;
    case 'openlayers':
      this.map.addControl(new OpenLayers.Control.LayerSwitcher());
      break;
  }
}

/**
 * addLargeControls adds a small map panning control and zoom buttons to the map
 */
Mapstraction.prototype.addLargeControls = function() {
  switch (this.api) {
    case 'yahoo':
      this.map.addPanControl();
      this.map.addZoomLong();
      break;
    case 'google':
      this.map.addControl(new GLargeMapControl());
      this.map.addControl(new GMapTypeControl());
      this.map.addControl(new GScaleControl()) ;
      this.map.addControl(new GOverviewMapControl()) ;
      break;
  }
}

/**
 * addMapTypeControls adds a map type control to the map (streets, aerial imagery etc)
 */
Mapstraction.prototype.addMapTypeControls = function() {
  switch (this.api) {
    case 'yahoo':
      this.map.addTypeControl();
      break;
    case 'google':
      this.map.addControl(new GMapTypeControl());
      break;
  }
}

/**
 * setCenterAndZoom centers the map to some place and zoom level
 * @param {LatLonPoint} point Where the center of the map should be
 * @param {int} zoom The zoom level where 0 is all the way out.
 */
Mapstraction.prototype.setCenterAndZoom = function(point, zoom) {
  switch (this.api) {
    case 'yahoo':
      var yzoom = 18 - zoom; // maybe?
      this.map.drawZoomAndCenter(point.toYahoo(),yzoom);
      break;
    case 'google':
      this.map.setCenter(point.toGoogle(), zoom);
      break;
    case 'microsoft':
      this.map.SetCenterAndZoom(point.toMicrosoft(),zoom);
      break;
    case 'openlayers':
      this.map.setCenter(new OpenLayers.LonLat(point.longitude, point.latitude), zoom);
      break;
    default:
      alert(this.api + ' not supported by Mapstraction.setCenterAndZoom');
  }
}


/**
 * openBubble opens a bubble with the given html/text
 * @param {String} text the text/html to display in the bubble
 */
Mapstraction.prototype.openBubble = function(marker, text) {
  // FIXME this should be part of marker but then the marker needs to know what type it is (Y, G, M)..
  switch (this.api) {
    case 'yahoo':
      ypin = marker.proprietary_marker;
      ypin.openSmartWindow(text);
      break;
    case 'google':
      alert('openBubble not implemented for google FIXME');
      break;
    case 'microsoft':
      alert('openBubble not implemented for google FIXME');
      break;
    default:
      alert(this.api + ' not supported by Mapstraction.addMarker');
  }
}


/**
 * addMarker adds a marker pin to the map
 * @param {Marker} marker The marker to add
 */
Mapstraction.prototype.addMarker = function(marker) {
  switch (this.api) {
    case 'yahoo':
      ypin = marker.toYahoo();
      marker.setChild(ypin);
      this.map.addOverlay(ypin);
      this.markers.push(marker);
      break;
    case 'google':
      gpin = marker.toGoogle();
      marker.setChild(gpin);
      this.map.addOverlay(gpin);
      this.markers.push(marker);
      break;
    case 'microsoft':
      mpin = marker.toMicrosoft();
      marker.setChild(mpin); // FIXME: MSFT maps remove the pin by pinID so this isn't needed?
      this.map.AddPushpin(mpin);
      this.markers.push(marker);
      break;
    case 'openlayers':
      //this.map.addPopup(new OpenLayers.Popup("chicken", new OpenLayers.LonLat(5,40), new OpenLayers.Size(200,200), "example popup"));
      break;
    default:
      alert(this.api + ' not supported by Mapstraction.addMarker');
  }
}

/**
 * removeMarker removes a Marker from the map
 * @param {Marker} marker The marker to remove
 */
Mapstraction.prototype.removeMarker = function(marker) {
  var tmparray = new Array();
  while(this.markers.length > 0){
    current_marker = this.markers.pop();
    if(marker == current_marker) {
      switch (this.api) {
        case 'google':
          this.map.removeOverlay(marker.proprietary_marker);
          break;
        case 'yahoo':
          this.map.removeOverlay(marker.proprietary_marker);
          break;
        case 'microsoft':
          this.map.DeletePushpin(marker.pinID);
          break;
      }
      marker.onmap = false;
      break;
    } else {
      tmparray.push(current_marker);
    }
  }
  this.markers = this.markers.concat(tmparray);
}

/**
 * removeAllMarkers removes all the Markers on a map
 */
Mapstraction.prototype.removeAllMarkers = function() {
  switch (this.api) {
    case 'yahoo':
      this.map.removeMarkersAll();
      break;
    case 'google':
      this.map.clearOverlays();
      break;
    case 'microsoft':
      this.map.DeleteAllPushpins();
      break;
  }
  this.map.markers = new Array(); // clear the mapstraction list of markers too
}

/**
 * getCenter gets the central point of the map
 * @returns  the center point of the map
 * @type LatLonPoint
 */
Mapstraction.prototype.getCenter = function() {
  var point = undefined;
  switch (this.api) {
    case 'yahoo':
      var pt = this.map.getCenterLatLon();
      point = new LatLonPoint(pt.Lat,pt.Lon);
      break;
    case 'google':
      var pt = this.map.getCenter();
      point = new LatLonPoint(pt.lat(),pt.lon());
      break;
    case 'microsoft':
      point = new LatLonPoint(this.map.GetCenterLatitude(),this.map.GetCenterLongitude());
      break;
    default:
      alert(this.api + ' not supported by Mapstraction.getCenter');
  }
  return point;
}

/**
 * setCenter sets the central point of the map
 * @param {LatLonPoint} point The point at which to center the map
 */
Mapstraction.prototype.setCenter = function(point) {
  switch (this.api) {
    case 'yahoo':
      this.map.panToLatLon(point.toYahoo());
      break;
    case 'google':
      this.map.setCenter(point.toGoogle());
      break;
    case 'microsoft':
      this.map.SetCenter(point.toMicrosoft());
      break;
    default:
      alert(this.api + ' not supported by Mapstraction.setCenter');
  }
}
/**
 * setZoom sets the zoom level for the map
 * MS doesn't seem to do zoom=0, and Gg's sat goes closer than it's maps, and MS's sat goes closer than Y!'s
 * TODO: Mapstraction.prototype.getZoomLevels or something.
 * @param {int} zoom The (native to the map) level zoom the map to.
 */
Mapstraction.prototype.setZoom = function(zoom) {
  switch (this.api) {
    case 'yahoo':
      var yzoom = 18 - zoom; // maybe?
      this.map.setZoomLevel(yzoom);
      break;
    case 'google':
      this.map.setZoom(zoom);
      break;
    case 'microsoft':
      this.map.SetZoomLevel(zoom);
      break;
    default:
      alert(this.api + ' not supported by Mapstraction.setZoom');
  }
}

/**
 * getZoom returns the zoom level of the map
 * @returns the zoom level of the map
 * @type int
 */
Mapstraction.prototype.getZoom = function() {
  switch (this.api) {
    case 'yahoo':
      return 18 - this.map.getZoomLevel(); // maybe?
    case 'google':
      return this.map.getZoom();
    case 'microsoft':
      return this.map.GetZoomLevel();
    default:
      alert(this.api + ' not supported by Mapstraction.getZoom');
  }
}


// any use this being a bitmask? Should HYBRID = ROAD | SATELLITE?
Mapstraction.ROAD = 1;
Mapstraction.SATELLITE = 2;
Mapstraction.HYBRID = 3;

/**
 * setMapType sets the imagery type for the map.
 * The type can be one of:
 * Mapstraction.ROAD
 * Mapstraction.SATELLITE
 * Mapstraction.HYBRID
 * @param {int} type The (native to the map) level zoom the map to.
 */
Mapstraction.prototype.setMapType = function(type) {
  switch (this.api) {
    case 'yahoo':
      switch(type) {
        case Mapstraction.ROAD:
          this.map.setMapType(YAHOO_MAP_REG);
          break;
        case Mapstraction.SATELLITE:
          this.map.setMapType(YAHOO_MAP_SAT);
          break;
        case Mapstraction.HYBRID:
          this.map.setMapType(YAHOO_MAP_HYB);
          break;
        default:
          this.map.setMapType(YAHOO_MAP_REG);
      }
      break;
    case 'google':
      switch(type) {
        case Mapstraction.ROAD:
          this.map.setMapType(G_NORMAL_MAP);
          break;
        case Mapstraction.SATELLITE:
          this.map.setMapType(G_SATELLITE_MAP);
          break;
        case Mapstraction.HYBRID:
          this.map.setMapType(G_HYBRID_MAP);
          break;
        default:
          this.map.setMapType(G_NORMAL_MAP);
      }
      break;
    case 'microsoft':
      // TODO: oblique?
      switch(type) {
        case Mapstraction.ROAD:
          this.map.SetMapStyle(Msn.VE.MapStyle.Road);
          break;
        case Mapstraction.SATELLITE:
          this.map.SetMapStyle(Msn.VE.MapStyle.Aerial);
          break;
        case Mapstraction.HYBRID:
          this.map.SetMapStyle(Msn.VE.MapStyle.Hybrid);
          break;
        default:
          this.map.SetMapStyle(Msn.VE.MapStyle.Road);
      }
      break;
    default:
      alert(this.api + ' not supported by Mapstraction.setType');
  }
}


/**
 * getBounds gets the BoundingBox of the map
 * @returns the bounding box for the current map state
 * @type BoundingBox
 */
Mapstraction.prototype.getBounds = function () {
  switch (this.api) {
    case 'google':
      var gbox = this.map.getBounds();
      var sw = gbox.getSouthWest();
      var ne = gbox.getNorthEast();
      return new BoundingBox(sw.lat(), sw.lng(), ne.lat(), ne.lng());
      break;
    case 'yahoo':
      var ybox = this.map.getBoundsLatLon();
      return new BoundingBox(ybox.LatMin, ybox.LonMin, ybox.LatMax, ybox.LonMax);
      break;
    case 'microsoft':
      var mbox = this.map.GetMapView();
      var nw = mbox.TopLeftLatLong;
      var se = mbox.BottomRightLatLong;
      return new BoundingBox(se.Latitude,nw.Longitude,nw.Latitude,se.Longitude);
      break;
  }
}

/**
 * setBounds sets the map to the appropriate location and zoom for a given BoundingBox
 * @param {BoundingBox} the bounding box you want the map to show
 */
Mapstraction.prototype.setBounds = function(bounds){
  var sw = bounds.getSouthWest();
  var ne = bounds.getNorthEast();
  switch (this.api) {
    case 'google':
      var gbounds = new GLatLngBounds(new GLatLng(sw.lat,sw.lon),new GLatLng(ne.lat,ne.lon));
      this.map.setCenter(gbounds.getCenter(), this.map.getBoundsZoomLevel(gbounds));
      break;

    case 'yahoo':
      if(sw.lon > ne.lon)
        sw.lon -= 360;
      var center = new YGeoPoint((sw.lat + ne.lat)/2,
          (ne.lon + sw.lon)/2);

      var container = this.map.getContainerSize();
      for(var zoom = 1 ; zoom <= 17 ; zoom++){
        var sw_pix = convertLatLonXY_Yahoo(sw,zoom);
        var ne_pix = convertLatLonXY_Yahoo(ne,zoom);
        if(sw_pix.x > ne_pix.x)
          sw_pix.x -= (1 << (26 - zoom)); //earth circumference in pixel
        if(Math.abs(ne_pix.x - sw_pix.x)<=container.width
            && Math.abs(ne_pix.y - sw_pix.y) <= container.height){
          this.map.drawZoomAndCenter(center,zoom); //Call drawZoomAndCenter here: OK if called multiple times anyway
          break;
        }
      }
      break;
    case 'microsoft':
      this.map.SetMapView([new VELatLong(sw.lat,sw.lon),new VELatLong(ne.lat,ne.lon)]);
      this.map.ZoomIn(); //For some reason, the zoom has to be incremented once more
      break;
  }
}

/**
 * getMap returns the native map object that mapstraction is talking to
 * @returns the native map object mapstraction is using
 */
Mapstraction.prototype.getMap = function() {
  // FIXME in an ideal world this shouldn't exist right?
  return this.map;
}

//////////////////////////////
//
//   LatLonPoint
//
/////////////////////////////

/**
 * LatLonPoint is a point containing a latitude and longitude with helper methods
 * @param {double} lat is the latitude
 * @param {double} lon is the longitude
 * @returns a new LatLonPoint
 * @type LatLonPoint
 */
function LatLonPoint(lat,lon) {
  // TODO error if undefined?
  //  if (lat == undefined) alert('undefined lat');
  //  if (lon == undefined) alert('undefined lon');
  this.lat = lat;
  this.lon = lon;
  this.lng = lon; // lets be lon/lng agnostic
}

/**
 * toYahoo returns a Y! maps point
 * @returns a YGeoPoint
 */
LatLonPoint.prototype.toYahoo = function() {
  return new YGeoPoint(this.lat,this.lon);
}
/**
 * toGoogle returns a Google maps point
 * @returns a GLatLng
 */
LatLonPoint.prototype.toGoogle = function() {
  return new GLatLng(this.lat,this.lon);
}
/**
 * toMicrosoft returns a VE maps point
 * @returns a VELatLong
 */
LatLonPoint.prototype.toMicrosoft = function() {
  return new VELatLong(this.lat,this.lon);
}
/**
 * toString returns a string represntation of a point
 * @returns a string like '51.23, -0.123'
 * @type String
 */
LatLonPoint.prototype.toString = function() {
  return this.lat + ', ' + this.lon;
}
/**
 * distance returns the distance in meters between two points
 * @param {LatLonPoint} otherPoint The other point to measure the distance from to this one
 * @returns the distance between the points in meters
 * @type double
 */
LatLonPoint.prototype.distance = function(otherPoint) {
  var d,dr;
  with (Math) {
    dr = 0.017453292519943295; // 2.0 * PI / 360.0; or, radians per degree
    d = cos(otherPoint.lon*dr - this.lon*dr) * cos(otherPoint.lat*dr - this.lat*dr);
    return acos(d)*6378.137; // equatorial radius
  }
  return -1; 
}
/**
 * equals tests if this point is the same as some other one
 * @param {LatLonPoint} otherPoint The other point to test with
 * @returns true or false
 * @type boolean
 */
LatLonPoint.prototype.equals = function(otherPoint) {
  return this.lat == otherPoint.lat && this.lon == otherPoint.lon;
}

//////////////////////////
//
//  BoundingBox
//
//////////////////////////

/**
 * BoundingBox creates a new bounding box object
 * @param {double} swlat the latitude of the south-west point
 * @param {double} swlon the longitude of the south-west point
 * @param {double} nelat the latitude of the north-east point
 * @param {double} nelon the longitude of the north-east point
 * @returns a new BoundingBox
 * @type BoundinbBox
 * @constructor
 */
function BoundingBox(swlat, swlon, nelat, nelon) {
  //FIXME throw error if box bigger than world
  //alert('new bbox ' + swlat + ',' +  swlon + ',' +  nelat + ',' + nelon);
  this.sw = new LatLonPoint(swlat, swlon);
  this.ne = new LatLonPoint(nelat, nelon);
}

/**
 * getSouthWest returns a LatLonPoint of the south-west point of the bounding box
 * @returns the south-west point of the bounding box
 * @type LatLonPoint
 */
BoundingBox.prototype.getSouthWest = function() {
  return this.sw;
}

/**
 * getNorthEast returns a LatLonPoint of the north-east point of the bounding box
 * @returns the north-east point of the bounding box
 * @type LatLonPoint
 */
BoundingBox.prototype.getNorthEast = function() {
  return this.ne;
}

/**
 * isEmpty finds if this bounding box has zero area
 * @returns whether the north-east and south-west points of the bounding box are the same point
 * @type boolean
 */
BoundingBox.prototype.isEmpty = function() {
  return this.ne == this.sw; // is this right? FIXME
}

/**
 * contains finds whether a given point is within a bounding box
 * @param {LatLonPoint} point the point to test with
 * @returns whether point is within this bounding box
 * @type boolean
 */
BoundingBox.prototype.contains = function(point){
  return point.lat >= this.sw.lat && point.lat <= this.ne.lat && point.lon>= this.sw.lon && point.lon <= this.ne.lon;
}

/**
 * toSpan returns a LatLonPoint with the lat and lon as the height and width of the bounding box
 * @returns a LatLonPoint containing the height and width of this bounding box
 * @type LatLonPoint
 */
BoundingBox.prototype.toSpan = function() {
  return new LatLonPoint( Math.abs(this.sw.lat - this.ne.lat), Math.abs(this.sw.lon - this.ne.lon) );
}

//////////////////////////////
//
//  Marker
//
///////////////////////////////

/**
 * Marker create's a new marker pin
 * @param {LatLonPoint} point the point on the map where the marker should go
 * @constructor
 */
function Marker(point) {
  this.location = point;
  this.onmap = false;
  this.proprietary_marker = false;
  this.pinID = "mspin-"+new Date().getTime()+'-'+(Math.floor(Math.random()*Math.pow(2,16)));
}

Marker.prototype.setChild = function(some_proprietary_marker) {
  this.proprietary_marker = some_proprietary_marker;
  this.onmap = true
}

Marker.prototype.setLabel = function(labelText) {
  this.labelText = labelText;
}

/**
 * setInfoBubble sets the html/text content for a bubble popup for a marker
 * @param {String} infoBubble the html/text you want displayed
 */
Marker.prototype.setInfoBubble = function(infoBubble) {
  this.infoBubble = infoBubble;
}

/**
 * setIcon sets the icon for a marker
 * @param {String} iconUrl The URL of the image you want to be the icon
 */
Marker.prototype.setIcon = function(iconUrl){
  this.iconUrl = iconUrl;
}

/**
 * toYahoo returns a Yahoo Maps compatible marker pin
 * @returns a Yahoo Maps compatible marker
 */
Marker.prototype.toYahoo = function() {
  var ymarker;
  if(this.iconUrl){
    ymarker = new YMarker(this.location.toYahoo (),new YImage(this.iconUrl
          ));
  }else{
    ymarker = new YMarker(this.location.toYahoo());
  }
  if(this.labelText) {
    ymarker.addLabel(this.labelText);

  }

  if(this.infoBubble) {
    var theInfo = this.infoBubble;
    YEvent.Capture(ymarker, EventsList.MouseClick, function() {
        ymarker.openSmartWindow(theInfo); });
  }

  return ymarker;
}

/**
 * toGoogle returns a Google Maps compatible marker pin
 * @returns Google Maps compatible marker
 */
Marker.prototype.toGoogle = function() {
  var options = new Object();
  if(this.labelText) {
    options.title =  this.labelText;
  }
  if(this.iconUrl){
    options.icon = new GIcon(G_DEFAULT_ICON,this.iconUrl);
  }
  var gmarker = new GMarker( this.location.toGoogle(),options);


  if(this.infoBubble) {
    var theInfo = this.infoBubble;
    GEvent.addListener(gmarker, "click", function() {
        gmarker.openInfoWindowHtml(theInfo);
        });
  }
  return gmarker;
}

/**
 * toMicrosoft returns a MSFT VE compatible marker pin
 * @returns MSFT VE compatible marker
 */
Marker.prototype.toMicrosoft = function() {
  var pin = new VEPushpin(this.pinID,this.location.toMicrosoft(),
      this.iconUrl,this.labelText,this.infoBubble);
  return pin;
}
