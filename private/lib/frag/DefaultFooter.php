    <div id="footer">
      <div class="centered">
      </div>
    </div>

    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/util-<?php echo UTIL_JS_LATEST_REVISION; ?>.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/CASH.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPCore.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PATH; ?>js/HPGeo.js"></script>
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
        
<?php if ( $message ) { ?>
        UI.setCurrentMessage( <?php echo $message; ?> );
<?php } ?>

        $( '#logo' ).button( H.has.touch, function ( button ) {
            document.location.href = H.baseURL;
        }, true );

        $( '#post-button' ).button( H.has.touch, function ( button ) {
            UI.showPostForm();
        }, true );

        $( '#cancel-button' ).button( H.has.touch , function ( button ) {
            UI.hidePostForm();
        }, true );

        POST = new HPMessageForm(
            document.getElementById( 'post-dialog' ),
            'input',
            function ( resource, nextItem ) {
<?php if ( $message ) { ?>
                resource.resource = '<?php echo API_PATH; ?>messages/<?php echo $message; ?>/replies/';
<?php } else { ?>
                //resource.resource = '<?php echo API_PATH; ?>messages/';
                resource.resource = '<?php echo BASE_PATH; ?>api/1.1/create/message/';
<?php } ?>
                nextItem();
            },
<?php if ( $message ) { ?>
            <?php echo $message; ?>,
<?php } else { ?>
            false,
<?php } ?>
            false,
            25000
        );

        HAPPENINGS = new HPHappeningFeed(
            //the element we'll use as the MessageFeed's container
            document.getElementById( 'happenings' ),
            //title of the MessageFeed
            'Happenings',
            //function to set the resource to which we will make the request (this is a function in order to allow conditional logic)
            function ( nextItem ) {
                var num = ( H.is.compact ) ? 10 : 20;
                H.getGeoLocation(
                    function ( latLng ) {
                        HAPPENINGS.resource = '<?php echo API_PATH; ?>happenings/?lat=' + latLng.lat + '&lng=' + latLng.lng + '&max=' + num;
                        nextItem();
                    },
                    function ( error ) {
                        HAPPENINGS.resource = '<?php echo API_PATH; ?>happenings/?max=' + num;
                        nextItem();
                    }
                );
            },
            //anonymous object containing callbacks to handle remote request outcomes
            false,
            //timeout before aborting the remote request and executing the error callback
            5000
        );

        MESSAGES = new HPMessageFeed(
            //the element we'll use as the MessageFeed's container
            document.getElementById( 'messages' ),
            //title of the MessageFeed
            'Messages',
            //function to set the resource to which we will make the request (this is a function in order to allow conditional logic)
            function ( nextItem ) {
                var num = ( H.is.compact ) ? 10 : 20;
                H.getGeoLocation(
                    function ( latLng ) {
                        MESSAGES.resource = '<?php echo API_PATH; ?>messages/?lat=' + latLng.lat + '&lng=' + latLng.lng + '&max=' + num;
                        nextItem();
                    },
                    function ( error ) {
                        MESSAGES.resource = '<?php echo API_PATH; ?>messages/?max=' + num;
                        nextItem();
                    }
                );
            },
            //anonymous object containing callbacks to handle remote request outcomes
            false,
            //timeout before aborting the remote request and executing the error callback
            5000
        );
        
        MESSAGE = new HPMessageView(
            //the element we'll use as the MessageView's container
            document.getElementById( 'message' ),
            //title of the MessageView
            'Message',
            //function to set the resource to which we will make the request (this is a function in order to allow conditional logic)
            function ( nextItem ) {
                H.getGeoLocation(
<?php if ( $happening ) { ?>
                    function ( latLng ) {
                        MESSAGE.resource = '<?php echo API_PATH; ?>happenings/' + UI.currentMessage + '/?lat=' + latLng.lat + '&lng=' + latLng.lng;
                        nextItem();
                    }, function ( error ) {
                        MESSAGE.resource = '<?php echo API_PATH; ?>happenings/' + UI.currentMessage + '/';
                        nextItem();
                    }
<?php } else { ?>
                    function ( latLng ) {
                        MESSAGE.resource = '<?php echo API_PATH; ?>messages/' + UI.currentMessage + '/?lat=' + latLng.lat + '&lng=' + latLng.lng;
                        nextItem();
                    }, function ( error ) {
                        MESSAGE.resource = '<?php echo API_PATH; ?>messages/' + UI.currentMessage + '/';
                        nextItem();
                    }
<?php } ?>
                );
            },
            //anonymous object containing callbacks to handle remote request outcomes
            {
                success : function ( data ) {
                    if ( MESSAGE.repliesAllowed ) {
                        REPLIES.load();
                        UI.enablePostButton();
                    } else {
                        REPLIES.disable();
                        UI.disablePostButton();
                    }
                }
            },
            //timeout before aborting the remote request and executing the error callback
            5000
        );

        REPLIES = new HPMessageFeed(
            //the element we'll use as the MessageFeed's container
            document.getElementById( 'replies' ),
            //title of the MessageFeed
            'Replies',
            //function to set the resource to which we will make the request (this is a function in order to allow conditional logic)
            function ( nextItem ) {
                H.getGeoLocation(
<?php if ( $happening ) { ?>
                    function ( latLng ) {
                        REPLIES.resource = '<?php echo API_PATH; ?>happenings/' + UI.currentMessage + '/replies/?lat=' + latLng.lat + '&lng=' + latLng.lng;
                        nextItem();
                    },
                    function ( error ) {
                        REPLIES.resource = '<?php echo API_PATH; ?>happenings/' + UI.currentMessage + '/replies/';
                        nextItem();
                    }
<?php } else { ?>
                    function ( latLng ) {
                        REPLIES.resource = '<?php echo API_PATH; ?>messages/' + UI.currentMessage + '/replies/?lat=' + latLng.lat + '&lng=' + latLng.lng;
                        nextItem();
                    },
                    function ( error ) {
                        REPLIES.resource = '<?php echo API_PATH; ?>messages/' + UI.currentMessage + '/replies/';
                        nextItem();
                    }
<?php } ?>
                );
            },
            //anonymous object containing callbacks to handle remote request outcomes
            false,
            //timeout before aborting the remote request and executing the error callback
            5000
        );

        if ( UI.currentMessage ) {
            MESSAGE.load();
        } else {
            HAPPENINGS.load( function () {
                MESSAGES.load();
            } );
        }

    });    
    </script>
  </body>
</html>
