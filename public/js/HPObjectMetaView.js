HPObjectMetaView = function( element, name ) {

    'use strict';

    this.element = element;
    this.name = name;
    this.countdownTimers = [];
    this.times = [];

    this.replyToView = null;

    this.init();

};

HPObjectMetaView.prototype.reportTimes = function() {
    for ( var i = 0; i < this.times.length; i++ ) {
        var last = ( i > 0 ) ? this.times[i-1].time : 0;
        var delta = (i > 0 ) ? (this.times[i].time - last) : 0;
        console.log( this.times[i].item + '; ' + delta + 'ms');
    }
    var length = this.times.length - 1;
    console.log( 'total elapsed: ' + (this.times[length].time - this.times[0].time) + 'ms');
}

HPObjectMetaView.prototype.init = function() {

    'use strict';

    var that = this,
        t,//emplate
        p,//roperties
        l = window._H.localization,
        h,//tml
        $container = $( this.element );

    t = '<div id="{{name}}-content"></div>'
      + '<div class="loading-notification"></div>'
      + '<div class="empty-notification"></div>'
      + '<div class="error-notification"></div>';

    p = {
        name: that.name
    };

    h = Mustache.render( t, p );

    $container.append(
        h,
        function() { }
    );

};

HPObjectMetaView.prototype.updateStatus = function( status, message ) {

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
            $container.addClass( 'loading' );
            break;
        case 'empty':
            $container.addClass( 'empty' );
            break;
        case 'error':
            $container.addClass( 'error' );
            break;
    }

};

HPObjectMetaView.prototype.render = function( object, geo, successCallback ) {

/*
that.times = [];
that.times.push({item:'appending objects', time:Date.now()});
that.reportTimes();
*/

    'use strict';

    var that = this,
        t = '',//emplate
        p = '',//roperties
        e,//stablishment
        v,//event
        m,//essage
        h = '',//tml
        Q = new CASHQueue(),
        $container = $( '#' + that.name + '-content' ),
        $object = $( '#' + that.name + '-object' ),
        objectWidth = parseInt( $container.width(), 10 ),
        next,
        emSize = window._H.getEmSize(),
        baseUrl = window._H.baseUrl,
        geoQueryString = geo.formatQueryStringParams(),
        l = window._H.localization;

    Q.addItem( function( nextItem ) {

        t = ''
          + '<div class="{{name}}-map">'
          + '<div id="{{name}}-map-container" class="{{name}}-map-container"></div>'
          + '</div>'
          + '{{#hasEstablishment}}'
          + '<div class="{{name}}-establishment">'
          + '{{#hasLogo}}'
          + '<img class="{{name}}-establishment-logo" src="{{establishmentLogoUrl}}" width="75" height="{{establishmentLogoHeight}}" />'
          + '{{/hasLogo}}'
          + '<h2 class="{{name}}-establishment-name{{#hasLogo}} has-logo{{/hasLogo}}">{{establishmentName}}</h2>'
          + '{{#hasLogo}}'
          + '<div class="clear"></div>'
          + '{{/hasLogo}}'
          + '<p class="{{name}}-establishment-description">{{{establishmentDescription}}}</p>'
          + '{{#establishmentAddress}}'
          + '<span class="{{name}}-establishment-contact-label">{{establishmentAddressLabel}}</span>'
          + '<span class="{{name}}-establishment-contact-item">{{{establishmentAddress}}}</span>'
          + '{{/establishmentAddress}}'
          + '{{#establishmentTel}}'
          + '<span class="{{name}}-establishment-contact-label">{{establishmentTelLabel}}</span>'
          + '<a class="{{name}}-establishment-contact-item" href="tel://{{establishmentTel}}" title="{{establishmentTel}}">{{establishmentTel}}</a>'
          + '{{/establishmentTel}}'
          + '{{#establishmentEmail}}'
          + '<span class="{{name}}-establishment-contact-label">{{establishmentEmailLabel}}</span>'
          + '<span class="{{name}}-establishment-contact-item">{{{establishmentEmail}}}</span>'
          + '{{/establishmentEmail}}'
          + '{{#establishmentUrl}}'
          + '<span class="{{name}}-establishment-contact-label">{{establishmentUrlLabel}}</span>'
          + '<span class="{{name}}-establishment-contact-item">{{{establishmentUrl}}}</span>'
          + '{{/establishmentUrl}}'
          + '<div class="clear"></div>'
          + '</div>'
          + '{{/hasEstablishment}}'
          + '<div class="{{name}}-search-from">'
          + '<a class="{{name}}-search-from-button ui-button" href="{{baseURL}}{{cityCode}}/?from={{objectId}}&ll={{lat}},{{lng}}&u={{now}}" title="{{searchFromHereLabel}}">{{searchFromHereLabel}}</a>'
          + '</div>'
          + '{{#replyTo}}'
          + '<div id="{{name}}-reply-to" class="{{name}}-reply-to">'
          + '<h2 class="{{name}}-reply-to-title">{{replyToTitle}}</h2>'
          + '<div id="{{name}}-reply-to-content"></div>'
          + '</div>'
          + '{{/replyTo}}'
          + '<div class="{{name}}-share">'
          + '<h2 class="object-meta-share-title">{{shareTitle}}</h2>'
          + '<a class="ui-button share-facebook" href="{{shareFacebookUrl}}" title="{{shareFacebookButtonLabel}}">{{shareFacebookButtonLabel}}</a>'
          + '<a class="ui-button share-twitter" href="{{shareTwitterUrl}}" title="{{shareTwitterButtonLabel}}">{{shareTwitterButtonLabel}}</a>'
          + '<a class="ui-button share-google" href="{{shareGoogleUrl}}" title="{{shareGoogleButtonLabel}}">{{shareGoogleButtonLabel}}</a>'
          + '<a class="ui-button share-tumblr" href="{{shareTumblrUrl}}" title="{{shareTumblrButtonLabel}}">{{shareTumblrButtonLabel}}</a>'
          + '<a class="ui-button share-pinterest" href="{{sharePinterestUrl}}" title="{{sharePinterestButtonLabel}}">{{sharePinterestButtonLabel}}</a>'
          + '<a class="ui-button share-weibo" href="{{shareWeiboUrl}}" title="{{shareWeiboButtonLabel}}">{{shareWeiboButtonLabel}}</a>'
          + '<div class="clear"></div>'
          + '</div>'
          + '<div class="clear"></div>';

        p = {
            baseURL: window._H.baseURL,
            name: that.name,
            establishmentLogoUrl: false,
            establishmentLogoHeight: false,
            establishmentAddressLabel: l['ESTABLISHMENT_ADDRESS_LABEL'],
            establishmentTelLabel: l['ESTABLISHMENT_TEL_LABEL'],
            establishmentEmailLabel: l['ESTABLISHMENT_EMAIL_LABEL'],
            establishmentUrlLabel: l['ESTABLISHMENT_URL_LABEL'],
            establishmentObjectUrlTitle: l['OBJECT_META_VIEW_ESTABLISHMENT_OBJECT_TITLE'],
            establishmentObjectUrlLabel: l['OBJECT_META_VIEW_ESTABLISHMENT_OBJECT_LABEL'],
            establishmentStreetViewUrl: window._H.baseUrl + 'img/loading-transparent-555555.gif',
            searchFromHereLabel: l['OBJECT_SEARCH_FROM_HERE_LABEL'],
            replyToTitle: l['OBJECT_META_REPLY_TO_TITLE'],
            shareTitle: l['OBJECT_META_SHARE_TITLE'],
            shareFacebookButtonLabel: l['OBJECT_META_SHARE_FACEBOOK_LABEL'],
            shareTwitterButtonLabel: l['OBJECT_META_SHARE_TWITTER_LABEL'],
            shareGoogleButtonLabel: l['OBJECT_META_SHARE_GOOGLE_LABEL'],
            shareTumblrButtonLabel: l['OBJECT_META_SHARE_TUMBLR_LABEL'],
            sharePinterestButtonLabel: l['OBJECT_META_SHARE_PINTEREST_LABEL'],
            shareWeiboButtonLabel: l['OBJECT_META_SHARE_WEIBO_LABEL'],
            shareUrl: object.url,
            shareCaption: l['OBJECT_META_SHARE_DEFAULT_CAPTION'],
            shareImageUrl: false
        };

        if ( object.hasOwnProperty( 'message' )
             && object.message.hasOwnProperty( 'replyTo' )
             && object.message.replyTo !== null ) {
            p.replyTo = true;
        }

        if ( object.hasOwnProperty( 'establishment' )
             && object.establishment !== null ) {

            e = object.establishment;
            p.hasEstablishment = true;
            if ( e.hasOwnProperty( 'logo' )
                 && e.logo !== null ) {
                p.hasLogo = true;
                p.establishmentLogoUrl = e.logo.thumbUrl;
                p.establishmentLogoHeight = 75 / e.logo.thumbAspectRatio;
                p.shareImageUrl = e.logo.fullUrl;
            }
            p.establishmentName = e.name;
            p.establishmentDescription = that.formatMetaText( e.description );
            if ( e.address !== null
                 || e.tel !== null
                 || e.email !== null
                 || e.url !== null ) {
                p.hasEstablishmentContactInfo = true;
            }
            p.establishmentAddress = that.formatMetaText( e.address );
            p.establishmentTel = e.tel;
            p.establishmentEmail = that.formatMetaEmail( e.email );
            p.establishmentUrl = that.formatMetaUrl( e.url );

            p.shareCaption = that.formatShareCaption( e.name );
            

        }

        if ( object.hasOwnProperty( 'event' )
             && object.event !== null ) {

            v = object.event;
            p.isEvent = true;
            p.establishmentObjectUrl = window._H.baseUrl + 'obj/' + v.establishmentObjectId + '/?' + window._H.geo.formatQueryStringParams();

        }

        if ( object.hasOwnProperty( 'message' )
             && object.message !== null ) {

            m = object.message;

            if ( m.text !== null ) {
                p.shareCaption = that.formatShareCaption( m.text );
            }

            if ( m.image !== null ) {
                p.shareImageUrl = m.image.fullUrl;
            }

        }

        p.objectId = object.id;
        p.cityCode = object.position.cityCode;
        p.lat = object.position.lat;
        p.lng = object.position.lng;
        p.now = Math.floor( Date.now() / 1000 );

        p.shareFacebookUrl = that.formatFacebookShareUrl( p.shareUrl, p.shareCaption, p.shareImageUrl);
        p.shareTwitterUrl = that.formatTwitterShareUrl( p.shareUrl, p.shareCaption, p.shareImageUrl);
        p.shareGoogleUrl = that.formatGoogleShareUrl( p.shareUrl, p.shareCaption, p.shareImageUrl);
        p.shareTumblrUrl = that.formatTumblrShareUrl( p.shareUrl, p.shareCaption, p.shareImageUrl);
        p.sharePinterestUrl = that.formatPinterestShareUrl( p.shareUrl, p.shareCaption, p.shareImageUrl);
        p.shareWeiboUrl = that.formatWeiboShareUrl( p.shareUrl, p.shareCaption, p.shareImageUrl);

        h = Mustache.render( t, p );

        $container.replaceContent( h );

        nextItem();

    } );

    Q.addItem( function( nextItem ) {

        that.renderMap( object, nextItem );

    } );

    Q.addItem( function( nextItem ) {

        if ( object.hasOwnProperty( 'message' )
             && object.message.hasOwnProperty( 'replyTo' )
             && object.message.replyTo !== null ) {

            that.replyToView = new HPObjectView(
                document.getElementById( 'object-meta-reply-to' ),
                'object-meta-reply-to'
            );

            that.replyToRenderFn = function( next ) {
                if ( typeof next !== 'function' ) {
                    next = function() {};
                }
                that.replyToView.render(
                    object.message.replyTo,
                    window._H.geo,
                    true,
                    false,
                    next
                );
            };
            that.replyToRenderFn( nextItem );

        } else {
            nextItem();
        }

    } );

    Q.addItem( function( nextItem ) {

        successCallback();

    } );

    Q.execute();

};

HPObjectMetaView.prototype.renderMap = function( object, successCallback ) {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        t,//emplate
        m,//ap properties
        l = window._H.localization,
        h,//tml
        $mapContainer = $( '#' + that.name + '-map-container' );

    Q.addItem( function( nextItem ) {

        t = '<img class="{{name}}-map-overhead" src="{{mapImageUrl}}" />'
          /*+ '<a class="ui-button {{name}}-map-interactive-button" href="#" title="{{interactiveMapButtonLabel}}">{{interactiveMapButtonLabel}}</a>'
          + '<button class="ui-button {{name}}-map-overhead-button">{{overheadMapButtonLabel}}</button>'
          + '<button class="ui-button {{name}}-map-street-view-button">{{streetViewButtonLabel}}</button>'*/
          + '<div class="clear"></div>';

        m = {
            name: that.name,
            interactiveMapButtonLabel: l['OBJECT_META_INTERACTIVE_MAP_BUTTON_LABEL'],
            /*overheadMapButtonLabel: l['OBJECT_META_MAP_BUTTON_LABEL'],
            streetViewButtonLabel: l['OBJECT_META_STREET_VIEW_BUTTON_LABEL'],*/
            mapImageWidth: parseInt( $( '#' + that.name + '-map-container' ).width(), 10 ),
            mapImageHeight: ( parseInt( $( '#' + that.name + '-map-container' ).width(), 10 ) > 318 ) ? 200 : 180
        };

        var next = function( url ) {
            m.mapImageUrl = url;
            nextItem();
        };

        that.formatMapImageUrl( object, m.mapImageWidth, m.mapImageHeight, next );

    } );

    Q.addItem( function( nextItem ) {

        h = Mustache.render( t, m );

        $mapContainer.replaceContent( h );

        successCallback();

    } );

    Q.execute();

};

HPObjectMetaView.prototype.formatMetaText = function( text ) {

    'use strict';

    var metaText = '';

    if ( typeof text === 'string' && text.length > 0 ) {
        metaText = text.split( '\n' );
        metaText = metaText.join( '<br />' );
    }
    
    return metaText;

};

HPObjectMetaView.prototype.formatMetaEmail = function( email ) {

    'use strict';

    var metaEmail = '';

    if ( typeof email === 'string' && email.length > 0 ) {
        metaEmail = '<a href="mailto:' + email + '" title="' + email + '">' + email + '</a>';
    }

    return metaEmail;

};

HPObjectMetaView.prototype.formatMetaUrl = function( url ) {

    'use strict';

    var metaUrl = '';

    if ( typeof url === 'string' && url.length > 0 ) {
        metaUrl = '<a href="' + url + '" title="' + url + '">' + url + '</a>';
    }

    return metaUrl;

};

HPObjectMetaView.prototype.formatMapImageUrl = function( object, width, height, callback ) {

    'use strict';

    var Q = new CASHQueue(),
        url = 'http://open.mapquestapi.com/staticmap/v4/getmap?'
            + '&type=map&imagetype=jpeg'
            + '&size=' + width + ',' + height;

    Q.addItem( function( nextItem ) {

        window._H.getGeoLocation(
            function( latLng ) {
                url += '&pois=mcenter,'
                     + latLng.lat
                     + ','
                     + latLng.lng
                     + ',|pcenter,'
                     + object.position.lat
                     + ','
                     + object.position.lng;
                nextItem();
            },
            function( error ) {
                url += '&pois=pcenter,'
                     + object.position.lat
                     + ','
                     + object.position.lng;
                nextItem();
            }
        );

    } );

    Q.addItem( function( nextItem ) {

        callback( url );

    } );

    Q.execute();

};

HPObjectMetaView.prototype.formatShareCaption = function( caption ) {

    'use strict';

    var shareCaption = window._H.localization['OBJECT_META_SHARE_DEFAULT_CAPTION'],
        i;

    if ( typeof caption === 'string' && caption.length > 0 ) {
        
        if ( caption.length > 128 ) {

            shareCaption = caption.substring( 0, 114 );

            for ( i = 115; i < 125; i++ ) {

                if ( caption.charAt( i ) === '&' ) {
                    break;
                }

                shareCaption += caption.charAt( i );

            }

            shareCaption += '... ';

        } else {

            shareCaption = caption;

        }

    }

    return shareCaption;

};

HPObjectMetaView.prototype.formatFacebookShareUrl = function( url, caption, imageUrl ) {

    'use strict';

    var shareUrl = 'http://m.facebook.com/sharer.php?u='
                 + encodeURIComponent( url )
                 + '&t='
                 + encodeURIComponent( caption );

    return shareUrl;

};

HPObjectMetaView.prototype.formatTwitterShareUrl = function( url, caption, imageUrl ) {

    'use strict';

    var shareUrl = 'http://twitter.com/share?text='
                 + encodeURIComponent( caption )
                 + '&url='
                 + encodeURIComponent( url );

    return shareUrl;
                
};

HPObjectMetaView.prototype.formatGoogleShareUrl = function( url, caption, imageUrl ) {

    'use strict';

    var shareUrl = 'https://plus.google.com/share?url='
                 + encodeURIComponent( url );

    return shareUrl;

};

HPObjectMetaView.prototype.formatTumblrShareUrl = function( url, caption, imageUrl ) {

    'use strict';

    var shareUrl = false;

    if ( typeof imageUrl !== 'undefined' && imageUrl !== false ) {

        shareUrl = 'http://www.tumblr.com/share/photo?source='
                 + encodeURIComponent( imageUrl )
                 + '&caption='
                 + encodeURIComponent( caption )
                 + '&click_thru='
                 + encodeURIComponent( url );

    } else {

        shareUrl = 'http://www.tumblr.com/share/link?url='
                 + encodeURIComponent( url )
                 + '&name='
                 + encodeURIComponent( caption )
                 + '&description='
                 + encodeURIComponent( caption );
    }

    return shareUrl;

};

HPObjectMetaView.prototype.formatPinterestShareUrl = function( url, caption, imageUrl ) {

    'use strict';

    var shareUrl = false;

    if ( typeof imageUrl !== 'undefined' && imageUrl !== false ) {

        shareUrl = 'http://pinterest.com/pin/create/button/?url='
                 + encodeURIComponent( url )
                 + '&media='
                 + encodeURIComponent( imageUrl )
                 + '&description='
                 + encodeURIComponent( caption );

    } else {

        shareUrl = 'http://pinterest.com/pin/create/button/?url='
                 + encodeURIComponent( url )
                 + '&description='
                 + encodeURIComponent( caption );    

    }

    return shareUrl;

};

HPObjectMetaView.prototype.formatWeiboShareUrl = function( url, caption, imageUrl ) {

    'use strict';

    var shareUrl = false;

    if ( typeof imageUrl !== 'undefined' && imageUrl !== false ) {

        shareUrl = 'http://service.weibo.com/share/share.php?url='
                 + encodeURIComponent( url )
                 + '&title='
                 + encodeURIComponent( caption )
                 + '&media='
                 + encodeURIComponent( imageUrl );

    } else {

        shareUrl = 'http://service.weibo.com/share/share.php?url='
                 + encodeURIComponent( url )
                 + '&title='
                 + encodeURIComponent( caption );
    }

    return shareUrl;

};
