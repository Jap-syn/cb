<?php
namespace cbadmin;

// コントローラー、アクションによる操作ログ定義
// ここに記載している内容のみ操作ログを取得する
return array(
    // LoginController.php
    __NAMESPACE__ . '\Controller\Login-login'   => '[ログイン] 画面表示',
    __NAMESPACE__ . '\Controller\Login-auth'    => '[ログイン] ログイン試行',
    __NAMESPACE__ . '\Controller\Login-logout'  => '[ログイン] ログアウト',
    // AgencyController.php
    __NAMESPACE__ . '\Controller\Agency-list' => '[代理店一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Agency-form' => '[代理店登録] 画面表示',
    __NAMESPACE__ . '\Controller\Agency-edit' => '[代理店編集] 画面表示',
    __NAMESPACE__ . '\Controller\Agency-confirm' => '[代理店登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Agency-back' => '[代理店登録内容確認] 戻るリンク押下処理',
    __NAMESPACE__ . '\Controller\Agency-save' => '[代理店登録内容確認] 確認リンク押下処理',
    __NAMESPACE__ . '\Controller\Agency-completion' => '[代理店登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Agency-dcsv' => '[代理店一覧] CSVダウンロード処理',
    // AgencyfeesummaryController.php
    __NAMESPACE__ . '\Controller\Agencyfeesummary-list' => '[代理店手数料確認] 画面表示',
    __NAMESPACE__ . '\Controller\Agencyfeesummary-dcsv1' => '[代理店手数料確認] 振込ダウンロード処理',
    __NAMESPACE__ . '\Controller\Agencyfeesummary-dcsv2' => '[代理店手数料確認] 代理店別CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Agencyfeesummary-dcsv3' => '[代理店手数料確認] 加盟店別注文別CSVダウンロード処理',
    // ApirelController.php
    __NAMESPACE__ . '\Controller\Apirel-index' => '[APIユーザー → サイト 設定] 画面表示',
    __NAMESPACE__ . '\Controller\Apirel-apioemselect' => '[APIユーザー → サイト 関連設定] 画面表示',
    __NAMESPACE__ . '\Controller\Apirel-entoemselect' => '[サイト → APIユーザー 関連設定] 画面表示',
    __NAMESPACE__ . '\Controller\Apirel-api2ent' => '[APIユーザー → サイト 関連設定] 画面表示',
    __NAMESPACE__ . '\Controller\Apirel-ent2api' => '[サイト → APIユーザー 関連設定] 画面表示',
    // ApiuserController.php
    __NAMESPACE__ . '\Controller\Apiuser-list' => '[APIユーザー 一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Apiuser-detail' => '[APIユーザー詳細情報] 画面表示',
    __NAMESPACE__ . '\Controller\Apiuser-add' => '[APIユーザー登録] 画面表示',
    __NAMESPACE__ . '\Controller\Apiuser-edit' => '[APIユーザー変更] 画面表示',
    __NAMESPACE__ . '\Controller\Apiuser-confirm' => '[APIユーザー登録／変更内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Apiuser-back' => '[APIユーザー登録／変更内容確認] 戻るリンク押下処理',
    __NAMESPACE__ . '\Controller\Apiuser-save' => '[APIユーザー登録／変更内容確認] 確認ボタン押下処理',
    __NAMESPACE__ . '\Controller\Apiuser-status' => '[APIユーザー詳細情報] APIユーザの有効/無効を切り替える処理',
    // AuthlogController.php
// ↓ここから↓ authlogの適切な画面名が不明なため、コントローラーActionのみ記述し、保留 ///////////////////
//     __NAMESPACE__ . '\Controller\Authlog-index' => '[]',
//     __NAMESPACE__ . '\Controller\Authlog-raw' => '[]',
//     __NAMESPACE__ . '\Controller\Authlog-byid' => '[]',
//     __NAMESPACE__ . '\Controller\Authlog-byip' => '[]',
//     __NAMESPACE__ . '\Controller\Authlog-byhash' => '[]',
//     __NAMESPACE__ . '\Controller\Authlog-rank' => '[]',
//     __NAMESPACE__ . '\Controller\Authlog-rankbyip' => '[]',
//     __NAMESPACE__ . '\Controller\Authlog-rankbyhash' => '[]',
// ↑ここまで↑ authlogの適切な画面名が不明なため、コントローラーActionのみ記述し、保留 ///////////////////
    // CalendarController.php
    __NAMESPACE__ . '\Controller\Calendar-index' => '[カレンダーメンテナンス] 画面表示',
    // CancelController.php
    __NAMESPACE__ . '\Controller\Cancel-list' => '[キャンセル確認] 画面表示',
    __NAMESPACE__ . '\Controller\Cancel-done' => '[キャンセル確認] キャンセル確定ボタン押下処理',
    // CancelregisterController.php
    __NAMESPACE__ . '\Controller\Cancelregister-cancelregisterform' => '[債権返却一括キャンセル] 画面表示',
    __NAMESPACE__ . '\Controller\Cancelregister-cancelregister' => '[債権返却一括キャンセル] 実行ボタン押下処理',
    __NAMESPACE__ . '\Controller\Cancelregister-generallycancelregisterform' => '[通常一括キャンセル] 画面表示',
    __NAMESPACE__ . '\Controller\Cancelregister-generallycancelregister' => '[通常一括キャンセル] 実行ボタン押下処理',
    // ClaimAccountController.php
    __NAMESPACE__ . '\Controller\ClaimAccount-index' => '[請求口座一覧] 画面表示(listへのエイリアス)',
    __NAMESPACE__ . '\Controller\ClaimAccount-list' => '[請求口座一覧] 画面表示',
    __NAMESPACE__ . '\Controller\ClaimAccount-detail' => '[請求口座詳細情報] 画面表示',
    __NAMESPACE__ . '\Controller\ClaimAccount-edit' => '[請求口座登録] 画面表示',
    __NAMESPACE__ . '\Controller\ClaimAccount-confirm' => '[請求口座登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\ClaimAccount-save' => '[請求口座登録内容確認] 登録内容確定ボタン押下処理',
    __NAMESPACE__ . '\Controller\ClaimAccount-complete' => '[請求口座登録完了] 画面表示',
    // CombinedclaimController.php
    __NAMESPACE__ . '\Controller\Combinedclaim-list' => '[請求取りまとめ事業者一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-add' => '[請求取りまとめ設定登録] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-edit' => '[請求取りまとめ編集] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-confirm' => '[請求取りまとめ設定登録確認] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-back' => '[請求取りまとめ設定登録確認] 戻るリンク押下処理',
    __NAMESPACE__ . '\Controller\Combinedclaim-save' => '[請求取りまとめ設定登録確認] 確認リンク押下処理',
    __NAMESPACE__ . '\Controller\Combinedclaim-detail' => '[請求取りまとめ設定詳細情報] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-orderlist' => '[請求取りまとめ注文一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-orderlistbysite' => '[請求取りまとめ注文一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-mergeconfirm' => '[請求取りまとめ注文一覧設定確認] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-mergeseparateconfirm' => '[請求取りまとめ注文一覧設定確認] 画面表示',
    __NAMESPACE__ . '\Controller\Combinedclaim-mergesave' => '[請求取りまとめ注文一覧設定確認] 確認リンク押下処理',
    __NAMESPACE__ . '\Controller\Combinedclaim-mergeback' => '[請求取りまとめ注文一覧設定確認] 戻るリンク押下',
    // CommentRegistController.php
    __NAMESPACE__ . '\Controller\CommentRegist-index' => '[コメント登録・修正] 画面表示',
    __NAMESPACE__ . '\Controller\CommentRegist-up' => '[コメント登録・修正] 更新処理',
    // CreditController.php
    __NAMESPACE__ . '\Controller\Credit-pointform' => '[社内与信条件設定] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-pointup' => '[社内与信条件設定] ポイント設定更新処理',
    __NAMESPACE__ . '\Controller\Credit-condition' => '[与信条件文言設定] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-conditioncate1' => '[与信条件文言設定] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-new' => '[与信条件新規登録] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-confirm' => '[与信条件新規登録確認] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-save' => '[与信条件新規登録確認] この内容で登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Credit-complete' => '[与信条件新規登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-search' => '[与信条件検索] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-result' => '[与信条件一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-savemodify' => '[与信条件一覧] この内容で更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Credit-impcriterionform' => '[与信ｼｽﾃﾑ判定基準CSV登録・修正] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-impcriterionconfirm' => '[与信ｼｽﾃﾑ判定基準CSV登録・修正確認] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-importform' => '[社内与信条件CSV登録・修正] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-importconfirm' => '[与信条件CSV登録・修正確認] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-execimport' => '[与信条件CSV登録・修正確認] この内容で登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Credit-importform2' => '[与信条件新規インポート] 画面表示',
    __NAMESPACE__ . '\Controller\Credit-execimport2' => '[与信条件新規インポート] インポート処理',
    // CreditlossController.php
    __NAMESPACE__ . '\Controller\Creditloss-list' => '[貸し倒れ入金処理] 画面表示',
    __NAMESPACE__ . '\Controller\Creditloss-save' => '[貸し倒れ入金処理登録] 画面表示',
    __NAMESPACE__ . '\Controller\Creditloss-dcsv' => '[貸し倒れ入金処理] CSVダウンロード処理',
    // CreditTransferController.php
    __NAMESPACE__ . '\Controller\CreditTransfer-index' => '[口座振替期間設定] 画面表示',
    __NAMESPACE__ . '\Controller\CreditTransfer-save'  => '[口座振替期間設定] 確定処理',
    __NAMESPACE__ . '\Controller\CreditTransfer-alertlist'  => '[口座振替アラート一覧] 画面表示',
    __NAMESPACE__ . '\Controller\CreditTransfer-alertmerge'  => '[口座振替アラート一覧] 更新処理',
    // CreditvalueController.php
    __NAMESPACE__ . '\Controller\Creditvalue-index' => '[注文利用額] 画面表示',
    __NAMESPACE__ . '\Controller\Creditvalue-save' => '[注文利用額] OKボタン押下処理',
    // CustomerAdrsListController.php
    __NAMESPACE__ . '\Controller\CustomerAdrsList-list' => '[顧客統合 名寄せリスト] 画面表示',
    __NAMESPACE__ . '\Controller\CustomerAdrsList-dcsv' => '[顧客統合 名寄せリスト] CSVダウンロード処理',
    // CustomerController.php
    __NAMESPACE__ . '\Controller\Customer-managementdetail' => '[管理顧客詳細情報] 画面表示',
    __NAMESPACE__ . '\Controller\Customer-managementform' => '[管理顧客登録] 画面表示',
    __NAMESPACE__ . '\Controller\Customer-managementedit' => '[管理顧客編集] 画面表示',
    __NAMESPACE__ . '\Controller\Customer-managementconfirm' => '[管理顧客登録／編集] 確定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Customer-memberdetail' => '[事業者別顧客詳細情報] 画面表示',
    __NAMESPACE__ . '\Controller\Customer-memberform' => '[事業者別顧客登録] 画面表示',
    __NAMESPACE__ . '\Controller\Customer-memberedit' => '[事業者別顧客編集] 画面表示',
    __NAMESPACE__ . '\Controller\Customer-memberconfirm' => '[事業者別顧客編集] 確定ボタン押下処理',
    // CustomerInquiryController.php
    __NAMESPACE__ . '\Controller\CustomerInquiry-form' => '[顧客検索] 画面表示',
    __NAMESPACE__ . '\Controller\CustomerInquiry-search' => '[顧客一覧] 画面表示',
    __NAMESPACE__ . '\Controller\CustomerInquiry-detail' => '[顧客詳細] 画面表示',
    // CvsagentController.php
    __NAMESPACE__ . '\Controller\Cvsagent-list' => '[コンビニ収納代行会社一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Cvsagent-add' => '[コンビニ収納代行会社設定] 画面表示',
    __NAMESPACE__ . '\Controller\Cvsagent-edit' => '[コンビニ収納代行会社編集] 画面表示',
    __NAMESPACE__ . '\Controller\Cvsagent-save' => '[コンビニ収納代行会社設定／編集] 確定ボタン押下処理',
    // DelimethodController.php
    __NAMESPACE__ . '\Controller\Delimethod-up' => '[配送方法設定] この内容で更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Delimethod-list' => '[配送方法設定] 画面表示',
    // EnterpriseclaimController.php
    __NAMESPACE__ . '\Controller\Enterpriseclaim-form' => '[事業者別請求残高検索] 画面表示',
    __NAMESPACE__ . '\Controller\Enterpriseclaim-list' => '[事業者別請求残高一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Enterpriseclaim-detail' => '[事業者別入金明細] 画面表示',
    __NAMESPACE__ . '\Controller\Enterpriseclaim-edit' => '[事業者別請求残高入金] 画面表示',
    __NAMESPACE__ . '\Controller\Enterpriseclaim-save' => '[事業者別請求残高入金] 確定ボタン押下処理',
    // EnterpriseContractController.php
    __NAMESPACE__ . '\Controller\EnterpriseContract-list' => '[加盟店サイト契約情報一覧] 画面表示',
    __NAMESPACE__ . '\Controller\EnterpriseContract-download' => '[加盟店サイト契約情報一覧] CSVダウンロード処理',
    // EnterpriseController.php
    __NAMESPACE__ . '\Controller\Enterprise-list' => '[事業者一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-detail' => '[事業者詳細情報] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-form' => '[事業者登録] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-edit' => '[事業者登録] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-confirm' => '[事業者登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-back' => '[事業者登録内容確認] 戻るリンク押下処理',
    __NAMESPACE__ . '\Controller\Enterprise-save' => '[事業者登録内容確認] 確認ボタン押下後、画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-completion' => '[事業者登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-up' => '[事業者詳細情報] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Enterprise-campaign' => '[キャンペーン設定] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-campaigndone' => '[キャンペーン設定] キャンペーン登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Enterprise-dcsv' => '[事業者一覧] CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Enterprise-ucsv' => '[事業者一覧] CSV取込処理',
    __NAMESPACE__ . '\Controller\Enterprise-sbsetting' => '[請求書同梱ツール設定] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-sbsettingup' => '[請求書同梱ツール設定] 更新ボタン押下処理',
//     __NAMESPACE__ . '\Controller\Enterprise-sbprintablecount' => '[請求書同梱ツール設定] 同梱ツール向けアクセスキー別未印刷件数取得処理',  // 未印刷件数をAJaxで取得
    __NAMESPACE__ . '\Controller\Enterprise-resetacs' => '[請求ストップ解除処理] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprise-resetacsdone' => '[請求ストップ解除処理] 解除を実行ボタン押下処理',
    __NAMESPACE__ . '\Controller\Enterprise-acsupdate' => '[請求ストップ解除処理] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Enterprise-resetpsw' => '[事業者詳細情報] パスワードリセットリンク押下処理',
    __NAMESPACE__ . '\Controller\Enterprise-resetpswdone' => '[事業者詳細情報] パスワードリセットリンク押下完了処理',
//     __NAMESPACE__ . '\Controller\Enterprise-delimethodlist' => '[事業者登録] 配送方法一覧取得処理',                                     // 配送方法一覧取得をAjaxで取得
    // EnterprisedelivController.php
    __NAMESPACE__ . '\Controller\Enterprisedeliv-edit' => '[加盟店別配送先設定] 画面表示',
    __NAMESPACE__ . '\Controller\Enterprisedeliv-save' => '[加盟店別配送先設定] 登録ボタン押下処理',
//     __NAMESPACE__ . '\Controller\Enterprisedeliv-master' => '[加盟店別配送先設定] 配送方法マスターのデータ処理',            // 配送方法マスターのリストボックスをAJaxで変更する
//     __NAMESPACE__ . '\Controller\Enterprisedeliv-current' => '[加盟店別配送先設定] 使用する配送方法のデータ処理',           // 使用する配送方法のリストボックスをAJaxで変更する
    // EnterpriseOperatorController.php
    __NAMESPACE__ . '\Controller\EnterpriseOperator-form' => '[加盟店オペレーター登録] 画面表示',
    __NAMESPACE__ . '\Controller\EnterpriseOperator-edit' => '[加盟店オペレーター編集] 画面表示',
    __NAMESPACE__ . '\Controller\EnterpriseOperator-confirm' => '[加盟店オペレーター登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\EnterpriseOperator-save' => '[加盟店オペレーター登録内容確認] 確認ボタン押下処理',
    __NAMESPACE__ . '\Controller\EnterpriseOperator-completion' => '[加盟店オペレーター登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\EnterpriseOperator-list' => '[加盟店オペレーター一覧] 画面表示',
    __NAMESPACE__ . '\Controller\EnterpriseOperator-chgpw' => '[パスワード変更] OKボタン押下',
    // ErrorController.php
    __NAMESPACE__ . '\Controller\Error-error' => '[システムエラー] 画面表示',
    __NAMESPACE__ . '\Controller\Error-nop' => '[システムエラー] 権限エラー',                                                    // 権限がないため表示される画面だが、画面タイトル無し
    // ExternalDemandController.php
    __NAMESPACE__ . '\Controller\ExternalDemand-regist' => '[外部督促出力ファイルの取り込み(SMS)] 画面表示',
    __NAMESPACE__ . '\Controller\ExternalDemand-confirm' => '[外部督促出力ファイルの取り込み(SMS)] 登録実行ボタン押下処理',
    __NAMESPACE__ . '\Controller\ExternalDemand-error' => '[外部督促出力ファイルの取り込み(SMS)　エラー] 画面表示',
    __NAMESPACE__ . '\Controller\ExternalDemand-completion' => '[外部督促出力ファイルの取り込み(SMS)　完了] 画面表示',
    // GeneralsvcController.php
    __NAMESPACE__ . '\Controller\Generalsvc-unote' => '[注文情報] 備考更新処理',                                              // Ajaxで備考を更新する
    __NAMESPACE__ . '\Controller\Generalsvc-registblk' => '[注文情報] ブラック登録ボタン押下処理',                             // Ajaxでブラック登録処理の実行
    __NAMESPACE__ . '\Controller\Generalsvc-registexc' => '[注文情報] 優良顧客登録ボタン押下処理',                             // Ajaxで優良顧客登録処理の実行
    __NAMESPACE__ . '\Controller\Generalsvc-registcancelconfirmarrival' => '[注文情報] 着荷確認取消ボタン押下処理',            // Ajaxで着荷確認取消処理の実行
    __NAMESPACE__ . '\Controller\Generalsvc-sendcheck' => '[社内与信確定待ちリスト] 送信ボタン押下処理',                       // Ajaxで与信チェック用メールの送信処理の実行
    __NAMESPACE__ . '\Controller\Generalsvc-sendexam' => '[事業者詳細情報] 審査結果メール送信ボタン押下処理',                  // Ajaxで審査結果メール送信処理の実行
    __NAMESPACE__ . '\Controller\Generalsvc-sendtest' => '[事業者詳細情報] 送達確認用メール送信ボタン押下処理',                // Ajaxで送達確認用メール送信処理の実行
    __NAMESPACE__ . '\Controller\Generalsvc-sendreturnbill' => '[] 請求書不達メール送信処理',                           // Controllerに記述されているが、呼び出されている箇所が存在しない
    __NAMESPACE__ . '\Controller\Generalsvc-cprc' => '[] 電話結果クレジットポイントの取得処理',                         // Controllerに記述されているが、呼び出されている箇所が存在しない
    __NAMESPACE__ . '\Controller\Generalsvc-cprsm' => '[] メール送信チェック結果クレジットポイントの取得処理',          // Controllerに記述されているが、呼び出されている箇所が存在しない
    __NAMESPACE__ . '\Controller\Generalsvc-chkaddress' => '[] 類似住所/電話番号検索処理',                              // Controllerに記述されているが、呼び出されている箇所が存在しない
    __NAMESPACE__ . '\Controller\Generalsvc-searchzip' => '[代理店登録／代理店編集／管理顧客登録／管理顧客編集／事業者別顧客編集／事業者登録／OEM先登録] 検索ボタン押下処理',  // Ajaxで郵便番号から住所を検索する処理の実行
    __NAMESPACE__ . '\Controller\Generalsvc-searchsubscriber' => '[サイト登録／編集] 加入者固有コード検索処理',                // Ajaxで加入者固有コードを検索する
    // GpController.php
    __NAMESPACE__ . '\Controller\GpController-notice' => '[お知らせ設定] 画面表示',
    __NAMESPACE__ . '\Controller\GpController-up' => '[お知らせ設定] 設定ボタン押下処理',
    __NAMESPACE__ . '\Controller\GpController-mailtf' => '[メールテンプレート設定] 画面表示',
    __NAMESPACE__ . '\Controller\GpController-mailup' => '[メールテンプレート設定] 設定ボタン押下処理',
    __NAMESPACE__ . '\Controller\GpController-gpmstrf' => '[クラス番号未定義／督促分類／キャリア／最終回収手段] 画面表示',                        // 画面名がcaption連想配列(クラス番号未定義／督促分類／キャリア／最終回収手段)に依存する
    __NAMESPACE__ . '\Controller\GpController-gpmstru' => '[クラス番号未定義／督促分類／キャリア／最終回収手段] この内容で更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\GpController-note' => '[備考マスタ設定] 画面表示',
    __NAMESPACE__ . '\Controller\GpController-noteup' => '[備考マスタ設定] 設定ボタン押下処理',
    // HaiponController.php
    __NAMESPACE__ . '\Controller\Haipon-index' => '[配ポンインポートファイルの出力] 画面表示',
    __NAMESPACE__ . '\Controller\Haipon-export' => '[配ポンインポートファイル出力] 画面表示',
    __NAMESPACE__ . '\Controller\Haipon-impconfirm' => '[着荷確認 対象] 画面表示',
    __NAMESPACE__ . '\Controller\Haipon-import' => '[インポート完了中] 画面表示',
    __NAMESPACE__ . '\Controller\Haipon-impdone' => '[インポート完了] 画面表示',
    // IdlockController.php
    __NAMESPACE__ . '\Controller\Idlock-index' => '[IDレベルロックアウト一覧] 画面表示(idllistのエイリアス)',
    __NAMESPACE__ . '\Controller\Idlock-idlist' => '[IDレベルロックアウト一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Idlock-cllist' => '[クライアントレベルロックアウト一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Idlock-idlockrelease' => '[IDレベルロックアウト一覧] ロック解除実行',                                         // JavaScriptでdoReleaseを宣言しているが、呼び出している箇所がない
    __NAMESPACE__ . '\Controller\Idlock-cllockrelease' => '[クライアントレベルロックアウト一覧] ロック解除実行',                               // JavaScriptでdoReleaseを宣言しているが、呼び出している箇所がない
    // IndexController.php
    __NAMESPACE__ . '\Controller\Index-index' => '[後払い.com - トップ] 画面表示',
    // JnbController.php
    __NAMESPACE__ . '\Controller\Jnb-index' => '[JNB契約情報一覧] 画面表示(listのエイリアス)',
    __NAMESPACE__ . '\Controller\Jnb-list' => '[JNB契約情報一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-detail' => '[JNB登録内容詳細] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-new' => '[JNB新規登録] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-edit' => '[JNB登録内容編集] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-confirm' => '[JNB登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-save' => '[JNB登録内容確認] 確認ボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnb-done' => '[JNB登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-br' => '[JNB支店マスター管理] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-brup' => '[JNB支店マスター管理] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnb-history' => '[JNB口座利用履歴] 画面表示',
    __NAMESPACE__ . '\Controller\Jnb-erraccounts' => '[JNB口座重複割り当て一覧] 画面表示',
    // JnbgrpController.php
    __NAMESPACE__ . '\Controller\Jnbgrp-index' => '[JNB契約情報一覧] 画面表示(Jnb/listへのエイリアス)',
    __NAMESPACE__ . '\Controller\Jnbgrp-imp' => '[JNB口座インポート] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-confirm' => '[JNB口座インポート内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-continue' => '[CSVエラー一覧／CSVエラークリア] インポート確認画面へ戻るリンク押下処理／インポート確認画面へ戻るリンク押下処理',
    __NAMESPACE__ . '\Controller\Jnbgrp-save' => '[JNB口座インポート内容確認／JNB口座インポート進行画面] インポート実行ボタン押下処理／続けるボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnbgrp-done' => '[JNB口座インポート完了] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-cancel' => '[JNB口座インポート内容確認] キャンセルボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnbgrp-canceldone' => '[JNB口座インポートキャンセル] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-csverr' => '[CSVエラー一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-csverrclear' => '[CSVエラークリア] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-errdetail' => '[JNB口座インポート エラー詳細] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-errclear' => '[JNB口座インポート エラー詳細] このエラーをクリアするボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnbgrp-detail' => '[JNB口座グループ詳細] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-ret' => '[JNB口座返却] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-doret' => '[口座返却エラー] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbgrp-restore' => '[JNB登録内容詳細] 返却済み口座グループを復活する処理',
    // JnbmonController.php
    __NAMESPACE__ . '\Controller\Jnbmon-index' => '[自動入金処理の実行状況] 画面表示(autorcptへのエイリアス)',
    __NAMESPACE__ . '\Controller\Jnbmon-autorcpt' => '[自動入金処理の実行状況] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbmon-stop' => '[自動入金処理の実行状況] プロセスを停止ボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnbmon-clear' => '[自動入金処理の実行状況] この記録を削除ボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnbmon-notifications' => '[入金通知履歴] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbmon-nfl3m' => '[直近3ヶ月の通知状況] 画面表示',
    // JnbrcptController.php
    __NAMESPACE__ . '\Controller\Jnbrcpt-index' => '[JNB手動入金一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbrcpt-exec' => '[JNB手動入金一覧] 差額確定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnbrcpt-execby' => '[JNB金額指定入金] 画面表示',
    __NAMESPACE__ . '\Controller\Jnbrcpt-execIndividual' => '[JNB金額指定入金] 入金確定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Jnbrcpt-dispose' => '[JNB手動入金一覧] この通知を破棄ボタン押下処理',
    // SmbcpaController.php
    __NAMESPACE__ . '\Controller\Smbcpa-index' => '[SMBCバーチャル口座契約情報一覧] 画面表示(listのエイリアス)',
    __NAMESPACE__ . '\Controller\Smbcpa-list' => '[SMBCバーチャル口座契約情報一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpa-detail' => '[SMBCバーチャル口座登録内容詳細] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpa-new' => '[SMBCバーチャル口座新規登録] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpa-edit' => '[SMBCバーチャル口座登録内容編集] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpa-confirm' => '[SMBCバーチャル口座登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpa-save' => '[SMBCバーチャル口座登録内容確認] 確認ボタン押下処理',
    __NAMESPACE__ . '\Controller\Smbcpa-done' => '[SMBCバーチャル口座登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpa-br' => '[SMBCバーチャル口座支店マスター管理] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpa-brup' => '[SMBCバーチャル口座支店マスター管理] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Smbcpa-history' => '[SMBCバーチャル口座利用履歴] 画面表示',
    // SmbcpagrpController.php
    __NAMESPACE__ . '\Controller\Smbcpagrp-index' => '[SMBCバーチャル口座契約情報一覧] 画面表示(Smbcpa/listへのエイリアス)',
    __NAMESPACE__ . '\Controller\Smbcpagrp-detail' => '[SMBCバーチャル口座グループ詳細] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpagrp-ret' => '[SMBCバーチャル口座返却] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpagrp-doret' => '[口座返却エラー] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcpagrp-restore' => '[SMBCバーチャル口座登録内容詳細] 返却済み口座グループを復活する処理',
    __NAMESPACE__ . '\Controller\Smbcpagrp-accedit' => '[SMBCバーチャル口座登録] 画面表示',
    // SmbcparcptController.php
    __NAMESPACE__ . '\Controller\Smbcparcpt-index' => '[SMBCバーチャル口座手動入金一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcparcpt-exec' => '[SMBCバーチャル口座手動入金一覧] 差額確定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Smbcparcpt-execby' => '[SMBCバーチャル口座金額指定入金] 画面表示',
    __NAMESPACE__ . '\Controller\Smbcparcpt-execIndividual' => '[SMBCバーチャル口座金額指定入金] 入金確定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Smbcparcpt-dispose' => '[SMBCバーチャル口座手動入金一覧] この通知を破棄ボタン押下処理',
    // MonthlyController.php
    __NAMESPACE__ . '\Controller\Monthly-list' => '[月次明細データ作成] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-fix' => '[月次明細作成データ] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-fixedlist' => '[月次明細] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-summary' => '[月次明細（請求書兼納品書）] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-chargelist' => '[月次明細（お取引明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-stamplist' => '[月次明細（印紙代明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-cancellist' => '[月次明細（キャンセル返金明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-adjustmentlist' => '[月次明細（調整額明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Monthly-paybacklist' => '[月次明細（立替精算戻し明細）] 画面表示',
    // OemclosingController.php
    __NAMESPACE__ . '\Controller\Oemclosing-index' => '[OEM明細確認] 画面表示',
    __NAMESPACE__ . '\Controller\Oemclosing-closing' => '[OEM締め処理] 画面表示',
    __NAMESPACE__ . '\Controller\Oemclosing-dmeisai' => '[OEM明細確認] OEM明細CSVダウンロード処理',
    // OemController.php
    __NAMESPACE__ . '\Controller\Oem-list' => '[OEM先一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Oem-detail' => '[OEM先詳細情報] 画面表示',
    __NAMESPACE__ . '\Controller\Oem-form' => '[OEM先登録] 画面表示',
    __NAMESPACE__ . '\Controller\Oem-edit' => '[OEM先編集] 画面表示',
    __NAMESPACE__ . '\Controller\Oem-confirm' => '[OEM先登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Oem-back' => '[OEM先登録内容確認] 戻るリンク押下処理',
    __NAMESPACE__ . '\Controller\Oem-save' => '[OEM先登録内容確認] 確認リンク押下処理',
    __NAMESPACE__ . '\Controller\Oem-completion' => '[OEM先登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Oem-up' => '[OEM先詳細情報] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Oem-dcsv' => '[OEM先一覧] OEM一覧のCSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oem-resetpsw' => '[OEM先詳細情報] パスワードリセットリンク押下処理',
    __NAMESPACE__ . '\Controller\Oem-resetpswdone' => '[OEM先詳細情報] パスワードリセット完了処理',
    // OemdelivController.php
    __NAMESPACE__ . '\Controller\Oemdeliv-index' => '[OEM先一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Oemdeliv-oem' => '[OEM先配送方法設定画面] 画面表示',
    __NAMESPACE__ . '\Controller\Oemdeliv-save' => '[OEM先配送方法設定画面] 登録ボタン押下処理',
//     __NAMESPACE__ . '\Controller\Oemdeliv-master' => '[OEM先配送方法設定画面] 配送方法マスターのデータ処理',            // 配送方法マスターのリストボックスをAJaxで変更する
//     __NAMESPACE__ . '\Controller\Oemdeliv-current' => '[OEM先配送方法設定画面] 使用する配送方法のデータ処理',           // 使用する配送方法のリストボックスをAJaxで変更する
//     __NAMESPACE__ . '\Controller\Oemdeliv-entinfo' => '[OEM先配送方法設定画面] 指定OEM配下で配送伝票自動仮登録が有効な事業者の情報を取得するデータ処理',  // 使用する配送方法のリストボックスをAJaxで変更する
    // OemmonthlyController.php
    __NAMESPACE__ . '\Controller\Oemmonthly-settlement' => '[OEM精算書] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-update' => '[OEM精算書] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-settlementlist' => '[OEM精算明細一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-summary' => '[OEM精算明細（請求書兼納品書）] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-chargelist' => '[OEM精算明細（お取引明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-stamplist' => '[OEM精算明細（印紙代明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-cancellist' => '[OEM精算明細（キャンセル返金明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-adjustmentlist' => '[OEM精算明細（調整金明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-paybacklist' => '[OEM精算明細（立替精算戻し明細）] 画面表示',
    __NAMESPACE__ . '\Controller\Oemmonthly-dseisansyo' => '[OEM精算書] OEM精算書CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-dmeisaiichiran' => '[OEM精算明細一覧] OEM精算明細一覧CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-dorderichiran' => '[OEM精算明細一覧] OEM注文明細一覧CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-download' => '[OEM精算明細（請求書兼納品書）] 明細一式ダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-ddecisionTransfer' => '[OEM精算明細（請求書兼納品書）] OEM請求書兼納品書CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-dordermeisai' => '[OEM精算明細（お取引明細）] OEMお取引別精算明細CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-dstampfee' => '[OEM精算明細（印紙代明細）] OEM印紙代明細CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-dcancel' => '[OEM精算明細（キャンセル返金明細）] OEMキャンセル返金明細CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-dadjustmentamount' => '[OEM精算明細（調整金明細）] OEM調整金明細CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Oemmonthly-dpayingback' => '[OEM精算明細（立替精算戻し明細）] OEM立替精算戻しCSVダウンロード処理',
    // OpAuthorityController.php
    __NAMESPACE__ . '\Controller\OpAuthority-list' => '[権限マスタ] 画面表示',
    __NAMESPACE__ . '\Controller\OpAuthority-save' => '[権限マスタ] この内容で更新ボタン押下処理',
    // OperatorController.php
    __NAMESPACE__ . '\Controller\Operator-form' => '[オペレーター登録] 画面表示',
    __NAMESPACE__ . '\Controller\Operator-edit' => '[オペレーター編集] 画面表示',
    __NAMESPACE__ . '\Controller\Operator-confirm' => '[オペレーター登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Operator-save' => '[オペレーター登録内容確認] 確認ボタン押下処理',
    __NAMESPACE__ . '\Controller\Operator-completion' => '[オペレーター登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Operator-list' => '[オペレーター一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Operator-chgpw' => '[オペレーター一覧／パスワード変更] PSW変更リンク押下処理／OKボタン押下処理',
    __NAMESPACE__ . '\Controller\Operator-resetpsw' => '[オペレーター一覧] PSWリセットリンク押下処理',
    // PayingBackController.php
    __NAMESPACE__ . '\Controller\PayingBack-list' => '[立替精算戻し指示一覧] 画面表示',
    __NAMESPACE__ . '\Controller\PayingBack-search' => '[立替精算戻し指示一覧] 表示ボタン押下処理／一覧絞込ボタン押下処理',
    __NAMESPACE__ . '\Controller\PayingBack-save' => '[立替精算戻し指示一覧] 登録ボタン押下処理',
    // PayingController.php
    __NAMESPACE__ . '\Controller\Paying-list' => '[立替確認] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-elist' => '[立替実行済み] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-dlist2' => '[立替確認－事業者リスト] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-transdatadl' => '[立替確認] 総合振り込みデータダウンロード処理',
    __NAMESPACE__ . '\Controller\Paying-eachtimebillingdl' => '[立替確認] 都度請求データダウンロード処理',
    __NAMESPACE__ . '\Controller\Paying-fix' => '[立替確認] 画面表示／立替締め処理リンク押下／立替確定処理リンク押下',
    __NAMESPACE__ . '\Controller\Paying-execcharge' => '[立替確認] 支払完了ボタン押下処理',
    __NAMESPACE__ . '\Controller\Paying- adjust' => '[立替確認－事業者リスト] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-confirm' => '[立替確認－事業者リスト] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Paying-trnlist' => '[注文明細] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-cnllist' => '[キャンセル明細] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-stamplist' => '[印紙代明細] 画面表示',
    __NAMESPACE__ . '\Controller\Paying-paybacklist' => '[立替精算戻し明細] 画面表示',
    // PayingCycleController.php
    __NAMESPACE__ . '\Controller\PayingCycle-list' => '[サイクル立替] 画面表示',
    __NAMESPACE__ . '\Controller\PayingCycle-regist' => '[立替サイクル登録確認] 戻るボタン押下',
    __NAMESPACE__ . '\Controller\PayingCycle-edit' => '[立替サイクル登録] 画面表示',
    __NAMESPACE__ . '\Controller\PayingCycle-confirm' => '[立替サイクル登録確認] 画面表示',
    __NAMESPACE__ . '\Controller\PayingCycle-save' => '[立替サイクル登録確認] 確認ボタン押下',
    __NAMESPACE__ . '\Controller\PayingCycle-detail' => '[立替サイクル登録完了] 画面表示',
    // PdfController.php
    __NAMESPACE__ . '\Controller\Pdf-doemmonthlyseisansyo' => '[OEM精算書] 精算書をPDFダウンロード処理',
    // PriceplanController.php
    __NAMESPACE__ . '\Controller\Priceplan-form' => '[加盟店料金プラン登録] 画面表示',
    __NAMESPACE__ . '\Controller\Priceplan-edit' => '[加盟店料金プラン編集] 画面表示',
    __NAMESPACE__ . '\Controller\Priceplan-confirm' => '[加盟店料金プラン登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Priceplan-save' => '[加盟店料金プラン登録内容確認] 確認ボタン押下処理',
    __NAMESPACE__ . '\Controller\Priceplan-completion' => '[加盟店料金プラン登録完了] 画面表示',
    __NAMESPACE__ . '\Controller\Priceplan-list' => '[加盟店料金プラン一覧] 画面表示',
    // ReclaimController.php
    __NAMESPACE__ . '\Controller\Reclaim-list' => '[請求書発行待ちリスト] 画面表示',
    __NAMESPACE__ . '\Controller\Reclaim-done' => '[請求書再発行] 画面表示',
    __NAMESPACE__ . '\Controller\Reclaim-csvoutput' => '[請求書発行待ちリスト] CSV出力ボタン押下処理',                         // Ajax処理
    __NAMESPACE__ . '\Controller\Reclaim-print' => '[請求書発行待ちリスト] 印刷ボタン押下処理',                                // Ajax処理
    __NAMESPACE__ . '\Controller\Reclaim-upstopclaim' => '[請求書発行待ちリスト] 紙STOPに更新ボタン押下処理',                  // Ajax処理
    __NAMESPACE__ . '\Controller\Reclaim-dcsv' => '[請求書発行待ちリスト] CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Reclaim-simplelist' => '[再請求発行待ちリスト(CSV一括)出力] 画面表示',
//  __NAMESPACE__ . '\Controller\Reclaim-simplecsv1' => '[再請求発行待ちリスト(CSV一括)出力] CSV出力ボタン押下', // simplecsv1→2という流れなので、2で出しているからOK
    __NAMESPACE__ . '\Controller\Reclaim-simplecsv2' => '[再請求発行待ちリスト(CSV一括)出力] CSV出力ボタン押下',
    __NAMESPACE__ . '\Controller\Reclaim-simpleup' => '[再請求発行待ちリスト(CSV一括)出力] 印刷済みに更新ボタン押下',
    // RetentionAlertController.php
    __NAMESPACE__ . '\Controller\RetentionAlert-search' => '[滞留アラート] 画面表示／検索ボタン押下処理',
    __NAMESPACE__ . '\Controller\RetentionAlert-dcsv' => '[滞留アラート] CSVダウンロード処理',
    // RwarvlcfmController.php
    __NAMESPACE__ . '\Controller\Rwarvlcfm-list' => '[着荷確認] 画面表示',
    __NAMESPACE__ . '\Controller\Rwarvlcfm-lump' => '[一括着荷確認対象検索] 画面表示(lump)',
    __NAMESPACE__ . '\Controller\Rwarvlcfm-filter' => '[一括着荷確認対象検索] 画面表示(filter)',
    __NAMESPACE__ . '\Controller\Rwarvlcfm-up' => '[着荷確認] 着荷確認決定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Rwarvlcfm-lumpup' => '[一括着荷確認] 着荷確認決定ボタン押下処理',
    __NAMESPACE__ . '\Controller\Rwarvlcfm-lumpcomplete' => '[一括着荷確認完了] 画面表示',
    __NAMESPACE__ . '\Controller\Rwarvlcfm-sendmail' => '[着荷確認] 事業者へ伝票番号間違いのメールを送信します処理',
    __NAMESPACE__ . '\Controller\Rwarvlcfm-dcsv' => '[着荷確認] CSVダウンロード処理',
    // RwclaimController.php
    __NAMESPACE__ . '\Controller\Rwclaim-list' => '[請求書発行待ちリスト(初回)] 画面表示',
    __NAMESPACE__ . '\Controller\Rwclaim-up' => '[請求書発行] 画面表示',
    __NAMESPACE__ . '\Controller\Rwclaim-csvoutput' => '[請求書発行待ちリスト(初回)] CSV出力ボタン押下処理',                        // Ajax処理
    __NAMESPACE__ . '\Controller\Rwclaim-print' => '[請求書発行待ちリスト(初回)] 印刷ボタン押下処理',                               // Ajax処理
    __NAMESPACE__ . '\Controller\Rwclaim-dcsv' => '[請求書発行待ちリスト(初回)] CSVダウンロード処理',
    // RwcreditController.php
    __NAMESPACE__ . '\Controller\Rwcredit-atlist' => '[社内与信実行待ちリスト] 画面表示',
    __NAMESPACE__ . '\Controller\Rwcredit-list' => '[社内与信確定待ちリスト] 画面表示',
    __NAMESPACE__ . '\Controller\Rwcredit-list2' => '[社内与信確定待ちリスト] 画面表示',
    __NAMESPACE__ . '\Controller\Rwcredit-donejudge' => '[社内与信確定（エラー）] 画面表示',
    __NAMESPACE__ . '\Controller\Rwcredit-parsepostal' => '[社内与信確定待ちリスト] 郵便番号から住所を検索する処理',
//     __NAMESPACE__ . '\Controller\Rwcredit-iscanlock' => '[社内与信確定待ちリスト] 与信可能かの判定処理',                               // Ajax処理
//     __NAMESPACE__ . '\Controller\Rwcredit-dolock' => '[社内与信確定待ちリスト] 与信排他制御テーブルへの登録処理',                      // Ajax処理
    // RworderController.php
    __NAMESPACE__ . '\Controller\Rworder-detail' => '[注文情報] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-detailup' => '[注文情報] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Rworder-reissueform' => '[初回請求再発行] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-reissuedone' => '[初回請求再発行] 初回請求再発行確定ボタン押下後、画面表示',
    __NAMESPACE__ . '\Controller\Rworder-precancelform' => '[キャンセル処理（申請）] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-precanceldone' => '[キャンセル処理（申請）] キャンセル処理（申請取消）実行ボタン押下後、画面表示',
    __NAMESPACE__ . '\Controller\Rworder-cancelcancelform' => '[キャンセル処理（申請取消）] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-cancelcanceldone' => '[キャンセル処理（申請取消）] キャンセル処理（申請取消）実行ボタン押下後、画面表示',
    __NAMESPACE__ . '\Controller\Rworder-editform' => '[注文情報] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-editdone' => '[注文情報] OKボタン押下処理',
    __NAMESPACE__ . '\Controller\Rworder-revival' => '[注文情報] 与信NG復活ボタン押下処理',
    __NAMESPACE__ . '\Controller\Rworder-getstatus' => '[注文情報] 注文詳細画面のボタン制御用ステータスを取得する処理',
    __NAMESPACE__ . '\Controller\Rworder-clmnondeliform' => '[請求書不達] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-clmnondelidone' => '[請求書不達] 臨時立替確定ボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rworder-sppayform' => '[臨時立替] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-sppaydone' => '[臨時立替] 臨時立替確定ボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rworder-sendmail' => '[注文情報] 督促メールボタン押下処理',
//     __NAMESPACE__ . '\Controller\Rworder-isValidDelijournalnumber' => '[注文情報] 伝票番号有効性チェック処理',                               // Ajax処理
    __NAMESPACE__ . '\Controller\Rworder-clmhis' => '[請求履歴一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Rworder-receiptissuehistory' => '[領収書履歴一覧] 画面表示',
    // RworderhistController.php
    __NAMESPACE__ . '\Controller\Rworderhist-list' => '[履歴一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Rworderhist-detail' => '[履歴照会] 画面表示',
    // RwrcptcfmController.php
    __NAMESPACE__ . '\Controller\Rwrcptcfm-list' => '[入金確認] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-simplelist' => '[入金処理] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-confirm' => '[入金確認] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-rcpt' => '[入金確定] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-dtlrcptform' => '[詳細入金処理] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-dtlrcptsave' => '[詳細入金処理(登録処理)] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impapform' => '[アプラス入金CSVインポート] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impatpayform' => '[＠Payment(EG社)インポート] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impatpaycbform' => '[＠ペイメント（CB・OEM）インポート] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impsmbcform' => '[SMBC決済ステーションインポート] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impap' => '[アプラス入金CSVインポート] インポートボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impatpay' => '[＠Payment(EG社)インポート] インポートボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impatpaycb' => '[＠ペイメントインポート] インポートボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impsmbc' => '[SMBC決済ステーションインポート] インポートボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impmtform' => '[MTデータインポート] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impmt' => '[MTデータインポート] インポートボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impacctrnsmufjform' => '[振替結果(MUFJ)インポート] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impacctrnsmufj' => '[振替結果(MUFJ)インポート] インポートボタン押下処理',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impacctrnsmufjlist' => '[振替結果(MUFJ)一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-impacctrnsmufjdtl' => '[振替結果詳細(MUFJ)一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-creacctrnsmufjform' => '[振替請求データ(MUFJ)作成] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-creacctrnsmufj' => '[振替請求データ(MUFJ)作成] 作成ボタン押下処理',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-dlacctrnsmufjlist' => '[振替請求データ(MUFJ)ダウンロード] 画面表示',
    __NAMESPACE__ . '\Controller\Rwrcptcfm-dlacctrnsmufj' => '[振替請求データ(MUFJ)ダウンロード] ダウンロードリンク押下処理',
    // RwsprcptController.php
    __NAMESPACE__ . '\Controller\Rwsprcpt-lacklist' => '[過不足入金一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Rwsprcpt-repayedit' => '[返金指示入力] 画面表示',
    __NAMESPACE__ . '\Controller\Rwsprcpt-repaysave' => '[返金指示] 画面表示',
    __NAMESPACE__ . '\Controller\Rwsprcpt-histlist' => '[返金指示確定待ち・履歴一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Rwsprcpt-repaycancel' => '[返金指示確定待ち・履歴一覧] 返金指示キャンセル処理',                  // Ajax処理
    __NAMESPACE__ . '\Controller\Rwsprcpt-repaydecision' => '[返金指示確定待ち・履歴一覧] 返金指示確定処理',                      // Ajax処理
    __NAMESPACE__ . '\Controller\Rwsprcpt-losslist' => '[雑損失・雑収入一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Rwsprcpt-lossedit' => '[雑損失雑収入等登録] 画面表示',
    __NAMESPACE__ . '\Controller\Rwsprcpt-losssave' => '[雑損失雑収入等登録] 登録ボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Rwsprcpt-dlacklistcsv' => '[過不足入金一覧] 過不足入金一覧CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Rwsprcpt-dhistlistcsv' => '[返金指示確定待ち・履歴一覧] 返金指示確定待ち・履歴一覧CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Rwsprcpt-dlosslistcsv' => '[雑損失・雑収入一覧] 雑損失・雑収入一覧CSVダウンロード処理',
    // SearcheController.php
    __NAMESPACE__ . '\Controller\Searche-form' => '[事業者検索] 画面表示',
    __NAMESPACE__ . '\Controller\Searche-search' => '[事業者検索] 検索ボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\Searche-download' => '[事業者検索] CSVダウンロード処理',
    // SearchfController.php
    __NAMESPACE__ . '\Controller\Searchf-form' => '[不払検索] 画面表示',
    __NAMESPACE__ . '\Controller\Searchf-search' => '[不払データ検索] 画面表示',
    __NAMESPACE__ . '\Controller\Searchf-dcsv' => '[不払データ検索] CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Searchf-directsearch' => '[後払い.com - トップ] 不払データ検索処理',
    __NAMESPACE__ . '\Controller\Searchf-export' => '[不払データ検索] オートコール・エクスポート処理',
    // SearchoController.php
    __NAMESPACE__ . '\Controller\Searcho-form' => '[注文検索] 画面表示',
    __NAMESPACE__ . '\Controller\Searcho-qform' => '[簡易注文検索] 画面表示',
    __NAMESPACE__ . '\Controller\Searcho-sform' => '[定型注文検索] 画面表示',
    __NAMESPACE__ . '\Controller\Searcho-search' => '[注文検索] 検索処理後、画面表示',
    __NAMESPACE__ . '\Controller\Searcho-qsearch' => '[簡易注文検索] 検索処理後、画面表示',
    __NAMESPACE__ . '\Controller\Searcho-ssearch' => '[定型注文検索] 検索処理後、画面表示',
    __NAMESPACE__ . '\Controller\Searcho-dcsv' => '[注文検索] 注文検索CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Searcho-qdcsv' => '[簡易注文検索] 簡易注文検索CSVダウンロード処理',
    __NAMESPACE__ . '\Controller\Searcho-sdcsv' => '[定型注文検索] 定型注文検索CSVダウンロード処理',
    // SiteController.php
    __NAMESPACE__ . '\Controller\Site-list' => '[サイト一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Site-regist' => '[サイト登録] 画面表示',
    __NAMESPACE__ . '\Controller\Site-edit' => '[サイト編集] 画面表示',
    __NAMESPACE__ . '\Controller\Site-confirm' => '[サイト登録内容確認] 画面表示',
    __NAMESPACE__ . '\Controller\Site-save' => '[サイト登録内容確認] 確認ボタン押下処理',
    __NAMESPACE__ . '\Controller\Site-siteagency' => '[代理店一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Site-agencyconfirm' => '[代理店一覧] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Site-campaign' => '[キャンペーン設定] 画面表示',
    __NAMESPACE__ . '\Controller\Site-campaigndone' => '[キャンペーン設定] キャンペーン登録ボタン押下処理',
    __NAMESPACE__ . '\Controller\Site-resetacs' => '[サイト登録／編集] 請求ストップ解除リンク押下処理',
    __NAMESPACE__ . '\Controller\Site-resetacsdone' => '[請求ストップ解除処理] 解除を実行ボタン押下処理',
    __NAMESPACE__ . '\Controller\Site-acsupdate' => '[請求ストップ解除処理] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\Generalsvc-searchsubscriber' => '[サイト登録／編集] 加入者固有コード検索処理',
    __NAMESPACE__ . '\Controller\Generalsvc-sscodelist' => '[加入者固有コード一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Generalsvc-payment' => '[支払可能種類] 画面表示',
    // SpclPayingController.php
    __NAMESPACE__ . '\Controller\SpclPaying-list' => '[臨時加盟店立替精算] 画面表示',
    __NAMESPACE__ . '\Controller\SpclPaying-process' => '[臨時加盟店立替精算] 検索ボタン／立替計算ボタン／立替確定ボタン／支払完了ボタン押下処理',
    __NAMESPACE__ . '\Controller\SpclPaying-calc_' => '[臨時加盟店立替精算] 立替計算ボタン押下処理',
    __NAMESPACE__ . '\Controller\SpclPaying-save_' => '[臨時加盟店立替精算] 立替確定ボタン押下処理',
    __NAMESPACE__ . '\Controller\SpclPaying-dcsvreserve' => '[臨時加盟店立替精算] CSVダウンロード事前処理',
    __NAMESPACE__ . '\Controller\SpclPaying-dcsv' => '[臨時加盟店立替精算] CSVダウンロード処理',
    // SystemConditionController.php
    __NAMESPACE__ . '\Controller\SystemCondition-index' => '[システム条件登録・修正] 画面表示',
    __NAMESPACE__ . '\Controller\SystemCondition-up' => '[システム条件登録・修正] 更新ボタン押下処理',
    __NAMESPACE__ . '\Controller\SystemCondition-new' => '[システム条件登録・修正] 新規ボタン押下処理',
    // TemplateController.php
    __NAMESPACE__ . '\Controller\Template-index' => '[テンプレート一覧] 画面表示',
    __NAMESPACE__ . '\Controller\Template-edit' => '[テンプレート編集] 画面表示',
    __NAMESPACE__ . '\Controller\Template-confirm' => '[テンプレート一覧] 削除リンク押下処理',
    __NAMESPACE__ . '\Controller\Template-save' => '[テンプレート編集] 登録ボタン押下処理',
    // TestController.php
    __NAMESPACE__ . '\Controller\Test-testform' => '[テスト用フォーム] 画面表示',
    __NAMESPACE__ . '\Controller\Test-credit' => '[テスト用フォーム] 与信テスト実行ボタン押下処理',
    __NAMESPACE__ . '\Controller\Test-claim' => '[テスト用フォーム] 請求SPテスト実行ボタン押下処理',
    // UnifiInstrController.php
    __NAMESPACE__ . '\Controller\UnifiInstr-unifiinstr' => '[顧客統合指示] 画面表示',
    __NAMESPACE__ . '\Controller\UnifiInstr-update' => '[顧客統合指示] 統合指示ボタン押下処理',
    __NAMESPACE__ . '\Controller\UnifiInstr-search' => '[顧客統合指示] 検索ボタン押下処理後、画面表示',
    __NAMESPACE__ . '\Controller\UnifiInstr-save' => '[顧客統合指示] 統合指示ボタン押下処理',
);
