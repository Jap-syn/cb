<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Coral\Coral\CoralValidate;
use models\Table\TableClaimHistory;
use models\Table\TableCustomer;
use models\Table\TableEnterprise;
use models\Table\TableOemClaimFee;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableSelfBillingProperty;
use models\Table\TableSite;
use models\Logic\LogicStampFee;
use models\View\ViewWaitForFirstClaim;
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseLog;
use models\Logic\ThreadPool\LogicThreadPoolItem;
use models\Table\TableSmbcpa;
use Zend\Log\Logger;
use models\Logic\SelfBilling\LogicSelfBillingException;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableSystemProperty;
use models\Logic\Exception\LogicClaimException;
use models\Table\TableClaimError;
use models\Table\TableOrderItems;
use models\Table\TableCode;
use models\Table\TableSiteSbpsPayment;

/**
 * 請求書同梱ツールAPIロジック
 */
class LogicSelfBilling {
	/**
	 * セッション維持向けのNoopコマンド
	 * @var string
	 */
	const CMD_NOOP = 'Noop';

    /**
     * セッション開始を宣言する
     * @var string
     */
	const CMD_PREPARE_SESSION = 'PrepareSession';

    /**
     * セッション終了を宣言する
     * @var string
     */
	const CMD_TERMINATE_SESSION = 'TerminateSession';

	/**
	 * レポート設定を取得するコマンド
	 * @var string
	 */
	const CMD_GET_REPORT_SETTINGS = 'GetReportSettings';

    /**
     * ジョブ転送可能データの件数を取得するコマンド
     * @var string
     */
	const CMD_COUNT_PRE_TARGETS = 'CountPreTargets';

	/**
	 * ジョブ転送可否問合せコマンド
	 * @var string
	 */
	const CMD_CAN_ENQUEUE = 'CanEnqueue';

    /**
     * ジョブ転送可能データをリストで取得するコマンド
     * @var string
     */
	const CMD_GET_PRE_TARGETS = 'GetPreTargets';

    /**
     * 選択されたデータのジョブ転送を実行するコマンド
     * @var string
     */
	const CMD_SEND_PRE_TARGETS_TO = 'SendPreTargetsTo';

    /**
     * 選択されたデータのジョブ転送を実行するコマンド
     * @var string
     */
	const CMD_SEND_PRE_TARGETS_TO_CB = 'SendPreTargetsToCB';

    /**
     * 印刷対象データの件数を取得するコマンド
     * @var string
     */
	const CMD_COUNT_TARGETS = 'CountTargets';

    /**
     * 印刷対象データをリストで取得するコマンド
     * @var string
     */
	const CMD_GET_TARGETS = 'GetTargets';

    /**
     * 印刷対象データをリストで取得するコマンド(条件付き)
     * @var string
     */
    const CMD_GET_TARGET_CONDITIONS = 'GetTargetConditions';

    /**
     * 印刷対象データを全件リストで取得するコマンド
     * @var string
     */
	const CMD_GET_ALL_TARGETS = 'GetAllTargets';

	/**
	 * 注文商品明細をリストで取得するコマンド
	 * @var string
	 */
	const CMD_GET_TARGET_ITEMS = 'GetTargetItems';

    /**
     * 選択されたデータが現時点で印刷可能であるかを問い合わせるコマンド
     * @var string
     */
	const CMD_JUDGE_PRINTABLE = 'JudgePrintable';

    /**
     * 選択されたデータを印刷済みとして更新するコマンド
     * @var string
     */
	const CMD_SET_PRINTED = 'SetPrinted';

    /**
     * 印紙代適用設定データを取得するコマンド
     * @var string
     */
    const CMD_GET_STAMPFEEE_SETTINGS = 'GetStampFeeSettings';

	/**
	 * コマンド名を識別する、送信コマンドの一次キー
	 * @var string
	 */
	const CMD_KEY_COMMAND = 'Command';

	/**
	 * コマンドパラメータを識別する、送信コマンドの一次キー
	 * @var string
	 */
	const CMD_KEY_PARAMS = 'Parameters';

	/**
	 * 処理結果を指定する、応答データの一次キー
	 * @var string
	 */
	const RES_KEY_STATUS = 'Status';

	/**
	 * 処理結果のデータを指定する、応答データの一次キー
	 * @var string
	 */
	const RES_KEY_RESULT = 'Data';

	/**
	 * 処理エラーメッセージを指定する、応答データの一次キー
	 * @var string
	 */
	const RES_KEY_ERR_REASON = 'Reason';

	/**
	 * 処理エラーメッセージコードを指定する、応答データの一次キー
	 * @var string
	 */
	const RES_KEY_ERR_CODE = 'Code';

	/**
	 * バージョン情報キー：メジャーバージョン部
	 * @var string
	 */
	const VER_KEY_MAJOR = 'major';

	/**
	 * バージョン情報キー：マイナーバージョン部
	 * @var string
	 */
	const VER_KEY_MINOR = 'minor';

	/**
	 * バージョン情報キー：ビルド番号部（予約のみ）
	 * @var string
	 */
	const VER_KEY_BUILD = 'build';

	/**
	 * バージョン情報キー：リビジョン部（予約のみ）
	 * @var string
	 */
	const VER_KEY_REVISION = 'revision';

	/**
	 * バージョン文字列を解析し、major/minorに展開する。
	 * 指定が適合している場合の戻り値は連想配列で、
	 * キー'major'にメジャーバージョン、'minor'にマイナーバージョンが格納される。
	 * nullや適合しない形式の文字列の場合、このメソッドはnullを返す
	 *
	 * @static
	 * @param string|null $version バージョン文字列
	 * @return array|null 展開した結果の連想配列またはnull
	 */
	public static function parseVersionString($version) {
		if(!preg_match('/^([1-9]\d+|\d)\.([1-9]\d+|\d).*$/u', nvl($version), $matches)) {
			return null;
		}
		return array(
			self::VER_KEY_MAJOR => (int)$matches[1],
			self::VER_KEY_MINOR => (int)$matches[2]
		);
	}

	/**
	 * バージョン文字列を正規化する
	 *
	 * @static
	 * @param string|null $version バージョン文字列
	 * @param bool $parse $versionをパースするかのフラグ。省略時はtrue
	 * @return string
	 */
	public static function getFixedVersionString($version, $parse = true) {
		$key_mj = LogicSelfBilling::VER_KEY_MAJOR;
		$key_mn = LogicSelfBilling::VER_KEY_MINOR;
		$key_bd = LogicSelfBilling::VER_KEY_BUILD;
		$key_rv = LogicSelfBilling::VER_KEY_REVISION;

		$ver = $parse ? LogicSelfBilling::parseVersionString($version): $version;
		return $ver ? join('.', array($ver[$key_mj], $ver[$key_mn])) : '';
	}

	/**
	 * システムレベルで請求書同梱ツールの利用が許可されているかのフラグ
	 *
	 * @access private
	 * @var boolean
	 */
	protected $_sys_sb_enabled;

	/**
	 * 支払期限日数
	 *
	 * @access private
	 * @var int
	 */
	protected $_payment_limit_days = 14;

	/**
	 * 利用可能な最低クライアントバージョン
	 *
	 * @access private
	 * @var string
	 */
	protected $_threshold_version = null;

	/**
	 * 印刷対象リストの上限
	 *
	 * @access private
	 * @var int
	 */
	protected $_target_list_limit = 250;

	/**
	 * EAN128メーカーコード
	 *
	 * @access private
	 * @var string
	 */
	protected $_ean128_maker_code = '908997';

	/**
	 * EAN128委託先コード
	 *
	 * @access private
	 * @var string
	 */
	protected $_ean128_corp_code = '0777';

	/**
	 * 現在ログイン中のアカウント情報
	 *
	 * @access private
	 * @var string
	 */
	protected $_ent;

    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_db;

    /**
     * ユーザーID
     * @var int
     */
    protected $_userId;

    /**
     * ハッシュ化パスワードを取り扱うためのユーティリティクラス
     * @var BaseAuthUtility
     */
    protected $_authUtil;

    /**
     * 印紙代設定ユーティリティロジック
     *
     * @access private
     * @var LogicStampFee
     */
    protected $_stampFeeLogic;

    /**
     * ロガーインスタンス
     *
     * @access protected
     * @var BaseLog
     */
    protected $_logger;

	public function __construct(Adapter $db, $enterpriseId, $userId, $authUtil) {
		$this->_db = $db;
		$enterprises = new TableEnterprise($this->_db);
		$this->_ent = $enterprises->findEnterprise2($enterpriseId)->current();
        $this->_userId = $userId;
        $this->_authUtil = $authUtil;

        $this->_stampFeeLogic = new LogicStampFee();
	}

	/**
	 * システムレベルの同梱ツール利用可否設定を取得する
	 *
	 * @return boolean
	 */
	public function getSystemSelfBillingEnabled() {
		return $this->_sys_sb_enabled;
	}

	/**
	 * システムレベルの同梱ツール利用可否を設定する
	 *
	 * @param boolean $enabled システムレベルで利用を許可する場合はtrue
	 * @return Logic_SelfBilling このインスタンス自身
	 */
	public function setSystemSelfBillingEnabled($enabled) {
		$this->_sys_sb_enabled = $enabled ? true : false;
		return $this;
	}

	/**
	 * 請求期限日数を取得する
	 *
	 * @return int
	 */
	public function getPaymentLimitDays() {
		return $this->_payment_limit_days;
	}

	/**
	 * 請求期限日数を設定する
	 *
	 * @param int|null $limit_days 期限日数。省略時や1より小さい指定の場合は14と解釈される
	 * @return Logic_SelfBilling このインスタンス自身
	 */
	public function setPaymentLimitDays($limit_days = null) {
		$days = (int)$limit_days;
		if($days < 1) $days = 14;		// デフォルトは14日間
		$this->_payment_limit_days = $days;

		return $this;
	}

	/**
	 * 利用可能な最低クライアントバージョンを取得する
	 *
	 * @return null|string
	 */
	public function getThresholdClientVersion() {
		return $this->_threshold_version;
	}

	/**
	 * 利用可能な最低クライアントバージョンを設定する
	 *
	 * @param string|null $version major.minor形式のバージョン文字列
	 * @return Logic_SelfBilling このインスタンス自身
	 */
	public function setThresholdClientVersion($version = null) {
		$this->_threshold_version = self::parseVersionString($version);
		return $this;
	}

	/**
	 * 印刷対象リストの上限設定を取得する
	 *
	 * @return int
	 */
	public function getTargetListLimit() {
		return $this->_target_list_limit;
	}

	/**
	 * 印刷対象リストの上限を設定する
	 * 設定可能な値域は1～5000で、この範囲を超えている場合は
	 * 最も近い限界値に丸められる。省略時は250
	 *
	 * @param int $limit 設定する上限値
	 * @return Logic_SelfBilling このインスタンス自身
	 */
	public function setTargetListLimit($limit = 250) {
		$limit = (int)$limit;
		if($limit < 1) $limit = 1;
		if($limit > 5000) $limit = 5000;
		$this->_target_list_limit = $limit;

		return $this;
	}

	/**
	 * EAN128 メーカーコードを取得する
	 *
	 * @return string
	 */
	public function getEAN128MakerCode() {
		return $this->_ean128_maker_code;
	}

	/**
	 * EAN128 メーカーコードを設定する。
	 *
	 * @var string $maker_code EAN128メーカーコード。数字6桁以外は受け付けない
	 * @return Logic_SelfBilling このインスタンス自身
	 */
	public function setEAN128MakerCode($maker_code) {
		$maker_code = nvl($maker_code);
		if(mb_ereg_match('^\d{6}$', $maker_code)) {
			$this->_ean128_maker_code = $maker_code;
		}
		return $this;
	}

	/**
	 * EAN128 委託先コードを取得する
	 *
	 * @return string
	 */
	public function getEAN128CorporateCode() {
		return $this->_ean128_corp_code;
	}

	/**
	 * EAN128 委託先コードを設定する
	 *
	 * @param string $corp_code EAN128委託先コード。数字のみで構成されている必要がある
	 * @return Logic_SelfBilling このインスタンス
	 */
	public function setEAN128CorporateCode($corp_code) {
		$corp_code = nvl($corp_code);
		if(mb_ereg_match('^\d+', $corp_code)) {
			$this->_ean128_corp_code = $corp_code;
		}
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
	 * 例外情報からエラーレスポンスデータを生成する
	 *
	 * @param Exception $err 例外
	 * @return array
	 */
	public function generateErrorResponse(Exception $err) {
		if($err instanceof LogicSelfBillingException) {
			return array(
				self::RES_KEY_STATUS => 'NG',
				self::RES_KEY_ERR_REASON => $err->getMessage(),
				self::RES_KEY_ERR_CODE => $err->getAdditionalCode() );
		} else {
			return array(
				self::RES_KEY_STATUS => 'NG',
				self::RES_KEY_ERR_REASON => $err->getMessage(),
				self::RES_KEY_ERR_CODE => LogicSelfBillingException::ERR_GENERAL_EXCEPTION );
		}
	}

    /**
     * 印紙代適用判断ユーティリティに設定データをインポートする
     *
     * @param array $settings 印紙代適用設定データ
     * @return Logic_SelfBilling このインスタンス
     */
    public function importStampFeeLogicSettings(array $settings) {
        $this->_stampFeeLogic->importSettings($settings);
        return $this;
    }

    /**
     * 現在の印紙代適用判断ユーティリティの設定データを取得する
     *
     * @return array
     */
    public function getCurrentStampFeeLogicSettings() {
        return $this->_stampFeeLogic->exportSettings();
    }

    /**
     * コマンドデータを解析し、要求に対応したメソッドへディスパッチする
     *
     * @param array $data コマンドデータ
     * @return array
     */
    public function dispatch($data)
    {
        // コマンドパラメータの形式チェック
        if(!is_array($data)) {
            throw new LogicSelfBillingException(
                'パラメータ指定が不正です',
                LogicSelfBillingException::ERR_DISPATCH_INVALID_PARAMETER );
        }

        // コマンド名とパラメータを抽出
        $cmd_name = $data[self::CMD_KEY_COMMAND];
        $params = $data[self::CMD_KEY_PARAMS];
        if(!is_array($params)) $params = array();

        // バージョンチェック
        if(!$this->isVersionGreaterThan($params['Version'])) {
            throw new LogicSelfBillingException(
                self::parseVersionString($params['Version']) ?
                    sprintf("お使いのクライアントバージョン %s.* は古いため利用できません\r\n" .
                            "バージョン %s.* 以降を入手してください",
                            self::getFixedVersionString($params['Version']),
                            self::getFixedVersionString($this->getThresholdClientVersion(), false)) :
                    'お使いのクライアントバージョンは不正なバージョンのため利用できません',
                LogicSelfBillingException::ERR_PERMISSION_INVALID_VERSION );
        }

        // 子クラス（LogicSelfBillingSelfBillingApi）から呼ばれた場合、各処理は子クラスの処理を参照する！！！
        // このクラスから呼ばれた場合は、このクラスの処理を参照する！！！
        switch($cmd_name) {
            case self::CMD_PREPARE_SESSION:
                // タスク開始要求
                return $this->execPrepareSession($params);

            case self::CMD_TERMINATE_SESSION:
                // タスク完了要求
                return $this->execTerminateSession($params);

            case self::CMD_NOOP:
                // Noop実行
                return $this->execNoop($params);

            case self::CMD_GET_REPORT_SETTINGS:
                // レポート設定取得要求
                return $this->execGetReportSettings($params);

            case self::CMD_COUNT_PRE_TARGETS:
                // ジョブ転送可能件数取得要求
                return $this->execCountPreTargets($params);

            case self::CMD_GET_PRE_TARGETS:
                // ジョブ転送可能リスト取得要求
                return $this->execGetPreTargets($params);

            case self::CMD_SEND_PRE_TARGETS_TO:
                // ジョブ転送要求
                return $this->execSendPreTargetsTo($params);

            case self::CMD_SEND_PRE_TARGETS_TO_CB:
                // ジョブ転送要求
                return $this->execSendPreTargetsTo($params, true);

            case self::CMD_COUNT_TARGETS:
                // 印刷対象件数取得要求
                return $this->execCountTargets($params);

            case self::CMD_GET_TARGETS:
                // 印刷対象リスト取得要求
                return $this->execGetTargets($params);

            case self::CMD_GET_TARGET_CONDITIONS:
                // 印刷対象リスト取得要求(条件付き)
                return $this->execGetTargetConditions($params);

            case self::CMD_GET_ALL_TARGETS:
                // 印刷対象リスト全件取得要求
                return $this->execGetAllTargets($params);

            case self::CMD_GET_TARGET_ITEMS:
                // 注文商品明細リスト取得要求
                return $this->execGetTargetItems($params);

            case self::CMD_JUDGE_PRINTABLE:
                // 印刷可能判断要求
                return $this->execJudgePrintable($params);

            case self::CMD_SET_PRINTED:
                // 印刷済み設定要求
                return $this->execSetPrinted($params);

            case self::CMD_GET_STAMPFEEE_SETTINGS:
                // 印紙代適用設定取得要求
                return $this->execGetStampFeeSettings($params);

            default:
                // 未定義コマンドの場合
                throw new LogicSelfBillingException(
                    'コマンドの指定が不正です',
                    LogicSelfBillingException::ERR_DISPATCH_INVALID_COMMAND );
        }
    }

// 2015/07/29 Del Y.Suzuki 新システムでは廃止 Stt
//     /**
//      * 指定のアクセスキーで現在のアカウントを認証し、正当なアクセスかを判断する
//      *
//      * @param string $access_key アクセスキー。現在のログインアカウントとの突合に使用される
//      * @return boolean
//      */
//     public function isValidAccess($access_key) {
// 2015/07/29 Del Y.Suzuki 新システムでは廃止 End

    /**
     * 指定のバージョン文字列が、設定されている最低クライアントバージョンよりも
     * 大きいかを判断する。
     * このインスタンスに最低クライアントバージョンが設定されていない場合は
     * 検査対象のデータに関わらずtrueを返す。逆に、最低バージョンが設定されている
     * 状況で検査対象が不適切な形式の場合は常にfalseになる
     *
     * @param string $version 検査するバージョン文字列
     * @return boolean
     */
    public function isVersionGreaterThan($version)
    {
        // 20150912_1534_suzuki_h バージョンチェックは行わない
        return true;
    }

	/**
	 * 現在のアカウントで同梱ツールの利用が可能かを判断する。
	 *
	 * @return boolean
	 */
	public function isSelfBillingEnabled() {
		try {
			$ent = $this->_ent;

			// システム設定と現在のアカウントの両方で利用許可があるかをチェック
			$sys_enabled = $this->getSystemSelfBillingEnabled();
			return $sys_enabled && $ent['SelfBillingMode'] && $ent['SelfBillingMode'] > 0;
		} catch(\Exception $err) {
			return false;
		}
	}

	/**
	 * Noop要求を実行する
	 *
	 * @param array $params コマンドパラメータ
	 * @return array
	 */
	public function execNoop($params) {
		// データが空のOKを無条件に返す
		return array(
			self::RES_KEY_STATUS => 'OK'
		);
	}

	/**
	 * タスク開始要求を実行する
	 *
	 * @param array $params コマンドパラメータ
	 * @return array
	 */
	public function execPrepareSession($params) {
		$is_key_valid = false;
// 		$sb_properties = new TableSelfBillingProperty($this->_db);
// 		foreach($sb_properties->findByEnterpriseId($this->_ent['EnterpriseId']) as $sb_row) {
// 			if($sb_row['AccessKey'] == $params['AccessKey']) {
// 				// ログイン時刻を更新
// 				$sb_row['LastLoginDateTime'] = date('Y-m-d H:i:s');
// 				$sb_row->save();
// 				$is_key_valid = true;
// 				break;
// 			}
// 		}

		// TODO: Prepare実行時にアクセスキーの不一致が発生するケースを検討
		// if(!$is_key_valid) throw new Exception('');

		$result = array();
		$keys = array(
			'EnterpriseId',
			'EnterpriseNameKj',
			'EnterpriseNameKn',
			'SelfBillingExportAllow',
            'AutoClaimStopFlg',
 		    'HideToCbButton'
		);
		foreach($keys as $key) {
			if(isset($this->_ent[$key])) $result[$key] = $this->_ent[$key];
		}
		return array(
			self::RES_KEY_STATUS => 'OK',
			self::RES_KEY_RESULT => $result
		);
	}

	/**
	 * タスク完了要求を実行する
	 *
	 * @param array $params コマンドパラメータ
	 * @return array
	 */
	public function execTerminateSession($params) {
		$sb_properties = new TableSelfBillingProperty($this->_db);
		foreach($sb_properties->findByEnterpriseId($this->_ent['EnterpriseId']) as $sb_row) {
			if($sb_row->AccessKey == $params['AccessKey']) {
				// ログアウト時刻を更新
				$sb_row->LastLogoutDateTime = date('Y-m-d H:i:s');
				$sb_row->save();
			}
		}
		return array(
			self::RES_KEY_STATUS => 'OK'
		);
	}

	/**
	 * レポート設定取得要求を実行する
	 *
	 * @param array $params コマンドパラメータ
	 * @return array
	 */
	public function execGetReportSettings($params) {
		return array(
			self::RES_KEY_STATUS => 'OK',
			self::RES_KEY_RESULT => array(
				'EAN128MakerCode' => $this->getEAN128MakerCode(),
				'EAN128CorporateCode' => $this->getEAN128CorporateCode()
			)
		);
	}

    /**
     * ジョブ転送可能件数取得要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execCountPreTargets($params)
    {

        $vwfc = new ViewWaitForFirstClaim($this->_db);
        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => array( 'count' => $vwfc->getToPrintCountSB($this->_ent['EnterpriseId']) )
        );
    }

    /**
     * ジョブ転送可能リスト取得要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execGetPreTargets($params)
    {

        $vwfc = new ViewWaitForFirstClaim($this->_db);
        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => $this->trimPreTargets($vwfc->getToPrintSB($this->_ent['EnterpriseId']))
        );
    }

    /**
     * ジョブ転送可能リストからクライアントで不要なカラムを除去する
     *
     * @access protected
     * @param Zend_Db_Table_Rowset_Abstract $viewRows View_WaitForFirstClaimから取得したRowset
     * @return array
     */
    protected function trimPreTargets($viewRows)
    {
        $colNames = array(
            'OrderSeq',
            'OrderId',
            'ReceiptOrderDate',
            'RegistDate',
            'NameKj',
            'UnitingAddress',
            'UseAmount',
            'Ent_OrderId',
            'DestNameKj',
            'DestPostalCode',
            'DestUnitingAddress',
            'DestPhone',
            'IsAnotherDeli'
        );
        $result = array();
        foreach($viewRows as $i => $row) {
            $resultRow = array();
            foreach($colNames as $colName) {
                if(isset($row[$colName])) {
                    if($colName == 'IsAnotherDeli') {
                        $resultRow[$colName] = $row[$colName] ? '！' : '';
                    } else {
                        $resultRow[$colName] = $row[$colName];
                    }
                }
            }
            $result[] = $resultRow;
        }
        return $result;
    }

    /**
     * ジョブ転送要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @param null|boolean $to_cb CBの印刷ジョブとするかのフラグ。省略時はfalse
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execSendPreTargetsTo($params, $to_cb = false)
    {
        $orders = new TableOrder($this->_db);
        $enterprises = new TableEnterprise($this->_db);
        $histories = new TableClaimHistory($this->_db);
        $payings = new TablePayingAndSales($this->_db);
        $oemClaimFees = new TableOemClaimFee($this->_db);
        $sites = new TableSite($this->_db);
        $mdlSysP = new TableSystemProperty($this->_db);
        $logicmo = new LogicMypageOrder($this->_db);
        $lgcc = new LogicCampaign($this->_db);
        $mdlce = new TableClaimError($this->_db);
        $mdloi = new TableOrderItems($this->_db);

        // パラメータから転送対象のシーケンスリストを構築
        $seqs = $params['Seqs'];
        $sentCount = 0;
        $processCount = 0;
        $errors = array();
        $isSmbc = false;
        if(!is_array($seqs)) $seqs = array();

        $shippingLogic = new LogicShipping($this->_db, $this->_userId);
        $shippingLogic->setLogger($this->_logger);

        try {
            foreach($seqs as $i => $seq)  {
                $order = $orders->find($seq)->current();

                // 指定のOrderが存在しないので何もしない
                if($order == null) continue;

                //伝票番号仮登録処理実行
                try {
                    $jnResult = $shippingLogic->registerClaimTemporaryJournalNumber($seq);
                    $this->debug(sprintf('journal-number register: result = %s', $jnResult ? 'OK' : 'NG'));

                    // テスト注文時のクローズ処理
                    if ($jnResult) {
                        $shippingLogic->closeIfTestOrder($seq);
                    }
                } catch(Exception $shippingLogicError) {
                    $this->debug($shippingLogicError->getMessage());
                }

                // 有効な注文か
                $sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
   AND o.DataStatus = 41
EOQ;
                $prm = array(
                        ':OrderSeq' => $seq,
                );
                $ret = $this->_db->query($sql)->execute($prm)->current()['cnt'];
                if ($ret == 0) {
                    // 有効な注文がいない場合はスキップ
                    continue;
                }
                // 取りまとめ請求対象の注文なので何もしない
                if(in_array($order['CombinedClaimTargetStatus'], array(1, 2))) continue;
                // すでに印刷予約が入っているので何もしない
                if($histories->getReservedCount( $seq ) > 0) {
                    continue;
                }

                // 処理件数のカウント
                $processCount++;

                // -------------------------------------------------------------
                // 別送に更新
                // -------------------------------------------------------------
                // 次期では、TOCBの場合はジョブ転送を行わない
                if ($to_cb) {
                    // 別送に更新
                    $this->_db->getDriver()->getConnection()->beginTransaction();
                    try {
                        // 注文の更新（注文.請求書送付区分を[12（同梱 → 別送）]へ更新）
                        $sql  = " UPDATE T_Order ";
                        $sql .= " SET    ClaimSendingClass = 12 ";
                        $sql .= " ,      UpdateId          = :UpdateId ";
                        $sql .= " ,      UpdateDate        = :UpdateDate ";
                        $sql .= " WHERE  P_OrderSeq        = :P_OrderSeq ";

                        $stm = $this->_db->query($sql);
                        $stm->execute(array(':UpdateId' => $this->_userId, ':UpdateDate' => date('Y-m-d H:i:s'), ':P_OrderSeq' => $seq));

                        $this->_db->getDriver()->getConnection()->commit();
                        $sentCount++;       // 転送カウントをインクリメント

                    } catch(\Exception $innerError) {
                        $this->_db->getDriver()->getConnection()->rollback();
                        // エラー情報をロギング
                        $msg = sprintf('[%s:%s] 以下のエラーにより別送に更新できませんでした　ERROR = %s', $order['OrderSeq'], $order['OrderId'], $innerError->getMessage());
                        $errors[] = $msg;
                        $this->info(sprintf('[%s] %s', $to_cb ? self::CMD_SEND_PRE_TARGETS_TO_CB : self::CMD_SEND_PRE_TARGETS_TO, $msg));
                        $errorCount++;
                    }

                    // ジョブ転送は行わない
                    continue;
                }

                // -------------------------------------------------------------
                // 同梱ジョブ転送
                // -------------------------------------------------------------

                // SMBCバーチャル口座オープン用にロック獲得を試行
                $lockItem = $this->getLockItemForSmbcpaAccount($order);

                // 本処理開始
                $this->_db->getDriver()->getConnection()->beginTransaction();
                try {

                    // 請求金額の再取得
$sql = <<<EOQ
SELECT SUM(UseAmount) AS UseAmount
  FROM T_Order o
 WHERE o.Cnl_Status = 0
   AND o.P_OrderSeq = :OrderSeq
EOQ;
                    $prm = array(
                            ':OrderSeq' => $seq,
                    );
                    $useAmount = $this->_db->query($sql)->execute($prm)->current()['UseAmount'];

                    $limitDate = $sites->getLimitDate($order['SiteId'], $this->getPaymentLimitDays());
                    // 請求履歴に未印刷データを作成
                    $his_data = array(
                            'OrderSeq' => $seq,
                            'ClaimDate' => date('Y-m-d'),
                            'ClaimCpId' => -1,
                            'ClaimPattern' => 1,    // 初回請求
                            'LimitDate' => $limitDate,
                            'DamageDays' => 0,
                            'DamageInterestAmount' => 0,
                            'ClaimFee' => 0,
                            'AdditionalClaimFee' => 0,
                            'PrintedFlg' => 0,
                            'MailFlg' => 0,
                            'EnterpriseBillingCode' => $this->_ent['EnterpriseId'],
                            'ClaimAmount' => $useAmount,
                            'RegistId' => $this->_userId,
                            'UpdateId' => $this->_userId,
                    );

                    //消費税率更新
                    $taxRate = $mdlSysP->getTaxRateAt(date('Y-m-d'));
                    $taxrateData = array(
                            'TaxRate' => $taxRate, // 消費税率
                            'UpdateId' => $this->_userId, // 更新者
                    );

                    if(date('Y-m-d') > '2019-09-30'){
                        $mdloi->updateTaxrate($taxrateData,$seq);
                        $histories->saveNew2($seq, $his_data);
                    }else{
                        $mdloi->updateTaxrateBefore($taxrateData,$seq);
                        $histories->saveNew($seq, $his_data);
                    }
                    // 立替・売上管理の更新
                    // 立替・売上管理更新用に親注文Seqから子注文Seqを再取得する。
                    $sql = <<<EOQ
SELECT OrderSeq, EnterpriseId, SiteId
  FROM T_Order
 WHERE P_OrderSeq = :P_OrderSeq
   AND Cnl_Status = 0
EOQ;
                    $ri = $this->_db->query($sql)->execute(array(':P_OrderSeq' => $seq));
                    $rows = ResultInterfaceToArray($ri);

                    // 取得できた件数分ループする
                    foreach ($rows as $row) {

                        // 親注文と子注文で処理をわける
                        if ($row['OrderSeq'] == $seq) {
                            $campdata = $lgcc->getCampaignInfo($row['EnterpriseId'], $row['SiteId']);
                            $fee = CoralValidate::isInt($campdata['ClaimFeeDK']) ? (int)$campdata['ClaimFeeDK'] : (int)$campdata['ClaimFeeBS'];
                            // 税込み金額に変換
                            $fee = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $fee);
                        } else {
                            // 子注文の場合、請求手数料は 0
                            $fee = 0;
                        }

                        // 対応するT_PayingAndSalesの請求手数料（ClaimFee）を同梱出力時用に更新する
                        $paying_row = $payings->findPayingAndSales(array('OrderSeq' => $row['OrderSeq']))->current();

                        // 請求手数料が変更されたので立替金額（ChargeAmount）の再計算を行う（2013.9.27 eda）
                        $charge_amount = $paying_row['UseAmount'] - ($paying_row['SettlementFee'] + $fee);

                        // 請求手数料と立替金額のみ更新
                        $payings->saveUpdate( array('ClaimFee' => $fee, 'ChargeAmount' => $charge_amount, 'UpdateId' => $this->_userId), $paying_row['Seq'] );
                    }

                    //OEMID取得
                    $oem_id = $orders->getOemId($seq);

                    //OEM判定
                    if(!is_null($oem_id) && $oem_id != 0){

                        //OEM請求手数料書き込み
                        $oemClaimFees->saveOemClaimFee($seq, $this->_userId, true);

                    }

                    // 注文の確定待ちフラグをアップ
                    $uOrder = array(
                            'ConfirmWaitingFlg' => '1',
                            'UpdateId'          => $this->_userId,
                    );
                    $orders->saveUpdateWhere($uOrder, array('P_OrderSeq' => $seq));

                    // 注文マイページを作成する
                    $logicmo->createMypageOrder($seq, $limitDate, $oem_id, $this->_userId, $this->_authUtil);

                    $this->_db->getDriver()->getConnection()->commit();

                    $sentCount++;       // 転送カウントをインクリメント
                } catch(LogicClaimException $ex) {
                    $this->_db->getDriver()->getConnection()->rollback();
                    // エラーコードからエラー内容を振り分け
                    if($ex->getCode() == LogicClaimException::ERR_CODE_PAYEASY){
                        $payeasyErrMsg = "ペイジー連携エラー ".$ex->getMessage();
                    }else{
                        $isSmbc = true;
                    }

                    $mdlce->saveNew(array('OrderSeq' => $seq, 'ErrorCode' => $ex->getCode(), 'ErrorMsg' => $ex->getMessage()));
                } catch(\Exception $innerError) {
                    $this->_db->getDriver()->getConnection()->rollback();
                    // エラー情報をロギング
                    $msg = sprintf('[%s:%s] 以下のエラーによりジョブ転送できませんでした　ERROR = %s', $order['OrderSeq'], $order['OrderId'], $innerError->getMessage());
                    $errors[] = $msg;
                    $this->info(sprintf('[%s] %s', $to_cb ? self::CMD_SEND_PRE_TARGETS_TO_CB : self::CMD_SEND_PRE_TARGETS_TO, $msg));
                    $errorCount++;
                }

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                        $this->debug(sprintf('thread pool item released. oseq = %s', $order['OrderSeq']));
                    }
                } catch(\Exception $unlockError) {
                    $this->info(sprintf('cannot unlock thread pool item. oseq = %s, error = %s', $order['OrderSeq'], $unlockError->getMessage()));
                }
            }

            if ($isSmbc) {
                throw new LogicSelfBillingException("SMBC連携に失敗したため、ジョブ転送できませんでした。");
            }elseif(!empty($payeasyErrMsg)){
                throw new LogicSelfBillingException($payeasyErrMsg);
            }
            // 注文ごとエラーが全件で発生したら例外
            if($processCount > 0 && $processCount == $errorCount) {
                throw new \Exception(sprintf('処理可能な %d 件すべてでエラーが発生しました。サポートセンターにご連絡ください', $processCount));
            }
        } catch(LogicSelfBillingException $err) {
            throw new LogicSelfBillingException($err->getMessage());

        } catch(\Exception $err) {
            throw new LogicSelfBillingException(
                "以下のエラーによりジョブ転送できませんでした。\r\n ERROR：" . $err->getMessage(),
                LogicSelfBillingException::ERR_GENERAL_EXCEPTION );
        }
        return array(
            self::RES_KEY_STATUS => 'OK',
            // 転送カウントを返す
            self::RES_KEY_RESULT => array( 'count' => $sentCount )
        );
    }

    /**
     * 印刷対象件数取得要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execCountTargets($params)
    {
$sql = <<<EOQ
SELECT  COUNT(*) AS count
FROM    T_Order O
        INNER JOIN T_ClaimHistory H ON (O.OrderSeq = H.OrderSeq)
        INNER JOIN T_Enterprise E ON (O.EnterpriseId = E.EnterpriseId)
        INNER JOIN T_Site S ON (O.SiteId = S.SiteId )
WHERE   O.EnterpriseId = :enterprise_id
AND     (O.CombinedClaimTargetStatus IN (91, 92) OR IFNULL(O.CombinedClaimTargetStatus, 0) = 0)
AND     H.Seq = (SELECT Seq FROM T_ClaimHistory WHERE OrderSeq = O.OrderSeq AND ClaimPattern = 1 AND PrintedFlg = 0 AND ValidFlg = 1 AND EnterpriseBillingCode IS NOT NULL)
AND     O.ClaimSendingClass <> 12
AND     S.SelfBillingFlg = 1
AND     O.ConfirmWaitingFlg = 1
EOQ;

        $count = $this->_db->query($sql)->execute($this->getBindsForGetTargets($params))->current()['count'];

        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => array( 'count' => $count )
        );
    }

    /**
     * 印刷対象リスト取得要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execGetTargets($params)
    {
$base_query = <<<EOQ
SELECT
	/* column from T_Order */
	O.OrderSeq,
	O.OrderId,
	O.ReceiptOrderDate,
	(SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq = O.OrderSeq AND Cnl_Status = 0) AS UseAmount ,
	(SELECT MIN(DataStatus) FROM T_Order WHERE P_OrderSeq = O.OrderSeq AND Cnl_Status = 0 ) AS DataStatus,
	0 AS Clm_Count,
	NULL AS LatestLimitDate,
	0 AS InstallmentPlanAmount,
	O.OemId,

	/* column from T_Customer */
	C.CustomerId,
	C.NameKj,
	C.PostalCode,
	C.PrefectureCode,
	C.PrefectureName,
	C.City,
	C.Town,
	C.Building,
	C.UnitingAddress,
	C.Phone AS CustoPhone,

	/* column from T_Enterprise */
	E.EnterpriseId,
	E.EnterpriseNameKj,
	CONCAT(
		IFNULL( E.PrefectureName, '' ),
		IFNULL( E.City, '' ),
		IFNULL( E.Town, '' ),
		IFNULL( E.Building, '' )
	) AS EnterpriseAddress,
	/* E.Phone, -> change to ContactPhoneNumber (2010.6.22) */
	E.ContactPhoneNumber AS Phone,
    F_GetCampaignVal( O.EnterpriseId, O.SiteId, DATE( NOW() ), 'ReClaimFee') AS ReClaimFee,
	E.ApplicationDate,
	E.PrintEntOrderIdOnClaimFlg,
	E.OrderpageUseFlg,

	/* column from T_Site */
	S.SiteId,
	S.SiteNameKj,
	S.Url,
	S.PaymentAfterArrivalFlg,

	/* calcurationed column from T_OrderItems */

(
		SELECT
			SUM( SumMoney )
		FROM
			T_OrderItems oi
            INNER JOIN T_Order t
                    ON oi.OrderSeq = t.OrderSeq
		WHERE
			t.P_OrderSeq = O.OrderSeq AND
            oi.DataClass = 1 AND
            oi.ValidFlg = 1 AND
            t.Cnl_Status = 0
	) AS TotalItemPrice,

	IFNULL(
		(
			SELECT
				SUM(UnitPrice)
			FROM
    			T_OrderItems oi
                INNER JOIN T_Order t
                        ON oi.OrderSeq = t.OrderSeq
			WHERE
			    t.P_OrderSeq = O.OrderSeq AND
				oi.DataClass = 2 AND
                oi.ValidFlg = 1 AND
                t.Cnl_Status = 0
		), 0
	) AS CarriageFee,
	IFNULL(
		(
			SELECT
				SUM(UnitPrice)
			FROM
    			T_OrderItems oi
                INNER JOIN T_Order t
                        ON oi.OrderSeq = t.OrderSeq
			WHERE
			    t.P_OrderSeq = O.OrderSeq AND
				oi.DataClass = 3 AND
                oi.ValidFlg = 1 AND
                t.Cnl_Status = 0
		), 0
	) AS ChargeFee,

	/* column from T_ClaimHistory */
	H.Seq,
	H.ClaimPattern,
	H.ClaimDate,
	H.LimitDate,
	IFNULL( H.DamageDays, 0 ) AS DamageDays,
	H.DamageBaseDate,
	IFNULL( H.DamageInterestAmount, 0 ) AS DamageInterestAmount,
	IFNULL( H.ClaimFee, 0 ) AS ClaimFee,
	IFNULL( H.AdditionalClaimFee, 0 ) AS AdditionalClaimFee,

	/* Claim count */
	(
		SELECT
			COUNT(*)
		FROM
			T_ClaimHistory
		WHERE
			OrderSeq = O.OrderSeq AND
			PrintedFlg <> 0 AND
            ValidFlg = 1
	) AS ReIssueCount,

	/* LimitDate for Barcode */
	DATE_ADD( H.ClaimDate, INTERVAL 65 DAY ) AS Bc_LimitDate,

	/* for ReClaim pattern 4, 5 and 6 */
	DATE_ADD( H.ClaimDate, INTERVAL 10 DAY ) AS ImplementationLimitDate,

	/* for Petition */
	DATE_ADD( H.ClaimDate, INTERVAL 50 DAY ) AS PublicTrialSchedule,

	/* literal column for update */
	-1 AS OpId,

	/* for other */
	CURRENT_DATE AS Today,

	O.Ent_OrderId,

	AC.Bk_BankCode,
	AC.Bk_BranchCode,
	AC.Bk_BankName,
	AC.Bk_BranchName,
	CONCAT(
		IFNULL(AC.Bk_BankName, ''),
		IFNULL(AC.Bk_BranchName, '')
	) AS Bk_BankAndBranchName,

	AC.Bk_DepositClass,
	(CASE AC.Bk_DepositClass WHEN 1 THEN '当座' ELSE '普通' END) AS Bk_DepositClassLabel,
	AC.Bk_AccountNumber,
	AC.Bk_AccountHolder,
	AC.Bk_AccountHolderKn,

	AC.Yu_SubscriberName,
	AC.Yu_AccountNumber,
	AC.Yu_ChargeClass,
	(CASE Yu_ChargeClass WHEN 2 THEN '加入者負担' ELSE '払込人負担' END) AS Yu_ChargeClassLabel,
	AC.Yu_SubscriberData,
	AC.Yu_Option1,
	AC.Yu_Option2,
    AC.Yu_Option3,
	AC.Yu_MtOcrCode1,
	AC.Yu_MtOcrCode2,
	AC.Yu_DtCode,

	AC.Cv_ReceiptAgentName,
	AC.Cv_ReceiptAgentCode,
	AC.Cv_SubscriberCode,
	IFNULL(AC.Cv_SubscriberName, '') AS Cv_SubscriberName,
	AC.Cv_Option1,
	AC.Cv_Option2,
	AC.Cv_Option3,
	AC.Cv_BarcodeData,
	AC.Cv_BarcodeString1,
	AC.Cv_BarcodeString2,
	AC.ConfirmNumber,
	AC.CustomerNumber,
	(CASE E.TaxClass WHEN 1
	THEN IFNULL(
		(
			SELECT
				SumMoney
			FROM
				T_OrderItems oi
				INNER JOIN T_Order t
						ON oi.OrderSeq = t.OrderSeq
			WHERE
				t.P_OrderSeq = O.OrderSeq AND
				oi.DataClass = 4 AND
				oi.ValidFlg = 1 AND
				t.Cnl_Status = 0
		), 0
	)
	ELSE AC.TaxAmount END) AS TaxAmount,
    AC.SubUseAmount_1,
    AC.SubTaxAmount_1,
    AC.SubUseAmount_2,
    AC.SubTaxAmount_2,

	/* column from T_Order */
	M.Token AS MypagePassword,
	M.AccessKey
    FROM    T_Order O
            INNER JOIN T_ClaimHistory H ON (O.OrderSeq = H.OrderSeq)
            INNER JOIN T_Customer C ON (O.OrderSeq = C.OrderSeq)
            INNER JOIN T_Enterprise E ON (O.EnterpriseId = E.EnterpriseId)
            INNER JOIN T_Site S ON (O.SiteId = S.SiteId AND O.EnterpriseId = S.EnterpriseId)
            INNER JOIN T_OemClaimAccountInfo AC ON (AC.ClaimHistorySeq = H.Seq AND AC.Status = 1)
            LEFT OUTER JOIN T_MypageOrder M ON (O.OrderSeq = M.OrderSeq)
    WHERE   O.EnterpriseId = :enterprise_id
    AND     (O.CombinedClaimTargetStatus IN (91, 92) OR IFNULL(O.CombinedClaimTargetStatus, 0) = 0)
    AND     H.Seq = (SELECT Seq FROM T_ClaimHistory WHERE OrderSeq = O.OrderSeq AND ClaimPattern = 1 AND PrintedFlg = 0 AND ValidFlg = 1 AND EnterpriseBillingCode IS NOT NULL )
    AND     O.ClaimSendingClass <> 12
    AND     S.SelfBillingFlg = 1
    AND     O.ConfirmWaitingFlg = 1
    ORDER BY
            O.OrderSeq
EOQ;
$base_query .= sprintf(' LIMIT %d', (int)$this->getTargetListLimit());

        $ri = $this->_db->query($base_query)->execute($this->getBindsForGetTargets($params));
        $rows = ResultInterfaceToArray($ri);

        //ペイジー収納機関番号取得
        $mdlCode = new TableCode($this->_db);
        $bk_number = $mdlCode->find(LogicPayeasy::PAYEASY_CODEID, LogicPayeasy::BK_NUMBER_KEYCODE)->current()['Note'];

        //再請求手数料を税込み金額に変換
        $mdlSysP = new TableSystemProperty($this->_db);
        $mdlch = new TableClaimHistory($this->_db);
        $mdlcd = new TableCode($this->_db);
        $mdlSitePayment = new TableSiteSbpsPayment($this->_db);
        foreach($rows as $key=>$row) {
            $rows[$key]['ReClaimFee'] = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $rows[$key]['ReClaimFee']);
            //収納機関番号を追加
            $rows[$key]['Bk_Number'] = $bk_number;

            //届いてから決済利用フラグが1：利用する場合に届いてから払い手続き期限日を設定
            if($rows[$key]['PaymentAfterArrivalFlg']){
                //請求履歴の最も古いレコードを取得する。
                $claimHistoryData = $mdlch->getOldestClaimHistory($rows[$key]['OrderSeq']);
                $siteId = $rows[$key]['SiteId'];
                if(!empty($claimHistoryData)) {
                    $minClaimDate = $claimHistoryData['ClaimDate'];
                }
                else{
                    $minClaimDate = 0;
                }

                //届いてから決済のサイト別の支払可能種類からMax(利用期間)を取得する
                $creditSettlementDays = 0;
                if (!empty($minClaimDate)) {
                    $maxNumUseDay = $mdlSitePayment->getMaxNumUseDay($siteId, $minClaimDate);
                    if (!empty($maxNumUseDay)) {
                        $creditSettlementDays = $maxNumUseDay;
                        //請求履歴.請求日 + 届いてから決済のサイト別の支払可能種類.Max(利用期間)
                        $creditLimitDate = date('Y/m/d', strtotime($minClaimDate. '+'. $creditSettlementDays. ' days'));
                        $rows[$key]['CreditLimitDate'] =  $creditLimitDate;
                    }
                }
            }

            //注文マイページの利用が1:利用する場合、マイページＵＲＬを設定
            if($rows[$key]['OrderpageUseFlg']){
                $orderpageUrl = $mdlcd->getMasterCaption(105, nvl($rows[$key]['OemId'], 0)); // OEM別の注文ﾏｲﾍﾟｰｼﾞURL
                $orderpageUrl = rtrim($orderpageUrl, '/'); // 念のため、最後の"/"は除く

                $rows[$key]['MypageUrl'] =  $orderpageUrl;
            }
        }
        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => $rows
        );
    }

	/**
	 * 印刷対象リスト全件取得要求を実行する
	 *
	 * @param array $params コマンドパラメータ
	 * @return array
	 */
	public function execGetAllTargets($params) {
		$sql = $this->getTargetsQuery(false, true);
		$rows = $this->_db->fetchAll($sql, $this->getBindsForGetTargets($params));
		return array(
			self::RES_KEY_STATUS => 'OK',
			self::RES_KEY_RESULT => $rows
		);
	}

    /**
     * 印刷対象リスト取得要求を実行する(条件付き)
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execGetTargetConditions($params)
    {
        $base_query = <<<EOQ
SELECT
	/* column from T_Order */
	O.OrderSeq,
	O.OrderId,
	O.ReceiptOrderDate,
	(SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq = O.OrderSeq AND Cnl_Status = 0) AS UseAmount ,
	(SELECT MIN(DataStatus) FROM T_Order WHERE P_OrderSeq = O.OrderSeq AND Cnl_Status = 0 ) AS DataStatus,
	0 AS Clm_Count,
	NULL AS LatestLimitDate,
	0 AS InstallmentPlanAmount,
	O.OemId,

	/* column from T_Customer */
	C.CustomerId,
	C.NameKj,
	C.PostalCode,
	C.PrefectureCode,
	C.PrefectureName,
	C.City,
	C.Town,
	C.Building,
	C.UnitingAddress,
	C.Phone AS CustoPhone,

	/* column from T_Enterprise */
	E.EnterpriseId,
	E.EnterpriseNameKj,
	CONCAT(
		IFNULL( E.PrefectureName, '' ),
		IFNULL( E.City, '' ),
		IFNULL( E.Town, '' ),
		IFNULL( E.Building, '' )
	) AS EnterpriseAddress,
	/* E.Phone, -> change to ContactPhoneNumber (2010.6.22) */
	E.ContactPhoneNumber AS Phone,
    F_GetCampaignVal( O.EnterpriseId, O.SiteId, DATE( NOW() ), 'ReClaimFee') AS ReClaimFee,
	E.ApplicationDate,
	E.PrintEntOrderIdOnClaimFlg,
	E.OrderpageUseFlg,

	/* column from T_Site */
	S.SiteId,
	S.SiteNameKj,
	S.Url,
	S.PaymentAfterArrivalFlg,

	/* calcurationed column from T_OrderItems */

(
		SELECT
			SUM( SumMoney )
		FROM
			T_OrderItems oi
            INNER JOIN T_Order t
                    ON oi.OrderSeq = t.OrderSeq
		WHERE
			t.P_OrderSeq = O.OrderSeq AND
            oi.DataClass = 1 AND
            oi.ValidFlg = 1 AND
            t.Cnl_Status = 0
	) AS TotalItemPrice,

	IFNULL(
		(
			SELECT
				SUM(UnitPrice)
			FROM
    			T_OrderItems oi
                INNER JOIN T_Order t
                        ON oi.OrderSeq = t.OrderSeq
			WHERE
			    t.P_OrderSeq = O.OrderSeq AND
				oi.DataClass = 2 AND
                oi.ValidFlg = 1 AND
                t.Cnl_Status = 0
		), 0
	) AS CarriageFee,
	IFNULL(
		(
			SELECT
				SUM(UnitPrice)
			FROM
    			T_OrderItems oi
                INNER JOIN T_Order t
                        ON oi.OrderSeq = t.OrderSeq
			WHERE
			    t.P_OrderSeq = O.OrderSeq AND
				oi.DataClass = 3 AND
                oi.ValidFlg = 1 AND
                t.Cnl_Status = 0
		), 0
	) AS ChargeFee,

	/* column from T_ClaimHistory */
	H.Seq,
	H.ClaimPattern,
	H.ClaimDate,
	H.LimitDate,
	IFNULL( H.DamageDays, 0 ) AS DamageDays,
	H.DamageBaseDate,
	IFNULL( H.DamageInterestAmount, 0 ) AS DamageInterestAmount,
	IFNULL( H.ClaimFee, 0 ) AS ClaimFee,
	IFNULL( H.AdditionalClaimFee, 0 ) AS AdditionalClaimFee,

	/* Claim count */
	(
		SELECT
			COUNT(*)
		FROM
			T_ClaimHistory
		WHERE
			OrderSeq = O.OrderSeq AND
			PrintedFlg <> 0 AND
            ValidFlg = 1
	) AS ReIssueCount,

	/* LimitDate for Barcode */
	DATE_ADD( H.ClaimDate, INTERVAL 65 DAY ) AS Bc_LimitDate,

	/* for ReClaim pattern 4, 5 and 6 */
	DATE_ADD( H.ClaimDate, INTERVAL 10 DAY ) AS ImplementationLimitDate,

	/* for Petition */
	DATE_ADD( H.ClaimDate, INTERVAL 50 DAY ) AS PublicTrialSchedule,

	/* literal column for update */
	-1 AS OpId,

	/* for other */
	CURRENT_DATE AS Today,

	O.Ent_OrderId,

	AC.Bk_BankCode,
	AC.Bk_BranchCode,
	AC.Bk_BankName,
	AC.Bk_BranchName,
	CONCAT(
		IFNULL(AC.Bk_BankName, ''),
		IFNULL(AC.Bk_BranchName, '')
	) AS Bk_BankAndBranchName,

	AC.Bk_DepositClass,
	(CASE AC.Bk_DepositClass WHEN 1 THEN '当座' ELSE '普通' END) AS Bk_DepositClassLabel,
	AC.Bk_AccountNumber,
	AC.Bk_AccountHolder,
	AC.Bk_AccountHolderKn,

	AC.Yu_SubscriberName,
	AC.Yu_AccountNumber,
	AC.Yu_ChargeClass,
	(CASE Yu_ChargeClass WHEN 2 THEN '加入者負担' ELSE '払込人負担' END) AS Yu_ChargeClassLabel,
	AC.Yu_SubscriberData,
	AC.Yu_Option1,
	AC.Yu_Option2,
    AC.Yu_Option3,
	AC.Yu_MtOcrCode1,
	AC.Yu_MtOcrCode2,
	AC.Yu_DtCode,

	AC.Cv_ReceiptAgentName,
	AC.Cv_ReceiptAgentCode,
	AC.Cv_SubscriberCode,
	IFNULL(AC.Cv_SubscriberName, '') AS Cv_SubscriberName,
	AC.Cv_Option1,
	AC.Cv_Option2,
	AC.Cv_Option3,
	AC.Cv_BarcodeData,
	AC.Cv_BarcodeString1,
	AC.Cv_BarcodeString2,
	AC.ConfirmNumber,
	AC.CustomerNumber,
	(CASE E.TaxClass WHEN 1
	THEN IFNULL(
		(
			SELECT
				SumMoney
			FROM
				T_OrderItems oi
				INNER JOIN T_Order t
						ON oi.OrderSeq = t.OrderSeq
			WHERE
				t.P_OrderSeq = O.OrderSeq AND
				oi.DataClass = 4 AND
				oi.ValidFlg = 1 AND
				t.Cnl_Status = 0
		), 0
	)
	ELSE AC.TaxAmount END) AS TaxAmount,
    AC.SubUseAmount_1,
    AC.SubTaxAmount_1,
    AC.SubUseAmount_2,
    AC.SubTaxAmount_2,

	/* column from T_Order */
	M.Token AS MypagePassword,
	M.AccessKey
    FROM    T_Order O
            INNER JOIN T_ClaimHistory H ON (O.OrderSeq = H.OrderSeq)
            INNER JOIN T_Customer C ON (O.OrderSeq = C.OrderSeq)
            INNER JOIN T_Enterprise E ON (O.EnterpriseId = E.EnterpriseId)
            INNER JOIN T_Site S ON (O.SiteId = S.SiteId AND O.EnterpriseId = S.EnterpriseId)
            INNER JOIN T_OemClaimAccountInfo AC ON (AC.ClaimHistorySeq = H.Seq AND AC.Status = 1)
            LEFT OUTER JOIN T_MypageOrder M ON (O.OrderSeq = M.OrderSeq)
    WHERE   O.EnterpriseId = :enterprise_id
    AND     (O.CombinedClaimTargetStatus IN (91, 92) OR IFNULL(O.CombinedClaimTargetStatus, 0) = 0)
    AND     H.Seq = (SELECT Seq FROM T_ClaimHistory WHERE OrderSeq = O.OrderSeq AND ClaimPattern = 1 AND PrintedFlg = 0 AND ValidFlg = 1 AND EnterpriseBillingCode IS NOT NULL )
    AND     O.ClaimSendingClass <> 12
    AND     S.SelfBillingFlg = 1
    AND     O.ConfirmWaitingFlg = 1
    {OrderIds}
    ORDER BY
            O.OrderSeq
EOQ;
        $cond = $this->getBindsForGetTargetConditions($params);
        if (strlen($cond['Condition']) == 0) {
            $base_query = str_replace('{OrderIds}', '', $base_query);
        } else {
            $base_query = str_replace('{OrderIds}', ' AND ('.$cond['Condition'].') ', $base_query);
        }
        $base_query .= sprintf(' LIMIT %d', (int)$this->getTargetListLimit());
        unset($cond['Condition']);

        $ri = $this->_db->query($base_query)->execute($cond);
        $rows = ResultInterfaceToArray($ri);

        //ペイジー収納機関番号取得
        $mdlCode = new TableCode($this->_db);
        $bk_number = $mdlCode->find(LogicPayeasy::PAYEASY_CODEID, LogicPayeasy::BK_NUMBER_KEYCODE)->current()['Note'];

        //再請求手数料を税込み金額に変換
        $mdlSysP = new TableSystemProperty($this->_db);
        $mdlch = new TableClaimHistory($this->_db);
        $mdlcd = new TableCode($this->_db);
        $mdlSitePayment = new TableSiteSbpsPayment($this->_db);
        foreach($rows as $key=>$row) {
            $rows[$key]['ReClaimFee'] = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $rows[$key]['ReClaimFee']);
            //収納機関番号を追加
            $rows[$key]['Bk_Number'] = $bk_number;

            //届いてから決済利用フラグが1：利用する場合に届いてから払い手続き期限日を設定
            if($rows[$key]['PaymentAfterArrivalFlg']){
                //請求履歴の最も古いレコードを取得する。
                $claimHistoryData = $mdlch->getOldestClaimHistory($rows[$key]['OrderSeq']);
                $siteId = $rows[$key]['SiteId'];
                if(!empty($claimHistoryData)) {
                    $minClaimDate = $claimHistoryData['ClaimDate'];
                }
                else{
                    $minClaimDate = 0;
                }

                //届いてから決済のサイト別の支払可能種類からMax(利用期間)を取得する。
                $creditSettlementDays = 0;
                if (!empty($minClaimDate)) {
                    $maxNumUseDay = $mdlSitePayment->getMaxNumUseDay($siteId, $minClaimDate);
                    if (!empty($maxNumUseDay)) {
                        $creditSettlementDays = $maxNumUseDay;
                        //請求履歴.請求日 + 届いてから決済のサイト別の支払可能種類.Max(利用期間)
                        $creditLimitDate = date('Y/m/d', strtotime($minClaimDate. '+'. $creditSettlementDays. ' days'));
                        $rows[$key]['CreditLimitDate'] =  $creditLimitDate;
                    }
                }
            }

            //注文マイページの利用が1:利用する場合、マイページＵＲＬを設定
            if($rows[$key]['OrderpageUseFlg']){
                $orderpageUrl = $mdlcd->getMasterCaption(105, nvl($rows[$key]['OemId'], 0)); // OEM別の注文ﾏｲﾍﾟｰｼﾞURL
                $orderpageUrl = rtrim($orderpageUrl, '/'); // 念のため、最後の"/"は除く

                $rows[$key]['MypageUrl'] =  $orderpageUrl;
            }
        }
        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => $rows
        );
    }

    /**
     * 印刷対象取得SQLを取得する
     *
     * @param boolean|null $for_count trueを指定した場合は件数取得クエリ、それ以外はリスト取得クエリ
     * @param boolean|null $for_all trueを指定した場合は全件リスト取得クエリを返す（$for_count = true時除く）
     * @return string
     */
    protected function getTargetsQuery($for_count = false, $for_all = false)
    {
// ジョブ転送中のデータ
        $base_query = <<<EOQ
            FROM    T_Order O
                    INNER JOIN T_ClaimHistory H ON (O.OrderSeq = H.OrderSeq)
                    INNER JOIN T_Customer C ON (O.OrderSeq = C.OrderSeq)
                    INNER JOIN T_Enterprise E ON (O.EnterpriseId = E.EnterpriseId)
                    INNER JOIN T_Site S ON (O.SiteId = S.SiteId AND O.EnterpriseId = S.EnterpriseId)
                    INNER JOIN T_OemClaimAccountInfo AC ON (AC.ClaimHistorySeq = H.Seq AND AC.Status = 1)
            WHERE   O.EnterpriseId = :enterprise_id
            AND     (O.CombinedClaimTargetStatus IN (91, 92) OR IFNULL(O.CombinedClaimTargetStatus, 0) = 0)
            AND     H.Seq = (SELECT Seq FROM T_ClaimHistory WHERE OrderSeq = O.OrderSeq AND ClaimPattern = 1 AND PrintedFlg = 0 AND EnterpriseBillingCode IS NOT NULL)
            AND     S.SelfBillingFlg = 1
            AND     ( SELECT MIN(DataStatus) FROM T_Order WHERE Cnl_Status = 0 AND P_OrderSeq = O.OrderSeq) = 41
            ORDER BY
                    O.OrderSeq
EOQ;
        // 全件でないリスト取得時は上限を設定する
        if(!$for_count && !$for_all) $base_query .= sprintf(' LIMIT %d', (int)$this->getTargetListLimit());
        return join('', array($for_count ? $this->getTargetsQueryPartForCount() : $this->getTargetsQueryPart(), $base_query));
    }

    /**
     * 印刷対象取得クエリのSELECTパートを取得する
     *
     * @access protected
     * @return string
     */
    protected function getTargetsQueryPart()
    {
        return <<<EOQ
               /* column from T_Order */
            SELECT  O.OrderSeq
                ,   O.OrderId
                ,   O.ReceiptOrderDate
                ,   (SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq AND O.OrderSeq) AS UseAmount
                ,   O.DataStatus
                /* 同梱請求書発行時点において請求回数は理論上0回のみ */
                ,   0 AS Clm_Count
                ,   O.Clm_L_LimitDate AS LatestLimitDate
                /* 同梱請求書発行時点に分割支払い済みは理論上ありえないので０固定とする by suzuki_h 20150730_1658 */
                ,   0 AS InstallmentPlanAmount
                /* column from T_Customer */
                ,   C.CustomerId
                ,   C.NameKj
                ,   C.PostalCode
                ,   C.PrefectureCode
                ,   C.PrefectureName
                ,   C.City
                ,   C.Town
                ,   C.Building
                ,   C.UnitingAddress
                ,   C.Phone AS CustoPhone
                /* column from T_Enterprise */
                ,   E.EnterpriseId
                ,   E.EnterpriseNameKj
                ,   CONCAT(IFNULL( E.PrefectureName, '' ), IFNULL( E.City, '' ), IFNULL( E.Town, '' ), IFNULL( E.Building, '' )) AS EnterpriseAddress
                    /* E.Phone, -> change to ContactPhoneNumber (2010.6.22) */
                ,   E.ContactPhoneNumber AS Phone
                ,   IFNULL( E.ReClaimFee, 0 ) AS ReClaimFee
                ,   E.ApplicationDate
                ,   E.PrintEntOrderIdOnClaimFlg
                /* column from T_Site */
                ,   S.SiteId
                ,   S.SiteNameKj
                ,   S.Url
                /* calcurationed column from T_OrderItems */
                ,   (SELECT SUM( SumMoney ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq AND DataClass = 1 AND ValidFlg = 1) AS TotalItemPrice
                ,   IFNULL((SELECT UnitPrice FROM T_OrderItems WHERE OrderSeq = O.OrderSeq AND DataClass = 2), 0) AS CarriageFee
                ,   IFNULL((SELECT UnitPrice FROM T_OrderItems WHERE OrderSeq = O.OrderSeq AND DataClass = 3), 0) AS ChargeFee
                ,   IFNULL((SELECT UnitPrice FROM T_OrderItems WHERE OrderSeq = O.OrderSeq AND DataClass = 4), 0) AS TaxFee
                /* column from T_ClaimHistory */
                ,   H.Seq
                ,   H.ClaimPattern
                ,   H.ClaimDate
                ,   H.LimitDate
                ,   IFNULL( H.DamageDays, 0 ) AS DamageDays
                ,   H.DamageBaseDate
                ,   IFNULL( H.DamageInterestAmount, 0 ) AS DamageInterestAmount
                ,   IFNULL( H.ClaimFee, 0 ) AS ClaimFee
                ,   IFNULL( H.AdditionalClaimFee, 0 ) AS AdditionalClaimFee
                /* Claim count */
                ,   (SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = O.OrderSeq AND PrintedFlg <> 0) AS ReIssueCount
                /* LimitDate for Barcode */
                ,   DATE_ADD( H.ClaimDate, INTERVAL 65 DAY ) AS Bc_LimitDate
                /* for ReClaim pattern 4, 5 and 6 */
                ,   DATE_ADD( H.ClaimDate, INTERVAL 10 DAY ) AS ImplementationLimitDate
                /* for Petition */
                ,   DATE_ADD( H.ClaimDate, INTERVAL 50 DAY ) AS PublicTrialSchedule
                /* literal column for update */
                ,-1 AS OpId
                /* for other */
                ,   CURRENT_DATE AS Today
                ,   O.Ent_OrderId
                ,   AC.Bk_BankCode
                ,   AC.Bk_BranchCode
                ,   AC.Bk_BankName
                ,   AC.Bk_BranchName
                ,   CONCAT(IFNULL(AC.Bk_BankName, ''), IFNULL(AC.Bk_BranchName, '')) AS Bk_BankAndBranchName
                ,   AC.Bk_DepositClass
                ,   (CASE AC.Bk_DepositClass WHEN 1 THEN '当座' ELSE '普通' END) AS Bk_DepositClassLabel
                ,   AC.Bk_AccountNumber
                ,   AC.Bk_AccountHolder
                ,   AC.Bk_AccountHolderKn
                ,   AC.Yu_SubscriberName
                ,   AC.Yu_AccountNumber
                ,   AC.Yu_ChargeClass
                ,   (CASE Yu_ChargeClass WHEN 2 THEN '加入者負担' ELSE '払込人負担' END) AS Yu_ChargeClassLabel
                ,   AC.Yu_SubscriberData
                ,   AC.Yu_Option1
                ,   AC.Yu_Option2
                ,   AC.Yu_Option3
                ,   AC.Yu_MtOcrCode1
                ,   AC.Yu_MtOcrCode2
                ,   AC.Yu_DtCode
                ,   AC.Cv_ReceiptAgentName
                ,   AC.Cv_ReceiptAgentCode
                ,   AC.Cv_SubscriberCode
                ,   IFNULL(AC.Cv_SubscriberName, '') AS Cv_SubscriberName
                ,   AC.Cv_Option1
                ,   AC.Cv_Option2
                ,   AC.Cv_Option3
                ,   AC.Cv_BarcodeData
                ,   AC.Cv_BarcodeString1
                ,   AC.Cv_BarcodeString2,
                ,   AC.TaxAmount
EOQ;
    }

    /**
     * 印刷対象取得クエリの件数取得向けSELECTパートを取得する
     *
     * @return string
     */
    protected function getTargetsQueryPartForCount()
    {
        return "SELECT  COUNT(*) AS count\n";
    }

    /**
     * コマンドパラメータと現在のアカウント情報から、
     * 印刷対象取得クエリ向けのバインドパラメータを構築する
     *
     * @access protected
     * @param array $params コマンドパラメータ
     * @return array
     */
    protected function getBindsForGetTargets($params)
    {
        return array( 'enterprise_id' => $this->_ent['EnterpriseId'] );
    }

    /**
     * コマンドパラメータと現在のアカウント情報から、
     * 印刷対象取得クエリ向けのバインドパラメータを構築する
     *
     * @access protected
     * @param array $params コマンドパラメータ
     * @return array
     */
    protected function getBindsForGetTargetConditions($params)
    {
        $oids = array();
        foreach ($params['Param'] as $data) {
            $oids[] = "OrderId='".$data['OrderId']."'";
        }

        return array( 'enterprise_id' => $this->_ent['EnterpriseId'], 'Condition' => implode(' OR ', $oids) );
    }

    /**
     * 注文商品明細リスト取得要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execGetTargetItems($params)
    {
        $base_sql = $this->getItemsQuery();

        $seqs = $params['Seqs'];
        if(!is_array($seqs)) $seqs = array();
        if(!empty($seqs)) {
            $seq = implode(',', $seqs);
            $rows = ResultInterfaceToArray($this->_db->query($base_sql)->execute(array( ':Seqs' => $seq )));
        } else {
            $rows = array();
        }
        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => $rows
        );
    }

    /**
     * 注文商品明細リスト用SQLを取得する
     *
     * @access protected
     * @return string
     */
    protected function getItemsQuery()
    {
        return <<<EOQ
            SELECT  OrderItemId
                ,   OrderSeq
                ,   ItemNameKj
                ,   UnitPrice
                ,   ItemNum
                ,   SumMoney
                ,   DataClass
                ,   Deli_ConfirmArrivalDate AS ConfirmArrivalDate
            FROM    T_OrderItems
            WHERE   OrderSeq IN ( :Seqs )
            AND     DataClass = 1
            ORDER BY
                    OrderSeq
                ,   OrderItemId
EOQ;
    }

    /**
     * 印刷可能判断要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execJudgePrintable($params)
    {
        $base_sql = $this->getJudePrintableQuery();

        $seqs = $params['Seqs'];
        $ent_id = $this->_ent['EnterpriseId'];
        if(!is_array($seqs)) $seqs = array();

        $rows = array();
        if(!empty($seqs)) {
            $seq = implode(',', $seqs);
            $ri = $this->_db->query($base_sql)->execute(array( ':Seqs' => $seq , ':enterprise_id' => $ent_id));
            foreach($ri as $row) {
                $rows[] = array('OrderSeq' => $row['OrderSeq']);
            }
        }
        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => $rows
        );
    }

    /**
     * 印刷可能判断用SQLを取得する
     *
     * @access protected
     * @return string
     */
    protected function getJudePrintableQuery()
    {
        // キャンセルは考慮しない
        // => そもそも、キャンセルされるとジョブ転送中から解除されるので。
        return <<<EOQ
    SELECT  O.OrderSeq
         ,  O.EnterpriseId
         ,  H.EnterpriseBillingCode
    FROM    T_Order O
            INNER JOIN T_ClaimHistory H ON (O.OrderSeq = H.OrderSeq)
            INNER JOIN T_Site S ON ( O.SiteId = S.SiteId )
    WHERE   O.EnterpriseId = :enterprise_id
    AND     (O.CombinedClaimTargetStatus IN (91, 92) OR IFNULL(O.CombinedClaimTargetStatus, 0) = 0)
    AND     H.Seq = (SELECT Seq FROM T_ClaimHistory WHERE OrderSeq = O.OrderSeq AND ClaimPattern = 1 AND PrintedFlg = 0 AND ValidFlg = 1 AND EnterpriseBillingCode IS NOT NULL )
    AND     O.ClaimSendingClass <> 12
    AND     ( SELECT MIN(DataStatus) FROM T_Order WHERE Cnl_Status = 0 AND P_OrderSeq = O.OrderSeq) = 41
    AND     S.SelfBillingFlg = 1
    AND     O.ConfirmWaitingFlg = 1
    AND     O.OrderSeq IN ( :Seqs )
EOQ;
    }

    /**
     * 印刷済み設定要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     * @see 子クラス（LogicSelfBillingSelfBillingApi）からも呼び出される。
     */
    public function execSetPrinted($params)
    {
        $mdlch = new TableClaimHistory($this->_db);
        $mdlodr = new TableOrder($this->_db);
        $history = new CoralHistoryOrder($this->_db);

        $seqs = $params['Seqs'];
        $ent_id = $this->_ent['EnterpriseId'];
        $current_date = date('Y-m-d H:i:s');
        if(!is_array($seqs)) $seqs = array();

        $claim_stop = (isset($this->_ent['AutoClaimStopFlg']) && $this->_ent['AutoClaimStopFlg'] == 1) ? true : false;

        $counter = 0;
        if(!empty($seqs)) {

            // 請求関連処理SQL
            $stm = $this->_db->query($this->getBaseP_ClaimControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $this->_db->getDriver()->getConnection()->beginTransaction();
            try {
                // 各モデルインスタンス生成

                foreach ($seqs as $key => $value) {

                    $poseq = $value;

                    $sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
EOQ;
                    $prm = array(
                            ':OrderSeq' => $poseq,
                    );
                    $ret = $this->_db->query($sql)->execute($prm)->current()['cnt'];
                    if ($ret == 0) {
                        // 有効な注文がいない場合はスキップ
                        continue;
                    }

                    // 請求履歴が有効かどうか判定
                    if ($mdlch->getReservedCount($poseq) <= 0) {
                        // 処理をスキップ
                        continue;
                    }

                    // 請求履歴データを取得
                    $data = $mdlch->findClaimHistory(array( 'PrintedFlg' => 0, 'ValidFlg' => 1, 'OrderSeq' => $poseq ))->current();

                    // 請求関連処理呼び出し用パラメータの設定
                    $prm = array(
                            ':pi_history_seq'   => $data['Seq'],
                            ':pi_button_flg'       => 1,
                            ':pi_user_id'          => $this->_userId,
                    );

                    $ri = $stm->execute($prm);

                    // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                    $retval = $this->_db->query($getretvalsql)->execute(null)->current();
                    if ($retval['po_ret_sts'] != 0) {
                        throw new \Exception($retval['po_ret_msg']);
                    }

                    // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                    $sql = <<<EOQ
                    SELECT  OrderSeq
                    FROM    T_Order
                    WHERE   P_OrderSeq = :P_OrderSeq
                    AND     Cnl_Status = 0
                    ;
EOQ;

                    $ri = $this->_db->query($sql)->execute(array(':P_OrderSeq' => $poseq));
                    $oseqs = ResultInterfaceToArray($ri);

                    // 注文履歴へ登録
                    // 取得できた件数分ループする
                    foreach ($oseqs as $row) {
                        // 備考に保存
                        $mdlodr->appendPrintedInfoToOemNote($row["OrderSeq"]);
                        // 注文履歴登録
                        $history->InsOrderHistory($row["OrderSeq"], 41, $userId);
                    }

                    // 請求履歴．印刷ステータス(PrintedStatus)を"9"(印刷済み)に更新する
                    $this->_db->query(" UPDATE T_ClaimHistory SET PrintedStatus = 9 WHERE Seq = :Seq ")->execute(array(':Seq' => $data['Seq']));

                    $counter++;
                }

                $this->_db->getDriver()->getConnection()->commit();

            }
            catch (\Exception $err) {
                $this->_db->getDriver()->getConnection()->rollback();
                throw new LogicSelfBillingException(
                    "以下のエラーにより印刷済みに設定できませんでした。\r\n ERROR：" . $err->getMessage(),
                    LogicSelfBillingException::ERR_GENERAL_EXCEPTION );
            }
        }

            return array(
                    self::RES_KEY_STATUS => 'OK',
                    self::RES_KEY_RESULT => array( 'count' => $counter )
            );

    }

// 2015/07/29 Del Y.Suzuki プロシージャコール → 更新件数カウントアップ へ変更のため、新システムでは廃止 Stt
//     /**
//      * 印刷済み設定用SQLを取得する
//      *
//      * @access protected
//      * @param array $seqs 抽出対象のOrderSeqのリスト
//      * @param string $current_date 実行日時
//      * @param null | boolean $claim_stop 請求ストップを実施するかのフラグ。省略時はfalse（ストップしない）
//      * @return string
//      */
//     protected function getSetPrintedUpdateQuery($seqs, $current_date, $claim_stop = false) {

//     /**
//      * 印刷済み設定後の確認用SQLを取得する
//      *
//      * @access protected
//      * @param array $seqs 抽出対象のOrderSeqのリスト
//      * @param string $current_date 実行日時
//      * @return string
//      */
//     protected function getSetPrintedCountQuery($seqs, $current_date) {
// 2015/07/29 Del Y.Suzuki プロシージャコール → 更新件数カウントアップ へ変更のため、新システムでは廃止 End

    /**
     * 指定の注文のOEM先備考に請求書印刷履歴を追記する
     *
     * @access protected
     * @param int $oseq 注文SEQ
     */
    protected function appendPrintedInfoToOemNote($oseq) {
        $orderTable = new TableOrder($this->_db);
        $orderTable->appendPrintedInfoToOemNote($oseq);
    }

    /**
     * 印紙代適用設定データ取得を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execGetStampFeeSettings($params) {
        return array(
            self::RES_KEY_STATUS => 'OK',
            self::RES_KEY_RESULT => $this->getCurrentStampFeeLogicSettings()
        );
    }

    /**
     * SMBCバーチャル口座オープン用のロックアイテムを獲得する
     *
     * @access protected
     * @param array $orderRow 対象注文の行オブジェクト
     * @return LogicThreadPoolItem | null
     */
    protected function getLockItemForSmbcpaAccount($orderRow) {
        $smbcpaTable = new TableSmbcpa($this->_db);
        $smbcpa = $smbcpaTable->findByOemId((int)$orderRow['OemId'])->current();
        if(!$smbcpa) return null;

        $pool = LogicThreadPool::getPoolForSmbcpaAccountOpen($smbcpa['SmbcpaId'], $this->_db);
        $pool->setLogger($this->getLogger());
        return $pool->openAsSingleton($orderRow['OrderSeq']);
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
            $logger->log($message, $priority);
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

    /**
     * 新システム向けの注文IDを取得する
     * @param string $orderId
     */
    protected function getOrderIdForNewSystem($orderId) {
        return $orderId;
    }

    /**
     * 請求関連処理ファンクションの基礎SQL取得。
     *
     * @return 請求関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ClaimControl() {
        return <<<EOQ
CALL P_ClaimControl(
    :pi_history_seq
,   :pi_button_flg
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }
}
