<?php

class Datastore {

    private $_db;
    private $_ready;
    private $_query;
    private $_stmt;

    private $_lang;

    public function __construct( &$lang ) {

        $this->_ready = false;

        $this->_lang =& $lang;

    }

    public function __destruct() {

        if ( is_resource( $this->_db ) ) {
            $this->_db->close();
        }

    }

    private function _connect() {

        $this->_db = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_OPEN );

        if ( !$this->_db->connect_errno ) {

            if (  $this->_db->set_charset( 'utf8' ) &&
                  $this->_db->query( 'SET time_zone = \'+0:00\'' ) ) {

                $this->_ready = true;

            } else {
                Common::logError(
                    'Datastore: Could not set database charset or timezone',
                    $this
                );
            }
        } else {
            Common::logError(
                'Datastore: Could not connect to database (Error #'
                . $this->_db->connect_errno . ').',
                $this
            );
        }

        return $this->_ready;

    }

    private function _checkStatus( $method ) {
        if ( !$this->_ready ) {
            if ( !$this->_connect() ) {
                throw new Exception(
                    $method . ': Could not connect to database.'
                );
            }
        }
    }

    private function _checkStatement( $method ) {
        if ( !$this->_stmt = $this->_db->prepare( $this->_query ) ) {
            throw new Exception(
                $method . ': Unable to prepare statement for query "'
                . $this->_query . '".'
            );
        }
    }

    private function _checkExecution( $method ) {
        if ( !$this->_stmt->execute() ) {
            throw new Exception(
                $method .': Unable to execute statement for query "'
                . $this->_query . '".'
            );
        }
    }


    /***************************************************************************
    * Account queries                                                          *
    ***************************************************************************/

    public function createAccount( $email, $password, $admin ) {

        $this->_checkStatus( __METHOD__ );

        $accountsId = false;

        $this->_query = 'INSERT INTO `accounts` (email, password, admin) '
                      . 'VALUES (?, ?, ?)';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'ssi',
            $email,
            $password,
            $admin
        );

        $this->_checkExecution( __METHOD__ );

        $accountsId = $this->_stmt->insert_id;

        $this->_stmt->close();

        return $accountsId;

    }

    public function retrieveAccount( $email ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT a.id, a.password, a.admin FROM `accounts` a '
                      . 'WHERE a.email = ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            's',
            $email
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $aId,
            $aPassword,
            $aAdmin
        );

        $account = null;

        if ( $this->_stmt->num_rows === 1 ) {

            $this->_stmt->fetch();

            if ( $aAdmin === 1 ) {
                $admin = true;
            } else {
                $admin = false;
            }

            $account = array(
                'id' => $aId,
                'email' => $email,
                'password' => $aPassword,
                'admin' => $admin
            );

        }

        $this->_stmt->close();

        return $account;

    }

    /*public function updateAccount( $accountsId, $email, $password ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `accounts` SET accounts.email = ?, '
                      . 'accounts.password = ? WHERE accounts.id = ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'ssi',
            $email,
            $password,
            $accountsId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }

    public function disableAccount( $accountsId ) {

        $this->_checkStatus( __METHOD__ );

        $this->_db->autocommit( false );

        $this->_query = 'UPDATE `accounts` SET accounts.status = \'DISABLED\' '
                      . 'WHERE accounts.id = ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'i',
            $accountsId
        );

        $this->_checkExecution( __METHOD__ );

        $accountId = $accountsId;

        $this->_stmt->close();

        try {

            $venues = $this->retrieveVenuesByAccount( $accountId );

            foreach ( $venues as $venue ) {

                $happenings = $this->retrieveHappeningsByVenue( $venue['id'] );

                foreach ( $happenings as $happening ) {
                    $this->disableMessage( $happening['messagesId'] );
                }

            }

        } catch ( Exception $e ) {
            throw $e;
        }

        $this->_db->commit();

        return true;

    }

    public function deleteAccount( $accountsId ) {

        $this->_checkStatus( __METHOD__ );

        $this->_db->autocommit( false );

        $this->_query = 'UPDATE `accounts` SET accounts.status = \'DELETED\', '
                      . 'messages.statusChanged = ? WHERE accounts.id = ?';

        $this->_checkStatement( __METHOD__ );

        $timestamp = time();

        $this->_stmt->bind_param(
            'ii',
            $timestamp,
            $accountsId
        );

        $this->_checkExecution( __METHOD__ );

        try {

            $venues = $this->retrieveVenuesByAccount( $accountsId );

            foreach ( $venues as $venue ) {

                $happenings = $this->retrieveHappeningsByVenue( $venue['id'] );

                foreach ( $happenings as $happening ) {
                    $this->deleteMessage( $happening['messagesId'] );
                }

            }

        } catch ( Exception $e ) {
            throw $e;
        }

        $this->_db->commit();

        $this->_stmt->close();

        return true;

    }*/


    /***************************************************************************
    * Voucher/payment queries                                                  *
    ***************************************************************************/

    public function createVoucher( $code, $serviceLevel, $serviceDuration ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'INSERT INTO `vouchers` ( code, serviceLevel, '
                      . 'serviceDuration ) VALUES ( ?, ?, ? )';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'sii',
            $code,
            $serviceLevel,
            $serviceDuration
        );

        if ( !$this->_stmt->execute() ) {
            //make special allowance for attempting to insert with a code that
            //already exists (error 1062 is an attempted violation of the unique
            //property on a column
            if ( $this->_db->errno === 1062 ) {
                $voucherId = false;
            } else {
                throw new Exception(
                    __METHOD__ .': Unable to execute statement for query "'
                    . $this->_query . '".'
                );
            }
        } else {
            $voucherId = $this->_stmt->insert_id;
        }

        $this->_stmt->close();

        return $voucherId;

    }

    public function retrieveVoucher( $code ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT v.id, v.serviceLevel, v.serviceDuration, '
                      . 'v.objectId, v.redeemed, v.daysRemaining '
                      . 'FROM `vouchers` v WHERE v.code = ? LIMIT 0,1';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            's',
            $code
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $voucherId,
            $serviceLevel,
            $serviceDuration,
            $objectId,
            $redeemed,
            $daysRemaining
        );

        $voucher = null;

        if ( $this->_stmt->num_rows === 1 ) {

            $this->_stmt->fetch();

            $voucher = array(
                'id' => $voucherId,
                'serviceLevel' => $serviceLevel,
                'serviceDuration' => $serviceDuration,
                'venues_id' => $objectId,
                'redeemed' => $redeemed,
                'daysRemaining' => $daysRemaining
            );

        }

        $this->_stmt->close();

        return $voucher;

    }

    public function retrieveActiveVouchers( $objectId ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT v.id, v.serviceLevel, v.serviceDuration, '
                      . ' v.redeemed, v.daysRemaining FROM `vouchers` v '
                      . 'WHERE v.objectId = ? AND v.daysRemaining > 0 '
                      . 'ORDER BY v.serviceLevel DESC';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId( $objectId );

        $this->_stmt->bind_param(
            's',
            $objectId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $voucherId,
            $serviceLevel,
            $serviceDuration,
            $redeemed,
            $daysRemaining
        );

        $vouchers = array();

        if ( $this->_stmt->num_rows > 0 ) {

            while ( $this->_stmt->fetch() ) {

                $vouchers[] = array(
                    'voucherId' => $voucherId,
                    'objectId' => $objectId,
                    'serviceLevel' => $serviceLevel,
                    'serviceDuration' => $serviceDuration,
                    'redeemed' => $redeemed,
                    'daysRemaining' => $daysRemaining
                );

            }

        }

        $this->_stmt->close();

        return $vouchers;

    }


    public function redeemVoucher( $voucherId, $objectId, $serviceLevel,
                                   $redeemed, $daysRemaining ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `vouchers` SET vouchers.objectId = ?, '
                      . 'vouchers.redeemed = ?, vouchers.daysRemaining = ? '
                      . 'WHERE vouchers.id = ? AND vouchers.objectId IS NULL';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'ssii',
            $objectId,
            $redeemed,
            $daysRemaining,
            $voucherId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return $success;

    }

    public function updateVoucherDaysRemaining( $vouchersId,
                                                $daysRemaining ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `vouchers` SET vouchers.daysRemaining = ? '
                      . 'WHERE vouchers.id = ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'ii',
            $daysRemaining,
            $vouchersId
        );

        $this->_checkExecution( __METHOD__ );

        $success = false;

        if ( $this->_db->affected_rows === 1 ) {
            $success = true;
        }

        $this->_stmt->close();

        return $success;

    }


    /***************************************************************************
    * Authenticated session queries                                            *
    ***************************************************************************/

    public function createSession( $accountsId, $token, $expires ) {

        $this->_checkStatus( __METHOD__ );

        $sessionsId = false;

        $this->_query = 'INSERT INTO `sessions` (accounts_id, token, expires) '
                      . 'VALUES (?, ?, ?)';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'isi',
            $accountsId,
            $token,
            $expires
        );

        $this->_checkExecution( __METHOD__ );

        $sessionsId = $this->_stmt->insert_id;

        $this->_stmt->close();

        return $sessionsId;

    }

    public function retrieveSession( $sessionsId = false ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT s.token, s.expires, a.id, a.email, a.admin '
                      . 'FROM `sessions` s LEFT JOIN `accounts` a ON '
                      . 's.accounts_id = a.id WHERE s.id = ? '
                      . 'AND s.ended = 0';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'i',
            $sessionsId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $sToken,
            $sExpires,
            $aId,
            $aEmail,
            $aAdmin
        );

        $session = false;

        if ( $this->_stmt->num_rows === 1 ) {

            $this->_stmt->fetch();

            if ( $aAdmin === 1 ) {
                $admin = true;
            } else {
                $admin = false;
            }

            $session = array(
                'sessionsId' => $sessionsId,
                'token' => $sToken,
                'expires' => $sExpires,
                'accountsId' => $aId,
                'email' => $aEmail,
                'admin' => $admin
            );

        }

        $this->_stmt->close();

        return $session;

    }

    public function deleteSession( $sessionsId ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `sessions` SET sessions.ended = 1 '
                      . 'WHERE sessions.id = ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'i',
            $sessionsId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }


    /***************************************************************************
    * Generic Object CRUD                                                      *
    ***************************************************************************/

    public function createObject( $obj, $ip ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'INSERT INTO `objects` '
                      . '(objectId, body, lastActivity, '
                      . 'ipAddress) VALUES (?, ?, ?, INET_ATON(?) )';

        $this->_checkStatement( __METHOD__ );

        $type = $obj->getType();

        if ( $type === null ) {
            throw new Exception( 'createObject: Invalid object type.' );
        }

        $objectId = Common::generateGlobalId( $type );
        $json = json_encode( $obj->exportToDatastore() );
        $lastActivity = time();

        $this->_stmt->bind_param(
            'ssii',
            $objectId,
            $json,
            $lastActivity,
            $ip
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        try {

            switch ( $type ) {

                case 1:
                    $message = $obj->getMessage();
                    if ( $message->getReplyTo() !== null ) {
                        $isReply = 1;
                    } else {
                        $isReply = 0;
                    }
                    $this->_indexMessage(
                        $objectId,
                        $isReply,
                        $obj->getLat(),
                        $obj->getLng(),
                        $obj->getGeoHash(),
                        $lastActivity,
                        $lastActivity
                    );
                    if ( $message->getReplyTo() !== null ) {
                        $this->_indexReply(
                            $message->getReplyTo(),
                            $objectId,
                            $message->getParentObjectType()
                        );
                    }
                    break;

                case 2:
                    $establishment = $obj->getEstablishment();
                    $this->_indexEstablishment(
                        $objectId,
                        $establishment->getAccountId(),
                        $establishment->getCity(),
                        $obj->getLat(),
                        $obj->getLng(),
                        $obj->getGeoHash(),
                        $lastActivity
                    );
                    break;

                case 6:
                    $establishment = $obj->getEstablishment();
                    $event = $obj->getEvent();
                    $this->_indexEvent(
                        $objectId,
                        $establishment->getAccountId(),
                        $event->getEstablishmentObjectId(),
                        $establishment->getCity(),
                        $obj->getLat(),
                        $obj->getLng(),
                        $obj->getGeoHash(),
                        $event->getStartDateTime(),
                        $event->getEndDateTime(),
                        $event->getRepeatsWeekly(),
                        $lastActivity
                    );
                    break;

                default:
                    break;

            }

        } catch ( Exception $e ) {
            throw $e;
        }

        return Common::normalizeGlobalId( $objectId );

    }

    public function retrieveObject( $objectId ) {

        //convenience method to grab just one object
        $object = $this->retrieveObjects( array( $objectId ) );
        if ( count( $object ) > 0 ) {
            return $object[$objectId];
        } else {
            return null;
        }

    }

    public function retrieveObjects( $objectIds ) {

        $this->_checkStatus( __METHOD__ );

        $idCount = count( $objectIds );

        //build the query string for an arbitrary number of ids
        $this->_query = 'SELECT o.objectId, o.body, o.lastActivity FROM '
                      . '`objects` o WHERE o.objectId IN (';
        for ( $i = 0; $i < $idCount; $i++ ) {
            $this->_query .= '?, ';
        }
        $this->_query = substr( $this->_query, 0, strlen( $this->_query ) - 2 )
                      . ')';

        $this->_checkStatement( __METHOD__ );

        //build the array of parameters for bind_param
        $params = array();

        $paramString = str_pad( '', $idCount,'s' );
        $paramIds = array_map( 'Common::denormalizeGlobalId', $objectIds );

        $params[] =& $paramString;
        for ( $i = 0; $i < $idCount; $i++ ) {
            $params[] =& $paramIds[$i];
        }

        //call bind_param
        call_user_func_array( array($this->_stmt, 'bind_param'), $params );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $objectId,
            $body,
            $lastActivity
        );

        $objects = array();

        $i = 0;

        while ( $this->_stmt->fetch() ) {

            $data = json_decode( $body, true );
            $data['id'] = Common::normalizeGlobalId( $objectId );
            $data['lastActivity'] = $lastActivity;
            $objects[$data['id']] = $data;
            $i++;

        }

        $this->_stmt->close();

        return $objects;

    }

    public function retrieveObjectReplies( $position, $objectId, $startOffset,
                                           $count ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.replyObjectId '
                      . 'FROM object_has_reply o '
                      . 'WHERE o.objectId = ? '
                      . 'ORDER BY o.replyObjectId LIMIT ?, ?';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId( $objectId );

        $this->_stmt->bind_param(
            'sii',
            $objectId,
            $startOffset,
            $count
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $replyObjectId
        );

        $replyObjectIds = array();

        while ( $this->_stmt->fetch() ) {
            $replyObjectIds[] = $replyObjectId;
        }

        $this->_stmt->close();

        $this->_query = 'SELECT COUNT(o.replyObjectId) as count '
                      . 'FROM `object_has_reply` o '
                      . 'WHERE o.objectId = ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            's',
            $objectId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $totalReplies
        );

        $this->_stmt->fetch();

        $this->_stmt->close();

        try {

            $replyObjects = array();
            $objects = array();

            if ( !empty( $replyObjectIds ) ) {
                $replyObjects = $this->retrieveObjects( $replyObjectIds );
                foreach ( $replyObjects as $id => $replyObjectData ) {
                    $obj = new Object( $this, $this->_lang );
                    $obj->load( $replyObjectData );
                    $obj->getGeoRelationFrom( $position );
                    $objects[] = $obj->exportToAPI();
                }
            }

        } catch ( Exception $e ) {
            throw $e;
        }

        return array(
            'objects' => $objects,
            'pageCount' => ceil( $totalReplies / $count )
        );

    }

    public function updateObject( $obj ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `objects` o SET o.body = ?, o.lastActivity = ? '
                      . 'WHERE o.objectId = ?';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId( $obj->getId() );
        $lastActivity = time();

        $json = json_encode( $obj->exportToDatastore() );

        $this->_stmt->bind_param(
            'sis',
            $json,
            $lastActivity,
            $objectId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        try {

            switch ( $obj->getType() ) {

                case 2:
                    $establishment = $obj->getEstablishment();
                    $this->_reindexEstablishment(
                        $objectId,
                        $establishment->getAccountId(),
                        $establishment->getCity(),
                        $obj->getLat(),
                        $obj->getLng(),
                        $obj->getGeoHash(),
                        false,
                        false
                    );
                    //we have to do this because of the parent->child
                    //relationship between establishments and events
                    $this->_reindexEstablishmentEvents( $obj );
                    break;

                case 6:
                    $establishment = $obj->getEstablishment();
                    $event = $obj->getEvent();
                    $startDateTime = $event->getStartDateTime();
                    $endDateTime = $event->getEndDateTime();
                    $this->_reindexEvent(
                        $objectId,
                        $establishment->getAccountId(),
                        $event->getEstablishmentObjectId(),
                        $establishment->getCity(),
                        $obj->getLat(),
                        $obj->getLng(),
                        $obj->getGeoHash(),
                        $startDateTime->format( 'Y-m-d H:i:s' ),
                        $endDateTime->format( 'Y-m-d H:i:s' ),
                        $event->getRepeatsWeekly(),
                        false,
                        false
                    );
                    break;

                default:
                    break;

            }

        } catch ( Exception $e ) {
            throw $e;
        }

        return true;

    }

    public function updateObjectStatus( $obj, $status ) {

        $statusChanged = time();
        $objectId = Common::denormalizeGlobalId( $obj->getId() );

        try {

            switch ( $obj->getType() ) {

                case 1:
                    $message = $obj->getMessage();
                    $this->_reindexMessage(
                        $objectId,
                        $message->getLastActivity(),
                        $status,
                        $statusChanged
                    );
                    break;

                case 2:
                    $establishment = $obj->getEstablishment();
                    $this->_reindexEstablishment(
                        $objectId,
                        $establishment->getAccountId(),
                        $establishment->getCity(),
                        $obj->getLat(),
                        $obj->getLng(),
                        $obj->getGeoHash(),
                        $status,
                        $statusChanged
                    );
                    try {
                        $eventObjects = $this->retrieveEstablishmentEvents(
                            $objectId,
                            true
                        );
                        foreach ( $eventObjects as $eventObject ) {
                            $thisObj = new Object( $this, $this->_lang );
                            $thisObj->load( $eventObject );
                            $this->updateObjectStatus( $thisObj, $status );
                        }
                    } catch ( Exception $e ) {
                        throw $e;
                    }
                    break;

                case 6:
                    $establishment = $obj->getEstablishment();
                    $event = $obj->getEvent();
                    $startDateTime = $event->getStartDateTime();
                    $endDateTime = $event->getEndDateTime();
                    $this->_reindexEvent(
                        $objectId,
                        $establishment->getAccountId(),
                        $event->getEstablishmentObjectId(),
                        $establishment->getCity(),
                        $obj->getLat(),
                        $obj->getLng(),
                        $obj->getGeoHash(),
                        $startDateTime->format( 'Y-m-d H:i:s' ),
                        $endDateTime->format( 'Y-m-d H:i:s' ),
                        $event->getRepeatsWeekly(),
                        $status,
                        $statusChanged
                    );
                    break;

                default:
                    break;

            }

        } catch ( Exception $e ) {
            throw $e;
        }

        return true;

    }


    public function enableObject( $obj ) {
        return $this->updateObjectStatus( $obj, 'ACTIVE' );
    }

    public function disableObject( $obj ) {
        return $this->updateObjectStatus( $obj, 'DISABLED' );
    }

    public function deleteObject( $obj ) {
        return $this->updateObjectStatus( $obj, 'DELETED' );
    }


    /***************************************************************************
    * Indexing queries                                                         *
    ***************************************************************************/

    private function _indexMessage( $objectId, $isReply, $lat, $lng, $geoHash,
                                    $lastActivity, $statusChanged ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'INSERT INTO `object_is_message` '
                      . '(objectId, isReply, lat, lng, geohash, lastActivity, '
                      . 'statusChanged) '
                      . 'VALUES (?, ?, ?, ?, ?, ?, ?)';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId(
            $objectId
        );

        $this->_stmt->bind_param(
            'sisssii',
            $objectId,
            $isReply,
            $lat,
            $lng,
            $geoHash,
            $lastActivity,
            $statusChanged
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }

    private function _reindexMessage( $objectId, $lastActivity, $status,
                                      $statusChanged ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `object_is_message` o '
                      . 'SET o.lastActivity = ?, o.status = ?, '
                      . 'o.statusChanged = ? WHERE o.objectId = ?';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId(
            $objectId
        );

        $this->_stmt->bind_param(
            'isis',
            $lastActivity,
            $status,
            $statusChanged,
            $objectId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }

    private function _indexReply( $objectId, $replyObjectId,
                                  $parentObjectType ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'INSERT INTO `object_has_reply` '
                      . '(objectId, replyObjectId) VALUES (?, ?)';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId(
            $objectId
        );

        $this->_stmt->bind_param(
            'ss',
            $objectId,
            $replyObjectId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        $this->_query = 'SELECT COUNT(o.objectId) as count '
                      . 'FROM object_has_reply o '
                      . 'WHERE o.objectId = ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            's',
            $objectId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->store_result();

        $this->_stmt->bind_result(
            $replyCount
        );

        $this->_stmt->fetch();

        $parentObjectReplyCount = $replyCount;

        $this->_stmt->close();

        switch ( $parentObjectType ) {

            case 1:
                $this->_query = 'UPDATE `object_is_message` o '
                              . 'SET o.replyCount = ? '
                              . 'WHERE o.objectId = ?';
                break;
            case 2:
                $this->_query = 'UPDATE `object_is_establishment` o '
                              . 'SET o.replyCount = ? '
                              . 'WHERE o.objectId = ?';
                break;
            case 6:
                $this->_query = 'UPDATE `object_is_event` o '
                              . 'SET o.replyCount = ? '
                              . 'WHERE o.objectId = ?';
                break;

        }

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'is',
            $parentObjectReplyCount,
            $objectId
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }

    private function _indexEstablishment( $objectId, $accountId, $city,
                                          $lat, $lng, $geoHash,
                                          $statusChanged ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'INSERT INTO `object_is_establishment` '
                      . ' (objectId, accountId, city, lat, lng, geohash, '
                      . 'statusChanged)'
                      . 'VALUES (?, ?, ?, ?, ?, ?, ?)';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId(
            $objectId
        );

        $this->_stmt->bind_param(
            'sissssi',
            $objectId,
            $accountId,
            $city,
            $lat,
            $lng,
            $geoHash,
            $statusChanged
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }

    private function _reindexEstablishment( $objectId, $accountId, $city,
                                            $lat, $lng, $geoHash,
                                            $status, $statusChanged ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `object_is_establishment` o '
                      . 'SET o.accountId = ?, o.city = ?, '
                      . 'o.lat = ?, o.lng = ?, o.geohash = ? ';
        if ( $status !== false && $statusChanged !== false ) {
            $this->_query .= ', o.status = ?, o.statusChanged = ? ';
        }
        $this->_query .= 'WHERE o.objectId = ?';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId(
            $objectId
        );

        if ( $status !== false && $statusChanged !== false ) {
            $this->_stmt->bind_param(
                'isssssis',
                $accountId,
                $city,
                $lat,
                $lng,
                $geoHash,
                $status,
                $statusChanged,
                $objectId
            );
        } else {
            $this->_stmt->bind_param(
                'isssss',
                $accountId,
                $city,
                $lat,
                $lng,
                $geoHash,
                $objectId
            );
        }

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }

    private function _indexEvent( $objectId, $accountId, $establishmentObjectId,
                                  $city, $lat, $lng, $geoHash,
                                  $startDateTime, $endDateTime, $repeatsWeekly,
                                  $statusChanged ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'INSERT INTO `object_is_event` '
                      . '(objectId, accountId, establishmentObjectId, city, '
                      . 'lat, lng, geohash, startDateTime, endDateTime, '
                      . 'repeatsWeekly, statusChanged) '
                      . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId(
            $objectId
        );

        $this->_stmt->bind_param(
            'sisssssssii',
            $objectId,
            $accountId,
            $establishmentObjectId,
            $city,
            $lat,
            $lng,
            $geoHash,
            $startDateTime->format('Y-m-d H:i:s'),
            $endDateTime->format('Y-m-d H:i:s'),
            $repeatsWeekly,
            $statusChanged
        );

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }

    private function _reindexEvent( $objectId, $accountId,
                                    $establishmentObjectId,
                                    $city, $lat, $lng, $geoHash,
                                    $startDateTime, $endDateTime,
                                    $repeatsWeekly, $status,
                                    $statusChanged ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'UPDATE `object_is_event` o '
                      . 'SET o.accountId = ?, o.establishmentObjectId = ?, '
                      . 'o.city = ?, o.lat = ?, o.lng = ?, o.geohash = ?, '
                      . 'o.startDateTime = ?, o.endDateTime = ?, '
                      . 'o.repeatsWeekly = ? ';
        if ( $status !== false && $statusChanged !== false ) {
            $this->_query .= ', o.status = ?, o.statusChanged = ? ';
        }
        $this->_query .= 'WHERE o.objectId = ?';

        $this->_checkStatement( __METHOD__ );

        $objectId = Common::denormalizeGlobalId(
            $objectId
        );

        if ( $status !== false && $statusChanged !== false ) {
            $this->_stmt->bind_param(
                'isssssssisis',
                $accountId,
                $establishmentObjectId,
                $city,
                $lat,
                $lng,
                $geoHash,
                $startDateTime,
                $endDateTime,
                $repeatsWeekly,
                $status,
                $statusChanged,
                $objectId
            );
        } else {
        $this->_stmt->bind_param(
            'isssssssis',
            $accountId,
            $establishmentObjectId,
            $city,
            $lat,
            $lng,
            $geoHash,
            $startDateTime,
            $endDateTime,
            $repeatsWeekly,
            $objectId
        );
        }

        $this->_checkExecution( __METHOD__ );

        $this->_stmt->close();

        return true;

    }


    /***************************************************************************
    * Search queries                                                           *
    ***************************************************************************/

    public function retrieveMessageObjectCount() {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT COUNT(o.objectId) FROM '
                      . 'object_is_message o '
                      . 'WHERE (o.isReply = 0 '
                      . 'OR (o.isReply = 1 AND o.replyCount > 0)) '
                      . 'AND o.status = \'ACTIVE\' ';

        $this->_checkStatement( __METHOD__ );

        $this->_checkExecution( __METHOD__ );

        $objectCount = 0;

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $messageObjectCount
            );

            while ( $this->_stmt->fetch() ) {
                $objectCount = $messageObjectCount;
            }

        }

        $this->_stmt->close();

        return $objectCount;

    }

    public function searchMessages( &$resultStart, &$resultCount ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, o.replyCount FROM '
                      . 'object_is_message o '
                      . 'WHERE (o.isReply = 0 '
                      . 'OR (o.isReply = 1 AND o.replyCount > 0)) '
                      . 'AND o.status = \'ACTIVE\' '
                      . 'ORDER BY o.lastActivity '
                      . 'LIMIT ?, ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'ii',
            $resultStart,
            $resultCount
        );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();
        $replyCounts = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $replyCount
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
                $replyCounts[] = $replyCount;
            }

        }

        $this->_stmt->close();

        return $this->_consolidateObjectData( $objectIds, $replyCounts );

    }

    public function retrieveEstablishmentObjectCount( &$city ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT COUNT(o.objectId) FROM '
                      . 'object_is_establishment o '
                      . 'WHERE o.city = ? '
                      . 'AND o.status = \'ACTIVE\' ';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param( 's', $city );

        $this->_checkExecution( __METHOD__ );

        $objectCount = 0;

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $messageObjectCount
            );

            while ( $this->_stmt->fetch() ) {
                $objectCount = $messageObjectCount;
            }

        }

        $this->_stmt->close();

        return $objectCount;

    }

    public function searchEstablishments( &$position, &$city, &$resultStart,
                                          &$resultCount ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, o.replyCount, '
                      . '6371 * '
                      . 'acos( '
                      . 'sin( radians( ? ) ) * '
                      . 'sin( radians( o.lat ) ) + '
                      . 'cos( radians( ? ) ) * '
                      . 'cos( radians( o.lat ) ) * '
                      . 'cos( radians( o.lng ) - radians( ? ) ) '
                      . ') AS distance '
                      . 'FROM object_is_establishment o '
                      . 'WHERE o.city = ? '
                      . 'AND o.status = \'ACTIVE\' '
                      . 'ORDER BY distance ASC '
                      . 'LIMIT ?, ?';

        $this->_checkStatement( __METHOD__ );

        $lat = $position->getLat();
        $lng = $position->getLng();

        $this->_stmt->bind_param(
            'ssssii',
            $lat,
            $lat,
            $lng,
            $city,
            $resultStart,
            $resultCount
        );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();
        $replyCounts = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $replyCount,
                $distance
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
                $replyCounts[] = $replyCount;
            }

        }

        return $this->_consolidateObjectData( $objectIds, $replyCounts );

    }

    public function retrieveEventObjectCount( &$city ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT COUNT(o.objectId) FROM '
                      . 'object_is_event o '
                      . 'WHERE o.city = ? '
                      . 'AND o.status = \'ACTIVE\' ';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param( 's', $city );

        $this->_checkExecution( __METHOD__ );

        $objectCount = 0;

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $messageObjectCount
            );

            while ( $this->_stmt->fetch() ) {
                $objectCount = $messageObjectCount;
            }

        }

        $this->_stmt->close();

        return $objectCount;

    }

    public function searchEvents( &$city, &$resultStart, &$resultCount ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, o.replyCount, '
                      . 'ABS( '
                      . '( '
                      . 'DAYOFWEEK( NOW() ) - '
                      . '( DAYOFWEEK(o.startDateTime) + 7 ) '
                      . ') '
                      . 'MOD 7 ) AS score '
                      . 'FROM object_is_event o '
                      . 'WHERE o.city = ? '
                      . 'AND '
                      . '(o.repeatsWeekly = 0 AND o.startDateTime > NOW() ) '
                      . 'OR '
                      . '(o.repeatsWeekly = 1 ) '
                      . 'AND o.status = \'ACTIVE\' '
                      . 'ORDER BY score ASC '
                      . 'LIMIT ?, ?';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'sii',
            $city,
            $resultStart,
            $resultCount
        );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();
        $replyCounts = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $replyCount,
                $score
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
                $replyCounts[] = $replyCount;
            }

        }

        $this->_stmt->close();

        return $this->_consolidateObjectData( $objectIds, $replyCounts );

    }

    private function _consolidateObjectData( &$objectIds, &$replyCounts ) {

        $results = array();

        try {

            if ( !empty( $objectIds ) ) {

                $objects = $this->retrieveObjects( $objectIds );
                $objectCount = count( $objectIds );
                $eventCounts = $this->_retrieveObjectEventCounts( $objectIds );
                $serviceLevels = $this->_retrieveObjectServiceLevels(
                    $objectIds
                );

                for ( $i = 0; $i < $objectCount; $i++ ) {
                    
                    $newObject = new Object( $this, $this->_lang );
                    //note that we're using the $objectIds index instead of the
                    //$objects one so as to preserve the ordering in the search
                    //results, since the generic retrieveObjects does not
                    //preserve the original order of the IN ( 1, 2 ... n )
                    //parameter
                    $id = Common::normalizeGlobalId( $objectIds[$i] );
                    if ( isset( $objects[$id] ) ) {
                        $newObject->load( $objects[$id] );
                        switch ( $newObject->getType() ) {
                            case 1:
                                $newObject->setReplyCount( $replyCounts[$i] );
                                break;
                            case 2:
                                $newObject->setEventCount( $eventCounts[$id] );
                                $newObject->setServiceLevel(
                                    $serviceLevels[$id]
                                );
                                break;
                            case 6:
                                $newObject->setReplyCount( $replyCounts[$i] );
                                $newObject->setServiceLevel(
                                    $serviceLevels[$id]
                                );
                                break;
                        }
                        $results[] = $newObject;
                    }
                }

            }

        } catch ( Exception $e ) {
            throw $e;
        }

        return $results;

    }

    private function _retrieveObjectReplyCounts( &$objectIds ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, '
                      . 'COUNT( o.replyObjectId ) AS replyCount '
                      . 'FROM object_has_reply o '
                      . 'WHERE o.objectId IN (';
        $items = array();
        foreach ( $objectIds as $objectId ) {
            $items[] = '\'' . Common::denormalizeGlobalId( $objectId ) . '\'';
        }
        $this->_query .= implode( ', ', $items )
                       . ') GROUP BY o.objectId';

        $this->_checkStatement( __METHOD__ );

        $this->_checkExecution( __METHOD__ );

        $replyCounts = array();
        foreach ( $objectIds as $objectId ) {
            $replyCounts[Common::normalizeGlobalId( $objectId )] = 0;
        }

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $replyCount
            );

            while ( $this->_stmt->fetch() ) {
                $replyCounts[
                    Common::normalizeGlobalId( $objectId )] = $replyCount;
            }

        }

        $this->_stmt->close();

        return $replyCounts;

    }

    private function _retrieveObjectEventCounts( &$objectIds ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.establishmentObjectId, '
                      . 'COUNT( o.objectId ) AS eventCount '
                      . 'FROM object_is_event o '
                      . 'WHERE o.establishmentObjectId IN (';
        $items = array();
        foreach ( $objectIds as $objectId ) {
            $items[] = '\'' . Common::denormalizeGlobalId( $objectId ) . '\'';
        }
        $this->_query .= implode( ', ', $items )
                       . ') GROUP BY o.establishmentObjectId';

        $this->_checkStatement( __METHOD__ );

        $this->_checkExecution( __METHOD__ );

        $eventCounts = array();
        foreach ( $objectIds as $objectId ) {
            $eventCounts[Common::normalizeGlobalId( $objectId )] = 0;
        }

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $eventCount
            );

            while ( $this->_stmt->fetch() ) {
                $eventCounts[
                    Common::normalizeGlobalId( $objectId )] = $eventCount;
            }

        }

        $this->_stmt->close();

        return $eventCounts;

    }

    private function _retrieveObjectServiceLevels( &$objectIds ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT v.objectId, v.serviceLevel FROM vouchers v '
                      . 'WHERE v.objectId IN (';
        $items = array();
        foreach ( $objectIds as $objectId ) {
            $items[] = '\'' . Common::denormalizeGlobalId( $objectId ) . '\'';
        }
        $this->_query .= implode( ', ', $items )
                       . ') GROUP BY v.objectId ORDER BY v.serviceLevel';

        $this->_checkStatement( __METHOD__ );

        $this->_checkExecution( __METHOD__ );

        $objectServiceLevels = array();

        foreach ( $objectIds as $objectId ) {
            $objectServiceLevels[Common::normalizeGlobalId( $objectId )] = 0;
        }

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $serviceLevel
            );

            while ( $this->_stmt->fetch() ) {
                $objectServiceLevels[
                    Common::normalizeGlobalId( $objectId )] = $serviceLevel;
            }

        }

        $this->_stmt->close();

        return $objectServiceLevels;

    }


    /***************************************************************************
    * Dashboard queries                                                       *
    ***************************************************************************/

    public function retrieveAllEstablishments() {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, o.status FROM '
                      . '`object_is_establishment` o WHERE o.status IN '
                      . '( \'ACTIVE\', \'DISABLED\' )';

        $this->_checkStatement( __METHOD__ );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();
        $objectStatuses = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $status
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
                $objectStatuses[$objectId] = $status;
            }

        }

        $this->_stmt->close();

        $objects = array();

        if ( !empty( $objectIds ) ) {

            try {
                $objects = $this->retrieveObjects( $objectIds );
                foreach ( $objectIds as $objectId ) {
                    $id = Common::normalizeGlobalId( $objectId );
                    $objects[$id]['status'] = $objectStatuses[ $objectId ];
                }
            } catch ( Exception $e ) {
                throw $e;
            }

        }

        return $objects;

    }

    public function retrieveCityEstablishments( $city ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, o.status FROM '
                      . '`object_is_establishment` o WHERE o.city = ? '
                      . 'AND o.status IN '
                      . '( \'ACTIVE\', \'DISABLED\' )';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            's',
            $city
        );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();
        $objectStatuses = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $status
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
                $objectStatuses[$objectId] = $status;
            }

        }

        $this->_stmt->close();

        $objects = array();

        if ( !empty( $objectIds ) ) {

            try {
                $objects = $this->retrieveObjects( $objectIds );
                foreach ( $objectIds as $objectId ) {
                    $id = Common::normalizeGlobalId( $objectId );
                    $objects[$id]['status'] = $objectStatuses[ $objectId ];
                }
            } catch ( Exception $e ) {
                throw $e;
            }

        }

        return $objects;

    }

    public function retrieveAccountEstablishments( $accountId,
                                                   $includeDisabled = false,
                                                   $includeDeleted = false ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, o.status FROM '
                      . '`object_is_establishment` '
                      . 'o WHERE o.accountId = ? AND o.status IN'
                      . '(\'ACTIVE\'';
        if ( $includeDisabled ) {
            $this->_query .= ', \'DISABLED\'';
        }
        if ( $includeDeleted ) {
            $this->_query .= ', \'DELETED\'';
        }
        $this->_query .= ')';

        $this->_checkStatement( __METHOD__ );

        $this->_stmt->bind_param(
            'i',
            $accountId
        );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();
        $objectStatuses = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $status
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
                $objectStatuses[$objectId] = $status;
            }

        }

        $this->_stmt->close();

        $objects = array();

        if ( !empty( $objectIds ) ) {

            try {
                $objects = $this->retrieveObjects( $objectIds );
                foreach ( $objectIds as $objectId ) {
                    $id = Common::normalizeGlobalId( $objectId );
                    $objects[$id]['status'] = $objectStatuses[ $objectId ];
                }
            } catch ( Exception $e ) {
                throw $e;
            }

        }

        return $objects;

    }

    public function retrieveEstablishmentMetadata( $establishmentObjectId ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.accountId, o.status FROM '
                      . '`object_is_establishment` o WHERE o.objectId = ?';

        $this->_checkStatement( __METHOD__ );

        $establishmentObjectId = Common::denormalizeGlobalId(
            $establishmentObjectId
        );

        $this->_stmt->bind_param(
            's',
            $establishmentObjectId
        );

        $this->_checkExecution( __METHOD__ );

        $ownerId = null;

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $accountId,
                $status
            );

            $this->_stmt->fetch();

        }

        $this->_stmt->close();

        return array(
            'accountId' => $accountId,
            'status' => $status
        );

    }

    public function retrieveEstablishmentEvents( $establishmentObjectId,
                                                 $includeDisabled = false,
                                                 $includeDeleted = false ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId, o.status FROM `object_is_event` o '
                      . 'WHERE o.establishmentObjectId = ? AND o.status IN '
                      . '(\'ACTIVE\'';
        if ( $includeDisabled ) {
            $this->_query .= ', \'DISABLED\'';
        }
        if ( $includeDeleted ) {
            $this->_query .= ', \'DELETED\'';
        }
        $this->_query .= ')';

        $this->_checkStatement( __METHOD__ );

        $establishmentObjectId = Common::denormalizeGlobalId(
            $establishmentObjectId
        );

        $this->_stmt->bind_param(
            's',
            $establishmentObjectId
        );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();
        $objectStatuses = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId,
                $status
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
                $objectStatuses[$objectId] = $status;
            }

        }

        $this->_stmt->close();

        $objects = array();

        if ( !empty( $objectIds ) ) {

            try {
                $objects = $this->retrieveObjects( $objectIds );
                foreach ( $objectIds as $objectId ) {
                    $id = Common::normalizeGlobalId( $objectId );
                    $objects[$id]['status'] = $objectStatuses[$objectId];
                }
            } catch ( Exception $e ) {
                throw $e;
            }

        }

        return $objects;

    }

    private function _reindexEstablishmentEvents( $establishmentObject ) {

        $this->_checkStatus( __METHOD__ );

        $this->_query = 'SELECT o.objectId FROM `object_is_event` o '
                      . 'WHERE o.establishmentObjectId = ?';

        $this->_checkStatement( __METHOD__ );

        $establishmentObjectId = Common::denormalizeGlobalId(
            $establishmentObject->getId()
        );

        $this->_stmt->bind_param(
            's',
            $establishmentObjectId
        );

        $this->_checkExecution( __METHOD__ );

        $objectIds = array();

        $this->_stmt->store_result();

        if ( $this->_stmt->num_rows > 0 ) {

            $this->_stmt->bind_result(
                $objectId
            );

            while ( $this->_stmt->fetch() ) {
                $objectIds[] = $objectId;
            }

        }

        $this->_stmt->close();

        $objects = array();

        if ( !empty( $objectIds ) ) {

            try {

                $objects = $this->retrieveObjects( $objectIds );

                foreach ( $objectIds as $objectId ) {

                    $id = Common::normalizeGlobalId( $objectId );
                    try {
                        $newObj = new Object( $this, $lang );
                        $newObj->load( $objects[$id] );
                        //overwrite old establishment object data
                        $establishmentData = $establishmentObject
                                             ->getEstablishment();
                        $position = $establishmentObject->getPosition();
                        $newObj->loadPosition( $position );
                        $newObj->loadEstablishment(
                            $establishmentData->exportToDatastore()
                        );
                        $this->updateObject( $newObj );

                    } catch ( Exception $e ) {
                        Common::logError( $e->getMessage(), $this );
                    }

                }

            } catch ( Exception $e ) {
                throw $e;
            }

        }

        return true;

    }

    /*public function retrieveEstablishmentServiceStatus( $objectId ) {

        try {

            $owner = $this->retrieveEstablishmentMetadata( $objectId );

            if ( $owner['accountId'] === 2 ) {

                $serviceStatus = array(
                    'level' => 2,
                    'daysRemaining' => 1
                );

            } else {

                $vouchers = $this->retrieveActiveVouchers( $objectId );

                $serviceLevel = 0;
                $daysRemaining = array();

                for ( $i = 0; $i <= SERVICE_NUMBER_OF_LEVELS; $i++ ) {
                    $daysRemaining[$i] = 0;
                }

                foreach ( $vouchers as $voucher ) {
                    if ( $voucher['serviceLevel'] > $serviceLevel &&
                         $voucher['daysRemaining'] > 0 ) {
                        $serviceLevel = $voucher['serviceLevel'];
                    }
                    $i = $voucher['serviceLevel'];
                    $daysRemaining[$i] = $daysRemaining[$i]
                                         + $voucher['daysRemaining'];
                }


                $serviceStatus = array(
                    'level' => $serviceLevel,
                    'daysRemaining' => $daysRemaining[$serviceLevel]
                );

            }

        } catch ( Exception $e ) {
            throw $e;
        }

        return $serviceStatus;

    }*/

    public function upgradeEstablishmentServiceLevel( $establishmentObjectId ) {

        try {

            $this->_checkStatus( __METHOD__ );

            $this->_query = 'UPDATE `object_is_establishment` o '
                          . 'SET o.serviceLevel = 1 '
                          . 'WHERE o.objectId = ?';

            $this->_checkStatement( __METHOD__ );

            $establishmentObjectId = Common::denormalizeGlobalId(
                $establishmentObjectId
            );

            $this->_stmt->bind_param(
                's',
                $establishmentObjectId
            );

            $this->_checkExecution( __METHOD__ );

            $this->_stmt->close();

            $this->_query = 'UPDATE `object_is_event` o '
                          . 'SET o.serviceLevel = 1 '
                          . 'WHERE o.establishmentObjectId = ?';

            $this->_checkStatement( __METHOD__ );

            $this->_stmt->bind_param(
                's',
                $establishmentObjectId
            );

            $this->_checkExecution( __METHOD__ );

            $this->_stmt->close();


        } catch ( Exception $e ) {
            throw $e;
        }

        return true;

    }

    public function downgradeEstablishmentServiceLevel( $establishmentObjectId ){

        try {

            $this->_checkStatus( __METHOD__ );

            $this->_query = 'UPDATE `object_is_establishment` o '
                          . 'SET o.serviceLevel = 0 '
                          . 'WHERE o.objectId = ?';

            $this->_checkStatement( __METHOD__ );

            $establishmentObjectId = Common::denormalizeGlobalId(
                $establishmentObjectId
            );

            $this->_stmt->bind_param(
                's',
                $establishmentObjectId
            );

            $this->_checkExecution( __METHOD__ );

            $this->_stmt->close();

            $this->_query = 'UPDATE `object_is_event` o '
                          . 'SET o.serviceLevel = 0 '
                          . 'WHERE o.establishmentObjectId = ?';

            $this->_checkStatement( __METHOD__ );

            $this->_stmt->bind_param(
                's',
                $establishmentObjectId
            );

            $this->_checkExecution( __METHOD__ );

            $this->_stmt->close();


        } catch ( Exception $e ) {
            throw $e;
        }

        return true;

    }

    public function retrieveEstablishmentServiceLevel( $establishmentObjectId ) {

        $serviceLevel = null;

        try {

            $this->_checkStatus( __METHOD__ );

            $this->_query = 'SELECT o.serviceLevel FROM `object_is_establishment` o '
                          . 'WHERE o.objectId = ?';

            $this->_checkStatement( __METHOD__ );

            $establishmentObjectId = Common::denormalizeGlobalId(
                $establishmentObjectId
            );

            $this->_stmt->bind_param(
                's',
                $establishmentObjectId
            );

            $this->_checkExecution( __METHOD__ );

            $this->_stmt->store_result();

            if ( $this->_stmt->num_rows > 0 ) {

                $this->_stmt->bind_result(
                    $establishmentServiceLevel
                );

                $this->_stmt->fetch();

                $serviceLevel = (int) $establishmentServiceLevel;

            }

            $this->_stmt->close();

        } catch ( Exception $e ) {
            throw $e;
        }

        return $serviceLevel;

    }

    /***************************************************************************
    * 'Filesystem' operations                                                  *
    ***************************************************************************/

    public function log( $type, $line ) {

        $filename = null;
        $written = false;
        $closed = false;
        $success = false;

        try {

            $line .= "\n";

            switch ( $type ) {

                case 'activity':
                    $filename = LOG_DIR . 'activity/' . date('Y-m-d') . '.log';
                    break;

                case 'message':
                    $filename = LOG_DIR . 'messages.log';
                    break;

                case 'error':
                    $filename = LOG_DIR . 'errors.log';
                    break;

                default:
                    break;

            }

            if ( $filename === null ) {
                throw new Exception(
                    'Datastore::log(): Invalid log type "' . $type . '".'
                );
            }

            $fp = fopen( $filename, 'a');
            if ( is_resource( $fp ) ) {
                $written = fwrite( $fp, $line );
                $closed = fclose( $fp );
            }

            if ( $written !== false && $closed === true ) {
                $success = true;
            }

        } catch ( Exception $e ) {

            throw $e;

        }

        return $success;

    }

    public function putImage( $imageResource, $directory, $filename,
                              $extension ) {

        $success = false;

        try {

            if ( $extension === 'png' || $extension === 'gif' ) {
                //save alpha for png and gif
                imagealphablending( $imageResource, false );
                imagesavealpha( $imageResource, true );
            } else {
                //interlacing functions as the progressive flag for JPEG?
                imageinterlace( $imageResource, true );
            }

            switch ( $extension ) {

                case 'jpg':
                    $success = imagejpeg(
                        $imageResource,
                        $directory . $filename,
                        85
                    );
                    break;

                case 'png':
                    $success = imagepng(
                        $imageResource,
                        $directory . $filename,
                        5
                    );
                    break;

                case 'gif':
                    $success = imagegif(
                        $imageResource,
                        $directory . $filename
                    );
                    break;

                default:
                    $success = false;
                    break;

            }

        } catch ( Exception $e ) {

            throw $e;

        }

        return $success;

    }

    public function deleteImage( $name ) {
        unlink( THUMB_DIR . $name );
        unlink( PIC_DIR . $name );
        return true;
    }

}
