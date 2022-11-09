var refwin = null;
var bfoseq = null;

// 定型コメント選択画面を開く
function openFixedNoteSelectForm(url, windowname, oseq, width, height) {
    if (bfoseq && oseq != bfoseq) {
        unloadFixedNoteSelectForm();
    }

    if (!refwin || refwin.closed) {
        var features = "location=no, menubar=no, status=no, scrollbars=no, toolbar=no";
        if (width) {
            if (window.screen.width > width)
                features += ",left=" + (window.screen.width - width) / 2;
            else
                width = window.screen.width;
            features += ",width=" + width;
        }
        if (height) {
            if (window.screen.height > height)
                features += ",top=" + (window.screen.height - height) / 2;
            else
                height = window.screen.height;
            features += ",height=" + height;
        }
        refwin = window.open(url, windowname, features);
        bfoseq = oseq;
    }
    else {
        refwin.focus();
    }
}

//定型コメント選択画面をアンロード
function unloadFixedNoteSelectForm() {
    if (!(!refwin || refwin.closed)) {
        refwin.close();
        refwin = null;
    }
}

// 備考へ定型コメント挿入(UseType1:注文詳細画面)
// (val : yyyy-mm-dd(半スペ)hh:mm(半スペ)担当者（半スペ）＋入力したコメント内容)
function setNoteUseType1(val) {
    // 備考へ設定
    var setobj = "Note";
    var nowval = window.opener.document.getElementById(setobj).value;
    window.opener.document.getElementById(setobj).value = val + "\n" + nowval;
}

//備考へ定型コメント挿入(UseType2:社内与信画面)
//(val : yyyy-mm-dd(半スペ)hh:mm(半スペ)担当者（半スペ）＋入力したコメント内容)
function setNoteUseType2(val, noteNo) {
    // 備考へ設定
    var setobj = "Incre_Note" + noteNo;
    var nowval = window.opener.document.getElementById(setobj).value;
    window.opener.document.getElementById(setobj).value = val + "\n" + nowval;
}
