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

$lang = new Language();
$datastore = new Datastore( $lang );

$r = new HTTPResponse();
$r->setMIMEAcceptType( 'text/html' );

$success = false;
$id = null;

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

    if ( isset( $_POST['id'] ) ) {

            $id = Common::validateGlobalId(
                $_POST['id']
            );

            if ( $id !== null ) {

                $success = $datastore->upgradeEstablishmentServiceLevel( $id );

                $r->setHTTPCode( 303 );
                $r->redirect(
                    DASHBOARD_REDIRECT_PATH . $id . '/'
                );
                $r->send( null );
                exit;

            } else {
                //invalid request (id parameter missing)
                if ( DEBUG ) {
                    echo 'Could not upgrade Establishment service level.';
                } else {
                    $r->setHTTPCode( 400 );
                    $r->send( null );
                }
                exit;
            }

    } else {
        //invalid request (id parameter missing)
        if ( DEBUG ) {
            echo 'Could not upgrade Establishment service level.';
        } else {
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
