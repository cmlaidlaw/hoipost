HPObject = function( obj ) {

    'use strict';

    var objectId = null,
        type = null,
        message = null,
        establishment = null,
        event = null;

        //required
        this.objectId = obj.id;
        this.type = obj.type;
        this.position = obj.position;

        obj.hasOwnProperty( 'message' ) ? this.message = obj.message : false;
        obj.hasOwnProperty( 'establishment' ) ? this.establishment = obj.establishment : false;
        obj.hasOwnProperty( 'event' ) ? this.event = obj.event : false;

};


/*******************************************************************************
* Rendering                                                                    *
*******************************************************************************/

HPObject.prototype.extractRenderProperties = function( obj, fullSize ) {

    'use strict';

    var p = { isMessage : false, isEstablishment : false, isEvent : false },
        m = null, //message component
        e = null, //establishment component
        v = null, //event component
        countdown,
        countdownShort;

    p.id = obj.id;
    p.objectUrl = obj.url;
    p.theme = obj.theme;
    p.location = obj.location;
    p.image = false;

    if ( fullSize ) {
        p.lastActivity = obj.lastActivity;
        p.replyCount = obj.replyCount;
    } else {
        p.lastActivity = obj.lastActivityShort;
        p.replyCount = obj.replyCountShort;
        p.iconSmall = true;
    }

    if ( obj.hasOwnProperty( 'position' )
         && obj.position !== null ) {

        if ( fullSize ) {
            p.geoRelation = obj.position.geoRelation;
        } else {
            p.geoRelation = obj.position.geoRelationShort;
        }

    }

    if ( obj.hasOwnProperty( 'message' )
         && obj.message !== null ) {

        m = obj.message;

        if ( m.hasOwnProperty( 'text' )
             && m.text !== null ) {
            p.text = HPObject.prototype.formatText.call( null, m.text, fullSize );
        }

        if ( m.hasOwnProperty( 'image' )
             && m.image !== null ) {
            p.image = true;
            p.imageThumbUrl = m.image.thumbUrl;
            p.imageThumbAspectRatio = m.image.thumbAspectRatio;
            p.imageFullUrl = m.image.fullUrl;
            p.imageFullAspectRatio = m.image.fullAspectRatio;
        }

    }

    if ( obj.hasOwnProperty( 'establishment' )
         && obj.establishment !== null ) {

        e = obj.establishment;

        if ( e.hasOwnProperty( 'address' ) ) {
            p.address = e.address;
        }

        if ( !obj.hasOwnProperty( 'event' ) ) {
            p.label = e.name;
        }

        if ( e.hasOwnProperty( 'logo' )
             && e.logo !== null
             && p.image === false ) {
            p.image = true;
            p.imageThumbUrl = e.logo.thumbUrl;
            p.imageThumbAspectRatio = e.logo.thumbAspectRatio;
            p.imageFullUrl = e.logo.fullUrl;
            p.imageFullAspectRatio = e.logo.fullAspectRatio;
            p.icon = true;
            p.iconSmall = true;
        } else if ( p.image === false ) {
            p.icon = true;
        }

        if ( p.iconSmall === true || p.icon === true ) {
            switch ( e.category ) {
                case 'unassigned':
                case 'retail':
                case 'transport':
                case 'convenience':
                case 'parks&rec':
                case 'landmarks&culture':
                default:
                    p.iconCategory = 'default';
                    break;
                case 'f&b':
                    p.iconCategory = 'food-drink';
                    break;
                case 'nightlife':
                    p.iconCategory = 'nightlife';
                    break;
            }
        }

    }

    if ( obj.hasOwnProperty( 'event' )
         && obj.event !== null ) {

        v = obj.event;

        if ( v.hasOwnProperty( 'startDateTime' )
             && v.startDateTime !== null ) {

            if ( fullSize ) {
                countdown = HPObject.prototype.formatCountdown.call( null, v.startDateTime, v.endDateTime, false, false );
            } else {
                countdown = HPObject.prototype.formatCountdown.call( null, v.startDateTime, v.endDateTime, false, true );
            }
            
            p.countdown = countdown.timing;
            p.countdownStatus = countdown.status;

        }

        if ( v.hasOwnProperty( 'repeatsWeekly' )
             && v.repeatsWeekly !== null ) {

            p.repeatsWeekly = true;

        }

        p.icon = false;

        if ( fullSize && p.image === false ) {
            p.iconSmall = false;
        }
        
    }

    if ( m !== null && e !== null && v !== null ) {
        p.isEvent = true;
    } else if ( e !== null ) {
        p.isEstablishment = true;
    } else if ( m !== null ) {
        p.isMessage = true;
    }

    return p;

};

HPObject.prototype.renderObject = function( obj, listItem, anchor, fullSize,
                                            width, geoQueryString, cssClass ) {

    'use strict';

    var t = '',
        l = window._H.localization,
        p = HPObject.prototype.extractRenderProperties.call( null, obj, fullSize );

    typeof listItem === 'undefined' ? listItem = false : false;
    typeof anchor === 'undefined' ? anchor = false : false;
    typeof fullSize === 'undefined' ? fullSize = false : false;
    typeof cssClass === 'string' ? cssClass = cssClass : false;

    if ( p.image && fullSize ) {
        p.imageHeight = Math.round( width / p.imageFullAspectRatio );
    } else {
        p.imageHeight = Math.round( width / p.imageThumbAspectRatio );
    }

    if ( listItem ) {
        t +=  '<li ';
    } else {
        t += '<div ';
    }

    t += 'id="obj-{{id}}" class="obj-theme-{{theme}}';

    if ( fullSize ) {
        t += ' obj-full-size';
    }

    t += ' {{#isEvent}}is-event{{/isEvent}}'
       + ' {{#image}}has-image{{/image}}'
       + ' {{#text}}has-text{{/text}}';

    if ( cssClass ) {
        t+= ' ' + cssClass;
    }

    t += '">';

    if ( anchor ) {
        t += '<a class="obj-anchor" href="{{objectUrl}}'
           + '{{#geoQueryString}}?{{geoQueryString}}{{/geoQueryString}}" '
           + 'title="'
           + l['OBJECT_VIEW_LABEL']
           + '">';
    }

    //location
    t += '<div class="obj-location">{{location}}</div>';

    //image
    t += '{{#image}}<img class="';

    if ( fullSize ) {
        t += 'obj-image-full" src="{{imageFullUrl}}" ';
    } else {
        t += 'obj-image-thumb" src="{{imageThumbUrl}}" ';
    }

    t += 'height="{{imageHeight}}"'
       + ' alt="' + l['OBJECT_IMAGE_ALT_LABEL'] + '" />'
       + '{{/image}}'
       + '{{#icon}}'
       + '<span class="';

    if ( fullSize ) {
        t += 'obj-icon-full{{#iconSmall}}-small{{/iconSmall}} obj-icon-{{iconCategory}}" ';
    } else {
        t += 'obj-icon-thumb{{#iconSmall}}-small{{/iconSmall}} obj-icon-{{iconCategory}}" ';
    }

    t += '></span>'
       + '{{/icon}}'
       + '{{#label}}<div class="obj-label{{#iconSmall}}-small{{/iconSmall}}">{{label}}</div>{{/label}}'
       + '<div class="obj-info">';

    //text
    t += '{{#text}}'
       + '<span class="obj-text">{{{text}}}</span>'
       + '{{/text}}'
       + '</div>';

    //address
    t += '{{#address}}<div class="obj-address-inline">{{address}}</div>{{/address}}';

    //meta
    t += '<div class="obj-meta">'
       + '{{#countdown}}'
       + '<span class="obj-countdown-{{countdownStatus}}">'
       + '{{countdown}}'
       + '</span>'
       + '{{/countdown}}'
       + '{{#repeatsWeekly}}'
       + '<span class="obj-repeats-weekly"></span>'
       + '{{/repeatsWeekly}}'
       + '{{#geoRelation}}'
       + '<span class="obj-georelation">{{geoRelation}}</span>'
       + '{{/geoRelation}}'
       + '</div>';

    if ( anchor ) {
        t += '</a>';
    }

    if ( listItem ) {
        t += '</li>';    
    } else {
        t += '</div>';
    }

    p.geoQueryString = geoQueryString;

    return Mustache.render( t, p );

};


HPObject.prototype.formatText = function( text, fullSize ) {

    'use strict';

    var msg,
        i,
        split,
        length,
        formattedText = null;

    if ( text ) {
        if ( fullSize === false ) {
            msg = text.substring( 0, 63 );
            //check to make sure we need to abbreviate
            if ( text.length >= 64 ) {
                //pull the first 128 characters
                msg = text.substring( 0,53 );
                for ( i = 53; i < 64; i++ ) {
                    //12/7/2012, cml
                    //point .charAt to the correct offset (d'oh)
                    if ( text.charAt( i ) !== '&' ) {
                        msg += text.charAt( i );
                    } else {
                        //10/2/2012, cml
                        //if we come across the start of an HTML entity, just dump the rest of the string from the preview
                        //(so we don't end up with something awkward at the end of the preview, like "blah blah&quo(...)"
                        break;
                    }
                }
                msg += '<span class="obj-abbreviated">(...)</span>';
            }
            split = msg.split( /\n/ );
        } else {
            split = text.split( /\n/ );
        }

        //fix for IE 8 ignoring a leading newline instead of treating the zero-th element of split as empty
        //cml, 10/12/2012
        if ( window._H.is.ie && text.charCodeAt( 0 ) === 10 ) {
            split[1] = split[0];
            split[0] = '';
        }

        if ( split[0].length > 0 ) {
            formattedText = '<em class="obj-headline">' + split[0] + '</em><br />';
        } else {
            formattedText = '';
        }

        if ( split.length > 1 ) {
            length = split.length - 1;
            i = 1;
            while ( i < length ) {
                if ( split[i].length > 0 ) {
                    formattedText += split[i] + '<br />';
                } else {
                    formattedText += '<br />';
                }
                i++;
            }
            formattedText += split[length];
        }

    }

    return formattedText;

};

HPObject.prototype.formatCountdown = function( startDateTime, endDateTime, doorsClose, abbreviate ) {

    'use strict';

    var startsAt = HPObject.prototype.parseDateTime( startDateTime ),
        endsAt = HPObject.prototype.parseDateTime( endDateTime ),
        now = new Date(),
        startOffset = startsAt.getTime() - now.getTime(),
        endOffset = endsAt.getTime() - now.getTime(),
        label,
        timing,
        offset,
        status,
        deadline,
        countdown,
        l = window._H.localization;

    status = 'starting';

    if ( abbreviate ) {
        label = '_LABEL_SHORT';
    } else {
        label = '_LABEL';
    }

    //countdown to event start
    if ( startOffset >= 0 && startOffset < 86400000 ) {

        timing = l['TIME_STARTS_IN' + label ];

        offset = Math.floor( ( startsAt.getTime() - now.getTime() ) / 1000 );

        countdown = HPObject.prototype.formatTimeOffset.call( null, offset );

        if ( abbreviate ) {

            if ( countdown.hours > 0 ) {
                timing += countdown.hours + l['TIME_HOUR' + label ];
            }
            if ( countdown.minutes > 0 ) {
                timing += countdown.minutes + l['TIME_MINUTE' + label ];
            }

        } else {

            if ( countdown.hours > 0 ) {
                if ( countdown.hours === 1 ) {
                    timing += countdown.hours + l['TIME_HOUR_SINGLE' + label ] + ' and ';
                } else {
                    timing += countdown.hours + l['TIME_HOUR_PLURAL' + label ] + ' and ';
                }
            }
            if ( countdown.minutes === 1 ) {
                timing += countdown.minutes + l['TIME_MINUTE_SINGLE' + label ];
            } else {
                timing += countdown.minutes + l['TIME_MINUTE_PLURAL' + label ];
            }

        }

    //countdown to event end
    } else if ( startOffset < 0 && endOffset >= 0 && endOffset < 86400000 ) {

        status = 'ending';

        timing = l['TIME_ENDS_IN' + label ];

        offset = Math.floor( ( endsAt.getTime() - now.getTime() ) / 1000 );

        countdown = HPObject.prototype.formatTimeOffset.call( null, offset );

        if ( abbreviate ) {

            if ( countdown.hours > 0 ) {
                timing += countdown.hours + l['TIME_HOUR' + label ];
            }
            if ( countdown.minutes > 0 ) {
                timing += countdown.minutes + l['TIME_MINUTE' + label ];
            }

        } else {

            if ( countdown.hours > 0 ) {
                if ( countdown.hours === 1 ) {
                    timing += countdown.hours + l['TIME_HOUR_SINGLE' + label ] + ' and ';
                } else {
                    timing += countdown.hours + l['TIME_HOUR_PLURAL' + label ] + ' and ';
                }
            }
            if ( countdown.minutes === 1 ) {
                timing += countdown.minutes + l['TIME_MINUTE_SINGLE' + label ];
            } else {
                timing += countdown.minutes + l['TIME_MINUTE_PLURAL' + label ];
            }

        }

    //upcoming event
    } else {

        timing = l['TIME_STARTS_ON' + label ];

        if ( abbreviate ) {

            timing += l['TIME_DAY_' + startsAt.getDay() + label ]
                   + ' @';
            if ( startsAt.getHours() < 10 ) {
                timing += '0';
            }
            timing += startsAt.getHours() + ':';
            if ( startsAt.getMinutes() < 10 ) {
                timing += '0';
            }
            timing += startsAt.getMinutes();
        
        } else {

            timing += l['TIME_DAY_' + startsAt.getDay() + label ]
                   + ' at ';
            if ( startsAt.getHours() < 10 ) {
                timing += '0';
            }
            timing += startsAt.getHours() + ':';
            if ( startsAt.getMinutes() < 10 ) {
                timing += '0';
            }
            timing += startsAt.getMinutes();

        }

    }

    return { status : status, timing : timing };

};

HPObject.prototype.parseDateTime = function( dateTime ) {

    'use strict';

    var date = new Date();

    date.setUTCFullYear( parseInt( dateTime.substring( 0, 4 ), 10 ) );
    date.setUTCMonth( parseInt( dateTime.substring( 5, 7 ), 10 ) - 1, parseInt( dateTime.substring( 8, 10 ), 10 ) );
    date.setUTCHours( parseInt( dateTime.substring( 11, 13 ), 10 ) );
    date.setUTCMinutes( parseInt( dateTime.substring( 14, 16 ), 10 ) );
    date.setUTCSeconds( parseInt( dateTime.substring( 17, 19 ), 10 ) );

    return date;

};

HPObject.prototype.formatTimeOffset = function( offset ) {

    'use strict';

    var days,
        hours,
        minutes,
        seconds;

    days = Math.floor( offset / 86400 );
    seconds = offset - ( days * 86400 );
    hours = Math.floor( seconds / 3600 );
    seconds = seconds - ( hours * 3600 );
    minutes = Math.floor( seconds / 60 );
    seconds = seconds - ( minutes * 60 );

    return { days: days, hours : hours, minutes : minutes, seconds : seconds };

};
