<?php
namespace models\Logic\CreditJudge;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use models\Table\TableOrder;
use models\Table\TableSite;
use models\Table\TableEnterprise;
use models\Table\TableCjResult;
use models\Table\TableCjMailHistory;
use models\Table\TableOem;
use models\Table\TableSystemProperty;
use models\View\ViewOrderCustomer;
use models\Logic\CreditJudge\Module\Coral\LogicCreditJudgeModuleCoralCore;
use models\Logic\CreditJudge\Module\Coral\LogicCreditJudgeModuleCoralExtra;
use models\Logic\CreditJudge\Module\LogicCreditJudgeModuleILuSys;
use models\Logic\CreditJudge\Module\LogicCreditJudgeModuleCoreThreshold;
use models\Logic\CreditJudge\Module\LogicCreditJudgeModuleJintec;
use models\Logic\CreditJudge\LogicCreditJudgeDbCache;
use models\Logic\CreditJudge\LogicCreditJudgeSystemConnect;
use models\Logic\CreditJudge\SystemConnect\LogicCreditJudgeSystemConnectException;
use models\Logic\CreditJudge\LogicCreditJudgePrejudgeThread;
use Coral\Base\BaseLog;
use Coral\Base\BaseDelegate;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use models\Logic\CreditJudge\Module\Coral\LogicCreditJudgeModuleCoralExtraThread;
use models\Logic\CreditJudge\Module\Coral\LogicCreditJudgeModuleCoralCoreThread;
use models\Table\TableCustomer;
use models\Table\TableOrderItems;
use models\Table\TableDeliveryDestination;
use models\Table\TableCreditCondition;
use models\Table\TableCreditConditionAddress;
use models\Table\TableCreditConditionName;
use models\Table\TableCreditConditionItem;
use models\Table\TableCreditConditionDomain;
use models\Table\TableCreditConditionEnterprise;
use models\Table\TableCreditConditionPhone;
use models\Table\TableCreditConditionMoney;
use models\View\ViewDelivery;
use Coral\Coral\History\CoralHistoryOrder;
use models\Logic\LogicShipping;
use models\Logic\MergeOrder\LogicMergeOrderHelper;
use models\Table\TableCreditLog;
use models\Table\TableJtcResult;
use models\Logic\LogicNormalizer;
use models\Table\TableManagementCustomer;
use models\Table\TableCreditSystemInfo;
use models\Table\ATableOrder;
use models\Table\TableBusinessCalendar;
use models\Table\TableAddCreditCondition;
use models\Table\TableOrderSummary;
use models\Table\TableOrderNotClose;

class LogicCreditJudgeSequencer {
    // 与信結果リストキー定数：与信OK確定分
    const RESULT_OK = 'ok';

    // 与信結果リストキー定数：与信NG確定分
    const RESULT_NG = 'ng';

    // 与信結果リストキー定数：与信保留確定分
    const RESULT_PENDING = 'pending';

    // 与信結果リストキー定数：与信再試行分
    const RESULT_RETRY = 'retry';

    // 与信結果リストキー定数：処理対象外分
    const RESULT_NOT_AVAILABLE = 'not available';

    // 実行モード定数：単独与信（＝API登録時）
    const RUNNING_MODE_SINGLE = 'single';

    // 実行モード定数：一括与信（＝バッチ）
    const RUNNING_MODE_MULTI = 'multi';

    // 実行モード定数：停止中
    const RUNNING_MODE_IDLE = 'idle';

    // コールバック種別定数：doJudgementsメソッドが開始された
    const CALLBACK_BEGIN_JUDGEMENTS = 'beginJudgements';

    // コールバック種別定数：doJudementメソッドが開始された
    const CALLBACK_BEGIN_JUDGEMENT = 'beginJudgement';

    // コールバック種別定数：preJudgeメソッドが開始された
    const CALLBACK_BEGIN_PREJUDGE = 'beginPreJudge';

    // コールバック種別定数：preJudgeメソッドが終了した
    const CALLBACK_END_PREJUDGE = 'endPreJudge';

    // コールバック種別定数：judgeMainメソッドが開始された
    const CALLBACK_BEGIN_JUDGEMAIN = 'beginJudgeMain';

    // コールバック種別定数：CoralCoreモジュールの処理が開始された
    const CALLBACK_BEGIN_CORALCORE = 'beginCoralCore';

    // コールバック種別定数：CoralCoreモジュールの処理が終了した
    const CALLBACK_END_CORALCORE = 'endCoralCore';

    // コールバック種別定数：CoralExtraモジュールの処理が開始された
    const CALLBACK_BEGIN_CORALEXTRA = 'beginCoralExtra';

    // コールバック種別定数：CoralExtraモジュールの処理が終了した
    const CALLBACK_END_CORALEXTRA = 'endCoralExtra';

    // コールバック種別定数：ILuSysモジュールの処理が開始された
    const CALLBACK_BEGIN_ILUSYS = 'beginILuSys';

    // コールバック種別定数：ILuSysモジュールの処理が終了した
    const CALLBACK_END_ILUSYS = 'endILuSys';

    // コールバック種別定数：Jintecモジュールの処理が開始された
    const CALLBACK_BEGIN_JINTEC = 'beginJintec';

    // コールバック種別定数：Jintecモジュールの処理が終了した
    const CALLBACK_END_JINTEC = 'endJintec';

    // コールバック種別定数：judgeMainメソッドが終了した
    const CALLBACK_END_JUDGEMAIN = 'endJudgeMain';

    // コールバック種別定数：doJudgementメソッドが終了した
    const CALLBACK_END_JUDGEMENT = 'endJudement';

    // コールバック種別定数：doJudgementsメソッドが終了した
    const CALLBACK_END_JUDGEMENTS = 'endJudgements';

    // 与信保留要求文言
    const CREDIT_JUDGE_PADING_REQUEST = '[与信保留]';

    /**
     * タイムアウト計測用処理開始時間
     * @var object
     */
    public $_actionStateTimestamp = NULL;

    /**
     * 既定のアプリケーション設定
     *
     * @static
     * @access protected
     * @var array
     */
    protected static $__config;

    /**
     * 既定のアプリケーション設定を取得する
     *
     * @static
     * @return string
     */
    public static function getDefaultConfig() {
        return self::$__config;
    }
    /**
     * 既定のアプリケーション設定を設定する
     *
     * @static
     * @param array $config
     */
    public static function setDefaultConfig($config) {
        self::$__config = $config;
    }

    /**
     * LogicCreditJudgeSequencer::RESULT_*で定義される与信結果定数を
     * 表示文言に変換する
     *
     * @static
     * @param string $result doJudgement系メソッドで返される与信実行結果
     * @return string $resultに対応する表示文言
     */
    public static function getJudgeResultLabel($result) {
        switch($result) {
            case self::RESULT_OK:
                return '与信OK';
            case self::RESULT_NG:
                return '与信NG';
            case self::RESULT_PENDING:
                return '与信保留';
            case self::RESULT_RETRY:
                return '与信再試行';
            case self::RESULT_NOT_AVAILABLE:
                return '与信対象外';
        }
        return $result;
    }

    /**
     * ユーザーID
     *
     * @static
     * @access protected
     * @var int
     */
    protected static $__user_id;

    /**
     * ユーザーIDを取得する
     *
     * @static
     * @return int
     */
    public static function getUserId() {
        return self::$__user_id;
    }
    /**
     * ユーザーIDを設定する
     *
     * @static
     * @param int $userId
     */
    public static function setUserId($userId) {
        self::$__user_id = $userId;
    }

    /**
     * DBアダプタ
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
     * 処理対象の注文の基準となるDataStatus
     *
     * @access protected
     * @var int
     */
    protected $_targetDs;

    /**
     * アプリケーション設定
     *
     * @access protected
     * @var array (Zend\Config\Reader\Ini)
     */
    protected $_config;

    /**
     * 与信処理結果リスト
     *
     * @access protected
     * @var array
     */
    protected $_judged_results;

    /**
     * ILU審査システム連携与信モジュール
     *
     * @access protected
     * @var LogicCreditJudgeModuleILuSys
     */
    protected $_mod_ilu;

    /**
     * 基幹システム与信モジュール
     *
     * @access protected
     * @var LogicCreditJudgeModuleCoreThreshold
     */
    protected $_mod_core;

    /**
     * Jintec連携与信モジュール
     *
     * @access protected
     * @var LogicCreditJudgeModuleJintec
     */
    protected $_mod_jintec;

    /**
     * 与信完了通知メール用識別ID
     *
     * @access protected
     * @var string
     */
    protected $_dmi_dec_seq_id = null;

    /**
     * 与信実行モード
     *
     * @access protected
     * @var string
     */
    protected $_running_mode = self::RUNNING_MODE_IDLE;

    /**
     * SMTPサーバ
     *
     * @access protected
     * @var string
     */
    protected $_smtp_server = null;

	/**
	 * ログの内部キャッシュ
	 * @access protected
	 * @var LogicCreditJudgeLogCache
	 */
	protected $_logCache;

    /**
     * 各種処理状況を通知するコールバックを管理する配列
     *
     * @access protected
     * @var array
     */
    protected $_callbacks;

    /**
     * 与信保留要求状態
     *
     * @access protected
     * @var array
     */
    protected $_pendingRequestState;

    /**
     * 与信判定基準ID
     *
     * @access protected
     * @var int
     */
    protected $_creditCriterionId;

    /**
     * 購入者 スコア初期値
     *
     * @var array
     */
    protected $_initCustUpdate = array(
            'RealCallScore' => 0,
            'RealSendMailScore' => 0,
            'Incre_NameScore' => 0,
            'Incre_AddressScore' => 0,
            'Incre_MailDomainScore' => 0,
            'Incre_PostalCodeScore' => 0,
            'Incre_TelScore' => 0,
            'Incre_MoneyScore' => 0,
            'Incre_ScoreTotal' => 0,
    );

    /**
     * 注文商品 スコア初期値
     *
     * @var array
    */
    protected $_initOrderItemsUpdate = array(
            'Incre_Score' => 0,
    );

    /**
     * 配送先 スコア初期値
     *
     * @var array
    */
    protected $_initDeliveryDestinationUpdate = array(
            'Incre_NameScore' => 0,
            'Incre_AddressScore' => 0,
            'Incre_SameCnAndAddrScore' => 0,
            'Incre_PostalCodeScore' => 0,
            'Incre_TelScore' => 0,
            'Incre_ScoreTotal' => 0,
    );

    /**
     * 注文 スコア初期化
     *
     * @var array
    */
    protected $_initOrderUpdate = array(
            'Incre_AtnEnterpriseScore' => 0,
            'Incre_BorderScore' => 0,
            'Incre_JudgeScoreTotal' => 0,
            'Incre_NoteScore' => 0,
            'Incre_PastOrderScore' => 0,
            'Incre_UnpaidScore' => 0,
            'Incre_NonPaymentScore' => 0,
            'Incre_IdentityDocumentScore' => 0,
            'Incre_MischiefCancelScore' => 0,
            'Incre_CoreScoreTotal' => 0,
            'Incre_ItemScoreTotal' => 0,
            'Incre_ScoreTotal'  => 0,
    );

    /**
     * 対象となった与信条件項目SEQS
     * @var array
     */
    protected $ConditionSeqs = array();

    /**
     * LogicCreditJudgeSequencerの新しいインスタンスを初期化する
     *
     * @param Adapter $adapter DBアダプタ
     * @param null | int $targetDs 処理対象のDataStatus。省略時は11が採用される
     * @param null | array $config 設定として使用する設定。
     *                                  省略時はクラスに設定されている既定の設定に読み替えられる。
     */
    public function __construct(Adapter $adapter, $config = null) {

        if($config == null) $config = self::getDefaultConfig();

        if (!isset(self::$__user_id)) self::setUserId(-1);

        $this->clearLogCache();
        if(LogicCreditJudgeAbstract::getDefaultLogCache() === null) {
            LogicCreditJudgeAbstract::setDefaultLogCache($this->getLogCache());
        }

        $this
            // ロギング初期設定
            ->setLogger(LogicCreditJudgeAbstract::getDefaultLogger())

            // DBアダプタ初期化
            ->setAdapter($adapter)

            // 設定のロード
            ->loadConfig($config)

            // 与信モジュール初期化
            ->initAllModules()

            // 与信実行結果バッファを初期化
            ->initJudgeResults()

            // コールバック管理を初期化
            ->initCallbacks();

        // 実行モード初期化
        $this->_running_mode = self::RUNNING_MODE_IDLE;
    }

    /**
     * DBアダプタを取得する
     *
     * @return Adapter
     */
    public function getAdapter() {
        return $this->_adapter;
    }
    /**
     * DBアダプタを設定する
     *
     * @param Adapter $adapter DBアダプタ
     * @return LogicCreditJudgeSequencer このインスタンス
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
	 * @return LogicCreditJudgeSequencer
	 */
	public function setLogger(BaseLog $logger = null) {
	    $this->_logger = $logger;
		return $this;
	}

    /**
     * ロードされているアプリケーション設定を取得する
     *
     * @return array (Zend\Config\Reader\Ini) ロードされているアプリケーション設定
     */
    public function getConfig() {
        if($this->_config == null) return null;

        return $this->_config;
    }
    /**
     * 指定の設定ファイルをロードする
     *
     * @param string $configPath 設定INIファイルのパス
     * @return LogicCreditJudgeSequencer このインスタンス
     */
    public function loadConfig($config) {

        $this->_config = $config;
        return $this;
    }
    /**
     * 与信処理向け設定を取得する
     *
     * @return array
     */
    public function getJudgeConfig() {
        return $this->getConfig()['cj_api'];
    }

    /**
     * デバッグモードであるかを判断する
     *
     * @return boolean
     */
    public function isDebugMode() {
        $config = $this->getJudgeConfig();
        return isset($config['debug_mode']) && $config['debug_mode'];
    }

    /**
     * ILU審査システム連携の回避時間を取得する
     * @return boolean
     */
    public function isBypassTime() {
        $starttime = mktime(3,55,0);
        $endtime = mktime(4,15,0);
        $now = mktime();
        return $starttime <= $now && $now <= $endtime ? true : false;
    }

    /**
     * ILU審査システム連携をバイパスすべきかを判断する
     *
     * @return boolean
     */
    public function toBeBypassIlu() {
        if($this->getCreditJudgePendingRequest()) return true;
        $config = $this->getJudgeConfig();
        return isset($config['bypass']) &&
                isset($config['bypass']['ilu']) &&
                $config['bypass']['ilu'] == 'true';
    }

    /**
     * ジンテック連携をバイパスすべきかを判断する
     *
     * @return boolean
     */
    public function toBeBypassJintec() {
        $config = $this->getJudgeConfig();
        if($this->getCreditJudgePendingRequest()) return true;
        return isset($config['bypass']) &&
                isset($config['bypass']['jintec']) &&
                $config['bypass']['jintec'] == 'true';
    }

    /**
     * 処理対象の基準となっている注文DataStatusを取得する
     *
     * @return int DataStatus
     */
    public function getTargetDataStatus() {
        $mode = $this->getCurrentRunningMode();
        if($mode == self::RUNNING_MODE_SINGLE) return 12;
        if($mode == self::RUNNING_MODE_MULTI) return 11;
        return 0;
    }

    /**
     * このインスタンスで使用するSMTPサーバ情報を取得する
     *
     * @return string
     */
    public function getSmtpServer() {
        if(empty($this->_smtp_server)) {
            $this->setSmtpServer($this->getConfig()['mail']['smtp']);
        }
        return $this->_smtp_server;
    }
    /**
     * このインスタンスで使用するSMTPサーバ情報を設定する
     *
     * @param string $smtp STMPサーバ情報
     * @return LogicCreditJudgeSequencer
     */
    public function setSmtpServer($smtp) {
        $this->_smtp_server = $smtp;
        return $this;
    }

    /**
     * 指定種別のコールバックを取得する
     *
     * @param string $type コールバック種別。このクラスのCALLBACK_で定義される定数値を指定する
     * @return BaseDelegate | null 指定種別に関連付けられているコールバック
     */
    public function getCallback($type) {
        if(!$type) return null;
        if(isset($this->_callbacks[$type]) && ($this->_callbacks[$type] instanceof BaseDelegate)) {
            return $this->_callbacks[$type];
        }
        return null;
    }

    /**
     * 指定種別のコールバックを設定する
     *
     * @param string $type コールバック種別。このクラスのCALLBACK_で定義される定数値を指定する
     * @param BaseDelegate | null コールバックデリゲート
     * @return LogicCreditJudgeSequencer
     */
    public function setCallback($type, BaseDelegate $callback = null) {
        if($type !== null) {
            $this->_callbacks[$type] = $callback;
        }
        return $this;
    }

    /**
     * 指定種別のコールバックを実行する
     *
     * @param string $type コールバック種別。このクラスのCALLBACK_で定義される定数値を指定する
     * @param null | array $args コールバック呼出しに使用するパラメータ配列
     * @return mixed コールバックの実行結果
     */
    public function execCallback($type, array $args = array()) {
        /** @var BaseDelegate */
        $callback = $this->getCallback($type);
        $callback_result = null;
        if($callback) {
            $this->debug(sprintf('execCallback called. type = %s, args = %s', $type, json_encode($args)));
            $this->debug(sprintf('execCallback callback = %s', $callback));
            $params = array_merge(array($this), $args);
            try {
                $callback_result = $callback->invokeByArray($params);
            } catch(\Exception $err) {
                $this->info('execCallback CALLBACK ERROR !!!! error = %s', $err->getMessage());
                throw $err;
            }
            $this->debug(sprintf('execCallback result = %s', $callback_result));
        }
        return $callback_result;
    }

    /**
     * ILU審査システム連携与信モジュールを取得する
     *
     * @return LogicCreditJudgeModuleILuSys
     */
    public function getModuleIluSys() {
        return $this->_mod_ilu;
    }

    /**
     * 基幹システム与信モジュールを取得する
     *
     * @return LogicCreditJudgeModuleCoreThreshold
     */
    public function getModuleCoreThreshold() {
        return $this->_mod_core;
    }

    /**
     * Jintec連携与信モジュールを取得する
     *
     * @return LogicCreditJudgeModuleJintec
     */
    public function getModuleJintec() {
        return $this->_mod_jintec;
    }

    /**
     * 与信完了通知メール用識別IDを取得する
     *
     * @return string
     */
    public function getCurrentDecSeqId() {
        return $this->_dmi_dec_seq_id;
    }

    /**
     * 現在の与信実行モードを取得する
     *
     * @return string
     */
    public function getCurrentRunningMode() {
        return $this->_running_mode;
    }

    /**
     * 最後に実行された与信処理結果をすべて取得する。
     * 戻り値の配列は、与信結果をキーとして、その結果と判定された注文SEQのリストを関連付けて
     * 格納した連想配列である。
     *
     * @return array
     */
    public function getLastJudgeResults() {
        return $this->_judged_results;
    }

    /**
     * 最後に実行された与信処理結果を、注文SEQと結果を1対1に対応させた
     * 配列として取得する
     *
     * @return array
     */
    public function getLastJudgeResultsAsOrderKey() {
        $results = array();
        if(is_array($this->_judged_results)) {
            $base_keys = array(
                self::RESULT_OK,
                self::RESULT_NG,
                self::RESULT_PENDING,
                self::RESULT_RETRY,
                self::RESULT_NOT_AVAILABLE
            );
            foreach($base_keys as $base_key) {
                foreach($this->_judged_results[$base_key] as $value) {
                    $results[$value] = $base_key;
                }
            }
        }
        return $results;
    }

    /**
     * LogicCreditJudgeAbstractで定義される与信判定結果定数値を
     * LogicCreditJudgeSequencerで定義される与信結果定数値に変換する
     *
     * @access protected
     * @param int $judge_result 各与信モジュールが返した与信結果
     * @return string このクラスで定義される与信判定結果定数値
     */
    protected function convertResultValue($judge_result) {
        // 実質的に変換されるのはJUDGE_RESULT_OK / JUDGE_RESULT_NG / JUDGE_RESULT_PENDINGのみ
        // JUDGE_RESULT_CONTINUE → RESULT_PENDINGと見なす
        // RESULT_NOT_AVAILABLE、RESULT_RETRYはjudgeMainで直接値を返しているため
        // このメソッドの引数由来には変換元の割り付けが存在しない
        switch($judge_result) {
            case LogicCreditJudgeAbstract::JUDGE_RESULT_OK:
                return self::RESULT_OK;
            case LogicCreditJudgeAbstract::JUDGE_RESULT_NG:
                return self::RESULT_NG;
            case LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE:
            case LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING:
                return self::RESULT_PENDING;
            default:
                return self::RESULT_NOT_AVAILABLE;
        }
    }

	/**
	 * ログの内部キャッシュを取得する
	 *
	 * @return LogicCreditJudgeLogCache
	 */
	public function getLogCache() {
	    return $this->_logCache;
	}
	/**
	 * ログキャッシュをクリアする
	 *
	 * @return LogicCreditJudgeSequencer
	 */
	public function clearLogCache() {
	    if($this->_logCache == null) $this->_logCache = new LogicCreditJudgeLogCache();
        $this->_logCache->clearCache();
		return $this;
	}
	/**
	 * キャッシュ済みログを取得する
	 *
	 * @return array
	 */
    public function getCachedLog() {
        if($this->_logCache == null) return array();
        return $this->_logCache->getCache();
    }

    /**
     * このインスタンスで使用する与信保留要求状態を取得する
     *
     * @return int|boolean
     */
    public function getCreditJudgePendingRequest() {
        return $this->_pendingRequestState;
    }
    /**
     * このインスタンスで使用する与信保留要求状態を設定する
     *
     * @param int|boolean $pendingRequestState
     * @return LogicCreditJudgeSequencer
     */
    public function setCreditJudgePendingRequest( $pendingRequestState = false) {
        $this->_pendingRequestState = $pendingRequestState;

        return $this;
    }

    /**
     * 与信判定基準IDを取得する
     *
     * @return int
     */
    public function getCreditCriterionId() {
        return $this->_creditCriterionId;
    }

    /**
     * 与信判定基準IDを設定する
     *
     * @param int $creditCriterionId 与信判定基準ID
     */
    public function setCreditCriterionId($creditCriterionId) {
        $this->_creditCriterionId = $creditCriterionId;

        // 与信結果ログ用にデータを保存
        $data = array();

        // [社内与信条件]
        $sql = ' SELECT * FROM M_CreditPoint WHERE CreditCriterionId = :CreditCriterionId ';
        $ri = $this->_adapter->query($sql)->execute(array(':CreditCriterionId' => $creditCriterionId));
        foreach ($ri as $row) {
            switch (intval($row['CpId'])) {
                case 105:   $data['Org_CpId105_Point']  = $row['Point']; break;
                case 106:   $data['Org_CpId106_Point']  = $row['Point']; break;
                case 107:   $data['Org_CpId107_Point']  = $row['Point']; break;
                case 108:   $data['Org_CpId108_Point']  = $row['Point']; break;
                case 109:   $data['Org_CpId109_Point']  = $row['Point']; break;
                case 201:   $data['Org_CpId201_Point']  = $row['Point']; break;
                case 202:   $data['Org_CpId202_Point']  = $row['Point']; break;
                case 203:   $data['Org_CpId203_Point']  = $row['Point']; break;
                case 206:   $data['Org_CpId206_Point']  = $row['Point']; break;
                case 301:   $data['Org_CpId301_Point']  = $row['Point']; $data['Org_CpId301_GeneralProp']  = $row['GeneralProp']; break;
                case 302:   $data['Org_CpId302_Point']  = $row['Point']; $data['Org_CpId302_GeneralProp']  = $row['GeneralProp']; break;
                case 303:   $data['Org_CpId303_Point']  = $row['Point']; $data['Org_CpId303_GeneralProp']  = $row['GeneralProp']; break;
                case 304:   $data['Org_CpId304_Point']  = $row['Point']; $data['Org_CpId304_GeneralProp']  = $row['GeneralProp']; break;
                case 401:   $data['Org_CpId401_Rate']   = $row['Rate'];  break;
                case 402:   $data['Org_CpId402_Rate']   = $row['Rate'];  break;
                case 403:   $data['Org_CpId403_Rate']   = $row['Rate'];  break;
                case 501:   $data['Org_CpId501_Description']   = $row['Description'];  break;
                default:    break;
            }
        }

        // [社内閾値]
        $sql = ' SELECT * FROM T_CreditJudgeThreshold WHERE CreditCriterionId = :CreditCriterionId ';
        $row = $this->_adapter->query($sql)->execute(array(':CreditCriterionId' => $creditCriterionId))->current();
        $data['Org_CoreSystemHoldMIN'] = $row['CoreSystemHoldMIN'];
        $data['Org_CoreSystemHoldMAX'] = $row['CoreSystemHoldMAX'];
        $data['Org_JudgeSystemHoldMIN'] = $row['JudgeSystemHoldMIN'];
        $data['Org_JudgeSystemHoldMAX'] = $row['JudgeSystemHoldMAX'];

        $GLOBALS['CreditLog'] = array_merge($data, $GLOBALS['CreditLog']);
        $GLOBALS['CreditLog']['Jud_CreditCriterion'] = $creditCriterionId;
    }

    /**
     * 注文登録API向けの単独与信処理を実行する
     *
     * @param int $oseq 注文SEQ
     * @return string 与信結果
     */
    public function doJudgementForApi($oseq, $actionStateTimestamp = NULL) {

        // 実行モードを単独与信に設定
        $this->_running_mode = self::RUNNING_MODE_SINGLE;

        // メール用識別IDを確定させる
        $this->_dmi_dec_seq_id = sprintf('%s-creditJudgeByApi-m', getFormatDateTimeMillisecond());

        //処理開始時間を設定する。(UNIX TIMESTAMP)
        $this->_actionStateTimestamp = $actionStateTimestamp;

        $error = null;
        try {
            // 与信処理実行
            $results = $this->doJudgements(array($oseq));
        } catch(\Exception $err) {
            $error = $err;
        }

        // 実行モードを停止中に戻す
        $this->_running_mode = self::RUNNING_MODE_IDLE;

        // エラーがあったらスロー
        if($error !== null) throw $error;

        // 結果を返す
        return $results[$oseq];
    }

    /**
     * バッチ処理向けに、与信実行待ち注文すべての与信処理を実行する
     * @param int $creditThreadNo 与信スレッドNo
     * @return array 与信結果のリスト。キーは注文SEQ、値は与信結果
     */
    public function doJudgementForBatch($creditThreadNo) {

        // 実行モードを全与信に設定
        $this->_running_mode = self::RUNNING_MODE_MULTI;

        // メール用識別IDを確定させる
        $this->_dmi_dec_seq_id = sprintf('%s-%s-creditJudgeByBatch-m', getFormatDateTimeMillisecond(), $creditThreadNo);

        // 与信対象の注文リストを抽出
        $order_seqs = array();
        /** @var ViewOrderCustomer */
        $view = new ViewOrderCustomer($this->getAdapter());
        // 1回の最大処理件数は1,000件に制限（2015.6.8 eda）
        $sql = " SELECT o.OrderSeq FROM T_Order o, T_Enterprise e, AT_Order ao WHERE o.EnterpriseId = e.EnterpriseId AND o.OrderSeq = ao.OrderSeq AND o.DataStatus = 11 AND o.Cnl_Status = 0 AND ao.DefectFlg = 0 AND e.CreditThreadNo = :CreditThreadNo ORDER BY o.OrderSeq LIMIT 1000 ";

        $ri = $this->_adapter->query($sql)->execute(array(':CreditThreadNo' => $creditThreadNo));
        foreach($ri as $row) {
            $order_seqs[] = $row['OrderSeq'];
        }

        $error = null;
        try {
            // 与信処理実行
            $results = $this->doJudgements($order_seqs);
        } catch(\Exception $err) {
            $error = $err;
        }

        // 実行モードを停止中に戻す
        $this->_running_mode = self::RUNNING_MODE_IDLE;

        // エラーがあったらスロー
        if($error !== null) throw $error;

        // 結果を返す
        return $results;
    }

    public function doJudgementForAwsBatch($creditThreadNo, $cjBatchSeq) {

        // 実行モードを全与信に設定
        $this->_running_mode = self::RUNNING_MODE_MULTI;

        // メール用識別IDを確定させる
        $this->_dmi_dec_seq_id = sprintf('%s-%s-creditJudgeByBatch-m', getFormatDateTimeMillisecond(), $creditThreadNo);

        // 与信対象の注文リストを抽出
        $order_seqs = array();
        /** @var ViewOrderCustomer */
        $view = new ViewOrderCustomer($this->getAdapter());
        // 1回の最大処理件数は1,000件に制限（2015.6.8 eda）
//        $sql = " SELECT o.OrderSeq FROM T_Order o, T_Enterprise e, AT_Order ao WHERE o.EnterpriseId = e.EnterpriseId AND o.OrderSeq = ao.OrderSeq AND o.DataStatus = 11 AND o.Cnl_Status = 0 AND ao.DefectFlg = 0 AND e.CreditThreadNo = :CreditThreadNo ORDER BY o.OrderSeq LIMIT 1000 ";
//
//        $ri = $this->_adapter->query($sql)->execute(array(':CreditThreadNo' => $creditThreadNo));
        $sql = " SELECT OrderSeq FROM T_CjBatchOrder WHERE CjBatchSeq = :CjBatchSeq ";
        $ri = $this->_adapter->query($sql)->execute(array(':CjBatchSeq' => $cjBatchSeq));
        foreach($ri as $row) {
            $order_seqs[] = $row['OrderSeq'];
        }

        $error = null;
        try {
            // 与信処理実行
            $results = $this->doJudgements($order_seqs);
        } catch(\Exception $err) {
            $error = $err;
        }

        // 実行モードを停止中に戻す
        $this->_running_mode = self::RUNNING_MODE_IDLE;

        // エラーがあったらスロー
        if($error !== null) throw $error;

        // 結果を返す
        return $results;
    }

    /**
     * 指定の注文SEQリストに対する与信処理を実行する
     *
     * @access protected
     * @param array $order_seqs 処理対象の注文SEQリスト
     * @return array 与信結果のリスト。キーは注文SEQ、値は与信結果
     */
    protected function doJudgements(array $order_seqs) {
        $mdlCrl = new TableCreditLog($this->_adapter);
        $mdlao = new ATableOrder($this->_adapter);
        $mdlo = new TableOrder($this->_adapter);
        $mdlonc = new TableOrderNotClose($this->_adapter);

        $start = microtime(true);
        $orderseqsCount = 0;
        if(!empty($order_seqs)) {
            $orderseqsCount = count($order_seqs);
        }
        $this->debug(sprintf('doJudgements start. target count = %s', $orderseqsCount));
        $results = array();
        $GLOBALS['MultiOrderScore'] = null; // 連続注文判定で使用するグローバル変数を初期化

        // doJudgements開始コールバックを実行
        $this->execCallback(self::CALLBACK_BEGIN_JUDGEMENTS, array($order_seqs));

        // 結果リストを初期化
        $this->initJudgeResults();
        foreach($order_seqs as $oseq) {
            $GLOBALS['CreditLog'] = $this->initCreditLog($oseq);
            $GLOBALS['CreditLog']['Jud_ManualStatus2'] = 0;

            // doJudgement開始コールバックを実行
            try {
                $this->execCallback(self::CALLBACK_BEGIN_JUDGEMENT, array($oseq));
            } catch(\Exception $callbackError) {
            }

            $result = $this->doJudgement($oseq);
            $results[$oseq] = $result;
            if($result == self::RESULT_RETRY) {
                // リトライ時はDataStatusのみ11に変更する
                $this->rollBackStatus($oseq);
            }

            // 注文履歴へ登録
            $reasonCd = null;
            switch ($result) {
                case self::RESULT_OK:
                    $reasonCd = 21;
                    break;
                case self::RESULT_NG:
                    $reasonCd = 22;
                    break;
                case self::RESULT_PENDING:
                    $reasonCd = 23;
                    break;
                default:
                    $reasonCd = null;
                    break;
            }

            // doJudgement終了コールバックを実行
            try {
                $this->execCallback(self::CALLBACK_END_JUDGEMENT, array($oseq));
            } catch(\Exception $callbackError) {
            }

            // 注文詳細「電話番号」「住所」有効フラグ　更新
            if ($reasonCd == 21){
                $sql = " UPDATE T_Customer SET ValidTel = 1, ValidAddress = 1 WHERE OrderSeq = :OrderSeq ";
                $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq));
            }

            // 自動与信NG理由 無保証変更可否 登録
            if ($reasonCd == 22){

                $NgReasonCode = null;
                if ($GLOBALS['CreditLog']['Jud_judgeTOrderClass'] == 1) {
                    $NgReasonCode = 1;
                } else if ($GLOBALS['CreditLog']['Jud_AutoUseAmountOverYN'] == 1) {
                    $NgReasonCode = 2;
                } else if ($GLOBALS['CreditLog']['Jud_SaikenCancelYN'] == 1) {
                    $NgReasonCode = 3;
                } else if ($GLOBALS['CreditLog']['Jud_NonPaymentCntYN'] == 1) {
                    $NgReasonCode = 4;
                } else if ($GLOBALS['CreditLog']['Jud_UnpaidCntYN'] == 1) {
                    $NgReasonCode = 5;
                } else if ($GLOBALS['CreditLog']['Jud_CoreStatus'] == -1) {
                    $NgReasonCode = 6;
                } else if ($GLOBALS['CreditLog']['Jud_IluStatus'] == -1) {
                    $NgReasonCode = 7;
                } else if ($GLOBALS['CreditLog']['Jud_JintecStatus'] == -1) {
                    $NgReasonCode = 8;
                } else if ($GLOBALS['CreditLog']['Jud_ManualStatus2'] == 1) {
                    $NgReasonCode = 9;
                }

                $sql = "SELECT S.NgChangeFlg, S.MuhoshoChangeDays, S.SiteId FROM T_Site S INNER JOIN T_Order O ON (O.OrderSeq = :OrderSeq) WHERE S.SiteId = O.SiteId ";
                $sdata = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                $NgLimitDay = date('Y-m-d', strtotime('+' . $sdata['MuhoshoChangeDays'] . ' day'));

                if ( $sdata['NgChangeFlg'] == 0) {
                    $BtnOkFlg = 0;
                } else {

                    // 過去二年間の取引の注文SEQを取得
                    $csql = " SELECT RegUnitingAddress, RegPhone FROM T_Customer WHERE OrderSeq = :OrderSeq ";
                    $row_c = $this->_adapter->query($csql)->execute(array(':OrderSeq' => $oseq))->current();

                    $seqs = array();
                    foreach($mdlonc->getPastNotCloseOrderSeqs(nvl($row_c['RegUnitingAddress'],''), nvl($row_c['RegPhone'],''), $sdata['SiteId']) as $row) {
                        if($row['OrderSeq'] != $oseq) {
                            $seqs[] = (int)$row['OrderSeq'];
                        }
                    }

                    // 過去二年間の未払い件数
                    $cnt = 0;
                    if(!empty($seqs)) {
                        $pastOrders = join(',', $seqs);
                        $cnt = $mdlo->findOrderCustomerByUnpaidCnt($pastOrders);
                    }

                    $BtnOkFlg = 0;
                    if ($cnt == 0) {
                        if ($NgReasonCode != 0) {
                            $csql = "SELECT Class1 FROM M_Code WHERE CodeId = 191 AND KeyCode = :KeyCode ";
                            $BtnOkFlg = $this->_adapter->query($csql)->execute(array(':KeyCode' => $NgReasonCode))->current()['Class1'];
                        }
                    }
                }

                $mdlao->saveUpdate(array('AutoJudgeNgReasonCode' => $NgReasonCode, 'NgButtonFlg' => $BtnOkFlg, 'NoGuaranteeChangeLimitDay' => $NgLimitDay), $oseq);
            }

            //注文履歴
            if (!is_null($reasonCd)) {
                $history = new CoralHistoryOrder($this->getAdapter());
                $history->InsOrderHistory($oseq, $reasonCd, self::getUserId());
            }

            // 与信保留メール送信
            if ( $GLOBALS['CreditLog']['Jud_DefectOrderYN'] == 1) {
                try {
                    CoralMail::create($this->_adapter, $this->getSmtpServer())->SendHoldMailToEnt2($oseq, self::getUserId());
                } catch(\Exception $e) {
                    // メール送信エラーについては、何もしない
                }
            }

            // 与信結果ログを保存
            $GLOBALS['CreditLog']['EndTime'] = date('Y-m-d H:i:s');
            $mdlCrl->saveNew($GLOBALS['CreditLog']);

        }

        $this->info(sprintf('doJudgements judgement completed. elapsed time = %s', (microtime(true) - $start)));

        // 単体与信モード時は事業者宛に与信開始メールを送信
        if($this->_running_mode == self::RUNNING_MODE_SINGLE) {
            try {
                $this->sendOrderedMail();
            } catch(\Exception $err) {
                $this->info(sprintf('doJudgements cannot sent mail to enterprise(ordered). error = %s', $err->getMessage()));
            }
        }

        // 事業者向け与信完了通知メール送信
        try {
            $this->sendCreditFinishMail();
        } catch(\Exception $err) {
            $this->info(sprintf('doJudgements cannot sent mail to enterprise(credit finish). error = %s', $err->getMessage()));
        }

        // ログ出力
        foreach($this->getLastJudgeResults() as $key => $list) {
            if(empty($list)) continue;
            $listCount = 0;
            if(!empty($list)) {
                $listCount = count($list);
            }
            $this->info(sprintf('[%s] -> %s items.', self::getJudgeResultLabel($key), number_format($listCount)));
        }
        $resultsCount = 0;
        if(!empty($results)) {
            $resultsCount = count($results);
        }
        $orderseqsCount = 0;
        if(!empty($order_seqs)) {
            $orderseqsCount = count($order_seqs);
        }
        $this->info(sprintf('doJudgements all tasks completed. count = %s / %s, elapsed time = %s', $resultsCount, $orderseqsCount, (microtime(true) - $start)));

        // doJudgements終了コールバックを実行
        $this->execCallback(self::CALLBACK_END_JUDGEMENTS, array($order_seqs));

        // 伝票番号の自動仮登録を実行
        $shippingLogic = new LogicShipping($this->getAdapter(), self::getUserId());
        $shippingLogic->setLogger($this->_logger);
        foreach($order_seqs as $oseq) {
            try {
                $jnResult = $shippingLogic->registerTemporaryJournalNumber($oseq);
                $this->debug(sprintf('journal-number register: result = %s', $jnResult ? 'OK' : 'NG'));

                // テスト注文時のクローズ処理
                if ($jnResult) {
                    $shippingLogic->closeIfTestOrder($oseq);
                }
            } catch(Exception $shippingLogicError) {
                $this->debug($shippingLogicError->getMessage());
            }
        }

        return $results;
    }

    /**
     * 指定の注文に対する与信処理を実行する
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @return string 与信結果
     */
    protected function doJudgement($oseq) {
        $start = microtime(true);
        $this->debug(sprintf(
                             '[%s] doJudgement start%s. running mode = %s, target DataStatus = %s, smtp = %s, ilu bypass = %s, jintec bypass = %s',
                             $oseq,
                             $this->isDebugMode() ? ' under debug mode' : '',
                             $this->getCurrentRunningMode(),
                             $this->getTargetDataStatus(),
                             $this->getSmtpServer(),
                             $this->toBeBypassIlu() ? 'yes' : 'no',
                             $this->toBeBypassJintec() ? 'yes' : 'no'));

        // 注文状態が与信可能でなければ処理せず与信対象外で完了
        if(!$this->isValidOrderStatus($oseq)) {
            $this->info(sprintf('[%s] doJudgement stopped (data status unmatch). elapsed time = %s', $oseq, (microtime(true) - $start)));
            return self::RESULT_NOT_AVAILABLE;
        }

        //強制バイパスフラグを0に設定
        $this->setCreditJudgePendingRequest(0);

        //与信保留要求設定判定
        if( $this->isPayingRequest($oseq) ){
            //強制バイパスフラグを設定
            $this->setCreditJudgePendingRequest(1);
        }

        // 与信自動化判定適用期間か判定する
        $autoFlg = $this->isAutoCreditDate($oseq);

        // 与信自動化判定適用期間の判定結果を元に、与信判定基準IDを取得する
        $creditCriterionId = $this->getCreditCriterionIdByOrder($oseq, false);

        // 与信判定基準IDを保存
        $this->setCreditCriterionId($creditCriterionId);

        // ILU審査システム連携与信モジュールを初期化する
        // コンストラクタ時点では与信判定基準IDが確定しないため、このタイミングで行う
        $this->initModuleIluSys($this->getCreditCriterionId());

        // 基幹システム与信モジュールを初期化する
        // コンストラクタ時点では与信判定基準IDが確定しないため、このタイミングで行う
        $this->initModuleCoreThreshold($this->getCreditCriterionId());

        // 与信事前処理実行
        $logicPre = new LogicCreditJudgePrejudgeThread($this->getAdapter(), $this->getConfig(), $this->getLogCache(), $oseq, self::getUserId(), $this->getCreditCriterionId());

        $sql = "SELECT E.IluCooperationFlg AS IluCooperationFlg FROM T_Order O INNER JOIN T_Enterprise E ON (O.EnterpriseId = E.EnterpriseId) WHERE O.OrderSeq = :OrderSeq ";
        $IluCooperationFlg = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['IluCooperationFlg'];
        if ($IluCooperationFlg == 0){
            // バイパス設定
            $logicPre->setBeBypassIlu(true);
            $this->info(sprintf('Enterprise ILU Bypass ON'));
        }else{
            // バイパス設定
            $logicPre->setBeBypassIlu($this->toBeBypassIlu());
        }
        // コールバック設定 → Sequencer呼び出し元で設定されているものを受け継がせる
        // 開始分
        $callback = $this->getCallback(self::CALLBACK_BEGIN_PREJUDGE);
        if ($callback != null) {
            $logicPre->setCallback(LogicCreditJudgePrejudgeThread::CALLBACK_BEGIN_PREJUDGE, $callback);
        }
        // 終了分
        $callback = $this->getCallback(self::CALLBACK_END_PREJUDGE);
        if ($callback != null) {
            $logicPre->setCallback(LogicCreditJudgePrejudgeThread::CALLBACK_END_PREJUDGE, $callback);
        }
        $logicPre->run();

        // 追加与信
        $logicExtra = new LogicCreditJudgeModuleCoralExtraThread($this->getAdapter(), $this->getConfig(), $this->getLogCache(), $oseq, $this->getCreditCriterionId(), $autoFlg);
        // コールバック設定 → Sequencer呼び出し元で設定されているものを受け継がせる
        // 開始分
        $callback = $this->getCallback(self::CALLBACK_BEGIN_CORALEXTRA);
        if ($callback != null) {
            $logicExtra->setCallback(LogicCreditJudgeModuleCoralExtraThread::CALLBACK_BEGIN_CORALEXTRA, $callback);
        }
        // 終了分
        $callback = $this->getCallback(self::CALLBACK_END_CORALEXTRA);
        if ($callback != null) {
            $logicExtra->setCallback(LogicCreditJudgeModuleCoralExtraThread::CALLBACK_END_CORALEXTRA, $callback);
        }
        $logicExtra->run();

        // 追加与信結果取得
        $logicExtraScore = $logicExtra->getScoreResult();
        $logicExtraJudge = $logicExtra->getJudgeResult();

        $logicCoreScore = null;
        if ($logicExtraJudge == LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE || $logicExtraJudge == LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING)
        {
            //保留・審査継続の場合のみ基本与信スコアリングを行う
            // 基本与信
            $logicCore = new LogicCreditJudgeModuleCoralCoreThread($this->getAdapter(), $this->getConfig(), $this->getLogCache(), $oseq, $this->getCreditCriterionId());
            // コールバック設定 → Sequencer呼び出し元で設定されているものを受け継がせる
            // 開始分
            $callback = $this->getCallback(self::CALLBACK_BEGIN_CORALCORE);
            if ($callback != null) {
                $logicCore->setCallback(LogicCreditJudgeModuleCoralCoreThread::CALLBACK_BEGIN_CORALCORE, $callback);
            }
            // 終了分
            $callback = $this->getCallback(self::CALLBACK_END_CORALCORE);
            if ($callback != null) {
                $logicCore->setCallback(LogicCreditJudgeModuleCoralCoreThread::CALLBACK_END_CORALCORE, $callback);
            }
            $logicCore->run();

            // 基本与信結果取得
            $logicCoreScore = $logicCore->getScoreResult();

             //20151209-Sodeyama 与信ＮＧ確定時のスコアリング対応（基本与信スコアリングに追加）
        } else { //与信確定時
        	$logicCoreScore = $logicExtra->getScoreResult();
        	$logicExtraScore =NULL;
        }


        // スコアリング結果保存
        try {
            $this->saveScoreResult($oseq, $logicCoreScore, $logicExtraScore);
        } catch(\Exception $err) {
            // RETRYを返し、データ更新や以降の処理を行わない
            return self::RESULT_RETRY;
        }

        // 与信本処理実行
        try {
            // judgeMain開始コールバックを実行
            try {
                $this->execCallback(self::CALLBACK_BEGIN_JUDGEMAIN, array($oseq));
            } catch(\Exception $callbackError) {
            }
            $result = $this->judgeMain($oseq, $autoFlg, $logicExtraJudge);
        } catch(\Exception $err) {
            // judgeMainの例外はCoralCore / CoralExtraでのエラーのみ
            // → RETRYを返し、データ更新や以降の処理を行わない
            // → ILuSys / Jintec の例外はjudgeMainで処理済み
            return self::RESULT_RETRY;
        }
        // judgeMain終了コールバックを実行
        try {
            $this->execCallback(self::CALLBACK_END_JUDGEMAIN, array($oseq));
        } catch(\Exception $callbackError) {
        }


        // 注文がキャンセルされていた場合
        if($this->isCancelled($oseq)) {
            // → NOT_AVAILABLEを返し、以降の処理をなにもしない
            $this->info(sprintf('[%s] doJudgement stopped (order cancelled). elapsed time = %s', $oseq, (microtime(true) - $start)));
            return self::RESULT_NOT_AVAILABLE;
        }

        // 与信結果別の処理分岐
        // → ステータス更新＋購入者宛与信結果通知メール送信
        try {
            $result = $this->updateOrderStatus($oseq, $result);
        } catch(\Exception $err) {
            // updateOrderStatusの例外はT_Order更新失敗時のみ
            // → RETRYを返し、以降の処理をなにもしない
            return self::RESULT_RETRY;
        }

        // 請求取りまとめ関連処理実行
        try {
            $this->updateCombinedClaimStatus($oseq);
        } catch(\Exception $err) {
            // → 請求取りまとめ処理失敗時はなにもしない（戻り値はjudgeMainの結果のまま）
            $this->info(sprintf('[%s] doJudement updateCombinedClaimStatus failed. error = %s', $oseq, $err->getMessage()));
        }

        // 補償外設定実行
        try {
            $this->updateOutOfAmends($oseq);
        } catch(\Exception $err) {
            // → 保証外設定失敗時はなにもしない（戻り値はjudgeMainの結果のまま）
            $this->info(sprintf('[%s] doJudgement updateOutOfAmends failed. error = %s', $oseq, $err->getMessage()));
        }

        $sql = " SELECT e.JintecFlg FROM T_Order o INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId) WHERE o.OrderSeq = :OrderSeq ";
        $entJintecFlg = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['JintecFlg'];
        if ($result ==  LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING && !isset($GLOBALS['CreditLog']['JtcSeq']) && !$this->toBeBypassJintec() && $entJintecFlg) {
            try {
                // ①LogicCreditJudgeModuleJintecのインスタンス取得(getModuleJintec())
                $lgc = $this->getModuleJintec();
                // ②$this->isAutoCreditDate()の戻り値を設定(LogicCreditJudgeModuleJintec->setAutoFlg())
                $lgc->setAutoFlg($this->isAutoCreditDate($oseq));
                // ③LogicCreditJudgeModuleJintec->judge()を呼び出し。
                $ret = $lgc->judge($oseq);
                // ④$this->saveJintecResult()を呼び出し。
                $this->saveJintecResult($ret, $lgc->getJtcSeq());
            } catch(LogicCreditJudgeSystemConnectException $connError) {
                $this->debug(sprintf('[%s] Module_Jintec::connect exception(%s times). -> %s', $oseq, 1, $connError->getMessage()));
            } catch(\Exception $err) {
                $this->info(sprintf('[%s] judgeMain.Jintec[ERROR] -> %s', $oseq, $err->getMessage()));
            }
        }

        // 結果を変換して返す
        $result = $this->convertResultValue($result);
        $this->info(sprintf('[%s] doJudgement completed normally. result = %s, elapsed time = %s', $oseq, self::getJudgeResultLabel($result), (microtime(true) - $start)));
        return $result;
    }

    /**
     * 与信結果バッファを初期化する
     *
     * @access protected
     * @return LogicCreditJudgeSequencer
     */
    protected function initJudgeResults() {
        $this->_judged_results = array(
            // 与信OK確定分
            self::RESULT_OK => array(),

            // 与信NG確定分
            self::RESULT_NG => array(),

            // 与信保留（＝手動与信対象）確定分
            self::RESULT_PENDING => array(),

            // リトライ対象分
            self::RESULT_RETRY => array(),

            // 処理対象外分
            self::RESULT_NOT_AVAILABLE => array()

        );
        return $this;
    }

    /**
     * コールバック配列を初期化する
     *
     * @access protected
     * @return LogicCreditJudgeSequencer
     */
    protected function initCallbacks() {
        $this->_callbacks = array();
        return $this;
    }

// *******************************************************************
//  与信前処理 → LogicCreditJudgePrejudgeThread の run メソッドに移植しました
//  （Thread化を想定）
// *******************************************************************
//    /**
//     * 与信前処理として、T_CjResultの初期化とILU審査システムへの登録を実行する
//     *
//     * @access protected
//     * @param int $oseq 注文SEQ
//     */
//    protected function preJudge($oseq) {
//    }

    /**
     * 指定注文の与信処理を実行する
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @param bool $autoFlg 自動化期間の場合true、それ以外はfalse
     * @param int $extraJudge 追加与信の結果
     * @return int 与信処理結果
     */
    protected function judgeMain($oseq, $autoFlg, $extraJudge) {

        $start = microtime(true);
        $this->debug(sprintf('[%s] judgeMain start', $oseq));

        // 各種モジュールの初期化
        /** @var LogicCreditJudgeModuleILuSys */
        $module_ilu = $this->getModuleIluSys();

        /** @var LogicCreditJudgeModuleCoreThreshold */
        $module_core = $this->getModuleCoreThreshold();

        /** @var LogicCreditJudgeModuleJintec */
        $module_jintec = $this->getModuleJintec();
        $module_jintec->setAutoFlg($autoFlg);

        $result_ok = LogicCreditJudgeAbstract::JUDGE_RESULT_OK;       // 与信OK確定
        $result_ng = LogicCreditJudgeAbstract::JUDGE_RESULT_NG;       // 与信NG確定
        $result_pn = LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING;  // 与信保留確定
        $result_ct = LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE; // 審査継続

// *******************************************************************
//  基本与信 → LogicCreditJudgeModuleCoralCoreThread の run メソッドに移植しました
//  追加与信 → LogicCreditJudgeModuleCoralExtraThread の run メソッドに移植しました
//  （Thread化を想定）
// *******************************************************************

        // Coral基準の基本与信・追加与信
        // 追加与信の結果を設定
        $result = $extraJudge;
        $this->debug(sprintf('[%s] judgeMain.Coral -> %s', $oseq, LogicCreditJudgeAbstract::getJudgeResultLabel($result)));

        // 与信保留要求の場合は保留に上書き
        if($this->getCreditJudgePendingRequest()){

            $result = $result_pn;
            //与信保留要求フラグを0に戻す
            $this->setCreditJudgePendingRequest(0);

            //ログに書き込む
            $this->info(sprintf('[%s] judgeMain Pending Bypass time = %s',$oseq,(microtime(true) - $start)));
        }

        // 審査継続以外は結果確定
        if($result != $result_ct) {
            $this->info(sprintf('[%s] judgeMain completed. elapsed time = %s', $oseq, (microtime(true) - $start)));
            return $result;
        }

        // 与信判定期間が自動化期間でない場合
        if (!$autoFlg) {
            // 追加与信とロジック共通
            $coral_extra = new LogicCreditJudgeModuleCoralExtra($this->getAdapter(), $this->getJudgeConfig());

            // 与信審査可能金額か確認する
            // 利用額が自動判定可能金額以上であれば保留
            if($coral_extra->judgeUseAmountOver($oseq)) {
                $GLOBALS['CreditLog']['Jud_UseAmountOverYN'] = 1;
                // 保留なしの場合、NGにする
                if($coral_extra->judgeNoPendingEnt($oseq)) {
                    $this->info(sprintf('[%s] judgeMain completed NG (over use amount). elapsed time = %s', $oseq, (microtime(true) - $start)));
                    return $result_ng;
                }
                else {
                    $this->info(sprintf('[%s] judgeMain completed PENDING (over use amount). elapsed time = %s', $oseq, (microtime(true) - $start)));
                    return $result_pn;
                }
            }

            // 利用限度額以内か判定する
            if($coral_extra->judgeCreditLimitAmountOver($oseq)) {
                $GLOBALS['CreditLog']['Jud_UseAmountOverYN'] = 1;
                // 保留なしの場合、NGにする
                if($coral_extra->judgeNoPendingEnt($oseq)) {
                    $this->info(sprintf('[%s] judgeMain completed NG (over limit amount). elapsed time = %s', $oseq, (microtime(true) - $start)));
                    return $result_ng;
                }
                else {
                    $this->info(sprintf('[%s] judgeMain completed PENDING (over limit amount). elapsed time = %s', $oseq, (microtime(true) - $start)));
                    return $result_pn;
                }
            }
        }

        // 手動与信強制化判定
        $result = $this->judgeJintecManual($oseq, $result, $module_jintec->getJtcSeq());
        $this->debug(sprintf('[%s] judgeMain.judgeJintecManual -> %s', $oseq, $result));
        if ($result ==  LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING) {
            return $result;
        }

        // ILU審査システム連携与信    ----------------------------------------------------------
        // → 例外時は審査継続とする
        if($this->toBeBypassIlu()) {
            // バイパス設定時は基幹システムのみで判定
            $this->info(sprintf('[%s] judgeMain.ILuSys bypassed -> Core Only Judge', $oseq));

            try {
                $result = $module_core->judge($oseq);
                $this->debug(sprintf('[%s] judgeMain.Core -> %s', $oseq, LogicCreditJudgeAbstract::getJudgeResultLabel($result)));
                // 審査継続以外は結果確定
                if($result != $result_ct) {
                    $GLOBALS['CreditLog']['Jud_CoreStatus'] = $result == $result_pn ? 0 : $result;
                    $this->info(sprintf('[%s] judgeMain completed. elapsed time = %s', $oseq, (microtime(true) - $start)));
                    return $result;
                }
            } catch(\Exception $err) {
                // ログ出力のみでなにもしない
                $this->info(sprintf('[%s] judgeMain.Core[ERROR] -> %s', $oseq, $err->getMessage()));
            }
        } else {
            // ILuSys開始コールバックを実行
            $callback_result = false;
            try {
                $callback_result = $this->execCallback(self::CALLBACK_BEGIN_ILUSYS, array($oseq));
            } catch(\Exception $callbackError) {}

            // コールバックがfalseを返さなかったらILU連携を実行
            if($callback_result !== false) {
                try {
                    $result = $module_ilu->judge($oseq);
                    $this->debug(sprintf('[%s] judgeMain.ILuSys -> %s', $oseq, LogicCreditJudgeAbstract::getJudgeResultLabel($result)));
                    // 審査継続以外は結果確定
                    if($result != $result_ct) {
                        $GLOBALS['CreditLog']['Jud_IluStatus'] = $result == $result_pn ? 0 : $result;
                        $this->info(sprintf('[%s] judgeMain completed. elapsed time = %s', $oseq, (microtime(true) - $start)));
                        return $result;
                    }
                } catch(\Exception $err) {
                    // ログ出力のみでなにもしない
                    $this->info(sprintf('[%s] judgeMain.ILuSys[ERROR] -> %s', $oseq, $err->getMessage()));
                }
            } else {
                $this->info(sprintf('[%s] judgeMain.ILuSys skipped by callback.', $oseq));
                $result = LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE;
            }

            // ILuSys終了コールバックを実行
            try {
                $callback_result = $this->execCallback(self::CALLBACK_END_ILUSYS, array($oseq));
            } catch(\Exception $callbackError) {}
        }

        // Jintec連携与信   -----------------------------------------------------------------
        // → 例外時は審査継続とする
        // 加盟店情報取得
        $order = $this->fetchOrderCustomer($oseq)->current();
        $mdle = new TableEnterprise($this->getAdapter());
        $enterprise = $mdle->findEnterprise($order['EnterpriseId'])->current();
        if($this->toBeBypassJintec()) {
            // バイパス設定時は何もしない
            $this->info(sprintf('[%s] judgeMain.Jintec bypassed', $oseq));
        } elseif ($enterprise['JintecFlg'] == 0) {
            // 加盟店.ジンテック与信 = 0(行わない) の場合は何もしない
            $this->info(sprintf('[%s] judgeMain.Jintec skip', $oseq));
        } else {
            // Jintec開始コールバックを実行
            $callback_result = false;
            try {
                $callback_result = $this->execCallback(self::CALLBACK_BEGIN_JINTEC, array($oseq));
            } catch(\Exception $callbackError) {}

            // コールバックがfalseを返さなかったらJintec連携を実行
            if($callback_result !== false) {
                $retry = 0;
                $retry_max = 3;
                while(++$retry <= $retry_max) {
                    try {
                        //$module_jintec->setOption('debug_mode', true);
                        $result = $module_jintec->judge($oseq);
                        $this->debug(sprintf('[%s] judgeMain.Jintec -> %s', $oseq, LogicCreditJudgeAbstract::getJudgeResultLabel($result)));

                        // ジンテック結果を保存
                        $this->saveJintecResult($result, $module_jintec->getJtcSeq());

                        // 審査継続以外は結果確定
                        if($result != $result_ct) {
                            $GLOBALS['CreditLog']['Jud_JintecStatus'] = $result == $result_pn ? 0 : $result;
                            $this->info(sprintf('[%s] judgeMain completed. elapsed time = %s', $oseq, (microtime(true) - $start)));
                            return $result;
                        }
                    } catch(LogicCreditJudgeSystemConnectException $connError) {
                        // 接続エラー時は既定回数リトライを試みる
                        $this->debug(sprintf('[%s] Module_Jintec::connect exception(%s times). -> %s', $oseq, $retry, $connError->getMessage()));
                        if($retry < $retry_max) {
                            // リトライ数が既定回数未満の場合は1秒WAITを入れる
                            usleep(1 * 1000000);
                        } else {
                            // 既定回数に達したらログ出力
                            $this->info(sprintf('[%s] judgeMain.Jintec[ERROR] -> %s', $oseq, $connError->getMessage()));
                        }
                    } catch(\Exception $err) {
                        // ログ出力のみでなにもしない
                        $this->info(sprintf('[%s] judgeMain.Jintec[ERROR] -> %s', $oseq, $err->getMessage()));
                        break;
                    }
                }
            } else {
                $this->info(sprintf('[%s] judgeMain.Jintec skipped by callback.', $oseq));
                $result = LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE;
            }

            // Jintec終了コールバックを実行
            try {
                $this->execCallback(self::CALLBACK_END_JINTEC, array($oseq));
            } catch(\Exception $callbackError) {}
        }

        $this->info(sprintf('[%s] judgeMain completed. result = [%s:%s], elapsed time = %s',
                             $oseq,
                             $result,
                             LogicCreditJudgeAbstract::getJudgeResultLabel($result),
                             (microtime(true) - $start)));

        // ここまで来て審査継続だったら保留確定にする
        return $result_pn;
    }

    /**
     * 指定の注文がキャンセルされているかを判断する
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @return boolean
     */
    protected function isCancelled($oseq) {
        return $this->fetchOrderCustomer($oseq)->current()['Cnl_Status'] > 0;
    }

    /**
     * 指定注文を与信完了状態に更新し、必要であれば
     * 購入者向け通知メール予約を行う
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @param int $result 与信結果
     */
    protected function updateOrderStatus($oseq, $result) {
        $udata = array();
        $order = $this->fetchOrderCustomer($oseq)->current();
        $decSeqId = $this->getCurrentDecSeqId();
        $mailReason = 0;    // 購入者向け与信完了通知メールモード

        $mdle = new TableEnterprise($this->getAdapter());
        $enterprise = $mdle->findEnterprise($order['EnterpriseId'])->current();



        // 加盟店.手動与信＝行わないの場合で、ここまでの結果が保留の場合、与信OKとする
        if ($enterprise['ManualJudgeFlg'] == 0 && $result == LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING) {
            $result = LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
        }

        else if ($enterprise['AutoCreditJudgeMode'] == 4 && $result == LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING && $GLOBALS['SkipTarget'] == 1) {
            $this->debug(sprintf('skip target OK. oseq = %s', $oseq));
            $result = LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
        }

        // 加盟店.自動与信ﾓｰﾄﾞ=4(保留なしの場合）、ここまでの結果が保留の場合、与信NGとする
        else if($enterprise['AutoCreditJudgeMode'] == 4 && $result == LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING) {
            $result = LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
            $GLOBALS['CreditLog']['Jud_ManualStatus2'] = 1;
        }

        $GLOBALS['CreditLog']['Jud_ManualStatus'] = $result == LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING ? 0 : $result;

        // 保留ボックス判定
        $isHoldBox = $this->judgeHoldBox($oseq, $result);
        if ($isHoldBox) {
            $GLOBALS['CreditLog']['Jud_DefectOrderYN'] = 1;
            $this->debug(sprintf('send to hold box. oseq = %s, judgeResult = %s', $oseq, $result));
            throw new \Exception('input defect order'); // 入力不備があるため、本注文の与信処理を終了させる
        }

        // システムプロパティの注文登録APIタイムアウト時間（秒）
        $mdlSysP = new TableSystemProperty($this->getAdapter());
        $apiOrderRestTimeOut = $mdlSysP->getValue('api', 'order', 'ApiOrderRest_TimeOut');

        // コメントの初期値を設定（NG）
        $note = sprintf("（%s与信自動ＮＧ[%s]）\n----\n%s"
                      , $this->_running_mode == self::RUNNING_MODE_SINGLE ? 'APIリアルタイム' : ''
                      , date('Y-m-d')
                      , $order['Incre_Note']
        );

        // 加盟店.注文登録APIタイムアウトフラグ=1(利用する)
        if ($enterprise['ApiOrderRestTimeOutFlg'] == '1')
        {
            // かつ、注文登録APIで設定される処理開始時間がnullでない
            if (!is_null($this->_actionStateTimestamp))
            {
                // かつ、（現在日時 － 処理開始時間） ＞ （システムプロパティ.注文登録APIタイムアウト時間（秒） － １）
                if ((time() - $this->_actionStateTimestamp) > ($apiOrderRestTimeOut - 1))
                {
                    $result = LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                    $GLOBALS['CreditLog']['Jud_ManualStatus2'] = 1;
                    $note = sprintf("（%s与信時間超過による与信NG[%s]）\n----\n%s"
                                  , $this->_running_mode == self::RUNNING_MODE_SINGLE ? 'APIリアルタイム' : ''
                                  , date('Y-m-d')
                                  , $order['Incre_Note']
                    );
                }
            }
        }

        // 与信結果別に更新内容を確定させる
        switch($result) {
            case LogicCreditJudgeAbstract::JUDGE_RESULT_OK:
                // 与信OK
                $note = sprintf("（%s与信自動ＯＫ[%s]）\n----\n%s",
                                $this->_running_mode == self::RUNNING_MODE_SINGLE ? 'APIリアルタイム' : '',
                                date('Y-m-d'),
                                $order['Incre_Note']);
                $udata = array(
                    'Incre_Status' => 1,
                    'Incre_DecisionDate' => date('Y-m-d'),
                    'Incre_DecisionOpId' => 0,
                    'Dmi_Status' => 1,
                    'DataStatus' => 31,
                    'Dmi_DecSeqId' => $decSeqId,
                    'Incre_Note' => $note,
                    'UpdateId' =>self::getUserId(),
                );
                $this->_judged_results[self::RESULT_OK][] = $oseq;
                $mailReason = 1;
                break;
            case LogicCreditJudgeAbstract::JUDGE_RESULT_NG:
                // 与信NG
                $udata = array(
                    'Incre_Status' => -1,
                    'Incre_DecisionDate' => date('Y-m-d'),
                    'Incre_DecisionOpId' => 0,
                    'Dmi_Status' => -1,
                    'DataStatus' => 91,
                    'CloseReason' => 3,
                    'Dmi_DecSeqId' => $decSeqId,
                    'Incre_Note' => $note,
                    'UpdateId' =>self::getUserId(),
                );
                $this->_judged_results[self::RESULT_NG][] = $oseq;
                $mailReason = 2;
                break;
            case LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING:
                $udata = array(
                    'DataStatus' => 15,
                    'UpdateId' =>self::getUserId(),
                );
                $this->_judged_results[self::RESULT_PENDING][] = $oseq;
                break;
            default:
                // この段階でOK/NG/保留のいずれでもない場合は例外
                throw new \Exception('invalid judge result');
        }

        // ステータス更新実行
        $table = new TableOrder($this->getAdapter());
        $table->saveUpdate($udata, $oseq);

        // OK/NGの場合は購入者宛メール送信予約を登録
        if($mailReason > 0) {
            $this->debug(sprintf('reserved to sending email to customer. oseq = %s, reason = %s', $oseq, $mailReason));
            $mailTable = new TableCjMailHistory($this->getAdapter());
            try {
                $mailTable->rsvCjMail($oseq, $mailReason, self::getUserId());
            } catch(\Exception $err) {
                // メール送信予約失敗時はなにもしない
            }
        }
        return $result;
    }

	/**
	 * 指定注文の請求取りまとめステータスを設定する
     * @access protected
	 * @param int $oseq 注文SEQ
	 */
	protected function updateCombinedClaimStatus($oseq) {

        // 注文情報取得
        $order = $this->fetchOrderCustomer($oseq)->current();

        // 事業者情報を取得
        $mdle = new TableEnterprise($this->getAdapter());
        $enterprise = $mdle->findEnterprise($order['EnterpriseId'])->current();

        // 請求取りまとめモードを確認
        $combinedClaimTargetStatus = 0;// 初期値:なし
        if($enterprise['CombinedClaimMode'] == 1) {
            // 事業者毎
            $combinedClaimTargetStatus = 1;
        }
        else if ($enterprise['CombinedClaimMode'] == 2) {
            // サイト情報を取得
            $mdls = new TableSite($this->getAdapter());
            $site = $mdls->findSite($order['SiteId'])->current();

            if($site['CombinedClaimFlg'] == 1) {
                // サイト毎
                $combinedClaimTargetStatus = 2;
            }
            else {
                // なし
                $combinedClaimTargetStatus = 0;
            }
        }

        // UPDATE
        $udata['CombinedClaimTargetStatus'] = $combinedClaimTargetStatus;
        $udata['UpdateId'] = self::getUserId();
        $table = new TableOrder($this->getAdapter());
        $table->saveUpdate($udata, $oseq);
	}

	/**
	 * 指定注文の補償外自動設定を実行する
     * @access protected
	 * @param int $oseq 注文SEQ
	 */
	protected function updateOutOfAmends($oseq) {

        // 注文情報取得
        $order = $this->fetchOrderCustomer($oseq)->current();

        // サイト情報を取得
        $mdls = new TableSite($this->getAdapter());
        $site = $mdls->findSite($order['SiteId'])->current();

        // サイト毎 or なし
        $outOfAmends = ($site['OutOfAmendsFlg'] == 1) ? 1 : 0;

        // UPDATE
        $udata['OutOfAmends'] = $outOfAmends;
        $udata['UpdateId'] = self::getUserId();
        $table = new TableOrder($this->getAdapter());
        $table->saveUpdate($udata, $oseq);

        // 補償外案件の状況によって請求取りまとめを更新する
        $mghelper = new LogicMergeOrderHelper($this->getAdapter(), $oseq);
        if($mghelper->chkCcTargetStatusByOutOfAmends($outOfAmends) != 9) {
            unset($udata);
            $udata['CombinedClaimTargetStatus'] = $mghelper->chkCcTargetStatusByOutOfAmends($outOfAmends);
            $table->saveUpdate($udata, $oseq);
        }
	}

    /**
     * 指定注文のDataStatusを与信実行待ちにロールバックする
     *
     * @access protected
     * @param int $oseq 注文SEQ
     */
    protected function rollBackStatus($oseq) {
        $sql = " UPDATE T_Order SET DataStatus = 11 WHERE OrderSeq = :OrderSeq ";
        $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq));
    }

    /**
     * 今回の与信によって結果が確定した事業者へ確定メールを送信する
     *
     * @access protected
     */
    protected function sendCreditFinishMail() {
        $decSeqId = $this->getCurrentDecSeqId();

        foreach($this->getMailTargetEntInfoForCreditFinish() as $info) {
            // メールの宛先となる事業者IDを入力データから取得
            $entId = $info["EnterpriseId"];
            // メールの宛先となるSiteIDを入力データから取得
            $siteId = $info["SiteId"];
            $this->debug(sprintf('sending mail to enterprise(credit finish). ent = %s, site = %s', $entId, $siteId));

            // 注文登録メールを送信
            try {
                CoralMail::create($this->getAdapter(), $this->getSmtpServer())
                    ->SendCreditFinishEachEnt(
                        $entId,
                        $siteId,
                        $decSeqId,
                        self::getUserId()
                    );
            } catch(CoralMailException $e) {
                // CoralMail内での例外のみ捕捉。エラーにはしない。
                $ie = $e->getInnerException();
                $this->info(sprintf(
                                    'credit finish mail send failed(credit finish). ent = %s, site = %s, error = %s : %s',
                                    $info['EnterpriseId'], $info['SiteId'], $e->getMessage(), $ie != null ? $ie->getMessage() : ''));
            }
        }
    }

    /**
     * 今回の与信によって与信保留となった事業者へ、与信開始メールを送信する
     *
     * @access protected
     */
    protected function sendOrderedMail() {
        foreach($this->getMailTargetEntInfoForOrdered() as $entId => $oseqs) {
            $oseqCount = 0;
            if(!empty($oseqs)) {
                $oseqCount = count($oseqs);
            }
            $this->debug(sprintf('sending mail to enterprise(ordered). ent = %s, count = %s', $entId, $oseqCount));

            // 注文登録メールを送信
            try {
                CoralMail::create($this->_adapter, $this->getSmtpServer())->SendOrderedMail($entId, $oseqs, self::getUserId());
            } catch(CoralMailException $e) {
                // CoralMail内での例外のみ捕捉。エラーにはしない。
                $ie = $e->getInnerException();
                $this->info(sprintf(
                                    'credit finish mail send failed(ordered). ent = %s, error = %s : %s',
                                    $entId, $e->getMessage(), $ie != null ? $ie->getMessage() : ''));
            }
        }
    }

    /**
     * 現在の与信実行結果のうち、与信OK確定・与信NG確定の注文があった事業者の
     * 事業者ID・サイトIDペアを集計する。
     * 戻り値はキーが事業者ID＋サイトID、値が事業者IDとサイトIDを格納した連想配列となる
     *
     * @access protected
     * @return array
     */
    protected function getMailTargetEntInfoForCreditFinish() {
        $db_cache = new LogicCreditJudgeDbCache($this->getAdapter());
        $raw_results = $this->getLastJudgeResults();

        $results = array();
        $target_result_keys = array(self::RESULT_OK, self::RESULT_NG);
        foreach($target_result_keys as $result_key) {
            foreach($raw_results[$result_key] as $oseq) {
                $order = $db_cache->fetchOrderCustomer($oseq)->current();
                $ent = $db_cache->fetchEnterprise($order['EnterpriseId'])->current();

                $match_key = sprintf('%s-%s', $ent['EnterpriseId'], $order['SiteId']);
                if(!isset($results[$match_key])) {
                    $results[$match_key] = array(
                        'EnterpriseId' => $ent['EnterpriseId'],
                        'SiteId' => $order['SiteId']
                    );
                }
            }
        }
        return $results;
    }

    /**
     * 現在の与信実行結果のうち、与信保留の注文があった事業者の事業者IDを集計する
     * 戻り値はキーが事業者ID、値が保留となった注文のSEQのリストとなる
     *
     * @access protected
     * @return array
     */
    protected function getMailTargetEntInfoForOrdered() {
        $db_cache = new LogicCreditJudgeDbCache($this->getAdapter());
        $raw_results = $this->getLastJudgeResults();

        $results = array();
        $target_result_keys = array(self::RESULT_PENDING);
        foreach($target_result_keys as $result_key) {
            foreach($raw_results[$result_key] as $oseq) {
                $order = $db_cache->fetchOrderCustomer($oseq)->current();
                $entId = $order['EnterpriseId'];
                if(!isset($results[$entId]) || !is_array($results[$entId])) {
                    $results[$entId] = array();
                }
                $results[$entId][] = $oseq;
            }
        }
        return $results;
    }

    /**
     * すべての与信モジュールを初期化する
     *
     * @access protected
     * @return LogicCreditJudgeSequencer
     */
    protected function initAllModules() {
        $this
            ->initModuleJintec();
        return $this;
    }

    /**
     * ILU審査システム連携与信モジュールを初期化する
     *
     * @access protected
     * @return LogicCreditJudgeSequencer
     */
    protected function initModuleIluSys($creditCriterionId) {
        $this->_mod_ilu = new LogicCreditJudgeModuleILuSys($this->getAdapter(), $creditCriterionId);
        return $this;
    }

    /**
     * 基幹システム与信モジュールを初期化する
     *
     * @access protected
     * @return LogicCreditJudgeSequencer
     */
    protected function initModuleCoreThreshold($creditCriterionId) {
        $this->_mod_core = new LogicCreditJudgeModuleCoreThreshold($this->getAdapter(), $creditCriterionId);
        return $this;
    }

    /**
     * Jintec連携与信モジュールを初期化する
     *
     * @access protected
     * @return LogicCreditJudgeSequencer
     */
    protected function initModuleJintec() {
        $this->_mod_jintec = new LogicCreditJudgeModuleJintec($this->getAdapter(), $this->getJudgeConfig());
        $this->_mod_jintec->setUserId(self::getUserId());
        return $this;
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
		$map = array(
			'EMERG', 'ALERT', 'CRIT', 'ERR',
			'WARN', 'NOTICE', 'INFO', 'DEBUG'
		);
        $fixed_message = sprintf('%s %s (%s) %s',
                                     date('Y-m-d H:i:s'),
                                     $map[$priority],
                                     $priority,
                                     $message);
		try {
            if($this->_logCache !== null) {
                $this->_logCache->append($fixed_message);
            }
		} catch(\Exception $err) {
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
	 * V_OrderCustomerから指定注文のデータを取得する
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return ResultInterface V_OrderCustomerから取得したデータ
	 */
	protected function fetchOrderCustomer($oseq) {
        $view = new ViewOrderCustomer($this->getAdapter());
        return $view->findOrderCustomerByOrderSeq($oseq);
	}

    /**
     * 指定注文の注文ステータス（DataStatus）が、現在の与信実行モードで与信可能な状態であるかを
     * 判断する
     *
     * @param int $oseq 注文SEQ
     * @return boolean 指定注文が現在のモードで与信実行可能ならtrue、それ以外はfalse
     */
    protected function isValidOrderStatus($oseq) {
        $ri = $this->fetchOrderCustomer($oseq);
        $targetDs = $this->getTargetDataStatus();

        if (!($ri->count() > 0)) {
            return false;
        }
        $order = $ri->current();

        if($order['DataStatus'] == $targetDs && $order['Cnl_Status'] == 0) {
            // 現在処理すべきDataStatusで且つキャンセルされていなければOK
            return true;
        }
        return false;
    }

    /**
     * 指定の注文の事業者が保留要求設定か判断する
     * @param int $oseq 注文SEQ
     * @return boolean
     */
    protected function isPayingRequest($oseq) {

        //orderCustomer取得
        $order = $this->fetchOrderCustomer($oseq)->current();

        //事業者情報取得
        $enterprise = $this->getEnterprise($order['EnterpriseId']);

        //事業者の保留要求が有効
        if( $enterprise['CreditJudgePendingRequest'] == 1 && strpos($order['Ent_Note'],self::CREDIT_JUDGE_PADING_REQUEST) === 0 ) {
            //与信保留要求有効
            return true;
        }

        //与信保留要求無効
        return false;
    }

    /**
     * 指定IDに一致する事業者を取得する
     * @param int $entId 事業者ID
     * @return ResultInterface
     */
    protected function getEnterprise($entId) {
        $table = new TableEnterprise($this->getAdapter());
        return $table->find($entId)->current();
    }

    /**
     * 指定注文の与信自動化期間を取得する
     *
     * @param int $oseq 注文SEQ
     * @return array 発見した場合は与信自動化期間、そうでない場合はnull
     */
    protected function getAutoCreditDateSpan($oseq) {
        // ビューを取得
        $ri = $this->fetchOrderCustomer($oseq);
        // 取得できない場合、null
        if (!($ri->count() > 0)) {
            return null;
        }
        $order = $ri->current();

        // サイトの与信自動化期間
        $siteId = $order['SiteId'];
        if (isset($siteId)) {
            $mdlSite = new TableSite($this->getAdapter());
            $ri = $mdlSite->findSite($siteId);
            // 取得できない場合、次の判定へ
            if ($ri->count() > 0) {
                $site = $ri->current();
                if (isset($site['AutoCreditDateFrom']) && isset($site['AutoCreditDateTo'])) {
                    // 取得できた場合終了
                    $autCreditDate['AutoCreditDateFrom'] = $site['AutoCreditDateFrom'];
                    $autCreditDate['AutoCreditDateTo'] = $site['AutoCreditDateTo'];

                    return $autCreditDate;
                }
            }
        }

        // OEMの与信自動化期間
        $oemId = $order['OemId'];
        if (isset($oemId)) {
            $mdlOem = new TableOem($this->getAdapter());
            $ri = $mdlOem->find($oemId);
            // 取得できない場合、次の判定へ
            if ($ri->count() > 0) {
                $oem = $ri->current();
                if (isset($oem['AutoCreditDateFrom']) && isset($oem['AutoCreditDateTo'])) {
                    // 取得できた場合終了
                    $autCreditDate['AutoCreditDateFrom'] = $oem['AutoCreditDateFrom'];
                    $autCreditDate['AutoCreditDateTo'] = $oem['AutoCreditDateTo'];

                    return $autCreditDate;
                }
            }
        }

        // システムプロパティの与信自動化期間
        $mdlSysP = new TableSystemProperty($this->getAdapter());
        // FROM
        $sysP = $mdlSysP->getValue('[DEFAULT]', 'systeminfo', 'AutoCreditDateFrom');
        if (!empty($sysP)) {
            $autCreditDate['AutoCreditDateFrom'] = $sysP;
        }
        // TO
        $sysP = $mdlSysP->getValue('[DEFAULT]', 'systeminfo', 'AutoCreditDateTo');
        if (!empty($sysP)) {
            $autCreditDate['AutoCreditDateTo'] = $sysP;
        }
        if (isset($autCreditDate) && count($autCreditDate) == 2) {
            // 取得できた場合終了
            return $autCreditDate;
        }

        // 見つからなかった場合、null
        return null;
    }

    /**
     * 指定注文が与信自動化判定適用期間か判定する
     *
     * @param int $oseq 注文SEQ
     * @return boolean 自動化期間の場合true、それ以外はfalse
     */
    protected function isAutoCreditDate($oseq) {
        // 与信自動化期間の取得
        $autCreditDate = $this->getAutoCreditDateSpan($oseq);

        if (isset($autCreditDate)) {
            // 見つかった場合、現在時間で判定
            $now = date("Y-m-d");
            if (   strtotime($autCreditDate['AutoCreditDateFrom']) <= strtotime($now)
                && strtotime($autCreditDate['AutoCreditDateTo']) >= strtotime($now)
            ) {
                $autoFlg = true;
            }
            else {
                $autoFlg = false;
            }
        }
        else {
            // 見つからなかった場合
            $autoFlg = false;
        }

        $this->debug(sprintf('[%s] isAutoCreditDate autoFlg = %s', $oseq, $autoFlg ? 'true' : 'false'));

        // 与信期間の判定結果を返す
        return $autoFlg;
    }

    /**
     * 指定注文の与信判定基準IDを取得する
     *
     * @param int $oseq 注文SEQ
     * @param boolean $autoFlg 自動化期間の場合true、それ以外はfalse
     * @return int 与信判定基準ID(見つからない場合0)
     */
    protected function getCreditCriterionIdByOrder($oseq, $autoFlg)
    {
        // ビューを取得
        $ri = $this->fetchOrderCustomer($oseq);
        // 取得できない場合、0
        if (!($ri->count() > 0)) {
            return 0;
        }
        $order = $ri->current();

        // サイトの与信判定基準ID
        $siteId = $order['SiteId'];
        if (isset($siteId)) {
            $mdlSite = new TableSite($this->getAdapter());
            $ri = $mdlSite->findSite($siteId);
            // 取得できない場合、次の判定へ
            if ($ri->count() > 0) {
                $site = $ri->current();

                // 自動化期間の場合、自動化用の項目も判定
                if ($autoFlg && isset($site['AutoCreditCriterion']) && $site['AutoCreditCriterion'] > 0) {
                    // 取得できた場合終了
                    $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Site AutoCreditCriterionId = %s', $oseq, $site['AutoCreditCriterion']));
                    return $site['AutoCreditCriterion'];
                }

                if (isset($site['CreditCriterion']) && $site['CreditCriterion'] > 0) {
                    // 取得できた場合終了
                    $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Site CreditCriterionId = %s', $oseq, $site['CreditCriterion']));
                    return $site['CreditCriterion'];
                }
            }
        }

        // OEMの与信判定基準ID
        $oemId = $order['OemId'];
        if (isset($oemId)) {
            $mdlOem = new TableOem($this->getAdapter());
            $ri = $mdlOem->find($oemId);
            // 取得できない場合、次の判定へ
            if ($ri->count() > 0) {
                $oem = $ri->current();

                // 自動化期間の場合、自動化用の項目も判定
                if ($autoFlg && isset($oem['AutoCreditCriterion']) && $oem['AutoCreditCriterion'] > 0) {
                    // 取得できた場合終了
                    $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Oem AutoCreditCriterionId = %s', $oseq, $oem['AutoCreditCriterion']));
                    return $oem['AutoCreditCriterion'];
                }

                if (isset($oem['CreditCriterion']) && $oem['CreditCriterion'] > 0) {
                    // 取得できた場合終了
                    $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Oem CreditCriterionId = %s', $oseq, $oem['CreditCriterion']));
                    return $oem['CreditCriterion'];
                }
            }
        }

        // システムプロパティの与信判定基準ID
        $mdlSysP = new TableSystemProperty($this->getAdapter());
        // 自動化期間の場合、自動化用の項目も判定
        $sysP = $mdlSysP->getValue('[DEFAULT]', 'systeminfo', 'AutoCreditCriterion');
        if ($autoFlg && !is_null($sysP) && strlen($sysP) > 0 && $sysP >= 0) {
            // 取得できた場合終了
            $this->debug(sprintf('[%s] getCreditCriterionIdByOrder System AutoCreditCriterionId = %s', $oseq, $sysP));
            return $sysP;
        }
        $sysP = $mdlSysP->getValue('[DEFAULT]', 'systeminfo', 'CreditCriterion');
        if (!is_null($sysP) && strlen($sysP) > 0 && $sysP >= 0) {
            // 取得できた場合終了
            $this->debug(sprintf('[%s] getCreditCriterionIdByOrder System CreditCriterionId = %s', $oseq, $sysP));
            return $sysP;
        }

        // 見つからなかった場合、0
        $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Not Found CreditCriterionId = %s', $oseq, 0));
        return 0;
    }

    /**
     * スコアリング結果保存
     *
     * @param int $oseq 注文Seq
     * @param array $coreScore 与信基本スコアリング結果
     * @param array $extraScore 与信追加スコアリング結果
     */
    protected function saveScoreResult($oseq, $coreScore, $extraScore) {
        $start = microtime(true);

        // トランザクション開始
        $this->_adapter->getDriver()->getConnection()->beginTransaction();
        try {

            // 購入者
            $mdlC = new TableCustomer($this->getAdapter());
            $udata = $this->_initCustUpdate;
            $customerId = 0;
            // 与信審査(追加)
            if (!is_null($extraScore)) {
                foreach($extraScore['Customer_ClassDetail'] as $key => $value) {
                    $udata[$key] = $value;
                }
                $customerId = $extraScore['Customer_CustomerId'];
            }
            // 与信審査(基本)
            if (!is_null($coreScore)) {
                foreach($coreScore['Customer_ScoreDetail'] as $key => $value) {
                    $udata[$key] = $value;
                }
                $udata['Incre_ScoreTotal'] = $coreScore['Customer_ScoreTotal'];
                $customerId = $coreScore['Customer_CustomerId'];
            }
            // ユーザーID
            $udata['UpdateId'] = self::getUserId();
            $mdlC->saveUpdate($udata, $customerId);

            // 注文商品
            $mdlOi = new TableOrderItems($this->getAdapter());
            // 与信審査(基本)
            if (!is_null($coreScore)) {
                foreach($coreScore['OrderItems_ScoreDetail'] as $id => $row) {
                    $udata = $this->_initOrderItemsUpdate;
                    $orderItemId = $id;
                    foreach($row as $key => $value) {
                        $udata[$key] = $value;
                    }
                    // ユーザーID
                    $udata['UpdateId'] = self::getUserId();
                    $mdlOi->saveUpdate($udata, $orderItemId);
                }
            } else {
                // 与信審査(基本)がない場合
                $viewD = new ViewDelivery($this->getAdapter());
                $vd = ResultInterfaceToArray($viewD->findByOrderSeq($oseq));
                foreach($vd as $row) {
                    $udata = $this->_initOrderItemsUpdate;
                    $orderItemId = $row['OrderItemId'];
                    // ユーザーID
                    $udata['UpdateId'] = self::getUserId();
                    $mdlOi->saveUpdate($udata, $orderItemId);
                }
            }

            // 配送先
            // 与信審査(追加)は必ずある想定
            $mdlDd = new TableDeliveryDestination($this->getAdapter());
            // 与信審査(追加)
            if (!is_null($extraScore)) {
                foreach($extraScore['DeliveryDestination_ClassDetail'] as $id => $row) {
                    $udata = $this->_initDeliveryDestinationUpdate;
                    $deliDestId = $id;
                    foreach($row as $key => $value) {
                        $udata[$key] = $value;
                    }

                    // 与信審査(基本)
                    if (!is_null($coreScore)) {
                        foreach($coreScore['DeliveryDestination_ScoreDetail'][$deliDestId] as $key => $value) {
                            $udata[$key] = $value;
                        }
                        $udata['Incre_ScoreTotal'] = $coreScore['DeliveryDestination_ScoreTotal'];
                    }
                    // ユーザーID
                    $udata['UpdateId'] = self::getUserId();
                    $mdlDd->saveUpdate($udata, $deliDestId);
                }
            }

            // 注文
            $mdlO = new TableOrder($this->getAdapter());
            $udata = $this->_initOrderUpdate;
            $udata['CreditConditionMatchData'] = json_encode(array());
            $Incre_JudgeScoreTotal = 0;
            $Incre_CoreScoreTotal = 0;
            // 与信審査(追加)
            if (!is_null($extraScore)) {
                $Incre_JudgeScoreTotal = $extraScore['Incre_JudgeScoreTotal'];
                $udata['Incre_JudgeScoreTotal'] = $Incre_JudgeScoreTotal;

                foreach($extraScore['JudgeScore_Detail'] as $key => $value) {
                    $udata[$key] = $value;
                }
            }
            // 与信審査(基本)
            if (!is_null($coreScore)) {
                foreach($coreScore['Order_ScoreDetail'] as $key => $value) {
                    $udata[$key] = $value;
                }
                $udata['CreditConditionMatchData'] = $coreScore['CreditConditionMatchData'];
                $udata['Incre_CoreScoreTotal'] = $coreScore['TotalScore'];
                $udata['Incre_ItemScoreTotal'] = $coreScore['OrderItems_ScoreTotal'];

                $Incre_CoreScoreTotal = $coreScore['TotalScore'];
            }

            $udata['Incre_ScoreTotal'] = $Incre_JudgeScoreTotal + $Incre_CoreScoreTotal;

            // ユーザーID
            $udata['UpdateId'] = self::getUserId();
            $mdlO->saveUpdate($udata, $oseq);

            // 社内与信条件 リピート
            if (!is_null($extraScore) && isset($extraScore['CreditCondition_Insert'])) {
                $mdlcc = new TableCreditCondition($this->getAdapter());
                foreach($extraScore['CreditCondition_Insert'] as $data) {
                    // 正規化を適用
                    // 対象はRegCstringのみ
                    $data = $mdlcc->fixDataArrayOrg($data, false);
                    $data['RegistId'] = self::getUserId();
                    $data['UpdateId'] = self::getUserId();
                    //登録
                    $savedRow = $mdlcc->saveFromArray($data);
                }
            }

            // トランザクションコミット
            $this->_adapter->getDriver()->getConnection()->commit();

        } catch(\Exception $err) {
            $this->info(sprintf('[%s] saveScoreResult[ERROR] -> %s, elapsed time = %s', $oseq, $err->getMessage(), (microtime(true) - $start)));
            $this->_adapter->getDriver()->getConnection()->rollBack();
            // 例外はそのまま上位へ
            throw $err;
        }
    }

    /**
     * 与信結果ログの初期配列を作成する
     * @param int $oseq 注文Seq
     * @return array:
     */
    protected function initCreditLog($oseq) {
        $mdlCrl = new TableCreditLog($this->_adapter);
        $mdlOrd = new TableOrder($this->_adapter);
        $mdlOem = new TableOem($this->_adapter);
        $mdlEnt = new TableEnterprise($this->_adapter);
        $mdlSit = new TableSite($this->_adapter);
        $mdlSysp = new TableSystemProperty($this->_adapter);

        // -------------------------------------------------------
        // 初期化
        $oemId = 0;
        $entId = 0;
        $siteId = 0;

        // -------------------------------------------------------
        // 行配列を作成
        $data = array();
        $data = $mdlCrl->newRow($data);

        // -------------------------------------------------------
        // 開始時間を設定
        $data['StartTime'] = date('Y-m-d H:i:s');

        // -------------------------------------------------------
        // 注文情報を設定
        $row = $mdlOrd->find($oseq)->current();

        $data['OrderSeq'] = $oseq;
        $data['OrderId'] = $row['OrderId'];
        $oemId = intval($row['OemId']);
        $entId = intval($row['EnterpriseId']);
        $siteId = intval($row['SiteId']);

        // -------------------------------------------------------
        // マスタ情報を設定

        // [OEM情報]
        if ($oemId > 0) {
            $row = $mdlOem->find($oemId)->current();
            $data['Oem_CreditCriterion'] = $row['CreditCriterion'];
            $data['Oem_AutoCreditDateFrom'] = $row['AutoCreditDateFrom'];
            $data['Oem_AutoCreditDateTo'] = $row['AutoCreditDateTo'];
        }

        // [事業者情報]
        $row = $mdlEnt->find($entId)->current();
        $data['Ent_JintecFlg'] = $row['JintecFlg'];
        $data['Ent_AutoJudgeFlg'] = $row['AutoJudgeFlg'];
        $data['Ent_ManualJudgeFlg'] = $row['ManualJudgeFlg'];
        $data['Ent_CreditThreadNo'] = $row['CreditThreadNo'];
        $data['Ent_UseAmountLimitForCreditJudge'] = $row['UseAmountLimitForCreditJudge'];
        $data['Ent_AutoCreditJudgeMode'] = $row['AutoCreditJudgeMode'];
        $data['Ent_JudgeSystemFlg'] = $row['JudgeSystemFlg'];
        $data['Ent_CreditJudgePendingRequest'] = $row['CreditJudgePendingRequest'];

        // [サイト情報]
        $row = $mdlSit->findSite($siteId)->current();
        $data['Sit_T_OrderClass'] = $row['T_OrderClass'];
        $data['Sit_AutoCreditLimitAmount'] = $row['AutoCreditLimitAmount'];
        $data['Sit_CreditOrderUseAmount'] = $row['CreditOrderUseAmount'];
        $data['Sit_AverageUnitPriceRate'] = $row['AverageUnitPriceRate'];
        $data['Sit_AutoCreditDateFrom'] = $row['AutoCreditDateFrom'];
        $data['Sit_AutoCreditDateTo'] = $row['AutoCreditDateTo'];
        $data['Sit_AutoCreditCriterion'] = $row['CreditCriterion'];
        $data['Sit_CreditJudgeMethod'] = $row['CreditJudgeMethod'];
        $data['Sit_MultiOrderScore'] = $row['MultiOrderScore'];
        $data['Sit_MultiOrderCount'] = $row['MultiOrderCount'];
        $data['Sit_SitClass'] = $row['SitClass'];

        // [社内与信条件]
        $sql = ' SELECT * FROM M_CreditPoint WHERE CreditCriterionId = 0 ';
        $ri = $this->_adapter->query($sql)->execute();
        foreach ($ri as $row) {
            switch (intval($row['CpId'])) {
                case 1:     $data['Def_CpId1_Point']    = $row['Point']; break;
                case 2:     $data['Def_CpId2_Point']    = $row['Point']; break;
                case 105:   $data['Def_CpId105_Point']  = $row['Point']; break;
                case 106:   $data['Def_CpId106_Point']  = $row['Point']; break;
                case 107:   $data['Def_CpId107_Point']  = $row['Point']; break;
                case 108:   $data['Def_CpId108_Point']  = $row['Point']; break;
                case 109:   $data['Def_CpId109_Point']  = $row['Point']; break;
                case 201:   $data['Def_CpId201_Point']  = $row['Point']; break;
                case 202:   $data['Def_CpId202_Point']  = $row['Point']; break;
                case 203:   $data['Def_CpId203_Point']  = $row['Point']; break;
                case 206:   $data['Def_CpId206_Point']  = $row['Point']; break;
                case 301:   $data['Def_CpId301_Point']  = $row['Point']; $data['Def_CpId301_GeneralProp']  = $row['GeneralProp']; break;
                case 302:   $data['Def_CpId302_Point']  = $row['Point']; $data['Def_CpId302_GeneralProp']  = $row['GeneralProp']; break;
                case 303:   $data['Def_CpId303_Point']  = $row['Point']; $data['Def_CpId303_GeneralProp']  = $row['GeneralProp']; break;
                case 304:   $data['Def_CpId304_Point']  = $row['Point']; $data['Def_CpId304_GeneralProp']  = $row['GeneralProp']; break;
                case 401:   $data['Def_CpId401_Rate']   = $row['Rate'];  break;
                case 402:   $data['Def_CpId402_Rate']   = $row['Rate'];  break;
                case 403:   $data['Def_CpId403_Rate']   = $row['Rate'];  break;
                case 501:   $data['Def_CpId501_Description']   = $row['Description'];  break;
                default:    break;
            }
        }

        // [社内閾値]
        $sql = ' SELECT * FROM T_CreditJudgeThreshold WHERE CreditCriterionId = 0 ';
        $row = $this->_adapter->query($sql)->execute()->current();
        $data['Def_CoreSystemHoldMIN'] = $row['CoreSystemHoldMIN'];
        $data['Def_CoreSystemHoldMAX'] = $row['CoreSystemHoldMAX'];
        $data['Def_JudgeSystemHoldMIN'] = $row['JudgeSystemHoldMIN'];
        $data['Def_JudgeSystemHoldMAX'] = $row['JudgeSystemHoldMAX'];

        // [システム条件]
        $data['Sys_enterpriseid'] = $mdlSysp->getValue('cbadmin', 'credit_judge', 'enterpriseid');
        $data['Sys_AutoCreditDateFrom'] = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'AutoCreditDateFrom');
        $data['Sys_AutoCreditDateTo'] = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'AutoCreditDateTo');
        $data['Sys_AutoCreditLimitAmount'] = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'AutoCreditLimitAmount');
        $data['Sys_BtoBCreditLimitAmount'] = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'BtoBCreditLimitAmount');
        $data['Sys_CreditCriterion'] = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CreditCriterion');
        $data['Sys_CreditOrderUseAmount'] = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CreditOrderUseAmount');
        $data['Sys_MultiOrderDays'] = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'MultiOrderDays');
        $data['Sys_default_average_unit_price_rate'] = $mdlSysp->getValue('cbadmin', 'cj_api', 'default_average_unit_price_rate');

        return $data;
    }

    /**
     * ジンテック判定結果を保存する
     * @param int $result
     * @param int $jtcSeq
     */
    protected function saveJintecResult($result, $jtcSeq) {
        $mdljtc = new TableJtcResult($this->_adapter);
        $jtcResult = 0;
        switch ($result) {
            case LogicCreditJudgeAbstract::JUDGE_RESULT_OK:
                $jtcResult = 1;
                break;
            case LogicCreditJudgeAbstract::JUDGE_RESULT_NG:
                $jtcResult = 2;
                break;
            case LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING:
                $jtcResult = 0;
                break;
            default:
                // OK/NG/保留以外は戻る
                return;
        }
        $mdljtc->saveUpdate(array('Result' => $jtcResult), $jtcSeq);
    }

    /**
     * 手動与信強制化判定
     * @param int $oseq
     * @param int $result
     * @param int $jtcSeq
     * @return int
     */
    protected function judgeJintecManual($oseq, $result, $jtcSeq) {

        $mdlo = new TableOrder($this->_adapter);

        // 過去２年間の注文SEQを取得
        $pastOrders = $this->getPastOrderSeqs($oseq);

        // ジンテック手動与信強制判定
        $booManual = $this->isJintecManualJudge($oseq, $pastOrders);

        if ($booManual) {
            // 与信結果は保留とする
            $result = LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING;

            $GLOBALS['CreditLog']['JintecManualJudgeFlg'] = 1;
        }

        return $result;
    }

    /**
     * 指定注文SEQの過去取引を示す注文SEQリストを初期化する。
     * このメソッドで初期化された注文SEQのリストは内部でキャッシュされ、
     * 再びこのメソッドが呼び出されるまで存続する
     *
     * @access protected
     * @param int $oseq 注文SEQ。初期化された注文SEQリストからは除外される
     * @return array 初期化された注文SEQのリスト
     */
    protected function getPastOrderSeqs($oseq) {

        $mdlo = new TableOrderNotClose($this->_adapter);
        $row_c = $this->_adapter->query(" SELECT RegUnitingAddress, RegPhone FROM T_Customer WHERE OrderSeq = :OrderSeq "
        )->execute(array(':OrderSeq' => $oseq))->current();

        $row_o = $this->_adapter->query(" SELECT SiteId FROM T_Order WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current();

        $seqs = array();
        foreach($mdlo->getPastNotCloseOrderSeqs(nvl($row_c['RegUnitingAddress'],''), nvl($row_c['RegPhone'],''), $row_o['SiteId']) as $row) {
            if($row['OrderSeq'] != $oseq) {
                $seqs[] = (int)$row['OrderSeq'];
            } else {
            }
        }
        return $seqs;
    }

    /**
     * 手動与信にまわすべきか否か判断する
     * @param unknown $oseq
     * @param unknown $pastOrders
     * @return boolean
     */
    protected function isJintecManualJudge($oseq, $pastOrders){
        $mdlo = new TableOrder($this->_adapter);
        $mdlvo = new ViewOrderCustomer($this->_adapter);
        $mdldeli = new ViewDelivery($this->_adapter);
        $mdlmc = new TableManagementCustomer($this->_adapter);
        $mdlcjt = new \models\Table\TableCreditJudgeThreshold($this->_adapter);

        $orderCustomer = $mdlvo->find($oseq)->current();
        $rowcjt = $mdlcjt->getByCriterionid($this->_creditCriterionId)->current();
        $sql = ' SELECT MailAddress FROM T_Customer WHERE OrderSeq = :OrderSeq ';
        $cust = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

        $oseqs = implode(',', $pastOrders);
        if (strlen($oseqs) > 0) {
            if ($rowcjt['JintecManualJudgeUnpaidFlg'] == 1) {
                // ------------------------------------------------------------------------
                // 未払い件数取得
                $cntUnpaid = $mdlo->findOrderCustomerByUnpaidCnt($oseqs);
                if ($cntUnpaid > 0) return true;
            }

            if ($rowcjt['JintecManualJudgeNonPaymentFlg'] == 1) {
                // ------------------------------------------------------------------------
                // 不払い件数取得
                $cntNonPayment = $mdlo->findOrderCustomerByNonPaymentCnt($oseqs);
                if ($cntNonPayment > 0)  return true;
            }
        }


        // ------------------------------------------------------------------------
        // 特定文字列が存在するか否か
        //顧客情報取得
        $mc = $mdlmc->findByOrderSeq($oseq)->current();
        $this->eid = $orderCustomer['EnterpriseId'];

        $order = $mdlo->find($oseq)->current();
        $this->ConditionSeqs = array();

        // 請求先氏名
        $res = $this->doneJudgeByMatches(true, 2, $mc['NameKj'], $oseq);
        if ($res) return true;

        // 請求先住所
        $res = $this->doneJudgeByMatches(true, 1, $mc['UnitingAddress'], $oseq);
        if ($res) return true;

        // メールアドレス
        if (isset($cust['MailAddress'])) {
            $res = $this->doneJudgeByMatches(true, 4, $cust['MailAddress'], $oseq);
            if ($res) return true;
        }

        // 請求先電話番号
        $res = $this->doneJudgeByMatches(true, 8, $mc['Phone'], $oseq);
        if ($res) return true;

        // 金額
        $res = $this->doneJudgeByMatches(true, 9, $order['UseAmount'], $oseq);
        if ($res) return true;
        // 商品
        $itemDelis = $mdldeli->findByOrderSeq($oseq);
        foreach($itemDelis as $itemDeli) {
            // 商品名に対するスコアを算出
            $res = $this->doneJudgeByMatches(true, 3, $itemDeli['ItemNameKj'], $oseq);
            if ($res) return true;

            if($orderCustomer['AnotherDeliFlg'] == 1) {	// 別配送先指定時のみ
                // 配送先氏名
                $res = $this->doneJudgeByMatches(true, 2, $itemDeli['DestNameKj'], $oseq);
                if ($res) return true;

                // 配送先住所
                $res = $this->doneJudgeByMatches(true, 1, $itemDeli['UnitingAddress'], $oseq);
                if ($res) return true;

                // 配送先電話番号
                $res = $this->doneJudgeByMatches(true, 8, $itemDeli['Phone'], $oseq);
                if ($res) return true;
            }
        }

        // 加盟店ログインID
        $res = $this->doneJudgeByMatches(false, 5, $orderCustomer['EnterpriseLoginId'], $oseq);
        if ($res) return true;

        // ------------------------------------------------------------------------
        // 審査システム回答条件判定
        $mdlcjr = new TableCjResult($this->_adapter);

        $cjResult = $mdlcjr->findByOrderSeq($oseq)->current(); // 審査システム結果

        if (strlen($rowcjt['JintecManualJudgeSns']) > 0) {
            // ジンテック手動与信強制条件設定が存在する場合

            // 審査システム詳細から該当データが存在するか確認
            $sql  = ' SELECT COUNT(1) as cnt ';
            $sql .= ' FROM T_CjResult_Detail ';
            $sql .= ' WHERE CjrSeq = :CjrSeq ';
            $sql .= ' AND DetectionPatternNo IN (' . $rowcjt['JintecManualJudgeSns'] . ') ';
            $prm = array(
                    ':CjrSeq' => $cjResult['Seq'],
            );
            $count = $this->_adapter->query($sql)->execute($prm)->current()['cnt'];

            if ($count > 0) {
                return true;
            }
        }

        return false;

    }

    /**
     * 与信対象項目の正規化設定
     * @access protected
     * @var array
     */
    protected $map = array(
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

    /**
     * 事業者ID
     * @var int
     */
    protected $eid;

    /**
     * 部分一致あるいは完全一致による与信
     *
     * @param boolean $isMatchesIn true:部分一致 false:完全一致
     * @param int $category カテゴリ
     * @param string $target 与信対象
	 * @param int $oseq 注文Seq
     * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
     */
    protected function doneJudgeByMatches($isMatchesIn, $category, $target, $oseq) {

        $mdlvd = new ViewDelivery($this->_adapter);
        $mdlacc = new TableAddCreditCondition($this->_adapter);
        $mdlos = new TableOrderSummary($this->_adapter);
        $mdloc = new ViewOrderCustomer($this->_adapter);
        $mdlcca = new TableCreditConditionAddress($this->_adapter);
        $mdlccn = new TableCreditConditionName($this->_adapter);
        $mdlcci = new TableCreditConditionItem($this->_adapter);
        $mdlccd = new TableCreditConditionDomain($this->_adapter);
        $mdlcce = new TableCreditConditionEnterprise($this->_adapter);
        $mdlccp = new TableCreditConditionPhone($this->_adapter);
        $mdlccm = new TableCreditConditionMoney($this->_adapter);

        // 対象の正規化
        $regtarget = LogicNormalizer::create($this->map[$category])->normalize($target);

        if (!empty($this->ConditionSeqs)) {
            $ConditionSeq = implode(',', $this->ConditionSeqs);
        } else {
            $ConditionSeq = null;
        }
        switch($category){
            case 1:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlcca->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 2:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccn->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 3:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlcci->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 4:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccd->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 5:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlcce->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 8:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccp->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 9:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccm->judge($category, $regtarget, $ConditionSeq);
                break;
            default:
                break;
        }

        foreach($matchDatas as $matchData) {
            $sPattern = $matchData['SearchPattern'];
            $regcString = $matchData['RegCstring'];
            $P_cstring = $matchData['Cstring'];

            // 検索方法チェック
            if($matchData['Category'] != 9 ){
                // 検索方法チェック
                if (!$isMatchesIn || $sPattern == 0 ||
                ($sPattern == 1 && strpos($regtarget, $regcString) === 0) ||
                ($sPattern == 2 && strpos(strrev($regtarget), strrev($regcString)) === 0) ||
                ($sPattern == 3 && $regtarget == $regcString)) {
                } else {
                    continue;
                }
            }else{
                //金額だけは値で比較する
                if (($sPattern == 0 && $regtarget == $regcString) ||
                ($sPattern == 1 && $regtarget <= $regcString) ||
                ($sPattern == 2 && $regtarget >= $regcString) ||
                ($sPattern == 3 && $regtarget == $regcString)){
                } else {
                    continue;
                }
            }
            //

            if ($matchData['AddConditionCount'] > 0) {

                $itemDeli = ResultInterfaceToArray($mdlvd->findByOrderSeq($oseq));
                $OrderSum = $mdlos->findByOrderSeq($oseq)->current();
                $OrderCust = $mdloc->find($oseq)->current();
                $truecnt = 0;

                $datas = $mdlacc->findAddConditionValid($matchData['Seq'],$category);

                foreach($datas as $data) {
                    $cstring = $data['Cstring'];
                    $pattern = $data['SearchPattern'];
                    $regCstring = $data['RegCstring'];

                    switch ($data['Category']) {
                        case 1: // 住所
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, $OrderSum['RegUnitingAddress'], $OrderSum['RegDestUnitingAddress']);
                            break;
                        case 2: // 氏名
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, $OrderSum['RegNameKj'], $OrderSum['RegDestNameKj']);
                            break;
                        case 3: // 商品名
                            foreach ($itemDeli as $item) {
                                $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ITEM_NAME)->normalize($item['ItemNameKj']), null);
                                if ($addjudge) {
                                    break;
                                }
                            }
                            break;
                        case 4: // ドメイン
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, LogicNormalizer::create(LogicNormalizer::FILTER_FOR_MAIL)->normalize($OrderSum['MailAddress']), null);
                            break;
                        case 5: // 加盟店ID
                            $addjudge = ($regCstring == LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ID)->normalize($OrderCust['EnterpriseLoginId'])) ? true : false;
                            break;
                        case 8: // 電話番号
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, $OrderSum['RegPhone'], $OrderSum['RegDestPhone']);
                            break;
                        case 9: // 金額
                            $addjudge = $this->doneJudgeByAddMatchesInt($pattern, $regCstring, $OrderCust['UseAmount']);
                            break;
                    }
                    if ($addjudge) {
                        $truecnt += 1;
                    }
                }

                if ($truecnt != $matchData['AddConditionCount']) {
                    continue;
                }

            }

            $this->ConditionSeqs[] = $matchData['Seq'];

            // 加盟店独自の設定確認
            $cceid = nvl($matchData['EnterpriseId'], -1);
            if ($cceid <> -1 && $cceid <> $this->eid) {
                continue;
            }

            if ($matchData['JintecManualReqFlg'] == 1) {
                // 強制手動与信と判断
                return true;
            }
        }

        return false;
    }

    /**
     * 保留ボックス判定
     */

    /**
     * 保留ボックス判定
     * @param int $oseq 注文SEQ
     * @param int $result 与信結果
     * @return boolean 保留ボックス判定されたか否か
     */
    protected function judgeHoldBox($oseq, $result) {

        $mdlat = new ATableOrder($this->_adapter);
        $mdlsysp = new TableSystemProperty($this->_adapter);
        $mdlbc = new TableBusinessCalendar($this->_adapter);

        // 処理対象は与信バッチ。API与信は処理対象外。
        if ($this->_running_mode != self::RUNNING_MODE_MULTI) {
            $this->debug(sprintf('not send to hold box(api mode). oseq = %s', $oseq));
            return false;
        }

        // 与信保留/与信NG以外の場合は処理対象外
        if ( !(($result == LogicCreditJudgeAbstract::JUDGE_RESULT_NG) || ($result == LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING)) ) {
            $this->debug(sprintf('not send to hold box(result ok). oseq = %s', $oseq));
            return false;
        }

        // 加盟店マスタの設定がOFFの場合は処理対象外
        $sql = " SELECT e.HoldBoxFlg FROM T_Order o INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId) WHERE o.OrderSeq = :OrderSeq ";
        $holdBoxFlg = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['HoldBoxFlg'];
        if (!$holdBoxFlg) {
            $this->debug(sprintf('not send to hold box(enterprise off). oseq = %s', $oseq));
            return false;
        }

        // 審査結果未取得の場合は処理対象外
        $cjrSeq = $GLOBALS['CreditLog']['CjrSeq'];
        if (!isset($cjrSeq)) {
            $this->debug(sprintf('not send to hold box(cjrseq is null). oseq = %s', $oseq));
            return false;
        }

        // GROUP_CONCATで、T_CjResult_Detailのリストを取得
        $sql = " SELECT GROUP_CONCAT(DetectionPatternNo) AS DetectionPatternNo FROM T_CjResult_Detail WHERE CjrSeq = :CjrSeq ";
        $detectionPatternNo = $this->_adapter->query($sql)->execute(array(':CjrSeq' => $cjrSeq))->current()['DetectionPatternNo'];
        if (strlen(trim($detectionPatternNo)) <= 0) {
            // 入力不備の元となるデータが存在しないため、falseを返却
            $this->debug(sprintf('not send to hold box(cjresult_detail not found). oseq = %s', $oseq));
            return false;
        }

        // M_Code.KeyCodeで引っ掛けて対象があるか確認
        $sql = <<<EOQ
SELECT GROUP_CONCAT(DISTINCT Note SEPARATOR '\n') AS Note, COUNT(1) AS Cnt
FROM M_Code
WHERE CodeId = 189
AND Class1 = '1'
AND ValidFlg = 1
AND KeyCode IN ($detectionPatternNo);
EOQ;
        $row = $this->_adapter->query($sql)->execute(null)->current();
        if (intval($row['Cnt']) <= 0) {
            // 入力不備が存在しないため、falseを返却
            $this->debug(sprintf('not send to hold box(valid config not found). oseq = %s', $oseq));
            return false;
        }

        $atorder = $mdlat->find($oseq)->current();
        if ($atorder['DefectFlg'] == 0 && $atorder['ResumeFlg'] == 1) {
            return false;
        }

        // キャンセル予定日を取得
        $defectCancelPlanDays = intval($mdlsysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'DefectCancelPlanDays'));
        if ($defectCancelPlanDays < 0) {
            $defectCancelPlanDays = 0; // マイナスの設定はありえないが、念のため
        }

        // 2営業日後を取得
        $defectCancelPlanDate = $mdlbc->getNextBusinessDateNonIncludeByDays(date('Y-m-d'), $defectCancelPlanDays);

        // AT_Order更新
        $data = array(
            'DefectFlg' => 1,
            'DefectInvisibleFlg' => 0,
            'DefectNote' => $row['Note'],
            'DefectCancelPlanDate' => ($defectCancelPlanDate . ' 23:59:59'),
        );
        $mdlat->saveUpdate($data, $oseq);

        // 入力不備ありのため、trueを返却
        return true;

    }

    /**
     * 追加与信条件の判定
     *
     * @param int $pattern 検索方法（0:部分一致、1:前方一致、2:後方一致、3:完全一致）
     * @param int $cstring 追加与信条件 条件文字列
     * @param int $string1 比較文字列１
     * @param int $string2 比較文字列２
     * @return true/false 判定結果
     */
    public function doneJudgeByAddMatches($pattern, $cstring, $string1, $string2) {

        if ($string2 == null) {
            if ($pattern == 0 && strpos($string1, $cstring) !== false) {
                return true;
            }

            if ($pattern == 1 && strpos($string1, $cstring) === 0) {
                return true;
            }

            if ($pattern == 2 && strpos(strrev($string1), strrev($cstring)) === 0) {
                return true;
            }

            if ($pattern == 3 && $string1 == $cstring) {
                return true;
            }
        } else {
            if ($pattern == 0 && (strpos($string1, $cstring) !== false || strpos($string2, $cstring) !== false)) {
                return true;
            }

            if ($pattern == 1 && (strpos($string1, $cstring) === 0 || strpos($string2, $cstring) === 0)) {
                return true;
            }

            if ($pattern == 2 && (strpos(strrev($string1), strrev($cstring)) === 0 || strpos(strrev($string2), strrev($cstring)) === 0)) {
                return true;
            }

            if ($pattern == 3 && ($string1 == $cstring || $string2 == $cstring)) {
                return true;
            }
        }

        return false;
    }

     /**
     * 追加与信条件の判定(金額用)
     *
     * @param int $pattern 検索方法（0:部分一致、1:前方一致、2:後方一致、3:完全一致）
     * @param int $cstring 追加与信条件 条件文字列
     * @param int $int 比較用の数値
     * @return true/false 判定結果
     */
    public function doneJudgeByAddMatchesInt($pattern, $cstring, $int) {

        if ($pattern == 0 && $int == $cstring) {
            return true;
        }

        if ($pattern == 1 && $int <= $cstring) {
            return true;
        }

        if ($pattern == 2 && $int >= $cstring) {
            return true;
        }

        if ($pattern == 3 && $int == $cstring) {
            return true;
        }

        return false;
    }
}
