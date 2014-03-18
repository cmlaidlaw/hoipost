<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Language.php' );

$lang = new Language();

if ( isset( $_GET['c'] ) ) {
    switch ( $_GET['c'] ) {
        default:
        case 'hk':
            $city = 'hk';
            break;
        case 'gz':
            $city = 'gz';
            break;
    }
} else {
    $city = 'hk';
}

$searchFrom = false;

if ( isset( $_GET['from'] ) ) {

    $searchFromId = Common::validateGlobalId(
        Common::extractGETValue( 'from' )
    );
    $searchFrom = $searchFromId;

    if ( $searchFromId !== null ) {

        require_once( LIB_DIR . 'Datastore.php' );
        require_once( LIB_DIR . 'obj/Object.php' );

        $datastore = new Datastore( $lang );


        $objData = $datastore->retrieveObject( $searchFromId );
        $obj = new Object( $datastore, $lang );
        $obj->load( $objData );

        if ( $obj->hasEstablishment() ) {
            $searchFrom = $lang->get( 'SEARCH_SEARCHING_FROM_OBJECT_LABEL' )
                        . $obj->getEstablishment()->getName();
        }

    }

}

$resultPage = Common::validateResultSetPage(
    Common::extractGETValue( 'p' )
);

$coordinates = Common::extractQueryStringCoordinates();

require( LIB_DIR . 'frag/SearchHeader.php' );


/*******************************************************************************
* Page content starts here                                                     *
*******************************************************************************/

?>
    <div id="content">
      <div class="centered">
        <div id="search" class="section-8-col"></div>
<?php if ( ALLOW_USER_MESSAGES ) { ?>
        <div id="post" class="section-8-col">
          <div class="section-content">
            <div id="message-form"></div>
            <div id="message-description">
              <h2 class="message-description-title"><?php echo $lang->get( 'MESSAGE_POST_TITLE' ); ?></h2>
              <p class="message-description-content"><?php echo $lang->get( 'MESSAGE_POST_DESCRIPTION' ); ?></p>
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
        <button id="ui-search-button" class="ui-button feature-button selected"><?php echo $lang->get( 'GLOBAL_SEARCH_LABEL' ); ?></button>
        <button id="ui-post-button" class="ui-button feature-button"><?php echo $lang->get( 'GLOBAL_POST_LABEL' ); ?></button>
<?php } ?>
        <div class="clear"></div>
        <!--<a class="city-option" href="../hk/" title="<?php echo $lang->get( 'CITIES_HONG_KONG' ); ?>"><?php echo $lang->get( 'CITIES_HONG_KONG' ); ?></a> |
        <a class="city-option" href="../gz/" title="<?php echo $lang->get( 'CITIES_GUANGZHOU' ); ?>"><?php echo $lang->get( 'CITIES_GUANGZHOU' ); ?></a>-->
      </div>
    </div>
<?php

/*******************************************************************************
* Footer and scripting starts here                                             *
*******************************************************************************/

require( LIB_DIR . 'frag/SearchFooter.php' );

?>
