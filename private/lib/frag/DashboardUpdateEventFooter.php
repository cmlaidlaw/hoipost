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
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/EventForm.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/pikaday.js"></script>
    
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

          POST = new EventForm(
              document.getElementById( 'event-form' ),
              'event-input',
              function ( self, nextItem ) {
                  self.resource = '<?php echo API_PATH; ?>obj/<?php echo $pageData['currentEvent']['id'] ?>/update/';
                  nextItem();
              },
              false,
              25000,
              <?php echo $pageData['currentEvent']['json']; ?>
          );

      } );
    </script>

  </body>
</html>