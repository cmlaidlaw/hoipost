<?php

require_once( '../lib/Common.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( '../conf/LocationNodes.php' );

$dynamicNodes = Array(
    //latitude first
    -1 => Array(
        //then longitude
        -1 => Array(),
        1 => Array()
    ),
    //latitude first
    1 => Array(
        //then longitude
        -1 => Array(),
        1 => Array()
    )
);

foreach ( $nodes as $key => $value ) {
    $node = array(
        'lat' => sprintf( '%.2f', $value['lat'] ),
        'lng' => sprintf( '%.2f', $value['lng'] ),
        'name' => $value['city'] . ', ' . $value['country']
    );
    
    if ( $value['lat'] >= 0 ) {
        if ( $value['lng'] >= 0 ) {
            $dynamicNodes[1][1][] = $node;
        } else {
            $dynamicNodes[1][-1][] = $node;
        }
    } else {
        if ( $value['lng'] >= 0 ) {
            $dynamicNodes[-1][1][] = $node;
        } else {
            $dynamicNodes[-1][-1][] = $node;
        }    
    }
}

function sortDynamicNodes ( $a, $b ) {
    if ( $a['lat'] > $b['lat'] ) {
        return 1;
    } else if ( $a['lat'] < $b['lat'] ) {
        return -1;
    } else {
        return 0;
    }
}

usort( $dynamicNodes[-1][-1], 'sortDynamicNodes' );
usort( $dynamicNodes[-1][1], 'sortDynamicNodes' );
usort( $dynamicNodes[1][-1], 'sortDynamicNodes' );
usort( $dynamicNodes[1][1], 'sortDynamicNodes' );

$fh = fopen( LIB_DIR . 'etc/DynamicNodeList.txt', 'w' );

if ( $fh ) {
    if ( fwrite( $fh, serialize( $dynamicNodes ) ) ) {
        echo 'Complete.';
    } else {
        echo 'Error.';
    }
}

fclose($fh);