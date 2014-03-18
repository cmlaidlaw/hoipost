<?php

/*******************************************************************************
*                                                                              *
* Image class                                                                  *
*                                                                              *
*******************************************************************************/

class ImageComponent extends ObjectError {

    private $_name;
    private $_thumbAspectRatio;
    private $_fullAspectRatio;

    public function __construct( &$datastore ) {

        if ( !is_object( $datastore ) ) {
            throw new Exception(
                'Image::__construct(): $datastore parameter is required.'
            );
        }

        parent::__construct();

        $this->_name = null;
        $this->_thumbAspectRatio = null;
        $this->_fullAspectRatio = null;

        $this->_data = $datastore;

    }


    /***************************************************************************
    * Public methods                                                           *
    ***************************************************************************/

    public function create( $image, $extension, $orientation ) {

        $this->_resetValidationErrors();

        if ( !is_resource( $image ) ) {

            $this->_addValidationError(
                'Image is not a valid image resource.'
            );

        } else {

            //first rotate according to orientation
            switch ( $this->_validateOrientation( $orientation ) ) {
                case 3:
                    $image = imagerotate( $image, 180, 0 );
                    break;
                case 6:
                    $image = imagerotate( $image, -90, 0 );
                    break;
                case 8:
                    $image = imagerotate( $image, 90, 0 );
                    break;
                default:
                    break;
            }

            //set up variables used in resizing
            $originalWidth = imagesx( $image );
            $originalHeight = imagesy( $image );
            $aspectRatio = $originalWidth / $originalHeight;
            $thumbCropped = false;
            $fullResized = false;

            //do thumbnail first
            $thumbWidth = IMAGE_THUMB_MAX_WIDTH;

            if ( $aspectRatio <= IMAGE_THUMB_MAX_RATIO ) {
                $thumbHeight = IMAGE_THUMB_MAX_HEIGHT;
                $thumbCropped = true;
            } else {
                $thumbHeight = floor( $thumbWidth / $aspectRatio );
            }

            $fullWidth = $originalWidth;
            $fullHeight = $originalHeight;
            if ( $originalWidth > IMAGE_FULL_MAX_WIDTH ) {
                $fullWidth = IMAGE_FULL_MAX_WIDTH;
                $fullHeight = floor( $fullWidth / $aspectRatio );
                $fullResized = true;
            }

            //create the thumbnail resource
            $thumb = imagecreatetruecolor( $thumbWidth, $thumbHeight );

            if ( $extension === 'png' || $extension === 'gif' ) {
                imagealphablending( $thumb, false );
                imagesavealpha( $thumb, true );                
            } else {
                imageinterlace( $thumb, true );
            }

            //composite the 'snip' icon onto the thumbnail if it was cropped
            if ( $thumbCropped ) {

                $croppedHeight = $originalWidth / IMAGE_THUMB_MAX_RATIO;

                imagecopyresampled(
                    $thumb, $image, 0, 0, 0, 0,
                    $thumbWidth, $thumbHeight, $originalWidth, $croppedHeight
                );

                $snip = imagecreatefromjpeg( LIB_DIR . 'etc/snip.jpg' );
                imagecopy(
                    $thumb, $snip, 0, $thumbHeight - 20,
                    0, 0, 180, 20
                );
                imagedestroy( $snip );

            } else {

                imagecopyresampled(
                    $thumb, $image, 0, 0, 0, 0,
                    $thumbWidth, $thumbHeight, $originalWidth, $originalHeight
                );

            }

            //create the full-sized resource
            if ( $fullResized ) {

                $full = imagecreatetruecolor( $fullWidth, $fullHeight );

                if ( $extension === 'png' || $extension === 'gif' ) {
                    imagealphablending( $full, false );
                    imagesavealpha( $full, true );                
                } else {
                    imageinterlace( $full, true );
                }

                imagecopyresampled(
                    $full, $image, 0, 0, 0, 0,
                    $fullWidth, $fullHeight, $originalWidth, $originalHeight
                );

            }

            //populate the object's attributes
            $filename = sha1( IMAGE_FILENAME_SALT . time() )
                           .  '.' . $extension;

            //write out the thumbnail and full-sized images to the
            //specified locations
            $success = $this->_data->putImage( $thumb, THUMB_DIR,
                                               $filename, $extension );
            imagedestroy( $thumb );
            if ( $success === false ) {
                $this->_addValidationError(
                    'Could not put thumbnail in datastore.'
                );
            }

            if ( $fullResized ) {
                $success = $this->_data->putImage( $full, PIC_DIR,
                                                   $filename, $extension );
                imagedestroy( $full );
            } else {
                $success = $this->_data->putImage( $image, PIC_DIR,
                                                   $filename, $extension );
                imagedestroy( $image );
            }

            if ( $success === false ) {
                $this->_addValidationError(
                    'Could not put full image in datastore.'
                );
            }

            $this->_name = $filename;
            $this->_thumbAspectRatio = $thumbWidth / $thumbHeight;
            $this->_fullAspectRatio = $fullWidth / $fullHeight;

        }

        //return true on success or an array of validation errors on failure
        if ( $this->isValid() ) {
            return true;
        } else {
            return false;
        }

    }

    public function load( $name, $thumbAspectRatio, $fullAspectRatio ) {

        $this->_resetValidationErrors();

        if ( $name !== null ) {
            $this->_name = $this->_validateName( $name );
        }

        //if a thumbAspectRatio is provided, validate and use it
        if ( $thumbAspectRatio !== null ) {
            $this->_thumbAspectRatio = $this->_validateAspectRatio(
                $thumbAspectRatio
            );        
        }

        //if a fullAspectRatio is provided, validate and use it
        if ( $fullAspectRatio !== null ) {
            $this->_fullAspectRatio = $this->_validateAspectRatio(
                $fullAspectRatio
            );
        }

        return $this->isValid();//return true on success or an array of validation errors on failure
        /*if ( $this->isValid() ) {
            return true;
        } else {
            return false;
        }*/

    }

    public function getName() {

        return $this->_name;

    }

    public function getThumbAspectRatio() {

        return $this->_thumbAspectRatio;

    }

    public function getFullAspectRatio() {

        return $this->_fullAspectRatio;

    }

    public function isValid() {

        $errors = array();

        if ( $this->_name === null ) {
            $this->_addValidationError( 'Invalid image name.' );
        }

        if ( $this->_thumbAspectRatio === null ) {
            $this->_addValidationError( 'Invalid thumbnail aspect ratio.' );
        }

        if ( $this->_fullAspectRatio === null ) {
            $this->_addValidationError( 'Invalid full-sized aspect ratio.' );
        }

        //return true or false depending on the existence of errors
        if ( empty( $this->_validationErrors ) ) {
            return true;
        } else {
            return false;
        }

    }

    public function exportToDatastore() {

        $export = array(
            'name' => $this->_name,
            'thumbAspectRatio' => $this->_thumbAspectRatio,
            'fullAspectRatio' => $this->_fullAspectRatio
        );

        return $export;

    }

    public function exportToAPI() {

        $export = array(
            'name' => $this->_name,
            'thumbUrl' => BASE_PATH . 'media/thumb/' . $this->_name,
            'thumbAspectRatio' => $this->_thumbAspectRatio,
            'fullUrl' => BASE_PATH . 'media/full/' . $this->_name,
            'fullAspectRatio' => $this->_fullAspectRatio
        );

        return $export;

    }

    /***************************************************************************
    * Private methods                                                          *
    ***************************************************************************/

    private static function _validateName( $name ) {

        $cleanName = null;
        $name = Common::validateString( $name,
                                        48,
                                        false,
                                        false,
                                        false,
                                        false );
        if ( $name !== null ) {
            $cleanName = $name;
        }

        return $cleanName;

    }

    private static function _validateAspectRatio( $ratio ) {

        $cleanRatio = null;
        $ratio = Common::validateFloat( $ratio );

        if ( $ratio !== null && $ratio > 0 ) {
            $cleanRatio = $ratio;
        }

        return $cleanRatio;

    }

    private static function _validateOrientation( $orientation ) {

        $cleanOrientation = 1;

        switch ( Common::validateInt( $orientation ) ) {
            case 3:
                $cleanOrientation = 3;
                break;
            case 6:
                $cleanOrientation = 6;
                break;
            case 8:
                $cleanOrientation = 8;
                break;
            default:
                break;
        }

        return $cleanOrientation;

    }

    private function _reset() {

        $this->_name = null;
        $this->_thumbAspectRatio = null;
        $this->_fullAspectRatio = null;

    }

}