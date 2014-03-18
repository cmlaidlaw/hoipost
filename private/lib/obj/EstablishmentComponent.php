<?php

/*******************************************************************************
*                                                                              *
* Establishment class                                                          *
* (a component that adds establishment information to an object)               *
*                                                                              *
*******************************************************************************/

class EstablishmentComponent extends ObjectError {

    private $_accountId;
    private $_city;
    private $_category;
    private $_name;
    private $_logo;
    private $_description;
    private $_address;
    private $_hours;
    private $_tel;
    private $_email;
    private $_url;

    public function __construct( &$datastore ) {

        if ( !is_object( $datastore ) ) {
            throw new Exception( 'Datastore is not an object.' );
        }

        parent::__construct();

        $this->_accountId = null;
        $this->_city = null;
        $this->_category = null;
        $this->_name = null;
        $this->_logo = null;
        $this->_description = null;
        $this->_address = null;
        $this->_hours = null;
        $this->_tel = null;
        $this->_email = null;
        $this->_url = null;

        $this->_data = $datastore;

    }


    /***************************************************************************
    * Public methods                                                           *
    ***************************************************************************/

    public function create ( $accountId, $city, $category = 'unassigned',
                             $name, $logo = null,
                             $description = null, $address = null,
                             $hours = null, $tel = null,
                             $email = null, $url = null ) {

        $this->_resetValidationErrors();

        $this->_accountId = $accountId;
        $this->_city = $city;
        $this->_category = $category;
        $this->_name = $name;
        $this->_logo = $logo;
        $this->_description = $description;
        $this->_address = $address;
        $this->_hours = $hours;
        $this->_tel = $tel;
        $this->_email = $email;
        $this->_url = $url;

        return true;//$this->isValid();

    }

    public function load( $data ) {

        $this->_resetValidationErrors();

        if ( isset( $data['accountId'] ) ) {
            $this->_accountId = $data['accountId'];
        }

        if ( isset( $data['city'] ) ) {
            $this->_city = $data['city'];
        }

        if ( isset( $data['category'] ) ) {
            $this->_category = $data['category'];
        } else {
            $this->_category = 'unassigned';
        }

        if ( isset( $data['name'] ) ) {
            $this->_name = $data['name'];
        }

        if ( isset( $data['logo'] ) ) {
            $this->_logo = new ImageComponent( $this->_data );
            $this->_logo->load(
                $data['logo']['name'],
                $data['logo']['thumbAspectRatio'],
                $data['logo']['fullAspectRatio']
            );
        }

        if ( isset( $data['description'] ) ) {
            $this->_description = $data['description'];
        }

        if ( isset( $data['address'] ) ) {
            $this->_address = $data['address'];
        }

        if ( isset( $data['hours'] ) ) {
            $this->_hours = $data['hours'];
        }

        if ( isset( $data['tel'] ) ) {
            $this->_tel = $data['tel'];
        }

        if ( isset( $data['email'] ) ) {
            $this->_email = $data['email'];
        }

        if ( isset( $data['url'] ) ) {
            $this->_url = $data['url'];
        }

        return true;//$this->isValid();

    }

    public function getAccountId() {
        return $this->_accountId;
    }

    public function getCity() {
        return $this->_city;
    }

    public function getCategory() {
        return $this->_category;
    }

    public function getName() {
        return $this->_name;
    }

    public function getLogo() {
        return $this->_logo;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getAddress() {
        return $this->_address;
    }

    public function getHours() {
        return $this->_hours;
    }

    public function getTel() {
        return $this->_tel;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function getURL() {
        return $this->_url;
    }

    public function isValid() {

        //accountId is REQUIRED
        $this->_accountId = $this->_validateAccountId( $this->_accountId );
        if ( $this->_accountId === null ) {
            $this->_addValidationError( 'Account Id is invalid.' );
        }

        //city is REQUIRED
        $this->_city = $this->_validateCity( $this->_city );
        if ( $this->_city === null ) {
            $this->_addValidationError( 'City is invalid.' );
        }

        //category is REQUIRED
        $this->_category = $this->_validateCategory( $this->_category );
        if ( $this->_category === null ) {
            $this->_category = 'unassigned';
            //$this->_addValidationError( 'Category is invalid.' );
        }

        //name is REQUIRED
        $this->_name = $this->_validateName( $this->_name );
        if ( $this->_name === null ) {
            $this->_addValidationError( 'Name is invalid.' );
        }

        //logo is OPTIONAL
        if ( $this->_logo !== null ) {
            if ( !$this->_logo->isValid() ) {
                foreach ( $this->_logo->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
            }
        }

        //description is OPTIONAL
        if ( $this->_description !== null ) {
            $this->_description = $this->_validateDescription(
                $this->_description
            );
            if ( $this->_description === null ) {
                $this->_addValidationError( 'Description is invalid.' );
            }
        }

        //address is OPTIONAL
        if ( $this->_address !== null ) {
            $this->_address = $this->_validateAddress( $this->_address );
            if ( $this->_address === null ) {
                $this->_addValidationError( 'Address is invalid.' );
            }
        }

        //hours is OPTIONAL
        if ( $this->_hours !== null ) {
            $this->_hours = $this->_validateHours( $this->_hours );
            if ( $this->_hours === null ) {
                $this->_addValidationError( 'Hours are invalid.' );
            }
        }

        //tel is OPTIONAL
        if ( $this->_tel !== null ) {
            $this->_tel = $this->_validateTel( $this->_tel );
            if ( $this->_tel === null ) {
                $this->_addValidationError( 'Tel is invalid.' );
            }
        }

        //email is OPTIONAL
        if ( $this->_email !== null ) {
            $this->_email = $this->_validateEmail( $this->_email );
            if ( $this->_email === null ) {
                $this->_addValidationError( 'Email is invalid.' );
            }
        }

        //url is OPTIONAL
        if ( $this->_url !== null ) {
            $this->_url = $this->_validateURL( $this->_url );
            if ( $this->_url === null ) {
                $this->_addValidationError( 'URL is invalid.' );
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
                'EstablishmentComponent::exportToDatastore(): Cannot export an '
                . 'invalid object.'
            );
        }

        $export = array(
            'accountId' => $this->_accountId,
            'city' => $this->_city,
            'category' => $this->_category,
            'name' => $this->_name,
            'logo' => null,
            'description' => $this->_description,
            'address' => $this->_address,
            'hours' => $this->_hours,
            'tel' => $this->_tel,
            'email' => $this->_email,
            'url' => $this->_url
        );

        if ( $this->_logo !== null ) {
            $export['logo'] = $this->_logo->exportToDatastore();
        }

        return $export;

    }

    public function exportToAPI() {

        //fail early
        /*if ( !$this->isValid() ) {
            throw new Exception(
                'EstablishmentComponent::exportToAPI(): Cannot export an '
                . 'invalid object.'
            );
        }*/

        //no need to encode these strings because they are all passed through
        //Mustache, which handles escaping itself
        $export = array(
            'category' => $this->_category,
            'name' => $this->_name,//Common::encodeString( $this->_name ),
            'logo' => null,
            'description' => $this->_description,//Common::encodeString( $this->_description ),
            'address' => $this->_address,//Common::encodeString( $this->_address ),
            'hours' => $this->_hours,
            'tel' => $this->_tel,
            'email' => $this->_email,
            'url' => $this->_url
        );

        if ( $this->_logo !== null ) {
            $export['logo'] = $this->_logo->exportToAPI();
        }

        return $export;

    }

    /***************************************************************************
    * Private methods                                                          *
    ***************************************************************************/

    private function _validateAccountId ( $id ) {
        return Common::validateInternalId( $id );
    }

    private function _validateCity( $city ) {

        $cleanCity = null;

        if ( in_array(
                 strtolower( $city ),
                 explode( ',', ESTABLISHMENT_AVAILABLE_CITIES )
             ) ) {
            $cleanCity = $city;
        }

        return $cleanCity;

    }

    private function _validateCategory( $category ) {

        $cleanCategory = null;

        if ( in_array(
                strtolower( $category ),
                explode( ',', ESTABLISHMENT_AVAILABLE_CATEGORIES )
             ) ) {
            $cleanCategory = $category;
        }

        return $cleanCategory;

    }

    private function _validateName( $name ) {

        $cleanName = null;
        $name = Common::validateString(
            $name,
            ESTABLISHMENT_NAME_MAX_LENGTH,
            false,
            true,
            true
        );

        if ( $name !== null ) {
            $cleanName = $name;
        }

        return $cleanName;

    }

    private function _validateDescription( $description ) {

        $cleanDescription = null;
        $description = Common::validateString(
            $description,
            ESTABLISHMENT_DESCRIPTION_MAX_LENGTH,
            true,
            true,
            true
        );

        if ( $description !== null ) {
            $cleanDescription = $description;
        }

        return $cleanDescription;

    }

    private function _validateAddress( $address ) {

        $cleanAddress = null;
        $address = Common::validateString(
            $address,
            ESTABLISHMENT_ADDRESS_MAX_LENGTH,
            true,
            true,
            true
        );

        if ( $address !== null ) {
            $cleanAddress = $address;
        }

        return $cleanAddress;

    }

    private function _validateHours( $hours ) {

        $cleanHours = null;
        $valid = true;

        if ( is_array( $hours ) && count( $hours ) === 7 ){

            $i = 0;
            foreach ( $hours as $day => $hourSet ) {

                if ( $day !== $i
                     || !isset( $hourSet['open'] )
                     || !isset( $hourSet['open']['hh'] )
                     || !is_int( $hourSet['open']['hh'] )
                     || $hourSet['open']['hh'] < 0
                     || $hourSet['open']['hh'] > 24
                     || !isset( $hourSet['open']['mm'] )
                     || !is_int( $hourSet['open']['mm'] )
                     || $hourSet['open']['mm'] < 0
                     || $hourSet['open']['mm'] > 59
                     || !isset( $hourSet['close'] )
                     || !isset( $hourSet['close']['hh'] )
                     || !is_int( $hourSet['close']['hh'] )
                     || $hourSet['close']['hh'] < 0
                     || $hourSet['close']['hh'] > 24
                     || !isset( $hourSet['close']['mm'] )
                     || !is_int( $hourSet['close']['mm'] )
                     || $hourSet['close']['mm'] < 0
                     || $hourSet['close']['mm'] > 59 ) {

                     $valid = false;

                }
                $i++;

            }

        }

        if ( $valid ) {
            $cleanHours = $hours;
        }

        return $cleanHours;

    }

    private function _validateTel( $tel ) {
        return Common::validateTel( $tel );
    }

    private function _validateEmail( $email ) {
        return Common::validateEmail( $email );
    }

    private function _validateURL( $url ) {
        return Common::validateURL( $url );
    }

    private function _reset() {

        $this->_name = null;
        $this->_logo = null;
        $this->_description = null;
        $this->_address = null;
        $this->_hours = null;
        $this->_tel = null;
        $this->_email = null;
        $this->_url = null;

    }

}