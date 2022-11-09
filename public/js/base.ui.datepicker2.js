// 月内の初日と最終日を習得するメソッドをDate.prototypeに拡張
Object.extend( Date.prototype, {
	getFirstDate : function() {
		var d = new Date( this.getFullYear(), this.getMonth(), 1 );
		return d;
	},
	getLastDate : function() {
		var m = {
			year : this.getFullYear(),
			month : this.getMonth()
		}
		var d = 32;
		while( m.month != new Date( m.year, m.month, --d ).getMonth() );
		return new Date( m.year, m.month, d );
	}
} );

// DatePicker2クラス
base.UI.DatePicker2 = Class.create();
// 定数およびスタティックプロパティの定義
Object.extend( base.UI.DatePicker2, {
	// コントロールで使用するCSSクラス名の定義
	classNames : {
		root : "base_ui_datepicker",
		header : "header",
		body : "body",
		prevYear : "prev_year",
		prevMonth : "prev_month",
		nextYear : "next_year",
		nextMonth : "next_month",
		titleCell : "title",
		headerRow : "header_row",
		weekRow : "week_row",
		cell : "date",
		selected : "selected",
		holiday : "holiday",
		saturday : "saturday",
		otherMonth : "other_month",
		cmdBar : "calendar_command_bar",
		cmdButton : "calendar_command_button"
	},
	// 設定可能なイベント名の定義
	eventNames : {
		beforepopup : "onBeforePopup",
		popup : "onPopup",
		beforemonthchanged : "onBeforeMonthChanged",
		monthchanged : "onMonthChanged",
		beforechange : "onBeforeChange",
		change : "onChange"
	},
	// DatePickerインスタンスの数
	__instances : [],
	// 初期化済みフラグ
	__initialized : false,
	// 初期化処理。コントロールを構成するHTMLのベースインスタンスを構築する
	__initialize : function() {
		if( base.UI.DatePicker2.__initialized ) return;
		base.UI.DatePicker2.__initialized = true;
		base.UI.DatePicker2._elementTemplate = (function() {
			var buf = [
				"<div class=\"{0}\" style=\"position:absolute;;display:none;\">".format( this.classNames.root ),
				"<table border=\"1\" cellpadding=\"0\" cellspacing=\"0\" class=\"{0}\">".format( this.classNames.header ),
				"<tbody>",
				"<tr>",
					"<td class=\"prev_year\" style=\"display: none;\">&nbsp;</td>",
					"<td class=\"prev_month\">&nbsp;</td>",
					"<td class=\"title\"></td>",
					"<td class=\"next_month\">&nbsp;</td>",
					"<td class=\"next_year\" style=\"display: none;\">&nbsp;</td>",
				"</tr>",
				"</tbody>",
				"</table>",
				"<table border=\"1\" cellpadding=\"0\" cellspacing=\"0\" class=\"{0}\">".format( this.classNames.body ),
				"<tbody>",
				"<tr class=\"{0}\">".format( this.classNames.headerRow ),
				"日月火水木金土".toArray().map( function(s) {
					return "<th>{0}</th>".format( s );
				} ).join(""),
				"</tr>",
				$R(0, 5).map( function(r) {
					return [
						"<tr class=\"{0}\">".format( this.classNames.weekRow ),
						String.create( "<td></td>", 7 ),
						"</tr>"
					].join("");
				}.bind( this ) ).join(""),
				"<tr><td class=\"{0}\" colspan=\"7\">".format( this.classNames.cmdBar ),
				[
					{ className : "yesterday", label : "昨日" },
					{ className : "today", label : "今日" },
					{ className : "tomorrow", label : "明日" }
				].map( function(conf) {
					return "<a href=\"#\" class=\"{0} {1}\" onclick=\"return false\">{2}</a>".format(
						this.classNames.cmdButton, conf.className, conf.label
					);
				}.bind( this ) ).join(""),
				"</td></tr>",
				"</tbody>",
				"</table>",
				"</div>"
			];
			
			var result = buf.join("").toElement();
			if( base.UI.currentBrowser() == base.UI.browserTypes.ie ) {
				var ifr = document.createElement("iframe");
				Object.extend( ifr, {
					frameborder : 0,
					src : "/blank.html"
				} );
				Object.extend( ifr.style, {
					position: "absolute",
					left : "0px",
					top : "0px",
					width : "100%",
					height : "100%",
					filter : "alpha(opacity=0)"
				} );
				result.insertBefore( ifr, result.firstChild );
				$A( result.getElementsByTagName("table") ).each( function(tbl) {
					tbl.style.position = "relative";
				} );
			}
			return result;
		}.bind( base.UI.DatePicker2 ) )();
	},
	addInstance : function(instance) {
		if( ! ( instance instanceof this ) ) return;
		this.__instances.push( instance );
	},
	getInstances : function() {
		return this.__instances;
	},
	requirePopup : function(instance) {
		for(var i = 0; i < this.__instances.length; i++) {
			var _ins = this.__instances[i];
			if( instance != _ins ) {
				_ins.hide();
			}
		}
	}
} );

base.UI.DatePicker2.prototype = {
	// 初期化処理
	initialize : function(element, target, trigger) {
		// スタティックメンバにアクセスするため、コンストラクタ自身を参照
		var p = base.UI.DatePicker2;
		
		// クラス初期化を試みる
		p.__initialize();
		
		// 内部要素
		this._elements = {};
		// イベントハンドラコンテナ
		this._events = {};
		
		// イベント種別ごとにリストを準備
		$H( p.eventNames ).keys().each( function(key) {
			this._events[ p.eventNames[key] ] = [];
		}.bind( this ) );
		
		// showメソッドとhideメソッドは自身にバインドしておく
		// → htmlイベントからキックされてもthis参照を保持するため
		this.show = this.show.bind( this );
		this.hide = this.hide.bind( this );
		
		// HTMLの構築 → 連動コントロールの設定 → ポップアップトリガの設定
		this.build( element ).hide().setTarget( target ).setTrigger( trigger );
		
		// 自分のコントロール以外がクリックされたら隠れるように
		// document.clickにイベントをアタッチ
		Event.observe( document, "click", function() { this.hide(); }.bind( this ), false );
		
		// オプション引数の処理
		var options = arguments[3] || {};
		var eventNames = $H( p.eventNames ).keys();
		for(var key in options) {
			if( eventNames.include( key ) ) {
				this.addEvent( key, options[ key ] );
			}
		}
		
		if( options.date instanceof Date ) {
			options.date = new Date( options.date.getFullYear(), options.date.getMonth(), options.date.getDate() );
		}
		
		// 選択中セルをクリア
		this._current = null;
		
		// 日付設定処理
		p.addInstance( this.setDate( options.date ) );
	},
	
	// HTML要素を構築する
	build : function(element) {
		var p = base.UI.DatePicker2;
		
		// テンプレートクローン作成
		var node = p._elementTemplate.cloneNode( true );
		
		// 引数またはクローンノードをメイン要素にする
		this.element = $( element ) || node;
		if( ! this.element.id ) {
			// idが割り振られていない場合は引数をIDにするか自動生成
			this.element.id = typeof(element) == "string" ?
				element : "base_ui_datepicker_{0}".format( ( p.__instances.length ) + 1 );
		} else if( this.element != node ) {
			// 引数が指定要素なのでクローンノードの子ノードをかっぱぐ
			this.element.innerHTML = node.innerHTML;
			if( node.parentNode ) {
				node.parentNode.removeChild( node );
			}
		}
		
		new Element.ClassNames( this.element ).add( p.classNames.root );
		Object.extend( this.element.style, {
			zIndex : 999
		} );
		if( this.element.parentNode != document.body ) {
			document.body.appendChild( this.element );
		}

		new Element.ClassNames( this.element ).add( p.classNames.root );
		
		this._elements.header = document.getElementsByClassName( p.classNames.header, this.element )[0];
		this._elements.body = document.getElementsByClassName( p.classNames.body, this.element )[0];
		[ "prevYear", "prevMonth", "nextYear", "nextMonth", "titleCell" ].each( function(key) {
			this._elements[ key ] = document.getElementsByClassName( p.classNames[ key ], this._elements.header )[0];
		}.bind( this ) );
		
		var commandBar = document.getElementsByClassName( p.classNames.cmdBar, this.element )[0];
		var buttons = {};
		
		document.getElementsByClassName( p.classNames.cmdButton, commandBar ).each( function(cmd) {
			var className = new Element.ClassNames(cmd).find( function(c) {
				return c != p.classNames.cmdButton;
			} );
			if( ! className ) throw $continue;
			
			buttons[ className ] = cmd;
			Event.observe( cmd, "click", function(evt) {
				var a_day = 1000 * 60 * 60 * 24;
				var d = Date.parseExact( new Date().format("yyyy/MM/dd"), "yyyy/MM/dd" );
				if( className == "yesterday" ) {
					d = new Date( d.valueOf() - a_day );
				} else if( className == "tomorrow" ) {
					d = new Date( d.valueOf() + a_day );
				}
				this.setDate( d );
				Event.stop( evt );
				setTimeout( function() {
					this.hide();
				}.bind(this), 250 );
			}.bindAsEventListener( this ) );
		}.bind( this ) );
		
		this._elements.commandButtons = buttons;
		
		// 前の年へ
		Event.observe( this._elements.prevYear, "click", function(evt) {
			this.addDisplayValue( { year : -1 } );
			Event.stop( evt );
		}.bindAsEventListener( this ) );
		
		// 前の月へ
		Event.observe( this._elements.prevMonth, "click", function(evt) {
			this.addDisplayValue( { month : -1 } );
			Event.stop( evt );
		}.bindAsEventListener( this ) );
		
		// 次の年へ
		Event.observe( this._elements.nextYear, "click", function(evt) {
			this.addDisplayValue( { year : 1 } );
			Event.stop( evt );
		}.bindAsEventListener( this ) );
		
		// 次の月へ
		Event.observe( this._elements.nextMonth, "click", function(evt) {
			this.addDisplayValue( { month : 1 } );
			Event.stop( evt );
		}.bindAsEventListener( this ) );
		
		[ "prevYear", "prevMonth", "nextYear", "nextMonth" ].each( function(key) {
			// 年月変更ボタンのマウスオーバー
			Event.observe( this._elements[ key ], "mouseover", function(evt) {
				var cn = new Element.ClassNames( Event.element( evt ) );
				cn.add( p.classNames[ key ] + "_a" );
			}.bindAsEventListener( this ) );
			// 年月変更ボタンのマウスアウト
			Event.observe( this._elements[ key ], "mouseout", function(evt) {
				var cn = new Element.ClassNames( Event.element(evt) );
				cn.remove( p.classNames[ key ] + "_a" );
			}.bindAsEventListener( this ) );
		}.bind( this ) );
		
		// 日付セルのイベント設定
		$A( this._elements.body.getElementsByTagName( "tr" ) ).each( function(row, r) {
			if( r == 0 ) {
				r.className = p.classNames.headerRow;
			} else {
				r.className = p.classNames.weekRow;
				$A( row.getElementsByTagName("td") ).each( function(col) {
					// click
					Event.observe( col, "click", function(evt) {
						var elem = Event.element( evt );
						var names = new Element.ClassNames( elem );
						if( ! names.include( p.classNames.otherMonth ) ) {
							// 有効セルの場合は日付を設定
							this.setDate( elem.dateValue );
						}
					}.bindAsEventListener( this ) );
					
					// mouseover
					Event.observe( col, "mouseover", function(evt) {
						var names = new Element.ClassNames( Event.element( evt ) );
						if( ! names.include( p.classNames.otherMonth ) ) {
							// 有効セルの場合のみ'hover'追加
							names.add( "hover" );
						}
					}.bindAsEventListener( this ) );
					
					// mouseout
					Event.observe( col, "mouseout", function(evt) {
						// 無条件に'hover'削除
						new Element.ClassNames( Event.element( evt ) ).remove( "hover" );
					}.bindAsEventListener( this ) );
				}.bind( this ) );
			}
		}.bind( this ) );
		return this;
	},
	
	// カレンダー表示の更新
	refresh : function() {
		var p = base.UI.DatePicker2;
		var rows = document.getElementsByClassName( "week_row", this._elements.body );
		
		var d = this.getDisplayDate();
		var date = this.getDate();
		var params = {
			first : d.getFirstDate().getDay(),
			last : d.getLastDate().getDate(),
			day : 1
		};
		
		// 選択中セルをクリア
		this._current = null;
		
		for(var r = 0, rm = rows.length; r < rm; r++) {
			var row = rows[ r ];
			var cols = row.getElementsByTagName( "td" );
			for(var c = 0, cm = cols.length; c < cm; c++) {
				var col = cols[ c ];
				
				var values = {
					classes : [ p.classNames.otherMonth ],
					text : "&nbsp;",
					value : null
				};
				if( ( r < 1 && c < params.first ) || params.day > params.last ) {
					// 当月外
				} else {
					// 当月内
					values.classes = [ p.classNames.cell ];
					switch( c ) {
					case 0:
						values.classes.push( p.classNames.holiday );
						break;
					case 6:
						values.classes.push( p.classNames.saturday );
						break;
					}
					values.text = params.day;
					values.value = new Date( d.getFullYear(), d.getMonth(), params.day );
					if( date && values.value.valueOf() == date.valueOf() ) {
						values.classes.push( [ p.classNames.selected ] );
					}
					params.day++;
				}
				Object.extend( col, {
					className : values.classes.join(" "),
					innerHTML : values.text,
					dateValue : values.value
				} );
			}
		}
		return this;
	},
	
	// 連動コントロールを取得する
	getTarget : function() {
		return this.target;
	},
	// 連動コントロールを設定する
	setTarget : function(target) {
		this.target = $(target);
		return this;
	},
	
	// ポップアップトリガコントロールを取得する
	getTrigger : function() {
		return this.trigger;
	},
	// ポップアップトリガコントロールを設定する
	setTrigger : function(trigger) {
		if( this.trigger ) {
			// 過去のトリガからイベントを削除
			try {
				Event.stopObserving( this.trigger, "click", this.show, false );
			} catch(e) {
			}
		}
		
		// 再設定＋イベント設定
		this.trigger = trigger;
		Event.observe( this.trigger, "click", this.show, false );
	},
	
	// カレンダーをポップアップ表示する
	show : function(evt) {
		var target = this.getTarget();
		if( target && ! target.disabled ) {
			base.UI.DatePicker2.requirePopup( this );
			this.setPosition();
			
			// フィールドの値をカレンダーに反映させる
			this.parseTargetValue();
			
			Element.show( this.element );
		}
		if( evt ) {
			Event.stop( evt );
		}
		return this;
	},
	
	// カレンダーを隠す
	hide : function(evt) {
		Element.hide( this.element );
		if( evt ) {
			Event.stop( evt );
		}
		return this;
	},
	
	parseTargetValue : function() {
		var target = this.getTarget();
		if( ! target ) return;
		
		var v = ( this.target.value || this.target.value == "" ) ?
			this.target.value : this.target.innerHTML;
		
		if( ! v || v.length == 0 ) return;
		
		// 現在のフォーマット文字列による日付解析を試みる
		try {
			var d = Date.parseExact( v, this.format || "yyyy/MM/dd" );
			// 日付として取得できたので値を反映
			if( ! isNaN( d ) ) this.setDate( d );
		} catch(e) {
		}
	},
	
	setPosition : function() {
		var target = this.getTarget();
		if( target ) {
			var pos = Position.cumulativeOffset( target );
			Object.extend( this.element.style, {
				left : "{0}px".format( pos[0] ),
				top : "{0}px".format( pos[1] + ( target.clientHeight || target.offsetHeight || 15 ) )
			} );
		}
		return this;
	},
	
	// イベントハンドラを指定のイベントに追加する
	addEvent : function(eventName, handler) {
		var handlers = this._events[ eventName ];
		if( ! ( handlers instanceof Array ) || handlers.include( handler ) ) return this;
		handlers.push( handler );
		return this;
	},
	
	// 指定のイベントハンドラを指定のイベントハンドラリストから削除する
	// イベントハンドラは同じインスタンスを参照している場合のみ削除される
	removeEvent: function(eventName, handler) {
		var handlers = this._events[ eventName ];
		if( ! ( handlers instanceof Array ) ) return this;
		
		this._events[ eventName ] = handlers.findAll( function(h) {
			return h != handler;
		} ).compact();
		
		return this;
	},
	
	// 指定のイベントを発行する
	_fireEvent : function(eventName) {
		var params = $A( arguments );
		eventName = params.shift();
		
		var handlers = this._events[ eventName ];
		if( ! ( handlers instanceof Array ) ) return false;
		var result = true;
		var error = null;
		handlers.each( function(handler) {
			try {
				var flag = handler.apply( null, params );
				if( flag === false ) {
					result = false;
					throw $break;
				}
			} catch(e) {
				error = e;
				result = false;
				throw $break;
			}
		} );
		if( error ) throw error;
		return result;
	},
	
	// 日付を取得する
	getDate : function() {
		return this.date;
	},
	
	// 日付を設定する
	setDate : function(date) {
		var p = base.UI.DatePicker2;
		if( date instanceof Date ) {
			// 時刻をクリア
			date = new Date( date.getFullYear(), date.getMonth(), date.getDate() );
			
			// 変更後に月が異なる場合
			var changeMonth = ( ! this.date ) ||
				( this.date.getMonth() != date.getMonth() ) ||
				( this.date.getFullYear() != date.getFullYear() );
			
			// 日付の変更がキャンセルされるか
			var cancel = ( changeMonth ) ?
				( this._fireEvent( p.eventNames.beforemonthchanged, date, this.date ) === false ) :
				false;
			
			// 変更がキャンセルされないので変更処理
			if( ! cancel ) {
				// 日付値の変更
				var prevDate = this.date;
				this.date = date;
				
				// 表示更新
				this.refresh();
				this._elements.titleCell.innerHTML = this.date.format( "yyyy年 M月" );
				
				// 月が変更されたらonMonthChangedを発行
				if( changeMonth ) {
					this._fireEvent( p.eventNames.monthchanged, this.date, prevDate );
				}
				
				// 日付選択イベントを発行
				this._fireEvent( p.eventNames.change, this.date, prevDate );
				
				// ターゲット要素が設定されていたら日付値を出力
				if( this.target ) {
					if( this.target.value || this.target.value == "" ) {
						this.target.value = this.date.format( this.format || "yyyy/MM/dd" );
					} else {
						this.target.innerHTML = this.date.format( this.format || "yyyy/MM/dd" );
					}
				}
			}
		}
		
		return this.setDisplayDate();
	},
	
	// 現在の表示月を取得する。
	// 戻り値は現在コントロールで表示している年月の月初を示す
	getDisplayDate : function() {
		var c = new Date();
		return this.displayDate || this.getDate() || new Date( c.getFullYera(), c.getMonth(), 1 );
	},
	
	// 表示月を設定する。
	// 設定値は常に対象年月の月初に置換される
	setDisplayDate : function(date) {
		if( isNaN(date) ) date = this.getDate() || new Date();
		this.displayDate = new Date( date.getFullYear(), date.getMonth(), 1 );
		this._elements.titleCell.innerHTML = this.displayDate.format( "yyyy年 M月" );
		return this.refresh();
	},
	
	// 現在選択日に指定のパラメータを加える
	// パラメータはキー'year'または'month'と増減値のペアで指定する。
	// 例： params = { year : 1 } → 現在選択日を1年後に設定
	addValue : function(params) {
		var d = this.getDate();
		if( ! isNaN( d ) ) {
			$H( params ).keys().each( function(key) {
				var val = parseInt( params[key] );
				if( isNaN( val ) ) throw $continue;
				switch( key ) {
				case "year":
					d = new Date( d.getFullYear() + val, d.getMonth(), d.getDate() );
					break;
				case "month":
					d = new Date( d.getFullYear(), d.getMonth() + val, d.getDate() );
					break;
				case "date":
					d = new Date( d.getFullYear(), d.getMonth(), d.getDate() + val );
					break;
				}
			} );
		}
		return this.setDate( d );
	},
	
	// 表示月に指定のパラメータを加える。
	// パラメータはキー'year'または'month'と増減値のペアで指定する。
	// 例： params = { year : 1 } → 表示月を現在の1年後に設定
	addDisplayValue : function(params) {
		var d = this.getDisplayDate();
		$H( params ).keys().each( function(key) {
			var val = parseInt( params[key] );
			if( isNaN( val ) ) throw $continue;
			switch( key ) {
			case "year":
				d = new Date( d.getFullYear() + val, d.getMonth(), d.getDate() );
				break;
			case "month":
				d = new Date( d.getFullYear(), d.getMonth() + val, d.getDate() );
				break;
			}
		} );
		return this.setDisplayDate( d );
	}
}
