-- ↓↓↓移行想定
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','auth', 'hash_salt', 'b7988057af195bdd73d86c735b3a0e3dbe7d5aea', 'パスワードハッシュに使用するSALT値', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','taxconf', '[DEFAULT]', '5:31500', '2014年3月31日以前の消費税率と印紙代適用金額', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','taxconf', '2014-04-01', '8:54000', '2014年4月1日以降の消費税率と印紙代適用金額', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','css', 'OemId2', '/* -------------- member ----------*/ /* グローバル */  /* ボディ */ body {  background-color: white;  color: black; }  /* ページヘッダ */ .application_header {  color: black;  border: solid #003C2C 0px;  border-bottom-width: 5...', 'OEMID:2用カスタムCSS', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','imageex:submenu_arrow', 'OemId2', 'R0lGODlhDwAGAMIAAAQ+LJSupNzi5GyOhLTGxPz+/P///wAAACH5BAEAAAYALAAAAAAPAAYAAAMWCLrc1VCZGaIKM4sosjfDMnwkARBkAgA7', 'OEMID:2向けのサブメニュー画像', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','css', 'OemId3', '/* -------------- member ----------*/ /* グローバル */  /* ボディ */ body {  background-color: white;  color: black; }  /* ページヘッダ */ .application_header {  color: black;  border: solid #245f9c 0px;  border-bottom-width: 5...', 'OEMID:3のCSSデータ', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','imageex:submenu_arrow', 'OemId3', 'R0lGODlhDwAGAMIAACRenKS61OTq9ISixMTS5Pz+/P///wAAACH5BAEAAAYALAAAAAAPAAYAAAMWCLrc1VCZGaIKM4sosjfDMnwkARBkAgA7', 'OEMID:3向けのサブメニュー画像', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','jnbconf', 'DefaultBankName', 'ジャパンネット銀行', 'JNB設定：デフォルトのJNB銀行名', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','jnbconf', 'DefaultBankCode', '0033', 'JNB設定：デフォルトのJNB金融機関コード', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','jnbconf', 'ReleaseAfterReceiptInterval', '30', 'JNB設定：入金済み口座の開放待ち日数', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','jnbconf', 'ForceReleaseOverReclaim7LimitInterval', '65', 'JNB設定：再請求7期限超過後の強制口座解放までの猶予日数', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','jnbconf', 'AllowRestoreReturnedAccounts', '', 'JNB設定：返却済み口座復活機能の利用許可', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','jnbconf', 'AllowDebugReceiptForm', '', 'JNB設定：デバッグ用入金通知シミュレーターの利用許可', NOW(), 9, NOW(), 9, '1');
-- INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','jnbconf', 'AllowFullUrlToDebugReceiptForm', '', 'JNB設定：入金通知シミュレーターの送信先URLにフルURLを許容するかの設定', NOW(), 9, NOW(), 9, '1');
-- ↑↑↑移行想定
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'BusinessDate', '', '業務日付（バッチの実行時に利用する想定）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'LongJournalDays', '60', '長期伝票登録期間日数（停滞アラートで使用する）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'LongConfirmArrivalDays', '60', '長期着荷確認待ち期間日数（停滞アラートで使用する。デフォルト３ヶ月（長期未着荷））', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'LongLoginDays', '60', '長期未ログイン期間日数（停滞アラートで使用する）', NOW(), 9, NOW(), 9, '1');

INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'OrderDefaultDaysPast', '60', '注文登録標準期間日数（過去）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'OrderDefaultDaysFuture', '180', '注文登録標準期間日数（未来）', NOW(), 9, NOW(), 9, '1');






INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'AutoCreditLimitAmount', '1000000', '自動与信限度額', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CreditCriterion', '', '与信判定基準', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CreditOrderUseAmount', '20000', '与信時注文利用額（ex. 300000円）', NOW(), 9, NOW(), 9, '1');

INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'AutoCreditDateFrom', '', '与信自動化有効期間FROM', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'AutoCreditDateTo', '', '与信自動化有効期間TO', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CreditNgDispDays', '', '与信NG表示期間日数（ex. 8日）', NOW(), 9, NOW(), 9, '1');



INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'LongEnterpriseClaimedDays', '60', '加盟店請求長期繰越日数閾値（デフォルト 60日）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MembershipAgreement', '', 'マイページ会員規約', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'ReClaimCautionMessageA', '', '請求書再発行注意メッセージＡ表示終了日数', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'ReClaimCautionMessageB', '', '請求書再発行注意メッセージＢ表示開始日数', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'DummyJournalNumber', '__dumy_jurnal_number__', 'ダミー用伝票番号', NOW(), 9, NOW(), 9, '1');


INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'TempFileDir', './data/temp', 'ファイル一時保存ディレクトリ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'BtoBCreditLimitAmount', '300000', 'BtoB与信限度額', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CombinedListDays', '7', '名寄せリスト表示期間（日数）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'OrderMypageValidDays', '150', '注文マイページ有効期間日数（初回請求期限より経過した日数を指定）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageReissueClaimPattern', '1', 'マイページ請求書再発行対象（再発行可能な閾値）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CbPostalCode', '160-0023', '請求書出力用－郵便番号', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CbUnitingAddress', '東京都新宿区西新宿7-7-30 小田急柏木ビル 8F', '請求書出力用－住所', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CbNameKj', '株式会社キャッチボール', '請求書出力用－会社名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CbPhone', '03-5332-3490', '請求書出力用－電話番号', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'mypage','url', 'hash_salt', 'ge2shzb85uwUNs92ajmKyJNrQHKEibsTFpgEVQBM', 'マイページ本登録URLハッシュに使用するSALT値', NOW(), 9, NOW(), 9, '1');



INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','order_item', 'order_item_carriage', '__ORDER_ITEM_CARRIAGE__', '注文時の商品送料の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','order_item', 'order_item_charge', '__ORDER_ITEM_CHARGE__', '注文時の店舗手数料の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','order_item', 'validate_on_client', '1', 'クライアントサイドで入力値検証を行うかのフラグ', NOW(), 9, NOW(), 9, '1');











INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','paging', 'search', '50', '', NOW(), 9, NOW(), 9, '1');







INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','thread_pool', 'order.group_name', 'api-order-rest', '注文登録API：スレッドグループ名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','thread_pool', 'order.thread_limit', '10', '注文登録API：同時実行スレッド数上限', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','thread_pool', 'order.lockwait_timeout', '120', '注文登録API：ロック獲得待ちタイムアウト', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','thread_pool', 'order.lock_retry_interval', '1', '注文登録API：ロック再獲得間隔', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.searchzip', '/tools/searchzip.php', '郵便番号検索', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.realcallscore', '/generalsvc/cprc', '電話コール結果に関するポイントの取得', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.realsendmailscore', '/generalsvc/cprsm', 'リアル送信チェックに関するポイントの取得', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.sendcheck', '/generalsvc/sendcheck', '与信用チェックメール送信', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.sendexam', '/generalsvc/sendexam', '事業者審査結果メール送信', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.sendtest', '/generalsvc/sendtest', '事業者宛　送達確認用メール送信', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.unote', '/generalsvc/unote', '備考更新', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.registblk', '/generalsvc/registblk', 'ブラック登録', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.registexc', '/generalsvc/registexc', '優良顧客登録', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.registcancelcancel', '/generalsvc/registcancelcancel', 'キャンセル取消', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.registcancelconfirmarrival', '/generalsvc/registcancelconfirmarrival', '着荷確認取消', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.chkaddress', '/generalsvc/chkaddress', '類似住所検索', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'url.returnbill', '/generalsvc/sendreturnbill', '請求書不達メール送信', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'prop.chkaddrstrnum', '9', '類似住所検索時の検索文字数', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','tools', 'orderstatus.style', 'default', '類似住所検索結果および着荷確認画面の色分け用CSSファイル名。css/orderstats 内の拡張子を除いた名前を指定する。この項目が未設定の場合は「default」に読み替えられる', NOW(), 9, NOW(), 9, '1');




INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','business', 'pay.limitdays', '14', '支払期限は請求日の↓日後', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','business', 'pay.limitdays2', '10', '支払期限は請求日の↓日後', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','business', 'pay.damagerate', '0', '遅延損害金利率（次期システムから遅延損害金は取らないため、０固定）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','business', 'pay.ignore_damage_for_reclaim1', 'true', '再請求1への遅延損害金適用を無視するか', NOW(), 9, NOW(), 9, '0');


INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','upload', 'root_directory', '', 'アップロードディレクトリのルートパス', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','paging', 'rwarvlcfm', '100', 'キーがコントローラ名に一致するように定義', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','paging', 'rwcredit', '20', 'キーがコントローラ名に一致するように定義', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','paging', 'rwdmi', '100', 'キーがコントローラ名に一致するように定義', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','paging', 'credit', '100', 'キーがコントローラ名に一致するように定義', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','paging', 'searchf', '200', 'キーがコントローラ名に一致するように定義', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','ent_sbsettings', 'enable_settings', 'true', '同梱ツール関連の設定を有効にするか', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','at_payment', 'corp_code', '7116', 'コンビニ用企業コード', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','at_payment', 'cust_num', '152', '郵便振替用顧客番号', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','at_payment', 'enable_stamp_fee', 'true', '印紙代精算', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','credit_judge', 'enterpriseid', '3772', '与信に加算点を設定する事業者', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'save_dir', '.', 'ファイル保存ディレクトリ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'timeout_time', '5', 'タイムアウト', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'default_average_unit_price_rate', '2.5', '与信時平均単価倍率初期値', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'bypass.ilu', 'false', 'モジュールバイパス設定', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'bypass.jintec', 'false', 'モジュールバイパス設定', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'url.ilu', 'http://xxx.xxx.xxx/ILUYoshin/Inspect.asmx/CreditInspection', '審査システム関連接続先URL：ILUメイン', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'url.ilu_pattern_master', 'http://xxx.xxx.xxx/ILUYoshin/Inspect.asmx/GetServiceStatus', '審査システム関連接続先URL：ILUパターンマスター', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'url.jintec', 'https://xxx.xxx.xxx/t2ktelinfo.do', '審査システム関連接続先URL：ジンテック（https://www.webt2k.com/t2ktelinfo.do）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'jintec_cid', '', '', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'jintec_id', '', '', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'jintec_pass', '', '', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'ilu_id', '1', 'ILUのシステムID', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'debug_mode', '0', '審査システム関連デバッグモード', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'debug_data_dir', '.', '通信モジュール用デバッグデータディレクトリ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'debug_file.ilu', 'system_connect_ok.xml', '通信モジュール用デバッグデータファイル名：ILUメインファイル名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'debug_file.ilu_pattern_master', 'JudgeMaster.xml', '通信モジュール用デバッグデータファイル名：ILUパターンマスターファイル名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'debug_file.jintec', 'jintec.xml', '通信モジュール用デバッグデータファイル名：ジンテックファイル名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'user_amount_over', '1', '与信時注文利用額機能有効フラグ 0:無効 1:有効', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'CustomerListImport', 'http://xxx.xxx.xxx/ILUYoshin/Inspect.asmx/CustomerListImport', '顧客情報編集URL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'GetCustomerListImportResult', 'http://xxx.xxx.xxx/ILUYoshin/Inspect.asmx/GetCustomerListImportResult', '顧客情報編集結果取得URL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'SetAggregationData', 'http://xxx.xxx.xxx/ILUYoshin/Inspect.asmx/SetAggregationData', '顧客名寄せ編集URL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'SetPaymentData', 'http://xxx.xxx.xxx/ILUYoshin/Inspect.asmx/SetPaymentData', '支払い情報編集URL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','cj_api', 'GetCustomerInformation', 'http://xxx.xxx.xxx/ILUYoshin/Inspect.asmx/GetCustomerInformation', '顧客情報取得URL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','smbc_relation', 'service_base_url', 'https://xxx.xxx.xxx/cooperation', '決済ステーションのベースURL。テスト用URLはhttps://www.paymentstation.jp/cooperationtest', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','smbc_relation', 'service_text_enc', 'sjis-win', '決済ステーション接続に使用するテキストエンコードを指定', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','smbc_relation', 'service_req_timeout', '10', '決済ステーションHTTPリクエスト時のタイムアウトを指定。デフォルト値は10（秒）', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','smbc_relation', 'service_req_retry', '2', '決済ステーションHTTPリクエストのリトライ回数。デフォルト値は2回', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','smbc_relation', 'service_adapter', 'Http', '決済ステーションへ接続するためのアダプタ名。デフォルト値はHttp', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','smbc_relation', 'service_interface_path.register', 'sf/at/ksuketsukeinforeg/uketsukeInforRegInit.do', '決済ステーション請求情報登録へ接続するインターフェイスパス。デフォルト値はsf/at/ksuketsukeinforeg/uketsukeInforRegInit.do', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','smbc_relation', 'service_interface_path.cancel', 'sf/cd/skuinfokt/skuinfoKakutei.do', '決済ステーション請求取消へ接続するインターフェイスパス。デフォルト値はsf/cd/skuinfokt/skuinfoKakutei.do', NOW(), 9, NOW(), 9, '0');

INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','printing', 'client.enable_export', 'false', '印刷ツールのエクスポート機能を有効にするかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','printing', 'client.manual_commit', 'false', 'エクスポート完了後のコミットを手動で行うかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','printing', 'upload_directory', 'printing_explorted', 'エクスポートファイルのアップロード先ディレクトリ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','printing', 'prefix.wellnet', 'wellnet_csv', 'エクスポートファイル名のプレフィックス設定', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'cbadmin','printing', 'allow_fc_layout_setting', 'true', 'サイト単位での初回請求書封書用紙利用設定を許可するかのフラグ', NOW(), 9, NOW(), 9, '1');







INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','order_item', 'order_item_carriage', '__ORDER_ITEM_CARRIAGE__', '注文時の商品送料の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','order_item', 'order_item_charge', '__ORDER_ITEM_CHARGE__', '注文時の店舗手数料の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','order_item', 'order_item_taxclass', '__ORDER_ITEM_TAXCLASS__', '注文時の外税額の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','order_item', 'validate_on_client', '1', 'クライアントサイドで入力値検証を行うかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'help_url', 'https://www.atobarai.jp/doc/help/help.html', 'ヘルプコンテンツのURL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'page_title_prefix', '【後払い.com】後払い決済管理システム', 'ページタイトルの共通プレフィックス', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'page_title_separator', ' : ', 'ページタイトルのセパレータ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'php_display_errors', '1', 'PHPエラーの出力を行うかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'debug_mode', '0', 'デバッグモードフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'smtp_server', 'localhost', 'SMTPサーバ設定', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'hide_unregisterable_orders', 'true', '個別伝票登録画面の初期状態で登録不可データを非表示にするかのフラグ', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','application_global', 'mail_address.ent_info_modified', 'customer@ato-barai.com', '事業者登録情報変更通知メール', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','paging', 'search', '50', 'ページングに関する設定', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'use_selfbilling', 'true', '請求書同梱ツールの利用をするかのシステムレベルの設定', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'update_journal_number_menu_id', 'header_menu_2', '請求書発行後の伝票登録番号更新向けメニューにバインドするヘッダメニューのID', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'payment_limit_days', '14', '同梱ツール向けの支払期限日数', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'ean128.maker_code', '908997', 'バーコード関連の設定：メーカーコード', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'ean128.corporate_code', '0777', 'バーコード関連の設定：コーポレートコード', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'threshold_version', '0.9', '利用可能な最低クライアントバージョン→ major.minor形式、build/revisionはチェックしない', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'target_list_limit', '250', '印刷対象リストの件数上限', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','selfbilling', 'shipping_sp_count', '30', '伝票番号更新対象の最大訴求日数（デフォルトは30）', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','combinedclaim', 'use_combinedclaim', 'true', '請求取りまとめを利用するかのシステムレベルの設定', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','combinedclaim', 'update_order_number_menu_id', 'header_menu_1', '請求取りまとめメニューにバインドするヘッダメニューのID', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','business', 'pay.limitdays', '14', '支払期限は請求日の↓日後', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'member','business', 'pay.limitdays2', '10', '支払期限は請求日の↓日後', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'cb_monthly_tighten_day', '1', 'cb月次締め日', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'cb_accounting_tighten_day', '3', 'cb会計締め日(第N営業日）', NOW(), 9, NOW(), 9, '1');










INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'orderpage','application_global', 'page_title_prefix', '注文マイページ', 'ページタイトルの共通プレフィックス', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'orderpage','application_global', 'page_title_separator', ' : ', 'ページタイトルのセパレータ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'orderpage','application_global', 'php_display_errors', '1', 'PHPエラーの出力を行うかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'orderpage','application_global', 'debug_mode', '0', 'デバッグモードフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'orderpage','application_global', 'smtp_server', 'xxx.xxx.xxx.xxx', 'SMTPサーバ設定', NOW(), 9, NOW(), 9, '1');










INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'mypage','application_global', 'page_title_prefix', '顧客マイページ', 'ページタイトルの共通プレフィックス', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'mypage','application_global', 'page_title_separator', ' : ', 'ページタイトルのセパレータ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'mypage','application_global', 'php_display_errors', '1', 'PHPエラーの出力を行うかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'mypage','application_global', 'debug_mode', '0', 'デバッグモードフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'mypage','application_global', 'smtp_server', 'localhost', 'SMTPサーバ設定', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'AccountingMonth', '', '会計月（YYYY-MM-DD)　　　　※DDは01固定', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemadmin','paging', 'searcho', '20', 'キーがコントローラ名に一致するように定義', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'mypage','identification', 'mailaddress', 'xxx@xxx.xxx', '身分証明書管理用PCのメールアドレス', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','order_item', 'order_item_carriage', '__ORDER_ITEM_CARRIAGE__', '注文時の商品送料の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','order_item', 'order_item_charge', '__ORDER_ITEM_CHARGE__', '注文時の店舗手数料の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','order_item', 'order_item_taxclass', '__ORDER_ITEM_TAXCLASS__', '注文時の外税額の商品名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','order_item', 'validate_on_client', '1', 'クライアントサイドで入力値検証を行うかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'help_url', '/../doc/help', 'ヘルプコンテンツのURL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'page_title_prefix', '後払い決済管理システム', 'ページタイトルの共通プレフィックス', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'page_title_separator', ' : ', 'ページタイトルのセパレータ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'php_display_errors', '1', 'PHPエラーの出力を行うかのフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'debug_mode', '0', 'デバッグモードフラグ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'smtp_server', 'localhost', 'SMTPサーバ設定', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'hide_unregisterable_orders', 'true', '個別伝票登録画面の初期状態で登録不可データを非表示にするかのフラグ', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','application_global', 'mail_address.ent_info_modified', 'customer@ato-barai.com', '事業者登録情報変更通知メール', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','paging', 'search', '50', 'ページングに関する設定', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'use_selfbilling', 'true', '請求書同梱ツールの利用をするかのシステムレベルの設定', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'update_journal_number_menu_id', 'header_menu_2', '請求書発行後の伝票登録番号更新向けメニューにバインドするヘッダメニューのID', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'payment_limit_days', '14', '同梱ツール向けの支払期限日数', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'ean128.maker_code', '908997', 'バーコード関連の設定：メーカーコード', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'ean128.corporate_code', '0777', 'バーコード関連の設定：コーポレートコード', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'threshold_version', '0.9', '利用可能な最低クライアントバージョン→ major.minor形式、build/revisionはチェックしない', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'target_list_limit', '250', '印刷対象リストの件数上限', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','selfbilling', 'shipping_sp_count', '30', '伝票番号更新対象の最大訴求日数（デフォルトは30）', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','combinedclaim', 'use_combinedclaim', 'true', '請求取りまとめを利用するかのシステムレベルの設定', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','combinedclaim', 'update_order_number_menu_id', 'header_menu_1', '請求取りまとめメニューにバインドするヘッダメニューのID', NOW(), 9, NOW(), 9, '0');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','business', 'pay.limitdays', '14', '支払期限は請求日の↓日後', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'oemmember','business', 'pay.limitdays2', '10', '支払期限は請求日の↓日後', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'AccountingDay', '3', '会計の締日（月の第xx営業日）', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'regist_mail_enterprise_host', '192.168.5.133', '加盟店申込メール メールサーバー接続先', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'regist_mail_enterprise_user', 'user', '加盟店申込メール 接続ＩＤ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'regist_mail_enterprise_password', 'user', '加盟店申込メール 接続パスワード', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'RemindDays', '730', '督促日数（貸し倒れ一覧表示時の経過日数初期値）', NOW(), 9, NOW(), 9, '1');
