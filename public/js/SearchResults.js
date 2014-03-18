SearchResults = function ( element, name, resourceFn, callbacks, title, resultPage, centerPoints, timeout ) {

    'use strict';

    //input properties
    this.element = element;
    this.name = name;
    this.computeResource = resourceFn;
    this.resource = false;
    this.successCallback = callbacks.success || function () { return true; };
    this.errorCallback = callbacks.error || function () { return true; };
    this.progressCallback = callbacks.progress || function () { return true; };
    this.timeout = timeout || 5000;
    this.errorCode = false;
    this.status = false;
    this.resultPage = resultPage;

    //output properties
    this.title = title;
    this.objects = [];

    this.view = new HPObjectCollectionView(
        this.element,
        this.name,
        this.title,
        centerPoints
    );

    this.searchRenderFn = function( next ) {
        if ( typeof next !== 'function' ) {
            next = function() {};
        }
        next();
    };

};

SearchResults.prototype.init = function() {

    'use strict';

    this.load();

};

SearchResults.prototype.load = function() {

    'use strict';

    var that = this,
        Q = new CASHQueue(),
        q,
        headers,
        actions,
        l = window._H.localization;

    //this item: calculates remote resource to hit for data
    Q.addItem( function ( nextItem ) {
        that.computeResource( that, nextItem );
        that.view.updateStatus( 'loading', l['GLOBAL_LOADING_SEARCH_LABEL'] );
    });

    //this item: makes remote request
    Q.addItem( function ( nextItem ) {
        headers = [{type : 'Accept', parameter : 'application/json'}];
        actions = {
            200 : function (data) {

                q = new CASHQueue();

                q.addItem( function ( nextInnerItem ) {
                    that.errorCode = false;
                    that.processObjects( data, nextInnerItem );
                } );

                q.addItem( function ( nextInnerItem ) {
                    that.searchRenderFn = function( next ) {
                        if ( typeof next !== 'function' ) {
                            next = function() {};
                        }
                        that.view.render(
                            that.objects,
                            that.resultPage,
                            that.pageCount,
                            window._H.baseURL,
                            window._H.geo,
                            next
                        );
                    };
                    that.searchRenderFn( nextInnerItem );
                } );

                q.addItem( function ( nextInnerItem ) {
                    that.view.updateStatus( 'success' );
                    nextItem();
                } );

                q.execute();

            },
            204 : function () {
                that.errorCode = false;
                that.view.updateStatus( 'empty', l['GLOBAL_EMPTY_SEARCH_LABEL'] );
                nextItem();
            },
            400 : function () {
                that.errorCode = 400;
                that.view.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                nextItem();
            },
            500 : function () {
                that.errorCode = 500;
                that.view.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                nextItem();
            },
            otherwise : function () {
                that.errorCode = -1;
                that.view.updateStatus( 'error', l['GLOBAL_ERROR_LABEL'] );
                nextItem();
            }
        };

        CASHAjax( 'GET', that.resource, headers, false, actions, that.progressCallback, that.timeout);

    });

    Q.execute();

};

SearchResults.prototype.processObjects = function ( data, nextItem ) {

    'use strict';

    var that = this,
        results;

    results = JSON.parse( data );
    that.objects = results.objects;
    that.pageCount = results.pageCount;

    nextItem();

};
