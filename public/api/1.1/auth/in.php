<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'HTTPResponse.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'Auth.php' );

$r = new HTTPResponse();
$r->setMIMEAcceptType( 'text/html' );

$lang = new Language();
$datastore = new Datastore( $lang );
$auth = new Auth( $datastore, $lang );

//check for a redirect after authentication
if ( isset( $_GET['next'] ) ) {
    $next = Common::validateRedirectPath( $_GET['next'] );
} else {
    $next = null;
}

//check authentication status
$authenticated = $auth->authenticateAccount(
    $_POST['email'],
    $_POST['password']
);

if ( $authenticated ) {

    Common::logMessage( 'User ' . $_POST['email'] . ' was authenticated.', $datastore );

    //redirect to the specified page, or the dashboard if no
    //destination is specified
    if ( $next !== null ) {
        $r->redirect( $next );
    } else {
        $r->redirect( AUTH_SIGN_IN_LANDING );
    }

    $r->send( null );
    exit;

} else {

    Common::logMessage( 'User ' . $_POST['email'] . ' failed authentication.', $datastore );

    //wait for five seconds to make brute-forcing more difficult
    sleep( 5 );

    //redirect back to sign-in form again, with the same destination
    if ( $next !== null ) {
        $next = Common::authRedirect( $next );
    } else {
        $r->redirect( AUTH_SIGN_IN_FORM );
    }

    $r->send( null );
    exit;

}
