<?php
namespace member\Controller;

use member\Application;
use models\Logic\LogicSbps;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\Table\TableGeneralPurpose;
use models\Table\TableApiUserEnterprise;
use models\Table\TableSelfBillingProperty;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use models\Table\TableUser;
use models\Table\TablePasswordHistory;
use models\Table\TableSystemProperty;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use Coral\Base\BaseHtmlUtils;
use Zend\Config\Reader\Ini;
use Zend\Session\Container;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableCode;
use models\Table\TablePricePlan;
use models\Table\TablePayingCycle;
use Coral\Coral\Validate\CoralValidateMultiMail;
use Coral\Coral\Validate\CoralValidatePhone;
use Coral\Coral\CoralValidate;
use models\Table\TableEnterpriseOperator;
use models\Logic\LogicCampaign;
use member\classes\SearchUtility;
use models\Table\TableBatchLock;
use models\Table\TableSiteSbpsPayment;
use models\Table\TablePayment;
use models\Table\TableSitePayment;
use models\Table\TableSbpsPayment;

class AccountController extends CoralControllerAction {
    const POST_DATA_SESSIONNAME = 'modifiedParameters';

    const VALIDATE_ERRORS = 'validateErrors';

    /**
     * バッチID
     * @var int
     */
    const EXECUTE_BATCH_ID = 3;

    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var TableEnterprise
     */
    private $_entTable;

    /**
     * 変更対象項目のスキーマ
     *
     * @var array
     */
    private $_validators;

    protected function _init() {
        $this->app = Application::getInstance();

        $this->addStyleSheet( './css/members.css' );
        $this->addStyleSheet( './css/index.css' );
        $this->addStyleSheet( './css/tab_support.css' );
        $this->addStyleSheet( './css/account.css' );
        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/bytefx.js' );
        $this->addJavaScript( '../js/corelib.js' );
        $this->addJavaScript( '../js/base.ui.js' );

        // メニュー情報をマスタから取得
        $menu_info = $this->app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
        }

        $this->_entTable = new TableEnterprise( $this->app->dbAdapter );

        // 変更可能項目の検証ルールリスト
        $this->_validators = array(
            'CpNameKj' => array(
                'label' => 'ご担当氏名',
                'require' => true,
                'size' => 20,
                'expression' => '/^.+$/',
                'text' => null
            ),
            'CpNameKn' => array(
                'label' => 'ご担当カナ氏名',
                'require' => true,
                'size' => 25,
                'expression' => '/^.+$/',
                'text' => null
            ),
            'DivisionName' => array(
                'label' => 'ご担当部署名',
                'require' => false,
                'size' => 20,
                'expression' => '/^.*$/',
                'text' => null
            ),
            'MailAddress' => array(
                'label' => 'メールアドレス',
                'type' => 'mail_addr',
                'require' => true,
                'size' => 40,
                'expression' => CoralValidateUtility::EMAIL_ADDRESS,
                'text' => '※ このメールアドレスに与信結果や立替情報が届きます。'
            ),
            'ContactPhoneNumber' => array(
                'label' => '連絡先電話番号',
                'type' => 'phone_number',
                'require' => true,
                'size' => 20,
                'expression' => CoralValidateUtility::PHONE_NUMBER,
                'text' => '※ この電話番号はご購入者様宛てに発行される請求書に印字されます。'
            ),
            'ContactFaxNumber' => array(
                'label' => '連絡先FAX番号',
                'type' => 'phone_number',
                'require' => false,
                'size' => 20,
                'expression' => CoralValidateUtility::PHONE_NUMBER,
                'text' => null
            )
        );

        $this->view->assign('validators', $this->_validators);
        $this->view->assign('userInfo', $this->app->authManager->getUserInfo());
        $this->view->assign('altUserInfo', $this->app->authManager->getAlternativeUserInfo());
    }

    public function indexAction() {
        // タイトルの設定
        $this->setPageTitle( '登録情報管理' )
                ->addStyleSheet( '../css/base.ui.modaldialog.css' )
                ->addJavaScript( '../js/base.ui.modaldialog.js' );

        // セッションストレージの取得
        $session = $this->getSessionStorage();

        // セッションにデータが存在していれば、セッションデータを取得する。
        if (isset( $session->{self::POST_DATA_SESSIONNAME})) {
            $params = $session->{self::POST_DATA_SESSIONNAME};
        }

        // アカウントデータの取得
        $entData = $this->_entTable->findEnterprise( $this->app->authManager->getUserInfo()->EnterpriseId )->current();

        // コンボボックスの設定
        $dispOrder1 = isset($params['DispOrder1']) ? $params['DispOrder1'] : $entData['DispOrder1'];
        $dispOrder2 = isset($params['DispOrder2']) ? $params['DispOrder2'] : $entData['DispOrder2'];
        $dispOrder3 = isset($params['DispOrder3']) ? $params['DispOrder3'] : $entData['DispOrder3'];
        $claimOrder1 = isset($params['ClaimOrder1']) ? $params['ClaimOrder1'] : $entData['ClaimOrder1'];
        $claimOrder2 = isset($params['ClaimOrder2']) ? $params['ClaimOrder2'] : $entData['ClaimOrder2'];

        // ラジオボタンの設定
        $journalRegist = isset($params['JournalRegistDispClass']) ? $params['JournalRegistDispClass'] : $entData['JournalRegistDispClass'];
        // 与信NG期間の設定
        $creditNgDays = isset($params['CreditNgDispDays']) ? $params['CreditNgDispDays'] : $entData['CreditNgDispDays'];

        //表示件数0だった場合50に設定する
        if($entData['DisplayCount'] == 0){
            $entData['DisplayCount'] = 50;
        }
        // 履歴検索時の表示件数設定
        $displayCount = isset($params['DisplayCount']) ? $params['DisplayCount'] : $entData['DisplayCount'];

        // 検証エラー情報の取得
        if( isset( $session->{self::VALIDATE_ERRORS} ) ) {
            $this->view->assign('errors', $session->{self::VALIDATE_ERRORS});
            unset( $session->{self::VALIDATE_ERRORS} );
        }

        // モードの判別
        $mode = strtolower($this->params()->fromRoute('mode',''));

        if($mode == 'error') $mode = 'rollback';
        $isRollback = $mode == 'rollback';
        $isReset = $mode == 'reset';        // 変更をリセットタブ押下の場合
        $defaultTab = 0;

        // 編集データにアサインする連想配列を構築
        $editData = array();
        if( isset( $session->{self::POST_DATA_SESSIONNAME} ) && $isRollback ) {
            $editData = $session->{self::POST_DATA_SESSIONNAME};
            $defaultTab = 2;
        } else {
            $editData = array(
                'ChangePassword' => '',
                'CurrentPassword' => '',
                'NewPassword' => '',
                'NewPassword2' => '',
                'CpNameKj' => $entData['CpNameKj'],
                'CpNameKn' => $entData['CpNameKn'],
                'DivisionName' => $entData['DivisionName'],
                'MailAddress' => $entData['MailAddress'],
                'ContactPhoneNumber' => $entData['ContactPhoneNumber'],
                'ContactFaxNumber' => $entData['ContactFaxNumber']
            );

            // セッションデータは削除
            if( isset( $session->{self::POST_DATA_SESSIONNAME} ) ) {
                unset( $session->{self::POST_DATA_SESSIONNAME} );
            }

            // 初期タブの設定
            // 変更リセットボタンが押下された場合、「設定変更」タブが初期タブ
            if ($isReset) {
                $defaultTab = 2;
            }
        }

        // アカウントデータをビューへアサイン
        $this->view->assign( 'editData', $editData );
        $this->view->assign( 'tabIndex', $defaultTab );

        // サイトのデータを取得
        $siteTable = new TableSite( $this->app->dbAdapter);
        $siteList = $siteTable->getAll( $this->app->authManager->getUserInfo()->EnterpriseId );
        $rs = new ResultSet();
        $siteListAry = $rs->initialize($siteList)->toArray();

        $logic = new LogicCampaign($this->app->dbAdapter);
        $cdata = $logic->getCampaignInfo($this->app->authManager->getUserInfo()->EnterpriseId, $siteListAry[0]['SiteId']);
        // キャンペーンのデータとマージする
        $entData = array_merge($entData, $cdata);

        // マージした配列でビューへアサインする
        $this->view->assign( 'entInfo', $entData );

        // コードマスタのデータを取得
        $masterTable = new TableCode( $this->app->dbAdapter );
        $masters = array();
        foreach( array(
            'PreSales' => 55,
            'Industry' => 54,
            'FfAccountClass' => 51,
            'TcClass' => 56,
            'SiteForm' => 10
        ) as $key => $class ) {
            $master = array();
            foreach( $masterTable->getMasterByClass($class) as $row ) {
                $master[ $row['KeyCode'] ] = "{$row['KeyContent']}";
            }
            $masters[ $key ] = $master;
        }

        // サイトに紐づくAPIユーザデータの取得
        $apiRels = new TableApiUserEnterprise( $this->app->dbAdapter );
        $siteApiUsers = array();
        foreach ($siteListAry as $key => $siteId) {
            $api_user_ids = array();
            $rows = $apiRels->findRelatedApiUsers( $siteId['SiteId'] );
            if($rows) {
                foreach($rows as $keys => $row) {
                    $api_user_ids[$keys] = "{$row['ApiUserId']}";
                }
            }
            $siteApiUsers[ $siteId['SiteId']  ] = $api_user_ids;
        }

        // 料金プランデータの取得
        $planTable = new TablePricePlan( $this->app->dbAdapter );
        $plan = $planTable->find($entData['Plan'])->current();

        // 立替サイクルデータの取得
        $payingTable = new TablePayingCycle( $this->app->dbAdapter );
        $paying = $payingTable->find($entData['PayingCycleId'])->current();

        // コンボボックスの内容を設定
        $dispOrderList = array(
            -1 => '',
            0 => '注文ID',
            1 => '購入者名',
            2 => '任意注文番号',
            3 => '注文日',
        );

        $claimOrderList = array(
                -1 => '',
                0 => '注文ID',
                1 => '別配送先',
                2 => '印刷状態',
                3 => '同梱/別送',
                4 => '任意注文番号',
                5 => '注文日',
                6 => '注文登録日',
                7 => '請求先氏名',
                8 => '請求先住所',
                9 => '利用額',
        );
        $OrderList = array(
                -1 => '',
                0 => '昇順',
                1 => '降順',
        );


        foreach ($siteListAry as $key => $siteId) {
            if ($entData['OemId'] == 0 and $siteId['ReceiptAgentId'] == 2 and $siteId['MufjBarcodeUsedFlg'] == 0) {
                $siteId['displayBtnFlag'] = $this->checkDisplayBtnFlag($siteId['SiteId']);
            } else {
                $siteId['displayBtnFlag'] = 0;
            }
            $siteListAry[$key] = $siteId;
        }

        // 取得したデータをビューへアサインする。
        $this->view->assign( 'siteList', $siteListAry );
        $this->view->assign( 'entData', $entData );
        $this->view->assign( 'masters', $masters );
        $this->view->assign( 'api_user_ids', $siteApiUsers );
        $this->view->assign('plan', $plan);
        $this->view->assign('paying', $paying);

        // 設定したコンボボックスをアサインする
        $this->view->assign("dispOrder1Tag",BaseHtmlUtils::SelectTag('DispOrder1', $dispOrderList, $dispOrder1));
        $this->view->assign("dispOrder2Tag",BaseHtmlUtils::SelectTag('DispOrder2', $dispOrderList, $dispOrder2));
        $this->view->assign("dispOrder3Tag",BaseHtmlUtils::SelectTag('DispOrder3', $dispOrderList, $dispOrder3));
        $this->view->assign("claimOrder1Tag",BaseHtmlUtils::SelectTag('ClaimOrder1', $claimOrderList, $claimOrder1));
        $this->view->assign("claimOrder2Tag",BaseHtmlUtils::SelectTag('ClaimOrder2', $OrderList, $claimOrder2));
        // ラジオボタンの初期値をアサインする
        $this->view->assign("journalRegist", $journalRegist);
        // 与信NG期間の初期値をアサインする
        $this->view->assign("creditNgDays", $creditNgDays);
        // 履歴検索時の表示件数設定の初期値をアサインする
        $this->view->assign("displayCount", $displayCount);
        // CSVテンプレート用の値をアサインする
        $this->view->assign("templateid", SearchUtility::TEMPLATE_ID);
        $this->view->assign("templateclass", SearchUtility::TEMPLATE_CLASS);

        return $this->view;
    }

    public function checkDisplayBtnFlag($sid) {
        $flagSbps = 0;
        $mdlSite = new TableSite($this->app->dbAdapter);
        $site = $mdlSite->findSite($sid)->current();

        if ($site['PaymentAfterArrivalFlg'] == 1) {
            $tblSbpsPayment = new TableSiteSbpsPayment($this->app->dbAdapter);
            $sitePayments = ResultInterfaceToArray($tblSbpsPayment->getAll($sid));
            if ($sitePayments) {
                $flagSbps = 1;
            }
        }

        $mdlSitePayment = new TableSitePayment($this->app->dbAdapter);
        $site_datas = ResultInterfaceToArray($mdlSitePayment->getAll($sid));
        $flag = 0;
        foreach ($site_datas as $val) {
            if ($val['UseStartFixFlg'] != 0 && $val['UseFlg'] != 0) {
                $flag = 1;
                break;
            }
        }

        if ($flag == 1 or $flagSbps == 1) {
            return 1;
        }

        return 0;
    }

    /**
     * Get Sbps Setting by Site ID
     */
    public function getsbpssettingAction() {
        // get site info
        $sid = $this->params()->fromPost( 'sid', '' );
        $mdlSite = new TableSite($this->app->dbAdapter);
        $site = $mdlSite->findSite($sid)->current();
        
        // get sbps payment methods
        $tblSbpsPayment = new TableSbpsPayment($this->app->dbAdapter);
        $sbpsPayments = array();
        if ($site['PaymentAfterArrivalFlg'] == 1) { // only show if PaymentAfterArrivalFlg = 1
            foreach ($tblSbpsPayment->getList() as $row){
                $sbpsPayments[$row['SbpsPaymentId']] = array(
                    'PaymentId' => $row['SbpsPaymentId'],
                    'PaymentName' => $row['PaymentNameKj']
                );
            }
        }

        // get sbps payment by Site Id
        $sitePaymentsData = array();
        $tblSbpsPayment = new TableSiteSbpsPayment($this->app->dbAdapter);
        $sitePayments = ResultInterfaceToArray($tblSbpsPayment->getAll($sid));
        if ($sitePayments) {
            foreach ($sitePayments as $sitePayment) {
                //if (is_null($sitePayment['UseStartDate']) || $sitePayment['UseStartDate'] > date('Y-m-d H:i:s')) continue;
                $sitePaymentsData[] = $sitePayment;
            }
        }

        // get min claim date
        $sql = "SELECT MIN(ch.ClaimDate) AS MinClaimDate FROM T_ClaimHistory ch, T_Order ord WHERE ch.OrderSeq = ord.OrderSeq AND ch.ClaimPattern = 1";
//        $minClaimDate = $this->app->dbAdapter->query($sql)->execute()->current()['MinClaimDate'];
        $minClaimDate = '2007-10-31';

        // get none-sbps payments
        $mdlPayment = new TablePayment($this->app->dbAdapter);
        $mdlSitePayment = new TableSitePayment($this->app->dbAdapter);
        $oid = $this->app->authManager->getUserInfo()->OemId;
        if (is_null($oid)) {
            $oid = 0;
        }
        $payments = ResultInterfaceToArray($mdlPayment->fetchAllSubscriberCode($oid));
        $site_datas = ResultInterfaceToArray($mdlSitePayment->getAll($sid));
        $datas = array();
        // 支払方法を表示用に加工
        foreach ($payments as $payment) {
            $paymentId = $payment['PaymentId'];
            $datas[$paymentId]['PaymentGroupName'] = $payment['PaymentGroupName'];
            $datas[$paymentId]['PaymentName'] = $payment['PaymentName'];
        }
        foreach ($site_datas as $val) {
            $paymentId = $val['PaymentId'];
            if ($val['UseStartFixFlg'] != 1 or $val['UseFlg'] != 1) {
                unset($datas[$paymentId]);
            }
            if (isset($datas[$paymentId])) {
                $datas[$paymentId]['UseFlg'] = $val['UseFlg'];
                $datas[$paymentId]['UseStartDate'] = $val['UseStartDate'];
            }
        }

        $query = " SELECT SubscriberName,LinePayUseFlg,LineApplyDate,LineUseStartDate,RakutenBankUseFlg,FamiPayUseFlg FROM M_SubscriberCode WHERE ValidFlg= 1 AND ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
        $stm = $this->app->dbAdapter->query($query);

        $prm = array(
            ':ReceiptAgentId' => $site['ReceiptAgentId'],
            ':SubscriberCode' => $site['SubscriberCode'],
        );

        $tmp = $stm->execute($prm)->current();
        if (!$tmp) {
            $datas = array();
        }

        $this->view->assign('site', $site);
        $this->view->assign('sbpsPayments', $sbpsPayments);
        $this->view->assign('sitePaymentsData', $sitePaymentsData);
        $this->view->assign('minClaimDate', $minClaimDate);
        $this->view->assign('payments', $datas);
        $this->setTemplate('sbps_payments_table');
        return $this->view;
    }

    public function confirmAction() {
        $postData = array();
        $postData = $this->params()->fromPost();
        foreach( $postData as $key => $value ) {
            $postData[ trim($key) ] = trim($value);
        }

        // 表示順対応
        if (($postData["DispOrder1"] == $postData["DispOrder2"]) && ($postData["DispOrder2"] == $postData["DispOrder3"])){
            $postData["DispOrder2"] = '-1';
            $postData["DispOrder3"] = '-1';
        } else if ($postData["DispOrder1"] == $postData["DispOrder2"]) {
            $postData["DispOrder2"] = '-1';
        } else if ($postData["DispOrder1"] == $postData["DispOrder3"]) {
            $postData["DispOrder3"] = '-1';
        } else if ($postData["DispOrder2"] == $postData["DispOrder3"]) {
            $postData["DispOrder3"] = '-1';
        }

        // 検証実行
        $errors = array();

        $errors = $this->validate($postData);

        // セッションへ保存
        $session = $this->getSessionStorage();
        $session->{self::POST_DATA_SESSIONNAME} = $postData;

        if(!empty($errors)) {

            // 検証エラーは入力画面へ戻す
            $session->{self::VALIDATE_ERRORS} = $errors;

            return $this->_redirect('account/index/mode/error');
        }

        $this->view->assign('postData', $postData);
        return $this->view;
    }

    public function saveAction() {
        $session = $this->getSessionStorage();

        // バッチ排他制御
        $mdlbl = new TableBatchLock (Application::getInstance()->dbAdapter);
        $BatchLock = $mdlbl->findBatchId($this::EXECUTE_BATCH_ID)['BatchLock'];
        $key = 'BatchLock';
        if ($BatchLock > 0) {
            $errors[$key] = array("現在、設定変更処理を行うことができません。しばらくたってから再度実行をお願い致します。");

            // 検証エラーは入力画面へ戻す
            $session->{self::VALIDATE_ERRORS} = $errors;

            return $this->_redirect('account/index/mode/error');
        }

        $postData = $session->{self::POST_DATA_SESSIONNAME};

        // アカウント行データを取得
        $entTable = new TableEnterprise( $this->app->dbAdapter );
        $entRow = $entTable->findEnterprise( $this->app->authManager->getUserInfo()->EnterpriseId )->current();

        $mdlph = new TablePasswordHistory($this->app->dbAdapter);
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);

        //パスワード期限切れ日数
        $sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitDay');

        // ユーザーIDの取得
        $mdlu = new TableUser( $this->app->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $mdlu->getUserId( $userClass, $seq );

        // パスワードの変更
        isset($postData['ChangePassword']) ? $changePassword = $postData['ChangePassword'] : $changePassword = '';

        if($changePassword) {
            // 加盟店登録時のログインIDか判定する。
            if ($entRow['LoginId'] != $this->app->authManager->getUserInfo()->LoginId) {
                // 追加分のオペレーターでログインしている場合、ログインしているオペレーターのパスワードを変更する。
                $mdleo = new TableEnterpriseOperator($this->app->dbAdapter);
                // 加盟店オペレータIDを取得
                $enterpriseOpId = $mdleo->findEnterpriseOpId($this->app->authManager->getUserInfo()->LoginId);

                $authUtil = $this->app->getAuthUtility();
                $entOp = array(
                    'LoginPasswd' => $authUtil->generatePasswordHash($this->app->authManager->getUserInfo()->LoginId, trim($postData['NewPassword'])),
                    'Hashed' => 1,
                    'UpdateId' => $userId,
                    'LastPasswordChanged' => date('Y-m-d H:i:s')
                );

                // 加盟店オペレーターテーブルの更新
                $mdleo->saveUpdate($entOp, $enterpriseOpId['EnterpriseOpId']);

                $passChgData = array(
                        'Category' => 2,
                        'LoginId' => $this->app->authManager->getUserInfo()->LoginId,
                        'LoginPasswd' => $entOp['LoginPasswd'],
                        'PasswdStartDay' => date('Y-m-d'),
                        'PasswdLimitDay' => date('Y-m-d', strtotime("+$sysTimeLimit days")),
                        'Hashed' => 1,
                        'RegistDate' => date('Y-m-d H:i:s'),
                        'RegistId' => $userId,
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $userId,
                        'ValidFlg' => 1);

                // パスワード履歴テーブルに1件追加する
                $mdlph->saveNew($passChgData);


            } else {
                $authUtil = $this->app->getAuthUtility();
                $entRow['LoginPasswd'] = $authUtil->generatePasswordHash($entRow['LoginId'], trim($postData['NewPassword']));
                $entRow['Hashed'] = 1;
                $entRow['UpdateId'] = $userId;
                $entRow['LastPasswordChanged'] = date('Y-m-d H:i:s');

                $passChgData = array(
                        'Category' => 2,
                        'LoginId' => $entRow['LoginId'],
                        'LoginPasswd' => $entRow['LoginPasswd'],
                        'PasswdStartDay' => date('Y-m-d'),
                        'PasswdLimitDay' => date('Y-m-d', strtotime("+$sysTimeLimit days")),
                        'Hashed' => 1,
                        'RegistDate' => date('Y-m-d H:i:s'),
                        'RegistId' => $userId,
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $userId,
                        'ValidFlg' => 1);

                // パスワード履歴テーブルに1件追加する
                $mdlph->saveNew($passChgData);
            }
        }

        // パスワード以外のデータの更新
        foreach( array(
            'CpNameKj', 'CpNameKn', 'DivisionName', 'MailAddress', 'ContactPhoneNumber', 'ContactFaxNumber',
            'CreditNgDispDays', 'JournalRegistDispClass', 'DispOrder1', 'DispOrder2', 'DispOrder3', 'ClaimOrder1', 'ClaimOrder2', 'DisplayCount'
        ) as $key ) {
            $entRow[$key] = $postData[$key];
        }

        $entRow['UpdateId'] = $userId;

        // DB更新
        $entTable->saveUpdate($entRow, $this->app->authManager->getUserInfo()->EnterpriseId );

        // セッションデータは削除
        if( isset( $session->{self::POST_DATA_SESSIONNAME} ) ) {
            unset( $session->{self::POST_DATA_SESSIONNAME} );
        }
        if( isset( $session->{self::VALIDATE_ERRORS} ) ) {
            unset( $session->{self::VALIDATE_ERRORS} );
        }

        // パスワードを変更していたら内部でログインしなおす
        if( $changePassword ) {
            $authMgr = $this->app->authManager;

            // ログイン状態をリセット
            $authMgr->resetLoginState();
        }

        try {
            $logicSbps = new LogicSbps($this->app->dbAdapter);
            $mdlcd = new TableCode($this->app->dbAdapter);
            $flag = $logicSbps->checkHasPaymentAfterArrivalFlg($this->app->authManager->getUserInfo()->EnterpriseId, 'T_Site');
            if ($flag) {
                $toAddress = $mdlcd->getMasterAssCode(213, 1);
            } else {
                $toAddress = $mdlcd->getMasterAssCode(213, 0);
            }
            CoralMail::create( $this->app->dbAdapter, $this->app->smtpServer )
                ->SendModifyEntMail(
                    $this->app->authManager->getUserInfo()->EnterpriseId,
                    $userId,
                    $toAddress
                );
        } catch(CoralMailException $err) {
            // CoralMail内の例外のみ捕捉
        }

        return $this->_redirect( 'account/index' );
    }

    public function changecsvAction() {
        $this->addStyleSheet( './css/column_table.css' );
        $this->addJavaScript( './js/column_table.js' );

        $params = $this->getParams();

        $tId = $params['tid'];
        $tClass = $params['tclass'];
        $eId = $params['eid'];
        $sId = $params['sid'];

        $mdlth = new TableTemplateHeader( $this->app->dbAdapter );
        $mdltf = new TableTemplateField( $this->app->dbAdapter );
        $mdle = new TableEnterprise( $this->app->dbAdapter );
        $ent = $mdle->find($eId)->current();

        // ユーザーIDの取得
        $mdlu = new TableUser( $this->app->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $mdlu->getUserId( $userClass, $seq );

        // 該当のテンプレート取得
        $templateSeq = $mdlth->getTemplateSeq( $tId, $tClass, $eId, $sId, false );

        // 該当のテンプレートがなかった場合
        if( empty( $templateSeq ) ) {
            while( 1 ) {
                // SiteIdのテンプレートが存在しない場合、加盟店デフォルトで取得
                $templateSeq = $mdlth->getTemplateSeq( $tId, $tClass, $eId, 0, false );
                if( !empty( $templateSeq ) ) {
                    break;
                }
                // 加盟店デフォルトのテンプレートが存在しない場合、OEMIDで取得
                if( is_null( $this->app->authManager->getUserInfo()->OemId ) ) $oemId = 0;
                $templateSeq = $mdlth->getTemplateSeq( $tId, 1, $oemId, 0, false );
                if( !empty( $templateSeq ) ) {
                    break;
                }
                // OEMIDのテンプレートが存在しない場合、CBで取得
                $templateSeq = $mdlth->getTemplateSeq( $tId, 0, 0, 0, false );
                break;
            }
            if( !empty( $templateSeq ) ) {
                // 新しくテンプレート作成
                $header = $mdlth->find( $templateSeq )->current();
                $header['TemplateClass'] = $tClass;
                $header['Seq'] = $eId;
                $header['TemplatePattern'] = $sId;
                $header['RegistId'] = $userId;
                $header['UpdateId'] = $userId;
                $newTemplateSeq = $mdlth->saveNew( $header );

                $fields = ResultInterfaceToArray( $mdltf->get( $templateSeq ) );
                foreach( $fields as $field ) {
                    $field['TemplateSeq'] = $newTemplateSeq;
                    $field['RegistId'] = $userId;
                    $field['UpdateId'] = $userId;
                    $mdltf->saveNew( $field );
                }

                $templateSeq = $newTemplateSeq;
            }
            else {
                throw new \Exception( 'テンプレートが存在しません。' );
            }
        }

        $templateName = $mdlth->find( $templateSeq )->current()['TemplateName'];

        // ListNumber順にTemplateFieldを取り出す
        $templateFieldList = ResultInterfaceToArray( $mdltf->get( $templateSeq ) );

        $validList = array();
        $invalidList = array();

        // サイトのデータを取得
        $siteTable = new TableSite( $this->app->dbAdapter);
        $site = $siteTable->findSite($sId)->current();
        foreach( $templateFieldList as $templateField ) {
            if ( $tId == 'CKA01005_1' && $ent['ReceiptStatusSearchClass'] == '0' &&
                ($templateField['PhysicalName'] == 'IsWaitForReceipt' || $templateField['PhysicalName'] == 'ReceiptDate' || $templateField['PhysicalName'] == 'ReceiptClass' || $templateField['PhysicalName'] == 'ReceiptProcessDate')) {
                // 入金ステータス検索条件区分 0:不可 の場合、入金状態・入金日は設定不可
                continue;
            }
            if ( $tId == 'CKA01005_1' && $site['PaymentAfterArrivalFlg'] == 0 && $templateField['PhysicalName'] == 'ExtraPayKey') {
                // サイト.届いてから払い利用フラグ 0：利用しない かつ テンプレートフィールドマスター.フィールド名 ExtraPayKeyの場合、設定不可
                continue;
            }
            if ( $tId == 'CKA01005_1' && $ent['CreditTransferFlg'] == 0 && $templateField['PhysicalName'] == 'CreditTransferRequestFlg') {
                continue;
            }
            if ( $tId == 'CKA01005_1' && $ent['CreditTransferFlg'] == 0 && $templateField['PhysicalName'] == 'RequestStatus') {
                continue;
            }
            if ( $tId == 'CKA01005_1' && $ent['CreditTransferFlg'] == 0 && $templateField['PhysicalName'] == 'RequestSubStatus') {
                continue;
            }
            if ( $tId == 'CKA01005_1' && $ent['CreditTransferFlg'] == 0 && $templateField['PhysicalName'] == 'RequestCompDate') {
                continue;
            }
            if ( $tId == 'CKA01005_1' && $ent['CreditTransferFlg'] == 0 && $templateField['PhysicalName'] == 'CreditTransferMethod1') {
                continue;
            }
            if ( $tId == 'CKA01005_1' && $ent['CreditTransferFlg'] == 0 && $templateField['PhysicalName'] == 'CreditTransferMethod2') {
                continue;
            }

            if( $templateField['ValidFlg'] == 1 ) {
                $validList[] = $templateField;
            }
            elseif( $templateField['ValidFlg'] == 0 ) {
                $invalidList[] = $templateField;
            }
        }

        // タイトルの設定
        $this->setPageTitle( 'テンプレートID：' . $tId . '　テンプレート名：' . $templateName );

        $this->view->assign( 'validList', $validList );
        $this->view->assign( 'invalidList', $invalidList );

        $this->view->assign( 'userId', $userId );
        $this->view->assign( 'templateSeq', $templateSeq );

        // リダイレクト先設定
        $redirect = 'account/changecsv/tid/' .$tId . '/tclass/' . $tClass .'/eid/' . $eId . '/sid/' . $sId;
        $this->view->assign( 'redirect', $redirect );

        return $this->view;
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

        $mdlph = new TablePasswordHistory($this->app->dbAdapter);
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);

        //パスワード最小桁数
        $passMin = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
        //過去パスワードの使用不可回数
        $passTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'UsePasswdTimes');
        //過去4回分のパスワードを取得
        $sql  = " SELECT LoginPasswd FROM T_PasswordHistory WHERE Category = :Category AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $passTimeLimit;
        $lastPass = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':Category' => 2, ':LoginId' => $this->app->authManager->getUserInfo()->LoginId)));

        // --------------------------
        // パスワード
        // --------------------------
        isset($data['ChangePassword']) ? $changePassword = $data['ChangePassword'] : $changePassword = '';
        if($changePassword) {
            // 非代理ログイン中以外で必要な検証
            if($this->app->authManager->getAlternativeUserInfo() == null) {
                // パスワードの正常性
                $key = 'CurrentPassword';
                if(!$this->isMatchCurrentPassword(trim($data[$key]))) {
                    $errors[$key] = array("現在のパスワードに誤りがあります");
                }
                if(!strlen(trim($data[$key]))) {
                    $errors[$key] = array("現在のパスワードが未入力です");
                }
                // 新パスワードと現行パスワードの一致(レターケース区別なし）の検出
                $key1 = 'CurrentPassword';
                $key = 'NewPassword';
                if(strcasecmp(trim($data[$key1]), trim($data[$key])) == 0) {
                    $errors[$key] = array("パスワードを変更してください");
                }
            }

            // 空パスワードのチェック
            $key = 'NewPassword';
            if(!strlen(trim($data[$key]))) {
                $errors[$key] = array("新しいパスワードが未入力です");
            }
            $key = 'NewPassword2';
            if(!strlen(trim($data[$key]))) {
                $errors[$key] = array("新しいパスワード（確認）が未入力です");
            }
            // 確認パスワードの不一致（レターケース区別あり）の検出
            $key1 = 'NewPassword';
            $key = 'NewPassword2';
            if(trim($data[$key1]) != trim($data[$key])) {
                $errors[$key] = array("新しいパスワードと新しいパスワード（確認）が一致しません");
            }
            //英大文字、英小文字、数字、記号、桁数のチェック
            $key = 'NewPassword';
            //パスワード文字数
            $pass_len = strlen(trim($data[$key]));
            if (!preg_match('/[a-z]/', trim($data[$key])) || !preg_match('/[A-Z]/', trim($data[$key])) || !preg_match('/[0-9]/', trim($data[$key]))
                || !preg_match('/[ -\/:-@\[-`\{-\~]/', trim($data[$key])) || $pass_len < $passMin)
            {
                $errors[$key] = array("パスワードは英大文字、英小文字、数字、記号のすべてを含む" .$passMin. "文字以上でご入力ください。");
            }

            $authUtil = $this->app->getAuthUtility();
            $newPassHashed = $authUtil->generatePasswordHash($this->app->authManager->getUserInfo()->LoginId, trim($data[$key1]));
            //過去4回分のパスワードとの重複チェック
            foreach ($lastPass as $pass)
            {
                 if ($newPassHashed == $pass['LoginPasswd'])
                {
                    $errors[$key] = array("過去" .$passTimeLimit. "回に使用したパスワードは利用できません。");
                }
            }

            // ここまでエラーがなければパスワードルールの検証
            if(empty($errors)) {
                $psw_validator = \models\Logic\AccountValidity\LogicAccountValidityPasswordValidator::getDefaultValidator();
                if(!$psw_validator->isValid($data['NewPassword'], $this->app->authManager->getUserInfo()->LoginId)) {
                    foreach($psw_validator->getLastErrors() as $psw_err) {
                        $errors['NewPassword'] = array($psw_err);
                        break;
                    }
                }
            }
        }

        // --------------------------
        // その他の項目の検証
        // --------------------------
        // CpNameKj: ご担当氏名
        $key = 'CpNameKj';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("ご担当氏名が未入力です");
        }

        // CpNameKn: ご担当かな氏名
        $key = 'CpNameKn';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("ご担当氏名カナが未入力です");
        }
        if (!isset($errors[$key]) && !(preg_match('/^[ァ-ヾ 　]+$/u', $data[$key]))) {
            $errors[$key] = array("ご担当氏名カナがカタカナでないです");
        }

        // MailAddress: メールアドレス
        $key = 'MailAddress';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("メールアドレスが未入力です");
        }
        // 現行にあわせてメールアドレス形式（xxxx@xxx.xxx）のもののみOKとする。
        if (!isset($errors[$key])) {
            foreach (explode(',', $data[$key]) as $v) {
                if (!preg_match('|^[0-9a-z_./?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$|', $v)) {
                    $errors[$key] = array("メールアドレスの入力値が不正です。");
                    break;
                }
            }
        }

        // ContactPhoneNumber: 連絡先電話番号
        $key = 'ContactPhoneNumber';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("連絡先電話番号が未入力です");
        }
        $cvp = new CoralValidatePhone();
        if (!isset($errors[$key]) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("連絡先電話番号の入力値が不正です");
        }

        // ContactFaxNumber: 連絡先FAX番号
        $key = 'ContactFaxNumber';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("連絡先FAX番号の入力値が不正です。");
        }

        // FfName: 与信NG表示期間
        $key = 'CreditNgDispDays';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && (!(is_numeric($data[$key])) || (is_numeric($data[$key]) && (int)$data[$key] < 0))) {
            $errors[$key] = array("与信NG表示期間には0以上の整数を入力してください。");
        }

        // dispOrder: 表示順
        $key = 'dispOrder';
        if (!isset($errors[$key]) && ($data['DispOrder1'] == '-1') && ($data['DispOrder2'] != '-1')) {
            $errors[$key] = array("表示順2は表示順1選択後入力してください。");
        }

        if (!isset($errors[$key]) && ($data['DispOrder2'] == '-1') && ($data['DispOrder3'] != '-1')) {
            $errors[$key] = array("表示順3は表示順2選択後入力してください。");
        }

        // dispOrder2: 表示順
        $key = 'dispOrder2';
        if (!isset($errors[$key]) && (($data['ClaimOrder1'] == '-1') || ($data['ClaimOrder2'] == '-1'))) {
            $errors[$key] = array("表示順は必ず入力してください。");
        }

        // DisplayCount: 1ページの表示件数
        $key = 'DisplayCount';
        if (!isset($errors[$key]) && (empty($data[$key]) || (strlen($data[$key]) > 0) && (!(is_numeric($data[$key])) || (is_numeric($data[$key]) && ((int)$data[$key] < 50 || (int)$data[$key] > 1000))))) {
            $errors[$key] = array("1ページの表示件数には50～1000までの整数を入力してください。");
        }

        // バッチ排他制御
        $mdlbl = new TableBatchLock (Application::getInstance()->dbAdapter);
        $BatchLock = $mdlbl->findBatchId($this::EXECUTE_BATCH_ID)['BatchLock'];

        $key = 'BatchLock';
        if (!isset($errors[$key]) && ($BatchLock > 0)) {
            $errors[$key] = array("現在、設定変更処理を行うことができません。しばらくたってから再度実行をお願い致します。");
        }

        return $errors;
    }

    /**
     * 指定のログインパスワードが現在の設定に一致するかを判断する
     *
     * @access private
     * @param string $password 確認するパスワード
     * @return boolean
     */
    private function isMatchCurrentPassword($password) {
        $entData = $this->_entTable->findEnterprise( $this->app->authManager->getUserInfo()->EnterpriseId )->current();
        $authUtility =
            Application::getInstance()
                ->getAuthUtility()
                ->setHashDisabled($entData['Hashed'] ? false : true);

        // ログイン中のIDが加盟店IDかオペレータIDか判断する
        if ($this->app->authManager->getUserInfo()->LoginId == $entData['LoginId'] )
        {
            $password = $authUtility->generatePasswordHash($entData['LoginId'], $password);
            $loginPassword = $entData['LoginPasswd'];
        }
        else
        {
            // 追加分のオペレーターでログインしている場合、ログインしているオペレーターのパスワードを変更する。
            $mdleo = new TableEnterpriseOperator($this->app->dbAdapter);
            // 加盟店オペレータIDを取得
            $enterpriseOpId = $mdleo->findEnterpriseOpId($this->app->authManager->getUserInfo()->LoginId);
            $entOpData = $mdleo->find($enterpriseOpId['EnterpriseOpId'])->current();

            $password = $authUtility->generatePasswordHash($entOpData['LoginId'], $password);
            $loginPassword = $entOpData['LoginPasswd'];
        }

//        $password = $authUtility->generatePasswordHash($entData['LoginId'], $password);
        return strcasecmp($password, $loginPassword /*$entData['LoginPasswd']*/) == 0;
    }

    /**
     * このコントローラクラス固有のセッション名前空間を取得する
     *
     * @return Container
     */
    private function getSessionStorage() {
        return new Container( Application::getInstance()->getApplicationId() . '_AccountModifiedData' );
    }

}
