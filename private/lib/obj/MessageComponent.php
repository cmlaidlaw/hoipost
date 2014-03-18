<?php

/*******************************************************************************
*                                                                              *
* Message class (a component that adds words and pictures to an object)        *
*                                                                              *
*******************************************************************************/

class MessageComponent extends ObjectError {

    //core attributes
    private $_text;
    private $_image;
    private $_replyTo;
    private $_replyToObject;
    private $_parentObjectType;

    private $_data;

    public function __construct( $datastore ) {

        parent::__construct();

        $this->_text = null;
        $this->_image = null;
        $this->_replyTo = null;
        $this->_replyToObject = null;

        $this->_data =& $datastore;

    }


    /***************************************************************************
    * Public methods                                                           *
    ***************************************************************************/

    public function create( $text, $image = null, $replyTo = null, $parentObjectType = null ) {

        $this->_resetValidationErrors();

        $this->_text = $text;
        $this->_image = $image;
        $this->_replyTo = $replyTo;
        $this->_replyToObject = null;
        $this->_parentObjectType = $parentObjectType;

        return true;//$this->isValid();

    }

    public function load( $data ) {

        //populate explicit values

        $this->_resetValidationErrors();

        if ( isset( $data['text'] ) ) {
            $this->_text = $data['text'];
        }

        if ( isset( $data['image'] ) ) {
            $this->_image = new ImageComponent( $this->_data );
            $this->_image->load(
                $data['image']['name'],
                $data['image']['thumbAspectRatio'],
                $data['image']['fullAspectRatio']
            );
        }

        if ( isset( $data['replyTo'] ) ) {
            $this->_replyTo = $data['replyTo'];
            $this->_replyToObject = null;
        }

        if ( isset( $data['parentObjectType'] ) ) {
            $this->_parentObjectType = $data['parentObjectType'];
        }

        return true;//$this->isValid();

    }

    public function getText() {
        return $this->_text;
    }

    public function getImage() {
        return $this->_image;
    }

    public function getReplyTo() {
        return $this->_replyTo;
    }

    public function setReplyToObject( $object ) {
        return $this->_replyToObject = $object;
    }

    public function getParentObjectType() {
        return $this->_parentObjectType;
    }

    public function isValid() {

        //text OR picture is REQUIRED
        if ( $this->_text === null && $this->_image === null ) {
            $this->_addValidationError( 'Either text or picture is required.' );
        }

        //if text is provided, make sure it is valid
        if ( $this->_text !== null ) {
            $this->_text = $this->_validateText( $this->_text );
            if ( $this->_text === null ) {
                $this->_addValidationError( 'Text is invalid.' );
            }
        }

        //image is OPTIONAL
        if ( $this->_image !== null ) {
            if ( !$this->_image->isValid() ) {
                foreach ( $this->_image->getValidationErrors() as $error ) {
                    $this->_addValidationError( $error );
                }
            }
        }

        //replyTo is OPTIONAL
        if ( $this->_replyTo !== null ) {
            $this->_replyTo = $this->_validateReplyTo( $this->_replyTo );
            if ( $this->_replyTo === null ) {
                $this->_addValidationError( 'ReplyTo is invalid.' );
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
                'MessageComponent::exportToDatastore(): '
                . 'Cannot export an invalid object.'
            );
        }

        $export = array(
            'text' => $this->_text,
            'image' => null,
            'replyTo' => $this->_replyTo,
            'parentObjectType' => $this->_parentObjectType
        );

        if ( $this->_image !== null ) {
            $export['image'] = $this->_image->exportToDatastore();
        }

        return $export;

    }

    public function exportToAPI() {

        //fail early
        /*if ( !$this->isValid() ) {
            throw new Exception(
                'MessageComponent::exportToAPI(): '
                . 'Cannot export an invalid object.'
            );
        }*/

        //no need to encode strings because they are all passed to Mustache,
        //which will escape characters on its own
        $export = array(
            'text' => $this->_text,//Common::encodeString( $this->_text ),
            'image' => null,
            'replyTo' => $this->_replyTo
        );

        if ( $this->_image !== null ) {
            $export['image'] = $this->_image->exportToAPI();
        }

        if ( $this->_replyToObject !== null ) {
            $export['replyTo'] = $this->_replyToObject->exportToAPI();
        }

        return $export;

    }

    /***************************************************************************
    * Private methods                                                          *
    ***************************************************************************/

    private function _validateText( $text ) {

        $cleanText = null;
        $text = Common::validateString(
            $text,
            MESSAGE_MAX_LENGTH,
            true,
            true,
            true
        );

        if ( $text !== null ) {
            $cleanText = $text;
        }

        return $cleanText;

    }

    private function _validateReplyTo( $replyTo ) {

        $cleanReplyTo = null;
        $replyTo = Common::validateGlobalId( $replyTo );
    
        if ( $replyTo !== null ) {
            $cleanReplyTo = $replyTo;
        }

        return $cleanReplyTo;

    }

    private function _reset() {

        $this->_text = null;
        $this->_image = null;
        $this->_replyTo = null;

        return true;

    }

}