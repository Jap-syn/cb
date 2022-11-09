/**
 * 表示中のドキュメントを覆って操作をhookするカバーウィンドウを作成する
 */
function createCover() {
    var el = document.createElement("div");
    var d = document, b = document.body;

    // メイン要素の初期構築
    Object.extend( el.style, {
        // 幅と高さはclientWidthとscrollWidthのうちの大きいほうを採用
        width : "{0}px".format(
            [ d.documentElement.clientWidth, b.clientWidth, b.scrollWidth ].max()
        ),
        height: "{0}px".format(
            [ d.documentElement.clientHeight, b.clientHeight, b.scrollHeight ].max()
        ),
        left : "0px", top : "0px", zIndex : 999,
        color : "black", backgroundColor: "gray",
        fontSize : "16pt", fontWeight : "bold",
        position : "absolute", display : "none",
        opacity : "0.6", "-moz-opacity" : "0.6", filter : "alpha(opacity=60)"
    } );

    // 子要素の追加
    el.appendChild( new function() {
        // ロード中表示エリア
        var e = Object.extend( d.createElement("div"), {
            innerHTML : "<img src=\"../../images/loading_01.gif\" /> please wait..."
        } );
        Object.extend( e.style, {
            padding : "16px 40px",
            color : "white", backgroundColor : "black",
            opacity : .9, "-moz-opacity" : .9, filter : "alpha(opacity=90)",
            position: "absolute", zIndex : 1000
        } );
        // メソッドを追加
        Object.extend( e, {
            center : function() {
                var offset = {
                    left : d.documentElement.scrollLeft || b.scrollLeft,
                    top : d.documentElement.scrollTop || b.scrollTop
                };

                var baseSize = base.UI.currentBrowser() == base.UI.browserTypes.opera ?
                    {
                        width : b.clientWidth,
                        height : b.clientHeight
                    } :
                    {
                        width : d.documentElement.clientWidth,
                        height : d.documentElement.clientHeight
                    };

                Object.extend( this.style, {
                    left : "{0}px".format(
                        parseInt( ( baseSize.width - this.clientWidth ) / 2 ) + offset.left
                    ),
                    top : "{0}px".format(
                        parseInt( ( baseSize.height - this.clientHeight ) / 2 ) + offset.top
                    )
                } );
            }
        } );

        return e;
    }() );
    //b.appendChild( el );

    // カバーウィンドウにプロパティとメソッドを追加
    Object.extend( el, {
        suspend : false,

        show : function() {
            if( this.suspend ) return;

            Object.extend( this.style, {
                left : "0px", top : "0px",
                width : "{0}px".format(
                    [
                        d.documentElement.scrollWidth, b.scrollWidth,
                        d.documentElement.clientWidth, b.clientWidth
                    ].max()
                ),
                height: "{0}px".format(
                    [
                        d.documentElement.scrollHeight, b.scrollHeight,
                        d.documentElement.clientHeight, b.clientHeight
                    ].max()
                )
            } );

            if( this._cover == null ) {
                this._cover = this._createCover();
                if( this._cover ) this.appendChild( this._cover );
            }

            this.firstChild.center();
            Element.show( this );
            this.firstChild.focus();
            this.firstChild.center();
        },
        hide : function() {
            Element.hide( this );
        },
        _createCover : function() {
            if( base.UI.currentBrowser() != base.UI.browserTypes.ie ) return null;

            var result = Object.extend( document.createElement("iframe"), {
                src : "/blank.html"
            } );
            Object.extend( result.style, {
                width : "100%", height : "100%", zIndex : -1,
                opacity : "0.0", "-moz-opacity" : "0.0", filter : "alpha(opacity=0)"
            } );

            return result;
        }
    } );

    return el;
}