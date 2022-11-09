if( typeof( Json ) == "undefined" ) {
	var Json = {
	}
}

/**
 * 任意のオブジェクトを可読性の高い改行・インデント付のJSON形式の文字列に変換するクラス。
 * コンストラクタに任意のオブジェクトを渡して初期化した後に{@link #value}プロパティへアクセスすると
 * JSON形式の文字列を取得できる
 * @class
 * @constructor
 * @param obj グラフ化する任意のオブジェクト
 */
Json.Formatter = Class.create();
Json.Formatter.prototype = {
	/**
	 * コンストラクタパラメータのオブジェクトが変換されたJSON形式の文字列を取得する
	 * @type String
	 */
	value : false,
	
	// コンストラクタ処理
	initialize : function(obj) {
		var indent_char = "\t";
		var name = arguments[1] ? arguments[1].toString().toLiteral() : null;
		var indent = isNaN( arguments[2] ) ? 0 : Number( arguments[2] );
		
		var current_indent = Json.Formatter.createString( indent_char, indent );
		var buffer = [ current_indent ];
		
		if( name ) {
			buffer.push( name );
			buffer.push( " : " );
		}
		
		var type = typeof( obj );
		if( type == "undefined" ) {
			// undefined
			buffer.push( "null" );
			
		} else if( obj == null ) {
			// null
			buffer.push( "null" );
			
		} else if( type == "string" ) {
			// string
			buffer.push( obj.toLiteral() );
			
		} else if( type == "number" ) {
			// number
			buffer.push( obj.toString() );
			
		} else if( type == "boolean" ) {
			// boolean
			buffer.push( ( !! obj ).toString() );
			
		} else if( obj instanceof Array ) {
			// array
			var hasProp = obj.length > 0;
			var lastIndex = obj.length - 1;
			
			buffer.push( "[" );
			buffer.push( hasProp ? "\r\n" : "" );
			for(var index = 0; index < obj.length; index++) {
				buffer.push( new Json.Formatter( obj[ index ], null, indent + 1 ).value );
				buffer.push( index < lastIndex ? "," : "" );
				buffer.push( "\r\n" );
			}
			buffer.push( hasProp ? current_indent : "" );
			buffer.push( "]" );
			
		} else {
			// object
			var hasProp = false;
			for(var key in obj) {
				if( typeof(obj) != "function" ) {
					hasProp = true;
					break;
				}
			}
			
			buffer.push( "{" );
			buffer.push( hasProp ? "\r\n" : "" );
			for(key in obj) {
				if( typeof(obj[key]) == "function" ) continue;
				buffer.push( new Json.Formatter( obj[key], key, indent + 1 ).value );
				buffer.push( ",\r\n" );
			}
			if( hasProp ) buffer.length--;
			
			buffer.push( hasProp ? "\r\n" : "" );
			buffer.push( hasProp ? current_indent : "" );
			buffer.push( "}" );
			
		}
		
		// valueプロパティ確定
		this.value = buffer.join("");
		
		this.toString = function() {
			return this.value;
		}.bind( this );
	}
}
Json.Formatter.createString = function(c, l) {
	var result = new Array( l );
	for(var i = 0; i < l; i++) result[ i ] = c;
	return result.join("");
}