<?php
//--------------------------------
// HTMLドキュメントの共通ヘッダ
use member\Application;

$app_global = Application::getInstance()->appGlobalConfig;
if( isset( $app_global['page_title_prefix'] ) ) {
	$page_title_prefix = $app_global['page_title_prefix'] . ( ( isset( $app_global['page_title_separaotor'] )) ?
		$app_global['page_title_separator'] : ' : ' );
} else {
	$page_title_prefix = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja-JP">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta name="robots" content="noindex" />
	<base href="<?php echo $this->baseUrl; ?>/" />
	<title><?php echo $page_title_prefix . $this->pageTitle; ?></title>
<?php foreach( $this->styleSheets as $path ) { ?>
	<link rel="styleSheet" href="<?php echo $path; ?>" />
<?php } ?>
	<script>
	// acceleration for IE ( via: http://d.hatena.ne.jp/amachang/20071010/1192012056 )
	// fixed for ie9 by eda in 2012-10-12.
	/*@cc_on
	if(!( ('performance' in window)||('msPerformance' in window) )) {
		eval( ( function(props) {
			var code = [];
			for ( var i = 0, l = props.length; i<l; i++ ){
				var prop = props[i];
				window['_'+prop]=window[prop];
				code.push(prop+'=_'+prop)
			}
			return 'var '+code.join(',');
		})('document self top parent alert setInterval clearInterval setTimeout clearTimeout'.split(' ')));
	}
	@*/
	</script>
<?php foreach( $this->javaScripts as $path ) { ?>
	<script src="<?php echo $path; ?>"></script>
<?php } ?>
	<script>
	function field_focus() {
		if( ! this.disabled && ! this.readonly ) {
			var classNames = new Element.ClassNames( this );
			classNames.add( "focused" );
			classNames.remove( "unfocused" );
			field_focus.currentElement = this;
		}
	}
	function field_blur() {
		if( ! this.disabled && ! this.readonly ) {
			var classNames = new Element.ClassNames( this );
			classNames.add( "unfocused" );
			classNames.remove( "focused" );

			if( field_focus.currentElement == this ) field_focus.currentElement = null;
		}
	}
	Event.observe( window, "load", function() {
		// 入力項目の初期フォーカス制御
		var inputs = $A( document.getElementsByTagName("input") ).findAll( function(input) {
			return ! "checkbox;radio;file;hidden".split(";").include( input.type || "text" );
		} )
		.concat( $A( document.getElementsByTagName("textarea") ) );
		//.concat( $A( document.getElementsByTagName("select") ) );	// 10.5.27 disabled for IE7/8's bug.

		if( inputs.length < 3 && $("quick_search_key") ) {
			$("quick_search_key").disabled = false;
			$("quick_search_submit").disabled = false;
		}

		inputs.each( function(input) {
			var classNames = new Element.ClassNames( input );

			if( classNames.include( "debug_parts" ) ) throw $continue;

			Event.observe( input, "focus", field_focus.bind( input ) );
			Event.observe( input, "blur", field_blur.bind( input ) );

			classNames.add( "unfocused" );
			if( input.disabled ) classNames.add( "disabled" );
		} );

		setTimeout( function() {
			inputs.each( function(input) {
				if( new Element.ClassNames( input ).include( "debug_parts" ) ) throw $continue;
				if( ! input.disabled && ! input.readonly && Element.visible( input ) ) {
					try {
						input.focus();
					} catch(e) {}
					throw $break;
				}
			} );

			if( $("quick_search_key") ) {
				$("quick_search_key").disabled = false;
				$("quick_search_submit").disabled = false;
				new Element.ClassNames($("quick_search_key")).remove("disabled");
			}
		}, 0 );

		// サブミットボタンの有効化
		$A( document.getElementsByTagName("button") ).each( function(button) {
			if( button.type == "submit" ) button.disabled = false;
		} );
		$A( document.getElementsByTagName("input") ).each( function(input) {
			if( input.type == "submit" ) input.disabled = false;
		} );
	} );
	</script>
