<?php

if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {

    //only POST method allowed
    header( 'HTTP/1.1 400 Bad Request' );
    header( 'Cache-Control: no-cache' );
    exit;

}

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'HTTPResponse.php' );
require_once( LIB_DIR . 'Search.php' );

$lang = new Language();
$datastore = new Datastore( $lang );

$r = new HTTPResponse();
$r->setMIMEAcceptType( 'application/json' );

//validate position
$coordinates = Common::extractRequestCoordinates( 'GET' );

$position = new PositionComponent( $lang );
$position->create(
    $coordinates['lat'],
    $coordinates['lng']
);

if ( !$position->isValid() ) {
    $r->setHTTPCode( 400 );
    $r->send( null );
    exit;
}

$city = Common::validateCity(
    Common::extractGETValue( 'city' )
);

$page = Common::validateResultSetPage(
    Common::extractGETValue( 'page' )
);

$count = Common::validateResultSetCount(
    Common::extractGETValue( 'count' )
);

try {

    $results = Search::getResults( $city, $position, $datastore, $page, $count );

    Common::logActivity(
        $coordinates['lat'],
        $coordinates['lng'],
        $results['objects'],
        $datastore
    );

    if ( $results['pageCount'] > 0 ) {
        $r->setHTTPCode( 200 );
        $r->send( $results );
        exit;
    } else {
        $r->setHTTPCode( 204 );
        $r->send( null );
        exit;
    }

} catch ( Exception $e ) {
    if ( DEBUG ) {
        echo var_dump( $e->getMessage() );
    } else {
        Common::logError( $e->getMessage(), $datastore );
    }
    $r->setHTTPCode( 500 );
    $r->send( null );
    exit;
}

//(by this point, we should have already sent back the response and exited);
//hopefully this won't ever happen
header( 'HTTP/1.1 500 Internal Server Error' );
header( 'Cache-Control: no-cache' );
exit;
