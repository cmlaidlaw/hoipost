<?php

require_once( LIB_DIR . 'obj/Object.php' );

class Search {

    public static function getResults( &$city, &$position, &$datastore, &$page,
                                       &$count ) {

        $offsets = array(
            'categories' => array(
                'messages' => null,
                'establishments' => array(
                    'totalObjects' => $datastore->retrieveEstablishmentObjectCount( $city ),
                    'weight' => 0.4
                ),
                'events' => array(
                    'totalObjects' => $datastore->retrieveEventObjectCount( $city ),
                    'weight' => 0.6
                )
            )
        );

        if ( ALLOW_USER_MESSAGES ) {
            $offsets['categories']['messages'] = array(
                'totalObjects' => $datastore->retrieveMessageObjectCount( $city ),
                'weight' => 0.3
            );
            $offsets['categories']['establishments']['weight'] = 0.3;
            $offsets['categories']['events']['weight'] = 0.4;
        }

        $totalObjects = 0;

        foreach ( $offsets['categories'] as $offset ) {
            if ( isset( $offset['totalObjects'] ) ) {
                    $totalObjects += $offset['totalObjects'];
            }
        }

        $totalPages = (int) ceil( $totalObjects / $count );

        if ( $page > $totalPages ) {
            return null;
        }

        $offsets = Search::calculateOffsets( $page, $count, $offsets );

        if ( $offsets === null ) {
            return null;
        }

        if ( isset( $offsets['lastPage'] )
             && $offsets['lastPage'] === true ) {
            $lastPage = true;
        }

        $offsets = $offsets['categories'];

        $scores = array();

        if ( $offsets['messages'] !== null ) {

            $messages = $datastore->searchMessages(
                $offsets['messages']['startOffset'],
                $offsets['messages']['actualPageCount']
            );

            for ( $i = 0;
                  $i < $offsets['messages']['actualPageCount'];
                  $i++ ) {
                $messages[$i]->getPositionName();
                $scores[] = array(
                    'id' => $messages[$i]->getId(),
                    'obj' => $messages[$i],
                    'score' => Search::calculateScore(
                        $position,
                        $messages[$i]
                    )
                );

            }

        }

        if ( $offsets['establishments'] !== null ) {

            $establishments = $datastore->searchEstablishments(
                $position,
                $city,
                $offsets['establishments']['startOffset'],
                $offsets['establishments']['actualPageCount']
            );

            for ( $i = 0;
                  $i < $offsets['establishments']['actualPageCount'];
                  $i++ ) {

                $scores[] = array(
                    'id' => $establishments[$i]->getId(),
                    'obj' => $establishments[$i],
                    'score' => Search::calculateScore(
                        $position,
                        $establishments[$i]
                    )
                );

            }

        }

        if ( $offsets['events'] !== null ) {

            $events = $datastore->searchEvents(
                $city,
                $offsets['events']['startOffset'],
                $offsets['events']['actualPageCount']
            );

            for ( $i = 0;
                  $i < $offsets['events']['actualPageCount'];
                  $i++ ) {

                $scores[] = array(
                    'id' => $events[$i]->getId(),
                    'obj' => $events[$i],
                    'score' => Search::calculateScore(
                        $position,
                        $events[$i]
                    )
                );

            }

        }

        uasort( $scores, 'Search::rankScores' );

        $results = array();

        foreach ( $scores as $index => $score ) {
            try {
                $results[] = $scores[$index]['obj']->exportToAPI();
            } catch ( Exception $e ) {
                Common::logError(
                    '[' . $scores[$index]['obj']->getId() . '] '
                        . $e->getMessage(),
                    $datastore
                );
            }
        }

        return array( 'objects' => $results, 'pageCount' => $totalPages );

    }

    public static function calculateScore( $position, $obj ) {

        $geoRelation = $obj->getGeoRelationFrom( $position );
        $distance = $geoRelation['distance'];

        $now = time();

        //recent activity increases the score
        $lastActivityScore = 1 - ( ( $now - $obj->getLastActivity() ) / $now );
        $distanceScore = 0;

        $finalScore = 0;

        //objects closer than 2km away get an initial score for proximity
        if ( $distance <= 2000 ) {
            $distanceScore = 1 - ( $distance / 2000 );
        }

        switch ( $obj->getType() ) {
            case 1:
                //more replies increases the score
                $replyCountScore = log( $obj->getReplyCount() );
                $finalScore = (
                    (
                        (
                            ( $lastActivityScore * 0.5 )
                            + ( $distanceScore * 0.3 )
                            + ( $replyCountScore * 0.2 )
                        ) / 3
                    ) * 0.85
                );
                break;
            case 2:
                $serviceLevelScore = sqrt( $obj->getServiceLevel() ) / 100;
                $eventCountScore = sqrt( $obj->getEventCount() ) / 100;
                $b = $obj->getEstablishment();
                $finalScore = (
                    (
                        ( $lastActivityScore * 0.1 )
                        + ( $distanceScore * 0.4 )
                        + ( $eventCountScore * 0.1 )
                        + ( $eventCountScore * 0.4 )
                    ) / 4
                );
                break;
            case 6:
                //give it an increasing score based on how soon it will occur
                //also, give it a big boost if it's happening right now
                $now = new DateTime( 'now' );
                $startDateTime = $obj->getEvent()->getStartDateTime();

                $today = $now->format('w');
                $eventDay = $startDateTime->format('w');

                $eventTimingScore = (
                    7 - abs( ( $today - ( $eventDay + 7 ) ) % 7 )
                ) / 7;
                $serviceLevelScore = sqrt( $obj->getServiceLevel() ) / 100;
                $replyCountScore = sqrt( $obj->getReplyCount() ) / 100;
                $finalScore = (
                    (
                        (
                            ( $lastActivityScore * 0.1 )
                            + ( $distanceScore * 0.1 )
                            + ( $serviceLevelScore * 0.1 )
                            + ( $eventTimingScore * 0.6 )
                            + ( $replyCountScore * 0.1 )
                        ) / 4
                    ) * 1.5
                );
                break;
        }
        return $finalScore;
    }

    public static function rankScores( $scoreA, $scoreB ) {
        if ( $scoreA['score'] <= $scoreB['score'] ) {
            return 1;
        } else {
            return -1;
        }
    }

    public static function calculateOffsets( $page, $count, $offsets ) {

        if ( $page > 1 ) {

            $offsets = Search::calculateOffsets( $page - 1, $count, $offsets );

            if ( $offsets === null ) {
                return null;
            }

        }

        if ( isset( $offsets['lastPage'] ) && $offsets['lastPage'] === true ) {
            return null;
        }

        $lastPage = false;
        $totalNeeded = 0;
        $totalAvailable = 0;
        $hasMore = array();

        foreach ( $offsets['categories'] as $category => $data ) {

            if ( $data !== null ) {

                $cat = $offsets['categories'][$category];

                $cat['nominalPageCount'] = ceil( $cat['weight'] * $count );

                if ( $page === 1 ) {
                    $cat['startOffset'] = 0;
                    $cat['totalCount'] = 0;
                } else {
                    $cat['startOffset'] = $cat['totalCount'];
                }

                if ( $cat['startOffset'] > $cat['totalObjects'] ) {

                    $cat['actualPageCount'] = 0;
                    $totalNeeded += $cat['nominalPageCount'];

                } else if ( $cat['startOffset'] + $cat['nominalPageCount']
                            > $cat['totalObjects'] ) {

                    $cat['actualPageCount'] = $cat['totalObjects']
                                            - $cat['startOffset'];
                    $totalNeeded += ( $cat['nominalPageCount']
                                      - $cat['actualPageCount'] );

                } else {

                    $cat['actualPageCount'] = $cat['nominalPageCount'];
                    $more = $cat['totalObjects']
                          - ( $cat['startOffset'] + $cat['actualPageCount'] );
                    $hasMore[] = array(
                        'category' => $category,
                        'amount' => $more
                    );
                    $totalAvailable += $more;

                }


                $offsets['categories'][$category] = $cat;

            }

        }

        while ( $totalNeeded > 0 ) {

            $foundAvailable = false;

            foreach ( $hasMore as $index => $data ) {

                $cat = $hasMore[$index];

                if ( $cat['amount'] > 0 ) {

                    $foundAvailable = true;

                    $totalNeeded--;
                    $cat['amount']--;
                    $offsets['categories'][$data['category']]
                        ['actualPageCount']++;

                }

                $hasMore[$index] = $cat;

            }

            if ( $foundAvailable === false ) {
                $totalNeeded = 0;
                $offsets['lastPage'] = true;
            }

        }

        foreach ( $offsets['categories'] as $category => $data ) {
            if ( $data !== null ) {
                $offsets['categories'][$category]['totalCount'] +=
                    $offsets['categories'][$category]['actualPageCount'];
            }
        }

        return $offsets;

    }

    public static function getTotalObjectCount( $categories ) {

        $count = 0;

        foreach ( $categories as $category => $data ) {
            $count = $count + $categories[$category]['pageCount'];
        }

        return $count;

    }

}
