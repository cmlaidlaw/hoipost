<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Dashboard.php' );
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

//check for admin status and select available establishments accordingly
if ( $account['admin'] === true ) {
    $city = 'hong kong';
    if ( isset( $_GET['c'] ) ) {
        $city = $_GET['c'];
    }
    $establishments = Dashboard::alphabetizeEstablishments(
        $datastore->retrieveCityEstablishments( $city )
    );
} else {
    $establishments = Dashboard::alphabetizeEstablishments(
        $datastore->retrieveAccountEstablishments( $account['id'] )
    );
}

$currentEstablishment = null;

//check if a current establishment is specified
$id = Common::validateGlobalId( Common::extractGETValue( 'b' ) );

if ( $id !== null ) {
    foreach ( $establishments as $establishment ) {
        if ( $establishment['id'] === $id ) {
            $currentEstablishment =& $establishment;
            //make sure to break here because otherwise $establishment changes
            //on the next iteration and since 'currentEstablishment' is
            //assigned by reference, it changes as well
            break;
        }
    }
}

//if there is an id set but it doesn't exist, redirect to the dashboard base
//path so we don't confuse anything that might look at the /objectId/ segment
if ( $id !== null && $currentEstablishment === null ) {
    header( 'Location: ' . DASHBOARD_BASE_PATH );
    exit;
}

//fall back to first business in the array if none match the specified id
if ( $currentEstablishment === null && !empty( $establishments ) ) {
    $currentEstablishment = $establishments[0];
}

if ( $currentEstablishment !== null ) {
    //calcualte the current service status and retrieve events
    //for this business
    try {
        $currentEstablishment['serviceLevel'] =
            $datastore->retrieveEstablishmentServiceLevel(
                $currentEstablishment['id']
            );
        $currentEstablishment['maxEvents'] = Dashboard::getServiceLevelMaxEvents(
            $currentEstablishment
        );
        $currentEstablishment['events'] =
            $datastore->retrieveEstablishmentEvents(
                $currentEstablishment['id'],
                true
            );
    } catch ( Exception $e ) {
        if ( DEBUG ) {
            echo var_dump($e->getMessage());
        } else {
            Common::logError( $e->getMessage, $datastore );
        }
    }
}

/*******************************************************************************
* User is authorized for this venue or happening below this point              *
*******************************************************************************/

$pageData = array(
    'account' => &$account,
    'city' => &$city,
    'establishments' => &$establishments,
    'currentEstablishment' => &$currentEstablishment
);

require( LIB_DIR . 'frag/DashboardHeader.php' );


/*******************************************************************************
* Page content starts here                                                     *
*******************************************************************************/

?>
    <div id="content">
      <div class="centered">
<?php

Dashboard::renderNotifications( $pageData, $lang );

Dashboard::renderNavSection( $pageData, $lang );

Dashboard::renderEstablishmentSection( $pageData, $lang );

Dashboard::renderServiceSection( $pageData, $lang );

echo '<div class="clear"></div>';

Dashboard::renderEventsSection( $pageData, $lang );

?>
      </div>
    </div>
<?php

/*******************************************************************************
* Footer and scripting starts here                                             *
*******************************************************************************/

require( LIB_DIR . 'frag/DashboardFooter.php' );

?>
