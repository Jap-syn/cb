<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use cbadmin\classes\OemCsvSettings;
use cbadmin\classes\OemCsvWriter;
use Coral\Coral\Controller\CoralControllerAction;
use models\Logic\LogicImageUpLoader;
use models\Logic\LogicOemClaimAccount;
use models\Logic\LogicTemplate;
use models\Logic\LogicFfTcFee;
use models\Logic\AccountValidity\LogicAccountValidityPasswordValidator;
use models\Table\TableOem;
use models\Table\TableOemOperator;
use models\Table\TablePasswordHistory;
use models\Table\TableSystemProperty;
use models\Table\TableTmpImage;
use models\Table\TableUser;
use models\Table\TableCode;
use Coral\Coral\CoralCodeMaster;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\IO\BaseIOUtility;
use Coral\Coral\CoralValidate;
use Zend\Session\Container;
use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;
use models\Table\TablePricePlan;

class OemController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
     */
    protected $app;

    /**
     * IndexControllerを初期化する
     */
    public function _init() {
        $this->app = Application::getInstance();

        preg_match('/^\/([^\/]+)\/?/', $_SERVER['REQUEST_URI'], $matches);
        $this
            ->addStyleSheet('../css/default02.css')
            ->addJavaScript('../js/prototype.js')
            ->addJavaScript('../js/json.js')
            ->addJavaScript('../js/corelib.js');

        $this->setPageTitle("後払い.com - OEM先管理");
        $this->view->assign( 'current_action', $this->getActionName() );
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        // コードマスターからOEM情報向けのマスター連想配列を作成し、ビューへアサインしておく
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $masters = array(
            'Prefecture' => $codeMaster->getPrefectureMaster(),
            'FfAccountClass' => $codeMaster->getAccountClassMaster(),
            'TcClass' => $codeMaster->getTcClassMaster(),
            'StyleSheets' => $codeMaster->getOemStyleSheetsMaster(),
        );

        // コードマスターから与信条件を作成し、ビューへアサインしておく
        $code = new TableCode( $this->app->dbAdapter );
        $codeList = ResultInterfaceToArray( $code->getMasterByClass( 91 ) );
        $creditCriterion = array();
        foreach($codeList as $value) {
            $creditCriterion[$value['KeyCode']] = $value['KeyContent'];
        }
        $this->view->assign('master_map', $masters);
        $this->view->assign('credit_criterion', $creditCriterion);
    }

    /**
     * OEM一覧を表示
     */
    public function listAction() {
        $oem = new TableOem($this->app->dbAdapter);
        unset($_SESSION['TMP_IMAGE_SEQ']);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('list', $oem->getAllOem());

        return $this->view;
    }

    /**
     * OEM情報詳細画面を表示
     */
    public function detailAction() {
        $oid = $this->params()->fromRoute('oid', -1);
        if($oid == -1){
            $oid = $_POST['oid'];
        }

        $t_oem = new TableOem($this->app->dbAdapter);
        //OEM情報取得
        $o = $t_oem->findOem2($oid)->current();

        if(!$o['OemId']) {
            throw new \Exception(sprintf("OEM-ID '%s' は不正な指定です", $oid));
        }

        $mdlOemOperator = new TableOemOperator($this->app->dbAdapter);

        //OEMオペレータ情報取得
        $oem_operator = $mdlOemOperator->findOperatorByOemId($oid)->current();

        //OEMオペレータが取得できなかった場合初期値空にしておく
        if(is_null($oem_operator['OemOpId'])){
            $oem_operator = array("LoginId" => "", "LoginPasswd" => "" );
        }

        // 利率を実数化補正
        $o = $this->fixSettelementFeeRate($o);

        // マスターがらみの項目については、キャプションを求めてセットする。
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $o['FfAccountClass'] = $codeMaster->getAccountClassCaption((int)$o['FfAccountClass']);
        $o['TcClass'] = $codeMaster->getTcClassCaption((int)$o['TcClass']);
        $o['StyleSheets'] = $codeMaster->getOemStyleSheetsCaption((int)$o['StyleSheets']);

        //立替方法名取得
        if($o['PayingMethod'] == 0 ){
            $o['payingmethod_name'] = "CB立替";
        }else{
            $o['payingmethod_name'] = "OEM立替";
        }

        // 料金プランマスタのデータを取得する。
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $ri = $mdlpp->getAll();
        $planName = ResultInterfaceToArray($ri);

        // 取得データからJSON形式の情報をdecodeする
        // プラン別決済手数料率
        $rate = Json::decode($o['SettlementFeeRatePlan'], Json::TYPE_ARRAY);

        // プラン別月額固定費
        $fee = Json::decode($o['EntMonthlyFeePlan'], Json::TYPE_ARRAY);

        // 配列にキーが存在しているか確認して配列を作りこむ。
        $plan = array();
        foreach ($planName as $key => $value) {
            // 基本の配列を作成する
            $plan[$key] = array(
                    'PricePlanId' => $value['PricePlanId'],
                    'PricePlanName' => $value['PricePlanName'],
            );
            // 決済手数料率
            // データが取得できた場合、以下処理を行う。
            if (isset($rate)) {
                if (array_key_exists($value['PricePlanId'], $rate)) {
                    foreach ($rate as $key2 => $value2) {
                        if ($value['PricePlanId'] == $key2) {
                            // 基本の配列に追加要素を足しこむ
                            $plan[$key] += array( 'SettlementFeeRate' => $value2 );
                        }
                    }
                } else {
                    $plan[$key] += array( 'SettlementFeeRate' => $value['SettlementFeeRate'] );
                }
            }
            // 店舗月額固定費
            // データが取得できた場合、以下処理を行う。
            if (isset($fee)) {
                if (array_key_exists($value['PricePlanId'], $fee)) {
                    foreach ($fee as $key2 => $value2) {
                        if ($value['PricePlanId'] == $key2) {
                            // 基本の配列に追加要素を足しこむ
                            $plan[$key] += array( 'EntMonthlyFee' => $value2 );
                        }
                    }
                } else {
                    $plan[$key] += array( 'EntMonthlyFee' => $value['MonthlyFee'] );
                }
            }
        }

        // 詳細画面からの更新処理で検証エラーが発生していたらその情報をマージする
        $o = array_merge($o, $_POST['prev_input'] ? $_POST['rev_input'] : array() );
        $this->view->assign('error', $_POST['prev_errors'] ? $_POST['prev_errors'] : array() );
        $backTo = $_POST['prev_backto'];

        $this->view->assign('data', $o);
        $this->view->assign('plan', $plan);
        $this->view->assign('backTo', $backTo);
        $this->view->assign('operator', $oem_operator);

        // 請求口座設定情報取得
        $oemClaimAccounts = new LogicOemClaimAccount($this->app->dbAdapter);
        $this->view->assign('claimAccounts', $oemClaimAccounts->findClaimAccountsByOemId($oid)->current());

        //echo sprintf('<pre>%s</pre>', f_e(var_export($o, true)));

        return $this->view;
    }

    /**
     * OEM登録フォームの表示
     */
    public function formAction() {
        unset($_SESSION['TMP_IMAGE_SEQ']);
        // 立替手数料を取得する
        $ffTcFee = new LogicFfTcFee($this->app->dbAdapter);

        // 料金プランマスタのデータを取得する。
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $planName = ResultInterfaceToArray($mdlpp->getAll());

        // 標準決済手数料率と店舗月額固定費取得用の配列を作成
        $plan = array();
        foreach ($planName as $key => $value) {
            $plan[$key] = array(
                    'PricePlanId' => $value['PricePlanId'],
                    'PricePlanName' => $value['PricePlanName'],
                    'SettlementFeeRate' => $value['SettlementFeeRate'],
                    'EntMonthlyFee' => $value['MonthlyFee']
            );
        }

        $this->view->
        assign('data',array('isNew' => true,
                'PayingMethod'=>0,
                'SameFfTcFeeUnderThirtyK' => $ffTcFee->getFfTcFee(1, null, 1, 0),
                'SameFfTcFeeThirtyKAndOver' => $ffTcFee->getFfTcFee(2, null, 1, 30001),
                'OtherFfTcFeeUnderThirtyK' => $ffTcFee->getFfTcFee(3, null, 2, 0),
                'OtherFfTcFeeThirtyKAndOver' => $ffTcFee->getFfTcFee(4, null, 2, 30001),
        ));
        $this->view->assign('plan', $plan);
        $this->view->assign('error', array());

        return $this->view;
    }

    /**
     * OEM編集画面を表示
     */
    public function editAction() {
        unset($_SESSION['TMP_IMAGE_SEQ']);
        $oid = $this->params()->fromRoute("oid", -1);

        $mdlOem = new TableOem($this->app->dbAdapter);
        $mdlOemOperator = new TableOemOperator($this->app->dbAdapter);

        // OEMデータを取得し、利率を実数化補正
        $oData = $mdlOem->findOem2($oid)->current();
        if(!$oData['OemId']) {
            throw new \Exception(sprintf("OEM-ID '%s' は不正な指定です", $oid));
        }
        $oData = $this->fixSettelementFeeRate($oData);

        $data = array('isNew' => false);
        // 代表オペレータのログインIDを反映
        $opData = $mdlOemOperator->findOperatorByOemId($oid)->current();
        if($opData) {
            $data['EntLoginId'] = $opData['LoginId'];
        }
         if($oData['FavIconType'] == 'url') {
            $data['FavIconUrl'] = $oData['FavIcon'];
        }

        // 料金プランマスタのデータを取得する。
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $planName = ResultInterfaceToArray($mdlpp->getAll());

        // 取得データからJSON形式の情報をdecodeする
        // プラン別決済手数料率
        $rate = Json::decode($oData['SettlementFeeRatePlan'], Json::TYPE_ARRAY);

        // プラン別月額固定費
        $fee = Json::decode($oData['EntMonthlyFeePlan'], Json::TYPE_ARRAY);

        // 配列にキーが存在しているか確認して配列を作りこむ。
        $plan = array();
        foreach ($planName as $key => $value) {
            // 基本の配列を作成する
            $plan[$key] = array(
                    'PricePlanId' => $value['PricePlanId'],
                    'PricePlanName' => $value['PricePlanName'],
            );
            // 決済手数料率
            // データが取得できた場合、以下処理を行う。
            if (isset($rate)) {
                if (array_key_exists($value['PricePlanId'], $rate)) {
                    foreach ($rate as $key2 => $value2) {
                        if ($value['PricePlanId'] == $key2) {
                            // 基本の配列に追加要素を足しこむ
                            $plan[$key] += array( 'SettlementFeeRate' => $value2 );
                        }
                    }
                } else {
                    $plan[$key] += array( 'SettlementFeeRate' => $value['SettlementFeeRate'] );
                }
            } else {
                $plan[$key] += array( 'SettlementFeeRate' => $value['SettlementFeeRate'] );
            }
            // 店舗月額固定費
            // データが取得できた場合、以下処理を行う。
            if (isset($fee)) {
                if (array_key_exists($value['PricePlanId'], $fee)) {
                    foreach ($fee as $key2 => $value2) {
                        if ($value['PricePlanId'] == $key2) {
                            // 基本の配列に追加要素を足しこむ
                            $plan[$key] += array( 'EntMonthlyFee' => $value2 );
                        }
                    }
                } else {
                    $plan[$key] += array( 'EntMonthlyFee' => $value['MonthlyFee'] );
                }
            } else {
                $plan[$key] += array( 'EntMonthlyFee' => $value['MonthlyFee'] );
            }
        }

        $this->view->assign('data', array_merge($data, $oData));
        $this->view->assign('plan', $plan);
        $this->view->assign('error', array());
        $this->view->assign('CurrentFixPatternMsg', $currentFixPatternMsg);

        $this->setTemplate('form');

        return $this->view;
    }

    /**
     * OEM登録内容の確認
     */
    public function confirmAction() {
        $data = $this->fixInputForm( $this->params()->fromPost('form', array()) );
        $params = $this->getParams();
        $favicon_info = array(
                'select' => isset($params['favicon_type']) ? $params['favicon_type'] : 'empty',
                'image' => $data['FavIcon'],
                'type' => null
        );
        $upload_file = $_FILES['form'];
        $image_uploaded = false;

        // 特定フィールドについては暗黙的に大文字変換を適用
        foreach(array('EntLoginIdPrefix', 'OrderIdPrefix') as $ucKey) {
            if(isset($data[$ucKey])) {
                $data[$ucKey] = strtoupper($data[$ucKey]);
            }
        }

        $errors = $this->validate($data, $favicon_info);

        //OEMIDがある場合OEMの情報取得する
        if(isset($data['OemId'] )){
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oem_data = $mdlOem->findOem2($data['OemId'])->current();

            if(!empty($data)){
                $data['LargeLogo']['image'] = $oem_data['LargeLogo'];
                $data['SmallLogo']['image'] = $oem_data['SmallLogo'];
                $data['Imprint']['image'] = $oem_data['Imprint'];
                $data['FavIcon'] = array(
                                         'image' => $oem_data['FavIcon'],
                                         'type' => $oem_data['FavIconType']);
            }

        }

        $db = $this->app->dbAdapter;

        $iul = new LogicImageUpLoader($db);

        //リロードされないようにセッション管理
        $tis = new Container('TMP_IMAGE_SEQ');

        // トランザクション開始
        $db = $this->app->dbAdapter;
        $db->getDriver()->getConnection()->beginTransaction();

        // ユーザーIDの取得
        $obj = new TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try{

            //ロゴ1設定チェック
            if(!empty($upload_file['name']['LargeLogo'])){

                //セッションにデータがある場合はセッションから画像データ取得する
                if(!is_null($tis->logo1)){
                    $logo1_seq = $tis->logo1;
                }else{

                    //画像を一時アップロード
                    $logo1_seq = $iul->saveLogo1TmpImage(null,$upload_file['type']['LargeLogo'],
                                                                     $upload_file['name']['LargeLogo'],$upload_file['tmp_name']['LargeLogo'],$userId);
                    $tis->logo1 = $logo1_seq;
                }

                //ロゴ1取得
                $logo1_data = $iul->getLogo1TmpImage($logo1_seq);

                //取得に成功していれば代入
                if(!is_null($logo1_data)){
                    $data['LargeLogo']['image'] = $logo1_data['ImageData'];
                    $data['LargeLogo']['seq'] = $logo1_seq;
                    $image_uploaded = true;
                }

            }
            //ロゴ2設定チェック
            if(!empty($upload_file['name']['SmallLogo'])){

                //セッションにデータがある場合はセッションから画像データ取得する
                if(!is_null($tis->logo2)){
                    $logo2_seq = $tis->logo2;
                }else{

                    //画像を一時アップロード
                    $logo2_seq = $iul->saveLogo2TmpImage(null,$upload_file['type']['SmallLogo'],
                                                            $upload_file['name']['SmallLogo'],$upload_file['tmp_name']['SmallLogo'],$userId);
                    $tis->logo2 = $logo2_seq;
                }
                //ロゴ2取得
                $logo2_data = $iul->getLogo2TmpImage($logo2_seq);

                if(!is_null($logo2_data)){

                    $data['SmallLogo']['image'] = $logo2_data['ImageData'];
                    $data['SmallLogo']['seq'] = $logo2_seq;
                    $image_uploaded = true;
                }
            }

            //印影チェック
            if(!empty($upload_file['name']['Imprint'])){

                //セッションにデータがある場合はセッションから画像データ取得する
                if(!is_null($tis->imprint)){
                    $imprint_seq = $tis->imprint;
                }else{

                    //画像を一時アップロード
                    $imprint_seq = $iul->saveImprintTmpImage(null,$upload_file['type']['Imprint'],
                                                            $upload_file['name']['Imprint'],$upload_file['tmp_name']['Imprint'],$userId);
                    $tis->imprint = $imprint_seq;
                }

                //印影取得
                $imprint_data = $iul->getImprintTmpImage($imprint_seq);
                if(!is_null($imprint_data)){

                    $data['Imprint']['image'] = $imprint_data['ImageData'];
                    $data['Imprint']['seq'] = $imprint_seq;
                    $image_uploaded = true;
                }
            }

            // faviconチェック
            if($favicon_info['select'] == 'local') {
                if(!empty($upload_file['name']['FavIcon'])) {
                    if(!is_null($tis->favicon)) {
                        // セッションにデータがある場合はセッションからシーケンスを取得
                        $favicon_seq = $tis->favicon;
                    } else {
                        // セッションにデータがないのでアップロードされたファイルを一時保存してそのシーケンスを取得
                        $favicon_seq =
                            $iul->saveFavIconTmpImage(null, $upload_file['type']['FavIcon'],
                                                      $upload_file['name']['FavIcon'],
                                                      $upload_file['tmp_name']['FavIcon'],
                                                      $userId);
                        // 取得したシーケンスをセッションに退避
                        $tis->favicon = $favicon_seq;
                    }

                    // favicon取得
                    $favicon_data = $iul->getFavIconTmpImage($favicon_seq);
                    if($favicon_data) {
                        $data['FavIcon']['image'] = $favicon_data['ImageData'];
                        $data['FavIcon']['type'] = $favicon['ImageType'];
                        $data['FavIcon']['seq'] = $favicon_seq;
                        $image_uploaded = true;
                    }
                }
            } else if($favicon_info['select'] == 'empty') {
                $data['FavIcon']['image'] = null;
                $data['FavIcon']['type'] = 'empty';
            } else {
                if(!empty($data['FavIconUrl'])) {
                    $data['FavIcon']['image'] = $data['FavIconUrl'];
                    $data['FavIcon']['type'] = 'url';
                }
            }

            $db->getDriver()->getConnection()->commit();

        }catch(\Exception $err){

            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }


        // 都道府県名を展開
        $codeMaster = new CoralCodeMaster($db);
        $data['PrefectureName'] = $codeMaster->getPrefectureName($data['PrefectureCode']);

        // 標準決済手数料率、店舗月額固定費を展開するための利用フランマスタを取得する。
        $mdlpp = new TablePricePlan($db);
        $planName = ResultInterfaceToArray($mdlpp->getAll());

        //立替方法名取得
        if( $data['PayingMethod'] == 0 ){
            $payingmethod_name = "CB立替";
        }else{
            $payingmethod_name = "OEM立替";
        }

        // 検証エラーがあった場合は入力画面を表示
        // count関数対策
        if(!empty($errors)) {
            // ロゴ・印影をDBの値に復元
            $data['LargeLogo'] = $oem_data['LargeLogo'];
            $data['SmallLogo'] = $oem_data['SmallLogo'];
            $data['Imprint'] = $oem_data['Imprint'];

            // faviconの設定
            if(in_array($favicon_info['select'], array('empty', 'url'))) {
                // url、emptyを選択していたらそちらを採用
                $data['FavIconType'] = $favicon_info['select'];
                $data['FavIcon'] = $favicon_info['select'] == 'url' ? $data['FavIconUrl'] : null;
            } else {
                // DBから復元
                $data['FavIcon'] = $oem_data['FavIcon'];
                $data['FavIconType'] = $oem_data['FavIconType'];
            }

            // エラー用に配列を作りこむ
            $plan = array();
            foreach ($planName as $key => $value) {
                $plan[$key] = array(
                        'PricePlanId' => $value['PricePlanId'],
                        'PricePlanName' => $value['PricePlanName'],
                        'SettlementFeeRate' => $data['SettlementFeeRate' . $value['PricePlanId']],
                        'EntMonthlyFee' => $data['EntMonthlyFee' . $value['PricePlanId']]
                );
            }

            $this->view->assign('plan', $plan);
            $this->view->assign('data', $data);
            $this->view->assign('error', $errors);

            if($image_uploaded) {
                $this->view->assign('imageParged', '検証エラーが発生したため、アップロードされた画像は破棄されました。もう一度登録してください');
            }

            $this->setTemplate('form');
            return $this->view;
        }

        // フォームデータ自身をエンコード
        $formData = base64_encode(serialize($data));

        $this->view->assign('plan', $planName);
        $this->view->assign('data', $data);
        $this->view->assign('imageUploaded', $image_uploaded);
        $this->view->assign('file', $file);
        $this->view->assign('payingmethod_name',$payingmethod_name);
        $this->view->assign('encoded_data', $formData);

        return $this->view;
    }

    /**
     * 確認画面からの戻り処理
     */
    public function backAction() {
        // エンコード済みのPOSTデータを復元する
        $oData = unserialize(base64_decode($this->params()->fromPost('hash')));

        $registeredImages = array(
            'LargeLogo' => $oData['LargeLogo'],
            'SmallLogo' => $oData['SmallLogo'],
            'Imprint' => $oData['Imprint'],
            'FavIcon' => $oData['FavIcon'],
            'FavIconType' => $oData['FavIcon']['type']
        );

        // すべての画像データをいったんDBから復元
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oem_data = $mdlOem->findOem2($oData['OemId'])->current();
        foreach(array('LargeLogo', 'SmallLogo', 'Imprint', 'FavIcon', 'FavIconType') as $key) {
            $oData[$key] = $oem_data[$key];
        }

        // URL指定の場合は入力データを復元する
        if(in_array($registeredImages['FavIconType'], array('url', 'empty'))) {
            $oData['FavIconType'] = $registeredImages['FavIconType'];
            $oData['FavIcon'] = $registeredImages['FavIconType'] == 'url' ? $registeredImages['FavIcon']['image'] : null;
        }

        // 標準決済手数料率、店舗月額固定費を展開するための利用フランマスタを取得する。
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $planName = ResultInterfaceToArray($mdlpp->getAll());

        // 戻ってきた処理用に配列を作りこむ
        $plan = array();
        foreach ($planName as $key => $value) {
            $plan[$key] = array(
                    'PricePlanId' => $value['PricePlanId'],
                    'PricePlanName' => $value['PricePlanName'],
                    'SettlementFeeRate' => $oData['SettlementFeeRate' . $value['PricePlanId']],
                    'EntMonthlyFee' => $oData['EntMonthlyFee' . $value['PricePlanId']]
            );
        }

        $this->view->assign('plan', $plan);
        $this->view->assign('data', $oData);
        $this->view->assign('error', array());

        $params = $this->getParams();

        if(isset($params['image_uploaded']) ? $params['image_uploaded'] : false) {
            $this->view->assign('imageParged', 'アップロードされた画像は破棄されました。もう一度登録してください');
        }

        // セッションデータを破棄
        unset($_SESSION['TMP_IMAGE_SEQ']);

        $this->setTemplate('form');

        return $this->view;
    }

    /**
     * OEM登録を実行
     */
    public function saveAction() {
        $oData = unserialize(base64_decode($this->params()->fromPost('hash')));

        $mdlOem = new TableOem($this->app->dbAdapter);

        $db = $this->app->dbAdapter;

        $iul = new LogicImageUpLoader($db);

        $mdl_tmpImage = new TableTmpImage($db);

        // ----- 同梱の整数化-----
        //初期費用率
        $oData['OpDkInitFeeRate'] = BaseGeneralUtils::ToSaveRate($oData['OpDkInitFeeRate']);
        //月額固定費率
        $oData['OpDkMonthlyFeeRate'] = BaseGeneralUtils::ToSaveRate($oData['OpDkMonthlyFeeRate']);

        // ---- APIの整数化 -----
        //注文登録利用料率
        $oData['OpApiRegOrdMonthlyFeeRate'] = BaseGeneralUtils::ToSaveRate($oData['OpApiRegOrdMonthlyFeeRate']);
        //全API初期費用率
        $oData['OpApiAllInitFeeRate'] = BaseGeneralUtils::ToSaveRate($oData['OpApiAllInitFeeRate']);
        //全API月額固定費率
        $oData['OpApiAllMonthlyFeeRate'] = BaseGeneralUtils::ToSaveRate($oData['OpApiAllMonthlyFeeRate']);

        // 登録日時を設定
        $oData['RegistDate'] = date("Y-m-d H:i:s");

        // 有効フラッグを設定
        $oData['ValidFlg'] = 1;

        // 料金プランマスタを取得してデータをループする。
        $mdlpp = new TablePricePlan($db);
        $plan = ResultInterfaceToArray($mdlpp->getAll());
        $rate = array();        // 標準決済手数料率用配列
        $fee = array();         // 店舗月額固定費用配列
        foreach ($plan as $value) {
            $rate[$value['PricePlanId']] = $oData['SettlementFeeRate' . $value['PricePlanId']];
            $fee[$value['PricePlanId']] = $oData['EntMonthlyFee' . $value['PricePlanId']];
        }
        // 標準決済手数料率をJSON形式にencodeする。
        $oData['SettlementFeeRatePlan'] = Json::encode( $rate );

        // 店舗月額固定費をJSON形式にencodeする。
        $oData['EntMonthlyFeePlan'] = Json::encode( $fee );

        // ---- 画像関連 ----
        $image_seq_array = array();

        //ロゴ1
        if(!empty($oData['LargeLogo']['seq'])){
            //一時画像取得
            $logo1_data = $iul->getLogo1TmpImage($oData['LargeLogo']['seq']);

            //画像取得に失敗した場合更新しない
            if(empty($logo1_data)){
                unset($oData['LargeLogo']);
            }else{
                //シーケンス番号を配列に格納
                $image_seq_array[] = $oData['LargeLogo']['seq'];

                $oData['LargeLogo'] = $logo1_data['ImageData'];

            }

        }else{
            unset($oData['LargeLogo']);
        }

        //ロゴ2
        if(!empty($oData['SmallLogo']['seq'])){
            //一時画像取得
            $logo2_data = $iul->getLogo2TmpImage($oData['SmallLogo']['seq']);

            //画像取得に失敗した場合更新しない
            if(empty($logo2_data)){
                unset($oData['SmallLogo']);
            }else{
                //シーケンス番号を配列に格納
                $image_seq_array[] = $oData['SmallLogo']['seq'];

                $oData['SmallLogo'] = $logo2_data['ImageData'];

            }
        }else{
            unset($oData['SmallLogo']);
        }

        //印影
        if(!empty($oData['Imprint']['seq'])){
            //一時画像取得
            $imprint_data = $iul->getImprintTmpImage($oData['Imprint']['seq']);

            //画像取得に失敗した場合更新しない
            if(empty($imprint_data)){
                unset($oData['Imprint']);
            }else{
                //シーケンス番号を配列に格納
                $image_seq_array[] = $oData['Imprint']['seq'];

                $oData['Imprint'] = $imprint_data['ImageData'];

            }
        }else{
            unset($oData['Imprint']);
        }

        // favicon
        if($oData['FavIcon']['type'] == 'empty') {
            $oData['FavIcon'] = null;
            $oData['FavIconType'] = 'empty';
        } else if(!empty($oData['FavIcon']['seq'])) {
            // 一時画像取得
            $favicon_data = $iul->getFavIconTmpImage($oData['FavIcon']['seq']);

            // 画像取得に失敗した場合は更新しない
            if(empty($favicon_data)) {
                unset($oData['FavIcon']);
            } else {
                // シーケンスを配列に退避
                $image_seq_array[] = $oData['FavIcon']['seq'];
                $oData['FavIcon'] = $favicon_data['ImageData'];
                $oData['FavIconType'] = $favicon_data['ImageType'];
            }
        } else if($oData['FavIcon']['type'] == 'url' && !empty($oData['FavIcon']['image'])) {
            $favicon_info = $oData['FavIcon'];
            $oData['FavIcon'] = $favicon_info['image'];
            $oData['FavIconType'] = $favicon_info['type'];
        } else {
            unset($oData['FavIcon']);
        }

        // 請求書発行履歴
        $oData['RecordClaimPrintedDateFlg'] = $oData['RecordClaimPrintedDateFlg'] ? 1 : 0;

        //精算日に翌週を選択した場合 - kim
        if($oData['day_week'] == 'week'){
            $oData['SettlementDay1'] = $oData['SettlementWeek1'];
            $oData['SettlementDay2'] = $oData['SettlementWeek2'];
            $oData['SettlementDay3'] = $oData['SettlementWeek3'];
        }

        // 空白はNULLに設定(数値型,DATE型に空文字列を指定すると0,0000-00-00になってしまうため)
        if( empty( $oData['OemFixedDay1'] ) ) {
            $oData['OemFixedDay1'] = null;
        }
        if( empty( $oData['OemFixedDay2'] ) ) {
            $oData['OemFixedDay2'] = null;
        }
        if( empty( $oData['OemFixedDay3'] ) ) {
            $oData['OemFixedDay3'] = null;
        }
        if( empty( $oData['OemFixedDay_Week'] ) ) {
            $oData['OemFixedDay_Week'] = null;
        }
        if( empty( $oData['SettlementDay1'] ) ) {
            $oData['SettlementDay1'] = null;
        }
        if( empty( $oData['SettlementDay2'] ) ) {
            $oData['SettlementDay2'] = null;
        }
        if( empty( $oData['SettlementDay3'] ) ) {
            $oData['SettlementDay3'] = null;
        }
        if( empty( $oData['SettlementDay_Week'] ) ) {
            $oData['SettlementDay_Week'] = null;
        }
        if( empty( $oData['AutoCreditDateFrom'] ) ) {
            $oData['AutoCreditDateFrom'] = null;
        }
        if( empty( $oData['AutoCreditDateTo'] ) ) {
            $oData['AutoCreditDateTo'] = null;
        }
        if( strlen( $oData['ConsignorCode'] )  == 0 ) {
            $oData['ConsignorCode'] = null;
        }
        if( strlen( $oData['RemittingBankCode'] )  == 0 ) {
            $oData['RemittingBankCode'] = null;
        }
        if( strlen( $oData['RemittingBranchCode'] )  == 0 ) {
            $oData['RemittingBranchCode'] = null;
        }
        if( strlen( $oData['AccountClass'] )  == 0 ) {
            $oData['AccountClass'] = null;
        }
        if( strlen( $oData['AccountNumber'] )  == 0 ) {
            $oData['AccountNumber'] = null;
        }

        // 締めパターンの設定
        if (!empty($oData['OemFixedDay2'])) {
            $oData['FixPattern'] = 2;       // 月2回
        } else {
            $oData['FixPattern'] = 1;       // 月1回
        }

        $mdlOemOperator = new TableOemOperator($db);
        $db->getDriver()->getConnection()->beginTransaction();

        // ユーザーIDの取得
        $obj = new TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try {
            //新規フラグ && OEMIDがあれば
            if( ! $oData['isNew'] && isset($oData['OemId']) ) {
                // 編集モード時
                $oData['UpdateId'] = $userId;
                $mdlOem->saveUpdate($oData, $oData['OemId']);

                $operator_Data = $mdlOemOperator->findOperatorByOemId($oData['OemId'])->current();
                $oemId = $oData['OemId'];
            } else {
                // 新規モード時
                $oData['RegistId'] = $userId;
                $oData['UpdateId'] = $userId;
                $newId = $mdlOem->saveNew($oData);

                // オペレータ新規作成
                $operator_Data = array(
                    //OEMID
                    'OemId' => $newId,
                    // ログインID → OEM IDを利用したプレフィックスを付ける
                    'LoginId' => sprintf('%04d%s', $newId, $oData['EntLoginId']),
                    // 氏名
                    'NameKj' => sprintf('%s 管理者', $oData['OemNameKj']),
                    // 氏名かな
                    'NameKn' => sprintf('%sカンリシャ', nvl(trim($oData['OemNameKn']).' ', '')),
                    // 所属
                    'Division' => '',
                    // 有効フラッグを設定
                    'ValidFlg' => 1,
                    // パスワードは常にハッシュ化
                    'Hashed' => 1,
                    // RegistIdは$userId
                    'RegistId' => $userId,
                    // UpdateIdは$userId
                    'UpdateId' => $userId,
                );

                // パスワードは自動設定
                $newPassword = $this->generateNewPassword($operator_Data['LoginId']);
                // パスワードハッシュ適用
                /** @var BaseGeneralUtils */
                $authUtil = Application::getInstance()->getAuthUtility();
                // ハッシュ済みパスワード
                $operator_Data['LoginPasswd'] = $authUtil->generatePasswordHash($operator_Data['LoginId'], $newPassword);

                // パスワード更新日時を設定
                $operator_Data['LastPasswordChanged'] = date('Y-m-d H:i:s');

                $newOemOpId = $mdlOemOperator->saveNew($operator_Data);

                // 表示用に生パスワードを反映しておく
                $operator_Data['GeneratedPassword'] = $newPassword;

                $oemId = $newId;

                // T_User新規登録
                $obj->saveNew(array('UserClass' => 1, 'Seq' => $newOemOpId, 'RegistId' => $userId, 'UpdateId' => $userId,));
            }
            $uData = array("OemId"=>$oemId,"DeleteFlg"=>1,"UpdateId"=>$userId);

            //画像一時テーブル更新
            foreach($image_seq_array as $value){
                $mdl_tmpImage->saveUpdate($uData, $value);
            }
            $db->getDriver()->getConnection()->commit();
        } catch(\Exception $err) {
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        //表示データのマージ
        $oData = array_merge($oData, $operator_Data ? $operator_Data : array());

        // 保存済みデータをエンコード
        $data = base64_encode(serialize($oData));

        $this->view->assign('data', $data);

        return $this->view;
    }

    /**
     * 登録完了画面の表示
     */
    public function completionAction() {
        $data = unserialize(base64_decode($this->getParams()['hash']));
        if(!$data) {
            return $this->_redirect("oem/list");
        }

        $this->view->assign('oid', $data['OemId']);
        $this->view->assign('data', $data);

        return $this->view;
    }

    /**
     * 備考更新アクション
     */
    public function upAction() {
        $oData = $this->params()->fromPost();
        $backTo = $_SERVER['HTTP_REFERER'];
        if( preg_match('/oem\/up/', $backTo) ) {
            // リファラがupActionだったらlistActionへ付け替える
            $backTo = f_path($this->getBaseUrl(), 'oem/list');
        }

        $mdle = new TableOem($this->app->dbAdapter);

        $currentRow = $mdle->find($oData['oid'])->current();
        if($currentRow) {
            // シーケンス指定が正しいので戻り先を再設定
            $backTo = f_path($this->getBaseUrl(), 'oem/detail/oid/'.$currentRow['OemId']);

            // T_Oemのカラムに一致するもののみをキーとした連想配列へ詰めなおす
            $data = array();
            $inputKeys = array_keys($oData);
            foreach($currentRow as $key => $value) {
                if( in_array($key, $inputKeys) ) {
                    $data[$key] = $oData[$key];
                }
            }

            // 入力検証
            $errors = $this->validateForUp($data);

            // count関数対策
            if(!empty($errors)) {
                $_POST['oid'] = $currentRow['OemId'];
                $_POST['prev_errors'] = $errors;
                $_POST['prev_backto'] = $backTo;
                $_POST['prev_input'] = $data;

                return $this->_forward('detail');
            }

            // ユーザーIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
            $data['UpdateId'] = $userId;

            // 更新処理
            $mdle->saveUpdate($data, $currentRow['OemId']);
        }

        $this->redirect()->toUrl($backTo);
    }

    /**
     * OEM一覧のCSVダウンロード
     */
    public function dcsvAction() {
        $sql .= "SELECT OemId ";
        $sql .= ",      ApplicationDate ";
        $sql .= ",      ServiceInDate ";
        $sql .= ",      RegistDate ";
        $sql .= ",      OemNameKj ";
        $sql .= ",      OemNameKn ";
        $sql .= ",      PostalCode ";
        $sql .= ",      PrefectureCode ";
        $sql .= ",      PrefectureName ";
        $sql .= ",      City ";
        $sql .= ",      Town ";
        $sql .= ",      Building ";
        $sql .= ",      RepNameKj ";
        $sql .= ",      RepNameKn ";
        $sql .= ",      Phone ";
        $sql .= ",      Fax ";
        $sql .= ",      MonthlyFee ";
        $sql .= ",      N_MonthlyFee ";
        $sql .= ",      SettlementFeeRateRKF ";
        $sql .= ",      SettlementFeeRateSTD ";
        $sql .= ",      SettlementFeeRateEXP ";
        $sql .= ",      SettlementFeeRateSPC ";
        $sql .= ",      ClaimFeeBS ";
        $sql .= ",      ClaimFeeDK ";
        $sql .= ",      EntMonthlyFeeRKF ";
        $sql .= ",      EntMonthlyFeeSTD ";
        $sql .= ",      EntMonthlyFeeEXP ";
        $sql .= ",      EntMonthlyFeeSPC ";
        $sql .= ",      OpDkInitFeeRate ";
        $sql .= ",      OpDkMonthlyFeeRate ";
        $sql .= ",      OpApiRegOrdMonthlyFeeRate ";
        $sql .= ",      OpApiAllInitFeeRate ";
        $sql .= ",      OpApiAllMonthlyFeeRate ";
        $sql .= ",      Salesman ";
        $sql .= ",      FfName ";
        $sql .= ",      FfCode ";
        $sql .= ",      FfBranchName ";
        $sql .= ",      FfBranchCode ";
        $sql .= ",      FfAccountNumber ";
        $sql .= ",      FfAccountClass ";
        $sql .= ",      FfAccountName ";
        $sql .= ",      TcClass ";
        $sql .= ",      CpNameKj ";
        $sql .= ",      CpNameKn ";
        $sql .= ",      DivisionName ";
        $sql .= ",      MailAddress ";
        $sql .= ",      ContactPhoneNumber ";
        $sql .= ",      ContactFaxNumber ";
        $sql .= ",      Note ";
        $sql .= ",      ValidFlg ";
        $sql .= ",      InvalidatedDate ";
        $sql .= ",      InvalidatedReason ";
        $sql .= ",      KisanbiDelayDays ";
        $sql .= ",      AccessId ";
        $sql .= ",      EntLoginIdPrefix ";
        $sql .= ",      OrderIdPrefix ";
        $sql .= ",      Notice ";
        $sql .= ",      ServiceName ";
        $sql .= ",      ServicePhone ";
        $sql .= ",      SupportTime ";
        $sql .= ",      SupportMail ";
        $sql .= ",      Copyright ";
        $sql .= ",      PayingMethod ";
        $sql .= ",      HelpUrl ";
        $sql .= ",      FixPattern ";
        $sql .= ",      ReclaimAccountPolicy ";
        $sql .= ",      EntAccountEditLimitation ";
        $sql .= ",      EntAccountAdditionalMessage ";
        $sql .= ",      CreditCriterion ";
        $sql .= ",      AutoCreditDateFrom ";
        $sql .= ",      AutoCreditDateTo ";
        $sql .= ",      AutoCreditCriterion ";
        $sql .= ",      OemClaimTransDays ";
        $sql .= ",      OemClaimTransFlg ";
        $sql .= ",      OemFixedPattern ";
        $sql .= ",      OemFixedDay1 ";
        $sql .= ",      OemFixedDay2 ";
        $sql .= ",      OemFixedDay3 ";
        $sql .= ",      OemFixedDay_Week ";
        $sql .= ",      SettlementDay1 ";
        $sql .= ",      SettlementDay2 ";
        $sql .= ",      SettlementDay3 ";
        $sql .= ",      SettlementDay_Week ";
        $sql .= ",      JapanPostPrintFlg ";
        $sql .= ",      MembershipAgreement ";
        $sql .= ",      B_OemFixedDate ";
        $sql .= ",      B_SettlementDate ";
        $sql .= ",      N_OemFixedDate ";
        $sql .= ",      N_SettlementDate ";
        $sql .= ",      RegistId ";
        $sql .= "FROM   T_Oem ";
        $sql .= "ORDER BY ";
        $sql .= "       OemId DESC ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI16142_1'; // OEM事業者一覧
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'oem_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * 管理者パスワードリセットアクション
     */
    public function resetpswAction() {
        $oid = $this->params()->fromRoute('oid', -1);
        try {
            $mdlOemOperator = new TableOemOperator($this->app->dbAdapter);
            $operator = $mdlOemOperator->findOperatorByOemId($oid)->current();
            if($operator) {
                // ランダムパスワードを生成
                $newPassword = $this->generateNewPassword($operator['LoginId']);
                $authUtil = $this->app->getAuthUtility();

                // ユーザーIDの取得
                $obj = new TableUser($this->app->dbAdapter);
                $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                // ハッシュ済みパスワードで更新
                $opData = array(
                    'LoginPasswd' => $authUtil->generatePasswordHash($operator['LoginId'], $newPassword),
                    'Hashed' => 1,
                    'UpdateId' => $userId,
                    'LastPasswordChanged' => date('Y-m-d H:i:s')
                );
                $mdlOemOperator->saveUpdate($opData, $operator['OemOpId']);

                // 生成された生パスワードをセッションに退避
                $_SESSION['oemop_resetpsw_newpsw'] = $newPassword;

                $mdlPasswordHistory = new TablePasswordHistory($this->app->dbAdapter);
                $mdlSystemProperty = new TableSystemProperty($this->app->dbAdapter);

                // パスワード期限切れ日数(日)の取得
                $propValue = $mdlSystemProperty->getValue("[DEFAULT]", "systeminfo", "PasswdLimitDay");

                // パスワード履歴テーブルに１件追加
                $data = array(
                     'Category'       => 4
                    ,'LoginId'        => $operator['LoginId']
                    ,'LoginPasswd'    => $authUtil->generatePasswordHash($operator['LoginId'], $newPassword)
                    ,'PasswdStartDay' => date('Y-m-d')
                    ,'PasswdLimitDay' => date('Y-m-d', strtotime("$propValue day"))
                    ,'Hashed'         => 1
                    ,'RegistId'       => $userId
                    ,'UpdateId'       => $userId
                    ,'ValidFlg'       => 0
                );
                $mdlPasswordHistory->saveNew($data);

                // パスワード履歴テーブルの有効フラグを更新
                $mdlPasswordHistory->validflgUpdate(4, $operator['LoginId'], $userId);

                // 完了画面へリダイレクト
                return $this->_redirect(sprintf('oem/resetpswdone/oid/%d', $operator['OemId']));
            } else {
                throw new \Exception(sprintf('不正なアカウントが指定されました。　OEM ID: %s は無効です', $oid));
            }
        } catch(\Exception $err) {
            // 例外はメッセージ表示を伴ってdetailへforward
            $_POST['oid'] = $oid;
            $_POST['prev_errors'] = array('OemId' => $err->getMessage());

            return $this->_forward('detail');
        }
    }

    /**
     * 事業者パスワードリセット完了アクション
     */
    public function resetpswdoneAction() {
        // セッションデータから生成済み生パスワードを取得
        $sess_key = 'oemop_resetpsw_newpsw';
        $newPassword = $_SESSION[$sess_key];
        unset($_SESSION[$sess_key]);

        $oid = $this->params()->fromRoute('oid', -1);
        $mdlOem = new TableOem($this->app->dbAdapter);
        $mdlOemOperator = new TableOemOperator($this->app->dbAdapter);

        $data = $mdlOem->findOem2($oid)->current();
        if($data) {
            $data['GeneratedPassword'] = $newPassword;
            $oem_operator = $mdlOemOperator->findOperatorByOemId($oid)->current();
            if($oem_operator) {
                $data['LoginId'] = $oem_operator['LoginId'];
            }
        }

        $this->view->assign('oid', $oid);
        $this->view->assign('data', $data);

        // 登録完了画面を流用
        $this->setTemplate('completion');

        return $this->view;
    }


    /**
     * OEMデータ連送配列の利率を実数に補正する
     *
     * @access protected
     * @param array $data OEMデータの連想配列
     * @return array 利率が実数に補正されたOEMデータの連想配列
     */
    protected function fixSettelementFeeRate($data) {
        // 同梱を実数化
        $data['OpDkInitFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpDkInitFeeRate']);
        $data['OpDkMonthlyFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpDkMonthlyFeeRate']);

        // APIを実数化
        $data['OpApiRegOrdMonthlyFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpApiRegOrdMonthlyFeeRate']);
        $data['OpApiAllInitFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpApiAllInitFeeRate']);
        $data['OpApiAllMonthlyFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpApiAllMonthlyFeeRate']);

        return $data;
    }

    /**
     * POSTされた入力フォームに対し、未送信キーを補完する
     *
     * @access protected
     * @param array $data POSTデータ
     * @return array $dataの未送信キーを補完したデータ
     */
    protected function fixInputForm(array $data) {
        $defaults = array(
            'FfAccountClass' => -1,
            'ReclaimAccountPolicy' => 0,
            'EntAccountEditLimitation' => 0,
            'TimemachineNgFlg' => 0,
            'FixedLengthFlg' => 0,
            'DspTaxFlg' => 0,
            'AddTcClass' => 0,
            'RecordClaimPrintedDateFlg' => 0,
            'ChangeIssuerNameFlg' => 0
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
    protected function validate($data = array(), $favicon_info = array()) {
        $isNew = $data['isNew'] ? true : false;

        $app = Application::getInstance();

        //CoralValidateインスタンス生成
        $CoralValidate = new CoralValidate();

        // ApplicationDate: 申込日
        $key = 'ApplicationDate';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'申込日'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isDate($data[$key])) {
            $errors[$key] = "'申込日'の形式が不正です";
        }
        if (!isset($errors[$key]) && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data[$key])) {
            $errors[$key] = "'申込日'の形式が不正です";
        }

        // OemNameKj: OEM先名
        $key = 'OemNameKj';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'OEM先名'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'OEM先名'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'OEM先名'が短すぎます";
        }

        // OemNameKn: OEM先名カナ
        $key = 'OemNameKn';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'OEM先名カナ'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'OEM先名カナ'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'OEM先名カナ'が短すぎます";
        }
        if (!isset($errors[$key]) && !preg_match('/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $data[$key] ) ) ) {
            $errors[$key] = "'OEM先名カナ'にカタカナ以外の文字が含まれています";
        }

        // Salesman: キャッチボール営業担当
        $key = 'Salesman';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'キャッチボール営業担当'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'キャッチボール営業担当'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'キャッチボール営業担当'が短すぎます";
        }

        // PostalCode: 郵便番号
        $key = 'PostalCode';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'郵便番号'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isPostCode($data[$key])) {
            $errors[$key] = "'郵便番号'の形式が不正です";
        }

        // PrefectureCode: 都道府県
        $key = 'PrefectureCode';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'都道府県'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'都道府県'の指定が不正です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->checkBetween($data[$key], 1, 47)) {
            $errors[$key] = "'都道府県'の指定が不正です";
        }

        // City: 市区郡
        $key = 'City';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'市区郡'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 255) {
            $errors[$key] = "'市区郡'は255文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'市区郡'が短すぎます";
        }

        // Town: 町域
        $key = 'Town';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'町域'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 255) {
            $errors[$key] = "'町域'は255文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'町域'が短すぎます";
        }

        // Building: ビル名
        $key = 'Building';
        if (!isset($errors[$key]) && strlen($data[$key]) > 255) {
            $errors[$key] = "'建物'は255文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 0) {
            $errors[$key] = "'建物'が短すぎます";
        }

        // RepNameKj: 代表者氏名
        $key = 'RepNameKj';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'代表者氏名'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'代表者氏名'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'代表者氏名'が短すぎます";
        }

        // RepNameKn: 代表者氏名カナ
        $key = 'RepNameKn';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'代表者氏名カナ'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'代表者氏名カナ'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'代表者氏名カナ'が短すぎます";
        }
        if (!isset($errors[$key]) && !preg_match('/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $data[$key] ) ) ) {
            $errors[$key] = "'代表者氏名カナ'にカタカナ以外の文字が含まれています";
        }

        // Phone: 電話番号
        $key = 'Phone';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'代表電話番号'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isPhone($data[$key])) {
            $errors[$key] = "'代表電話番号'が不正な形式です";
        }

        // Fax: FAX番号
        $key = 'Fax';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isPhone($data[$key])) {
            $errors[$key] = "'代表FAX番号'が不正な形式です";
        }

        // AccessId: アクセス識別ID
        $key = 'AccessId';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'アクセス識別ID'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 3) {
            $errors[$key] = "'アクセス識別ID'は3～50文字で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 50) {
            $errors[$key] = "'アクセス識別ID'は3～50文字で入力してください";
        }
        if (!isset($errors[$key]) && !preg_match('/^[a-zA-Z][a-zA-Z0-9\-]+$/', $data[$key])) {
            $errors[$key] = "'アクセス識別ID'は半角アルファベットで始まり、アルファベット、数字およびハイフン（-）のみで構成されている必要があります";
        }

        // EntLoginId: 管理者ログインID
        $key = 'EntLoginId';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'管理者ログインID'は必須です";
        }
        // 文字列長上限は新規時16文字、変更時（＝DB値そのまま）は20文字
        if (!isset($errors[$key]) && strlen($data[$key]) < 4) {
            $errors[$key] = "'管理者ログインID'は4～16文字で入力してください";
        }
        if($isNew) {
            if (!isset($errors[$key]) && strlen($data[$key]) > 16) {
                $errors[$key] = "'管理者ログインID'は4～16文字で入力してください";
            }
        } else {
            if (!isset($errors[$key]) && strlen($data[$key]) > 20) {
                $errors[$key] = "'管理者ログインID'は4～16文字で入力してください";
            }
        }
        if (!isset($errors[$key]) && !preg_match('/^[a-zA-Z0-9\-_]+$/', $data[$key])) {
            $errors[$key] = "'管理者ログインID'は半角アルファベット、数字、ハイフン（-）、アンダースコア（_）のみで構成されている必要があります";
        }

        // EntLoginIdPrefix: ログインID固有プレフィックス
        $key = 'EntLoginIdPrefix';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'ログインID固有プレフィックス'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) != 2) {
            $errors[$key] = "'ログインID固有プレフィックス'は2文字で入力してください";
        }
        if (!isset($errors[$key]) && !preg_match('/^[A-Z]{2}$/', $data[$key])) {
            $errors[$key] = "'ログインID固有プレフィックス'は半角大文字アルファベットで入力してください";
        }

        // OrderIdPrefix: 注文ID固有プレフィックス
        $key = 'OrderIdPrefix';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'注文ID固有プレフィックス'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) != 2) {
            $errors[$key] = "'注文ID固有プレフィックス'は2文字で入力してください";
        }
        if (!isset($errors[$key]) && !preg_match('/^[A-Z]{2}$/', $data[$key])) {
            $errors[$key] = "'注文ID固有プレフィックス'は半角大文字アルファベットで入力してください";
        }

        // PayingMethod: 立替方法
        $key = 'PayingMethod';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'立替方法'を選択してください";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'立替方法'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] <= -1) {
            $errors[$key] = "'立替方法'の指定が不正です";
        }

        // ServiceName: サービス名
        $key = 'ServiceName';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'サービス名'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'サービス名'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'サービス名'が短すぎます";
        }

        // ServicePhone:サポート電話番号
        $key = 'ServicePhone';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isPhone($data[$key])) {
            $errors[$key] =  "サポート電話番号'が不正な形式です";
        }

        // SupportMail: サポートメールアドレス
        $key = 'SupportMail';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isMail($data[$key])) {
            $errors[$key] =  "'サポートメールアドレス'が不正な形式です";
        }

        // CpNameKj: 担当者名
        $key = 'CpNameKj';
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'担当者名'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'担当者名'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'担当者名'が短すぎます";
        }

        // CpNameKn: 担当者名カナ
        $key = 'CpNameKn';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'担当者名カナ'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'担当者名カナ'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'担当者名カナ'が短すぎます";
        }
        if (!isset($errors[$key]) && !preg_match('/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $data[$key] ) ) ) {
            $errors[$key] = "'担当者名カナ'にカタカナ以外の文字が含まれています";
        }

        // DivisionName: 部署名
        $key = 'DivisionName';
        if (!isset($errors[$key]) && strlen($data[$key]) > 255) {
            $errors[$key] = "'部署名'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 0) {
            $errors[$key] = "'部署名'が短すぎます";
        }

        // MailAddress: メールアドレス
        $key = 'MailAddress';
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'メールアドレス'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isMail($data[$key])) {
            $errors[$key] = "'メールアドレス'が不正な形式です";
        }

        // ContactPhoneNumber: 連絡先電話番号
        $key = 'ContactPhoneNumber';
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'連絡先電話番号'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isPhone($data[$key])) {
            $errors[$key] = "'連絡先電話番号'が不正な形式です";
        }

        // ContactFaxNumber: 連絡先FAX番号

        $key = 'ContactFaxNumber';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isPhone($data[$key])) {
            $errors[$key] = "'連絡先FAX番号'が不正な形式です";
        }

        // EntAccountEditLimitation: 事業者情報編集の制限
        $key = 'EntAccountEditLimitation';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'事業者情報編集の制限'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !($data[$key] == 0 || $data[$key] == 1)) {
            $errors[$key] = "'事業者情報編集の制限'に未定義の値が指定されました";
        }

        // EntAccountAdditionalMessage: 編集画面追加メッセージ
        $key = 'EntAccountAdditionalMessage';
        if (!isset($errors[$key]) && strlen($data[$key]) > 255) {
            $errors[$key] = "'編集画面追加メッセージ'は255文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 0) {
            $errors[$key] = "'銀行名'が短すぎます";
        }

        // ServiceInDate: サービス開始日
        $key = 'ServiceInDate';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'サービス開始日'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isDate($data[$key])) {
            $errors[$key] = "'サービス開始日'が不正な形式です";
        }
        if (!isset($errors[$key]) && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data[$key])) {
            $errors[$key] = "'サービス開始日'の形式が不正です";
        }

        // 標準決済手数料率
        // 料金プランマスタからデータを取得
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $plan = ResultInterfaceToArray($mdlpp->getAll());
        // 料金プランマスタ分ループする
        foreach ($plan as $value) {
            $key = 'SettlementFeeRate' . $value['PricePlanId'];
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
                $errors[$key] = "'標準決済手数料率 - " . $value['PricePlanName'] . "'は必須です";
            }
            if (!isset($errors[$key]) && !$CoralValidate->isFloat($data[$key])) {
                $errors[$key] = "'標準決済手数料率 - " . $value['PricePlanName'] . "'の指定が不正です";
            }
            if (!isset($errors[$key]) && $data[$key] <= -1) {
                $errors[$key] = "'標準決済手数料率 - " . $value['PricePlanName'] . "'の指定が不正です";
            }
        }

        // ClaimFeeBS: 標準請求手数料 - 別送
        $key = 'ClaimFeeBS';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'標準請求手数料 - 別送'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'標準請求手数料 - 別送'の指定が不正です";
        }

        // ClaimFeeDK: 標準請求手数料 - 同梱
        $key = 'ClaimFeeDK';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'標準請求手数料 - 同梱'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'標準請求手数料 - 同梱'の指定が不正です";
        }

            // 標準決済手数料率
        // 料金プランマスタからデータを取得
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $plan = ResultInterfaceToArray($mdlpp->getAll());
        // 料金プランマスタ分ループする
        foreach ($plan as $value) {
            $key = 'EntMonthlyFee' . $value['PricePlanId'];
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
                $errors[$key] = "'標準店舗月額固定費 - " . $value['PricePlanName'] . "'は必須です";
            }
            if (!isset($errors[$key]) && !$CoralValidate->isFloat($data[$key])) {
                $errors[$key] = "'標準店舗月額固定費 - " . $value['PricePlanName'] . "'の指定が不正です";
            }
            if (!isset($errors[$key]) && $data[$key] <= -1) {
                $errors[$key] = "'標準店舗月額固定費 - " . $value['PricePlanName'] . "'の指定が不正です";
            }
        }

        // MonthlyFee: 月額固定費
        $key = 'MonthlyFee';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'月額固定費'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'月額固定費'の指定が不正です";
        }

        // N_MonthlyFee: 次回請求月額固定費
        $key = 'N_MonthlyFee';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'次回請求月額固定費'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'次回請求月額固定費'の指定が不正です";
        }

        // FfName: 銀行名
        $key = 'FfName';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'銀行名'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'銀行名'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'銀行名'が短すぎます";
        }

        // FfCode: 銀行番号
        $key = 'FfCode';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'銀行番号'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'銀行番号'の形式が不正です";
        }

        // FfBranchName: 支店名
        $key = 'FfBranchName';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'支店名'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 160) {
            $errors[$key] = "'支店名'は160文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'支店名'が短すぎます";
        }

        // FfBranchCode: 支店番号
        $key = 'FfBranchCode';
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'支店番号'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'支店番号'の形式が不正です";
        }

        // FfAccountClass: 口座種別
        $key = 'FfAccountClass';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'口座種別'を選択してください";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'口座種別'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] <= 0) {
            $errors[$key] = "'口座種別'の指定が不正です";
        }

        // FfAccountNumber: 口座番号
        $key = 'FfAccountNumber';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'口座番号'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'口座番号'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 80) {
            $errors[$key] = "'口座番号'は80文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'口座番号'が短すぎます";
        }

        // FfAccountName: 口座名義
        $key = 'FfAccountName';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'口座名義'は必須です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 255) {
            $errors[$key] = "'口座名義'は255文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
            $errors[$key] = "'口座名義'が短すぎます";
        }

        // TcClass: 振込手数料
        $key = 'TcClass';
        if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'振込手数料'を指定してください";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'振込手数料'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] <= 0) {
            $errors[$key] = "'振込手数料'の指定が不正です";
        }

        // InvalidatedDate: 無効年月日
        $key = 'InvalidatedDate';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isDate($data[$key])) {
            $errors[$key] = "'無効年月日'の形式が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data[$key])) {
            $errors[$key] = "'無効年月日'の形式が不正です";
        }

        // InvalidatedReason: 無効理由
        $key = 'InvalidatedReason';
        if (!isset($errors[$key]) && strlen($data[$key]) > 400) {
            $errors[$key] = "'無効理由'は4000文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 0) {
            $errors[$key] = "'無効理由'が短すぎます";
        }

        // KisanbiDelayDays: 延滞起算猶予
        $key = 'KisanbiDelayDays';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'延滞起算猶予'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->checkBetween($data[$key], 0, 365)) {
            $errors[$key] = "'延滞起算猶予'の指定が不正です";
        }

        // OemFixedPattern: OEM締パターン
        $key = 'OemFixedPattern';
        if ( $data[$key] == "0" ){

            // OEM締日
            $key = 'OemFixedDay';
            $key1 = 'OemFixedDay1';
            $key2 = 'OemFixedDay2';
            $key3 = 'OemFixedDay3';

            if (!isset($errors[$key]) && strlen($data[$key1]) == 0) {
                $errors[$key] = "'OEM締日1'は必須です";
            }
            if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key1])) {
                $errors[$key] = "'OEM締日1'の指定が不正です";
            }
            if (!isset($errors[$key]) && !$CoralValidate->checkBetween($data[$key1], 1, 31)) {
                $errors[$key] = "'OEM締日1'の指定が不正です";
            }

            // OemFixedDay2: OEM締日2
            if (!isset($errors[$key]) && strlen($data[$key2]) > 0 && !$CoralValidate->isInt($data[$key2])) {
                $errors[$key] = "'OEM締日2'の指定が不正です";
            }
            if (!isset($errors[$key]) && strlen($data[$key2]) > 0 && !$CoralValidate->checkBetween($data[$key2], 1, 31)) {
                $errors[$key] = "'OEM締日2'の指定が不正です";
            }

            // OemFixedDay3: OEM締日3
            if (!isset($errors[$key]) && strlen($data[$key3]) > 0 && !$CoralValidate->isInt($data[$key3])) {
                $errors[$key] = "'OEM締日3'の指定が不正です";
            }
            if (!isset($errors[$key]) && strlen($data[$key3]) > 0 && !$CoralValidate->checkBetween($data[$key3], 1, 31)) {
                $errors[$key] = "'OEM締日3'の指定が不正です";
            }

// 2015/09/11 Y.Suzuki Add 締め日のValidationを追加 Stt
            // OemFixedDay3: OEM締日3
            // 締日2が指定されていない場合にはエラー
            if (!isset($errors[$key]) && strlen($data[$key2]) == 0 && strlen($data[$key3]) > 0) {
                $errors[$key] = "'OEM締日2'が未入力の場合'OEM締日3'は指定できません";
            }
// 2015/09/11 Y.Suzuki Add 締め日のValidationを追加 End

            // 精算予定日
            $key_day = 'SettlementDay_day';
            $key_week = 'SettlementDay_week';
            $key1 = 'SettlementDay1';
            $key2 = 'SettlementDay2';
            $key3 = 'SettlementDay3';
            $key4 = 'SettlementWeek1';
            $key5 = 'SettlementWeek2';
            $key6 = 'SettlementWeek3';
            $radio_key = 'day_week';

            if ( $data[$radio_key] == "day" ) {
                // SettlementDay1: 精算予定日1
                if (!isset($errors[$key_day]) && strlen($data[$key1]) == 0) {
                    $errors[$key_day] = "'精算予定日1'必須です";
                }
                if (!isset($errors[$key_day]) && !$CoralValidate->isInt($data[$key1])) {
                    $errors[$key_day] = "'精算予定日1'の指定が不正です";
                }
                if (!isset($errors[$key_day]) && !$CoralValidate->checkBetween($data[$key1], 1, 31)) {
                    $errors[$key_day] = "'精算予定日1'の指定が不正です";
                }

                // SettlementDay2: 精算予定日2
                if (!isset($errors[$key_day]) && strlen($data[$key2]) > 0 && !$CoralValidate->isInt($data[$key2])) {
                    $errors[$key_day] = "'精算予定日2'の指定が不正です";
                }
                if (!isset($errors[$key_day]) && strlen($data[$key2]) > 0 && !$CoralValidate->checkBetween($data[$key2], 1, 31)) {
                    $errors[$key_day] = "'精算予定日2'の指定が不正です";
                }

                // SettlementDay3: 精算予定日3
                if (!isset($errors[$key_day]) && strlen($data[$key3]) > 0 && !$CoralValidate->isInt($data[$key3])) {
                    $errors[$key_day] = "'精算予定日3'の指定が不正です";
                }
                if (!isset($errors[$key_day]) && strlen($data[$key3]) > 0 && !$CoralValidate->checkBetween($data[$key3], 1, 31)) {
                    $errors[$key_day] = "'精算予定日3'の指定が不正です";
                }

// 2015/09/11 Y.Suzuki Add 精算予定日のValidationを追加 Stt
                // SettlementDay3: 精算予定日3
                // 精算予定日2が指定されていない場合にはエラー
                if (!isset($errors[$key_day]) && strlen($data[$key2]) == 0 && strlen($data[$key3]) > 0) {
                    $errors[$key_day] = "'精算予定日2'が未入力の場合'精算予定日3'は指定できません";
                }
// 2015/09/11 Y.Suzuki Add 精算予定日のValidationを追加 End

                if (!isset($errors['OemFixedDay']) && !isset($errors['SettlementDay'])) {
                    // 本段階で[OEM締日][精算予定日]いずれにもエラーがないと判断されているときのみ検証する
                    if (((strlen($data['OemFixedDay1']) > 0) ? true : false) != ((strlen($data['SettlementDay1']) > 0) ? true : false) ||
                        ((strlen($data['OemFixedDay2']) > 0) ? true : false) != ((strlen($data['SettlementDay2']) > 0) ? true : false) ||
                        ((strlen($data['OemFixedDay3']) > 0) ? true : false) != ((strlen($data['SettlementDay3']) > 0) ? true : false)) {
                        $errors[$key_day] = "'OEM締日'と'精算予定日'の指定日の数は同じでなければなりません";
                    }
                }

            } else {
                // SettlementDay1: 精算予定日1
                if (!isset($errors[$key_week]) && strlen($data[$key4]) == 0) {
                    $errors[$key_week] = "'精算予定日1'必須です";
                }
                if (!isset($errors[$key_week]) && strlen($data[$key5]) == 0 && strlen($data[$key6]) > 0) {
                    $errors[$key_week] = "'精算予定日2'が未入力の場合'精算予定日3'は指定できません";
                }
                if (!isset($errors['OemFixedDay']) && !isset($errors['SettlementDay'])) {
                   // 本段階で[OEM締日][精算予定日]いずれにもエラーがないと判断されているときのみ検証する
                   if (((strlen($data['OemFixedDay1']) > 0) ? true : false) != ((strlen($data['SettlementWeek1']) > 1) ? true : false) ||
                       ((strlen($data['OemFixedDay2']) > 0) ? true : false) != ((strlen($data['SettlementWeek2']) > 1) ? true : false) ||
                       ((strlen($data['OemFixedDay3']) > 0) ? true : false) != ((strlen($data['SettlementWeek3']) > 1) ? true : false)) {
                       $errors[$key_week] = "'OEM締日'と'精算予定日'の指定日の数は同じでなければなりません";
                   }
               }
            }
        } else {
            // OemFixedDay_Week: OEM締日（週締め）
            $key = 'OemFixedDay_Week';
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
                $errors[$key] = "'OEM締日（週締め）'必須です";
            }

            // SettlementDay_Week: 精算予定日（週締め）
            $key = 'SettlementDay_Week';
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
                $errors[$key] = "'精算予定日（週締め）'必須です";
            }

        }

        // Note: 備考
        $key = 'Note';
        if (!isset($errors[$key]) && strlen($data[$key]) > 4000) {
            $errors[$key] = "'備考'は4000文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 0) {
            $errors[$key] = "'備考'が短すぎます";
        }

        // OpDkInitFeeRate: 同梱 - 初期費用率
        $key = 'OpDkInitFeeRate';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isFloat($data[$key])) {
            $errors[$key] = "'同梱 - 初期費用率'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] <= 0.0) {
            $errors[$key] = "'同梱 - 初期費用率'の指定が不正です";
        }

        // OpDkMonthlyFeeRate: 同梱 - 月額固定費率
        $key = 'OpDkMonthlyFeeRate';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isFloat($data[$key])) {
            $errors[$key] = "'同梱 - 月額固定費率'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] <= 0.0) {
            $errors[$key] = "'同梱 - 月額固定費率'の指定が不正です";
        }

        // OpApiRegOrdMonthlyFeeRate: API - 注文登録利用料率
        $key = 'OpApiRegOrdMonthlyFeeRate';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isFloat($data[$key])) {
            $errors[$key] = "'API - 注文登録利用料率'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] <= 0.0) {
            $errors[$key] = "'API - 注文登録利用料率'の指定が不正です";
        }

        // OpApiAllInitFeeRate: API - 全API初期費用率
        $key = 'OpApiRegOrdMonthlyFeeRate';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isFloat($data[$key])) {
            $errors[$key] = "'API - 全API初期費用率'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] <= 0.0) {
            $errors[$key] = "'API - 全API初期費用率'の指定が不正です";
        }

        // OpApiAllMonthlyFeeRate: API - 全API月額固定費率
        $key = 'OpApiAllMonthlyFeeRate';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isFloat($data[$key])) {
            $errors[$key] = "'API - 全API月額固定費率'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] <= 0.0) {
            $errors[$key] = "'API - 全API月額固定費率'の指定が不正です";
        }

        // ReclaimAccountPolicy: 再請求時の名義
        $key = 'ReclaimAccountPolicy';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'再請求時の名義'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !($data[$key] == 0 || $data[$key] == 1)) {
            $errors[$key] = "'再請求時の名義'に未定義の値が指定されました";
        }

        // SameFfTcFeeUnderThirtyK: 立替金振込手数料
        // 立替金振込手数料
        $key = 'SameFfTcFeeUnderThirtyK';
        if(!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'同行振込手数料:30,000円未満'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'同行振込手数料:30,000円未満'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] < 0) {
            $errors[$key] = "'同行振込手数料:30,000円未満'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] > 10000) {
            $errors[$key] = "'同行振込手数料:30,000円未満'の指定が不正です";
        }

        // SameFfTcFeeThirtyKAndOver: 立替金振込手数料
        $key = 'SameFfTcFeeThirtyKAndOver';
        if(!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'同行振込手数料:30,000円以上'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'同行振込手数料:30,000円以上'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] < 0) {
            $errors[$key] = "'同行振込手数料:30,000円以上'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] > 10000) {
            $errors[$key] = "'同行振込手数料:30,000円以上'の指定が不正です";
        }

        // OtherFfTcFeeUnderThirtyK: 立替金振込手数料
        $key = 'OtherFfTcFeeUnderThirtyK';
        if(!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'他行振込手数料:30,000円未満'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'他行振込手数料:30,000円未満'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] < 0) {
            $errors[$key] = "'他行振込手数料:30,000円未満'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] > 10000) {
            $errors[$key] = "'他行振込手数料:30,000円未満'の指定が不正です";
        }

        // OtherFfTcFeeThirtyKAndOver: 立替金振込手数料
        $key = 'OtherFfTcFeeThirtyKAndOver';
        if(!isset($errors[$key]) && strlen($data[$key]) == 0) {
            $errors[$key] = "'他行振込手数料:30,000円以上'は必須です";
        }
        if (!isset($errors[$key]) && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'他行振込手数料:30,000円以上'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] < 0) {
            $errors[$key] = "'他行振込手数料:30,000円以上'の指定が不正です";
        }
        if (!isset($errors[$key]) && $data[$key] > 10000) {
            $errors[$key] = "'他行振込手数料:30,000円以上'の指定が不正です";
        }

        // ConsignorCode: 委託者コード
        $key = 'ConsignorCode';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !preg_match('/^[0-9]+$/', $data[$key])) {
            $errors[$key] = "'委託者コード'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < 1 ) {
            $errors[$key] = "'委託者コード'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] > 9999999999 ) {
            $errors[$key] = "'委託者コード'の指定が不正です";
        }

        // ConsignorName: 委託者名
        $key = 'ConsignorName';
            if (!isset($errors[$key]) && strlen($data[$key]) > 40) {
            $errors[$key] = "'委託者名'は40文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 0) {
            $errors[$key] = "'委託者名'が短すぎます";
        }

        // RemittingBankCode: 仕向金融機関番号
        $key = 'RemittingBankCode';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'仕向金融機関番号'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < 0 ) {
            $errors[$key] = "'仕向金融機関番号'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] > 9999 ) {
            $errors[$key] = "'仕向金融機関番号'の指定が不正です";
        }

        // RemittingBankName: 仕向金融機関名
        $key = 'RemittingBankName';
        if (!isset($errors[$key]) && mb_strlen($data[$key]) > 15 ) {
            $errors[$key] = "'仕向金融機関名'は15文字以内で入力してください";
        }
        if (!isset($errors[$key]) && mb_strlen($data[$key]) < 0 ) {
            $errors[$key] = "'仕向金融機関名'が短すぎます";
        }

        // RemittingBranchCode: 仕向支店番号
        $key = 'RemittingBranchCode';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'仕向支店番号'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < 0 ) {
            $errors[$key] = "'仕向支店番号'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] > 999 ) {
            $errors[$key] = "'仕向支店番号'の指定が不正です";
        }

        // RemittingBranchName: 仕向支店名
        $key = 'RemittingBranchName';
        if (!isset($errors[$key]) && mb_strlen($data[$key]) > 15 ) {
            $errors[$key] = "'仕向支店名'は15文字以内で入力してください";
        }
        if (!isset($errors[$key]) && mb_strlen($data[$key]) < 0 ) {
            $errors[$key] = "'仕向支店名'が短すぎます";
        }

        // AccountClass: 預金種目
        $key = 'AccountClass';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'預金種目'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < 0 ) {
            $errors[$key] = "'預金種目'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] > 9 ) {
            $errors[$key] = "'預金種目'の指定が不正です";
        }

        // AccountNumber: 口座番号
        $key = 'AccountNumber';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'口座番号'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < 0 ) {
            $errors[$key] = "'口座番号'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] > 9999999 ) {
            $errors[$key] = "'口座番号'の指定が不正です";
        }

        // JapanPostPrintFlg: 郵政印刷
        $key = 'JapanPostPrintFlg';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'郵政印刷'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < 0) {
            $errors[$key] = "'郵政印刷'の指定が不正です";
        }

        // CreditCriterion: 与信判定基準
        $key = 'CreditCriterion';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'与信判定基準'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < -1) {
            $errors[$key] = "'与信判定基準'の指定が不正です";
        }

        // 与信自動化有効期間
        $key = 'AutoCreditDate';
        // AutoCreditDateFrom: 与信自動化有効期間From
        $key1 = 'AutoCreditDateFrom';
        if (!isset($errors[$key]) && strlen($data[$key1]) > 0 && !$CoralValidate->isDate($data[$key1])) {
            $errors[$key] = "'与信自動化有効期間の開始日'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key1]) > 0 && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data[$key1])) {
            $errors[$key] = "'与信自動化有効期間の開始日'の形式が不正です";
        }

        // AutoCreditDateTo: 与信自動化有効期間To
        $key2 = 'AutoCreditDateTo';
        if (!isset($errors[$key]) && strlen($data[$key2]) > 0 && !$CoralValidate->isDate($data[$key2])) {
            $errors[$key] = "'与信自動化有効期間の終了日'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key2]) > 0 && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data[$key2])) {
            $errors[$key] = "'与信自動化有効期間の終了日'の形式が不正です";
        }

        // AutoCreditDateFrom＋To: 与信自動化有効期間範囲指定
        if (!isset($errors[$key]) && ((strlen ($data[$key1]) > 0 && strlen ($data[$key2]) == 0) || (strlen ($data[$key1]) == 0 && strlen ($data[$key2]) > 0))) {
            $errors[$key] = "'与信自動化有効期間'はFROM、TO両方を入力して下さい";
        }
        if (!isset($errors[$key]) && strlen ($data[$key1]) > 0 && strlen ($data[$key2]) > 0 && strtotime($data[$key1]) > strtotime($data[$key2])) {
            $errors[$key] = "'与信自動化有効期間'の範囲指定が不正です";
        }

        // AutoCreditCriterion: 自動化用与信判定基準
        $key = 'AutoCreditCriterion';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'自動化用与信判定基準'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < -1) {
            $errors[$key] = "'自動化用与信判定基準'の指定が不正です";
        }

        // OemClaimTransFlg: 債権移行
        $key = 'OemClaimTransFlg';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'債権移行'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && $data[$key] < 0) {
            $errors[$key] = "'債権移行'の指定が不正です";
        }

        // OemClaimTransDays: 債権移行基準日数
        $key = 'OemClaimTransDays';
        if (!isset($errors[$key]) && strlen($data[$key]) > 0 && !$CoralValidate->isInt($data[$key])) {
            $errors[$key] = "'債権移行基準日数'の指定が不正です";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) > 10) {
            $errors[$key] = "'債権移行基準日数'は10文字以内で入力してください";
        }

        $key = 'FavIconUrl';
        if(isset($favicon_info['select']) && $favicon_info['select'] == 'url') {
            // faviconを外部URL参照に指定した場合はURLとして検証する
            if (!isset($errors[$key]) && strlen($data[$key]) == 0) {
                $errors[$key] = "'favicon'に外部URLを使用する場合、URLの登録は必須です";
            }
            if (!isset($errors[$key]) && strlen($data[$key]) > 8192) {
                $errors[$key] = "'favicon'のURLは8192文字以内で入力してください";
            }
            if (!isset($errors[$key]) && strlen($data[$key]) < 1) {
                $errors[$key] = "'favicon'のURLは1文字以上で入力してください";
            }
            if (!isset($errors[$key]) && !preg_match('/^(https?:\/\/)?([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $data[$key])) {
                $errors[$key] = "'favicon'のURLに不適切な文字が含まれています";
            }
        }

        // AccessIdにエラーがなければ重複チェックを行う
        if(!isset($errors['AccessId'])) {
            $oemTable = new TableOem($this->app->dbAdapter);
            if($oemTable->countAccessId($data['AccessId'], $data['OemId']) > 0) {
                $errors['AccessId'] = "この'アクセス識別ID'はすでに使用されています";
            }
        }

        // EntLoginIdPrefixにエラーがなければ重複チェックを行う
        if(!isset($errors['EntLoginIdPrefix'])) {
            $oemTable = new TableOem($this->app->dbAdapter);
            $isAT = strcasecmp($data['EntLoginIdPrefix'], 'AT') == 0;
            if($isAT || $oemTable->countEntLoginIdPrefix($data['EntLoginIdPrefix'], $data['OemId']) > 0) {
                $errors['EntLoginIdPrefix'] = "この'ログインID固有プレフィックス'はすでに使用されています";
            }
        }
        // OrderIdPrefixにエラーがなければ重複チェックを行う
        if(!isset($errors['OrderIdPrefix'])) {
            $oemTable = new TableOem($this->app->dbAdapter);
            $isAK = strcasecmp($data['OrderIdPrefix'], 'AK') == 0;
            if($isAK || $oemTable->countOrderIdPrefix($data['OrderIdPrefix'], $data['OemId']) > 0) {
                $errors['OrderIdPrefix'] = "この'注文ID固有プレフィックス'はすでに使用されています";
            }
        }
        // EntLoginIdPrefix、OrderIdPrefixにエラーがなければ、これらの差異をチェックする
        if(!isset($errors['EntLoginIdPrefix']) && !isset($errors['OrderIdPrefix'])) {
            if($data['EntLoginIdPrefix'] == $data['OrderIdPrefix']) {
                $errors['EntLoginIdPrefix'] = "'ログインID固有プレフィックス'と'注文ID固有プレフィックス'を同じにすることはできません";
                $errors['OrderIdPrefix'] = "'ログインID固有プレフィックス'と'注文ID固有プレフィックス'を同じにすることはできません";
            }
        }

        //ヘルプのリンク先が設定されていれば
        if(!empty($data['HelpUrl'])){
            //ヘルプのリンク先入力チェック
            if (!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $data['HelpUrl'])) {
                $errors['HelpUrl'] = "ヘルプのリンク先のURLが不正です";
            }
        }

        // OemNameKjにエラーがなければ重複チェックを行う
        if( !isset( $errors['OemNameKj'] ) ) {
            $oemTable = new TableOem($this->app->dbAdapter);
            $oemList = $oemTable->getAllOem();
            foreach( $oemList as $oem ) {
                if( $oem['OemNameKj'] == $data['OemNameKj'] && $oem['OemId'] != $data['OemId'] ) {
                    $errors['OemNameKj'] = "既に他のOEMで登録済のOEM名です";
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * 詳細画面更新用入力検証処理
     *
     * @access protected
     * @param array $data
     * @return array
     */
    protected function validateForUp($data = array()) {
        $app = Application::getInstance();

        $errors = array();
        $key = 'Note';
        if (!isset($errors[$key]) && strlen($data[$key]) > 4000) {
            $errors[$key] = "'備考'は4000文字以内で入力してください";
        }
        if (!isset($errors[$key]) && strlen($data[$key]) < 0) {
            $errors[$key] = "'備考'が短すぎます";
        }

        return $errors;
    }

    /**
     * 新しいランダムパスワードを生成する
     *
     * @access protected
     * @param null | string $loginId ログインID
     * @return string
     */
    protected function generateNewPassword($loginId = null)
    {
        $validator = LogicAccountValidityPasswordValidator::getDefaultValidator();
        $i = 0;
        while (true) {
            Application::getInstance()->logger->debug(sprintf('[OemController::generateNewPassword] generating new password for %s (total %d times)', $loginId, ++$i));
            $newPassword = BaseGeneralUtils::MakePassword(8);          // パスワードをランダム設定
            if ($validator->isValid($newPassword, $loginId))
            {
                return $newPassword;
            }
        }
    }
}