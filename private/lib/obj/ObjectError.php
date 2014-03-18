<?php

class ObjectError {

    protected $_validationErrors;

    public function __construct() {

        $this->_validationErrors = array();

    }

    public function getValidationErrors() {
        return $this->_validationErrors;
    }

    protected function _addValidationError( $error ) {
        $this->_validationErrors[] = $error;
    }

    protected function _resetValidationErrors() {
        $this->_validationErrors = array();
    }

}