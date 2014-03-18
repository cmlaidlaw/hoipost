EventForm = function( element, name, resourceFn, callbacks, timeout, values ) {
    
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
    this.hasText = false;
    this.hasStartDate = false;
    this.hasStartHours = false;
    this.hasStartMinutes = false;
    this.hasEndDate = false;
    this.hasEndHours = false;
    this.hasEndMinutes = false;

    this.startDatePicker = null;
    this.endDatePicker = null;

    this.init( values );

};

EventForm.prototype = MessageForm.prototype;

EventForm.prototype.init = function( values ) {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        t,//emplate
        l = window._H.localization,
        p,//roperties
        m,//essage
        e,//vent
        dateTime,
        nextOccurrence,
        html,
        $container = $( this.element );

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
           + '{{#establishmentObjectId}}'
           + '<input name="establishmentObjectId" type="hidden" value="{{establishmentObjectId}}" />'
           + '{{/establishmentObjectId}}'
           + '<input type="hidden" name="MAX_FILE_SIZE" value="{{uploadMaxSize}}" />'
           + '{{#hasImage}}'
           + '<input id="{{name}}-retain-image" name="retainImage" type="hidden" value="1" />'
           + '{{/hasImage}}'
           + '<div id="{{name}}-message" class="section-content-4-col">'
           + '<div id="{{name}}-image-container" class="form-image-control-container">'
           + '<button id="{{name}}-image-add" class="ui-button form-image-control-add-button" type="button">{{addImageLabel}}'
           + '<button id="{{name}}-image-remove" class="ui-button form-image-control-remove-button" type="button"{{#hasImage}} style="display:block;"{{/hasImage}}>{{removeImageLabel}}</button>'
           + '<input id="{{name}}-image-control" class="form-image-control" name="image" type="file" accept="image/*" />'
           + '</div>'
           + '<textarea id="{{name}}-text" class="form-textarea-4-col" name="text" placeholder="{{textPlaceholder}}" maxlength="{{textMaxLength}}">'
           + '{{#text}}{{text}}{{/text}}'
           + '</textarea>'
           + '</div>'
        //preview
           + '<div id="{{name}}-preview" class="section-content-4-col">'
           + '<div id="{{name}}-preview-object" class="{{#theme}}obj-theme-{{theme}}{{/theme}}'
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
           + '<span class="obj-countdown-starting">'
           + '{{countdownPlaceholder}}'
           + '</span>'
           + '<span class="obj-georelation">'
           + '{{georelationPlaceholder}}'
           + '</span>'
           + '</div>'
           + '</div>'
           + '</div>'
           + '<div class="clear"></div>'
           + '<div class="section-content-4-col">'
           + '<h3>{{dateTimesLocalNotice}}</h3>'
           + '<table><tbody>'
        //startDate
           + '<tr>'
           + '<td><label class="form-label-4-col" for="{{name}}-start-date">{{startDateLabel}}</label></td>'
           + '<td>'
           + '<input id="{{name}}-start-date" class="form-input-4-col" type="text" name="startDate"'
           + '{{#startDate}} value="{{startDate}}" {{/startDate}}placeholder="{{datePlaceholder}}" />'
           + '<span class="form-input-description"></span>'
           + '</td>'
           + '</tr>'
        //startTime
           + '<tr>'
           + '<td><label class="form-label-4-col" for="{{name}}-sart-hours">{{startTimeLabel}}</label></td>'
           + '<td>'
           + '<select id="{{name}}-start-hours" class="form-select-4-col form-time-select" name="startHours" placeholder="{{hoursPlaceholder}}">'
           + '<option value="00">00</option>'
           + '<option value="01">01</option>'
           + '<option value="02">02</option>'
           + '<option value="03">03</option>'
           + '<option value="04">04</option>'
           + '<option value="05">05</option>'
           + '<option value="06">06</option>'
           + '<option value="07">07</option>'
           + '<option value="08">08</option>'
           + '<option value="09">09</option>'
           + '<option value="10">10</option>'
           + '<option value="11">11</option>'
           + '<option value="12">12</option>'
           + '<option value="13">13</option>'
           + '<option value="14">14</option>'
           + '<option value="15">15</option>'
           + '<option value="16">16</option>'
           + '<option value="17">17</option>'
           + '<option value="18">18</option>'
           + '<option value="19">19</option>'
           + '<option value="20">20</option>'
           + '<option value="21">21</option>'
           + '<option value="22">22</option>'
           + '<option value="23">23</option>'
           + '</select>'
           + '<span class="form-time-separator">:</span>'
           + '<select id="{{name}}-start-minutes" class="form-select-4-col form-time-select" name="startMinutes" placeholder="{{minutesPlaceholder}}">'
           + '<option value="00" selected>00</option>'
           + '<option value="01">01</option>'
           + '<option value="02">02</option>'
           + '<option value="03">03</option>'
           + '<option value="04">04</option>'
           + '<option value="05">05</option>'
           + '<option value="06">06</option>'
           + '<option value="07">07</option>'
           + '<option value="08">08</option>'
           + '<option value="09">09</option>'
           + '<option value="10">10</option>'
           + '<option value="11">11</option>'
           + '<option value="12">12</option>'
           + '<option value="13">13</option>'
           + '<option value="14">14</option>'
           + '<option value="15">15</option>'
           + '<option value="16">16</option>'
           + '<option value="17">17</option>'
           + '<option value="18">18</option>'
           + '<option value="19">19</option>'
           + '<option value="20">20</option>'
           + '<option value="21">21</option>'
           + '<option value="22">22</option>'
           + '<option value="23">23</option>'
           + '<option value="24">24</option>'
           + '<option value="25">25</option>'
           + '<option value="26">26</option>'
           + '<option value="27">27</option>'
           + '<option value="28">28</option>'
           + '<option value="29">29</option>'
           + '<option value="30">30</option>'
           + '<option value="31">31</option>'
           + '<option value="32">32</option>'
           + '<option value="33">33</option>'
           + '<option value="34">34</option>'
           + '<option value="35">35</option>'
           + '<option value="36">36</option>'
           + '<option value="37">37</option>'
           + '<option value="38">38</option>'
           + '<option value="39">39</option>'
           + '<option value="40">40</option>'
           + '<option value="41">41</option>'
           + '<option value="42">42</option>'
           + '<option value="43">43</option>'
           + '<option value="44">44</option>'
           + '<option value="45">45</option>'
           + '<option value="46">46</option>'
           + '<option value="47">47</option>'
           + '<option value="48">48</option>'
           + '<option value="49">49</option>'
           + '<option value="50">50</option>'
           + '<option value="51">51</option>'
           + '<option value="52">52</option>'
           + '<option value="53">53</option>'
           + '<option value="54">54</option>'
           + '<option value="55">55</option>'
           + '<option value="56">56</option>'
           + '<option value="57">57</option>'
           + '<option value="58">58</option>'
           + '<option value="59">59</option>'
           + '</select>'
           + '<span class="form-input-description"></span>'
           + '</td>'
           + '</tr>'
           + '<tr>'
        //endDate
           + '<tr>'
           + '<td><label class="form-label-4-col" for="{{name}}-end-date">{{endDateLabel}}</label></td>'
           + '<td>'
           + '<input id="{{name}}-end-date" class="form-input-4-col" type="text" name="endDate"'
           + '{{#endDate}} value="{{endDate}}" {{/endDate}}placeholder="{{datePlaceholder}}" />'
           + '<span class="form-input-description"></span>'
           + '</td>'
           + '</tr>'
        //endTime
           + '<tr>'
           + '<td><label class="form-label-4-col" for="{{name}}-end-hours">{{endTimeLabel}}</label></td>'
           + '<td>'
           + '<select id="{{name}}-end-hours" class="form-select-4-col form-time-select" name="endHours" placeholder="{{hoursPlaceholder}}">'
           + '<option value="00">00</option>'
           + '<option value="01">01</option>'
           + '<option value="02">02</option>'
           + '<option value="03">03</option>'
           + '<option value="04">04</option>'
           + '<option value="05">05</option>'
           + '<option value="06">06</option>'
           + '<option value="07">07</option>'
           + '<option value="08">08</option>'
           + '<option value="09">09</option>'
           + '<option value="10">10</option>'
           + '<option value="11">11</option>'
           + '<option value="12" selected>12</option>'
           + '<option value="13">13</option>'
           + '<option value="14">14</option>'
           + '<option value="15">15</option>'
           + '<option value="16">16</option>'
           + '<option value="17">17</option>'
           + '<option value="18">18</option>'
           + '<option value="19">19</option>'
           + '<option value="20">20</option>'
           + '<option value="21">21</option>'
           + '<option value="22">22</option>'
           + '<option value="23">23</option>'
           + '</select>'
           + '<span class="form-time-separator">:</span>'
           + '<select id="{{name}}-end-minutes" class="form-select-4-col form-time-select" name="endMinutes" placeholder="{{minutesPlaceholder}}">'
           + '<option value="00" selected>00</option>'
           + '<option value="01">01</option>'
           + '<option value="02">02</option>'
           + '<option value="03">03</option>'
           + '<option value="04">04</option>'
           + '<option value="05">05</option>'
           + '<option value="06">06</option>'
           + '<option value="07">07</option>'
           + '<option value="08">08</option>'
           + '<option value="09">09</option>'
           + '<option value="10">10</option>'
           + '<option value="11">11</option>'
           + '<option value="12">12</option>'
           + '<option value="13">13</option>'
           + '<option value="14">14</option>'
           + '<option value="15">15</option>'
           + '<option value="16">16</option>'
           + '<option value="17">17</option>'
           + '<option value="18">18</option>'
           + '<option value="19">19</option>'
           + '<option value="20">20</option>'
           + '<option value="21">21</option>'
           + '<option value="22">22</option>'
           + '<option value="23">23</option>'
           + '<option value="24">24</option>'
           + '<option value="25">25</option>'
           + '<option value="26">26</option>'
           + '<option value="27">27</option>'
           + '<option value="28">28</option>'
           + '<option value="29">29</option>'
           + '<option value="30">30</option>'
           + '<option value="31">31</option>'
           + '<option value="32">32</option>'
           + '<option value="33">33</option>'
           + '<option value="34">34</option>'
           + '<option value="35">35</option>'
           + '<option value="36">36</option>'
           + '<option value="37">37</option>'
           + '<option value="38">38</option>'
           + '<option value="39">39</option>'
           + '<option value="40">40</option>'
           + '<option value="41">41</option>'
           + '<option value="42">42</option>'
           + '<option value="43">43</option>'
           + '<option value="44">44</option>'
           + '<option value="45">45</option>'
           + '<option value="46">46</option>'
           + '<option value="47">47</option>'
           + '<option value="48">48</option>'
           + '<option value="49">49</option>'
           + '<option value="50">50</option>'
           + '<option value="51">51</option>'
           + '<option value="52">52</option>'
           + '<option value="53">53</option>'
           + '<option value="54">54</option>'
           + '<option value="55">55</option>'
           + '<option value="56">56</option>'
           + '<option value="57">57</option>'
           + '<option value="58">58</option>'
           + '<option value="59">59</option>'
           + '</select>'
           + '<span class="form-input-description"></span>'
           + '</td>'
           + '</tr>'
           + '<tr>'
        //repeatsWeekly
           + '<td><label class="form-label-4-col" for="{{name}}-repeats-weekly">{{repeatsWeeklyLabel}}</label></td>'
           + '<td>'
           + '<fieldset id="{{name}}-repeats-weekly" class="form-fieldset">'
           + '<input id="{{name}}-repeats-weekly-true" class="form-radio" name="repeatsWeekly" type="radio" value="1"{{#repeatsWeekly}} checked {{/repeatsWeekly}}/>'
           + '<label class="form-fieldset-label" for="{{name}}-repeats-weekly-true">{{repeatsWeeklyTrueLabel}}</label><br />'
           + '<input id="{{name}}-repeats-weekly-false" class="form-radio" name="repeatsWeekly" type="radio" value="0"{{^repeatsWeekly}} checked {{/repeatsWeekly}}/>'
           + '<label class="form-fieldset-label" for="{{name}}-repeats-weekly-false">{{repeatsWeeklyFalseLabel}}</label>'
           + '</fieldset>'
           + '<span class="form-input-description"></span>'
           + '</td>'
           + '</tr>'
        //nextOccurrence
           + '<tr id="{{name}}-next-occurrence-section"{{^repeatsWeekly}} style="display:none;"{{/repeatsWeekly}}>'
           + '<td><label class="form-label-4-col" for="{{name}}-next-occurrence">{{nextOccurrenceLabel}}</label></td>'
           + '<td><input id="{{name}}-next-occurrence" class="form-input-4-col" type="text"'
           + '{{#nextOccurrence}} value="{{nextOccurrence}}"{{/nextOccurrence}}'
           + ' placeholder="{{nextOccurrencePlaceholder}}" readonly />'
           + '</td>'
           + '<span class="form-input-description"></span>'
           + '</tr>'
           + '</tbody></table>'
           + '<span id="{{name}}-message-content-validation-error" class="form-validation-error" style="display:none;">{{messageContentValidationError}}</span>'
           + '<span id="{{name}}-date-order-validation-error" class="form-validation-error" style="display:none;">{{dateOrderValidationError}}</span>'
           + '<button id="{{name}}-submit" class="ui-button" type="submit">'
           + '{{#eventObjectId}}{{updateButtonLabel}}{{/eventObjectId}}'
           + '{{^eventObjectId}}{{createButtonLabel}}{{/eventObjectId}}'
           + '</button>'
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
            startDateLabel: l['EVENT_START_DATE_LABEL'],
            startTimeLabel: l['EVENT_START_HOURS_LABEL'],
            endDateLabel: l['EVENT_END_DATE_LABEL'],
            endTimeLabel: l['EVENT_END_TIME_LABEL'],
            datePlaceholder: l['EVENT_DATE_PLACEHOLDER'],
            hoursPlaceholder: l['EVENT_HOURS_PLACEHOLDER'],
            minutesPlaceholder: l['EVENT_MINUTES_PLACEHOLDER'],
            repeatsWeeklyLabel: l['EVENT_REPEATS_WEEKLY_LABEL'],
            repeatsWeeklyTrueLabel: l['EVENT_REPEATS_WEEKLY_TRUE_LABEL'],
            repeatsWeeklyFalseLabel: l['EVENT_REPEATS_WEEKLY_FALSE_LABEL'],
            nextOccurrenceLabel: l['EVENT_NEXT_OCCURRENCE_LABEL'],
            nextOccurrencePlaceholder: l['EVENT_NEXT_OCCURRENCE_PLACEHOLDER'],
            countdownPlaceholder: l['EVENT_COUNTDOWN_PLACEHOLDER'],
            georelationPlaceholder: l['EVENT_GEORELATION_PLACEHOLDER'],
            messageContentValidationError: l['MESSAGE_CONTENT_VALIDATION_ERROR'],
            dateOrderValidationError: l['EVENT_DATE_ORDER_VALIDATION_ERROR'],
            updateButtonLabel: l['EVENT_UPDATE_SUBMIT_LABEL'],
            createButtonLabel: l['EVENT_CREATE_SUBMIT_LABEL']
        };

        if ( values.hasOwnProperty( 'establishmentObjectId' )
             && values.establishmentObjectId !== null ) {
            p.establishmentObjectId = values.establishmentObjectId;
        }

        if ( values.hasOwnProperty( 'id' )
             && values.id !== null ) {
            p.eventObjectId = values.id;
        }

        if ( values.hasOwnProperty( 'theme' )
             && values.theme !== null ) {
            p.theme = values.theme;
        }

        if ( values.hasOwnProperty( 'message' )
             && values.message !== null ) {

            m = values.message;

            p.text = m.text;
            if ( m.image !== null ) {
                p.imageFullUrl = m.image.fullUrl;
                p.imageHeight = Math.floor( window._H.previewImageWidth / m.image.fullAspectRatio );
                p.hasImage = true;
            }

        }

        if ( values.hasOwnProperty( 'event' )
             && values.event !== null ) {

            e = values.event;

            if ( e.hasOwnProperty( 'startDateTime' )
                 && e.startDateTime !== null ) {

                dateTime = that.dateTimeToComponents( e.startDateTime );
                p.startDate = dateTime.date;
                p.startHours = dateTime.hours;
                p.startMinutes = dateTime.minutes;

            }

            if ( e.hasOwnProperty( 'endDateTime' )
                 && e.endDateTime !== null ) {

                dateTime = that.dateTimeToComponents( e.endDateTime );
                p.endDate = dateTime.date;
                p.endHours = dateTime.hours;
                p.endMinutes = dateTime.minutes;

            }

            if ( e.hasOwnProperty( 'repeatsWeekly' )
                 && e.repeatsWeekly !== null ) {

                p.repeatsWeekly = e.repeatsWeekly;
                nextOccurrence = that.dateTimeToComponents( e.startDateTime )
                p.nextOccurrence = nextOccurrence.date + ' ' + nextOccurrence.hours + ':' + nextOccurrence.minutes;

            }

        }

        html = Mustache.render( t, p );

        $container.replaceContent( html );

        //after the <select> elements have been inserted into the DOM, set them
        //to the correct values (instead of doing it in Mustache like the others)
        document.getElementById( that.name + '-start-hours' ).value = p.startHours;
        document.getElementById( that.name + '-start-minutes' ).value = p.startMinutes;
        document.getElementById( that.name + '-end-hours' ).value = p.endHours;
        document.getElementById( that.name + '-end-minutes' ).value = p.endMinutes;

        nextItem();

    } );

    //this item: set default form values and event listeners
    Q.addItem( function( nextItem ) {

        that.updateStatus( 'incomplete' );

        //set up initial event listener for the picture upload control
        $( '#' + that.name + '-image-control' ).on( 'change', function( e ) {
            that.processImage( e, 'image', that.name + '-preview-image' );
        } );

        $( '#' + that.name + '-image-remove' ).button( window._H.has.touch, function( button ) {
            //if the remove button is activated, remove the hint to keep the existing picture
            that.removeImage( 'image', that.name + '-preview-image', '' );
        }, false );

        $( '#' + that.name + '-text' ).on( 'keyup', function() {
            that.processText();
        } );

        $( '#' + that.name + '-start-hours' ).on( 'change', function() {
            that.processHours( true );
        } );

        $( '#' + that.name + '-start-minutes' ).on( 'change', function() {
            that.processMinutes( true );
        } );

        $( '#' + that.name + '-end-hours' ).on( 'change', function() {
            that.processHours();
        } );

        $( '#' + that.name + '-end-minutes' ).on( 'change', function() {
            that.processMinutes();
        } );

        $( '#' + that.name + '-recurring-true' ).on( 'click', function( e ) {
            $( '#' + that.name + '-next-occurrence-section' ).css( 'display', 'table-row' );
        } );

        $( '#' + that.name + '-recurring-false' ).on( 'click', function( e ) {
            $( '#' + that.name + '-next-occurrence-section' ).css( 'display', 'none' );
        } );

        $( '#' + that.name + '-submit' ).button( window._H.has.touch, function( button ) {
            document.getElementById( that.name + '-form' ).submit();
        }, false );

        that.startDatePicker = new Pikaday(
            {
                field: document.getElementById( that.name + '-start-date'),
                onSelect: function() { that.processDate( true ); }
            }
        );

        that.endDatePicker = new Pikaday(
            {
                field: document.getElementById( that.name + '-end-date' ),
                onSelect: function() { that.processDate( false ); }
            }
        );

        //prevent submission of empty forms (need to do this on the form element as well as the submit button!)
        $( '#' + that.name + '-form' ).on( 'submit', function( e ) {
            //make sure the form is valid to submit
            if ( that.status !== 'complete' ) {
                $.prototype.halt.call( null, e );
                return false;
            }
        } );

        nextItem();

    } );

    Q.addItem( function( nextItem ) {

        //pre-process any provided values
        that.processText();
        that.processDate( true );
        that.processHours( true );
        that.processMinutes( true );
        that.processDate( false );
        that.processHours( false );
        that.processMinutes( false );
        that.updateFormStatus();

    } );

    Q.execute();

};

EventForm.prototype.updateFormStatus = function() {

    'use strict';

    if ( this.hasText || this.hasImage ) {
        $( '#' + this.name + '-message-content-validation-error' ).hide();
    } else {
        $( '#' + this.name + '-message-content-validation-error' ).show();
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
        $( '#' + this.name + '-date-order-validation-error' ).hide();
    } else {
        $( '#' + this.name + '-date-order-validation-error' ).show();
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

};

EventForm.prototype.dateTimeToComponents = function( dateTime ) {

    'use strict';

    var thisDate,
        obj = {};

    thisDate = HPObject.prototype.parseDateTime.call( null, dateTime );

    switch ( thisDate.getDay() ) {
        case 0:
            obj.date = 'Sun ';
            break;
        case 1:
            obj.date = 'Mon ';
            break;
        case 2:
            obj.date = 'Tue ';
            break;
        case 3:
            obj.date = 'Wed ';
            break;
        case 4:
            obj.date = 'Thu ';
            break;
        case 5:
            obj.date = 'Fri ';
            break;
        case 6:
            obj.date = 'Sat ';
            break;
    }

    switch ( thisDate.getMonth() ) {
        case 0:
            obj.date += 'Jan ';
            break;
        case 1:
            obj.date += 'Feb ';
            break;
        case 2:
            obj.date += 'Mar ';
            break;
        case 3:
            obj.date += 'Apr ';
            break;
        case 4:
            obj.date += 'May ';
            break;
        case 5:
            obj.date += 'Jun ';
            break;
        case 6:
            obj.date += 'Jul ';
            break;
        case 7:
            obj.date += 'Aug ';
            break;
        case 8:
            obj.date += 'Sep ';
            break;
        case 9:
            obj.date += 'Oct ';
            break;
        case 10:
            obj.date += 'Nov ';
            break;
        case 11:
            obj.date += 'Dec ';
            break;
    }

    obj.date += thisDate.getDate() + ' ' + thisDate.getFullYear();

    obj.hours = thisDate.getHours();
    if ( obj.hours < 10 ) {
        obj.hours = '0' + obj.hours;
    }

    obj.minutes = thisDate.getMinutes();
    if ( obj.minutes < 10 ) {
        obj.minutes = '0' + obj.minutes;
    }

    return obj;

};

EventForm.prototype.validateDate = function( dateString ) {

    'use strict';

    var parts = dateString.split( ' ' ),
        day = parts[0],
        month = parts[1],
        date = parts[2],
        year = parts[3],
        hasDay = false,
        hasMonth = false,
        hasDate = false,
        hasYear = false,
        validDate = false;

    //thu may 02 2013
    
    if ( day === 'Sun'
         || day === 'Mon'
         || day === 'Tue'
         || day === 'Wed'
         || day === 'Thu'
         || day === 'Fri'
         || day === 'Sat' ) {
        hasDay = true;
    }

    if ( month === 'Jan'
         || month === 'Feb'
         || month === 'Mar'
         || month === 'Apr'
         || month === 'May'
         || month === 'Jun'
         || month === 'Jul'
         || month === 'Aug'
         || month === 'Sep'
         || month === 'Oct'
         || month === 'Nov'
         || month === 'Dec' ) {
        hasMonth = true;
    }

    if ( date > 0 && date < 32 ) {
        hasDate = true;
    }

    if ( year > 2012 ) {
        hasYear = true;
    }

    if ( hasDay === true
         && hasMonth === true
         && hasDate === true
         && hasYear === true ) {

        validDate = parts[0] + ', '
                  + parts[2] + ' '
                  + parts[1] + ' '
                  + parts[3];

    }

    return validDate;

};

EventForm.prototype.processDate = function( isStart ) {

    'use strict';

    var that = this,
        value = ( isStart ) ? document.getElementById( this.name + '-start-date' ).value
                            : document.getElementById( this.name + '-end-date' ).value,
        date = this.validateDate( value ),
        startDate,
        split;

    if ( isStart && date !== false ) {
        this.hasStartDate = true;
        //if a new start date happens after the existing end date,
        //advance the end date to be equal to the start date
        if ( new Date( this.startDatePicker.getDate() ) > new Date( this.endDatePicker.getDate() ) ) {
            startDate = $( '#' + this.name + '-start-date' ).val();
            $( '#' + this.name + '-end-date' ).val( startDate );
            split = startDate.split( ' ' );
            this.endDatePicker.setDate(
                new Date(
                    split[0] + ' '
                    + split[2] + ' '
                    + split[1] + ' '
                    + split[3]
                )
            );
        }
    } else if ( isStart ) {
        this.hasStartDate = false;
        $( '#' + this.name + '-end-date' ).val( '' ).attr( 'readonly', 'readonly' );
    } else if ( !isStart && date !== false ) {
        this.hasEndDate = true;
    } else {
        this.hasEndDate = false;
    }

    this.updateFormStatus();

};

EventForm.prototype.validateHours = function( hours ) {

    'use strict';

    var validHours = false;

    if ( hours.match( /[0-9]|[01]\d|2[0-3]/ ) ) {
        validHours = hours;
    }

    return validHours;

};

EventForm.prototype.processHours = function( isStart ) {

    'use strict';

    var value = ( isStart ) ? document.getElementById( this.name + '-start-hours' ).value
                            : document.getElementById( this.name + '-end-hours' ).value,
        hours = this.validateHours( value );

    if ( isStart && hours !== false ) {
        this.hasStartHours = true;
    } else if ( isStart ) {
        this.hasStartHours = false;
    } else if ( !isStart && hours !== false ) {
        this.hasEndHours = true;
    } else {
        this.hasEndHours = false;
    }

    this.updateFormStatus();

};

EventForm.prototype.validateMinutes = function( minutes ) {

    'use strict';

    var validMinutes = false;

    if ( minutes.match( /[0-5]\d/ ) ) {
        validMinutes = minutes;
    }

    return validMinutes;

};

EventForm.prototype.processMinutes = function( isStart ) {

    'use strict';

    var value = ( isStart ) ? document.getElementById( this.name + '-start-minutes' ).value
                            : document.getElementById( this.name + '-end-minutes' ).value,
        minutes = this.validateMinutes( value );

    if ( isStart && minutes !== false ) {
        this.hasStartMinutes = true;
    } else if ( isStart ) {
        this.hasStartMinutes = false;
    } else if ( !isStart && minutes !== false ) {
        this.hasEndMinutes = true;
    } else {
        this.hasEndMinutes = false;
    }

    this.updateFormStatus();

};

EventForm.prototype.calculateNextOccurrence = function() {

    'use strict';

    var startDate = document.getElementById( this.name + '-start-date').value,
        startHours = document.getElementById( this.name + '-start-hours').value,
        startMinutes = document.getElementById( this.name + '-start-minutes').value,
        dateParts,
        dateString = '',
        startDateTime,
        currentDateTime,
        nextOccurrence,
        nextStartDateTime,
        i;

    dateParts = startDate.split( ' ' );
    dateString = dateParts[0] + ', '
               + dateParts[2] + ' '
               + dateParts[1] + ' '
               + dateParts[3] + ' '
               + startHours + ':'
               + startMinutes + ':00';
               //+ ' UTC';
    startDateTime = new Date( dateString );
    currentDateTime = new Date();

    if ( startDateTime > currentDateTime ) {
        nextOccurrence = startDateTime;
    } else {

        nextStartDateTime = new Date( dateString );

        i = 1000;
        while ( i > 0 ) {
            nextStartDateTime.setDate( nextStartDateTime.getDate() + 7 );
            if ( nextStartDateTime > currentDateTime ) {
                nextOccurrence = nextStartDateTime;
                break;
            }
            i++;
        }

    }

    $( '#' + this.name + '-next-occurrence').val(
        nextOccurrence.toString().substring( 0, 21 )
    );

};

EventForm.prototype.checkStartEndOrder = function() {

    'use strict';

    var startDateTime,
        endDateTime;

    startDateTime = new Date( this.startDatePicker.getDate() );
    startDateTime.setHours( document.getElementById( this.name + '-start-hours' ).value );
    startDateTime.setMinutes( document.getElementById( this.name + '-start-minutes' ).value );
    
    endDateTime = new Date( this.endDatePicker.getDate() );
    endDateTime.setHours( document.getElementById( this.name + '-end-hours' ).value );
    endDateTime.setMinutes( document.getElementById( this.name + '-end-minutes' ).value );

    if ( startDateTime < endDateTime ) {
        this.hasGoodStartEndOrder = true;
    } else {
        this.hasGoodStartEndOrder = false;
    }

};