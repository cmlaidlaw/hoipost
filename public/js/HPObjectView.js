HPObjectView = function ( element, name ) {

    'use strict';

    this.element = element;
    this.name = name;
    this.countdownTimers = [];
    this.times = [];
    this.init();

};

HPObjectView.prototype.reportTimes = function() {
    for ( var i = 0; i < this.times.length; i++ ) {
        var last = ( i > 0 ) ? this.times[i-1].time : 0;
        var delta = (i > 0 ) ? (this.times[i].time - last) : 0;
        console.log( this.times[i].item + '; ' + delta + 'ms');
    }
    var length = this.times.length - 1;
    console.log( 'total elapsed: ' + (this.times[length].time - this.times[0].time) + 'ms');
}

HPObjectView.prototype.init = function() {

    'use strict';

};

HPObjectView.prototype.updateStatus = function( status, message ) {

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

HPObjectView.prototype.render = function( object, geo, anchor, fullSize, successCallback ) {

/*
that.times = [];
that.times.push({item:'appending objects', time:Date.now()});
that.reportTimes();
*/

    'use strict';

    var that = this,
        o = '',//bject markup
        s = '',//style markup
        p = '',//agination markup
        Q = new CASHQueue(),
        $container = $( '#' + that.name + '-content' ),
        $object = $( '#' + that.name + '-object' ),
        objectWidth = parseInt( $container.width(), 10 ),
        next,
        emSize = window._H.getEmSize(),
        baseURL = window._H.baseURL,
        geoQueryString = geo.formatQueryStringParams(),
        l = window._H.localization;

    Q.addItem( function( nextItem ) {

        $container.removeClass( 'layout' );

        that.resetCountdownTimers();

        o += HPObject.prototype.renderObject.call(
            null,
            object,
            false,
            anchor,
            fullSize,
            objectWidth,
            geoQueryString
        );

        if ( object.hasOwnProperty( 'event' )
             && object.hasOwnProperty( 'startDateTime' )
             && object.hasOwnProperty( 'endDateTime' ) ) {
            that.countdownTimers.push(
                {
                    id: object.id,
                    startDateTime: object.startDateTime,
                    endDateTime: object.endDateTime,
                    timeout: null
                }

            );

            that.startCountdownTimers();

        }

        $container.replaceContent( o );

        nextItem();

    } );

    Q.addItem( function ( nextItem ) {

        $container.addClass( 'layout' );

        successCallback();

    } );

    Q.execute();

};

HPObjectView.prototype.resetCountdownTimers = function() {
    'use strict';
};

HPObjectView.prototype.startCountdownTimers = function() {

    'use strict';

    var that = this,
        timers = this.countdownTimers,
        i,
        timerCount = timers.length;

    for ( i = 0; i < timerCount; i++ ) {
        var fn = function () {
            that.updateCountdownTimer( i );
        };
        setTimeout( fn, 1000 );
    }

};

HPObjectView.prototype.updateCountdownTimer = function( i ) {
    'use strict';
    console.log( this.countdownTimers[i] );
};