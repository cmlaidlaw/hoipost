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
require_once( LIB_DIR . 'obj/Object.php' );

$lang = new Language();
$datastore = new Datastore( $lang );

$r = new HTTPResponse();
$r->setMIMEAcceptType( 'application/json' );

$objectId = Common::validateGlobalId(
    Common::extractGETValue( 'id' )
);

if ( $objectId === null ) {
    $r->setHTTPCode( 404 );
    $r->send( null );
    exit;
}

$coordinates = Common::extractRequestCoordinates( 'GET' );

$position = new PositionComponent( $lang );
$position->create(
    $coordinates['lat'],
    $coordinates['lng']
);

try {

    $objData = $datastore->retrieveObject( $objectId );

    if ( $objData !== null ) {

        $obj = new Object( $datastore, $lang );
        $obj->load( $objData );
        $obj->getGeoRelationFrom( $position );

        if ( $obj->hasMessage() ) {
            $replyTo = $obj->getMessage()->getReplyTo();
            if ( $replyTo !== null ) {
                $replyToObjData = $datastore->retrieveObject( $replyTo );
                $replyToObj = new Object( $datastore, $lang );
                $replyToObj->load( $replyToObjData );
                $replyToObj->getGeoRelationFrom( $position );
                $obj->appendReplyTo( $replyToObj );
            }
        }

        if ( $obj->getType() === 1 ) {
            $obj->getPositionName();
        }

        try {
            $data = $obj->exportToAPI();
        } catch ( Exception $e ) {
            throw new Exception(
                '[' . $obj->getId() . '] ' . $e->getMessage()
            );
        }

        Common::logActivity(
            $coordinates['lat'],
            $coordinates['lng'],
            array( $data ),
            $datastore
        );

        $r->setHTTPCode( 200 );
        $r->send( $data );
        exit;

    } else {

        $r->setHTTPCode( 404 );
        $r->send( null );
        exit;

    }

} catch ( Exception $e ) {
    Common::logError( $e->getMessage() );
    $r->setHTTPCode( 500 );
    $r->send( null );
    exit;
}

//(by this point, we should have already sent back the response and exited);
//hopefully this won't ever happen
header( 'HTTP/1.1 500 Internal Server Error' );
header( 'Cache-Control: no-cache' );
exit;
