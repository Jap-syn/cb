<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Json\Json;

/**
 * 加盟店登録クラス
 */
class LogicEnterpriseRegister
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct(Adapter $adapter)
	{
	    $this->_adapter = $adapter;
	}

	/**
	 * 登録処理
	 * @param array $datas 入力データ
	 * @param int $oemId OemID
	 * @param int $userId ユーザID
	 *
	 * @return array | string | null エラーデータ
	 */
	public function register( $datas, $oemId, $userId ) {

	    $return = array();
	    $enterpriseids = array();

        // チェック処理
        $errors = $this->validate( $datas );
        if (!empty($errors)) {
            $return['error'] = $errors;
            return $return;
        }

        $mdl_ent = new \models\Table\TableEnterprise($this->_adapter);
        $mdl_sit = new \models\Table\TableSite($this->_adapter);
        $mdl_ttl = new \models\Table\TableEnterpriseTotal($this->_adapter);
        $mdl_usr = new \models\Table\TableUser($this->_adapter);
        $mdl_atent = new \models\Table\ATableEnterprise($this->_adapter);

        $row_oem = null;
        if ($oemId > 0) {
            $row_oem = $this->_adapter->query(" SELECT * FROM T_Oem WHERE OemId = :OemId ")->execute(array(':OemId' => $oemId))->current();
            $settlementFeeRatePlan = Json::decode($row_oem['SettlementFeeRatePlan'], Json::TYPE_ARRAY);
        }

        // 登録処理
        $this->_adapter->getDriver()->getConnection()->beginTransaction();
        try {
            foreach( $datas as $row ) {

                // 加盟店登録
                $eData = array();
                $eData['EnterpriseNameKj'] = $row['EnterpriseNameKj'];
                $eData['EnterpriseNameKn'] = $row['EnterpriseNameKn'];
                $eData['PostalCode'] = $row['PostalCode'];
                $eData['PrefectureCode'] = $this->_adapter->query(" SELECT PrefectureCode FROM M_Prefecture WHERE PrefectureName = :PrefectureName ")->execute(array(':PrefectureName' => $row['PrefectureName']))->current()['PrefectureCode'];
                $eData['PrefectureName'] = $row['PrefectureName'];
                $eData['City'] = $row['City'];
                $eData['Town'] = $row['Town'];
                $eData['Building'] = $row['Building'];
                $eData['Phone'] = $row['Phone'];
                $eData['Industry'] = 0;
                $eData['Plan'] = $this->getPricePlanIdBySearchLikeForward($row['PlanName']);
                $row_priceplan = $this->_adapter->query(" SELECT * FROM M_PricePlan WHERE PricePlanId = :PricePlanId AND ValidFlg = 1 ")->execute(array(':PricePlanId' => $eData['Plan']))->current();
                $eData['MonthlyFee'] = $row_priceplan['MonthlyFee'];
                $eData['FfName'] = $row['FfName'];
                $eData['FfCode'] = $row['FfCode'];
                $eData['FfBranchName'] = $row['FfBranchName'];
                $eData['FfBranchCode'] = $row['FfBranchCode'];
                $eData['FfAccountNumber'] = $row['FfAccountNumber'];
                $eData['FfAccountClass'] = $row['FfAccountClass'];
                $eData['FfAccountClass'] = ($row['FfAccountClass'] == '当座') ? 2 : 1;
                $eData['FfAccountName'] = $row['FfAccountName'];
                $eData['CpNameKj'] = $row['CpNameKj'];
                $eData['CpNameKn'] = $row['CpNameKn'];
                $eData['DivisionName'] = $row['DivisionName'];
                $eData['MailAddress'] = $row['MailAddress'];
                $eData['ContactPhoneNumber'] = $row['ContactPhoneNumber'];
                $eData['ValidFlg'] = 1;
                $eData['RepNameKj'] = $row['RepNameKj'];
                $eData['RepNameKn'] = $row['RepNameKn'];
                $eData['PreSales'] = 0;
                if ($row['PreSales'] != '') {
                    $row_preSales = $this->_adapter->query(" SELECT KeyCode FROM M_Code WHERE CodeId = 55 AND Note = :Note ")->execute(array(':Note' => $row['PreSales']))->current();
                    $eData['PreSales'] = ($row_preSales) ? $row_preSales['KeyCode'] : 2;// rowが得られないときの[2]は「100万円超～500万円以下」を示す
                }
                $eData['TcClass'] = 1;
                $eData['ApplicationDate'] = date('Y-m-d');
                $eData['N_MonthlyFee'] = $row_priceplan['MonthlyFee'];
                $eData['UnitingAddress'] = $row['PrefectureName'] . $row['City'] . $row['Town'] . $row['Building'];
                $eData['AutoCreditJudgeMode'] = 0;
                $eData['UseAmountLimitForCreditJudge'] = 0;
                $eData['OemId'] = $oemId;
                $eData['Hashed'] = 1;
                if ($oemId > 0) {
                    $eData['OemMonthlyFee'] = $row_oem['MonthlyFee'];
                    $eData['N_OemMonthlyFee'] = $row_oem['N_MonthlyFee'];
                }
                $eData['ClaimClass'] = 1;
                $row_payingcycle = $this->_adapter->query(" SELECT * FROM M_PayingCycle WHERE PayingCycleName = :PayingCycleName AND ValidFlg = 1 ")->execute(array(':PayingCycleName' => $row['PayingCycleName']))->current();
                $eData['PayingCycleId'] = $row_payingcycle['PayingCycleId'];
                $eData['CreditJudgePendingRequest'] = 1;
                $eData['LastPasswordChanged'] = date('Y-m-d H:i:s');
                $eData['PayingMail'] = 1;
                $eData['RegistId'] = $userId;
                $eData['UpdateId'] = $userId;

                $newId = $mdl_ent->saveNew($eData);


                // 加盟店更新
                $eData = array();
                $entPrefix = nvl($mdl_ent->getLoginIdPrefix($newId), 'AT');// ログインID向けプレフィックスを確定
                $eData['EnterpriseId'] = $newId;                            // 獲得したプライマリキーをセットしておく
                $eData['LoginId'] = sprintf('%s%08d', $entPrefix, $newId);	// 新しいEnterpriseIdによりログインIDを指定
                $newPassword = $this->generateNewPassword($eData['LoginId']);// パスワードをランダム設定
                // パスワードハッシュ適用
                $authUtil = $this->getAuthUtility();
                $eData['LoginPasswd'] = $authUtil->generatePasswordHash($eData['LoginId'], $newPassword);
                // (Noteにﾊﾟｽﾜｰﾄﾞを保管)
                $eData['Note'] = "キャンペーンコード：" . $row['CampaignCode'] . "\n" . $row['Note'] . "\n" . "パスワード：" . $newPassword;

                $mdl_ent->saveUpdate($eData, $newId);                 // 更新保存

                // (加盟店別集計の登録)
                $mdl_ttl->saveNew(array('EnterpriseId'=>$newId, 'RegistId'=>$userId, 'UpdateId'=>$$userId));

                // T_User新規登録
                $mdl_usr->saveNew(array('UserClass' => 2, 'Seq' => $newId, 'RegistId' => $userId, 'UpdateId' => $userId,));

                // サイト登録
                for ($i = 1; $i<=5; $i++) {

                    if ($row['SiteNameKj' . $i] == '') { continue; }// (サイト名が未入力時は処理不要)

                    $eSit = array();
                    $eSit['EnterpriseId'] = $newId;
                    $eSit['SiteNameKj'] = $row['SiteNameKj' . $i];
                    $eSit['SiteNameKn'] = $eSit['SiteNameKj'];
                    $eSit['Url'] = $row['Url' . $i];
                    $eSit['ReqMailAddrFlg'] = 0;
                    $eSit['ValidFlg'] = 1;
                    $eSit['SiteForm'] = 99;
                    $eSit['OutOfAmendsFlg'] = 0;
                    $eSit['SettlementAmountLimit'] = $row_priceplan['SettlementAmountLimit'];
                    $eSit['SettlementFeeRate'] = $row_priceplan['SettlementFeeRate'];
                    $eSit['ClaimFeeBS'] = $row_priceplan['ClaimFeeBS'];
                    $eSit['ClaimFeeDK'] = $row_priceplan['ClaimFeeDK'];
                    $eSit['ReClaimFee'] = $row_priceplan['ReClaimFee'];
                    if ($oemId > 0) {
                        $eSit['OemSettlementFeeRate'] = $settlementFeeRatePlan[$row_priceplan['PricePlanId']];
                        $eSit['OemClaimFee'] = $row_oem['ClaimFeeBS'];
                        $eSit['SelfBillingOemClaimFee'] = $row_oem['ClaimFeeDK'];
                    }
                    $eSit['CreditCriterion'] = 0;
                    $eSit['AutoCreditCriterion'] = 0;
                    $eSit['SiteConfDate'] = date('Y-m-d');
                    $eSit['CreaditStartMail'] = 1;
                    $eSit['CreaditCompMail'] = 1;
                    $eSit['ClaimMail'] = 1;
                    $eSit['ReceiptMail'] = 1;
                    $eSit['CancelMail'] = 1;
                    $eSit['SoonPaymentMail'] = 1;
                    $eSit['NotPaymentConfMail'] = 1;
                    $eSit['AutoJournalDeliMethodId'] = 1;
                    $eSit['AutoJournalIncMode'] = 0;
                    $eSit['PrintFormDK'] = 1;
                    $eSit['PrintFormBS'] = 1;
                    $eSit['ClaimDisposeMail'] = 0;
                    $eSit['RegistId'] = $userId;
                    $eSit['UpdateId'] = $userId;

                    $mdl_sit->saveNew($eSit);
                }

                $enterpriseids[] = $newId;

                // 加盟店_会計テーブル登録
                $mdl_atent->saveNew(array('EnterpriseId' => $newId));
            }

            $return['EnterpriseIds'] = $enterpriseids;

            $this->_adapter->getDriver()->getConnection()->commit();

        } catch( \Exception $e ) {
            $this->_adapter->getDriver()->getConnection()->rollback();
            $errors = $e->getMessage();
            $return['error'] = $errors;
        }

        return $return;
	}

    /**
     * 入力されたデータを検証する
     *
     * @param array $datas 入力データ
     * @return array | null エラーデータ
     */
    protected function validate( $datas ) {

        $errors = array();

        $cvpc = new \Coral\Coral\Validate\CoralValidatePostalCode();    // 郵便番号検証
        $cvp = new \Coral\Coral\Validate\CoralValidatePhone();          // 電話番号検証
        $cvmm = new \Coral\Coral\Validate\CoralValidateMultiMail();     // メール検証

        $datasCount = 0;
        if (!empty($datas)) {
            $datasCount = count( $datas );
        }
        for( $i = 0; $i < $datasCount; $i++ ) {

            // EnterpriseNameKj : 会社名
            $key = 'EnterpriseNameKj';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("会社名は必須です。");
            }

            // EnterpriseNameKn : 会社名（カナ）
            $key = 'EnterpriseNameKn';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("会社名（カナ）は必須です。");
            }

            // RepNameKj : 代表者氏名（漢字）
            $key = 'RepNameKj';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("代表者氏名（漢字）は必須です。");
            }

            // RepNameKn : 代表者氏名（カナ）
            $key = 'RepNameKn';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("代表者氏名（カナ）は必須です。");
            }

            // (チェックなし)DivisionName : 部署

            // CpNameKj : ご担当者氏名（漢字）
            $key = 'CpNameKj';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("ご担当者氏名（漢字）は必須です。");
            }

            // CpNameKn : ご担当者氏名（カナ）
            $key = 'CpNameKn';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("ご担当者氏名（カナ）は必須です。");
            }

            // PostalCode : 郵便番号
            $key = 'PostalCode';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("郵便番号は必須です。");
            }
            if (!isset($errors[$i][$key]) && !$cvpc->isValid($datas[$i][$key])) {
                $errors[$i][$key] = array("郵便番号が不正な形式です。");
            }

            // PrefectureName : 都道府県
            $key = 'PrefectureName';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("都道府県は必須です。");
            }
            $cnt = (int)$this->_adapter->query(" SELECT COUNT(1) AS cnt FROM M_Prefecture WHERE PrefectureName = :PrefectureName "
                )->execute(array(':PrefectureName' => $datas[$i][$key]))->current()['cnt'];
            if (!isset($errors[$i][$key]) && ($cnt == 0)) {
                $errors[$i][$key] = array("都道府県を正しく入力してください。");
            }

            // City : 市区郡
            $key = 'City';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("市区郡は必須です。");
            }

            // Town : 町域
            $key = 'Town';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("町域は必須です。");
            }

            // (チェックなし)Building : 建物名

            // Phone : お電話番号
            $key = 'Phone';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("お電話番号は必須です。");
            }
            if (!isset($errors[$i][$key]) && !$cvp->isValid($datas[$i][$key])) {
                $errors[$i][$key] = array("お電話番号が不正な形式です。");
            }

            // ContactPhoneNumber : 請求書に記載するお電話番号
            $key = 'ContactPhoneNumber';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("請求書に記載するお電話番号は必須です。");
            }
            if (!isset($errors[$i][$key]) && !$cvp->isValid($datas[$i][$key])) {
                $errors[$i][$key] = array("請求書に記載するお電話番号が不正な形式です。");
            }

            // MailAddress : メールアドレス
            $key = 'MailAddress';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("メールアドレスは必須です。");
            }
            if (!isset($errors[$i][$key]) && !$cvmm->isValid($datas[$i][$key])) {
                $errors[$i][$key] = array("メールアドレスが不正な形式です。");
            }

            // (チェックなし)SiteNameKj1 : サイト名1

            // (チェックなし)Url1 : ショップURL1

            // (チェックなし)SiteNameKj2 : サイト名2

            // (チェックなし)Url2 : ショップURL2

            // (チェックなし)SiteNameKj3 : サイト名3

            // (チェックなし)Url3 : ショップURL3

            // (チェックなし)SiteNameKj4 : サイト名4

            // (チェックなし)Url4 : ショップURL4

            // (チェックなし)SiteNameKj5 : サイト名5

            // (チェックなし)Url5 : ショップURL5

            // PreSales: ショップ月商
            $key = 'PreSales';
            if (!isset($errors[$i][$key]) && (strlen($datas[$i][$key]) > 0)) {
                $cnt = (int)$this->_adapter->query(" SELECT COUNT(1) AS cnt FROM M_Code WHERE CodeId = 55 AND Note = :Note "
                    )->execute(array(':Note' => $datas[$i][$key]))->current()['cnt'];
                if ($cnt == 0 && (!($datas[$i][$key][0] == '1' && $datas[$i][$key][1] == '0' && $datas[$i][$key][2] == '1'))) {
                    $errors[$i][$key] = array("ショップ月商に紐づくマスタ設定がありません。");
                }
            }

            // PlanName : ご利用希望プラン
            $key = 'PlanName';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("ご利用希望プランは必須です。");
            }
            if (!isset($errors[$i][$key])) {
                $planid = $this->getPricePlanIdBySearchLikeForward($datas[$i][$key]);
                if ($planid == -1) {
                    $errors[$i][$key] = array("ご利用希望プランに紐づくマスタ設定がありません。");
                }
            }

            // PayingCycleName : お支払希望日
            $key = 'PayingCycleName';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("お支払希望日は必須です。");
            }
            if (!isset($errors[$i][$key])) {
                $cnt = (int)$this->_adapter->query(" SELECT COUNT(1) AS cnt FROM M_PayingCycle WHERE PayingCycleName = :PayingCycleName AND ValidFlg = 1 "
                )->execute(array(':PayingCycleName' => $datas[$i][$key]))->current()['cnt'];
                if ($cnt == 0) {
                    $errors[$i][$key] = array("お支払希望日に紐づくマスタ設定がありません。");
                }
            }

            // (チェックなし)Note : 備考

            // (チェックなし)CampaignCode : キャンペーンコード

            // FfName : 金融機関名
            $key = 'FfName';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("金融機関名は必須です。");
            }

            // FfCode : 金融機関コード
            $key = 'FfCode';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("金融機関コードは必須です。");
            }
            if (!isset($errors[$i][$key]) && !(is_numeric($datas[$i][$key]))) {
               $errors[$i][$key] = array("金融機関コードが数値ではありません。");
            }

            // FfBranchName : 支店名
            $key = 'FfBranchName';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("支店名は必須です。");
            }

            // FfBranchCode : 支店名コード
            $key = 'FfBranchCode';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("支店名コードは必須です。");
            }
            if (!isset($errors[$i][$key]) && !(is_numeric($datas[$i][$key]))) {
                $errors[$i][$key] = array("支店名コードが数値ではありません。");
            }

            // FfAccountClass : 口座種目
            $key = 'FfAccountClass';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("口座種目は必須です。");
            }

            // FfAccountNumber : 口座番号
            $key = 'FfAccountNumber';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("口座番号は必須です。");
            }

            // FfAccountName : 口座名義
            $key = 'FfAccountName';
            if (!isset($errors[$i][$key]) && !(strlen($datas[$i][$key]) > 0)) {
                $errors[$i][$key] = array("口座名義は必須です。");
            }
        }

        return $errors;
    }

    /**
     * 料金プランマスター[料金プラン名]に前方一致で一致する[料金プランID]の取得
     *
     * @param string $str (検証対象)ご利用希望プラン
     * @return int PricePlanId 但し、一致するものがなかった場合は[-1]を戻す
     */
    protected function getPricePlanIdBySearchLikeForward($str)
    {
        $sql = " SELECT PricePlanId, PricePlanName FROM M_PricePlan WHERE ValidFlg = 1 ";
        $ri = $this->_adapter->query($sql)->execute(null);
        foreach ($ri as $row) {
            $retval = strpos($str, $row['PricePlanName']);
            if (false !== $retval && $retval == 0) {
                return $row['PricePlanId'];
            }
        }

        return -1;
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
        $validator = \models\Logic\AccountValidity\LogicAccountValidityPasswordValidator::getDefaultValidator();
        $i = 0;
        while (true) {
            $newPassword = \Coral\Base\BaseGeneralUtils::MakePassword(8);			// パスワードをランダム設定
            if ($validator->isValid($newPassword, $loginId))
            {
                return $newPassword;
            }
        }
    }

    /**
     * 認証ユーティリティを取得する
     *
     * @return \Coral\Base\Auth\BaseAuthUtility
     */
    protected function getAuthUtility() {
        $sysProps = new \models\Table\TableSystemProperty($this->_adapter);
        return new \Coral\Base\Auth\BaseAuthUtility($sysProps->getHashSalt());
    }

}
