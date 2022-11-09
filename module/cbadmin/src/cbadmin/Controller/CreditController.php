<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use Coral\Coral\Validate\CoralValidatePhone;
use Coral\Base\Reflection\BaseReflectionUtility;
use models\Table\TableCreditPoint;
//use models\Table\TableCreditCondition;
use \models\Logic\LogicNormalizer;
use models\Logic\Validation\LogicValidationResult;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\IsNull;
use models\Table\TableCreditJudgeThreshold;
use models\Table\TableUser;
use models\Table\TableCode;
use models\Logic\LogicTemplate;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use Zend\Json\Json;
use models\Table\TableCreditSystemInfo;
use models\Table\TableEnterprise;
use Zend\Db\Adapter\Adapter;
use models\Table\TableAddCreditCondition;

class CreditController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    const SES_UPDATE = "creditpointupdated";
    const SES_CRECATE = "creditconditioncategory";
    const SES_UPLOADCRITERION = "creditcriterionupload";
    const SES_UPLOADCRITERIONERR = "creditcriterionuploaderr";
    const SES_UPLOADCONDITION = "creditconditionupload";
    const SES_UPLOADCONDITIONERR = "creditconditionuploaderr";
    const SES_UPLOADCONDITIONUPD = "creditconditionuploadupdate";

    const CREDITCRITERIONID = 0;
    const CRITERIONCODEID = 91;

	protected $_primary = array('Seq');

    /**
     * Adapter
     *
     * @var Adapter
     */
    private $db;


    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')
        ->addStyleSheet('../css/cbadmin/credit/result/default.css')
        ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 社内与信情報管理");
    }

    /**
     * ポイント設定フォームの表示
     */
    public function pointformAction()
    {
        if (isset($_SESSION[self::SES_UPDATE]))
        {
            unset($_SESSION[self::SES_UPDATE]);
            $this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        // 社内与信ポイント
        $mdlPoint = new TableCreditPoint($this->app->dbAdapter);
        $this->view->assign("list_dep1", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 1, true));
        $this->view->assign("list_dep2", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 2, true));
//        $this->view->assign("list_dep3", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 3, true));
//        $this->view->assign("list_dep4", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 4, true));
        $this->view->assign("list_dep5", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 5, true));
//        $this->view->assign("list_dep6", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 6, true));
        $this->view->assign("list_dep7", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 7, true));
        $this->view->assign("list_dep8", $mdlPoint->getAllByCriterionidDependence(self::CREDITCRITERIONID, 8, true));

        // 審査システム内ポイントはJSON形式から文字列化
        $list6 = $mdlPoint->findCreditPoint(self::CREDITCRITERIONID, 501)->current();
        $list6Json = Json::decode( (!empty($list6['Description']) ? $list6['Description'] : '[]'), Json::TYPE_ARRAY);
        unset($sinsa);
        foreach ($list6Json as $row) {
            if (isset($sinsa)) $sinsa .= ',';
            $sinsa .= $row['Key'] . ',' . sprintf("%.2f", $row['Value']);
        }
        $this->view->assign("list_dep6", $sinsa);

        // 与信閾値
        $mdlThreshold = new TableCreditJudgeThreshold($this->app->dbAdapter);
        $thre = $mdlThreshold->getByCriterionid(self::CREDITCRITERIONID)->current();
        $this->view->assign("CJThreshold", $thre);

        // 社内与信システム情報
        $mdlCreSystemInfo = new TableCreditSystemInfo($this->app->dbAdapter);
        $rowSystemInfo = $mdlCreSystemInfo->find()->current();
        $this->view->assign("CreditSystemInfo", $rowSystemInfo);

        // 審査システム回答条件
        $this->view->assign("JudgeList", explode(',', $thre['JintecManualJudgeSns']));

        return $this->view;
    }

    /**
     * ポイント設定更新
     */
    public function pointupAction()
    {
        $mdlPoint = new TableCreditPoint($this->app->dbAdapter);
        $mdlCode = new TableCode($this->app->dbAdapter);
        $mdlThreshold = new TableCreditJudgeThreshold($this->app->dbAdapter);
        $mdlCreSystemInfo = new TableCreditSystemInfo($this->app->dbAdapter);
        $datas = $this->getParams();

        // エラーチェック、画面情報保持のため、データ収集
        $formData = $this->getPointformData($datas);

        // エラーチェック
        $errorMessage = $this->checkPointformData($formData);
        if(isset($errorMessage))
        {
            // エラーの場合、終了
            $this->view->assign("errors", $errorMessage);

            // 画面情報設定
            $this->view->assign("list_dep1", $formData['list1']);
            $this->view->assign("list_dep2", $formData['list2']);
//            $this->view->assign("list_dep3", $formData['list3']);
//            $this->view->assign("list_dep4", $formData['list4']);
            $this->view->assign("list_dep5", $formData['list5']);
//            $this->view->assign("list_dep6", $formData['list6']);
            $this->view->assign("list_dep7", $formData['list7']);
            $this->view->assign("list_dep8", $formData['list8']);
            $this->view->assign("CJThreshold", $formData['CJThreshold']);

            $this->view->assign("list_dep6", $formData['list6Str']);

            $this->view->assign("CreditSystemInfo", $formData['CreditSystemInfo']);
            $this->view->assign("JudgeList", explode(',', $formData['CreditSystemInfo']['JintecManualJudgeSns']));

            $this->setTemplate('pointform');

            return $this->view;
        }

        // トランザクションの開始
        $db = $this->app->dbAdapter;

        try {
            $db->getDriver()->getConnection()->beginTransaction();

            // ユーザID
            $user = new TableUser($this->app->dbAdapter);
            $userId = $user->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $i = 1;        // CpIdはCpId=1から増分1を保証する。

            while(isset($datas["CpId" . $i]))
            {
                unset($udata);

                // CpIdを取得　他の項目は$iでなくこちらを使用
                $cpId = $datas["CpId" . $i];

                switch((int)$datas["Dependence" . $cpId])
                {
                    case 1:
                        $udata["Point"] = mb_convert_kana($datas["Point" . $cpId], "n", "UTF-8");
                        $udata["Message"] = $datas["Message" . $cpId];
                        $udata["UpdateId"] = $userId;
                        break;
                    case 2:
                        $udata["Point"] = mb_convert_kana($datas["Point" . $cpId], "n", "UTF-8");
                        $udata["GeneralProp"] = mb_convert_kana($datas["GeneralProp" . $cpId], "n", "UTF-8");
                        $udata["Message"] = $datas["Message" . $cpId];
                        $udata["UpdateId"] = $userId;
                        break;
//                    case 3:
//                        $udata["Point"] = mb_convert_kana($datas["Point" . $cpId], "n", "UTF-8");
//                        $udata["Message"] = $datas["Message" . $cpId];
//                        break;
//                    case 4:
//                        $udata["GeneralProp"] = mb_convert_kana($datas["GeneralProp" . $cpId], "n", "UTF-8");
//                        $udata["Message"] = $datas["Message" . $cpId];
//                        break;
                    case 5:
                        $udata["Rate"] = mb_convert_kana($datas["Rate" . $cpId], "n", "UTF-8");
                        $udata["UpdateId"] = $userId;
                        break;
//                    case 6:
//                        break;
                    case 7:
                        $udata["Point"] = mb_convert_kana($datas["Point" . $cpId], "n", "UTF-8");
                        $udata["Message"] = $datas["Message" . $cpId];
                        $udata["UpdateId"] = $userId;
                        break;
                    case 8:
                        $udata["Point"] = mb_convert_kana($datas["Point" . $cpId], "n", "UTF-8");
                        $udata["UpdateId"] = $userId;
                        break;
                    default:
                        break;
                }

                $mdlPoint->saveUpdate($udata, self::CREDITCRITERIONID, $cpId);
                $i++;
            }

            // 審査システム内重みづけ
            $list6 = $formData['list6'];
            // JSON形式で登録
            $list6Json = json::encode($list6);
            unset($udata);
            $udata["Description"] = $list6Json;
            $udata["UpdateId"] = $userId;
            $mdlPoint->saveUpdate($udata, self::CREDITCRITERIONID, 501);

            // 与信閾値
            $thre = $formData['CJThreshold'];
            if (!is_null($thre['Seq'])) {
                $thre['JintecManualJudgeSns'] = $formData['CreditSystemInfo']['JintecManualJudgeSns'];  // JintecManualJudgeSnsは、[M_CreditSystemInfo]⇒[T_CreditJudgeThreshold]のﾌｨｰﾙﾄﾞ化
                $thre['UpdateId'] = $userId;
                $mdlThreshold->saveUpdate($thre, $thre['Seq']);
            }

            $systemInfo = $formData['CreditSystemInfo'];
            unset($systemInfo['JintecManualJudgeSns']);                                                 // JintecManualJudgeSnsは、[M_CreditSystemInfo]⇒[T_CreditJudgeThreshold]のﾌｨｰﾙﾄﾞ化
            // チェックボックスは通知されないので"0"で置き換え
            $systemInfo['AutoCreditLimitAmount1'] = isset($systemInfo['AutoCreditLimitAmount1']) ? $systemInfo['AutoCreditLimitAmount1'] : 0;
            $systemInfo['AutoCreditLimitAmount2'] = isset($systemInfo['AutoCreditLimitAmount2']) ? $systemInfo['AutoCreditLimitAmount2'] : 0;
            $systemInfo['AutoCreditLimitAmount3'] = isset($systemInfo['AutoCreditLimitAmount3']) ? $systemInfo['AutoCreditLimitAmount3'] : 0;
            $systemInfo['AutoCreditLimitAmount4'] = isset($systemInfo['AutoCreditLimitAmount4']) ? $systemInfo['AutoCreditLimitAmount4'] : 0;

            $mdlCreSystemInfo->saveUpdate($systemInfo);

            // コミット
            $db->getDriver()->getConnection()->commit();

            $_SESSION[self::SES_UPDATE] = "u";
            return $this->_redirect('credit/pointform');
        }
        catch (\Exception $err) {
            // ロールバック
            $db->getDriver()->getConnection()->rollBack();

            // エラーの場合、終了
            $errorMessage[] = $err->getMessage();
            $this->view->assign("errors", $errorMessage);

            // 画面情報設定
            $this->view->assign("list_dep1", $formData['list1']);
            $this->view->assign("list_dep2", $formData['list2']);
//            $this->view->assign("list_dep3", $formData['list3']);
//            $this->view->assign("list_dep4", $formData['list4']);
            $this->view->assign("list_dep5", $formData['list5']);
//            $this->view->assign("list_dep6", $formData['list6']);
            $this->view->assign("list_dep7", $formData['list7']);
            $this->view->assign("list_dep8", $formData['list8']);
            $this->view->assign("CJThreshold", $formData['CJThreshold']);

            $this->view->assign("list_dep6", $formData['list6Str']);
            $this->view->assign("CreditSystemInfo", $formData['CreditSystemInfo']);
            $this->view->assign("JudgeList", explode(',', $formData['CreditSystemInfo']['JintecManualJudgeSns']));

            $this->setTemplate('pointform');

            return $this->view;
        }
    }

    /**
     * 社内与信条件設定フォームの表示
     */
    public function conditionAction()
    {
        $category = $this->params()->fromRoute('cate', 0);
        if ($category == 0)
        {
            return $this->_forward('pointform');
        }
        else if ($category == 1)
        {
            return $this->_forward('conditioncate1');
        }

        $msg = $this->params()->fromRoute('msg');
        if ($msg == "u")
        {
            $this->view->assign("message", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        $_SESSION[self::SES_CRECATE] = $category;

        $codeMaster = new CoralCodeMaster();
        $categoryName = $codeMaster->getCreditCategoryCaption($category);

        $list = $this->findCreditCondition(array('OrderSeq' => -1, 'Category' => $category));

        $this->view->assign('categoryName', $categoryName);
        $this->view->assign('list', $list);

        return $this->view;
    }

    /**
     * 社内与信条件設定フォームの表示
     */
    public function conditioncate1Action()
    {
        $category = 1;

        $msg = $this->params()->fromRoute('msg');
        if ($msg == "u")
        {
            $this->view->assign("message", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        $_SESSION[self::SES_CRECATE] = $category;

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $categoryName = $codeMaster->getCreditCategoryCaption($category);

        $list = $this->findCreditCondition(array('OrderSeq' => -1, 'Category' => $category));

        $this->view->assign('categoryName', $categoryName);
        $this->view->assign('list', $list);

        return $this->view;
    }

    /**
     * 与信条件検索アクション
     */
    public function newAction() {
        $expressions = null;
        $page = 1;

        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        // シリアライズされた入力データを取得
        $hash_data = $this->params()->fromPost('hash');
        if($hash_data) {
            // シリアライズされた内容を復元
            $expressions = unserialize(base64_decode($hash_data));
        }

        // カテゴリを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $categoryName = array();
        foreach ($codeMaster->getMasterCodes(3) as $key => $value) {
            $category = array();
            $category['Code'] = $key;
            $category['Caption'] = $value;
            array_push($categoryName, $category);
        }

        // 検索方法を取得
        $searchPattern = array();
        foreach ($codeMaster->getMasterCodes(192) as $key => $value) {
            $pattern = array();
            $pattern['Code'] = $key;
            $pattern['Caption'] = $value;
            array_push($searchPattern, $pattern);
        }

        // 有効無効マスタを取得
        $validFlgName = $codeMaster->getCreditConditionValidFlgMaster();

        if(! $expressions) $expressions = array();

        $this->view->assign('expressions', $expressions);
        $this->view->assign('categoryName', $categoryName);
        $this->view->assign('validFlgName', $validFlgName);
        $this->view->assign('EnterpriseId', $enterpriseId);
        $this->view->assign('searchPattern', $searchPattern);

        return $this->view;
    }

    /**
     * 与信条件登録内容確認アクション
     */
    public function confirmAction() {
        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        $input = $this->params()->fromPost('expressions', array());
        $this->view->assign('data', $input);

// コメントアウト  By Yanase 20150227 検証処理仕様が未確定のため
//          // 検証処理
//          $validation_results = $mdl->validate($input);
// コメントアウト  By Yanase 20150227 検証処理仕様が未確定のため

// 暫定追加（仕様確定時に削除） By Yanase 20150227
        $validation_results = new LogicValidationResult();
// 暫定追加（仕様確定時に削除） By Yanase 20150227

         // 重複チェックを実行
         $validation_results = $this->checkDuplicate($input, $enterpriseId, $validation_results);

        // カテゴリを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

         if(! $validation_results->isValid()) {
             $categoryName = array();
             foreach ($codeMaster->getMasterCodes(3) as $key => $value) {
                 $category = array();
                 $category['Code'] = $key;
                 $category['Caption'] = $value;
                 array_push($categoryName, $category);
             }

             // 検索方法を取得
             $searchPattern = array();
             foreach ($codeMaster->getMasterCodes(192) as $key => $value) {
                 $pattern = array();
                 $pattern['Code'] = $key;
                 $pattern['Caption'] = $value;
                 array_push($searchPattern, $pattern);
             }


             // 検証エラーは編集画面へ差し戻し
             $this->view->assign('validationResults', $validation_results);
             $this->view->assign('categoryName', $categoryName);
             $this->view->assign('expressions', $input);
             $this->view->assign('EnterpriseId', $enterpriseId);
             $this->view->assign('searchPattern', $searchPattern);
             $this->setTemplate('new');

             return $this->view;
         }
         // 20180404 Add
         // validate処理に引数が増えたので対応。こちらでは使用しない。
         $seqData = array();
         $errors = $this->validate($input, $seqData);
         $this->view->assign('error_messages', $errors);
         // count関数対策
         if( !empty($errors)) {
            $categoryName = array();
            foreach ($codeMaster->getMasterCodes(3) as $key => $value) {
                $category = array();
                $category['Code'] = $key;
                $category['Caption'] = $value;
                array_push($categoryName, $category);
            }

            // 検索方法を取得
            $searchPattern = array();
            foreach ($codeMaster->getMasterCodes(192) as $key => $value) {
                $pattern = array();
                $pattern['Code'] = $key;
                $pattern['Caption'] = $value;
                array_push($searchPattern, $pattern);
            }

            // 検証エラーは編集画面へ差し戻し
            $this->view->assign('validationResults', $validation_results);
            $this->view->assign('categoryName', $categoryName);
            $this->view->assign('expressions', $input);
            $this->view->assign('EnterpriseId', $enterpriseId);
            $this->view->assign('searchPattern', $searchPattern);
            $this->setTemplate('new');

            return $this->view;
         }

        // 入力をシリアライズしてビューへ割り当てる
        $this->view->assign('hashedData', base64_encode(serialize($input)));
        $this->view->assign('category', $codeMaster->getCreditCategoryCaption($input['Category']));
        $this->view->assign('pattern', $codeMaster->getCreditSearchPatternCaption($input['SearchPattern']));

        $this->view->assign('EnterpriseId', $enterpriseId);

        return $this->view;
    }

    /**
     * 与信条件保存アクション
     */
    public function saveAction() {
        // シリアライズされた入力データを取得
        $hash_data = $this->params()->fromPost('hash');
        if(! $hash_data) throw new \Exception('data lost !!');

        $enterpriseId = $this->params()->fromPost('EnterpriseId');

        // シリアライズされた内容を復元
        $input = unserialize(base64_decode($hash_data));
        if(! $input) {
            //
            throw new \Exception('data lost !!');
        }

        $validation_results = new LogicValidationResult();

        // エラーがあったら編集画面へ差し戻し
        if(! $validation_results->isValid()) {
            $this->view->assign('validationResults', $validation_results);
            $this->view->assign('expressions', $input);
            $this->view->assign('EnterpriseId', $enterpriseId);
            $this->setTemplate('new');

            return $this->view;
        }

        // 新規追加用の補完処理を適用
        $input = $this->fixDataArrayForNew($input);

        // 正規化を適用
        $input = $this->fixDataArray($input);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $input['RegistId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
        $input['UpdateId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $input['EnterpriseId'] = $enterpriseId;

        // 永続化実行
        $savedRow = $this->saveFromArray($input);

        // 完了画面へリダイレクト
        if ($enterpriseId == null ) {
            return $this->_redirect('credit/complete');
        } else {
            return $this->_redirect('credit/complete/eid/' . $enterpriseId);
        }
    }

    /**
     * 与信条件登録完了アクション
     */
    public function completeAction() {
        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        $this->view->assign('EnterpriseId', $enterpriseId);
        //
        return $this->view;
    }

    /**
     * 与信条件検索アクション
     */
    public function searchAction() {
        $expressions = null;
        $page = 1;

        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        // シリアライズされた入力データを取得
        $hash_data = $this->params()->fromPost('hash');

        if($hash_data) {
            // シリアライズされた内容を復元
            $expressions = unserialize(base64_decode($hash_data));
        }

        // カテゴリを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $categoryName = array();
        foreach ($codeMaster->getCreditCategoryMaster() as $key => $value) {
            $category = array();
            $category['Code'] = $key;
            $category['Caption'] = $value;
            array_push($categoryName, $category);
        }

        // 有効無効マスタを取得
        $validFlgName = $codeMaster->getCreditConditionValidFlgMaster();

        if(! $expressions) $expressions = array();

        $this->view->assign('expressions', $expressions);
        $this->view->assign('categoryName', $categoryName);
        $this->view->assign('validFlgName', $validFlgName);
        $this->view->assign('EnterpriseId', $enterpriseId);
        return $this->view;
    }

    /**
     * 検索結果表示アクション
     */
    public function resultAction() {
        $this->addJavaScript('../js/corelib.js')
            ->addJavaScript('../js/bytefx.js');

        $this
            ->addStyleSheet('../css/base.ui.customlist.css')
            ->addJavaScript('../js/base.ui.js')
            ->addJavaScript('../js/base.ui.customlist.js');

        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        $expressions = null;
        $pase = 1;
        $params = $this->getParams();
        if (!isset($params['page'])) {
            // 1. [$params] 内に、キー[page]が存在しないとき
            // a. 検索画面からの検索ボタン押下イベントと判定
            // b. 検索条件 を [$_SESSION['SESS_SEARCHO']]へアサインする（一度セッション情報を破棄後、設定しゴミを含ませないようにする）
            $expressions = $this->params()->fromPost('expressions', array());
            unset($_SESSION['SESS_CREDITSEARCH']);
            $_SESSION['SESS_CREDITSEARCH'] = $expressions;
        }
        else {
            // 2. [$params] 内に、キー[page]が存在するとき
            // a. ページング[前][後]ボタン押下イベントと判定
            // b. [$param['page']] 値をページ番号とする
            // c. 検索条件を $_SESSION['SESS_SEARCHO'] とする
            $page = $params['page'];
            $expressions = $_SESSION['SESS_CREDITSEARCH'];
        }

        // 検索を実行してリスト表示を初期化
        $this->execSearch($expressions, $page, $enterpriseId);
        $this->view->assign('EnterpriseId', $enterpriseId);

        // CSVダウンロードURL
        $csv = null;
        $csv = $expressions;

        unset($csv['controller']);
        unset($csv['action']);
        unset($csv['module']);
        $csv['EnterpriseId'] = $enterpriseId;
        $this->view->assign('durl', 'credit/download?' . http_build_query($csv));

        return $this->view;
    }

     /**
     * 与信条件変更保存アクション
     */
    public function savemodifyAction() {

        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        $app = Application::getInstance();
        $req = $this->params();

        // 検索条件を復元
        $nav_info = $req->fromPost('nav', array('hash' => base64_encode(serialize(array())), 'page' => 1, 'total_count' => 0));

        // トランザクション開始
        $db = $this->app->dbAdapter;
        $db->getDriver()->getConnection()->beginTransaction();

        // 検証エラーメッセージのリスト
        $errors = array();
        // 20180404 add エラー行のSeqリスト
        $seqData = array();
        // 検証エラー行データのリスト
        $input = array();


        try {

            foreach($req->fromPost('form', array()) as $data) {
                // 20180404 mod 引数追加
                $errors[] = $this->validate($data, $seqData);
            }

            $errors = array_filter($errors);

            $error = array();
            foreach($errors as $data => $val1) {
                foreach ($val1 as $key2 => $val2){
                    $error[] = $val2;
                }
            }
            foreach($req->fromPost('form', array()) as $data) {
                $validation_results = new LogicValidationResult();

                $validation_results = $this->checkDuplicate($data, $enterpriseId, $validation_results);
                if(! $validation_results->isValid()) {
                    break;
                }
            }
            // 検証実行 追加 By Isobe 20150401
            // count関数対策
            if( !empty($errors) || (! $validation_results->isValid())) {

                //条件文字列・ポイント未入力データ取得
                $i=0;
                foreach($req->fromPost('form', array()) as $data) {
                    $data['ValidFlg'] = $data['delete'];
                    $data['JintecManualReqFlg'] = $data['jintec'];
                    $data['AddConditionCount'] = $data['addcount'];
                    $validation_results = new LogicValidationResult();
                    $validation_results = $this->checkDuplicate($data, $enterpriseId, $validation_results);

                    if(! $validation_results->isValid()) {
                        $input[]=$data;
                        $valid_result = $validation_results;
                    }
                    // 20180404 mod 未入力以外のエラーも発生したため
                    if(in_array($data['Seq'], $seqData)) {
                    //if($data['Cstring']=="" || $data['Point']==""){
                        $input[]=$data;
                    }
                }

                 $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf[$cn] : 100;
                 if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 100;
                 // count関数対策
                 $inputCount = 0;
                 if (!empty($input)){
                    $inputCount = count($input);
                }
                 $pager = new CoralPager( $inputCount, $ipp );

                 // [paging] 指定ページを取得
                 $current_page = $nav_info['page'];
                 if( $current_page < 1 ) $current_page = 1;
                 $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
                 $categoryName = $codeMaster->getCreditCategoryMasterList();
                 $patternName = $codeMaster->getCreditSearchPatternMasterList();

                // 検索条件関連のみビューに割り当てる
                $this->view->assign('validationResults', $valid_result);
                $this->view->assign( 'error_messages', $error);
                $this->view->assign( 'pager', $pager );
                $this->view->assign('hashedData', $nav_info['hash']);
                $this->view->assign( 'current_page', $current_page );
                $this->view->assign('result', $input);
                $this->view->assign('categoryName', $categoryName);
                $this->view->assign('patternName', $patternName);

                $this->view->assign('EnterpriseId', $enterpriseId);

                // result.phtmlへ戻る
                $this->setTemplate('result');
                $db->getDriver()->getConnection()->rollBack();
                return $this->view;
            }

            $datas = array();
            // 画面上での重複チェックのため、postされたデータをループ＆要らない要素削除処理。
            foreach($req->fromPost('form', array()) as $data) {
                unset($data['Seq']);
                unset($data['_row_hash']);
                unset($data['Category']);
                $datas[]=$data;
            }

            $errseq = array();
            $array=$req->fromPost('form', array());
            //画面上重複チェックループ処理
            foreach($array as $dataa ){
                $dataas = $dataa;
                unset($dataas['Seq']);
                unset($dataas['_row_hash']);
                unset($dataas['Category']);

                $con = 0;
                foreach($datas as $datab ){
                    if($dataas == $datab){
                        $con++;
                    }
                }
                //重複するデータが存在したら
                if($con > 1){
                    $err[] =$dataa;
                }
            }

            //画面上で重複するデータが存在したらエラーメッセージ表示
            $errors=array();
            if(!empty($err)){
                foreach ($err as $error){
                    $errors[] = "Seq " . $error['Seq'] . " ：重複する与信条件がすでに登録されています。条件文字列、コメント、ポイントのいずれかを変更してください。";
                }

                foreach($array as $data) {
                    $array[$data['Seq']]['ValidFlg'] = $data['delete'];
                    $array[$data['Seq']]['JintecManualReqFlg'] = $data['jintec'];
                    $array[$data['Seq']]['AddConditionCount'] = $data['addcount'];
                }

                $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf[$cn] : 100;
                if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 100;
                // count関数対策
                $arrayCount = 0;
                if (!empty($array)){
                    $arrayCount = count($array);
                }
                $pager = new CoralPager( $arrayCount, $ipp );

                // [paging] 指定ページを取得
                $current_page = $nav_info['page'];
                if( $current_page < 1 ) $current_page = 1;
                $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
                $categoryName = $codeMaster->getCreditCategoryMasterList();
                $patternName = $codeMaster->getCreditSearchPatternMasterList();


                // 検索条件関連のみビューに割り当てる
                $this->view->assign('validationResults', $valid_result);
                $this->view->assign( 'error_messages', $errors);
                $this->view->assign( 'pager', $pager );
                $this->view->assign('hashedData', $nav_info['hash']);
                $this->view->assign( 'current_page', $current_page );
                $this->view->assign('result', $array);
                $this->view->assign('categoryName', $categoryName);
                $this->view->assign('patternName', $patternName);

                $this->view->assign('EnterpriseId', $enterpriseId);

                // result.phtmlへ戻る
                $this->setTemplate('result');
                $db->getDriver()->getConnection()->rollBack();
                return $this->view;
            }

            // postされたデータをループ処理
            foreach($req->fromPost('form', array()) as $data) {
                // レンダリング時に算出された行ハッシュを抽出
                $row_hash = $data['_row_hash'];
                unset($data['_row_hash']);

                $validFlg = $data['delete'];
                if(is_null($validFlg)) {
                    $validFlg = 0;
                }
                unset($data['delete']);

                // 与信強制化
                $jintecManualReqFlg = 0;
                if ($data['jintec'] == 1) {
                    $jintecManualReqFlg = 1;
                }
                unset($data['jintec']);

                unset($data['addcount']);

                // 対象行を抽出

                $rs = new ResultSet();
                $row = $rs->initialize($this->findCreditCondition($data));
                if(! $row) {
                    // 対象行が見つからない場合は処理しない
                    $app->logger->debug(sprintf('存在しないシーケンス： %s', $data['Seq']));
                    continue;
                }

                $row = $row->toArray();

                // 内容が変更されていなければ処理しない
                ksort($data);
                if($validFlg == $row[0]['ValidFlg'] && $jintecManualReqFlg == $row[0]['JintecManualReqFlg'] && base64_encode(serialize($data)) == $row_hash) {
                    continue;
                }

                // 有効無効化
                if($validFlg == 1) {
                    $row['ValidFlg'] = 1;
                }
                else {
                    $row['ValidFlg'] = 0;
                }

                // 与信強制化
                $row['JintecManualReqFlg'] = $jintecManualReqFlg;

                // ユーザーIDの取得
                $obj = new \models\Table\TableUser($this->app->dbAdapter);
                $row['UpdateId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
                $row['RegistId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                $row = array_merge($row, $data);
                $this->saveFromArray($this->fixDataArray($row));
            }
            // すべて正常なのでトランザクションをコミット
            $db->getDriver()->getConnection()->commit();

        } catch(\Exception $err) {
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        // 検索条件関連のみビューに割り当てる
        $this->view->assign('hashedData', $nav_info['hash']);
        $this->view->assign('page', $nav_info['page']);

        $this->view->assign('EnterpriseId', $enterpriseId);

        return $this->view;
    }
            // 検証実行 追加 By Isobe 20150401

                // 検証処理
//                 $validation_results = $mdl->validate($data);
                // 重複チェックを実行
                // コメントアウト  By Isobe 20150331 未確定
//                 $validation_results = $this->checkDuplicate($data,  $validation_results);
                // コメントアウト  By Isobe 20150331 未確定
                /*
                if(! $validation_results->isValid()) {
                    // 検証エラー時は処理しない
                    $errors[] = array(
                                      'seq' => $data['Seq'],
                                      'message' => sprintf('Seq %s ：%s', $data['Seq'], join(' / ', $validation_results->getErrors()))
                                    );

                    // 検証エラー行のみ、デフォルトビューへ行を割り当てる
                    $input[] = $row;
                } else {
                    // エラーがないので永続化を実行
                    $mdl->saveFromArray($mdl->fixDataArray($row));
                }
                */
            // コメントアウト  By Isobe 20150401 未確定
//             if(count($errors) > 0) {
//                 $this
//                     ->addStyleSheet('../css/base.ui.customlist.css')
//                     ->addJavaScript('../js/base.ui.js')
//                     ->addJavaScript('../js/base.ui.customlist.js');
//                 // [paging] 1ページあたりの項目数
//                 // ※：config.iniからの取得を追加
//                 $cn = $this->getControllerName();
//                 $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf[$cn] : 100;
//                 if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 100;

//                 // [paging] 指定ページを取得
//                 $current_page = $nav_info['page'];
//                 if( $current_page < 1 ) $current_page = 1;

//                 // 表示用のカテゴリを取得
//                 // カテゴリを取得
//                 $codeMaster = new CoralCodeMaster();
// //                 $categoryName = $codeMaster->masterToArray($codeMaster->getCreditCategoryMasterList(), true);
//                 $categoryName = $codeMaster->getCreditCategoryMasterList();

//                 // [paging] ページャ初期化
//                 $pager = new CoralPager( count($input), $ipp );
//                 // [paging] 指定ページを補正
//                 if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
//                 // [paging] 対象リストをページング情報に基づいて対象リストをスライス
//                 if( count($input) > 0 ) $input = array_slice( $input, $pager->getStartIndex( $current_page ), $ipp );
//                 // [paging] ページングナビゲーション情報
//                 $page_links = array( 'base' => 'credit/result/page' );
//                 $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
//                 $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
//                 // [paging] ページング関連の情報をビューへアサイン
//                 $this->view->assign( 'current_page', $current_page );
//                 $this->view->assign( 'pager', $pager );
//                 $this->view->assign( 'page_links', $page_links );

//                 $this->view->assign('categoryName', $categoryName);
//                 $this->view->assign('resultcount', count($input));
//                 $this->view->assign('result', $input);
//                 $this->view->assign('expressions', $expressions);
//                 $this->view->assign('error_messages', $errors);
//                 $this->view->assign('hashedData', $nav_info['hash']);
//                 $this->setTemplate('result');

//                 return $this->view;
//             }
            // コメントアウト  By Isobe 20150401 未確定

//                 $mdl->saveFromArray($mdl->fixDataArray($row));
//              }
//             // すべて正常なのでトランザクションをコミット
//             $db->getDriver()->getConnection()->commit();

//         } catch(\Exception $err) {
//             $db->getDriver()->getConnection()->rollBack();
//             throw $err;
//         }

//         // 検索条件関連のみビューに割り当てる
//         $this->view->assign('hashedData', $nav_info['hash']);
//         $this->view->assign('page', $nav_info['page']);

//         return $this->view;
//    }

    /**
     * 検索条件向け連想配列と対象ページ番号を指定して、
     * 与信条件検索を実行しリスト表示の初期化を行う
     *
     * @param array $expressions 検索条件の連想配列
     * @param int $page ページ番号
     */
    protected function execSearch($expressions, $page, $enterpriseId) {
        if(! $expressions) $expressions = array();
        if(! $page) $page = 1;

        // [paging] 1ページあたりの項目数
        // ※：config.iniからの取得を追加
        $cn = $this->getControllerName();
        $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf['$cn'] : 20;
        if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 20;

        // [paging] 指定ページを取得
        $current_page = (int)($this->params()->fromRoute('page', 1));
        if( $current_page < 1 ) $current_page = 1;

        $rs = new ResultSet();

        //TableCreditCondition6テーブル分取得
        $list = $rs->initialize($this->getCreditCondtionList($expressions, $enterpriseId))->toArray();

        // 表示用のカテゴリを取得
        // カテゴリを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
//         $categoryName = $codeMaster->masterToArray($codeMaster->getCreditCategoryMasterList(), true);
        $categoryName = $codeMaster->getCreditCategoryMasterList();
        $patternName = $codeMaster->getCreditSearchPatternMasterList();

        // [paging] ページャ初期化
        // count関数対策
        $listCount = 0;
        if (!empty($list)){
            $listCount = count($list);
        }


        $pager = new CoralPager( $listCount, $ipp );
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        // count関数対策
        if(!empty($list)) $list = array_slice( $list, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        if ($enterpriseId == -1 ) {
            $page_links = array( 'base' => 'credit/result/page' );
        } else {
            $page_links = array( 'base' => 'credit/result/eid/' . $enterpriseId . '/page' );
        }

        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );


        // [paging] ページング関連の情報をビューへアサイン
        // count関数対策
        $resultcount = 0;

        if (!empty($list)){
            $resultcount = count($list);
        }


        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign('categoryName', $categoryName);
        $this->view->assign('patternName', $patternName);
        $this->view->assign('resultcount', $resultcount);
        $this->view->assign('result', $list);
        $this->view->assign('hashedData', base64_encode(serialize($expressions)));
    }


    /**
     * 指定内容と重複するデータがすでに登録済みであるかをチェックし、検証結果にマージする
     *
     * @access protected
     * @param array $data 登録向け連想配列
     * @param Logic_Validate_Result 検証結果
     * @return Logic_Validate_Result 検証結果。重複データが存在する場合はその情報がマージされた状態となる
     */
    protected function checkDuplicate(array $data, $enterpriseId, LogicValidationResult $validation_results) {
        // 重複行検索を実行
        $dups = $this->findDuplicatedConditions($data, $enterpriseId);

        if($dups->count() > 0) {
            // 重複エラーがあったら検証エラーに付け替える
            if(isset($data['Seq']))
            {
                $validation_results
                ->addError("Seq " . $data['Seq'] . '：重複する与信条件がすでに登録されています。条件文字列、コメント、ポイントのいずれかを変更してください')
                ->addInvalidKey('Cstring');
            }else{
                $validation_results
                ->addError( '重複する与信条件がすでに登録されています。条件文字列、コメント、ポイントのいずれかを変更してください')
                ->addInvalidKey('Cstring');
            }
        }
        return $validation_results;
    }

    /**
     * APIユーザ登録/変更フォームの内容を検証する
     * @param array $data 登録フォームデータ
     * @param array $seqData エラー行のSeq配列
     * @return array エラーメッセージの配列
     */
    protected function validate($data = array(), &$seqData = array()) {

        $errors = array();
        // 20180404 Add 電話番号チェッククラス
        $cvp = new CoralValidatePhone();

        //Cstring: 条件文字列
        $Key = 'Cstring';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            if(!isset($data['Seq'])){
                $errors[$Key] = " '条件文字列'は必須入力です。";
            }else{
                $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'は必須入力です。";
                $seqData[$data['Seq']] = $data['Seq'];
            }
        }
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 4000)){
            if(!isset($data['Seq'])){
                $errors[$Key] = "'条件文字列'が長すぎます";
            }else{
                $errors[$Key] = "'条件文字列'が長すぎます";
                $seqData[$data['Seq']] = $data['Seq'];
            }
        }
        //↓↓↓↓ Add 20180404 社内与信条件入力チェック追加 ↓↓↓↓
        $category = intval($data['Category']);
        // カテゴリがドメインの場合
        if (isset($category) && $category == 4) {
            // 英数字記号のみ
            if(!isset($errors[$Key]) && (!preg_match("/^[!-~]+$/", $data[$Key]))){
                if(!isset($data['Seq'])){
                    $errors[$Key] = "'条件文字列'は英数字記号のみで入力する必要があります。";
                }else{
                    $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'は英数字記号のみで入力する必要があります。";
                    $seqData[$data['Seq']] = $data['Seq'];
                }
            }
        }

        // カテゴリが事業者IDの場合
        if (isset($category) && $category == 5) {
            // 英数字のみ
            if(!isset($errors[$Key]) && (!preg_match("/^[a-zA-Z0-9]+$/", $data[$Key]))){
                if(!isset($data['Seq'])){
                    $errors[$Key] = "'条件文字列'は英数字10桁で入力する必要があります。";
                }else{
                    $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'は英数字10桁で入力する必要があります。";
                    $seqData[$data['Seq']] = $data['Seq'];
                }
            }

            // 10桁固定
            if(!isset($errors[$Key]) && (strlen ($data[$Key]) <> 10)){
                if(!isset($data['Seq'])){
                    $errors[$Key] = "'条件文字列'は英数字10桁で入力する必要があります。";
                }else{
                    $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'は英数字10桁で入力する必要があります。";
                    $seqData[$data['Seq']] = $data['Seq'];
                }
            }
        }

        // カテゴリが電話番号の場合
        if (isset($category) && $category == 8) {
            // 電話番号形式チェック
            //if (!$cvp->isValid($data[$Key])) {
            if(!isset($errors[$Key]) && (!preg_match("/^[0-9０-９－\\-]+$/u", $data[$Key]))){
                if(!isset($data['Seq'])){
                    $errors[$Key] = "'条件文字列'が電話番号として正しくありません。";
                }else{
                    $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'が電話番号として正しくありません。";
                    $seqData[$data['Seq']] = $data['Seq'];
                }
            }

            // 13桁以下
            if(!isset($errors[$Key]) && (mb_strlen ($data[$Key]) > 13)){
                if(!isset($data['Seq'])){
                    $errors[$Key] = "'条件文字列'が電話番号として正しくありません。";
                }else{
                    $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'が電話番号として正しくありません。";
                    $seqData[$data['Seq']] = $data['Seq'];
                }
            }

        }
        //↑↑↑↑ Add 20180404 社内与信条件入力チェック追加 ↑↑↑↑

        // カテゴリが金額の場合
        if (isset($category) && $category == 9) {
            // 半角数字のみ
            if(!isset($errors[$Key]) && (!preg_match("/^[0-9]+$/", $data[$Key]))){
                if(!isset($data['Seq'])){
                    $errors[$Key] = "'条件文字列'は半角数字のみで入力する必要があります。";
                }else{
                    $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'は半角数字のみで入力する必要があります。";
                    $seqData[$data['Seq']] = $data['Seq'];
                }
            }
        }

        //Comment: コメント
        $Key = 'Comment';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 255)){
            if(!isset($data['Seq'])){
                $errors[$Key] = "'コメント'が長すぎます";
            }else{
                $errors[$Key] = "'コメント'が長すぎます";
                $seqData[$data['Seq']] = $data['Seq'];
            }
        }

        //Point: ポイント
        $Key = 'Point';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            if(!isset($data['Seq'])){
                $errors[$Key] = " 'ポイント'は必須入力です。";
            }else{
                $errors[$Key] = "Seq " . $data['Seq'] . " ：'ポイント'は必須入力です。";
                $seqData[$data['Seq']] = $data['Seq'];
            }
        }
        if(!isset($errors[$Key]) && ( ($data[$Key]) < -10000000) || ( ($data[$Key]) > 10000000 ) ) {
            if(!isset($data['Seq'])){
                $errors[$Key] = "'ポイント'として正しくありません。";
            }else{
                $errors[$Key] = "'ポイント'として正しくありません。";
                $seqData[$data['Seq']] = $data['Seq'];
            }
        }

        return $errors;
    }

    /**
     * 画面情報の収集
     *
     * @param array $datas パラメータ
     * @return array 画面情報
     */
    private function getPointformData($datas)
    {
        // 社内与信ポイントマスタ情報

        $i = 1;        // CpIdはCpId=1から増分1を保証する。

        $list1 = array();
        $list2 = array();
        $list3 = array();
        $list4 = array();
        $list5 = array();
        $list6 = array();
        $list7 = array();
        $list8 = array();

        while(isset($datas["CpId" . $i]))
        {
            unset($udata);

            // CpIdを取得　他の項目は$iでなくこちらを使用
            $cpId = $datas["CpId" . $i];

            $udata["CpId"] = $cpId;
            $udata["Dependence"] = $datas["Dependence" . $cpId];
            $udata["Caption"] = $datas["Caption" . $cpId];
            $udata["Point"] = $datas["Point" . $cpId];
            $udata["Rate"] = $datas["Rate" . $cpId];
            $udata["Description"] = $datas["Description" . $cpId];
            $udata["GeneralProp"] = $datas["GeneralProp" . $cpId];
            $udata["Message"] = $datas["Message" . $cpId];

            switch((int)$datas["Dependence" . $cpId])
            {
                case 1:
                    $list1[] = $udata;
                    break;
                case 2:
                    $list2[] = $udata;
                    break;
//                case 3:
//                    $list3[] = $udata;
//                    break;
//                case 4:
//                    $list4[] = $udata;
//                    break;
                case 5:
                    $list5[] = $udata;
                    break;
//                case 6:
//                    $list6[] = $udata;
//                    break;
                case 7:
                    $list7[] = $udata;
                    break;
                case 8:
                    $list8[] = $udata;
                    break;
                default:
                    break;
            }

            $i++;
        }

        // 審査システム内重みづけ
        $list6 = $this->getSinsaPoint($datas['SinsaPoint'], $count);
        $list6Str = $datas['SinsaPoint'];

        $pointformData['list1'] = $list1;
        $pointformData['list2'] = $list2;
        $pointformData['list3'] = $list3;
        $pointformData['list4'] = $list4;
        $pointformData['list5'] = $list5;
        $pointformData['list6'] = $list6;
        $pointformData['list6Str'] = $list6Str;
        $pointformData['list6Count'] = $count;
        $pointformData['list7'] = $list7;
        $pointformData['list8'] = $list8;

        // 与信閾値
        $thresholdData = (array_key_exists('threshold', $datas)) ? $datas['threshold'] : null;
        $thresholdData['JintecManualJudgeUnpaidFlg'] = isset($datas['threshold']['JintecManualJudgeUnpaidFlg']) ? 1 : 0;
        $thresholdData['JintecManualJudgeNonPaymentFlg'] = isset($datas['threshold']['JintecManualJudgeNonPaymentFlg']) ? 1 : 0;
        $pointformData['CJThreshold'] = $thresholdData;

        // 社内与信システム設定
        $systemInfo = (array_key_exists('systeminfo', $datas)) ? $datas['systeminfo'] : null;

        $jintecManualJudgeSns = array();
        if (isset($datas['JintecManualJudgeSns11'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns11'];
        if (isset($datas['JintecManualJudgeSns12'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns12'];
        if (isset($datas['JintecManualJudgeSns13'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns13'];
        if (isset($datas['JintecManualJudgeSns14'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns14'];
        if (isset($datas['JintecManualJudgeSns101'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns101'];
        if (isset($datas['JintecManualJudgeSns102'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns102'];
        if (isset($datas['JintecManualJudgeSns103'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns103'];
        if (isset($datas['JintecManualJudgeSns104'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns104'];
        if (isset($datas['JintecManualJudgeSns105'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns105'];
        if (isset($datas['JintecManualJudgeSns106'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns106'];
        if (isset($datas['JintecManualJudgeSns107'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns107'];
        if (isset($datas['JintecManualJudgeSns108'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns108'];
        if (isset($datas['JintecManualJudgeSns109'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns109'];
        if (isset($datas['JintecManualJudgeSns111'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns111'];
        if (isset($datas['JintecManualJudgeSns114'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns114'];
        if (isset($datas['JintecManualJudgeSns115'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns115'];
        if (isset($datas['JintecManualJudgeSns116'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns116'];
        if (isset($datas['JintecManualJudgeSns117'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns117'];
        if (isset($datas['JintecManualJudgeSns118'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns118'];
        if (isset($datas['JintecManualJudgeSns201'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns201'];
        if (isset($datas['JintecManualJudgeSns202'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns202'];
        if (isset($datas['JintecManualJudgeSns203'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns203'];
        if (isset($datas['JintecManualJudgeSns204'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns204'];
        if (isset($datas['JintecManualJudgeSns205'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns205'];
        if (isset($datas['JintecManualJudgeSns206'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns206'];
        if (isset($datas['JintecManualJudgeSns207'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns207'];
        if (isset($datas['JintecManualJudgeSns208'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns208'];
        if (isset($datas['JintecManualJudgeSns301'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns301'];
        if (isset($datas['JintecManualJudgeSns302'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns302'];
        if (isset($datas['JintecManualJudgeSns303'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns303'];
        if (isset($datas['JintecManualJudgeSns304'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns304'];
        if (isset($datas['JintecManualJudgeSns305'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns305'];
        if (isset($datas['JintecManualJudgeSns401'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns401'];
        if (isset($datas['JintecManualJudgeSns402'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns402'];
        if (isset($datas['JintecManualJudgeSns403'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns403'];
        if (isset($datas['JintecManualJudgeSns404'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns404'];
        if (isset($datas['JintecManualJudgeSns405'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns405'];
        if (isset($datas['JintecManualJudgeSns406'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns406'];
        if (isset($datas['JintecManualJudgeSns407'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns407'];
        if (isset($datas['JintecManualJudgeSns501'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns501'];
        if (isset($datas['JintecManualJudgeSns502'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns502'];
        if (isset($datas['JintecManualJudgeSns503'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns503'];
        if (isset($datas['JintecManualJudgeSns504'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns504'];
        if (isset($datas['JintecManualJudgeSns551'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns551'];
        if (isset($datas['JintecManualJudgeSns552'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns552'];
        if (isset($datas['JintecManualJudgeSns553'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns553'];
        if (isset($datas['JintecManualJudgeSns554'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns554'];
        if (isset($datas['JintecManualJudgeSns555'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns555'];
        if (isset($datas['JintecManualJudgeSns556'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns556'];
        if (isset($datas['JintecManualJudgeSns557'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns557'];
        if (isset($datas['JintecManualJudgeSns558'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns558'];
        if (isset($datas['JintecManualJudgeSns559'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns559'];
        if (isset($datas['JintecManualJudgeSns560'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns560'];
        if (isset($datas['JintecManualJudgeSns601'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns601'];
        if (isset($datas['JintecManualJudgeSns602'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns602'];
        if (isset($datas['JintecManualJudgeSns1001'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns1001'];
        if (isset($datas['JintecManualJudgeSns1002'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns1002'];
        if (isset($datas['JintecManualJudgeSns1003'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns1003'];
        if (isset($datas['JintecManualJudgeSns1004'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns1004'];
        if (isset($datas['JintecManualJudgeSns1005'])) $jintecManualJudgeSns[] = $datas['JintecManualJudgeSns1005'];
        $systemInfo['JintecManualJudgeSns'] = implode(',', $jintecManualJudgeSns);

        $pointformData['CreditSystemInfo'] = $systemInfo;

        return $pointformData;
    }

    /**
     * 審査システム内重みづけ設定取得
     *
     * @param string $sinsaPoint 審査システム内重みづけ設定(カンマ区切り)
     * @param int $count 項目数（※戻り値）
     * @return array 審査システム内重みづけ設定
     */
    private function getSinsaPoint($sinsaPoint, &$count)
    {
        // 空白、改行、タブは消す
        $search = array(" ", "　", "\r", "\n", "\t");
        $replace = "";
        $newSinsaPoint = str_replace($search, $replace, $sinsaPoint);

        // 空文字列の場合、終了
        if ($newSinsaPoint == "") {
            $count = 0;
            return array();
        }

        // カンマ区切りで取得
        $sinsaPointList = explode(",", $newSinsaPoint);

        // 連想配列に変換
        $i = 0;
        unset($row);
        while (isset($sinsaPointList[$i]))
        {
            if ($i % 2 == 0)
            {
                // 偶数番は審査パターンID
                $row['Key'] = $sinsaPointList[$i];
            }
            else
            {
                // 奇数番は倍率
                $row['Value'] = $sinsaPointList[$i];
                $list6[] = $row;
                unset($row);
            }

            $i++;
        }
        if (isset($row))
        {
            $list6[] = $row;
            unset($row);
        }

        // 項目数を戻す
        $count = $i;

        // 配列を戻す
        return $list6;
    }

    /**
     * 社内与信条件設定画面 入力内容チェック
     *
     * @param array $formData 画面入力内容
     * @return array エラーメッセージの配列
     */
    private function checkPointformData($formData)
    {
        unset($errorMessage);

        // 基幹システム内重みづけ設定
        foreach ($formData['list5'] as $row)
        {
            // 入力がある場合
            if (isset($row["Rate"]) && (strlen($row["Rate"]) > 0))
            {
                // 全角は変換
                $rate = mb_convert_kana($row["Rate"], "n", "UTF-8");

                if ($this->isNumWithDecimal($rate, 2))
                {
                    // 数値の場合、範囲チェック
                    $rate = floatval($rate);

                    // 1～10
                    if ($rate < 0.00 || $rate > 10.00)
                    {
                        $errorMessage[] = "基幹システム内重みづけ設定の倍率の設定が範囲外です。";
                        break;
                    }
                }
                else
                {
                    // 数値でない場合エラー
                    $errorMessage[] = "基幹システム内重みづけ設定の倍率の設定が範囲外です。";
                    break;
                }
            }
        }

        // 審査システム内重みづけ設定
        if ((int)$formData['list6Count'] % 2 == 1)
        {
            // 項目数が奇数の場合エラー
            $errorMessage[] = "審査ﾊﾟﾀｰﾝIDと重みづけ値をカンマ区切りで交互に入力してください。"
                            . "<br />"
                            . "例：与信ﾊﾟﾀｰﾝ505と与信ﾊﾟﾀｰﾝ301に1.5と0.75の重みづけ値をそれぞれ設定（例: 505,1.5,301,0.75）";
        }
        else
        {
            // カンマ区切りの形式があっている場合のみ内容チェック
            unset($overlap);
            foreach ($formData['list6'] as $row)
            {
                // 審査パターンID
                // 入力がある場合
                if (isset($row["Key"]) && (strlen($row["Key"]) > 0))
                {
                    // 全角は変換
                    $caption = mb_convert_kana($row["Key"], "n", "UTF-8");

                    if ($this->isNumWithDecimal($caption, 0))
                    {
                        // 数値の場合、範囲チェック
                        $caption = intval($caption);

                        // 6桁の数値まで
                        if ($caption < 0 || $caption > 999999)
                        {
                            $errorMessage[] = "審査システム内重みづけパターンIDの設定が範囲外です。";
                            break;
                        }
                    }
                    else
                    {
                        // 数値でない場合エラー
                        $errorMessage[] = "審査システム内重みづけパターンIDの設定が範囲外です。";
                        break;
                    }

                    // 重複チェック
                    if (isset($overlap[$caption]))
                    {
                        $errorMessage[] = "同じ審査パターンＩＤが２つ以上指定されています。";
                    }
                    else
                    {
                        $overlap[$caption] = 1;
                    }
                }
                else
                {
                    // 未入力の場合エラー
                    $errorMessage[] = "審査ﾊﾟﾀｰﾝIDと重みづけ値をカンマ区切りで交互に入力してください。"
                                    . "<br />"
                                    . "例：与信ﾊﾟﾀｰﾝ505と与信ﾊﾟﾀｰﾝ301に1.5と0.75の重みづけ値をそれぞれ設定（例: 505,1.5,301,0.75）";
                    break;
                }

                // 倍率
                // 入力がある場合
                if (isset($row["Value"]) && (strlen($row["Value"]) > 0))
                {
                    // 全角は変換
                    $rate = mb_convert_kana($row["Value"], "n", "UTF-8");

                    if ($this->isNumWithDecimal($rate, 2))
                    {
                        // 数値の場合、範囲チェック
                        $rate = floatval($rate);

                        // 1～10
                        if ($rate < 0.00 || $rate > 10.00)
                        {
                            $errorMessage[] = "審査システム内重みづけ倍率の設定が範囲外です。";
                            break;
                        }
                    }
                    else
                    {
                        // 数値でない場合エラー
                        $errorMessage[] = "審査システム内重みづけ倍率の設定が範囲外です。";
                        break;
                    }
                }
                else
                {
                    // 未入力の場合エラー
                    $errorMessage[] = "審査ﾊﾟﾀｰﾝIDと重みづけ値をカンマ区切りで交互に入力してください。"
                                    . "<br />"
                                    . "例：与信ﾊﾟﾀｰﾝ505と与信ﾊﾟﾀｰﾝ301に1.5と0.75の重みづけ値をそれぞれ設定（例: 505,1.5,301,0.75）";
                    break;
                }
            }
        }

        // 与信閾値
        $thre = $formData['CJThreshold'];
        $compFlgJ = true;
        $compFlgC = true;
        if (!$this->isNumWithDecimal($thre['JudgeSystemHoldMAX'], 0, true))
        {
            $errorMessage[] = "審査システム利用時保留上限値が数値ではありません。";
            $compFlgJ = false;
        }
        if (!$this->isNumWithDecimal($thre['JudgeSystemHoldMIN'], 0, true))
        {
            $errorMessage[] = "審査システム利用時保留下限値が数値ではありません。";
            $compFlgJ = false;
        }
        if (!$this->isNumWithDecimal($thre['CoreSystemHoldMAX'], 0, true))
        {
            $errorMessage[] = "基幹システムのみ保留上限値が数値ではありません。";
            $compFlgC = false;
        }
        if (!$this->isNumWithDecimal($thre['CoreSystemHoldMIN'], 0, true))
        {
            $errorMessage[] = "基幹システムのみ保留下限値が数値ではありません。";
            $compFlgC = false;
        }

        if($compFlgJ)
        {
            if (intval($thre['JudgeSystemHoldMAX']) < intval($thre['JudgeSystemHoldMIN']))
            {
                $errorMessage[]="審査システム利用時保留上限値が審査システム利用時保留下限値より小さくなっています。";
            }
            elseif(intval($thre['JudgeSystemHoldMAX']) == intval($thre['JudgeSystemHoldMIN']))
            {
                $errorMessage[]="審査システム利用時保留上限値が審査システム利用時保留下限値が同じ値です。";
            }
        }

        if($compFlgC)
        {
            if (intval($thre['CoreSystemHoldMAX']) < intval($thre['CoreSystemHoldMIN']))
            {
                $errorMessage[]="基幹システムのみ保留上限値が基幹システムのみ保留下限値より小さくなっています。";
            }
            elseif(intval($thre['CoreSystemHoldMAX']) == intval($thre['CoreSystemHoldMIN']))
            {
                $errorMessage[]="基幹システムのみ保留上限値が基幹システムのみ保留下限値が同じ値です。";
            }
        }

        // 社内与信システム設定
        $systemInfo = $formData['CreditSystemInfo'];
        if (!$this->isNumWithDecimal($systemInfo['ClaimPastDays'], 0, true))
        {
            $errorMessage[] = "請求先の過去注文有効日数が数値ではありません。";
        } elseif (intval($systemInfo['ClaimPastDays']) <= 0) {
            $errorMessage[] = "請求先の過去注文有効日数は１以上の数字で入力してください。";

        }
        if (!$this->isNumWithDecimal($systemInfo['DeliveryPastDays'], 0, true))
        {
            $errorMessage[] = "配送先の過去注文有効日数が数値ではありません。";
        } elseif (intval($systemInfo['DeliveryPastDays']) <= 0) {
            $errorMessage[] = "配送先の過去注文有効日数は１以上の数字で入力してください。";

        }

        return $errorMessage;
    }

    /**
     * 数値チェック
     *
     * @param string $val チェックする文字列
     * @param int $deci 許可する小数点以下の桁数
     * @param boolean $minus 負数を許可する場合true, 省略時はfalse
     * @return boolean 数値ならTRUE
     */
    private function isNumWithDecimal($val, $deci, $minus = false)
    {
        // null の場合 false
        if (!isset($val)) return false;

        // 空文字列の場合 false
        if (strlen($val) == 0) return false;

        // 先頭と末尾の空白は無視
        $val = trim($val);
        // 空白のみの場合 false
        if (strlen($val) == 0) return false;

        // 末尾の0は無視
        $val = rtrim($val, "0");
        // 0のtrimで空になった場合、0
        if (strlen($val) == 0) $val = "0";

        // trimで . が末尾になった場合は 0 を付与
        // 末尾が . の場合、0付加
        if (mb_substr($val, -1, 1) == ".") $val .= "0";

        $minusStr = "";
        if ($minus) $minusStr = "-?";

        if ($deci == 0)
        {
            // 整数のみ
            return preg_match('/^' . $minusStr . '[0-9]+$/', $val) > 0;
        }
        else
        {
            // 小数許可
            return preg_match('/^' . $minusStr . '[0-9]+(.[0-9]{1,' . $deci . '})?$/', $val) > 0;
        }
    }

    /**
     * 与信ｼｽﾃﾑ判定基準CSV登録・修正画面へ遷移
     */
    public function impcriterionformAction()
    {
        // ファイル処理後のメッセージがある場合表示
        if (isset($_SESSION[self::SES_UPLOADCRITERIONERR]))
        {
            $errors = $_SESSION[self::SES_UPLOADCRITERIONERR];
            unset($_SESSION[self::SES_UPLOADCRITERIONERR]);
            $this->view->assign("errors", $errors);
        }

        // 取込は一度のみ（F5対応）
        $_SESSION[self::SES_UPLOADCRITERION] = "upload";

        return $this->view;
    }

    /**
     * 与信ｼｽﾃﾑ判定基準CSV登録・修正処理
     */
    public function impcriterionconfirmAction()
    {
        if (isset($_SESSION[self::SES_UPLOADCRITERION])) {
            // 一度のみ
            unset($_SESSION[self::SES_UPLOADCRITERION]);
        }
        else {
            // 二度目以降は行なしで表示
            $this->view->assign("datas", array());
            return $this->view;
        }
        unset($systemError);

        // パラメータを正しい構造で受けた取った場合のみ実行
        if (isset($_FILES['csvFile']['error']) && is_int($_FILES['csvFile']['error']))
        {
            try {
                // ファイルアップロードエラーチェック
                switch ($_FILES['csvFile']['error']) {
                    case UPLOAD_ERR_OK:
                        // エラーなし
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // ファイル未選択
                        throw new \Exception("ファイルを選択してください。");
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        // 許可サイズを超過
                        throw new \Exception("ファイルサイズが大きすぎます。");
                    default:
                        throw new \Exception("その他エラーが発生しました。");
                }

                // ファイルの内容を取得
                $tmp_name = $_FILES['csvFile']['tmp_name'];

                // テンプレートヘッダー/フィールドマスタを使用して、CSVを読み込む
                $templateId = 'CKI16132_1';     // テンプレートID       与信ｼｽﾃﾑ判定基準CSV
                $templateClass = 0;             // 区分                 CB
                $seq = 0;                       // シーケンス           区分CBのため0
                $templatePattern = 0;           // テンプレートパターン 区分CBのため0

                // 一時的にWarningをCatchできるようにする
                set_error_handler(function ($errno, $errstr) {
                                    throw new \Exception($errstr, $errno);
                                  }, E_WARNING);
                try {
                    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
                    $datas = $logicTemplate->convertFiletoArray($tmp_name, $templateId, $templateClass, $seq, $templatePattern);
                }
                catch (\Exception $ex) {
                    // WarningのCatchを解除
                    restore_error_handler();

                    // ここでCatchした場合はこのエラーにする
                    throw new \Exception( "ファイルが読み込めませんでした。" );
                }
                if( $datas === false ) {
                    throw new \Exception( $logicTemplate->getErrorMessage() );
                }
                // CSV内容チェック
                $chkResult = $this->checkCriterionData($datas, $templateId, $templateClass, $seq, $templatePattern);

                // エラー時、終了
                if (isset($chkResult)) {
                    // 終了
                    $_SESSION[self::SES_UPLOADCRITERIONERR] = $chkResult;

                    return $this->_redirect('credit/impcriterionform');
                }
                else {
                    // 登録処理
                    $saveList = $this->saveCriterionData($datas);
                }
            } catch (\Exception $e) {
                $err['line'] = 0;
                $err['col'] = 0;
                $err['message'] = $e->getMessage();
                $systemError[] = $err;
            }
        }
        else
        {
            $err['line'] = 0;
            $err['col'] = 0;
            $err['message'] = "ファイルのアップロードに失敗しました。";
            $systemError[] = $err;
        }

        // その他エラーが発生した場合、終了
        if (isset($systemError)) {
            $_SESSION[self::SES_UPLOADCRITERIONERR] = $systemError;

            return $this->_redirect('credit/impcriterionform');
        }

        // 正常終了
        $this->view->assign("datas", $saveList);
        return $this->view;
    }

    /**
     * 取得CSV内容を指定テンプレートでチェック
     *
     * @param array $datas CSVファイルデータ
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @return array エラーメッセージ配列(正常の場合、null )
     */
    private function checkCriterionData($datas, $templateId, $templateClass, $seq, $templatePattern )
    {
        // ※この時点でテンプレートマスタとの一致は確定のためそのチェックはなし
        // テンプレートヘッダより、テンプレートSEQ取得
        $mdlTemplateH = new TableTemplateHeader($this->app->dbAdapter);
        $templateSeq = $mdlTemplateH->getTemplateSeq($templateId, $templateClass, $seq, $templatePattern);
        $header = $mdlTemplateH->find($templateSeq)->current();
        // テンプレートフィールドより、項目情報取得
        $mdlTemplateF = new TableTemplateField($this->app->dbAdapter);
        $fields = ResultInterfaceToArray($mdlTemplateF->get($templateSeq));

        $line = 1;
        if ($header['TitleClass'] == 1 || $header['TitleClass'] == 2) $line++;
        unset($csvErrors);
        unset($lineErrors);
        // 取込対象が0件の場合
        // count関数対策
        if (empty($datas)) {
            $errorRow['line'] = 0;
            $errorRow['col'] = 0;
            $errorRow['message'] = "取込対象が存在しません。";
            $csvErrors[] = $errorRow;
        }
        foreach ($datas as $row) {
            // 一行ずつ内容をチェック
            unset($errorRow);

            // 与信判定基準ID 重複チェック
            if (isset($overlapCreId[$row['CreditCriterionId']]))
            {
                $errorRow['line'] = $line;
                $errorRow['col'] = 0;
                $errorRow['message'] = "ファイル内で重複した与信判定基準IDがあります。";
                $csvErrors[] = $errorRow;
                break;
            }
            else
            {
                $overlapCreId[$row['CreditCriterionId']] = 1;
            }

            // テンプレートフィールドによるチェック
            $i = 1;
            foreach($fields as $field) {
                $colData = $row[$field['PhysicalName']];

                // 必須フラグ
                if ($field['ValidFlg'] == 1 && $field['RequiredFlg'] == 1)
                {
                    // 未設定
                    if (!isset($colData) || strlen($colData) == 0) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $field['LineNumber'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . $field['LogicalName'];
                        break;
                    }
                }

                // 項目番号を保存
                $fieldIdx[$field['PhysicalName']] = $i;
                $i++;
            }

            // エラーがある場合は後続のチェックは行わずに次行へ
            if (isset($errorRow))
            {
                $lineErrors[] = $errorRow;
            }
            else
            {
                // 個別チェック

                // 与信判定基準ID
                $colData = $row['CreditCriterionId'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 正の整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['CreditCriterionId'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "与信判定基準ID";
                    }
                }

                // 与信判定基準名称
                $colData = $row['CreditCriterionName'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 100文字超過はエラー
                    if (mb_strlen($colData) > 100) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['CreditCriterionName'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "与信判定基準名称";
                    }
                }

//                // 優良顧客住所
//                $colData = $row['GoodCustomerAddressScore'];
//                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
//                    // 整数でない場合エラー
//                    if (!$this->isNumWithDecimal($colData, 0, true)) {
//                        $errorRow['line'] = $line;
//                        $errorRow['col'] = $fieldIdx['GoodCustomerAddressScore'];
//                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "優良顧客住所";
//                    }
//                }

//                // 優良顧客氏名
//                $colData = $row['GoodCustomerNameScore'];
//                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
//                    // 整数でない場合エラー
//                    if (!$this->isNumWithDecimal($colData, 0, true)) {
//                        $errorRow['line'] = $line;
//                        $errorRow['col'] = $fieldIdx['GoodCustomerNameScore'];
//                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "優良顧客氏名";
//                    }
//                }

//                // ﾌﾞﾗｯｸ住所
//                $colData = $row['BlackCustomerAddressScore'];
//                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
//                    // 整数でない場合エラー
//                    if (!$this->isNumWithDecimal($colData, 0, true)) {
//                        $errorRow['line'] = $line;
//                        $errorRow['col'] = $fieldIdx['BlackCustomerAddressScore'];
//                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "ﾌﾞﾗｯｸ住所";
//                    }
//                }

//                // ﾌﾞﾗｯｸ氏名
//                $colData = $row['BlackCustomerNameScore'];
//                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
//                    // 整数でない場合エラー
//                    if (!$this->isNumWithDecimal($colData, 0, true)) {
//                        $errorRow['line'] = $line;
//                        $errorRow['col'] = $fieldIdx['BlackCustomerNameScore'];
//                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "ﾌﾞﾗｯｸ氏名";
//                    }
//                }

                // 住所相違
                $colData = $row['SameCnAndAddrScore'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['SameCnAndAddrScore'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "住所相違";
                    }
                }

                // 郵便番号チェック
                $colData = $row['PostalCodeScore'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['PostalCodeScore'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "郵便番号チェック";
                    }
                }

                // 過去取引状況による加点
                $colData = $row['PastOrderScore'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['PastOrderScore'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "過去取引状況による加点";
                    }
                }

                // 身分証ｱｯﾌﾟﾛｰﾄﾞ
                $colData = $row['IdentityDocumentScore'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['IdentityDocumentScore'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "身分証ｱｯﾌﾟﾛｰﾄﾞ";
                    }
                }

                // いたずらｷｬﾝｾﾙ
                $colData = $row['MischiefCancelCount'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['MischiefCancelCount'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "いたずらｷｬﾝｾﾙ";
                    }
                }

                // 未払い回数
                $colData = $row['UnpaidCount'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['UnpaidCount'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "未払い回数";
                    }
                }

                // 不払い回数
                $colData = $row['NonPaymentCount'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['NonPaymentCount'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "不払い回数";
                    }
                }

                // 不払い日数
                $colData = $row['NonPaymentDays'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['NonPaymentDays'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "不払い日数";
                    }
                }

                // 債権返却ｷｬﾝｾﾙ回数
                $colData = $row['Cnl_ReturnSaikenCount'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['Cnl_ReturnSaikenCount'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "債権返却ｷｬﾝｾﾙ回数";
                    }
                }

                // 請求総額設定①-条件
                $colData = $row['ClaimTotal_1-Condition'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ClaimTotal_1-Condition'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "請求総額設定①-条件";
                    }
                }

                // 請求総額設定①-ｽｺｱ
                $colData = $row['ClaimTotal_1-Score'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ClaimTotal_1-Score'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "請求総額設定①-ｽｺｱ";
                    }
                }

                // 請求総額設定②-条件
                $colData = $row['ClaimTotal_2-Condition'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ClaimTotal_2-Condition'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "請求総額設定②-条件";
                    }
                }

                // 請求総額設定②-ｽｺｱ
                $colData = $row['ClaimTotal_2-Score'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ClaimTotal_2-Score'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "請求総額設定②-ｽｺｱ";
                    }
                }

                // 未払い総額-条件
                $colData = $row['UnpaidTotal-Condition'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['UnpaidTotal-Condition'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "未払い総額-条件";
                    }
                }

                // 未払い総額-ｽｺｱ
                $colData = $row['UnpaidTotal-Score'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['UnpaidTotal-Score'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "未払い総額-ｽｺｱ";
                    }
                }

                // 不払い総額-条件
                $colData = $row['NonPaymentTotal-Condition'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['NonPaymentTotal-Condition'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "不払い総額-条件";
                    }
                }

                // 不払い総額-ｽｺｱ
                $colData = $row['NonPaymentTotal-Score'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['NonPaymentTotal-Score'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "不払い総額-ｽｺｱ";
                    }
                }

                // 審査ｼｽﾃﾑ利用時保留上限
                $colData = $row['JudgeSystemHoldMAX'];
                unset($JudgeSystemHoldMAX);
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['JudgeSystemHoldMAX'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "審査ｼｽﾃﾑ利用時保留上限";
                    }
                    else {
                        $JudgeSystemHoldMAX = intval($colData);
                    }
                }

                // 審査ｼｽﾃﾑ利用時保留下限
                $colData = $row['JudgeSystemHoldMIN'];
                unset($JudgeSystemHoldMIN);
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['JudgeSystemHoldMIN'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "審査ｼｽﾃﾑ利用時保留下限";
                    }
                    else {
                        $JudgeSystemHoldMIN = intval($colData);
                    }
                }

                // 基幹ｼｽﾃﾑのみ保留上限
                $colData = $row['CoreSystemHoldMAX'];
                unset($CoreSystemHoldMAX);
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['CoreSystemHoldMAX'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑのみ保留上限";
                    }
                    else {
                        $CoreSystemHoldMAX = intval($colData);
                    }
                }

                // 基幹ｼｽﾃﾑのみ保留下限
                $colData = $row['CoreSystemHoldMIN'];
                unset($CoreSystemHoldMIN);
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['CoreSystemHoldMIN'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑのみ保留下限";
                    }
                    else {
                        $CoreSystemHoldMIN = intval($colData);
                    }
                }

                // 審査ｼｽﾃﾑ利用時保留 上限・下限チェック
                if (!isset($errorRow) && isset($JudgeSystemHoldMAX) && isset($JudgeSystemHoldMIN)) {
                    if ($JudgeSystemHoldMAX < $JudgeSystemHoldMIN) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['JudgeSystemHoldMAX'];
                        $errorRow['message'] = "審査システム利用時保留上限値が審査システム利用時保留下限値より小さくなっています";
                    }
                    elseif ($JudgeSystemHoldMAX == $JudgeSystemHoldMIN) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['JudgeSystemHoldMAX'];
                        $errorRow['message'] = "審査システム利用時保留上限値が審査システム利用時保留下限値が同じ値です。";
                    }
                }

                // 基幹ｼｽﾃﾑのみ保留 上限・下限チェック
                if (!isset($errorRow) && isset($CoreSystemHoldMAX) && isset($CoreSystemHoldMIN)) {
                    if ($CoreSystemHoldMAX < $CoreSystemHoldMIN) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['CoreSystemHoldMAX'];
                        $errorRow['message'] = "基幹システムのみ保留上限値が基幹システムのみ保留下限値より小さくなっています";
                    }
                    elseif ($CoreSystemHoldMAX == $CoreSystemHoldMIN) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['CoreSystemHoldMAX'];
                        $errorRow['message'] = "基幹システムのみ保留上限値が基幹システムのみ保留下限値が同じ値です。";
                    }
                }

                // 基幹ｼｽﾃﾑ-請求先-ｽｺｱ倍率
                $colData = $row['Core-Customer-ScoreRate'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 小数第2位までの正の数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 2)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['Core-Customer-ScoreRate'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑ-請求先-ｽｺｱ倍率";
                    }
                    else {
                        // 数値の場合、0～10でなければエラー
                        $colFloat = floatval($colData);
                        if ($colFloat < 0.00 || $colFloat > 10.00) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Core-Customer-ScoreRate'];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑ-請求先-ｽｺｱ倍率";
                        }
                    }
                }

                // 基幹ｼｽﾃﾑ-注文商品-ｽｺｱ倍率
                $colData = $row['Core-OrderItem-ScoreRate'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 小数第2位までの正の数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 2)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['Core-OrderItem-ScoreRate'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑ-注文商品-ｽｺｱ倍率";
                    }
                    else {
                        // 数値の場合、0～10でなければエラー
                        $colFloat = floatval($colData);
                        if ($colFloat < 0.00 || $colFloat > 10.00) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Core-OrderItem-ScoreRate'];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑ-注文商品-ｽｺｱ倍率";
                        }
                    }
                }

                // 基幹ｼｽﾃﾑ-配送先-ｽｺｱ倍率
                $colData = $row['Core-Delivery-ScoreRate'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 小数第2位までの正の数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 2)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['Core-Delivery-ScoreRate'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑ-配送先-ｽｺｱ倍率";
                    }
                    else {
                        // 数値の場合、0～10でなければエラー
                        $colFloat = floatval($colData);
                        if ($colFloat < 0.00 || $colFloat > 10.00) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Core-Delivery-ScoreRate'];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . "基幹ｼｽﾃﾑ-配送先-ｽｺｱ倍率";
                        }
                    }
                }

                // 審査ｼｽﾃﾑ 検出ﾊﾟﾀｰﾝID,ｽｺｱ倍率
                unset($overlapJudgeId);
                for ($i = 1 ; $i <= 70 ; $i++) {
                    unset($key);
                    unset($value);
                    // 審査ｼｽﾃﾑ-検出ﾊﾟﾀｰﾝID
                    $col = 'Judge-DetectionPatternID_' . $i;
                    $name = '審査ｼｽﾃﾑ-検出ﾊﾟﾀｰﾝID_' . $i;
                    $colData = $row[$col];
                    if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                        // 正の正数でない場合エラー
                        if (!$this->isNumWithDecimal($colData, 0)) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx[$col];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . $name;
                            break;
                        }
                        else {
                            // 数値の場合、0～999999でなければエラー
                            $colInt = intval($colData);
                            if ($colInt < 0 || $colInt > 999999) {
                                $errorRow['line'] = $line;
                                $errorRow['col'] = $fieldIdx[$col];
                                $errorRow['message'] = "次の値がレイアウトにあっていません。" . $name;
                                break;
                            }
                            $key = $colInt;
                        }

                        // 検出パターンID重複チェック
                        if (!isset($errorRow) && isset($overlapJudgeId[$colData])) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx[$col];
                            $errorRow['message'] = "同じ審査パターンＩＤが２つ以上指定されています。";
                            break;
                        }
                        else {
                            $overlapJudgeId[$colData] = 1;
                        }
                    }

                    // 審査ｼｽﾃﾑ-ｽｺｱ倍率
                    $col = 'Judge-ScoreRate_' . $i;
                    $name = '審査ｼｽﾃﾑ-ｽｺｱ倍率_' . $i;
                    $colData = $row[$col];
                    if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                        // 小数第2位までの正の数でない場合エラー
                        if (!$this->isNumWithDecimal($colData, 2)) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx[$col];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . $name;
                            break;
                        }
                        else {
                            // 数値の場合、0～10でなければエラー
                            $colFloat = floatval($colData);
                            if ($colFloat < 0.00 || $colFloat > 10.00) {
                                $errorRow['line'] = $line;
                                $errorRow['col'] = $fieldIdx[$col];
                                $errorRow['message'] = "次の値がレイアウトにあっていません。" . $name;
                                break;
                            }
                            $value = $colFloat;
                        }
                    }

                    // IDと倍率でペアが成立しない場合エラー
                    if((isset($key) && !isset($value)) || (!isset($key) && isset($value))) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx[$col];
                        $errorRow['message'] = "審査ｼｽﾃﾑ-検出ﾊﾟﾀｰﾝIDと審査ｼｽﾃﾑ-ｽｺｱ倍率はペアで設定してください。";
                        break;
                    }
                }

                // 与信強制手動化-未払い1
                $colData = $row['ManualJudge-UnpaidFlg'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 数値換算時に[0][1]でない場合エラー
                    $val = intval($colData);
                    if (!($val == 0 || $val == 1)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ManualJudge-UnpaidFlg'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "与信強制手動化-未払い1";
                    }
                }

                // 与信強制手動化-不払い1
                $colData = $row['ManualJudge-NonPaymentFlg'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 数値換算時に[0][1]でない場合エラー
                    $val = intval($colData);
                    if (!($val == 0 || $val == 1)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ManualJudge-NonPaymentFlg'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "与信強制手動化-不払い1";
                    }
                }

                // 与信強制手動化-審査システム回答条件
                $colData = $row['ManualJudge-JintecManualJudgeSns'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 各要素が数値換算時に0より大きくない場合エラー
                    $isExistInvalidValue = false;
                    $arycol = explode(',', $colData);
                    foreach ($arycol as $val) {
                        if (!(intval($val) > 0)) {
                            $isExistInvalidValue = true;
                            break;
                        }
                    }
                    if ($isExistInvalidValue) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ManualJudge-JintecManualJudgeSns'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "与信強制手動化-審査システム回答条件";
                    }
                }

                // 個別チェックエラーを保存
                if (isset($errorRow))
                {
                    $lineErrors[] = $errorRow;
                }
            }

            // 行数カウント
            $line++;
        }

        // 全体のエラーがある場合、終了
        if (isset($csvErrors)) {
            return $csvErrors;
        }
        // 行ごとのエラーがある場合、終了
        if (isset($lineErrors)) {
            return $lineErrors;
        }

        return null;
    }

    /**
     * 取得CSV内容を保存
     *
     * @param array $datas CSVデータ
     */
    private function saveCriterionData($datas)
    {
        unset($results);

        // トランザクションの開始
        $db = $this->app->dbAdapter;

        try {
            $db->getDriver()->getConnection()->beginTransaction();

            // ユーザID
            $user = new TableUser($this->app->dbAdapter);
            $userId = $user->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mdlCode = new TableCode($this->app->dbAdapter);
            $mdlCreditPont = new TableCreditPoint($this->app->dbAdapter);
            $mdlThreshold = new TableCreditJudgeThreshold($this->app->dbAdapter);

            foreach($datas as $data) {
                unset($result);
                // コードマスター 与信判定基準ID存在チェック
                $code = $mdlCode->find(self::CRITERIONCODEID, $data['CreditCriterionId'])->current();
                if(is_null($code['KeyCode'])) {
                    // 存在しない場合、登録
                    $newCode['CodeId'] = self::CRITERIONCODEID;
                    $newCode['KeyCode'] = $data['CreditCriterionId'];
                    $newCode['KeyContent'] = $data['CreditCriterionName'];
                    $newCode['Class1'] = null;
                    $newCode['Class2'] = null;
                    $newCode['Class3'] = null;
                    $newCode['Note'] = null;
                    $newCode['SystemFlg'] = 0;
                    $newCode['RegistId'] = $userId;
                    $newCode['UpdateId'] = $userId;
                    $newCode['ValidFlg'] = 1;

                    $mdlCode->saveNew($newCode);
                }
                else {
                    // 存在する場合、名称を更新
                    $updCode['KeyContent'] = $data['CreditCriterionName'];
                    $updCode['UpdateId'] = $userId;
                    $mdlCode->saveUpdate($updCode, self::CRITERIONCODEID, $data['CreditCriterionId']);
                }

                // 社内与信ポイントマスター 与信判定基準ID存在チェック
                $sql = " SELECT COUNT(*) AS Cnt FROM M_CreditPoint WHERE CreditCriterionId = :CreditCriterionId";
                $stm = $this->app->dbAdapter->query($sql);
                $prm = array(
                        ':CreditCriterionId' => $data['CreditCriterionId'],
                );
                $cnt = $stm->execute($prm)->current()['Cnt'];
                if ($cnt == 0) {
                    // 登録
                    // 社内ポイントマスター
//                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 101)->current();
//                    $idata = $this->getInsertCriterionRow(101, $pointData, $data, $userId);
//                    $mdlCreditPont->saveNew($idata);
//                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 102)->current();
//                    $idata = $this->getInsertCriterionRow(102, $pointData, $data, $userId);
//                    $mdlCreditPont->saveNew($idata);
//                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 103)->current();
//                    $idata = $this->getInsertCriterionRow(103, $pointData, $data, $userId);
//                    $mdlCreditPont->saveNew($idata);
//                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 104)->current();
//                    $idata = $this->getInsertCriterionRow(104, $pointData, $data, $userId);
//                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 105)->current();
                    $idata = $this->getInsertCriterionRow(105, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 106)->current();
                    $idata = $this->getInsertCriterionRow(106, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 107)->current();
                    $idata = $this->getInsertCriterionRow(107, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 108)->current();
                    $idata = $this->getInsertCriterionRow(108, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 109)->current();
                    $idata = $this->getInsertCriterionRow(109, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);

                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 201)->current();
                    $idata = $this->getInsertCriterionRow(201, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 202)->current();
                    $idata = $this->getInsertCriterionRow(202, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 203)->current();
                    $idata = $this->getInsertCriterionRow(203, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 204)->current();
                    $idata = $this->getInsertCriterionRow(204, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 205)->current();
                    $idata = $this->getInsertCriterionRow(205, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 206)->current();
                    $idata = $this->getInsertCriterionRow(206, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);

                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 301)->current();
                    $idata = $this->getInsertCriterionRow(301, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 302)->current();
                    $idata = $this->getInsertCriterionRow(302, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 303)->current();
                    $idata = $this->getInsertCriterionRow(303, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 304)->current();
                    $idata = $this->getInsertCriterionRow(304, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);

                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 401)->current();
                    $idata = $this->getInsertCriterionRow(401, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 402)->current();
                    $idata = $this->getInsertCriterionRow(402, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);
                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 403)->current();
                    $idata = $this->getInsertCriterionRow(403, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);

                    $pointData = $mdlCreditPont->findCreditPoint(self::CREDITCRITERIONID, 501)->current();
                    $idata = $this->getInsertCriterionRow(501, $pointData, $data, $userId);
                    $mdlCreditPont->saveNew($idata);

                    // 与信閾値
                    $thre = $mdlThreshold->getByCriterionid($data['CreditCriterionId'])->current();
                    if (is_null($thre['Seq'])) {
                        $idata['CreditCriterionId'] = $data['CreditCriterionId'];
                        $idata['JudgeSystemHoldMAX'] = $data['JudgeSystemHoldMAX'];
                        $idata['JudgeSystemHoldMIN'] = $data['JudgeSystemHoldMIN'];
                        $idata['CoreSystemHoldMAX'] = $data['CoreSystemHoldMAX'];
                        $idata['CoreSystemHoldMIN'] = $data['CoreSystemHoldMIN'];
                        $idata['JintecManualJudgeUnpaidFlg'] = intval($data['ManualJudge-UnpaidFlg']);
                        $idata['JintecManualJudgeNonPaymentFlg'] = intval($data['ManualJudge-NonPaymentFlg']);
                        $idata['JintecManualJudgeSns'] = $data['ManualJudge-JintecManualJudgeSns'];
                        $idata['RegistId'] = $userId;
                        $idata['UpdateId'] = $userId;
                        $idata['ValidFlg'] = 1;

                        $mdlThreshold->saveNew($idata);
                    }

                    // 登録/更新情報設定
                    $result['CreditCriterionId'] = $data['CreditCriterionId'];
                    $result['CreditCriterionName'] = $data['CreditCriterionName'];
                    $result['process'] = "登録";
                    $results[] = $result;
                }
                else {
                    // 更新
                    // 更新条件(共通)
                    $conditionArray = array(
                        "CreditCriterionId" => $data['CreditCriterionId'],
                        'CpId' => 0,
                        "ValidFlg" => 1,
                    );
                    // 社内与信ポイントマスター
//                    $udata = $this->getUpdateCriterionRow(101, $data, $userId);
//                    $conditionArray['CpId'] = 101;
//                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
//                    $udata = $this->getUpdateCriterionRow(102, $data, $userId);
//                    $conditionArray['CpId'] = 102;
//                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
//                    $udata = $this->getUpdateCriterionRow(103, $data, $userId);
//                    $conditionArray['CpId'] = 103;
//                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
//                    $udata = $this->getUpdateCriterionRow(104, $data, $userId);
//                    $conditionArray['CpId'] = 104;
 //                   $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(105, $data, $userId);
                    $conditionArray['CpId'] = 105;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(106, $data, $userId);
                    $conditionArray['CpId'] = 106;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(107, $data, $userId);
                    $conditionArray['CpId'] = 107;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(108, $data, $userId);
                    $conditionArray['CpId'] = 108;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(109, $data, $userId);
                    $conditionArray['CpId'] = 109;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);

                    $udata = $this->getUpdateCriterionRow(201, $data, $userId);
                    $conditionArray['CpId'] = 201;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(202, $data, $userId);
                    $conditionArray['CpId'] = 202;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(203, $data, $userId);
                    $conditionArray['CpId'] = 203;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(204, $data, $userId);
                    $conditionArray['CpId'] = 204;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(205, $data, $userId);
                    $conditionArray['CpId'] = 205;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(206, $data, $userId);
                    $conditionArray['CpId'] = 206;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);

                    $udata = $this->getUpdateCriterionRow(301, $data, $userId);
                    $conditionArray['CpId'] = 301;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(302, $data, $userId);
                    $conditionArray['CpId'] = 302;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(303, $data, $userId);
                    $conditionArray['CpId'] = 303;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(304, $data, $userId);
                    $conditionArray['CpId'] = 304;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);

                    $udata = $this->getUpdateCriterionRow(401, $data, $userId);
                    $conditionArray['CpId'] = 401;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(402, $data, $userId);
                    $conditionArray['CpId'] = 402;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);
                    $udata = $this->getUpdateCriterionRow(403, $data, $userId);
                    $conditionArray['CpId'] = 403;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);

                    $udata = $this->getUpdateCriterionRow(501, $data, $userId);
                    $conditionArray['CpId'] = 501;
                    $mdlCreditPont->saveUpdateWhere($udata, $conditionArray);

                    // 与信閾値
                    $thre = $mdlThreshold->getByCriterionid($data['CreditCriterionId'])->current();
                    if (!is_null($thre['Seq'])) {
                        $udata['JudgeSystemHoldMAX'] = $data['JudgeSystemHoldMAX'];
                        $udata['JudgeSystemHoldMIN'] = $data['JudgeSystemHoldMIN'];
                        $udata['CoreSystemHoldMAX'] = $data['CoreSystemHoldMAX'];
                        $udata['CoreSystemHoldMIN'] = $data['CoreSystemHoldMIN'];
                        $udata['JintecManualJudgeUnpaidFlg'] = intval($data['ManualJudge-UnpaidFlg']);
                        $udata['JintecManualJudgeNonPaymentFlg'] = intval($data['ManualJudge-NonPaymentFlg']);
                        $udata['JintecManualJudgeSns'] = $data['ManualJudge-JintecManualJudgeSns'];

                        $mdlThreshold->saveUpdate($udata, $thre['Seq']);
                    }

                    // 登録/更新情報設定
                    $result['CreditCriterionId'] = $data['CreditCriterionId'];
                    $result['CreditCriterionName'] = $data['CreditCriterionName'];
                    $result['process'] = "更新";
                    $results[] = $result;
                }
            }

            // コミット
            $db->getDriver()->getConnection()->commit();

            return $results;
        }
        catch (\Exception $err) {
            // ロールバック
            $db->getDriver()->getConnection()->rollBack();

            // エラーの場合、終了
            throw new \Exception($err->getMessage());
        }
    }

    /**
     * 社内与信ポイントマスタ登録用に成型
     *
     * @param int $cpId 与信ポイントID
     * @param array $pointData CB基準の社内与信ポイントマスターレコード
     * @param array $data CSVレコードデータ
     * @param string $userId ユーザID
     * @return array 社内与信ポイントマスタ登録配列
     */
    private function getInsertCriterionRow($cpId, $pointData, $data, $userId)
    {
        // 全与信ポイントID共通
        $insertRow['CreditCriterionId'] = $data['CreditCriterionId'];
        $insertRow['CpId'] = $cpId;
        $insertRow['Caption'] = $pointData['Caption'];
        $insertRow['Point'] = null;
        $insertRow['Message'] = $pointData['Message'];
        $insertRow['Description'] = $pointData['Description'];
        $insertRow['Dependence'] = $pointData['Dependence'];
        $insertRow['GeneralProp'] = null;
        $insertRow['SetCategory'] = null;
        $insertRow['CreditCriterionName'] = $data['CreditCriterionName'];
        $insertRow['Rate'] = null;
        $insertRow['RegistId'] = $userId;
        $insertRow['UpdateId'] = $userId;
        $insertRow['ValidFlg'] = 1;

        switch ($cpId) {
//            case 101:
//                $insertRow['Point'] = $data['GoodCustomerAddressScore'];
//                break;
//            case 102:
//                $insertRow['Point'] = $data['GoodCustomerNameScore'];
//                break;
//            case 103:
//                $insertRow['Point'] = $data['BlackCustomerAddressScore'];
//                break;
//            case 104:
//                $insertRow['Point'] = $data['BlackCustomerNameScore'];
//                break;
            case 105:
                $insertRow['Point'] = $data['SameCnAndAddrScore'];
                break;
            case 106:
                $insertRow['Point'] = $data['PostalCodeScore'];
                break;
            case 107:
                $insertRow['Point'] = $data['PastOrderScore'];
                break;
            case 108:
                $insertRow['Point'] = $data['IdentityDocumentScore'];
                break;
            case 109:
                $insertRow['Point'] = $data['MischiefCancelCount'];
                break;
            case 201:
                $insertRow['Point'] = $data['UnpaidCount'];
                break;
            case 202:
                $insertRow['Point'] = $data['NonPaymentCount'];
                break;
            case 203:
                $insertRow['Point'] = $data['Cnl_ReturnSaikenCount'];
                break;
            case 204:
                $insertRow['Point'] = intval($data['NonPaymentCount_Site']);
                break;
            case 205:
                $insertRow['Point'] = intval($data['NonPaymentCount_OtherSite']);
                break;
            case 206:
                $insertRow['Point'] = $data['NonPaymentDays'];
                break;
            case 301:
                $insertRow['GeneralProp'] = $data['ClaimTotal_1-Condition'];
                $insertRow['Point'] = $data['ClaimTotal_1-Score'];
                break;
            case 302:
                $insertRow['GeneralProp'] = $data['ClaimTotal_2-Condition'];
                $insertRow['Point'] = $data['ClaimTotal_2-Score'];
                break;
            case 303:
                $insertRow['GeneralProp'] = $data['UnpaidTotal-Condition'];
                $insertRow['Point'] = $data['UnpaidTotal-Score'];
                break;
            case 304:
                $insertRow['GeneralProp'] = $data['NonPaymentTotal-Condition'];
                $insertRow['Point'] = $data['NonPaymentTotal-Score'];
                break;
            case 401:
                $insertRow['Rate'] = $data['Core-Customer-ScoreRate'];
                break;
            case 402:
                $insertRow['Rate'] = $data['Core-OrderItem-ScoreRate'];
                break;
            case 403:
                $insertRow['Rate'] = $data['Core-Delivery-ScoreRate'];
                break;
            default:
                break;
        }

        // 審査ｼｽﾃﾑの条件はJSON形式
        if ($cpId == 501) {
            for ($i = 1 ; $i <= 70 ; $i++) {
                unset($list);
                // 審査ｼｽﾃﾑ-検出ﾊﾟﾀｰﾝID
                $col = 'Judge-DetectionPatternID_' . $i;
                $colData = $data[$col];
                if (isset($colData) && strlen($colData) > 0) {
                    $colInt = intval($colData);
                    $list['Key'] = $colInt;
                }

                // 審査ｼｽﾃﾑ-ｽｺｱ倍率
                $col = 'Judge-ScoreRate_' . $i;
                $colData = $data[$col];
                if (isset($colData) && strlen($colData) > 0) {
                    $colFloat = floatval($colData);
                    $list['Value'] = $colFloat;
                }

                if (isset($list)) {
                    $list6[] = $list;
                }
            }

            $list6Json = json::encode(isset($list6) ? $list6 : array());
            $insertRow['Description'] = $list6Json;
        }

        return $insertRow;
    }

    /**
     * 社内与信ポイントマスタ更新用に成型
     *
     * @param int $cpId 与信ポイントID
     * @param array $data CSVレコードデータ
     * @param string $userId ユーザID
     * @return array 社内与信ポイントマスタ登録配列
     */
    private function getUpdateCriterionRow($cpId, $data, $userId)
    {
        switch ($cpId) {
//            case 101:
//                $updateRow['Point'] = $data['GoodCustomerAddressScore'];
//                break;
//            case 102:
//                $updateRow['Point'] = $data['GoodCustomerNameScore'];
//                break;
//            case 103:
//                $updateRow['Point'] = $data['BlackCustomerAddressScore'];
//                break;
//            case 104:
//                $updateRow['Point'] = $data['BlackCustomerNameScore'];
//                break;
            case 105:
                $updateRow['Point'] = $data['SameCnAndAddrScore'];
                break;
            case 106:
                $updateRow['Point'] = $data['PostalCodeScore'];
                break;
            case 107:
                $updateRow['Point'] = $data['PastOrderScore'];
                break;
            case 108:
                $updateRow['Point'] = $data['IdentityDocumentScore'];
                break;
            case 109:
                $updateRow['Point'] = $data['MischiefCancelCount'];
                break;
            case 201:
                $updateRow['Point'] = $data['UnpaidCount'];
                break;
            case 202:
                $updateRow['Point'] = $data['NonPaymentCount'];
                break;
            case 203:
                $updateRow['Point'] = $data['Cnl_ReturnSaikenCount'];
                break;
            case 204:
                $updateRow['Point'] = intval($data['NonPaymentCount_Site']);
                break;
            case 205:
                $updateRow['Point'] = intval($data['NonPaymentCount_OtherSite']);
                break;
            case 206:
                $updateRow['Point'] = $data['NonPaymentDays'];
                break;
            case 301:
                $updateRow['GeneralProp'] = $data['ClaimTotal_1-Condition'];
                $updateRow['Point'] = $data['ClaimTotal_1-Score'];
                break;
            case 302:
                $updateRow['GeneralProp'] = $data['ClaimTotal_2-Condition'];
                $updateRow['Point'] = $data['ClaimTotal_2-Score'];
                break;
            case 303:
                $updateRow['GeneralProp'] = $data['UnpaidTotal-Condition'];
                $updateRow['Point'] = $data['UnpaidTotal-Score'];
                break;
            case 304:
                $updateRow['GeneralProp'] = $data['NonPaymentTotal-Condition'];
                $updateRow['Point'] = $data['NonPaymentTotal-Score'];
                break;
            case 401:
                $updateRow['Rate'] = $data['Core-Customer-ScoreRate'];
                break;
            case 402:
                $updateRow['Rate'] = $data['Core-OrderItem-ScoreRate'];
                break;
            case 403:
                $updateRow['Rate'] = $data['Core-Delivery-ScoreRate'];
                break;
            default:
                break;
        }

        // 審査ｼｽﾃﾑの条件はJSON形式
        if ($cpId == 501) {
            for ($i = 1 ; $i <= 70 ; $i++) {
                unset($list);
                // 審査ｼｽﾃﾑ-検出ﾊﾟﾀｰﾝID
                $col = 'Judge-DetectionPatternID_' . $i;
                $colData = $data[$col];
                if (isset($colData) && strlen($colData) > 0) {
                    $colInt = intval($colData);
                    $list['Key'] = $colInt;
                }

                // 審査ｼｽﾃﾑ-ｽｺｱ倍率
                $col = 'Judge-ScoreRate_' . $i;
                $colData = $data[$col];
                if (isset($colData) && strlen($colData) > 0) {
                    $colFloat = floatval($colData);
                    $list['Value'] = $colFloat;
                }

                if (isset($list)) {
                    $list6[] = $list;
                }
            }

            $list6Json = json::encode(isset($list6) ? $list6 : array());
            $updateRow['Description'] = $list6Json;
        }

        $updateRow['CreditCriterionName'] = $data['CreditCriterionName'];
        $updateRow['UpdateId'] = $userId;

        return $updateRow;
    }

    /**
     * 社内与信条件CSV登録・修正画面へ遷移
     */
    public function importformAction()
    {
        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        // 登録後メッセージ表示
        if (isset($_SESSION[self::SES_UPLOADCONDITIONUPD]))
        {
            unset($_SESSION[self::SES_UPLOADCONDITIONUPD]);
            $this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        // ファイル処理後のメッセージがある場合表示
        if (isset($_SESSION[self::SES_UPLOADCONDITIONERR]))
        {
            $errors = $_SESSION[self::SES_UPLOADCONDITIONERR];
            unset($_SESSION[self::SES_UPLOADCONDITIONERR]);
            $this->view->assign("errors", $errors);
        }

        // 取込は一度のみ（F5対応）
        $_SESSION[self::SES_UPLOADCONDITION] = "upload";

        $this->view->assign('EnterpriseId', $enterpriseId);

        return $this->view;
    }

    /**
     * 社内与信条件CSV登録・修正確認画面へ遷移
     * CSVの内容チェックも行う
     */
    public function importconfirmAction()
    {
        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);

        if (isset($_SESSION[self::SES_UPLOADCONDITION])) {
            // 一度のみ
            unset($_SESSION[self::SES_UPLOADCONDITION]);
        }
        else {
            // 二度目以降は行なしで表示
            $this->view->assign("datas", array());
            return $this->view;
        }
        unset($systemError);

        // パラメータを正しい構造で受けた取った場合のみ実行
        if (isset($_FILES['csvFile']['error']) && is_int($_FILES['csvFile']['error']))
        {
            try {
                // ファイルアップロードエラーチェック
                switch ($_FILES['csvFile']['error']) {
                    case UPLOAD_ERR_OK:
                        // エラーなし
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // ファイル未選択
                        throw new \Exception("ファイルを選択してください。");
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        // 許可サイズを超過
                        throw new \Exception("ファイルサイズが大きすぎます。");
                    default:
                        throw new \Exception("その他エラーが発生しました。");
                }

                // ファイルの内容を取得
                $tmp_name = $_FILES['csvFile']['tmp_name'];

                // テンプレートヘッダー/フィールドマスタを使用して、CSVを読み込む
                $templateId = 'CKI16135_1';     // テンプレートID       与信条件CSV
                $templateClass = 0;             // 区分                 CB
                $seq = 0;                       // シーケンス           区分CBのため0
                $templatePattern = 0;           // テンプレートパターン 区分CBのため0

                // 一時的にWarningをCatchできるようにする
                set_error_handler(function ($errno, $errstr) {
                    throw new \Exception($errstr, $errno);
                }, E_WARNING);

                try {
                    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
                    $datas = $logicTemplate->convertFiletoArray($tmp_name, $templateId, $templateClass, $seq, $templatePattern);
                }
                catch (\Exception $ex) {
                    // WarningのCatchを解除
                    restore_error_handler();

                    // ここでCatchした場合はこのエラーにする
                    throw new \Exception( "ファイルが読み込めませんでした。" );
                }

                if( $datas === false ) {
                    throw new \Exception( $logicTemplate->getErrorMessage() );
                }

                // CSV内容チェック
                $chkResult = $this->checkConditionData($datas, $templateId, $templateClass, $seq, $templatePattern, $enterpriseId);

                // エラー時、終了
                if (isset($chkResult)) {
                    // 終了
                    $_SESSION[self::SES_UPLOADCONDITIONERR] = $chkResult;

                    if ($enterpriseId == null || $enterpriseId == -1 ){
                        return $this->_redirect('credit/importform');
                    } else {
                        return $this->_redirect('credit/importform/eid/' . $enterpriseId );
                    }
                }
                else {
                    // 表示用に加工
                    $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
                    $categoryName = $codeMaster->getCreditCategoryCaption($category);
                    foreach ($datas as $key => $value) {
                        // カテゴリ名
                        $datas[$key]['CategoryName'] = $codeMaster->getCreditCategoryCaption($datas[$key]['Category']);
                        // 検索方法
                        $datas[$key]['SearchPatternName'] = $codeMaster->getCreditSearchPatternCaption($datas[$key]['SearchPattern']);
                        // 有効/無効
                        $datas[$key]['ValidFlg'] = $datas[$key]['Seq'] == "" ? 1 : $datas[$key]['ValidFlg'];
                    }
                }
            } catch (\Exception $e) {
                $err['line'] = 0;
                $err['col'] = 0;
                $err['message'] = $e->getMessage();
                $systemError[] = $err;
            }
        }
        else
        {
            $err['line'] = 0;
            $err['col'] = 0;
            $err['message'] = "ファイルのアップロードに失敗しました。";
            $systemError[] = $err;
        }

        // その他エラーが発生した場合、終了
        if (isset($systemError)) {
            $_SESSION[self::SES_UPLOADCONDITIONERR] = $systemError;

            if ($enterpriseId == null){
                return $this->_redirect('credit/importform');
            } else {
                return $this->_redirect('credit/importform/eid/' . $enterpriseId );
            }
        }

        // 正常終了
        $this->view->assign("hash", base64_encode(serialize($datas)));
        $this->view->assign("datas", $datas);

        $this->view->assign('EnterpriseId', $enterpriseId);

        return $this->view;
    }

    /**
     * 与信条件CSV　取得CSV内容を指定テンプレートでチェック
     *
     * @param array $datas CSVファイルデータ
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @return array エラーメッセージ配列(正常の場合、null )
     */
    private function checkConditionData($datas, $templateId, $templateClass, $seq, $templatePattern, $enterpriseId )
    {
        // ※この時点でテンプレートマスタとの一致は確定のためそのチェックはなし
        // テンプレートヘッダより、テンプレートSEQ取得
        $mdlTemplateH = new TableTemplateHeader($this->app->dbAdapter);
        $templateSeq = $mdlTemplateH->getTemplateSeq($templateId, $templateClass, $seq, $templatePattern);
        $header = $mdlTemplateH->find($templateSeq)->current();
        // テンプレートフィールドより、項目情報取得
        $mdlTemplateF = new TableTemplateField($this->app->dbAdapter);
        $fields = ResultInterfaceToArray($mdlTemplateF->get($templateSeq));

        $cvp = new CoralValidatePhone();

        $line = 1;
        if ($header['TitleClass'] == 1 || $header['TitleClass'] == 2) $line++;
        unset($csvErrors);
        unset($lineErrors);
        // 取込対象が0件の場合
        // count関数対策
        if (empty($datas)) {
            $errorRow['line'] = 0;
            $errorRow['col'] = 0;
            $errorRow['message'] = "取込対象が存在しません。";
            $csvErrors[] = $errorRow;
        }
        foreach ($datas as $row) {
            // 一行ずつ内容をチェック
            unset($errorRow);

            // 条件Seq 重複チェック
            if (isset($row['Seq']) && strlen($row['Seq']) > 0) {
                if (isset($overlapSeq[$row['Seq']]))
                {
                    $errorRow['line'] = $line;
                    $errorRow['col'] = 0;
                    $errorRow['message'] = "ファイル内で重複した条件Seqがあります。";
                    $csvErrors[] = $errorRow;
                    break;
                }
                else
                {
                    $overlapSeq[$row['Seq']] = 1;
                }
            }

            // テンプレートフィールドによるチェック
            $i = 1;
            foreach($fields as $field) {
                $colData = $row[$field['PhysicalName']];

                // 必須フラグ
                if ($field['ValidFlg'] == 1 && $field['RequiredFlg'] == 1)
                {
                    // 未設定
                    if (!isset($colData) || strlen($colData) == 0) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $field['LineNumber'];
                        $errorRow['message'] = $field['LogicalName'] . "は必須入力です。";
                        break;
                    }
                }

                // 項目番号を保存
                $fieldIdx[$field['PhysicalName']] = $i;
                $i++;
            }

            // エラーがある場合は後続のチェックは行わずに次行へ
            if (isset($errorRow))
            {
                $lineErrors[] = $errorRow;
            }
            else
            {
                // 個別チェック
                unset($condSeq);
                unset($category);

                // 条件Seq
                $colData = $row['Seq'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 正の整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['Seq'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "条件Seq";
                    }
                    else {
                        // 該当する社内与信条件が存在しない場合エラー
                        $condSeq = intval($colData);
                        if ($this->findCreditConditionCnt(array('Seq' => $condSeq)) == 0) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Seq'];
                            $errorRow['message'] = "更新対象の社内与信条件が登録されていません。";
                        }
                    }
                }

                // 条件カテゴリ
                $colData = $row['Category'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['Category'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "条件カテゴリ";
                    }
                    else {
                        // 1,2,3,4,5,8,9でない場合エラー
                        $category = intval($colData);
                        if (!in_array($category, array(1, 2, 3, 4, 5, 8, 9))) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Category'];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . "条件カテゴリ";
                        }
                        // 事業者IDが通知されているとき、1,2,3,4,8,9でない場合エラー
                        if (nvl($enterpriseId, 0) > 0) {
                            if (!in_array($category, array(1, 2, 3, 4, 8, 9))){
                                $errorRow['line'] = $line;
                                $errorRow['col'] = $fieldIdx['Category'];
                                $errorRow['message'] = "次の値がレイアウトにあっていません。" . "条件カテゴリ";
                            }
                        }
                    }
                }

                // 検索方法
                $colData = $row['SearchPattern'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['SearchPattern'];
                        $errorRow['message'] = "次の値がレイアウトにあっていません。" . "検索方法";
                    }
                    else {
                        // 0,1,2,3でない場合エラー
                        $pattern = intval($colData);
                        if (!in_array($pattern, array(0, 1, 2, 3))) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['SearchPattern'];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . "検索方法";
                        }
                    }
                }


                // 条件文字列
                $colData = $row['Cstring'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // ↓↓↓↓↓ 20180406 Add 社内与信条件入力チェック追加 ↓↓↓↓↓
                    // 条件カテゴリ=4(ドメイン)の場合
                    if (isset($category) && $category == 4) {
                        // 英数字記号のみ
                        if(!preg_match("/^[!-~]+$/", $colData)){
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['CString'];
                            $errorRow['message'] =
                            "条件カテゴリがドメインの場合、'条件文字列'は英数字記号のみで入力する必要があります。";
                        }
                    }
                    // 条件カテゴリ=5(事業者ID)の場合
                    if (isset($category) && $category == 5) {
                        // 英数字のみ
                        if(!preg_match("/^[a-zA-Z0-9]+$/", $colData)){
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['CString'];
                            $errorRow['message'] =
                            "条件カテゴリが事業者IDの場合、条件文字列'は英数字10桁で入力する必要があります。";
                        }
                        // 10桁固定
                        else if(strlen ($colData) <> 10){
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['CString'];
                            $errorRow['message'] =
                            "条件カテゴリが事業者IDの場合、条件文字列'は英数字10桁で入力する必要があります。";
                        }
                    }
                    // 条件カテゴリ=8(電話番号)の場合
                    if (isset($category) && $category == 8) {
                        // 電話番号形式チェック
                        //if (!$cvp->isValid($colData)) {
                        if(!preg_match("/^[0-9０-９－\\-]+$/u", $colData)){
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Category'];
                            $errorRow['message'] = "条件文字列が電話番号として正しくありません。";
                        }
                        // 13桁以下
                        else if(mb_strlen ($colData) > 13){
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Category'];
                            $errorRow['message'] = "条件文字列が電話番号として正しくありません。";
                        }
                    }
                    // 条件カテゴリ=9(金額)の場合
                    if (isset($category) && $category == 9) {
                        // 数字のみ
                        if(!preg_match("/^[0-9]+$/", $colData)){
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['CString'];
                            $errorRow['message'] =
                            "条件カテゴリが金額の場合、'条件文字列'は数字のみで入力する必要があります。";
                        }
                    }
                    // ↑↑↑↑↑ 20180406 Add 社内与信条件入力チェック追加 ↑↑↑↑↑
                }

                // ポイント
                $colData = $row['Point'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 整数でない場合エラー
                    if (!$this->isNumWithDecimal($colData, 0, true)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['Point'];
                        $errorRow['message'] = "ポイントは整数で入力する必要があります。";
                    }
                    else {
                        // -10000000～10000000でない場合エラー
                        $point = intval($colData);
                        if ($point < -10000000 || $point > 10000000) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['Point'];
                            $errorRow['message'] = "ポイントの入力値が範囲外です。";
                        }
                    }
                }

                // コメント
                $colData = $row['Comment'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // チェックなし
                }

                // 有効/無効
                $colData = $row['ValidFlg'];
                if (!isset($errorRow) && isset($colData) && strlen($colData) > 0) {
                    // 更新時のみチェック
                    if (isset($condSeq)) {
                        // 整数でない場合エラー
                        if (!$this->isNumWithDecimal($colData, 0, true)) {
                            $errorRow['line'] = $line;
                            $errorRow['col'] = $fieldIdx['ValidFlg'];
                            $errorRow['message'] = "次の値がレイアウトにあっていません。" . "有効/無効";
                        }
                        else {
                            // 0,1でない場合エラー
                            $colInt = intval($colData);
                            if (!in_array($colInt, array(0, 1))) {
                                $errorRow['line'] = $line;
                                $errorRow['col'] = $fieldIdx['ValidFlg'];
                                $errorRow['message'] = "次の値がレイアウトにあっていません。" . "有効/無効";
                            }
                        }
                    }
                }
                elseif (!isset($errorRow)) {
                    // 更新時のみチェック
                    if (isset($condSeq)) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['ValidFlg'];
                        $errorRow['message'] = "有効/無効は必須入力です。";
                    }
                }

                // 条件文字列＋ポイント＋コメントの重複チェック
                if (!isset($errorRow)) {
                    $dups = $this->findDuplicatedConditions($row, $enterpriseId);
                    if($dups->count() > 0) {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = 0;
                        $errorRow['message'] = "重複する与信条件がすでに登録されています。条件文字列、コメント、ポイントのいずれかを変更してください。";
                    }
                }

                // 条件文字列＋ポイント＋コメントの重複チェック(ファイル内)
                if (!isset($errorRow)) {
                    // チェック用のキーを取得
                    $fixRow = $this->fixDataArray($row);
                    $condKey = $fixRow['Category'] . "_" . $fixRow['ComboHash'];
                    if (isset($overlapCond[$condKey]))
                    {
                        $errorRow['line'] = $line;
                        $errorRow['col'] = 0;
                        $errorRow['message'] = "重複する与信条件がすでに登録されています。条件文字列、コメント、ポイントのいずれかを変更してください。";
                        $csvErrors[] = $errorRow;
                        break;
                    }
                    else
                    {
                        $overlapCond[$condKey] = 1;
                    }
                }

                // 加盟店IDチェック
                if ($enterpriseId == -1){
                    $enterpriseId = null;
                }

                if ($this->findCreditConditionCnt(array('Seq' => $row['Seq'])) > 0){
                    if ($this->findCreditConditionCnt(array('Seq' => $row['Seq'], 'EnterpriseId' => $enterpriseId)) == 0){
                        $errorRow['line'] = $line;
                        $errorRow['col'] = $fieldIdx['EnterpriseId'];
                        $errorRow['message'] = "異なる事業者の社内与信条件が指定されています。";
                    }
                }

                // 個別チェックエラーを保存
                if (isset($errorRow))
                {
                    $lineErrors[] = $errorRow;
                }

            }

            // 行数カウント
            $line++;
        }

        // 全体のエラーがある場合、終了
        if (isset($csvErrors)) {
            return $csvErrors;
        }
        // 行ごとのエラーがある場合、終了
        if (isset($lineErrors)) {
            return $lineErrors;
        }

        return null;
    }

    /**
     * 社内与信CSV登録・更新処理
     */
    public function execimportAction()
    {

        $enterpriseId = $this->params()->fromPost('EnterpriseId');

        $params = $this->getParams();

        if (isset($params["save_button"])) {
            // 登録・更新処理を行う

            // 画面情報取得
            $datas = unserialize(base64_decode($params['hash']));

            // 登録・更新
            $this->saveConditionData($datas, $enterpriseId);

            $_SESSION[self::SES_UPLOADCONDITIONUPD] = "updated";

            // 社内与信CSV登録・更新画面へ遷移
            if ($enterpriseId == null){
                return $this->_redirect('credit/importform');
            } else {
                return $this->_redirect('credit/importform/eid/' . $enterpriseId);
            }
        }
        else {
            unset($_SESSION[self::SES_UPLOADCONDITIONUPD]);

            // 社内与信CSV登録・更新画面へ遷移
            if ($enterpriseId == null){
                return $this->_redirect('credit/importform');
            } else {
                return $this->_redirect('credit/importform/eid/' . $enterpriseId);
            }
        }
    }

    /**
     * 社内与信条件CSV　取得CSV内容を保存
     *
     * @param array $datas CSVデータ
     */
    private function saveConditionData($datas, $enterpriseId)
    {
        unset($result);

        $db = Application::getInstance()->dbAdapter;

        // トランザクションの開始
        $db = $this->app->dbAdapter;

        try {
            $db->getDriver()->getConnection()->beginTransaction();

            // ユーザID
            $user = new TableUser($this->app->dbAdapter);
            $userId = $user->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            foreach($datas as $data) {

                if ($data["Seq"] == "") {
                    // 登録
                    // 新規追加用の補完処理を適用
                    $data = $this->fixDataArrayForNew($data);

                    // 正規化を適用
                    $data = $this->fixDataArray($data);

                    // ユーザーIDの取得
                    $data['RegistId'] = $userId;
                    $data['UpdateId'] = $userId;

                    // 加盟店IDの設定
                    if ($enterpriseId != 0){
                        $data['EnterpriseId'] = $enterpriseId;
                    }

                    // DB反映
                    $this->saveFromArray($data);
                }
                else {
                    // 更新
                    // 正規化を適用
                    $data = $this->fixDataArray($data);

                    // ユーザーIDの取得
                    $data['UpdateId'] = $userId;

                    // 加盟店IDの設定
                    if ($enterpriseId != 0){
                        $data['EnterpriseId'] = $enterpriseId;
                    } else {
                        $data['EnterpriseId'] = null;
                    }

                    // DB反映
                    $this->saveFromArray($data);
                }
            }

            // コミット
            $db->getDriver()->getConnection()->commit();
        }
        catch (\Exception $err) {
            // ロールバック
            $db->getDriver()->getConnection()->rollBack();

            $result = $err->getMessage();
        }

        return $result;
    }

    /**
     * 与信条件インポートフォーム（隠し画面）
     */
    public function importform2Action()
    {
        return $this->view;
    }

    /**
     * 与信条件インポート実行（隠し画面）
     * @throws Exception
     */
    public function execimport2Action()
    {
        $message = "";
        $tmpName = $_FILES["ccfile"]["tmp_name"];

        // TODO: kashira - ファイルエンコーディング確認
        //NetB_IO_Utility::convertFileEncoding($tmpName, null, null, true);

        // トランザクションの開始
        $db = $this->app->dbAdapter;

        try
        {
            $handle = fopen($tmpName, "r");

            if (!$handle)
            {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">与信条件ファイルのオープンに失敗しました。</span>';
            }
            else
            {
                $db->getDriver()->getConnection()->beginTransaction();

                // ユーザID
                $user = new TableUser($this->app->dbAdapter);
                $userId = $user->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                while (($data = fgetcsv($handle, 1000, ",")) !== false)
                {
                    $input["Category"] = 1;
                    //$input["Cstring"] = mb_convert_encoding($data[0], "UTF-8", "SJIS-win");
                    $input["Cstring"] = $data[0];
                    $input["Point"] = -1100;
                    $input["Comment"] = "ホテル住所";
                    $input['RegistId'] = $userId;
                    $input['UpdateId'] = $userId;

                    // 検証処理
                    //$validation_results = $mdl->validate($input);
                    $validation_results = new LogicValidationResult();

                    // 重複チェックを実行
                    $validation_results = $this->checkDuplicate($input, $validation_results);

                    // エラーがあるか？
                    if(! $validation_results->isValid())
                    {
                        $message .= "重複," . $input["Cstring"] . "<br/>";
                    }
                    else
                    {
                        // 新規追加用の補完処理を適用
                        $input = $this->fixDataArrayForNew($input);

                        // 正規化を適用
                        $input = $this->fixDataArray($input);

                        // 永続化実行
                        $savedRow = $this->saveFromArray($input);
                    }
                }

                fclose($handle);

                $db->getDriver()->getConnection()->commit();

                $message .= sprintf("与信条件ファイル　「%s」　をインポートしました。", $_FILES["ccfile"]["name"]);
            }
        }
        catch(\Exception $e)
        {
            $message = $e->getMessage();
            $db->getDriver()->getConnection()->rollback();
        }

        $this->view->assign('message', $message);

        return $this->view;
    }

    /**
     * CSVダウンロード
     *
     */
    public function downloadAction()
    {
        $params = $this->getParams();

        // 全件ダウンロード
        unset($params['limit_offset']);

        // 検索条件に従って一覧を取得
        $rs = new ResultSet();
        //TableCreditCondition6テーブル分取得
        $datas = $rs->initialize($this->getCreditCondtionList($params, $params['EnterpriseId']))->toArray();

        // テンプレートヘッダー/フィールドマスタを使用して、CSVを読み込む
        $templateId = 'CKI16135_1';     // テンプレートID       与信条件CSV
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス           区分CBのため0
        $templatePattern = 0;           // テンプレートパターン 区分CBのため0

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'CreditCondition_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * 追加与信条件表示アクション
     */
    public function addAction() {

        $mdlac = new TableAddCreditCondition($this->app->dbAdapter);

        $req = $this->getParams();

        $enterpriseId = (isset($req['eid'])) ? $req['eid'] : -1;

        $seq = (isset($req['seq'])) ? $req['seq'] : -1;

        $category = (isset($req['category'])) ? $req['category'] : -1;

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        // 親条件の取得
        $sql = " SELECT * FROM T_CreditConditionName WHERE Seq = :Seq AND Category = :Category ";
        $sqlName = $sql;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);

        $sql = $sqlName . "UNION" . $sqlAddress . "UNION" . $sqlPhone . "UNION" . $sqlDomain . "UNION" . $sqlItem . "UNION" . $sqlEnterprise . "UNION" . $sqlMoney;

        $data = $this->app->dbAdapter->query($sql)->execute(array(':Seq' => $seq , ':Category' => $category ))->current();

        $this->view->assign("pdata", $data);
        // カテゴリを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        //　検索条件に従って一覧を取得
        $rs = new ResultSet();
        $list = $rs->initialize($mdlac->findAddCondition($seq , $category))->toArray();

        $this->view->assign('result', $list);

        // 表示用のカテゴリを取得
        // カテゴリを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $categoryName = $codeMaster->getCreditCategoryMasterList();
        $patternName = $codeMaster->getCreditSearchPatternMasterList();

        $this->view->assign('categoryName', $categoryName);
        $this->view->assign('patternName', $patternName);

        // 検証エラーは編集画面へ差し戻し
        $this->view->assign('category', $codeMaster->getCreditCategoryCaption($data['Category']));
        $this->view->assign('pattern', $codeMaster->getCreditSearchPatternCaption($data['SearchPattern']));


        // 事業者名・ログインIDの取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);
        $this->view->assign('EnterpriseId', $enterpriseId);
        //
        return $this->view;
    }

    /**
     * 追加与信条件更新/登録
     */
    public function saveaddAction()
    {
        $req = $this->getParams();


        $enterpriseId = (isset($req['eid'])) ? $req['eid'] : -1;

        $pseq = (isset($req['seq']) && is_numeric($req['seq'])) ? $req['seq'] : -1;
        $category = (isset($req['category'])) ? $req['category'] : -1;

        //住所:1
        if($category == 1){
            $sql = " SELECT COUNT(*) AS cnt FROM T_CreditConditionAddress WHERE Seq = :Seq AND ValidFlg = 1 AND Category = :Category ";

        }
        //氏名:2
        if($category == 2){
            $sql = " SELECT COUNT(*) AS cnt FROM T_CreditConditionName WHERE Seq = :Seq AND ValidFlg = 1 AND Category = :Category ";

        }
        //商品:3
        if($category == 3){
            $sql = " SELECT COUNT(*) AS cnt FROM T_CreditConditionItem WHERE Seq = :Seq AND ValidFlg = 1 AND Category = :Category ";

        }
        //ドメイン:4
        if($category == 4){
            $sql = " SELECT COUNT(*) AS cnt FROM T_CreditConditionDomain WHERE Seq = :Seq AND ValidFlg = 1 AND Category = :Category ";

        }
        //加盟店ID:5
        if($category == 5){
            $sql = " SELECT COUNT(*) AS cnt FROM T_CreditConditionEnterprise WHERE Seq = :Seq AND ValidFlg = 1 AND Category = :Category ";

        }
        //電話番号:8
        if($category == 8){
            $sql = " SELECT COUNT(*) AS cnt FROM T_CreditConditionPhone WHERE Seq = :Seq AND ValidFlg = 1 AND Category = :Category ";

        }
        //金額:9
        if($category == 9){
            $sql = " SELECT COUNT(*) AS cnt FROM T_CreditConditionMoney WHERE Seq = :Seq AND ValidFlg = 1 AND Category = :Category ";

        }

        $cnt = (int)$this->app->dbAdapter->query($sql)->execute(array(':Seq' => $pseq , ':Category' => $category))->current()['cnt'];

        if ($cnt == 0 ) {

            return $this->_redirect('credit/add/seq/' . $pseq . '/category/' . $category);
        }

        // 事業者名・ログインIDの取得・アサイン
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("LoginId", $enterpriseData['LoginId']);
        $this->view->assign('EnterpriseId', $enterpriseId);

        $this->view->assign('PSeq', $pseq);
        $this->view->assign('Category', $category);
        //追加条件カウント
        $count = 0;
        //有効件数カウント
        $valcnt = 0;

        for ( $i = 0; $i < 4; $i++ ) {

            if ($req['seq_' . $i] <> '' && $req['cstring_' . $i] == '' && $req['validflg_' . $i] == 0) {
                break;
            }
        }

        for ( $i = 0; $i < 4; $i++ ) {

            $seq = $req['seq_' . $i];
            //未設定画面での追加
            if ($seq == '' && $req['cstring_' . $i] <> '') {

                $input['P_ConditionSeq'] = $pseq;
                $input['P_Category'] = $category;
                $input['Category'] = $req['category_' . $i];
                $input['SearchPattern'] = $req['searchpattern_' . $i];
                $input['Cstring'] = $req['cstring_' . $i];
                $input['ValidFlg'] = (isset($req['validflg_' . $i])) ? $req['validflg_' . $i] : 0;

                $mdlacc = new TableAddCreditCondition($this->app->dbAdapter);

                // 新規追加用の補完処理を適用
                $input = $mdlacc->fixDataArrayForNew($input);

                // 正規化を適用
                $input = $mdlacc->fixDataArray($input);

                // ユーザーIDの取得
                $obj = new \models\Table\TableUser($this->app->dbAdapter);
                $input['RegistId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
                $input['UpdateId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                $input['EnterpriseId'] = $enterpriseId;

                // 永続化実行
                $savedRow = $mdlacc->saveFromArray($input);

                $count += 1;
                if ($input['ValidFlg'] == 1) {
                    $valcnt += 1;
                }
            //○件画面での追加、更新
            } elseif ($seq <> '' && $req['form'][$seq]['Cstring'] <> '') {

                $row['Seq'] = $seq;
                $row['P_Category'] = $category;
                $row['Category'] = $req['form'][$seq]['Category'];
                $row['SearchPattern'] = $req['form'][$seq]['SearchPattern'];
                $row['Cstring'] = $req['form'][$seq]['Cstring'];
                $row['ValidFlg'] = $req['form'][$seq]['delete'];

                $mdlacc = new TableAddCreditCondition($this->app->dbAdapter);

                $obj = new \models\Table\TableUser($this->app->dbAdapter);
                $row['UpdateId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
                $row['EnterpriseId'] = $enterpriseId;

                $list = $mdlacc->fixDataArrayOrg($row);


                 $mdlacc->saveFromArray($list);

                 $count += 1;
                if ($row['ValidFlg'] == 1) {
                    $valcnt += 1;
                }
            }

            if ($count > 0) {

                $data['AddConditionCount'] = $valcnt;

                $this->saveUpdate($data, $pseq, $category);
            }
        }

        return $this->view;
    }

    /**
     * 追加与信情報 登録/変更フォームの内容を検証する
     * @param array $data 登録フォームデータ
     * @return array エラーメッセージの配列
     */
    protected function addvalidate($data = array()) {

        $errors = array();

        //Cstring: 条件文字列
        $Key = 'Cstring';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            if(!isset($data['Seq'])){
                $errors[$Key] = " '条件文字列'は必須入力です。";
            }else{
                $errors[$Key] = "Seq " . $data['Seq'] . " ：'条件文字列'は必須入力です。";
            }
        }
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 4000)){
            $errors[$Key] = "'条件文字列'が長すぎます";
        }

        return $errors;
    }

    /**
     * 指定条件にしたがって与信情報を取得する
     *
     * @return ResultInterface
     */
    public function getCreditCondtionList($expressions, $enterpriseId)
    {
        $prm = array();
        $sql = " SELECT * FROM T_CreditConditionName WHERE ( OrderSeq = -1  ";

        $sql2 = null;
        $prm2 = array();
        $sql2flg = 0;

        // 加盟店ID
        if ($enterpriseId != -1) {
            $sql .= (" AND EnterpriseId = :EnterpriseId ");
            $sql2 .= (" AND EnterpriseId = :EnterpriseId ");
            $prm += array(':EnterpriseId' => $enterpriseId);
            $prm2 += array(':EnterpriseId' => $enterpriseId);
        } else {
            $sql .= (" AND EnterpriseId IS NULL ");
            $sql2 .= (" AND EnterpriseId IS NULL ");
        }

        foreach($expressions as $key => $value) {

            if(is_array($value)) {
                if(! count($value)) continue;
            } else {
                $value = trim(nvl($value, ''));
                if(! strlen($value)) continue;
            }

            switch($key) {
                case 'ValidFlg':    // 有効フラグ(0:無効/1:有効、のときのみ条件に加える)
                    if ($value == 0 || $value == 1) {
                        $sql .= (" AND ValidFlg = :ValidFlg ");
                        $sql2 .= (" AND ValidFlg = :ValidFlg ");
                        $prm += array(':ValidFlg' => $value);
                        $prm2 += array(':ValidFlg' => $value);
                        $sql2flg = 1;
                    }
                    break;
                case 'Category':    // カテゴリ('0' はカテゴリ未指定)    if(! strlen($value)) continue;
                    if($value != 0) {
                        $sql .= (" AND Category = :Category ");
                        $sql2 .= (" AND Category = :Category ");
                        $prm += array(':Category' => $value);
                        $prm2 += array(':Category' => $value);
                        $sql2flg = 1;
                    }
                    break;
                case 'Cstring':     // 条件文字列の部分一致検索
                    $sql2flg = 1;
                    // 選択されたカテゴリに応じて正規化パターンを決定する
                    $map = array(
                    '1' => LogicNormalizer::FILTER_FOR_ADDRESS,
                    '2' => LogicNormalizer::FILTER_FOR_NAME,
                    '3' => LogicNormalizer::FILTER_FOR_ITEM_NAME,
                    '4' => LogicNormalizer::FILTER_FOR_MAIL,
                    '5' => LogicNormalizer::FILTER_FOR_ID,
                    //'6' => LogicNormalizer::FILTER_FOR_ADDRESS,
                    //'7' => LogicNormalizer::FILTER_FOR_ID,
                    '8' => LogicNormalizer::FILTER_FOR_TEL,
                    '9' => LogicNormalizer::FILTER_FOR_MONEY,
                    );
                    if($map[$expressions['Category']]) {
                        $normalizer = LogicNormalizer::create($map[$expressions['Category']]);
                        $sql .= (" AND " . $this->makeLikeExpression('RegCstring', $normalizer->normalize($value)));
                        $sql2 .= (" AND " . $this->makeLikeExpression('RegCstring', $normalizer->normalize($value)));
                    }
                    else {
                        // カテゴリ未指定時はすべてのパターンの正規化を適用してOR検索
                        $w = array();
                        $v_list = array();
                        foreach($map as $key => $const) {
                            $normalizer = LogicNormalizer::create($const);
                            $v = $normalizer->normalize($value);
                            if(strlen($v) && ! in_array(sprintf('[%s]', $v), $v_list)) {
                                $v_list[] = sprintf('[%s]', $v);
                            }
                        }
                        foreach($v_list as $v) {
                            $w[] = sprintf('(%s)', $this->makeLikeExpression('RegCstring', preg_replace('/[\[\]]/u', '', $v)));
                        }
                        $sql .= " AND ( " . join(" OR ", $w) . " ) ";
                        $sql2 .= " AND ( " . join(" OR ", $w) . " ) ";
                    }
                    break;
                case 'Comment':     // コメントの部分一致検索
                    $sql .= (" AND Comment LIKE :Comment ");
                    $prm += array(':Comment' => '%' . $value . '%');
                    break;
                case 'Point':       // スコア
                    $sql .= (" AND Point = :Point ");
                    $prm += array(':Point' => $value);
                    break;
            }
        }

        $db = Application::getInstance()->dbAdapter;
        // 追加社内与信条件を検索
        $addseq = -1;
        //if ($sql2flg == 1) {
     //       $addsql = " SELECT IFNULL(GROUP_CONCAT(P_ConditionSeq), 0) AS AddSeq FROM T_AddCreditCondition WHERE 1 = 1 " . $sql2;
     //       $addseq = $db->query($addsql)->execute($prm2)->current()['AddSeq'];
     //   }
     //   $sql .=  " ) OR Seq IN ( $addseq ) ";
        if ($sql2flg == 1) {
            $addsql = " SELECT P_ConditionSeq , P_Category FROM T_AddCreditCondition WHERE 1 = 1 " . $sql2;
            $addsql .= " GROUP BY P_ConditionSeq, P_Category ";
            $addseq = $db->query($addsql)->execute($prm2);
        }
        $pConditionSeq = 0;
              if($addseq != -1){
            $sqlAddressAddCondition = " ) OR Seq IN ( 0 ";
            $sqlNameAddCondition = " ) OR Seq IN ( 0 ";
            $sqlItemAddCondition = " ) OR Seq IN ( 0 ";
            $sqlDomainAddCondition = " ) OR Seq IN ( 0 ";
            $sqlEnterpriseAddCondition = " ) OR Seq IN ( 0 ";
            $sqlPhoneAddCondition = " ) OR Seq IN ( 0 ";
            $sqlMoneyAddCondition = " ) OR Seq IN ( 0 ";

            foreach ($addseq as $data){

                switch($data['P_Category']){
                    case 1:
                        $sqlAddressAddCondition .= ", " . $data['P_ConditionSeq'];
                        break;
                    case 2:
                        $sqlNameAddCondition .= ", " . $data['P_ConditionSeq'];
                        break;
                    case 3:
                        $sqlItemAddCondition .= ", " . $data['P_ConditionSeq'];
                        break;
                    case 4:
                        $sqlDomainAddCondition .= ", " . $data['P_ConditionSeq'];
                        break;
                    case 5:
                        $sqlEnterpriseAddCondition .= ", " . $data['P_ConditionSeq'];
                        break;
                    case 8:
                        $sqlPhoneAddCondition .= ", " . $data['P_ConditionSeq'];
                        break;
                    case 9:
                        $sqlMoneyAddCondition .= ", " . $data['P_ConditionSeq'];
                        break;
                    default:
                        break;
                }
            }
        }
        $sqlAddressAddCondition .= " ) ";
        $sqlNameAddCondition .= " )  ";
        $sqlItemAddCondition .= " )  ";
        $sqlDomainAddCondition .= " )  ";
        $sqlEnterpriseAddCondition .= " )  ";
        $sqlPhoneAddCondition .= " )  ";
        $sqlMoneyAddCondition .= " )  ";

        $sqlName = $sql . $sqlNameAddCondition;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql) . $sqlAddressAddCondition;
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql) . $sqlPhoneAddCondition;
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql) . $sqlDomainAddCondition;
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql) . $sqlItemAddCondition;
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql) . $sqlEnterpriseAddCondition;
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql) . $sqlMoneyAddCondition;

        $sql = $sqlName . "UNION" . $sqlAddress . "UNION" . $sqlPhone . "UNION" . $sqlDomain . "UNION" . $sqlItem . "UNION" . $sqlEnterprise . "UNION" . $sqlMoney;
        $sql .= " ORDER BY Seq ";

        return $db->query($sql)->execute($prm);
    }

    /**
     * 指定の登録向け連想配列と、カテゴリ・条件文字列・コメント・スコアが一致するデータを
     * 検索する。
     * 連想配列内でキー'Seq'に有効な値が格納されている場合は指定シーケンスを除外する動作となる
     *
     * @param array $data 登録向けの連想配列。重複検出に向けて、内部でfixDataArray()メソッドを経由する
     * @return ResultInterface $combo_hash $combo_hashと同一の結合ハッシュを持つすべてのデータ
     */
    public function findDuplicatedConditions($data, $enterpriseId) {
        $data = $this->fixDataArray($data);
        $cat = (int)$data['Category'];
        $hash = $data['ComboHash'];

        return isset($data['Seq']) ?
        $this->getDuplicatedConditionsByComboHash($cat, $hash, $enterpriseId, (int)$data['Seq']) :
        $this->getDuplicatedConditionsByComboHash($cat, $hash, $enterpriseId);
    }

    /**
     * 永続化向けの連想配列に対し、正規化関連のカラムの値を補完更新する
     *
     * @param array $data 処理対象の連想配列
     * @return array
     */
    public function fixDataArray(array $data) {
        return $this->fixDataArrayOrg($data, true);
    }

	  /**
     * 永続化向けの連想配列に対し、正規化関連のカラムの値を補完更新する
     *
     * @param array $data 処理対象の連想配列
     * @return array
     */
    public function fixDataArrayOrg(array $data, $flg = true) {
        // 選択されたカテゴリに応じて正規化パターンを決定する
        $map = array(
                '1' => LogicNormalizer::FILTER_FOR_ADDRESS,
                '2' => LogicNormalizer::FILTER_FOR_NAME,
                '3' => LogicNormalizer::FILTER_FOR_ITEM_NAME,
                '4' => LogicNormalizer::FILTER_FOR_MAIL,
                '5' => LogicNormalizer::FILTER_FOR_ID,
                //'6' => Logic_Normalizer::FILTER_FOR_ADDRESS,
                //'7' => Logic_Normalizer::FILTER_FOR_ID,
                '8' => LogicNormalizer::FILTER_FOR_TEL,
                '9' => LogicNormalizer::FILTER_FOR_MONEY,
       );
        $key = $map[$data['Category']];

        // カテゴリが不正な場合はエラーとする
        if(empty($key)) throw new \Exception('invalid category specified');

        // 正規化実行
        $data['RegCstring'] =
            LogicNormalizer::create($key)->normalize($data['Cstring']);

		if($flg) {
	        // さらにRegCstringのハッシュ値を作成
	        $data['RegCstringHash'] = md5($data['RegCstring']);

	        // さらに、さらにRegCstring＋Comment＋Scoreのハッシュ値を作成
	        $data['ComboHash'] = $this->createComboHash($data);
		}
        return $data;
    }

    /**
     * 登録用連想配列の内容から結合ハッシュを生成する
     *
     * @param array $data 登録用連想配列
     * @return string 結合条件ハッシュ
     */
    public function createComboHash($data) {
        $fields = array($data['RegCstring'], $data['Comment'], $data['Point']);

        return md5(join('|', $fields));
    }

    /**
     * 条件結合ハッシュを指定して、指定カテゴリ内での重複条件を取得する
     *
     * @param int $category 対象カテゴリ。有効な値を指定しなかった場合は例外が発生する
     * @param string $combo_hash 正規化条件文字列・コメント・スコアの結合から取得した、条件結合ハッシュ
     * @return ResultInterface $combo_hash $combo_hashと同一の結合ハッシュを持つすべてのデータ
     */
    public function getDuplicatedConditionsByComboHash($category, $combo_hash, $enterpriseId, $seq = null) {

        $db = Application::getInstance()->dbAdapter;
        $prm = array();
        if ($seq === null) {
            $where = " ValidFlg = 1 AND Category = :Category AND ComboHash = :ComboHash ";
            $prm = array(
                    ':Category' => $category,
                    ':ComboHash' => $combo_hash,
            );
        }
        else {
            $where = " ValidFlg = 1 AND Category = :Category AND ComboHash = :ComboHash AND Seq <> :Seq ";
            $prm = array(
                    ':Category' => $category,
                    ':ComboHash' => $combo_hash,
                    ':Seq' => $seq,
            );
        }

        if ($enterpriseId == -1 || $enterpriseId == null) {
            $where .= " AND EnterpriseId IS NULL ";
        } else {
            $where .= " AND EnterpriseId = :EnterpriseId ";
            $prm[':EnterpriseId'] = $enterpriseId;
        }

        $sql = " SELECT * FROM T_CreditConditionName WHERE " . $where ;
        $sqlName = $sql;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);

        $sql = $sqlName . "UNION" . $sqlAddress . "UNION" . $sqlPhone . "UNION" . $sqlDomain . "UNION" . $sqlItem . "UNION" . $sqlEnterprise . "UNION" . $sqlMoney;
        $sql .=  " ORDER BY Seq DESC ";
        return $db->query($sql)->execute($prm);
    }

    /**
     * 永続化向けの連想配列に対し、新規インサート向けの初期値補完を適用する
     *
     * @param array $data 補完する連想配列
     * @param int $ent_id 事業者ID
     * @return array
     */
    public function fixDataArrayForNew(array $data) {
        return array_merge($data, array(
                'Seq' => -1,                    // dummy
                'OrderSeq' => -1,               // 手動設定データ
                'RegistDate' => date('Y-m-d'),  // 登録日
                'Class' => -1,                  // 手動設定データ
                'ValidFlg' => 1                 // 有効フラッグ
        ));
    }

    /**
     * カラム名に一致するキーを持つ連想配列のデータを
     * テーブルへ永続化する
     *
     * @param array $data 永続化するデータを格納した連想配列
     * @return ResultInterface 保存された行データ
     */
    public function saveFromArray(array $data) {

        $db = Application::getInstance()->dbAdapter;

        // プライマリキー設定を初期化
        $pkeys = $this->_primary;
        if(! is_array($pkeys)) $primaries = array($pkeys);

        // 入力値からプライマリキー情報を抽出
        $primaries = array();
        foreach((array)$pkeys as $key) {
            if(isset($data[$key])) $primaries[] = $data[$key];
        }

        // プライマリキーが不完全なのでエラー
        $primariesCount = 0;
        if(!empty($primaries)) {
            $primariesCount = count($primaries);
        }
        $pkeysCount = 0;
        if(!empty($pkeys)) {
            $pkeysCount = count($pkeys);
        }
        if($primariesCount != $pkeysCount) {
            throw new \Exception('invalid primary key(s)');
        }

        // プライマリキーに一致するデータの取得を試みる
        $pkval = $data['Seq'];
        $category = $data['Category'];
        $sql = " SELECT * FROM T_CreditConditionName WHERE Seq = :Seq AND Category = :Category ";
        $sqlName = $sql;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);
        $sql = $sqlName . "UNION" . $sqlAddress . "UNION" . $sqlPhone . "UNION" . $sqlDomain . "UNION" . $sqlItem . "UNION" . $sqlEnterprise . "UNION" . $sqlMoney;

        $ri = $db->query($sql)->execute(array(':Seq' => $pkval , ':Category' => $category));

        if ($data['Category'] == 5) {
            $data['SearchPattern'] = 3;
        }

        if ($ri->count() > 0) {
            // UPDATE
            $this->saveUpdate($data, $pkval, $category);
        }
        else {
            // INSERT
            // ユーザーIDの取得
            $pkval = $this->saveNew($data);
        }

        $sql = " SELECT * FROM T_CreditConditionName WHERE Seq = :Seq  AND Category = :Category ";
        $sqlName = $sql;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);
        $sql = $sqlName . "UNION" . $sqlAddress . "UNION" . $sqlPhone . "UNION" . $sqlDomain . "UNION" . $sqlItem . "UNION" . $sqlEnterprise . "UNION" . $sqlMoney;

        return $db->query($sql)->execute(array(':Seq' => $pkval , ':Category' => $category));
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $db = Application::getInstance()->dbAdapter;

       $sql  = " INSERT INTO T_CreditConditionName (OrderSeq, Category, Class, Cstring, CstringHash, RegistDate, ValidFlg, Point, RegCstring, Comment, RegCstringHash, ComboHash, CreditCriterionId, JintecManualReqFlg, EnterpriseId, SearchPattern, AddConditionCount, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :Category ";
        $sql .= " , :Class ";
        $sql .= " , :Cstring ";
        $sql .= " , :CstringHash ";
        $sql .= " , :RegistDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :Point ";
        $sql .= " , :RegCstring ";
        $sql .= " , :Comment ";
        $sql .= " , :RegCstringHash ";
        $sql .= " , :ComboHash ";
        $sql .= " , :CreditCriterionId ";
        $sql .= " , :JintecManualReqFlg ";
        $sql .= " , :EnterpriseId";
        $sql .= " , :SearchPattern";
        $sql .= " , :AddConditionCount";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        switch($data['Category']){
            case 1:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
                break;
            case 3:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
                break;
            case 4:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
                break;
            case 5:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
                break;
            case 8:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
                break;
            case 9:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);
                break;
            default:
                break;
        }
        $stm = $db->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':Category' => $data['Category'],
                ':Class' => $data['Class'],
                ':Cstring' => $data['Cstring'],
                ':CstringHash' => $data['CstringHash'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':Point' => $data['Point'],
                ':RegCstring' => $data['RegCstring'],
                ':Comment' => $data['Comment'],
                ':RegCstringHash' => $data['RegCstringHash'],
                ':ComboHash' => $data['ComboHash'],
                ':CreditCriterionId' => $data['CreditCriterionId'],
                ':JintecManualReqFlg' => isset($data['JintecManualReqFlg']) ? $data['JintecManualReqFlg'] : 0,
                ':EnterpriseId' => intval($data['EnterpriseId']) > 0 ? $data['EnterpriseId'] : null,
                ':SearchPattern' => isset($data['SearchPattern']) ? $data['SearchPattern'] : 0,
                ':AddConditionCount' => isset($data['AddConditionCount']) ? $data['AddConditionCount'] : 0,
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq 更新するSeq
     */
    public function saveUpdate($data, $seq, $category )
    {
        $db = Application::getInstance()->dbAdapter;

        $sql = " SELECT * FROM T_CreditConditionName WHERE Seq = :Seq AND Category = :Category ";
        $sqlName = $sql;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);

        $sql = $sqlName . "UNION" . $sqlAddress . "UNION" . $sqlPhone . "UNION" . $sqlDomain . "UNION" . $sqlItem . "UNION" . $sqlEnterprise  . "UNION" . $sqlMoney;

        $stm = $db->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':Category' => $category
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CreditConditionName ";
        $sql .= " SET ";
        $sql .= "     Seq = :Seq ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   Category = :Category ";
        $sql .= " ,   Class = :Class ";
        $sql .= " ,   Cstring = :Cstring ";
        $sql .= " ,   CstringHash = :CstringHash ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   Point = :Point ";
        $sql .= " ,   RegCstring = :RegCstring ";
        $sql .= " ,   Comment = :Comment ";
        $sql .= " ,   RegCstringHash = :RegCstringHash ";
        $sql .= " ,   ComboHash = :ComboHash ";
        $sql .= " ,   CreditCriterionId = :CreditCriterionId ";
        $sql .= " ,   JintecManualReqFlg = :JintecManualReqFlg ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   SearchPattern = :SearchPattern ";
        $sql .= " ,   AddConditionCount = :AddConditionCount ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";

        $sql .= " WHERE Seq = :Seq ";
        switch($row['Category']){
            case 1:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
                break;
            case 3:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
                break;
            case 4:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
                break;
            case 5:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
                break;
            case 8:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
                break;
            case 9:
                $sql = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);
                break;
            default:
                break;
        }
        $stm = $db->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':Category' => $row['Category'],
                ':Class' => $row['Class'],
                ':Cstring' => $row['Cstring'],
                ':CstringHash' => $row['CstringHash'],
                ':RegistDate' => $row['RegistDate'],
                ':ValidFlg' => $row['ValidFlg'],
                ':Point' => $row['Point'],
                ':RegCstring' => $row['RegCstring'],
                ':Comment' => $row['Comment'],
                ':RegCstringHash' => $row['RegCstringHash'],
                ':ComboHash' => $row['ComboHash'],
                ':CreditCriterionId' => $row['CreditCriterionId'],
                ':JintecManualReqFlg' => $row['JintecManualReqFlg'],
                ':EnterpriseId' => intval($row['EnterpriseId']) > 0 ? $row['EnterpriseId'] : null,
                ':SearchPattern' => intval($row['SearchPattern']) > 0 ? $row['SearchPattern'] : 0,
                ':AddConditionCount' => intval($row['AddConditionCount']) > 0 ? $row['AddConditionCount'] : 0,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId']
        );
        return $stm->execute($prm);
    }

    /**
     * 部分一致検索用のLIKE句を構築する
     *
     * @param string $field カラム名
     * @param mixed $value 検索値
     * @return string
     */
    public function makeLikeExpression($field, $value) {
        return $field . " LIKE '%" . self::escapeWildcard($value) . "%' ";
    }

    /**
     * MySQLでLIKEを発行できるよう入力文字列をエスケープする
     * エスケープする内容は通常のZend_Db_Adapter_Abstract::quote()とは以下の点が異なる。
     * ・ワイルドカード文字（%および_）もバックスラッシュエスケープする
     * ・バックスラッシュ自体は通常の2重バックスラッシュではなく4重バックスラッシュにエスケープする
     * ・（quoteではないので）前後に引用符は付加しない
     * @param string $s
     * @return string
     */
    public static function escapeWildcard($s) {
        // 事前にバックスラッシュを2重化してからaddcslashesを行う
        return addcslashes(str_replace("\\", "\\\\", $s), "\000\r\n\\'\"\032%_");
    }

    /**
     * 指定条件（AND）の与信条件データの件数を取得する。
     *
     * @param array $conditionArray 検索条件を格納した連想配列
     * @return int 件数
     */
    public function findCreditConditionCnt($conditionArray)
    {
        $db = Application::getInstance()->dbAdapter;

        $prm = array();
        $sql  = " SELECT COUNT(Seq) AS Cnt FROM ( ";
        $sql2  = " SELECT Seq FROM T_CreditConditionName WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {

            if ($value == null){
                $sql2 .= (" AND " . $key . " IS NULL ");
            } else {
                $sql2 .= (" AND " . $key . " = :" . $key);
                $prm += array(':' . $key => $value);
            }
        }
        $sqlName = $sql2;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql2);
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql2);
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql2);
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql2);
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql2);
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql2);
        $sql = $sql . $sqlName . " UNION " . $sqlAddress . " UNION " . $sqlPhone . " UNION " . $sqlDomain . " UNION " . $sqlItem . " UNION " . $sqlEnterprise . " UNION " . $sqlMoney;
        $sql .= " ) AS tmp GROUP BY Seq ";

        $stm = $db->query($sql);

        return (int)$stm->execute($prm)->current()['Cnt'];
    }

    /**
     * 指定条件（AND）の与信条件データを取得する。
     *
     * @param array $conditionArray 検索条件を格納した連想配列
     * @return ResultInterface
     */
    public function findCreditCondition($conditionArray)
    {
        $db = Application::getInstance()->dbAdapter;
        $prm = array();
        $sql  = " SELECT * FROM T_CreditConditionName WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sqlName = $sql;
        $sqlAddress = str_replace('T_CreditConditionName','T_CreditConditionAddress',$sql);
        $sqlPhone = str_replace('T_CreditConditionName','T_CreditConditionPhone',$sql);
        $sqlDomain = str_replace('T_CreditConditionName','T_CreditConditionDomain',$sql);
        $sqlItem = str_replace('T_CreditConditionName','T_CreditConditionItem',$sql);
        $sqlEnterprise = str_replace('T_CreditConditionName','T_CreditConditionEnterprise',$sql);
        $sqlMoney = str_replace('T_CreditConditionName','T_CreditConditionMoney',$sql);
        $sql = $sqlName . " UNION " . $sqlAddress . " UNION " . $sqlPhone . " UNION " . $sqlDomain . " UNION " . $sqlItem . " UNION " . $sqlEnterprise . " UNION " . $sqlMoney;

        $stm = $db->query($sql);

        return $stm->execute($prm);
    }

}
