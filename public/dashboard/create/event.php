<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Dashboard.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'Auth.php' );
require_once( LIB_DIR . 'obj/Object.php' );

$lang = new Language();
$datastore = new Datastore( $lang );
$auth = new Auth( $datastore, $lang );


/*******************************************************************************
* Reject non-authenticated or unauthorized requests to this page               *
*******************************************************************************/

if ( !$auth->checkAuthentication() ) {

    $auth->redirect( DASHBOARD_BASE_PATH );
    exit;

}

$account = $auth->getAccountInfo();

//user is authenticated, now check for authorization

$currentEstablishmentId = Common::validateGlobalId(
    Common::normalizeGlobalId(
        Common::extractGETValue( 'id' )
    )
);

$establishmentMetadata = $datastore->retrieveEstablishmentMetadata( $currentEstablishmentId );

if ( $currentEstablishmentId === null
     || ( $account['admin'] !== true
          && $establishmentMetadata['accountId'] !== $account['id'] ) ) {

    header( 'Location: ' . DASHBOARD_BASE_PATH );
    exit;

}


/*******************************************************************************
* User is authorized to create a happening below this point                    *
*******************************************************************************/

$currentEstablishmentData = $datastore->retrieveObject(
    $currentEstablishmentId
);

$obj = new Object( $datastore, $lang );
$obj->load( $currentEstablishmentData );
$theme = $obj->getTheme();

$currentEstablishmentData['status'] = $establishmentMetadata['status'];

$pageData = array(
    'account' => &$account,
    'currentEstablishment' => &$currentEstablishmentData,
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

Dashboard::renderNotifications( $pageData, $lang );

Dashboard::renderNavSection( $pageData, $lang );

?>
        <div class="section-4-col">
          <h2 class="section-title"><?php echo $lang->get( 'EVENT_CREATE_SECTION_TITLE'); ?></h2>
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

require( LIB_DIR . 'frag/DashboardCreateEventFooter.php' );

?>
