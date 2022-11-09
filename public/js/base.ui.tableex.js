/**
 * table要素をwrapしてカラムヘッダを固定したまま縦方向にスクロールできるように
 * 機能を付加するヘルパクラス
 */
base.UI.TableEx = Class.create();

Object.extend( base.UI.TableEx, {
	// 内部で利用するUI向け要素を識別するCSSクラス名
	classNames : {
		root : "base_ui_tableex",
		outerHeader : "base_ui_tableex_outerheader",
		header : "base_ui_tableex_header",
		outerBody : "base_ui_tableex_outerbody",
		body : "base_ui_tableex_body",
		colHeader : "base_ui_tableex_column"
	},
	// 元の要素からコピーをするCSSスタイル名のリスト
	styleNames : {
		root : [
			"color",
			"backgroundColor", "backgroundImage", "backgroundPosition", "backgroundRepeat",
			"fontSize", "fontFamily", "fontWeight"/*, "fontStyle", "fontVariant"*/
		],
		header : [
//			"color",
			"backgroundColor", "backgroundImage", "backgroundPosition", "backgroundRepeat",
			"borderTopColor", "borderTopWidth", "borderTopStyle",
			"borderRightColor", "borderRightWidth", "borderRightStyle",
			"borderLeftColor", "borderLeftWidth", "borderLeftStyle",
			"fontSize", "fontFamily", "fontWeight", "fontStyle", "fontVariant"
		],
		body : [
		],
		colHeader : [
			"color",
			"whiteSpace",
			"backgroundColor", "backgroundImage", "backgroundPosition", "backgroundRepeat",
			"borderTopColor", "borderTopWidth", "borderTopStyle",
			"borderRightColor", "borderRightWidth", "borderRightStyle",
			"borderBottomColor", "borderBottomWidth", "borderBottomStyle",
			"borderLeftColor", "borderLeftWidth", "borderLeftStyle",
			"paddingTop", "paddingBottom",
			"fontSize", "fontFamily", "fontWeight", "fontStyle", "fontVariant",
			"textAlign", "textDecoration"
		]
	}
} );

base.UI.TableEx.prototype = {
	// 初期化処理
	initialize : function(element, options) {
		this.element = $(element);

		// オプション引数の整形
		this.options = Object.extend( {
			height : 250
		}, options || {} );

		// ターゲットのtableをwrapするUI要素の基本ツリーを構成
		var container = [
			"<div class={0}>".format( base.UI.TableEx.classNames.root.toLiteral() ),
			"<div class={0}>".format( base.UI.TableEx.classNames.outerHeader.toLiteral() ),
			"<div class={0}></div>".format( base.UI.TableEx.classNames.header.toLiteral() ),
			"</div>",
			"<div class={0}>".format( base.UI.TableEx.classNames.outerBody.toLiteral() ),
			"<div class={0}></div>".format( base.UI.TableEx.classNames.body.toLiteral() ),
			"</div>",
			"</div>"
		].join("").toElement();

		// 参照用にプロパティへセット
		this.elements = {
			container : container,
			header : document.getElementsByClassName( base.UI.TableEx.classNames.header, container )[0],
			body : document.getElementsByClassName( base.UI.TableEx.classNames.body, container )[0],
			outerHeader : document.getElementsByClassName( base.UI.TableEx.classNames.outerHeader, container )[0],
			outerBody : document.getElementsByClassName( base.UI.TableEx.classNames.outerBody, container )[0]
		};

		// UIをtableのコンテナ要素へ挿入
		this.element.parentNode.insertBefore( this.elements.container, this.element );

		// tableから必要なスタイルをコピー
		Object.extend(
			this.elements.container.style,
			base.UI.getElementStyles( this.element, base.UI.TableEx.styleNames.root )
		);

		this.element.style.visibility = "hidden";

		// ボディ作成
		this.createBody();

		// ヘッダ作成
		this.createHeader();

	},

	// 元tableのカラムヘッダ行を取得する
	getHeaderRow : function() {
		var headerRow = null;
		if( this.element.getElementsByTagName("thead")[0] != null ) {
			headerRow = this.element.getElementsByTagName("thead")[0].getElementsByTagName("tr")[0];
		}
		if( headerRow == null ) {
			headerRow = ( this.element.getElementsByTagName("tbody")[0] != null ) ?
				( this.element.getElementsByTagName("tbody")[0].getElmentsByTagName("tr")[0] ) :
				( this.element.getElementsByTagName("tr")[0] );
		}
		return headerRow;
	},

	// 位置固定のヘッダ行を作成
	createHeader : function() {
		var header = this.elements.header;
		var headerRow = this.getHeaderRow();
		var firstRow = this.getFirstVisibleRow();

		header.innerHTML = "";
		Object.extend( header.style, {
			whiteSpace : "nowrap",
			overflow: "visible",
			width: "{0}px".format(
				this.element.offsetWidth * 1.5
			),
			height : base.UI.getElementStyles( headerRow, [ "height" ] ).height
		} );
		header.title = header.style.width;

		// td/thの代わりにdivでカラムヘッダを作成
		$A( headerRow.cells ).each( function(cell, index) {
			var col = "<div class={1}>{0}</div>".format( cell.innerHTML, base.UI.TableEx.classNames.colHeader ).toElement();

			var styles = base.UI.getElementStyles( cell, base.UI.TableEx.styleNames.colHeader );

			var refCol = firstRow.cells[index] || headerRow.cells[index];

			Object.extend( col.style, styles );
			Object.extend( col.style, {
				width : "{0}px".format(
					refCol.clientWidth ||
					refCol.offsetWidth ||
					parseInt( refCol.style.width )
				)
			} );

			header.appendChild( col );
		} );

		// 元のヘッダ行を非表示にする
		Element.hide( headerRow );

		// カラム幅の最終調整
		setTimeout( this.fixColumnSize.bind( this ), 0 );
	},

	// 表本体を構成する
	createBody : function() {
		var body = this.elements.body;
		var outer = this.elements.outerBody;

		// 内部ボディのサイズを大きく取る
		Object.extend( body.style, {
			width : "{0}px".format( 20000 ),
			height: "{0}px".format( 5000 )
		} );
		// テーブルを内部ボディに挿入
		body.appendChild( this.element );

		// スクロール領域の高さ確定
		Object.extend( outer.style, {
			height : "{0}px".format( this.options.height )
		} );

		var _self = this;
		Event.observe( window, "resize", function(evt) {
			setTimeout( _self.fixColumnSize.bind( _self ), 0 );
		}.bindAsEventListener( window ) );
	},

	// カラムヘッダの幅を調整する
	fixColumnSize : function() {
		var $S = base.UI.getElementStyles.bind( base.UI );
		var cols = document.getElementsByClassName( base.UI.TableEx.classNames.colHeader, this.elements.header );
		var cells = $A( this.getFirstVisibleRow().getElementsByTagName("td") );

		var len = cols.length > cells.length ? cols.length : cells.length;
		for(var i = 0; i < len; i++) {
			var col = cols[i], cell = cells[i];

			if( ! cell ) continue;

			cell.style.whiteSpace = "nowrap";

			var colWidth = parseInt( $S( col, [ "width" ] ).width );
			var cellStyles = $S( cell, [ "width", "paddingLeft", "paddingRight" ] );
			if( isNaN( parseInt( cellStyles.width ) ) ) cellStyles.width = cell.clientWidth;
			if( isNaN( parseInt( cellStyles.paddingLeft ) ) ) cellStyles.paddingLeft = 0;
			if( isNaN( parseInt( cellStyles.paddingRight ) ) ) cellStyles.paddingRight = 0;

			var cellWidth = parseInt( cellStyles.width );
			var pad = parseInt( cellStyles.paddingLeft ) + parseInt( cellStyles.paddingRight );

			if( colWidth > cellWidth ) {
				// ヘッダが広い場合はそちらに合わせる
				// ※ style.widthを指定すると、それ＋左右のパディングになるため、パディングを差し引く
				cell.style.width = "{0}px".format( colWidth - pad );
			} else {
				// データカラムが広い場合はそちらに合わせる
				if( /(Internet Explorer)|(Opera)/.test( base.UI.currentBrowser() ) ) {
					// IEとOperaはpaddingを考慮しない
					col.style.width = "{0}px".format( cellWidth );
				} else {
					col.style.width = "{0}px".format( cellWidth + pad );
				}
			}
		}
		// 内部ボディのサイズをテーブルに合わせる
		var tableStyles = base.UI.getElementStyles( this.element, [ "width", "height" ] );
		Object.extend( this.elements.body.style, {
			width : "{0}px".format(
				( this.element.clientWidth || parseInt( tableStyles.width ) ) + 4
			),
			height : "{0}px".format(
				( this.element.offsetHeight || parseInt( tableStyles.height ) ) + 4
			)
		} );
		// ヘッダコンテナのサイズ調整
		var widthes = {
			outer : this.elements.outerBody.clientWidth || this.elements.outerBody.offsetWidth,
			inner : this.elements.body.clientWidth || this.elements.body.offsetWidth
		}
		this.elements.outerHeader.style.width = "{0}px".format(
			widthes.outer > widthes.inner ? widthes.inner : widthes.outer
		);

		// ボディのスクロール領域の高さを再計算
		if( parseInt( this.elements.body.style.height ) < this.options.height ) {
			this.elements.outerBody.style.height = "{0}px".format(
				parseInt( this.elements.body.style.height )
				+ 18
			);
		}

		// IE7以前の場合のみ、外側の要素の幅を100%指定する
		if( base.UI.currentBrowser() == base.UI.browserTypes.ie ) {
			with( this.elements ) {
				container.style.width = outerHeader.style.width = outerBody.style.width = "100%";
			}
		}

		var _self = this;
		// 本体とカラムヘッダのスクロールを同期させる
		Event.observe( this.elements.outerBody, "scroll", function() {
			_self.elements.outerHeader.scrollLeft = _self.elements.outerBody.scrollLeft;
		}, false );

		this.element.style.visibility = "visible";

	},

	// 現在表示中の先頭のデータ行を取得する
	getFirstVisibleRow : function() {
		return $A( this.element.getElementsByTagName("tr") ).find( function(tr) {
			return Element.visible( tr ) && tr.getElementsByTagName("td").length > 0;
		} );
	}
}
