<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Dashboard.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'Auth.php' );
require_once( LIB_DIR . 'obj/Object.php' );

$lang = new Language();
$datastore = new Datastore( $lang );
$auth = new Auth( $datastore );


/*******************************************************************************
* Reject non-authenticated or unauthorized requests to this page               *
*******************************************************************************/

if ( !$auth->checkAuthentication() ) {

    $auth->redirect( DASHBOARD_BASE_PATH );
    exit;

}

$account = $auth->getAccountInfo();

//user is authenticated, now check for authorization

$currentEventId = Common::validateGlobalId(
    Common::normalizeGlobalId(
        Common::extractGETValue( 'id' )
    )
);

$currentEvent = $datastore->retrieveObject( $currentEventId );

try {

    $obj = new Object( $datastore, $lang );
    $obj->load( $currentEvent );
    $theme = $obj->getTheme();
    $currentEvent['json'] = json_encode( $obj->exportToAPI() );
    unset( $obj );

} catch ( Exception $e ) {

    if ( DEBUG ) {
        echo var_dump( $e->getMessage() );
    } else {
        Common::logError( $e->getMessage(), $datastore );
    }

}

if ( $currentEventId === null
     || ( $account['admin'] !== true
          &&
          (int) $currentEvent['establishment']['accountId'] !== (int) $account['id']
        ) ) {

    header( 'Location: ' . DASHBOARD_BASE_PATH );
    exit;

}


/*******************************************************************************
* User is authorized to create a happening below this point                    *
*******************************************************************************/

$pageData = array(
    'account' => &$account,
    'currentEstablishment' => array( 'id' => &$currentEvent['event']['establishmentObjectId'] ),
    'currentEvent' => &$currentEvent,
    'theme' => &$theme
);

require( LIB_DIR . 'frag/DashboardHeader.php' );


/*******************************************************************************
* Page content starts here                                                     *
*******************************************************************************/


?>
    <div id="content">
      <div class="centered">
<?php

Dashboard::renderNavSection( $pageData, $lang );

Dashboard::renderNotifications( $pageData, $lang );

?>
        <div class="section-4-col">
          <h2 class="section-title"><?php echo $lang->get( 'EVENT_UPDATE_SECTION_TITLE'); ?></h2>
          <div class="section-content">
            <div id="event-form"></div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
    </div>
<?php

/*******************************************************************************
* Footer and scripting starts here                                             *
*******************************************************************************/

require( LIB_DIR . 'frag/DashboardUpdateEventFooter.php' );

?>
