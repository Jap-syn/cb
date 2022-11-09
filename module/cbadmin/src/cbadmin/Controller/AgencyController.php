<?php
namespace cbadmin\Controller;

use Zend\Config\Reader\Ini;
// use Zend\Json\Json;
// use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
// use Coral\Base\IO\BaseIOUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
// use Coral\Coral\Mail\CoralMail;
// use Coral\Coral\CoralValidate;
use Coral\Coral\Validate\CoralValidatePostalCode;
use Coral\Coral\Validate\CoralValidatePhone;
// use Coral\Coral\Validate\CoralValidateMultiMail;
use cbadmin\Application;
// use cbadmin\classes\EnterpriseCsvWriter;
// use cbadmin\classes\EnterpriseCsvSettings;
// use models\Table\TableEnterprise;
// use models\Table\TableSite;
use models\Table\TableOem;
// use models\Table\TableApiUserEnterprise;
// use models\Table\TableSelfBillingProperty;
// use models\Table\TableClaimHistory;
// use models\Table\TableOrder;
use models\Table\TableAgency;
use models\Logic\LogicTemplate;

class AgencyController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';

	/**
	 * @var Application
	 */
	protected $app;

    /**
     *  与信時注文利用額フラグ
     */
    protected $debugUserAmountOver = 0;

	/**
	 * IndexControllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();

        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
        $protocol = preg_match('/^on$/i', $_SERVER['HTTPS']) ? 'https' : 'http';

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');

        $this->setPageTitle("後払い.com - 代理店管理");
        $this->view->assign( 'current_action', $this->getActionName() );

        // コードマスターから事業者情報向けのマスター連想配列を作成し、ビューへアサインしておく
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $masters = array(
                'Prefecture' => $codeMaster->getPrefectureMaster(),
                'FfAccountClass' => $codeMaster->getAccountClassMaster(),
                'TcClass' => $codeMaster->getTcClassMaster(),
                'ExaminationResult' => $codeMaster->getExaminationResultMaster(),
        );

        $this->view->assign('master_map', $masters);
	}

	/**
	 * 事業者一覧を表示
	 */
	public function listAction()
	{
        //パラメータ取得
        $params = $this->getParams();

        $bndprm = array();
        // 代理店リスト取得
        $sql  = " SELECT a.AgencyId ";
        $sql .= " ,      a.OemId ";
        $sql .= " ,      o.OemNameKj ";
        $sql .= " ,      a.AgencyNameKj ";
        $sql .= " ,      a.AgencyNameKn ";
        $sql .= " ,      a.Salesman ";
        $sql .= " ,      a.RepNameKj ";
        $sql .= " ,      a.Phone  ";
        $sql .= " ,      CASE a.ExaminationResult WHEN 1 THEN 'cyan' WHEN 2 THEN 'gray' ELSE 'purple' END AS BkColor ";// 審査結果(0：未審査/1：OK/2：NG)
        $sql .= " FROM   M_Agency a LEFT OUTER JOIN T_Oem o On a.OemId = o.OemId ";
        $sql .= " WHERE  1 = 1 ";
        if (isset($params['OemId']) && $params['OemId'] > 0) {
            // OEM選択が有効な時の対応
            $sql .= " AND a.OemId = :OemId";
            $bndprm[':OemId'] = $params['OemId'];
            $this->view->assign('selectOemId', $params['OemId']);
        }
        $sql .= " ORDER BY OemId, AgencyId ";

        $stm = $this->app->dbAdapter->query($sql);

        $ar = ResultInterfaceToArray($stm->execute($bndprm));

        // OEMIDと名前のリスト取得
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oemList = $mdlOem->getOemIdList();

        $this->view->assign('oemList', $oemList);
        $this->view->assign('list', $ar);

        return $this->view;
	}

	/**
	 * 代理店登録フォームの表示
	 */
	public function formAction() {

        $params = $this->getParams();

        $mdlOem = new TableOem($this->app->dbAdapter);

        //全てのOEM情報取得
        $all_oem_data = $mdlOem->getAllOem();

        $oem_master = array();
        foreach ($all_oem_data as $value){
            //必要なものをOEMIDをキーに取得
            $oem_master[$value['OemId']] = array(
                    "MonthlyFee" => $value['MonthlyFee'],
                    "N_MonthlyFee" => $value['N_MonthlyFee'],
                    "SettlementFeeRateRKF" => $value['SettlementFeeRateRKF'],
                    "SettlementFeeRateSTD" => $value['SettlementFeeRateSTD'],
                    "SettlementFeeRateEXP" => $value['SettlementFeeRateEXP'],
                    "SettlementFeeRateSPC" => $value['SettlementFeeRateSPC'],
                    "ClaimFeeBS" => $value['ClaimFeeBS'])
                    ;
        }

        // config.ini、cj_apiセクションの値取得
        $data = $this->app->config;
        $default_average_price_rate = $data['cj_api'];

        $this->view->assign('mode', '/mode/new');
        $this->view->assign('data', array(
                'isNew' => true,
                'AverageUnitPriceRate' => $default_average_price_rate['default_average_unit_price_rate'],
        ));

        $this->view->assign('selectOem',$oem);
        $this->view->assign('oemList', $mdlOem->getOemIdList());
        $this->view->assign('oem_master',$oem_master);
        $this->view->assign('error', array());

        return $this->view;
	}

	/**
	 * 代理店編集画面を表示
	 */
	public function editAction() {

        $params = $this->getParams();

        $aid = (isset($params['aid'])) ? $params['aid'] : -1;

        // 代理店マスターデータを取得
        $mdl = new TableAgency($this->app->dbAdapter);
        $eData =$mdl->find($aid)->current();

        // OEM情報取得
        $mdlOem = new TableOem($this->app->dbAdapter);
        if(!is_null($eData['OemId'])) {
            $oemList = $mdlOem->findOem($eData['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $eData['OemNameKj'] = $oemList['OemNameKj'];
            }
        }

        //全てのOEM情報取得
        $all_oem_data = $mdlOem->getAllOem();

        $oem_master = array();
        foreach ($all_oem_data as $value){
            //必要なものをOEMIDをキーに取得
            $oem_master[$value['OemId']] = array(
                    "MonthlyFee"           => $value['MonthlyFee'],
                    "N_MonthlyFee"         => $value['N_MonthlyFee'],
                    "SettlementFeeRateRKF" => $value['SettlementFeeRateRKF'],
                    "SettlementFeeRateSTD" => $value['SettlementFeeRateSTD'],
                    "SettlementFeeRateEXP" => $value['SettlementFeeRateEXP'],
                    "SettlementFeeRateSPC" => $value['SettlementFeeRateSPC'],
                    "ClaimFeeBS"           => $value['ClaimFeeBS'],
                    );
        }

        $data = array('isNew' => false);

        $this->view->assign('data', array_merge($data, $eData));
        $this->view->assign('error', array());
        $this->view->assign('oem_master',$oem_master);

        $this->setTemplate('form');
        return $this->view;
	}

	/**
	 * 事業者登録内容の確認
	 */
	public function confirmAction()
	{
        $params = $this->getParams();

        $data = $this->fixInputForm( isset($params['form']) ? $params['form'] : array() );

        $mdlOem = new TableOem($this->app->dbAdapter);
        $errors = $this->validate($data);

        // count関数対策
        if(!empty($errors)) {
            //全てのOEM情報取得
            $all_oem_data = $mdlOem->getAllOem();

            $oem_master = array();
            foreach ($all_oem_data as $value){
                //必要なものをOEMIDをキーに取得
                $oem_master[$value['OemId']] = array(
                        "MonthlyFee"           => $value['MonthlyFee'],
                        "N_MonthlyFee"         => $value['N_MonthlyFee'],
                        "SettlementFeeRateRKF" => $value['SettlementFeeRateRKF'],
                        "SettlementFeeRateSTD" => $value['SettlementFeeRateSTD'],
                        "SettlementFeeRateEXP" => $value['SettlementFeeRateEXP'],
                        "SettlementFeeRateSPC" => $value['SettlementFeeRateSPC'],
                        "ClaimFeeBS"           => $value['ClaimFeeBS'],
                        );
            }

            // 検証エラーは入力画面へ戻す
            $this->view->assign('data', $data);
            $this->view->assign('selectOem',isset($data['OemId'])?$data['OemId']:0);
            $this->view->assign('oemList', $mdlOem->getOemIdList());
            $this->view->assign('oem_master',$oem_master);
            $this->view->assign('error', $errors);

            $this->setTemplate('form');
            return $this->view;
        }

        //「CB負担」の時は振込先をNULLにする
        if($data['ChargeClass'] == 1){
            $data['TransferFeeClass'] = null;
        }

        // 都道府県名を展開
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $data['PrefectureName'] = $codeMaster->getPrefectureName($data['PrefectureCode']);

        // フォームデータ自身をエンコード
        $formData = base64_encode(serialize($data));

        if(!is_null($data['OemId']) && $data['OemId'] != 0) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem($data['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $data['OemNameKj'] = $oemList['OemNameKj'];
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('encoded_data', $formData);

        return $this->view;
	}

	/**
	 * 確認画面からの戻り処理
	 */
	public function backAction()
	{
        $params = $this->getParams();

        $eData = unserialize(base64_decode($params['hash']));

        $mdlOem = new TableOem($this->app->dbAdapter);

        //全てのOEM情報取得
        $all_oem_data = $mdlOem->getAllOem();

        $oem_master = array();
        foreach ($all_oem_data as $value){
            //必要なものをOEMIDをキーに取得
            $oem_master[$value['OemId']] = array(
                    "MonthlyFee"           => $value['MonthlyFee'],
                    "N_MonthlyFee"         => $value['N_MonthlyFee'],
                    "SettlementFeeRateRKF" => $value['SettlementFeeRateRKF'],
                    "SettlementFeeRateSTD" => $value['SettlementFeeRateSTD'],
                    "SettlementFeeRateEXP" => $value['SettlementFeeRateEXP'],
                    "SettlementFeeRateSPC" => $value['SettlementFeeRateSPC'],
                    "ClaimFeeBS"           => $value['ClaimFeeBS'],
                    );
        }

        $this->view->assign('data', array_merge($eData));
        $this->view->assign('error', array());
        $this->view->assign('oemList', $mdlOem->getOemIdList());
        $this->view->assign('oem_master',$oem_master);

        $this->setTemplate('form');
        return $this->view;
	}

	/**
	 * 代理店登録を実行
	 */
	public function saveAction()
	{
        $params = $this->getParams();

        $eData = unserialize(base64_decode($params['hash']));

        $mdl = new TableAgency($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        if( ! $eData['isNew'] && isset($eData['AgencyId']) ) {
            // 編集
            unset($eData['FeeUnpaidBalance']);  // 手数料未払残高は更新しない
            $eData['UpdateId'] = $userId;
            $mdl->saveUpdate($eData, $eData['AgencyId']);
        }
        else {
            // 新規保存
            $eData['RegistId'] = $userId;
            $eData['UpdateId'] = $userId;
            $newId = $mdl->saveNew($eData);

            $eData['AgencyId'] = $newId;
        }

        // 保存済みデータをエンコード
        $data = base64_encode(serialize($eData));

        $this->view->assign('data', $data);

        return $this->view;
	}

	/**
	 * 登録完了画面の表示
	 */
	public function completionAction()
	{
        $params  = $this->getParams();

        $data = unserialize(base64_decode($params['hash']));
        if (!$data) {
            return $this->_redirect("agency/list");
        }

        if(!is_null($data['OemId']) && $data['OemId'] != 0) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem($data['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $data['OemNameKj'] = $oemList['OemNameKj'];
            }
        }

        $this->view->assign('aid', $data['AgencyId']);
        $this->view->assign('data', $data);

        return $this->view;
	}

	/**
	 * 代理店一覧のCSVダウンロード
	 */
	public function dcsvAction()
	{
	    $params  = $this->getParams();

	    $oemId = isset( $params['oemid'] ) ? $params['oemid'] : 0;

        $sql  = " SELECT a.AgencyId ";
        $sql .= " ,      a.OemId ";
        $sql .= " ,      a.AgencyNameKj ";
        $sql .= " ,      a.AgencyNameKn ";
        $sql .= " ,      a.PostalCode ";
        $sql .= " ,      a.PrefectureCode ";
        $sql .= " ,      a.PrefectureName ";
        $sql .= " ,      a.City ";
        $sql .= " ,      a.Town ";
        $sql .= " ,      a.Building ";
        $sql .= " ,      a.RepNameKj ";
        $sql .= " ,      a.RepNameKn ";
        $sql .= " ,      a.Phone ";
        $sql .= " ,      a.Fax ";
        $sql .= " ,      a.Salesman ";
        $sql .= " ,      a.FfName ";
        $sql .= " ,      a.FfCode ";
        $sql .= " ,      a.BranchName ";
        $sql .= " ,      a.FfBranchCode ";
        $sql .= " ,      a.AccountNumber ";
        $sql .= " ,      a.FfAccountClass ";
        $sql .= " ,      a.AccountHolder ";
        $sql .= " ,      a.TcClass ";
        $sql .= " ,      a.Note ";
        $sql .= " ,      a.RegistDate ";
        $sql .= " ,      F_GetLoginUserName( a.RegistId ) AS RegistName ";
        $sql .= " ,      a.UpdateDate ";
        $sql .= " ,      F_GetLoginUserName( a.UpdateId ) AS UpdateName ";
        $sql .= " ,      a.ValidFlg ";
        $sql .= " ,      o.OemNameKj ";
        $sql .= "        FROM M_Agency a LEFT OUTER JOIN T_Oem o On a.OemId = o.OemId ";
        $sql .= "        WHERE 1=1";

        $prm = array();
        if( $oemId > 0 ) {
            $sql .= " AND a.OemId = :OemId ";
            $prm = array( ':OemId' => $oemId );
	    }
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $datas = ResultInterfaceToArray( $ri );

        $templateId = 'CKI16140_1';    // 代理店一覧
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'agency_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
	}

	/**
	 * POSTされた入力フォームに対し、未送信キーを補完する
	 *
	 * @access protected
	 * @param array $data POSTデータ
	 * @return array $dataの未送信キーを補完したデータ
	 */
	protected function fixInputForm(array $data)
	{
        $defaults = array(
                'FfAccountClass' => -1
        );

        return array_merge($defaults, $data);
	}

	/**
	 * 入力検証処理
	 *
	 * @access protected
	 * @param array $data
	 * @return array
	 */
	protected function validate($data = array())
	{
        $isNew = $data['isNew'] ? true : false;

        $errors = array();

        //------------------
        // 代理店情報
        //------------------
        // ApplicationDate: 登録日
        $key = 'ApplicationDate';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("登録日が未入力です。");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("登録日が入力されていても日付形式でありません。");
        }

        // Salesman: キャッチボール営業
        $key = 'Salesman';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("キャッチボール営業が未入力です。");
        }

        // AgencyNameKj: 代理店名
        $key = 'AgencyNameKj';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("代理店名が未入力です。");
        }
        $sql  = " SELECT (a.AgencyNameKj)";
        $sql .= "        FROM M_Agency a ";
        $sql .= "        WHERE 1=1";
        $sql .= " AND a.AgencyNameKj = :AgencyNameKj ";
        $prm = array();
        if( $isNew == false) {
            $sql .= " AND a.AgencyId <> :AgencyId ";
            $prm = array( ':AgencyId' => $data['AgencyId'], ':AgencyNameKj' => $data['AgencyNameKj']);
        }else{
            $prm = array( ':AgencyNameKj' => $data['AgencyNameKj']);
        }
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        
        if(!isset($errors[$key]) && ($ri->count() > 0)){
            $errors[$key] = array("代理店名が既に登録されています。");
        }

        // AgencyNameKn: 代理店名カナ
        $key = 'AgencyNameKn';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("代理店名カナが未入力です。");
        }
        if (!isset($errors[$key]) && !(preg_match('/^[ァ-ヾ 　]+$/u', $data[$key]))) {
            $errors[$key] = array("代理店名カナがカタカナでないです。");
        }

        // PostalCode: 〒
        $key = 'PostalCode';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("所在地〒が未入力です。");
        }
        $cvpc = new CoralValidatePostalCode();
        if (!isset($errors[$key]) && !$cvpc->isValid($data[$key])) {
            $errors[$key] = array("所在地〒が不正な形式です。");
        }

        // PrefectureCode: 都道府県
        $key = 'PrefectureCode';
        if (!isset($errors[$key]) && !((int)$data[$key] >= 1 && (int)$data[$key] <=47 )) {
            $errors[$key] = array("所在地県が未選択です。");
        }

        // City: 市区郡
        $key = 'City';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("所在地市区郡が未入力です。");
        }

        // Town: 町域
        $key = 'Town';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("所在地町域が未入力です。");
        }

        // RepNameKj: 代表者氏名
        $key = 'RepNameKj';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("代表者氏名が未入力です。");
        }

        // RepNameKn: 代表者氏名カナ
        $key = 'RepNameKn';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("代表者氏名カナが未入力です。");
        }
        if (!isset($errors[$key]) && !(preg_match('/^[ァ-ヾ 　]+$/u', $data[$key]))) {
            $errors[$key] = array("代表者カナがカタカナでないです。");
        }

        // Phone: 代表電話番号
        $key = 'Phone';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("代表電話番号が未入力です。");
        }
        $cvp = new CoralValidatePhone();
        if (!isset($errors[$key]) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("代表電話番号が不正な形式です。");
        }

        // Fax: 代表FAX番号
        $key = 'Fax';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("代表FAX番号が不正な形式です。");
        }

        //------------------
        // 入金口座
        //------------------
        // FfName: 銀行名
        $key = 'FfName';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("銀行名が未入力です。");
        }

        // FfCode: 銀行番号
        $key = 'FfCode';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("銀行番号が未入力です。");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("銀行番号が数値でありません。");
        }

        // BranchName: 支店名
        $key = 'BranchName';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("支店名が未入力です。");
        }

        // FfBranchCode: 支店番号
        $key = 'FfBranchCode';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("支店番号が未入力です。");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("支店番号が数値でありません。");
        }

        // AccountNumber: 口座番号
        $key = 'AccountNumber';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("口座番号が未入力です。");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("口座番号が数値でありません。");
        }

        // AccountHolder: 口座名義
        $key = 'AccountHolder';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("口座名義が未入力です。");
        }

        //------------------
        // その他
        //------------------
        // FeePaymentThreshold: 手数料支払閾値
        $key = 'FeePaymentThreshold';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("手数料支払閾値が数値でありません。");
        }

        //------------------
        // 審査状況
        //------------------

        return $errors;
    }
}

