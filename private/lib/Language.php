<?php

class Language {

    private $_code;
    private $_localization;
    
    public function __construct( $code = null ) {

        $this->_code = 'En';
        $this->_localization = Common::loadLocalization( $this->_code );

    }
    
    public function getCode() {
        return $this->_code;
    }
    
    public function get( $key ) {
        return $this->_localization[$key];
    }

    public function exportToJS( $name ) {

        $var = 'var ' . $name . '={';

        foreach ($this->_localization as $key => $value ) {
            $var .= '\'' . addslashes( $key ) . '\':\'';
            $var .= addslashes( $value ) . '\',';
        }

        $var = substr( $var, 0, strlen( $var ) - 1 );
        $var .= '};';

        return $var;

    }

}