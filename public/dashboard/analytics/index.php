<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'Language.php' );

$lang = new Language();
$datastore = new Datastore( $lang );

/*******************************************************************************
* Reject non-authenticated requests to this page                               *
*******************************************************************************/

require_once( LIB_DIR . 'Auth.php' );

$auth = new Auth( $datastore );

if ( !$auth->checkAuthentication() ) {
    $auth->redirect( DASHBOARD_BASE_PATH );
    exit;
}

//user is authenticated

$account = $auth->getAccountInfo();

$establishmentId = Common::validateGlobalId(
    Common::normalizeGlobalId(
        $_GET['id']
    )
);

$ok = false;

if ( $establishmentId !== null ) {

    if ( $account['admin'] === true ) {

        $ok = true;

    } else {

        $establishments = $datastore->retrieveAccountEstablishments(
            $account['id']
        );

        if ( count( $establishments ) > 0 ) {

            foreach ( $establishments as $establishment ) {

                if ( $establishment['id'] === $establishmentId ) {
                    $ok = true;
                }

            }

        }

    }

}

if ( $ok === true ) {

    //recurse log files, collecting data about the specified establishment

    require_once( LIB_DIR . 'Analytics.php' );

    $dir = new DirectoryIterator( LOG_DIR . 'activity/' );

    $results = array();

    foreach ( $dir as $fileinfo ) {

        if ( !$fileinfo->isDot() ) {

            $filename = $fileinfo->getFilename();

            $fp = fopen( LOG_DIR . 'activity/' . $filename, 'r' );


            $result = Analytics::scanLogFile(
                $fp,
                Common::denormalizeGlobalId( $establishmentId )
            );

            if ( $result !== false ) {
                $results[ substr( $filename, 0, -4 ) ] = $result;
            }

        }

    }

    //sort because the directory iterator apparently does not
    //operate in chronological or filename-lexical order
    ksort( $results );

    $totalViews = 0;
    echo 'Total days: ' . count($results) . '<br />';
    foreach ( $results as $day => $result ) {
        echo $day . ': ' . count( $result ) . ' views<br />';
        $totalViews += count( $result );
    }
    echo 'Total views: ' . $totalViews . '<br />';

}
