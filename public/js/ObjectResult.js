ObjectResult = function( element, name, resourceFn, callbacks,
                         replyResourceFn, replyResultPage, replyCallbacks,
                         centerPoints, timeout ) {

    'use strict';

    //input properties
    this.element = element;
    this.name = name;
    this.computeResource = resourceFn;
    this.resource = false;
    this.successCallback = callbacks.success || function() { return true; };
    this.errorCallback = callbacks.error || function() { return true; };
    this.progressCallback = callbacks.progress || function() { return true; };
    this.computeReplyResource = replyResourceFn;
    this.replyResource = false;
    this.replySuccessCallback = replyCallbacks.success || function() { return true; };
    this.replyErrorCallback = replyCallbacks.error || function() { return true; };
    this.replyProgressCallback = replyCallbacks.progress || function() { return true; };

    this.timeout = timeout || 5000;
    this.errorCode = false;
    this.status = false;

    this.object = null;

    this.view = new HPObjectView(
        this.element,
        this.name
    );

    this.objectRenderFn = function( next ) {
        if ( typeof next !== 'function' ) {
            next = function() {};
        }
        next();
    };

    this.metaView = new HPObjectMetaView(
        document.getElementById( this.name + '-meta' ),
        this.name + '-meta'
    );

    this.metaRenderFn = function( next ) {
        if ( typeof next !== 'function' ) {
            next = function() {};
        }
        next();
    };

    if ( typeof this.computeReplyResource === 'function' ) {

        this.replyObjects = [];
        this.replyResultPage = replyResultPage;
        this.replyPageCount = 0;

        this.repliesView = new HPObjectCollectionView(
            document.getElementById( this.name + '-replies' ),
            this.name + '-replies',
            false,
            false
        );

        this.repliesRenderFn = function( next ) {
            if ( typeof next !== 'function' ) {
                next = function() {};
            }
            next();
        };

    }

};

ObjectResult.prototype.init = function() {

    'use strict';

    this.load();

};

ObjectResult.prototype.load = function() {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        q,
        headers,
        actions,
        l = window._H.localization;

    //this item: calculates remote resource to hit for data
    Q.addItem( function( nextItem ) {
        that.computeResource( that, nextItem );
        that.view.updateStatus( 'loading', l['GLOBAL_LOADING_OBJECT_LABEL'] );
        that.metaView.updateStatus( 'loading' );
	if ( typeof that.computeReplyResource === 'function' ) {
            that.repliesView.updateStatus( 'loading', l['GLOBAL_LOADING_REPLIES_LABEL'] );
	}
    });

    //this item: makes remote request
    Q.addItem( function( nextItem ) {
        headers = [{type : 'Accept', parameter : 'application/json'}];
        actions = {
            200 : function(data) {

                q = new CASHQueue();

                q.addItem( function( nextInnerItem ) {
                    that.errorCode = false;
                    that.processObject( data, nextInnerItem );
                } );

                q.addItem( function( nextInnerItem ) {
                    that.objectRenderFn = function( next ) {
                        if ( typeof next !== 'function' ) {
                            next = function() {};
                        }
                        that.view.render(
                            that.object,
                            window._H.geo,
                            false,
                            true,
                            next
                        );
                    };
                    that.objectRenderFn( nextInnerItem );
                } );

                q.addItem( function( nextInnerItem ) {
                    that.view.updateStatus( 'success' );
                    that.metaRenderFn = function( next ) {
                        if ( typeof next !== 'function' ) {
                            next = function() {};
                        }
                        that.metaView.render(
                            that.object,
                            window._H.geo,
                            next
                        );
                    };
                    that.metaRenderFn( nextInnerItem );
                } );

                q.addItem( function( nextInnerItem ) {
                    that.metaView.updateStatus( 'success' );
                    nextInnerItem();
                } );

                if ( typeof that.computeReplyResource === 'function' ) {
                    q.addItem( function( nextInnerItem ) {
                        that.loadReplies();
		        nextItem();
                    } );
                }

                q.execute();

            },
            400 : function() {
                that.errorCode = 400;
                that.view.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                that.metaView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
		if ( typeof that.computeReplyResouce === 'function' ) {
                    that.repliesView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
		}
                nextItem();
            },
            404 : function() {
                that.errorCode = 404;
                that.view.updateStatus( 'error', l['GLOBAL_EMPTY_OBJECT_LABEL'] );
                that.metaView.updateStatus( 'error', l['GLOBAL_EMPTY_OBJECT_LABEL'] );
                if ( typeof that.computeReplyResource === 'function' ) {
		    that.repliesView.updateStatus( 'error', l['GLOBAL_EMPTY_OBJECT_LABEL'] );
                }
		nextItem();
            },
            500 : function() {
                that.errorCode = 500;
                that.view.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                that.metaView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                if ( typeof that.computeReplyResource === 'function' ) {
                    that.repliesView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                }
		nextItem();
            },
            otherwise : function() {
                that.errorCode = -1;
                that.view.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                that.metaView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                if ( typeof that.computeReplyResource === 'function' ) {
		    that.repliesView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                }
		nextItem();
            }
        };

        CASHAjax( 'GET', that.resource, headers, false, actions, that.progressCallback, that.timeout);

    });

    Q.execute();

};

ObjectResult.prototype.loadReplies = function() {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        q,
        headers,
        actions,
        l = window._H.localization;

    //this item: calculates remote resource to hit for data
    Q.addItem( function( nextItem ) {
        that.computeReplyResource( that, nextItem );
    });

    //this item: makes remote request
    Q.addItem( function( nextItem ) {
        headers = [{type : 'Accept', parameter : 'application/json'}];
        actions = {
            200 : function(data) {

                q = new CASHQueue();

                q.addItem( function( nextInnerItem ) {
                    that.errorCode = false;
                    that.processObjects( data, nextInnerItem );
                } );

                q.addItem( function( nextInnerItem ) {
                    that.repliesRenderFn = function( next ) {
                        if ( typeof next !== 'function' ) {
                            next = function() {};
                        }
                        that.repliesView.render(
                            that.replyObjects,
                            that.replyResultPage,
                            that.replyPageCount,
                            window._H.baseURL + that.object.id + '/',
                            window._H.geo,
                            next
                        );
                    };
                    that.repliesRenderFn( nextInnerItem );
                } );

                q.addItem( function( nextInnerItem ) {
                    that.repliesView.updateStatus( 'success' );
                    nextItem();
                } );

                q.execute();

            },
            204 : function() {
                that.errorCode = false;
                that.repliesView.updateStatus( 'empty', l['GLOBAL_EMPTY_REPLIES_LABEL'] );
                nextItem();
            },
            400 : function() {
                that.errorCode = 400;
                that.repliesView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                nextItem();
            },
            500 : function() {
                that.errorCode = 500;
                that.repliesView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                nextItem();
            },
            otherwise : function() {
                that.errorCode = -1;
                that.repliesView.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                nextItem();
            }
        };

        CASHAjax( 'GET', that.replyResource, headers, false, actions, that.progressCallback, that.timeout);

    });

    Q.execute();

};

ObjectResult.prototype.processObject = function( data, nextItem ) {

    'use strict';

    var that = this,
        result;

    result = JSON.parse( data );
    that.object = result;

    nextItem();

};

ObjectResult.prototype.processObjects = function( data, nextItem ) {

    'use strict';

    var that = this,
        results;

    results = JSON.parse( data );
    that.replyObjects = results.objects;
    that.replyPageCount = results.pageCount;

    nextItem();

};
