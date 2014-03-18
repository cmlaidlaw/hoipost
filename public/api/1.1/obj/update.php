<?php

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {

    //only POST method allowed
    header( 'HTTP/1.1 400 Bad Request' );
    header( 'Cache-Control: no-cache' );
    exit;

}

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'HTTPResponse.php' );
require_once( LIB_DIR . 'Auth.php' );
require_once( LIB_DIR . 'obj/Object.php' );

$lang = new Language();
$datastore = new Datastore( $lang );

$r = new HTTPResponse();
$r->setMIMEAcceptType( 'text/html' );

$success = false;
$id = false;

$isAuthorized = false;
$isEvent = false;
$isEstablishment = false;

try {

    $objectId = Common::validateGlobalId(
        Common::normalizeGlobalId(
            Common::extractGETValue( 'id' )
        )
    );

    if ( ( isset( $_POST['text'] )
           || ( ( isset( $_POST['image'] ) && isset( $_POST['orientation'] ) )
                || isset( $_FILES['image'] ) )
           || isset ( $_POST['retainImage'] ) )
         && isset( $_POST['startDate'] )
         && isset( $_POST['startHours'] )
         && isset( $_POST['startMinutes'] )
         && isset( $_POST['endDate'] )
         && isset( $_POST['endHours'] )
         && isset( $_POST['endMinutes'] )
         && isset( $_POST['repeatsWeekly'] ) ) {

        $isEvent = true;

        if ( $objectId !== null ) {

            $auth = new Auth( $datastore );

            if ( $auth->checkAuthentication() ) {

                $account = $auth->getAccountInfo();

                $object = $datastore->retrieveObject(
                    $objectId
                );

                if ( isset( $object['event'] )
                     && isset( $object['event']['establishmentObjectId'] ) ) {

                    $establishmentObject = $datastore->retrieveObject(
                        $object['event']['establishmentObjectId']
                    );

                    //check object exists and user is authorized to update it
                    if ( $object !== null
                         && $establishmentObject !== null
                         && isset( $establishmentObject['position'] )
                         && isset( $establishmentObject['establishment'] )
                         && ( $account['admin'] === true
                         || $account['id'] === $establishmentObject['establishment']['accountId'] ) ) {

                        //inherit lat and lng from the establishment object
                        $coordinates = array(
                            'lat' => $establishmentObject['position']['lat'],
                            'lng' => $establishmentObject['position']['lng']
                        );

                        $isAuthorized = true;

                    }

                }

            }

        }

    } else if (
         isset( $_LAT['lat'] )
         && isset( $_LAT['lng'] )
         && isset( $_POST['name'] )
         && ( ( isset( $_POST['logo'] ) && isset( $_POST['orientation'] ) )
              || isset( $_FILES['logo'] ) )
              || isset ( $_POST['retainImage'] )
         && isset( $_POST['description'] )
         && isset( $_POST['address'] )
         //ignore 'hours' for the moment
         && isset( $_POST['tel'] )
         && isset( $_POST['email'] )
         && isset( $_POST['url'] ) ) {
              
        $isEstablishment = true;

        $auth = new Auth( $datastore );

        if ( $auth->checkAuthentication() ) {

            $account = $auth->getAccountInfo();

            $object = $datastore->retrieveObject(
                $objectId
            );

            $establishmentObject =& $object;
            $object['event'] =& $object['event'];

            //check object exists and user is authorized to update it
            if ( $establishmentObject !== null
                 && ( $account['admin'] === true
                 || $account['id'] === $establishmentObject['accountId'] ) ) {

                if ( $account['admin'] === true ) {
                    $coordinates = Common::extractRequestCoordinates( 'POST' );
                } else {
                    //inherit lat and lng from the establishment object
                    $coordinates = array(
                        'lat' => $object['position']['lat'],
                        'lng' => $object['position']['lng']
                    );
                }

                $isAuthorized = true;

            }

        }

    }

    if ( $isEstablishment || $isEvent ) {

        if ( $isAuthorized === true ) {

            $obj = new Object( $datastore, $lang );
            $obj->setId( $objectId );

            if ( $account['admin'] === true ) {
                $establishmentAccountId = Common::extractPOSTValue( 'accountId' );
                $establishmentCity = Common::extractPOSTValue( 'city' );
                $establishmentCategory = Common::extractPOSTValue( 'category' );
            } else {
                $establishmentAccountId = $establishmentObject['establishment']['accountId'];
                $establishmentCity = $establishmentObject['establishment']['city'];
                $establishmentCategory = $establishmentObject['establishment']['category'];
            }

            $success = $obj->create(
                $coordinates['lat'],
                $coordinates['lng']
            );

            if ( $success ) {

                if ( $isEvent ) {

                    if ( (int) Common::extractPOSTValue( 'retainImage' )
                         === 1 ) {
                        $image = $object['message']['image'];
                    } else {
                        $image = Common::extractPOSTImage( 'image' );
                        //delete the old thumb/full images
                        if ( $object['message']['image'] !== null ) {
                            $datastore->deleteImage(
                                $object['message']['image']['name']
                            );
                        }
                    }

                    $messageSuccess = $obj->createMessage(
                        Common::extractPOSTValue( 'text' ),
                        $image,
                        Common::extractPOSTValue( 'replyTo' ),
                        6
                    );

                    $establishmentSuccess = $obj->loadEstablishment(
                        $establishmentObject['establishment']
                    );

                    $eventSuccess = $obj->createEvent(
                        $object['event']['establishmentObjectId'],
                        Common::extractPOSTValue( 'startDate' ),
                        Common::extractPOSTValue( 'startHours' ),
                        Common::extractPOSTValue( 'startMinutes' ),
                        Common::extractPOSTValue( 'endDate' ),
                        Common::extractPOSTValue( 'endHours' ),
                        Common::extractPOSTValue( 'endMinutes' ),
                        (bool) Common::extractPOSTValue( 'repeatsWeekly' ),
                        Common::getTimeZone(
                            $establishmentObject['establishment']['city']
                        )
                    );

                    $success = ( $messageSuccess
                                 && $establishmentSuccess
                                 && $eventSuccess );

                } else if ( $isEstablishment ) {

                    if ( (int) Common::extractPOSTValue( 'retainImage' )
                         === 1 ) {
                        $logo = $establishmentObject['establishment']['logo'];
                    } else {
                        $logo = Common::extractPOSTImage( 'logo' );
                        if ( $establishmentObject['establishment']['logo'] !== null ) {
                            $datastore->deleteImage(
                                $establishmentObject['establishment']['logo']['name']
                            );
                        }
                    }

                    $success = $obj->createEstablishment(
                        $establishmentAccountId,
                        $establishmentCity,
                        $establishmentCategory,
                        Common::extractPOSTValue( 'name' ),
                        $logo,
                        Common::extractPOSTValue( 'description' ),
                        Common::extractPOSTValue( 'address' ),
                        null, //ignore hours for now
                        Common::extractPOSTValue( 'tel' ),
                        Common::extractPOSTValue( 'email' ),
                        Common::extractPOSTValue( 'url' )
                    );

                }

                if ( $success ) {

                    $ip = $_SERVER['REMOTE_ADDR'];

                    $success = $datastore->updateObject( $obj );

                    if ( $success !== false ) {

                        //for synchronous requests, redirect 
                        //based on what type of object was created
                        if ( $r->MIMEAcceptType() === 'text/html' ) {
                            $r->setHTTPCode( 303 );
                            switch ( $obj->getType() ) {
                                case 2:
                                    $r->redirect(
                                        DASHBOARD_REDIRECT_PATH
                                        . $objectId . '/?n=b_update_ok'
                                    );
                                    break;
                                case 6:
                                    $r->redirect(
                                        DASHBOARD_REDIRECT_PATH
                                        . 'update/event/' . $objectId
                                        . '/?n=e_update_ok'
                                    );
                                    break;
                                default:
                                    break;
                            }
                            $r->send( null );
                            exit;
                        //for async, send back a 201 (created)
                        } else {
                            $r->setHTTPCode( 201 );
                            $r->send( null );
                            exit;
                        }

                    } else {
                        //datastore error
                        if ( DEBUG ) {
                            foreach ( $obj->getValidationErrors() as $error ) {
                                echo $error . '<br />';
                            }
                        } else {
                            Common::logError(
                                'Could not store object.',
                                $datastore
                            );
                            $r->setHTTPCode( 500 );
                            $r->send( null );
                        }
                        exit;
                    }
                } else {
                    //invalid object
                    if ( DEBUG ) {
                        foreach ( $obj->getValidationErrors() as $error ) {
                            echo $error . '<br />';
                        }
                    } else {
                        Common::logError(
                            'Could not create object. ['
                            . serialize( $_POST )
                            . ';' . serialize( $_FILES ) . ']',
                            $datastore
                        );
                        $r->setHTTPCode( 400 );
                        $r->send( null );
                    }
                    exit;
                }
            } else {
                //could not geolocate
                if ( DEBUG ) {
                    foreach ( $obj->getValidationErrors() as $error ) {
                        echo $error . '<br />';
                    }
                } else {
                    Common::logError(
                        'Position invalid. ['
                        . serialize( $_POST )
                        . ';' . serialize( $_FILES ) . ']',
                        $datastore
                    );
                    $r->setHTTPCode( 500 );
                    $r->send( null );
                }
                exit;
            }
        } else {
            //forbidden
            if ( DEBUG ) {
                echo var_dump( 'Forbidden.', $_POST, $_FILES );
            } else {
                Common::logError(
                    'Forbidden. ['
                    . serialize( $_POST )
                    . ';' . serialize( $_FILES ) . ']',
                    $datastore
                );
                $r->setHTTPCode( 403 );
                $r->send( null );
            }
            exit;
        }
    } else {
        //invalid object type
        if  ( DEBUG ) {
            echo var_dump('Invalid object type.', $_POST, $_FILES );
        } else {
            Common::logError(
                'Invalid object type. ['
                . serialize( $_POST )
                . ';' . serialize( $_FILES ) . ']',
                $datastore
            );
            $r->setHTTPCode( 400 );
            $r->send( null );
        }
        exit;
    }
} catch ( Exception $e ) {
    if ( DEBUG ) {
        echo $e->getMessage() . '<br />';
    } else {
        Common::logError( $e->getMessage(), $datastore );
        $r->setHTTPCode( 500 );
        $r->send( null );
    }
    exit;
}

//by this point, we should have already sent back the response and exited
//(hopefully this won't ever happen)
header( 'HTTP/1.1 500 Internal Server Error' );
header( 'Cache-Control: no-cache' );
exit;
