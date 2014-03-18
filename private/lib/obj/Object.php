<?php

require( LIB_DIR . 'obj/ObjectError.php' );
require( LIB_DIR . 'obj/PositionComponent.php' );
require( LIB_DIR . 'obj/ImageComponent.php' );
require( LIB_DIR . 'obj/MessageComponent.php' );
require( LIB_DIR . 'obj/EstablishmentComponent.php' );
require( LIB_DIR . 'obj/EventComponent.php' );

/*******************************************************************************
*                                                                              *
* Basic 'container' class which holds other components                         *
*                                                                              *
*******************************************************************************/

class Object extends ObjectError {

    private $_id;

    private $_position;

    private $_message;
    private $_establishment;
    private $_event;

    private $_hasMessage;
    private $_hasEstablishment;
    private $_hasEvent;

    //metadata for search/ranking
    private $_lastActivity;
    private $_serviceLevel;
    private $_replyCount;
    private $_eventCount;

    public function __construct( &$datastore, &$lang ) {

        if ( !is_object( $datastore ) ) {
            throw new Exception( 'Datastore is not an object.' );
        }

        parent::__construct();

        $this->_id = null;

        $this->_position = null;

        $this->_message = null;
        $this->_establishment = null;
        $this->_event = null;

        $this->_hasMessage = false;
        $this->_hasEstablishment = false;
        $this->_hasEvent = false;

        $this->_lastActivity = null;
        $this->_serviceLevel = null;
        $this->_replyCount = null;
        $this->_eventCount = null;

        $this->_data =& $datastore;
        $this->_lang =& $lang;

    }

    public function create( $lat, $lng ) {

        $this->_resetValidationErrors();

        $this->_createPosition( $lat, $lng );

        return $this->isValid();

    }

    public function load( $objectData ) {

        $this->_resetValidationErrors();

        if ( isset( $objectData['id'] ) ) {
            $this->_id = Common::normalizeGlobalId( $objectData['id'] );
        } else {
            $this->_addValidationError( 'Id is required.' );
        }

        if ( isset( $objectData['lastActivity'] ) ) {
            $this->_lastActivity = $objectData['lastActivity'];
        } else {
            $this->_addValidationError( 'Last Activity is required.' );
        }

        if ( isset( $objectData['position'] ) ) {
            $this->_loadPosition( $objectData['position'] );
        } else {
            $this->_addValidationError( 'Position is required.' );
        }

        if ( isset( $objectData['message'] ) ) {
            $this->loadMessage( $objectData['message'] );
        }

        if ( isset( $objectData['establishment'] ) ) {
            $this->loadEstablishment( $objectData['establishment'] );
        }

        if ( isset( $objectData['event'] ) ) {
            $this->loadEvent( $objectData['event'] );
        }

        return true;//$this->isValid();

    }

    public function save() {

        if ( $this->isValid() ) {

            if ( $this->hasEvent() && !$this->hasEstablishment() ) {
                throw new Exception( 'Object has event but not business.' );
            }

            $this->_id = Common::normalizeGlobalId(
                $this->_data->createObject( $this, $ip )
            );

        }

    }

    public function loadPosition( $position ) {
        $this->_position = $position;
    }

    public function createMessage( $text, $image, $replyTo, $parentObjectType ) {

        /*if ( !$this->isValid() ) {
            $this->_addValidationError(
                'Cannot create message with invalid object.'
            );
            return false;
        }*/

        try {

            $this->_message = new MessageComponent( $this->_data );

            if ( is_array( $image ) ) {

                if ( isset( $image['resource'] )
                     && isset( $image['extension'] )
                     && isset( $image['orientation'] ) ) {

                    $imageObj = new ImageComponent( $this->_data );
                    $imageObj->create(
                        $image['resource'],
                        $image['extension'],
                        $image['orientation']
                    );

                } else if ( isset( $image['name'] )
                            && isset( $image['thumbAspectRatio'] )
                            && isset( $image['fullAspectRatio'] ) ) {

                    $imageObj = new ImageComponent( $this->_data );
                    $imageObj->load(
                        $image['name'],
                        $image['thumbAspectRatio'],
                        $image['fullAspectRatio']
                    );

                }

            } else {
                $imageObj = null;
            }

            $success = $this->_message->create( $text, $imageObj, $replyTo, $parentObjectType );
            if ( $success ) {
                $this->_hasMessage = true;
            } else {
                $this->_hasMessage = false;
                foreach ( $this->_message->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
                $this->_message = null;
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
            $this->_message = null;
        }

        if ( $this->_message !== null ) {
            return true;//$this->_message->isValid();
        } else {
            return false;
        }

    }

    public function loadMessage( $messageData ) {

        /*if ( !$this->isValid() ) {
            $this->_addValidationError(
                'Cannot load message with invalid object.'
            );
            return false;
        }*/

        try {
            $this->_message = new MessageComponent( $this->_data );
            $success = $this->_message->load( $messageData );
            if ( $success ) {
                $this->_hasMessage = true;
            } else {
                $this->_hasMessage = false;
                foreach ( $this->_message->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
                $this->_message = null;
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
            $this->_message = null;
        }

        if ( $this->_message !== null ) {
            return true;//$this->_message->isValid();
        } else {
            return false;
        }

    }

    public function createEstablishment( $accountId, $city, $category,
                                         $name, $logo, $description,
                                         $address, $hours, $tel,
                                         $email, $url ) {

        /*if ( !$this->isValid() ) {
            $this->_addValidationError(
                'Cannot create business with invalid object.'
            );
            return false;
        }*/

        try {

            $this->_establishment = new EstablishmentComponent( $this->_data );

            if ( is_array( $logo ) ) {

                if ( isset( $logo['resource'] )
                     && isset( $logo['extension'] )
                     && isset( $logo['orientation'] ) ) {

                    $logoObj = new ImageComponent( $this->_data );
                    $logoObj->create(
                        $logo['resource'],
                        $logo['extension'],
                        $logo['orientation']
                    );

                } else if ( isset( $logo['name'] )
                            && isset( $logo['thumbAspectRatio'] )
                            && isset( $logo['fullAspectRatio'] ) ) {

                    $logoObj = new ImageComponent( $this->_data );
                    $logoObj->load(
                        $logo['name'],
                        $logo['thumbAspectRatio'],
                        $logo['fullAspectRatio']
                    );

                }

            } else {
                $logoObj = null;
            }

            $success = $this->_establishment->create( $accountId, $city,
                                                      $category,$name,
                                                      $logoObj, $description,
                                                      $address, $hours, $tel,
                                                      $email, $url );

            if ( $success ) {
                $this->_hasEstablishment = true;
            } else {
                $this->_hasEstablishment = false;
                foreach ( $this->_establishment->getValidationErrors()
                          as $error ) {
                    $this->_addValidationError( $error );
                }
                $this->_establishment = null;
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
            $this->_establishment = null;
        }

        if ( $this->_establishment !== null ) {
            return true;//$this->_establishment->isValid();
        } else {
            return false;
        }

    }

    public function loadEstablishment( $establishmentData ) {

        /*if ( !$this->isValid() ) {
            $this->_addValidationError(
                'Cannot load business with invalid object.'
            );
            return false;
        }*/

        try {
            $this->_establishment = new EstablishmentComponent( $this->_data );
            $success = $this->_establishment->load( $establishmentData );
            if ( $success ) {
                $this->_hasEstablishment = true;
            } else {
                $this->_hasEstablishment = false;
                foreach ( $this->_establishment->getValidationErrors()
                          as $error ) {
                    $this->_addValidationError( $error );
                }
                $this->_establishment = null;
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
            $this->_establishment = null;
        }

        if ( $this->_establishment !== null ) {
            return true;//$this->_establishment->isValid();
        } else {
            return false;
        }

    }

    public function createEvent( $establishmentObjectId, $startDate,
                                 $startHours, $startMinutes, $endDate,
                                 $endHours, $endMinutes, $repeatsWeekly,
                                 $timeZone ) {

        /*if ( !$this->isValid() ) {
            $this->_addValidationError(
                'Cannot create event with invalid object.'
            );
            return false;
        }*/

        try {
            $this->_event = new EventComponent();
            $success = $this->_event->create( $establishmentObjectId,
                                              $startDate, $startHours,
                                              $startMinutes, $endDate,
                                              $endHours, $endMinutes,
                                              $repeatsWeekly, $timeZone );
            if ( $success ) {
                $this->_hasEvent = true;
            } else {
                $this->_hasEvent = false;
                foreach ( $this->_event->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
                $this->_event = null;
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
            $this->_event = null;
        }

        if ( $this->_event !== null ) {
            return true;//$this->_event->isValid();
        } else {
            return false;
        }

    }

    public function loadEvent( $eventData ) {

        /*if ( !$this->isValid() ) {
            $this->_addValidationError(
                'Cannot load event with invalid object.'
            );
            return false;
        }*/

        try {
            $this->_event = new EventComponent();
            $success = $this->_event->load( $eventData );
            if ( $success ) {
                $this->_hasEvent = true;
            } else {
                $this->_hasEvent = false;
                foreach ( $this->_event->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
                $this->_event = null;
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
            $this->_event = null;
        }

        if ( $this->_event !== null ) {
            return true;//$this->_event->isValid();
        } else {
            return false;
        }

    }

    public function getId() {
        return $this->_id;
    }

    public function setId( $id ) {
        $this->_id = Common::normalizeGlobalId( $id );
    }

    public function getLastActivity() {
        return $this->_lastActivity;
    }

    public function setLastActivity( $lastActivity ) {
        $this->_lastActivity = $lastActivity;
    }

    public function getServiceLevel() {
        return $this->_serviceLevel;
    }

    public function setServiceLevel( $serviceLevel ) {
        $this->_serviceLevel = $serviceLevel;
    }

    public function getReplyCount() {
        return $this->_replyCount;
    }

    public function setReplyCount( $replyCount ) {
        $this->_replyCount = $replyCount;
    }

    public function appendReplyTo( $object ) {
        $this->_message->setReplyToObject( $object );
    }

    public function replyAllowed() {
        return true;
    }

    public function getEventCount() {
        return $this->_eventCount;
    }

    public function setEventCount( $eventCount ) {
        $this->_eventCount = $eventCount;
    }

    public function getPosition() {
        return $this->_position;
    }

    public function getLat() {
        return $this->_position->getLat();
    }

    public function getLng() {
        return $this->_position->getLng();
    }

    public function getGeoHash() {
        return $this->_position->getGeoHash();
    }

    public function getGeoRelation() {
        return $this->_position->getRelation();
    }

    public function getGeoRelationFrom( $position ) {
        $this->_position->calculateRelationFrom( $position );
        return $this->_position->getRelation();
    }

    public function getGeoRelationTo( $position ) {
        $this->_position->calculateRelationTo( $position );
        return $this->_position->getRelation();
    }

    public function calculateGeoRelationTo( $position ) {
        return $this->_position->calculateRelationTo( $position );
    }

    public function calculateGeoRelationFrom( $position ) {
        return $this->_position->calculateRelationFrom( $position );
    }

    public function getPositionName() {
        return $this->_position->getName();
    }

    public function hasMessage() {
        return $this->_hasMessage;
    }

    public function getMessage() {
        return $this->_message;
    }

    public function hasEstablishment() {
        return $this->_hasEstablishment;
    }

    public function getEstablishment() {
        return $this->_establishment;
    }

    public function getEstablishmentCategory() {
        return $this->_establishment->getCategory();
    }

    public function hasEvent() {
        return $this->_hasEvent;
    }

    public function getEvent() {
        return $this->_event;
    }

    public function getType() {

        //do something like unix file permissions here, and then mask out
        //invalid sums

        $type = 0;

        if ( $this->hasMessage() ) {
            $type += 1;
        }

        if ( $this->hasEstablishment() ) {
            $type += 2;
        }

        if ( $this->hasEvent() ){
            $type += 3;
        }

        if ( $type !== 1
             && $type !== 2
             && $type !== 6 ) {

            $type = null;

        }

        return $type;

    }

    public function getTheme() {

        switch ( $this->getType() ) {
            case 1:
                $theme = 1;
                break;
            case 2:
            case 6:
                switch ( $this->getEstablishmentCategory() ) {
                    default:
                    case 'unassigned':
                        $theme = 8;
                        break;
                    case 'retail':
                        $theme = 2;
                        break;
                    case 'f&b':
                        $theme = 3;
                        break;
                    case 'nightlife':
                        $theme = 4;
                        break;
                    case 'transport':
                        $theme = 5;
                        break;
                    case 'convenience':
                        $theme = 6;
                        break;
                    case 'parks&rec':
                        $theme = 7;
                        break;                    
                    case 'landmarks&culture':
                        $theme = 8;
                        break;
                }
                break;
        }

        return $theme;

    }

    public function isValid() {

        if ( $this->_position !== null ) {

            try {
                if ( !$this->_position->isValid() ) {
                    foreach ( $this->_position->getValidationErrors()
                              as $error ) {
                        $this->_addValidationError( $error );
                    }
                }
            } catch ( Exception $e ) {
                $this->_addValidationError( $e->getMessage() );
            }
        } else {
            $this->_addValidationError( 'Position is required.' );
        }

        if ( $this->_hasMessage ) {
            try {
                if ( !$this->_message->isValid() ) {
                    foreach ( $this->_message->getValidationErrors()
                              as $error ) {
                        $this->_addValidationError( $error );
                    }
                }
            } catch ( Exception $e ) {
                $this->_addValidationError( $e->getMessage() );
            }
        }

        if ( $this->_hasEstablishment ) {
            try {
                if ( !$this->_establishment->isValid() ) {
                    foreach ( $this->_establishment->getValidationErrors()
                              as $error ) {
                        $this->_addValidationError( $error );
                    }
                }
            } catch ( Exception $e ) {
                $this->_addValidationError( $e->getMessage() );
            }
        }

        if ( $this->_hasEvent ) {
            try {
                if ( !$this->_event->isValid() ) {
                    foreach ( $this->_event->getValidationErrors()
                              as $error ) {
                        $this->_addValidationError( $error );
                    }
                }
            } catch ( Exception $e ) {
                $this->_addValidationError( $e->getMessage() );
            }
        }

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
                'HoipostObject::exportToDatastore(): Cannot export an '
                . 'invalid object.'
            );
        }

        $export = array(
            'type' => $this->getType(),
            'position' => $this->_position->exportToDatastore()
        );

        if ( $this->_hasMessage ) {
            $export['message'] = $this->_message->exportToDatastore();
        }

        if ( $this->_hasEstablishment ) {
            $export['establishment'] = $this->_establishment
                                       ->exportToDatastore();
        }

        if ( $this->_hasEvent ) {
            $export['event'] = $this->_event->exportToDatastore();
        }

        return $export;

    }

    public function exportToAPI() {

        //fail early
        /*if ( !$this->isValid() ) {
        echo var_dump($this->getValidationErrors());
        exit;
            throw new Exception(
                'HoipostObject::exportToAPI(): Cannot export an invalid object.'
            );
        }*/

        $export = array(
            'id' => $this->_id,
            'url' => BASE_PATH . 'obj/' . $this->_id . '/',
            'type' => $this->getType(),
            'position' => $this->_position->exportToAPI(),
            'lastActivity' => $this->_lang->get( 'OBJECT_LAST_ACTIVITY_LABEL' )
                            . Common::formatTimeRelation(
                                  time(),
                                  $this->_lastActivity,
                                  false,
                                  $this->_lang
                              ),
            'lastActivityShort' => Common::formatTimeRelation(
                time(),
                $this->_lastActivity,
                true,
                $this->_lang
            )
        );

        $export['theme'] = $this->getTheme();
        $export['location'] = $this->_getLocation();
        $export['replyCount'] = $this->_formatReplyCount( false );
        $export['replyCountShort'] = $this->_formatReplyCount( true );

        if ( $this->_hasMessage ) {
            $export['message'] = $this->_message->exportToAPI();
        }

        if ( $this->_hasEstablishment ) {
            $export['establishment'] = $this->_establishment->exportToAPI();
        }

        if ( $this->_hasEvent ) {
            $export['event'] = $this->_event->exportToAPI();
        }

        return $export;

    }

    /***************************************************************************
    * Protected methods                                                        *
    ***************************************************************************/

    protected function _createPosition( $lat, $lng ) {

        try {
            $this->_position = new PositionComponent( $this->_lang );
            if ( !$this->_position->create( $lat, $lng ) ) {
                foreach ( $this->_position->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
        }

        return $this->_position->isValid();

    }

    protected function _loadPosition( $positionData ) {

        try {
            $this->_position = new PositionComponent( $this->_lang );
            $success = $this->_position->load( $positionData );
            if ( !$success ) {
                foreach ( $this->_position->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
            }
        } catch ( Exception $e ) {
            $this->_addValidationError( $e->getMessage() );
        }

        return $this->_position->isValid();

    }

    protected function _createImage( $resource, $extension, $orientation ) {

        $image = null;

        if ( is_resource( $resource )
             && $extension !== null
             && $orientation !== null) {

            $image = new ImageComponent( $this->_data );

            $success = $image->create(
                $resource,
                $extension,
                $orientation
            );

            if ( !$success ) {
                foreach ( $image->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
                $image = null;
            }

        }

        return $image;

    }

    protected function _loadImage( $imageData ) {

        $image = null;

        if ( $imageData !== null ) {
        
            $image = new ImageComponent( $this->_data );
            $success = $image->load( $imageData['name'],
                                     $imageData['thumbAspectRatio'],
                                     $imageData['fullAspectRatio'] );

            if ( !$success ) {
                foreach ( $image->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
                $image = null;
            }

        }

        return $image;

    }

    private function _getLocation() {

        switch ( $this->getType() ) {
            case 1:
                $location = $this->getPositionName();
                break;
            case 2:
                switch ( $this->getEstablishmentCategory() ) {
                    case 'unassigned':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_UNASSIGNED' );
                        break;
                    case 'retail':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_RETAIL' );
                        break;
                    case 'f&b':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_F&B' );
                        break;
                    case 'nightlife':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_NIGHTLIFE' );
                        break;
                    case 'transport':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_TRANSPORT' );
                        break;
                    case 'convenience':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_CONVENIENCE' );
                        break;
                    case 'parks&rec':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_PARKS&REC' );
                        break;                    
                    case 'landmarks&culture':
                        $location = $this->_lang->get( 'ESTABLISHMENT_LOCATION_LANDMARKS&CULTURE' );
                        break;
                }
                break;
            case 6:
                $location = $this->_lang->get( 'EVENT_LOCATION_PREFIX' )
                          . $this->getEstablishment()->getName();
                break;
            default:
                $location = $this->_position->getName();
                break;
        }

        return $location;

    }

    private function _formatReplyCount( $abbreviate ) {

        if ( !is_int( $this->_replyCount ) ) {
            $replyCount = 0;
        } else {
            $replyCount = $this->_replyCount;
        }

        if ( $replyCount === 1 ) {
            return (string) $replyCount . $this->_lang->get( 'OBJECT_REPLY_COUNT_SUFFIX_SINGLE' );
        } else {
            return (string) $replyCount . $this->_lang->get( 'OBJECT_REPLY_COUNT_SUFFIX_PLURAL' );
        }
    }

}