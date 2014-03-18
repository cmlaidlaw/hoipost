MessageForm = function ( element, name, resourceFn, callbacks, values, timeout ) {

    this.element = element;
    this.name = name;
    this.computeResource = resourceFn;
    this.resource = 'not set';
    this.message = null;
    this.successCallback = callbacks.success || function () { return true; };
    this.errorCallback = callbacks.error || function () { return true; };
    this.progressCallback = callbacks.progress || function () { return true; };
    this.timeout = timeout || 5000;
    this.errorCode = false;
    this.status = false;
    this.data = false;

    this.hasImage = false;
    this.hasText = false;

    this.init( values );

};

MessageForm.prototype.init = function ( values ) {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        t = '',//emplate
        l = window._H.localization,
        p,//roperties
        m,//essage
        h,//tml
        $container = $( this.element );

    if ( typeof values === 'undefined' ) {
        values = {};
    }

    //this item: calculates remote resource to which we post data
    Q.addItem( function ( nextItem ) {
        that.computeResource( that, nextItem );
    } );

    //this item: generates form HTML and inserts it into the DOM
    Q.addItem( function ( nextItem ) {

        t += '<form id="{{name}}-form" method="POST" action="{{resource}}" enctype="multipart/form-data" autocomplete="off">'
           + '<input type="hidden" name="MAX_FILE_SIZE" value="{{uploadMaxSize}}" />'
           + '{{#replyTo}}'
           + '<input type="hidden" name="replyTo" value="{{replyTo}}" />'
           + '{{/replyTo}}'
           + '<input id="{{name}}-lat" name="lat" type="hidden" value="false" />'
           + '<input id="{{name}}-lng" name="lng" type="hidden" value="false" />'
           + '<div id="{{name}}-message" class="section-content-4-col">'
           + '<div id="{{name}}-image-container" class="form-image-control-container">'
           + '<button id="{{name}}-image-add" class="ui-button form-image-control-add-button" type="button">{{addImageLabel}}'
           + '<button id="{{name}}-image-remove" class="ui-button form-image-control-remove-button" type="button"{{#hasImage}} style="display:block;"{{/hasImage}}>{{removeImageLabel}}</button>'
           + '<input id="{{name}}-image-control" class="form-image-control" name="image" type="file" accept="image/*" />'
           + '</div>'
           + '<textarea id="{{name}}-text" class="form-textarea-4-col" name="text" placeholder="{{textPlaceholder}}" maxlength="{{textMaxLength}}">'
           + '{{#text}}{{text}}{{/text}}'
           + '</textarea>'
           + '<button id="{{name}}-submit" class="ui-button" type="submit">'
           + '{{createButtonLabel}}'
           + '</button>'
           + '</div>'
        //preview
           + '<div id="{{name}}-preview" class="section-content-4-col">'
           + '<div id="{{name}}-preview-object" class="obj-theme-1'
           + '{{#hasImage}} has-image{{/hasImage}}'
           + '{{#hasText}} has-text{{/hasText}}'
           + '">'
           + '<div class="obj-location">Preview</div>'
           + '<div class="pic-loading"></div>'
           + '{{#hasImage}}'
           + '<img id="{{name}}-preview-image" class="obj-image-thumb" src="{{imageFullUrl}}" height="{{imageHeight}}" />'
           + '{{/hasImage}}'
           + '{{^hasImage}}'
           + '<img id="{{name}}-preview-image" class="obj-image-thumb" src="{{imagePlaceholderUrl}}" />'
           + '{{/hasImage}}'
           + '<div class="obj-info">'
           + '<div id="{{name}}-preview-text" class="obj-text">'
           + '{{#text}}{{text}}{{/text}}'
           + '</div>'
           + '</div>'
           + '<div class="obj-meta">'
           + '<span class="obj-georelation">'
           + '{{georelationPlaceholder}}'
           + '</span>'
           + '<span class="obj-replies">'
           + '{{replyCountPlaceholder}}'
           + '</span>'
           + '</div>'
           + '</div>'
           + '</div>'
           + '</div>'
           + '<div class="clear"></div>'
           + '</form>';

        p = {
            name: that.name,
            resource: that.resource,
            uploadMaxSize: l['UPLOAD_MAX_SIZE'],
            addImageLabel: l['MESSAGE_ADD_IMAGE_LABEL'],
            removeImageLabel: l['MESSAGE_REMOVE_IMAGE_LABEL'],
            textPlaceholder: l['MESSAGE_TEXT_PLACEHOLDER'],
            textMaxLength: l['MESSAGE_TEXT_MAX_LENGTH'],
            georelationPlaceholder: 'XXX',
            replyCountPlaceholder: 'XXX',
            createButtonLabel: l['MESSAGE_CREATE_SUBMIT_LABEL'],
            cancelButtonLabel: 'Cancel'
        };

        if ( values.hasOwnProperty( 'replyTo' ) ) {
            p.replyTo = values.replyTo;
            p.createButtonLabel = l['REPLY_CREATE_SUBMIT_LABEL'];
        }

        h = Mustache.render( t, p );

        $container.replaceContent( h );

        nextItem();

    } );

    //this item: set default form values and event listeners
    Q.addItem( function ( nextItem ) {

        that.updateStatus( 'incomplete' );

        //set up initial event listener for the image upload control
        $( '#' + that.name + '-image-control' ).on( 'change', function ( e ) {
            that.processImage( e, 'image', that.name + '-preview-image' );
        } );

        $( '#' + that.name + '-image-remove' ).button( window._H.has.touch, function ( button ) {
            that.removeImage( 'image', that.name + '-preview-image', '' );
        }, false );

        $( '#' + that.name + '-text' ).on( 'keyup', function () {
            that.processText();
        });

        $( '#' + that.name + '-submit' ).button( window._H.has.touch, function ( button ) {
            that.disableSubmit();
            H.getGeoLocation(
                function (latLng) {
                    $( '#' + that.name + '-lat' ).attr( 'value', latLng.lat );
                    $( '#' + that.name + '-lng' ).attr( 'value', latLng.lng );
                    document.getElementById( that.name + '-form' ).submit();
                    nextItem();
                },
                function (error) {
                    document.getElementById( that.name + '-form' ).submit();
                    nextItem();
                }
            );
        }, false );


        //prevent submission of empty forms (need to do this on the form element as well as the submit button!)
        $( '#' + that.name + '-form' ).on( 'submit', function ( e ) {
            //make sure the form is valid to submit
            if ( that.status !== 'complete' ) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        } );

    } );

    Q.execute();

};

MessageForm.prototype.updateStatus = function ( status ) {

    'use strict';

    var $element = $( this.element );

    $element.removeClass( 'loading' );
    $element.removeClass( 'success' );
    $element.removeClass( 'complete' );
    $element.removeClass( 'incomplete' );
    $element.removeClass( 'empty' );
    $element.removeClass( 'error' );

    switch (status) {

        case 'loading':
            this.status = 'loading';
            this.disableSubmit();
            $element.addClass( 'loading' );
        break;

        case 'success':
            this.status = 'success';
            this.clearInput();
            this.enableSubmit();
            $element.addClass( 'success' );
        break;

        case 'complete':
            this.status = 'complete';
            this.enableSubmit();
            $element.addClass( 'complete' );
        break;

        case 'incomplete':
            this.status = 'incomplete';
            this.disableSubmit();
            $element.addClass( 'incomplete' );
        break;

        case 'empty':
            this.status = 'empty';
            this.disableSubmit();
            $element.addClass( 'empty' );
        break;

        case 'error':
            this.status = 'error';
            this.disableSubmit();
            $element.addClass( 'error' );
        break;
    }

};

MessageForm.prototype.enableSubmit = function () {

    'use strict';

    var $button = $('#' + this.name + '-submit');

    if ( $button.hasClass( 'disabled' ) ) {
        $button.removeClass( 'disabled' ).removeAttr( 'disabled' );
    }

};

MessageForm.prototype.disableSubmit = function () {

    'use strict';

    var $button = $('#' + this.name + '-submit');

    if ( !$button.hasClass( 'disabled' ) ) {
        $button.addClass( 'disabled' ).attr( 'disabled', 'disabled' );
    }

};

MessageForm.prototype.updateFormStatus = function () {

    'use strict';

    if ( this.hasText || this.hasImage ) {
        this.updateStatus( 'complete' );
    } else {
        this.updateStatus( 'incomplete' );
    }

};

MessageForm.prototype.reset = function () {

    'use strict';

    this.updateStatus( 'incomplete' );

    $( '#' + this.name + '-text' ).attr( 'value', '' );
    this.processText();
    this.removeImage( 'image', this.name + '-preview-image', '' );
    
};

MessageForm.prototype.processText = function () {

    'use strict';

    var message = document.getElementById( this.name + '-text' ).value,
        messageText = '',
        $preview = $( '#' + this.name + '-preview-object' );

    if ( message.replace(/\s+/, '').length > 0 ) {

        messageText = HPObject.prototype.formatText.call( null, document.getElementById( this.name + '-text' ).value, false );

        if ( messageText !== null ) {

            $( '#' + this.name + '-preview-text' ).replaceContent( messageText );
            if ( !$preview.hasClass( 'has-text' ) ) {
                $preview.addClass( 'has-text' );
            }
            this.hasText = true;
            this.updateFormStatus();

        }

    } else {

        $( '#' + this.name + '-preview-text' ).replaceContent( '' );
        $preview.removeClass( 'has-text' );
        this.hasText = false;
        this.updateFormStatus();

    }

};


/* image processing */
MessageForm.prototype.extractTIFFData = function ( JPEGBinary ) {

    'use strict';

    var bin = JPEGBinary,
        length = JPEGBinary.length,
        arrayBuffer = new ArrayBuffer(length),
        int8View = new Int8Array(arrayBuffer),
        i,
        littleEndian = false,
        TIFFStart,
        IFDOffset;

    function getByteAt ( offset ) {
        'use strict';
        if ( offset < arrayBuffer.byteLength ) {
            return new Uint8Array( arrayBuffer, offset, 1 )[0];
        }
        return 0;
    }

    function getUShortAt ( offset ) {
        'use strict';
        var b0, b1;
        if ( offset < arrayBuffer.byteLength - 1 ) {
            if ( littleEndian ) {
                b0 = getByteAt( offset );
                b1 = getByteAt( offset + 1 );
            } else {
                b0 = getByteAt( offset + 1 );
                b1 = getByteAt( offset );
            }
            return (b1 << 8) + b0;
        }
        return 0;
    }

    function getULongAt ( offset ) {
        'use strict';
        var b0, b1, b2, b3;
        if ( offset < arrayBuffer.byteLength - 3 ) {
            if ( littleEndian ) {
                b0 = getByteAt( offset );
                b1 = getByteAt( offset + 1 );
                b2 = getByteAt( offset + 2 );
                b3 = getByteAt( offset + 3 );
            } else {
                b0 = getByteAt( offset + 3 );
                b1 = getByteAt( offset + 2 );
                b2 = getByteAt( offset + 1 );
                b3 = getByteAt( offset );
            }
            return ( b3 * Math.pow(2, 24) ) + ( b2 << 16 ) + ( b1 << 8 ) + b0;
        }
        return 0;
    }
    
    function getURationalAt ( offset ) {
        'use strict';
        return { num: getULongAt( offset ), den : getULongAt( offset + 4 ) };
    }
    
    function getIFDEntries ( start, numIFDEntries ) {

        var offset = start,
            entryCount = 0,
            tag,
            count,
            value,
            data = {};

        while (entryCount < numIFDEntries ) {

            tag = false;

            switch ( getUShortAt( offset ) ) {
                case 256:
                    tag = 'ImageWidth';
                break;
                case 257:
                    tag = 'ImageLength';
                break;
                case 259:
                    tag = 'Compression';
                break;
                case 262:
                    tag = 'PhotometricInterpretation';
                break;
                case 271:
                    tag = 'Make';
                break;
                case 272:
                    tag = 'Model';
                break;
                case 274:
                    tag = 'Orientation';
                break;
                case 296:
                    tag = 'ResolutionUnit';
                break;
                case 282:
                    tag = 'XResolution';
                break;
                case 283:
                    tag = 'YResolution';
                break;
                case 305:
                    tag = 'Software';
                break;
                case 306:
                    tag = 'DateTime';
                break;
                case 34665:
                    tag = 'ExifOffset';
                break;
                case 34853:
                    tag = 'GPSInfoOffset';
                break;
                default:
                break;
            }
            
            if ( tag ) {

                value = false;

                switch ( getUShortAt( offset + 2 ) ) {
                  case 1:
                  //what's a signed byte look like?
                  case 6:
                      //byte fits into the 4-byte value entry
                      value = getByteAt( offset + 8 );
                  break;
                  case 2:
                      count = getULongAt( offset + 4 );
                      //if it's short enough to fit into the four-byte value entry, look for it there
                      if (count <= 4) {
                          value = bin.substring( offset + 8 );
                      //otherwise, check at the specified offset
                      } else {
                          value = bin.substring( TIFFStart + getULongAt( offset + 8 ), TIFFStart + getULongAt( offset + 8 ) + count );
                      }
                  break;
                  case 3:
                      //short fits into the 4-byte value entry
                      value = getUShortAt( offset + 8 );
                  break;
                  case 4:
                      //long fits into the 4-byte value entry
                      value = getULongAt( offset + 8 );
                  break;
                  case 5:
                      //rational doesn't fit into the 4-byte value entry, so look for it
                      //at the specified offset
                      value = getURationalAt( offset + 8 );
                  break;
                }

                if ( tag && value ) {
                    data[tag] = value;
                }

            }

            entryCount++;
            offset = offset + 12;

        }

        return data;

    };

    //reminder for endianness (0 as a short):
    //0008 big endian
    //0800 little endian

    //fill the array buffer with the binary data
    for ( i = 0; i < length; i++ ) {
        int8View[i] = bin[i].charCodeAt(0);
    }

    //find the APP1 marker
    for ( i = 0; i < 128; i++ ) {
        
        if ( getByteAt( i ) === 0xff && getByteAt( i + 1 ) === 0xe1 ) {

            //skip ahead past the Exif marker
            i = i + 8;

            //test for the II little endian marker
            if ( getByteAt( i ) === 0x00 &&
                 getByteAt( i + 1 ) === 0x00 &&
                 getByteAt( i + 2 ) === 0x49 &&
                 getByteAt( i + 3 ) === 0x49 ) {
                //little endian
                littleEndian = true;
            }

            //skip the '42' magic number and then find the offset
            //to the first IFD to pull the TIFF data
            TIFFStart = i + 2;
            IFDOffset = TIFFStart + getULongAt( TIFFStart + 4 );
            return getIFDEntries( IFDOffset + 2, getUShortAt( IFDOffset ) );

        }
        
    }

    return false;

};

MessageForm.prototype.processImage = function ( e, dataElementName, previewElementId ) {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        file = false,
        fileSlice,
        reader = new FileReader(),
        TIFFData,
        canvas = document.createElement( 'canvas' ),
        ctx = canvas.getContext( '2d' ),
        tempImage,
        orientation,
        $previewElement = $( '#' + previewElementId ),
        inline,
        orient;

/******************************************************************************

    //indicate thumbnail preview is loading here to give immediate feedback

    //need more robust control over the preview pic... maybe some direct methods
    //to transition the state and also to clear out the current src properly
    //instead of just 'removeClass('has-image')...
    
******************************************************************************/
    $( '#' + that.name + '-preview' ).removeClass( 'has-image' );

    if ( e.target.files.length > 0 ) {

        file = e.target.files[0];

        that.imageFilename = e.target.files[0].name;

        if ( !file.type.match(/image.*/) ) {
            //if it's not an image, remove it by recycling the input-image-control element
            return false;
        }

        Q.addItem( function ( nextItem ) {

            if ( file.type === 'image/jpeg' ) {

                //first, check for TIFF info for orientation data
                if ( file.slice ) {
                    fileSlice = file.slice( 0, 65536 );
                } else if ( file.webkitSlice ) {
                    fileSlice = file.webkitSlice( 0, 65536 );
                } else if ( file.mozSlice ) {
                    fileSlice = file.mozSlice( 0, 65536 );
                } else {
                    fileSlice = file;
                }

                reader.onload = function ( e ) {
                    TIFFData = that.extractTIFFData( e.target.result );
                    nextItem();
                }
                reader.readAsBinaryString( fileSlice );

            } else {
                nextItem();
            }

        });

        Q.addItem( function ( nextItem ) {

            reader.onload = function (e) {

                //second, downsize using an unattached <canvas> element
                if ( file.type === 'image/jpeg' ) {

                    tempImage = new Image();
                    tempImage.onload = function ( e ) {

                        orientation = TIFFData['Orientation'] || 1;

                        if ( tempImage.width > tempImage.height ) {
                            canvas.width = 800;
                            canvas.height = 800 * ( tempImage.height / tempImage.width );
                        } else {
                            canvas.width = 800 / ( tempImage.height / tempImage.width );
                            canvas.height = 800;
                        }

                        if ( window._H.is.ios ) {
                            that.renderImageToCanvas( tempImage, canvas, { width: canvas.width, height: canvas.height }, false );
                        } else {
                            ctx.drawImage( tempImage, 0, 0, canvas.width, canvas.height );
                        }

                        //remove the original file upload control so the name 'pic' doesn't get used
                        //by two separate input elements
                        $( '#' + that.name + '-image-control' ).remove();

                        //append an actual input element here to avoid an annoying-looking bug in how
                        //the append() method treats string input (looks like it messes up existing
                        //event listeners)
                        inline = document.createElement( 'input' );
                        inline.id = that.name + '-inline-image';
                        inline.type = 'hidden';
                        inline.name = dataElementName;
                        inline.value = canvas.toDataURL( 'image/jpeg', 0.7 );

                        $( '#' + that.name + '-form' ).append( inline, function () {
                            orient = document.createElement( 'input' );
                            orient.id = that.name + '-inline-orientation';
                            orient.type = 'hidden';
                            orient.name = 'orientation';
                            orient.value = orientation;
                            
                            $( '#' + that.name + '-form' ).append( orient, function () {
                                that.hasImage = true;
                                that.updateFormStatus();
                                $( '#' + that.name + '-image-remove' ).show();
                                nextItem();
                            } );

                        } );

                    }
                    tempImage.src = e.target.result;

                } else {
                    that.hasImage = true;
                    that.updateFormStatus();
                    $( '#' + that.name + '-image-remove' ).show();
                    
                    nextItem();
                }

                //cml, 3/4/2013
                //to do:
                //patch through a direct thumbnail for non-JPEG files
                $previewElement.elements[0].onload = function () {

                    $previewElement.removeAttr( 'height' );
                    $previewElement.parent().addClass( 'has-image' );
                    //console.log('Total time: ' + (Date.now() - now) + 'ms');
                    
                }
                //thumb.src = window.URL.createObjectURL( new Blob( [new DataView( e.target.result )], {type: 'image/jpeg'} ) );
                $previewElement.elements[0].src = e.target.result;

            }

            //cml, 2/14/2013
            //to do:
            //figure out why the readAsArrayBuffer / createObjectURL combination doesn't seem to work on safari
            //reader.readAsArrayBuffer( file );
            reader.readAsDataURL( file );

        });

        Q.execute();

    } else {

        that.hasImage = false;
        $( '#' + that.name + '-image-remove' ).hide();
        this.updateFormStatus();

    }
    
};

/**
* Mega pixel image rendering library for iOS6 Safari
*
* Fixes iOS6 Safari's image file rendering issue for large size image (over mega-pixel),
* which causes unexpected subsampling when drawing it in canvas.
* By using this library, you can safely render the image with proper stretching.
*
* Copyright (c) 2012 Shinichi Tomita <shinichi.tomita@gmail.com>
* Released under the MIT license
* https://github.com/stomita/ios-imagefile-megapixel/blob/master/src/megapix-image.js
*/

MessageForm.prototype.detectSubsampling = function ( img ) {

    'use strict';

    var iw = img.naturalWidth,
        ih = img.naturalHeight,
        canvas = document.createElement( 'canvas' );

    // subsampling may happen over megapixel image
    if ( iw * ih > 1024 * 1024 ) {

        canvas.width = canvas.height = 1;
        var ctx = canvas.getContext( '2d' );
        ctx.drawImage( img, -iw + 1, 0 );
        // subsampled image becomes half smaller in rendering size.
        // check alpha channel value to confirm image is covering edge pixel or not.
        // if alpha value is 0 image is not covering, hence subsampled.
        return ctx.getImageData( 0, 0, 1, 1 ).data[3] === 0;

    } else {

        return false;

    }

};

/**
* Detecting vertical squash in loaded image.
* Fixes a bug which squash image vertically while drawing into canvas for some images.
*/
MessageForm.prototype.detectVerticalSquash = function( img, iw, ih ) {

    var canvas = document.createElement('canvas');
    canvas.width = 1;
    canvas.height = ih;
    var ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    var data = ctx.getImageData(0, 0, 1, ih).data;
    // search image edge pixel position in case it is squashed vertically.
    var sy = 0;
    var ey = ih;
    var py = ih;
    while (py > sy) {
      var alpha = data[(py - 1) * 4 + 3];
      if (alpha === 0) {
        ey = py;
      } else {
        sy = py;
      }
      py = (ey + sy) >> 1;
    }
    var ratio = (py / ih);
    return (ratio===0)?1:ratio;
  }

/**
* Rendering image element (with resizing) into the canvas element
*/
MessageForm.prototype.renderImageToCanvas = function( img, canvas, options, doSquash ) {
    var iw = img.naturalWidth, ih = img.naturalHeight;
    var width = options.width, height = options.height;
    var ctx = canvas.getContext('2d');
    ctx.save();
    //this.transformCoordinate(canvas, width, height, options.orientation);
    canvas.width = width;
    canvas.height = height;
    var subsampled = this.detectSubsampling(img);
    if (subsampled) {
      iw /= 2;
      ih /= 2;
    }
    var d = 1024; // size of tiling canvas
    var tmpCanvas = document.createElement('canvas');
    tmpCanvas.width = tmpCanvas.height = d;
    var tmpCtx = tmpCanvas.getContext('2d');
    var vertSquashRatio = doSquash ? this.detectVerticalSquash(img, iw, ih) : 1;
    var dw = Math.ceil(d * width / iw);
    var dh = Math.ceil(d * height / ih / vertSquashRatio);
    var sy = 0;
    var dy = 0;
    while (sy < ih) {
      var sx = 0;
      var dx = 0;
      while (sx < iw) {
        tmpCtx.clearRect(0, 0, d, d);
        tmpCtx.drawImage(img, -sx, -sy);
        ctx.drawImage(tmpCanvas, 0, 0, d, d, dx, dy, dw, dh);
        sx += d;
        dx += dw;
      }
      sy += d;
      dy += dh;
    }
    ctx.restore();
    tmpCanvas = tmpCtx = null;
}

MessageForm.prototype.removeImage = function ( dataElementName, previewElementId, previewPlaceholderUrl ) {

    'use strict';

    var that = this,
        oldInput = document.getElementById( that.name + '-image-control' ),
        newInput = document.createElement( 'input' );

    //build a matching <input type="file"> control, since we can't arbitrarily clear it out 
    newInput.type = 'file'; 
    newInput.id = that.name + '-image-control'; 
    newInput.name = dataElementName;
    newInput.accept = 'image/*';

    //if it exists, remove old element and its 'onchange' event listener
    if ( oldInput ) {
        oldInput.parentNode.replaceChild( newInput, oldInput );
    } else {
        document.getElementById( that.name + '-image-container' ).appendChild( newInput );
    }

    $( '#' + that.name + '-image-control' ).addClass( 'form-image-control' );

    //add processImage 'onchange' callback to the new form control
    $( '#' + that.name + '-image-control' ).on( 'change', function ( e ) {
        $( '#' + previewElementId ).removeAttr( 'height' );
        that.processImage( e, dataElementName, previewElementId );
    }, false );
    
    //remove inline base64 image
    $( '#' + that.name + '-inline-image' ).remove();
    $( '#' + that.name + '-inline-orientation' ).remove();
    $( '#' + that.name + '-retain-image' ).val( '0' );

    //update the form UI to reflect the new state
    $( '#' + that.name + '-image-remove' ).hide();
    $( '#' + previewElementId ).attr( 'src', previewPlaceholderUrl ).removeAttr( 'height' );
    //$( '#' + that.name + '-preview' ).removeClass( 'has-image' );

    that.hasImage = false;
    that.updateFormStatus();

};
