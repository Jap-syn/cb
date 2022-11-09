// 検索結果のカラムオーダー編集ライブラリ

/**
 * 編集カラムオブジェクトを配置するコンテナクラス
 */
var ColumnContainer = Class.create();
ColumnContainer.prototype = {
	/**
	 * ColumnContainerの新しいインスタンスを初期化する
	 * 
	 * @param string|object element コンテナとして扱う要素
	 * @param string mode 格納するカラムオブジェクトのモード。'visible'または'invisible'のどちらか
	 */
	initialize : function(element, mode) {
		this.element = $(element);
		this._columns = [];
		this.mode = mode || "visible";
		ColumnContainer._add( this );
	},
	
	/**
	 * このコンテナに格納されているカラムオブジェクトの配列を取得する
	 * 
	 * @return array
	 */
	columns : function() {
		return document.getElementsByClassName("column_object", this.element).map( function(element) {
			return EditableColumn._columns.findColumn( element );
		} ).compact();
	},
	
	/**
	 * 格納しているカラムオブジェクトをすべてクリアする
	 */
	clear : function() {
		document.getElementsByClassName("column_object", this.element).each( function(col) {
			col.parentNode.removeChild( col );
		} );
		
		this._columns = [];
	},
	
	/**
	 * 格納されている子要素の表示状態を初期化する
	 */
	refresh : function() {
		var _self = this;
		this.columns().each( function(column) {
			column.classNames.remove( _self.mode == "visible" ? "invisible_column" : "visible_column" );
			column.classNames.add( "{0}_column".format( _self.mode ) );
		} );
	}
};
Object.extend( ColumnContainer, {
	// 初期化済みフラグ
	_initialized : false,
	// 生成されたColumnContainerのインスタンスの配列
	_containers : [],
	/**
	 * ColumnContainerのインスタンスを管理リストに追加する
	 * 
	 * @param ColumnContainer instance 管理リストに追加するColumnContainer
	 */
	_add : function(instance) {
		// インスタンスリストの初期化。検索メソッドを追加する
		if( ! this._initialized ) {
			this._containers = [];
			this._containers = Object.extend( [], {
				// ColumnContainerから関連する要素を検索
				findElement : function(container) {
					var result = this.find( function(item) {
						return item == container;
					} );
					return result ? result.element : null;
				}.bind( this._containers ),
				// 指定要素に関連付けられたColumnContainerを検索
				findContainer : function(element) {
					return this.find( function(item) {
						return item.element == element;
					} );
				}
			} );
			this._initialized = true;
		}
		this._containers.push( instance );
	},
	/**
	 * ColumnContainerのインスタンスリストを取得する
	 * 
	 * @return array
	 */
	containers : function() {
		return [].concat( this._containers );
	}
} );


/**
 * ドラッグドロップでカラムオーダーと表示状態を編集するためのカラムオブジェクト
 */
var EditableColumn = Class.create();
EditableColumn.prototype = {
	/**
	 * EditableColumnの新しいインスタンスを初期化する
	 * 
	 * @param string|object element 操作対象の要素
	 * @param object properties 関連付けて管理するハッシュ
	 */
	initialize : function(element, properties) {
		this.properties = Object.extend( {
			column : "",
			order : -1,
			caption : "",
			hidden : false
		}, properties );
		
		this.element = $(element);
		this.element.innerHTML = this.properties.caption;
		this.classNames = new Element.ClassNames( this.element );
		
		this.classNames.add( "column_object");
		EditableColumn._add( this );
		
		this._timer = null;
		var _self = this;
		
		var _offset = null;
		bytefx.drag(
			// target element
			this.element,
			// drag start
			function(evt) {
				evt = Object.extend( {}, evt || window.event || event );
				if( _self.timer ) {
					clearTimeout( _self.timer );
					_self.timer = null;
				}
				
				_offset = new Point(
					( evt.layerX || evt.offsetX || 0 ) - ( [ document.documentElement.scrollLeft, document.body.scrollLeft ].max() ),
					( evt.layerY || evt.offsetY || 0 ) - ( [ document.documentElement.scrollTop, document.body.scrollTop ].max() )
				);
				
				_self.classNames.add( "dragging_column" );
				Object.extend( this.style, {
					position : "absolute",
					zIndex : 999
				} );
				this.parentNode.insertBefore( EditableColumn.getPlaceHolder( this.innerHTML ), this );
				Element.show( EditableColumn.getPlaceHolder() );
				EditableColumn.currentColumn = this;
				
				document.body.appendChild( this );
				bytefx.position( this, {
					x : evt.clientX - _offset.x,
					y : evt.clientY - _offset.y
				} );
				
				bytefx.alpha( EditableColumn.getPlaceHolder(), 70 );
			}.bind( this.element ),
			// drag end
			function(evt) {
				evt = Object.extend( {}, evt || window.event || event );
				if( _self.timer ) {
					clearTimeout( _self.timer );
					_self.timer = null;
				}
				var target = EditableColumn.getPlaceHolder();
				bytefx.position( this, {
					x : evt.clientX - _offset.x,
					y : evt.clientY - _offset.y
				} );
				
				bytefx.move( this, bytefx.$position( target ), 60, function() {
					Element.hide( EditableColumn.getPlaceHolder() );
					bytefx.alpha( EditableColumn.getPlaceHolder(), 100 );
					EditableColumn.currentColumn = null;
					
					
					target.parentNode.insertBefore( this, target );
					
					_self.classNames.remove( "dragging_column" );
					Object.extend( this.style, {
						position : "",
						zIndex : 0
					} );
					
					ColumnContainer.containers().each( function(container) {
						container.refresh();
					} );
				}.bind( this ) );
			}.bind( this.element ),
			// callback while dragging
			function(evt) {
				evt = Object.extend( {}, evt || window.event || event );
				
				bytefx.position( this, {
					x : evt.clientX - _offset.x,
					y : evt.clientY - _offset.y
				} );
				
				var in_rect = function(element, point) {
					var p = bytefx.$position(element);
					var s = bytefx.$size(element);
					return new Rectangle( p.x, p.y, s.width, s.height ).contains( point );
				};
				
				if( _self.timer ) clearTimeout( _self.timer );
				_self.timer = setTimeout( function() {
					var cursor = new Point( Event.pointerX(evt), Event.pointerY(evt) );
					var placeholder = EditableColumn.getPlaceHolder();
					
					if( ! in_rect( placeholder, cursor ) ) {
						var target = EditableColumn._columns.find( function(column) {
							if( column == _self ) return false;
							return in_rect( column.element, cursor );
						}.bind( this ) );
						if( target && target.element.parentNode ) {
							var before = target.element.previousSibling == placeholder ?
								target.element.nextSibling : target.element;
							target.element.parentNode.insertBefore( placeholder, before );
							return;
						}
						
						target = ColumnContainer.containers().find( function(container) {
							return in_rect( container.element, cursor );
						} );
						if( target ) {
							target.element.appendChild( placeholder );
						}
					}
					
					_self.timer = null;
				}.bind( this ), 50 );
			}.bind( this.element ),
			// position
			null
		);
	}
};
Object.extend( EditableColumn, {
	_initialized : false,
	_columns : null,
	_init : function() {
		this._columns = [];
		
		this._columns = Object.extend( this._columns, {
			findElement : function(column) {
				var result = this.find( function(item) {
					return item == column;
				} );
				return result ? result.element : null;
			}.bind( this._columns ),
			findColumn : function(element) {
				var result = this.find( function(item) {
					return item.element == element;
				} );
				return result;
			}.bind( this._columns )
		} );
		
		this._initialized = true;
	},
	_add : function(column) {
		if( ! this._initialized ) this._init();
		
		this._columns.push( column )
	},
	getPlaceHolder : function(text) {
		if( this.__placeHolder == null ) {
			this.__placeHolder = "<div class=\"column_object placeholder\"></div>".toElement();
		}
		this.__placeHolder.innerHTML = text || this.__placeHolder.innerHTML || "placeholder";
		return this.__placeHolder;
	}
} );

