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

$id = Common::validateGlobalId(
  Common::extractPOSTValue( 'id' )
);

$code = Common::validateVoucherCode(
    Common::extractPOSTValue( 'code' )
);

$auth = new Auth( $datastore, $lang );

//user is authenticated at all?
if ( !$auth->checkAuthentication() ) {
    $auth->redirect( DASHBOARD_BASE_PATH );
    exit;
}

$account = $auth->getAccountInfo();

try {

    $voucher = $datastore->retrieveVoucher( $code );

    if ( $voucher !== null ) {

        $now = new DateTime( 'now' );
        
        $success = $datastore->redeemVoucher(
            $voucher['id'],
            $id,
            $voucher['serviceLevel'],
            $now->format( 'Y-m-d H:i:s' ),
            $voucher['serviceDuration']
        );

        if ( $success !== false ) {

            //for synchronous requests, redirect 
            //based on what type of object was created
            if ( $r->MIMEAcceptType() === 'text/html' ) {
                $r->setHTTPCode( 303 );
                $r->redirect(
                    DASHBOARD_REDIRECT_PATH . '/' . $id . '/?n=v_redeem_ok'
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
                echo 'Could not create account.';
            } else {
                Common::logError(
                    'Could not create account.',
                    $datastore
                );
                $r->setHTTPCode( 500 );
                $r->send( null );
            }
            exit;
        }

    } else {
        echo 'WRONG';
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

//(by this point, we should have already sent back the response and exited);
//hopefully this won't ever happen
header('HTTP/1.1 500 Internal Server Error');
header('Cache-Control: no-cache');
exit;
