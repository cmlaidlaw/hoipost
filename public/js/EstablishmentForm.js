EstablishmentForm = function( element, name, resourceFn, callbacks, timeout, values ) {
    
    this.element = element;
    this.name = name;
    this.computeResource = resourceFn;
    this.resource = 'not set';
    this.successCallback = callbacks.success || function() { return true; };
    this.errorCallback = callbacks.error || function() { return true; };
    this.progressCallback = callbacks.progress || function() { return true; };
    this.timeout = timeout || 5000;
    this.errorCode = false;
    this.status = false;
    this.data = false;

    this.hasImage = false;

    this.init( values );

};

EstablishmentForm.prototype = MessageForm.prototype;

EstablishmentForm.prototype.init = function( values ) {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        t = '',//emplate
        l = window._H.localization,
        p,//roperties
        e,//stablishment
        html,
        $container = $( this.element ),
        select,
        city,
        node,
        category;

    if ( this.element !== null ) {

        if ( typeof values === 'undefined' || values === null ) {
            values = {};
        }

        //this item: calculates remote resource to which we post data
        Q.addItem( function( nextItem ) {
            that.computeResource( that, nextItem );
        } );

        //this item: generates form HTML and inserts it into the DOM
        Q.addItem( function( nextItem ) {

            t = '<form id="{{name}}-form" method="POST" action="{{resource}}" enctype="multipart/form-data" autocomplete="off">'
              + '<input type="hidden" name="MAX_FILE_SIZE" value="{{uploadMaxSize}}" />'
              + '<input id="{{name}}-retain-image" type="hidden" name="retainImage" value="{{#hasLogo}}1{{/hasLogo}}{{^hasLogo}}0{{/hasLogo}}" />'
              + '<div class="section-content-4-col">'
              + '<table><tbody>'
              + '{{#admin}}'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-owner">{{accountLabel}}</label></td>'
              + '<td>'
              + '<input id="{{name}}-owner" class="form-input-4-col" class="{{name}}-owner" name="accountId" type="text" MAX_LENGTH="20" placeholder="{{accountPlaceholder}}" value="{{accountId}}" />'
              + '<span class="form-input-description">( Dummy account ID: 2 )</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-city">{{cityLabel}}</label></td>'
              + '<td>'
              + '<select id="{{name}}-city" class="form-select-4-col" class="{{name}}-city" name="city" placeholder="{{cityPlaceholder}}">'
              + '</select>'
              + '<span class="form-input-description"></span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-lat">{{latLabel}}</label></td>'
              + '<td>'
              + '<input id="{{name}}-lat" class="form-input-4-col" name="lat" type="text" maxlength="16" placeholder="{{latPlaceholder}}" value="{{lat}}" />'
              + '<span class="form-input-description"></span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-lng">{{lngLabel}}</label></td>'
              + '<td>'
              + '<input id="{{name}}-lng" class="form-input-4-col" name="lng" type="text" maxlength="16" placeholder="{{lngPlaceholder}}" value="{{lng}}" />'
              + '<span class="form-input-description"></span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-category">{{categoryLabel}}</label></td>'
              + '<td>'
              + '<select id="{{name}}-category" class="form-select-4-col" name="category" placeholder="{{categoryPlaceholder}}" />'
              + '</select>'
              + '<span class="form-input-description"></span>'
              + '</td>'
              + '</tr>'
              + '{{/admin}}'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-name">{{establishmentNameLabel}}</label></td>'
              + '<td>'
              + '<input id="{{name}}-name" class="form-input-4-col" class="{{name}}-name" name="name" type="text" maxlength="256" placeholder="{{establishmentNamePlaceholder}}" value="{{establishmentName}}" />'
              + '<span class="form-input-description">{{establishmentNameMaxLength}}</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-image-control">{{logoLabel}}</label></td>'
              + '<td>'
              + '<img id="{{name}}-preview-image" src="{{logoThumbUrl}}" height="{{logoHeight}}" alt="{{logoLabel}}" />'
              + '<div class="form-image-control-container">'
              + '<button id="{{name}}-image-add" class="ui-button form-image-control-add-button" type="button">{{addLogoLabel}}</button>'
              + '<button id="{{name}}-image-remove" class="ui-button form-image-control-remove-button" type="button"{{#hasLogo}} style="display:block;"{{/hasLogo}}>{{removeLogoLabel}}</button>'
              + '<input id="{{name}}-image-control" class="form-image-control" name="logo" type="file" accept="image/*" />'
              + '</div>'
              + '<span class="form-input-description">{{logoMaxFileSize}}</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-description">{{descriptionLabel}}</label></td>'
              + '<td>'
              + '<textarea id="{{name}}-description" class="form-textarea-4-col" name="description" placeholder="{{descriptionPlaceholder}}">{{description}}</textarea>'
              + '<span class="form-input-description">{{descriptionMaxLength}}</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-address">{{addressLabel}}</label></td>'
              + '<td>'
              + '<textarea id="{{name}}-address" class="form-textarea-4-col" name="address" placeholder="{{addressPlaceholder}}">{{address}}</textarea>'
              + '<span class="form-input-description">{{addressMaxLength}}</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-tel">{{telLabel}}</label></td>'
              + '<td>'
              + '<input id="{{name}}-tel" class="form-input-4-col"name="tel" type="text" maxlength="16" placeholder="{{telPlaceholder}}" value="{{tel}}" />'
              + '<span class="form-input-description">{{telMaxLength}}</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-email">{{emailLabel}}</label></td>'
              + '<td>'
              + '<input id="{{name}}-email" class="form-input-4-col" name="email" type="text" maxlength="256" placeholder="{{emailPlaceholder}}" value="{{email}}" />'
              + '<span class="form-input-description">{{emailMaxLength}}</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td><label class="form-label-4-col" for="{{name}}-url">{{urlLabel}}</label></td>'
              + '<td>'
              + '<input id="{{name}}-url" class="form-input-4-col" name="url" type="text" maxlength="256" placeholder="{{urlPlaceholder}}" value="{{url}}" />'
              + '<span class="form-input-description">{{urlMaxLength}}</span>'
              + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td></td>'
              + '<td><input id="{{name}}-submit-button" class="ui-button" type="submit" value="{{submitButtonLabel}}" />'
              + '<button id="{{name}}-cancel-button" class="ui-button" type="button">{{cancelButtonLabel}}</button>'
              + '<div class="clear"></div>'
              + '</td>'
              + '</tr>'
              + '</tbody></table>'
              + '</div>'
              + '<div class="section-content-4-col">'
              + '</div>'
              + '<div class="clear"></div>'
              + '</form>';

            p = {
                name: that.name,
                resource: that.resource,
                uploadMaxSize: l['UPLOAD_MAX_SIZE'],
                accountLabel: l['ESTABLISHMENT_ACCOUNT_LABEL'],
                accountPlaceholder: l['ESTABLISHMENT_ACCOUNT_PLACEHOLDER'],
                cityLabel: l['ESTABLISHMENT_CITY_LABEL'],
                cityPlaceholder: l['ESTABLISHMENT_CITY_PLACEHOLDER'],
                latLabel: l['ESTABLISHMENT_LAT_LABEL'],
                latPlaceholder: l['ESTABLISHMENT_LAT_PLACEHOLDER'],
                lngLabel: l['ESTABLISHMENT_LNG_LABEL'],
                lngPlaceholder: l['ESTABLISHMENT_LNG_PLACEHOLDER'],
                categoryLabel: l['ESTABLISHMENT_CATEGORY_LABEL'],
                categoryPlaceholder: l['ESTABLISHMENT_CATEGORY_PLACEHOLDER'],
                establishmentNameLabel: l['ESTABLISHMENT_NAME_LABEL'],
                establishmentNamePlaceholder: l['ESTABLISHMENT_NAME_PLACEHOLDER'],
                establishmentNameMaxLength: l['ESTABLISHMENT_NAME_MAX_LENGTH'],
                logoLabel: l['ESTABLISHMENT_LOGO_LABEL'],
                addLogoLabel: l['ESTABLISHMENT_ADD_LOGO_LABEL'],
                removeLogoLabel: l['ESTABLISHMENT_REMOVE_LOGO_LABEL'],
                logoMaxFileSize: l['ESTABLISHMENT_LOGO_MAX_SIZE'],
                descriptionLabel: l['ESTABLISHMENT_DESCRIPTION_LABEL'],
                descriptionPlaceholder: l['ESTABLISHMENT_DESCRIPTION_PLACEHOLDER'],
                descriptionMaxLength: l['ESTABLISHMENT_DESCRIPTION_MAX_LENGTH'],
                addressLabel: l['ESTABLISHMENT_ADDRESS_LABEL'],
                addressPlaceholder: l['ESTABLISHMENT_ADDRESS_PLACEHOLDER'],
                addressMaxLength: l['ESTABLISHMENT_ADDRESS_MAX_LENGTH'],
                telLabel: l['ESTABLISHMENT_TEL_LABEL'],
                telPlaceholder: l['ESTABLISHMENT_TEL_PLACEHOLDER'],
                telMaxLength: l['ESTABLISHMENT_TEL_MAX_LENGTH'],
                emailLabel: l['ESTABLISHMENT_EMAIL_LABEL'],
                emailPlaceholder: l['ESTABLISHMENT_EMAIL_PLACEHOLDER'],
                emailMaxLength: l['ESTABLISHMENT_EMAIL_MAX_LENGTH'],
                urlLabel: l['ESTABLISHMENT_URL_LABEL'],
                urlPlaceholder: l['ESTABLISHMENT_URL_PLACEHOLDER'],
                urlMaxLength: l['ESTABLISHMENT_URL_MAX_LENGTH'],
                submitButtonLabel: l['ESTABLISHMENT_UPDATE_SUBMIT_LABEL'],
                cancelButtonLabel: l['ESTABLISHMENT_UPDATE_CANCEL_LABEL']
            };

            if ( values.hasOwnProperty( 'admin' ) ) {
                p.admin = true;
                if ( values.hasOwnProperty( 'accountId' )
                     && values.accountId !== null ) {
                    p.accountId = values.accountId;
                }
                if ( values.hasOwnProperty( 'availableCities' )
                     && values.availableCities !== null ) {
                    p.availableCities = values.availableCities;
                }
                if ( values.hasOwnProperty( 'availableCategories' )
                     && values.availableCategories !== null ) {
                    p.availableCategories = values.availableCategories;
                }
                if ( values.hasOwnProperty( 'position' )
                     && values.position.hasOwnProperty( 'lat' )
                     && values.position.hasOwnProperty( 'lng' ) ) {
                    p.lat = values.position.lat;
                    p.lng = values.position.lng;
                }
            }

            if ( values.hasOwnProperty( 'establishment' ) ) {
                e = values.establishment;
                if ( e.hasOwnProperty( 'name' )
                     && e.name !== null ) {
                    p.establishmentName = e.name;
                }
                if ( e.hasOwnProperty( 'logo' )
                     && e.logo !== null ) {
                    p.hasLogo = true;
                    p.logoThumbUrl = e.logo.thumbUrl
                    p.logoHeight = window._H.imagePreviewWidth / e.logo.fullAspectRatio;
                } else {
                    p.hasLogo = false;
                    p.logoThumbUrl = l['ESTABLISHMENT_LOGO_PLACEHOLDER_URL'];
                    p.logoHeight = 150;
                }
                if ( e.hasOwnProperty( 'description' )
                     && e.description !== null ) {
                    p.description = e.description;
                }
                if ( e.hasOwnProperty( 'address' )
                     && e.address !== null ) {
                    p.address = e.address;
                }
                if ( e.hasOwnProperty( 'tel' )
                     && e.tel !== null ) {
                    p.tel = e.tel;
                }
                if ( e.hasOwnProperty( 'email' )
                     && e.email !== null ) {
                    p.email = e.email;
                }
                if ( e.hasOwnProperty( 'url' )
                     && e.url !== null ) {
                    p.url = e.url;
                }
            }

            //add a default logo placeholder if no logo exists
            //(i.e. on a blank 'create establishment' form)
            if ( !p.hasOwnProperty( 'logoThumbUrl' ) ) {
                p.logoThumbUrl = l['ESTABLISHMENT_LOGO_PLACEHOLDER_URL'];
                p.logoHeight = 150;
            }

            html = Mustache.render( t, p );

            $container.replaceContent( html );

            nextItem();

        } );

        //this item: set default form values and event listeners
        Q.addItem( function( nextItem ) {

            that.updateStatus( 'incomplete' );

            if ( p.hasOwnProperty( 'availableCities' ) ) {
                select = document.getElementById( that.name + '-city' );
                for ( city in p.availableCities ) {
                    node = document.createElement('option');
                    node.value = p.availableCities[city];
                    if ( typeof e !== 'undefined'
                         && e.hasOwnProperty( 'city' )
                         && p.availableCities[city] === e.city ) {
                        node.setAttribute( 'selected', true );
                    }
                    node.appendChild( document.createTextNode( p.availableCities[city] ) );
                    select.appendChild(node);
                }
            }

            if ( p.hasOwnProperty( 'availableCategories' ) ) {
                select = document.getElementById( that.name + '-category' );
                for ( category in p.availableCategories ) {
                    node = document.createElement('option');
                    node.value = p.availableCategories[category];
                    if ( typeof e !== 'undefined'
                         && e.hasOwnProperty( 'category' )
                         && p.availableCategories[category] === e.category ) {
                        node.setAttribute( 'selected', true );
                    }
                    node.appendChild( document.createTextNode( p.availableCategories[category] ) );
                    select.appendChild(node);
                }
            }

            //set up initial event listener for the picture upload control
            $( '#' + that.name + '-image-control' ).on( 'change', function( e ) {
                that.processImage( e, 'logo', that.name + '-preview-image' );
            } );

            $( '#' + that.name + '-image-remove' ).button( window._H.has.touch, function( button ) {
                //if the remove button is activated, remove the hint to keep the existing picture
                that.removeImage( 'logo', that.name + '-preview-image', l['ESTABLISHMENT_LOGO_PLACEHOLDER_URL'] );
            }, false );

            $( '#' + that.name + '-submit' ).button( window._H.has.touch, function( button ) {
                document.getElementById( that.name + '-form' ).submit();
            }, false );

            if ( values.admin === true ) {

                //prevent submission of empty forms (need to do this on the form element as well as the submit button!)
                $( '#' + that.name + '-form' ).on( 'submit', function( e ) {
                    var name = document.getElementById( that.name + '-name' ).value,
                        lat = document.getElementById( that.name + '-lat' ).value,
                        lng = document.getElementById( that.name + '-lng' ).value;
                    if ( name === null
                         || name === ''
                         || name.replace( /\s+/, '' ).length === 0 ) {
                        $.prototype.halt.call( null, e );
                        alert( window._H.localization['ESTABLISHMENT_NAME_VALIDATION_ERROR'] );
                        return false;
                    }
                    if ( lat === null
                         || lat === '' ) {
                        $.prototype.halt.call( null, e );
                        alert( window._H.localization['ESTABLISHMENT_LAT_VALIDATION_ERROR'] );
                        return false;
                    }
                    if ( lng === null
                         || lng === '' ) {
                        $.prototype.halt.call( null, e );
                        alert( window._H.localization['ESTABLISHMENT_LNG_VALIDATION_ERROR'] );
                        return false;
                    }
                } );

            } else {

                //prevent submission of empty forms (need to do this on the form element as well as the submit button!)
                $( '#' + that.name + '-form' ).on( 'submit', function( e ) {
                    var name = document.getElementById( that.name + '-name' ).value;
                    if ( name === null
                         || name.replace( /\s+/, '' ).length === 0 ) {
                        $.prototype.halt.call( null, e );
                        alert( window._H.localization['ESTABLISHMENT_NAME_VALIDATION_ERROR'] );
                        return false;
                    }
                } );

            }

            $( '#' + that.name + '-cancel-button' ).button( H.has.touch, function ( button ) {
                $( that.element ).hide();
                $( '#establishment-info' ).show();
                window.scrollTo( 0, 1 );
            } );

            nextItem();

        } );

        Q.addItem( function( nextItem ) {

            //pre-process any provided values
            that.updateFormStatus();

        } );

        Q.execute();

    }

};

EstablishmentForm.prototype.updateFormStatus = function() {

    'use strict';
/*
    if ( this.hasText || this.hasImage ) {
        //$( '#' + this.name + '-message-content-validation-error' ).hide();
    } else {
        //$( '#' + this.name + '-message-content-validation-error' ).show();
    }

    if ( this.hasStartDate
         && this.hasStartHours
         && this.hasStartMinutes ) {
        this.calculateNextOccurrence();
    }

    if ( this.hasStartDate
         && this.hasStartHours
         && this.hasStartMinutes
         && this.hasEndDate
         && this.hasEndHours
         && this.hasEndMinutes ) {
        this.checkStartEndOrder();
    }

    if ( this.hasGoodStartEndOrder ) {
        //$( '#' + this.name + '-date-order-validation-error' ).hide();
    } else {
        //$( '#' + this.name + '-date-order-validation-error' ).show();
    }

    if ( ( this.hasText || this.hasImage )
           && this.hasStartDate
           && this.hasStartHours
           && this.hasStartMinutes
           && this.hasEndDate
           && this.hasEndHours
           && this.hasEndMinutes
           && this.hasGoodStartEndOrder ) {

        this.updateStatus( 'complete' );
        

    } else {
        this.updateStatus( 'incomplete' );
    }
*/
};