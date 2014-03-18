<?php

class Session {

    public static function createSession( &$datastore, $accountsId ) {

        $session = null;
        $sessionsId = false;
        $token = Common::hash( $accountsId . COOKIE_TOKEN_SALT );
        $expires = time() + COOKIE_EXPIRATION_DELAY;

        try {

            $sessionsId = $datastore->createSession(
                $accountsId,
                $token,
                $expires
            );

            if ( $sessionsId !== false ) {
                Session::setSessionCookie(
                    $sessionsId,
                    $token,
                    $expires
                );
            }

        } catch ( Exception $e ) {
            Common::logError( $e->getMessage(), $datastore );
        }

        return $sessionsId;

    }

    public static function validateSession( &$datastore ) {

        $validAccount = null;

        if ( isset( $_COOKIE['auth'] ) ) {

            $cookieData = explode( ',', $_COOKIE['auth'] );

            $sessionsId = Session::validateSessionId( $cookieData[0] );
            $token = Common::validateHash( $cookieData[1] );

            if ( $sessionsId !== null && $token !== null ) {

                try {

                    $session = $datastore->retrieveSession( $sessionsId );

                    if ( $session !== null ) {
                        
                        //check token and expires here and choose if it's valid
                        if ( $token === $session['token'] ) {

                            $timeToExpiry = $session['expires'] - time();

                            //if the session has already expired, kill it
                            if ( $timeToExpiry <= 0 ) {

                                if ( !Session::destroySession(
                                        $datastore,
                                        $session['sessionsId']
                                      ) ) {
                                    //destroy the cookie first
                                    Session::setSessionCookie( );
                                    throw new Exception( 'validateSession: '
                                                          . 'Session::'
                                                          . 'destroySession '
                                                          . 'returned false' );
                                }

                            //if the session will expire within 1 hour,
                            //recycle it
                            } else if ( $timeToExpiry <= COOKIE_RECYCLE_WINDOW ) {

                                if ( !Session::destroySession(
                                        $datastore,
                                        $session['sessionsId']
                                      ) ) {
                                    //destroy the cookie first
                                    Session::setSessionCookie( );
                                    throw new Exception( 'validateSession: '
                                                          . 'Session::'
                                                          . 'destroySession '
                                                          . 'returned false' );
                                }

                                $newSession = Session::createSession(
                                    $datastore,
                                    $session['accountsId']
                                );

                                if ( $newSession !== false ) {
                                    $validAccount = array(
                                        'id' => $session['accountsId'],
                                        'email' => $session['email'],
                                        'admin' => $session['admin']
                                    );
                                }

                            //otherwise, just return the valid session
                            } else {

                                $validAccount = array(
                                    'id' => $session['accountsId'],
                                    'email' => $session['email'],
                                    'admin' => $session['admin']
                                );

                            }

                        }

                    }

                } catch ( Exception $e ) {
                    Common::logError( $e->getMessage(), $datastore );
                }

            }

        }

        return $validAccount;

    }

    public static function destroySession( &$datastore, $sessionsId ) {

        $success = false;

        $sessionsId = Session::validateSessionId( $sessionsId );

        if ( $sessionsId !== null ) {

            try {

                if ( $datastore->deleteSession( $sessionsId ) ) {
                    Session::setSessionCookie( );
                    $success = true;
                }

            } catch ( Exception $e ) {
                Common::logError( $e->getMessage(), $datastore );
            }

        }

        return $success;

    }

    public static function setSessionCookie( $sessionsId = null,
                                             $token = null,
                                             $expires = null ) {

        //if this is a valid session, then set the cookie accordingly
        if ( $sessionsId !== null && $token !== null && $expires !== null ) {

            setcookie(
                'auth',
                $sessionsId . ',' . $token,
                $expires,
                '/',
                COOKIE_DOMAIN,
                true,
                true
            );

        //otherwise destroy the session
        } else {

            setcookie(
                'auth',
                '',
                1,
                '/',
                COOKIE_DOMAIN,
                true,
                true
            );

        }

    }

    public static function validateSessionId( $sessionId ) {

        $cleanSessionId = null;
        $sessionId = filter_var(
            trim( $sessionId ),
            FILTER_SANITIZE_NUMBER_INT
        );

        if ( $sessionId !== false ) {
            $sessionId = (int) preg_replace( '/[^0-9]+/', '', $sessionId );
            if ( is_int( $sessionId ) && $sessionId > 0 ) {
                $cleanSessionId = $sessionId;
            }            
        }

        return $cleanSessionId;

    }

}
