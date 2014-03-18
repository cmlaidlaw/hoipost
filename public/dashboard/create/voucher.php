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

if ( !empty( $businesses ) ) {
    $currentEstablishment = $businesses[0];
}

$pageData = array(
    'account' => &$account,
    'businesses' => &$establishments,
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

Dashboard::renderAdminSection( $pageData, $lang );

?>
        <div class="create-business-section">
          <h2 class="dashboard-section-title"><?php echo $lang->get( 'ADMIN_CREATE_VOUCHER_TITLE'); ?></h2>
          <div class="dashboard-section-content">
            
            <form method="POST" action="<?php echo API_PATH; ?>obj/" enctype="multipart/form-data" autocomplete="off">
              <input type="hidden" name="requestMethod" value="PUT" />
              <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo UPLOAD_MAX_SIZE; ?>" />
              <table>
                  <tbody>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->get( 'BUSINESS_ASSIGN_TO_ACCOUNT_LABEL' ); ?></td>
                      <td><input id="business-owner" class="business-account-id" name="accountId" type="text" MAX_LENGTH="20" placeholder="<?php echo $lang->get( 'BUSINESS_ASSIGN_TO_ACCOUNT_PLACEHOLDER' ); ?>" />
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->get( 'BUSINESS_ASSIGN_TO_CITY_LABEL' ); ?></td>
                      <td>
                        <select id="business-city" class="business-city" name="city" placeholder="<?php echo $lang->get('BUSINESS_ASSIGN_TO_CITY_PLACEHOLDER' ); ?>">
<?php

$availableCities = explode( ',', BUSINESS_AVAILABLE_CITIES );

if ( is_array( $availableCities ) ) {

    foreach ( $availableCities as $city ) {

        echo '<option value="' . $city . '">' . $city .'</option>';
                            
    }

}

?>
                        </select>
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->get( 'BUSINESS_LAT_LABEL' ); ?></td>
                      <td><input id="business-lat" class="business-lat" name="lat" type="text" MAX_LENGTH="16" placeholder="<?php echo $lang->get( 'BUSINESS_LAT_PLACEHOLDER' ); ?>" />
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->get( 'BUSINESS_LNG_LABEL' ); ?></td>
                      <td><input id="business-lng" class="business-lng" name="lng" type="text" MAX_LENGTH="16" placeholder="<?php echo $lang->get( 'BUSINESS_LNG_PLACEHOLDER' ); ?>" />
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->get( 'BUSINESS_NAME_LABEL' ); ?></td>
                      <td class="business-form-content">
                        <input id="business-name" class="business-name" name="name" type="text" MAX_LENGTH="256" placeholder="<?php echo $lang->get( 'BUSINESS_NAME_PLACEHOLDER' ); ?>" />
                        <br />
                        <span class="business-form-meta"><?php echo $lang->get( 'BUSINESS_NAME_MAX_LENGTH' ); ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->GET( 'BUSINESS_LOGO_LABEL' ); ?></td>
                      <td class="business-form-content">
                        <input id="business-logo" name="logo" type="file" />
                        <br />
                        <span class="business-form-meta"><?php echo $lang->get( 'BUSINESS_LOGO_MAX_SIZE' ); ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->GET( 'BUSINESS_DESCRIPTION_LABEL' ); ?></td>
                      <td class="business-form-content">
                        <textarea id="business-description" name="description" placeholder="<?php echo $lang->get( 'BUSINESS_DESCRIPTION_PLACEHOLDER' ); ?>"></textarea>
                        <br />
                        <span class="business-form-meta"><?php echo $lang->get( 'BUSINESS_DESCRIPTION_MAX_LENGTH' ); ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->GET( 'BUSINESS_ADDRESS_LABEL' ); ?></td>
                      <td class="business-form-content">
                        <textarea id="business-address" name="address" placeholder="<?php echo $lang->get( 'BUSINESS_ADDRESS_PLACEHOLDER' ); ?>"></textarea>
                        <br />
                        <span class="business-form-meta"><?php echo $lang->get( 'BUSINESS_ADDRESS_MAX_LENGTH' ); ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->GET( 'BUSINESS_TEL_LABEL' ); ?></td>
                      <td class="business-form-content">
                        <input id="business-tel" name="tel" type="text" MAX_LENGTH="16" placeholder="<?php echo $lang->get( 'BUSINESS_TEL_PLACEHOLDER' ); ?>" />
                        <br />
                        <span class="business-form-meta"><?php echo $lang->get( 'BUSINESS_TEL_MAX_LENGTH' ); ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->GET( 'BUSINESS_EMAIL_LABEL' ); ?></td>
                      <td class="business-form-content">
                        <input id="business-email" name="email" type="text" MAX_LENGTH="256" placeholder="<?php echo $lang->get( 'BUSINESS_EMAIL_PLACEHOLDER' ); ?>" />
                        <br />
                        <span class="business-form-meta"><?php echo $lang->get( 'BUSINESS_EMAIL_MAX_LENGTH' ); ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td class="business-form-label"><?php echo $lang->GET( 'BUSINESS_URL_LABEL' ); ?></td>
                      <td class="business-form-content">
                        <input id="business-url" name="url" type="text" MAX_LENGTH="256" placeholder="<?php echo $lang->get( 'BUSINESS_URL_PLACEHOLDER' ); ?>" />
                        <br />
                        <span class="business-form-meta"><?php echo $lang->get( 'BUSINESS_URL_MAX_LENGTH' ); ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td class="business-form-label">
                      </td>
                      <td>
                        <input class="ui-button" type="submit" value="<?php echo $lang->get( 'ADMIN_CREATE_BUSINESS_LABEL' ); ?>" />
                        <a id="create-business-cancel-button" class="ui-button" href="<?php echo DASHBOARD_BASE_PATH; ?>" title="<?php echo $lang->get( 'ADMIN_CREATE_BUSINESS_CANCEL_LABEL' ); ?>"><?php echo $lang->get( 'ADMIN_CREATE_BUSINESS_CANCEL_LABEL' ); ?></a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php

/*******************************************************************************
* Footer and scripting starts here                                             *
*******************************************************************************/

require( LIB_DIR . 'frag/DashboardCreateEstablishmentFooter.php' );

?>
