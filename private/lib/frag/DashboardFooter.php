    <div id="footer">
      <div class="centered">
      </div>
    </div>

    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/util-<?php echo UTIL_JS_LATEST_REVISION; ?>.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/CASH.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPCore.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPGeo.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPObject.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/MessageForm.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/EstablishmentForm.js"></script>
    <script type="text/javascript">
      $( document ).ready( function() {

          <?php echo $lang->exportToJS( 'localization' ); ?>

          H = new HPCore('<?php echo BASE_PATH; ?>', localization);

          $( '#establishment-select-form' ).on( 'submit', function( e ) {
              var city = $( '#city-select-control' ).val(),
                  establishmentObjectId = $( '#establishment-select-control' ).val();
              $.prototype.halt.call( null, e );
              window.location.href = $( '#establishment-select-form' ).attr( 'action' ) + encodeURIComponent( establishmentObjectId ) + '/?c=' + city;
              return false;
          } );

          $( '#update-establishment-button' ).button( H.has.touch, function( button ) {
              $( '#establishment-info' ).hide();
              $( '#establishment-form' ).show();
          } );

<?php

if ( isset( $pageData['currentEstablishment'] ) ) {

    require_once( LIB_DIR . 'obj/Object.php' );

    $obj = new Object( $datastore, $lang );
    $obj->load( $pageData['currentEstablishment'] );
    $establishmentObject = $obj->exportToAPI();
    if ( $pageData['account']['admin'] === true ) {
        $establishmentObject['admin'] = true;
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
        $establishmentObject['availableCities'] = $cities;
        $establishmentObject['availableCategories'] = $categories;
        $establishmentObject['accountId'] = $pageData['currentEstablishment']
                                            ['establishment']['accountId'];
    }
    $establishmentObject = json_encode( $establishmentObject );

    $events = '          var events=[';

    if ( isset( $pageData['currentEstablishment']['events'] )
         && count( $pageData['currentEstablishment']['events'] ) > 0 ) {

        require_once( LIB_DIR . 'obj/Object.php' );

        try {

            foreach ( $pageData['currentEstablishment']['events'] as $event ) {
                $obj = new Object( $datastore, $lang );
                $obj->load( $event );
                $events .= json_encode( $obj->exportToAPI() ) . ',';
            }
            unset( $obj );
            $events = substr( $events, 0, strlen( $events ) -1 );
        
        } catch ( Exception $e ) {
            if ( DEBUG ) {
                echo var_dump( $e->getMessage() );
            } else {
                Common::logError( $e->getMessage(), $datastore );
            }
        }

    }

$events .= '];';

?>

          FORM = new EstablishmentForm(
              document.getElementById( 'establishment-form' ),
              'establishment-input',
              function( self, nextItem ) {
                  self.resource = '<?php echo API_PATH; ?>obj/<?php echo $pageData['currentEstablishment']['id']; ?>/update/';
                  nextItem();
              },
              false,
              25000,
              <?php echo $establishmentObject; ?>
          );

          <?php echo $events; ?>
          var i = 0, eventCount = events.length, html;
          for ( i = 0; i < eventCount; i++ ) {
              html = HPObject.prototype.renderObject.call(
                  null, events[i], false, false, false, 150
              );
              $( '#obj-' + events[i].id ).replaceContent( html );
          }

<?php } ?>

      } );
    </script>

  </body>
</html>
