<script>
var TabControl = Object.extend( Class.create(), {
	__tabPrototype : {
		_page : null,
		setHandler : function(handler) {
			this.eventHandler = handler;
			return this;
		},
		bindPage : function(element) {
			this._page = element;
			return this;
		},
		show : function() {
			Element.show( this._page );
			this.eventHandler( this );
			new Element.ClassNames( this ).add( "selected" );
			try {
				if( base.UI.isIE() ) bytefx.fade( this, 99, 100 );
			} catch(e) {
				alert(e);
			}
			return this;
		},
		hide : function() {
			Element.hide( this._page );
			var element = this;
			new Element.ClassNames( this ).remove( "selected" );
			return this;
		}
	}
} );
TabControl.prototype = {
	initialize : function(element, item_class, callback) {
		this.element = $(element);
		this._item_class = item_class || "tab_item";

		this.tabChangeHandler = function(tab) {
			if( tab == this.activeTab ) return;
			if( this.activeTab ) this.activeTab.hide();
			this.activeTab = tab;
			var index = -1;
			for(var i = 0, l = this.tabs.length; i < l; i++) {
				if(this.tabs[i] == this.activeTab) {
					index = i;
					break;
				}
			}
			(callback || Prototype.K)(this.activeTab, index);
		}.bind( this );

		var _self = this;
		this.tabs = document.getElementsByClassName( this._item_class, this.element ).map( function(li) {
			Object.extend( li, TabControl.__tabPrototype );
			li.bindPage( $( "{0}_page".format( li.id ) ) )
				.setHandler( _self.tabChangeHandler );

			var link = li.getElementsByTagName("a")[0];

			Event.observe( link, "click", function(evt) {
				li.show();
			}.bindAsEventListener( link ) );

			Event.observe( li, "click", function(evt) {
				this.show();
			}.bindAsEventListener( li ) );

			return li;
		} );
	}
}
</script>
