/**
 * UIライブラリの名前空間兼ユーティリティクラス
 */
base.UI = {
	// 判別可能なブラウザの定義
	browserTypes : {
		ie : "Internet Explorer",
		ie7 : "Internet Explorer 7",
		ie9 : "Internet Explorer 9",
		ie11 : "Internet Explorer 11",
		ff : "Mozilla Firefox",
		opera : "Opera",
		sf : "Safari",
		sf3 : "Safari 3",
		other : "unknown browser"
	},

	// 現在のブラウザ種別を取得する
	currentBrowser : function() {
		if( this._currentRuntime == null ) {
			if( typeof(ActiveXObject) == "function" ) {
				// IE/IE7
				this._currentBrowser = typeof( XMLHttpRequest ) == "undefined" ?
					this.browserTypes.ie : this.browserTypes.ie7;
			} else if( window.document.all && !window.opera ) {
				// IE9
				this._currentBrowser = this.browserTypes.ie9;
			} else if( /(MSIE)|(Trident)/.test(window.navigator.userAgent) ) {
				// IE11
				this._currentBrowser = this.browserTypes.ie11;
			} else if( window.opera && typeof( window.opera.postError ) == "function" ) {
				// opera
				this._currentBrowser = this.browserTypes.opera;
			} else if( window.sidebar && typeof( window.sidebar.addPanel ) == "function" ) {
				// Mozilla/Firefox
				this._currentBrowser = this.browserTypes.ff;
			} else if( window.mozInnerScreenX != undefined ) {
				// Mozilla/Firefox
				this._currentBrowser = this.browserTypes.ff;
			} else if( window.getMatchedCSSRules ) {
				// Safari 3
				this._currentBrowser = this.browserTypes.sf3;
			} else if( /Konqueror|Safari|KHTML/.test( window.navigator.userAgent ) ) {
				// Safari
				this._currentBrowser = this.browserTypes.sf;
			} else {
				// other browser
				this._currentBrowser = this.browserTypes.other;
			}
		}
		return this._currentBrowser;
	},

	isIE : function(browserType) {
		browserType = browserType || base.UI.currentBrowser();
		return [
				base.UI.browserTypes.ie,
				base.UI.browserTypes.ie7,
				base.UI.browserTypes.ie9,
				base.UI.browserTypes.ie11 ].include( browserType );
	},

	isOpera : function(browserType) {
		browserType = browserType || base.UI.currentBrowser();
		return browserType == base.UI.browserTypes.opera;
	},

	isMozilla : function(browserType) {
		browserType = browserType || base.UI.currentBrowser();
		return browserType == base.UI.browserTypes.ff;
	},

	isSafari : function(browserType) {
		return [ base.UI.browserTypes.sf, base.UI.browserTypes.sf3 ].include( browserType );
	},

	// 指定のHTML要素の実行時スタイルを取得する
	getElementStyles : function(target, properties) {
		target = $(target);
		properties = ( properties instanceof Array ) ? properties : [];
		var obj = target.currentStyle ||
			(
				document.getComputedStyle ?
					document.getComputedStyle( target, "" ) :
					document.defaultView.getComputedStyle( target, "" )
			);

		var result = {};
		properties.each( function(prop) {
			// プロパティ指定がない場合か、対象プロパティが指定プロパティに含まれる場合は
			// 結果にセットする
			if( obj[ prop ] || properties.length == 0 ) {
				var val = ( obj[prop] || "" );
				// プロパティ値がクォートされていたらそれを解除する（Operaのみ）
				result[ prop ] = ( base.UI.currentBrowser() == base.UI.browserTypes.opera ) ?
					val.replace( /(["']|(\\")|(\\'))/g, "" ) : val;
			};
		} );

		return result;
	},

	 // Webブラウザ定義の名前付き140色の辞書を取得する。
	 // プロパティ名がカラーネームで値が16進色表現（#[0-9A-z]{6}）になる
	namedColors : {
		aliceblue : "#F0F8FF", antiquewhite : "#FAEBD7",
		aqua : "#00FFFF", aquamarine : "#7FFFD4",
		azure : "#F0FFFF", beige : "#F5F5DC",
		bisque : "#FFE4C4", black : "#000000",
		blanchedalmond : "#FFEBCD", blue : "#0000FF",
		blueviolet : "#8A2BE2", brown : "#A52A2A",
		burlywood : "#DEB887", cadetblue : "#5F9EA0",
		chartreuse : "#7FFF00", chocolate : "#D2691E",
		coral : "#FF7F50", cornflowerblue : "#6495ED",
		cornsilk : "#FFF8DC", crimson : "#DC143C",
		cyan : "#00FFFF", darkblue : "#00008B",
		darkcyan : "#008B8B", darkgoldenrod : "#B8860B",
		darkgray : "#A9A9A9", darkgreen : "#006400",
		darkkhaki : "#BDB76B", darkmagenta : "#8B008B",
		darkolivegreen : "#556B2F", darkorange : "#FF8C00",
		darkorchid : "#9932CC", darkred : "#8B0000",
		darksalmon : "#E9967A", darkseagreen : "#8FBC8F",
		darkslateblue : "#483D8B", darkslategray : "#2F4F4F",
		darkturquoise : "#00CED1", darkviolet : "#9400D3",
		deeppink : "#FF1493", deepskyblue : "#00BFFF",
		dimgray : "#696969", dodgerblue : "#1E90FF",
		firebrick : "#B22222", floralwhite : "#FFFAF0",
		forestgreen : "#228B22", fuchsia : "#FF00FF",
		gainsboro : "#DCDCDC", ghostwhite : "#F8F8FF",
		gold : "#FFD700", goldenrod : "#DAA520",
		gray : "#808080", green : "#008000",
		greenyellow : "#ADFF2F", honeydew : "#F0FFF0",
		hotpink : "#FF69B4", indianred : "#CD5C5C",
		indigo : "#4B0082", ivory : "#FFFFF0",
		khaki : "#F0E68C", lavender : "#E6E6FA",
		lavenderblush : "#FFF0F5", lawngreen : "#7CFC00",
		lemonchiffon : "#FFFACD", lightblue : "#ADD8E6",
		lightcoral : "#F08080", lightcyan : "#E0FFFF",
		lightgoldenrodyellow : "#FAFAD2", lightgreen : "#90EE90",
		lightgrey : "#D3D3D3", lightpink : "#FFB6C1",
		lightsalmon : "#FFA07A", lightseagreen : "#20B2AA",
		lightskyblue : "#87CEFA", lightslategray : "#778899",
		lightsteelblue : "#B0C4DE", lightyellow : "#FFFFE0",
		lime : "#00FF00", limegreen : "#32CD32",
		linen : "#FAF0E6", magenta : "#FF00FF",
		maroon : "#800000", mediumaquamarine : "#66CDAA",
		mediumblue : "#0000CD", mediumorchid : "#BA55D3",
		mediumpurple : "#9370DB", mediumseagreen : "#3CB371",
		mediumslateblue : "#7B68EE", mediumspringgreen : "#00FA9A",
		mediumturquoise : "#48D1CC", mediumvioletred : "#C71585",
		midnightblue : "#191970", mintcream : "#F5FFFA",
		mistyrose : "#FFE4E1", moccasin : "#FFE4B5",
		navajowhite : "#FFDEAD", navy : "#000080",
		oldlace : "#FDF5E6", olive : "#808000",
		olivedrab : "#6B8E23", orange : "#FFA500",
		orangered : "#FF4500", orchid : "#DA70D6",
		palegoldenrod : "#EEE8AA", palegreen : "#98FB98",
		paleturquoise : "#AFEEEE", palevioletred : "#DB7093",
		papayawhip : "#FFEFD5", peachpuff : "#FFDAB9",
		peru : "#CD853F", pink : "#FFC0CB",
		plum : "#DDA0DD", powderblue : "#B0E0E6",
		purple : "#800080", red : "#FF0000",
		rosybrown : "#BC8F8F", royalblue : "#4169E1",
		saddlebrown : "#8B4513", salmon : "#FA8072",
		sandybrown : "#F4A460", seagreen : "#2E8B57",
		seashell : "#FFF5EE", sienna : "#A0522D",
		silver : "#C0C0C0", skyblue : "#87CEEB",
		slateblue : "#6A5ACD", slategray : "#708090",
		snow : "#FFFAFA", springgreen : "#00FF7F",
		steelblue : "#4682B4", tan : "#D2B48C",
		teal : "#008080", thistle : "#D8BFD8",
		tomato : "#FF6347", turquoise : "#40E0D0",
		violet : "#EE82EE", wheat : "#F5DEB3",
		white : "#FFFFFF", whitesmoke : "#F5F5F5",
		yellow : "#FFFF00", yellowgreen : "#9ACD32"
	},

	// 指定のカラー名を#xxx形式のカラー値に展開する
	// 未定義のカラー名の場合は引数がそのまま返る
	parseColorName : function(color) {
		return this.namedColors[ color.toLowerCase() ] || color;
	},
	calcAbsolutePosition : function(element) {
		var x = 0, y = 0;
		while( element ) {
			x += element.offsetLeft || 0;
			y += element.offsetTop || 0;
			element = element.parentNode;
		}
		return new Point( x, y );
	}
}

/**
 * 任意の要素（または要素の組み合わせ）を、マウスによるドラッグドロップ
 * が可能になるように拡張するヘルパクラス
 * @class
 * @constrcutro
 * @param {HTMLDOMObject} dragTarget ドラッグドロップでの移動機能を追加するHTML要素。positionはabsoluteである必要がある
 * @param {HTMLDOMObject} dragHandle dragTargetを移動させるドラッグハンドル要素。省略時はdragTargetになる
 */
base.UI.DragHelper = Class.create();
base.UI.DragHelper.prototype = {
	/**
	 * @ignore
	 * @private
	 * 初期化処理
	 */
	initialize : function(dragTarget, dragHandle) {
		this.target = $( dragTarget );
		this.handle = $( dragHandle ) || this.target;

		var _self = this;
		var br = base.UI.currentBrowser();

		var _events = {
			onStart : function(evt) {
				if( typeof( _self.onstart ) == "function" ) {
					return _self.onstart( evt );
				}
			},
			onBeforeMove : function(moveSize, evt) {
				if( typeof( _self.onbeforemove ) == "function" ) {
					return _self.onbeforemove( moveSize, evt );
				}
				return moveSize;
			},
			onAfterMove : function(movedSize, evt) {
				if( typeof( _self.onmove ) == "function" ) {
					_self.onmove( movedSize, evt );
				}
			},
			onEnd : function(evt) {
				if( typeof( _self.onend ) == "function" ) {
					return _self.onend( evt );
				}
			}
		}

		// ドラッグハンドルのmousedownでドラッグ開始
		Event.observe( this.handle, "mousedown", function(evt) {
			_events.onStart( evt );
			var mousePos = new Point( Event.pointerX(evt), Event.pointerY(evt) );

			// ドラッグ中のイベントハンドラ
			var mouseMove = function(ev) {
				var newPos = new Point( Event.pointerX(ev), Event.pointerY(ev) );
				var v = Point.subtract( newPos, mousePos.toSize() );

				var pos = _self.target.style.position == "absolute" ?
					new Point( parseInt( Element.getStyle( _self.target, "left" ) ), parseInt( Element.getStyle( _self.target, "top" ) ) ) :
					new Point( _self.target.offsetLeft, _self.target.offsetTop );

				var pos2 = pos.clone();

				var size = _events.onBeforeMove( v.toSize(), ev );

				if( size != null ) {
					pos.add( size );
					Element.setStyle( _self.target, { left : "{0}px".format( pos.x.toString() ), top : "{0}px".format( pos.y.toString() ) } );
					mousePos.add( size );
				} else {
					mousePos = newPos;
				}
				_events.onAfterMove( size, ev );

			}.bindAsEventListener( _self.handle );

			// ドラッグ完了イベントハンドラ
			var mouseUp = function(ev) {
				if( window.releaseEvents ) {
					window.releaseEvents( Event.MouseMove | Event.MouseUp );
				} else if( _self.handle.releaseCapture ) {
					_self.handle.releaseCapture();
				}
				// mousemove/mouseupのイベントハンドラを解除する
				Event.stopObserving( window.captureEvents ? window : _self.handle, "mousemove", mouseMove, false );
				Event.stopObserving( window.captureEvents ? window : _self.handle, "mouseup", mouseUp, false );

				_events.onEnd( evt );
			}.bindAsEventListener( _self.handle );

			// マウスキャプチャを開始
			if( window.captureEvents ) {
				window.captureEvents( Event.MouseMove | Event.MouseUp );
			} else if( _self.handle.setCapture ) {
				_self.handle.setCapture();
			} else {
				return;
			}
			// ドラッグ中イベント、ドラッグ完了イベントのハンドラを登録する
			Event.observe( window.captureEvents ? window : _self.handle, "mousemove", mouseMove, false );
			Event.observe( window.captureEvents ? window : _self.handle, "mouseup", mouseUp, false );

		}.bindAsEventListener( this.handle ), false );
	}
}
