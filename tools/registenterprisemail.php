<?php
chdir(dirname(__DIR__));

require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Logic\LogicCalendar;
use Coral\Base\BaseLog;
use Coral\Coral\Mail\CoralMail;

use Zend\Mail\Storage;
use models\Table\TableEnterpriseMailReceivedHistory;
use models\Table\TableSystemProperty;

class Application extends BaseApplicationAbstract {
	protected $_application_id = 'tools';

	/**
	 * Application の唯一のインスタンスを取得します。
	 *
	 * @static
	 * @access public
	 * @return Application
	 */
	public static function getInstance() {
		if( self::$_instance === null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Application の新しいインスタンスを初期化します。
	 *
	 * @ignore
	 * @access private
	 */
	private function __construct() {
		parent::init();
	}

	/**
	 * @var Adapter
	 */
	public $dbAdapter;

	/**
	 * ログクラス
	 *
	 * @var BaseLog
	 */
	public $logger;

	/**
	 * アプリケーションを実行します。
	 *
	 * @access public
	 */
	public function run() {
        $exitCode = 1;

        try {

            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
            // データベースアダプタをiniファイルから初期化します
            $data = array();
            if (file_exists($configPath))
            {
                $reader = new Ini();
                $data = $reader->fromFile($configPath);
            }
            $this->dbAdapter = new Adapter($data['database']);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

$this->logger->info('registenterprisemail.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // メール内を調査し、加盟店登録メールがあれば登録処理を行う
            $this->_checkAndRegist($data);

$this->logger->info('registenterprisemail.php end');
            $exitCode = 0; // 正常終了

    	} catch( \Exception $e ) {
    	    // エラーログを出力
    	    if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
    	    }
    	}

    	// 終了コードを指定して処理終了
    	exit($exitCode);

	}

    /**
     * メール内を調査し、加盟店登録メールがあれば登録処理を行う
     *
     * @param array $config 設定
     */
	protected function _checkAndRegist($config) {

        // POP3インスタンス作成
        $sysp = new TableSystemProperty($this->dbAdapter);
        $mail = new Storage\Pop3( array(
                'host' => $sysp->getValue('[DEFAULT]', 'systeminfo', 'regist_mail_enterprise_host'),
                'user' => $sysp->getValue('[DEFAULT]', 'systeminfo', 'regist_mail_enterprise_user'),
                'password' => $sysp->getValue('[DEFAULT]', 'systeminfo', 'regist_mail_enterprise_password'),
            )
        );

        // 対象開始日時の確定(遡ること１時間分)
        $stdt = date('Y-m-d H:i:s', strtotime(' -1 hour '));

        // ユーザーID(バッチユーザー)の取得
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userId = $obj->getUserId( 99, 1 );

        // 加盟店登録クラス
        $lgc = new \models\Logic\LogicEnterpriseRegister($this->dbAdapter);

        // 加盟店メール受信履歴登録クラス
        $mdlemrh = new TableEnterpriseMailReceivedHistory($this->dbAdapter);

        // サーバ上受信メールを調査
        foreach($mail as $message) {

            if (!(($message->subject == 'お申し込みです！') &&
                  ($stdt < date('Y-m-d H:i:s', strtotime($message->date))))) {
                continue;
            }

            // 本段階で、加盟店申し込みメールと判定された
            $str_body = mb_convert_encoding($message->getContent(), 'UTF-8', 'ISO-2022-JP');

            // 事業者メール受信履歴登録
            $emrsData = array(
                'ReceivedDate'      => date('Y-m-d H:i:s', strtotime($message->date)),
                'GetDate'           => date('Y-m-d H:i:s'),
                'FromAddress'       => $message->from,
                'Subject'           => $message->subject,
                'Body'              => $str_body,
                'ProcessClass'      => '0',
                'ErrorClass'        => '0',
                'EnterpriseId'      => null,
                'RegistId'          => $userId,
                'UpdateId'          => $userId,
                'ValidFlg'          => '1',
            );
            $entMailRcvdSeq = $mdlemrh->saveNew($emrsData);

            // (メール本文より[加盟店登録クラス(LogicEnterpriseRegister)]に適合する配列を生成)
            $ary_mail = explode("\n", $str_body);
            $data = $this->_convertForLogicEnterpriseRegister($ary_mail);

            // 加盟店登録処理
            $returns = $lgc->register($data, 0, $userId);
            $enterpriseids = $returns['EnterpriseIds'];
            $errors = $returns['error'];

            // 事業者メール
            $emrsData = array(
                'ProcessClass'      => '1',
                'ErrorClass'        => '0',
                'EnterpriseId'      => $enterpriseids[0],
            );

            // エラーがあった場合
            if (( is_array($errors) && count( $errors ) > 0 ) || (!is_array($errors) && $errors != '')) {
                // 事業者メール登録バッチエラーメール送信
                $mail = new CoralMail($this->dbAdapter, $config['mail']['smtp']);
                $mail->SendRegistEnterpriseMailFailMail($str_body);
                $emrsData['ErrorClass'] = '1';
$this->logger->alert('[' . $entMailRcvdSeq . ']' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            }

            // 事業者メール受信履歴更新
            $mdlemrh->saveUpdate($emrsData, $entMailRcvdSeq);
        }
    }

    /**
     * メール本文より[加盟店登録クラス(LogicEnterpriseRegister)]に適合する配列を生成し戻す
     *
     * @param array $ary_mail メール本文の配列
     * @return array [加盟店登録クラス(LogicEnterpriseRegister)]に適合する配列
     */
    protected function _convertForLogicEnterpriseRegister($ary_mail) {

        $ary_reg = array();

        // count関数対策
        $aryMailCount = 0;
        if (!empty($ary_mail)) {
        $aryMailCount = count($ary_mail);
        }
        for ($i=0; $i<$aryMailCount; $i++) {
            $row = trim($ary_mail[$i]);

            if ($row == '■会社名：') {
                $isEnterpriseNameKj = true;
            }
            else if ($isEnterpriseNameKj) {
                $ary_reg[0]['EnterpriseNameKj'] = $row;
                $isEnterpriseNameKj = false;
            }
            else if ($row == '■会社名（カナ）：') {
                $isEnterpriseNameKn = true;
            }
            else if ($isEnterpriseNameKn) {
                $ary_reg[0]['EnterpriseNameKn'] = $row;
                $isEnterpriseNameKn = false;
            }
            else if ($row == '■代表者氏名（漢字）：') {
                $isRepNameKj = true;
            }
            else if ($isRepNameKj) {
                $ary_reg[0]['RepNameKj'] = $row;
                $isRepNameKj = false;
            }
            else if ($row == '■代表者氏名（カナ）：') {
                $isRepNameKn = true;
            }
            else if ($isRepNameKn) {
                $ary_reg[0]['RepNameKn'] = $row;
                $isRepNameKn = false;
            }
            else if ($row == '■部署：') {
                $isDivisionName = true;
            }
            else if ($isDivisionName) {
                $ary_reg[0]['DivisionName'] = $row;
                $isDivisionName = false;
            }
            else if ($row == '■ご担当者氏名（漢字）：') {
                $isCpNameKj = true;
            }
            else if ($isCpNameKj) {
                $ary_reg[0]['CpNameKj'] = $row;
                $isCpNameKj = false;
            }
            else if ($row == '■ご担当者氏名（カナ）：') {
                $isCpNameKn = true;
            }
            else if ($isCpNameKn) {
                $ary_reg[0]['CpNameKn'] = $row;
                $isCpNameKn = false;
            }
            else if ($row == '■郵便番号') {
                $isPostalCode = true;
            }
            else if ($isPostalCode) {
                $ary_reg[0]['PostalCode'] = $row;
                $isPostalCode = false;
            }
            else if ($row == '■都道府県') {
                $isPrefectureName = true;
            }
            else if ($isPrefectureName) {
                $ary_reg[0]['PrefectureName'] = $row;
                $isPrefectureName = false;
            }
            else if ($row == '■市区郡') {
                $isCity = true;
            }
            else if ($isCity) {
                $ary_reg[0]['City'] = $row;
                $isCity = false;
            }
            else if ($row == '■町域') {
                $isTown = true;
            }
            else if ($isTown) {
                $ary_reg[0]['Town'] = $row;
                $isTown = false;
            }
            else if ($row == '■建物名') {
                $isBuilding = true;
            }
            else if ($isBuilding) {
                $ary_reg[0]['Building'] = $row;
                $isBuilding = false;
            }
            else if ($row == '■お電話番号：') {
                $isPhone = true;
            }
            else if ($isPhone) {
                $ary_reg[0]['Phone'] = $row;
                $isPhone = false;
            }
            else if ($row == '■請求書に記載するお電話番号：') {
                $isContactPhoneNumber = true;
            }
            else if ($isContactPhoneNumber) {
                $ary_reg[0]['ContactPhoneNumber'] = $row;
                $isContactPhoneNumber = false;
            }
            else if ($row == '■メールアドレス：') {
                $isMailAddress = true;
            }
            else if ($isMailAddress) {
                $ary_reg[0]['MailAddress'] = $row;
                $isMailAddress = false;
            }
            else if ($row == '■サイト名') {
                $isSiteNameKj1 = true;
            }
            else if ($isSiteNameKj1) {
                $ary_reg[0]['SiteNameKj1'] = $row;
                $isSiteNameKj1 = false;
            }
            else if ($row == '■ショップURL') {
                $isUrl1 = true;
            }
            else if ($isUrl1) {
                $ary_reg[0]['Url1'] = $row;
                $isUrl1 = false;
            }
            else if ($row == '■サイト名2') {
                $isSiteNameKj2 = true;
            }
            else if ($isSiteNameKj2) {
                $ary_reg[0]['SiteNameKj2'] = $row;
                $isSiteNameKj2 = false;
            }
            else if ($row == '■ショップURL2') {
                $isUrl2 = true;
            }
            else if ($isUrl2) {
                $ary_reg[0]['Url2'] = $row;
                $isUrl2 = false;
            }
            else if ($row == '■サイト名3') {
                $isSiteNameKj3 = true;
            }
            else if ($isSiteNameKj3) {
                $ary_reg[0]['SiteNameKj3'] = $row;
                $isSiteNameKj3 = false;
            }
            else if ($row == '■ショップURL3') {
                $isUrl3 = true;
            }
            else if ($isUrl3) {
                $ary_reg[0]['Url3'] = $row;
                $isUrl3 = false;
            }
            else if ($row == '■サイト名4') {
                $isSiteNameKj4 = true;
            }
            else if ($isSiteNameKj4) {
                $ary_reg[0]['SiteNameKj4'] = $row;
                $isSiteNameKj4 = false;
            }
            else if ($row == '■ショップURL4') {
                $isUrl4 = true;
            }
            else if ($isUrl4) {
                $ary_reg[0]['Url4'] = $row;
                $isUrl4 = false;
            }
            else if ($row == '■サイト名5') {
                $isSiteNameKj5 = true;
            }
            else if ($isSiteNameKj5) {
                $ary_reg[0]['SiteNameKj5'] = $row;
                $isSiteNameKj5 = false;
            }
            else if ($row == '■ショップURL5') {
                $isUrl5 = true;
            }
            else if ($isUrl5) {
                $ary_reg[0]['Url5'] = $row;
                $isUrl5 = false;
            }
            else if ($row == '■ショップ月商：') {
                $isPreSales = true;
            }
            else if ($isPreSales) {
                $ary_reg[0]['PreSales'] = $row;
                $isPreSales = false;
            }
            else if ($row == '■ご利用希望プラン：') {
                $isPlanName = true;
            }
            else if ($isPlanName) {
                $ary_reg[0]['PlanName'] = $row;
                $isPlanName = false;
            }
            else if ($row == '■お支払希望日：') {
                $isPayingCycleName = true;
            }
            else if ($isPayingCycleName) {
                $ary_reg[0]['PayingCycleName'] = $row;
                $isPayingCycleName = false;
            }
            else if ($row == '■備考：') {
                $isNote = true;
            }
            else if ($isNote) {
                $ary_reg[0]['Note'] = $row;
                $isNote = false;
            }
            else if ($row == '■キャンペーンコード：') {
                $isCampaignCode = true;
            }
            else if ($isCampaignCode) {
                $ary_reg[0]['CampaignCode'] = $row;
                $isCampaignCode = false;
            }
            else if ($row == '■金融機関名：') {
                $isFfName = true;
            }
            else if ($isFfName) {
                $ary_reg[0]['FfName'] = $row;
                $isFfName = false;
            }
            else if ($row == '■金融機関コード：') {
                $isFfCode = true;
            }
            else if ($isFfCode) {
                $ary_reg[0]['FfCode'] = $row;
                $isFfCode = false;
            }
            else if ($row == '■支店名：') {
                $isFfBranchName = true;
            }
            else if ($isFfBranchName) {
                $ary_reg[0]['FfBranchName'] = $row;
                $isFfBranchName = false;
            }
            else if ($row == '■支店名コード：') {
                $isFfBranchCode = true;
            }
            else if ($isFfBranchCode) {
                $ary_reg[0]['FfBranchCode'] = $row;
                $isFfBranchCode = false;
            }
            else if ($row == '■口座種目：') {
                $isFfAccountClass = true;
            }
            else if ($isFfAccountClass) {
                $ary_reg[0]['FfAccountClass'] = $row;
                $isFfAccountClass = false;
            }
            else if ($row == '■口座番号：') {
                $isFfAccountNumber = true;
            }
            else if ($isFfAccountNumber) {
                $ary_reg[0]['FfAccountNumber'] = $row;
                $isFfAccountNumber = false;
            }
            else if ($row == '■口座名義：') {
                $isFfAccountName = true;
            }
            else if ($isFfAccountName) {
                $ary_reg[0]['FfAccountName'] = $row;
                $isFfAccountName = false;
            }
        }

        // 金融機関名、金融機関コード、支店名、支店名コード、口座種目、口座番号、口座名義すべてが未設定の場合、固定値で入力とする
        if ((isset($ary_reg[0]['FfName']) && $ary_reg[0]['FfName'] == '') &&
            (isset($ary_reg[0]['FfCode']) && $ary_reg[0]['FfCode'] == '') &&
            (isset($ary_reg[0]['FfBranchName']) && $ary_reg[0]['FfBranchName'] == '') &&
            (isset($ary_reg[0]['FfBranchCode']) && $ary_reg[0]['FfBranchCode'] == '') &&
            (isset($ary_reg[0]['FfAccountClass']) && $ary_reg[0]['FfAccountClass'] == '') &&
            (isset($ary_reg[0]['FfAccountNumber']) && $ary_reg[0]['FfAccountNumber'] == '') &&
            (isset($ary_reg[0]['FfAccountName']) && $ary_reg[0]['FfAccountName'] == '')) {

            $ary_reg[0]['FfName']          = 'サンプル銀行';
            $ary_reg[0]['FfCode']          = '1111';
            $ary_reg[0]['FfBranchName']    = 'サンプル支店';
            $ary_reg[0]['FfBranchCode']    = '1111';
            $ary_reg[0]['FfAccountClass']  = '普通';
            $ary_reg[0]['FfAccountNumber'] = '1111111';
            $ary_reg[0]['FfAccountName']   = 'ｻﾝﾌﾟﾙ';
        }

        return $ary_reg;
    }
}
Application::getInstance()->run();