<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\View\ViewOrderCustomer;
use Coral\Coral\CoralCodeMaster;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableOrder;
use models\Logic\LogicMergeOrder;
use models\Table\TableUser;
use models\Sequence\SequenceGeneral;
use models\Table\TableOem;
use models\Logic\LogicSbps;

/**
 * WebAPIユーザを管理するコントローラ
 */
class CombinedclaimController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';
    /**
     * アプリケーションインスタンス
     * @var NetB_Application_Abstract
     */
    protected $app;

    /**
     * 事業者テーブル
     * @var TableEnterprise
     */
    protected $enttable;

    /**
     * サイトテーブル
     * @var TableSite
     */
    protected $sitetable;

    /**
     * 注文テーブル
     * @var TableOrder
     */
    protected $ordertable;

    /**
     * コントローラ初期化
     */
    protected function _init() {
        $this->app = Application::getInstance();
        $this->app->addClass('Logic_MergeOrder');
        $this->app->addClass('View_OrderCustomer');
        $this->app->addClass('');
        $this->app->addClass('Table_Enterprise');
        $this->app->addClass('Table_Site');

        // CSS、JSファイルを追加
        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/combinedclaim/tab_support.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json+.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/sortable_ja.js');
        // ページタイトル
        $this->setPageTitle("後払い.com - 請求取りまとめ事業者管理");

        $this->view->assign( 'userInfo', $this->app->authManagerAdmin->getUserInfo() );
        $this->view->assign( 'mode', 'add' );

        $this->enttable = new TableEnterprise($this->app->dbAdapter);
        $this->sitetable = new TableSite($this->app->dbAdapter);
        $this->ordercusview= new ViewOrderCustomer($this->app->dbAdapter);
    }

    /**
     * listAction
     * 請求取りまとめ事業者一覧表示
     */
    public function listAction() {
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $enterprises = array();
        $ri = $this->enttable->fetchAll('ValidFlg = 1 and CombinedClaimMode in (1, 2)', 'EnterpriseId DESC');
        $rs = new ResultSet();
        $array = $rs->initialize($ri)->toArray();
        foreach( $array as $row) {
            $entarray = $row;
            $entarray['CombinedClaimModeCap'] = $codeMaster->getCombinedClaimModeCaption($entarray['CombinedClaimMode']);
            $enterprises[] = $entarray;
        }
        $this->view->assign('list', $enterprises);
        return $this->view;
    }

    /**
     * addAction
     * 新規請求取りまとめ登録フォーム
     */
    public function addAction() {

        $eid = $this->params()->fromRoute("eid", -1);

        $enterprise = $this->enttable->findEnterprise2($eid);

        $sites = $this->sitetable->getValidAll($eid);

        $data['EnterpriseId'] = $enterprise['EnterpriseId'];
        $data['EnterpriseNameKj'] = $enterprise['EnterpriseNameKj'];
        $data['CombinedClaimMode'] = $enterprise['CombinedClaimMode'];

        $i = 0;
        foreach($sites as $site) {
            $data['SiteId'.$i] = $site->SiteId;
            $data['SiteNameKj'.$i] = $site->SiteNameKj;
            $data['CombinedClaimFlg'.$i] = $site->CombinedClaimFlg;
            $i++;
        }

        $codeMaster = new Coral_CodeMaster($this->app->dbAdapter);
        $data['combinedclaimmodelist'] = $codeMaster->getCombinedClaimMode();
        $this->view->assign('data', $data);
        $this->view->assign('mode', 'add');

        return $this->view;
    }

    /**
     * editAction
     * 請求取りまとめ編集フォーム
     */
    public function editAction() {
        $eid = $this->params()->fromRoute("eid", -1);

        $enterprise = $this->enttable->findEnterprise2($eid)->current();

        $sites = $this->sitetable->getValidAll($eid);

        $data['EnterpriseId'] = $enterprise['EnterpriseId'];
        $data['EnterpriseNameKj'] = $enterprise['EnterpriseNameKj'];
        $data['CombinedClaimMode'] = $enterprise['CombinedClaimMode'];
        $data['CombinedClaimFlgReg'] = is_null($enterprise['CombinedClaimFlg']) ? 0 : $enterprise['CombinedClaimFlg'];
        $data['AutoCombinedClaimDay'] = $enterprise['AutoCombinedClaimDay'];

        $i = 0;
        foreach($sites as $site) {
            $data['SiteId'.$i] = $site['SiteId'];
            $data['SiteNameKj'.$i] = $site['SiteNameKj'];
            $data['CombinedClaimFlg'.$i] = $site['CombinedClaimFlg'];
            $i++;
        }

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $data['combinedclaimmodelist'] = $codeMaster->getCombinedClaimMode();
        $data['combinedclaimflgreglist'] = array(1 => 'する', 0 => 'しない');
        $this->view->assign('data', $data);
        $this->view->assign('nowCCMode', $enterprise['CombinedClaimMode']);

        return $this->view;
    }

    /**
     * 事業者登録内容の確認
     */
    public function confirmAction() {
        // POSTされたデータを取得
        $data = $this->getRequest()->getPost('data', array());//-------------------------------------------------

        // 請求取りまとめモードのキャプション設定
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $data['combinedclaimmodelist']  = $mode = $codeMaster->getCombinedClaimMode();
        foreach($mode as $value => $caption ) {
            if($value == $data['CombinedClaimMode'] ) {
                    $data['CombinedClaimModeCap'] = $caption;
                    break;
                }
        }
        // 請求取りまとめ（定期購入）のキャプション設定
        $data['combinedclaimflgreglist'] = array(1 => 'する', 0 => 'しない');
        foreach($data['combinedclaimflgreglist'] as $value => $caption ) {
            if($value == $data['CombinedClaimFlgReg'] ) {
                $data['CombinedClaimFlgRegCap'] = $caption;
                break;
            }
        }

        // チェック
        $checked = 0;
        $i = 0;
        while (array_key_exists('SiteId' . $i, $data)) {
            if ((array_key_exists('CombinedClaimFlg' . $i, $data)) == true && $data['CombinedClaimFlg' . $i] == "on" && $data['CombinedClaimMode'] == 2)
            {
                $data['CombinedClaimCap'.$i] = "取りまとめる";
                $checked++;
            } else {
                $data['CombinedClaimCap'.$i] = "取りまとめない";
            }
            $i++;
        }

        // 請求取りまとめモードがサイト毎の場合にサイトが選択されていない場合にはエラー
        if($data["CombinedClaimMode"] == 2) {
            if($checked == 0 ) {
                $errors = '請求取りまとめモードをサイト毎に設定する場合にはサイトにチェックを入れてください。';

                // 検証エラーは入力画面へ戻す
                $this->view->assign('data', $data);
                $this->view->assign('error', $errors);

                $this->setTemplate('edit');
                return $this->view;
            }
        }

        // 自動請求取りまとめ指定日が0～31の数値でない場合エラー（未入力は0判定）
        if (strlen($data['AutoCombinedClaimDay']) > 0) {
            if (!(is_numeric($data['AutoCombinedClaimDay']))) {
                $errors = '自動請求取りまとめ指定日が不正です。';
            }
            elseif ((int)$data['AutoCombinedClaimDay'] < 0 || (int)$data['AutoCombinedClaimDay'] > 31) {
                $errors = '自動請求取りまとめ指定日が不正です。';
            }

            if (isset($errors)) {
                // 検証エラーは入力画面へ戻す
                $i=0;
                while (array_key_exists('SiteId' . $i, $data)) {
                    if ((array_key_exists('CombinedClaimFlg' . $i, $data)) == true && $data['CombinedClaimFlg' . $i] == "on" && $data['CombinedClaimMode'] == 2)
                    {
                        $data['CombinedClaimFlg'.$i] = 1;
                    } else {
                        $data['CombinedClaimFlg'.$i] = 0;
                    }
                    $i++;
                }
                $this->view->assign('data', $data);
                $this->view->assign('error', $errors);

                $this->setTemplate('edit');
                return $this->view;
            }
        }

        // 請求取りまとめモードが現在0もしくはnull以外でモードをなしに変更する場合にはWarning
        $enterprise = $this->enttable->findEnterprise2($data["EnterpriseId"])->current();
        if($data["CombinedClaimMode"] == 0 && ($enterprise['CombinedClaimMode'] == 1 || $enterprise['CombinedClaimMode'] == 2)) {
            $this->view->assign('warning', "請求取りまとめモードをなしに設定すると、全ての注文が個別に請求されます。");
        }

        // フォームデータ自身をエンコード
        $formData = base64_encode(serialize($data));
        
        // if Enterprise registed Todo and CombinedClaimMode != 0 then show error
        if ($data["CombinedClaimMode"] != 0) {
            $logicSbps = new LogicSbps($this->app->dbAdapter);
            $checkTodo = $logicSbps->checkSettingTodo($data['EnterpriseId']);
            if ($checkTodo['isValid']) {
                $errors = '届いてから払いの設定が行われているため、修正できません。届いてから払いを無効にした後、修正してくだい。';
                $this->view->assign('data', $data);
                $this->view->assign('error', $errors);
                $this->setTemplate('edit');
                return $this->view;
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('encoded_data', $formData);
        return $this->view;
    }

    /**
     * backAction
     * 確認画面から変更フォームへ戻る
     */
    public function backAction() {
        // エンコード済みのPOSTデータを復元する
// 		$data = unserialize(base64_decode($this->getRequest()->getPost('hash')));//-------------------------------------
        $data = unserialize(base64_decode($this->getRequest()->getPost('hash')));
        if( $data == null ) {
            throw new \Exception('編集中のデータが失われました');
        }

        $i=0;
        //while (($data["SiteId" . $i]))
        while (array_key_exists('SiteId' . $i, $data)) {
            if ((array_key_exists('CombinedClaimFlg' . $i, $data)) == true && $data['CombinedClaimFlg' . $i] == "on") {
                $data['CombinedClaimFlg'.$i] = 1;
            } else {
                $data['CombinedClaimFlg'.$i] = 0;
            }
            $i++;
        }
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $data['combinedclaimmodelist'] = $codeMaster->getCombinedClaimMode();
        $this->view->assign('data', $data);

// 		$this->_helper->viewRenderer('edit');
        $this->setTemplate('edit');

        return $this->view;
    }

    /**
     * saveAction
     * 新規・編集の永続化処理
     */
    public function saveAction() {
        // エンコード済みのPOSTデータを復元する
        $data = unserialize(base64_decode($this->getRequest()->getPost('hash')));
        if( $data == null ) {
            throw new \Exception('編集中のデータが失われました');
        }

        // 現状の請求取りまとめモードを取得する
        $enterprise = $this->enttable->findEnterprise2($data['EnterpriseId'])->current();
        $ccMode = $enterprise['CombinedClaimMode'];

        // トランザクションの開始
        $db = $this->app->dbAdapter;

        try {
            $db->getDriver()->getConnection()->beginTransaction();
            // 事業者テーブルへの更新
            $this->enttable->saveUpdate(array(
                                            'CombinedClaimMode' => $data['CombinedClaimMode'],
                                            'CombinedClaimFlg' => $data['CombinedClaimFlgReg'],
                                            'AutoCombinedClaimDay' => $data['AutoCombinedClaimDay'],
                                        )
                                        , $data['EnterpriseId']);

            // サイトテーブルへの更新
            $i=0;
            while (array_key_exists('SiteId' . $i, $data)) {
                if ((array_key_exists('CombinedClaimFlg' . $i, $data)) == true && $data['CombinedClaimFlg' . $i] == "on" && $data['CombinedClaimMode'] == 2)
                {
                    $data['CombinedClaimFlg'.$i] = 1;
                } else {
                    $data['CombinedClaimFlg'.$i] = 0;
                }
                $this->sitetable->saveUpdate(array('CombinedClaimFlg' => $data['CombinedClaimFlg'.$i]), $data["SiteId" . $i]);
                $i++;
            }

            // 請求取りまとめモードを事業者毎/サイト毎から「なし」にした場合には請求取りまとめモードを落とす
            if(($ccMode == 1 || $ccMode == 2) && $data['CombinedClaimMode'] == 0) {
                $ordertable= new TableOrder($this->app->dbAdapter);

                $ordertable->updateCombinedClaimStatus($data['EnterpriseId']);
            }

            $db->getDriver()->getConnection()->commit();
        }
        catch (\Exception $err) {
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        // 詳細ページへリダイレクト
        return $this->_redirect( sprintf("combinedclaim/detail/eid/%s", $data['EnterpriseId']) );
    }

    /**
     * detailAction
     * 請求取りまとめ詳細表示
     */
    public function detailAction() {
        $eid = $this->params()->fromRoute("eid", -1);

        $enterprise = $this->enttable->findEnterprise2($eid)->current();

        $ri = $this->sitetable->getValidAll($eid);
        $rs = new ResultSet();
        $sites = $rs->initialize($ri)->toArray();

        // 請求取りまとめモードのキャプション設定
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mode = $codeMaster->getCombinedClaimMode();
        foreach($mode as $value => $caption ) {
            if($value == $enterprise['CombinedClaimMode'] ) {
                $combinedClaimModeCap = $caption;
                break;
            }
        }
        // 請求取りまとめ（定期購入）のキャプション設定
        $data['combinedclaimflgreglist'] = array(1 => 'する', 0 => 'しない');
        foreach($data['combinedclaimflgreglist'] as $value => $caption ) {
            if($value == $enterprise['CombinedClaimFlg'] ) {
                $combinedClaimFlgRegCap = $caption;
                break;
            }
        }
        // サイト取りまとめのキャプション設定
        $combinedClaimCap = array();
        $i = 0;
        foreach($sites as $site) {
            if ($site['CombinedClaimFlg'] == 1) {
                $combinedClaimCap[$i] = "取りまとめる";
            } else {
                $combinedClaimCap[$i] = "取りまとめない";
            }
            $i++;
        }
        $this->view->assign('enterprise', $enterprise);
        $this->view->assign('sites', $sites);
        $this->view->assign('combinedClaimCap', $combinedClaimCap);
        $this->view->assign('combinedClaimModeCap', $combinedClaimModeCap);
        $this->view->assign('combinedClaimFlgRegCap', $combinedClaimFlgRegCap);

        return $this->view;
    }

    /**
     * 注文一覧を表示する
     *
     */
    public function orderlistAction()
    {
        $param = $this->getParams();

        $eid = isset($param['eid'] ) ? $param['eid'] : -1;
        $mode = isset($param['mode'] ) ? $param['mode'] : -1;
        $data = $param['data'];

        // サイト情報取得
        $ri = $this->sitetable->getSiteListByCombinedClaim($eid);
        $sites = ResultInterfaceToArray($ri);

        // 注文一覧の取得
        $orderlist = false;

        if ((!isset($param["combinedStatus"])) || (isset($param["combinedStatus"]) && $param["combinedStatus"] == '0')) {
            if($mode == 1) {
                $orderlist = $this->ordercusview->getMergeOrderByEnterprise($eid);
            } else if($mode == 2) {
                // 選択中のサイトの対象注文一覧を取得（パラメータのSiteIdがない場合には一番若いサイトの注文を表示）
                $orderlist = $this->ordercusview->getMergeOrderBySite($eid, isset($data['sid']) ? $data['sid'] : $sites[0]['SiteId']);
            }
        } else if ((isset($param["combinedStatus"]) && $param["combinedStatus"] == '1')) {
            if($mode == 1) {
                $orderlist = $this->ordercusview->getMergeOrderCancelByEnterprise($eid);
            } else if($mode == 2) {
                // 選択中のサイトの対象注文一覧を取得（パラメータのSiteIdがない場合には一番若いサイトの注文を表示）
                $orderlist = $this->ordercusview->getMergeOrderCancelBySite($eid, isset($data['sid']) ? $data['sid'] : $sites[0]['SiteId']);
            }
        }

        $this->view->assign('eid', $eid);
        $this->view->assign('mode', $mode);
        $this->view->assign('combinedStatus', $param["combinedStatus"]);
        $this->view->assign('sid', isset($data['sid']) ? $data['sid'] : $sites[0]['SiteId']);
        $this->view->assign('sites', $sites);
        $this->view->assign('list', $orderlist);
        return $this->view;
    }

    /**
     * 注文一覧を表示する
     *
     */
    public function orderlistbysiteAction()
    {
        $param = $this->getParams();

        $eid = isset($param['eid'] ) ? $param['eid'] : -1;
        $mode = isset($param['mode'] ) ? $param['mode'] : -1;
        $sid = isset($param['site'] ) ? $param['site'] : -1;
        $combinedStatus = isset($param['status'] ) ? $param['status'] : -1;

        // サイト情報取得
        $ri = $this->sitetable->getSiteListByCombinedClaim($eid);
        $sites = ResultInterfaceToArray($ri);

        $orderlist = false;
        if ($combinedStatus == '0') {
                // 選択中のサイトの対象注文一覧を取得
                $orderlist = $this->ordercusview->getMergeOrderBySite($eid, $sid);
        } else if ($combinedStatus == '1') {
            // 選択中のサイトの対象注文一覧を取得
            $orderlist = $this->ordercusview->getMergeOrderCancelBySite($eid, $sid);
        }

        $this->view->assign('eid', $eid);
        $this->view->assign('mode', $mode);
        $this->view->assign('sid', $sid);
        $this->view->assign('sites', $sites);
        $this->view->assign('list', $orderlist);
        $this->view->assign('combinedStatus', $combinedStatus);

        $this->setTemplate('orderlist');
        return $this->view;
    }

    /**
     * 取りまとめ情報の確認画面を表示する
     */
    public function mergeconfirmAction() {
        // POSTされたデータを取得
        $data = $this->params()->fromPost('data', array());

        // 取りまとめキャンセル情報をセットする。
        $param = $this->getParams();
        if ($param['combinedStatus'] == 1) {
            $data['combinedStatus'] = 1;
        }

        // 取りまとめ対象の注文顧客情報を取得する
        $sql = " SELECT voc.*, s.SelfBillingFlg FROM V_OrderCustomer voc INNER JOIN T_Site s ON (s.SiteId = voc.SiteId) WHERE voc.OrderId = :OrderId ";
        $ordercustomers = array();
        $i=0;
        while (array_key_exists('OrderId' . $i, $data)) {
            if(!empty($data["OrderSeq" . $i])) {
                // DBより情報を取得する
                $ordercustomer = $this->app->dbAdapter->query($sql)->execute(array(":OrderId" => $data["OrderId" . $i]))->current();
                array_push($ordercustomers, $ordercustomer);
            }
            $i++;
        }

        if($data["combinedStatus"] == 0){

            // count関数対策
            $orderCustomersCount = 0;
            if (!empty($ordercustomers)) {
                $orderCustomersCount = count($ordercustomers);	
            }

            $errors = "";
            if ($errors == "") {
                // 請求取りまとめは異なるSelfBillingFlgを許容できない
                for($i = 0; $i < $orderCustomersCount; $i++) {
                    if ($ordercustomers[0]['SelfBillingFlg'] != $ordercustomers[$i]['SelfBillingFlg']) {
                        $errors = '請求書同梱の方法が異なるサイト間では、請求取りまとめはできません。';
                        break;
                    }
                }
            }

            if ($errors == "") {
                // 取りまとめ対象の正規化氏名が同一かの判定
                for($i = 0; $i < $orderCustomersCount; $i++) {
                    $regNameKj = ($i == 0) ? $ordercustomers[$i]["RegNameKj"] : $regNameKj;
                    // 正規化氏名が異なる場合にはエラー
                    if(strcmp($regNameKj, $ordercustomers[$i]["RegNameKj"]) != 0) {
                        $errors = '請求先氏名が異なる場合には請求取りまとめはできません。';
                        break;
                    }
                }
            }

            if ($errors != "") {
                $orderlist = false;
                if($data["mode"] == 1) {
                    $orderlist = $this->ordercusview->getMergeOrderByEnterprise($data["eid"]);
                }
                else {
                    // サイト情報取得
                    $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                    // 対象の対象注文一覧を取得
                    $orderlist = $this->ordercusview->getMergeOrderBySite($data["eid"], $data["sid"]);
                }
                // 検証エラーは入力画面へ戻す
                $this->view->assign('eid', $data["eid"]);
                $this->view->assign('mode', $data["mode"]);
                $this->view->assign('sid', $data["sid"]);
                $this->view->assign('sites', $sites);
                $this->view->assign('list', $orderlist);
                $this->view->assign('data', $data);
                $this->view->assign('error', $errors);

                $this->setTemplate('orderlist');
                return $this->view;
            }
        }

        // ビューへアサインするために変数へ代入
        $setCombined = isset($data['combinedStatus']);

        // フォームデータ自身をエンコード
        $data = base64_encode(serialize($data));

        $this->view->assign('ordercustomers', $ordercustomers);
        $this->view->assign('combinedStatus', $setCombined);
        $this->view->assign('encoded_data', $data);
        return $this->view;
    }

    /**
     * 取りまとめ情報の確認画面を表示する
     */
    public function mergeseparateconfirmAction() {
        // POSTされたデータを取得
        $data = $this->params()->fromPost('data', array());

        // 個別取りまとめ情報をセットする
        $data["separate"] = 1;

        // チェックがつけられてる可能性もあるので一旦全部の$data["OrderSeq"]を外す
        $i=0;
        while (array_key_exists('OrderId' . $i, $data)) {
            if(!empty($data["OrderSeq" . $i])) {
                unset($data["OrderSeq" . $i]);
            }
            $i++;
        }

        $ordercustomers = array();
        $i=0;
        while (array_key_exists('OrderId' . $i, $data)) {
            // DBより対象の注文情報を取得する
            $ordercustomer = $this->ordercusview->findOrderCustomer(array("OrderId" => $data["OrderId" . $i]))->current();
            array_push($ordercustomers, $ordercustomer);

            // 全部のOrderSeqを設定する
            $data["OrderSeq".$i] = $ordercustomer["OrderSeq"];

            $i++;
        }

        // ビューへアサインするために結果を変数へ代入
        $setSeparate = isset($data["separate"]);

        // フォームデータ自身をエンコード
        $data = base64_encode(serialize($data));
        $this->view->assign('separate', $setSeparate);
        $this->view->assign('ordercustomers', $ordercustomers);
        $this->view->assign('encoded_data', $data);

        $this->setTemplate('mergeconfirm');
        return $this->view;
    }

    /**
     * 取りまとめ情報の確認画面を表示する
     */
    public function mergesaveAction() {
        // エンコード済みのPOSTデータを復元する
        $data = unserialize(base64_decode($this->getRequest()->getPost('hash')));

        if( $data == null ) {
            throw new \Exception('編集中のデータが失われました');
        }

        // 個別に請求取りまとめ
        if($data["separate"]) {
            $i=0;
            // トランザクションの開始
            $db = $this->app->dbAdapter;

            try {
                $db->getDriver()->getConnection()->beginTransaction();
                while (($data["OrderSeq" . $i])) {
                    // 請求取りまとめ対象かチェック
                    if (!$this->ordercusview->isMergeOrder($data["OrderSeq" . $i])) {
                        // 他端末による更新で対象外になった場合
                        $db->getDriver()->getConnection()->rollBack();

                        $errors = '指定した取りまとめ注文が他の端末から更新されました。確認してください。';

                        // 一旦全部の$data["OrderSeq"]を外す
                        $i=0;
                        while ((isset($data["OrderSeq" . $i]))) {
                            unset($data["OrderSeq" . $i]);
                            $i++;
                        }

                        $orderlist = false;
                        if($data["mode"] == 1) {
                            $orderlist = $this->ordercusview->getMergeOrderByEnterprise($data["eid"]);
                        }
                        else {
                            // サイト情報取得
                            $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                            // 対象の対象注文一覧を取得
                            $orderlist = $this->ordercusview->getMergeOrderBySite($data["eid"], $data["sid"]);
                        }
                        // 検証エラーは入力画面へ戻す
                        $this->view->assign('eid', $data["eid"]);
                        $this->view->assign('mode', $data["mode"]);
                        $this->view->assign('sid', $data["sid"]);
                        $this->view->assign('sites', $sites);
                        $this->view->assign('list', $orderlist);
                        $this->view->assign('data', $data);
                        $this->view->assign('combinedStatus', $data["combinedStatus"]);
                        $this->view->assign('error', $errors);

                        $this->setTemplate('orderlist');
                        return $this->view;
                    }

                    // ユーザIDの取得
                    $userTable = new TableUser($db);
                    $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                    // 加盟店IDの取得
                    $entID = $data["eid"];

                    // 汎用シーケンスの取得
                    $seqTable = new SequenceGeneral($db);
                    $nextVal = $seqTable->nextValue("CombinedDictateGroup");

                    $lgcMergeOrder = new LogicMergeOrder($db);
                    $lgcMergeOrder->merge(array($data["OrderSeq" . $i]), $userID, $entID, $nextVal);
                    $i++;
                }
                $db->getDriver()->getConnection()->commit();
            }
            catch (\Exception $err) {
                $db->getDriver()->getConnection()->rollBack();
                throw $err;
            }
        // 通常請求取りまとめ
        } else {
            $targetseqs = array();
            $i=0;
            while (($data["OrderId" . $i])) {
                if(!empty($data["OrderSeq" . $i])) {
                    array_push($targetseqs, ($data["OrderSeq" . $i]));

                    try {
                        // 請求取りまとめ対象かチェック
                        if ($data["combinedStatus"] == 1) {
                            // 取りまとめキャンセル
                            if (!$this->ordercusview->isMergeOrderCancel($data["OrderSeq" . $i])) {
                                // 他端末による更新で対象外になった場合
                                throw new \Exception('指定した取りまとめ注文が他の端末から更新されました。確認してください。');
                            }
                        }
                        else {
                            // 取りまとめ
                            if (!$this->ordercusview->isMergeOrder($data["OrderSeq" . $i])) {
                                // 他端末による更新で対象外になった場合
                                throw new \Exception('指定した取りまとめ注文が他の端末から更新されました。確認してください。');
                            }
                        }
                    }
                    catch (\Exception $err) {
                        // 他端末による更新で対象外になった場合
                        $errors = $err->getMessage();

                        $orderlist = false;
                        if ($data["combinedStatus"] == 0) {
                            if($data["mode"] == 1) {
                                $orderlist = $this->ordercusview->getMergeOrderByEnterprise($data["eid"]);
                            }
                            else {
                                // サイト情報取得
                                $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                                // 対象の対象注文一覧を取得
                                $orderlist = $this->ordercusview->getMergeOrderBySite($data["eid"], $data["sid"]);
                            }
                        }
                        else {
                            if($data["mode"] == 1) {
                                $orderlist = $this->ordercusview->getMergeOrderCancelByEnterprise($data["eid"]);
                            } else {
                                // サイト情報取得
                                $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                                // 対象の対象注文一覧を取得
                                $orderlist = $this->ordercusview->getMergeOrderCancelBySite($data["eid"], $data["sid"]);
                            }
                        }
                        // 検証エラーは入力画面へ戻す
                        $this->view->assign('eid', $data["eid"]);
                        $this->view->assign('mode', $data["mode"]);
                        $this->view->assign('sid', $data["sid"]);
                        $this->view->assign('sites', $sites);
                        $this->view->assign('list', $orderlist);
                        $this->view->assign('data', $data);
                        $this->view->assign('combinedStatus', $data["combinedStatus"]);
                        $this->view->assign('error', $errors);

                        $this->setTemplate('orderlist');
                        return $this->view;
                    }
                }
                $i++;
            }

            // 対象が1以上あった場合
            // count関数対策
            if (!empty($targetseqs)) {
                // トランザクションの開始
                $db = $this->app->dbAdapter;
                try {
                    $db->getDriver()->getConnection()->beginTransaction();
                    // ユーザIDの取得
                    $userTable = new TableUser($db);
                    $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                    // 加盟店IDの取得
                    $entID = $data["eid"];

                    // 汎用シーケンスの取得
                    $seqTable = new SequenceGeneral($db);
                    $nextVal = $seqTable->nextValue("CombinedDictateGroup");

                    $lgcMergeOrder = new LogicMergeOrder($db);
                    if ($data["combinedStatus"] == 1) {
                        $lgcMergeOrder->mergecancel($targetseqs, $userID);
                    } else {
                        $lgcMergeOrder->merge($targetseqs, $userID, $entID, $nextVal);
                    }
                    $db->getDriver()->getConnection()->commit();
                }
                catch (\Exception $err) {
                    $db->getDriver()->getConnection()->rollBack();
                    throw $err;
                }
            }
        }

        // 注文一覧へリダイレクト
        return $this->_redirect( sprintf("combinedclaim/orderlist/eid/%s/mode/%s", $data["eid"], $data["mode"]) );
    }

    /**
     * backAction
     * 確認画面から変更フォームへ戻る
     */
    public function mergebackAction() {
        // エンコード済みのPOSTデータを復元する
        $data = unserialize(base64_decode($this->getRequest()->getPost('hash')));
        if( $data == null ) {
            throw new \Exception('編集中のデータが失われました');
        }

        $orderlist = false;
        if($data["mode"] == 1) {
            if ($data["combinedStatus"] == 0) {
                $orderlist = $this->ordercusview->getMergeOrderByEnterprise($data["eid"]);
            } else {
                $orderlist = $this->ordercusview->getMergeOrderCancelByEnterprise($data["eid"]);
            }
        } else {
            // サイト情報取得
            $ri = $this->sitetable->getSiteListByCombinedClaim($data["eid"]);
            $rs = new ResultSet();
            $sites = $rs->initialize($ri)->toArray();

            // 対象の対象注文一覧を取得
            if ($data["combinedStatus"] == 0) {
                $orderlist = $this->ordercusview->getMergeOrderBySite($data["eid"], $data["sid"]);
            } else {
                $orderlist = $this->ordercusview->getMergeOrderCancelBySite($data["eid"], $data["sid"]);
            }
        }

        // 全個別取りまとめの場合にはチェックされていないのでdata["OrderSeq"]は全削除
        if((array_key_exists('separate', $data)) == true && $data["separate"]) {
            // 一旦全部の$data["OrderSeq"]を外す
            $i=0;
            while ((isset($data["OrderSeq" . $i]))) {
                unset($data["OrderSeq" . $i]);
                $i++;
            }
        }
        $this->view->assign('eid', $data["eid"]);
        $this->view->assign('mode', $data["mode"]);
        $this->view->assign('sid', $data["sid"]);
        $this->view->assign('sites', isset($sites) ? $sites :NULL);
        $this->view->assign('list', $orderlist);
        $this->view->assign('data', $data);
        $this->view->assign('combinedStatus', $data["combinedStatus"]);

        $this->setTemplate('orderlist');
        return $this->view;
    }
}