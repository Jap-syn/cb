// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//
//  【 画像のPOPアップ 】  http://www.cman.jp
//
//   商用,改変,再配布はすべて自由ですですが、動作保証はありません
//
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//    maintenance history
//
//    Ver  Date        contents
//    1.0  2015/7/15   New
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//
//  使用方法
//
//  1.この下の【ユーザ設定】を確認＆設定してください
//
//  2.htmlの画像に以下の設定をしてください
//
//   「 onclick="cmanImgPop(this,'拡大する画像のURL') 」
//
//   (例1) 拡大する画像のURLを省略した場合
//         <img src="neko.jpg" width="10" height="20" border="0" onclick="cmanImgPop(this);">
//         → "neko.gif"画像をクリックしたら、neko.jpgを拡大して表示する
//
//   (例2) 拡大する画像のURLを指定する場合
//         <img src="neko.jpg" width="10" height="20" border="0" onclick="cmanImgPop(this,'http://www.cman.jp/sample.jpg');">
//         → "neko.gif"画像をクリックしたら、http://www.cman.jp/sample.jpgを拡大して表示する
//
//
//   【注意】
//     引数やユーザ設定内容についてはノーチェックです
//     解析しやすいようにコメントを多く入れています。
//     JavaScriptのファイルサイズを削減する場合は、コメントを消してください。
//
//
//   詳細は以下でご確認ください
//    https://web-designer.cman.jp/javascript_ref/image/pop_open/
//
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

var cmanImgPopCns = {};

// ┏━【ユーザ設定】━━━━━━━━━━━━━━━━┓
// ┃                                                ┃
// ┃  以下の[a]～[h]の設定をしてください            ┃
// ┃                                                ┃
// ┃┌──────────────────────┐┃
// ┃│ [a] オリジナルサイズを最大表示する比率 (%) │┃
// ┃│     範囲 : 1(%) ～ 1000(%)                 │┃
// ┃│     制約 : 「枠幅×2」+「影幅」を含む      │┃
cmanImgPopCns["imgMaxSize"] = 100;
// ┃└──────────────────────┘┃
// ┃┌──────────────────────┐┃
// ┃│ [b] ブラウザ幅に対する最大表示幅の比率 (%) │┃
// ┃│     範囲 : 0(%) ～ 100(%)                  │┃
// ┃│     制約 : 0(%)は、無視して[a]で表示       │┃
cmanImgPopCns["brwMaxSize"] = 70;
// ┃└──────────────────────┘┃
// ┃┌──────────────────────┐┃
// ┃│ [c] 画像を枠の幅（px値)                    │┃
// ┃│     範囲 : 0(px) ～ 50(px)                 │┃
// ┃│     制約 : 0(px)は、枠が出ない             │┃
cmanImgPopCns["borderSize"] = 3;
// ┃└──────────────────────┘┃
// ┃┌──────────────────────┐┃
// ┃│ [d] 画像を枠の色（16進)                    │┃
// ┃│     範囲 : #000000 ～ #ffffff(#+6桁指定)   │┃
// ┃│     制約 : 範囲外は枠幅を0(px)になる       │┃
cmanImgPopCns["borderColor"] = "#cfcfcf";
// ┃└──────────────────────┘┃
// ┃┌──────────────────────┐┃
// ┃│ [e] 影を付ける幅 (px値)                    │┃
// ┃│     範囲 : 0(px) ～ 20(px)                 │┃
// ┃│     制約 : 0(px)は、影が出ない             │┃
cmanImgPopCns["shadowSize"] = 2;
// ┃└──────────────────────┘┃
// ┃┌──────────────────────┐┃
// ┃│ [f] 影の色（16進)                          │┃
// ┃│     範囲 : #000000 ～ #ffffff              │┃
// ┃│     制約 : 範囲外は影幅を0(px)になる       │┃
cmanImgPopCns["shadowColor"] = "#999999";
// ┃└──────────────────────┘┃
// ┃┌──────────────────────┐┃
// ┃│ [g] 1回に移動する係数                      │┃
// ┃│     範囲    : 1 ～10（ 小さい : 細かい ）  │┃
// ┃│     制約    : 10は移動表示しない           │┃
cmanImgPopCns["moveWidth"] = 1;
// ┃└──────────────────────┘┃
// ┃┌──────────────────────┐┃
// ┃│ [h] 移動する時間間隔（ミリ秒）             │┃
// ┃│     範囲    : 1 ～99（ 小さい : 早い ）    │┃
cmanImgPopCns["moveTime"] = 10;
// ┃└──────────────────────┘┃
// ┗━━━━━━━━━━━━━━━━━━━━━━━━┛



// ━━━ 初期設定 ━━━━━━━━━━━━━━━━━━━━━━━━━━
cmanImgPopCns["popDiv"]    = 'cmanPopDiv';
cmanImgPopCns["msg"]       = 'cmanPopMsg';
cmanImgPopCns["movePar1"]  = '0.009,0.027,0.055,0.091,0.136,0.191,0.255,0.327,0.409,0.5,0.591,0.673,0.745,0.809,0.864,0.909,0.945,0.973,0.991,1';
cmanImgPopCns["movePar2"]  = '0.011,0.033,0.067,0.111,0.167,0.233,0.311,0.4,0.5,0.6,0.689,0.767,0.833,0.889,0.933,0.967,0.989,1';
cmanImgPopCns["movePar3"]  = '0.014,0.042,0.083,0.139,0.208,0.292,0.389,0.5,0.611,0.708,0.792,0.861,0.917,0.958,0.986,1';
cmanImgPopCns["movePar4"]  = '0.018,0.054,0.107,0.179,0.268,0.375,0.5,0.625,0.732,0.821,0.893,0.946,0.982,1';
cmanImgPopCns["movePar5"]  = '0.024,0.071,0.143,0.238,0.357,0.5,0.643,0.762,0.857,0.929,0.976,1';
cmanImgPopCns["movePar6"]  = '0.033,0.1,0.2,0.333,0.5,0.667,0.8,0.9,0.967,1';
cmanImgPopCns["movePar7"]  = '0.05,0.15,0.3,0.5,0.7,0.85,0.95,1';
cmanImgPopCns["movePar8"]  = '0.083,0.25,0.5,0.75,0.917,1';
cmanImgPopCns["movePar9"]  = '0.167,0.5,0.833,1';
cmanImgPopCns["movePar10"] = '1';
var cmanImgPopWk  = {};
//cmanImgPopWk["moveIdx"]       移動テーブルIDX
//cmanImgPopWk["start*"]        移動開始の位置、大きさ
//cmanImgPopWk["end*"]          移動終了の位置、大きさ
//cmanImgPopWk["now*"]          移動中のの位置、大きさ
//cmanImgPopWk["objPop"];       画像の表示POPエリア
//cmanImgPopWk["objStartImg"]   クリックされた画像
//cmanImgPopWk["objOrgImg"]     拡大する画像
//cmanImgPopWk["moveTimer"]     画像移動用タイマ


// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//  imageのクリックの関数 (POPアップを開く)
//
//    引数1 : クリックされたオブジェクト(this)
//    引数2 : POP表示する画像のURL
//            指定が無いい場合は、引数1のsrcが使用される
//
//    戻り  : なし
//
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function cmanImgPop(argObj,argUrl){

    cmanImgPopWk["objStartImg"] = argObj;


    // ----- 画像オリジナルサイズの取得 -------------------------------
    if(delete cmanImgPopWk["objOrgImg"]){
            delete cmanImgPopWk["objOrgImg"];
    }
    cmanImgPopWk["objOrgImg"] = new Image();

    if((typeof argUrl === 'undefined')||(argUrl == '')){
        cmanImgPopWk["objOrgImg"].src = argObj.src;
    }else{
        cmanImgPopWk["objOrgImg"].src = argUrl;
    }

    cmanImgPopWk["waitStop"]  = 0;
    cmanImgPopWk["wiatCnt"]   = 0;

    cmanImgPopWk["objOrgImg"].onload = function() {
        cmanImgPopWk["waitStop"] = 1;
    }

    cmanImgPopWk["objOrgImg"].onerror = function() {
        cmanImgPopWk["waitStop"] = 2;
    }

    cmanImgPopWk["waitTimer"] = setTimeout("cmanImgWait()",100);

}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//     画像の取得を待つ
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function cmanImgWait(){

    var wLoopEnd = 0;

    if (cmanImgPopWk["waitTimer"]) {
        clearTimeout(cmanImgPopWk["waitTimer"]);
    }


    if(cmanImgPopWk["waitStop"] == 1){
        wLoopEnd = 1;
        cmanImgOpen();
        return true;
    }

    if(cmanImgPopWk["waitStop"] == 2){
        wLoopEnd = 1;
        return true;
    }

    cmanImgPopWk["wiatCnt"]++;
    if(cmanImgPopWk["wiatCnt"] > 30){
        wLoopEnd = 1;
    }

    if(wLoopEnd == 0){
        cmanImgPopWk["waitTimer"] = setTimeout("cmanImgWait()",100);
    }

}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//     ポップアップ画像を開く
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function cmanImgOpen(){

    // ----- ブラウザサイズの取得 -------------------------------------
    var wBrwW = document.documentElement.clientWidth;
    var wBrwH = document.documentElement.clientHeight;


    // ----- 画像の倍率指定よりMAXサイズの計算 ------------------------
    var wImgMaxW = Math.round( cmanImgPopWk["objOrgImg"].width  * ( cmanImgPopCns["imgMaxSize"] / 100 ));
    var wImgMaxH = Math.round( cmanImgPopWk["objOrgImg"].height * ( cmanImgPopCns["imgMaxSize"] / 100 ));


    // ----- ブラウザのの倍率指定よりMAXサイズの計算 ------------------
    var wBrwMaxW = 0;
    var wBrwMaxH = 0;
    if(cmanImgPopCns["brwMaxSize"] != 0){
        wBrwMaxW = Math.round( wBrwW * ( cmanImgPopCns["brwMaxSize"] / 100 )) - (cmanImgPopCns["borderSize"] * 2) - (cmanImgPopCns["shadowSize"] * 2);
        wBrwMaxH = Math.round( wBrwH * ( cmanImgPopCns["brwMaxSize"] / 100 )) - (cmanImgPopCns["borderSize"] * 2) - (cmanImgPopCns["shadowSize"] * 2);
    }


    // ----- 表示する画像サイズの計算 ---------------------------------
    if((wBrwMaxW != 0)&&(wBrwMaxW < wImgMaxW)){        // 横幅で小さい方を取得
        cmanImgPopWk["endW"] = wBrwMaxW;
        cmanImgPopWk["endH"] = Math.round( wBrwMaxW * (cmanImgPopWk["objOrgImg"].height / cmanImgPopWk["objOrgImg"].width) );
    }else{
        cmanImgPopWk["endW"] = wImgMaxW;
        cmanImgPopWk["endH"] = wImgMaxH;
    }

    if((wBrwMaxH != 0)&&(wBrwMaxH < cmanImgPopWk["endH"])){        // 横幅で計算したサイズをさらに高さで再計算
        cmanImgPopWk["endW"] = Math.round( wBrwMaxH * (cmanImgPopWk["objOrgImg"].width / cmanImgPopWk["objOrgImg"].height) );
        cmanImgPopWk["endH"] = wBrwMaxH;
    }


    // ----- 表示位置の計算 -------------------------------------------
    cmanImgPopWk["endY"] = Math.floor( ( wBrwH - ( cmanImgPopWk["endH"] + (cmanImgPopCns["borderSize"] * 2) + (cmanImgPopCns["shadowSize"] * 2) ) ) / 2);
    cmanImgPopWk["endY"] += document.documentElement.scrollTop || document.body.scrollTop;
    cmanImgPopWk["endY"] -= 200; // 上部へのずらし
    cmanImgPopWk["endX"] = Math.floor( ( wBrwW - ( cmanImgPopWk["endW"] + (cmanImgPopCns["borderSize"] * 2) + (cmanImgPopCns["shadowSize"] * 2) ) ) / 2);
    cmanImgPopWk["endX"] += document.documentElement.scrollLeft || document.body.scrollLeft;


    // 元画像の情報取得
    cmanImgPopWk["startY"] = Math.floor(cmanImgPopWk["objStartImg"].getBoundingClientRect().top);
    cmanImgPopWk["startY"] += document.documentElement.scrollTop || document.body.scrollTop;
    cmanImgPopWk["startX"] = Math.floor(cmanImgPopWk["objStartImg"].getBoundingClientRect().left);
    cmanImgPopWk["startX"] += document.documentElement.scrollLeft || document.body.scrollLeft;
    cmanImgPopWk["startH"] = cmanImgPopWk["objStartImg"].height;
    cmanImgPopWk["startW"] = cmanImgPopWk["objStartImg"].width;


    // ----- 表示枠の作成＆割り当て -----------------------------------
    if(document.getElementById(cmanImgPopCns["popDiv"])){
        document.getElementById(cmanImgPopCns["popDiv"]).style.display = "none";
    }else{

        var wEle = document.createElement("div");   // 新規に要素（タグ）を生成
        wEle.style.display = "none";
        wEle.id = cmanImgPopCns["popDiv"];
        document.body.appendChild(wEle);            // このページ (document.body) の最後に生成した要素を追加
    }

    cmanImgPopWk["objPop"] = document.getElementById(cmanImgPopCns["popDiv"]);


    // ----- 画像の表示 -----------------------------------------------
    var wImgOut    = '<img src="' + cmanImgPopWk["objOrgImg"].src + '" style="width:100%;height:100%;" border="0">';
    wImgOut  += '<div style="color: blue;position: absolute;top: 0;display: none;opacity: 0.55;background-color: #ccffcc;font-weight:bold;padding: 3px;font-size:9pt;" id="' + cmanImgPopCns["msg"] + '">ClickでClose</div>';
    cmanImgPopWk["objPop"].innerHTML = wImgOut;


    // ----- 閉じるイベント追加 ---------------------------------------
    if(cmanImgPopWk["objPop"].addEventListener){
        cmanImgPopWk["objPop"].addEventListener("click", cmanImgClose, false);
        cmanImgPopWk["objPop"].addEventListener("mouseover", cmanImgMsgOn, false);
        cmanImgPopWk["objPop"].addEventListener("mouseout", cmanImgMsgOff, false);
    }else{
        cmanImgPopWk["objPop"].eventTarget.attachEvent("onclick", cmanImgClose);
        cmanImgPopWk["objPop"].addEventListener("onmouseover", cmanImgMsgOn, false);
        cmanImgPopWk["objPop"].addEventListener("onmouseout", cmanImgMsgOff, false);
    }


    // ----- 表示枠のstyle初期編集 ------------------------------------
    cmanImgPopWk["objPop"].style.position  = "absolute";
    cmanImgPopWk["objPop"].style.top       = cmanImgPopWk["startY"] + "px";
    cmanImgPopWk["objPop"].style.left      = cmanImgPopWk["startX"] + "px";
    cmanImgPopWk["objPop"].style.height    = cmanImgPopWk["startH"] + "px";
    cmanImgPopWk["objPop"].style.width     = cmanImgPopWk["startW"] + "px";
    cmanImgPopWk["objPop"].style.cursor    = "pointer";
    cmanImgPopWk["objPop"].style.border    = 'none';
    cmanImgPopWk["objPop"].style.boxShadow = 'none';
    cmanImgPopWk["objPop"].style.display   = "";

    // ----- 表示開始位置の設定 ---------------------------------------
    cmanImgPopWk["nowX"] = cmanImgPopWk["startY"];
    cmanImgPopWk["nowY"] = cmanImgPopWk["startX"];
    cmanImgPopWk["nowW"] = cmanImgPopWk["startH"];
    cmanImgPopWk["nowH"] = cmanImgPopWk["startW"];


    // ----- 画像POP表示開始 ------------------------------------------
    cmanImgPopWk["msgOff"]  = 1;
    cmanImgPopWk["moveIdx"] = 0;
    cmanImgPopWk["moveTimer"] = setTimeout("cmanImgMove(1)",cmanImgPopCns["moveTime"]);

    return true;
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//     ポップアップ画像を閉じる
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function cmanImgClose(){

    cmanImgPopWk["msgOff"]  = 1;
    cmanImgMsgOff();

    // ----- 表示開始位置の設定 ---------------------------------------
    cmanImgPopWk["nowX"] = cmanImgPopWk["endY"];
    cmanImgPopWk["nowY"] = cmanImgPopWk["endX"];
    cmanImgPopWk["nowW"] = cmanImgPopWk["endH"];
    cmanImgPopWk["nowH"] = cmanImgPopWk["endW"];

    // ----- 画像POP表示開始 ------------------------------------------
    cmanImgPopWk["moveIdx"] = cmanImgPopCns["movePar" + cmanImgPopCns["moveWidth"]].split(",").length;  // 最終IDXを設定
    cmanImgPopWk["moveTimer"] = setTimeout("cmanImgMove(-1)",cmanImgPopCns["moveTime"]);

    return true;
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//     ポップアップの移動
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function cmanImgMove(argIdxAdd){


    if (cmanImgPopWk["moveTimer"]) {
        clearTimeout(cmanImgPopWk["moveTimer"]);
    }

    // ----- 移動のパーセント取得 ------------------------------------
    var wParTbl = cmanImgPopCns["movePar" + cmanImgPopCns["moveWidth"]].split(",");
    var wPar = wParTbl[cmanImgPopWk["moveIdx"]];

    // ----- 次に開く画像サイズを計算 --------------------------------
    cmanImgPopWk["nowBorder"] = Math.floor(cmanImgPopCns["borderSize"] * wPar);
    cmanImgPopWk["nowShadow"] = Math.floor(cmanImgPopCns["shadowSize"] * wPar);

    // 次の移動位置（縦）計算
    cmanImgPopWk["objPop"].style.top       = Math.floor((cmanImgPopWk["endY"] - cmanImgPopWk["startY"]) * wPar + cmanImgPopWk["startY"]) + "px";
    cmanImgPopWk["objPop"].style.left      = Math.floor((cmanImgPopWk["endX"] - cmanImgPopWk["startX"]) * wPar + cmanImgPopWk["startX"]) + "px";
    cmanImgPopWk["objPop"].style.height    = Math.floor((cmanImgPopWk["endH"] - cmanImgPopWk["startH"]) * wPar + cmanImgPopWk["startH"]) + "px";
    cmanImgPopWk["objPop"].style.width     = Math.floor((cmanImgPopWk["endW"] - cmanImgPopWk["startW"]) * wPar + cmanImgPopWk["startW"]) + "px";
    cmanImgPopWk["objPop"].style.border    = ( (cmanImgPopWk["nowBorder"] == 0) ? 'none' : cmanImgPopWk["nowBorder"] + "px solid " + cmanImgPopCns["borderColor"] );
    cmanImgPopWk["objPop"].style.boxShadow = ( (cmanImgPopWk["nowShadow"] == 0) ? 'none' : cmanImgPopWk["nowShadow"] + "px " + cmanImgPopWk["nowShadow"] + "px " + cmanImgPopCns["shadowColor"] );

    cmanImgPopWk["moveIdx"] += argIdxAdd;


    // 終了 or 次ループ
    if((cmanImgPopWk["moveIdx"] > wParTbl.length)||(cmanImgPopWk["moveIdx"] < 0)){
        // タイマクリア
        clearTimeout(cmanImgPopWk["moveTimer"]);
        cmanImgPopWk["msgOff"] = 0;

        if(argIdxAdd < 0){
            cmanImgPopWk["objPop"].parentNode.removeChild(cmanImgPopWk["objPop"]);
            for(var wKey in cmanImgPopWk){
                delete cmanImgPopWk[wKey];
            }
        }
    }else{
        // 次のループ
        cmanImgPopWk["moveTimer"] = setTimeout("cmanImgMove(" + argIdxAdd + ")",cmanImgPopCns["moveTime"]);
    }

    return true;
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//     メッセージ表示
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function cmanImgMsgOn(){
    if(cmanImgPopWk["msgOff"] == 0){
        document.getElementById(cmanImgPopCns["msg"]).style.display="";
    }
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//     メッセージ非表示
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function cmanImgMsgOff(){
    document.getElementById(cmanImgPopCns["msg"]).style.display="none";
}