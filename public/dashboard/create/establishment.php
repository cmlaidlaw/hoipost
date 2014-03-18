<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Dashboard.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'Auth.php' );

$lang = new Language();
$datastore = new Datastore( $lang );
$auth = new Auth( $datastore, $lang );

/*******************************************************************************
* Reject non-authenticated and non-admin requests to this admin page           *
*******************************************************************************/

//user is authenticated at all?
if ( !$auth->checkAuthentication() ) {
    $auth->redirect( DASHBOARD_BASE_PATH );
    exit;
}

$account = $auth->getAccountInfo();

//user is admin?
if ( $account['admin'] !== true ) {
    header( 'Location: ' . DASHBOARD_BASE_PATH );
    exit;
}


/*******************************************************************************
* User is authenticated as admin below this point                              *
*******************************************************************************/

//check for admin status and select available establishments accordingly
if ( $account['admin'] === true ) {
    $establishments = Dashboard::alphabetizeEstablishments(
        $datastore->retrieveAllEstablishments()
    );
} else {
    $establishments = Dashboard::alphabetizeEstablishments(
        $datastore->retrieveAccountEstablishments( $account['id'] )
    );
}

$pageData = array(
    'account' => &$account,
    'establishments' => &$establishments
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
          <h2 class="section-title"><?php echo $lang->get( 'ESTABLISHMENT_CREATE_SECTION_TITLE'); ?></h2>
          <div class="section-content">
            <div id="create-establishment-form">
            </div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
    </div>
<?php

/*******************************************************************************
* Footer and scripting starts here                                             *
*******************************************************************************/

require( LIB_DIR . 'frag/DashboardCreateEstablishmentFooter.php' );

?>
