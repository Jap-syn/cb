<div id="cancel_dialog" style="display:none">
    <div>
        <div id="cancel_reason_title" align="left" style="height: 40px; vertical-align:top;">
        </div>
        <div align="left" style="margin: 20px 0px 10px 0px;">
        <select id="cancel_reason_list">
        </select>
        </div>
        <div align="left" style="height: 110px; vertical-align:top;">
        <textarea rows="2" cols="1" id="cancel_reason"></textarea>
        </div>
        <div id="combined_order_cancel" align="left" style="color: red;">
        </div>
        <div class="dlg_buttons" align="center">
            <button id="dialog_ok">OK</button>
            <button id="dialog_cancel">キャンセル</button>
        </div>
        <!-- 2.6 mail -->
        <input type="hidden" id="cancel_mail_flag" value="0">
    </div>
</div>
<script>
var todoitekara;
// キャンセル申請
var rowOrderSeq;
document.getElementsByClassName("do_cancel", $("search_result_table")).each( function(cmd) {
    Event.observe( cmd, "click", function(evt) {
        Event.stop( evt );
        requestCancel( this );
    }.bindAsEventListener( cmd ) );
} );
function getCancelDialog(todoitekara) {
    //SBPS-29: check title by todoitekara
    let title = '';
    if( window.cancelDlg == null ) {
        if (todoitekara == 1) {
            title = 'キャンセル確定'
            // 2.6 mail
            $('cancel_mail_flag').value = "1";
        } else  {
            title = 'キャンセル申請'
        }
        window.cancelDlg = new base.UI.ModalDialog(
            "cancel_dialog",
            {
                width: 500,
                height: 250,
                title: title,
                draggable : false
            }
        );
    }
    Element.show( $("cancel_dialog") );
    return window.cancelDlg;
};
function requestCancel(cmd) {
//    var seq = /^cnl_(.+)$/.exec( cmd.id )[1];
//    if( seq == null ) return;
    rowOrderSeq = /^cnl_(.+)$/.exec( cmd.id )[1];
    if( rowOrderSeq == null ) return;

    var element = document.getElementById('cnl_'+rowOrderSeq);
    todoitekara =  element.dataset.value;
    var row = document.getElementById( "row_{0}".format( rowOrderSeq ) );
    if( row == null ) return;

    rowClassNames = new Element.ClassNames( row );
    rowClassNames.add( "hover" );

//    var reason = prompt( "{0} のキャンセル理由を入力してください（空文字可）".format( row.title ), "" );

    var dlg = getCancelDialog(todoitekara);
    // ダイアログイベントのクリア
    dlg.options.preClose = Prototype.emptyFunction;

    var title = document.getElementById( "cancel_reason_title");
    //SBPS-29: check title by todoitekara
    if (todoitekara == 1){
        title.innerHTML = "注文ID：{0} のキャンセル理由を入力してください（空文字可）<br> <span style='color: red'>※届いてから払いの注文の為、キャンセル取消が行えません。<br>ご注意ください。</span><br>".format( row.dataset.id ), "";
    } else {
        title.innerHTML = "注文ID：{0} のキャンセル理由を入力してください（空文字可）".format( row.dataset.id ), "";
    }

    // キャンセル理由リスト
    var list = $( "cancel_reason_list" );

    // キャンセル理由リストの構築
    <?php foreach( $this->cancelList as $key => $value ) {
        if( $key == -99 ) continue;
        echo 'var opt = document.createElement( "option" );' . "\r\n";
        echo 'opt.value="' . $key . "\";\r\n";
        echo 'opt.innerHTML="' . $value . "\";\r\n";
        echo "list.appendChild( opt );\r\n";
    }?>

    // 請求取りまとめキャンセルメッセージ
    var combinedFlg = document.getElementById( "combined_{0}".format( rowOrderSeq ) ).value;
    var combined = document.getElementById( "combined_order_cancel");
    if (combinedFlg == "91" || combinedFlg == "92") {
        combined.innerHTML = "とりまとめのすべての商品がキャンセルされます";
    }
    else {
        combined.innerHTML = "";
    }

    // ダイアログイベント（preClose）の設定
    dlg.options.preClose = function() {
        $("cancel_reason_list").selectedIndex = 0;
        $("cancel_reason").value = "";
        return true;
    }

    dlg.center().open();
}
Event.observe( $("dialog_ok"), "click", function() {
    var row = document.getElementById( "row_{0}".format( rowOrderSeq ) );
    var list = $( "cancel_reason_list" );
    var reason = $( "cancel_reason" );
    var cancel_reason_list_value = list.value;
    var cancel_reason = reason.value;
    // 2.6 mail
    var cancel_mail_flag_value = $('cancel_mail_flag').value;
    if( cancel_reason_list_value != null && cancel_reason != null ) {
        var ajax = new Ajax.Request(
            "{0}ajax/requestCancel".format(
                ( document.getElementsByTagName("base")[0] || { href : "./" } ).href
            ),
            {
                method : "post",
                asynchronous : false,
                postBody : $H( {
                    orderSeq : rowOrderSeq,
                    reason : cancel_reason,
                    reason_code : cancel_reason_list_value,
                    userid : <?php echo $this->userId; ?>,
                    // 2.6 mail
                    cancel_mail_flag: cancel_mail_flag_value
                } ).toQueryString()
            }
        );
        var result = ajax.transport.responseText.parseJSON();

        if( result === false ) {
//            alert( "キャンセル申請でエラーが発生しました：\r\n{0}".format( ajax.transport.responseText.stripTags() ) );
            alert( "この注文は、他の端末からキャンセル申請されているか、または、キャンセル不可の注文です。" );
        } else {
            if( ! result.result ) {
//                alert( "キャンセル申請でエラーが発生しました：\r\n{0}".format( result.reasonCode ) );
                if (result.isToDo == 1) {
                    if (result.Exc == 0) {
                        alert( "SBPS側でエラーが発生しました。{0}({1}) ".format( result.reasonCode, result.errCodeSbps ));
                    } else {
                        alert( "この注文は、既にキャンセル処理されている注文です。");
                    }
                } else {
                    alert( "この注文は、他の端末からキャンセル申請されているか、または、キャンセル不可の注文です。" );
                }
            } else {
                if (result.combinedFlg) {
                	alert( "取りまとめ注文を全てキャンセルしました。" );
                }
				Object.extend( $("current_search_conditions"), {
					action : "search/result"
				} ).submit();
            }
        }
    }
    rowClassNames.remove( "hover" );

    getCancelDialog(1).close();
}, false );

Event.observe( $("dialog_cancel"), "click", function() {
    //SBPS-29: clear options in select cancel reason list
    var list = $( "cancel_reason_list" );
    list.innerHTML = "";
    getCancelDialog(1).close();
}, false );

// キャンセル取消
document.getElementsByClassName("do_cancel_cancel", $("search_result_table")).each( function(cmd) {
    Event.observe( cmd, "click", function(evt) {
        Event.stop( evt );
        requestCancelCancel( this );
    }.bindAsEventListener( cmd ) );
} );
function requestCancelCancel(cmd) {
    var seq = /^cnl_cnl(.+)$/.exec( cmd.id )[1];
    if( seq == null ) return;

    var row = document.getElementById( "row_{0}".format( seq ) );
    if( row == null ) return;

    var rowClassNames = new Element.ClassNames( row );
    rowClassNames.add( "hover" );

    // 請求取りまとめキャンセルメッセージ
    var combinedFlg = document.getElementById( "combined_{0}".format( seq ) ).value;
    var combinedMsg = "";
    if (combinedFlg == "91" || combinedFlg == "92") {
    	combinedMsg = "\r\nとりまとめのすべての商品がキャンセル取消されます";
    }

    var confirmMsg = "{0} のキャンセル申請を取り消します" + combinedMsg;
    var ret = confirm( confirmMsg.format( row.title ) );

    if( ret == true ) {
        var ajax = new Ajax.Request(
            "{0}ajax/requestCancelCancel".format(
                ( document.getElementsByTagName("base")[0] || { href : "./" } ).href
            ),
            {
                method : "post",
                asynchronous : false,
                postBody : $H( {
                    orderSeq : seq,
                    userid : <?php echo $this->userId; ?>
                } ).toQueryString()
            }
        );
        var result = ajax.transport.responseText.parseJSON();

        if( result === false ) {
//            alert( "キャンセル取消でエラーが発生しました：\r\n{0}".format( ajax.transport.responseText.stripTags() ) );
            alert( "この注文は、他の端末からキャンセル申請取り消しされているか、または、キャンセル申請されていない注文です。" );
        } else {
            if( ! result.result ) {
//                alert( "キャンセル取消でエラーが発生しました：\r\n{0}".format( result.reasonCode ) );
                alert( "この注文は、他の端末からキャンセル申請取り消しされているか、または、キャンセル申請されていない注文です。" );
            } else {
            	if (result.combinedFlg) {
                	alert( "取りまとめ注文の全てのキャンセル申請を取り消しました。" );
                }
				Object.extend( $("current_search_conditions"), {
					action : "search/result"
				} ).submit();
            }
        }
    }
    rowClassNames.remove( "hover" );
}

</script>
