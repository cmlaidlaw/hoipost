<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Language.php' );

$lang = new Language();

$objectId = '00000000000000000000';

if ( isset( $_GET['id'] ) ) {

    if ( strlen( $_GET['id'] ) === 4 ) {

        require_once( '/PATH/TO/conf/ShortCodes.php' );

        $code = Common::cleanShortCode( $_GET['id'] );

        if ( $code !== null && isset( $shortCodes[$code] ) ) {
            $objectId = $shortCodes[$code];
        }

    } else {
        $objectId = Common::validateGlobalId( $_GET['id'] );
    }

}

$currentPage = Common::validateInt(
    Common::extractGETValue( 'page' )
);

if ( $currentPage === null ) {
    $currentPage = 1;
}

$coordinates = Common::extractQueryStringCoordinates();

require( LIB_DIR . 'frag/ObjectHeader.php' );


/*******************************************************************************
* Page content starts here                                                     *
*******************************************************************************/

?>
    <div id="content">
      <div class="centered">


        <div id="object-view" class="section-8-col">
          <div id="object" class="section-content-5-col">
            <div id="object-content"></div>
            <div class="loading-notification"></div>
            <div class="empty-notification"></div>
            <div class="error-notification"></div>
          </div>
          <div id="object-meta" class="section-content-3-col"></div>
        </div>
<?php if ( ALLOW_USER_MESSAGES ) { ?>
          <div id="object-replies" class="section-8-col"></div>
          <div id="post" class="section-8-col">
            <div class="section-content">
              <div id="message-form"></div>
              <div id="message-description">
                <h2 class="message-description-title"><?php echo $lang->get( 'REPLY_POST_TITLE' ); ?></h2>
                <p class="message-description-content"><?php echo $lang->get( 'REPLY_POST_DESCRIPTION' ); ?></p>
                <div class="message-description-tos">
                  <?php echo $lang->get( 'GLOBAL_POST_TOS' ); ?>
                </div>
              </div>
              <div class="clear"></div>
              <div id="message-bottom"></div>
            </div>
            <span id="ui-post-hint"></span>
          </div>
          <div class="clear"></div>
          <button id="ui-search-button" class="ui-button feature-button selected"><?php echo $lang->get( 'GLOBAL_REPLIES_LABEL' ); ?></button>
          <button id="ui-post-button" class="ui-button feature-button"><?php echo $lang->get( 'GLOBAL_REPLY_LABEL' ); ?></button>
          <a id="ui-dashboard-button" class="ui-button feature-button" href="<?php echo DASHBOARD_BASE_PATH; ?>" title="<?php echo $lang->get( 'GLOBAL_DASHBOARD_LABEL' ); ?>"><?php echo $lang->get( 'GLOBAL_DASHBOARD_LABEL' ); ?></a>
<?php } ?>
          <div class="clear"></div>
        </div>


    </div>
<?php

/*******************************************************************************
* Footer and scripting starts here                                             *
*******************************************************************************/

require( LIB_DIR . 'frag/ObjectFooter.php' );

?>
