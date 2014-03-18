HPCore = function(baseURL, localization) {

    //quietly expose for internal references to the core object and act like a pseudo-singleton
    if ( !window._H ) {

        window._H = this;

        //only ever need one instantiation of the geo component, so may as well make it accessible via the core
        this.geo = new HPGeo();

        var xhr = new XMLHttpRequest();
        var uaString = navigator.userAgent.toLowerCase();

        this.has = {
            deviceOrientation : typeof window.ondeviceorientation === 'undefined' ? false : true,
            fileReader : typeof window.FileReader === 'undefined' ? false : true,
            history : !!(window.history && history.pushState),
            geolocation: navigator.geolocation,
            orientation : typeof window.orientation === 'undefined' ? false : true,
            touch : this.eventSupported( 'touchstart' ),
            xhr2FileUpload : window.XMLHttpRequest && Object.prototype.hasOwnProperty.call( xhr, 'upload' )
        };

        this.is = {
            //test for vendor so we know which vendor-specific prefix to use
            webkit : uaString.match( /webkit/ ) !== null,
            chrome : uaString.match( /chrome/ ) !== null,
            gecko : uaString.match( /gecko/ ) !== null && uaString.match( /like gecko/ ) === null,
            opera : uaString.match( /opera/ ) !== null,
            msie : uaString.match( /msie/ ) !== null,
            lteIE8 : ( uaString.match( /msie/ ) !== null && uaString.indexOf( 'msie 8.0' ) !== -1 ) ? true : false,
            //also test for OS for maps integration
            ios : uaString.match( /iphone/ ) !== null || uaString.match( /ipad/ ) !== null || uaString.match( /ipod/ ) !== null,
            android : uaString.match( /android/ ) !== null,
            //finally, decide whether or not to use the 'compact' layout
            compact : parseInt( $('#content').width(), 10 ) <= 320 && this.has.touch ? true : false
        };

        xhr = null;

        this.baseURL = baseURL;
        this.localization = localization;

        this.emSize = 10;
        this.previewImageWidth = 150;
        this.objectWidth = 150;

        this.resizeTimeout = null;

        window.onresize = function( e ) {

            clearTimeout( window._H.resizeTimeout );
            window._H.resizeTimeout = setTimeout(
                function() {
                    if ( typeof SEARCH !== 'undefined' ) {
                        SEARCH.searchRenderFn();
                    }
                    if ( typeof OBJECT !== 'undefined' ) {
                        var Q = new CASHQueue();
                        Q.addItem( function( nextItem ) {
                            OBJECT.objectRenderFn( nextItem );
                        } );
                        if ( typeof OBJECT.computeReplyResource === 'function' ) {
                            Q.addItem( function( nextItem ) {
                                OBJECT.repliesRenderFn( nextItem );
                            } );
                        }
                        Q.execute();
                    }
                },
                500
            );

        };

        if ( this.is.lteIE8 ) {

            $( document.getElementsByTagName( 'body' )[0] ).addClass( 'ie8' );

            this.lastInnerWidth = document.documentElement.clientWidth;
            this.lastInnerHeight = document.documentElement.clientHeight;

            //overwrite the onresize event with a check against the last size
            //for IE8 because it appears to compulsively report window resize
            //events even when the size hasn't changed
            window.onresize = function( e ) {

                if ( document.documentElement.clientWidth !== window._H.lastInnerWidth
                     || document.documentElement.clientHeight !== window._H.lastInnerHeight ) {

//                    clearTimeout( window._H.resizeTimeout );
//                    window._H.resizeTimeout = setTimeout(
//                        function() {
                            if ( typeof SEARCH !== 'undefined' ) {
                                SEARCH.searchRenderFn();
                            }
                            if ( typeof OBJECT !== 'undefined' ) {
                                var Q = new CASHQueue();
                                Q.addItem( function( nextItem ) {
                                    OBJECT.objectRenderFn( nextItem );
                                } );
                                Q.addItem( function( nextItem ) {
                                    OBJECT.repliesRenderFn( nextItem );
                                } );
                                Q.execute();
                            }
//                        },
//                        500
//                    );

                }

            }

        }

        if ( this.has.deviceOrientation
             && ( this.is.ios || this.is.android ) ) {

            $( '#compass-container' ).show();

            window.addEventListener( 'deviceorientation', function( e ) {

                var heading = 0;

                if ( e.webkitCompassHeading ) {
                    heading = Math.round( e.webkitCompassHeading );
                } else if ( window.chrome ) {
                    heading = Math.round( e.alpha ) - 270;
                } else {
                    heading = Math.round( e.alpha );
                }

                window._H.geo.updateHeading( heading );

            } );

        }

        return this;

    }

    return window._H;

};

/* http://perfectionkills.com/detecting-event-support-without-browser-sniffing/ */
HPCore.prototype.eventSupported = function( event ) {

    'use strict';

    var tags = { 'select':'input', 'change':'input', 'submit':'form','reset':'form', 'error':'img','load':'img','abort':'img' },
        element = document.createElement( tags[event] || 'div' ),
        ok;

    event = 'on' + event;

    //(event in element) was the original comparison below but it makes jslint complain and the typeof should do the same job
    ok = ( typeof element[event] !== 'undefined' );

    if ( !ok ) {
        element.setAttribute( event, 'return;' );
        ok = typeof element[event] === 'function';
    }

    element = null;

    return ok;

};

//convenience method to retrieve lat/lng that automatically refreshes stale geolocation data
HPCore.prototype.getGeoLocation = function( successCallback, errorCallback ) {

    'use strict';

    var geo = this.geo,
        now;

    if ( this.is.lteIE8 ) {
        now = Math.floor( new Date().getTime() / 1000 );
    } else {
        now = Math.floor( Date.now() / 1000 );
    }

    //if our geo information is more than 5 minutes old, re-query for an update lat/lng pair
    if ( !geo.latLng || ( now - geo.lastUpdate ) > 300 ) {
        geo.queryPosition(
            function ( latLng ) { successCallback( latLng ); },
            function ( error ) { errorCallback( error ); }
        );
    //otherwise, just return the existing data
    } else {
        successCallback( geo.latLng );
    }

};

HPCore.prototype.setGeoLocation = function( lat, lng, updated ) {

    'use strict';

    this.geo.latLng = { lat: lat, lng: lng };
    this.geo.lastUpdate = updated;

}

HPCore.prototype.getEmSize = function() {

    'use strict';

    var b = document.getElementsByTagName( 'body' )[0],
        em = 10;

    if ( b.currentStyle ) {
        em = b.currentStyle['fontSize'];
    } else if ( window.getComputedStyle ) {
        em = document.defaultView.getComputedStyle( b, null ).getPropertyValue(
            'font-size'
        );
    }

    return parseInt( em, 10 );

}

HPCore.prototype.getDefaultObjectWidth = function() {

    'use strict';

    return Math.floor( 15 * this.getEmSize() );

};
