<?php

require_once( '../lib/HoipostData.php' );
require_once( LIB_DIR . 'HoipostDatastore.php' );

$start = microtime( true );

$datastore = new HoipostDatastore();

$venues = $datastore->retrieveVenues();

//values to increment for report
$venuesProcessedCount = 0;
$vouchersExpiredCount = 0;
$vouchersUpdatedCount = 0;
$serviceLevelsUpdatedCount = 0;

foreach ( $venues as $venue ) {

    $vouchers = $datastore->retrieveActiveVouchers( $venue['id'] );

    $expireVoucher = false;
    $updateVoucher = false;
    $updateServiceLevel = false;

    $newDaysRemaining = false;
    $newServiceLevel = false;

    if ( count( $vouchers ) > 0 ) {
        //we need to update on the subtraction so we know when it's necessary
        //to actually update (otherwise, 0-days-remaining results would just
        //disappear with no information about when we need to downgrade venues
        
        //we know that the 0th element is the highest service level
        //since the query is ordered by service level descending
        if ( $vouchers[0]['daysRemaining'] === 1 ) {
            //if this is the last day, roll over to the next voucher in the
            //queue or set to default (free)
            if ( count( $vouchers ) > 1 ) {
                $updateVoucher = true;
                $updateVoucherId = $vouchers[1]['id'];
                $newDaysRemaining = $vouchers[1]['daysRemaining'];
                $newServiceLevel = $vouchers[1]['serviceLevel'];                
            } else {
                $updateServiceLevel = true;
                $newServiceLevel = 0;
            }
            //expire the current voucher
            $expireVoucher = true;
            $expireVoucherId = $vouchers[0]['id'];
        } else {
            $updateVoucher = true;
            $updateVoucherId = $vouchers[0]['id'];
            $newDaysRemaining = $vouchers[0]['daysRemaining'] - 1;
        }

    }

    if ( $expireVoucher !== false ) {
        try {
            $voucherExpired = $datastore->updateVoucherDaysRemaining( $expireVoucherId, 0 );
            if ( $voucherExpired === false ) {
                HoipostData::logError( 'UpdateVenueServiceLevels: Could not expire voucher (vouchers_id: ' . $expireVoucherId . ')' );
            }
            $vouchersExpiredCount++;
        } catch ( Exception $e ) {
            HoipostData::logError( $e->getMessage() );
        }
    }

    if ( $updateVoucher !== false ) {
        try {
            $voucherUpdated = $datastore->updateVoucherDaysRemaining( $updateVoucherId, $newDaysRemaining );
            if ( $voucherUpdated === false ) {
                HoipostData::logError( 'UpdateVenueServiceLevels: Could not update voucher days remaining (vouchers_id: ' . $updateVoucherId . '; daysRemaining: ' . $newDaysRemaining . ')' );
            }
            $vouchersUpdatedCount++;
        } catch ( Exception $e ) {
            HoipostData::logError( $e->getMessage() );
        }
    }

    if ( $updateServiceLevel !== false ) {
        try {
            $venueUpdated = $datastore->updateVenueServiceLevel( $venue['id'], $newServiceLevel );
            $serviceLevelsUpdatedCount++;
        } catch ( Exception $e ) {
            HoipostData::logError( $e->getMessage() );
        }
    }

    $venuesProcessedCount++;

}

$now = new DateTime( "now" );
$now = $now->format( 'Y-m-s H:i:s' );

$elapsed = ceil( ( microtime( true ) - $start ) * 1000 );

$report = 'UpdateVenueServiceLevels: Processed ' . $venuesProcessedCount . ' venues in ' . $elapsed . 'ms; ' . $vouchersExpiredCount . ' vouchers expired, ' . $vouchersUpdatedCount . ' vouchers updated, ' . $serviceLevelsUpdatedCount . ' service levels updated'; 
HoipostData::logMessage( $report );
echo  $now . ' ' . $report;