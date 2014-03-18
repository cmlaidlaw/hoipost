    <div id="footer">
      <div class="centered">
      </div>
    </div>

    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/util-<?php echo UTIL_JS_LATEST_REVISION; ?>.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/CASH.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPCore.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPGeo.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/MessageForm.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/EstablishmentForm.js"></script>
    <script type="text/javascript">
      $( document ).ready( function () {

          <?php echo $lang->exportToJS( 'localization' ); ?>

          H = new HPCore('<?php echo BASE_PATH; ?>', localization);

          $( '#establishment-select-form' ).on( 'submit', function ( e ) {
              var establishmentObjectId = $( '#establishment-select-control' ).val();
              e.preventDefault();
              e.stopPropagation();
              e.cancelBubble = true;
              window.location.href = $( '#establishment-select-form' ).attr( 'action' ) + encodeURIComponent( establishmentObjectId ) + '/';
              return false;
          } );

<?php

$availableCities = explode( ',', ESTABLISHMENT_AVAILABLE_CITIES );
$cities = '';
if ( is_array( $availableCities ) ) {
    foreach ( $availableCities as $city ) {
        $cities[] = $city;
    }
}

$availableCategories = explode( ',', ESTABLISHMENT_AVAILABLE_CATEGORIES );
$categories = '';
if ( is_array( $availableCategories ) ) {
    foreach ( $availableCategories as $category ) {
        $categories[] = $category;
    }
}

$json = json_encode( array( 'admin' => true, 'availableCities' => $cities, 'availableCategories' => $categories ) );

?>

          FORM = new EstablishmentForm(
              document.getElementById( 'create-establishment-form' ),
              'establishment-input',
              function ( self, nextItem ) {
                  self.resource = '<?php echo API_PATH; ?>obj/';
                  nextItem();
              },
              false,
              25000,
              <?php echo $json; ?>
          );

      } );
    </script>

  </body>
</html>