<?php

$resources = array();
$items = array();

$requests = 0;
$searchRequests = 0;
$objectRequests = 0;
$miscRequests = 0;

$hits = 0;
$positions = array();
$uniquePositions = 0;
$ips = array();
$uniqueIps = 0;
$uas = array();
$uniqueUas = 0;

$path = '/var/www/hoipost-live/private/log/activity/';
$date = new DateTime( 'now' );
$date->sub( new DateInterval( 'P1D' ) );
$date = $date->format( 'Y-m-d' );

@$fp = fopen( $path . $date . '.log', 'r' );

if ( is_resource( $fp ) ) {

    while ( $line = fgets( $fp ) ) {
        list( $ipTime, $resource, $meta ) = explode( ' - ', $line );
        $time = substr( $ipTime, 0, 8 );
        $ip = substr( $ipTime, 10, -1 );

        //filter out requests coming in locally (i.e. siege)
        if ( $ip !== '198.199.113.77' ) {

            $meta = json_decode( $meta, true );
            $positions[] = $meta['ll'];
            $uas[] = $meta['ua'];
            $ips[] = $ip;
            if ( is_array( $meta['id'] ) ) {
                foreach ( $meta['id'] as $id ) {
                    if ( !isset( $items[$id] ) ) {
                        $items[$id] = array(
                            'hits' => 0,
                            'positions' => array()
                        );
                    }
                    $hits++;
                    $items[$id]['hits']++;
                    $items[$id]['positions'][] = $meta['ll'];
                }
            }
            $requests++;
            if ( substr( $resource, 6, 16 ) === '/api/1.1/search/' ) {
                $searchRequests++;
            } else if ( substr( $resource, 6, 13 ) === '/api/1.1/obj/' ) {
                $objectRequests++;
            } else {
                $miscRequests++;
            }
        }
    }

    fclose( $fp );

}

$uniquePositions = count( array_flip( array_flip( $positions ) ) );
$uniqueUas = count( array_flip( array_flip( $uas ) ) );
$uniqueIps = count( array_flip( array_flip( $ips ) ) );

$report = '<h1>Hoipost.com Daily Usage: ' . $date . '</h1>'
        . 'Total requests: ' . $requests . '<br /><br />'
        . 'Request type distribution:<br />'
        . '<table>'
        . '<tr><td>Search:</td><td>';

if ( $requests !== 0 ) {
    $report .= round( ( $searchRequests / $requests ) * 100 );
} else {
    $report .= '0';
}

$report .= '%</td></tr>'
         . '<tr><td>Object:</td><td>';

if ( $requests !== 0 ) {
    $report .= round( ( $objectRequests / $requests ) * 100 );
} else {
    $report .= '0';
}

$report  .= '%</td></tr>'
          . '<tr><td>Misc:</td><td>';

if ( $requests !== 0 ) {
    $report .= round( ( $miscRequests / $requests ) * 100 );
} else {
    $report .= '0';
}

$report .= '%</td></tr>'
         . '</table><br />'
         . 'Total items viewed: ' . $hits . '<br /><br />'
         . 'Unique positions: ' . $uniquePositions . '<br /><br />'
         . 'Unique IP addresses: ' . $uniqueIps . '<br /><br />'
         . 'Unique user agents: ' . $uniqueUas . '<br />'
         . '<br />'
         . '-end-'
         . "\n";

fwrite( STDOUT, $report );
//echo $report;
