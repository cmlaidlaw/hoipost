<?php

/*******************************************************************************
*                                                                              *
* Event class (a component that adds an active timeframe to an object)         *
*                                                                              *
*******************************************************************************/

class EventComponent extends ObjectError {

    private $_establishmentObjectId;

    private $_startDateTime;
    private $_endDateTime;
    private $_repeatsWeekly;

    public function __construct() {

        parent::__construct();

        $this->_establishmentObjectId = null;

        $this->_startDateTime = null;
        $this->_endDateTime = null;
        $this->_repeatsWeekly = null;

    }


    /***************************************************************************
    * Public methods                                                           *
    ***************************************************************************/

    public function create ( $establishmentObjectId, $startDate, $startHours,
                             $startMinutes, $endDate, $endHours,
                             $endMinutes, $repeatsWeekly, $timeZone ) {

        $this->_resetValidationErrors();

        $this->_establishmentObjectId = $establishmentObjectId;

        $this->_startDateTime = $this->_calculateDateTime( $startDate,
                                                           $startHours,
                                                           $startMinutes,
                                                           $timeZone );
        $this->_endDateTime = $this->_calculateDateTime( $endDate,
                                                         $endHours,
                                                         $endMinutes,
                                                         $timeZone );
        $this->_repeatsWeekly = $repeatsWeekly;

        return true;//$this->isValid();

    }

    public function load( $data ) {

        $this->_resetValidationErrors();

        if ( isset( $data['establishmentObjectId'] ) ) {
            $this->_establishmentObjectId = $data['establishmentObjectId'];
        }

        if ( isset( $data['startDateTime'] ) ) {
            $this->_startDateTime = new DateTime( $data['startDateTime'] );
        } else {
            $this->_addValidationError( 'StartDateTime is required.' );
        }

        if ( isset( $data['endDateTime'] ) ) {
            $this->_endDateTime = new DateTime( $data['endDateTime'] );
        } else {
            $this->_addValidationError( 'EndDateTime is required.' );
        }

        if ( isset( $data['repeatsWeekly'] ) ) {
            $this->_repeatsWeekly = $data['repeatsWeekly'];
        } else {
            $this->_addValidationError( 'RepeatsWeekly is required.' );
        }

        return true;//$this->isValid();

    }

    public function getEstablishmentObjectId() {
        return $this->_establishmentObjectId;
    }

    public function getStartDateTime() {
        return $this->_startDateTime;
    }

    public function getEndDateTime() {
        return $this->_endDateTime;
    }

    public function getRepeatsWeekly() {
        return $this->_repeatsWeekly;
    }

    public function isValid() {

        //establishmentObjectId is REQUIRED
        $this->_establishmentObjectId = Common::validateGlobalId(
            $this->_establishmentObjectId
        );
        if ( $this->_establishmentObjectId === null ) {
            $this->_addValidationError( 'Establishment Id is invalid.' );
        }

        //startDateTime is REQUIRED
        $this->_startDateTime = $this->_validateDateTime(
            $this->_startDateTime
        );
        if ( $this->_startDateTime === null ) {
            $this->_addValidationError( 'Start DateTime is invalid.' );
        }

        //endDateTime is REQUIRED
        $this->_endDateTime = $this->_validateDateTime(
            $this->_endDateTime
        );
        if ( $this->_endDateTime === null ) {
            $this->_addValidationError( 'End DateTime is invalid.' );
        }

        //make sure startDateTime is earlier than the endDateTime
        if ( $this->_startDateTime >= $this->_endDateTime ) {
            $this->_addValidationError(
                'StartDateTime must preceed EndDateTime.'
            );
        }

        //repeatsWeekly is REQUIRED
        $this->_repeatsWeekly = $this->_validateRepeatsWeekly(
            $this->_repeatsWeekly
        );
        if ( $this->_repeatsWeekly === null ) {
            $this->_addValidationError( 'RepeatsWeekly is invalid.' );
        }

        //return true or false depending on the existence of errors
        if ( empty( $this->_validationErrors ) ) {
            return true;
        } else {
            return false;
        }

    }

    public function exportToDatastore() {

        //fail early
        if ( !$this->isValid() ) {
            throw new Exception(
                'EventComponent::exportToDatastore(): '
                . 'Cannot export an invalid object.'
            );
        }

        $UTC = new DateTimeZone( 'UTC' );

        $startDateTimeUTC = $this->_startDateTime;
        $startDateTimeUTC->setTimezone( $UTC );

        $endDateTimeUTC = $this->_endDateTime;
        $endDateTimeUTC->setTimezone( $UTC );

        $export = array(
            'establishmentObjectId' => $this->_establishmentObjectId,
            'startDateTime' => $startDateTimeUTC->format( 'Y-m-d H:i:s' ),
            'endDateTime' => $endDateTimeUTC->format( 'Y-m-d H:i:s' ),
            'repeatsWeekly' => $this->_repeatsWeekly
        );

        return $export;

    }

    public function exportToAPI() {

        //fail early
        /*if ( !$this->isValid() ) {
            throw new Exception(
                'EventComponent::exportToAPI(): '
                . 'Cannot export an invalid object.'
            );
        }*/

        $now = new DateTime( 'now' );
        $UTC = new DateTimeZone( 'UTC' );

        $startDateTimeUTC = $this->_startDateTime;
        $startDateTimeUTC->setTimezone( $UTC );

        $endDateTimeUTC = $this->_endDateTime;
        $endDateTimeUTC->setTimezone( $UTC );

        //if the both the start and end datetimes have
        //occurred, then return the next occurrence of
        //the event
        if ( $startDateTimeUTC <= $now
             && $endDateTimeUTC <= $now ) {

            $nextStartDateTime = Common::getNextOccurrence(
                $this->_startDateTime->format( 'Y-m-d H:i:s' ),
                $this->_repeatsWeekly,
                false
            );

            $nextEndDateTime = Common::getNextOccurrence(
                $this->_endDateTime->format( 'Y-m-d H:i:s' ),
                $this->_repeatsWeekly,
                false
            );

            //if the next starting time was advanced beyond the next
	    //ending time ( i.e. the event is in-progress) then roll
	    //back the starting time by one week
	    if ( $nextStartDateTime > $nextEndDateTime ) {

                $nextStartDateTime->sub(
                    new DateInterval( 'P1W' )
                );

            }

        } else {

            $nextStartDateTime = $startDateTimeUTC;
            $nextEndDateTime = $endDateTimeUTC;
        }

        $export = array(
            'establishmentObjectId' => $this->_establishmentObjectId,
            'startDateTime' => $nextStartDateTime->format( 'Y-m-d H:i:s' ),
            'endDateTime' => $nextEndDateTime->format( 'Y-m-d H:i:s' ),
            'repeatsWeekly' => $this->_repeatsWeekly
        );

        return $export;

    }

    /***************************************************************************
    * Private methods                                                          *
    ***************************************************************************/

    private function _validateDateTime( $dateTime ) {

        $cleanDateTime = null;

        if ( $dateTime instanceof DateTime ) {
            $cleanDateTime = $dateTime;
        }

        return $cleanDateTime;

    }

    private function _validateTimeZone( $timeZone ) {

        return Common::validateTimeZone( $timeZone );

    }

    private function _validateRepeatsWeekly( $repeatsWeekly ) {

        return Common::validateBoolean( $repeatsWeekly );

    }

    private function _calculateDateTime( $date, $hours, $minutes, $timeZone ) {

        $dateTime = null;

        $date = preg_replace(
            '/[^A-Z][^a-z][^a-z] [^A-Z][^a-z][^a-z] [^0-3][^0-9] [^0-9]{4}/',
            '',
            $date
        );

        $hours = preg_replace(
            '/[^0-2][^0-9]/',
            '',
            $hours
        );

        $minutes = preg_replace(
            '/[^0-5][^0-9]/',
            '',
            $minutes
        );

        if ( strlen( $hours ) === 2
             && (int) $hours >= 0
             && (int) $hours < 24
             && strlen( $minutes ) === 2
             && (int) $minutes >= 0
             && (int) $minutes < 60 ) {

            $result = false;

            try {

                $dateTimeZone = $this->_validateTimeZone( $timeZone );

                if ( $dateTimeZone !== null ) {
                    $result = DateTime::createFromFormat(
                        'D M d Y, H:i',
                        $date . ', ' . $hours . ':' . $minutes,
                        new DateTimeZone( $dateTimeZone )
                    );
                    $result->setTimezone( new DateTimeZone( 'UTC' ) );
                } else {
                    $this->_addValidationError( 'Invalid timezone.' );
                }

            } catch ( Exception $e ) {
                $this->_addValidationError( 'Invalid timezone.' );
            }

            if ( $result !== false ) {
                $dateTime = $result;
            }

        }

        return $dateTime;

    }

    private function _reset() {
        
        $this->_startDateTime = null;
        $this->_endDateTime = null;
        $this->_repeatsWeekly = null;

    }

}
