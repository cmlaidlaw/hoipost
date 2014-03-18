HPGeo = function () {

    'use strict';

    //geo data config and bookeeping
    this.latLng = false;
    this.highAccuracy = true;
    this.headings = [];
    this.maxHeadings = 20;
    this.lastUpdate = false;
    this.error = false;

};

HPGeo.prototype.queryPosition = function (successCallback, errorCallback) {
    
    'use strict';
    
    var geo = this;
    
    if (navigator.geolocation) {

        //wrap the callbacks in anonymous functions to preserve the object's
        //execution context
        navigator.geolocation.getCurrentPosition(
            function (position) {
                geo.latLng = {
                    lat : position.coords.latitude,
                    lng : position.coords.longitude
                };
                geo.error = false;
                geo.lastUpdate = Math.floor( Date.now() / 1000 );
                successCallback(geo.latLng);
            },
            function (error) {
                geo.error = geo.parseError(error);
                errorCallback(geo.error);
            },
            {
                enableHighAccuracy: geo.highAccuracy,
                timeout: 1000, //1 second timeout
                maximumAge: 300000 //5 minute maximum age
            }
        );

    } else {

        geo.error = 'Geolocation unavailable';
        errorCallback(geo.error);

    }

};

HPGeo.prototype.parseError = function (error) {

    'use strict';

    switch (error.code)  {  
        case error.PERMISSION_DENIED:
            return 'User did not share geolocation data';
            break;
        case error.POSITION_UNAVAILABLE:
            return 'Could not detect current position';
            break;
        case error.TIMEOUT:
            return 'Position query timed out';
            break;
        default:
            return 'Unknown error';
            break;
    }

};

HPGeo.prototype.formatRelation = function (latA, lngA, latB, lngB) {
    
    'use strict';
    
    var rad = Math.PI / 180, r1, r2, dt, dg, dts, dgs, a, d, b, localization = window._H.localization, h, s;
    
    r1 = latA * rad;
    r2 = latB * rad;
        
    dt = r2 - r1;
    dg = (lngB * rad) - (lngA * rad);
    dts = Math.sin(dt/2);
    dgs = Math.sin(dg/2);
    
    a = dts * dts + dgs * dgs * Math.cos(r1) * Math.cos(r2); 
    
    d = 6378100 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    b = Math.atan2((Math.sin(dg) * Math.cos(r2)), (Math.cos(r1) * Math.sin(r2) - Math.sin(r1) * Math.cos(r2) * Math.cos(dg))) * 180 / Math.PI;
    
    if (b > -22.5 && b <= 22.5) {
        h = localization['NORTH_VERBOSE'];
        s = localization['NORTH_BRIEF'];
    } else if (b > 22.5 && b <= 67.5) {
        h = localization['NORTHEAST_VERBOSE'];
        s = localization['NORTHEAST_BRIEF'];
    } else if (b > 67.5 && b <= 112.5) {
        h = localization['EAST_VERBOSE'];
        s = localization['EAST_BRIEF'];
    } else if (b > 112.5 && b <= 157.5) {
        h = localization['SOUTHEAST_VERBOSE'];
        s = localization['SOUTHEAST_BRIEF'];
    } else if (b > 157.5 || b <= -157.5) {
        h = localization['SOUTH_VERBOSE'];
        s = localization['SOUTH_BRIEF'];
    } else if (b > -157.5 && b <= -112.5) {
        h = localization['SOUTHWEST_VERBOSE'];
        s = localization['SOUTHWEST_BRIEF'];
    } else if (b > -112.5 && b <= -67.5) {
        h = localization['WEST_VERBOSE'];
        s = localization['WEST_BRIEF'];
    } else if (b > -67.5 && b <= -22.5) {
        h = localization['NORTHWEST_VERBOSE'];
        s = localization['NORTHWEST_BRIEF'];
    }
    
    return {distance: d, bearing: b, heading:h, shortHeading:s};
    
};

HPGeo.prototype.formatQueryStringParams = function () {

    'use strict';

    var q = '';

    if ( typeof this.latLng.lat === 'number'
         && typeof this.latLng.lng === 'number'
         && typeof this.lastUpdate === 'number' ) {

        q += '&ll='
          + this.latLng.lat
          + ','
          + this.latLng.lng
          + '&u='
          + this.lastUpdate;

    }

    return q;

};

HPGeo.prototype.updateHeading = function( heading ) {

    'use strict';

    var H = window._H,
        headings = H.geo.headings,
        i,
        headingCount,
        average,
        el = document.getElementById( 'compass-face' );

    heading = parseInt( heading, 10 );

    headingCount = headings.length;

    if ( headingCount === 0 ) {
        headings.push( heading );
        headingCount++;
    } else if ( headingCount < H.geo.maxHeadings ) {
        headings.push( heading );
        headingCount++;
    } else {
        headings.shift();
        headings.push( heading );
    }

//    average = 0;

//    for ( i = 0; i < headingCount; i++ ) {        
//        average += headings[i];
//    }

//    average = Math.abs( Math.round( average / headingCount ) );

    average = heading;

    if ( H.is.webkit ) {
        el.style.webkitTransform = 'translate3d( -' + average + 'px,0,0 )';
    } else if ( H.is.gecko ) {
        el.style.MozTransform = 'translate3d( -' + average + 'px,0,0 )';
    } else if ( el.style.hasOwnProperty( 'transform' ) ) {
        el.style.transform = 'translate3d( -' + average + 'px,0,0 )';
    }

};
