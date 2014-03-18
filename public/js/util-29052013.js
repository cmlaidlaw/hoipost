/*!
 * Main DOM element selector
 * Basically, Sizzle lite lite lite with some extra useful crap
 */

function $ (selectorString) {
    
    'use strict';

    if (!(this instanceof $)) {
        return new $(selectorString);
    }
    
    if (typeof window.$listeners === 'undefined') {
        window.$listeners = {};
    }
    
    this.elements = [];
    this.selectors = [];
    this.listeners = window.$listeners;
    this.select(selectorString);
    
}

$.prototype.ready = function(callback) {
    
    'use strict';
    
    /*!
     * contentloaded.js
     *
     * Author: Diego Perini (diego.perini at gmail.com)
     * Summary: cross-browser wrapper for DOMContentLoaded
     * Updated: 20101020
     * License: MIT
     * Version: 1.2
     *
     * URL:
     * http://javascript.nwbox.com/ContentLoaded/
     * http://javascript.nwbox.com/ContentLoaded/MIT-LICENSE
     *
     */

    // @win window reference
    // @fn function reference
    //function contentLoaded(win, fn) {
    
    var done = false, top = true, win = window,

    doc = win.document, root = doc.documentElement,

    add = doc.addEventListener ? 'addEventListener' : 'attachEvent',
    rem = doc.addEventListener ? 'removeEventListener' : 'detachEvent',
    pre = doc.addEventListener ? '' : 'on',

    init = function(e) {
      if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
      (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
      if (!done && (done = true)) {
          callback.call(win, e.type || e);//fn.call(win, e.type || e);
      }
    },

    poll = function() {
        try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
        init('poll');
    };

    if (doc.readyState == 'complete') {
        callback.call(win, 'lazy');//fn.call(win, 'lazy');
    } else {
        if (doc.createEventObject && root.doScroll) {
            try { top = !win.frameElement; } catch(e) { }
            if (top) poll();
        }
        doc[add](pre + 'DOMContentLoaded', init, false);
        doc[add](pre + 'readystatechange', init, false);
        win[add](pre + 'load', init, false);
    }

    //}
    
};

$.prototype.select = function(selectorString) {
    
    'use strict';
        
    var s, i, selection, j;
    
    if (typeof selectorString === 'string') {
        
        s = selectorString.split(',');
        i = s.length;
        
        while (i--) {
            
            //get rid of whitespace
            s[i] = s[i].replace(/ +/g, '');
            
            if (s[i].charAt(0) === '#') {
                
                selection = document.getElementById(s[i].substring(1, s[i].length));

                if (selection !== null) {
                    this.elements.push(selection);
                }
                //remember the selector that tried to match against element
                this.selectors.push(s[i]);
                
            } else if (s[i].charAt(0) === '.') {

                if ( document.getElementsByClassName ) {
                    selection = document.getElementsByClassName(s[i].substring(1, s[i].length));
                } else {
                    selection = document.querySelectorAll( s[i] );
                }
                j = selection.length;
                while (j--) {
                    if (selection[j] !== null) {
                        this.elements.push(selection[j]);
                    }
                }
                //remember the selector that tried to match against element
                this.selectors.push(s[i]);
            }
        }
    //if the selector string isn't a string after all, then let's push it onto the elements stack as an actual html element
    } else if (typeof selectorString === 'object') {
        this.elements.push(selectorString);
    }
};

$.prototype.parent = function() {
    
    'use strict';
    
    var i, newElements, j, dupe;
    
    i = this.elements.length;
    
    newElements = [];
    
    while (i--) {
        
        j = newElements.length;
        dupe = false;
        
        while (j--) {
            
            if (this.elements[i].parentNode === newElements[j]) {
                
                dupe = true;
                
            }
            
        }
        
        if (dupe === false && (typeof this.elements[i].parentNode !== 'undefined')) {
            
            newElements.push(this.elements[i].parentNode);
            
        }
        
    }
    
    this.elements = newElements;
    this.selectors = [];
    
    return this;
    
};

$.prototype.children = function(selector) {
    
    'use strict';
    
    var that, i, j, children, id, classes, k;

    that = new $();
    that.selectors = [selector];
    
    i = this.elements.length;
    
    while (i--) {
        
        j = this.elements[i].childNodes.length;
        children = this.elements[i].childNodes;
        
        while (j--) {
            
            if (typeof selector === 'undefined') {
                
                that.elements.push(children[j]);
                
            } else {
                
                if (children[j].nodeType === 1 && children[j].hasAttribute('id')) { 
                    classes = children[j].getAttribute('id');
                    if (id === selector.substring(1, selector.length)) {
                        that.elements.push(children[j]);
                    }
                }
                
                if (children[j].nodeType === 1 && children[j].hasAttribute('class')) {
                    classes = children[j].getAttribute('class').replace(/ +/g, '#').split('#');
                    k = classes.length;
                    while (k--) {
                        if (classes[k] === selector.substring(1, selector.length)) {
                            that.elements.push(children[j]);
                        }
                    }
                }
            }
        }
    }
    
    return that;
    
};

$.prototype.siblings = function(selector) {
    
    'use strict';
    
    return this.parent().children(selector);
    
};

$.prototype.each = function(callback) {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    while (i--) {
        callback.call(this);
    }
    
    return this;
};

$.prototype.empty = function() {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    while (i--) {
        
        this.elements[i].innerHTML = '';
        
    }
    
    return this;
    
};

$.prototype.applyEventListeners = function(element) {
    
    'use strict';
    
    var id, classes, events, i, j;
    
    events = [];
    
    //go through 'content' and add any relevant event listeners
    if (element.nodeType === 1 && element.hasAttribute('id')) {
        id = element.getAttribute('id');
    } else {
        id = null;
    }
    
    if (element.nodeType === 1 && element.hasAttribute('class')) {
        classes = element.getAttribute('class').replace(/ +/g, '#').split('#');
    } else {
        classes = [];
    }
    
    if (id !== null) {
        
        //check for an id match
        if (this.listeners.hasOwnProperty('#' + id)) {
            
            i = this.listeners['#' + id].length;
            
            while (i--) {
                events.push(this.listeners['#' + id][i]);
            }
        }
    }
    
    if (classes.length > 0) {
        
        //check for anything matching each class
        i = classes.length;
        
        while (i--) {
            
            if (this.listeners.hasOwnProperty('.' + classes[i])) {
                
                j = this.listeners['.' + classes[i]].length;
                
                while (j--) {
                    events.push(this.listeners['.' + classes[i]][j]);
                }
            }
        }
    }
    
    function bindParameter (parameter) {
        return (function() {
            return parameter;
        }());
    }
    
    i = events.length;
    
    if (element.addEventListener) {
        while (i--) {
            element.addEventListener(bindParameter(events[i].event), bindParameter(events[i].callback), true);
        }
    } else if (element.attachEvent) {
        while (i--) {
            element.addEventListener(bindParameter(events[i].event), bindParameter(events[i].callback), true);
        }
    }
    
};

$.prototype.recurseNodes = function(node, callback) {
    
    'use strict';
    
    var children, i;
    
    if (node.nodeType === 1) {
        
        callback.call(this, node);
        
        if (node.hasChildNodes()) {
            
            children = node.childNodes;
            
            i = children.length;
            
            while (i--) {
                
                this.recurseNodes(children[i], this.applyEventListeners);
                
            }
            
        }
    }
};

$.prototype.on = function(event, callback) {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    while (i--) {
        //first assign listener to all matching extant elements
        if(this.elements[i].addEventListener) {
            this.elements[i].addEventListener(event, callback);
        } else if (this.elements[i].attachEvent) {
            this.elements[i].attachEvent('on' + event, callback);
        }
        
    }
    
    i = this.selectors.length;
    
    while (i--) {
        
        //then save the callback so we can apply it when we add to the dom (note this is outside the loop, in case no matching elements exist yet)
        if (!this.listeners.hasOwnProperty(this.selectors[i]) || typeof this.listeners[this.selectors[i]].length === 'undefined') {
            this.listeners[this.selectors[i]] = [];
        }
        
        this.listeners[this.selectors[i]].push({'event':event, 'callback':callback});
        
    }
    
    return this;
    
};

$.prototype.replaceContent = function(content) {
    
    'use strict';

    if (this.elements.length > 0) {

        this.elements[0].innerHTML = content;
        
    };
    
    return this;
    
};

$.prototype.append = function(content, callback) {

    'use strict';
    
    var i, workaround, element, children, j, k, clone;
    
    i = this.elements.length;
    
    workaround = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    
    if (workaround.exec(navigator.userAgent) !== null) {
        
        //note that here instead of just using the workaround for versions of IE up to 9 (which we're testing with) we
        //are assuming that by version 10 or 11 they still won't have fixed this ridiculous 7-year-old bug =(
        //...but maybe we should be using this for everything since DOM node maniuplation is maybe more solid overall
        workaround = true;
        
    } else {
        
        workaround = false;
        
    }
    
    if (typeof content === 'string') {
        
        while (i--) {
            
            //branch because IE fucks up innerHTML on <select> tags, manually clone DOM elements from a div we set innerHTML on instead
            if (workaround === true && (this.elements[i].nodeType === 1) && (this.elements[i].nodeName.toLowerCase() === 'select')) {
                
                content = ['<select>', this.elements[i].innerHTML, content, '</select>'].join('');
                
                //create a div to paste the entire temporary <select> into
                //(doing it this way because we are accepting a string as the new content, rather than a DOM structure)
                element = document.createElement('div');
                element.innerHTML = content;
                //reassign element to the first child of the div (the temporary <select>)
                element = element.firstChild;
                
                j = element.childNodes.length;
                
                //copy every child <option> individually using DOM methods
                for (k = 0; k < j; k++) {
                    
                    clone = element.options[k].cloneNode(true);
                    this.elements[i].appendChild(clone);
                    
                }
                
            } else {
                
                children = this.elements[i].innerHTML;
                this.elements[i].innerHTML = [children, content].join('');
                
            }
            
            //we have to jump to the parent because we removed and then re-added some dom elements in the 'children' segment
            content = this.elements[i];
            
            j = content.childNodes.length;
            
            while (j--) {
                
                //recurse over content and children, applying event listeners
                this.recurseNodes(content.childNodes[j], this.applyEventListeners);
                
            }
            
        }
        
        //throw this stuff away for the garbage collector
        clone = null;
        element = null;
        j = null;
        k = null;
        
    } else {
    
        while (i--) {
            
            content = this.elements[i].appendChild((document.createDocumentFragment().appendChild(content)));
            
            j = content.childNodes.length;
            
            while (j--) {
                
                //recurse over content and children, applying event listeners
                this.recurseNodes(content.childNodes[j], this.applyEventListeners);
                
            }
            
        }
        
    }

    if (callback && typeof callback === 'function') {
        callback();
    }

    return this;
    
};

$.prototype.remove = function() {
    
    'use strict';

    var i, e;
    
    i = this.elements.length;
    
    while (i--) {
        
        this.elements[i].parentNode.removeChild(this.elements[i]);
        
    }
    
    return this;
    
};

$.prototype.hasClass = function(classname) {
    
    'use strict';
    
    var i, classes, j;
    
    i = this.elements.length;
    
    while (i--) {
        
        if (this.elements[i].nodeType === 1 && this.elements[i].hasAttribute('class')) {
            
            classes = this.elements[i].getAttribute('class').replace(/ +/g, '#').split('#');
            
            j = classes.length;
            
            while (j--) {
                if (classes[j] === classname) {
                    return true;
                }
            }
        }
    }
    return false;
};

$.prototype.addClass = function(classname) {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    while (i--) {
        
        if (this.elements[i].nodeType === 1 && this.elements[i].hasAttribute('class')) {
            this.elements[i].setAttribute('class', (this.elements[i].getAttribute('class') + ' ' + classname));
        } else {
            this.elements[i].setAttribute('class', classname);
        }
        
    }
    
    return this;
    
};

$.prototype.removeClass = function(classname) {
    
    'use strict';
    
    var i, classes, j, clean;
    
    i = this.elements.length;
    
    while (i--) {
        
        if (this.elements[i].nodeType === 1 && this.elements[i].hasAttribute('class')) {
            classes = this.elements[i].getAttribute('class').replace(/ +/g, '#').split('#');
        } else {
            classes = [];
        }
        
        clean = '';
        
        j = classes.length;
        
        while (j--) {
            
            if (classes[j] != classname) {
                
                clean += classes[j] + ' ';
                
            }
            
        }

        this.elements[i].setAttribute('class', clean.trim() );
        
    }
    
    return this;
    
};

$.prototype.show = function() {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    while (i--) {
        this.elements[i].style.display = 'block';
    }
    
    return this;
    
};

$.prototype.hide = function() {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    while (i--) {
        this.elements[i].style.display = 'none';
    }
    
    return this;
    
};

$.prototype.css = function(property, value) {
    
    'use strict';
    
    var i;
    
    //in case there are no matching elements, just pass the core object along the chain
    if (this.elements.length > 0) {
        
        if (typeof value === 'undefined') {
            
            return this.elements[0].style[property];
            
        }
        
        i = this.elements.length;
        
        while (i--) {
            
            this.elements[i].style[property] = value;
            
        }
        
    }
    
    return this;
    
};

$.prototype.val = function(value) {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    if (typeof value !== 'undefined') {
        
        while (i--) {
            
            this.elements[i].setAttribute('value', value);
            
        }
        
        return this;
        
    }
        
    while (i--) {
        
        if (this.elements[i].nodeType === 1 && this.elements[i].hasAttribute('value')) {
            
            //this seems to cause problems with at least <input type="text">, in which it continues
            //to report a blank value even after text is typed in the form control
            //return this.elements[i].getAttribute('value');
            return this.elements[i].value;
            
        }
        
        if (typeof this.elements[i].value !== 'undefined' && (this.elements[i].value !== '')) {
            
            return this.elements[i].value;
            
        }
        
    }
    
    return null;
    
};

$.prototype.width = function() {
    
    'use strict';
    
    if (this.elements.length > 0) {
        
        return Math.max(this.elements[0].clientWidth, this.elements[0].offsetWidth);
        
    }
    
    return 0;
    
};

$.prototype.height = function() {
    
    'use strict';
    
    if (this.elements.length > 0) {
        
        return Math.max(this.elements[0].clientHeight, this.elements[0].offsetHeight);
        
    }
    
    return 0;
    
};

$.prototype.attr = function(attribute, value) {
    
    'use strict';
    
    if (typeof value === 'undefined') {
        
        if (attribute === 'class') {
            
            return this.elements[0].className;
            
        }
        
        return this.elements[0][attribute];
        
    } else {
        
        if (typeof this.elements[0] !== 'undefined') {
            
            this.elements[0][attribute] = value;
            
        }
        
        return this;
        
    }
    
};

$.prototype.removeAttr = function(attribute) {
    
    'use strict';
            
    if (typeof this.elements[0] !== 'undefined') {
        
        this.elements[0].removeAttribute(attribute);
        
    }
    
    return this;
    
};

$.prototype.offsetLeft = function() {
    
    'use strict';
    
    var offset, element;
    element = this.elements[0];
    
    for (offset = 0; element != null; offset += element.offsetLeft, element = element.offsetParent);
    
    return offset;
    
};

$.prototype.offsetTop = function() {
    
    'use strict';
    
    var offset, element;
    element = this.elements[0];
    
    for (offset = 0; element != null; offset += element.offsetTop, element = element.offsetParent);
    
    return offset;
    
};

$.prototype.clear = function() {
    
    'use strict';
    
    var i;
    
    i = this.elements.length;
    
    while (i--) {
        
        this.elements[i].value = '';
        
    }
    
    return this;
    
};

$.prototype.blur = function() {
    
    'use strict';
    
    this.elements[0].blur();
    
    return this;
    
};

$.prototype.halt = function(event) {
    
    'use strict';

    if ( event.stopPropagation ) {
        event.stopPropagation();
        event.preventDefault();    
    } else {
        event.cancelBubble = true;
        event.returnValue = false;
    }
    
};

/*
 * Traverses up the DOM from an element, matching each consecutive element against one or more selectors and returning the first
 * element that matches any of the selectors
 * This is used in the 'button' method, as a way to actually get the element that was used to set up the 'button', rather than some child
 * element that caught the click
 */

$.prototype.getMatchingElement = function(start, selectors) {
    
    'use strict';
    
    var element, length, i, classes, classLength, j, parent;
    
    element = start;
    length = selectors.length;
    
    for (i = 0; i < length; i++) {
        if (selectors[i].charAt(0) === '#' && element.hasAttribute('id')) {
            if (selectors[i].substring(1, selectors[i].length) === element.getAttribute('id')) {
                return element;
            }
        } else if (selectors[i].charAt(0) === '.' && element.hasAttribute('class')) {
            classes = element.getAttribute('class').split(' ');
            classLength = classes.length;
            for (j = 0; j < classLength; j++) {
                if (selectors[i].substring(1, selectors[i].length) === classes[j].trim()) {
                    return element;
                }
            }
        }
    }
    
    //recurse here if we still haven't found a match but we're not at the top of the DOM
    if (element.parentElement !== null) {
        return this.getMatchingElement(element.parentElement, selectors);
    } else {
        return false;
    }
    
};

$.prototype.button = function(hasTouch, callback, onStart, matchFutureElements) {
    
    'use strict';
    
    var that, i, element, touchStart, touchMove, touchEnd, halt, mouseDown, mouseUp;

    that = this;
    i = this.elements.length;

    if (hasTouch) {
        
        if (onStart) {
            
            touchStart = function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (e.target.tagName.toLowerCase() !== 'a') {
                    if (typeof callback === 'function') {
                        //ascend DOM to find the first parent which matches one of the selectors
                        callback(that.getMatchingElement(e.target, that.selectors));
                    }
                
                } else {
                    window.location = e.target.getAttribute('href');
                }
            };
            
            touchMove = function(e) {
                e.preventDefault();
                e.stopPropagation();
            };
            
            touchEnd = function(e) {
                e.preventDefault();
                e.stopPropagation();
            };
            
        } else {
        
            touchStart = function(e) {
                e.target.hpTouchDragged = false;
                e.target.hpTouchCoords = { x : e.touches[0].clientX, y : e.touches[0].clientY };
            };
            
            touchMove = function(e) {
                //if finger moves more than 10px flag to cancel
                //code.google.com/mobile/articles/fast_buttons.html
                if (Math.abs(e.touches[0].clientX - e.target.hpTouchCoords.x) > 10 ||
                    Math.abs(e.touches[0].clientY - e.target.hpTouchCoords.y) > 10) {
                        e.target.hpTouchDragged = true;
                }
            };
            
            touchEnd = function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (!e.target.hpTouchDragged) {
                    if (e.target.tagName.toLowerCase() !== 'a') {
                        if (typeof callback === 'function') {
                            //ascend DOM to find the first parent which matches one of the selectors
                            callback(that.getMatchingElement(e.target, that.selectors));
                        }
                    
                    } else {
                        window.location = e.target.getAttribute('href');
                    }
                }
            };

        }

        halt = function(e) {
            e.stopPropagation();
            e.preventDefault();
        };
        
        while (i--) {
            
            element = this.elements[i];

            //first assign listeners to all extant matching elements
            if ( element.addEventListener ) {
                element.addEventListener('touchstart', touchStart);
                element.addEventListener('touchmove', touchMove);
                element.addEventListener('touchend', touchEnd);
                element.addEventListener('click', halt);
                element.addEventListener('dblclick', halt);
                element.addEventListener('mousedown', halt);
                element.addEventListener('mouseup', halt);
            }

        }

        if (matchFutureElements) {
        
            i = this.selectors.length;
        
            while (i--) {
            
                //then save the callback so we can apply it when we add to the dom (note this is outside the loop, in case no matching elements exist yet)
                if (!this.listeners.hasOwnProperty(this.selectors[i]) || typeof this.listeners[this.selectors[i]].length === 'undefined') {
                    this.listeners[this.selectors[i]] = [];
                }
            
                this.listeners[this.selectors[i]].push({'event': 'touchstart', 'callback':touchStart});
                this.listeners[this.selectors[i]].push({'event': 'touchmove', 'callback':touchMove});
                this.listeners[this.selectors[i]].push({'event': 'touchend', 'callback':touchEnd});
                this.listeners[this.selectors[i]].push({'event': 'click', 'callback':halt});
                this.listeners[this.selectors[i]].push({'event': 'dblclick', 'callback':halt});
                this.listeners[this.selectors[i]].push({'event': 'mousedown', 'callback':halt});
                this.listeners[this.selectors[i]].push({'event': 'mouseup', 'callback':halt});
            
            }

        }

    //no touch, just clicks
    } else {

        halt = function( e ) {
            $.prototype.halt.call( null, e );
        }

        if (onStart) {

            mouseDown = function(e) {
                var mouseButton = (e.which) ? e.which : e.button,
                    target = (e.target) ? e.target : e.srcElement;

                if (mouseButton === 1) {
                    if (target.tagName.toLowerCase() !== 'a') {
                        halt(e);
                        if (typeof callback === 'function') {
                            //ascend DOM to find the first parent which matches one of the selectors
                            callback(that.getMatchingElement(target, that.selectors));
                        }
                    } else {
                        window.location = target.getAttribute('href');
                    }
                }
            };
            
            mouseUp = halt;

        } else {

            mouseDown = function(e) {
                var mouseButton = (e.which) ? e.which : e.button;
                //only execute on left click
                if (mouseButton === 1) {
                    halt(e);
                }
            };
            
            mouseUp = function(e) {
                var mouseButton = (e.which) ? e.which : e.button,
                    target = (e.target) ? e.target : e.srcElement;
                //only execute on left click
                if (mouseButton === 1) {
                    if (target.tagName.toLowerCase() !== 'a') {
                        halt(e);
                        if (typeof callback === 'function') {
                            //ascend DOM to find the first parent which matches one of the selectors
                            callback(that.getMatchingElement(target, that.selectors));
                        }
                    } else {
                        window.location = target.getAttribute('href');
                    }
                }
            };
        
        }
        
        while (i--) {
        
            element = this.elements[i];
            if ( element.addEventListener ) {
                element.addEventListener('mousedown', mouseDown);
                element.addEventListener('mouseup', mouseUp);
                element.addEventListener('click', halt);
                element.addEventListener('dblclick', halt);
            } else {
                element.attachEvent('onmousedown', mouseDown);
                element.attachEvent('onmouseup', mouseUp);
                element.attachEvent('onclick', halt);
                element.attachEvent('ondblclick', halt);            
            }

        }

        if (matchFutureElements) {

            i = this.selectors.length;
        
            while (i--) {
            
                //then save the callback so we can apply it when we add to the dom (note this is outside the loop, in case no matching elements exist yet)
                if (!this.listeners.hasOwnProperty(this.selectors[i]) || typeof this.listeners[this.selectors[i]].length === 'undefined') {
                    this.listeners[this.selectors[i]] = [];
                }
            
                this.listeners[this.selectors[i]].push({'event': 'mousedown', 'callback':mouseDown});
                this.listeners[this.selectors[i]].push({'event': 'mouseup', 'callback':mouseUp});
                this.listeners[this.selectors[i]].push({'event': 'click', 'callback':halt});
                this.listeners[this.selectors[i]].push({'event': 'dblclick', 'callback':halt});
            
            }

        }

    }

};

$.prototype.dragger = function(touch, callback) {

    'use strict';

    var that, i, element, touchStart, touchMove, touchEnd, halt, mouseDown, selectStart, mouseUp, mouseOut, mouseMove;

    that = this;
    i = this.elements.length;

    if (touch) {

        touchStart = function(e) {
            var element = that.getMatchingElement(e.target, that.selectors);
            //if (e.which === 1) {
                element._dragging = true;
                element._lastX = e.touches[0].clientX;
                element._lastY = e.touches[0].clientY;
            //}
        };

        touchEnd = function(e) {
            var element = that.getMatchingElement(e.target, that.selectors);
            //if (e.which === 1 && element._dragging) {
            if (element._dragging) {
                element._dragging = false;
            }
        };

        touchMove = function(e) {
            var element = that.getMatchingElement(e.target, that.selectors);
            //if (e.which === 1 && element._dragging) {
            if (element._dragging) {
                var dX = e.touches[0].clientX - element._lastX;
                var dY = e.touches[0].clientY - element._lastY;
                //logMe(e.touches[0].clientY + ' ' + element._lastY + ' (' + Math.abs(dY) + ')');
                if ((Math.abs(dX) * 1.25) > Math.abs(dY)) {
                    callback(element, (e.touches[0].clientX - element._lastX));
                    element._lastX = e.touches[0].clientX;
                    element._lastY = e.touches[0].clientY;
                    e.preventDefault();
                } else {
                    //element._dragging = false;
                }
            }
        };

        while (i--) {

            element = this.elements[i];

            //first assign listeners to all extant matching elements
            element.addEventListener('touchstart', touchStart);
            element.addEventListener('touchmove', touchMove);
            element.addEventListener('touchend', touchEnd);

        }

        i = this.selectors.length;

        while (i--) {

            //then save the callback so we can apply it when we add to the dom (note this is outside the loop, in case no matching elements exist yet)
            if (!this.listeners.hasOwnProperty(this.selectors[i]) || typeof this.listeners[this.selectors[i]].length === 'undefined') {
                this.listeners[this.selectors[i]] = [];
            }

            this.listeners[this.selectors[i]].push({'event': 'touchstart', 'callback' : touchStart, 'capture' : true});
            this.listeners[this.selectors[i]].push({'event': 'touchmove', 'callback' : touchMove, 'capture' : true});
            this.listeners[this.selectors[i]].push({'event': 'touchend', 'callback' : touchEnd, 'capture' : true});

        }

    //no touch, just clicks
    } else {
        
        mouseDown = function(e) {
            var element = that.getMatchingElement(e.target, that.selectors);
            if (e.which === 1) {
                element._dragging = true;
                element._startX = element._lastX = e.clientX;
                element._startY = element._lastY = e.clientY;
            }
            e.stopPropagation();
            if (e.preventDefault) {
                e.preventDefault();
            }
            return false;
        };
        
        mouseUp = function(e) {
            var element = that.getMatchingElement(e.target, that.selectors);
            if (e.which === 1 && element._dragging) {
                element._dragging = false;
                if (Math.abs(element._lastX - element._startX) < 10 && Math.abs(element._lastY - element._startY) < 10) {
                    //didn't move much, dispatch a click on the event target
                    element = that.getMatchingElement(e.target, ['.message']);
                    if (element) {
                        //fire mouseup event for this element
                        var evt = document.createEvent("MouseEvents");
                        evt.initMouseEvent('click', false, true, window, 0, e.screenX, e.screenY, e.clientX, e.clientY, false, false, false, false, 0, null);
                        element.dispatchEvent(evt);
                    }
                } else {
                    e.stopPropagation();
                    e.preventDefault();
                }
            }
        };
        
        mouseMove = function(e) {
            var element = that.getMatchingElement(e.target, that.selectors);
            if (e.which === 1 && element._dragging) {
                callback(element, (e.clientX - element._lastX));
                element._lastX = e.clientX;
            }
        };
        
        halt = function(e) {
            e.stopPropagation();
            e.preventDefault();
        };
        
        while (i--) {
        
            element = this.elements[i];
            element.addEventListener('mousedown', mouseDown, true);
            element.addEventListener('mouseup', mouseUp, true);
            element.addEventListener('mousemove', mouseMove, true);
            element.addEventListener('click', halt, true);
            element.addEventListener('dblclick', halt, true);
        
        }
        
        i = this.selectors.length;
        
        while (i--) {
            
            //then save the callback so we can apply it when we add to the dom (note this is outside the loop, in case no matching elements exist yet)
            if (!this.listeners.hasOwnProperty(this.selectors[i]) || typeof this.listeners[this.selectors[i]].length === 'undefined') {
                this.listeners[this.selectors[i]] = [];
            }
            
            this.listeners[this.selectors[i]].push({'event': 'mousedown', 'callback' : mouseDown, 'capture' : true});
            this.listeners[this.selectors[i]].push({'event': 'mouseup', 'callback' : mouseUp, 'capture' : true});
            this.listeners[this.selectors[i]].push({'event': 'mousemove', 'callback' : mouseMove, 'capture' : true});
            this.listeners[this.selectors[i]].push({'event': 'click', 'callback' : halt, 'capture' : true});
            this.listeners[this.selectors[i]].push({'event': 'dblclick', 'callback' : halt, 'capture' : true});
            
        }
        
    }
    
};

/*
 * polyfill for String.trim()
 */

if ( !String.prototype.trim ) {
    String.prototype.trim = function() { return this.replace( /^\s+|\s+$/g, '' ); };
}

/*!
 * mustache.js - Logic-less {{mustache}} templates with JavaScript
 * http://github.com/janl/mustache.js
 */
var Mustache=(typeof module!=="undefined"&&module.exports)||{};(function(exports){exports.name="mustache.js";exports.version="0.5.0-dev";exports.tags=["{{","}}"];exports.parse=parse;exports.compile=compile;exports.render=render;exports.clearCache=clearCache;exports.to_html=function(template,view,partials,send){var result=render(template,view,partials);if(typeof send==="function"){send(result);}else{return result;}};var _toString=Object.prototype.toString;var _isArray=Array.isArray;var _forEach=Array.prototype.forEach;var _trim=String.prototype.trim;var isArray;if(_isArray){isArray=_isArray;}else{isArray=function(obj){return _toString.call(obj)==="[object Array]";};}
var forEach;if(_forEach){forEach=function(obj,callback,scope){return _forEach.call(obj,callback,scope);};}else{forEach=function(obj,callback,scope){for(var i=0,len=obj.length;i<len;++i){callback.call(scope,obj[i],i,obj);}};}
var spaceRe=/^\s*$/;function isWhitespace(string){return spaceRe.test(string);}
var trim;if(_trim){trim=function(string){return string==null?"":_trim.call(string);};}else{var trimLeft,trimRight;if(isWhitespace("\xA0")){trimLeft=/^\s+/;trimRight=/\s+$/;}else{trimLeft=/^[\s\xA0]+/;trimRight=/[\s\xA0]+$/;}
trim=function(string){return string==null?"":String(string).replace(trimLeft,"").replace(trimRight,"");};}
var escapeMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':'&quot;',"'":'&#39;'};function escapeHTML(string){return String(string).replace(/&(?!\w+;)|[<>"']/g,function(s){return escapeMap[s]||s;});}
function debug(e,template,line,file){file=file||"<template>";var lines=template.split("\n"),start=Math.max(line-3,0),end=Math.min(lines.length,line+3),context=lines.slice(start,end);var c;for(var i=0,len=context.length;i<len;++i){c=i+start+1;context[i]=(c===line?" >> ":"    ")+context[i];}
e.template=template;e.line=line;e.file=file;e.message=[file+":"+line,context.join("\n"),"",e.message].join("\n");return e;}
function lookup(name,stack,defaultValue){if(name==="."){return stack[stack.length-1];}
var names=name.split(".");var lastIndex=names.length-1;var target=names[lastIndex];var value,context,i=stack.length,j,localStack;while(i){localStack=stack.slice(0);context=stack[--i];j=0;while(j<lastIndex){context=context[names[j++]];if(context==null){break;}
localStack.push(context);}
if(context&&typeof context==="object"&&target in context){value=context[target];break;}}
if(typeof value==="function"){value=value.call(localStack[localStack.length-1]);}
if(value==null){return defaultValue;}
return value;}
function renderSection(name,stack,callback,inverted){var buffer="";var value=lookup(name,stack);if(inverted){if(value==null||value===false||(isArray(value)&&value.length===0)){buffer+=callback();}}else if(isArray(value)){forEach(value,function(value){stack.push(value);buffer+=callback();stack.pop();});}else if(typeof value==="object"){stack.push(value);buffer+=callback();stack.pop();}else if(typeof value==="function"){var scope=stack[stack.length-1];var scopedRender=function(template){return render(template,scope);};buffer+=value.call(scope,callback(),scopedRender)||"";}else if(value){buffer+=callback();}
return buffer;}
function parse(template,options){options=options||{};var tags=options.tags||exports.tags,openTag=tags[0],closeTag=tags[tags.length-1];var code=['var buffer = "";',"\nvar line = 1;","\ntry {",'\nbuffer += "'];var spaces=[],hasTag=false,nonSpace=false;var stripSpace=function(){if(hasTag&&!nonSpace&&!options.space){while(spaces.length){code.splice(spaces.pop(),1);}}else{spaces=[];}
hasTag=false;nonSpace=false;};var sectionStack=[],updateLine,nextOpenTag,nextCloseTag;var setTags=function(source){tags=trim(source).split(/\s+/);nextOpenTag=tags[0];nextCloseTag=tags[tags.length-1];};var includePartial=function(source){code.push('";',updateLine,'\nvar partial = partials["'+trim(source)+'"];','\nif (partial) {','\n  buffer += render(partial,stack[stack.length - 1],partials);','\n}','\nbuffer += "');};var openSection=function(source,inverted){var name=trim(source);if(name===""){throw debug(new Error("Section name may not be empty"),template,line,options.file);}
sectionStack.push({name:name,inverted:inverted});code.push('";',updateLine,'\nvar name = "'+name+'";','\nvar callback = (function() {','\n  return function() {','\n    var buffer = "";','\nbuffer += "');};var openInvertedSection=function(source){openSection(source,true);};var closeSection=function(source){var name=trim(source);var openName=sectionStack.length!=0&&sectionStack[sectionStack.length-1].name;if(!openName||name!=openName){throw debug(new Error('Section named "'+name+'" was never opened'),template,line,options.file);}
var section=sectionStack.pop();code.push('";','\n    return buffer;','\n  };','\n})();');if(section.inverted){code.push("\nbuffer += renderSection(name,stack,callback,true);");}else{code.push("\nbuffer += renderSection(name,stack,callback);");}
code.push('\nbuffer += "');};var sendPlain=function(source){code.push('";',updateLine,'\nbuffer += lookup("'+trim(source)+'",stack,"");','\nbuffer += "');};var sendEscaped=function(source){code.push('";',updateLine,'\nbuffer += escapeHTML(lookup("'+trim(source)+'",stack,""));','\nbuffer += "');};var line=1,c,callback;for(var i=0,len=template.length;i<len;++i){if(template.slice(i,i+openTag.length)===openTag){i+=openTag.length;c=template.substr(i,1);updateLine='\nline = '+line+';';nextOpenTag=openTag;nextCloseTag=closeTag;hasTag=true;switch(c){case"!":i++;callback=null;break;case"=":i++;closeTag="="+closeTag;callback=setTags;break;case">":i++;callback=includePartial;break;case"#":i++;callback=openSection;break;case"^":i++;callback=openInvertedSection;break;case"/":i++;callback=closeSection;break;case"{":closeTag="}"+closeTag;case"&":i++;nonSpace=true;callback=sendPlain;break;default:nonSpace=true;callback=sendEscaped;}
var end=template.indexOf(closeTag,i);if(end===-1){throw debug(new Error('Tag "'+openTag+'" was not closed properly'),template,line,options.file);}
var source=template.substring(i,end);if(callback){callback(source);}
var n=0;while(~(n=source.indexOf("\n",n))){line++;n++;}
i=end+closeTag.length-1;openTag=nextOpenTag;closeTag=nextCloseTag;}else{c=template.substr(i,1);switch(c){case'"':case"\\":nonSpace=true;code.push("\\"+c);break;case"\r":break;case"\n":spaces.push(code.length);code.push("\\n");stripSpace();line++;break;default:if(isWhitespace(c)){spaces.push(code.length);}else{nonSpace=true;}
code.push(c);}}}
if(sectionStack.length!=0){throw debug(new Error('Section "'+sectionStack[sectionStack.length-1].name+'" was not closed properly'),template,line,options.file);}
stripSpace();code.push('";',"\nreturn buffer;","\n} catch (e) { throw {error: e, line: line}; }");var body=code.join("").replace(/buffer \+= "";\n/g,"");if(options.debug){if(typeof console!="undefined"&&console.log){console.log(body);}else if(typeof print==="function"){print(body);}}
return body;}
function _compile(template,options){var args="view,partials,stack,lookup,escapeHTML,renderSection,render";var body=parse(template,options);var fn=new Function(args,body);return function(view,partials){partials=partials||{};var stack=[view];try{return fn(view,partials,stack,lookup,escapeHTML,renderSection,render);}catch(e){throw debug(e.error,template,e.line,options.file);}};}
var _cache={};function clearCache(){_cache={};}
function compile(template,options){options=options||{};if(options.cache!==false){if(!_cache[template]){_cache[template]=_compile(template,options);}
return _cache[template];}
return _compile(template,options);}
function render(template,view,partials){return compile(template)(view,partials);}})(Mustache);