<?php
namespace member;

// コントローラー、アクションによる操作ログ定義
return array(
    // LoginController.php
    __NAMESPACE__ . '\Controller\Login-login'   => '[ログイン] 画面表示',
    __NAMESPACE__ . '\Controller\Login-auth'    => '[ログイン] ログイン試行',
    __NAMESPACE__ . '\Controller\Login-logout'  => '[ログイン] ログアウト',
    // AccountController.php
    __NAMESPACE__ . '\Controller\Account-index' => '[登録情報確認] 画面表示',
    __NAMESPACE__ . '\Controller\Account-confirm' => '[登録情報設定変更確認] 画面表示',
    __NAMESPACE__ . '\Controller\Account-save' => '[登録情報設定変更確認] この内容で登録するボタン押下処理',
    __NAMESPACE__ . '\Controller\Account-changecsv' => '[登録情報設定変更] 画面表示',
    // AjaxController.php
//     __NAMESPACE__ . '\Controller\Ajax-getPostalData' => '[注文登録内容の修正] 郵便番号から住所を取得する処理',                 // Ajaxにより、郵便番号から住所を取得する
    __NAMESPACE__ . '\Controller\Ajax-requestCancel' => '[取引情報詳細] 注文キャンセル依頼',                                   // Ajaxにより、指定の注文データのキャンセル依頼を行う
    __NAMESPACE__ . '\Controller\Ajax-requestCancelCancel' => '[取引履歴検索] キャンセル取消',
    //     __NAMESPACE__ . '\Controller\Ajax-checkPassword' => '[] 現在のアカウントのパスワードマッチングを行う',                     // ※Controller、scriptから呼ばれていない※  Ajaxにより、現在のアカウントのパスワードマッチングを行う
//     __NAMESPACE__ . '\Controller\Ajax-getSearchResultOrder' => '[結果カラム編集ダイアログ] 履歴検索結果のカラム設定を問い合わせる処理',   // Ajaxにより、履歴検索結果のカラムオーダーを問い合わせる
//     __NAMESPACE__ . '\Controller\Ajax-setSearchResultColumnModify' => '[結果カラム編集ダイアログ] 検索履歴結果のカラムスキーマを設定する処理',  // Ajaxにより、履歴検索結果のカラムオーダーを設定する
//     __NAMESPACE__ . '\Controller\Ajax-setSearchResultColumnDisplay' => '[] 検索結果スキーマに対し、指定のカラムの表示状態を変更する処理',  // ※Controller、scriptから呼ばれていない※  Ajaxにより、検索結果スキーマに対し、指定のカラムの表示状態を変更する処理
//     __NAMESPACE__ . '\Controller\Ajax-resetSearchResultColumnSchema' => '[結果カラム編集ダイアログ] 検索結果スキーマをデフォルトにリセットする処理', // Ajaxにより、検索結果スキーマをデフォルトにリセットする
//     __NAMESPACE__ . '\Controller\Ajax-getOrderSummaries' => '[メニュー] 与信中および伝票登録待ちの情報を返す処理',                                     // Ajaxにより、世心中および伝票登録待ちの情報を返す
//     __NAMESPACE__ . '\Controller\Ajax-entorderidcheck' => '[] 指定された任意注文番号を検索する処理',   // ※Controller、scriptから呼ばれていない※  Ajaxにより、指定された任意注文番号を検索する処理
//     __NAMESPACE__ . '\Controller\Ajax-dupconfig' => '[] 登録ボタンのチェック状態を保存する処理',               // Ajaxにより、登録ボタンのチェック状態を保存する処理
    // ChangeCsvController.php
    __NAMESPACE__ . '\Controller\ChangeCsv-index' => '[各情報のCSV設定変更] 登録ボタン押下処理',
    // ClaimController.php
    __NAMESPACE__ . '\Controller\Claim-index' => '[ご利用明細] 画面表示',
    __NAMESPACE__ . '\Controller\Claim-download' => '[ご利用明細] CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Claim-billissue' => '[ご利用明細] 請求書印刷ボタン押下処理',
    __NAMESPACE__ . '\Controller\Claim-confirmNews' => '[立替速報確認] 画面表示',
    // IndexController.php
    __NAMESPACE__ . '\Controller\Index-index' => '[メニュー] 画面表示',
    __NAMESPACE__ . '\Controller\Index-download' => '[サンプルファイルのダウンロード] 画面表示',
    // MergeController.php
    __NAMESPACE__ . '\Controller\Merge-index' => '[請求取りまとめ注文一覧] 画面の表示(listへのエイリアス)',
    __NAMESPACE__ . '\Controller\Merge-list' => '[請求取りまとめ注文一覧] 画面の表示',
    __NAMESPACE__ . '\Controller\Merge-listbysite' => '[サイト名ごとの注文一覧] 画面の表示',
    __NAMESPACE__ . '\Controller\Merge-confirm' => '[取りまとめ情報確認／取りまとめキャンセル情報確認] 画面の表示',
    __NAMESPACE__ . '\Controller\Merge-separateconfirm' => '[全取りまとめ情報] 画面表示',
    __NAMESPACE__ . '\Controller\Merge-save' => '[取りまとめ情報確認／取りまとめキャンセル情報確認／全取りまとめ情報] 実行するボタン押下処理',
    __NAMESPACE__ . '\Controller\Merge-back' => '[取りまとめ情報確認／取りまとめキャンセル情報確認／全取りまとめ情報] 修正するボタン押下処理',
    // MonthlyController.php
    __NAMESPACE__ . '\Controller\Monthly-index' => '[ご利用明細（月別）] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-stampimage' => '[ご利用明細（月別）] 認印の表示',                   //
    __NAMESPACE__ . '\Controller\Monthly-download' => '[ご利用明細（月別）] CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Monthly-billissue' => '[ご利用明細（月別）] 請求書印刷ボタン押下処理',
    // OrderCancelController.php
    __NAMESPACE__ . '\Controller\OrderCancel-registCsv' => '[注文キャンセル（CSV一括登録）] 画面表示',
    __NAMESPACE__ . '\Controller\OrderCancel-confirmCsv' => '[一括注文キャンセル　CSV登録確認／一括注文キャンセル　CSV登録エラー] 画面表示',
    __NAMESPACE__ . '\Controller\OrderCancel-saveCsv' => '[一括注文キャンセル　CSV登録確認] 登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\OrderCancel-completeCsv' => '[一括注文キャンセル　登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\OrderCancel-download' => '[注文キャンセル（CSV一括登録）] 雛形をダウンロードボタン押下処理',
    // OrderController.php
    __NAMESPACE__ . '\Controller\Order-index' => '[注文登録（個別登録）] 画面表示(orderへのエイリアス)',
    __NAMESPACE__ . '\Controller\Order-order' => '[注文登録（個別登録）] 画面表示',
    __NAMESPACE__ . '\Controller\Order-confirm' => '[注文登録（個別登録） 確認] 画面表示',
    __NAMESPACE__ . '\Controller\Order-save' => '[注文登録（個別登録） 確認] この内容で登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Order-complete' => '[注文登録完了（個別登録）] 画面表示',
    __NAMESPACE__ . '\Controller\Order-orderCsv' => '[注文登録（CSV一括登録）] 画面表示',
    __NAMESPACE__ . '\Controller\Order-confirmCsv' => '[CSV登録確認／CSV登録エラー] 画面表示',
    __NAMESPACE__ . '\Controller\Order-saveCsv' => '[CSV登録確認] 登録するボタン押下処理',
    __NAMESPACE__ . '\Controller\Order-completeCsv' => '[注文登録完了（CSV一括登録）] 画面表示',
    __NAMESPACE__ . '\Controller\Order-download' => '[注文登録（個別登録）／注文登録（CSV一括登録）] 雛形をダウンロードボタン押下処理',
    __NAMESPACE__ . '\Controller\Order-edit' => '[注文登録内容の修正] 画面表示',
    __NAMESPACE__ . '\Controller\Order-editconfirm' => '[注文登録内容の修正 確認] 画面表示',
    __NAMESPACE__ . '\Controller\Order-editdone' => '[注文登録内容の修正 確認] この内容で登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Order-completeEdit' => '[登録内容修正完了] 画面表示',
//     __NAMESPACE__ . '\Controller\Order-enablesite' => '[注文登録（個別登録）] 受付サイト選択時処理',                                    // Ajaxにより必須項目アイコンの表示、非表示
    // RwclaimController.php
    __NAMESPACE__ . '\Controller\Rwclaim-list' => '[請求書発行(同梱待ちリスト)] 画面表示',
    __NAMESPACE__ . '\Controller\Rwclaim-up' => '[請求書発行(同梱待ちリスト)] 印刷済に更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Rwclaim-csvoutput' => '[請求書発行(同梱待ちリスト)] CSV出力ボタン押下処理',                                        // AjaxによりCSV出力の処理
    __NAMESPACE__ . '\Controller\Rwclaim-print' => '[請求書発行(同梱待ちリスト)] 印刷ボタン押下',                                         // Ajaxにより印刷の処理
    __NAMESPACE__ . '\Controller\Rwclaim-upbs' => '[請求書発行(同梱待ちリスト)] 別送に更新ボタン押下',                                    // Ajaxにより別送に更新の処理
    __NAMESPACE__ . '\Controller\Rwclaim-dcsv' => '[請求書発行(同梱待ちリスト)] CSV出力処理',
    __NAMESPACE__ . '\Controller\Rwclaim-csvsetting' => '[請求書　CSV設定] 画面表示',
    __NAMESPACE__ . '\Controller\Rwclaim-update' => '[請求書　CSV設定] CSV出力項目設定ボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rwclaim-init' => '[請求書　CSV設定] 初期設定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Rwclaim-changecsv' => '[登録情報設定変更] 画面表示',
    // SearchController.php
    __NAMESPACE__ . '\Controller\Search-index' => '[取引履歴検索] 画面表示(searchへのエイリアス)',
    __NAMESPACE__ . '\Controller\Search-search' => '[取引履歴検索] 画面表示',
    __NAMESPACE__ . '\Controller\Search-result' => '[取引履歴検索結果] 画面表示',
    __NAMESPACE__ . '\Controller\Search-detail' => '[取引情報詳細] 画面表示',
    __NAMESPACE__ . '\Controller\Search-download' => '[取引履歴検索結果] 結果をダウンロードボタン押下処理',
    __NAMESPACE__ . '\Controller\Search-quick' => '[ヘッダメニューバー] 検索ボタン押下処理',
    __NAMESPACE__ . '\Controller\Search-cancel' => '[取引情報詳細] キャンセルボタン押下処理',
    __NAMESPACE__ . '\Controller\Search-noguarantee' => '[取引情報詳細] 無保証に変更',
    // ShippingController.php
    __NAMESPACE__ . '\Controller\Shipping-index' => '[配送伝票入力（個別入力）] 画面表示(registへのエイリアス)',
    __NAMESPACE__ . '\Controller\Shipping-regist' => '[配送伝票入力（個別入力）] 画面表示',
    __NAMESPACE__ . '\Controller\Shipping-confirm' => '[入力内容の確認] 画面表示',
    __NAMESPACE__ . '\Controller\Shipping-save' => '[入力内容の確認] この内容で登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Shipping-complete' => '[配送伝票入力完了] 画面表示',
    __NAMESPACE__ . '\Controller\Shipping-download' => '[配送伝票入力（個別入力）] 一覧をダウンロードボタン押下処理',
    __NAMESPACE__ . '\Controller\Shipping-registCsv' => '[配送伝票入力（CSV一括入力）] 画面表示',
    __NAMESPACE__ . '\Controller\Shipping-changeCsv' => '[配送伝票修正（CSV一括入力）] 画面表示',
    __NAMESPACE__ . '\Controller\Shipping-confirmCsv' => '[登録内容の確認／一括配送伝票入力　CSV登録エラー] 画面表示',
    __NAMESPACE__ . '\Controller\Shipping-confirmChangeCsv' => '[配送伝票修正（CSV一括入力）] 登録実行ボタン押下処理',
    __NAMESPACE__ . '\Controller\Shipping-saveCsv' => '[登録内容の確認] この内容で登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Shipping-completeChangeCsv' => '[配送伝票修正完了] 画面表示',

);
