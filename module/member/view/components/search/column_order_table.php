<div id="column_order_editor" style="display: none">
	<div class="help">
		<ul>
			<li>項目をドラッグドロップ操作して、検索結果項目の並び順を編集できます</li>
			<li>「非表示項目」に並んだ項目は検索結果に表示されなくなります</li>
		</ul>
	</div>
	<div class="container_area">
		<div id="visible_column_container" class="column_container">
			<div class="title">表示する項目</div>
		</div>
		<div class="dummy"></div>
		<div id="invisible_column_container" class="column_container">
			<div class="title">非表示項目</div>
		</div>
		<div class="dialog_button_container">
			<button type="button" id="columnEditOkButton">OK</button>
			<button type="button" id="columnEditCancelButton">キャンセル</button>&nbsp;
			<button type="button" id="columnEditResetButton">初期値に戻す</button>
		</div>
	</div>
</div>
<script>
function applyColumnSetting() {
	// chromeでif-modified-sinceヘッダ付与が許容されなくなったのでタイムスタンプクエリ方式に変更(2013.11.19 eda)
	var ajax = new Ajax.Request( "{0}ajax/getSearchResultOrder?{1}".format( document.getElementsByTagName("base")[0].href, new Date().valueOf() ), {
		method : "get",
		asynchronous : false
	} );
	var orderData = ajax.transport.responseText.parseJSON().data;

	document.getElementsByClassName("caption", $("order_search_form_table")).each( function(caption) {
		var classNames = new Element.ClassNames( caption );

		// 現在非表示設定か
		var hidden = classNames.include( "hidden" );

		// カラム名
		var colName = classNames.find( function(name) {
			return ! [ "search_item", "caption", "hidden", "requesting" ].include( name );
		} );

		if( ! colName || ! orderData[ colName ] ) return;

		var data = orderData[ colName ];

		classNames[ data.hidden ? "add" : "remove" ]("hidden");
		caption.setTitle();
	} );
}

// OK button
Event.observe( $("columnEditOkButton"), "click", function(evt) {
	var visibles = ColumnContainer._containers.findContainer( $("visible_column_container") ).columns().map( function(col, index) {
		var result = Object.extend( {}, col.properties );
		return Object.extend( result, {
			order : index + 1,
			hidden : false
		} );
	} );
	var invisibles = ColumnContainer._containers.findContainer( $("invisible_column_container") ).columns().map( function(col, index) {
		var result = Object.extend( {}, col.properties );
		return Object.extend( result, {
			order : index + visibles.length + 1,
			hidden : true
		} );
	} );

	var postData = {};
	[].concat( visibles, invisibles ).each( function(item) {
		postData[ item.column ] = item;
	} );

	WindowCover.show();
	var errorHandler = function(transport, err) {
		WindowCover.hide();
		alert(  err || new Error( transport.responseText ) );
	}

	var ajax = new Ajax.Request( "{0}ajax/setSearchResultColumnModify".format( document.getElementsByTagName("base")[0].href ), {
		method : "post",
		postBody : $H( { "postData" : postData.toJSONString() } ).toQueryString(),
		onFailure : errorHandler,
		onException : errorHandler,
		onComplete : function(transport) {
			var response = transport.responseText.parseJSON();
			if( ! response ) {
				errorHandler(
					transport,
					new Error( "cannot sent postData : {0}".format( ajax.transport.responseText || "(n/a)" ) )
				);
				return;
			}

			if( ! response.result ) {
				errorHandler( transport, new Error( response.reason ) );
				return;
			}

			applyColumnSetting();

			WindowCover.hide();
			columnEditor.close();
		}
	} );
}.bindAsEventListener( $("columnEditOkButton") ) );

// キャンセルボタン
Event.observe( $("columnEditCancelButton"), "click", function(evt) {
	columnEditor.close();
}.bindAsEventListener( $("columnEditCancelButton") ) );

// 初期値に戻す
Event.observe( $("columnEditResetButton"), "click", function(evt) {
	if( ! confirm( "項目の並び順と表示設定を初期値に戻しますか？" ) ) return;

	WindowCover.show();
	var errorHandler = function(transport, err) {
		WindowCover.hide();
		alert( err || new Error( transport.responseText || "エラー" ) );
	};

	// chromeでif-modified-sinceヘッダ付与が許容されなくなったのでタイムスタンプクエリ方式に変更(2013.11.19 eda)
	new Ajax.Request( "{0}ajax/resetSearchResultColumnSchema?{1}".format( document.getElementsByTagName("base")[0].href, new Date().valueOf() ), {
		method : "get",
		onFailure : errorHandler,
		onException : errorHandler,
		onComplete : function(transport) {
			var result = transport.responseText.parseJSON();
			if( ! result ) {
				errorHandler( transport, new Error( transport.responseText ) );
				return;
			}

			if( ! result.result ) {
				errorHandler( transport, result.reason );
				return;
			}

			loadData();
			applyColumnSetting();

			WindowCover.hide();
		}
	} );
}.bindAsEventListener( $("columnEditResetButton") ) );

function loadData() {
	ColumnContainer.containers().each( function(container) {
		container.clear();
	} );
	EditableColumn._init();

	WindowCover.show();
	// chromeでif-modified-sinceヘッダ付与が許容されなくなったのでタイムスタンプクエリ方式に変更(2013.11.19 eda)
	new Ajax.Request( "{0}ajax/getSearchResultOrder?{1}".format( document.getElementsByTagName("base")[0].href, new Date().valueOf() ), {
		onComplete : function(transport) {
			WindowCover.hide();
			var data = transport.responseText.parseJSON().data;
			$H(data).keys().each( function(key) {
				try {
					var item = data[key];
					var el = document.createElement("div");

					var parent = item.hidden ? $("invisible_column_container") : $("visible_column_container");
					parent.appendChild( el );
					new EditableColumn( el, item );
				} catch(e) {
					alert("item = {0}".format( item.column ) + "\n" + e);
					throw $break;
				}
			} );

			ColumnContainer.containers().each( function(container) {
				container.refresh();
			} );
		}
	} );
}

Event.observe( window, "load", function(evt) {
	[ "visible", "invisible" ].each(function(mode) {
		try {
			new ColumnContainer( $("{0}_column_container".format(mode)), mode ).refresh();
		} catch(e) {
			alert("mode = {0}".format( mode ) + "\n" + e);
		}
	} );

	window.columnEditor = new base.UI.ModalDialog( $("column_order_editor"), {
		width : 800,
		height: 350,
		title : "検索表示のカスタマイズ",
		draggable : false
	} );
	Element.show( $("column_order_editor") );
}.bindAsEventListener( window ) );
</script>
