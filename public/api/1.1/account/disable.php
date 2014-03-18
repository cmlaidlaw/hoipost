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
$isBusiness = false;
$isEvent = false;

$auth = new Auth( $datastore, $lang );

//user is authenticated at all?
if ( !$auth->checkAuthentication() ) {
    $auth->redirect( DASHBOARD_BASE_PATH );
    exit;
}

$account = $auth->getAccountInfo();

//user is admin?
if ( $account['admin'] !== true ) {
    header( 'Location: ' . DASHBOARD_BASE_PATH );
    exit;
}

try {

    $id = Common::validateInt( Common::extractGETValue( 'id' ) );

    if ( $id !== null ) {

        $success = $datastore->disableAccount( $id );

        if ( $success !== false ) {

            //for synchronous requests, redirect 
            //based on what type of object was created
            if ( $r->MIMEAcceptType() === 'text/html' ) {
                $r->setHTTPCode( 303 );
                $r->redirect(
                    DASHBOARD_REDIRECT_PATH . '/?n=a_disable_ok'
                );
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
