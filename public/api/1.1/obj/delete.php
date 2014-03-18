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

$isMessage = false;
$isEstablishment = false;
$isEvent = false;
$isAuthorized = false;

try {

    $id = Common::validateGlobalId(
        Common::normalizeGlobalId(
            Common::extractGETValue( 'id' )
        )
    );

    if ( $id !== null ) {

        $data = $datastore->retrieveObject( $id );

        if ( $data !== null ) {

            $obj = new Object( $datastore, $lang );
            $obj->load( $data );

            switch ( $obj->getType() ) {
                case 2:
                    $isMessage = true;
                    break;
                case 3:
                    $isEstablishment = true;
                    break;
                case 6:
                    $isEvent = true;
                    break;
            }

            $auth = new Auth( $datastore, $lang );

            if ( $auth->checkAuthentication() ) {

                $account = $auth->getAccountInfo();

                if ( ( $isEvent
                       && ( $account['admin'] === true
                            || $account['id'] === $obj['Establishment']['accountId']
                          ) )
                     || ( ( $isMessage || $isEstablishment )
                          && $account['admin'] === true ) ) {

                    $isAuthorized = true;

                }

            }

        }

    }

    if ( $isMessage || $isEstablishment || $isEvent ) {

        if ( $isAuthorized === true ) {

            //delete thumb/full images associated with the object
            switch ( $obj->getType() ) {
                case 1:
                case 6:
                    $message = $obj->getMessage();
                    $image = $message->getImage();
                    if ( $image !== null ) {
                        $datastore->deleteImage( $image->getName() );
                    }
                    break;
                case 2:
                    $Establishment = $obj->getEstablishment();
                    $logo = $Establishment->getLogo();
                    if ( $logo !== null ) {
                        $datastore->deleteImage( $logo->getName() );
                    }
                    break;
            }

            $success = $datastore->deleteObject( $obj );

            if ( $success !== false ) {

                //for synchronous requests, redirect 
                //based on what type of object was created
                if ( $r->MIMEAcceptType() === 'text/html' ) {
                    $r->setHTTPCode( 303 );
                    switch ( $obj->getType() ) {
                        case 1:
                            $r->redirect(
                                DASHBOARD_REDIRECT_PATH . '?n=m_delete_ok'
                            );
                            break;
                        case 2:
                            $r->redirect(
                                DASHBOARD_REDIRECT_PATH . '?n=b_delete_ok'
                            );
                            break;
                        case 6:
                            $eventObject = $obj->getEvent();
                            $r->redirect(
                                DASHBOARD_REDIRECT_PATH
                                . $eventObject->getEstablishmentObjectId()
                                . '/?n=e_delete_ok'
                            );
                            break;
                    }
                    $r->send( null );
                    exit;
                //for async, send back a 201 (created)
                } else {
                    $r->setHTTPCode( 200 );
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
        //object does not exist
        if  ( DEBUG ) {
            echo var_dump('Object does not exist.', $_POST, $_FILES );
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
