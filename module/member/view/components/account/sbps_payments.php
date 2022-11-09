<style>
    .base_ui_modaldialog_title {
        background-color: white;
        color: black;
        text-align: center;
        padding-top: 10px;
        border-bottom: solid 1px;
        font-size: 18px;
    }
</style>
<div id="sbpsPaymentDialog" style="display:none; padding: 10px;">
    <div id="sbpsPaymentTable"></div>
    <div class="dlg_buttons" align="center" style="margin-top: 20px;">
        <button id="dialog_cancel">閉じる</button>
    </div>
</div>

<script>
document.getElementsByClassName("do_cancel", $("site_table")).each( function(cmd) {
    Event.observe( cmd, "click", function(evt) {
        Event.stop( evt );
        openDialog( this );
    }.bindAsEventListener( cmd ) );
} );

function getSbpsPaymentDialog() {
    if( window.cancelDlg == null ) {
        window.cancelDlg = new base.UI.ModalDialog(
            "sbpsPaymentDialog",
            {
                width: 1000,
                height: 450,
                title: '支払方法種類',
                draggable : false
            }
        );
    }
    Element.show( $("sbpsPaymentDialog") );
    return window.cancelDlg;
};

function openDialog(cmd) {
    var siteId = /^cnl_(.+)$/.exec( cmd.id )[1];
    if( siteId == null ) return;

    // using ajax to get sbps settings
    $("sbpsPaymentTable").innerHTML = 'ローディング...';
    var ajax = new Ajax.Request(
        "{0}account/getsbpssetting".format(
            ( document.getElementsByTagName("base")[0] || { href : "./" } ).href
        ),
        {
            method : "post",
            asynchronous : true,
            postBody : $H( {
                sid : siteId,
            } ).toQueryString(),
            onException : function(xhr, err) {
                throw err;
            },
            onComplete : function(xhr) {
                var html = xhr.responseText;
                $("sbpsPaymentTable").innerHTML = html;
            }
        }
    );

    var element = document.getElementById('cnl_'+siteId);
    var row = document.getElementById( "row_{0}".format( siteId ) );
    if( row == null ) return;

    var dlg = getSbpsPaymentDialog();
    dlg.center().open();
}

Event.observe( $("dialog_cancel"), "click", function() {
    var dlg = getSbpsPaymentDialog();
    dlg.close();
}, false );

</script>
