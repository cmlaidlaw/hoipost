<?php

require_once( '/PATH/TO/Common.php' );
require_once( LIB_DIR . 'Language.php' );
require_once( LIB_DIR . 'Datastore.php' );
require_once( LIB_DIR . 'obj/Object.php' );

$lang = new Language();
$datastore = new Datastore( $lang );

$establishmentId = null;

if ( isset( $_GET['id'] ) ) {

    if ( strlen( $_GET['id'] ) === 4 ) {

        require_once( '/PATH/TO/conf/ShortCodes.php' );

        $code = Common::cleanShortCode( $_GET['id'] );

        if ( $code !== null && isset( $shortCodes[$code] ) ) {
            $establishmentId = $shortCodes[$code];
        }

    } else {
        $establishmentId = Common::validateGlobalId( $_GET['id'] );
    }

}

if ( $establishmentId !== null ) {

    $serviceLevel = $datastore->retrieveEstablishmentServiceLevel( $establishmentId );

    switch ( $serviceLevel ) {
        default:
        case 0:
            $maxHappenings = SERVICE_LEVEL_0_MAX_EVENTS;
            break;
        case 1:
            $maxHappenings = SERVICE_LEVEL_1_MAX_EVENTS;
            break;
        case 2:
            $maxHappenings = SERVICE_LEVEL_2_MAX_EVENTS;
            break;
    }

    $establishment = new Object( $datastore, $lang );
    $establishment->load( $datastore->retrieveObject( $establishmentId ) );
    $establishmentName = $establishment->getEstablishment()->getName();

    $happenings = $datastore->retrieveEstablishmentEvents( $establishmentId );

    //consolidate happenings as if it were the result of an API call
    foreach ( $happenings as $index => $happening ) {

        $obj = new Object( $datastore, $lang );
        $obj->load( $happenings[$index] );
        $happenings[$index] = $obj->exportToAPI();

    }

}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCode(); ?>" xml:lang="<?php echo $lang->getCode(); ?>">
  <head>
    <title>Submit a Happening to Hoipost</title>
    <meta name="description" content="Format happening information" />
    <meta name="keywords" content="" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"  />
    <script type="text/javascript">if (top != self) { top.location.replace(self.location.href); }</script>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_PATH; ?>css/main-31072013.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_PATH; ?>css/pikaday-04072013.css" />
    <!--[if lte IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_PATH; ?>css/ie8-29052013.css" />
    <![endif]-->
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_PATH; ?>img/favicon.png" />
  </head>
  <body>
    <div id="masthead">
      <div class="centered">
        <a id="logo" href="<?php echo BASE_PATH; ?>" title="Home">
          <h1>Hoipost</h1>
        </a>
        <div class="clear"></div>
      </div>
    </div>
    <div id="content">
      <div class="centered">
<?php if ( $establishmentId !== null ) { ?>
        <div id="formatter" class="section-8-col">
          <h2 class="section-title">Create a happening at <?php echo $establishmentName; ?></h2>
          <div class="section-content-4-col">
            <div id="formatter-input"></div>
          </div>
          <div class="section-content-4-col">
            <div id="formatter-success">
              <!--<span id="formatter-success-hint"></span>
              <h3 id="formatter-success-title">Ready to submit!</h3>-->
              <div class="clear"></div>
              <a id="formatter-output-email-button" class="ui-button ui-button-green" href="#" title="Submit Happening by e-mail"><span class="ui-button-icon">&#9993;</span> Submit Happening by e-mail</a>
              <p class="formatter-success-body">If you're having trouble using the button, you can also submit this Happening manually by copying the text from the box below into an e-mail addressed to <em>happenings@hoipost.com</em>.</p>
              <textarea id="formatter-output" placeholder="Happening code will appear here after pressing the 'Format Now' button"></textarea>
            </div>
          </div>
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
<?php } else { ?>
        <div id="formatter-error" class="section-8-col">
          <h2 class="section-title">Error</h2>
          <div class="section-content-8-col">
            Sorry, there was an error. Please ensure the link you are using is the one which was assigned to you.
          </div>
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
<?php } ?>
      </div>
    </div>
    <div id="footer">
      <div class="centered">
      </div>
    </div>

    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/util-29052013.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/CASH.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPCore.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPGeo.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObject.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/MessageForm.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/EventFormatter.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/pikaday.js"></script>
    <script type="text/javascript">

    <?php echo $lang->exportToJS( 'localization' ); ?>

    $( document ).ready( function () {

        H = new HPCore('<?php echo BASE_PATH; ?>', localization);
        if ( H.is.ios || H.is.android ) {
            //hide the address bar in mobile browsers
            setTimeout( function(){ window.scrollTo(0, 1); }, 0 );
        }

        POST = new EventFormatter(
            document.getElementById( 'formatter-input' ),
            'formatter-input',
            document.getElementById( 'formatter-output' ),
            'formatter-output',
            '<?php echo $establishmentId; ?>',
            <?php echo $maxHappenings; ?>,
            <?php echo json_encode( $happenings ); ?>
        );

    });
    </script>
  </body>
</html>
