/**
 * メインメニューおよびサブメニューを構築する
 */

/**
 * サブメニュークラス
 */
var SubMenu = Class.create();
SubMenu.prototype = {
	initialize : function(element, subMenu) {
		this.element = $(element);
		
		var _self = this;
		Event.observe( this.element, "mouseover", function(evt) {
			if( ! Element.visible( _self.element.subMenu ) ) {
				_self.showSubMenu();
			}
		}.bindAsEventListener( this.element ) );
		
		Event.observe( this.element, "mouseout", function(evt) {
			if( Element.visible( _self.element.subMenu ) ) {
				_self.hideSubMenu();
			}
		}.bindAsEventListener( this.element ) );
		
		this.buildSubMenu(subMenu);
	},
	
	buildSubMenu : function(subMenu) {
		this.element.subMenu = Object.extend( subMenu, {
			_onCursor : false,
			showMenu : function() {
				Element.show( this );
			},
			hideMenu : function() {
				if( this._onCursor ) return;
				Element.hide( this );
			}
		} );
		
		Event.observe( this.element.subMenu, "mouseover", function(evt) {
			this._onCursor = true;
			this.showMenu();
		}.bindAsEventListener( this.element.subMenu ) );
		
		Event.observe( this.element.subMenu, "mouseout", function(evt) {
			this._onCursor = false;
			
			setTimeout( this.hideMenu.bind( this ), 500 );
		}.bindAsEventListener( this.element.subMenu ) );
		
		this.element.subMenu.hideMenu();
	},
	
	showSubMenu : function() {
		var pos = Position.cumulativeOffset( this.element );
		
		var subMenu = this.element.subMenu;
		Object.extend( subMenu.style, {
			left : "{0}px".format( pos[0] ),
			top : "{0}px".format( pos[1] + this.element.clientHeight + 1 ),
			minWidth: "{0}px".format( this.element.clientWidth + 20 )
		} );
		
		
		this.element.subMenu.showMenu();
	},
	
	hideSubMenu : function() {
		var _self = this;
		setTimeout( function() {
			_self.element.subMenu.hideMenu();
		}, 1000 );
	}
}

// メインメニューのイベント
document.getElementsByClassName("menu_title_container", $("header_menu")).each( function(menu) {
	Event.observe( menu, "mouseover", function(evt) {
		new Element.ClassNames( this ).add( "hover" );
	}.bindAsEventListener( menu ) );
	
	Event.observe( menu, "mouseout", function(evt) {
		new Element.ClassNames( this ).remove( "hover" );
	}.bindAsEventListener( menu ) );
	
	Event.observe( menu, "click", function(evt) {
		var link = this.getElementsByTagName("a")[0];
		if( link.href ) {
			if( link.target ) {
				window.open( link.href, link.target );
			} else {
				window.location.href = link.href;
			}
			Event.stop( evt );
		}
	}.bindAsEventListener( menu ) );
} );


document.getElementsByClassName("has_submenu").each( function(menu) {
	var subMenu = $("_{0}".format( menu.id ));
	document.body.appendChild( subMenu );
	new SubMenu( menu, $( "_{0}".format( menu.id ) ) );
} );
