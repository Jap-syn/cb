<?php
namespace oemadmin;

// コントローラー、アクションによる操作ログ定義
return array(
    // --------------------
    // ClaimController
    // --------------------
    __NAMESPACE__ . '\Controller\Claim-index'   => '[債権明細] 画面表示',
    // --------------------
    // EnterpriseController
    // --------------------
    __NAMESPACE__ . '\Controller\Enterprise-list'   => '[事業者一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-detail'   => '[事業者詳細] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-dcsv'   => '[事業者一覧] CSVダウンロード',
    // --------------------
    // GpController
    // --------------------
    __NAMESPACE__ . '\Controller\Gp-notice'   => '[お知らせ設定] 画面表示',
    __NAMESPACE__ . '\Controller\Gp-up'   => '[お知らせ設定] 設定ボタン押下',
    __NAMESPACE__ . '\Controller\Gp-mailtf'   => '[メールテンプレート] 画面表示',
    __NAMESPACE__ . '\Controller\Gp-mailup'   => '[メールテンプレート] 設定ボタン押下',
    // --------------------
    // IndexController
    // --------------------
    __NAMESPACE__ . '\Controller\Index-index'   => '[メニュー] 画面表示',
    // --------------------
    // LoginController
    // --------------------
    __NAMESPACE__ . '\Controller\Login-login'   => '[ログイン] 画面表示',
    __NAMESPACE__ . '\Controller\Login-auth'   => '[ログイン] ログインボタン押下、認証処理',
    __NAMESPACE__ . '\Controller\Login-logout'   => '[ログイン] ログアウトボタン押下、ログアウト処理',
    // --------------------
    // MonthlyController
    // --------------------
    __NAMESPACE__ . '\Controller\Monthly-settlement'   => '[月次明細] 精算書画面表示',
    __NAMESPACE__ . '\Controller\Monthly-store'   => '[月次明細] 店舗別精算明細画面表示',
    __NAMESPACE__ . '\Controller\Monthly-storedetail'   => '[月次明細] 店舗別精算明細 店舗詳細画面表示',
    __NAMESPACE__ . '\Controller\Monthly-trading'   => '[月次明細] 取引別精算明細画面表示',
    __NAMESPACE__ . '\Controller\Monthly-cancel'   => '[月次明細] キャンセル返金明細画面表示',
    __NAMESPACE__ . '\Controller\Monthly-canceldetail'   => '[月次明細] キャンセル返金明細 店舗詳細画面表示',
    __NAMESPACE__ . '\Controller\Monthly-payingdatadl'   => '[月次明細] 精算書をCSVダウンロード',
    // --------------------
    // OemController
    // --------------------
    __NAMESPACE__ . '\Controller\Oem-detail'   => '[登録情報] 画面表示',
    __NAMESPACE__ . '\Controller\Oem-edit'   => '[登録情報] OEM編集画面表示',
    __NAMESPACE__ . '\Controller\Oem-confirm'   => '[登録情報] OEM編集画面 確認ボタン押下',
    __NAMESPACE__ . '\Controller\Oem-back'   => '[登録情報] OEM編集確認画面 戻るボタン押下',
    __NAMESPACE__ . '\Controller\Oem-save'   => '[登録情報] OEM編集確認画面 確定ボタン押下',
    __NAMESPACE__ . '\Controller\Oem-completion'   => '[登録情報] OEM編集完了画面表示',
    // --------------------
    // OperatorController
    // --------------------
    __NAMESPACE__ . '\Controller\Operator-chgpw'   => '[パスワード変更] 画面表示、変更ボタン押下(同アクション)',
    // --------------------
    // PayingController
    // --------------------
    __NAMESPACE__ . '\Controller\Paying-list'   => '[立替確認] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-forecast2'   => '[立替確認] 立替予測画面表示',
    __NAMESPACE__ . '\Controller\Paying-elist'   => '[立替確認] 立替実行済み画面表示',
    __NAMESPACE__ . '\Controller\Paying-dlist2'   => '[立替確認] 立替実行済み－事業者リスト画面表示',
    __NAMESPACE__ . '\Controller\Paying-dlist3'   => '[立替確認] 立替確認－事業者リスト画面表示',
    __NAMESPACE__ . '\Controller\Paying-transdatadl'   => '[立替確認] 振込データダウンロードボタン押下',
    __NAMESPACE__ . '\Controller\Paying-trnlist'   => '[立替確認] 注文明細画面表示',
    __NAMESPACE__ . '\Controller\Paying-cnllist'   => '[立替確認] キャンセル明細画面表示',
    __NAMESPACE__ . '\Controller\Paying-stamplist'   => '[立替確認] 印紙代明細画面表示',
    __NAMESPACE__ . '\Controller\Paying-paybacklist'   => '[立替確認] 立替精算戻し明細画面表示',
    // --------------------
    // PdfController
    // --------------------
    __NAMESPACE__ . '\Controller\Pdf-monthlysettlement'   => '[月次明細] 精算書をPDFでダウンロード',
    // --------------------
    // ResourceController
    // --------------------
    // 操作ではないため記載なし
    // --------------------
    // RworderController
    // --------------------
    __NAMESPACE__ . '\Controller\Rworder-detail'   => '[注文情報] 注文情報詳細画面表示',
    __NAMESPACE__ . '\Controller\Rworder-up'   => '[注文情報] 更新ボタン押下',
    // --------------------
    // SearcheController
    // --------------------
    __NAMESPACE__ . '\Controller\Searche-form'   => '[事業者検索] 検索フォーム画面表示',
    __NAMESPACE__ . '\Controller\Searche-search'   => '[事業者検索] 検索ボタン押下、事業者一覧画面表示',
    // --------------------
    // SearchoController
    // --------------------
    __NAMESPACE__ . '\Controller\Searcho-form'   => '[注文検索] 検索フォーム画面表示',
    __NAMESPACE__ . '\Controller\Searcho-qform'   => '[注文検索] 簡易検索フォーム画面表示',
    __NAMESPACE__ . '\Controller\Searcho-search'   => '[注文検索] 検索ボタン押下、注文データ検索結果画面表示',
    __NAMESPACE__ . '\Controller\Searcho-qsearch'   => '[注文検索] 検索ボタン押下、簡易注文データ検索結果画面表示',
    __NAMESPACE__ . '\Controller\Searcho-dcsv'   => '[注文検索] 注文データ検索結果 検索結果をCSVでダウンロード',
    __NAMESPACE__ . '\Controller\Searcho-qdcsv'   => '[注文検索] 簡易注文データ検索結果 検索結果をCSVでダウンロード',
    // --------------------
    // SiteController
    // --------------------
    __NAMESPACE__ . '\Controller\Site-list'   => '[サイト一覧] サイト一覧画面表示',

);
