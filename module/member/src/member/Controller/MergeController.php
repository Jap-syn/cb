<?php
namespace member\Controller;

use Zend\Config\Reader\Ini;
use Zend\Db\ResultSet\ResultSet;
use Coral\Coral\Controller\CoralControllerAction;
use member\Application;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\Table\TableOrder;
use models\View\ViewOrderCustomer;
use models\Logic\LogicMergeOrder;
use models\Sequence\SequenceGeneral;
use models\Table\TableCode;

/**
 * WebAPIユーザを管理するコントローラ
 */
class MergeController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';
	/**
	 * アプリケーションインスタンス
	 * @var Application
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
	 * コードマスター
	 * @var TableCode
	 */
	protected $codetable;

	/**
	 * ViewOrderCustomer
	 * @var ViewOrderCustomer
	 */
	protected $ordercusview;

	/**
	 * コントローラ初期化
	 */
	protected function _init() {

        $this->app = Application::getInstance();

        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/bytefx.js' );
        $this->addJavaScript( '../js/corelib.js' );
        $this->addJavaScript( '../js/base.ui.js');
        $this->addJavaScript( '../js/base.ui.datepicker.js');
        $this->addJavaScript( '../js/json_format.js' );
        $this->addStyleSheet( './css/members.css' );
        $this->addStyleSheet( './css/index.css' );
        $this->addStyleSheet( './css/combinedclaim.css' );
        $this->addStyleSheet( '../css/base.ui.datepicker.css' );
        $this->addStyleSheet( './css/account.css' );
        $this->addStyleSheet( './css/tab_support.css' );
        //$this->view->assign( 'current_action', $this->getCurrentAction() );

        // メニュー情報をマスタから取得
        $menu_info = $this->app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
        }

        $this->enttable = new TableEnterprise($this->app->dbAdapter);
        $this->sitetable = new TableSite($this->app->dbAdapter);
        $this->ordercusview= new ViewOrderCustomer($this->app->dbAdapter);
        $this->codetable = new TableCode($this->app->dbAdapter);
        $this->setPageTitle( '請求取りまとめ注文一覧' );
	}

	/**
	 * indexAction
	 * listActionのエイリアス
	 */
	public function indexAction() {
	    return $this->_forward('list');
	}

    /**
     * 注文一覧を表示する
     *
     */
    public function listAction() {

        $param = $this->getParams();
        $data = $param['data'];

        // モードの確認
        $enterprise = $this->enttable->findEnterprise($this->app->authManager->getUserInfo()->EnterpriseId)->current();

        // サイト情報取得
        $ri = $this->sitetable->getSiteListByCombinedClaim($enterprise['EnterpriseId']);
        $sites = ResultInterfaceToArray($ri);

        // 取りまとめマニュアルのリンクURL取得
        $url = $this->codetable->find(195,0)->current()['Note'];

        $orderlist = array();

        if ((!isset($param["combinedStatus"])) || (isset($param["combinedStatus"]) && $param["combinedStatus"] == '0')) {
            if($enterprise['CombinedClaimMode'] == 1) {
                // 事業者毎
                $ri = $this->ordercusview->getMergeOrderByEnterprise2($enterprise['EnterpriseId']);
                $orderlist = ResultInterfaceToArray($ri);
            } else if($enterprise['CombinedClaimMode'] == 2) {
                // サイト毎
                // 選択中のサイトの対象注文一覧を取得（パラメータのSiteIdがない場合には一番若いサイトの注文を表示）
                $ri = $this->ordercusview->getMergeOrderBySite2($enterprise['EnterpriseId'], isset($data['sid']) ? $data['sid'] : $sites[0]['SiteId']);
                $orderlist = ResultInterfaceToArray($ri);
            }

            $nameKj = '';
            $phone  = '';
            $oseq   = 0;

            foreach ( $orderlist as &$value ) {
                if ($value['NameKj'] == $nameKj && $value['Phone'] == $phone){
                    $value['P_OrderSeq'] = $oseq;
                } else {
                    $nameKj = $value['NameKj'];
                    $phone  = $value['Phone'];
                    $oseq   = $value['OrderSeq'];
                    $value['P_OrderSeq'] = $oseq;
                }
            }
            unset($value);

            foreach ( $orderlist as $key => $value ) {
                $sort[$key] = $value['P_OrderSeq'];
            }
            if (!empty($orderlist)) {
                array_multisort($sort, SORT_ASC, $orderlist);
            }

        } else if ((isset($param["combinedStatus"]) && $param["combinedStatus"] == '1')) {
            if($enterprise['CombinedClaimMode'] == 1) {
                // 事業者毎
                $ri = $this->ordercusview->getMergeOrderCancelByEnterprise($enterprise['EnterpriseId']);
                $orderlist = ResultInterfaceToArray($ri);
            } else if($enterprise['CombinedClaimMode'] == 2) {
                // サイト毎
                // 選択中のサイトの対象注文一覧を取得（パラメータのSiteIdがない場合には一番若いサイトの注文を表示）
                $ri = $this->ordercusview->getMergeOrderCancelBySite($enterprise['EnterpriseId'], isset($data['sid']) ? $data['sid'] : $sites[0]['SiteId']);
                $orderlist = ResultInterfaceToArray($ri);
            }
        }

        $this->view->assign('sid', isset($data['sid']) ? $data['sid'] : $sites[0]['SiteId']);
        $this->view->assign('eid', $enterprise['EnterpriseId']);
        $this->view->assign('mode', $enterprise['CombinedClaimMode']);
        $this->view->assign('sites', $sites);
        $this->view->assign('list', $orderlist);
        $this->view->assign('combinedStatus', $param["combinedStatus"]);
        $this->view->assign('url', $url);

        return $this->view;
    }

    /**
     * 注文一覧を表示する
     *
     */
    public function listbysiteAction() {

        $this->setPageTitle( '請求取りまとめ注文一覧' );

        $params = $this->getParams();

        $eid  = isset($params['eid'] ) ? $params['eid']  : -1;
        $mode = isset($params['mode']) ? $params['mode'] : -1;
        $site = isset($params['site']) ? $params['site'] : -1;
        $combinedStatus = isset($params['status']) ? $params['status'] : -1;

        // サイト情報取得
        $ri = $this->sitetable->getSiteListByCombinedClaim($eid);
        $sites = ResultInterfaceToArray($ri);

        if ($combinedStatus == '0') {
            // 選択中のサイトの対象注文一覧を取得
            $ri = $this->ordercusview->getMergeOrderBySite2($eid, $site);
            $orderlist = ResultInterfaceToArray($ri);

            $nameKj = '';
            $phone  = '';
            $oseq   = 0;

            foreach ( $orderlist as &$value ) {
                if ($value['NameKj'] == $nameKj && $value['Phone'] == $phone){
                    $value['P_OrderSeq'] = $oseq;
                } else {
                    $nameKj = $value['NameKj'];
                    $phone  = $value['Phone'];
                    $oseq   = $value['OrderSeq'];
                    $value['P_OrderSeq'] = $oseq;
                }
            }
            unset($value);

            foreach ( $orderlist as $key => $value ) {
                $sort[$key] = $value['P_OrderSeq'];
            }
            if (!empty($orderlist)) {
                array_multisort($sort, SORT_ASC, $orderlist);
            }

            // 取りまとめマニュアルのリンクURL取得
            $url = $this->codetable->find(195,0)->current()['Note'];

            $this->view->assign('url', $url);

        } else if ($combinedStatus == '1') {
            // 選択中のサイトの対象注文一覧を取得
            $ri = $this->ordercusview->getMergeOrderCancelBySite($eid, $site);
            $orderlist = ResultInterfaceToArray($ri);
        }

        $this->view->assign('sid', $site);
        $this->view->assign('eid', $eid);
        $this->view->assign('mode', $mode);
        $this->view->assign('sites', $sites);
        $this->view->assign('list', $orderlist);
        $this->view->assign('combinedStatus', $combinedStatus);

        $this->setTemplate('list');
        return $this->view;
	}

    /**
     * チェックした取りまとめ情報の確認画面を表示する
     */
    public function confirmAction() {

        $params = $this->getParams();

        // POSTされたデータを取得
        $data  = isset($params['data'] ) ? $params['data']  : array();

        if ($params['combinedStatus'] == 1) {
            $data['combinedStatus'] = 1;
        }

        // 個別取りまとめ情報をセットする
        $data["separate"] = 0;
        $this->view->assign('separate', $data["separate"]);

        $sql = " SELECT voc.*, s.SelfBillingFlg FROM V_OrderCustomer voc INNER JOIN T_Site s ON (s.SiteId = voc.SiteId) WHERE voc.OrderId = :OrderId ";
        $ordercustomers = array();
        $i=0;
        while (($data["OrderId" . $i]))
        {
            if(!empty($data["OrderSeq" . $i])) {
                $ordercustomer = $this->app->dbAdapter->query($sql)->execute(array(":OrderId" => $data["OrderId" . $i]))->current();
                array_push($ordercustomers, $ordercustomer);
            }
            $i++;
        }

        if($data["combinedStatus"] == 0){
            $errors = "";
            if ($errors == "") {
                // 請求取りまとめは異なるSelfBillingFlgを許容できない
                if(!empty($ordercustomers)) {
                    for($i = 0; $i < count($ordercustomers); $i++) {
                        if ($ordercustomers[0]['SelfBillingFlg'] != $ordercustomers[$i]['SelfBillingFlg']) {
                            $errors = '請求書同梱の方法が異なるサイト間では、請求取りまとめはできません。';
                            break;
                        }
                    }
                }
            }

            if ($errors == "") {
                // 取りまとめ対象の正規化氏名が同一かの判定
                if(!empty($ordercustomers)) {
                    for($i = 0; $i < count($ordercustomers); $i++) {
                        $regNameKj = ($i == 0) ? $ordercustomers[$i]["RegNameKj"] : $regNameKj;
                        // 正規化氏名が異なる場合にはエラー
                        if(strcmp($regNameKj, $ordercustomers[$i]["RegNameKj"]) != 0) {
                            $errors = '請求先氏名が異なる場合には請求取りまとめはできません。';
                            break;
                        }
                    }
                }
            }

            if ($errors != "") {
                $orderlist = array();
                if($data["mode"] == 1) {
                    $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderByEnterprise2($data["eid"]));
                }
                else {
                    // サイト情報取得
                    $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                    // 対象の対象注文一覧を取得
                    $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderBySite2($data["eid"], $data["sid"]));
                }

                $nameKj = '';
                $phone  = '';
                $oseq   = 0;

                foreach ( $orderlist as &$value ) {
                    if ($value['NameKj'] == $nameKj && $value['Phone'] == $phone){
                        $value['P_OrderSeq'] = $oseq;
                    } else {
                        $nameKj = $value['NameKj'];
                        $phone  = $value['Phone'];
                        $oseq   = $value['OrderSeq'];
                        $value['P_OrderSeq'] = $oseq;
                    }
                }
                unset($value);

                foreach ( $orderlist as $key => $value ) {
                    $sort[$key] = $value['P_OrderSeq'];
                }
                if (!empty($orderlist)) {
                    array_multisort($sort, SORT_ASC, $orderlist);
                }

                // 取りまとめマニュアルのリンクURL取得
                $url = $this->codetable->find(195,0)->current()['Note'];

                // 検証エラーは入力画面へ戻す
                $this->view->assign('eid', $data["eid"]);
                $this->view->assign('mode', $data["mode"]);
                $this->view->assign('sid', $data["sid"]);
                $this->view->assign('sites', $sites);
                $this->view->assign('list', $orderlist);
                $this->view->assign('data', $data);
                $this->view->assign('error', $errors);
                $this->view->assign('url', $url);

                $this->setTemplate('list');
                return $this->view;
            }
        }

        // ビューへアサインするために変数へ代入
        $setCombined = isset($data['combinedStatus']);

        // フォームデータ自身をエンコード
        $data = base64_encode(serialize($data));
        $this->view->assign('ordercustomers', $ordercustomers);
        $this->view->assign('encoded_data', $data);
        $this->view->assign('combinedStatus', $setCombined);

        return $this->view;
	}

	/**
	 * 全取りまとめ情報の確認画面を表示する
	 */
	public function separateconfirmAction() {

        $params = $this->getParams();

        // POSTされたデータを取得
        $data  = isset($params['data'] ) ? $params['data']  : array();

        // 個別取りまとめ情報をセットする
        $data["separate"] = 1;
        $this->view->assign('separate', $data["separate"]);

        // チェックがつけられてる可能性もあるので一旦全部の$data["OrderSeq"]を外す
        $i=0;
        while (($data["OrderId" . $i]))
        {
            if(!empty($data["OrderSeq" . $i])) {
                unset($data["OrderSeq" . $i]);
            }
            $i++;
        }

        $ordercustomers = array();
        $i=0;
        while (($data["OrderId" . $i]))
        {
            // DBより対象の注文情報を取得する
            $ordercustomer = $this->ordercusview->findOrderCustomer(array("OrderId" => $data["OrderId" . $i]))->current();
            array_push($ordercustomers, $ordercustomer);

            // 全部のOrderSeqを設定する
            $data["OrderSeq".$i] = $ordercustomer["OrderSeq"];

            $i++;
        }
        // フォームデータ自身をエンコード
        $data = base64_encode(serialize($data));

        $this->view->assign('ordercustomers', $ordercustomers);
        $this->view->assign('encoded_data', $data);

        $this->setTemplate('confirm');
        return $this->view;
	}

	/**
	 * 取りまとめを実行する
	 */
	public function saveAction() {

        $params = $this->getParams();

        // エンコード済みのPOSTデータを復元する
        $hash  = isset($params['hash'] ) ? $params['hash']  : null;
        $data = unserialize(base64_decode($hash));

        if( $data == null ) {
            throw new \Exception('編集中のデータが失われました');
        }
        // 個別に請求取りまとめ
        if($data["separate"]) {
            $i=0;
            // トランザクションの開始
            try {
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
                while (($data["OrderSeq" . $i]))
                {
                    // 請求取りまとめ対象かチェック
                    if (!$this->ordercusview->isMergeOrder($data["OrderSeq" . $i])) {
                        // 他端末による更新で対象外になった場合
                        $this->app->dbAdapter->getDriver()->getConnection()->rollback();

                        $errors = '指定した取りまとめ注文が他の端末から更新されました。確認してください。';

                        // 一旦全部の$data["OrderSeq"]を外す
                        $i=0;
                        while ((isset($data["OrderSeq" . $i]))) {
                            unset($data["OrderSeq" . $i]);
                            $i++;
                        }

                        $orderlist = array();
                        if($data["mode"] == 1) {
                            $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderByEnterprise2($data["eid"]));
                        }
                        else {
                            // サイト情報取得
                            $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                            // 対象の対象注文一覧を取得
                            $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderBySite2($data["eid"], $data["sid"]));
                        }

                        $nameKj = '';
                        $phone  = '';
                        $oseq   = 0;

                        foreach ( $orderlist as &$value ) {
                            if ($value['NameKj'] == $nameKj && $value['Phone'] == $phone){
                                $value['P_OrderSeq'] = $oseq;
                            } else {
                                $nameKj = $value['NameKj'];
                                $phone  = $value['Phone'];
                                $oseq   = $value['OrderSeq'];
                                $value['P_OrderSeq'] = $oseq;
                            }
                        }
                        unset($value);

                        foreach ( $orderlist as $key => $value ) {
                            $sort[$key] = $value['P_OrderSeq'];
                        }
                        if (!empty($orderlist)) {
                            array_multisort($sort, SORT_ASC, $orderlist);
                        }

                        // 取りまとめマニュアルのリンクURL取得
                        $url = $this->codetable->find(195,0)->current()['Note'];

                        // 検証エラーは入力画面へ戻す
                        $this->view->assign('eid', $data["eid"]);
                        $this->view->assign('mode', $data["mode"]);
                        $this->view->assign('sid', $data["sid"]);
                        $this->view->assign('sites', $sites);
                        $this->view->assign('list', $orderlist);
                        $this->view->assign('data', $data);
                        $this->view->assign('combinedStatus', $data["combinedStatus"]);
                        $this->view->assign('error', $errors);
                        $this->view->assign('url', $url);

                        $this->setTemplate('list');
                        return $this->view;
                    }

                    $lgcMergeOrder = new LogicMergeOrder($this->app->dbAdapter);

                    // ユーザーIDの取得
                    $obj = new \models\Table\TableUser($this->app->dbAdapter);
                    getUserInfoForMember($this->app, $userClass, $seq);
                    $userId = $obj->getUserId($userClass, $seq);

                    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

                    $SeqGeneral = new SequenceGeneral($this->app->dbAdapter);
                    $nextval =$SeqGeneral->nextValue('CombinedDictateGroup');

                    $lgcMergeOrder->merge(array($data["OrderSeq" . $i]), $userId, $entId, $nextval);
                    $i++;
                }
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch (\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                throw $err;
            }
        }
        else {
            $targetseqs = array();
            $i=0;
            while (($data["OrderId" . $i]))
            {
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

                        $orderlist = array();
                        if ($data["combinedStatus"] == 0) {
                            if($data["mode"] == 1) {
                                $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderByEnterprise2($data["eid"]));
                            }
                            else {
                                // サイト情報取得
                                $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                                // 対象の対象注文一覧を取得
                                $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderBySite2($data["eid"], $data["sid"]));
                            }

                            $nameKj = '';
                            $phone  = '';
                            $oseq   = 0;

                            foreach ( $orderlist as &$value ) {
                                if ($value['NameKj'] == $nameKj && $value['Phone'] == $phone){
                                    $value['P_OrderSeq'] = $oseq;
                                } else {
                                    $nameKj = $value['NameKj'];
                                    $phone  = $value['Phone'];
                                    $oseq   = $value['OrderSeq'];
                                    $value['P_OrderSeq'] = $oseq;
                                }
                            }
                            unset($value);

                            foreach ( $orderlist as $key => $value ) {
                                $sort[$key] = $value['P_OrderSeq'];
                            }
                            if (!empty($orderlist)) {
                                array_multisort($sort, SORT_ASC, $orderlist);
                            }

                            // 取りまとめマニュアルのリンクURL取得
                            $url = $this->codetable->find(195,0)->current()['Note'];

                            $this->view->assign('url', $url);
                        }
                        else {
                            if($data["mode"] == 1) {
                                $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderCancelByEnterprise($data["eid"]));
                            } else {
                                // サイト情報取得
                                $sites = ResultInterfaceToArray($this->sitetable->getSiteListByCombinedClaim($data["eid"]));
                                // 対象の対象注文一覧を取得
                                $orderlist = ResultInterfaceToArray($this->ordercusview->getMergeOrderCancelBySite($data["eid"], $data["sid"]));
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

                        $this->setTemplate('list');
                        return $this->view;
                    }
                }
                $i++;
            }

            // 対象が1以上あった場合
            if(!empty($targetseqs)) {
                // トランザクションの開始
                try {
                    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                    // ユーザーIDの取得
                    $obj = new \models\Table\TableUser($this->app->dbAdapter);
                    getUserInfoForMember($this->app, $userClass, $seq);
                    $userId = $obj->getUserId($userClass, $seq);

                    // 加盟店IDの取得
                    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

                    // 汎用シーケンスの取得
                    $SeqGeneral = new SequenceGeneral($this->app->dbAdapter);
                    $nextval =$SeqGeneral->nextValue('CombinedDictateGroup');

                    $lgcMergeOrder = new LogicMergeOrder($this->app->dbAdapter);
                    if ($data["combinedStatus"] == 1) {
                        $lgcMergeOrder->mergecancel($targetseqs, $userId);
                    } else {
                        $lgcMergeOrder->merge($targetseqs, $userId, $entId, $nextval);
                    }

                    $this->app->dbAdapter->getDriver()->getConnection()->commit();
                }
                catch (\Exception $err) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                    throw $err;
                }
            }
        }

        // 注文一覧へリダイレクト
        return $this->_redirect("merge/list/");
    }

    /**
     * backAction
     * 確認画面から変更フォームへ戻る
     */
    public function backAction() {

        $params = $this->getParams();

        // エンコード済みのPOSTデータを復元する
        $hash  = isset($params['hash'] ) ? $params['hash']  : null;
        $data = unserialize(base64_decode($hash));

        if( $data == null ) {
            throw new \Exception('編集中のデータが失われました');
        }

        $orderlist = array();
        if($data["mode"] == 1) {
            if ($data["combinedStatus"] == 0) {
                $orderlist = $this->ordercusview->getMergeOrderByEnterprise2($data["eid"]);
            } else {
                $orderlist = $this->ordercusview->getMergeOrderCancelByEnterprise($data["eid"]);
            }
            $rs = new ResultSet();
            $rs->initialize($orderlist);
            $orderlist = $rs->toArray();
        } else {
            // サイト情報取得
            $sites = $this->sitetable->getSiteListByCombinedClaim($data["eid"]);
            $rs = new ResultSet();
            $rs->initialize($sites);
            $sites = $rs->toArray();

            // 対象の対象注文一覧を取得
            if ($data["combinedStatus"] == 0) {
                $orderlist = $this->ordercusview->getMergeOrderBySite2($data["eid"], $data["sid"]);
            } else {
                $orderlist = $this->ordercusview->getMergeOrderCancelBySite($data["eid"], $data["sid"]);
            }
            $rs = new ResultSet();
            $rs->initialize($orderlist);
            $orderlist = $rs->toArray();
        }

        if ($data["combinedStatus"] == 0) {
            $nameKj = '';
            $phone  = '';
            $oseq   = 0;

            foreach ( $orderlist as &$value ) {
                if ($value['NameKj'] == $nameKj && $value['Phone'] == $phone){
                    $value['P_OrderSeq'] = $oseq;
                } else {
                    $nameKj = $value['NameKj'];
                    $phone  = $value['Phone'];
                    $oseq   = $value['OrderSeq'];
                    $value['P_OrderSeq'] = $oseq;
                }
            }
            unset($value);

            foreach ( $orderlist as $key => $value ) {
                $sort[$key] = $value['P_OrderSeq'];
            }
            if (!empty($orderlist)) {
                array_multisort($sort, SORT_ASC, $orderlist);
            }

            // 取りまとめマニュアルのリンクURL取得
            $url = $this->codetable->find(195,0)->current()['Note'];

            $this->view->assign('url', $url);
        }

        // 全個別取りまとめの場合にはチェックされていないのでdata["OrderSeq"]は全削除
        if($data["separate"]) {
            // 一旦全部の$data["OrderSeq"]を外す
            $i=0;
            while (($data["OrderSeq" . $i]))
            {
                unset($data["OrderSeq" . $i]);
                $i++;
            }
        }

        $this->view->assign('eid', $data["eid"]);
        $this->view->assign('mode', $data["mode"]);
        $this->view->assign('sid', $data["sid"]);
        $this->view->assign('sites', $sites);
        $this->view->assign('list', $orderlist);
        $this->view->assign('data', $data);
        $this->view->assign('combinedStatus', $data["combinedStatus"]);

        $this->setTemplate('list');
        return $this->view;
	}
}