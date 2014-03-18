<?php

/*******************************************************************************
*                                                                              *
* Position class                                                               *
*                                                                              *
*******************************************************************************/

class PositionComponent extends ObjectError {

    private $_city;

    private $_lat;
    private $_lng;
    private $_geoHash;

    //latitude and longitude in radians (for relational calculations)
    private $_rLat;
    private $_rLng;

    private $_relation;
    private $_name;

    private $_lang;

    public function __construct( &$lang ) {

        parent::__construct();

        $this->_city = null;
        $this->_lat = null;
        $this->_lng = null;
        $this->_rLat = null;
        $this->_rLng = null;
        $this->_geoHash = null;
        $this->_relation = null;
        $this->_name = null;

        $this->_lang =& $lang;

    }


    /***************************************************************************
    * Public methods                                                           *
    ***************************************************************************/

    public function create( $latitude, $longitude, $geoHash = null ) {

        $this->_resetValidationErrors();

        $this->_lat = $latitude;
        $this->_lng = $longitude;
        $this->_rLat = deg2rad( $this->_lat );
        $this->_rLng = deg2rad( $this->_lng );

        $this->_city = Common::getNearestActiveCity( $this->_lat, $this->_lng );

        if ( $geoHash !== null ) {
            $this->_geoHash = $geoHash;
        }

        return true;

    }

    public function load( $positionData ) {

        $calculateCity = false;

        $this->_resetValidationErrors();

        if ( isset( $positionData['city'] ) ) {
            $this->_city = (string) $positionData['city'];
        } else {
            $calculateCity = true;
        }

        if ( isset( $positionData['lat'] ) ) {
            $this->_lat = (float) $positionData['lat'];
        } else {
            $this->_addValidationError(
                'Position::load(): Attribute \'lat\' is required.'
            );
        }

        if ( isset( $positionData['lng'] ) ) {
            $this->_lng = (float) $positionData['lng'];
        } else {
            $this->_addValidationError(
                'Position::load(): Attribute \'lng\' is required.'
            );
        }

        if ( $calculateCity === true ) {

            $this->_city = Common::getNearestActiveCity(
                $this->_lat,
                $this->_lng
            );

            if ( $this->_city === false ) {
                $this->_addValidationError(
                    'Position::load(): getNearestActiveCity() returned false.'
                );
            }

        }

        if ( isset( $positionData['rLat'] ) ) {
            $this->_rLat = (float) $positionData['rLat'];
        } else {
            $this->_addValidationError(
                'Position::load(): Attribute \'rLat\' is required.'
            );
        }

        if ( isset( $positionData['rLng'] ) ) {
            $this->_rLng = (float) $positionData['rLng'];
        } else {
            $this->_addValidationError(
                'Position::load(): Attribute \'rLng\' is required.'
            );
        }

        if ( isset( $positionData['geoHash'] ) ) {
            $this->_geoHash = $positionData['geoHash'];
        }

        return true;

    }

    public function getLat() {

        return $this->_lat;

    }

    public function getLng() {

        return $this->_lng;

    }

    public function getGeoHash() {

        if ( $this->_geoHash === null ) {
            $this->_calculateGeoHash();
        }

        return $this->_geoHash;

    }

    public function getRelation() {

        return $this->_relation;

    }

    public function calculateRelationTo( $destination ) {

        //only proceed if this position and the destination
        //are validated
        if ( $this->isValid() && $destination->isValid() ) {

            $distance = $this->_distanceTo( $destination );
            $bearing = $this->_bearingTo( $destination );
            $direction = $this->_cardinalDirectionOf( $bearing );

            $this->_relation = array(
                'distance' => $distance,
                'bearing' => $bearing,
                'cardinalDirection' => $direction
            );

        }

        return true;

    }

    public function calculateRelationFrom( $position ) {

        $position->calculateRelationTo( $this );
        $this->_relation = $position->getRelation();

        return true;

    }

    public function getName() {

        $nodes = Common::loadDynamicNodeList(
            LIB_DIR . 'etc/DynamicNodeList.txt'
        );

        $nearestNode = Common::getNearestNode(
            $nodes,
            $this->_lat,
            $this->_lng
        );

        return $this->_name = $nearestNode['name'];

    }

    public function isValid() {

        $this->_city = Common::validateActiveCity( $this->_city );
        if ( $this->_city === null ) {
            $this->_addValidationError( 'Invalid active city.' );
        }

        $this->_lat = Common::validateLatitude( $this->_lat );
        if ( $this->_lat === null ) {
            $this->_addValidationError( 'Invalid latitude.' );
        }

        $this->_lng = Common::validateLongitude( $this->_lng );
        if ( $this->_lng === null ) {
            $this->_addValidationError( 'Invalid longitude.' );
        }

        $this->_rLat = $this->_validateRadianLatitude( $this->_rLat );
        if ( $this->_rLat === null ) {
            $this->_addValidationError( 'Invalid latitude in radians.' );
        }

        $this->_rLng = $this->_validateRadianLongitude( $this->_rLng );
        if ( $this->_rLng === null ) {
            $this->_addValidationError( 'Invalid longitude in radians.' );
        }

        if ( $this->_geoHash === null ) {
            try {
                $this->getGeoHash();
            } catch ( Exception $e ) {
                $this->_addValidationError( 'Could not calculate GeoHash.' );
            }
        } else {
            $this->_geoHash = $this->_validateGeoHash( $this->_geoHash );
            if ( $this->_geoHash === null ) {
                $this->_addValidationError( 'Invalid GeoHash.' );
            }
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
                'Position::exportToDatastore(): Cannot export an '
                . ' invalid object.'
            );
        }

        $export = array(
            'city' => $this->_city,
            'lat' => $this->_lat,
            'lng' => $this->_lng,
            'rLat' => $this->_rLat,
            'rLng' => $this->_rLng,
            'geoHash' => $this->getGeoHash()
        );

        return $export;

    }


    public function exportToAPI() {

        $export = array(
            'cityCode' => Common::getActiveCityCode( $this->_city ),
            'lat' => $this->_lat,
            'lng' => $this->_lng,
            'geoHash' => $this->getGeoHash()
        );

        $geoRelation = $this->_formatGeoRelation( false );

        if ( $geoRelation !== null ) {
            $export['geoRelation'] = $geoRelation;
        }

        $geoRelationShort = $this->_formatGeoRelation( true );

        if ( $geoRelationShort !== null ) {
            $export['geoRelationShort'] = $geoRelationShort;
        }

        return $export;

    }


    /***************************************************************************
    * Private methods                                                          *
    ***************************************************************************/

    private function _validateRadianLatitude( $latitude ) {

        $cleanLatitude = null;
        $latitude = Common::validateFloat( $latitude );

        if ( $latitude !== null
             && $latitude >= deg2rad( -90 )
             && $latitude <= deg2rad( 90 ) ) {
            $cleanLatitude = (float) $latitude;
        }

        return $cleanLatitude;

    }

    private function _validateRadianLongitude( $longitude ) {

        $cleanLongitude = null;
        $longitude = Common::validateFloat( $longitude );

        if ( $longitude !== null
             && $longitude >= deg2rad( -180 )
             && $longitude <= deg2rad( 180 ) ) {
            $cleanLongitude = (float) $longitude;
        }

        return $cleanLongitude;

    }

    private function _formatGeoRelation( $short = false ) {

        $formatted = null;

        if ( $this->_relation !== null ) {

            if ( $short ) {
                $label = '_SHORT_LABEL';
            } else {
                $label = '_LABEL';
            }

            $distance = '';
            
            if ( $this->_relation['distance'] > 10000 ) {
                $formatted = $this->_lang->get( 'GEO_DISTANCE_FAR' );
            } else if ( $this->_relation['distance'] > 1000 ) {
                $formatted = round(
                    ( $this->_relation['distance'] / 1000 ),
                    1
                )
                . $this->_lang->get(
                      'GEO_DISTANCE_KM'
                      . $label
                  )
                . $this->_lang->get(
                      'GEO_DIRECTION_'
                      . $this->_relation['cardinalDirection']
                      . $label
                  );
            } else if ( $this->_relation['distance'] > 10 ) {
                $formatted = $this->_relation['distance']
                           . $this->_lang->get(
                                'GEO_DISTANCE_M'
                                . $label
                             )
                           . $this->_lang->get(
                                 'GEO_DIRECTION_'
                                 . $this->_relation['cardinalDirection']
                                 . $label
                             );
            } else {
                $formatted = $this->_lang->get( 'GEO_DISTANCE_NEAR' );
            }

        }

        return $formatted;

    }

    private function _calculateGeoHash() {

        try {

            require_once( LIB_DIR . 'GeoHash.php' );

            $g = new GeoHash();
            $g->setLatitude( $this->_lat );
            $g->setLongitude( $this->_lng );
            $this->_geoHash = $g->getHash();

        } catch ( Exception $e ) {

            throw new Exception(
                'Position::_calculateGeoHash(): '
                . 'Could not calculate GeoHash '
                . '(Caught exception in GeoHash.php).'
            );

        }

    }

    private function _validateGeoHash( $geoHash ) {

        $cleanGeoHash = null;
        $test = preg_replace(
            '/[^0-9bcdefghjkmnpqrstuvwxyz]+/',
            '',
            substr( $geoHash, 0, GEOHASH_MAX_LENGTH )
        );

        if ( $test === $geoHash ) {
            $cleanGeoHash = $geoHash;
        }

        return $cleanGeoHash;

    }

    private function _distanceTo( $destination ) {

        //(using spherical law of cosines)

        //mean radius of Earth is about 6,371km
        $radius = 6371;

        $rLat = deg2rad( $destination->getLat()  );
        $rLng = deg2rad( $destination->getLng()  );
        
        return round(
                   $radius *
                   acos(
                       sin( $this->_rLat ) *
                       sin( $rLat ) +
                       cos( $this->_rLat ) *
                       cos( $rLat ) *
                       cos( $rLng - $this->_rLng )
                   ) * 1000
               );

    }

    private function _bearingTo( $destination ) {

        $rLat = deg2rad( $destination->getLat() );
        $rLng = deg2rad( $destination->getLng() );
        $dLng = $rLng - $this->_rLng;

        $y = sin( $dLng ) * cos( $rLat );
        $x = cos( $this->_rLat ) * sin( $rLat )
             - sin( $this->_rLat ) * cos( $rLat ) * cos( $dLng );

        return ( rad2deg( atan2( $y, $x ) ) + 360 ) % 360;

    }

    private function _cardinalDirectionOf( $bearing ) {

        $direction = null;

        if ( $bearing >= 337.5 && $bearing < 22.5 ) {
            $direction = 'N';
        } else if ( $bearing >= 22.5 && $bearing < 67.5 ) {
            $direction = 'NE';
        } else if ( $bearing >= 67.5 && $bearing < 112.5 ) {
            $direction = 'E';
        } else if ( $bearing >= 112.5 && $bearing < 157.5 ) {
            $direction = 'SE';
        } else if ( $bearing >= 157.5 && $bearing < 202.5 ) {
            $direction = 'S';
        } else if ( $bearing >= 202.5 && $bearing < 247.5 ) {
            $direction = 'SW';
        } else if ( $bearing >= 247.5 && $bearing < 292.5 ) {
            $direction = 'W';
        } else {
            $direction = 'NW';
        }

        if ( $direction === null ) {
            throw new Exception(
                'Position::_cardinalDirectionOf(): $bearing out of range.'
            );
        }

        return $direction;

    }

    private function _reset() {

        $this->_lat = null;
        $this->_lng = null;
        $this->_rLat = null;
        $this->_rLng = null;
        $this->_geoHash = null;

    }

}