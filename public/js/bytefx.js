/**_______________________________________
 *
 *    bytefx :: simple effects in few bytes
 * ---------------------------------------
 *
 * @author              Andrea Giammarchi
 * @site                http://www.devpro.it/bytefx/
 * @version             0.4
 * @requires		anything *
 * 			* old browsers (IE <= 5) should require JSL
 * @credits		Matteo Galli (aka Ratatuia) for Safari debug,
 * 			Boyan Djumakov, for debug and reports (http://webnos.blogspot.com/)
 * ---------------------------------------
 * 
 * Copyright (c) 2006 Andrea Giammarchi
 *
 * Permission is hereby granted, free of charge,
 * to any person obtaining a copy of this software and associated
 * documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * _______________________________________
 */
bytefx = new function(){

	// public methods

	/**
	 * public method,
         * 	bytefx.alpha(element:Object, opacity:UShort):Void
         * @param	Object		X/HTML Element to apply alpha
         * @param	UShort		Unsigned short number, from 0 to 100
         * @example
         * 		bytefx.alpha(document.getElementById("some-div"), 50);
         *		// set alpha to 50
	 */
	this.alpha = function(element, opacity){
		var	style = $element(element).style;
		style.opacity = style.MozOpacity = style.KhtmlOpacity = opacity / 100;
		style.filter = "alpha(opacity=" + opacity + ")";
	};

	/**
	 * public method,
         * 	bytefx.clear(element:Object):Void
         * @param	Object		X/HTML Element to clear every bytefx interval
         * @example
         * 		bytefx.clear(document.getElementById("some-div"));
         *		// remove color, fade, move and size intervals
	 */
	this.clear = function(element){
		var	interval = ["size", "scroll", "move", "fade", "color"],
			index = interval.length;
		while(index--)
			clearInterval($element(element).bytefx[interval[index]]);
	};

	/**
	 * public method,
         * 	bytefx.color(element:Object, style:String, start:String, end:String, speed:UShort[, callback:Function]):Void
         * @param	Object		X/HTML Element to change color
         * @param	String		Type of style to change color ("background", "color", "backgroundColor", ...)
         * @param	String		Start hex color, 3 or 6 chars plus "#" prefix ("#93D", "#0178AF", ...)
         * @param	String		End hex color, 3 or 6 chars plus "#" prefix ("#93D", "#0178AF", ...)
         * @param	UShort		Unsigned short number, from 1 to 100 (big is faster)
         * @param	Function	callback to call with element as scope on gradient complete
         * @example
         * 		bytefx.color(document.getElementById("some-div"), "backgroundColor", "#FFF", "#000", 2);
         *		// switch color from "#FFF" to "#000"
	 */
	this.color = function(element, style, start, end, speed, callback){
		end = bytefx.color$(end);
		clearInterval($element(element).bytefx.color);
		element.bytefx.color = setInterval(function(){
			var	color = bytefx.color$(start),
				index = 3;
			while(index--)
				color[index] = $end(color[index], end[index], speed);
			element.style[style] = start = bytefx.$color(color);
			if("" + color == "" + end)
				$callback(element, "color", callback);
		}, 1);
	};

	/**
	 * public method,
         * 	bytefx.drag(element:Object[, start:Function[, end:Function[, callback:Function[, position:Object]]]]):Void
         * @param	Object		X/HTML Element to make draggable
         * @param	Function	callback to call with element as scope on drag init
         * @param	Function	callback to call with element as scope on drag end
         * @param	Function	callback to call with element as scope during drag
         * @param	Object		object with 4 properties:
         * 				$x, $y, x$, y$
         *                              Each propery should be an Int32 or null but must be present.
         * @example
         * 		bytefx.drag(document.getElementById("some-div"));	// base
         * 		bytefx.drag(someDiv, initCallback);			// base with an init callback
         * 		bytefx.drag(someDiv, null, endCallback);		// base with an end callback
         * 		bytefx.drag(someDiv, null, null, null, {$y:10, y$:10, $x:null, x$:null});
	 *									// blocked Y position
	 */
	this.drag = function(element, start, end, callback, position){
		function $callback(evt, callback){
			if(callback)
				callback.call(element, evt);
			return false;
		};
		var	tmp = $element(element).bytefx.drag;
		bytefx.$event(element, "onmousedown", function(evt){
			tmp.start = true;
			tmp.onmousedown = d.onmousedown;
			tmp.onmouseup = d.onmouseup;
			d.onmouseup = element.onmouseup;
			d.onmousedown = $callback;
			return $callback(evt, start);
		});
		bytefx.$event(element, "onmouseup", function(evt){
			tmp.start = false;
			d.onmousedown = tmp.onmousedown;
			d.onmouseup = tmp.onmouseup;
			return $callback(evt, end);
		});
		bytefx.$event(d, "onmousemove", function(evt){
			var	x = evt.clientX,
				y = evt.clientY,
				size = {x: x - tmp.x, y: y - tmp.y};
			if(tmp.start) {
				if(position) {
					size.x = max(size.x, position.$x);
					size.y = max(size.y, position.$y);
					size.x = min(size.x, position.x$);
					size.y = min(size.y, position.y$);
				};
				bytefx.position(element, size);
				$callback(evt, callback);
			}
			else{
				tmp.x = x - element.offsetLeft;
				tmp.y = y - element.offsetTop;
			};
			return false;
		});
	};

	/**
	 * public method,
         * 	bytefx.fade(element:Object, start:UShort, end:UShort, speed:Number[, callback:Function]):Void
         * @param	Object		X/HTML Element to fade
         * @param	UShort		start alpha value, an unsigned short number, from 0 to 100
         * @param	UShort		end alpha value, an unsigned short number, from 0 to 100
         * @param	Number		Unsigned short greater than 0 (1-100) or unsigned Float greater than 0 (0.1 - 100.0)
         * @param	Function	callback to call with element as scope on fade complete
         * @example
         * 		bytefx.fade(document.getElementById("some-div"), 100, 0, 5, function(){alert("disappeared")});
         *		// fade element from alpha 100 to 0
	 */
	this.fade = function(element, start, end, speed, callback){
		clearInterval($element(element).bytefx.fade);
		element.bytefx.fade = setInterval(function(){
			start = $end(start, end, speed);
			bytefx.alpha(element, start);
			if(start == end)
				$callback(element, "fade", callback);
		}, 1);
	};

	/**
	 * public method,
         * 	bytefx.move(element:Object, position:Object, speed:Number[, callback:Function]):Void
         * @param	Object		X/HTML Element to move
         * @param	Object		an object with atleast 2 properties, x and y, used to set new position as left and top
         * @param	Number		Unsigned short greater than 0 (1-100) or unsigned Float greater than 0 (0.1 - 100.0)
         * @param	Function	callback to call with element as scope on movement complete
         * @example
         * 		bytefx.move(document.getElementById("some-div"), {x:200, y:250}, 5, function(){alert("moved")});
         *		// move an element from its position to x 200 and y 250 (x as left and y as top coordinates)
	 */
	this.move = function(element, position, speed, callback){
		var	start = bytefx.$position($element(element));
		$setInterval(element, "move", speed / 100, start, position, ["x", "y"], "position", callback);
	};

	/**
	 * public method,
         * 	bytefx.position(element:Object, position:Object):Void
         * @param	Object		X/HTML Element to set position
         * @param	Object		an object with atleast 2 properties, x and y, used to set new position as left and top
         * @example
         * 		bytefx.position(document.getElementById("some-div"), {x:200, y:123});
         *		// set element position with left = 200 and top = 123
	 */
	this.position = function(element, position){
		var	style = $element(element).style;
		style.position = "absolute";
		style.left = position.x + "px";
		style.top = position.y + "px";
	};

	/**
	 * public method,
         * 	bytefx.scroll(element:Object, speed:Number[, callback:Function]):Void
         * @param	Object		Target X/HTML Element (window scroll to this element)
         * @param	Number		Unsigned short greater than 0 (1-100) or unsigned Float greater than 0 (0.1 - 100.0)
         * @param	Function	callback to call with target element as scope on scroll complete
         * @example
         * 		bytefx.scroll(document.getElementById("some-div"), 2, function(){alert(this + " is on top")});
         *		// scroll window to element "some-div" and then call callback
	 */
	this.scroll = function(element, speed, callback){
		function scroll(position){
			return d.documentElement ? d.documentElement[position] : d.body[position];
		};
		var	start = bytefx.$scroll(),
			end = {x:start.x, y:min(bytefx.$position(element).y, max(scroll("offsetHeight"), d.body.offsetHeight) - min(scroll("clientHeight"), d.body.clientHeight))};
		$setInterval($element(bytefx), "scroll", speed / 100, start, end, ["x", "y"], "scroll$", callback ? function(){callback.call(element)} : null);
	};

	/**
	 * public method,
         * 	bytefx.size(element:Object, size:Object, speed:Number[, callback:Function]):Void
         * @param	Object		X/HTML Element to resize
         * @param	Object		an object with atleast 2 properties, width and height, used to set new size
         * 				This object should have other 4 parameters too, useful to correct different
         * 				browsers engines. IE shouldn't have problems but FireFox, Opera or Safari should
         * 				render elements with padding or borders in a different way.
         *				Using corrective parameters you could remove thi issue.
         * 				These properties are:
         * 					$width, corrective start width unsigned integer
         * 					width$, corrective end width unsigned integer
         *					$height, corrective start height unsigned integer
         * 					height$, corrective end height unsigned integer         *                                      
         * @param	Number		Unsigned short greater than 0 (1-100) or unsigned Float greater than 0 (0.1 - 100.0)
         * @param	Function	callback to call with element as scope on resize complete
         * @example
         * 		bytefx.size(document.getElementById("some-div"), {width:400, height:250}, 4, function(){alert("resized")});
         *		// resize element from its size to 400px X 250px
	 */
	this.size = function(element, size, speed, callback){
		var	start = bytefx.$size($element(element)),
			tmp = w.opera;
		if(!/msie/i.test(navigator.userAgent) || (tmp && parseInt(tmp.version()) >= 9)){
			if(size.$width)
				start.width -= size.$width;
			if(size.$height)
				start.height -= size.$height;
			if(size.width$)
				size.width -= size.width$;
			if(size.height$)
				size.height -= size.height$;
		};
		element.style.overflow = "hidden";
		$setInterval(element, "size", speed / 100, start, size, ["width", "height"], "size$", callback);
	};



	// extra public methods, used by bytefx but maybe useful for other scripts too

	/**
	 * extra public method,
         * 	bytefx.$color(color:Array):String
         * @param	Array		3 indexes array with Red, Green, Blue values from 0 to 255 (i.e. [1, 200, 30], ...)
         * @return	String		hex string with "#" prefix, respective css value ([255, 255, 255] => "#FFFFFF")
         * @example
         * 		bytefx.$color([255, 0, 0]); // #FF0000
	 */
	this.$color = function(color){
		function tmp(index){
			var	tmp = color[index].toString(16);
			return tmp.length == 1 ? "0" + tmp : tmp;
		};
		return "#" + tmp(0) + tmp(1) + tmp(2);
	};

	/**
	 * extra public method,
         * 	bytefx.color$(color:String):Array
         * @param	String		string with "#" prefix with 3 or 6 hex values ("#A92", "#0139A0", ...)
         * @return	Array		3 indexes array with Red, Green, Blue values from 0 to 255 (i.e. [1, 200, 30], ...)
         * @example
         * 		bytefx.color$("#FF0000"); // [255, 0, 0]
	 */
	this.color$ = function(color){
		function tmp(index){
			return color.charAt(index);
		};
		color = color.substring(1);
		if(color.length == 3)
			color = tmp(0) + tmp(0) + tmp(1) + tmp(1) + tmp(2) + tmp(2);
		return [parseInt(tmp(0) + tmp(1), 16), parseInt(tmp(2) + tmp(3), 16), parseInt(tmp(4) + tmp(5), 16)];
	};

	/**
	 * extra public method,
         * 	bytefx.$event(element:Object, eventName:String, callback:Function):Void
         * @param	Object		X/HTML Element to apply event
         * @param	String		generic event name ("onclick", "onmouseup", "onmousedown", ...)
         * @param	Function	callback to call with element as scope on event fired
         * @example
         * 		bytefx.$event(document.getElementById("some-div"), "onclick", 5, function(){alert("clicked!")});
         *		// add an onclick event without override other events
	 */
	this.$event = function(element, tmp, callback){
		var	value = element[tmp];
		element[tmp] = function(evt){
			if(!evt)
				evt = w.event;
			if(value)
				value.call(this, evt);
			return callback.call(this, evt);
		};
	};

	/**
	 * extra public method,
         * 	bytefx.$position(element:Object):Object
         * @param	Object		X/HTML Element to get absolute position
         * @return	Object		object with 2 properties, x and y, as left and top coordinates
         * @example
         * 		bytefx.$position(document.getElementById("some-div")); // {x:234, y:301}
	 */
	this.$position = function(element){
		var	position = {x:element.offsetLeft, y:element.offsetTop};
		while(element = element.offsetParent){
			position.x += element.offsetLeft;
			position.y += element.offsetTop;
		};
		return position;
	};

	/**
	 * extra public method,
         * 	bytefx.$scroll(Void):Object
         * @return	Object		object with 2 properties, x and y, as left and top page scroll position
         * @example
         * 		bytefx.$scroll(); // {x:0, y:10}
	 */
	this.$scroll = function(){
		function scroll(position, scroll){
			return (d.documentElement ? d.documentElement[position] : w[scroll] || d.body[position]) || 0;
		};
		return {x:scroll("scrollLeft", "pageXOffset"), y:scroll("scrollTop", "pageYOffset")};
	};

	/**
	 * extra public method, (maybe not useful)
         * 	bytefx.$scroll(null, position:Object):Void
         * @param	null		everything (not used) for compatibility reasons
         * @param	Object		object with x and y keys to use window.scrollTo method
         * @example
         * 		bytefx.scroll$(null, {x:0, y:10}); // scroll document to x 0 and y 10
	 */	
	this.scroll$ = function(element, position){
		w.scrollTo(position.x, position.y);
	};

	/**
	 * extra public method,
         * 	bytefx.$size(element:Object):Object
         * @param	Object		X/HTML Element to get size
         * @return	Object		object with 2 properties, width and height as pixel dimension
         * @example
         * 		bytefx.$size(document.getElementById("some-div")); // {width:500, height:620}
	 */
	this.$size = function(element){
		return {width:element.offsetWidth, height:element.offsetHeight};
	};

	/**
	 * extra public method,
         * 	bytefx.size$(element:Object, size:Object):Void
         * @param	Object		X/HTML Element to set size
         * @param	Object		object with 2 properties, width and height as pixel dimension
         * @example
         * 		bytefx.size$(document.getElementById("some-div"), {width:500, height:620});
         *              // set element size to width = 500px and height = 620px
	 */
	this.size$ = function(element, size){
		var	style = element.style;
		style.width = size.width + "px";
		style.height = size.height + "px";
	};



	// private methods, used inside other public methods

	/**
	 * private method,
         * 	[virtual scope]$callback(element:Object, intervalName:String[, callback:Function]):Void
         * @param	Object		X/HTML Element to clear interval
         * @param	String		interval name ("move", "fade", ...)
         * @param	Function	callback to call with element as scope on event complete
	 */
	function $callback(element, interval, callback){
		clearInterval(element.bytefx[interval]);
		if(callback)
			callback.call(element);
	};

	/**
	 * private method,
         * 	[virtual scope]$element(element:Object):Object
         * @param	Object		X/HTML Element to verify
         * @param	Object		same element with bytefx object if was not present
	 */
	function $element(element){
		if(!element.bytefx)
			element.bytefx = {color:0, drag:{}, fade:0, move:0, scroll:0, size:0};
		return element;
	};

	/**
	 * private method,
         * 	[virtual scope]$end(x:Number, y:Number, speed:Number):Number
         * @param	Number		start UInt32 or Float number
         * @param	Number		end UInt32 or Float number (start number will be exactly this one)
         * @param	Number		UInt32 or Float number as increment velocity
         * @return	Number		new value, more near y than x
	 */
	function $end(x, y, speed){
		return x < y ? min(x + speed, y) : max(x - speed, y);
	};

	/**
	 * private method,
         * 	[virtual scope]$setInterval(element:Object, intervalName:String, speed:Number, startValues:Object, endValues:Object, propertiesName:Array, methodName:String[, finalCallback:Function]):Void
	 */
	function $setInterval(element, interval, speed, start, position, style, tmp, callback){
		clearInterval(element.bytefx[interval]);
		element.bytefx[interval] = setInterval(function(){
			start[style[0]] += (position[style[0]] - start[style[0]]) * speed;
			start[style[1]] += (position[style[1]] - start[style[1]]) * speed;
			bytefx[tmp](element, start);
			if(round(start[style[0]]) == position[style[0]] && round(start[style[1]]) == position[style[1]]){
				bytefx[tmp](element, position);
				$callback(element, interval, callback);
			}
		}, 1);
	};
	
	var	w = window,
		d = document,
		max = Math.max,
		min = Math.min,
		round = Math.round;
};