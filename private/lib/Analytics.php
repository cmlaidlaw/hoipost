<?php

class Analytics {

    public static function scanLogFile( &$fp, &$id ) {

        $results = array();

        while ( $line = fgets( $fp ) ) {

            if ( preg_match( '/"' . $id . '"/', $line ) === 1 ) {

                $results[] = Analytics::parseLogLine( $line );

            }

        }

        if ( count( $results ) === 0 ) {
            $results = false;
        }

        return $results;

    }

    public static function parseLogLine( $line ) {

        /*

        07:04:06 [69.71.164.229]
        GET : /api/1.1/search/?city=hk&page=1&count=18
        {
            "ua":"Mozilla\/5.0 (iPhone; CPU iPhone OS 6_1_4 like Mac OS X) AppleWebKit\/536.26 (KHTML, like Gecko) Version\/6.0 Mobile\/10B350 Safari\/8536.25",
            "ll":"51.527938,-0.088591",
            "id":
            [
                "382799806295318909",
                "382462606052827295",
                ...
                "130976206255346354"
            ]
        }

        */

        $pieces = explode( ' - ', $line );

        $json = json_decode( $pieces[2], true );

        return array(
            'time' => substr( $pieces[0], 0, 8 ),
            'ip' => substr( $pieces[0], 10, strlen( $pieces[0] ) - 11 ),
            'll' => $json['ll'],
            'ua' => $json['ua']
        );

    }

}
