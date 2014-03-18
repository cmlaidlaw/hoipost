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
$isMessage = false;

try {

    if ( isset( $_POST['establishmentObjectId'] )
         && ( isset( $_POST['text'] )
              || ( (
                     isset( $_POST['image'] )
                     && isset( $_POST['orientation'] )
                   )
                   || isset( $_FILES['image'] ) ) )
         && isset( $_POST['startDate'] )
         && isset( $_POST['startHours'] )
         && isset( $_POST['startMinutes'] )
         && isset( $_POST['endDate'] )
         && isset( $_POST['endHours'] )
         && isset( $_POST['endMinutes'] )
         && isset( $_POST['repeatsWeekly'] ) ) {

        $isEvent = true;

        $establishmentObjectId = Common::validateGlobalId(
            Common::normalizeGlobalId(
                Common::extractPOSTValue( 'establishmentObjectId' )
            )
        );

        if ( $establishmentObjectId !== null ) {

            $auth = new Auth( $datastore, $lang );

            if ( $auth->checkAuthentication() ) {

                $account = $auth->getAccountInfo();

                $establishmentObject = $datastore->retrieveObject(
                    $establishmentObjectId
                );

                //check user is admin or owns the associated establishment
                if ( $account['admin'] === true
                     || $establishmentObject['id'] === $account['id'] ) {

                    //inherit lat and lng from the establishment object
                    $coordinates = array(
                        'lat' => $establishmentObject['position']['lat'],
                        'lng' => $establishmentObject['position']['lng']
                    );

                    $isAuthorized = true;

                }

            }

        }

    } else if (
         isset( $_POST['accountId'] )
         && isset( $_POST['city'] )
         && isset( $_POST['category'] )
         && isset( $_POST['name'] )
         && ( isset( $_POST['logo'] ) && isset( $_POST['orientation'] ) )
            || isset( $_FILES['logo'] )
         && isset( $_POST['description'] )
         && isset( $_POST['address'] )
         //ignore 'hours' for the moment
         && isset( $_POST['tel'] )
         && isset( $_POST['email'] )
         && isset( $_POST['url'] ) ) {

        $isEstablishment = true;

        $auth = new Auth( $datastore, $lang );

        if ( $auth->checkAuthentication() ) {

            $account = $auth->getAccountInfo();

            //check user is admin
            if ( $account['admin'] === true ) {

                $isAuthorized = true;
                //use the specified coordinates
                $coordinates = Common::extractRequestCoordinates( 'POST' );

            }

        }

    } else if (
	 ALLOW_USER_MESSAGES
         && isset( $_POST['text'] )
         && ( ( isset( $_POST['image'] ) && isset( $_POST['orientation'] ) )
              || isset( $_FILES['image'] ) ) ) {

        $isMessage = true;
        $parentObjectType = null;

        if ( isset( $_POST['replyTo'] ) ) {

            $parentObjectId = Common::validateGlobalId(
                Common::extractPOSTValue( 'replyTo' )
            );

            if ( $parentObjectId !== null ) {

                $parentObjectData = $datastore->retrieveObject( $parentObjectId );

                $parentObject = new Object( $datastore, $lang );
                $parentObject->load( $parentObjectData );

                if ( $parentObject->replyAllowed() ) {

                    //anonymous users are authorized to post messages
                    $isAuthorized = true;

                    //use the specified coordinates
                    $coordinates = Common::extractRequestCoordinates( 'POST' );

                    $parentObjectType = $parentObject->getType();

                }

            }

        } else {

            //anonymous users are authorized to post messages
            $isAuthorized = true;

            //use the specified coordinates
            $coordinates = Common::extractRequestCoordinates( 'POST' );

        }

    }

    if ( $isMessage || $isEstablishment || $isEvent ) {

        if ( $isAuthorized === true ) {

            $obj = new Object( $datastore, $lang );

            $success = $obj->create(
                $coordinates['lat'],
                $coordinates['lng']
            );

            if ( $success ) {

                if ( $isMessage ) {

                    if ( isset( $_POST['replyTo'] ) ) {

                        $success = $obj->createMessage(
                            Common::extractPOSTValue( 'text' ),
                            Common::extractPOSTImage( 'image' ),
                            $parentObjectId,
                            $parentObjectType
                        );

                    } else {

                        $success = $obj->createMessage(
                            Common::extractPOSTValue( 'text' ),
                            Common::extractPOSTImage( 'image' ),
                            null,
                            null
                        );

                    }

                } else if ( $isEstablishment ) {

                    $success = $obj->createEstablishment(
                        Common::extractPOSTValue( 'accountId' ),
                        Common::extractPOSTValue( 'city' ),
                        Common::extractPOSTValue( 'category' ),
                        Common::extractPOSTValue( 'name' ),
                        Common::extractPOSTImage( 'logo' ),
                        Common::extractPOSTValue( 'description' ),
                        Common::extractPOSTValue( 'address' ),
                        null, //ignore hours for now
                        Common::extractPOSTValue( 'tel' ),
                        Common::extractPOSTValue( 'email' ),
                        Common::extractPOSTValue( 'url' )
                    );

                } else if ( $isEvent ) {

                    $messageSuccess = $obj->createMessage(
                        Common::extractPOSTValue( 'text' ),
                        Common::extractPOSTImage( 'image' ),
                        Common::extractPOSTValue( 'replyTo' ),
                        null
                    );

                    $establishmentSuccess = $obj->loadEstablishment(
                        $establishmentObject['establishment']
                    );

                    $eventSuccess = $obj->createEvent(
                        $establishmentObject['id'],
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

                }

                if ( $success ) {

                    $ip = $_SERVER['REMOTE_ADDR'];

                    $id = $datastore->createObject( $obj, $ip );

                    if ( $id !== false ) {

                        //for synchronous requests, redirect 
                        //based on what type of object was created
                        if ( $r->MIMEAcceptType() === 'text/html' ) {
                            $r->setHTTPCode( 303 );
                            switch ( $obj->getType() ) {
                                case 1:
                                    $r->redirect( 'obj/' . $id . '/' );
                                    break;
                                case 2:
                                    $r->redirect(
                                        DASHBOARD_REDIRECT_PATH . $id . '/'
                                    );
                                    break;
                                case 6:
                                    $eventObject = $obj->getEvent();
                                    $r->redirect(
                                        DASHBOARD_REDIRECT_PATH
                                        . $eventObject->getEstablishmentObjectId()
                                        . '/'
                                    );
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
	    if ( ALLOW_USER_MESSAGES ) {
                Common::logError(
                    'Invalid object type. ['
                    . serialize( $_POST )
                    . ';' . serialize( $_FILES ) . ']',
                    $datastore
                );
	    }
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
