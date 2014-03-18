    <div id="footer">
      <div class="centered">
      </div>
    </div>

    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/util-<?php echo UTIL_JS_LATEST_REVISION; ?>.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/CASH.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPCore.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPGeo.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObject.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObjectCollectionView.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/SearchResults.js"></script>
<?php if ( ALLOW_USER_MESSAGES ) { ?>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/MessageForm.js"></script>
<?php } ?>
    <script type="text/javascript">

    <?php echo $lang->exportToJS( 'localization' ); ?>


    $( document ).ready( function () {

        H = new HPCore('<?php echo BASE_PATH; ?>', localization);
        if ( H.is.ios || H.is.android ) {
            //hide the address bar in mobile browsers
            setTimeout( function(){ window.scrollTo(0, 1); }, 0 );
        }
<?php

if ( $coordinates !== null && ( time() - $coordinates['lastUpdate'] ) < 300 ) {
    $params = $coordinates['lat']
            . ', ' . $coordinates['lng']
            . ', ' . $coordinates['lastUpdate'];
?>
        H.setGeoLocation( <?php echo $params; ?> );
<?php
}
?>

<?php if ( ALLOW_USER_MESSAGES ) { ?>
        $( '#ui-search-button' ).button( H.has.touch, function ( button ) {
            $( '#search' ).show();
            $( '#post' ).hide();
            $( '#ui-post-button' ).removeClass( 'selected' );
            $( button ).addClass( 'selected' );
        }, true );

        $( '#ui-post-button' ).button( H.has.touch, function ( button ) {
            $( '#search' ).hide();
            $( '#post' ).show();
            $( '#ui-search-button' ).removeClass( 'selected' );
            $( button ).addClass( 'selected' );
        }, true );
<?php } ?>

        SEARCH = new SearchResults(
            document.getElementById( 'search' ),
            'search',
            function ( self, nextItem ) {
                H.getGeoLocation(
                    function ( latLng ) {
                        self.resource = '<?php echo API_PATH; ?>search/?city=<?php echo $city; ?>&page=<?php echo $resultPage; ?>&count=18' + H.geo.formatQueryStringParams();
                        nextItem();
                    },
                    function ( error ) {
                        self.resource = '<?php echo API_PATH; ?>search/?city=<?php echo $city; ?>&page=<?php echo $resultPage; ?>&count=18' + H.geo.formatQueryStringParams();
                        nextItem();
                    }
                );
            },
            false,
<?php if ( $searchFrom !== false ) {
    echo "'" . $searchFrom . "',\n";
} else {
    echo "false,\n";
} ?>
            <?php echo $resultPage; ?>,
            <?php echo Common::exportSearchCenterPoints( $city ); ?>,
            5000
        );

<?php if ( ALLOW_USER_MESSAGES ) { ?>
        POST = new MessageForm(
            document.getElementById( 'message-form' ),
            'message-input',
            function ( self, nextItem ) {
                H.getGeoLocation(
                    function ( latLng ) {
                        self.resource = '<?php echo API_PATH; ?>obj/';
                        nextItem();
                    },
                    function ( error ) {
                        self.resource = '<?php echo API_PATH; ?>obj/';
                        nextItem();
                    }
                );
            },
            false,
            25000
        );
<?php } ?>
        SEARCH.init();
<?php if ( ALLOW_USER_MESSAGES ) { ?>
        POST.init();
<?php } ?>
    });
    </script>
  </body>
</html>
