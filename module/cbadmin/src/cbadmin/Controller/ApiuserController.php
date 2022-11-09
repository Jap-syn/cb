<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;
use models\Table\TableApiUser;
use models\Table\TableOem;
use Zend\Session\Container;
use models\Table\TableApiUserEnterprise;
use Coral\Coral\Validate\CoralValidateRequired;
/**
 * WebAPIユーザを管理するコントローラ
 */
class ApiuserController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';
    /**
     * アプリケーションインスタンス
     * @var NetB_Application_Abstract
     */
    protected $app;

    /**
     * APIユーザテーブル
     * @var TableApiUser
     */
    protected $apiUsers;

    /**
     * コントローラ初期化
     */
    protected function _init() {
        $this->app = Application::getInstance();
        $this->app->addClass('Table_Enterprise');
        $this->app->addClass('Table_ApiUser');
        $this->app->addClass('Table_Oem');
        $this->app->addClass('Table_ApiUserEnterprise');

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json+.js');
        $this->addJavaScript('../js/corelib.js');
        $this->setPageTitle("後払い.com - APIユーザー管理");

        $this->view->assign('current_action', $this->getActionName());
        $this->view->assign( 'mode', 'add' );
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
        $this->apiUsers = new TableApiUser($this->app->dbAdapter);

        return $this->view;
    }

    /**
     * listAction
     * APIユーザ一覧表示
     */
    public function listAction() {
        $db = $this->app->dbAdapter;
        $users = array();
        $mdlOem = new TableOem($db);
        $relations = new TableApiUserEnterprise($db);

        //セッション情報があればリセットする
        if( isset($this->getStorage()->postData) ) {
            $this->clearStorage('postData');
        }

        $oem_id = "-1";
        $params = $this->params()->fromRoute();

        //OEMIDが設定されている場合検索条件とする
        $where = "";
        if(isset($params['OemId']) && $params['OemId'] != ""){
            $oem_id = (int)$params['OemId'];
            $where = ('IFNULL(OemId, 0) = '. $oem_id);
        }

        $ri = $this->apiUsers->fetchAll($where, 'ApiUserId DESC');
        foreach($ri as $userRow) {
            $user = $userRow;
            $rels = $relations->findRelatedEnterprises($userRow["ApiUserId"]);

            // count関数対策
            $rels_count = 0;
            if(!empty($rels)){
                $rels_count = count($rels);
            }

            $user['relCount'] = $rels == null ? 0 : $rels_count;
            $user['oem'] = isset($user['OemId'])?$user['OemId']:0;
            $users[] = $user;
        }

        $oem_list = $mdlOem->getOemIdList();
        $oem_list[0] = "キャッチボール";

        $this->view->assign('list', $users);
        $this->view->assign('oemList', $oem_list);
        $this->view->assign('selectOemId', $oem_id);
        $this->view->assign('showInvalid', $this->params()->fromRoute('showInvalid') ? true : false);

        return $this->view;
    }

    /**
     * detailAction
     * APIユーザ情報詳細表示
     */
    public function detailAction() {
        $apiUser = $this->getApiUser( $this->params()->fromRoute('id', -1) );

        $relations = new TableApiUserEnterprise($this->app->dbAdapter);

        $this->view->assign('apiUser', $apiUser);
        $this->view->assign('oemId',$apiUser["OemId"]);
        $this->view->assign('rels', $relations->findRelatedEnterprises($apiUser["ApiUserId"]));

        return $this->view;
    }

    /**
     * addAction
     * 新規APIユーザ登録フォーム
     */
    public function addAction() {
        $baseData = array();
        if( $this->getStorage()->fromBackAction ) {
            $baseData = array_merge($baseData, $this->getStorage()->postData);
        }
        $mdlOem = new TableOem($this->app->dbAdapter);

        // セッションデータはクリア
        $this->clearStorage(array('postData', 'fromBackAction'));
        //$apiUser = $this->apiUsers->createRow($baseData);
        // ApiUserIdのみ明示的にクリア
        unset( $apiUser->ApiUserId );

        $oem_list = $mdlOem->getOemIdList();

        $this->view->assign('apiUser', isset($baseData) ? $baseData : $apiUser);
        $this->view->assign('rels', null);
        $this->view->assign('isNew',1);
        $this->view->assign('oemList', $oem_list);
        $this->view->assign('mode', 'add');

        $this->setTemplate('edit');
        return $this->view;
    }

    /**
     * editAction
     * APIユーザ情報編集フォーム
     */
    public function editAction() {
        $apiUser = $this->getApiUser( $this->params()->fromRoute('id', -1) );

        $relations = new TableApiUserEnterprise($this->app->dbAdapter);

        $data = $apiUser;
        // セッションにデータがある場合（＝backActionからのリダイレクト）はDBの値にセッションデータを合成
        if( isset($this->getStorage()->postData) ) {
            $data = array_merge($apiUser, $this->getStorage()->postData);
            $this->clearStorage('postData');	// セッションデータをクリアする
        }
        $this->view->assign('apiUser', $data);
        $this->view->assign('rels', $relations->findRelatedEnterprises($apiUser["ApiUserId"]));
        $this->view->assign('mode', 'edit');

        return $this->view;
    }

    /**
     * confirmAction
     * 新規・編集の確認画面
     */
    public function confirmAction() {
        $mdlOem = new TableOem($this->app->dbAdapter);

        $oem_name = "";

        // POSTされたデータを取得
        $data = $this->params()->fromPost('data', array());
        $isNew = $this->params()->fromPost('isNew',0);


        // IPアドレスリストは改行をセミコロンに置換しておく
        if( isset($data['ConnectIpAddressList']) ) {
            $data['ConnectIpAddressList'] = join( ';',
                preg_split("((\r\n)|[\r\n])", trim((string)$data['ConnectIpAddressList'])) );
        }

        // 有効フラグは厳密に'0' or '1'として整備する
        if( isset($data['ValidFlg']) ) {
            $data['ValidFlg'] = trim((string)$data['ValidFlg']);
            if( $data['ValidFlg'] !== '1' && $data['ValidFlg'] !== '0' ) {
                $data['ValidFlg'] = null;
            }
        }

        //OEMが設定されている場合OEM名取得
        if((isset($data['OemId'])) && !is_null($data['OemId']) && $data['OemId'] != 0) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem2($data['OemId'])->current();
            //OEM情報が取れている場合OEM名をセット
            $oem_name = $oemList['OemNameKj'];
        }

        // 先にビューへアサインしておく
        $this->view->assign('apiUser', $data);
        $this->view->assign('isNew', $isNew);
        $this->view->assign('oemName', $oem_name);

        // 検証実行
        $errors = $this->validate($data);
        $this->view->assign('errorMessages', $errors);
        
        // count関数対策
        if( !empty($errors) > 0 ) {
            //OEMリスト
            $this->view->assign('oemList', $mdlOem->getOemIdList());

            // edit.phtmlへ戻る
            $this->setTemplate('edit');
        }
        // セッションへ退避
        $this->getStorage()->postData = $data;
        return $this->view;
	}

    /**
     * backAction
     * 確認画面から変更フォームへ戻る
     */
    public function backAction() {
        $data = $this->getStorage()->postData;
        if( $data == null ) {
            throw new \Exception('編集中のデータが失われました');
        }

        if( CoralValidateRequired::isInt($data['ApiUserId']) ) {
            $url = "apiuser/edit/id/{$data['ApiUserId']}";
        } else {
            $url = "apiuser/add";
            // add へ飛ばす場合はbacActionからの遷移であることをセッションに記録
            $this->getStorage()->fromBackAction = true;
        }
        return $this->_redirect( $url );
    }

    /**
     * saveAction
     * 新規・編集の永続化処理
     */
    public function saveAction() {
        $data = $this->getStorage()->postData;

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $row = array();
        try {
        	$row = $this->getApiUser($data['ApiUserId']);
        } catch(\Exception $err) {
//             $row = $this->apiUsers->createRow();
        }
        foreach($data as $key => $value) {
            if($key == 'ApiUserId') continue;
            $row[$key] = $value;
        }

        // 有効フラグを設定
        //if( empty($row->ValidFlg) ) $row->ValidFlg = 1;
        // 登録日を設定
        if( empty($row['RegistDate']) ) $row['RegistDate'] = date('Y-m-d H:i:s');
        // サービス開始日も設定
        if( empty($row['ServiceInDate']) ) $row['ServiceInDate'] = date('Y-m-d H:i:s');

        $id = $row['ApiUserId'];
        // 永続化実行
        if(strlen($id) > 0){
            $row['UpdateId'] = $userId;
        	$ri = $this->apiUsers->saveupdate($row, $id);
        }else{
            $row['RegistId'] = $userId;
            $row['UpdateId'] = $userId;
            $id = $this->apiUsers->saveNew($row);

            // T_Userを新規登録
            $obj->saveNew(array('UserClass' => 3, 'Seq' => $id, 'RegistId' => $userId, 'UpdateId' => $userId,));
        }
        // セッションクリア
        $this->clearStorage('postData');
        // 詳細ページへリダイレクト
        return $this->_redirect( "apiuser/detail/id/$id" );
	}

    /**
     * statusAction
     * APIユーザの有効/無効を切り替える
     */
    public function statusAction() {
        $apiUser = $this->getApiUser((int)$this->params()->fromRoute('id', -1));
        $toStatus = $this->params()->fromRoute('to', true);
        $apiUser["ValidFlg"] = $toStatus == 'false' ? 0 : 1;
        $this->apiUsers->saveUpdate($apiUser,$apiUser["ApiUserId"]);

        return $this->_redirect( "apiuser/detail/id/{$apiUser["ApiUserId"]}" );
    }


    /**
     * 指定IDのAPIユーザ情報を取得する。
     * APIユーザが存在しない場合は例外をスローする
     * @param int $id APIユーザID
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function getApiUser($id) {
        $result = $this->apiUsers->findApiUser($id)->current();
        if( $result == null ) {
            throw new \Exception('指定のAPIユーザーが見つかりません');
        }
        return $result;
    }

	/**
	 * このコントローラ固有の{@link Zend_Session_Namespace}を取得する
	 * @return Zend_Session_Namespace
	 */
	protected function getStorage() {
		$namespace = get_class($this) . '_SessionStorage';
// 		$storage = new Zend_Session_Namespace($namespace);
		$storage = new Container($namespace);
		return $storage;
	}

	/**
	 * このコントローラ固有の{@link Zend_Session_Namespace}から、
	 * 指定キーのデータを削除する。キーが指定されていない場合はすべてが削除される。
	 * @param null|string|array $keys 削除するオブジェクトキー。省略時はすべてのデータが削除される
	 */
	protected function clearStorage($keys = null) {

		$storage = $this->getStorage();

		if( $keys == null ) {
			$storage->unsetAll();
		} else {
			if( ! is_array($keys) ) $keys = array((string)$keys);
			foreach($keys as $key) {
				if( isset( $storage->$key ) ) unset($storage->$key);
			}
		}
	}

	/**
	 * APIユーザ登録/変更フォームの内容を検証する
	 * @param array $data 登録フォームデータ
	 * @return array エラーメッセージの配列
	 */
	protected function validate($data = array()) {

	    $errors = array();

	    //ApiUserNameKj: APIユーザー
	    $Key = 'ApiUserNameKj';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
	        $errors[$Key] = "'APIユーザー名'は入力必須です'";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 1)){
	        $errors[$Key] = "'APIユーザー名'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 160)){
	        $errors[$Key] = "'APIユーザー名'が長すぎます";
	    }

	    //ApiUserNameKn: APIユーザーカナ
	    $Key = 'ApiUserNameKn';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'APIユーザー名カナ'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 160)){
	        $errors[$Key] = "'APIユーザー名'が長すぎます";
	    }

	    //ValidFlg: 状態(有効、無効)
	    $Key = 'ValidFlg';
	    if(!isset($errors[$Key]) && !isset($data[$Key])){
	        $errors[$Key] = "'状態' が未設定か不正です";
	    }

	    //CpNameKj: 担当者名
	    $Key = 'CpNameKj';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'担当者名'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 160)){
	        $errors[$Key] = "'担当者名'が長すぎます";
	    }

	    //CpNameKn: 担当者名カナ
	    $Key = 'CpNameKn';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'担当者名カナ'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 160)){
	        $errors[$Key] = "'担当者名カナ'が長すぎます";
	    }

	    //DivisionName: 部署名
	    $Key = 'DivisionName';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'部署名'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 255)){
	        $errors[$Key] = "'部署名'が長すぎます";
	    }

	    //MailAddress: メールアドレス
	    $Key = 'MailAddress';
	    if (!isset($errors[$Key]) && strlen($data[$Key]) > 0 && !IsValidFormatMail($data[$Key])) {
	        $errors[$Key] = "'メールアドレス'が無効です";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'メールアドレス'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 255)){
	        $errors[$Key] = "'メールアドレス'が長すぎます";
	    }

	    //ContactPhoneNumber: 連絡先電話番号
	    $Key = 'ContactPhoneNumber';
	    if (!isset($errors[$Key]) && strlen($data[$Key]) > 0 && !IsValidFormatTell($data[$Key])) {
	        $errors[$Key] = "'連絡先電話番号'が無効です";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'連絡先電話番号'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 50)){
	        $errors[$Key] = "'連絡先電話番号'が長すぎます";
	    }

	    //ContactFaxNumber: 連絡先FAX番号
	    $Key = 'ContactFaxNumber';
	    if (!isset($errors[$Key]) && strlen($data[$Key]) > 0 && !IsValidFormatTell($data[$Key])) {
	        $errors[$Key] = "'連絡先FAX番号'が無効です";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'連絡先FAX番号'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 50)){
	        $errors[$Key] = "'連絡先FAX番号'が長すぎます";
	    }

	    //ConnectIpAddressList: 接続IPアドレス
	    $Key = 'ConnectIpAddressList';
	    if (!isset($errors[$Key]) && strlen($data[$Key]) > 0 && !IsValidFormatIp($data[$Key])) {
	        $errors[$Key] = "'接続IPアドレス'が無効です";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'接続IPアドレス'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 4000)){
	        $errors[$Key] = "'接続IPアドレス'が長すぎます";
	    }

	    //: 備考
	    $Key = 'Note';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) < 0)){
	        $errors[$Key] = "'備考'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 4000)){
	        $errors[$Key] = "'備考'が長すぎます";
	    }

	    return $errors;
	}
}