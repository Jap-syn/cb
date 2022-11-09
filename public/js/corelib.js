var base = {
	/**
	 * VersionInfo プロパティ
	 * ライブラリに関する情報を保持するメンバ
	 */ 
	VersionInfo : {
		/**
		 * バージョンを取得する
		 * @return バージョンを示す文字列
		 * @type String
		 */
		version : "0.5.c",
		
		/**
		 * ライブラリの最終更新日を取得する
		 * @return ライブラリの最終更新日時
		 * @type Date
		 */
		lastUpdate : new Date( "2007/08/07" ),
		
		/**
		 * バージョンと更新日時を示す文字列を返す。
		 * {@link Object#toString}のオーバーライドメソッド。
		 * @return ライブラリバージョンと更新日時を示す文字列
		 * @type String
		 */
		toString : function() { return "version " + this.version + " ( " + this.lastUpdate.format("yyyy/MM/dd") + ")"; }
	},
	
	/**
	 * メソッドパラメータが不正であることを通知する{@link Error}オブジェクトを作成する
	 * methodHintにオブジェクト名またはメソッド名などのヒント情報を追加するとメッセージに反映される
	 * @param methodHint 例外発生元のメソッドまたは操作を示すオブジェクト
	 * @return パラメータが不正であることを通知する内容を含む{@link Error}オブジェクト
	 * @type Error
	 */
	invalidParameterError : function(methodHint) {
		var hint = ( methodHint == null ) ? "" : methodHint.toString();
		var msg = "invalid parameter";
		if( hint.length > 0 ) msg += " on method '" + hint + "'";
		var err = new Error( msg );
		err.name = "InvalidParameterError";
		return err;
	}
	
}
/**
 * Windowsファイル名規則に適合しない文字を検出する正規表現
 * @member base
 * @return Windowsファイル名規則に適合しない文字を検出する正規表現
 * @type RegExp
 */
base.invalidFileNameChars = /[\\\/:,;\*\?"<>\|]/g;
//"

/**
 * Microsoft.JScriptの{@link Error#toString}をNetscape互換にするためのオーバーライドメソッド。
 * この拡張により、{@link Error#toString}が例外の名前と例外メッセージを含んだ文字列を返すようになる
 */
Error.prototype.toString = function() {
	return this.name + ": " + this.message;
}

/**
 * cで指定した文字または文字列をlen回連結した文字列を返す。
 * このメソッドは{@link String}クラスの静的な拡張である。
 * @param {String} c 繰り返す文字または文字列
 * @param {Number} len 繰り返す回数。1以下を指定した場合は0回と見なされる
 * @return cをlen回連結した文字列
 * @type String
 * @member String
 */
String.create = function(c, len) {
	var result = new Array();
	for(var i = 0; i < len; i++) result.push( c.toString() );
	return result.join( "" );
}

/**
 * 書式指定と可変パラメータを組み合わせて文字列をフォーマットする。<br>
 * 書式指定のプレースホルダは{n[:format]}のように指定する。nは0から連番で指定する。<br>
 * 例えば、 String.format("{0:yyyy-MM-dd}, {1}", new Date(2006, 6, 20), 1000) とした場合、<br>
 * 結果は"2006-07-20, 1000"となる。
 * @param {String} format パラメータ位置とコロンで接続された書式指定文字列を組み合わせたプレースホルダで、<br>
 * 書式指定文字列は省略可能。'{0}'、'{0:yyyy/MM/dd}'の要領で指定する。<br>
 * パラメータ位置に一致するパラメータが存在しない場合は例外がスローされる。例えば、String.format("{2}", obj)は例外になる。
 * @param arguments[1]～ プレースホルダにマップされる任意のオブジェクト。<br>
 * formatメソッドが実装されたオブジェクト（{@link Number}、{@link Date}）の場合は、対応するプレースホルダの書式指定が適用される。
 * @return formatと指定されたパラメータによりフォーマットされた文字列
 * @type String
 * @member String
 */
String.format = function(format) {
	var args = createArgList.apply( this, arguments );
	
	var reg = /\{((\d)|([1-9]\d+))(:((([^\}])|(\}\}))+))?\}/g;
	return format.replace( reg, function() {
		var idx = Number( arguments[1] );
		if( typeof( args[ idx ] ) == "undefined" ) throw new Error("パラメータが一致しない。 -> " + idx);
		
		var val = args[ idx ];
		var format = arguments[ 5 ];
		
		if( typeof( val.format ) != "function" || format == undefined ) {
			return val.toString();
		} else {
			return val.format( format );
		}
	} );
	
	// パラメータリスト作成関数
	function createArgList() {
		var result = [];
		for(var i = 1; i < arguments.length; i++) result.push( arguments[i] );
		return result;
	}
}
// Stringインスタンスの拡張
Object.extend( String.prototype, {
	/**
	 * インスタンス文字列の左側の空白を除去して返す。
	 * このメソッドは非破壊的メソッドで、元のインスタンスの内容は変更されない
	 * @return このインスタンスの左側の空白を除去した文字列
	 * @type String
	 */
	trimLeft : function() {
		return this.toString().replace( /^[\s　]+/, "" );
	},

	/**
	 * インスタンス文字列の右側の空白を除去して返す
	 * このメソッドは非破壊的メソッドで、元のインスタンスの内容は変更されない
	 * @return このインスタンスの右側の空白を除去した文字列
	 * @type String
	 */
	trimRight : function() {
		return this.toString().replace( /[\s　]+$/, "" );
	},

	/**
	 * インスタンス文字列の左右の空白を除去した文字列を返す
	 * このメソッドは非破壊的メソッドで、元のインスタンスの内容は変更されない
	 * @return このインスタンスの左右の空白を除去した文字列
	 * @type String
	 */
	trim : function() {
		return this.trimLeft().trimRight();
	},

	/**
	 * インスタンス文字列を文字列リテラル表記に変換する。<br>
	 * 例えば、インスタンスが「abc」の場合、このメソッドは
	 * 「"abc"」を返す
	 * @return このインスタンスの文字列リテラル表記
	 * @type String
	 */
	toLiteral : function() {
		var s = this;
		[
			{ match : /\\/g, replace : "\\\\" },
			{ match : /\r/g, replace : "\\r" },
			{ match : /\n/g, replace : "\\n" },
			{ match : /\f/g, replace : "\\f" },
			{ match : /\t/g, replace : "\\t" },
			{ match : /"/g, replace : "\\\"" }
		].each(function(value) {
			s = s.replace( value.match, value.replace );
		} );
		return "\"" + s + "\"";
	},
	
	/**
	 * インスタンス文字列が表現するHTMLフラグメントをHTML要素に変換する
	 * @return HTML要素
	 * extends String
	 */
	toElement : function() {
		// 基本はDIV要素を作成し、その中にフラグメントを展開し、取り出したフラグメントを返す
		if( ! document || ! document.createElement ) return null;
		var base = document.createElement("DIV");
		if( ! base ) return null;
		
		// prototype.jsが読み込まれている場合は、スクリプトタグを除去する
		base.innerHTML = this.stripScripts ? this.stripScripts() : this;
		return base.childNodes[0] || null;
	},
	
	/**
	 * インスタンスを書式指定として可変パラメータをフォーマットする。
	 * {@link String::format}へのショートカットメソッド
	 */
	format : function() {
		return String.format.apply( String, [ this ].concat( $A( arguments ) ) );
	}
} );

/**
 * 指定の文字列を、指定のフォーマット情報を使用して日付時刻として解析する
 * フォーマット文字列はこのライブラリによりオーバーライドされた{@link Date#toString}で
 * 指定可能な文字列と同じ形式である。例えば、Date.parseExact("06/01/01", "yy/MM/dd")のように使用する。
 * @param {String} s 解析する文字列
 * @param {String} format 書式指定文字列
 * @return sをformatの書式で解釈した結果のDate値
 * @type Date
 * @member Date
 */
Date.parseExact = function(s, format) {
	// 必ずStringに変換
	var value = s.toString();
	
	//書式指定子の構築
	var paramNames = [ "d", "y", "M", "H", "m", "s", "f" ];
	var params = {
		d : { reg : /d{1,2}/i, value : 1, index : -1, length : -1 },
		y : { reg : /y{1,4}/i, value : 0, index : -1, length : -1, correct : function(n) {
			if( this.length == 4 || n > 999 ) return n;
			if( n > 69 && n < 100 ) return n + 1900;
			return n + 2000;
		} },
		M : { reg : /M{1,2}/,  value : 0, index : -1, length : -1, correct : function(n) { return n - 1; } },
		H : { reg : /H{1,2}/i, value : 0, index : -1, length : -1 },
		m : { reg : /m{1,2}/,  value : 0, index : -1, length : -1 },
		s : { reg : /s{1,2}/i, value : 0, index : -1, length : -1 },
		f : { reg : /f{1,3}/i, value : 0, index : -1, length : -1 }
	};
	
	// 書式指定子と指定フォーマットから正規表現に展開
	var regs = format;
	for(var i = 0; i < paramNames.length; i++) {
		var token = params[ paramNames[i] ];
		if( token.reg.test( format ) ) {
			var r = token.reg.exec( format );
			token.index = r.index;
			token.length = r.lastIndex - r.index;
		}
		regs = regs.replace( token.reg, function(s) {
			if( s.length == 1 ) return "(\\d+)";
			
			return "(\\d{" + s.length + "})";
		} );
	};
	
	// valueがformatと一致しないので例外をスロー
	var reg = new RegExp( regs );
	if( ! reg.test( value ) ) throw new Error("値がフォーマットと一致しません。");
	
	// 正規表現にマッチさせる
	var match = reg.exec( value );
	// 書式指定子と正規表現のマッチ結果をマッピングさせるため、一致位置でソートした配列を作成
	var filters = [];
	for(var i = 0; i < paramNames.length; i++) {
		filters.push( params[ paramNames[i] ] );
	};
	filters.sort( function(a, b) {
		if( a.index == b.index ) return 0;
		return a.index < b.index ? -1 : 1;
	} );
	
	// ソート順序から、正規表現のマッチ結果とマッピングし、
	// 対応するサブ文字列から数値を取得する
	var pos = 0; // サブ文字列のインデックス。+1でサブ文字列と対応できる
	for(var i = 0; i < filters.length; i++) {
		var token = filters[i];
		if( token.index > -1 ) {
			var num = Number( match[ pos + 1 ] );
			if( token.correct ) num = token.correct( num );
			token.value = num;
			pos++;
		} else {
		}
	};
	
	// 書式指定子と値のマッピングが完了したので、結果のDateを構築
	var result = new Date(
		params.y.value, params.M.value, params.d.value,
		params.H.value, params.m.value, params.s.value );
	
	// ミリ秒を追加
	result.setMilliseconds( params.f.value );
	
	return result;
}

/**
 * オーバーライドされた{@link Date#toString}で暗黙的に使用されるフォーマット文字列。
 * 設定可能な値は、{@link Date#toString}のパラメータで指定可能な書式指定文字列となる。
 * @type String
 * @member Date
 */
Date.defaultFormat = undefined;

/**
 * {@link Date#toString}をオーバーライドするために、オリジナルのメソッドを退避する
 * オーバーライドされた{@link Date#toString}の内部から、条件によって呼び出される
 */
if( typeof( Date.prototype._toString ) == "undefined" ) {
	Date.prototype._toString = Date.prototype.toString;
}

/**
 * {@link Date#toString}に、書式指定パラメータを指定可能なように拡張。
 * 書式指定パラメータは"y"（年）、"M"（月）、"d"（日）、"H"（時）、"m"（分）、
 * "s"（秒）、および"f"（ミリ秒）と任意の区切り文字列の組み合わせで、
 * 各パラメータ文字列の位置は任意である
 * @param {String} format 書式指定フォーマット文字列。例えば'yyyy/MM/dd'等。
 * @return このDateインスタンスに指定書式を反映した文字列
 * @type String
 */
Date.prototype.toString = function(format) {
	// フォーマットの初期値
	if( format == null ) format = "";
	
	// フォーマット指定がない場合はDateオブジェクトのプロパティからの取得を試みる
	if( format.length == 0 ) {
		if( Date.defaultFormat != null && Date.defaultFormat.length > 0 ) {
			format = Date.defaultFormat;
		}
	}
	
	// フォーマット未指定が確定したら、オリジナルのtoStringを実行
	if( format.length == 0 || format == "_default" ) return this._toString();
	
	// 書式指定子の長さを補正
	format = format.replace(/y{5,}/ig, "yyyy").replace(/M{3,}/g, "MM").replace(/d{3,}/ig, "dd");
	format = format.replace(/h{3,}/ig, "HH").replace(/m{3,}/g, "mm").replace(/s{3,}/ig, "ss");
	format = format.replace(/f{4,}/g, "fff");
	
	// 年
	var result = format.replace( /yyyy/ig, ( "0000" + this.getFullYear().toString() ).slice(-4) );
	result = result.replace( /yyy?/ig, ( "0000" + this.getFullYear().toString() ).slice(-2) );
	result = result.replace( /y/ig, this.getFullYear().toString() );
	// 月
	result = result.replace( /MM/g, ( "00" + ( this.getMonth() + 1 ).toString() ).slice(-2) );
	result = result.replace( /M/g, ( this.getMonth() + 1 ).toString() );
	// 日
	result = result.replace( /dd/ig, ( "00" + this.getDate().toString() ).slice(-2) );
	result = result.replace( /d/ig, this.getDate().toString() );
	// 時
	result = result.replace( /hh/ig, ( "00" + this.getHours().toString() ).slice(-2) );
	result = result.replace( /h/ig, this.getHours().toString() );
	// 分
	result = result.replace( /mm/g, ( "00" + this.getMinutes().toString() ).slice(-2) );
	result = result.replace( /m/g, this.getMinutes().toString() );
	// 秒
	result = result.replace( /ss/ig, ( "00" + this.getSeconds().toString() ).slice(-2) );
	result = result.replace( /s/ig, this.getSeconds().toString() );
	// ミリ秒
	result = result.replace( /fff/ig, ( "000" + this.getMilliseconds().toString() ).slice(-3) );
	result = result.replace( /ff/ig, ( ( "000" + this.getMilliseconds().toString() ).slice(-3) + "00" ).substr(0, 2) );
	result = result.replace( /f/ig, this.getMilliseconds().toString() );
	
	// 結果を返す
	return result;
}

/**
 * インスタンスの月部分を実際の月表記に合わせた数値を返す
 * <ul>例
 * <li>new {@link Date}("2006/10/01").getMonth(); // 9
 * <li>new {@link Date}("2006/10/01").{@link #getMonthValue}; // 10
 * </ul>
 * @return このインスタンスの月部分の、表記上の数値
 * @type Number
 */
Date.prototype.getMonthValue = function() {
	return this.getMonth() + 1;
}

/**
 * インスタンスの日付時刻値を、簡易書式指定して文字列に変換する、オーバーライドされた{@link #toString}へのショートカット。
 * @param {String} f 書式指定子を含む文字列。省略時は'yyyy/MM/dd HH:mm:ss'として扱われる
 * @return このインスタンスが示す日付時刻データに書式指定を適用した文字列
 * @type String
 */
Date.prototype.format = function(f) {
	// パラメータのクローンを作成して処理結果文字列にする
	var source = f;
	if( source == null ) {
		// nullやundefinedの場合は標準書式を用いる
		source = "yyyy/MM/dd HH:mm:ss";
		
	} else if( source.length == 0 ) {
		// 空文字の場合も標準書式を用いる
		source = "yyyy/MM/dd HH:mm:ss";
		
	} else if( source.constructor != String ) {
		// 文字列以外が指定されたら文字列に変換する
		source = source.toString();
		
	}
	
	return this.toString( source );
}

/**
 * インスタンスが示す数値を、3桁ごとにカンマで区切った文字列を返す
 * @return このインスタンスの数値に3桁ごとのカンマ区切りを適用した文字列
 * @type String
  */
Number.prototype.format = function(f) {
	var base = this.toString();
	if( f != "0" ) return base;
	var parts = base.split(".");
	
	var vect = parts[0].substr(0, 1) == "-" ? "-" : "";
	var s = parts[0].replace("-", "");
	var fract = parts.length > 1 ? "." + parts[1] : "";
	
	var result = new Array();
	while(s.length > 0) {
		if( s.length > 3 ) {
			result.push( s.slice(-3) );
			s = s.substr(0, s.length - 3);
		} else {
			result.push( s );
			s = "";
		}
	}
	result.reverse();
	return vect + result.join(",") + fract;
}


/**
 * RegExpのオプションフラグを示す文字列を整形する
 * @param {String} str オプションフラグを示す文字列
 * @return 整形されたオプションフラグ文字列。これには'g'、'i'、'm'以外の文字が含まれない
 * @type String
 * @member RegExp
 */
RegExp.toFlags = function(str) {
	return "{0}{1}{2}".format(
		str.indexOf("g") > -1 ? "g" : "",
		str.indexOf("i") > -1 ? "i" : "",
		str.indexOf("m") > -1 ? "m" : ""
	);
}

/**
 * 指定のオプションフラグ文字列から、3つのbool値を持つ
 * 無名オブジェクトに変換する。
 * @param {String} str オプションフラグ文字列
 * @return オプションフラグを示す無名オブジェクト。
 * このオブジェクトは'global'、'ignoreCase'、'multiline'の各boolプロパティを持ち、
 * toStringメソッドでオプションフラグ文字列に再変換可能
 * @member RegExp
 */
RegExp.parseFlags = function(str) {
	str = RegExp.toFlags( str );
	return {
		global : str.indexOf("g") > -1,
		ignoreCase : str.indexOf("i") > -1,
		multiline : str.indexOf("m") > -1,
		toString : function() {
			return "{0}{1}{2}".format(
				this.global ? "g" : "",
				this.ignoreCase ? "i" : "",
				this.multiline ? "m" : ""
			) ;
		}
	};
}

// RegExpインスタンスの拡張
Object.extend( RegExp.prototype, {
	/**
	 * この正規表現のオプションフラグ文字列を抽出する。
	 * 例えば、/\d+/g.getFlags() は 'g'を返す。
	 * @return この正規表現インスタンスのオプションフラグ文字列
	 * @type String
	 */
	getFlags : function() {
		var reg = this;
		return $H( RegExp.parseFlags("") ).keys().map( function(option) {
			return reg[option] ? option.substr(0, 1) : null;
		} ).compact().join("");
	},
	/**
	 * このインスタンスと同一パターンの、指定のオプションフラグで初期化された
	 * 新しいRegExpインスタンスを取得する非破壊メソッド。
	 * @param {String} flags 正規表現オプションフラグ文字列
	 * @return このインスタンスと同一のパターンを持ち、flagsで指定されたオプションを備えた
	 * 新しいRegExpインスタンス
	 * @type RegExp
	 */
	setFlags : function(flags) {
		return new RegExp( this.source, RegExp.toFlags( flags ) );
	},
	/**
	 * このインスタンスと同一のパターン・オプションに、指定のオプションフラグを追加した
	 * 新しいRegExpインスタンスを取得する非破壊メソッド。
	 * 例えば、/[a-z]/g.addFlags('i') は /[a-z]/ig を返す
	 * @param {String} flags 正規表現オプションフラグ文字列
	 * @return このインスタンスと同一のパターン・オプションに、flagsで指定された
	 * オプションを追加した新しいRegExpインスタンス
	 * @type RegExp
	 */
	addFlags : function(flags) {
		var newFlags = RegExp.parseFlags( flags );
		var curFlags = RegExp.parseFlags( this.getFlags() );
		$H( curFlags ).keys().each( function(key) {
			curFlags[ key ] = ( curFlags[ key ] | newFlags[ key ] ) ? true : false;
		} );
		
		return new RegExp( this.source, curFlags.toString() );
	},
	/**
	 * このインスタンスと同一のパターン・オプションに、指定のオプションフラグを
	 * 除外した新しいRegExpインスタンスを取得する非破壊メソッド。
	 * 例えば、/[a-z]/ig.removeFlags('i') は /[a-z]/g を返す。
	 * @param {String} flags 正規表現オプションフラグ文字列
	 * @return このインスタンスと同一のパターン・オプションに、flagsで指定された
	 * オプションを除外した新しいRegExpインスタンス
	 * @type RegExp
	 */
	removeFlags : function(flags) {
		var newFlags = RegExp.parseFlags( flags );
		var curFlags = RegExp.parseFlags( this.getFlags() );
		$H( curFlags ).keys().each( function(key) {
			curFlags[ key ] = ( curFlags[ key ] &~ newFlags[ key ] ) ? true : false;
		} );
		
		return new RegExp( this.source, curFlags.toString() );
	}
} );

/**
 * @class
 * URL情報を扱うクラス。.NET FrameworkのSystem.Uriクラスに酷似したメンバーを実装する
 * @constructor
 * @param {String} url URL文字列
 */
function Url(url) {
	/*
		正規表現実行後の配列定義 ※：()内はプロパティとして公開されない部分一致
			[0] = input
			[1] = scheme
			[2] = (userName:password)
			[3] = userName
			[4] = (:password)
			[5] = password
			[6] = (hostName:port)
			[7] = hostName
			[8] = (lastSegment of hostName)
			[9] = (:port)
			[10] = port
			[11] = path
			[12] = (path)
			[13] = (part of path)
			[14] = (?query)
			[15] = query
			[16] = (first part of query)
			[17] = (first value of query)
			[18] = (last part of query)
			[19] = (last value of query)
			[20] = fragment
	*/
	var regs = {
		mailto : /mailto:([\w-\.]+)@(([\w-]+)(\.[\w-]+))*/,
		normal : /(\w+):\/\/(([^\s\/@:]+)?(:([^\s\/@:]+))?@)?(([\w-_]+(\.[\w-_]+)*)(:(\d+)?)?)((\/([^\/\?#]+))*)(\/?\?(([^=&#]+(=[^\&#]+)?)(&[^&#]+(=[^&#]+)?)*))?(#.*)?/
	};
	
	/**
	 * このURLのプロトコルスキームを取得する
	 * @param {String} scheme プロトコルスキーム
	 * @return プロトコルスキーム
	 * @type String
	 */
	this.scheme = undefined;
	
	/**
	 * このURLのホスト名を取得する
	 * @param {String} host ホスト名
	 * @return ホスト名
	 * @type String
	 */
	this.host = undefined;
	
	/**
	 * このURLのポート番号を取得する
	 * @param {Number} port ポート番号
	 * @return ポート番号
	 * @type Number
	 */
	this.port = undefined;
	
	/**
	 * URLが示すファイルの名前を取得する
	 * @param {String} fileName ファイル名
	 * @return ファイル名
	 * @type String
	 */
	this.fileName = undefined;
	
	/**
	 * URLに含まれるクエリ文字列を取得する
	 * @param {String} queryString URLのクエリ部分
	 * @return URLのクエリ部分
	 * @type String
	 */
	this.queryString = undefined;
	
	/**
	 * URLのクエリ部分を示す{@link Query}オブジェクトを取得する
	 * @param {Query} query クエリオブジェクト
	 * @return クエリオブジェクト
	 * @type Query
	 */
	this.query = undefined;
	
	/**
	 * URLのファイル名に含まれるフラグメント情報を取得する。フラグメント情報とはHTMLのAnchorタグで#により指定される位置情報である
	 * @param {String} fragment
	 */
	this.fragment = undefined;
	
	/**
	 * URLの、フラグメント情報を含まない絶対パスを取得する
	 * @param {String} absolutePath フラグメント情報を含まない、URLの絶対パス
	 * @return フラグメント情報を含まない、URLの絶対パス
	 * @type String
	 */
	this.absolutePath = undefined;
	
	/**
	 * 絶対URIを取得する
	 * @param {String} absoluteUri このURLが示す絶対URI。コンストラクタパラメータに一致する
	 * @return このURLが示す絶対URI。コンストラクタパラメータに一致する
	 */
	this.absoluteUri = undefined;
	
	/**
	 * このURLのローカルパスを取得する
	 * @param {String} localPath このURLのローカルパス
	 * @return このURLのローカルパス
	 * @type String
	 */
	this.localPath = undefined;
	
	/**
	 * フラグメント情報を含まない、サーバ上の絶対リソースアドレスを取得する。
	 * このプロパティは{@link #absolutePath} + '?' + {@link #queryString}に一致する
	 * @param {String} pathAndQuery サーバ上の絶対リソースアドレス
	 * @return サーバ上の絶対リソースアドレス
	 */
	this.pathAndQuery = undefined;
	
	/**
	 * URLを構成するセグメント情報を取得する
	 * @param {Array} segments このURLを構成するセグメント情報を示す{@link Array}
	 * @return このURLを構成するセグメント情報を示す{@link Array}
	 * @type Array
	 */
	this.segments = undefined;
	
	/**
	 * URLに含まれるユーザ情報を取得する
	 * @param {UserInfo} userInfo このURLに含まれるユーザ情報
	 * @return このURLに含まれるユーザ情報
	 * @type UserInfo
	 */
	this.userInfo = undefined;
	
	var result = regs.mailto.exec( url );
	if( result != null ) {
		// mailtoプロトコルの場合
		this.scheme = "mailto";
		this.host = result[2];
		this.port = Url.welknownPorts[ "mailto" ];
		this.fileName = "";
		this.queryString = "";
		this.query = null;
		this.fragment = "";
		this.absolutePath = "";
		this.absoluteUri = url;
		this.localPath = "";
		this.pathAndQuery = "";
		this.segments = [];
		this.userInfo = new UserInfo( result[1], null );
	} else {
		// fileプロトコル時の事前処理
		if( url.trim().indexOf("file") > -1 ) {
			url = url.split("\\").join("/");
		}
		// エスケープする
		url = encodeURI( url );
		
		// mailto以外の場合は他にマッチするスキームがないかをチェック
		result = regs.normal.exec( url );
		if( result == null ) throw( new Error( 0, "parameter error" ) );
		
		// スキーム確定
		var defaultPort = Url.welknownPorts[ result[1] ];
		
		this.scheme = result[1];
		this.host = result[7];
		this.port = ! /\d+/.test( result[10] ) ? defaultPort : Number( result[10] );
		this.queryString = result[14] ? result[14] : "";
		if( this.queryString.substr( 0, 1 ) == "/" ) this.queryString = this.queryString.substr( 1 );
		this.query = this.queryString.length > 0 ? new Query( this.queryString ) : null;
		this.fragment = result[20] ? result[20] : "";
		this.absolutePath = createAbsolutePath( url, result[6], this.queryString );
		if( this.fragment.length > 0 ) this.absolutePath = this.absolutePath.split("#")[0];
		this.absoluteUri = url;
		this.localPath = this.absolutePath;
		this.pathAndQuery = this.absolutePath + this.queryString;
		this.segments = createSegments( this.absolutePath );
		this.fileName = this.segments[ this.segments.length - 1];
		if( this.fileName ) {
			if( ! /\w/.test( this.fileName.slice(-1) ) ) this.fileName = "";
		} else {
			this.fileName = "";
		}
		this.userInfo = new UserInfo( result[3], result[5] );
		
		// fileプロトコルの特別処理
		if( this.scheme == "file" ) {
			this.absolutePath = this.host + ":" + this.absolutePath;
			this.absoluteUri = this.scheme + ":///" + this.absolutePath;
			this.localPath = this.host + ":" + decodeURI( this.localPath );
			this.pathAndQuery = this.host + ":" + this.pathAndQuery;
			this.segments = [ this.host + ":" ].concat( this.segments );
			this.host = "";
		}
	}
	
	/**
	 * {@link #host}のタイプを示す文字列を取得する。このプロパティは'IP'、'DNS'または'BASIC'を返す
	 * @param {String} hostNameType hostNameのタイプを示す文字列
	 * @return hostNameのタイプを示す文字列
	 * @type String
	 */
	this.hostNameType = /\d{1,3}(\.\d{1,3}){3}/.test( this.host ) ? "IP" : "DNS";
	if( this.scheme == "file" ) this.hostNameType = "BASIC";
	
	/**
	 * URLに含まれるポート番号が{@link #scheme}のプロトコルの標準ポートであるかを示す値を取得する
	 * @param {Boolean} isDefaultPort {@link #port}が{@link #scheme}の標準ポートである場合はtrue、それ以外はfalse
	 * @return {@link #port}が{@link #scheme}の標準ポートである場合はtrue、それ以外はfalse
	 * @type Boolean
	 */
	this.isDefaultPort = this.port == Url.welknownPorts[ this.scheme ];
	
	/**
	 * URLがローカルファイルであるかを示す値を取得する
	 * @param {Boolean} isFile このURLがローカルファイルを示している場合はtrue、それ以外はfalse
	 * @return このURLがローカルファイルを示している場合はtrue、それ以外はfalse
	 * @type Boolean
	 */
	this.isFile = this.scheme == "file";
	
	/**
	 * このURLがローカルループバックアドレスかを示す値を取得する。
	 * {@link #host}が'localhost'または'127.0.0.1'の場合、このプロパティはtrueを示す
	 * @param {Boolean} isLoopback このURLがローカルループバックアドレスを示す場合はtrue、それ以外はfalse
	 * @return このURLがローカルループバックアドレスを示す場合はtrue、それ以外はfalse
	 * @type Boolean
	 */
	this.isLoopback = /(127\.0\.0\.1)|(localhost)|(loopback)/i.test( this.host );
	
	/**
	 * このURLがWindowsのUNCパスであるかを示す値を取得する。
	 * このプロパティは常にfalseを返す
	 * @param isUnc サポートされていない
	 * @return サポートされていない
	 */
	this.isUnc = false;
		
	/**
	 * このURL文字列がエスケープされているかを示す値を取得する
	 * このプロパティは常にfalseを返す
	 * @param userEscape サポートされていない
	 * @return サポートされていない
	 */
	this.userEscaped = false;
	
	/**
	 * URLのクエリ文字列を示すオブジェクト。名前をキーとして値にアクセスする連想配列として提供される
	 * @constructor
	 * @param {String} query URL文字列中のクエリ文字列
	 */
	function Query(query) {
		var arr = query.split("&");
		for(var i = 0; i < arr.length; i++) {
//			var part = arr[i].split("=");
			var item = arr[i];
			var part = item.indexOf("=") > 0 ?
				[ item.substr( 0, item.indexOf("=") ), item.substr( item.indexOf("=") + 1 ) ] :
				[ item ];
			var key = part[0].replace(/\?/g, "");
			this[ key ] = part.length > 1 ? part[1] : undefined;
		}
	}
	
	/**
	 * URLに関連付けられたユーザ情報を示すオブジェクト。
	 * ユーザIDとパスワードの情報をプロパティに持つ
	 * @constructor
	 * @param {String} userName ユーザ名
	 * @param {String] password パスワード
	 */
	function UserInfo(userName, password) {
		/**
		 * このユーザ情報のユーザ名を取得する
		 * @param {String} userName ユーザ名
		 * @return ユーザ名
		 * @type String
		 */
		this.userName = userName;
		
		/**
		 * このユーザ情報のパスワードを取得する
		 * @param {String} パスワード
		 * @return パスワード
		 * @type String
		 */
		this.password = password;
		
		/**
		 * このユーザ情報の内容を示す文字列を取得する。{@link Object#toString}のオーバーライドメソッド
		 * @return このインスタンスの内容を示す文字列
		 * @type String
		 */
		this.toString = function() {
			if( this.userName && this.password ) {
				return this.userName + ":" + this.password;
			} else if( this.userName ) {
				return this.userName;
			}
			return "";
		}
	}
	
	// URLの絶対パス情報を作成するヘルパメソッド
	function createAbsolutePath(baseUrl, authority, query) {
		var path = baseUrl.substr( baseUrl.indexOf( authority ) + authority.length );
		if( query.length == 0 ) return path;
		
		path = path.substr( 0, path.indexOf( query ) );
		return ( path.length == 0 ) ? "/" : path;
	}
	
	// URLのセグメントリストを作成するヘルパメソッド
	function createSegments(source) {
		var result = new Array();
		while(source.length > 0) {
			var pos = source.indexOf("/");
			if( pos < 0 ) break;
			result.push( source.substr( 0, pos + 1 ) );
			source = source.substr( pos + 1 );
		}
		if( source.length > 0 ) result.push( source );
		return result;
	}
	
}
/**
 * {@link Url#port}プロパティがサポートする、定義済みのプロトコルスキームの標準ポートを取得する
 * このプロパティはプロトコルスキーム名をキーとし、ポート番号を値として持つ連想配列を提供する
 * @member Url
 */
Url.welknownPorts = {
	http : 80,
	https : 443,
	mailto : 25,
	ftp : 21,
	file : -1
}

/**
 * {@link Url}オブジェクトをURL文字列に変換する。{@link Object#toString}のオーバーライド。
 * @return URL文字列
 * @type String
 */
Url.prototype.toString = function() {
	return this.absoluteUri;
}

/**
 * {@link #clone}メソッドによる自己コピーと、
 * {@link #equals}メソッドによる同値検査、および空であるかを検査する{@link #isEmpty}メソッドを
 * サポートするデータ型のインターフェイスオブジェクト。
 * {@link Point}クラス、{@link Size}クラス、および{@link Rectangle}クラスがこのインターフェイスを実装する。
 * {@link _ValueType}インターフェイスを実装するオブジェクトは、
 * 値として有効なプロパティ名の配列を返す_keysメソッドと、実際のクローニング処理を
 * 行う_cloneメソッドを実装する必要がある
 */
var _ValueType = {
	/**
	 * このオブジェクトのクローンを作成する
	 * @return このオブジェクトのクローンオブジェクト
	 */
	clone : function() {
		return this._clone();
	},
	
	/**
	 * このオブジェクトが空であるかを判断する。
	 * 空の状態とは、有効なプロパティの値がすべて0の場合を示す
	 * @return このオブジェクトの有効なプロパティがすべて0の場合はtrue、それ以外はfalse
	 * @type Boolean
	 */
	isEmpty : function() {
		var result = true;
		var keys = this._keys();
		for(var i = 0; i < keys.length; i++) {
			result = result && this[keys[i]] == 0;
		}
		return result;
	},
	
	/**
	 * 指定のオブジェクトの値がこのオブジェクトと等しいかを判断する。
	 * @param obj 比較するオブジェクト
	 * @return objがこのオブジェクトと同じ型で同じプロパティの値を持つ場合はtrue、それ以外はfalse
	 * @type Boolean
	 */
	equals : function(obj) {
		if( ! ( obj instanceof ( this.constructor ) ) ) return false;
		var keys = this._keys();
		var result = true;
		for(var i = 0; i < keys.length; i++) {
			result = result && this[ keys[i] ] == obj[ keys[i] ];
		}
		return result;
	},
	
	/**
	 * このオブジェクトの内容を示す文字列を返す。{@link Object#toString}のオーバーライド
	 * @return このオブジェクトの内容を表す文字列。{@link _ValueType}においては、JSON形式の文字列になる
	 * @type String
	 */
	toString : function() {
		var result = [ "{" ];
		var keys = this._keys();
		for(var i = 0; i < keys.length; i++) {
			result = result.concat( [ keys[i], ":", this[ keys[i] ] ] );
			if( i < keys.length - 1 ) result.push( "," );
		}
		result.push("}");
		return result.join(" ");
	}
}

/**
 * 座標を示す_ValueType実装クラス。{@link Size}オブジェクトとの相互変換をサポートする。
 * コンストラクタパラメータは原則X座標・Y座標の二つのNumberを要求するが、
 * {@link Size}オブジェクトを指定することもできる。この場合、第二パラメータは利用されない
 * @class
 * @constructor
 * @param x X座標（またはSizeオブジェクト）
 * @param y Y座標。xにSizeオブジェクトが指定された場合は使用されない
 */
var Point = Class.create();
Point.prototype = {
	/**
	 * Pointオブジェクトを初期化する
	 */
	initialize : function(x, y) {
		if( x instanceof Size ) {
			this.x = x.width;
			this.y = x.height;
		} else {
			this.x = isNaN( x ) ? 0 : Math.ceil( x );
			this.y = isNaN( y ) ? 0 : Math.ceil( y );
		}
		// Object.extendでは、なぜかprototypeに_ValueType.toStringがコピーされないので
		// インスタンスごとに手動でコピーする
		this.toString = _ValueType.toString;
	},
	
	/**
	 * この座標情報を、{@link Size}オブジェクトが示す大きさの分だけ平行移動する。
	 * 例えば{ x : 10, y : 10 }のPointオブジェクトにadd( new Size( 5, 5 ) )を
	 * 実行すると、元のPointオブジェクトは{ x : 15, y : 15 }になる
	 * @param {Size} size 加算する大きさの情報を保持するSizeオブジェクト
	 */
	add : function(size) {
		var pt = Point.add( this, size );
		this.x = pt.x;
		this.y = pt.y;
	},
	
	/**
	 * この座標情報を、{@link Size}オブジェクトが示す大きさの分だけ負の方向に平行移動する。
	 * 例えば{ x : 10, y : 10 }のPointオブジェクトにadd( new Size( 5, 5 ) )を
	 * 実行すると、元のPointオブジェクトは{ x : 15, y : 15 }になる
	 * @param {Size} size 加算する大きさの情報を保持するSizeオブジェクト
	 */
	subtract : function(size) {
		var pt = Point.subtract( this, size );
		this.x = pt.x;
		this.y = pt.y;
	},
	
	/**
	 * このオブジェクトの座標情報から{@link Size}オブジェクトを作成する。
	 * 作成される{@link Size}は、原点（x=0,y=0）を基準とした矩形の大きさを示す。
	 * @return この座標情報と原点からなる大きさを示すSizeオブジェクト
	 * @type Size
	 */
	toSize : function() {
		return new Size( this );
	},
	
	/**
	 * このPointオブジェクトのクローンオブジェクトを作成する
	 * @ignore
	 */
	_clone : function() {
		return new Point( this.x, this.y );
	},
	
	/**
	 * このオブジェクトの値として有効なプロパティ名の配列を返す。
	 * Pointオブジェクトの実装においては、["x","y"]を返す
	 * @ignore
	 */
	_keys : function() {
		return [ "x", "y" ];
	}
}
// _ValueTypeを継承する
Object.extend( Point.prototype, _ValueType );
/**
 * xおよびyプロパティを持つ任意のオブジェクトから{@link Point}オブジェクトを作成する
 * @member Point
 * @param obj 任意のオブジェクト。Number型のxおよびyプロパティが必要
 * @return objのプロパティを元に作成されたPoint
 * @type Point
 */
Point.fromObject = function(obj) {
	return new Point( obj["x"], obj["y"] );
};
/**
 * ある{@link Point}オブジェクトを、指定の{@link Size}オブジェクトの分だけ平行移動した結果の
 * {@link Point}オブジェクトを取得する
 * @member Point
 * @param {Point} pt 演算対象のPoint
 * @param {Size} sz 演算対象のSize
 * @return 演算結果のPoint
 * @type Point
 */
Point.add = function(pt, sz) {
	if( ! ( pt instanceof Point ) || ! ( sz instanceof Size ) ) throw new Error("型が一致しません");
	return new Point( pt.x + sz.width, pt.y + sz.height );
};
/**
 * ある{@link Point}オブジェクトを、指定の{@link Size}オブジェクトの分だけ負の方向に平行移動した結果の
 * {@link Point}オブジェクトを取得する
 * @member Point
 * @param {Point} pt 演算対象のPoint
 * @param {Size} sz 演算対象のSize
 * @return 演算結果のPoint
 * @type Point
 */
Point.subtract = function(pt, sz) {
	if( ! ( pt instanceof Point ) || ! ( sz instanceof Size ) ) throw new Error("型が一致しません");
	return new Point( pt.x - sz.width, pt.y - sz.height );
};

/**
 * 矩形の大きさ（＝幅と高さ）を表現する_ValueType実装クラス。
 * {@link Point}オブジェクトとの相互変換をサポートする
 * コンストラクタパラメータは原則 幅・高さの二つのNumberを要求するが、
 * {@link Point}オブジェクトを指定することもできる。この場合、第二パラメータは利用されない
 * @class
 * @constructor
 * @param width 矩形の幅（またはPointオブジェクト）
 * @param height 矩形の高さ。widthにPointオブジェクトが指定された場合は使用されない
 */
var Size = Class.create();
Size.prototype = {
	/**
	 * Sizeオブジェクトを初期化する
	 */
	initialize : function(width, height) {
		if( width instanceof Point ) {
			this.width = width.x;
			this.height = width.y;
		} else {
			this.width = isNaN( width ) ? 0 : Math.ceil( width );
			this.height = isNaN( height ) ? 0 : Math.ceil( height );
		}
		// Object.extendでは、なぜかprototypeに_ValueType.toStringがコピーされないので
		// インスタンスごとに手動でコピーする
		this.toString = _ValueType.toString;
	},
	
	/**
	 * 指定の{@link Size}オブジェクトの大きさをこのオブジェクトに加算する。
	 * 例えば、{ width : 100, height: 90 }のSizeオブジェクトに
	 * add( new Size( 50, 50 ) )を実行すると、元のSizeオブジェクトは
	 * { width : 150, height : 140 }になる
	 * @param {Size} size 加算するSizeオブジェクト
	 */
	add : function(size) {
		var sz = Size.add( this, size );
		this.width = sz.width;
		this.height = sz.height;
	},
	
	/**
	 * 指定の{@link Size}オブジェクトの大きさをこのオブジェクトから減算する。
	 * 例えば、{ width : 100, height : 90 }のSizeオブジェクトに
	 * subtract( new Size( 50, 50 ) ) を実行すると、元のSizeオブジェクトは
	 * { width : 50, height : 40 }になる
	 * @param {Size} size 減算するSizeオブジェクト
	 */
	subtract : function(size) {
		var sz = Size.subtract( this, size );
		this.width = sz.width;
		this.height = sz.height;
	},
	
	/**
	 * このオブジェクトの矩形サイズから{@link Point}オブジェクトを作成する。
	 * @return 原点（x=0,y=0）から、このオブジェクトが示す大きさ分だけ移動した位置を示すPoint
	 * @type Point
	 */
	toPoint : function() {
		return new Point( this );
	},
	
	/**
	 * このSizeオブジェクトのクローンオブジェクトを作成する
	 * @ignore
	 */
	_clone : function() {
		return new Size( this.width, this.height );
	},
	
	/**
	 * このオブジェクトの値として有効なプロパティ名の配列を返す。
	 * Sizeオブジェクトの実装においては、["width","height"]を返す
	 * @ignore
	 */
	_keys : function() {
		return [ "width", "height" ];
	}
}
// _ValeuTypeを継承する
Object.extend( Size.prototype, _ValueType );
/**
 * widthおよびheightプロパティを持つ任意のオブジェクトから{@link Size}オブジェクトを
 * 作成する
 * @member Size
 * @param obj 任意のオブジェクト。Number型のwidthおよびheightプロパティが必要
 * @return objのプロパティから作成されたSize
 * @type Size
 */
Size.fromObject = function(obj) {
	return new Size( obj["width"], obj["height"] );
};
/**
 * ある{@link Size}オブジェクトに、別の{@link Size}オブジェクトを加算した結果の
 * {@link Size}オブジェクトを返す
 * @member Size
 * @param {Size} sz1 演算に使用する一方のSize
 * @param {Size} sz2 演算に使用するもう一方のSize
 * @return 加算した結果のSize
 * @type Size
 */
Size.add = function(sz1, sz2) {
	if( ! ( sz1 instanceof Size ) || ! ( sz2 instanceof Size ) ) throw new Error("型が一致しません。");
	return Point.add( sz1.toPoint(), sz2 ).toSize();
};
/**
 * ある{@link Size}オブジェクトから、別の{@link Size}オブジェクトを減算した結果の
 * {@link Size}オブジェクトを返す
 * @member Size
 * @param {Size} sz1 演算に使用する一方のSize
 * @param {Size} sz2 演算に使用するもう一方のSize
 * @return 減算した結果のSize
 * @type Size
 */
Size.subtract = function(sz1, sz2) {
	if( ! ( sz1 instanceof Size ) || ! ( sz2 instanceof Size ) ) throw new Error("型が一致しません。");
	return Point.subtract( sz1.toPoint(), sz2 ).toSize();
};

/**
 * 位置を示す基準座標と大きさを持つ矩形領域を表現するオブジェクトで、
 * _ValueTypeを継承する。
 * @class
 * @constructor
 * @param x 基準座標のX座標、または位置を示す{@link Point}オブジェクト
 * @param y 基準座標のY座標、または大きさを示す{@link Size}オブジェクト
 * @param width 矩形の幅。xおよびyにPointとSizeが指定された場合は無視される
 * @param height 矩形の高さ。xおよびyにPointとSizeが指定された場合は無視される
 */
var Rectangle = Class.create();
Rectangle.prototype = {
	/**
	 * Rectangleオブジェクトを初期化する
	 */
	initialize : function(x, y, width, height) {
		var pt, sz;
		if( ( x instanceof Point ) && ( y instanceof Size ) ) {
			pt = x;
			sz = y;
		} else {
			pt = new Point( x, y );
			sz = new Size( width, height );
		}
		this.x = pt.x;
		this.y = pt.y;
		this.width = sz.width;
		this.height = sz.height;
		
		this.toString = _ValueType.toString;
	},
	/**
	 * この矩形領域の左上隅の座標を取得する
	 * @return この矩形領域の基準座標
	 * @type Point
	 */
	getPoint : function() {
		return new Point( this.x, this.y );
	},
	/**
	 * この矩形領域の基準座標を示す{@link Point}を設定して移動させる
	 * @param {Point} point 移動先の位置を示すPoint
	 */
	setPoint : function(point) {
		if( ! ( point instanceof Point ) ) return;
		this.x = point.x;
		this.y = point.y;
	},
	/**
	 * この矩形領域の大きさを取得する
	 * @return この矩形領域の大きさ
	 * @type Size
	 */
	getSize : function() {
		return new Size( this.width, this.height );
	},
	/**
	 * この矩形領域の大きさを、指定の{@link Size}に設定する
	 * @param {Size} size 新しい大きさを示すSize
	 */
	setSize : function(size) {
		if( ! ( size instanceof Size ) ) return;
		this.width = size.width;
		this.height = size.height;
	},
	/**
	 * この矩形領域の左端位置を取得する。
	 * {@link #x}プロパティに一致する
	 * @return このオブジェクトの左座標
	 * @type Number
	 */
	getLeft : function() {
		return this.x;
	},
	/**
	 * この矩形領域の上端位置を取得する。
	 * {@link y}プロパティに一致する
	 * @return このオブジェクトの上座標
	 * @type Number
	 */
	getTop : function() {
		return this.y;
	},
	/**
	 * この矩形領域の右端位置を取得する
	 * @return このオブジェクトの右端位置
	 * @type Number
	 */
	getRight : function() {
		return this.x + this.width;
	},
	/**
	 * この矩形領域の下端位置を取得する
	 * @return このオブジェクトの下端位置
	 * @type Number
	 */
	getBottom : function() {
		return this.y + this.height;
	},
	/**
	 * 矩形領域を指定の{@link Point}分だけ平行移動する
	 * @param {Point} point 移動先の位置を示すPoint
	 */
	moveTo : function(point) {
		if( ! ( point instanceof Point ) ) return;
		this.x = point.x;
		this.y = point.y;
	},
	/**
	 * 矩形領域の位置を{@link Size}の大きさ分だけ平行移動する
	 * @param {Size} size 移動量を示すSize
	 * @param {Boolean} minus マイナス移動する場合はtrueを指定する。省略時はfalseとして扱われる
	 */
	moveBy : function(size, minus) {
		if( ! ( size instanceof Size ) ) return;
		
		if( arguments.length < 2 ) minus = false;
		minus = Type.convertTo("boolean", minus);
		
		var pt = minus ?
			Point.subtract( this.getPoint(), size ) :
			Point.add( this.getPoint(), size );
		
		this.x = pt.x;
		this.y = pt.y;
	},
	/**
	 * この矩形を、指定の{@link Rectangle}との交差部分に置き換える。
	 * 交差部分がない場合、このインスタンスの{@link #isEmpty}メソッドはtrueを返すようになる
	 * @param {Rectangle} rect 交差部分を作成するもう一方のRectangle
	 */
	intersect : function(rect) {
		var result = Rectangle.intersect( this, rect );
		this.x = result.x;
		this.y = result.y;
		this.width = result.width;
		this.height = result.height;
	},
	/**
	 * 指定の{@link Rectangle}が、この矩形と交差部分を作り出すかを判断する。
	 * @param {Rectangle} rect テストするRectangle
	 * @return このオブジェクトとrectの交差部分が存在する場合はtrue、それ以外はfalse
	 * @type Boolean
	 */
	intersectsWith : function(rect) {
		return ! Rectangle.intersect( this, rect ).isEmpty();
	},
	/**
	 * 指定の{@link Rectangle}、または{@link Point}がこの矩形領域内に完全に内包されるかを判断する。<br>
	 * 例えば、{ x : 0, y : 0, width : 10, height : 10 }のRectangleに対し、{ x : 5, y : 5, width: 5, height: 5 }の
	 * Rectangleは内包されるが、{ x : 5, y : 5, width : 6, height : 5 }の矩形は右端が元の矩形からはみ出るため、内包されない。
	 * @param val 検査するRectangle、またはPoint
	 * @return valがこの矩形領域内に完全に内包される場合はtrue、それ以外はfalse
	 */
	contains : function(val) {
		if( val instanceof Rectangle ) {
			return this.contains( val.getPoint() ) && this.contains( new Point( val.getRight(), val.getBottom() ) );
		} else if( val instanceof Point ) {
			return val.x >= this.x && val.y >= this.y && val.x <= this.getRight() && val.y <= this.getBottom();
		} else {
			return false;
		}
	},
	/**
	 * この矩形を、指定した{@link Size}分だけ膨らませる。
	 * 例えば、{ x : 10, y : 10, width : 5, height : 5 }の矩形に
	 * { width: 5, height : 5 }を指定した場合、結果は{ x : 5, y : 5, width : 10, height : 10 }となり、
	 * 元の矩形と幾何学的中心座標が変化せずに基準位置と大きさが変化する。変化する大きさの量は、
	 * 横方向・縦方向ともにsizeの2倍になる。
	 * @param {Size} size 増分を示すSize
	 */
	inflate : function(size) {
		if( ! ( size instanceof Size ) ) return;
		this.x -= size.width;
		this.y -= size.height;
		this.width += size.width * 2;
		this.height += size.height * 2;
	},
	/**
	 * このオブジェクトのクローンを作成する
	 * @ignore
	 */
	_clone : function() {
		return new Rectangle( this.getPoint(), this.getSize() );
	},
	/**
	 * このオブジェクトの値として有効なプロパティ名の配列を返す。
	 * Rectangleオブジェクトの実装においては、["x","y","width","height"]を返す
	 * @ignore
	 */
	_keys : function() {
		return [ "x", "y", "width", "height" ];
	}
}
// _ValueTypeを継承する
Object.extend( Rectangle.prototype, _ValueType );
/**
 * x、y、widthおよびheightプロパティを持つ任意のオブジェクトから
 * {@link Rectangle}オブジェクトを作成する。
 *
 */
Rectangle.fromObject = function(obj) {
	if( obj == null ) return Rectangle.getEmpty();
	return new Rectangle( Point.fromObject(obj), Size.fromObject(obj) );
};
/**
 * 空の{@link Rectangle}オブジェクト（位置が[ 0, 0 ]で、大きさが[ 0, 0 ]）を作成する。
 * @member Rectangle
 * @return 位置・大きさすべてが0を示す、空のRectangle
 * @type Rectangle
 */
Rectangle.getEmpty = function() {
	return new Rectangle( 0, 0, 0, 0 );
};
/**
 * 上下左右の座標位置から形成される{@link Rectangle}オブジェクトを作成する。
 * @member Rectangle
 * @param {Number} left 左位置
 * @param {Number} top 上位置
 * @param {Number} right 右位置
 * @param {Number} bottom 下位置
 * @return パラメータで示される座標を頂点とするRectangle
 * @type Rectangle
 */
Rectangle.fromLTRB = function(left, top, right, bottom) {
	var pt = new Point( left, top );
	var sz = new Size( right - left, bottom - top );
	return new Rectangle( pt, sz );
};
/**
 * 2つの{@link Rectangle}の和集合を表す新しい{@link Rectangle}を作成する
 * @member Rectangle
 * @param {Rectangle} rect1 集合要素のRectangleの1つ
 * @param {Rectangle} rect2 もう一方の集合要素のRectangle
 * @return パラメータから合成されたRectangle
 * @type Rectangle
 */
Rectangle.union = function(rect1, rect2) {
	if( ! ( rect1 instanceof Rectangle ) || ! ( rect2 instanceof Rectangle ) ) return null;
	var lt1 = {
		l : rect1.getLeft(),
		t : rect1.getTop(),
		r : rect1.getRight(),
		b : rect1.getBottom()
	};
	var lt2 = {
		l : rect2.getLeft(),
		t : rect2.getTop(),
		r : rect2.getRight(),
		b : rect2.getBottom()
	};
	// 最小の左端・上端と最大の右端・下端で構成される矩形を返す
	return Rectangle.fromLTRB(
		lt1.l < lt2.l ? lt1.l : lt2.l,
		lt1.t < lt2.t ? lt1.t : lt2.t,
		lt1.r > lt2.r ? lt1.r : lt2.r,
		lt1.b > lt2.b ? lt1.b : lt2.b );
}
/**
 * 2つの{@link Rectangle}の交差部分を示す新しい{@link Rectangle}を作成する。<br>
 * rect1とrect2で交差を形成できない場合は空のRectangleを返す。
 * @param {Rectangle} rect1 交差部分を形成するRectangleの1つ
 * @param {Rectangle} rect2 交差部分を形成するもう一方のRectangle
 * @return パラメータの交差部分を示すRectangle
 * @type Rectangle
 */
Rectangle.intersect = function(rect1, rect2) {
	if( ! ( rect1 instanceof Rectangle ) || ! ( rect2 instanceof Rectangle ) ) return null;
	var lt1 = {
		l : rect1.getLeft(),
		t : rect1.getTop(),
		r : rect1.getRight(),
		b : rect1.getBottom()
	};
	var lt2 = {
		l : rect2.getLeft(),
		t : rect2.getTop(),
		r : rect2.getRight(),
		b : rect2.getBottom()
	};
	
	// 最大の左端・上端と最小の右端・下端の矩形を作成
	var result = Rectangle.fromLTRB(
		lt1.l > lt2.l ? lt1.l : lt2.l,
		lt1.t > lt2.t ? lt1.t : lt2.t,
		lt1.r < lt2.r ? lt1.r : lt2.r,
		lt1.b < lt2.b ? lt1.b : lt2.b );
	
	// 大きさがマイナスになる場合は交差部分が存在しないので空のRectangleを返す
	if( result.width < 0 || result.height < 0 ) return Rectangle.getEmpty();
	
	// 結果を返す
	return result;
};
/**
 * 指定の{@link Rectangle}を、指定の{@link Size}分だけ膨らませた{@link Rectangle}を
 * 作成する。例えば、{ x : 10, y : 10, width : 5, height : 5 }の矩形に
 * { width: 5, height : 5 }を指定した場合、結果は{ x : 5, y : 5, width : 10, height : 10 }となり、
 * 元の矩形と幾何学的中心座標が変化せずに基準位置と大きさが変化する。変化する大きさの量は、
 * 横方向・縦方向ともにsizeの2倍になる。
 * このメソッドは元のRectangleに影響を与えない
 * @param {Rectangle} rect 基準のRectangle
 * @param {Size} size 縦・横の増分を示すSize
 * @return rectをsizeで増減した位置・大きさのRectangle
 * @type Rectangle
 */
Rectangle.inflate = function(rect, size) {
	var result = rect.clone();
	result.inflate( size );
	return result;
}

// （JScript環境向け）EnumeratorオブジェクトをEnumerable対応に
if( typeof( Enumerator ) == "function" ) {
	Enumerator.prototype._each = function(iterator) {
		this.moveFirst();
		for(; ! this.atEnd(); this.moveNext()) {
			iterator( this.item() );
		}
	}
	Object.extend( Enumerator.prototype, Enumerable );
}

// 英数字と記号の全角-半角を相互変換するメソッドをString.prototypeに追加
with( {
	wideChars : "　！”＃＄％＆’（）＝～｜ー－＾￥＠‘「」｛｝＋＊／・＿；：、。．＜＞？０１２３４５６７８９ａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ".split(""),
	narrowChars : " !\"#$%&'()=~|--^\\@`[]{}+*//_;:,..<>?0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""),
	i : 0,
	wideMap : {},
	narrowMap : {}
} ) {
	for(i = 0, l = wideChars.length; i < l; i++) {
		wideMap[ wideChars[i] ] = narrowChars[i];
		narrowMap[ narrowChars[i] ] = wideChars[i];
	}
	String.prototype.toWideChar = function() {
		return this.replace( /./g, function(s) {
			return narrowMap[s] || s;
		} );
	};
	String.prototype.toNarrowChar = function() {
		return this.replace( /./g, function(s) {
			return wideMap[s] || s;
		} );
	};
}

// base.Utility ユーティリティクラス
base.Utility = {
	transPostalCode : function(s) {
		s = ( s || "" )
			.toNarrowChar()
			.trim()
			.replace( /[^\d]/g, "" )
			.substr(0, 7);
		if( s.length < 4 ) return s;
		return "{0}-{1}".format( s.substr(0, 3), s.substr(3) );
	},
	transDateString : function(s, glue) {
		glue = glue || "/";
		s = ( s || "" )
			.toNarrowChar()
			.trim()
			.replace( /[-.]/g, "/")
			.replace( /[^\d\/]/g, "" )
			.split("/");

		if( s.length < 3 && s.length > 1 ) {
			s.unshift( new Date().getFullYear() );
		}

		s = s.map( function(part, i) {
			if( ! /^\d+$/.test( part ) ) return part;
			var v = Number(part);
			if( i == 0 && v < 100 ) v += ( v < 90 ? 2000 : 1900 );
			return i == 0 ? ( "0000" + v ).slice(-4) : ( "00" + v ).slice(-2);
		} ).join(glue);

		try {
			return Date.parseExact( s, "yyyy/MM/dd" ).format( "yyyy/MM/dd" );
		} catch(e) {
			return s;
		}
	}
}

// document.getElementsByClassName のパフォーマンス改善版
document.getElementsByClassName2 = document.getElementsByClassName;
document.getElementsByClassName = function(className, parentElement) {
  var children = ($(parentElement) || document.body).getElementsByTagName('*');
  var result = [];
  for(var i = 0, l = children.length; i < l; i++) {
	var child = children[i];
    if (child.className.match(new RegExp("(^|\\s)" + className + "(\\s|$)")))
		result[ result.length ] = child;
  }
  return result;
}