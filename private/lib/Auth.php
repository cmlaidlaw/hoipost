<?php

class Auth {

    private $_authenticated;
    private $_account;
    private $_data;

    public function __construct ( &$datastore ) {

        require_once( LIB_DIR . 'Session.php' );

        $this->_authenticated = false;
        $this->_account = false;

        $this->_data =& $datastore;

    }

    //returns true on success
    public function authenticateAccount( $email, $password ) {

        if ( $this->_authenticated === false ) {

            $cleanEmail = Common::validateEmail( $email );

            if ( $cleanEmail !== null ) {

                $account = $this->_data->retrieveAccount( $cleanEmail );

                if ( !is_array( $account ) ) {
                    if ( DEBUG ) {
                          throw new Exception(
                                __METHOD__ . ': Datastore::'
                                . 'retrieveAccount() returned null'
                          );
                    } else {
                        Common::logError(
                            __METHOD__ . ': Datastore::retrieveAccount() '
                            . 'returned null',
                            $this->_data
                        );
                    }
                }

                if ( Common::checkHash(
                         $password,
                         $account['password']
                     ) ) {

                    try {

                        $this->session = Session::createSession(
                            $this->_data,
                            $account['id']
                        );

                        if ( $this->session === false ) {
                            if ( DEBUG ) {
                                  throw new Exception(
                                        __METHOD__ . ': Session::'
                                        . 'createSession() returned false'
                                  );
                            } else {
                                Common::logError(
                                    __METHOD__ . ': Session::createSession() '
                                    . 'returned false',
                                    $this->_data
                                );
                            }
                        } else {

                            $this->_account = array(
                                'id' => $account['id'],
                                'email' => $cleanEmail,
                                'admin' => $account['admin']
                            );

                            $this->_authenticated = true;

                        }

                    } catch ( Exception $e ) {
                        Common::logError( $e->getMessage(), $this->_data );
                    }

                }

            }

        }

        return $this->_authenticated;

    }

    public function checkAuthentication() {

        if ( !$this->_authenticated ) {

            $account = Session::validateSession( $this->_data );

            if ( $account !== null ) {

                $this->_account = array(
                    'id' => $account['id'],
                    'email' => $account['email'],
                    'admin' => $account['admin']
                );

                $this->_authenticated = true;

            }

        }

        return $this->_authenticated;

    }

    public function getAccountInfo() {
        return $this->_account;
    }

    //returns true on success
    public function deauthenticateAccount() {

        $account = $this->getAccountInfo();

        if ( !isset( $account['id'] ) || !Session::destroySession( 
                  $this->_data,
                  $this->_account['id'] ) ) {

            if ( DEBUG ) {
                  throw new Exception(
                      __METHOD__ . ': Session::destroySession() returned false'
                  );
            } else {
                Common::logError(
                    __METHOD__ . ': Session::destroySession() returned false',
                    $this->_data
                );
            }

        } else {

            $this->_authenticated = false;

        }

        return !$this->_authenticated;

    }

    public function redirect( $next ) {

        $redirect = BASE_PATH . AUTH_SIGN_IN_FORM;

        $next = parse_url( $next, PHP_URL_PATH );

        if ( substr( $next, 0, 8 ) === '/hoipost' ) {
            $next = substr( $next, 9, strlen( $next ) );
        }

        if ( $next !== null ) {
            $redirect .= '?next=' . urlencode( $next );
        }

        header( 'Location: ' . $redirect );

    }

}