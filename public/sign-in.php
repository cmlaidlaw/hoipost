<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'Auth.php' );

$lang = new Language();
$datastore = new Datastore( $lang );
$auth = new Auth( $datastore, $lang );

//redirect authenticated users to the dashboard
if ( $auth->checkAuthentication() ) {
    header( 'Location: ' . DASHBOARD_BASE_PATH );
    exit;
}

/*******************************************************************************
* User is not authenticated below this point                                   *
*******************************************************************************/

require( LIB_DIR . 'frag/AuthHeader.php' );

/*******************************************************************************
* Page content starts here                                                     *
*******************************************************************************/
?>
    <div id="content">
      <div class="centered">
        <div class="section-3-col">
          <h2 class="section-title">Sign In</h2>
          <div class="section-content">
            <div class="section-content-3-col">
              <form id="sign-in-form" method="POST" action="<?php echo API_AUTH_SIGN_IN_GATEWAY; ?>" autocomplete="off">
                <table>
                  <tbody>
                    <tr>
                      <td><label class="form-label-3-col" for="sign-in-email"><?php echo $lang->get( 'AUTH_EMAIL_LABEL' ); ?></label></td>
                      <td><input id="sign-in-email" class="form-input-3-col" type="text" name="email" placeholder="<?php echo $lang->get( 'AUTH_EMAIL_PLACEHOLDER' ); ?>" /></td>
                    </tr>
                    <tr>
                      <td><label class="form-label-3-col" for="sign-in-password"><?php echo $lang->get( 'AUTH_PASSWORD_LABEL' ); ?></label></td>
                      <td><input id="sign-in-password" class="form-input form-input-3-col" type="password" name="password" placeholder="<?php echo $lang->get( 'AUTH_PASSWORD_PLACEHOLDER' ); ?>" /></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><input class="ui-button ui-button-green" type="submit" value="<?php echo $lang->get( 'AUTH_SIGN_IN_LABEL' ); ?>" /></td>
                    </tr>
                  </tbody>
                </table>
              </form>
            </div>
            <div class="clear"></div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
    </div>
<?php

/*******************************************************************************
* Footer and scripting starts here                                             *
*******************************************************************************/

require( LIB_DIR . 'frag/AuthFooter.php' );

?>
