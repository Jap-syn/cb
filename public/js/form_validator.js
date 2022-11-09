base.FormValidator = Class.create();
Object.extend( base.FormValidator, {
	EVENT_PRE_VALIDATION : "preValidate",
	EVENT_VALIDATION : "validate",
	EVENT_POST_VALIDATION : "postValidate"
} );

base.FormValidator.prototype = {
	initialize : function() {
		var ctor = base.FormValidator;

		this._validators = [];
		this._events = {};
		[
			ctor.EVENT_PRE_VALIDATION,
			ctor.EVENT_VALIDATION,
			ctor.EVENT_POST_VALIDATION
		].each( function(eventName) {
			// イベントハンドラリストの初期化
			this._events[ eventName ] = [];

			// イベントハンドラ関連メソッドの追加
			var eventKey = "{0}{1}".format(
				eventName.substr( 0, 1 ).toUpperCase(),
				eventName.substr( 1 )
			);
			this[ "add" + eventKey ] = function(handler) {
				return this.addEvent( eventName, handler );
			}.bind( this );
			this[ "remove" + eventKey ] = function(handler) {
				return this.removeEvent( eventName, handler );
			}.bind( this );
			this[ "fire" + eventKey ] = function(args) {
				return this.fireEvent( eventName, args );
			}.bind( this );
		}.bind( this ) );
	},
	addValidation : function(element, rule) {
		if (!rule) { return this; }	// ruleが無効な時は、以降の処理を行わない(20150413_1450)
		rule = ( rule instanceof RegExp ) ?
			rule : new RegExp( rule.toString() );

		var validator = {
			element : $( element ),
			rule : rule
		};
		var oldValidator = this._validators.find( function(item) {
			return item.element == validator.element;
		} );
		if( oldValidator != null ) {
			oldValidator.rule = validator.rule;
		} else {
			this._validators.push( validator );
		}

		return this;
	},
	addEvent : function(eventName, handler) {
		var events = this._events[ eventName ];
		if( ( events instanceof Array ) && ( handler instanceof Function ) ) {
			events.push( handler );
		}

		return this;
	},
	removeEvent : function(eventName, handler) {
		var events = this._events[ eventName ];
		if( ( events instanceof Array ) ) {
			var newEvents = [];
			for(var i = 0; i < events.length; i++) {
				if( events[i] != handler ) newEvents.push( events[i] );
			}
			this._events[ eventName ] = newEvents;
		}

		return this;
	},
	fireEvent : function(eventName, args) {
		var result = null;
		var events = this._events[ eventName ];
		if( events instanceof Array ) {
			var _self = this;
			events.each( function(evt) {
				var evt_result = evt.call( _self, args );
				if( evt_result === false ) result = false;
			} );
		}
		if( result === false ) return false;
	},
	validate : function() {
		this.firePreValidate( null );

		var results = this._validators.map( function(v) {
			try {
				var el = v.element;
				var value = el.value || el.text || el.innerHTML;
				var result = el.disabled ? true : v.rule.test( value );
				var event_result = this.fireValidate( { element : el, value : value, result : result } );
				if( event_result === false ) {
					return false;
				}
				return result;
			} catch(e) {
				alert( e );
				return false;
			}
		}.bind( this ) ).findAll( function(result) {
			return ! result;
		}.bind( this ) );

		this.firePostValidate( null );

		return results.length == 0;
	}
}
