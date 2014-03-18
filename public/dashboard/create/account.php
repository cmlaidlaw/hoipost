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

//check for admin status and select available businesses accordingly
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
          <h2 class="section-title"><?php echo $lang->get( 'ACCOUNT_CREATE_SECTION_TITLE'); ?></h2>
          <div class="section-content">
            <div class="section-content-4-col">
              <form method="POST" action="<?php echo API_PATH; ?>account/create/" enctype="multipart/form-data" autocomplete="off">
                <table>
                    <tbody>
                      <tr>
                        <td><label class="form-label-4-col" for="account-email"><?php echo $lang->get( 'ACCOUNT_EMAIL_LABEL' ); ?></label></td>
                        <td><input id="account-email" class="form-input-4-col" name="email" type="text" MAX_LENGTH="256" placeholder="<?php echo $lang->get( 'ACCOUNT_EMAIL_PLACEHOLDER' ); ?>" /></td>
                      </tr>
                      <tr>
                        <td><label class="form-label-4-col" for="account-password"><?php echo $lang->get( 'ACCOUNT_PASSWORD_LABEL' ); ?></label></td>
                        <td><input id="account-password" class="form-input-4-col" name="password" type="text" MAX_LENGTH="256" placeholder="<?php echo $lang->get( 'ACCOUNT_PASSWORD_PLACEHOLDER' ); ?>" /></td>
                      </tr>
                      <tr>
                        <td><label class="form-label-4-col" for="account-admin"><?php echo $lang->get( 'ACCOUNT_ADMIN_LABEL' ); ?></label></td>
                        <td>
                          <fieldset id="account-admin" class="form-fieldset">
                            <input id="account-admin-true" class="form-radio" name="admin" type="radio" value="1" />
                            <label for="account-admin-true"><?php echo $lang->get( 'ACCOUNT_ADMIN_TRUE_LABEL' ); ?></label><br />
                            <input id="account-admin-false" class="form-radio" name="admin" type="radio" value="0" checked/>
                            <label for="account-admin-true"><?php echo $lang->get( 'ACCOUNT_ADMIN_FALSE_LABEL' ); ?></label>
                          </fieldset>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <input class="ui-button" type="submit" value="<?php echo $lang->get( 'ACCOUNT_CREATE_SUBMIT_LABEL' ); ?>" />
                        </td>
                    </tbody>
                  </table>
                </form>
              </div>
              <div class="section-content-4-col">
              </div>  
              <div class="clear"></div>
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

require( LIB_DIR . 'frag/DashboardFooter.php' );

?>
