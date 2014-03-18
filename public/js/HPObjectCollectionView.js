HPObjectCollectionView = function( element, name, title, centerPoints ) {

    'use strict';

    this.element = element;
    this.name = name;
    this.title = title;
    this.columns = 0;
    this.columnHeights = [];
    this.shortestColumn = 0;

    this.times = [];

    this.init( centerPoints );

};

HPObjectCollectionView.prototype.reportTimes = function() {
    for ( var i = 0; i < this.times.length; i++ ) {
        var last = ( i > 0 ) ? this.times[i-1].time : 0;
        var delta = (i > 0 ) ? (this.times[i].time - last) : 0;
        console.log( this.times[i].item + '; ' + delta + 'ms');
    }
    var length = this.times.length - 1;
    console.log( 'total elapsed: ' + (this.times[length].time - this.times[0].time) + 'ms');
}

HPObjectCollectionView.prototype.init = function( centerPoints ) {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        q = new CASHQueue(),
        t = '',
        resourceResult,
        p,
        baseURL = window._H.baseURL,
        l = window._H.localization,
        h,//tml
        select,
        frag,
        point,
        node;

    //'<div id="{{name}}-title">{{currentCityLabel}}<span>{{currentCity}}</span> (<a href="#" title="{{changeCityLabel}}">{{changeCityLabel}}</a>)</div>'
    t = '<style id="{{name}}-styles" type="text/css"></style>'
      + '{{#title}}<div id="{{name}}-title">{{title}}</div>{{/title}}'
      + '<ol id="{{name}}-objects" class="collection-objects"></ol>'
      + '<div class="loading-notification"></div>'
      + '<div class="empty-notification"></div>'
      + '<div class="error-notification"></div>'
      + '<div class="collection-controls">'
      + '<div id="{{name}}-pagination" class="collection-pagination"></div>'
      + '<div id="{{name}}-center-point" class="collection-center-point">'
      + '<form id="{{name}}-center-point-form" method="GET" action="" autocomplete="off">'
      + '<select id="{{name}}-center-point-control" class="collection-center-point-control form-select-3-col" autocomplete="off">'
      + '<option class="form-select-option-disabled" value="placeholder" disabled{{^hasGeolocation}} selected{{/hasGeolocation}}>{{centerPointPlaceholder}}</option>'
      + '{{#hasGeolocation}}'
      + '<option value="self" selected>{{centerPointCurrentPositionLabel}}</option>'
      + '{{/hasGeolocation}}'
      + '</select>'
      + '<button id="{{name}}-center-point-button" class="collection-center-point-button ui-button ui-button-green" type="submit">{{centerPointButtonLabel}}</button>'
      + '<div class="clear"></div>'
      + '</form>'
      + '</div>'
      + '<div class="clear"></div>'
      + '<span id="ui-search-hint"></span>'
      + '</div>';

    p = {
        name: that.name,
        title: that.title,
        hasGeolocation: window._H.has.geolocation,
        centerPointPlaceholder: l['GLOBAL_SEARCH_FROM_PLACEHOLDER'],
        centerPointCurrentPositionLabel: l['GLOBAL_SEARCH_FROM_CURRENT_POSITION_LABEL'],
        centerPointButtonLabel: l['GLOBAL_SEARCH_FROM_SUBMIT_LABEL']
    };

    h = Mustache.render( t, p );

    $( this.element ).replaceContent( h );

    select = document.getElementById( this.name + '-center-point-control' );
    frag = document.createDocumentFragment();

    for ( point in centerPoints ) {
        node = document.createElement( 'option' );
        node.value = centerPoints[point].lat + ',' + centerPoints[point].lng;
        node.appendChild( document.createTextNode( point ) );
        frag.appendChild(node);
    }

    select.appendChild(frag);

    $( '#' + this.name + '-center-point-form' ).on( 'submit', function( e ) {

        var ll = $( '#' + that.name + '-center-point-control' ).val(),
            now = Math.floor( new Date().getTime() / 1000 );

        $.prototype.halt.call( null, e );

        if ( ll === 'placeholder' ) {
            return false;
        } else if ( ll === 'self' ) {
            $( '#' + that.name + '-center-point-control' ).addClass( 'disabled' ).attr( 'disabled', 'disabled' );
            $( '#' + that.name + '-center-point-button' ).addClass( 'disabled' ).attr( 'disabled', 'disabled' );
            window._H.geo.lastUpdate = 0;
            window._H.getGeoLocation(
                function( latLng ) {
                    ll = latLng.lat + ',' + latLng.lng;
                    window.location.href = $( '#' + that.name + '-center-point-form' ).attr( 'action' ) + '?ll=' + encodeURIComponent( ll ) + '&u=' + encodeURIComponent( now );
                    return false;
                },
                function( error ) {
                    $( '#' + that.name + '-center-point-control' ).removeClass( 'disabled' ).removeAttr( 'disabled' );
                    $( '#' + that.name + '-center-point-button' ).removeClass( 'disabled' ).removeAttr( 'disabled' );
                }
            );
        } else {
            window.location.href = $( '#' + that.name + '-center-point-form' ).attr( 'action' ) + '?ll=' + encodeURIComponent( ll ) + '&u=' + encodeURIComponent( now );
            return false;
        }

    } );

};

HPObjectCollectionView.prototype.updateStatus = function( status, message ) {

    'use strict';

    var $container = $( this.element );

    $container.removeClass( 'success' )
              .removeClass( 'loading' )
              .removeClass( 'empty' )
              .removeClass( 'error' );

    switch ( status ) {
        case 'success':
            $container.addClass( 'success' );
            break;
        case 'loading':
            if ( typeof message !== 'undefined' ) {
                $container.children( '.loading-notification' ).replaceContent( message );
            }
            $container.addClass( 'loading' );
            break;
        case 'empty':
            if ( typeof message !== 'undefined' ) {
                $container.children( '.empty-notification' ).replaceContent( message );
            }
            $container.addClass( 'empty' );
            break;
        case 'error':
            if ( typeof message !== 'undefined' ) {
                $container.children( '.error-notification' ).replaceContent( message );
            }
            $container.addClass( 'error' );
            break;
    }

};

HPObjectCollectionView.prototype.render = function( objects, currentPage,
                                                    totalPages, baseURL, geo,
                                                    successCallback ) {

/*
that.times = [];
that.times.push({item:'appending objects', time:Date.now()});
that.reportTimes();
*/

    'use strict';

    var that = this,
        objectCount = objects.length,
        objectWidth = window._H.getDefaultObjectWidth(),
        i,//ndex
        obj,//ect
        o = '',//bject markup
        s = '',//style markup
        el,//ement for IE8 alternative style injection
        p = '',//agination markup
        Q = new CASHQueue(),
        $container = $( that.element ),
        $objects = $( '#' + that.name + '-objects' ),
        next,
        emSize = window._H.getEmSize(),
        columns,
        columnHeights,
        tallest,
        startingPage,
        endingPage,
        range = 1,
        geoQueryString = geo.formatQueryStringParams(),
        l = window._H.localization;

    Q.addItem( function( nextItem ) {

        $container.removeClass( 'layout' );
        $container.addClass( 'loading' );

        that.columns = Math.floor(
            $( that.element ).width()
            / ( window._H.getDefaultObjectWidth() + window._H.getEmSize() )
        );

        that.resetColumnHeights();

        for ( i = 0; i < objectCount; i++ ) {
            obj = objects[i];
            o += HPObject.prototype.renderObject.call(
                null,
                obj,
                true,
                true,
                false,
                objectWidth,
                geoQueryString
            );
            if ( obj.hasOwnProperty( 'event' )
                 && obj.hasOwnProperty( 'startDateTime' )
                 && obj.hasOwnProperty( 'endDateTime' ) ) {
                //do something with countdown timers here
            }
        }

        $objects.replaceContent( o );

        nextItem();

    } );

    Q.addItem( function( nextItem ) {

        for ( i = 0; i < objectCount; i++ ) {
            s += that.computeObjectLayout( objects[i].id );
        }

        //calculate the tallest column after the last object has finished
        //layout so we know how tall to make the container
        columns = that.columnHeights.length;
        columnHeights= that.columnHeights;
        tallest = 0;
        for ( i = 0; i < columns; i++ ) {
            if ( columnHeights[i] > columnHeights[tallest] ) {
                tallest = i;
            }
        }

        s += '#' + that.name + '-objects{height:'
           + ( columnHeights[tallest] )
           + 'px;}';

        //IE8 seems to reject the <style> element in the document body, so
        //create a new element (or grab the existing one), set its properties
        //accordingly and append it to the document head as necessary
        //(instead of just replacing the content of the element from the
        //template as we do for other browsers)
        if ( window._H.is.lteIE8 ) {
            el = document.getElementById( that.name + '-styles' );
            if ( el === null ) {
                el = document.createElement('style');
                el.id = that.name + '-styles';
                el.type = 'text/css';
                el.styleSheet.cssText = s;
                document.getElementsByTagName( 'head' )[0].appendChild( el );
            } else {
                el.styleSheet.cssText = s;
            }
        } else {
            $( '#' + that.name + '-styles' ).replaceContent( s );
        }

        //starting pagination loop from 1 for obvious reasons
        startingPage = Math.max( 1, currentPage - range );
        endingPage = Math.min( totalPages, currentPage + range );

        p = '<span class="collection-pagination-label">'
          + l['SEARCH_RESULTS_PAGINATION_LABEL']
          + '</span>'
          + '<div class="collection-pagination-pages">';

        if ( currentPage === 1 ) {

            startingPage += 1;
            endingPage += 2;

            startingPage = Math.max( 2, startingPage );
            endingPage = Math.min( totalPages - 1, endingPage );

            p += '<span class="collection-page-item-current">'
               + 1
               + '</span>';

            if ( endingPage >= startingPage ) {

                for ( i = startingPage; i <= endingPage; i++ ) {
                    p += '<a class="collection-page-item'
                       + '" href="'
                    if ( i > 1 ) {
                        p += i + '/';
                    }
                    p += '?' + geoQueryString
                       + '" title="'
                       + l['SEARCH_RESULTS_PAGE_LABEL_PREFIX']
                       + i
                       + '">'
                       + i
                       + '</a>';
                }

                if ( totalPages > 5 && currentPage + range < totalPages - 1 ) {
                    p += '<span class="collection-page-item-spacer">...</span>';
                }

            }

            if ( totalPages > 1 ) {

                p += '<a class="collection-page-item" href="'
                   + totalPages + '/?' + geoQueryString
                   + '" title="'
                   + l['SEARCH_RESULTS_PAGE_LABEL_PREFIX']
                   + totalPages
                   + '">'
                   + totalPages
                   + '</a>';

            }

        } else if ( currentPage === totalPages ) {

            startingPage -= 2;
            endingPage -= 1;

            startingPage = Math.max( 2, startingPage );
            endingPage = Math.min( totalPages - 1, endingPage );

            p += '<a class="collection-page-item" href="'
               + '../?' + geoQueryString
               + '" title="'
               + l['SEARCH_RESULTS_PAGE_LABEL_PREFIX']
               + '1">'
               + 1
               + '</a>';

            if ( totalPages > 5 && currentPage - range > 2 ) {
                p += '<span class="collection-page-item-spacer">...</span>';
            }

            for ( i = startingPage; i <= endingPage; i++ ) {
                p += '<a class="collection-page-item'
                   + '" href="../'
                if ( i > 1 ) {
                    p += i + '/';
                }
                p += '?' + geoQueryString
                   + '" title="'
                   + l['SEARCH_RESULTS_PAGE_LABEL_PREFIX']
                   + i
                   + '">'
                   + i
                   + '</a>';
            }

            p += '<span class="collection-page-item-current">'
               + totalPages
               + '</span>';

        } else {

            if ( currentPage - range < 2 ) {
                endingPage += 1;
            } else if ( currentPage + range > totalPages - 1 ) {
                startingPage -= 1;
            }

            startingPage = Math.max( 2, startingPage );
            endingPage = Math.min( totalPages - 1, endingPage );

            p += '<a class="collection-page-item" href="'
               + '../?' + geoQueryString
               + '" title="'
               + l['SEARCH_RESULTS_PAGE_LABEL_PREFIX']
               + '1">'
               + 1
               + '</a>';

            if ( totalPages > 5 && currentPage - range > 2 ) {
                p += '<span class="collection-page-item-spacer">...</span>';
            }

            for ( i = startingPage; i <= endingPage; i++ ) {
                if ( i === currentPage ) {
                  p += '<span class="collection-page-item-current">'
                     + i
                     + '</span>';

                } else {
                    p += '<a class="collection-page-item'
                       + '" href="'
                    if ( i > 1 ) {
                        p += '../' + i + '/';
                    } else {
                        p += '../';
                    }
                    p += '?' + geoQueryString
                       + '" title="'
                       + l['SEARCH_RESULTS_PAGE_LABEL_PREFIX']
                       + i
                       + '">'
                       + i
                       + '</a>';
                }
            }

            if ( totalPages > 5 && currentPage + range < totalPages - 1 ) {
                p += '<span class="collection-page-item-spacer">...</span>';
            }

            p += '<a class="collection-page-item" href="'
               + '../' + totalPages + '/?' + geoQueryString
               + '" title="'
               + l['SEARCH_RESULTS_PAGE_LABEL_PREFIX']
               + totalPages
               + '">'
               + totalPages
               + '</a>';

        }

        p += '</div>'
           + '<div class="clear"></div>';

        $( '#' + that.name + '-pagination' ).replaceContent( p );

        $container.removeClass( 'loading' );
        $container.addClass( 'layout' );

        successCallback();

    } );

    Q.execute();

};

HPObjectCollectionView.prototype.resetColumnHeights = function() {

    'use strict';

    var i,
        columns = this.columns,
        columnHeights;

    columnHeights = [];

    for ( i = 0; i < columns; i++ ) {
        columnHeights.push( 0 );
    }

    this.columnHeights = columnHeights;
    this.shortestColumn = 0;

};

HPObjectCollectionView.prototype.computeObjectLayout = function( objectId ) {

    'use strict';

    var i,
        columns = this.columns,
        columnHeights = this.columnHeights,
        shortest = this.shortestColumn,
        $obj = $( '#obj-' + objectId ),
        s,
        objectWidth = window._H.getDefaultObjectWidth(),
        emSize = window._H.getEmSize(),
        halfEm = Math.floor( emSize / 2 );

    for ( i = 0; i < columns; i++ ) {
        if ( columnHeights[i] < columnHeights[shortest] ) {
            shortest = i;
        }
    }

    s = '#obj-' + objectId + '{left:'
      + ( ( halfEm + shortest * ( objectWidth + emSize ) ) - 1 )
      + 'px;top:'
      + ( halfEm + columnHeights[shortest] )
      + 'px;}';

    columnHeights[shortest] += parseInt( $obj.height(), 10 ) + emSize;

    //reassign the shortest column since it looks like scalar values aren't
    //passed by reference like objects are
    this.shortestColumn = shortest;

    return s;

};
