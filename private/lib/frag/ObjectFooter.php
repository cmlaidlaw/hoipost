    <div id="footer">
      <div class="centered">
      </div>
    </div>

    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/util-<?php echo UTIL_JS_LATEST_REVISION; ?>.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/CASH.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPCore.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPGeo.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObject.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/ObjectResult.js"></script>
<?php if ( ALLOW_USER_MESSAGES ) { ?>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/MessageForm.js"></script>
<?php } ?>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObjectView.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObjectMetaView.js"></script>
<?php if ( ALLOW_USER_MESSAGES ) { ?>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObjectCollectionView.js"></script>
<?php } ?>
    <script type="text/javascript">

    <?php echo $lang->exportToJS( 'localization' ); ?>


    $( document ).ready( function () {

        //instantiate the core Hoipost object
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
<?php if ( $objectId !== null ) { ?>

<?php if ( ALLOW_USER_MESSAGES ) { ?>

        $( '#ui-search-button' ).button( H.has.touch, function ( button ) {
            $( '#object-replies' ).show();
            $( '#post' ).hide();
            $( '#ui-post-button' ).removeClass( 'selected' );
            $( button ).addClass( 'selected' );
        }, true );

        $( '#ui-post-button' ).button( H.has.touch, function ( button ) {
            $( '#object-replies' ).hide();
            $( '#post' ).show();
            $( '#ui-search-button' ).removeClass( 'selected' );
            $( button ).addClass( 'selected' );
        }, true );

<?php } ?>

        OBJECT = new ObjectResult(
            document.getElementById( 'object' ),
            'object',
            function ( self, nextItem ) {
                H.getGeoLocation(
                    function ( latLng ) {
                        self.resource = '<?php echo API_PATH; ?>obj/<?php echo $objectId; ?>/?' + H.geo.formatQueryStringParams();
                        nextItem();
                    },
                    function ( error ) {
                        self.resource = '<?php echo API_PATH; ?>obj/<?php echo $objectId; ?>/?' + H.geo.formatQueryStringParams();
                        nextItem();
                    }
                );
            },
            false,
<?php if ( ALLOW_USER_MESSAGES ) { ?>
            function ( self, nextItem ) {
                H.getGeoLocation(
                    function (latLng ) {
                        self.replyResource = '<?php echo API_PATH; ?>obj/<?php echo $objectId; ?>/replies/?page=<?php echo $currentPage; ?>&count=10' + H.geo.formatQueryStringParams();
                        nextItem();
                    },
                    function ( error ) {
                        self.replyResource = '<?php echo API_PATH; ?>obj/<?php echo $objectId; ?>/replies/?page=<?php echo $currentPage; ?>&count=10' + H.geo.formatQueryStringParams();
                        nextItem();
                    }
                );
            },
            <?php echo $currentPage; ?>,
            false,
            <?php echo Common::exportSearchCenterPoints(); ?>,
<?php } else { ?>
	    false,
	    false,
	    false,
            false,
<?php } ?>
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
            {replyTo: '<?php echo $objectId; ?>'},
            25000
        );
<?php } ?>
        OBJECT.init();

<?php } ?>

    });
    </script>
  </body>
</html>
