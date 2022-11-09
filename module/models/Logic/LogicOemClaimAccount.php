<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Log\Logger;
use Coral\Base\BaseLog;
use models\Table\TableOrder;
use models\Table\TableClaimHistory;
use models\Table\TableEnterprise;
use models\Table\TableOem;
use models\Table\TableCvsReceiptAgent;
use models\Table\TableOemBankAccount;
use models\Table\TableOemYuchoAccount;
use models\Table\TableOemCvsAccount;
use models\Table\TableOemClaimAccountInfo;
use models\Table\TableSystemProperty;
use models\Table\TableSmbcRelationAccount;
use models\Table\TableSmbcRelationLog;
use models\Logic\LogicYuchoUtility;
use models\Logic\BarcodeData\LogicBarcodeDataCvs;
use Zend\Json\Json;
use models\Logic\Exception\LogicClaimException;
use models\Table\TableClaimError;
use models\Table\TableSite;
use models\Table\TableSubscriberCode;

/**
 * CBまたはOEMごとに関連付けられた請求口座設定を管理するロジック
 */
class LogicOemClaimAccount {
    /** コードマップ種別キー定数：銀行口座 - 口座サービス区分 @var string */
    const MAPKEY_BK_SERVICE_KIND = 'Bk_ServiceKind';

    /** コードマップ種別キー定数：銀行口座 - 口座サービス区分（ショート） @var string */
    const MAPKEY_BK_SERVICE_KIND_S = 'Bk_ServiceKind_S';

    /** コードマップ種別キー定数：銀行口座 - 口座預金種別 @var string */
    const MAPKEY_BK_DEPOSIT_CLASS = 'Bk_DepositClass';

    /** コードマップ種別キー定数：ゆうちょ口座 - 払込負担区分 @var string */
    const MAPKEY_YU_CHARGE_CLASS = 'Yu_ChargeClass';

    /** コードマップ種別キー定数：コンビニ収納代行会社マスター - 無効フラグ @var string */
    const MAPKEY_RECEIPT_AGENT_INVALID_FLG = 'ReceiptAgentInvalidFlg';

	/** コードマップ種別キー定数：SMBC決済ステーションアカウント - 払込票発行区分 @var string */
	const MAPKEY_SMBC_HAKKOU_KBN = 'Smbc_HakkouKbn';

	/** コードマップ種別キー定数：SMBC決済ステーションアカウント - 払込票郵送先区分 @var string */
	const MAPKEY_SMBC_YUUSOUSAKI_KBN = 'Smbc_YuusousakiKbn';

    /** 閾値定数：コンビニ支払可能上限額 @var int */
    const CLAIM_AMOUNT_LIMIT_AMOUNT = 300000;

    /** メッセージ定数：請求金額がCLAIM_AMOUNT_LIMIT_AMOUNT以上の場合のバーコード代替メッセージ @var string */
    const CLAIM_AMOUNT_OVER_LIMIT_MESSAGE = 'コンビニエンスストアでは30万円を超えるお支払いはできません。';

	/** 口座サービス区分定数：通常（固定口座） @var string */
	const SERVICE_KIND_DEFAULT = 0;

	/** 口座サービス区分ラベル定数：0 - 通常（固定口座） @var string */
	const SERVICE_KIND_LABEL_DEFAULT = '通常（固定口座）';

	/** 口座サービス区分ラベル定数（ショート）：0 - 固定 @var string */
	const SERVICE_KIND_LABEL_S_DEFAULT = '固定';

	/** 口座サービス区分定数：仮想口座（SMBC決済ステーション） @var string */
	const SERVICE_KIND_SMBC = 1;

	/** 口座サービス区分ラベル定数：1 - 仮想口座（SMBC決済ステーション） @var string */
	const SERVICE_KIND_LABEL_SMBC = '仮想口座（SMBC決済ステーション）';

	/** 口座サービス区分ラベル定数（ショート）：1 - SMBC @var string */
	const SERVICE_KIND_LABEL_S_SMBC = 'SMBC';

	/** 口座サービス区分定数：仮想口座（ジャパンネットバンク） @var string */
	const SERVICE_KIND_JNB = 2;

	/** 口座サービス区分ラベル定数：2 - 仮想口座（ジャパンネットバンク） @var string */
	const SERVICE_KIND_LABEL_JNB = '仮想口座（ジャパンネットバンク）';

	/** 口座サービス区分ラベル定数（ショート）：2 - JNB @var string */
	const SERVICE_KIND_LABEL_S_JNB = 'JNB';

    /** 口座サービス区分定数：仮想口座（SMBCバーチャル口座） @var string */
    const SERVICE_KIND_SMBCPA = 3;

    /** 口座サービス区分ラベル定数：3 - 仮想口座（SMBCバーチャル口座） @var string */
    const SERVICE_KIND_LABEL_SMBCPA = '仮想口座（SMBCバーチャル口座）';

    /** 口座サービス区分ラベル定数（ショート）：3 - SMBCバーチャル口座 @var string */
    const SERVICE_KIND_LABEL_S_SMBCPA = 'SMBCEB';

	/**
	 * デフォルトロガーインスタンス
	 *
	 * @static
	 * @access protected
	 * @var BaseLog
	 */
	protected static $__logger;

    /**
     * 利用可能なバーコード生成ロジック名とロジッククラス名をマッピングした
     * マスター連想配列を取得する
     *
     * @static
     * @return array
     */
    public static function getBarcodeLogicClasses() {
        return array(
            'Aplus' => 'LogicBarcodeDataCvsAplus',
            'AtPayment' => 'LogicBarcodeDataCvsAtPayment',
            'NTTSmartTrade' => 'LogicBarcodeDataCvsNTTSmartTrade',
            'IndividualPay' => 'LogicBarcodeDataCvsIndividualPay',
        	'KessaiNavi' => 'LogicBarcodeDataCvsKessaiNavi',
            'CvsNet' => 'LogicBarcodeDataCvsCvsNet',
        );
    }

    /**
     * 指定のバーコード生成ロジック名に対応するロジッククラス名を取得する
     *
     * @static
     * @param string $cbLogicName バーコード生成ロジック名
     * @return string
     */
    public static function getBarcodeLogicClassName($bcLogicName) {
        $map = self::getBarcodeLogicClasses();
        return isset($map[$bcLogicName]) ? $map[$bcLogicName] : null;
    }

    /**
     * 各種口座情報の選択肢を定義したマスター連想配列を取得する。
     * 戻り値の配列は種別（＝対象のプロパティ）を示すキー定数をキー、
     * 値-ラベルの連想配列を値に持つ
     *
     * @static
     * @return array
     */
    public static function getCodeMap() {
        return array(
            // 銀行口座 - 口座サービス区分
            self::MAPKEY_BK_SERVICE_KIND => array(
				// 通常（固定口座）
				self::SERVICE_KIND_DEFAULT	=> self::SERVICE_KIND_LABEL_DEFAULT,

				// 仮想口座（SMBC決済ステーション）
				self::SERVICE_KIND_SMBC		=> self::SERVICE_KIND_LABEL_SMBC/*,

				// 仮想口座（ジャパンネットバンク）
				self::SERVICE_KIND_JNB		=> self::SERVICE_KIND_LABEL_JNB*/

                // 仮想口座（SMBCバーチャル口座）
                /* self::SERVICE_KIND_SMBCPA   => self::SERVICE_KIND_LABEL_SMBCPA */
            ),

            // 銀行口座 - 口座サービス区分（ショート）
            self::MAPKEY_BK_SERVICE_KIND_S => array(
				// 通常（固定口座）
				self::SERVICE_KIND_DEFAULT	=> self::SERVICE_KIND_LABEL_S_DEFAULT,

				// 仮想口座（SMBC決済ステーション）
				self::SERVICE_KIND_SMBC		=> self::SERVICE_KIND_LABEL_S_SMBC/*,

				// 仮想口座（ジャパンネットバンク）
				self::SERVICE_KIND_JNB		=> self::SERVICE_KIND_LABEL_S_JNB*/

                // 仮想口座（SMBCバーチャル口座）
                /* self::SERVICE_KIND_SMBCPA	=> self::SERVICE_KIND_LABEL_S_SMBCPA */
            ),

            // 銀行口座 - 口座預金種別
            self::MAPKEY_BK_DEPOSIT_CLASS => array(
                '0' => '普通',
                '1' => '当座'
            ),

            // ゆうちょ口座 - 払込負担区分
            self::MAPKEY_YU_CHARGE_CLASS => array(
                '0' => '払込人負担',
                '2' => '加入者負担'
            ),

            // コンビニ収納代行会社マスター - 無効フラグ
            self::MAPKEY_RECEIPT_AGENT_INVALID_FLG => array(
                '0' => '有効',
                '1' => '無効'
            ),

			// SMBC決済ステーションアカウント - 払込票発行区分
			self::MAPKEY_SMBC_HAKKOU_KBN => array(
				'1' => '自社発行',
				'2' => '代行発行'
			),

			// SMBC決済ステーションアカウント - 郵送先区分
			self::MAPKEY_SMBC_YUUSOUSAKI_KBN => array(
				'1' => '収納企業',
				'2' => '顧客'
			)

        );
    }

	/**
	 * デフォルトのロガーを取得する
	 *
	 * @static
	 * @return BaseLog
	 */
	public static final function getDefaultLogger() {
		return self::$__logger;
	}
	/**
	 * デフォルトのロガーを設定する
	 *
	 * @static
	 * @param BaseLog ロガー
	 */
	public static final function setDefaultLogger(BaseLog $logger = null) {
		self::$__logger = $logger;
	}

    /**
     * アダプタ
     *
     * @access protected
     * @var Adapter
     */
    protected $_adapter = null;

	/**
	 * ロガーインスタンス
	 *
	 * @access protected
	 * @var BaseLog
	 */
	protected $_logger;

    /**
     * LogicOemClaimAccountの新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter) {
        $this
            ->setAdapter($adapter)
            ->setLogger(self::getDefaultLogger());
    }

    /**
     * アダプタを取得する
     *
     * @return Adapter
     */
    public function getAdapter() {
        return $this->_adapter;
    }
    /**
     * アダプタを設定する
     *
     * @param Adapter $adapter アダプタ
     * @return LogicOemClaimAccount このインスタンス
     */
    public function setAdapter(Adapter $adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

	/**
	 * このインスタンスで使用するロガーを取得する
	 *
	 * @return BaseLog
	 */
	public function getLogger() {
		return $this->_logger;
	}
	/**
	 * このインスタンスで使用するロガーを設定する
	 *
	 * @param BaseLog $logger
	 * @return LogicOemClaimAccount
	 */
	public function setLogger(BaseLog $logger = null) {
		$this->_logger = $logger;
		return $this;
	}

    /**
     * 注文テーブルモデルを取得する
     *
     * @return TableOrder
     */
    public function getOrderTable() {
        return new TableOrder($this->getAdapter());
    }

    /**
     * 請求履歴テーブルモデルを取得する
     *
     * @return TableClaimHistory
     */
    public function getClaimHistoryTable() {
        return new TableClaimHistory($this->getAdapter());
    }

    /**
     * 事業者テーブルモデルを取得する
     *
     * @return TableEnterprise
     */
    public function getEnterpriseTable() {
        return new TableEnterprise($this->getAdapter());
    }

    /**
     * サイトテーブルモデルを取得する
     *
     * @return TableSite
     */
    public function getSiteTable() {
    	return new TableSite($this->getAdapter());
    }

    /**
     * 加入者固有コード管理マスタモデルを取得する
     *
     * @return TableSubscriberCode
     */
    public function getSubscriberCodeTable() {
    	return new TableSubscriberCode($this->getAdapter());
    }

    /**
     * OEMテーブルモデルを取得する
     *
     * @return TableOem
     */
    public function getOemTable() {
        return new TableOem($this->getAdapter());
    }

    /**
     * コンビニ収納代行会社マスターモデルを取得する
     *
     * @return TableCvsReceiptAgent
     */
    public function getReceiptAgentMaster() {
        return new TableCvsReceiptAgent($this->getAdapter());
    }

    /**
     * OEM銀行口座テーブルモデルを取得する
     *
     * @return TableOemBankAccount
     */
    public function getBankAccountTable() {
        return new TableOemBankAccount($this->getAdapter());
    }

    /**
     * OEMゆうちょ口座テーブルモデルを取得する
     *
     * @return TableOemYuchoAccount
     */
    public function getYuchoAccountTable() {
        return new TableOemYuchoAccount($this->getAdapter());
    }

    /**
     * OEMコンビニ収納情報テーブルを取得する
     *
     * @return TableOemCvsAccount
     */
    public function getCvsAccountTable() {
        return new TableOemCvsAccount($this->getAdapter());
    }

    /**
     * OEM請求口座テーブルモデルを取得する
     *
     * @return TableOemClaimAccountInfo
     */
    public function getClaimAccountInfoTable() {
        return new TableOemClaimAccountInfo($this->getAdapter());
    }

    /**
     * システムプロパティテーブルモデルを取得する
     *
     * @return TableSystemProperty
     */
    public function getSystemPropertyTable() {
        return new TableSystemProperty($this->getAdapter());
    }

	/**
	 * SMBC決済ステーション連携アカウントテーブルモデルを取得する
	 *
	 * @return TableSmbcRelationAccount
	 */
	public function getSmbcAccountTable() {
		return new TableSmbcRelationAccount($this->getAdapter());
	}

	/**
	 * SMBC決済ステーション連携ログテーブルモデルを取得する
	 *
	 * @return TableSmbcRelationLog
	 */
	public function getSmbcRelationLogTable() {
	    return new TableSmbcRelationLog($this->getAdapter());
	}

    /**
     * 設定済みの請求口座設定をすべて取得する
     *
     * @return ResultInterface
     */
    public function fetchAllClaimAccounts() {
        $sql = $this->_buildFetchQuery();
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * 指定OEM向けの請求口座設定を取得する
     *
     * @param int $oemId OEM-ID
     * @return ResultInterface | null
     */
    public function findClaimAccountsByOemId($oemId = 0) {
        $oemId = (int)$oemId;
        $sql = $this->_buildFetchQuery(array('v.OemId = ' . $oemId));
        $ri = $this->_adapter->query($sql)->execute(null);
        if (!($ri->count() > 0)) {
            return null;
        }
        return $ri;
    }

    /**
     * 指定OEM向けの銀行・ゆうちょ・コンビニ各請求口座設定を保存する
     *
     * @param int $oemId OEM-ID
     * @package array $data 永続化対象のデータ。基本スキーマはfindClaimAccountByOrderId()で取得できるものと一致させる
     */
    public function saveClaimAccounts($oemId, array $data) {
        $bankTable = $this->getBankAccountTable();
        $yuchoTable = $this->getYuchoAccountTable();
        $cvsTable = $this->getCvsAccountTable();
		$smbcTable = $this->getSmbcAccountTable();

        $bankColsMap = array(
            'Bk_ServiceKind'        => 'ServiceKind',
            'Bk_BankCode'           => 'BankCode',
            'Bk_BranchCode'         => 'BranchCode',
            'Bk_BankName'           => 'BankName',
            'Bk_BranchName'         => 'BranchName',
            'Bk_DepositClass'       => 'DepositClass',
            'Bk_AccountNumber'      => 'AccountNumber',
            'Bk_AccountHolder'      => 'AccountHolder',
            'Bk_AccountHolderKn'    => 'AccountHolderKn',
        );
        $yuchoColsMap = array(
            'Yu_SubscriberName' => 'SubscriberName',
            'Yu_AccountNumber'  => 'AccountNumber',
            'Yu_ChargeClass'    => 'ChargeClass',
            'Yu_SubscriberData' => 'SubscriberData',
            'Yu_Option1'        => 'Option1',
            'Yu_Option2'        => 'Option2',
            'Yu_Option3'        => 'Option3'
        );
        $cvsColsMap = array(
            'Cv_ReceiptAgentId' => 'ReceiptAgentId',
            'Cv_SubscriberCode' => 'SubscriberCode',
            'Cv_SubscriberName' => 'SubscriberName',
            'Cv_Option1'        => 'Option1',
            'Cv_Option2'        => 'Option2',
            'Cv_Option3'        => 'Option3'
        );
		$smbcColMap = array(
			'Smbc_DisplayName'			=> 'DisplayName',
			'Smbc_ApiVersion'			=> 'ApiVersion',
			'Smbc_BillMethod'			=> 'BillMethod',
			'Smbc_KessaiId'				=> 'KessaiId',
			'Smbc_ShopCd'				=> 'ShopCd',
			'Smbc_SyunoCoCd1'			=> 'SyunoCoCd1',
			'Smbc_SyunoCoCd2'			=> 'SyunoCoCd2',
			'Smbc_SyunoCoCd3'			=> 'SyunoCoCd3',
			'Smbc_SyunoCoCd4'			=> 'SyunoCoCd4',
			'Smbc_SyunoCoCd5'			=> 'SyunoCoCd5',
			'Smbc_SyunoCoCd6'			=> 'SyunoCoCd6',
			'Smbc_ShopPwd1'				=> 'ShopPwd1',
			'Smbc_ShopPwd2'				=> 'ShopPwd2',
			'Smbc_ShopPwd3'				=> 'ShopPwd3',
			'Smbc_ShopPwd4'				=> 'ShopPwd4',
			'Smbc_ShopPwd5'				=> 'ShopPwd5',
			'Smbc_ShopPwd6'				=> 'ShopPwd6',
			'Smbc_SeikyuuName'			=> 'SeikyuuName',
			'Smbc_SeikyuuKana'			=> 'SeikyuuKana',
			'Smbc_HakkouKbn'			=> 'HakkouKbn',
			'Smbc_YuusousakiKbn'		=> 'YuusousakiKbn',
			'Smbc_Yu_SubscriberName'	=> 'Yu_SubscriberName',
			'Smbc_Yu_AccountNumber'		=> 'Yu_AccountNumber',
			'Smbc_Yu_ChargeClass'		=> 'Yu_ChargeClass',
			'Smbc_Yu_SubscriberData'	=> 'Yu_SubscriberData',
			'Smbc_Cv_ReceiptAgentName'	=> 'Cv_ReceiptAgentName',
		    'Smbc_Cv_ReceiptAgentCode'	=> 'Cv_ReceiptAgentCode',
			'Smbc_Cv_SubscriberName'	=> 'Cv_SubscriberName'
		);
        $configs = array(
            'bank' => array('table' => $bankTable, 'map' => $bankColsMap, 'pk' => 'BankAccountId'),
            'yucho' => array('table' => $yuchoTable, 'map' => $yuchoColsMap, 'pk' => 'YuchoAccountId'),
            'cvs' => array('table' => $cvsTable, 'map' => $cvsColsMap, 'pk' => 'CvsAccountId')
		);
		if($data['Bk_ServiceKind'] == self::SERVICE_KIND_SMBC) {
			$configs['smbc'] = array('table' => $smbcTable, 'map' => $smbcColMap, 'pk' => 'SmbcAccountId');
		}

        $this->debug(sprintf('[saveClaimAccounts : %s] begin', $oemId));
        $this->_adapter->getDriver()->getConnection()->beginTransaction();
        try {
            foreach($configs as $key => $config) {
                $udata = array();
                foreach($config['map'] as $field => $col) {
                    if(isset($data[$field])) {
                        $udata[$col] = $data[$field];
                    }
                }
                $ri = $config['table']->findByOemId($oemId);
                $this->debug(sprintf('[saveClaimAccounts : %s] table = %s, sent data = %s', $oemId, $key, var_export($udata, true)));
                if($ri->count() > 0) {  // 更新
                    $current = $ri->current();
                    $this->debug(sprintf('[saveClaimAccounts : %s]...updating(pk = %s)...', $oemId, $current[$config['pk']]));
                    $config['table']->saveUpdate($udata, $current[$config['pk']]);
                    $this->debug(sprintf('[saveClaimAccounts : %s] done(pk = %s).', $oemId, $current[$config['pk']]));
                } else {		        // 新規登録
                    $this->debug(sprintf('[saveClaimAccounts : %s] ...inserting...', $oemId));
                    $pk = $config['table']->saveNew($oemId, $udata);
                    $this->debug(sprintf('[saveClaimAccounts : %s] done. primary key = %s', $oemId, $pk));
                }
            }
            $this->_adapter->getDriver()->getConnection()->commit();
            $this->debug(sprintf('[saveClaimAccounts : %s] completed', $oemId));
        }
        catch(\Exception $err) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            $this->info(sprintf('[saveClaimAccounts : %s] ERROR!!!! message = %s', $oemId, $err->getMessage()));
            throw $err;
        }
    }

    /**
     * 指定条件で請求口座設定向けデータを問い合わせるSQLを組み立てる
     *
     * @access protected
     * @param null | array $expressions 検索条件のリスト
     * @return string
     */
    protected function _buildFetchQuery(array $expressions = array()) {
        $q = <<<EOQ
SELECT
    v.OemId,
    v.NameKj,
    v.ReclaimAccountPolicy,

    b.BankAccountId     AS  Bk_AccountId,
    b.ServiceKind       AS  Bk_ServiceKind,
    b.BankCode          AS  Bk_BankCode,
    b.BranchCode        AS  Bk_BranchCode,
    b.BankName          AS  Bk_BankName,
    b.BranchName        AS  Bk_BranchName,
    b.DepositClass      AS  Bk_DepositClass,
    b.AccountNumber     AS  Bk_AccountNumber,
    b.AccountHolder     AS  Bk_AccountHolder,
    b.AccountHolderKn   AS  Bk_AccountHolderKn,
    b.RegistDate        AS  Bk_RegistDate,
    b.UpdateDate        AS  Bk_UpdateDate,

    y.YuchoAccountId    AS  Yu_AccountId,
    y.SubscriberName    AS  Yu_SubscriberName,
    y.AccountNumber     AS  Yu_AccountNumber,
    y.ChargeClass       AS  Yu_ChargeClass,
    y.SubscriberData    AS  Yu_SubscriberData,
    y.Option1           AS  Yu_Option1,
    y.Option2           AS  Yu_Option2,
    y.Option3           AS  Yu_Option3,
    y.RegistDate        AS  Yu_RegistDate,
    y.UpdateDate        AS  Yu_UpdateDate,

    c.CvsAccountId      AS  Cv_AccountId,
    c.ReceiptAgentId    AS  Cv_ReceiptAgentId,
    c.SubscriberCode    AS  Cv_SubscriberCode,
    c.SubscriberName    AS  Cv_SubscriberName,
    c.Option1           AS  Cv_Option1,
    c.Option2           AS  Cv_Option2,
    c.Option3           AS  Cv_Option3,
    c.RegistDate        AS  Cv_RegistDate,
    c.UpdateDate        AS  Cv_UpdateDate,

    r.ReceiptAgentName  AS  Cv_ReceiptAgentName,
    r.ReceiptAgentCode  AS  Cv_ReceiptAgentCode,
    r.BarcodeLogicName  AS  Cv_BarcodeLogicName,
    r.ValidFlg          AS  Cv_ValidFlg,

	s.DisplayName			AS	Smbc_DisplayName,
	s.ApiVersion			AS	Smbc_ApiVersion,
	s.BillMethod			AS	Smbc_BillMethod,
	s.KessaiId				AS	Smbc_KessaiId,
	s.ShopCd				AS	Smbc_ShopCd,
	s.SyunoCoCd1			AS	Smbc_SyunoCoCd1,
	s.SyunoCoCd2			AS	Smbc_SyunoCoCd2,
	s.SyunoCoCd3			AS	Smbc_SyunoCoCd3,
	s.SyunoCoCd4			AS	Smbc_SyunoCoCd4,
	s.SyunoCoCd5			AS	Smbc_SyunoCoCd5,
	s.SyunoCoCd6			AS	Smbc_SyunoCoCd6,
	s.ShopPwd1				AS	Smbc_ShopPwd1,
	s.ShopPwd2				AS	Smbc_ShopPwd2,
	s.ShopPwd3				AS	Smbc_ShopPwd3,
	s.ShopPwd4				AS	Smbc_ShopPwd4,
	s.ShopPwd5				AS	Smbc_ShopPwd5,
	s.ShopPwd6				AS	Smbc_ShopPwd6,
	s.SeikyuuName			AS	Smbc_SeikyuuName,
	s.SeikyuuKana			AS	Smbc_SeikyuuKana,
	s.HakkouKbn				AS	Smbc_HakkouKbn,
	s.YuusousakiKbn			AS	Smbc_YuusousakiKbn,
	s.Yu_SubscriberName		AS	Smbc_Yu_SubscriberName,
	s.Yu_AccountNumber		AS	Smbc_Yu_AccountNumber,
	s.Yu_ChargeClass		AS	Smbc_Yu_ChargeClass,
	s.Yu_SubscriberData		AS	Smbc_Yu_SubscriberData,
	s.Cv_ReceiptAgentName	AS	Smbc_Cv_ReceiptAgentName,
    s.Cv_ReceiptAgentCode   AS  Smbc_Cv_ReceiptAgentCode,
	s.Cv_SubscriberName		AS	Smbc_Cv_SubscriberName,
	s.RegistDate			AS	Smbc_RegistDate,
	s.UpdateDate			AS	Smbc_UpdateDate,

    jnb.JnbId               AS  Jnb_Id,
    jnb.DisplayName         AS  Jnb_DisplayName,
    jnb.BankCode            AS  Jnb_BankCode,
    jnb.BankName            AS  Jnb_BankName,
    jgr.GroupCount          AS  Jnb_GroupCount,
    jgr.TotalAccounts       AS  Jnb_TotalAccounts,
    smbcpa.SmbcpaId         AS  Smbcpa_Id,
    smbcpa.DisplayName      AS  Smbcpa_DisplayName,
    smbcpa.BankCode         AS  Smbcpa_BankCode,
    smbcpa.BankName         AS  Smbcpa_BankName,
    sgr.GroupCount          AS  Smbcpa_GroupCount,
    sgr.TotalAccounts       AS  Smbcpa_TotalAccounts

FROM
    (
        SELECT OemId, OemNameKj AS NameKj, ReclaimAccountPolicy FROM T_Oem
        UNION ALL
        SELECT 0 AS OemId, 'キャッチボール' AS NameKj, 0 AS ReclaimAccountPolicy
    ) v LEFT OUTER JOIN
    T_OemBankAccount b ON b.OemId = v.OemId
        LEFT OUTER JOIN
    T_OemYuchoAccount y ON y.OemId = v.OemId
        LEFT OUTER JOIN
    T_OemCvsAccount c ON c.OemId = v.OemId
        LEFT OUTER JOIN
    M_CvsReceiptAgent r ON r.ReceiptAgentId = c.ReceiptAgentId
		LEFT OUTER JOIN
	T_SmbcRelationAccount s ON s.OemId = v.OemId
        LEFT OUTER JOIN
    T_Jnb jnb ON (jnb.OemId = b.OemId AND IFNULL(jnb.ValidFlg, 0) = 1)
        LEFT OUTER JOIN
    (
        SELECT JnbId, COUNT(*) AS GroupCount, SUM(TotalAccounts) AS TotalAccounts
        FROM T_JnbAccountGroup
        GROUP BY JnbId
    ) jgr ON jgr.JnbId = jnb.JnbId
        LEFT OUTER JOIN
    T_Smbcpa smbcpa ON (smbcpa.OemId = b.OemId AND IFNULL(smbcpa.ValidFlg, 0) = 1)
        LEFT OUTER JOIN
    (
        SELECT SmbcpaId, COUNT(*) AS GroupCount, SUM(TotalAccounts) AS TotalAccounts
        FROM T_SmbcpaAccountGroup
        GROUP BY SmbcpaId
    ) sgr ON sgr.SmbcpaId = smbcpa.SmbcpaId
%s
ORDER BY
    v.OemId
EOQ;
        $where = '';
        if(!empty($expressions)) {
            $where = 'WHERE ' . join(' AND ', $expressions) . PHP_EOL;
        }
        return sprintf($q, $where);
    }

    /**
     * 指定請求履歴を元に請求口座情報をT_OemClaimAccountInfoへ新規追加する(2019-10-01 以降)
     *
     * @param int $chSeq 請求履歴SEQ
     * @param boolean $is_strict 印刷済み請求履歴は許容しない「厳密モード」かの指定。省略時はtrue
     * @param array $option
     * @return int T_OemClaimAccountInfo.ClaimAccountSeq
     */
    public function insertClaimAccountInfo2($chSeq, $is_strict = true, $option = null)
    {
        if ( !isset($_SESSION['claimAc1Sec']) ) { $_SESSION['claimAc1Sec'] = 0; }
        if ( !isset($_SESSION['claimAc2Sec']) ) { $_SESSION['claimAc2Sec'] = 0; }
        $stTime = microtime(true); // 処理経過時間測定
        $data = $this->createClaimAccountInfoData2($chSeq, $is_strict);
        $_SESSION['claimAc1Sec'] += (microtime(true) - $stTime);
        $data['Status'] = 0;
        try {
            $caSeq = $this->getClaimAccountInfoTable()->saveNew($chSeq, $data);

            $update_data = array(
                    'Status' => 1
            );

            $smbc_registered = false;
            $smbcpa_used = false;
            switch($data['Bk_ServiceKind']) {
                case self::SERVICE_KIND_SMBC :
                    $smbc_update_data = $this->registerToSmbcRelationService($chSeq, $caSeq);
                    if(!empty($smbc_update_data)) {
                        $update_data = array_merge($update_data, $smbc_update_data);
                        $update_data['Bk_ServiceKind'] = self::SERVICE_KIND_SMBC;
                        $smbc_registered = true;
                    } else {
                        // SMBC連携に失敗した場合、エラーメッセージを例外として投げる
                        $message = null;
                        if (!$this->isSmbcSucceed($caSeq, $message)) {
                            throw new LogicClaimException($message, LogicClaimException::ERR_CODE_SMBC);
                        }
                    }
                    break;
            }
            if(!$smbc_registered) {
                // SMBC決済ステーションを利用しない／利用できない場合はSMBCバーチャル口座を払い出す
                $stTime = microtime(true); // 処理経過時間測定
                $smbcpa_update_data = $this->openSmbcpaAccount($chSeq);
                $_SESSION['claimAc2Sec'] += (microtime(true) - $stTime);
                if(!empty($smbcpa_update_data)) {
                    $update_data = array_merge($update_data, $smbcpa_update_data);
                    $update_data['Bk_ServiceKind'] = self::SERVICE_KIND_SMBCPA;
                    $smbcpa_used = true;
                }
            }

            //ペイジー収納番号発番
            $sql = "SELECT OrderSeq FROM T_ClaimHistory WHERE Seq = :Seq";
            $oseq = $this->_adapter->query($sql)->execute(array(':Seq' => $chSeq))->current()['OrderSeq'];

            $sql = "SELECT OemId FROM T_Order WHERE OrderSeq = :OrderSeq";
            $oemid = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['OemId'];

            $claimPattern = isset($option['ClaimPattern']) ? $option['ClaimPattern'] : 8; //指定が無い場合は連携を行わない

            $logicpayeasy = new LogicPayeasy($this->_adapter, $this->_logger);
            if($logicpayeasy->isPayeasyOem($oemid) && $claimPattern <= 7){

                $this->_logger->info('ペイジー収納番号発番処理 ClaimHistorySeq:'.$chSeq.' OrderSeq:'.$oseq.' ClaimPattern:'.$claimPattern );

                $responseBody = '';
                $message = null;

                //収納番号発番処理
                $isSuccess = $logicpayeasy->getBkNumber($chSeq, $responseBody, $message);
                if($isSuccess == false){
                    //ClaimErrorにエラー情報を登録
                    $mdlce = new TableClaimError($this->_adapter);
                    $mdlce->saveNew ( array (
                            'OrderSeq' => $oseq,
                            'ErrorCode' => LogicClaimException::ERR_CODE_PAYEASY,
                            'ErrorMsg' => $message
                    ) );
                    throw new LogicClaimException($message, LogicClaimException::ERR_CODE_PAYEASY);
                }
                //更新データに収納機関受付番号と加盟店取引番号を追加する
                $update_data['ConfirmNumber'] = $responseBody['stran'];
                $update_data['CustomerNumber'] = $responseBody['bktrans'];
            }

            // 請求口座情報を更新する
            $this->getClaimAccountInfoTable()->saveUpdate($update_data, $caSeq);
        }
        catch(\Exception $err) {
            // DBインサート時の例外のみハンドルしてログ出力
            $this->info(sprintf('[insertClaimAccountInfo : chSeq = %s, strict = %s] ERROR !!! message = %s (%s)', $chSeq, $is_strict ? 'YES' : 'NO', $err->getMessage(), get_class($err)));
            throw $err;
        }
    }

    /**
     * 指定請求履歴に関連付けられた請求口座情報を更新する(2019-10-01 以降)
     *
     * @param int $chSeq 請求履歴SEQ
     * @param boolean $is_strict 印刷済み請求履歴は許容しない「厳密モード」かの指定。省略時はtrue
     */
    public function updateClaimAccountInfo2($chSeq, $is_strict = true)
    {
        $ri = $this->getClaimAccountInfoTable()->findByClaimHistorySeq($chSeq);
        if (!($ri->count() > 0)) {
            // 未挿入の場合はエラー
            $this->info(sprintf('[updateClaimAccountInfo : chSeq = %s, strict = %s] oops !!! target row not found !!!', $chSeq, $is_strict ? 'YES' : 'NO'));
            throw new \Exception('更新対象の請求口座情報が見つかりませんでした');
        }
        $current = $ri->current();

        $data = $this->createClaimAccountInfoData2($chSeq, $is_strict);
        $data['InnerSeq'] = $current['InnerSeq'];
        $data['Status'] = 1;

        try {
            $this->getClaimAccountInfoTable()->saveUpdate($data, $current['ClaimAccountSeq']);
        }
        catch(\Exception $err) {
            // 更新エラーはログに出力
            $this->info(sprintf('[updateClaimAccountInfo : chSeq = %s, strict = %s] ERROR !!! message = %s (%s)', $chSeq, $is_strict ? 'YES' : 'NO', $err->getMessage(), get_class($err)));
        }
    }

    /**
     * T_OemClaimAccountInfoに登録するためのデータを
     * 請求履歴SEQから生成する(2019-10-01 以降)
     *
     * @param int $chSeq 請求履歴SEQ
     * @param boolean $is_strict 印刷済み請求履歴は許容しない「厳密モード」かの指定。省略時はtrue
     * @return array T_OemClaimAccountInfoのスキーマに適合する連想配列
     */
    public function createClaimAccountInfoData2($chSeq, $is_strict = true)
    {
        try {
            // 請求履歴データを取得
            $ri = $this->getClaimHistoryTable()->find($chSeq);
            // 履歴が存在しない場合または厳密モード指定ですでに印刷済みの場合はエラー
            if (!($ri->count() > 0)) {
                throw new \Exception('指定の請求履歴が見つかりませんでした');
            }

            $his = $ri->current();
            if($is_strict && $his['PrintedFlg']) {
                throw new \Exception('指定の請求書はすでに印刷済みです');
            }

            // 注文データを取得
            $ri = $this->getOrderTable()->find($his['OrderSeq']);
            // 注文が存在しない場合はエラー
            if (!($ri->count() > 0)) {
                throw new \Exception('注文情報が見つかりませんでした');
            }
            $ord = $ri->current();

            // OEM ID確定（→ null　は 0：キャッチボール に読み替え）
            $oemId = (int)$ord['OemId'];

            // OEMデータを取得
            $ri = $this->getOemTable()->find($oemId);
            if($oemId != 0 && !($ri->count() > 0)) {
                // OEM IDが0以外でOEMデータが存在しないのは異常事態
                throw new \Exception('OEMの設定情報が見つかりません');
            }
            $oemData = $ri->current();

            // 今回の請求に使用する口座関連情報確定
            // キャッチボール分や初回請求、OEM先設定で再請求を自社印刷にするポリシーの場合は確定しているOEM ID、
            // そうでない場合（＝再請求をCBにする設定のOEM先の再請求）はキャッチボールの情報で構築する
            $ri = $this->findClaimAccountsByOemId(
            ($oemId == 0 || $his['ClaimPattern'] == 1 || !$oemData['ReclaimAccountPolicy']) ? $oemId : 0
            );
            $accData = $ri->current();

            // ゆうちょ請求口座情報をコードマスターから取得
            $ri = $this->findClaimAccountInfoForCodeMaster($oemId, $his['ClaimPattern']);
            if ($ri->count() > 0) {
                // 値が取れた場合、コードマスターの設定で上書き
                $rowCode = $ri->current();
                $accData['Yu_SubscriberName'] = $rowCode['Class3'];
                $accData['Yu_AccountNumber']  = $rowCode['Class2'];
            }

            // 加盟店データを取得
            $ri = $this->getEnterpriseTable()->find($ord['EnterpriseId']);
            // 加盟店データが存在しない場合はエラー
            if (!($ri->count() > 0)) {
                throw new \Exception('加盟店の設定情報が見つかりません');
            }
            $ent = $ri->current();
            if (strlen($his['EnterpriseBillingCode']) > 0 && $ent['ChargeClass'] == 2) {
                $ri = $this->findClaimAccountInfoForCodeMaster2($oemId);
                if ($ri->count() > 0) {
                    // 値が取れた場合、コードマスターの設定で上書き
                    $rowCode = $ri->current();
                    $accData['Yu_SubscriberName'] = $rowCode['Class3'];
                    $accData['Yu_AccountNumber']  = $rowCode['Class2'];
                    $accData['Yu_ChargeClass']    = 2;
                }
            }

            // サイトデータを取得
            $ri = $this->getSiteTable()->findSite($ord['SiteId']);
            // サイトデータが存在しない場合はエラー
            if (!($ri->count() > 0)) {
            	throw new \Exception('サイト情報が見つかりません');
            }
            $siteData = $ri->current();
//            $mSubscriberCodeData = array();
//            if(!empty($siteData['SubscriberCode'])){
//            	//
//            	$ri = $this->getSubscriberCodeTable()->findReceiptAgentIdSubscriberCode($siteData['ReceiptAgentId'],$siteData['SubscriberCode']);
//                if (count($ri) > 0) {
//                    $mSubscriberCodeData = $ri;
//            	}
//            }

            // 不要カラムを除去
            $accData = $this->trimAccountInfoData($accData);

            // 返却データ構築開始、以下のカラムを追加
            //   請求履歴SEQ
            //   請求用紙モード
            $data = array_merge(
            array(
                    'ClaimHistorySeq' => $chSeq,
                    'ClaimLayoutMode' => $this->calcClaimLayoutMode($chSeq)
            ), $accData);

            // 有効なアカウントデータが設定されていない場合は例外
            if(empty($data['Bk_AccountNumber']) || empty($data['Yu_AccountNumber']) || empty($data['Cv_SubscriberCode'])) {
                throw new \Exception('請求口座設定が設定されていないか不完全な状態です');
            }

            // 請求履歴.請求パターンが1 or 2 かつ、加盟店.NTTスマートトレード利用フラグが1：利用するの場合、以下の3項目を書き換える。
            if ( ($his['ClaimPattern'] == '1' || $his['ClaimPattern'] == '2') && $ent['NTTSmartTradeFlg'] == '1' ) {
                $data['Cv_BarcodeLogicName'] = 'NTTSmartTrade';
                $data['Cv_ReceiptAgentCode'] = $this->getSystemPropertyTable()->getValue('[DEFAULT]', 'systeminfo', 'ReceiptAgentCode');
                $data['Cv_SubscriberCode'] = $this->getSystemPropertyTable()->getValue('[DEFAULT]', 'systeminfo', 'SubscriberCode');
            }
            // 請求履歴.請求パターンが1以外、且つ、バーコードロジックが決済ナビの場合、バーコードロジックを＠ペイメントに変更する
            if ($his['ClaimPattern'] != 1 && $data['Cv_BarcodeLogicName'] == 'KessaiNavi') {
                $data['Cv_BarcodeLogicName'] = 'AtPayment';
            }
            //加盟店.LINE Pay利用（＠ペイメント用）が1：利用する かつ コンビニ収納代行会社.バーコード生成ロジッククラス名がAtPaymentの場合
            if( $ent['NTTSmartTradeFlg'] == '0' &&  $data['Cv_BarcodeLogicName'] == 'AtPayment'){
            	$data['Cv_SubscriberCode'] = str_pad($siteData['SubscriberCode'],5,'0');
            	$data['Cv_SiteId'] = sprintf("%06d",$siteData['SiteId']);
                /*
            	if(!empty($siteData['SubscriberCode']) && $mSubscriberCodeData['LinePayUseFlg']==1 ){
            		$data['Cv_SiteId'] = sprintf("%06d",$siteData['SiteId']);
            	}else{
            		$data['Cv_SiteId'] = sprintf("%06d",0);
				}
                */
            }
            // サイト.三菱UFJ＝ON、且つ、初回請求の場合、CvsNetバーコードロジックを利用する
            if (($his['ClaimPattern'] == 1) && ($siteData['MufjBarcodeUsedFlg'] == '1')) {
                $cvs = $this->getCvsNetRecord();
                if(!$cvs) {
                    ;
                } else {
                    $data['Cv_ReceiptAgentName'] = $cvs['ReceiptAgentName'];
                    $data['Cv_BarcodeLogicName'] = $cvs['BarcodeLogicName'];
                    $data['Cv_ReceiptAgentCode'] = $cvs['ReceiptAgentCode'];
                    $data['Cv_SubscriberCode'] = $siteData['MufjBarcodeSubscriberCode'];
                }
            }

              // ゆうちょMT/DTデータを補完
            $data = $this->fillYuchoMtData($data);

            // 消費税額等 算出
            $data = $this->calcShareTaxAmount($data);

            $data['Ent'] = $ord['EnterpriseId'];

            // CVSバーコードデータを補完
            $data = $this->fillBarcodeData($data);

            return $data;

        }
        catch(\Exception $err) {
            $msg = $err->getMessage() . '%s';
            $info = '';
            try {
                if($oemId != null) {
                    $info = sprintf(' (OemId = %s, OrderSeq = %s, Seq = %s)', $oemId, $ord['OrderSeq'], $chSeq);
                } else
                    if($ord != null) {
                        $info = sprintf(' (OrderSeq = %s, Seq = %s)', $ord['OrderSeq'], $chSeq);
                    }
            }
            catch(\Exception $innerError) {
                $info = sprintf(' (Seq = %s)', $chSeq);
            }
            // エラーをロギングするために例外をハンドル
            $this->info(sprintf('[createClaimAccountInfoData chSeq = %s] ERROR !!! messege = %s (%s)', $chSeq, sprintf($msg, $info), get_class($err)));
            throw $err;
        }
    }

    /**
     * findClaimAccountsByOemIdで取得したデータから、
     * T_OemClaimAccountInfoに不要なカラムを除去する
     *
     * @access protected
     * @param array $data findClaimAccountsByOemIdで取得したアカウントデータ
     * @return array
     */
    protected function trimAccountInfoData(array $data) {
        $cols = array(
            'Bk_AccountId',
            'Bk_ModifiedDate',
            'Yu_AccountId',
            'Yu_ModifiedDate',
            'Cv_AccountId',
            'Cv_ReceiptAgentId',
            'Cv_ModifiedDate',
            'Cv_InvalidFlg',

			// T_SmbcRelationAccount由来の情報はすべて不要
			'Smbc_DisplayName',
			'Smbc_ApiVersion',
			'Smbc_BillMethod',
			'Smbc_KessaiId',
			'Smbc_ShopCd',
            'Smbc_SyunoCoCd1',
            'Smbc_SyunoCoCd2',
            'Smbc_SyunoCoCd3',
			'Smbc_SyunoCoCd4',
			'Smbc_SyunoCoCd5',
			'Smbc_SyunoCoCd6',
			'Smbc_ShopPwd1',
			'Smbc_ShopPwd2',
			'Smbc_ShopPwd3',
			'Smbc_ShopPwd4',
			'Smbc_ShopPwd5',
			'Smbc_ShopPwd6',
			'Smbc_SeikyuuName',
			'Smbc_SeikyuuKana',
			'Smbc_HakkouKbn',
			'Smbc_YuusousakiKbn',
			'Smbc_Yu_SubscriberName',
			'Smbc_Yu_AccountNumber',
			'Smbc_Yu_ChargeClass',
			'Smbc_Yu_SubscriberData',
			'Smbc_Cv_ReceiptAgentName',
            'Smbc_Cv_ReceiptAgentCode',
			'Smbc_Cv_SubscriberName',
			'Smbc_RegistDate',
            'Smbc_UpdateDate'
        );
        foreach($cols as $col) {
            if(isset($data[$col])) {
                unset($data[$col]);
            }
        }
        return $data;
    }

    /**
     * 指定のデータにゆうちょMT/DT用データを補完する
     * ※：2014.9.13現在MTのみ実装済み
     *
     * @access protected
     * @param array $data
     * @return array
     */
    protected function fillYuchoMtData(array $data) {
        $util = new LogicYuchoUtility();

        // バーコード生成に必要な注文SEQ、請求日、バーコード用の再請求回数、支払期限、支払金額を取得する
        $subData = $this->getSubDataForBarcode($data['ClaimHistorySeq'])->current();

        // 上段データ
        if(strlen($data['Yu_AccountNumber']) != 11) {
            // 登録桁数が11桁でない場合は12桁に補完した上で6桁目を除去
            $accNumber = substr(sprintf('%012d', 0).$data['Yu_AccountNumber'], -12);
            $accNumber = join('', array(substr($accNumber, 0, 5), substr($accNumber, -6)));
        } else {
            // 登録桁数が11桁の場合はそのまま使用
            $accNumber = $data['Yu_AccountNumber'];
        }
        $data1_part = array(
                $accNumber,                                                 // 口座番号11桁
                sprintf('%011d', (int)$subData['ClaimAmount']),             // 請求金額ゼロ詰11桁
                $data['Yu_ChargeClass'],                                    // 払込負担区分
                '00000',                                                    // 予備ゼロ詰5桁
                substr(sprintf('%09d', 0).$data['Yu_SubscriberData'], -9)   // 加入者固有データ9桁
        );
        $data1 = join('', $data1_part);

        // 下段データ
        $data2 = sprintf('%042s', $subData['OrderSeq']);                                // 注文SEQゼロ詰42桁

        $cd1 = $util->calcMtCode($data1);
        $cd2 = $util->calcMtCode($data2);

        return array_merge(
        $data,
        array(
                'Yu_MtOcrCode1' => sprintf('%s%s   X', $cd1, $data1),   // 上段は末尾に「   X」（4桁）を付与する
                'Yu_MtOcrCode2' => $cd2 . $data2
        ) );
    }

    /**
     * 指定のデータにコンビニバーコードデータを補完する
     *
     * @access protected
     * @param array $data
     * @return array
     */
    protected function fillBarcodeData(array $data) {
        $chSeq = $data['ClaimHistorySeq'];

        /** @var LogicStampFee 印紙代関連ユーティリティ */
        $stampFeeSettings = $this->getSystemPropertyTable()->getStampFeeSettings();
        $sfUtil = LogicBarcodeDataCvs::createStampFeeLogic($stampFeeSettings);

        // バーコード生成に必要な注文SEQ、請求日、バーコード用の再請求回数、支払期限、支払金額を取得する
        $subData = $this->getSubDataForBarcode($chSeq)->current();

        // 支払金額がコンビニ支払上限額以内ならバーコードデータを生成
        if(((int)$subData['ClaimAmount']) <= self::CLAIM_AMOUNT_LIMIT_AMOUNT) {
            /** @var LogicBarcodeDataCvsAbstract バーコードデータ生成ロジック */
            $bcGen = LogicBarcodeDataCvs::createGenerator(
                self::getBarcodeLogicClassName($data['Cv_BarcodeLogicName']),
                $data['Cv_ReceiptAgentCode'],
                $data['Cv_SubscriberCode']
            );

            if(date('Y-m-d') > '2019-09-30'){
                $hisTable = $this->getClaimHistoryTable();
                $claimtotal = (int)$hisTable->getClaimAmount($data['ClaimHistorySeq']);
                $ClaimAmount = $claimtotal - $data['TaxAmount'];

                if ($ClaimAmount < 50000 ){
                    $claimtotal += 1;
                }

                $bcGen
                    ->setUniqueSequence($subData['OrderSeq'])
                    ->setReIssueCount($subData['ReIssueCount'])
                    ->setLimitDate($subData['Bc_LimitDate'])
                    ->setPaymentMoney($subData['ClaimAmount'])
                    ->setStampFlagThresholdPrice($claimtotal);

            } else {
            // バーコードロジックをセットアップ
                $bcGen
                    ->setUniqueSequence($subData['OrderSeq'])
                    ->setReIssueCount($subData['ReIssueCount'])
                    ->setLimitDate($subData['Bc_LimitDate'])
                    ->setPaymentMoney($subData['ClaimAmount'])
                    ->setStampFlagThresholdPrice($sfUtil->getStampFeeThresholdAt($subData['ClaimDate']));
            }
            // CB店子管理コード
            $bcGen->setSiteId($data['Cv_SiteId']);

            // バーコードデータを生成
            $bcData = $bcGen->generate();
            $bcStrings = $bcGen->generateString();
        } else {
            // 支払金額が30万超の場合はメッセージを入れておく
            $bcData = self::CLAIM_AMOUNT_OVER_LIMIT_MESSAGE;
            $bcStrings = array('', ''); // 表示文字列は空にしておく
        }

        // 元データにマージして返却
        return array_merge(
            array(
                    'Cv_BarcodeData' => $bcData,
                    'Cv_BarcodeString1' => $bcStrings[0],
                    'Cv_BarcodeString2' => $bcStrings[1]
            ), $data);
    }

    /**
     * 指定の請求履歴SEQから、バーコード生成に必要な各種データを取得する
     *
     * @access protected
     * @param int $chSeq 請求履歴SEQ
     * @return ResultInterface
     */
    protected function getSubDataForBarcode($chSeq) {
        $q = <<<EOQ
SELECT
    h.OrderSeq,
    h.ClaimDate,
	(
		(
			SELECT COUNT(*) FROM T_ClaimHistory
	 		WHERE OrderSeq = h.OrderSeq
            AND   ValidFlg = 1
	 	) - 1
	) AS ReIssueCount,
	CASE WHEN s.BarcodeLimitDays=999 THEN '999999'
	ELSE  DATE_FORMAT(DATE_ADD(h.LimitDate, INTERVAL s.BarcodeLimitDays DAY), '%y%m%d')
	END  AS Bc_LimitDate,
    h.ClaimAmount - IFNULL(c.ReceiptAmountTotal,  0) AS ClaimAmount
FROM
	T_ClaimHistory h
    LEFT OUTER JOIN T_ClaimControl c ON h.ClaimId = c.ClaimId
    LEFT OUTER JOIN T_Order o ON h.OrderSeq = o.OrderSeq
    LEFT OUTER JOIN T_Site s ON o.SiteId = s.SiteId
WHERE
	h.Seq = :Seq
EOQ;

        return $this->_adapter->query($q)->execute(array(':Seq' => $chSeq));
    }

	/**
	 * 指定請求履歴の商品代金から内消費税額を算出する
	 *
	 * @param int $seq 請求履歴SEQ
	 * @return int 消費税額
	 * TODO: 消費税算出基準額を請求金額合計に変更する可能性あり → getClaimAmount()に切り替える
	 */
	public function calcTaxAmount($seq) {
        $sysProps = $this->getSystemPropertyTable();

        $hisTable = $this->getClaimHistoryTable();

        $sql = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";
        $his = $this->_adapter->query($sql)->execute(array(':Seq' => $seq))->current();
        $price = (int)$hisTable->getClaimAmount($seq);
        $taxRate = $sysProps->getTaxRateAt($his['ClaimDate']);

        // 入金の有無
        $sql = " SELECT * FROM T_ClaimControl WHERE ClaimId = :ClaimId ";
        $cc = $this->_adapter->query($sql)->execute(array(':ClaimId' => $his['ClaimId']))->current();
        if ($cc !== false) {
            $price -= $cc['ReceiptAmountTotal'];
        }

        // 注文に外税(DataClass:4)がある時の対応
        $sql  = " SELECT COUNT(oi.OrderItemId) AS cnt, SUM(oi.SumMoney) AS SumMoney FROM T_Order o ";
        $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
        $sql .= " WHERE  o.P_OrderSeq = :OrderSeq ";
        $sql .= " AND    o.Cnl_Status = 0 ";
        $sql .= " AND    oi.DataClass = 4 ";
        $row = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $his['OrderSeq']))->current();
        if ((int)$row['cnt'] > 0) {
            return $row['SumMoney'];
        }

        return ceil($price / (100 + $taxRate) * $taxRate);
	}

	/**
	 * 指定請求履歴の商品代金から内消費税額を算出する(2019-10-01 以降)
	 *
	 * @param array $data
	 * @return array
	 * TODO: 消費税算出基準額を請求金額合計に変更する可能性あり → getClaimAmount()に切り替える
	 */
	public function calcShareTaxAmount(array $data) {
	    $sysProps = $this->getSystemPropertyTable();

	    $hisTable = $this->getClaimHistoryTable();
	    $seq = $data['ClaimHistorySeq'];

	    $sql = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";
	    $his = $this->_adapter->query($sql)->execute(array(':Seq' => $seq))->current();
	    $price = (int)$hisTable->getClaimAmount($seq);
	    // $taxRate = $sysProps->getTaxRateAt($his['ClaimDate']);

	    $sql  = " SELECT oi.* ";
	    $sql .= " FROM   T_Order o ";
	    $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
	    $sql .= " WHERE  o.P_OrderSeq  = :OrderSeq ";
	    $sql .= " AND    o.Cnl_Status  = 0 ";
	    $sql .= " AND    oi.DataClass != 4 ";
        $sql .= " AND    oi.ValidFlg   = 1 ";
	    $oi = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $his['OrderSeq']));

	    $rows = ResultInterfaceToArray($oi);

	    // 変数初期化
	    $total = 0;
	    $ClaimAmount_0 = 0;
	    $ClaimAmount_8 = 0;
	    $ClaimAmount_10 = 0;
	    $discount = 0;

	    foreach($rows as $datas) {
	        if ($datas['DataClass'] == 1) {
	            if($datas['TaxRate'] == 0 && $datas['SumMoney'] > 0) { // 0％対象商品
	                $ClaimAmount_0 += $datas['SumMoney'];
	            } else if($datas['TaxRate'] == 8 && $datas['SumMoney'] > 0) { // 8％対象商品
	                $ClaimAmount_8 += $datas['SumMoney'];
	            } elseif($datas['TaxRate'] == 10 && $datas['SumMoney'] > 0) { // 10％対象商品
	                $ClaimAmount_10 += $datas['SumMoney'];
	            } elseif($datas['SumMoney'] < 0) {  // 値引き額
	                $discount += -1 * $datas['SumMoney'];
	            }
	        } else {
	            $ClaimAmount_10 += $datas['SumMoney'];
	        }
	    }


	    $total = $ClaimAmount_0 + $ClaimAmount_8 + $ClaimAmount_10;

	    // 注文に外税(DataClass:4)がある時の対応
	    $sql  = " SELECT COUNT(oi.OrderItemId) AS cnt, SUM(oi.SumMoney) AS SumMoney FROM T_Order o ";
	    $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
	    $sql .= " WHERE  o.P_OrderSeq = :OrderSeq ";
	    $sql .= " AND    o.Cnl_Status = 0 ";
	    $sql .= " AND    oi.DataClass = 4 ";
        $sql .= " AND    oi.ValidFlg  = 1 ";
	    $taxrow = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $his['OrderSeq']))->current();

	    $outtax = 0;

	    if((int)$taxrow['cnt'] > 0) {
	        $outtax = $taxrow['SumMoney'];
	    }

	    // 督促時の再請求手数料を算出
	    $reclaimfee = $price - $total + $discount - $outtax;

	    // 再請求手数料を10％対象商品に加算
	    $ClaimAmount_10 = $ClaimAmount_10 + $reclaimfee;
        //消費税率0%の合計値がHigh:1,Middle:2,Low:3
	    $taxFlag0 = 0;
	    //消費税率8%の合計値がHigh:1,Middle:2,Low:3
	    $taxFlag8 = 0;
	    // 金額が大きい税率を判定
	    if($ClaimAmount_8 >= $ClaimAmount_10){
	        if($ClaimAmount_8 >= $ClaimAmount_0){
	            $high = $ClaimAmount_8;
	            $taxrateHigh = 8;
	            $taxFlag8 = 1;
	            if($ClaimAmount_0 >= $ClaimAmount_10){
	                $middle = $ClaimAmount_0;
	                $taxrateMiddle = 0;
	                $low = $ClaimAmount_10;
	                $taxrateLow = 10;
	                $taxFlag0 = 2;
	            }else{
	                $middle = $ClaimAmount_10;
	                $taxrateMiddle = 10;
	                $low = $ClaimAmount_0;
	                $taxrateLow = 0;
	                $taxFlag0 = 3;
	            }
	        } else {
	            $high = $ClaimAmount_0;
	            $taxrateHigh = 0;
	            $middle = $ClaimAmount_8;
	            $taxrateMiddle = 8;
	            $low = $ClaimAmount_10;
	            $taxrateLow = 10;
	            $taxFlag0 = 1;
	            $taxFlag8 = 2;
	        }
	    } else{
	        if($ClaimAmount_0 >= $ClaimAmount_10){
	            $high = $ClaimAmount_0;
	            $taxrateHigh = 0;
	            $taxFlag0 = 1;
	            $middle = $ClaimAmount_10;
	            $taxrateMiddle = 10;
	            $low = $ClaimAmount_8;
	            $taxrateLow = 8;
	            $taxFla8 = 3;
	        } else {
	            $high = $ClaimAmount_10;
	            $taxrateHigh = 10;
	            if($ClaimAmount_0 >= $ClaimAmount_8){
	                $middle = $ClaimAmount_0;
	                $taxrateMiddle = 0;
	                $low = $ClaimAmount_8;
	                $taxrateLow = 8;
	                $taxFlag0 = 2;
	                $taxFlag8 = 3;
	            }else{
	                $middle = $ClaimAmount_8;
	                $taxrateMiddle = 8;
	                $low = $ClaimAmount_0;
	                $taxrateLow = 0;
	                $taxFlag0 = 3;
	                $taxFlag8 = 2;
	            }
	        }
	    }
	   	    // 入金の有無
	    $sql = " SELECT * FROM T_ClaimControl WHERE ClaimId = :ClaimId ";
	    $cc = $this->_adapter->query($sql)->execute(array(':ClaimId' => $his['ClaimId']))->current();
	    if ($cc !== false) {
	        $discount += $cc['ReceiptAmountTotal'];
	    }

	    // 値引き計算（按分）
	    if ($discount > 0) {
	        $discountHigh = round($discount * $high / ($total + $reclaimfee));
	        $discountMiddle = round($discount * $middle / ($total + $reclaimfee));
	        $discountLow  = $discount - ($discountHigh + $discountMiddle);

	        $amountHigh = $high - $discountHigh;
	        $amountMiddle = $middle - $discountMiddle;
	        $amountLow  = $low  - $discountLow;
	    } else {
	        $amountHigh = $high;
	        $amountMiddle  = $middle;
	        $amountLow  = $low;
	    }

	    //消費税率0%の合計値がHigh:1の場合
        if($taxFlag0 == 1){
            $amountHigh = $amountMiddle;
            if($taxFlag8 == 2){
                $taxrateHigh = 8;
            } else {
                $taxrateHigh = 10;
            }

        //消費税率0%の合計値がLow:3の場合
        } else if($taxFlag0 == 3){
            $amountLow = $amountMiddle;
            if($taxFlag8 == 2){
                $taxrateLow = 8;
            } else {

                $taxrateLow = 10;
            }
        }

		    // 消費税額の計算
	    if ((int)$taxrow['cnt'] > 0) { // 外税時
	        $taxHigh = ceil($amountHigh * $taxrateHigh / 100);
	        if ($taxHigh > $outtax){
	            $taxHigh = $outtax;
	        }
	        $taxLow  = $outtax - $taxHigh ;
	    } else {

	        $taxHigh = ceil($amountHigh / (100 + $taxrateHigh) * $taxrateHigh);
	        $taxLow  = ceil($amountLow  / (100 + $taxrateLow ) * $taxrateLow );
	    }

	    if ($taxrateHigh == 8){
	        $ClaimAmount_8  = $amountHigh;
	        $TaxAmount_8    = $taxHigh;
	        $ClaimAmount_10 = $amountLow;
	        $TaxAmount_10   = $taxLow;
	        $TaxAmount      = $taxHigh + $taxLow;
	    }else if($taxrateHigh == 10){
	        $ClaimAmount_8  = $amountLow;
	        $TaxAmount_8    = $taxLow;
	        $ClaimAmount_10 = $amountHigh;
	        $TaxAmount_10   = $taxHigh;
	        $TaxAmount      = $taxHigh + $taxLow;
	    }else if($taxFlag8 == 2){
	        $ClaimAmount_8  = $amountHigh;
	        $TaxAmount_8    = $taxHigh;
	        $ClaimAmount_10 = $amountLow;
	        $TaxAmount_10   = $taxLow;
	        $TaxAmount      = $taxHigh + $taxLow;
	    }else if($taxFlag8 == 3){
	        $ClaimAmount_8  = $amountLow;
	        $TaxAmount_8    = $taxLow;
	        $ClaimAmount_10 = $amountHigh;
	        $TaxAmount_10   = $taxHigh;
	        $TaxAmount      = $taxHigh + $taxLow;
	    }

	    // 外税額設定が無しは、内税計算時に請求金額が1円のものは内税額を0円
        if ((int)$taxrow['cnt'] == 0) {
            if ($ClaimAmount_8 == 1) {
                $TaxAmount = $TaxAmount - $TaxAmount_8;
                $TaxAmount_8 = 0;
            }
            if ($ClaimAmount_10 == 1) {
                $TaxAmount = $TaxAmount - $TaxAmount_10;
                $TaxAmount_10 = 0;
            }
        }
	   	    return array_merge(
            array(
	            'TaxAmount'      => $TaxAmount,
	            'SubUseAmount_1' => $ClaimAmount_8,
	            'SubTaxAmount_1' => $TaxAmount_8,
	            'SubUseAmount_2' => $ClaimAmount_10,
	            'SubTaxAmount_2' => $TaxAmount_10
	        ), $data);
	}

    /**
     * 指定OEM先の未印刷請求履歴件数を取得する
     *
     * @param int | null $oemId OEM ID
     * @return int
     */
    public function getClaimReservedCountByOemId($oemId = 0) {
        $oemId = (int)$oemId;
        $result = $this->getClaimReservedCounts($oemId);
        return isset($result[$oemId]) ? $result[$oemId] : 0;
    }

    /**
     * 指定OEM先または全OEM先の、OEM先ごとの未印刷請求履歴件数を取得する
     *
     * @param null | int $oemId 絞り込むOEM ID。未指定の場合はCBを含むすべてのOEM先が対象
     * @return array キーがOEM ID、値が未印刷履歴件数の連想配列
     */
    public function getClaimReservedCounts($oemId = null) {
        $q = <<<EOQ
SELECT
	v1.OemId,
	IFNULL(v2.ReservedCount, 0) AS ReservedCount
FROM
	(
		SELECT 0 AS OemId UNION ALL
		SELECT OemId FROM T_Oem WHERE IFNULL(ValidFlg, 0) = 1
	) v1 LEFT OUTER JOIN
	(
		SELECT
			IFNULL(o.OemId, 0) AS OemId,
			COUNT(*) ReservedCount
		FROM
			T_Order o INNER JOIN
			T_ClaimHistory h ON h.OrderSeq = o.OrderSeq
		WHERE
			h.PrintedFlg = 0 AND
			o.Cnl_Status = 0 AND
			IFNULL(o.CombinedClaimTargetStatus, 0) NOT IN (1, 2)
		GROUP BY
			IFNULL(o.OemId, 0)
	) v2 ON v1.OemId = v2.OemId
%s
ORDER BY
	v1.OemId
EOQ;

        $prm = array();
        if($oemId !== null) {
            $q = sprintf($q, " WHERE v1.OemId = :OemId ");
            $prm += array(':OemId' => $oemId);
        } else {
            $q = sprintf($q, '');
        }

        $result = array();
        $ri = $this->_adapter->query($q)->execute($prm);
        foreach($ri as $row) {
            $result[$row['OemId']] = (int)$row['ReservedCount'];
        }
        return $result;
    }

    /**
     * 指定のEAN128バーコードデータで請求書を発行している注文データを取得する
     *
     * @param string $barcodeData 44桁のEAN128形式バーコードデータ
     * @param null | boolean $ignoreNotPrinted 未印刷請求を無視するかのフラグ。省略時はtrue
     * @return ResultInterface
     */
    public function findOrderByCvsBarcodeData($barcodeData, $ignoreNotPrinted = true) {
        $accTable = $this->getClaimAccountInfoTable();
        $hisTable = $this->getClaimHistoryTable();
        $ordTable = $this->getOrderTable();

        foreach($accTable->findByCvsBarcodeData($barcodeData) as $accRow) {

            $sql = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";
            $ri = $this->_adapter->query($sql)->execute(array(':Seq' => $accRow['ClaimHistorySeq']));
            if (!($ri->count() > 0)) {
                continue;
            }
            $row = $ri->current();
            if($ignoreNotPrinted && !$row['PrintedFlg']) {
                continue;
            }

            return $ordTable->find($row['OrderSeq']);
        }
        return null;
    }

    /**
     * 指定のゆうちょMTコードで請求書を発行している注文データを取得する
     *
     * @param string $mtCode MTコード
     * @param null | int $target 1段目・2段目の指定。省略時は2（＝2段目）
     * @return ResultInterface
     */
    public function findOrderByYuchoMtCode($mtCode, $target = 2, $ignoreNotPrinted = true) {
        $accTable = $this->getClaimAccountInfoTable();
        $hisTable = $this->getClaimHistoryTable();
        $ordTable = $this->getOrderTable();

        foreach($accTable->findByYuchoMtCode($mtCode, $target) as $accRow) {

            $sql = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";
            $ri = $this->_adapter->query($sql)->execute(array(':Seq' => $accRow['ClaimHistorySeq']));
            if (!($ri->count() > 0)) {
                continue;
            }
            $row = $ri->current();
            if($ignoreNotPrinted && !$row['PrintedFlg']) {
                continue;
            }

            return $ordTable->find($row['OrderSeq']);
        }
        return null;
    }

    /**
     * 登録実績があるコンビニ加入者固有コードをすべて取得する
     *
     * @return ResultInterface
     */
    public function getAllCvsSubscriberCodes() {
        return $this->getClaimAccountInfoTable()->getCvSubscriberCodes();
    }

    /**
     * 登録実績があるゆうちょ加入者固有データをすべて取得する
     *
     * @return ResultInterface
     */
    public function getAllYuchoSubscriberDatas() {
        return $this->getClaimAccountInfoTable()->getYuSubscriberDatas();
    }

    /**
     * 指定請求履歴の請求用紙モードを算出する
     *
     * @access protected
     * @param int $chSeq 請求履歴SEQ
     * @return 請求用紙モード（0：通常、1：封書、2：同梱）
     */
    protected function calcClaimLayoutMode($chSeq) {
        // 同梱請求の場合は2
        // 初回請求の場合はサイトの初回請求用紙モード（0または1）
        // 再１の場合はサイトの初回請求用紙モード（0または1）
        // それ以外は0
        $q = <<<EOQ
SELECT
	(CASE
		WHEN his.EnterpriseBillingCode IS NOT NULL THEN 2
		ELSE
			(CASE
				WHEN his.ClaimPattern = 1 THEN
					(CASE sit.FirstClaimLayoutMode
						WHEN 1 THEN 1
						ELSE 0
					END)
				WHEN his.ClaimPattern = 2 THEN
					(CASE sit.FirstClaimLayoutMode
						WHEN 1 THEN 1
						ELSE 0
				    END)
				ELSE 0
			END)
	END) AS ClaimLayoutMode
FROM
	T_ClaimHistory his INNER JOIN
	T_Order ord ON ord.OrderSeq = his.OrderSeq INNER JOIN
	T_Site sit ON sit.SiteId = ord.SiteId INNER JOIN
	T_Enterprise ent ON ent.EnterpriseId = ord.EnterpriseId LEFT OUTER JOIN
	T_Oem oem ON oem.OemId = ent.OemId LEFT OUTER JOIN
	T_OemBankAccount oba ON oba.OemId = oem.OemId
WHERE
	his.Seq = :Seq
EOQ;
        // 当該行が見つからない場合は0
        $row = $this->_adapter->query($q)->execute(array(':Seq' => $chSeq))->current();
        return ($row) ? (int)$row['ClaimLayoutMode'] : 0;
    }

    /**
     * 指定の請求履歴の注文情報を元にSMBC決済ステーションへ請求情報登録する
     *
     * @access protected
     * @var int $chSeq 請求履歴SEQ
     * @var int $caSeq OEM請求口座SEQ
     * @return array 請求情報登録で獲得できた口座情報
     */
    protected function registerToSmbcRelationService($chSeq, $caSeq) {
        $his = $this->getClaimHistoryTable()->find($chSeq)->current();
        if(!$his) throw new \Exception('claim history not found !!!');
        if(!in_array($his['ClaimPattern'], array(1, 2))) {
            // 初回請求、再請求1以外は決済ステーション連携は行わない
            return array();
        }

        /** @var LogicSmbcRelationServiceRegister */
        $service = LogicSmbcRelation::openRegisterService($this->getAdapter(), $this->getLogger());

        $dep_map = array(
                '1' => 0,
                '2' => 1
        );

        try {
            // 請求情報登録を実行
            $result = $service->sendTo($chSeq);

            // 受信データから各種口座情報を抽出する準備
            $logTable = $this->getSmbcRelationLogTable();

            // コンビニ情報の反映は必須
            $update_values = $logTable->extractCvsDataByClaimAccount($caSeq);

            // 銀行口座情報の反映
            $update_values = array_merge($update_values, $logTable->extractBankAccountDataByClaimAccount($caSeq));

            // 用紙レイアウトが封書または同梱の場合はゆうちょ口座情報も反映
            $oca = $this->getClaimAccountInfoTable()->find($caSeq)->current();
            if(!$oca) throw new \Exception ('claim account info not found !!!');
            if(in_array($oca['ClaimLayoutMode'], array(1, 2))) {
                $update_values = array_merge($update_values, $logTable->extractYuchoDataByClaimAccount($caSeq));
            }

            return $update_values;

        } catch(\Exception $err) {
            $this->info(sprintf('[registerToSmbcRelationService chSeq = %s, caSeq = %s] ERROR !!! messege = %s (%s)', $chSeq, $caSeq, $err->getMessage(), get_class($err)));
            // エラー情報はすでに決済ステーションログに永続化されているので、
            // ここでは戻り値を空の配列にして処理終了
            return array();
        }
    }

    /**
     * 指定の請求履歴の注文情報向けに、SMBCバーチャル口座を払い出す。
     * 対象の注文でSMBCバーチャル口座が利用できない場合、このメソッドの戻り値は空の配列となる
     *
     * @access protected
     * @var int $chSeq 請求履歴SEQ
     * @return array 払い出された銀行口座更新データ
     */
    protected function openSmbcpaAccount($chSeq)
    {
        $his = $this->getClaimHistoryTable()->find($chSeq)->current();
        if(!$his) throw new \Exception('claim history not found !!!');

        // 再請求以降の場合はOEM設定から再請求時の名義ポリシーを取得し、CB名義だったらSMBCバーチャル口座を利用しないよう制限する
        if($his['ClaimPattern'] > 1) {
            $order = $this->getOrderTable()->find($his['OrderSeq'])->current();
            $oem = $this->getOemTable()->find($order['OemId'])->current();
            if($oem && $oem['ReclaimAccountPolicy']) {
                return array();
            }
        }

        $smbcpaAccountLogic = new \models\Logic\Smbcpa\LogicSmbcpaAccount($this->getAdapter());

        try
        {
            $smbcpaAccount = $smbcpaAccountLogic->openAccount($his['OrderSeq']);
            return $smbcpaAccountLogic->getAccountDataForOemClaimAccountInfo($smbcpaAccount['AccountSeq']);
        } catch(\Exception $err) {
            // 例外はロギングのみにして空データを返す
            $this->info(sprintf('[openSmbcpaAccount chSeq = %s] ERROR !!! messege = %s (%s)', $chSeq, $err->getMessage(), get_class($err)));
            return array();
        }
    }

    /**
     * 指定注文で最後に出力した請求書の銀行口座／ゆうちょ口座情報を取得する。
     * 当該注文で再請求7を出力した実績がある場合、このメソッドが返す口座情報は当該注文の
     * OEM先に設定されている固定口座となる。
     *
     * 戻り値はフラットな連想配列で以下のキーを持つ。
     * - Bk_BankCode			銀行コード
     * - Bk_BranchCode			支店コード
     * - Bk_BankName			銀行名
     * - Bk_BranchName			支店名
     * - Bk_DepositClass		銀行口座種別（0：普通、1：当座）
     * - Bk_DepositClassName	銀行口座種別表示文字（'普通' or '当座'）
     * - Bk_AccountNumber		銀行口座番号
     * - Bk_AccountHolder		銀行口座名義
     * - Bk_AccountHolderKn		銀行口座名義（カナ）
     * - Yu_SubscriberName		ゆうちょ加入者名
     * - Yu_AccountNumber		ゆうちょ口座記号番号
     *
     * 当該注文で印刷済みの請求履歴がない場合、このメソッドはnullを返す
     *
     * @param int $oseq 注文SEQ
     * @return array | null
     */
    public function findLastInformedClaimAccountInfo($oseq) {
        $q = <<<EOQ
SELECT
	IFNULL(oca.Bk_BankCode, obk.BankCode) AS Bk_BankCode,
	IFNULL(oca.Bk_BranchCode, obk.BranchCode) AS Bk_BranchCode,
	IFNULL(oca.Bk_BankName, obk.BankName) AS Bk_BankName,
	IFNULL(oca.Bk_BranchName, obk.BranchName) AS Bk_BranchName,
	IFNULL(oca.Bk_DepositClass, IFNULL(obk.DepositClass, 0)) AS Bk_DepositClass,
	(CASE IFNULL(oca.Bk_DepositClass, IFNULL(obk.DepositClass, 0))
	 WHEN 0 THEN '普通' ELSE '当座'
	END) AS Bk_DepositClassName,
	IFNULL(oca.Bk_AccountNumber, obk.AccountNumber) AS Bk_AccountNumber,
	IFNULL(oca.Bk_AccountHolder, obk.AccountHolder) AS Bk_AccountHolder,
	IFNULL(oca.Bk_AccountHolderKn, obk.AccountHolderKn) AS Bk_AccountHolderKn,
	IFNULL(oca.Yu_SubscriberName, oyu.SubscriberName) AS Yu_SubscriberName,
	IFNULL(oca.Yu_AccountNumber, oyu.AccountNumber) AS Yu_AccountNumber
FROM
	T_ClaimHistory his INNER JOIN
	T_Order ord ON ord.OrderSeq = his.OrderSeq LEFT OUTER JOIN
	T_OemClaimAccountInfo oca ON (
		oca.ClaimHistorySeq = his.Seq AND
		(SELECT COUNT(*)
		 FROM T_ClaimHistory
		 WHERE
			OrderSeq = oca.OrderSeq AND
			ClaimPattern = 9 AND PrintedFlg = 1
		) = 0
	) LEFT OUTER JOIN
	T_OemBankAccount obk ON obk.OemId = IFNULL(ord.OemId, 0) LEFT OUTER JOIN
	T_OemYuchoAccount oyu ON oyu.OemId = IFNULL(ord.OemId, 0)
WHERE
	his.Seq = (
		SELECT MAX(Seq) FROM T_ClaimHistory
		WHERE OrderSeq = his.OrderSeq AND PrintedFlg = 1
	) AND
	his.OrderSeq = :OrderSeq
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':OrderSeq' => (int)$oseq))->current();
        return ($row) ? $row : null;
    }

    /**
     * 指定注文向けの、銀行口座／ゆうちょ口座固定口座情報を取得する。
     *
     * 戻り値はフラットな連想配列で以下のキーを持つ。
     * - Bk_BankCode			銀行コード
     * - Bk_BranchCode			支店コード
     * - Bk_BankName			銀行名
     * - Bk_BranchName			支店名
     * - Bk_DepositClass		銀行口座種別（0：普通、1：当座）
     * - Bk_DepositClassName	銀行口座種別表示文字（'普通' or '当座'）
     * - Bk_AccountNumber		銀行口座番号
     * - Bk_AccountHolder		銀行口座名義
     * - Bk_AccountHolderKn		銀行口座名義（カナ）
     * - Yu_SubscriberName		ゆうちょ加入者名
     * - Yu_AccountNumber		ゆうちょ口座記号番号
     *
     * @param int $oseq 注文SEQ
     * @return array | null
     */
    public function findFixedClaimAccountIfo($oseq) {
        $q = <<<EOQ
SELECT
	'default' as Kind,
	bk.BankCode AS Bk_BankCode,
	bk.BranchCode AS Bk_BranchCode,
	bk.BankName AS Bk_BankName,
	bk.BranchName AS Bk_BranchName,
	bk.DepositClass AS Bk_DepositClass,
	(CASE IFNULL(bk.DepositClass, 0)
	 WHEN 0 THEN '普通' ELSE '当座'
	END) AS Bk_DepositClassName,
	bk.AccountNumber AS Bk_AccountNumber,
	bk.AccountHolder AS Bk_AccountHolder,
	bk.AccountHolderKn AS Bk_AccountHolderKn,
	yu.SubscriberName AS Yu_SubscriberName,
	yu.AccountNumber AS Yu_AccountNumber
FROM
	T_Order ord INNER JOIN
	T_OemBankAccount bk ON bk.OemId = IFNULL(ord.OemId, 0) INNER JOIN
	T_OemYuchoAccount yu ON yu.OemId = IFNULL(ord.OemId, 0)
WHERE
	ord.OrderSeq = :OrderSeq
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':OrderSeq' => (int)$oseq))->current();
        return ($row) ? $row : null;
    }

    /**
     * OEMID、請求パターンから請求口座情報を取得する
     * @param unknown $oemId
     * @param unknown $claimPattern
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function findClaimAccountInfoForCodeMaster($oemId, $claimPattern) {

        if ( !isset($oemId) ) {
            $oemId = 0;
        }

        $sql = <<<EOQ
SELECT  Class2 AS Class2
       ,Class3 AS Class3
FROM    M_Code c
WHERE   c.CodeId    = 180
AND     c.KeyCode   = :KeyCode
EOQ;

        $prm = array(
            ':KeyCode' => ($oemId * 10) + $claimPattern  // OEMID×10＋請求パターン
        );

        $ri = $this->_adapter->query($sql)->execute($prm);
        return $ri;
    }


    /**
     * OEMIDから加入者負担用 請求口座情報を取得する
     * @param unknown $oemId
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function findClaimAccountInfoForCodeMaster2($oemId) {

        if ( !isset($oemId) ) {
            $oemId = 0;
        }

        $sql = <<<EOQ
SELECT  Class2 AS Class2
       ,Class3 AS Class3
FROM    M_Code c
WHERE   c.CodeId    = 193
AND     c.KeyCode   = :KeyCode
EOQ;

        $prm = array(
            ':KeyCode' => ($oemId * 10) + 1  // OEMID×10＋1
        );

        $ri = $this->_adapter->query($sql)->execute($prm);
        return $ri;
    }


    /**
     * SMBC連携が成功したか判断する
     * @param int $caSeq OEM請求口座SEQ
     * @param string $message エラーメッセージ(本関数がfalseを返すときのみ設定する)
     */
    protected function isSmbcSucceed($caSeq, &$message) {

        // SQL実行
        $sql = <<<EOQ
SELECT   *
FROM   T_SmbcRelationLog
WHERE  1 = 1
AND    ClaimAccountSeq = :ClaimAccountSeq
AND    TargetFunction = 1
EOQ;

        $prm = array(
                ':ClaimAccountSeq' => $caSeq,
        );

        $ri = $this->_adapter->query($sql)->execute($prm);

        if ($ri->count() <= 0) {
            // データが取得出来ない場合はtrueを返却(SMBC連携自体行う必要がなかったと判断)
            return true;
        }

        $row = $ri->current();
        if ($row['Status'] == 2) {
            // 連携に成功したのでtrueを返却
            return true;
        }

        // 後続処理でロールバックされる可能性があるので、T_SmbcRelationLogはテキストログへ出力しておく。
        $this->info("SMBC連携エラー情報：" . var_export($row, true));

        // 結果メッセージがあれば結果メッセージを設定、なければ受信エラー情報を設定
        $message = (strlen($row['ResponseMessage']) > 0) ? $row['ResponseMessage'] : $row['ErrorReason'];
        return false;

    }


    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($message, $priority) {
		$logger = $this->getLogger();
		$message = sprintf('[%s] %s', get_class($this), $message);
		if($logger) {
			$logger->log($priority, $message);
		}
	}

    /**
     * DEBUGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function debug($message) {
		$this->log($message, Logger::DEBUG);
	}

    /**
     * INFOレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function info($message) {
		$this->log($message, Logger::INFO);
	}

    /**
     * NOTICEレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function notice($message) {
		$this->log($message, Logger::NOTICE);
	}

    /**
     * WARNレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function warn($message) {
		$this->log($message, Logger::WARN);
	}

    /**
     * ERRレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function err($message) {
		$this->log($message, Logger::ERR);
	}

    /**
     * CRITレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function crit($message) {
		$this->log($message, Logger::CRIT);
	}

    /**
     * ALERTレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function alert($message) {
		$this->log($message, Logger::ALERT);
	}

    /**
     * EMERGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function emerg($message) {
		$this->log($message, Logger::EMERG);
	}

    private function getCvsNetRecord()
    {
        $sql = <<<EOQ
SELECT  *
FROM    M_CvsReceiptAgent
WHERE   BarcodeLogicName = 'CvsNet'
AND     ValidFlg = 1
ORDER BY ReceiptAgentId
EOQ;

        $ri = $this->_adapter->query($sql)->execute()->current();
        return $ri;
    }
}
