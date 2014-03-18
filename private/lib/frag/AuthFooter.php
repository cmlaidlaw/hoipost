    <div id="footer">
      <div class="centered">
      </div>
    </div>

    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/util-<?php echo UTIL_JS_LATEST_REVISION; ?>.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/CASH.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPCore.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPGeo.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObject.js"></script>
    <script type="text/javascript">

    <?php echo $lang->exportToJS( 'localization' ); ?>

    $( document ).ready( function () {

        //instantiate the core Hoipost object
        H = new HPCore('<?php echo BASE_PATH; ?>', localization);
        if ( H.is.ios || H.is.android ) {
            //hide the address bar in mobile browsers
            setTimeout( function(){ window.scrollTo(0, 1); }, 0 );
        }
        if ( H.is.compact ) {
            $( document.getElementsByTagName( 'body' )[0] ).addClass( 'compact' );
        }
        
        $( '#logo' ).button( H.has.touch, function ( button ) {
            document.location.href = H.baseURL;
        }, true );

    });    
    </script>
  </body>
</html>
