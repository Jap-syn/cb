// 指定select要素の現在の情報を取得する
function listInfo(ele) {
	var
		list = $(ele),
		size = list.options.length,
		current = list.selectedIndex >= 0 ? list.options[list.selectedIndex] : null,
		curIndex = list.selectedIndex,
		required = list.selectedIndex < 0 ? false : list.options[list.selectedIndex].className == 'required';
	return {
		list : list,
		size : size,
		current : current,
		curIndex : curIndex,
		required : required
	};
}

// 「対象項目」リストで項目が選択された
function validListSelected() {
	var info = listInfo('valid-list');

	// アクションボタンの状態を更新
	$('up-to-item').disabled = !(info.current && info.curIndex > 0);
	$('down-to-item').disabled = !(info.current && info.curIndex < info.size - 1);
	$('item-to-invalid').disabled = (!info.current || info.required);
}

// 「非対象項目」リストで項目が選択された
function invalidListSelected() {
	var info = listInfo('invalid-list');

	// アクションボタンの状態を更新
	$('item-to-valid').disabled = !(info.current);
	$('item-to-valid-all').disabled = !(info.size > 0);
}

// 「対象項目」リストの選択項目を1つ上に移動する
function upToItem() {
	if($('up-to-item').disabled) return;

	var
		info = listInfo('valid-list'),
		// 現在の1つ上の項目
		valid = info.list.options[info.curIndex - 1];

	if(valid) {
		info.list.insertBefore(info.current, valid);
		// ボタンの状態を更新
		updateButtons();
	}
}

// 「対象項目」リストの選択項目を1つ下に移動する
function downToItem() {
	if($('down-to-item').disabled) return;

	var
		info = listInfo('valid-list'),
		// 現在の1つ下の項目
		valid = info.list.options[info.curIndex + 1];

	if(valid) {
		info.list.insertBefore(valid, info.current);
		// ボタンの状態を更新
		updateButtons();
	}
}

// 「対象項目」リストの選択項目を「非対象項目」へ移動する
function toInvalidItem() {
	if($('item-to-invalid').disabled) return;

	var
		validInfo = listInfo('valid-list'),
		invalidInfo = listInfo('invalid-list'),
		// 挿入場所（＝現在選択中の項目値より大きい最小値を持つ項目）
		valid = $A(invalidInfo.list.options).find(function(opt) {
			return (Number(opt.value) > Number(validInfo.current.value));
		});

	if(valid) {
		// 途中に差し込む
		invalidInfo.list.insertBefore(validInfo.current, valid);
	} else {
		// 末尾に差し込む
		invalidInfo.list.appendChild(validInfo.current);
	}

	var lastIndex = validInfo.curIndex, validInfo = listInfo('valid-list');
	validInfo.list.selectedIndex = lastIndex < validInfo.size ? lastIndex : lastIndex - 1;

	// ボタンの状態を更新
	updateButtons();
}

// 「非対象項目」リストの選択項目を「対象項目」リストへ移動する
function toValidItem() {
	if($('item-to-valid').disabled) return;

	var
		validInfo = listInfo('valid-list'),
		invalidInfo = listInfo('invalid-list'),
		// 「対象項目」リストの選択項目
		valid = validInfo.list.options[validInfo.curIndex + 1];

	if(validInfo.curIndex >= 0 && valid) {
		// 選択項目があったらその直後に挿入
		validInfo.list.insertBefore(invalidInfo.current, valid);
	} else {
		// 選択項目がないので末尾へ挿入
		validInfo.list.appendChild(invalidInfo.current);
	}

	// ボタンの状態を更新
	updateButtons();
}

// 「非対象項目」リストの全項目を「対象項目」リストへ移動する
function toValidItemAll() {
	if($('item-to-valid-all').disabled) return;

	var
		validInfo = listInfo('valid-list'),
		invalidInfo = listInfo('invalid-list');

	// 常に末尾へ逐次挿入する
	$A(invalidInfo.list.options).each(function(opt) {
		validInfo.list.appendChild(opt);
	});

	// ボタンの状態を更新
	updateButtons();
}

// 登録
function submit() {
	var
		i = 1,
		validlist = $('valid-list'),
		invalidlist = $('invalid-list'),
		validArray = new Array(),
		invalidArray = new Array();

	$A(validlist.options).each(function(opt) {
		validArray[i++] = opt.value;
	});

	i = 1;
	$A(invalidlist.options).each(function(opt) {
		invalidArray[i++] = opt.value;
	});

	document.getElementById('validListData').value = validArray;
	document.getElementById('invalidListData').value = invalidArray;
}

// 各select要素の状態に応じて各種ボタンの状態を更新する
function updateButtons() {
	validListSelected();
	invalidListSelected();
}

// 初期化処理
function init() {
	// 各種イベントのバインド
	[
		{ id : 'up-to-item', meth : upToItem },
		{ id : 'down-to-item', meth : downToItem },
		{ id : 'item-to-invalid', meth : toInvalidItem },
		{ id : 'item-to-valid', meth : toValidItem },
		{ id : 'item-to-valid-all', meth : toValidItemAll },
		{ id : 'submit', meth : submit }
	].each(function(conf) {
		Event.observe($(conf.id), 'click', conf.meth);
	});
	[
		{ id : 'valid-list', meth : validListSelected },
		{ id : 'invalid-list', meth : invalidListSelected },
	].each(function(conf) {
		Event.observe($(conf.id), 'change', conf.meth);
	});

	// ボタンの状態を更新
	updateButtons();
}

// windowの準備ができたら初期化を実行
window.entInfo = [];
Event.observe(window, 'load', init);
