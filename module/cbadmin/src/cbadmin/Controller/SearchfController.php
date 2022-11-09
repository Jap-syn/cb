<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\Reflection\BaseReflectionUtility;
use models\Table\TableEnterprise;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TableOperator;
use models\Table\TableOem;
use models\Table\TableDeliMethod;
use models\Table\TablePayingAndSales;
use models\Table\TableClaimHistory;
use models\Table\TableStampFee;
use models\Table\TableCancel;
use models\Table\TableEnterpriseClaimed;
use models\View\ViewOrderCustomer;
use models\View\ViewArrivalConfirm;
use models\View\ViewWaitForCancelConfirm;
use models\View\ViewChargeConfirm;
use models\Logic\LogicChargeDecision;
use models\Logic\LogicTemplate;
use cbadmin\classes\SearchfCache;

class SearchfController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * 氏名・氏名かなの検索データを作成するための不要文字抽出用正規表現
     *
     * @var string
     */
    const REGEXP_TRIM_NAME = '[ 　\r\n\t\v]';

    /**
     * 電話番号の検索データを作成するための不要文字抽出用正規表現
     *
     * @var string
     */
    const REGEXP_TRIM_PHONE = '[^0-9]';

    /**
     * 不払い検索キャッシュ
     *
     * @access protected
     * @var SearchfCache;
     */
    protected $_cache;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet( '../css/base.ui.tableex.css' );
        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/corelib.js' );
        $this->addJavaScript( '../js/base.ui.js');
        $this->addJavaScript( '../js/base.ui.tableex.js' );
        $this->addJavaScript( '../js/base.ui.datepicker.js' );
        $this->addJavaScript( '../js/sortable_ja.js' );

        $this->setPageTitle("後払い.com - 不払検索");
    }

    /**
     * 検索フォームの表示
     */
    public function formAction()
    {
        // 不払い検索キャッシュをセッションストレージから抹消
        SearchfCache::clearInstanceFromStorage();

        $mdlOem = new TableOem($this->app->dbAdapter);

        $oem_list = $mdlOem->getOemIdList();

        //初期値セット
        $oem_list[0] = "全て";

        //OEM先リストSELECTタグ
        $this->view->assign('oemTag',
            BaseHtmlUtils::SelectTag("Oem",
            $oem_list
            )
        );

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // 督促分類
        unset($iMaster);
        unset($seletedIMaster);
        $iMaster = $codeMaster->getRemindClassMaster();
        foreach($iMaster as $value => $key)
        {
            $seletedIMaster[] = $value;
        }
        $custom['RemindClassTag'] = BaseHtmlUtils::InputCheckBoxTag('RemindClass', $iMaster, $seletedIMaster);

//         // 支払意志
//         $custom['PaymentWillTag'] = BaseHtmlUtils::InputRadioTag(
//             'PaymentWill',
//             array(1 => '意思あり', -1 => '意思なし', 0 => '全て')
//         );

//         // タッチ履歴
//         $custom['TouchHistoryFlgTag'] = BaseHtmlUtils::InputRadioTag(
//             'TouchHistoryFlg',
//             array(1 => '履歴あり', -1 => '履歴なし', 0 => '全て')
//         );

        // TEL有効
        unset($iMaster);
        unset($seletedIMaster);
        $iMaster = $codeMaster->getValidTelMaster();
        foreach($iMaster as $value => $key)
        {
            $seletedIMaster[] = $value;
        }
        $custom['ValidTelTag'] = BaseHtmlUtils::InputCheckBoxTag('ValidTel', $iMaster, $seletedIMaster);

        // 住所有効
        unset($iMaster);
        unset($seletedIMaster);
        $iMaster = $codeMaster->getValidAddressMaster();
        foreach($iMaster as $value => $key)
        {
            $seletedIMaster[] = $value;
        }
        $custom['ValidAddressTag'] = BaseHtmlUtils::InputCheckBoxTag('ValidAddress', $iMaster, $seletedIMaster);

        // メール有効
        unset($iMaster);
        unset($seletedIMaster);
        $iMaster = $codeMaster->getValidMailMaster();
        foreach($iMaster as $value => $key)
        {
            $seletedIMaster[] = $value;
        }
        $custom['ValidMailTag'] = BaseHtmlUtils::InputCheckBoxTag('ValidMail', $iMaster, $seletedIMaster);

        // 請求ストップ
        $custom['ClaimStopTag'] = BaseHtmlUtils::InputRadioTag(
            'ClaimStop',
            array(0 => '無視', 1 => '全ストップ', 2 => '紙', 3 => 'メール')
        );

        // 訪問済
        $custom['VisitFlgTag'] = BaseHtmlUtils::InputRadioTag(
            'VisitFlg',
            array(0 => '全て', 1 => '訪問済み', 2 => '未訪問')
        );

        // 最終回収手段
        unset($iMaster);
        unset($seletedIMaster);
        $iMaster = $codeMaster->getFinalityCollectionMeanMaster();
        foreach($iMaster as $value => $key)
        {
            $seletedIMaster[] = $value;
        }
        $custom['FinalityCollectionMeanTag'] = BaseHtmlUtils::InputCheckBoxTag('FinalityCollectionMeanTag', $iMaster, $seletedIMaster);

        // 顧客ステータス
        $this->view->assign('custStsTag',BaseHtmlUtils::SelectTag("custSts",array(0 => '全て', 1 => 'ブラック', 2 => '優良')));
        
        // 請求代行プラン
				$this->view->assign('BillingAgentStsTag',BaseHtmlUtils::SelectTag("BillingAgentSts",array(0 => '含めない', 1 => '含める', 2 => 'のみ')));
        
        // 架電
        unset($iMaster);
        unset($seletedIMaster);
        $iMaster = array(0 => '30日架電未', 1 => '30日架電済', 2 => '90日架電未', 3 => '90日架電済');
        foreach($iMaster as $value => $key)
        {
            $seletedIMaster[] = $value;
        }
        $custom['CalledTag'] = BaseHtmlUtils::InputCheckBoxTag('Called', $iMaster, $seletedIMaster);

        $this->view->assign('custom', $custom);

        return $this->view;
    }

    /**
     * 検索実行
     */
    public function searchAction() {
        // JSとCSSのバインド
        $orderstatus = $this->app->tools['orderstatus'];

        $this->addStyleSheet( '../css/cbadmin/orderstatus/' . ( $orderstatus['style'] ? $orderstatus['style'] : 'default' ) . '.css' );
        $this->addStyleSheet( '../css/base.ui.customlist.css' );
        $this->addJavaScript( '../js/bytefx.js' );
        $this->addJavaScript( '../js/base.ui.customlist.js' );

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // 要求パラメータの抽出
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        // セッションからキャッシュの復元を試みる
        $cache = SearchfCache::getInstanceFromStorage();
        if( ! $cache ) {
            // キャッシュが保存されていないので新規作成
            $cache = new SearchfCache();
            $cache
                ->setDbAdapter( $this->app->dbAdapter )
                ->buildExpressions( $params );
        } else {
            // 復元したキャッシュにDBアダプタを割り当てる
            $cache->setDbAdapter( $this->app->dbAdapter );
        }

        // キャッシュID指定があり（＝絞込み、ソート等）、現在のキャッシュIDと一致しない場合は不正として処理
        if( ! empty( $params['cache_id'] ) && $params['cache_id'] != $cache->getCacheId() ) {
            $this->view->assign( 'invalid_cache_id', true );
            return;
        }

        // ページ指定がない場合のみソートやフィルタの適用をする
        if( ! isset( $params['page'] ) ) {
            // ソート指定
            if( isset($params['sort']) ) {
                $cache->addSortKey( $params['sort'] );
            }

            // 再検索（支払意思指定）
            if( isset($params['will']) ) {
                $cache->setRedoExression($params['will']);
            }

            // 絞込み指定
            if( isset($params[SearchfCache::FILTER_TARGET_MONTH]) || isset($params[SearchfCache::FILTER_TARGET_REMIND_CLASS]) ) {
                $cache->setFilters( $params );
            }
        }

        // 検索ボタン押下時

        // 検索実行
        $search_results = $cache->getResults();
        // 検索結果のハッシュをビューに割り当てる
        $this->view
            ->assign( 'hash', md5( serialize($search_results) ) );
        
        // [paging] 指定ページ確定
        $page = $params['page'];
        if( ! BaseReflectionUtility::isPositiveInteger($page) ) $page = 1;
        // [paging] 1ページあたりの項目数をconfig.iniから取得。未定義の場合は200とする
        $cn = $this->getControllerName();
        $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf->$cn : 200;
        if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 200;

        // count関数対策
        $search_resultsLen = 0;
        if(!empty($search_results)) {
            $search_resultsLen = count($search_results);
		}

        // [paging] ページャ初期化
        $pager = new CoralPager( $search_resultsLen, $ipp );
        // [paging] 指定ページを補正
        if( $page > $pager->getTotalPage() ) $page = $pager->getTotalPage();
        // [paging] ページナビゲーション情報
        $page_links = array( 'base' => 'searchf/search/cache_id/' . $cache->getCacheId() . '/page' );
        $page_links['prev'] = $page_links['base'] . '/' . ( $page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $page + 1 );
        // [paging] ページング関連情報をビューへアサイン
        $this->view->assign( 'current_page', $page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        // データのトリミングと加工
        $datas = array();
        for($i = 0, $l = $pager->getTotalItems(); $i < $l; $i++) {
            if( $i < $pager->getStartIndex($page) || $i > $pager->getEndIndex($page) ) {
                continue;
            }
            $row = $search_results[$i];
            $row['index_in_cache'] = $i;    // キャッシュ中のインデックス位置を埋め込む
            // 表示向けの加工を適用
            $datas[] = $cache->applyViewData( $row );
        }

        // 督促分類での絞り込み指定用リスト作成
        $remind_classes = $codeMaster->getRemindClassMaster();
        // 初回期限月での絞り込み指定用リスト作成
        $target_months = SearchfCache::generateMonthList();

        $summary = $cache->getSummaries();
        $this->view->assign( 'filter_month_list', $target_months );             // 絞込み可能な月のリスト
        $this->view->assign( 'filter_classes', $remind_classes );               // 督促分類リスト
        $this->view->assign( 'list', $datas );                                  // 表示対象データ
        $this->view->assign( 'summaries', $summary );                           // 検索サマリ
        $this->view->assign( 'cache_id', $cache->getCacheId() );                // 検索キャッシュID
        $this->view->assign( 'sort_keys', $cache->getSortKeys() );              // ソートキーリスト
        $this->view->assign( 'filters' , $cache->getFilters() );                // 適用済み絞込条件のリスト
        $this->view->assign( 'redo_expression', $cache->getRedoExression() );   // 支払意思条件

        // キャッシュを保存
        SearchfCache::setInstanceToStorage($cache);

        return $this->view;
    }

    /**
     * ダウンロード実行
     */
    public function dcsvAction() {
        // 要求パラメータの抽出
        $params = $this->params()->fromPost();

        // セッションからキャッシュの復元を試みる
        $cache = SearchfCache::getInstanceFromStorage();
        if( ! $cache ) {
            // キャッシュが保存されていないのでエラー
            return $this->_redirect( 'error/nop' );

        } else {
            // 復元したキャッシュにDBアダプタを割り当てる
            $cache->setDbAdapter( $this->app->dbAdapter );
        }

        // キャッシュID指定があり（＝絞込み、ソート等）、現在のキャッシュIDと一致しない場合は不正としてエラー扱い
        if( ! empty( $params['cache_id'] ) && $params['cache_id'] != $cache->getCacheId() ) {
            return $this->_redirect( 'error/nop' );
        }

        // 条件はそのままでCVSデータ取得
        $csv = $cache->getCsv();

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI11081_2';     // テンプレートID       不払いCSV
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス           CB
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $csv, sprintf( 'Fubarai_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * トップページからショートカットで不払い検索実行
     */
    public function directsearchAction() {
        // 要求パラメータのみ抽出
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        // 不払いキャッシュをクリア
        SearchfCache::clearInstanceFromStorage();

        // 約束日からの経過日数指定の場合は以下の手順でパラメータを補正する
        // 1. PromPayDateF/T をクリア
        // 2. past_days_from の整数値からPromPayDateTを生成
        if( isset( $params['past_days_from'] ) ) {
            unset( $params['first_limit_date'] );
            unset( $params['PromPayDateF'] );
            unset( $params['PromPayDateT'] );

            $days = (int)($params['past_days_from']);
            $d = date('Y-m-d');
            $params['PromPayDateT'] = date('Y-m-d', strtotime($d . " - ". $days . "day"));

        } else
        if( isset( $params['first_limit_date'] ) ) {
            // 初回支払期限指定検索（2010.4.5 追加 eda）
            $date = $params['first_limit_date'];
            unset( $params['PromPayDateF'] );
            unset( $params['PromPayDateT'] );

            $params['sort'] = 'UseAmount';    // 金額が最優先ソートキー
            $params['LimitDateF'] = $params['LimitDateT'] = $date;

        } else {
            // 約束日経過指定がない場合は全件検索のキャッシュを作っておく
            $cache = new SearchfCache();
            $cache->setDbAdapter( $this->app->dbAdapter )->buildExpressions(array())->getResults();
            SearchfCache::setInstanceToStorage( $cache );
        }
        foreach($params as $key => $value) {
            $this->app->logger->debug( '[SearchfController#directsearchAction] $params[' . $key . '] => ' . $value );
        }

        // searchActionへフォワード
        return $this->_forward( 'search', $params );
    }

    /**
     * オートコールエクスポート実行
     */
    public function exportAction() {
        // 要求パラメータの抽出
        $params = $this->params()->fromPost();

        // セッションからキャッシュの復元を試みる
        $cache = SearchfCache::getInstanceFromStorage();
        if( ! $cache ) {
            // キャッシュが保存されていないのでエラー
            return $this->_redirect( 'error/nop' );

        } else {
            // 復元したキャッシュにDBアダプタを割り当てる
            $cache->setDbAdapter( $this->app->dbAdapter );
        }

        // キャッシュID指定があり（＝絞込み、ソート等）、現在のキャッシュIDと一致しない場合は不正としてエラー扱い
        if( ! empty( $params['cache_id'] ) && $params['cache_id'] != $cache->getCacheId() ) {
            return $this->_redirect( 'error/nop' );
        }

        // 条件はそのままでエクスポートデータ取得
        $csv = $cache->getExport();

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI11081_1';     // テンプレートID       不払いオートコールCSV
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス           CB
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $csv, sprintf( 'AutoCall_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        // 以下、オートコール結果への登録／更新
        $mdlac = new \models\Table\TableAutoCall($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        try {
            $search_query = $cache->getResults();
            foreach( $search_query as $row ) {

                // [Status=0]のレコードの取得
                $row_ac = $this->app->dbAdapter->query(" SELECT Seq FROM T_AutoCall WHERE Status = 0 AND AddInfo = :AddInfo LIMIT 1 "
                    )->execute(array(':AddInfo' => $row['OrderId']))->current();

                if ($row_ac) {  // (UPDATE)
                    $prm_upd = array(
                        'Phone1' => $row['SearchPhone'],
                        'Phone2' => $row['SearchPhone'],
                        'Phone3' => $row['SearchPhone'],
                        'UpdateId' => $userId,
                    );
                    $mdlac->saveUpdate($prm_upd, $row_ac['Seq']);
                }
                else {          // (INSERT)
                    $prm_ins = array(
                        'OrderSeq' => $row['OrderSeq'],
                        'AddInfo' => $row['OrderId'],
                        'Phone1' => $row['SearchPhone'],
                        'Phone2' => $row['SearchPhone'],
                        'Phone3' => $row['SearchPhone'],
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                    );
                    $mdlac->saveNew($prm_ins);
                }
            }
        }
        catch(\Exception $e) {
            return $this->_redirect( 'error/nop' );
        }

        return $response;
    }

    /**
     * 一括登録時のファイルフィールドのname属性
     *
     * @var string
     */
    const UPLOAD_FIELD_NAME = 'Csv_File';

    /**
     * importアクション
     *
     */
    public function importAction()
    {
        return $this->view;
    }

    /**
     * confirmアクション
     *
     */
    public function confirmAction()
    {
        $errors = array();

        // CSVファイル取り込み
        $csv = $_FILES[ self::UPLOAD_FIELD_NAME ]['tmp_name'];

        // 拡張子チェック
        if( strrchr( $_FILES[ self::UPLOAD_FIELD_NAME ]['name'], '.' ) === '.csv' && $csv != "" ) {
            $templateId = 'CKI11081_3'; // 不払いオートコール取込CSV
            $templateClass = 0;
            $seq = 0;
            $templatePattern = 0;

            // CSV解析実行
            $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
            $rows = $logicTemplate->convertFiletoArray( $csv, $templateId, $templateClass, $seq, $templatePattern );

            // ロジック解析失敗
            if( $rows == false ) {
                $this->view->assign( 'error', $logicTemplate->getErrorMessage() );
                $this->setTemplate( 'error' );
                return $this->view;
            }

            // NOTE : 特別なバリデーションは行わない(20150910_1700)

            // データ更新

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser( $this->app->dbAdapter );
            $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            $mdlac = new \models\Table\TableAutoCall($this->app->dbAdapter);
            $mdlo = new \models\Table\TableOrder($this->app->dbAdapter);

            $nonsetlist = array();
            $regCount = 0;
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                foreach( $rows as $row ) {

                    // [Status=0]のレコードの取得
                    $row_ac = $this->app->dbAdapter->query(" SELECT Seq, OrderSeq FROM T_AutoCall WHERE Status = 0 AND AddInfo = :AddInfo LIMIT 1 "
                        )->execute(array(':AddInfo' => $row['AddInfo']))->current();

                    if ($row_ac) {

                        // 開始日時が取得出来ない場合は、督促日にシステム日付をセットする(20151127)
                        $callStartDate = (IsValidDate($row['CallStartDate'])) ? date('Y-m-d', strtotime($row['CallStartDate'])) : date('Y-m-d');

                        // (UPDATE:T_AutoCall)
                        $prm_upd = array();
                        foreach( $row as $key => $val ) {
                            $prm_upd[$key] = $val;
                        }
                        $prm_upd['Status'] = 1;
                        $prm_upd['UpdateId'] = $userId;

                        $mdlac->saveUpdate($prm_upd, $row_ac['Seq']);

                        // (UPDATE:T_Order)
                        $mdlo->saveUpdate(
                            array(
                                'FinalityRemindDate' => $callStartDate,
                                'FinalityRemindOpId' => $this->app->authManagerAdmin->getUserInfo()->OpId,
                                'UpdateId' => $userId,
                                'UpdateDate' => date('Y-m-d H:i:s'),
                            ) , $row_ac['OrderSeq']);

                        $regCount++;
                    }
                    else {
                        // T_AutoCallに更新対象の注文IDがない場合は、リストへ積み上げる
                        $nonsetlist[] = $row['AddInfo'];
                    }
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                $this->view->assign( 'importInfo', $regCount . '件のオートコール結果を取り込みました。' );
                $this->view->assign( 'nonsetlist', $nonsetlist );
                $this->setTemplate( 'completion' );
                return $this->view;
            }
            catch( \Exception $e ) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();

                $this->view->assign( 'error',$e->getMessage() );
                $this->setTemplate( 'error' );
                return $this->view;
            }
        }
        else
        {
            $this->view->assign( 'error', 'ファイル形式が適切ではありません。<br />CSVファイルを登録してください' );
            $this->setTemplate( 'error' );
            return $this->view;
        }
    }

    /**
     * 外部督促エクスポート実行
     */
    public function exportremindAction() {
        // 要求パラメータの抽出
        $params = $this->params()->fromPost();

        // セッションからキャッシュの復元を試みる
        $cache = SearchfCache::getInstanceFromStorage();
        if( ! $cache ) {
            // キャッシュが保存されていないのでエラー
            return $this->_redirect( 'error/nop' );

        } else {
            // 復元したキャッシュにDBアダプタを割り当てる
            $cache->setDbAdapter( $this->app->dbAdapter );
        }

        // キャッシュID指定があり（＝絞込み、ソート等）、現在のキャッシュIDと一致しない場合は不正としてエラー扱い
        if( ! empty( $params['cache_id'] ) && $params['cache_id'] != $cache->getCacheId() ) {
            return $this->_redirect( 'error/nop' );
        }

        // 督促データダウンロードボタン押下時
        // 督促CSVデータ取得
        $csv = $cache->getRemind();

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI11080_1';     // テンプレートID       不払い分督促データCSV
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス           CB
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $csv, sprintf( 'Tokusoku_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        // 以下、PushSMS結果への登録／更新
        $mdlac = new \models\Table\TablePushSms($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        try {
            $search_query = $cache->getResults();
            foreach( $search_query as $row ) {

                // [Status=0]のレコードの取得
                $row_ac = $this->app->dbAdapter->query(" SELECT Seq FROM T_PushSms WHERE Status = 0 AND OrderSeq = :OrderSeq LIMIT 1 "
                    )->execute(array(':OrderSeq' => $row['OrderSeq']))->current();

                if ($row_ac) {  // (UPDATE)
                    $prm_upd = array(
                            'PhoneNumber'   => str_replace('-', '', $row['Phone']),   //P_CUS.Phone
                            'MessageNumber' => 1,
                            'Message'       => '',
                            'ReferenceDate' => date('Y-m-d'),
                            'UpdateId'      => $userId,
                    );
                    $mdlac->saveUpdate($prm_upd, $row_ac['Seq']);
                }
                else {          // (INSERT)
                    $prm_ins = array(
                            'OrderSeq'      => $row['OrderSeq'],
                            'PhoneNumber'   => str_replace('-', '', $row['Phone']),   //P_CUS.Phone
                            'MessageNumber' => 1,
                            'Message'       => '',
                            'ReferenceDate' => date('Y-m-d'),
                            'RegistId'      => $userId,
                            'UpdateId'      => $userId,
                    );
                    $mdlac->saveNew($prm_ins);
                }
            }
        }
        catch(\Exception $e) {
            return $this->_redirect( 'error/nop' );
        }

        return $response;
    }
}

