// カスタムドロップダウンコントロール
base.UI.CustomList = Class.create();

// スタティックメンバの追加
Object.extend( base.UI.CustomList, {
} );

base.UI.CustomList.prototype = {
    onchange : Prototype.emptyFunction,
    onshow : Prototype.emptyFunction,
    onhide : Prototype.emptyFunction,
    /**
     * @constructor
     *
     * base.UI.CustomerListの新しいインスタンスを初期化する
     *
     * @param String|HTMLElement target ドロップダウンを所有する要素のIDまたは要素自体
     * @param null|Array list リスト項目
     * @param null|String itemAlign リストの文字寄せ指定。'left'または'right'を指定する
     * @param null|Number visibleItemsCount 表示するリスト項目の上限数
     */
    initialize : function(target, trigger, list, itemAlign, visibleItemsCount) {
        "getSelectedIndex;setSelectedIndex;show;hide".split(";").each( function(prop) {
            this[prop] = this[prop].bind( this );
        }.bind( this ) );
        
        this._element = this._createElement();
        document.body.appendChild( this._element );
        this._list = [];
        
        // メイン要素のイベント設定
        var _self = this;
        Event.observe( this._element, "click", function(evt) {
            var element = Event.element(evt);
            var index = -1;
            var item = document.getElementsByClassName( "list_item", this ).find( function(el, idx) {
                if( el === element ) {
                    index = idx;
                    return true;
                }
                return false;
            } );
            if( element ) {
                // まずはイベントを停止
                Event.stop( evt );
                
                // 現在の選択を先に解除
                var current = _self._getItemElement( _self.getSelectedIndex() );
                if( current ) new Element.ClassNames(current).remove("selected");
                
                // delayedで点滅させる
                var count = 0, cn = new Element.ClassNames(element);
                setTimeout( function() {
                    cn[ count++ % 2 == 0 ? "add" : "remove" ]("selected");
                    if( count < 4 ) {
                        setTimeout( arguments.callee, 75 );
                        return;
                    }
                    // 点滅終了
                    _self.setSelectedIndex( index );
                }, 50 );
            }
        }.bindAsEventListener( this._element ) );
        
		var last_hovered = null;
		Event.observe( this._element, "mouseover", function(evt) {
			var element = Event.element(evt);
			var cn = new Element.ClassNames( element );
			if( ! cn.include( "list_item" ) ) return;
			if( last_hovered && last_hovered != element ) new Element.ClassNames(last_hovered).remove("hover");
			last_hovered = element;
			cn.add("hover");
		}.bindAsEventListener( this._element ) );
        // 連動ターゲットとポップアップトリガーを設定
        this.setTarget( target ).setTrigger( trigger );
        
        // 文字寄せと表示項目数を設定
        this.setItemAlign( itemAlign || "left" );
        this.setVisibleItemsCount( visibleItemsCount );
        
        // リストの初期選択実行
        this._selectedIndex = -1;
        this.setList( list );
        
        
    },
    // 連動対象の要素を取得・設定する
    getTarget : function() {
        return this._target;
    },
    setTarget : function(target) {
        this._target = $(target);
        return this;
    },
    // ポップアップトリガー要素を取得・設定する。トリガー要素のclickイベントで自動的にポップアップする
    getTrigger : function() {
        
    },
    setTrigger : function(trigger) {
        if( this._trigger ) {
            // 以前のトリガーからイベントを削除
            try {
                Event.stopObserving( this._trigger, "click", this.show, false );
            } catch(e) {
            }
        }
        
        // 再設定＋イベント設定
        this._trigger = trigger || this.getTarget();
        if( this._trigger ) Event.observe( this._trigger, "click", this.show, false );
        
        return this;
    },
    // リスト項目を取得・設定する
    getList : function() {
        return this._list;
    },
    setList : function(list) {
        if( list == null ) list = [];
        if( typeof list.each != "function" ) {
            list = [ list ];
        }
        this._list = list.map( function(item) {
            if( item.text && item.value ) return item;
            return { text : String(item), value : String(item) };
        } );
        
        this._buildList();
    },
    // 項目の文字寄せを取得・設定
    getItemAlign : function() {
        return this._itemAlign;
    },
    setItemAlign : function(align) {
        align = /^((left)|(right))$/i.test(align) ? align : "left";
        this._itemAlign = align;
        this._element.style.textAlign = this._itemAlign;
        
        return this;
    },
    // 最大表示項目数を取得・設定する
    getVisibleItemsCount : function() {
        return this._visibleItemsCount;
    },
    setVisibleItemsCount : function(count) {
        count = isNaN(count) ? 5 : Number(count);
        if( count < 5 ) count = 5;
        this._visibleItemsCount = count;
        
        this._buildList();
        
        return this;
    },
    // ドロップダウンリストを表示
    show : function() {
        if( ! Element.visible( this._element) ) {
            this._setPosition();
            Element.show( this._element );
            if( this._element.clientHeight < this._element.scrollHeight ) {
                var item = this._getItemElement( this.getSelectedIndex() );
                this._element.scrollTop = item ? item.offsetTop : 0;
            }
            // このコントロール以外がクリックされたら閉じるようdocument.onclickにイベントを仕掛ける
            Event.observe( document, "click", this.hide );
        }
        
        ( this.onshow || Prototype.K )( this );
        
        if( arguments[0] ) {
            try {
                Event.stop( arguments[0] );
            } catch(e) {}
        }
		
		// Firefox3で幅が適切にならないことへの対応( 08.7.4 ）
		var target = this._element;
		var getWidth = function() {
			return base.UI.isOpera() ? target.offsetWidth : target.clientWidth;
		}
		if( getWidth() < target.scrollWidth ) {
			target.style.width = "{0}px".format( target.scrollWidth );
			setTimeout( function() {
				if( Element.visible(target) && getWidth() < target.scrollWidth ) {
					target.style.width = "{0}px".format( parseInt(target.style.width) + 5 );
					setTimeout( arguments.callee, 0 );
				}
			}, 0 );
		}
        return this;
    },
    // ドロップダウンリストを非表示
    hide : function() {
        if( Element.visible( this._element ) ) {
            Element.hide( this._element );
            // document.onclickを解除
            Event.stopObserving( document, "click", this.hide );
        }
        
        ( this.onhide || Prototype.K )( this );
        
        return this;
    },
    // ドロップダウンリストの表示をトグル切り替え
    toggle : function() {
        return this[ Element.visible( this._element) ? "hide" : "show" ]();
    },
    // 選択中項目のインデックス番号を取得・設定
    getSelectedIndex : function() {
        return this._selectedIndex;
    },
    setSelectedIndex : function(index, noChangeDisplay) {
        index = isNaN(index) ? -1 : parseInt(index);
        if( index < -1 ) index = -1;
        if( index > this._list.length - 1 ) index = this._list.length - 1;
        
        var item = this._getItemElement( this._selectedIndex );
        if( item ) new Element.ClassNames(item).remove( "selected" );
        this._selectedIndex = index;
        item = this._getItemElement( this._selectedIndex );
        if( item ) {
            new Element.ClassNames(item).add( "selected" );
            if(
               ( item.offsetTop < this._element.scrollTop ) ||
               ( item.offsetTop + item.clientHeight > this._element.scrollTop + this._element.clientHeight )
            ) {
                // 選択項目が表示範囲外の場合は補正する
                this._element.scrollTop = item.offsetTop;
            }
        }
        
        ( this.onchange || Prototype.emptyFunction )( this._list[ this._selectedIndex ], this._selectedIndex, this );
        if( ! noChangeDisplay ) setTimeout( this.hide, 0 );
        return this;
    },
    // 指定の値に一致する項目を選択する
    selectByValue : function(value) {
        var index = -1;
        var target = this._list.find( function(item) {
            var result = item.value == value;
            if( result ) index = arguments[1];
            return result;
        } );
        this.setSelectedIndex( index, true );
        return index;
    },
    // リストコンテナ要素を作成する
    _createElement : function() {
        var el = Object.extend( document.createElement("div"), {
            className : "base_ui_customlist"
        } );
        Object.extend( el.style, {
            width : base.UI.currentBrowser() == base.UI.browserTypes.ie ? "0px" : "auto",
            overflow : "auto",
            padding : "0px",
            margin : "0px",
            position : "absolute",
            zIndex : 999,
            display : "none",
            border : "solid 1px silver",
            color : "black",
            backgroundColor : "white"
        } );
        // IE6のみの処理
        if( base.UI.currentBrowser() == base.UI.browserTypes.ie ) {
            Object.extend( el.style, {
                overflow : "",
                overflowX : "visible",
                overflowY : "auto"
            } );
            // 背景にIFRAMEを配置してselect要素などの上にかぶさるように補正
            var ifr = Object.extend( document.createElement("iframe"), {
                frameborder : 0,
                src : "/blank.html"
            } );
            Object.extend( ifr.style, {
                position : "absolute",
                left : "0px", top : "0px",
                width : "100%", height : "100%",
                filter : "alpha(opacity=0)"
            });
            el.insertBefore( ifr, el.firstChild );
        }
        return el;
    },
    // リストの表示要素を更新する
    _buildList : function() {
        this._element.innerHTML = "";
        this._list.each( function(item, index) {
            var el = Object.extend( document.createElement("div"), {
                className : "list_item",
                innerHTML : item.text || item.value || index
            } );
            var bt = base.UI.currentBrowser();
            Object.extend( el.style, {
                border : "none 0px",
                padding: ( base.UI.currentBrowser() == base.UI.browserTypes.ie || base.UI.isMozilla() ) ?
                    "1px 2px" : "1px 18px 1px 2px",
                margin : "0px",
                fontSize : "9pt",
                height : "18px",
                display : "block",
                cursor : "default",
                whiteSpace : "nowrap"
            } );
            this._element.appendChild( el );
        }.bind( this ) );
        
        this._element.style.height = this._list.length > this._visibleItemsCount ?
            "{0}px".format( this._visibleItemsCount * 20 ) : "auto";
    },
    // 指定のインデックス位置にある項目の要素を取得
    _getItemElement : function(index) {
        return document.getElementsByClassName( "list_item", this._element ).find( function(el, idx) {
            return idx == index;
        } );
    },
    // ドロップダウンリストの表示位置を調整
    _setPosition : function() {
        var target = this.getTarget();
        if( target ) {
            var pos = Position.cumulativeOffset( target );
            Object.extend( this._element.style, {
                left : "{0}px".format( pos[0] ),
                top : "{0}px".format( pos[1] + (
					( target.clientHeight >= 0 ? target.clientHeight : 0 ) ||
					( target.offsetHeight >= 0 ? target.offsetHeight : 0 ) ||
					15
				) )
            });
        }
    }
}

