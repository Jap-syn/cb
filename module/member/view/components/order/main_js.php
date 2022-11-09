<script>
<?php
use Zend\Json\Json;
use Coral\Coral\Validate\CoralValidateUtility;
use member\classes\OrderInputInfo;
?>
// 入力値の整形処理
with( {
	blurHandler : function(evt) {
		var transTargets = {
			"o_receipt_order_date" : "date",
			"c_postalcode" : "postalCode",
			"d_postalcode" : "postalCode"
		};
		var transFunctions  = {
			date : base.Utility.transDateString,
			postalCode : base.Utility.transPostalCode
		};

		this.value = ( this.value || "" ).trim();
		var conf = transTargets[ this.id ];
		if( conf ) this.value = transFunctions[ conf ]( this.value );
	}
} ) {
	Form.getElements($("order_form" )).each( function(field) {
		field.preFormat = blurHandler.bind( field );
		Event.observe( field, "blur", blurHandler.bindAsEventListener( field ) );
	} );
}

// FormValidator初期化
var validator = new base.FormValidator();
validator.commonValidateHandler = function(args) {
	var row = args.element;
	while( ! /tr/i.test( row.tagName ) ) row = row.parentNode;
	var classNames = new Element.ClassNames( row );
	if( args.result ) {
		classNames.remove( "validate_error" );
	} else {
		classNames.add( "validate_error" );
	}
};
<?php
$validation_list = array();
foreach( $this->form_info->getAllItems() as $item ) {
	$validation_list[] = array(
		'id' => $item->getId(),
		'rule' => $item->getValidation()
	);
}
// 請求金額合計を追加
$validation_list[] = array(
	'id' => 'total_receipt',
	'rule' => '/^(\d|([1-9]\d+))$/'
);
echo Json::encode( $validation_list, Json::TYPE_ARRAY );
?>.each( function(info) {
	validator.addValidation( info.id, eval( info.rule ) );
} );
validator.addValidate( validator.commonValidateHandler );

<?php if ($this->ReOrderFlg == 1){ ?>
validator.addValidation( $("c_prefecturename"), /^.*$/ );
validator.addValidation( $("c_city"), /^.*$/ );
validator.addValidation( $("c_town"), /^.*$/ );
validator.addValidation( $("d_prefecturename"), /^.*$/ );
validator.addValidation( $("d_city"), /^.*$/ );
validator.addValidation( $("d_town"), /^.*$/ );
<?php } else { ?>
validator.addValidation( $("c_address"), /^.*$/ );
validator.addValidation( $("d_address"), /^.*$/ );
<?php } ?>

// メールアドレスの必須変更ユーティリティクラス
var MailRequireManager = Class.create();
MailRequireManager.prototype = {
	initialize : function(sites) {
		this.sites = ( sites instanceof Array ) ? sites : [];
	},
	addSite : function(site) {
		this.sites.push( site );
	},
	isRequire : function(siteId) {
		var site = this.sites.find( function(s) {
			return s.id == siteId;
		} );
		return site ? site.require : false;
	}
}
Object.extend( MailRequireManager, {
	requiredExpression : <?php echo CoralValidateUtility::EMAIL_ADDRESS; ?>,
	nonRequiredExpression : /^.*$/
} );

//役務提供予定日の必須変更ユーティリティクラス
var EkimuRequireManager = Class.create();
EkimuRequireManager.prototype = {
	initialize : function(sites) {
		this.sites = ( sites instanceof Array ) ? sites : [];
	},
	addSite : function(site) {
		this.sites.push( site );
	},
	isRequire : function(siteId) {
		var site = this.sites.find( function(s) {
			return s.id == siteId;
		} );
		return site ? site.require : false;
	}
}
Object.extend( EkimuRequireManager, {
	requiredExpression : <?php echo OrderInputInfo::REGEXP_DATETIME; ?>,
	nonRequiredExpression : /^.*$/
} );

// 検証エラーリスト作成関数
function createValidateErrors(errors) {
	var area = $("validate_error_area");
	Element.hide( area );

	var title = area.getElementsByTagName("h4")[0];
	var root = document.getElementsByClassName("error_info_list", area)[0];
	root.innerHTML = "";
	title.innerHTML = "{0} 件の入力エラー".format( errors.length );
	if( errors.length == 0 ) return;

	errors.map( function(errorInfo) {
		return Object.extend( "<li>{0}</li>".format( errorInfo.label ).toElement(), {
			onmouseover : function() {
				new Element.ClassNames( this ).add( "hover" );
			},
			onmouseout : function() {
				new Element.ClassNames( this ).remove( "hover" );
			},
			onclick : function() {
				if( errorInfo.target ) {
					setTimeout( function() {
						bytefx.scroll( errorInfo.row, 70, function() {
							errorInfo.target.focus();
						} );
					}, 100 );
				}
			}
		} );
	} ).each( function(li) {
		root.appendChild( li );
	} );

	Element.show( area );
	setTimeout( function() {
		bytefx.scroll( area, 70 );
	}, 0 );
}

<?php
$siteInfo = array();
foreach( $this->site_list as $row ) {
	$siteInfo[] = array(
		'id' => $row['SiteId'],
		'require' => (bool)($row['ReqMailAddrFlg'])
	);
}
?>
var reqManager = new MailRequireManager(
	<?php echo Json::encode( $siteInfo, Json::TYPE_ARRAY ); ?>
);

with( {
	handler : function(evt) {
		var f = $("c_mailaddress");
        var is_require = reqManager.isRequire( $("o_siteid").value );

		var row = f;
		while( ! /^tr$/i.test( row.tagName ) ) row = row.parentNode;

		var classNames = new Element.ClassNames( row );
		if( is_require ) {
			classNames.add( "must_row" );
		} else {

            if ($("o_CreditTransferRequestFlg") != null) {
                if ($("o_CreditTransferRequestFlg").value == 1) {
                    classNames.add( "must_row" );
                    is_require = true;
                }
                else {
                    classNames.remove( "must_row" );
                    is_require = false;
                }
            }
            else {
                classNames.remove( "must_row" );
                is_require = false;
            }

		}

        validator.addValidation( f, is_require ? MailRequireManager.requiredExpression : MailRequireManager.nonRequiredExpression );

	}
} ) {
	Event.observe( $("o_siteid"), "change", handler, false );
	handler.call( $("o_siteid") );

    if ($("o_CreditTransferRequestFlg") != null) {
        Event.observe( $("o_CreditTransferRequestFlg"), "change", handler, false );
        handler.call( $("o_CreditTransferRequestFlg") );
    }

}

<?php
$ekimusiteInfo = array();
foreach( $this->site_list as $row ) {
	$ekimusiteInfo[] = array(
		'id' => $row['SiteId'],
		'require' => (bool)($row['ServiceTargetClass'])
	);
}
?>
var reqManagerEkimu = new EkimuRequireManager(
	<?php echo Json::encode( $ekimusiteInfo, Json::TYPE_ARRAY ); ?>
);

with( {
	handler : function(evt) {
		var f = $("o_service_expected_date");
		var is_require = reqManagerEkimu.isRequire( this.value );

		var exp = is_require ?
			EkimuRequireManager.requiredExpression :
			EkimuRequireManager.nonRequiredExpression;

		validator.addValidation( f, exp );

		var row = f;
		while( ! /^tr$/i.test( row.tagName ) ) row = row.parentNode;

		var classNames = new Element.ClassNames( row );
		if( is_require ) {
			classNames.add( "must_row" );
		} else {
			classNames.remove( "must_row" );
		}
	}
} ) {
	Event.observe( $("o_siteid"), "change", handler, false );
	handler.call( $("o_siteid") );
}

// 別配送先チェックボックス
with( {
	handler : function(evt) {
		var container = $("<?php echo OrderInputInfo::ARRKEY_DELIV; ?>");
		var items = [
			$A( container.getElementsByTagName("input") ),
			$A( container.getElementsByTagName("button") ),
			$A( container.getElementsByTagName("select") ),
			$A( container.getElementsByTagName("textarea") )
		].flatten();

		var disabled = ! this.checked;
		items.each( function(item) {
			if( disabled ) {
				item.disabled = "disabled";
				new Element.ClassNames( item ).add( "disabled" );
			} else {
				item.removeAttribute( "disabled" );
				new Element.ClassNames( item ).remove( "disabled" );
			}
		} );
	}
} ) {
	Event.observe( $("use_delivery_info"), "click", handler.bindAsEventListener( $("use_delivery_info") ) );
	// 別配送先初期処理
	handler.call( $("use_delivery_info") );
}

// 商品行および送料・手数料行の初期化
[ document.getElementsByClassName("item_row"), document.getElementsByClassName("extra_item_row") ].flatten().each( function(row) {
	initItemRow( row, false );
} );

var fixField = function(field, objtype) {
	var v = field.value;
	if( fixField._matchReg.test( v ) ) {
		for(var i = 0, l = fixField._replaceRegs.length; i < l; i++) {
			v = v.replace( fixField._replaceRegs[i], i );
		}
	}

    if (objtype == 2) {
        v = parseFloat( v.replace(/[,、]/g, "").replace( /^(-?)0*(.*$)/, "$1$2" ) );
    }
    else {
    	v = parseInt( v.replace(/[,、]/g, "").replace( /^(-?)0*(.*$)/, "$1$2" ) );
    }

	if( isNaN( v ) ) v = 0;
	field.value = v;
};
fixField._replaceTarget = "０１２３４５６７８９";
fixField._matchReg = /[０-９]/g;
fixField._replaceRegs = $A(fixField._replaceTarget).map( function(s) { return new RegExp( s, "g" ); } );

// 請求金額計算
Event.observe( $("calc_total_receipt"), "click", function(evt) {

	var tbl = $("<?php echo OrderInputInfo::ARRKEY_ITEMS; ?>");
	var body = tbl.getElementsByTagName("tbody")[0];

	var rows = [].concat( document.getElementsByClassName("item_row", body), document.getElementsByClassName("extra_item_row", body) );
	var total = rows.inject( 0, function(ttl, row) {
		var priceField = $A( row.getElementsByTagName("input") ).find( function(input) {
			return new Element.ClassNames(input).include( "i_unitprice" );
		} );
		var numField = $A( row.getElementsByTagName("input") ).find( function(input) {
			return new Element.ClassNames(input).include( "i_itemnum" );
		} );

 		fixField( priceField, 1 );
		fixField( numField, 2 );

	    var dgtrate = Math.pow(10,<?php echo (int)$this->userInfo->DispDecimalPoint; ?>);
	    var tmpval = ( Number( priceField.value ) * Number( numField.value * dgtrate ).toFixed(0) );
	    tmpval /= dgtrate;

		<?php if ((int)$this->userInfo->UseAmountFractionClass == 0) { ?>tmpval = Math.floor( tmpval );<?php } ?>
	    <?php if ((int)$this->userInfo->UseAmountFractionClass == 1) { ?>tmpval = Math.round( tmpval );<?php } ?>
		<?php if ((int)$this->userInfo->UseAmountFractionClass == 2) { ?>tmpval = Math.ceil(  tmpval );<?php } ?>
		return ttl + tmpval;
	} );
	$("total_receipt").value = total;
}.bindAsEventListener( $("calc_total_receipt" ) ) );
(function() {
	$("calc_total_receipt").click();
})();

// 商品追加
Event.observe( $("item_add_button"), "click", function(evt) {
	var tbl = $("<?php echo OrderInputInfo::ARRKEY_ITEMS; ?>");
	var body = tbl.getElementsByTagName("tbody")[0];

	var row = ( document.getElementsByClassName("item_row")[0] ).cloneNode(true);
	initItemRow( row, true );
	body.insertBefore( row, $("none_item_row") );
	new Element.ClassNames( row ).remove( "validate_error" );
	setTimeout( function() {
		var f = row.getElementsByTagName("input")[0];
		if( f ) f.focus();
	}, 0 );
}, false );

// 商品項目行初期化処理
function initItemRow(row, isNew) {
	var delButton = document.getElementsByClassName("item_delete_button", row)[0];
	var nameField = document.getElementsByClassName("i_itemnamekj", row)[0];
	var priceField = document.getElementsByClassName("i_unitprice", row)[0];
	var numField = document.getElementsByClassName("i_itemnum", row)[0];
	var expDiv = document.getElementsByClassName("item_exsample", row)[0];

	if( isNew ) {
		Element.show( delButton );

		[ nameField, priceField, numField ].each( function(field) {
			field.value = "";
			var classNames = new Element.ClassNames( field );
			classNames.remove( "must" );
		} );

		expDiv.parentNode.removeChild( expDiv );
	};

	if( delButton ) {
		delButton.onclick = function() {
			row.parentNode.removeChild( row );
			$("calc_total_receipt").click();
		}.bindAsEventListener( delButton );
	}

	priceField.onchange = numField.onchange = function() {
		$("calc_total_receipt").click();
	}
}

function trimFields() {
	[].concat(
		$A( $("order_form").getElementsByTagName("input") ),
		$A( $("order_form").getElementsByTagName("textarea") )
	).each( function(input) {
		input.value = input.value.trim();
		( input.preFormat || Prototype.K )();
	} );
}

// formのsubmitイベント
Event.observe( $("order_form"), "submit", function(evt) {
	var err = null;
	var result = <?php echo ( ! $this->validate_on_client ) ? 'true' : 'false'; ?>;
	try {
		// フィールドのトリミング
		trimFields();

		// 商品情報のリスト化
		var itemList = document.getElementsByClassName("item_row").map( function(row) {
			return {
				"i_itemnamekj" : document.getElementsByClassName("i_itemnamekj", row)[0],
				"i_unitprice" : document.getElementsByClassName("i_unitprice", row)[0],
				"i_itemnum" : document.getElementsByClassName("i_itemnum", row)[0],
			    "i_taxrate" : document.getElementsByClassName("i_taxrate", row)[0]
			};
		} );

<?php if( $this->validate_on_client ) { ?>

		// 商品以外の情報の検証
		result = validator.validate();

		var itemValidator = new base.FormValidator();
		itemValidator.addValidate( function(args) {
			var row = args.element;
			while( ! /^tr$/i.test( row.tagName ) ) row = row.parentNode;

			var unitPrice = document.getElementsByClassName("i_unitprice", row )[0];
			var itemNum = document.getElementsByClassName("i_itemnum", row )[0];
			// 単価 × 数量が0の場合はバリデーションエラー
//			if( parseInt( unitPrice.value ) * parseInt( itemNum.value ) == 0 ) args.result = false;
			if( parseFloat( itemNum.value ) <= 0.0 ) args.result = false;

			var event_result = validator.commonValidateHandler.call( this, args );

			if( event_result === false || args.result === false ) {
				return false;
			}
		} );
		// 検証を行うのは商品名のみ
		itemList.each( function(item) {
			itemValidator.addValidation( item["i_itemnamekj"], /^.{1,255}$/ );
		} );
		result = result & itemValidator.validate();

<?php } ?>

		if( result ) {
			itemList = itemList.map( function(item) {
				return {
					"i_itemnamekj" : item["i_itemnamekj"].value || "",
					"i_unitprice" : item["i_unitprice"].value || "",
					"i_itemnum" : item["i_itemnum"].value || "",
					"i_taxrate" : item["i_taxrate"].value || ""
				};
			} );
			$("i_itemlist").value = itemList.toJSONString();
		}
	} catch(e) {
		err = e;
		result = false;
	}
	var error_items = [];
	var error_check = 0;
	if( ! result ) {
		Event.stop( evt );
		if( err ) {
			alert( "検証時にエラーが発生しました：{0}".format( err ) );
			error_check = 1;
		} else {
			alert( "入力データに誤りがあります" );
			error_check = 1;
		}

		// 検証エラーになった項目の名前をカタログする
		error_items = document.getElementsByClassName("validate_error", $("order_form")).map( function(row) {
			var result = {
				row : row,
				target : (
					row.getElementsByTagName("input")[0] ||
					row.getElementsByTagName("select")[0] ||
					row.getElementsByTagName("textarea")[0] ||
					null
				),
				label : null
			};
			if( new Element.ClassNames( row ).include("item_row") ) {
				result.label = "商品名";
			} else if( result.target == $("total_receipt") ) {
				result.label = "請求金額合計";
			} else {
				result.label = ( document.getElementsByClassName("item_name", row)[0] || { innerHTML : null } ).innerHTML.replace(
					/<br.*$/i, ""
				);
			}
			return result;
		} );
	}
	createValidateErrors( error_items );

}, false );

// 住所検索結果モーダルダイアログ
var addrDlg = null;
function getAddressDialog() {
	if( window.addrDlg == null ) {
		window.addrDlg = new base.UI.ModalDialog(
			"address_select_dialog",
			{
				width: 360,
				height: 280,
				title: "住所の選択",
				draggable : false
			}
		);
	}
	Element.show( $("address_select_dialog") );
	return window.addrDlg;
};

// 住所検索結果モーダルダイアログのボタンイベント
Event.observe( $("dialog_ok"), "click", function() {
	getAddressDialog().close();
}, false );

Event.observe( $("dialog_cancel"), "click", function() {
	$("address_list").selectedIndex = -1;
	getAddressDialog().close();
}, false );

// 住所検索結果リストのイベント
with( { el : $("address_list") } ) {
	Event.observe( el, "change", function(evt) {
		$("dialog_ok").disabled = this.selectedIndex < 0;
		$("address_selection_info").innerHTML = this.selectedIndex < 0 ?
			"" : this.options[ this.selectedIndex ].innerHTML.stripTags();
	}.bindAsEventListener( el ), false );
	Event.observe( el, "dblclick", function(evt) {
		$("dialog_ok").click();
	}.bindAsEventListener( el ), false );
};

<?php if ($this->ReOrderFlg == 1) { ?>
// 住所補間ボタン
[
    {
        id : "address_from_postalcode_c",
        parentField : "c_postalcode",
        targetField : "c_address"
    },
    {
        id : "address_from_postalcode_d",
        parentField : "d_postalcode",
        targetField : "d_address"
    }
].each( function(conf) {
    if( !$(conf.id) ) throw $continue;

    var applyAddress = function(p_id, t_id, data) {
        if(! $(p_id) || !$(t_id)) return;
        $(t_id).value = [
            data.PrefectureKanji,
            data.CityKanji,
            data.TownKanji
        ].join("").trim();
        $(p_id).value = base.Utility.transPostalCode( data.PostalCode7 );
    };

    var target = $( conf.id );
    var field = $( conf.parentField );

    Event.observe( target, "click", function(evt) {
        var PC_MIN_LEN = 3;
        field.value = field.value.replace( /[^\d\-]/g, "" ).substr( 0, 8 );
        if( field.value.length < PC_MIN_LEN ) {
            alert( "最低 {0} 桁の郵便番号を指定してください。".format( PC_MIN_LEN ) );
            return;
        }
        WindowCover.show();
        var ajax = new Ajax.Request(
            "<?php echo $this->baseUrl; ?>/ajax/getpostaldata",
            {
                method : "post",
                postBody : $H( {
                    "postalcode" : field.value.replace( /[^\d]/g, "").substr(0, 7)
                } ).toQueryString(),
                onException : function(xhr, err) {
                    WindowCover.hide();
                    throw err;
                },
                onComplete : function(xhr) {
                    WindowCover.hide();
                    try {
                        var items = xhr.responseText.parseJSON();
                        if( ! items ) {
                            throw new Error( [
                                "JSON data parse error !",
                                "=====",
                                "url:",
                                ajax.url || "(n/a)",
                                "---",
                                "header:",
                                xhr.getAllResponseHeaders(),
                                "---",
                                "contents:",
                                xhr.responseText
                            ].join( "\n" ) );
                        }

                        if( items.length == 0 ) {
                            alert( "指定の郵便番号に一致するデータはありません。" );
                        } else if( items.length == 1 ) {
                            applyAddress( conf.parentField, conf.targetField, items[0] );
                        } else {
                            var dlg = getAddressDialog();
                            // ダイアログイベントのクリア
                            dlg.options.preClose = Prototype.emptyFunction;

                            // 検索結果リストのクリア
                            var list = $("address_list");
                            list.options.length = 0;

                            // 検索結果リストの構築
                            items.each( function(item) {
                                var opt = document.createElement("option");
                                opt.value = item.toJSONString();
                                opt.innerHTML = [
                                    item.PostalCode7,
                                    item.PrefectureKanji,
                                    item.CityKanji,
                                    item.TownKanji
                                ].join(" ");
                                list.appendChild( opt );
                            } );

                            // ダイアログイベント（preClose）の設定
                            dlg.options.preClose = function() {
                                try {
                                    if( list.selectedIndex >= 0 ) {
                                        var data = list.value.parseJSON();
                                        applyAddress( conf.parentField, conf.targetField, data );
                                    }
                                } finally {
                                    $("address_list").selectedIndex = -1;
                                    $("dialog_ok").disabled = true;
                                    $("address_selection_info").innerHTML = "";
                                    return true;
                                }
                            }

                            dlg.center().open();
                        }
                    } catch(e) {
                        alert( e );
                    }
                }
            }
        );
    }, false );
} );
<?php } else { ?>
// 住所補間ボタン
[
	{
		id : "address_from_postalcode_c",
		parentField : "c_postalcode",
		mapping : {
			PostalCode7 : "c_postalcode",
			PrefectureKanji : "c_prefecturename",
			CityKanji : "c_city",
			TownKanji : "c_town"
		}
	},
	{
		id : "address_from_postalcode_d",
		parentField : "d_postalcode",
		mapping : {
			PostalCode7 : "d_postalcode",
			PrefectureKanji : "d_prefecturename",
			CityKanji : "d_city",
			TownKanji : "d_town"
		}
	}
].each( function(conf) {
	var applyAddress = function(map, data) {
		$H( map ).each( function(entry) {
			if( entry.key == "PrefectureKanji" ) {
				var el = $( entry.value );
				$A( el.options ).each( function(opt, index) {
					if( opt.getAttribute("label") == data[ entry.key ] ) {
						el.selectedIndex = index;
						throw $break;
					}
				} );
			} else {
				$( entry.value ).value = entry.key == "PostalCode7" ?
					base.Utility.transPostalCode( data[ entry.key ] ) : data[ entry.key ];
			}
		} );
	}

	var target = $( conf.id );
	var field = $( conf.parentField );

	Event.observe( target, "click", function(evt) {
		var PC_MIN_LEN = 3;
		field.value = field.value.replace( /[^\d\-]/g, "" ).substr( 0, 8 );
		if( field.value.length < PC_MIN_LEN ) {
			alert( "最低 {0} 桁の郵便番号を指定してください。".format( PC_MIN_LEN ) );
			return;
		}
		WindowCover.show();
		var ajax = new Ajax.Request(
			"<?php echo $this->baseUrl; ?>/ajax/getpostaldata",
			{
				method : "post",
				postBody : $H( {
					"postalcode" : field.value.replace( /[^\d]/g, "").substr(0, 7)
				} ).toQueryString(),
				onException : function(xhr, err) {
					WindowCover.hide();
					throw err;
				},
				onComplete : function(xhr) {
					WindowCover.hide();
					try {
						var items = xhr.responseText.parseJSON();
						if( ! items ) {
							throw new Error( [
								"JSON data parse error !",
								"=====",
								"url:",
								ajax.url || "(n/a)",
								"---",
								"header:",
								xhr.getAllResponseHeaders(),
								"---",
								"contents:",
								xhr.responseText
							].join( "\n" ) );
						}

						if( items.length == 0 ) {
							alert( "指定の郵便番号に一致するデータはありません。" );
						} else if( items.length == 1 ) {
							applyAddress( conf.mapping, items[0] );
						} else {
							var dlg = getAddressDialog();
							// ダイアログイベントのクリア
							dlg.options.preClose = Prototype.emptyFunction;

							// 検索結果リストのクリア
							var list = $("address_list");
							list.options.length = 0;

							// 検索結果リストの構築
							items.each( function(item) {
								var opt = document.createElement("option");
								opt.value = item.toJSONString();
								opt.innerHTML = [
									item.PostalCode7,
									item.PrefectureKanji,
									item.CityKanji,
									item.TownKanji
								].join(" ");
								list.appendChild( opt );
							} );

							// ダイアログイベント（preClose）の設定
							dlg.options.preClose = function() {
								try {
									if( list.selectedIndex >= 0 ) {
										var data = list.value.parseJSON();
//										field.value = data.PostalCode;
										applyAddress( conf.mapping, data );
									}
								} finally {
									$("address_list").selectedIndex = -1;
									$("dialog_ok").disabled = true;
									$("address_selection_info").innerHTML = "";
									return true;
								}
							}

							dlg.center().open();
						}
					} catch(e) {
						alert( e );
					}
				}
			}
		);
	}, false );
} );
<?php } ?>

Event.observe( window, "load", function() {
	// DatePickerの初期化
	new base.UI.DatePicker(
		"datePicker1",
		"o_receipt_order_date",
		"selectDate"
	);

	// モーダルダイアログの初期化
	getAddressDialog().close();

	// [今日]ボタン
	Event.observe( $("setToday"), "click", function(evt) {
		$("o_receipt_order_date").value = new Date().format( "yyyy/MM/dd" );
	}, false );

} );
</script>
