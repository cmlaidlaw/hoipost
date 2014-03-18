<?php

require_once( __DIR__ . '/../conf/Config.php' );

class Common {

    /***************************************************************************
    * Input validation for atomic data types                                   *
    ***************************************************************************/

    public static function validateBoolean( $boolean ) {

        $cleanBoolean = null;

        if ( is_bool( $boolean ) ) {
            $cleanBoolean = (bool) $boolean;
        }

        return $cleanBoolean;

    }

    public static function validateInt( $int ) {

        $cleanInt = null;

        if ( filter_var( trim( $int ), FILTER_VALIDATE_INT ) !== false ) {
            $cleanInt = (int) trim( $int );
        }

        return $cleanInt;

    }

    public static function validateFloat( $float ) {

        $cleanFloat = null;

        if ( filter_var( trim( $float ), FILTER_VALIDATE_FLOAT ) !== false ) {
            $cleanFloat = (float) trim( $float );
        }

        return $cleanFloat;

    }

    public static function validateString( $string,
                                           $maxLength,
                                           $allowNewlines = true,
                                           $collapseNewlines = false,
                                           $collapseWhitespace = false ) {

        $cleanString = null;

        if ( mb_detect_encoding( $string, 'UTF-8', true ) ) {
            if ( mb_strlen( preg_replace( '/\s+/u', '', $string ) ) > 0 ) {
                if ( $allowNewlines ) {
                    //normalize with unix-style line breaks
                    $string = str_replace(
                        array( "\r\n", "\r" ),
                        "\n",
                        $string
                    );

                    if ( $collapseNewlines ) {
                        //replace multiple consecutive newlines
                        $string = preg_replace(
                            '/\n(\s*\n)+/',
                            "\n\n",
                            $string
                        );
                    }

                } else {
                    $string = str_replace(
                        array( "\r\n", "\r", "\n" ),
                        '',
                        $string
                    );
                }

                if ( $collapseWhitespace ) {
                    //replace all extra consecutive whitespace with a
                    //single space
                    $string = preg_replace(
                        '/[\s]*((?!\n)\s){2,}[\s]*/u',
                        ' ',
                        $string
                    );
                }

                //trim it if it's longer than the maximum allowed length
                if ( mb_strlen( $string ) > $maxLength ) {
                    $cleanString = mb_substr(
                        $string,
                        0,
                        $maxLength - 10,
                        'UTF-8'
                    );
                    $i = $maxLength - 10;
                    while ( $i < $maxLength ) {
                        $thisChar = mb_substr( $string, $i, 1 );
                        if ( $thisChar === '&' &&
                              ( $i > $maxLength - 5 ||
                                mb_substr( $string, $i, 5 ) !== '&amp;' ) ) {
                            break;
                        }
                        $cleanString .= $thisChar;
                        $i++;
                    }
                } else {
                    $cleanString = $string;
                }

            }
        }

        return $cleanString;

    }


    public static function encodeString( $string ) {

        return htmlspecialchars(
            $string,
            ENT_QUOTES,
            'UTF-8'
        );

    }

    public static function decodeString( $string ) {

        return html_entity_decode(
            $string,
            ENT_QUOTES,
            'UTF-8'
        );

    }

    /***************************************************************************
    * Input validation for business-use-specific data types                    *
    ***************************************************************************/

    public static function validateInternalId( $id ) {

        $cleanId = null;
        $id = preg_replace(
            '/[^0-9]+/',
            '',
            filter_var( $id, FILTER_SANITIZE_NUMBER_INT )
        );

        if ( strlen( $id ) > 0 && strlen( $id ) <= 20 ) {
            $cleanId = $id;
        }

        return $cleanId;

    }

    public static function validateActiveCity( $city ) {

        $activeCity = null;

        $activeCities = array(
            'hong kong',
            'guangzhou'
        );

        $city = strtolower( $city );

        if ( in_array( $city, $activeCities ) ) {
            $activeCity = $city;
        }

        return $activeCity;

    }

    public static function validateLatitude( $latitude ) {

        $cleanLatitude = null;
        $latitude = Common::validateFloat( $latitude );

        if ( $latitude !== null && $latitude >= -90 && $latitude <= 90 ) {
            $cleanLatitude = round( (float) $latitude, 6 );
        }

        return $cleanLatitude;

    }

    public static function validateLongitude( $longitude ) {

        $cleanLongitude = null;
        $longitude = Common::validateFloat( $longitude );

        if ( $longitude !== null && $longitude >= -180 && $longitude <= 180 ) {
            $cleanLongitude = round( (float) $longitude, 6 );
        }

        return $cleanLongitude;

    }

    public static function validateTel( $tel ) {

        $cleanTel = null;
        $tel = preg_replace(
            '/[^0-9]+/',
            '',
            filter_var( $tel, FILTER_SANITIZE_NUMBER_INT )
        );

        if ( strlen( $tel ) > 0 && strlen( $tel ) < TEL_MAX_LENGTH ) {
            $cleanTel = $tel;
        }

        return $cleanTel;

    }

    public static function validateEmail( $email ) {

        $cleanEmail = null;
        $email = trim( filter_var( trim( $email ), FILTER_SANITIZE_EMAIL ) );

        if ( strlen( $email ) <= EMAIL_MAX_LENGTH && strlen( $email ) > 0 ) {
            if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                $cleanEmail = $email;
            }
        }

        return $cleanEmail;

    }

    public static function validateURL( $url ) {

        $cleanUrl = null;
        $url = trim( filter_var( trim( $url ), FILTER_SANITIZE_URL ) );

        if ( strlen( $url ) <= URL_MAX_LENGTH && strlen( $url ) > 0 ) {
            $cleanUrl = trim( $url );
        }

        return $cleanUrl;

    }

    public static function validateCity( $city ) {

        $cleanCity = null;

        switch ( $city ) {
            case 'hk':
                $cleanCity = 'hong kong';
                break;
            case 'gz':
                $cleanCity = 'guangzhou';
                break;
            default:
                $cleanCity = 'hong kong';
                break;
        }

        return $cleanCity;

    }

    public static function validateTimeZone( $timeZone ) {

        $cleanTimeZone = null;

        switch ( $timeZone ) {
            case 'UTC':
                $cleanTimeZone = $timeZone;
                break;
            case 'Asia/Hong_Kong':
                $cleanTimeZone = $timeZone;
                break;
            default:
                break;
        }

        return $cleanTimeZone;

    }

    public static function validateVoucherCode( $code ) {

        $cleanCode = null;

        if ( strlen( $code ) === 7 ) {
            $valid = true;
            for ( $i = 0; $i < 7; $i++ ) {
                if ( strpos(
                        VOUCHER_CODE_CHARACTER_SET,
                        $code[$i] ) === false ) {
                    $valid = false;
                }
            }
            if ( $valid === true ) {
                $cleanCode = $code;
            }
        }

        return $cleanCode;

    }

    public static function validateHash( $hash ) {

        $cleanHash = null;

        if ( strlen( $hash ) >= 20 && strlen( $hash ) <= 60 ) {
            $cleanHash = $hash;
        }

        return $cleanHash;

    }

    public static function validateTimestamp( $timestamp ) {

        $cleanTimestamp = null;

        $timestamp = substr( (string) $timestamp, 0, 10 );
        $timestamp = (int) preg_replace( '/[^0-9]+/', '', $timestamp );

        if ( $timestamp > 0 ) {
            $cleanTimestamp = $timestamp;
        }

        return $cleanTimestamp;

    }

    public static function validateTIFFOrientation( $orientation ) {

        $cleanOrientation = null;
        $orientation = (int) preg_replace(
            '/[^0-9]+/',
            '',
            filter_var( $orientation, FILTER_SANITIZE_NUMBER_INT )
        );

        if ( $orientation > 0 && $orientation < 10 ) {
            $cleanOrientation = $orientation;
        }

        return $cleanOrientation;

    }

    public static function validateResultSetPage( $page ) {

        $cleanPage = 1;
        $page = Common::validateInt( $page );

        if ( is_int( $page )
             && ( $page > 0 )
             && ( $page < 10 ) ) {

            $cleanPage = $page;

        }

        return $cleanPage;

    }

    public static function validateResultSetCount( $count ) {

        $cleanCount = 1;
        $count = Common::validateInt( $count );

        if ( is_int( $count )
             && ( $count > 0 )
             && ( $count < 50 ) ) {

            $cleanCount = $count;

        }

        return $cleanCount;

    }

    /***************************************************************************
    * Global ID helpers                                                        *
    ***************************************************************************/

    //this will fail sometime around 2040(!) when time() - CUSTOM_EPOCH_OFFSET
    //rolls over on the 10th (high) digit
    public static function generateGlobalId( $type ) {

        $id = null;

        $time = time() - CUSTOM_EPOCH_OFFSET;

        $type = str_pad( $type, 2, '0', STR_PAD_LEFT );
        $rand = str_pad(
            (string) mt_rand( 0, 999999999 ),
            9,
            '0',
            STR_PAD_LEFT
        );

        if ( $time !== null && $type !== null && $rand !== null ) {
            $id = $time . $type . $rand;
        }

        return $id;

    }

    public static function validateGlobalId( $id ) {

        $cleanId = null;
        $id = Common::validateString(
            preg_replace( '/[^0-9]+/', '', $id ),
            20,
            false,
            false,
            false,
            false
        );

        if ( strlen( $id ) === 20 && preg_match( '/[^1-9]+/', $id ) === 1 ) {
            $cleanId = $id;
        }

        return $cleanId;

    }

    public static function normalizeGlobalId( $id ) {
        return str_pad( $id, 20, '0', STR_PAD_LEFT );
    }

    public static function denormalizeGlobalId( $id ) {
        return ltrim( $id, '0' );
    }

    public static function extractGlobalIdType( $id ) {
        return ltrim( substr( $id, 9, 2 ), '0' );
    }

    public static function cleanShortCode( $code ) {

        $cleanCode = null;

        $code = preg_replace( '/[^YU0-9ABCDEFGHTJKLMN]+/', '', strtoupper( $code ) );

        if ( strlen( $code ) === 4 ) {

            $cleanCode = (string) $code;

        }

        return $cleanCode;

    }

    /***************************************************************************
    * Request processing helpers                                               *
    ***************************************************************************/

    public static function extractGETValue( $key ) {

        $value = null;

        if ( isset( $_GET[$key] ) && mb_strlen( $_GET[$key] ) > 0 ) {
            $value = $_GET[$key];
        }

        return $value;

    }

    public static function extractPOSTValue( $key ) {

        $value = null;

        if ( isset( $_POST[$key] ) && mb_strlen( $_POST[$key] ) > 0 ) {
            $value = $_POST[$key];
        }

        return $value;

    }

    public static function extractPOSTImage( $key ) {

        require_once( LIB_DIR . 'Datastore.php' );

        $image = null;
        $resource = null;
        $MIMEtype = null;
        $extension = null;
        $orientation = null;

        //check for inline images appended to the form itself
        if ( isset( $_POST[$key] ) && isset( $_POST['orientation'] ) ) {

            $base64Data = explode( ',', $_POST[$key] );
            $MIMEtype = explode( ';', $base64Data[0] );
            $MIMEtype = explode( ':', $MIMEtype[0] );
            $MIMEtype = $MIMEtype[1];
            $resource = imagecreatefromstring(
                base64_decode( $base64Data[1], true )
            );
            $orientation = Common::validateTIFFOrientation(
                $_POST['orientation']
            );

        } else if ( isset( $_FILES[$key] )
                    && is_uploaded_file( $_FILES[$key]['tmp_name'] ) ) {

            if ( $_FILES[$key]['error'] === UPLOAD_ERR_OK ) {

                $MIMEtype = $_FILES[$key]['type'];

                switch ( $MIMEtype ) {
                    //fix for IE8 uploads having a non-standard MIME type
                    case 'image/pjpeg':
                    case 'image/jpg':
                        $MIMEtype = 'image/jpeg';
                    case 'image/jpeg':
                        $resource = imagecreatefromjpeg(
                            $_FILES[$key]['tmp_name']
                        );
                        break;
                    case 'image/png':
                        $resource = imagecreatefrompng(
                            $_FILES[$key]['tmp_name']
                        );
                        break;
                    case 'image/gif':
                        $resource = imagecreatefromgif(
                            $_FILES[$key]['tmp_name']
                        );
                        break;
                    default:
                        break;
                }

                $orientation = 1;

            } else {

                switch ( $_FILES[$key]['error'] ) {

                    case UPLOAD_ERR_INI_SIZE:
                        $pictureError = 'The uploaded file exceeds the '
                                        . 'upload_max_filesize directive in '
                                        . 'php.ini.';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $pictureError = 'The uploaded file exceeds the '
                                        . 'MAX_FILE_SIZE directive that was '
                                        . 'specified in the HTML form.';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $pictureError = 'The uploaded file was only partially '
                                        . 'uploaded.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $pictureError = 'No file was uploaded.';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $pictureError = 'Missing a temporary folder.';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $pictureError = 'Failed to write file to disk.';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $pictureError = 'File upload stopped by extension.';
                        break;
                    default:
                        $pictureError = 'Unknown upload error.';
                        break;

                }

                Common::logError(
                    'Common::extractPOSTImage(): ' . $pictureError,
                    new Datastore()
                );

            }

        }

        switch ( $MIMEtype ) {

            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
            default:
                break;

        }

        if ( is_resource( $resource )
             && $extension !== null
             && $orientation !== null ) {

            $image = array(
                'resource' => $resource,
                'extension' => $extension,
                'orientation' => $orientation
            );

        }

        return $image;

    }

    public static function extractQueryStringCoordinates() {

        $coordinates = array(
            'lat' => null,
            'lng' => null,
            'lastUpdate' => null
        );

        $latLng = Common::extractGETValue( 'll' );

        if ( $latLng !== null && strpos( $latLng, ',' ) !== false ) {
            $thisLatLng = explode( ',', $latLng );
            $coordinates['lat'] = Common::validateLatitude( $thisLatLng[0] );
            $coordinates['lng'] = Common::validateLongitude( $thisLatLng[1] );
        }

        $coordinates['lastUpdate'] = Common::validateInt(
            Common::extractGETValue( 'u' )
        );

        if ( $coordinates['lat'] === null
             || $coordinates['lng'] === null
             || $coordinates['lastUpdate'] === null ) {
            $coordinates = null;
        }

        return $coordinates;

    }

    public static function extractRequestCoordinates( $requestType ) {

        $coordinates = array(
            'lat' => null,
            'lng' => null
        );

        if ( $requestType === 'GET' ) {

            $latLng = Common::extractGETValue( 'll' );

            if ( $latLng !== null && strpos( $latLng, ',' ) !== false ) {
                $thisLatLng = explode( ',', $latLng );
                $coordinates['lat'] = Common::validateLatitude(
                    $thisLatLng[0]
                );
                $coordinates['lng'] = Common::validateLongitude(
                    $thisLatLng[1]
                );
            }

        } else if ( $requestType === 'POST' ) {

            if ( isset( $_POST['lat'] ) && isset( $_POST['lng'] ) ) {
                $coordinates['lat'] = Common::validateLatitude(
                    $_POST['lat']
                );
                $coordinates['lng'] = Common::validateLongitude(
                    $_POST['lng']
                );
            }

        }

        if ( $coordinates['lat'] === null || $coordinates['lng'] === null ) {

            $geolocated = Common::geolocateFromIP(
                $_SERVER['REMOTE_ADDR']
            );

            if ( $geolocated !== null ) {

                $coordinates['lat'] = (float) $geolocated['lat'];
                $coordinates['lng'] = (float) $geolocated['lng'];

            } else {

                $coordinates['lat'] = DEFAULT_LATITUDE;
                $coordinates['lng'] = DEFAULT_LONGITUDE;

            }

        }

        return $coordinates;

    }

	//
	// THIS CALL WILL FAIL: The IP-geolocation module is not included
	//
    public static function geolocateFromIP( $address ) {

        $coordinates = null;

        return $coordinates;

    }

    /***************************************************************************
    * Date and time handling                                                   *
    ***************************************************************************/

    public static function formatTimeRelation( $a, $b, $short = false,
                                               &$lang ) {

        $difference = $a - $b;

        if ( $short ) {
            $label = '_SHORT_LABEL';
        } else {
            $label = '_LABEL';
        }

        if ( $difference > 63072000 ) {
            $formatted = floor( $difference / 31536000 )
                       . $lang->get( 'TIME_YEARS_AGO' . $label );
        } else if ( $difference > 31536000 ) {
            $formatted = 1 . $lang->get( 'TIME_YEAR_AGO' . $label );
        } else if ( $difference > 172800 ) {
            $formatted = floor( $difference / 86400 )
                       . $lang->get( 'TIME_DAYS_AGO' . $label );
        } else if ( $difference > 86400 ) {
            $formatted = 1 . $lang->get( 'TIME_DAY_AGO' . $label );
        } else if ( $difference > 7200 ) {
            $formatted = floor( $difference / 3600 )
                       . $lang->get( 'TIME_HOURS_AGO' . $label );
        } else if ( $difference > 3600 ) {
            $formatted = 1 . $lang->get( 'TIME_HOUR_AGO' . $label );
        } else if ( $difference > 120 ) {
            $formatted = floor( $difference / 60 )
                       . $lang->get( 'TIME_MINUTES_AGO' . $label );
        } else if ( $difference > 60 ) {
            $formatted = 1 . $lang->get( 'TIME_MINUTE_AGO' . $label );
        } else {
            $formatted = $lang->get( 'TIME_LESS_THAN_ONE_MINUTE_AGO' . $label );
        }

        return $formatted;

    }

    public static function getNextOccurrence( $dateTime, $recurring,
                                              $returnTimestamp = true ) {

        $nextOccurrence = null;

        if ( $recurring ) {

            $original = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $dateTime
            );

            $now = new DateTime( 'now' );

            //figure out if we need to use the natural startTimestamp or
            //the next recurring one based on a 1 week interval
            $diff = $now->diff( $original )->format( '%R%a' );

            //if the offset to the event is positive (it's in the future),
            //then use the natural startTime
            if ( substr( $diff, 0, 1 ) !== '-' ) {

                if ( $returnTimestamp ) {
                    $nextOccurrence = $original->getTimestamp();
                } else {
                    $nextOccurrence = $original;
                }

            //otherwise, add the appropriate number of weeks to the original
            //dateTime so that the new starting time is within 7 days of now
            } else {

                $weekOffset = (int) floor(
                    abs(
                        (int) substr(
                            $diff,
                            1,
                            strlen( $diff )
                        )
                    ) / 7
                ) + 1;

                if ( $returnTimestamp ) {

                    $nextOccurrence = $original->add(
                        new DateInterval( 'P' . $weekOffset . 'W' )
                    )->getTimestamp();

                } else {

                    $nextOccurrence = $original->add(
                        new DateInterval( 'P' . $weekOffset . 'W' )
                    );

                }

            }

        } else {

            if ( $returnTimestamp ) {

                $nextOccurrence = DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $dateTime
                )->getTimestamp();

            } else {

                $nextOccurrence = DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $dateTime
                );

            }
        }

        return $nextOccurrence;

    }

    public static function getTimeZone( $city ) {
        $timeZone = null;
        switch ( $city ) {
            case 'hong kong':
                $timezone = 'Asia/Hong_Kong';
                break;
            case 'guangzhou':
                $timeZone = 'Asia/Hong_Kong';
                break;
            default:
                break;
        }
        return $timeZone;
    }

    public static function toLocalDateTime( $timeString, $city ) {

        $local = array( 'date' => null, 'hours' => null, 'minutes' => null );

        $time = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $timeString,
            new DateTimeZone( 'UTC' )
        );

        $localTimeZone = new DateTimeZone( HoipostData::getTimeZone( $city ) );

        $time->setTimeZone( $localTimeZone );

        //Mon Mar 11 2013
        $local['date'] = $time->format( 'D M d Y' );
        $local['hours'] = $time->format( 'H' );
        $local['minutes'] = $time->format( 'i' );
        return $local;
    }


    /***************************************************************************
    * Dynamic node handling                                                    *
    ***************************************************************************/

    public static function loadDynamicNodeList( $filename ) {
        $fh = fopen( $filename, 'r' );
        $nodes = unserialize( fread( $fh, filesize( $filename ) ) );
        fclose( $fh );
        return $nodes;
    }

    public static function iterateNodes( $nodes, $start, $end, $lat, $lng ) {

        $half = floor( ( $end - $start ) / 2 );
        $quarter = floor( $half / 2 );
        $eighth = floor( $half / 4 );
        $sixteenth = floor( $half / 8 );

        //test midpoint
        if ( $lat < (float) $nodes[$half]['lat'] ) {
            //test midpoint again
            if ( $lat < (float) $nodes[$quarter]['lat'] ) {
                //test midpoint again
                if ( $lat < (float) $nodes[$eighth]['lat'] ) {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$sixteenth]['lat'] ) {
                        $start = $start;// 0
                        $end = $sixteenth - 1;// 1/16
                    } else {
                        $start = $sixteenth;// 1/16
                        $end = $eighth - 1;// 2/16
                    }
                } else {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$eighth + $sixteenth]['lat'] ) {
                        $start = $eighth;//2/16
                        $end = $eighth + $sixteenth - 1;//3/16
                    } else {
                        $start = $eighth + $sixteenth;// 3/16
                        $end = $quarter - 1;// 4/16
                    }
                }
            } else {
                //test midpoint again
                if ( $lat < (float) $nodes[$quarter + $eighth]['lat'] ) {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$quarter + $sixteenth]['lat'] ) {
                        $start = $quarter;//4/16
                        $end = $quarter + $sixteenth - 1;//5/16
                    } else {
                        $start = $quarter + $sixteenth;//5/16
                        $end = $quarter + $eighth - 1;//6/16
                    }
                } else {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$quarter + $eighth + $sixteenth]['lat']
                        ) {
                        $start = $quarter + $eighth;//6/16
                        $end = $quarter + $eighth + $sixteenth - 1;//7/16
                    } else {
                        $start = $quarter + $eighth + $sixteenth;//7/16
                        $end = $half - 1;//8/16
                    }
                }
            }
        } else {
            //test midpoint again
            if ( $lat < (float) $nodes[$half + $quarter]['lat'] ) {
                //test midpoint again
                if ( $lat < (float) $nodes[$half + $eighth]['lat'] ) {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$half + $sixteenth]['lat'] ) {
                        $start = $half;// 8/16
                        $end = $half + $sixteenth - 1;// 9/16
                    } else {
                        $start = $half + $sixteenth;// 9/16
                        $end = $half + $eighth - 1;// 10/16
                    }
                } else {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$half + $eighth + $sixteenth]['lat'] ) {
                        $start = $half + $eighth;//10/16
                        $end = $half + $eighth + $sixteenth - 1;//11/16
                    } else {
                        $start = $half + $eighth + $sixteenth;// 11/16
                        $end = $half + $quarter - 1;// 12/16
                    }
                }
            } else {
                //test midpoint again
                if ( $lat < (float) $nodes[$half + $quarter + $eighth]['lat'] ) {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$half + $quarter + $sixteenth]['lat'] ) {
                        $start = $half+ $quarter;//12/16
                        $end = $half + $quarter + $sixteenth - 1;//13/16
                    } else {
                        $start = $half + $quarter + $sixteenth;//13/16
                        $end = $half + $quarter + $eighth - 1;//14/16
                    }
                } else {
                    //test midpoint again
                    if ( $lat < (float) $nodes[$half + $quarter + $eighth
                          + $sixteenth]['lat'] ) {
                        $start = $half + $quarter + $eighth;//14/16
                        $end = $half + $quarter + $eighth + $sixteenth - 1;
                        //15/16
                    } else {
                        $start = $half + $quarter + $eighth + $sixteenth;//15/16
                        $end = $end;//16/16
                    }
                }
            }
        }

        //cache user's lat and lng in radians here so we don't have to
        //compute it on every iteration of sphericalLawOfCosines
        $rLat = deg2rad(  $lat  );
        $rLng = deg2rad(  $lng  );

        $bestDistance = Common::sphericalLawOfCosines(
            $lat,
            $lng,
            (float) $nodes[$start]['lat'],
            (float) $nodes[$start]['lng'],
            $rLat,
            $rLng
        );
        $thisDistance = false;
        $best = $start;

        //no need to calculate the haversine for the starting value twice, so
        //increment it before the loop
        $start = $start + 1;

        for ($i = $start; $i <= $end; $i++) {

            //calc distance between user position and node position
            $thisDistance = Common::sphericalLawOfCosines(
                $lat,
                $lng,
                (float) $nodes[$i]['lat'],
                (float) $nodes[$i]['lng'],
                $rLat,
                $rLng
            );

            if ($thisDistance < $bestDistance) {
                $bestDistance = $thisDistance;
                $best = $i;
            }

        }

        return array(
            'distance' => $bestDistance,
            'lat' => (float) $nodes[$best]['lat'],
            'lng' => (float) $nodes[$best]['lng'],
            'name' => $nodes[$best]['name']
        );

    }

    public static function sphericalLawOfCosines( $lat1, $lng1, $lat2, $lng2,
                                                  $rLat1 = false,
                                                  $rLng1 = false ) {

        //mean radius of Earth is 6,371.009 km
        $radius = 6371;

        if ( !$rLat1 ) {
            $rLat1 = deg2rad(  $lat1  );
        }

        if ( !$rLng1 ) {
            $rLng1 = deg2rad(  $lng1  );
        }

        $rLat2 = deg2rad(  $lat2  );
        $rLng2 = deg2rad(  $lng2  );

        return  $radius *
                acos(
                    sin($rLat1) *
                    sin($rLat2) +
                    cos($rLat1) *
                    cos($rLat2) *
                    cos($rLng2 - $rLng1)
                );

    }

    public static function getNearestActiveCity( $lat, $lng ) {

        $activeCities = array(
            'hong kong' => array( 'lat' => 22.27, 'lng' => 114.14 ),
            'guangzhou' => array( 'lat' => 23.12, 'lng' => 113.25 )
        );

        $nearestDistance = 100000;
        $nearestCity = false;

        foreach ( $activeCities as $cityName => $cityPosition ) {

            $distance = Common::sphericalLawOfCosines(
                $lat,
                $lng,
                $activeCities[$cityName]['lat'],
                $activeCities[$cityName]['lng'],
                false,
                false
            );

            if ( $distance < $nearestDistance ) {

                $nearestDistance = $distance;
                $nearestCity = $cityName;

            }

        }

        return $nearestCity;

    }

    public static function getActiveCityCode( $city ) {

        $cityCode = false;

        switch ( $city ) {
            case 'hong kong':
                $cityCode = 'hk';
                break;
            case 'guangzhou':
                $cityCode = 'gz';
                break;
        }

        return $cityCode;

    }

    public static function getNearestNode( $nodes, $lat, $lng ) {

        if ( $lat >= 0 ) {
            $latSlice = 1;
            if ( $lng >= 0 ) {
                $lngSlice = 1;
            } else {
                $lngSlice = -1;
            }
        } else {
            $latSlice = -1;
            if ( $lng >= 0 ) {
                $lngSlice = 1;
            } else {
                $lngSlice = -1;
            }
        }

        $count = count( $nodes[$latSlice][$lngSlice] );
        $start = floor( $count / 2 );

        if ( (float) $nodes[$latSlice][$lngSlice][$start]['lat'] >= $lat ) {
            return Common::iterateNodes(
                $nodes[$latSlice][$lngSlice],
                0,
                $start,
                $lat,
                $lng
            );
        } else {
            return Common::iterateNodes(
                $nodes[$latSlice][$lngSlice],
                $start,
                $count,
                $lat,
                $lng
            );
        }

    }

    public static function exportSearchCenterPoints( $city ) {

        switch ( $city ) {

            case 'hong kong':
                echo json_encode(
                    array()
                );
                break;

            case 'guangzhou':
                echo json_encode(
                    array(
                        'GZIFC West Tower (Zhujiang New Town)' => array( 'lat' => 23.120371, 'lng' => 113.317968 ),
                        'Zhujiang Park, West Gate (Zhujiang New Town)' => array( 'lat' => 23.123153, 'lng' => 113.328450 ),
                        'Huacheng Square, North (Zhujiang New Town)' => array( 'lat' => 23.129192, 'lng' => 113.319331 ),
                        'ZJNT Metro Station, A2 Exit (Zhujiang New Town)' => array( 'lat' => 23.122473, 'lng' => 113.314551 ),
                        'CITIC Plaza (Tianhe)' => array( 'lat' => 23.144392, 'lng' => 113.319298 ),
                        'Tianhe Stadium (Tianhe)' => array( 'lat' => 23.140597, 'lng' => 113.319288 ),
                        'Grandview Mall (Tianhe)' => array( 'lat' => 23.134874, 'lng' => 113.321638 ),
                        'Guangzhou East Train Station (Tianhe)' => array( 'lat' => 23.151961, 'lng' => 113.319369 ),
                        'Haizhu Square (Haizhu)' => array( 'lat' => 23.117270, 'lng' => 113.260659 ),
                        'Xinghai Concert Hall (Ersha Island)' => array( 'lat' => 23.110327, 'lng' => 113.300144 ),
                        'Garden Hotel (Yuexiu)' => array( 'lat' => 23.138293, 'lng' => 113.280907 ),
                        'White Swan Hotel (Shamian Island)' => array( 'lat' => 23.108516, 'lng' => 113.237492 )
                    )
                );
                break;

            default:
                echo json_encode(
                    array()
                );
                break;

        }

    }


    /***************************************************************************
    * Metrics and logging                                                      *
    ***************************************************************************/

    public static function logActivity( $lat, $lng, $objects, &$datastore ) {

        $ids = array();

        if ( count( $objects ) > 0 ) {

            foreach ( $objects as $object ) {

                $ids[] = Common::denormalizeGlobalId( $object['id'] );

                if ( isset ( $object['message'] ) ) {

                    if ( isset( $object['message']['replyTo'] )
                         && is_array( $object['message']['replyTo'] ) ) {

                        $ids[] = Common::denormalizeGlobalId(
                            $object['message']['replyTo']['id']
                        );

                    }

                }

            }

        }

        $params = array(
            'ua' => $_SERVER['HTTP_USER_AGENT'],
            'll' => $lat . ',' . $lng,
            'id' => $ids
        );

        $line = date( 'H:i:s' ) . ' [' . $_SERVER['REMOTE_ADDR'] . '] - ';
        $line .= $_SERVER['REQUEST_METHOD'] . ' : ' . $_SERVER['REQUEST_URI'];
        $line .= ' - ' . json_encode( $params );

        try {

            $success = $datastore->log( 'activity', $line );

        } catch ( Exception $e ) {
            throw $e;
        }

        return $success;

    }

    public static function logMessage( $message, $datastore ) {

        $success = false;

        try {

            $line = date( 'Y-m-d H:i:s' )
                  . ' [' . $_SERVER['REMOTE_ADDR'] . '] - '
                  . $_SERVER['REQUEST_METHOD'] . ' : '
                  . $_SERVER['REQUEST_URI']
                  . ' - ' . $message;

            $success = $datastore->log( 'message', $line );

        } catch ( Exception $e ) {

            throw $e;

        }

        return $success;

    }
    
    public static function logError( $message, $datastore ) {

        $success = false;

        try {

            $line = date( 'Y-m-d H:i:s' )
                  . ' [' . $_SERVER['REMOTE_ADDR'] . '] - '
                  . $_SERVER['REQUEST_METHOD'] . ' : '
                  . $_SERVER['REQUEST_URI']
                  . ' - ' . $message;

            $success = $datastore->log( 'error', $line );

        } catch ( Exception $e ) {

            throw $e;

        }

        return $success;

    }


    /***************************************************************************
    * Authentication                                                           *
    ***************************************************************************/

    public static function hash( $password ) {

        $hashedPassword = null;

        try {

            require_once( LIB_DIR . 'PasswordHash.php' );

            $hasher = new PasswordHash( 8, false );
            $hash = $hasher->HashPassword( $password );
            if ( strlen( $hash ) >= 20 ) {
                $hashedPassword = $hash;
            }
            unset( $hasher );

        } catch ( Exception $e ) {

            throw $e;

        }

        return $hashedPassword;

    }

    public static function checkHash( $password, $hash ) {

        $success = false;

        try {

            require_once( LIB_DIR . 'PasswordHash.php' );

            $hasher = new PasswordHash( 8, false );
            if ( $hasher->CheckPassword( $password, $hash ) ) {
                $success = true;
            }
            unset( $hasher );

        } catch ( Exception $e ) {

            throw $e;
        
        }

        return $success;

    }


    /***************************************************************************
    * Payment vouchers and service level bookkeeping                           *
    ***************************************************************************/

    public static function generateVoucherCode() {

        $characters = VOUCHER_CODE_CHARACTER_SET;
        $length = strlen( $characters ) - 1;
        $code = '';

        for ( $i = 0; $i < VOUCHER_CODE_LENGTH; $i++ ) {
            $code .= $characters[mt_rand( 0, $length )];
        }

        return $code;

    }

    public static function getServiceLevelMaxEvents( $serviceLevel ) {

        switch ( $serviceLevel ) {

            case 1:
                $max = SERVICE_LEVEL_1_MAX_EVENTS;
                break;

            case 2:
                $max = SERVICE_LEVEL_2_MAX_EVENTS;
                break;

            default:
                $max = SERVICE_LEVEL_0_MAX_EVENTS;
                break;

        }

        return $max;

    }


    /***************************************************************************
    * Localization                                                             *
    ***************************************************************************/

    public static function loadLocalization( $code ) {

        $thisLocalization = null;

        if ( file_exists( PRIVATE_DIR . 'conf/lang/' . $code . '.php' ) ) {

            require_once( PRIVATE_DIR . 'conf/lang/' . $code . '.php' );
            $thisLocalization = $localization;
            unset( $localization );

        }

        return $thisLocalization;

    }

}
