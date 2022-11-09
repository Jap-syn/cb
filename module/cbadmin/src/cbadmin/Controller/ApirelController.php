<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;
use models\Table\TableApiUser;
use models\Table\TableEnterprise;
use models\Table\TableApiUserEnterprise;
use models\Table\TableOem;
use models\Table\TableSite;
use models\Table\TableUser;

/**
 * WebAPIユーザと事業者の関連付けを管理するコントローラ
 */
class ApirelController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';
    /**
     * アプリケーションインスタンス
     * @var Application
     */
    protected $app;

    /**
     * APIユーザテーブル
     * @var TableApiUser
     */
    protected $apiUsers;

    /**
     * サイトテーブル
     * @var TableSite
     */
    protected $site;

    /**
     * API - 事業者リレーションテーブル
     * @var TableApiUserEnterprise
     */
    protected $apiRelations;

    /**
     * コントローラ初期化
     */
    protected function _init() {
        $this->app = Application::getInstance();
        $this->app->addClass('TableEnterprise');
        $this->app->addClass('TableApiUser');
        $this->app->addClass('TableOem');
        $this->app->addClass('TableApiUserEnterprise');

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json+.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/bytefx.js');

        $this->setPageTitle("後払い.com - APIユーザー管理");
        $this->view->assign('current_action', $this->getActionName());
        $this->view->assign( 'userInfo', $this->app->authManagerAdmin->getUserInfo());
        $this->apiUsers = new TableApiUser($this->app->dbAdapter);
        $this->site = new TableSite($this->app->dbAdapter);
        $this->apiRelations = new TableApiUserEnterprise($this->app->dbAdapter);

        // OEMリストはあらかじめ割り当てておく
        $mdlOem = new TableOem($this->app->dbAdapter);
        $this->view->assign('oemList', $mdlOem->getOemIdList());
        return $this->view;
    }

    /**
     * indexAction
     */
    public function indexAction() {
    }

    /**
     * apioemselect
     */
    public function apioemselectAction() {
        if ($this->flashMessenger()->hasErrorMessages()){
            $this->view->assign('error', $this->flashMessenger()->getErrorMessages()[0]);
        } else {
            $this->view->assign('error', '');
        }
        return $this->view;
    }
    /**
     * entoemselect
     */
    public function entoemselectAction() {
        if ($this->flashMessenger()->hasErrorMessages()){
            $this->view->assign('error', $this->flashMessenger()->getErrorMessages()[0]);
        } else {
            $this->view->assign('error', '');
        }
        return $this->view;
    }

    /**
     * api2entAction
     * 指定のAPIユーザに対して事業者の関連付けを行う
     */
    public function api2entAction() {
        $req = $this->getRequest();

        $force_cb_select = ($req->getPost('causer') != null);

        $post_array = $this->params()->fromPost();
        $route_array = $this->params()->fromRoute();
        $array = array_merge($post_array, $route_array);

        // [キャッチボール登録ユーザ]クリックかどうか
        if($force_cb_select) {
            // OEM先としてキャッチボール選択確定
            $array['oemId'] = 0;
        } else {
            // リクエストパラメータから選択OEMを取得
            if(!strlen($array['oemId'])) $array['oemId'] = -1;
        }

        // OEM未選択時はOEM選択画面へ差し戻し
        if($array['oemId'] == -1) {
            $this->flashMessenger()->addErrorMessage("OEM先が選択されていません");
            return $this->_redirect('apirel/apioemselect');
        }

        //キャッチボールではない場合OEM名取得
        if( $array['oemId'] != 0 ) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem2($array['oemId']);

            //OEM情報が取れている場合OEM名をセット
            if(!is_null($oemList)){
                $oem_name = $oemList->current()['OemNameKj'];
            }
        }else{
            $oem_name = "キャッチボール";
        }
        $this->view->assign('oemId', $array['oemId']);
        $this->view->assign('oemName',$oem_name);

        // APIユーザ取得
        $val = array_key_exists('id', $array) ? $array['id'] : -1;
        $ent = $this->getApiUser($val);

        if( $ent == null ) {
            // APIユーザ未指定時
            $this->view->assign('apiUsers', $this->apiUsers->getAllValidApiUsers($array['oemId']));
        } else {
            // APIユーザ選択確定時
            if((array_key_exists('is_edited', $array) ? $array['is_edited']: null) == 'true' ){
                // 登録確定アクションなら永続化を実行し完了メッセージをセット
                try {
                    $this->saveApi2Ent($ent['ApiUserId'], array_key_exists('data', $array) ? $array['data'] : null, $array['oemId']);
                    $this->view->assign('message', '関連設定が完了しました');
                } catch(\Exception $err) {
                    $this->view->assign('message', sprintf('エラー：%s', $err->getMessage()));
                }
            }

            $rels = $this->createRelatedEnterprises($ent['ApiUserId']);
            $relIds = array();
            foreach($rels as $rel) $relIds[] = (string)array_key_exists('SiteId', $rel) ? $rel['SiteId']: '';

            $this->view->assign('apiUser', $ent);
            $this->view->assign('relations', $rels);
            $this->view->assign('sites', $this->createAllEnterprises($relIds, $array['oemId']));
        }

        $mdlOem = new TableOem($this->app->dbAdapter);
        $this->view->assign('oemListTag',\Coral\Base\BaseHtmlUtils::SelectTag("oemList", $mdlOem->getOemIdList(), $array['oemId'], 'onChange="changeOem(); "'));

		return $this->view;
	}

    /**
     * ent2apiAction
     * 指定の事業者に対してAPIユーザの関連付けを行う
     */
    public function ent2apiAction() {

        $req = $this->getRequest();
        // [キャッチボール登録事業者]クリックかどうか
        $force_cb_select = ($req->getPost('causer') != null);

        $post_array = $this->params()->fromPost();
        $route_array = $this->params()->fromRoute();
        $array = array_merge($post_array, $route_array);

        if($force_cb_select) {
            // OEM先としてキャッチボール選択確定
            $array['oemId'] = 0;
        } else {
            // リクエストパラメータから選択OEMを取得
            if(!strlen($array['oemId'])) $array['oemId'] = -1;
        }

        // OEM未選択時はOEM選択画面へ差し戻し
        if($array['oemId'] == -1) {
            $this->flashMessenger()->addErrorMessage("OEM先が選択されていません");
            return $this->_redirect('apirel/entoemselect');
        }

        // キャッチボールでない場合はOEM名取得
        if( $array['oemId'] != 0 ) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem2($array['oemId']);

            //OEM情報が取れている場合OEM名をセット
            if(!is_null($oemList)){
                $oem_name = $oemList->current()['OemNameKj'];
            }
        } else {
            $oem_name = "キャッチボール";
        }
        $this->view->assign('oemId', $array['oemId']);
        $this->view->assign('oemName',$oem_name);

        // サイト情報取得
        $val = array_key_exists('id', $array) ? $array['id'] : -1;
        $site = $this->getSite($val);

        if( $site == null ) {
            // サイト未指定時
            $sql = <<<EOQ
SELECT  s.SiteId
    ,   s.SiteNameKj
FROM    T_Site s
        INNER JOIN T_Enterprise e ON (e.EnterpriseId = s.EnterpriseId)
WHERE   IFNULL(e.OemId, 0) = :OemId
ORDER BY
        s.SiteId
;
EOQ;
            $ri = $this->app->dbAdapter->query($sql)->execute(array( ':OemId' => $array['oemId'] ));
            $sites = ResultInterfaceToArray($ri);

            $this->view->assign('sites', $sites);
        } else {
            // 事業者選択確定時
            if((array_key_exists('is_edited', $array) ? $array['is_edited']: null) == 'true' ){

                // 登録確定アクションなら永続化を実行し完了メッセージをセット
                try {
                    $this->saveEnt2Api($site['SiteId'], array_key_exists('data', $array) ? $array['data'] : null, $array['oemId']);
                    $this->view->assign('message', '関連設定が完了しました');
                } catch(\Exception $err) {
                    $this->view->assign('message', sprintf('エラー：%s', $err->getMessage()));
                }
            }

            $rels = $this->createRelatedApiUsers($site['SiteId']);
            $relIds = array();
            foreach($rels as $rel) $relIds[] = (string)array_key_exists('ApiUserId', $rel) ? $rel['ApiUserId']: '';

            $this->view->assign('site', $site);
            $this->view->assign('enterpriseId', $site['EnterpriseId']);
            $this->view->assign('relations', $rels);
            $this->view->assign('apiUsers', $this->createAllApiUsers($relIds, $array['oemId']));

        }

        $mdlOem = new TableOem($this->app->dbAdapter);
        $this->view->assign('oemListTag',\Coral\Base\BaseHtmlUtils::SelectTag("oemList", $mdlOem->getOemIdList(), $array['oemId'], 'onChange="changeOem(); "'));

        return $this->view;
    }

    /**
     * 指定IDに一致するAPIユーザを取得する
     * @param int $apiUserId APIユーザID
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function getApiUser($apiUserId) {
        return $this->apiUsers->findApiUser($apiUserId)->current();
    }

    /**
     * 指定IDに一致するサイトを取得する
     * @param int $entId サイトID
     * @return array
     */
    private function getSite($siteId) {
        $ri = $this->site->findSite($siteId);
        if($ri->count() <= 0){
            return null;
        }else{
            return $ri->current();
        }
	}

    /**
     * 指定APIユーザに関連付けられたサイト情報の配列を生成する
     * @param int $apiUserId APIユーザID
     * @return array サイトIDとサイト名の配列
     */
    protected function createRelatedEnterprises($apiUserId) {
        $keys = array( 'SiteId', 'SiteNameKj', 'ValidFlg' );

        $result = array();
        $rels = $this->apiRelations->findRelatedEnterprises($apiUserId);
        if( $rels == null || $rels->count() == 0 ) return $result;
        foreach($rels as $ent) {
            $row = array();
            foreach($keys as $key) {
                if( ! isset($ent[$key]) ) continue;
                $row[$key] = $ent[$key];
            }
            $result[] = $row;
        }
        return $result;
	}

    /**
     * 指定のサイトを除いた、全サイト情報の配列を生成する
     * @param null|array $ignoreIds 除外するサイトIDの配列
     * @param int $oem_id OEMID
     * @return array サイトIDとサイト名の配列
     */
    protected function createAllEnterprises($ignoreIds = array(), $oem_id = 0) {
        $keys = array( 'SiteId', 'SiteNameKj', 'ValidFlg', 'OemId' );

        if( $ignoreIds == null ) $ignoreIds = array();
        if( ! is_array($ignoreIds) ) $ignoreIds = array($ignoreIds);

        $db = $this->app->dbAdapter;
        $result = array();

        $sql = <<<EOQ
SELECT  s.*
    ,   e.OemId
FROM    T_Site s
        INNER JOIN T_Enterprise e ON (e.EnterpriseId = s.EnterpriseId)
WHERE   IFNULL(e.OemId, 0) = :OemId
ORDER BY
        s.SiteId
;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':OemId' => $oem_id ));

        $sites = ResultInterfaceToArray($ri);

        // count関数対策
        $site_count = 0;
        if(!empty($sites)){
            $site_count = count($sites);
        }

        if( $sites == null || $site_count == 0 ) return $result;
        foreach($sites as $site) {
            if( in_array( (string)$site['SiteId'], $ignoreIds  ) ) continue;
            $row = array();
            foreach($keys as $key) {
                if( ! isset($site[$key]) ) continue;
                $row[$key] = $site[$key];
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * 指定サイトに関連付けられたAPIユーザ情報の配列を生成する
     * @param int $siteId サイトID
     * @return array APIユーザIDとAPIユーザ名の配列
     */
    protected function createRelatedApiUsers($siteId) {
        $keys = array( 'ApiUserId', 'ApiUserNameKj', 'ValidFlg' );
        $result = array();
        $rels = $this->apiRelations->findRelatedApiUsers($siteId);
        if( $rels == null || $rels->count() == 0 ) return $result;
        foreach($rels as $user) {
            $row = array();
            foreach($keys as $key) {
                if( ! isset($user[$key]) ) continue;
                $row[$key] = $user[$key];
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * 指定のAPIユーザを除いた、全APIユーザ情報の配列を生成する
     * @param null|array $ignoreIds 除外するAPIユーザIDの配列
     * @return array APIユーザIDとAPIユーザ名の配列
     */
    protected function createAllApiUsers($ignoreIds = array(),$oem_id = 0) {
        $keys = array( 'ApiUserId', 'ApiUserNameKj', 'ValidFlg' );

        if( $ignoreIds == null ) $ignoreIds = array();
        if( ! is_array($ignoreIds) ) $ignoreIds = array($ignoreIds);

        $db = $this->app->dbAdapter;
        $result = array();

        $sql = <<<EOQ
SELECT  *
FROM    T_ApiUser
WHERE   IFNULL(OemId, 0) = :OemId
ORDER BY
        ApiUserId
;
EOQ;
        $apiusers = $this->app->dbAdapter->query($sql)->execute(array( ':OemId' => $oem_id ));

        if( $apiusers == null || $apiusers->count() == 0 ) return $result;
        foreach($apiusers as $user) {
            if( in_array( (string)$user['ApiUserId'], $ignoreIds  ) ) continue;
            if($oem_id != nvl($user['OemId'], 0)) continue;
            $row = array();
            foreach($keys as $key) {
                if( ! isset($user[$key]) ) continue;
                $row[$key] = $user[$key];
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * 指定APIユーザにサイト情報を関連付ける
     * @param int $apiUserId APIユーザID
     * @param array $postData フォームから登録されたサイト情報の配列
     * @param int oem_id 選択されたOEMID
     */
    protected function saveApi2Ent($apiUserId, $postData = array(),$oem_id = 0) {
        if( $postData == null ) $postData = array();
        if( $apiUserId == null || $apiUserId < 0 ) {
            throw new \Exception(sprintf('不正なAPIユーザーIDが指定されました。ApiUserId = %s', nvl($apiUserId, '(null)')));
        }

        $mdlsite = new TableSite($this->app->dbAdapter);

        $db = $this->app->dbAdapter;

        $db->getDriver()->getConnection()->beginTransaction();
        try {
            $this->apiRelations->deleteByApiUserId($apiUserId);
            foreach((array)$postData as $singleData) {
                // サイト情報取得
                $site_data = $mdlsite->findSite2($singleData['SiteId'])->current();

                // サイト情報が取得できなかった場合例外
                if(is_null( $site_data)){
                   throw new \Exception("不正なサイトIDが指定されました。SiteId = ".$singleData['SiteId']);
                }

                // ユーザIDの取得
                $obj = new TableUser($this->app->dbAdapter);
                $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                $row = $this->apiRelations->saveNew( array( 'ApiUserId' => $apiUserId, 'SiteId' => $singleData['SiteId'], 'RegistId' => $userId,'UpdateId' => $userId  ));
                $row;
            }
            $db->getDriver()->getConnection()->commit();
        } catch(\Exception $err) {
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }
    }

    /**
     * 指定のサイトにAPIユーザ情報を関連付ける
     * @param int $entId サイトID
     * @param array $postData フォームから登録されたAPIユーザ情報の配列
     */
    protected function saveEnt2Api($siteId, $postData = array(), $oem_id = 0) {
        if( $postData == null ) $postData = array();
        if( $siteId == null || $siteId < 0 ) {
            throw new \Exception(sprintf('不正なサイトIDが指定されました。SiteId = %s', $siteId));
        }

        $mdle = new TableApiUser($this->app->dbAdapter);

        $db = $this->app->dbAdapter;

        // トランザクション開始
        $db->getDriver()->getConnection()->beginTransaction();
        try {
            $this->apiRelations->deleteBySiteId($siteId);
            foreach((array)$postData as $singleData) {
                // APIユーザ情報取得
                $apiuser_data = $mdle->findApiUser($singleData['ApiUserId'])->current();

                // APIユーザ情報が取得できなかった場合例外
                if(is_null( $apiuser_data)){
                    throw new \Exception("不正なAPIユーザIDが指定されました。ApiUserId = ".$singleData['ApiUserId']);
                }

                // ユーザIDの取得
                $obj = new TableUser($this->app->dbAdapter);
                $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                $row = $this->apiRelations->saveNew( array( 'ApiUserId' => $singleData['ApiUserId'], 'SiteId' => $siteId, 'RegistId' => $userId,'UpdateId' => $userId ));
                $row;
            }
            $db->getDriver()->getConnection()->commit();
        } catch(\Exception $err) {
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }
    }

    /**
     * (Ajax)OEM変更⇒選択可能サイトリストの取得
     */
    public function chgoemAction() {
        $params = $this->getParams();
        $info = array();
        try {
            $sql = " SELECT sit.SiteId, sit.SiteNameKj, sit.ValidFlg FROM T_Enterprise ent INNER JOIN T_Site sit ON (sit.EnterpriseId = ent.EnterpriseId) WHERE IFNULL(ent.OemId,0) = :OemId ";
            // [登録済みサイト]表示内容は除外
            if ($params['selsiteid'] != '') {
                $selsiteid = explode(',', $params['selsiteid']);
                foreach ($selsiteid as &$value) {
                    if (!is_numeric($value)) {
                        $value = -1;
                    }
                }
                $selsiteid = implode(',', $selsiteid);

                $sql .= (' AND    sit.SiteId NOT IN (' . \Coral\Base\BaseUtility::escapeWildcard($selsiteid) . ') ');
            }
            $sql .= ' ORDER BY SiteId ';
            $ri = $this->app->dbAdapter->query($sql)->execute(array( ':OemId' => $params['seloemId'] ));
            $info['siteList'] = ResultInterfaceToArray($ri);

            $msg = '1';
        }
        catch(\Exception $e) {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'info' => $info));
        return $this->response;
    }

    /**
     * (Ajax)OEM変更⇒選択可能APIユーザリストの取得
     */
    public function chgoem2Action() {
        $params = $this->getParams();
        $info = array();
        try {
            $sql = " SELECT ApiUserId, ApiUserNameKj, ValidFlg FROM T_ApiUser WHERE IFNULL(OemId, 0) = :OemId ";
            // [登録済みAPIユーザー]表示内容は除外
            if ($params['selapiuserid'] != '') {
                $selapiuserid = explode(',', $params['selapiuserid']);
                foreach ($selapiuserid as &$value) {
                    if (!is_numeric($value)) {
                        $value = -1;
                    }
                }
                $selapiuserid = implode(',', $selapiuserid);

                $sql .= (' AND    ApiUserId NOT IN (' . \Coral\Base\BaseUtility::escapeWildcard($selapiuserid) . ') ');
            }
            $sql .= ' ORDER BY ApiUserId ';
            $ri = $this->app->dbAdapter->query($sql)->execute(array( ':OemId' => $params['seloemId'] ));
            $info['apiuserList'] = ResultInterfaceToArray($ri);

            $msg = '1';
        }
        catch(\Exception $e) {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'info' => $info));
        return $this->response;
    }
}