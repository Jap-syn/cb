<?php
use oemmember\Application;

$selfBillingConfig = Application::getInstance()->selfBillingConfig;
$selfBillingEnabled = Application::getInstance()->getSelfBillingEnabled();
$ent = Application::getInstance()->getCurrentEnterpriseData();
$combinedClaimConfig = Application::getInstance()->combinedClaimConfig;
$oemId = Application::getInstance()->authManager->getUserInfo()->OemId;
$oemInfo = $this->currentOemInfo;

//--------------------------------
// コンテンツヘッダ下のメニューバー
?>
<div id="header_menu" class="header_menu" style="line-height:100%">
    <h3>
        <a id="logout_menu" href="login/logout" style="line-height:100%">⇒ログアウト</a>
<?php
// クイック検索。導入しないかも。
// エラー画面表示時は表示しない
if (!$this->exception) {
        echo $this->render('oemmember/quick_search_bar.php');
}
?>
        ↓下記のメニューよりご利用になりたい機能を選択してください。
    </h3>
    <span style="clear:both;font-size:0px;height: 0px;"></span>
    <ul>
<?php
//--------------------------------
// メニューバー構築
foreach($this->menuLinks as $link) {

    $classNames = array( 'menu_title_container' );
    $attr = null;
    if( isset($link['submenus']) && !empty( $link['submenus'] ) ) {
        $classNames[] = 'has_submenu';
    }
?>
    <?php
    if ($link['id'] == 'header_menu_6') {
        $linkHref = Application::getInstance()->getLinkHelp(0);
        if (Application::getInstance()->hasPaymentAfterArrivalFlg(Application::getInstance()->authManager->getUserInfo()->EnterpriseId)) {
            $linkHref = Application::getInstance()->getLinkHelp(1);
        }
        ?>
        <li id="<?php echo $link['id']; ?>" class="<?php echo join(' ', $classNames); ?>">
            <div class="menu_title">
                <a class="header_menu_item" href="<?php echo $this->basePath() . '/'. $linkHref; ?>"<?php if( $link['new'] == 'true' ) { echo ' target="_blank"'; } ?>>
                    <?php echo $link['text']; ?>
                </a>
            </div>
            <div class="menu_description"><?php echo $link['desc']; ?></div>
        </li>
    <?php } else {?>
        <li id="<?php echo $link['id']; ?>" class="<?php echo join(' ', $classNames); ?>">
            <div class="menu_title">
                <a class="header_menu_item" href="<?php echo $link['href']; ?>"<?php if( $link['new'] == 'true' ) { echo ' target="_blank"'; } ?>>
                    <?php echo $link['text']; ?>
                </a>
            </div>
            <div class="menu_description"><?php echo $link['desc']; ?></div>
        </li>
    <?php }
    ?>
<?php if( !empty( $link['submenus'] ) ) { // サブメニュー構築 ?>
        <div id="_<?php echo $link['id']; ?>" class="submenu_container" style="display: none">
<?php foreach( $link['submenus'] as $subLink ) { ?>
            <a href="<?php echo $subLink['href']; ?>" title="<?php echo $subLink['desc']; ?>">
                <?php echo $subLink['text']; ?>
            </a>
<?php } ?>
        </div>
<?php
    }
}
?>
    </ul>
</div>
<script src="../../oemmember/js/menu.js"></script>
<script src="../../oemmember/js/cover.js"></script>
<script>
Event.observe( window, "load", function() {
    window.WindowCover = createCover();
    document.body.appendChild( window.WindowCover );

    // ページ遷移時にWindowCoverで操作を不能にする
    var evName = base.UI.currentBrowser() == base.UI.browserTypes.opera ?
        "unload" : "beforeunload";
    Event.observe( window, evName, function(evt) {
        WindowCover.show();
    }.bindAsEventListener( window ) );

    // ダウンロードクリックによるunload発生時はWindowCoverを出さないようにする
    Event.observe( document.body, "click", function(evt) {
        WindowCover.suspend = ( Event.element(evt) == $("download") );
    }.bindAsEventListener( document.body ) );
} );

window.onerror = function(msg, url, line) {
    Debug.write( "error: {0} on {1} ( line : {2:0} )".format( msg, url, line ) );
    Debug.show();
    return <?php echo Application::getInstance()->appGlobalConfig['debug_mode'] ? 'true' : 'false'; ?>;
}
var Debug = {
    messages : [],
    show : function() { return this; },
    hide : function() { return this; },
    shrink : function() { return this; },
    write : function(msg) {
        return this;
    },
    clear : function() {
        return this;
    }

}

<?php if( Application::getInstance()->appGlobalConfig['debug_mode'] ) { ?>
Object.extend( Debug, {
    _window : Object.extend( document.createElement("div"), {
        className : "debug_window debug_parts"
    } ),

    _content : Object.extend( document.createElement("div"), {
        className : "debug_messages debug_parts"
    } ),

    _commandbar : Object.extend( document.createElement("div"), {
        className : "debug_command_bar debug_parts"
    } ),

    _command : Object.extend( document.createElement("input"), {
        type : "text",
        size : 30,
        className : "debug_command debug_parts"
    } ),

    _exec : Object.extend( document.createElement("button"), {
        className : "debug_command_exec debug_parts",
        innerHTML : "eval"
    } ),

    _title : Object.extend( document.createElement("div"), {
        className : "debug_title debug_parts",
        innerHTML : [
            "<span style='float: left;'>debug messages</span>",
            "<a href='#' onclick='Debug.shrink(); return false;'>_</a>",
            "<a href='#' onclick='Debug.hide(); return false;'>x</a>"
        ].join("")
    } ),

    _initialize : function() {
        Object.extend( this._window.style, {
            display : "none"
        } );

        this._commandbar.appendChild( this._command );
        this._commandbar.appendChild( this._exec );

        this._window.appendChild( this._title );
        this._window.appendChild( this._commandbar );
        this._window.appendChild( this._content );
        document.body.appendChild( this._window );

        var evalCommand = function(evt) {
            var c = Debug._command.value.trim();
            if( c.length == 0 ) return;

            Debug.write( "> {0}".format( c ) );
            Debug._command.value = "";

            with( {
                write : Debug.write.bind( Debug ),
                clear : Debug.clear.bind( Debug )
            } ) {
                var result = eval( c );
            }

            if( result != null ) Debug.write( "→ {0}".format( result ) );

            setTimeout( function() {
                Debug._command.focus();
            }, 0 );
        }.bindAsEventListener( this._exec );

        Event.observe( this._exec, "click", evalCommand );

        Event.observe( this._command, "keydown", function(evt) {
            if( evt.keyCode == Event.KEY_RETURN ) evalCommand(evt);
        }.bindAsEventListener( this._command ) );

        delete this._initialize;

        return this;
    },

    show : function() {
        Element.show( this._window );
        setTimeout( function() {
            Debug._command.focus();
        }, 0 );
        return this;
    },

    hide : function() {
        Element.hide( this._window );
        return this;
    },

    shrink : function() {
        var visible = ( arguments.length == 0 ? Element.visible( this._content ) : !! arguments[0] ) ?
            "hide" : "show";
        Element[ visible ]( this._content );

        return this;
    },

    write : function(msg) {
        this.messages.push( msg );
        this._content.appendChild( "<div>{0}</div>".format(msg).toElement() );
        setTimeout( function() {
            this._content.scrollTop = this._content.scrollHeight;
        }.bind( this ), 0 );

        return this;
    },

    clear : function() {
        this.messages = [];
        this._content.innerHTML = "";

        return this;
    }
} );
<?php } ?>

$H( Debug ).keys().each( function(key) {
    if( typeof Debug[key] == "function" ) {
        Debug[key] = Debug[key].bind( Debug );
    }
} );

Debug.hide();

<?php if( Application::getInstance()->appGlobalConfig['debug_mode'] ) { ?>
new base.UI.DragHelper( Debug._window, Debug._title );
Debug._initialize();

(function() {
    var e = Object.extend( "<span>d</span>".toElement(), {
        onclick : function() {
            Debug.show();
        }
    } );
    Object.extend( e.style, {
        border: "solid 1px dimgray",
        color : "dimgray",
        padding: "1px 2px",
        fontSize : "8pt",
        position: "absolute",
        right : "0px",
        top : "0px",
        cursor : "pointer",
        backgroundColor : "white",
        opacity : 0.7,
        "-moz-opacity" : 0.7,
        filter : "alpha(opacity=70)"
    } );
    document.body.appendChild( e );
})();
<?php } ?>
</script>
