EventFormatter = function( element, name, outputElement, outputName, establishmentId, maxHappenings, happenings ) {
    
    this.element = element;
    this.name = name;
    this.outputElement = outputElement;
    this.outputName = outputName;
    this.status = false;

    this.createReplaceSpecified = false;
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

    this.establishmentId = establishmentId;
    this.maxHappenings = maxHappenings;
    this.happenings = happenings;

    this.init();

};

EventFormatter.prototype = MessageForm.prototype;

EventFormatter.prototype.init = function() {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        t,//emplate
        l = window._H.localization,
        p,//roperties,
        h,//appenings
        happeningList,
        m,//essage
        e,//vent
        dateTime,
        nextOccurrence,
        html,
        $container = $( this.element ),
        values = {};

    //this item: generates form HTML and inserts it into the DOM
    Q.addItem( function( nextItem ) {

         t = '<div id="{{name}}-form">'
           + '<input id="{{name}}-establishment-id" type="hidden" value="{{establishmentId}}" />'
           + '<input id="{{name}}-replace-id" type="hidden" value="0" />'
         //happenings (replace/create new)
           + '<span id="{{name}}-create-replace-validation-error" class="form-validation-error" style="display:none;"><span class="form-validation-error-icon">&#9888;</span>{{happeningCreateReplaceValidationError}}</span>'
           + '{{#createAllowed}}'
           + '<button id="{{name}}-happening-create-button" class="ui-button">'
           + '{{createButtonLabel}}'
           + '</button>'
           + '{{/createAllowed}}'
           + '{{#replaceAllowed}}'
           + '<button id="{{name}}-happening-replace-button" class="ui-button">'
           + '{{replaceButtonLabel}}'
           + '</button>'
           + '{{/replaceAllowed}}'
           + '<div class="clear"></div>'
           + '<div id="{{name}}-happenings" class="layout">'
           + '<ul id="{{name}}-happening-list">{{{happeningList}}}</ul>'
           + '</div>'
           + '<span id="{{name}}-message-content-validation-error" class="form-validation-error" style="display:none;"><span class="form-validation-error-icon">&#9888;</span>{{messageContentValidationError}}</span>'
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
           + '<select id="{{name}}-start-hours" class="form-select-4-col form-time-select" name="startHours">'
           + '<option value="placeholder" disabled="disabled" selected="selected">{{hoursPlaceholder}}</option>'
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
           + '<select id="{{name}}-start-minutes" class="form-select-4-col form-time-select" name="startMinutes">'
           + '<option value="placeholder" disabled="disabled" selected="selected">{{minutesPlaceholder}}</option>'
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
           + '<td colspan="2">'
           + '<span id="{{name}}-date-order-validation-error" class="form-validation-error" style="display:none;"><span class="form-validation-error-icon">&#9888;</span>{{dateOrderValidationError}}</span>'
           + '</td>'
           + '</tr>'
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
           + '<select id="{{name}}-end-hours" class="form-select-4-col form-time-select" name="endHours">'
           + '<option value="placeholder" disabled="disabled" selected="selected">{{hoursPlaceholder}}</option>'
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
           + '<select id="{{name}}-end-minutes" class="form-select-4-col form-time-select" name="endMinutes">'
           + '<option value="placeholder" disabled="disabled" selected="selected">{{minutesPlaceholder}}</option>'
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
           + '</div>'
           + '<div class="clear"></div>'
           + '</div>';

        p = {
            name: that.name,
            establishmentId: that.establishmentId,
            happeningCreateReplaceValidationError: l['HAPPENING_CREATE_REPLACE_VALIDATION_ERROR'],
            createButtonLabel: l['HAPPENING_CREATE_BUTTON_LABEL'],
            replaceButtonLabel: l['HAPPENING_REPLACE_BUTTON_LABEL'],
            replaceSectionTitle: l['HAPPENING_REPLACE_SECTION_TITLE'],
            messageContentValidationError: l['MESSAGE_CONTENT_VALIDATION_ERROR'],
            uploadMaxSize: l['UPLOAD_MAX_SIZE'],
            addImageLabel: l['MESSAGE_ADD_IMAGE_LABEL'],
            removeImageLabel: l['MESSAGE_REMOVE_IMAGE_LABEL'],
            textPlaceholder: l['EVENT_DESCRIPTION_PLACEHOLDER'],
            textMaxLength: l['MESSAGE_TEXT_MAX_LENGTH'],
            startDateLabel: l['EVENT_START_DATE_LABEL'],
            startTimeLabel: l['EVENT_START_HOURS_LABEL'],
            dateOrderValidationError: l['EVENT_DATE_ORDER_VALIDATION_ERROR'],
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
            theme: 1
        };

        p.happeningList = '';
        p.happeningCount = 0;

        for ( h in that.happenings ) {
            p.happeningList += '<li class="' + that.name + '-happening-list-item">';
            p.happeningList += HPObject.prototype.renderObject.call(
                null,
                that.happenings[h],
                false,
                false,
                false,
                150,
                false,
                'happening'
            );
            p.happeningList += '<div class="' + that.name + '-happening-replace-item-button-container">';
            p.happeningList += '<button class="ui-button ui-button-green ' + that.name + '-happening-replace-item-button">';
            p.happeningList += l['HAPPENING_REPLACE_ITEM_BUTTON_LABEL'];
            p.happeningList += ' </button>';
            p.happeningList += '</div>';
            p.happeningList += '</li>';
            p.happeningCount++;
        }

        p.happeningList += '<div class="clear"></div>';

        p.createAllowed = p.happeningCount < that.maxHappenings ? true : false;
        p.replaceAllowed = p.happeningCount > 0 ? true : false;

        p.happeningCount = l['HAPPENING_CURRENT_COUNT_LABEL_PREFIX']
                         + p.happeningCount + '/' + that.maxHappenings
                         + l['HAPPENING_CURRENT_COUNT_LABEL_SUFFIX'];


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

        $( '#' + that.name + '-happening-create-button' ).button( window._H.has.touch, function( button ) {
            $( '#' + that.name + '-replace-id' ).val( '-1' );
            $( '#' + that.name + '-happenings' ).removeClass( 'active' );
            $( button ).addClass( 'ui-button-green' );
            $( '#' + that.name + '-happening-replace-button' ).removeClass( 'selected' ).removeClass( 'ui-button-green' );
            that.updateFormStatus();
        } );

        $( '#' + that.name + '-happening-replace-button' ).button( window._H.has.touch, function( button ) {
            var $list = $( '#' + that.name + '-happenings' );
            if ( $list.hasClass( 'active' ) ) {
                $( button ).removeClass( 'selected' );
                $list.removeClass( 'active' );
            } else {
                $( button ).addClass( 'selected' );
                $list.addClass( 'active' );
            }
        } );

        $( '.' + that.name + '-happening-replace-item-button' ).button( window._H.has.touch, function( button ) {
            var id = $( button ).parent().siblings( '.happening' ).attr( 'id' ).substring( 4 );
            $( '#' + that.name + '-replace-id' ).val( id );
            $( '#' + that.name + '-happenings' ).removeClass( 'active' );
            $( '#' + that.name + '-happening-replace-button' ).removeClass( 'selected' ).addClass( 'ui-button-green' );
            $( '#' + that.name + '-happening-create-button' ).removeClass( 'ui-button-green' );
            that.updateFormStatus();
        } );

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

        //update the form status on blur in case someone selects and
        //deletes the start or end date text instead of using the
        //pikaday widget
        $( '#' + that.name + '-start-date' ).on( 'blur', function() {
            that.updateFormStatus();
        } );

        $( '#' + that.name + '-start-date' ).on( 'blur', function() {
            that.updateFormStatus();
        } );

        $( '#' + that.name + '-start-hours' ).on( 'change', function() {
            //ugly, hacky way of auto-setting the minutes to '00' when you select a value for hours
            var val = $( '#' + that.name + '-start-minutes' ).val();
            if ( val === 'placeholder' || val === null ) {
                var elements = $( '#' + that.name + '-start-minutes' ).children().elements;
                $( elements[59] ).attr( 'selected', 'selected' );                
                $( elements[60] ).removeAttr( 'selected' );
                that.processMinutes( true );
            }
            that.processHours( true );
        } );

        $( '#' + that.name + '-start-minutes' ).on( 'change', function() {
            that.processMinutes( true );
        } );

        $( '#' + that.name + '-end-hours' ).on( 'change', function() {
            //ugly, hacky way of auto-setting the minutes to '00' when you select a value for hours
            var val = $( '#' + that.name + '-end-minutes' ).val();
            if ( val === 'placeholder' || val === null ) {
                var elements = $( '#' + that.name + '-end-minutes' ).children().elements;
                $( elements[59] ).attr( 'selected', 'selected' );
                $( elements[60] ).removeAttr( 'selected' );
                that.processMinutes();
            }
            that.processHours();
        } );

        $( '#' + that.name + '-end-minutes' ).on( 'change', function() {
            that.processMinutes();
        } );

        $( '#' + that.name + '-repeats-weekly-true' ).on( 'click', function( e ) {
            that.updateFormStatus();
        } );

        $( '#' + that.name + '-repeats-weekly-false' ).on( 'click', function( e ) {
            that.updateFormStatus();
        } );

        $( '#' + that.name + '-format' ).button( window._H.has.touch, function( button ) {
            that.format();
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

        nextItem();

    } );

    Q.addItem( function( nextItem ) {

        /**
        *
        *  Base64 encode / decode
        *  http://www.webtoolkit.info/
        *
        **/
        window._H.Base64 = {

            // private property
            _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

            // public method for encoding
            encode : function (input) {
                var output = "";
                var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
                var i = 0;

                input = window._H.Base64._utf8_encode(input);

                while (i < input.length) {

                    chr1 = input.charCodeAt(i++);
                    chr2 = input.charCodeAt(i++);
                    chr3 = input.charCodeAt(i++);

                    enc1 = chr1 >> 2;
                    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                    enc4 = chr3 & 63;

                    if (isNaN(chr2)) {
                        enc3 = enc4 = 64;
                    } else if (isNaN(chr3)) {
                        enc4 = 64;
                    }

                    output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

                }

                return output;
            },

            // public method for decoding
            decode : function (input) {
                var output = "";
                var chr1, chr2, chr3;
                var enc1, enc2, enc3, enc4;
                var i = 0;

                input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

                while (i < input.length) {

                    enc1 = this._keyStr.indexOf(input.charAt(i++));
                    enc2 = this._keyStr.indexOf(input.charAt(i++));
                    enc3 = this._keyStr.indexOf(input.charAt(i++));
                    enc4 = this._keyStr.indexOf(input.charAt(i++));

                    chr1 = (enc1 << 2) | (enc2 >> 4);
                    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                    chr3 = ((enc3 & 3) << 6) | enc4;

                    output = output + String.fromCharCode(chr1);

                    if (enc3 != 64) {
                        output = output + String.fromCharCode(chr2);
                    }
                    if (enc4 != 64) {
                        output = output + String.fromCharCode(chr3);
                    }

                }

                output = window._H.Base64._utf8_decode(output);

                return output;

            },

            // private method for UTF-8 encoding
            _utf8_encode : function (string) {
                string = string.replace(/\r\n/g,"\n");
                var utftext = "";

                for (var n = 0; n < string.length; n++) {

                    var c = string.charCodeAt(n);

                    if (c < 128) {
                        utftext += String.fromCharCode(c);
                    }
                    else if((c > 127) && (c < 2048)) {
                        utftext += String.fromCharCode((c >> 6) | 192);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }
                    else {
                        utftext += String.fromCharCode((c >> 12) | 224);
                        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }

                }

                return utftext;
            },

            // private method for UTF-8 decoding
            _utf8_decode : function (utftext) {
                var string = "";
                var i = 0;
                var c = c1 = c2 = 0;

                while ( i < utftext.length ) {

                    c = utftext.charCodeAt(i);

                    if (c < 128) {
                        string += String.fromCharCode(c);
                        i++;
                    }
                    else if((c > 191) && (c < 224)) {
                        c2 = utftext.charCodeAt(i+1);
                        string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                        i += 2;
                    }
                    else {
                        c2 = utftext.charCodeAt(i+1);
                        c3 = utftext.charCodeAt(i+2);
                        string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                        i += 3;
                    }

                }

                return string;
            }

        }

    } );

    Q.execute();

};

EventFormatter.prototype.enableSubmit = function () {

    'use strict';

    var $button = $('#' + this.name + '-format');

    if ( $button.hasClass( 'disabled' ) ) {
        $button.removeClass( 'disabled' ).removeAttr( 'disabled' );
    }

};

EventFormatter.prototype.disableSubmit = function () {

    'use strict';

    var $button = $('#' + this.name + '-format');

    if ( !$button.hasClass( 'disabled' ) ) {
        $button.addClass( 'disabled' ).attr( 'disabled', 'disabled' );
    }

};


EventFormatter.prototype.format = function() {

    'use strict';

    var repeatsWeekly = $( '#' + this.name + '-repeats-weekly-true' ).attr( 'checked' ),
        output = {}, establishmentId, mailtoUrl;

    if ( this.status === 'complete' ) {

        output.establishmentId = $( '#' + this.name + '-establishment-id' ).val();
        output.replaceObjectId = $( '#' + this.name + '-replace-id' ).val();
        output.text = $( '#' + this.name + '-text' ).val();
        output.startDate = $( '#' + this.name + '-start-date' ).val();
        output.startHours = $( '#' + this.name + '-start-hours' ).val();
        output.startMinutes = $( '#' + this.name + '-start-minutes' ).val();
        output.endDate = $( '#' + this.name + '-end-date' ).val();
        output.endHours = $( '#' + this.name + '-end-hours' ).val();
        output.endMinutes = $( '#' + this.name + '-end-minutes' ).val();

        if ( repeatsWeekly === true ) {
            output.repeatsWeekly = true;
        } else {
            output.repeatsWeekly = false;
        }

        establishmentId = output.establishmentId;

        output = JSON.stringify( output );

        if ( this.hasImage ) {
            output.imageFilename = this.imageFilename;
        }

        output = window._H.Base64.encode( output );

        if ( this.hasImage ) {
            output += '\n\n***\n\nDON\'T FORGET TO ATTACH THIS FILE:\n\n"' + this.imageFilename + '"\n\n***';
        }

        $( this.outputElement ).replaceContent( output );

        mailtoUrl = 'mailto:happenings@hoipost.com?subject='
                  + encodeURIComponent( establishmentId )
                  + '&body='
                  + encodeURIComponent( output );

        $( '#formatter-output-email-button' ).removeClass( 'disabled' ).removeAttr( 'disabled' ).attr( 'href', mailtoUrl );

        $( '#formatter-success' ).show();

        window.scrollTo( 0, 0 );

    }

};

EventFormatter.prototype.resetFormatted = function() {

    'use strict';

    var $button = $( '#formatter-output-email-button' );

    $( this.outputElement ).replaceContent( '' );

    $( '#formatter-success' ).hide();

    $button.attr( 'href', '#' );

    if ( !$button.hasClass( 'disabled' ) ) {
        $button.addClass( 'disabled' ).attr( 'disabled', 'disabled' );
    }

}

EventFormatter.prototype.updateFormStatus = function() {

    'use strict';

    var replaceId = $( '#' + this.name + '-replace-id' ).val();

    if ( replaceId.length === 20 || replaceId === '-1' ) {
        this.createReplaceSpecified = true;
        $( '#' + this.name + '-create-replace-validation-error' ).hide();
    } else {
        $( '#' + this.name + '-create-replace-validation-error' ).show();
    }

    if ( this.hasText || this.hasImage ) {
        $( '#' + this.name + '-message-content-validation-error' ).hide();
    } else {
        $( '#' + this.name + '-message-content-validation-error' ).show();
    }

    if ( this.validateDate( document.getElementById( this.name + '-start-date' ).value ) === false ) {
        this.hasStartDate = false;
    }

    if ( this.validateDate( document.getElementById( this.name + '-end-date' ).value ) === false ) {
        this.hasEndDate = false;
    }

    if ( this.hasStartDate
         && this.hasStartHours
         && this.hasStartMinutes
         && this.hasEndDate
         && this.hasEndHours
         && this.hasEndMinutes ) {
        this.checkStartEndOrder();
    } else {
        this.hasGoodStartEndOrder = false;
    }

    if ( this.hasGoodStartEndOrder ) {

        $( '#' + this.name + '-date-order-validation-error' ).hide();

        if ( $( '#' + this.name + '-repeats-weekly-true' ).attr( 'checked' ) === true ) {
            this.calculateNextOccurrence();
            $( '#' + this.name + '-next-occurrence-section' ).css( 'display', 'table-row' );
        } else {
            $( '#' + this.name + '-next-occurrence-section' ).css( 'display', 'none' );
        }

    } else {
        $( '#' + this.name + '-date-order-validation-error' ).show();
        $( '#' + this.name + '-next-occurrence-section' ).css( 'display', 'none' );
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

        this.format();        

    } else {

        this.updateStatus( 'incomplete' );

        this.resetFormatted();

    }

};

EventFormatter.prototype.dateTimeToComponents = function( dateTime ) {

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

EventFormatter.prototype.validateDate = function( dateString ) {

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

EventFormatter.prototype.processDate = function( isStart ) {

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

EventFormatter.prototype.validateHours = function( hours ) {

    'use strict';

    var validHours = false;

    if ( hours.match( /[0-9]|[01]\d|2[0-3]/ ) ) {
        validHours = hours;
    }

    return validHours;

};

EventFormatter.prototype.processHours = function( isStart ) {

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

EventFormatter.prototype.validateMinutes = function( minutes ) {

    'use strict';

    var validMinutes = false;

    if ( minutes.match( /[0-5]\d/ ) ) {
        validMinutes = minutes;
    }

    return validMinutes;

};

EventFormatter.prototype.processMinutes = function( isStart ) {

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

EventFormatter.prototype.calculateNextOccurrence = function() {

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

    if ( this.validateDate( startDate ) !== false
         && this.validateHours( startHours ) !== false
         && this.validateMinutes( startMinutes ) !== false ) {

        dateParts = startDate.split( ' ' );
        dateString = dateParts[0] + ', '
                   + dateParts[2] + ' '
                   + dateParts[1] + ' '
                   + dateParts[3] + ' '
                   + startHours + ':'
                   + startMinutes + ':00';
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

    }

};

EventFormatter.prototype.checkStartEndOrder = function() {

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
