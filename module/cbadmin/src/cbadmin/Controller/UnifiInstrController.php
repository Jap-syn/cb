<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use models\Table\TableCombinedList;
use models\Table\TableUser;
use models\Table\TableManagementCustomer;
use Zend\Db\Adapter\Adapter;
use Zend\Http\Header\Vary;

class UnifiInstrController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    const SESS_SAVEAFTER_CID = 'SESS_UNIFI_SAVEAFTER_CID';
    const SESS_SAVEAFTER_SKEY = 'SESS_UNIFI_SAVEAFTER_SKEY';
    const SESS_SAVEAFTER_MSG = "SESS_SAVEAFTER_MSG";

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * 管理者権限フラグ
     * @var boolean
     */
    private $isAdmin;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $userInfo = $this->app->authManagerAdmin->getUserInfo();

        // ログイン中アカウントの権限を確認して、利用可能なロールで無い場合はエラーにする
        $this->isAdmin = $userInfo->RoleCode > 1;

        $this->view->assign('userInfo', $userInfo);

        $this
            ->addStyleSheet('../css/default02.css')
            ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 顧客統合管理");
    }

    /**
     * 統合指示一覧を表示
     * 検索ボタン押下時はパラメータによる条件付与あり
     */
    public function unifiinstrAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        // パラメータ取得
        // 名寄せリストID
        $combinedListId = $this->params()->fromRoute( 'combinedlistid', '-1' );
        $params = $this->getParams();
        // 検索ボタンの場合、名寄せリストIDをfromRouteではなく通常のパラメータで取得
        if ($combinedListId == -1 && isset($params['CombinedListIdSearch'])) {
            $combinedListId = $params['CombinedListIdSearch'];
        }

        // 検索結果画面からの遷移時、検索条件をセッションから取得
        // 画面条件
        if (!isset($sKey) && isset($_SESSION[self::SESS_SAVEAFTER_SKEY])){
            $sKey = $_SESSION[self::SESS_SAVEAFTER_SKEY];
        }

        // 管理統合指示データ取得
        $ar = $this->getCombiledList($combinedListId);

        // 取得データを反映
        // 名寄せリストID
        $this->view->assign('combinedlistid', $combinedListId);
        // 管理統合指示一覧
        $this->view->assign('list', $ar);
        // 類似顧客検索条件
        $this->view->assign('key', $sKey);

        // 更新後の場合、メッセージ表示
        if (isset($_SESSION[self::SESS_SAVEAFTER_MSG])) {
            unset($_SESSION[self::SESS_SAVEAFTER_MSG]);
            $this->view->assign('message', sprintf('<font color="red"><b>名寄せリストを更新しました。</b></font>'));
        }

        return $this->view;
    }

    /**
     * 管理統合指示一覧の情報を取得
     * データベースから取得
     *
     * @access private
     * @param int $combinedListId 名寄せリストID
     * @return array 管理統合指示一覧データ
     */
    private function getCombiledList($combinedListId)
    {
        // 管理統合指示データ取得
        $sql  = " SELECT b.ManCustId ";
        $sql .= "      , b.NameKj ";
        $sql .= "      , b.NameKn ";
        $sql .= "      , b.UnitingAddress ";
        $sql .= "      , b.Phone ";
        $sql .= "      , b.MailAddress ";
        $sql .= "      , a.AggregationLevel ";
        $sql .= "      , a.LikenessFlg ";
        $sql .= "      , a.CombinedDictateFlg ";
        $sql .= "   FROM T_CombinedList a ";
        $sql .= "        INNER JOIN T_ManagementCustomer b ON a.ManCustId = b.ManCustId ";
        $sql .= "  WHERE a.ValidFlg = 1 ";
        $sql .= "    AND b.ValidFlg = 1 ";

        // 名寄せリストID
        if (isset($combinedListId)) {
            $sql .= " AND a.CombinedListId = :CombinedListId";
            $params[':CombinedListId'] = $combinedListId;
        } else {
            // 0件にする
            $sql .= " AND 1 = 0 ";
        }

        $sql .= "  ORDER BY b.ManCustId ASC ";

        $stm = $this->app->dbAdapter->query($sql);

        // 結果をarrayで返す
        $ar = ResultInterfaceToArray($stm->execute($params));

        return $ar;
    }

    /**
     * 統合指示を実行
     * UPDATEのみ
     */
    public function updateAction()
    {
        // リファラーがなければ顧客統合名寄せリストへリダイレクト
        if(!isset($_SERVER['HTTP_REFERER']))
        {
            return $this->_redirect('customeradrslist/list');
        }

        // パラメータ取得
        $params = $this->getParams();
        $combinedListId = $params['CombinedListIdSave'];  // 名寄せリストID

        // 更新内容チェック
        $errors = $this->checkSaveData($params);

        // count関数対策
        if (isset($errors) && !empty($errors))
        {
            // エラーがあればエラーメッセージをセット
            $this->view->assign('errors', $errors);
            $this->view->assign('process', '統合指示');

            // ビューに入力を割り当てる
            // 名寄せリストID
            $this->view->assign('combinedlistid', $combinedListId);
            // 管理統合指示一覧
            $data = $this->getListInfo($params);
            $this->view->assign('list', $data);

            // 再表示　入力を維持
            $this->setTemplate('unifiinstr');

            return $this->view;
        }
        else
        {
            // エラーがない場合、更新処理を行う
            // ユーザーIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // トランザクションの開始
            $db = $this->app->dbAdapter;

            try {
                $db->getDriver()->getConnection()->beginTransaction();

                // 更新処理
                $this->updateCombinedList($combinedListId, $params, $userId);

                // コミット
                $db->getDriver()->getConnection()->commit();

                // 画面を再表示
                // 更新メッセージ表示フラグをセッションに設定
                $_SESSION[self::SESS_SAVEAFTER_MSG] = "u";
                // 名寄せリストIDをパラメータに指定
                return $this->_redirect("unifiinstr/unifiinstr/combinedlistid/" . $combinedListId);
            }
            catch (\Exception $err) {
                // ロールバック
                $db->getDriver()->getConnection()->rollBack();

                // エラーがあればエラーメッセージをセット
                $errors[] = $err->getMessage();
                $this->view->assign('errors', $errors);
                $this->view->assign('process', '統合指示');

                // ビューに入力を割り当てる
                // 名寄せリストID
                $this->view->assign('combinedlistid', $combinedListId);
                // 管理統合指示一覧
                $data = $this->getListInfo($params);
                $this->view->assign('list', $data);

                // 再表示　入力を維持
                $this->setTemplate('unifiinstr');

                return $this->view;
            }

        }
    }

    /**
     * 更新内容チェック
     *
     * @access private
     * @param array $params 画面情報
     * @return array エラーメッセージリスト
     */
    private function checkSaveData($params)
    {
        // 統合指示件数
        $instrCnt = 0;

        // 統合指示した類似顧客件数
        $likeCnt = 0;

        // 管理統合指示チェック
        $listValidFlg = 0;

        $mdlCL = new TableCombinedList($this->app->dbAdapter);
        $mdl = new TableManagementCustomer($this->app->dbAdapter);

        $i = 0;

        // 内容チェック 管理統合指示一覧
        while (isset($params["ManCustId" . $i]))
        {
            // 管理統合指示が削除されている場合カウント
            $cl = $mdlCL->find($params["CombinedListIdSave"], $params["ManCustId" . $i])->current();
            if (is_null($cl['ManCustId'])) {
                $listValidFlg++;
            }

            // 統合指示フラグ
            if (array_key_exists("CombinedDictateFlg" . $i, $params) && $params["CombinedDictateFlg" . $i] == 'on')
            {
                // チェックオン時、カウント
                $instrCnt++;

                // 類似顧客フラグ
                if (array_key_exists("LikenessFlg" . $i, $params) && $params["LikenessFlg" . $i] == 'on')
                {
                    // 統合指示、類似顧客の両方がチェックオンの場合カウント
                    $likeCnt++;
                }
            }

            // 管理顧客番号
            $manCustId = $params["ManCustId" . $i];
            $customer = $mdl->find($manCustId)->current();
            if ($customer['ValidFlg'] == 0) {
                // 削除顧客に追加
                $delCustomer[] = $manCustId;
            }

            $i++;
        }

        $ManCustIdCnt = $i;

        $i = 0;

        // 内容チェック 類似顧客検索結果
        while (isset($params["SearchManCustId" . $i]))
        {
            // 統合指示フラグ
            if (array_key_exists("SearchCombinedDictateFlg" . $i, $params) && $params["SearchCombinedDictateFlg" . $i] == 'on')
            {
                // チェックオン時、カウント
                $instrCnt++;

                // 類似顧客フラグ
                if (array_key_exists("SearchLikenessFlg" . $i, $params) && $params["SearchLikenessFlg" . $i] == 'on')
                {
                    // 統合指示、類似顧客の両方がチェックオンの場合カウント
                    $likeCnt++;
                }
            }

            // 管理顧客番号
            $manCustId = $params["SearchManCustId" . $i];
            $customer = $mdl->find($manCustId)->current();
            if ($customer['ValidFlg'] == 0) {
                // 削除顧客に追加
                $delCustomer[] = $manCustId;
            }

            $i++;
        }

        // 管理統合指示一覧が0件の場合エラー
        if ($ManCustIdCnt == 0) {
            $errors[] = "管理統合指示に対象が存在しません。";
        }

        // 統合指示が削除されている場合
        if ($listValidFlg > 0) {
            $errors[] = "管理統合指示が削除されています。名寄せリストに戻ってください。";
        }

        // 統合指示が1件以下の場合エラー
        if ($instrCnt == 1) {
            $errors[] = "2件以上の顧客に統合指示を行ってください。";
        }

        // 統合指示した全顧客が類似顧客になっている場合エラー
        if ($instrCnt > 0 && $instrCnt == $likeCnt) {
            $errors[] = "統合先とする顧客の類似顧客ﾁｪｯｸを外してください。";
        }

        // 削除された顧客が存在する場合エラー
        // count関数対策
        if (isset($delCustomer) && !empty($delCustomer)) {
            $strtemp = "削除された顧客が統合指示に含まれています。（%s）";
            foreach ($delCustomer as $delCust) {
                $errors[] = sprintf($strtemp, $delCust);
            }
        }

        // 統合先が複数の場合エラー
        if ($instrCnt > 0 && ($instrCnt - $likeCnt) > 1) {
            $errors[] = "統合先とする顧客は1件のみです。統合する他の顧客には類似顧客ﾁｪｯｸを入れてください。";
        }

        // 結果を返す
        return $errors;
    }

    /**
     * 画面情報の管理統合指示一覧を取得
     * オペレータの入力を維持
     *
     * @access private
     * @param array $params 画面情報
     * @return array 管理統合指示一覧情報
     */
    private function getListInfo($params)
    {
        $i = 0;

        // 内容チェック
        while (isset($params["ManCustId" . $i]))
        {
            // 値のクリア
            unset($row);

            // 管理顧客番号
            $row['ManCustId'] = $params["ManCustId" . $i];

            // 顧客名
            $row['NameKj'] = $params["NameKj" . $i];

            // 顧客名カナ
            $row['NameKn'] = $params["NameKn" . $i];

            // 住所
            $row['UnitingAddress'] = $params["Address" . $i];

            // 電話番号
            $row['Phone'] = $params["Phone" . $i];

            // メールアドレス
            $row['MailAddress'] = $params["MailAddress" . $i];

            // 類似顧客フラグ
            if (array_key_exists('LikenessFlg' . $i, $params) && $params["LikenessFlg" . $i] == 'on')
            {
                $row['LikenessFlg'] = 1;
            }
            else
            {
                $row['LikenessFlg'] = 0;
            }

            // 統合指示フラグ
            if (array_key_exists("CombinedDictateFlg" . $i, $params) && $params["CombinedDictateFlg" . $i] == 'on')
            {
                $row['CombinedDictateFlg'] = 1;
            }
            else
            {
                $row['CombinedDictateFlg'] = 0;
            }

            // 一覧情報に追加
            $list[] = $row;

            $i++;
        }

        if (!isset($list)) {
            $list = array();
        }

        // 結果を返す
        return $list;
    }

    /**
     * 名寄せリストテーブル更新
     *
     * @access private
     * @param int $combinedListId 名寄せリストID
     * @param array $params 画面情報
     * @param string $userId ユーザーID
     * @return なし
     */
    private function updateCombinedList($combinedListId, $params, $userId)
    {
        $mdl = new TableCombinedList($this->app->dbAdapter);

        $i = 0;

        // 更新部分
        while (isset($params["ManCustId" . $i]))
        {
            // 値のクリア
            unset($combinedData);

            // 管理顧客番号
            $combinedData['ManCustId'] = $params["ManCustId" . $i];

            // 類似顧客フラグ
            if (array_key_exists("LikenessFlg" . $i, $params) && $params["LikenessFlg" . $i] == 'on')
            {
                $combinedData['LikenessFlg'] = 1;
            }
            else
            {
                $combinedData['LikenessFlg'] = 0;
            }

            // 統合指示フラグ
            if (array_key_exists("CombinedDictateFlg" . $i, $params) && $params["CombinedDictateFlg" . $i] == 'on')
            {
                $combinedData['CombinedDictateFlg'] = 1;
            }
            else
            {
                $combinedData['CombinedDictateFlg'] = 0;
            }

            // 統合指示日時
            $combinedData['CombinedDictateDate'] = date('Y-m-d H:i:s');

            // 更新者
            $combinedData['UpdateId'] = $userId;

            // 更新実行
            $mdl->saveUpdate($combinedData, $combinedListId, $params["ManCustId" . $i]);

            $i++;
        }
    }

    /**
     * 検索処理
     */
    public function searchAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        // パラメータ取得
        $params = $this->getParams();
        // 名寄せリストID
        $combinedListId = $params['CombinedListIdSearch'];

        // 画面条件 リスト化して取得
        $sKeyar = $this->getRequest()->getPost()->toArray();
        $sKey = (array_key_exists('key', $sKeyar)) ? $sKeyar['key'] : null;

        // 統合指示後の画面再描画時、検索条件はパラメータでなくセッションから取得
        // 名寄せリストID
        if (!isset($combinedListId) && isset($_SESSION[self::SESS_SAVEAFTER_CID])) {
            $combinedListId = $_SESSION[self::SESS_SAVEAFTER_CID];
        }
        // 画面条件
        if (!isset($sKey) && isset($_SESSION[self::SESS_SAVEAFTER_SKEY])){
            $sKey = $_SESSION[self::SESS_SAVEAFTER_SKEY];
        }

        // 管理統合指示データ取得
        $list = $this->getCombiledList($combinedListId);
        // データがない場合
        // count関数対策
        if (!isset($list) || empty($list)) {
            // エラーがあればエラーメッセージをセット
            $errors[] = "管理統合指示に対象が存在しません。";
            $this->view->assign('errors', $errors);
            $this->view->assign('process', '検索');

            // ビューに入力を割り当てる
            // 名寄せリストID
            $this->view->assign('combinedlistid', $combinedListId);
            // 管理統合指示一覧
            $this->view->assign('list', $list);
            // 類似顧客検索条件
            $this->view->assign('key', $sKey);

            // 再表示　入力を維持
            $this->setTemplate('unifiinstr');

            return $this->view;
        }

        // 更新内容チェック
        $errors = $this->checkSearchData($sKey);
        // count関数対策
        if (isset($errors) && !empty($errors))
        {
            // エラーがあればエラーメッセージをセット
            $this->view->assign('errors', $errors);
            $this->view->assign('process', '検索');

            // ビューに入力を割り当てる
            // 名寄せリストID
            $this->view->assign('combinedlistid', $combinedListId);
            // 管理統合指示一覧
            $this->view->assign('list', $list);
            // 類似顧客検索条件
            $this->view->assign('key', $sKey);

            // 再表示　入力を維持
            $this->setTemplate('unifiinstr');

            return $this->view;
        }
        else
        {
            // エラーがなければ検索処理を行う
            // 類似顧客検索結果取得
            $ar = $this->getSearchLikenessCustomer($list, $sKey);

            // 取得データを反映
            // 名寄せリストID
            $this->view->assign('combinedlistid', $combinedListId);
            // 管理統合指示一覧
            $this->view->assign('list', $list);
            // 類似顧客検索結果
            $this->view->assign('slist', $ar);
            // 類似顧客検索条件
            $this->view->assign('key', $sKey);

            // 更新後の場合、メッセージ表示
            if (isset($_SESSION[self::SESS_SAVEAFTER_MSG])) {
                unset($_SESSION[self::SESS_SAVEAFTER_MSG]);
                $this->view->assign('message', sprintf('<font color="red"><b>名寄せリストを更新しました。</b></font>'));
            }

            return $this->view;
        }
    }

    /**
     * 検索条件チェック
     *
     * @access private
     * @param unknown $sKey
     * @return array エラーメッセージリスト
     */
    private function checkSearchData($sKey)
    {
        // 検索条件カウント
        $keyCnt = 0;

        // 顧客名
        if (isset($sKey['NameKj']) && $sKey['NameKj'] != '') {
            $keyCnt++;
        }
        // 顧客名カナ
        if (isset($sKey['NameKn']) && $sKey['NameKn'] != '') {
            $keyCnt++;
        }
        // 郵便番号
        if (isset($sKey['PostalCode']) && $sKey['PostalCode'] != '') {
            $keyCnt++;
        }
        // 住所
        if (isset($sKey['Address']) && $sKey['Address'] != '') {
            $keyCnt++;
        }
        // 電話番号
        if (isset($sKey['Phone']) && $sKey['Phone'] != '') {
            $keyCnt++;
        }
        // メールアドレス
        if (isset($sKey['MailAddress']) && $sKey['MailAddress'] != '') {
            $keyCnt++;
        }

        if ($keyCnt == 0) {
            $errors[] = "検索条件が入力されていません。";
        }

        return $errors;
    }

    /**
     * 類似顧客検索結果の情報を取得
     * データベースから取得
     *
     * @access private
     * @param int $list 管理統合指示一覧データ
     * @param array $sKey 検索条件
     * @return array 類似顧客検索結果データ
     */
    private function getSearchLikenessCustomer($list, $sKey)
    {
        // 類似顧客検索データ取得
        $sql  = " SELECT a.ManCustId ";
        $sql .= "      , a.NameKj ";
        $sql .= "      , a.NameKn ";
        $sql .= "      , a.UnitingAddress ";
        $sql .= "      , a.Phone ";
        $sql .= "      , a.MailAddress ";
        $sql .= "      , 0 AS LikenessFlg ";
        $sql .= "      , 0 AS CombinedDictateFlg ";
        $sql .= "   FROM T_ManagementCustomer a ";
        $sql .= "  WHERE a.ValidFlg = 1 ";

        // 管理統合指示一覧の顧客は除く
        $sql .= " AND a.ManCustId NOT IN (";
        $i = 0;
        foreach ($list as $row) {
            if ($i != 0) {
                $sql .= ",";
            }

            $sql .= $row["ManCustId"];

            $i++;
        }
        $sql .= ") ";

        // 画面条件
        // 顧客名
        if (isset($sKey['NameKj']) && $sKey['NameKj'] != '') {
            $sql .= " AND a.SearchNameKj LIKE '%" . BaseUtility::escapeWildcard($sKey['NameKj']) . "%' ";
        }
        // 顧客名カナ
        if (isset($sKey['NameKn']) && $sKey['NameKn'] != '') {
            $sql .= " AND a.SearchNameKn LIKE '%" . BaseUtility::escapeWildcard($sKey['NameKn']) . "%' ";
        }
        // 郵便番号
        if (isset($sKey['PostalCode']) && $sKey['PostalCode'] != '') {
            $sql .= " AND a.PostalCode LIKE '%" . BaseUtility::escapeWildcard($sKey['PostalCode']) . "' ";
        }
        // 住所
        if (isset($sKey['Address']) && $sKey['Address'] != '') {
            $sql .= " AND a.SearchUnitingAddress LIKE '%" . BaseUtility::escapeWildcard($sKey['Address']) . "%' ";
        }
        // 電話番号
        if (isset($sKey['Phone']) && $sKey['Phone'] != '') {
            $sql .= " AND a.SearchPhone LIKE '%" . BaseUtility::escapeWildcard($sKey['Phone']) . "%' ";
        }
        // メールアドレス
        if (isset($sKey['MailAddress']) && $sKey['MailAddress'] != '') {
            $sql .= " AND a.MailAddress LIKE '%" . BaseUtility::escapeWildcard($sKey['MailAddress']) . "%' ";
        }

        $sql .= "  ORDER BY a.ManCustId DESC ";

        $stm = $this->app->dbAdapter->query($sql);

        $ar = ResultInterfaceToArray($stm->execute(null));

        return $ar;
    }

    /**
     * 統合指示を実行 類似検索結果を含む
     * UPDATE & INSERT
     */
    public function saveAction()
    {
        // リファラーがなければ顧客統合名寄せリストへリダイレクト
        if(!isset($_SERVER['HTTP_REFERER']))
        {
            return $this->_redirect('customeradrslist/list');
        }

        // パラメータ取得
        $params = $this->getParams();
        $combinedListId = $params['CombinedListIdSave'];  // 名寄せリストID

        // 呼び出し元判定
        if (isset($params['research_button']))
        {
            // 再検索ボタンが押された場合、統合指示画面へ遷移
            // 検索条件をセッションに保存
            $sKeyar = $this->getRequest()->getPost()->toArray();
            $sKey = (array_key_exists('key', $sKeyar)) ? $sKeyar['key'] : null;
            unset($_SESSION[self::SESS_SAVEAFTER_SKEY]);
            $_SESSION[self::SESS_SAVEAFTER_SKEY] = $sKey;

            // 統合指示画面へ遷移
            // 名寄せリストIDをパラメータに指定
            return $this->_redirect("unifiinstr/unifiinstr/combinedlistid/" . $combinedListId);
        }
        else
        {
            // 統合指示ボタンが押された場合、更新・登録処理
            // 更新内容チェック
            $errors = $this->checkSaveData($params);
            // count関数対策
            if (isset($errors) && !empty($errors))
            {
                // エラーがあればエラーメッセージをセット
                $this->view->assign('errors', $errors);
                $this->view->assign('process', '統合指示');

                // ビューに入力を割り当てる
                // 名寄せリストID
                $this->view->assign('combinedlistid', $combinedListId);
                // 管理統合指示一覧
                $data = $this->getListInfo($params);
                $this->view->assign('list', $data);
                // 類似顧客検索結果
                $sData = $this->getSearchListInfo($params);
                $this->view->assign('slist', $sData);
                // 検索条件
                $sKeyar = $this->getRequest()->getPost()->toArray();
                $sKey = (array_key_exists('key', $sKeyar)) ? $sKeyar['key'] : null;
                $this->view->assign('key', $sKey);

                // 再表示　入力を維持
                $this->setTemplate('search');

                return $this->view;
            }
            else
            {
                // エラーがない場合、更新処理を行う
                // ユーザーIDの取得
                $obj = new TableUser($this->app->dbAdapter);
                $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                // トランザクションの開始
                $db = $this->app->dbAdapter;

                try {
                    $db->getDriver()->getConnection()->beginTransaction();

                    // 更新処理
                    $this->updateCombinedList($combinedListId, $params, $userId);

                    // 登録処理
                    $this->insertCombinedList($combinedListId, $params, $userId);

                    // コミット
                    $db->getDriver()->getConnection()->commit();

                    // 表示用にセッションを設定
                    // 名寄せリストID
                    unset($_SESSION[self::SESS_SAVEAFTER_CID]);
                    $_SESSION[self::SESS_SAVEAFTER_CID] = $combinedListId;

                    // 検索条件
                    $sKeyar = $this->getRequest()->getPost()->toArray();
                    $sKey = (array_key_exists('key', $sKeyar)) ? $sKeyar['key'] : null;
                    unset($_SESSION[self::SESS_SAVEAFTER_SKEY]);
                    $_SESSION[self::SESS_SAVEAFTER_SKEY] = $sKey;

                    // 画面を再表示
                    // 更新メッセージ表示フラグをセッションに設定
                    $_SESSION[self::SESS_SAVEAFTER_MSG] = "u";
                    return $this->_redirect("unifiinstr/search/");
                }
                catch (\Exception $err) {
                    // ロールバック
                    $db->getDriver()->getConnection()->rollBack();

                    // エラーがあればエラーメッセージをセット
                    $errors[] = $err->getMessage();
                    $this->view->assign('errors', $errors);
                    $this->view->assign('process', '統合指示');

                    // ビューに入力を割り当てる
                    // 名寄せリストID
                    $this->view->assign('combinedlistid', $combinedListId);
                    // 管理統合指示一覧
                    $data = $this->getListInfo($params);
                    $this->view->assign('list', $data);
                    // 類似顧客検索結果
                    $sData = $this->getSearchListInfo($params);
                    $this->view->assign('slist', $sData);
                    // 検索条件
                    $sKeyar = $this->getRequest()->getPost()->toArray();
                    $sKey = (array_key_exists('key', $sKeyar)) ? $sKeyar['key'] : null;
                    $this->view->assign('key', $sKey);

                    // 再表示　入力を維持
                    $this->setTemplate('search');

                    return $this->view;
                }
            }
        }
    }

    /**
     * 画面情報の類似顧客検索結果を取得
     * オペレータの入力を維持
     *
     * @access private
     * @param array $params 画面情報
     * @return array 類似顧客検索結果情報
     */
    private function getSearchListInfo($params)
    {
        $i = 0;

        // 内容チェック
        while (isset($params["SearchManCustId" . $i]))
        {
            // 値のクリア
            unset($row);

            // 管理顧客番号
            $row['ManCustId'] = $params["SearchManCustId" . $i];

            // 顧客名
            $row['NameKj'] = $params["SearchNameKj" . $i];

            // 顧客名カナ
            $row['NameKn'] = $params["SearchNameKn" . $i];

            // 住所
            $row['UnitingAddress'] = $params["SearchAddress" . $i];

            // 電話番号
            $row['Phone'] = $params["SearchPhone" . $i];

            // メールアドレス
            $row['MailAddress'] = $params["SearchMailAddress" . $i];

            // 類似顧客フラグ
            if (array_key_exists('SearchLikenessFlg' . $i, $params) && $params["SearchLikenessFlg" . $i] == 'on')
            {
                $row['LikenessFlg'] = 1;
            }
            else
            {
                $row['LikenessFlg'] = 0;
            }

            // 統合指示フラグ
            if (array_key_exists("SearchCombinedDictateFlg" . $i, $params) && $params["SearchCombinedDictateFlg" . $i] == 'on')
            {
                $row['CombinedDictateFlg'] = 1;
            }
            else
            {
                $row['CombinedDictateFlg'] = 0;
            }

            // 一覧情報に追加
            $slist[] = $row;

            $i++;
        }

        // 結果を返す
        return $slist;
    }

    /**
     * 名寄せリストテーブル登録
     *
     * @access private
     * @param int $combinedListId 名寄せリストID
     * @param array $params 画面情報
     * @param string $userId ユーザーID
     * @return なし
     */
    private function insertCombinedList($combinedListId, $params, $userId)
    {
        $mdl = new TableCombinedList($this->app->dbAdapter);

        $i = 0;

        // 更新部分
        while (isset($params["SearchManCustId" . $i]))
        {
            // 値のクリア
            unset($combinedData);

            // 名寄せリストID
            $combinedData['CombinedListId'] = $combinedListId;

            // 管理顧客番号
            $combinedData['ManCustId'] = $params["SearchManCustId" . $i];

            // 類似顧客フラグ
            if (array_key_exists("SearchLikenessFlg" . $i, $params) && $params["SearchLikenessFlg" . $i] == 'on')
            {
                $combinedData['LikenessFlg'] = 1;
            }
            else
            {
                $combinedData['LikenessFlg'] = 0;
            }

            // 統合指示フラグ
            if (array_key_exists("SearchCombinedDictateFlg" . $i, $params) && $params["SearchCombinedDictateFlg" . $i] == 'on')
            {
                $combinedData['CombinedDictateFlg'] = 1;
            }
            else
            {
                $combinedData['CombinedDictateFlg'] = 0;
            }

            // 統合指示日時
            $combinedData['CombinedDictateDate'] = date('Y-m-d H:i:s');

            // 統合日時
            $combinedData['CombinedDate'] = null;

            // 名寄せレベル
            $combinedData['AggregationLevel'] = null;

            // 登録者
            $combinedData['RegistId'] = $userId;

            // 更新者
            $combinedData['UpdateId'] = $userId;

            // 有効フラグ
            $combinedData['ValidFlg'] = 1;

            // 統合指示を行った場合のみ、登録を行う
            if ($combinedData['CombinedDictateFlg'] == '1') {
                $mdl->saveNew($combinedData);
            }

            $i++;
        }
    }

    private function checkAdminPermission() {
        // 権限により機能制約を設けるなら以下のコメントアウトを解除する
        // TODO: 抽象アクションクラスをもう1層設けるなりして、もう少し共通化したい（09.07.17 eda）
        //if( ! $this->isAdmin ) throw new Exception('権限がありません');
    }
}
