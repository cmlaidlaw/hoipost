<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'Auth.php' );

$lang = new Language();
$datastore = new Datastore( $lang );
$auth = new Auth( $datastore, $lang );

if ( $auth->checkAuthentication() ) {

    $deauthenticated = $auth->deauthenticateAccount();

    if ( $deauthenticated ) {

        header('Location: ' . BASE_PATH . AUTH_SIGN_OUT_LANDING );
        exit;

    } else {

        header('Location: ' . ERROR_PATH );
        exit;

    }

} else {

    header('Location: ' . BASE_PATH . AUTH_SIGN_OUT_LANDING );
    exit;

}

//(by this point, we should have already sent back the response and exited);
//hopefully this won't ever happen
header( 'HTTP/1.1 500 Internal Server Error' );
header( 'Cache-Control: no-cache' );
exit;
