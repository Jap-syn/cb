// ModalDialogクラス
base.UI.ModalDialog = Class.create();

// 定数およびスタティックプロパティの定義
Object.extend( base.UI.ModalDialog, {
	// 現在表示中のインスタンスのリスト
	__actives : [],
	
	// 生成したインスタンスのカウント
	__count : 0,
	
	// 定義済みのCSSクラス名
	classNames : {
		root : "base_ui_modaldialog",
		background : "base_ui_modaldialog_background",
		title : "base_ui_modaldialog_title",
		container : "base_ui_modaldialog_container"
	},
	
	// 指定のbase.UI.ModalDialogインスタンスからアクティブ化の通知を受け取る
	_setActive : function(instance) {
		var current = base.UI.ModalDialog.__actives.last();
		
		var zIndex = current ? parseInt( current.style.zIndex ) : 899;
		instance.elements.background.style.zIndex = zIndex + 1;
		instance.elements.root.style.zIndex = zIndex + 2;
		
		base.UI.ModalDialog.__actives.push( instance );
	},
	
	// 指定のbase.UI.ModalDialogインスタンスから非アクティブ化の通知を受け取る
	_setDeactive : function(instance) {
		base.UI.ModalDialog.__actives = base.UI.ModalDialog.__actives.findAll( function(o) {
			return o != instance;
		} );
	}
} );

base.UI.ModalDialog.prototype = {
	initialize : function(element, options) {
		options = Object.extend( {
			width : 320,
			height: 240,
			title : "ダイアログ",
			preClose : Prototype.emptyFunction,
			draggable : false
		}, options );
		
		options.contents = $(element);
		
		this.options = options;
		
		var count = ++base.UI.ModalDialog.__count;
		
		this.elements = {
			root : Object.extend( document.createElement("div"), {
				id : "base_UI_ModalDialog_{0}".format( count ),
				className : base.UI.ModalDialog.classNames.root
			} ),
			background : Object.extend( document.createElement("div"), {
				id : "base_UI_ModalDialog_Bg_{0}".format( count ),
				className : base.UI.ModalDialog.classNames.background
			} ),
			title : Object.extend( document.createElement("div"), {
				id : "base_UI_ModalDialog_Title_{0}".format( count ),
				className : base.UI.ModalDialog.classNames.title
			} ),
			container : Object.extend( document.createElement("div"), {
				id : "base_UI_ModalDialog_Container_{0}".format( count ),
				className : base.UI.ModalDialog.classNames.container
			} ),
			ieCover : null
		};
		
		with( this.elements ) {
			// オプションを反映
			$H( options ).keys().each( function(key) {
				var method = "set{0}".format( key.replace( /^./, function(s) { return s.toUpperCase(); } ) );
				if( typeof( this[method] ) != "function" ) throw $continue;
				this[ method ]( options[ key ] );
			}.bind( this ) );
			// バックグラウンドカバーをdocument.bodyへ追加
			document.body.appendChild( background );
			// ルート要素をdocument.bodyへ追加
			document.body.appendChild( root );
			// 子要素をルートへ追加
			root.appendChild( title );
			root.appendChild( container );
		}
		
		if( options.draggable ) {
			// ドラッグ可能に
			new base.UI.DragHelper( this.elements.root, this.elements.title );
		}
		
		this.hide();
	},
	setTitle : function(title) {
		this.elements.title.innerHTML = title || "";
		
		return this;
	},
	getTitle : function() {
		return this.elements.title.innerHTML.stripTags();
	},
	setWidth : function(width) {
		if( isNaN( width ) ) width = 32;
		this.elements.root.style.width = "{0}px".format( Math.abs(width) );
		
		return this;
	},
	getWidth : function() {
		return parseInt( this.elements.root.style.width );
	},
	setHeight : function(height) {
		if( isNaN( height ) ) height = 32;
		this.elements.root.style.height = "{0}px".format( Math.abs( height ) );
		
		return this;
	},
	getHeight : function() {
		return parseInt( this.elements.root.style.height );
	},
	setContents : function(contents) {
		contents = $(contents);
		with( this.elements ) {
			var old = this.getContents();
			if( old ) container.removeChild( old );
			if( contents ) container.appendChild( contents );
		}
		
		return this;
	},
	getContents : function() {
		return this.elements.firstChild;
	},
	center : function() {
		with( {
			bg : this.elements.background,
			root : this.elements.root
		} ) {
			var offset = {
				left : document.documentElement.scrollLeft || document.body.scrollLeft,
				top : document.documentElement.scrollTop || document.body.scrollTop
			};
			
			var baseSize = base.UI.currentBrowser() == base.UI.browserTypes.opera ?
				{
					width : document.body.clientWidth,
					height : document.body.clientHeight
				} :
				{
					width : document.documentElement.clientWidth,
					height : document.documentElement.clientHeight
				};
			Object.extend( root.style, {
				left : "{0}px".format(
					parseInt( ( baseSize.width - this.getWidth() ) / 2 ) + offset.left
				),
				top : "{0}px".format(
					parseInt( ( baseSize.height - this.getHeight() ) / 2 ) + offset.top
				)
			} );
		}
		
		return this;
	},
	// ダイアログを表示する
	show : function() {
		this._setCover();
		var bg = this.elements.background;
		var root = this.elements.root;
		
		// バックグラウンドカバーのサイズ確定
		Object.extend( bg.style, {
			left : "0px", top : "0px",
			width : "{0}px".format(
				[
					document.documentElement.scrollHeight,
					document.body.scrollWidth,
					document.documentElement.clientWidth,
					document.body.clientWidth
				].max()
			),
			height: "{0}px".format(
				[
					document.documentElement.scrollHeight,
					document.body.scrollHeight,
					document.documentElement.clientHeight,
					document.body.clientHeight
				].max()
			)
		} );
		
		// ルート要素の表示位置確定
		this.center();
		
		// アクティブ化を通知
		base.UI.ModalDialog._setActive( this );
		
		// 表示
		[ root, bg, this.getContents() ].each( function(element) {
			if( element ) Element.show( element );
		} );
		
		// 表示コンテナの高さ確定
		this.elements.container.style.height = "{0}px".format(
			this.elements.root.clientHeight - this.elements.container.offsetTop - 4
		);
		
		return this;
	},
	// ダイアログを閉じる
	hide : function() {
		if( ( this.options.preClose || Prototype.K )() === false ) return;
		
		with( {
			root : this.elements.root,
			bg : this.elements.background
		} ) {
			[ root, bg, this.getContents() ].each( function(element) {
				if( element ) Element.hide( element );
			} );
		}
		
		// 非アクティブ化を通知
		base.UI.ModalDialog._setDeactive( this );
		
		return this;
	},
	// show()のエイリアス
	open : function() {
		return this.show();
	},
	// hide()のエイリアス
	close : function() {
		return this.hide();
	},
	// IE向けのカバー用IFRAMEを追加する
	_setCover : function() {
		if( base.UI.currentBrowser() == base.UI.browserTypes.ie && ! this.elements.ieCover ) {
			this.elements.ieCover = Object.extend( document.createElement("iframe"), {
				frameborder : 0,
				src : "/blank.html"
			} );
			Object.extend( this.elements.ieCover.style, {
				position : "absolute",
				left : "0px",
				top : "0px",
				width : "100%",
				height: "100%",
				filter : "alpha(opacity=0)",
				zIndex : -1
			} );
			this.elements.root.insertBefore( this.elements.ieCover, this.elements.root.firstChild );
			this.elements.background.appendChild( this.elements.ieCover.cloneNode(true) );
		}
	}
}
