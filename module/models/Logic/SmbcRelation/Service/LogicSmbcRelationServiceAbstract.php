<?php
namespace models\Logic\SmbcRelation\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Validate\CoralValidateUtility;
use models\Table\TableSmbcRelationLog;
use models\Logic\LogicSmbcRelation;
use models\Logic\SmbcRelation\Adapter\LogicSmbcRelationAdapterAbstract;
use models\Logic\SmbcRelation\LogicSmbcRelationAdapter;
use models\Logic\SmbcRelation\LogicSmbcRelationException;
use models\Table\TableClaimHistory;
use models\Table\TableSmbcRelationControl;

/**
 * SMBC決済ステーション連携の抽象サービスクラス。
 * 具体的なサービス（請求情報送信、請求取消等）はこのクラスから派生させた
 * サービスクラスで実装する。
 *
 * 派生クラスは最低限以下の抽象メソッドを実装する必要がある
 * - public function getTargetFunctionCode() : int - Table_SmbcRelationLogで定義される機能識別コードを返す
 */
abstract class LogicSmbcRelationServiceAbstract {
    /** オプションキー定数：決済ステーション接続に使用するテキストエンコードを指定するキー @var string */
    const OPT_SERVICE_TEXT_ENC = 'service_text_enc';

    /** オプションキー定数：決済ステーションのベースURLを指定するキー @var string */
    const OPT_SERVICE_BASE_URL = 'service_base_url';

    /** オプションキー定数：決済ステーションのサービスインターフェイスエンドポイントパスを指定するキー @var string */
    const OPT_SERVICE_INTERFACE_PATH = 'service_interface_path';

    /** オプションキー定数：決済ステーションHTTPリクエスト時のタイムアウトを指定するキー @var string */
    const OPT_SERVICE_REQ_TIMEOUT = 'service_req_timeout';

    /** オプションキー定数：決済ステーションHTTPリクエストのリトライ回数を指定するキー @var string */
    const OPT_SERVICE_REQ_RETRY = 'service_req_retry';

    /** オプションキー定数：決済ステーションへ接続するためのアダプタ名を指定するキー @var string */
    const OPT_SERVICE_ADAPTER = 'service_adapter';

    /** オプションデフォルト定数：決済ステーション接続に使用するテキストエンコード @var string */
    const DEFAULT_SERVICE_TEXT_ENC = 'sjis-win';

    /** オプションデフォルト定数：決済ステーションのベースURL @var string */
    const DEFAULT_SERVICE_BASE_URL = 'https://www.paymentstation.jp/cooperation/';
    // テストサーバの場合は'https://www.paymentstation.jp/cooperationtest'

    /** オプションデフォルト定数：決済ステーションのサービスインターフェイスエンドポイントパス @var string */
    const DEFAULT_SERVICE_INTERFACE_PATH = '';

    /** オプションデフォルト定数：決済ステーションHTTPリクエスト時のタイムアウト（秒） @var int */
    const DEFAULT_SERVICE_REQ_TIMEOUT = 10;

    /** オプションデフォルト定数：決済ステーションHTTPリクエストのリトライ回数 @var int */
    const DEFAULT_SERVICE_REQ_RETRY = 2;

    /** オプションデフォルト定数：決済ステーションへ接続するためのアダプタ名 @var string */
    const DEFAULT_SERVICE_ADAPTER = 'Http';

    /** フィールドフィルタ定数：半角 → 全角フィルタ @var string */
    const FILTER_NARROW_TO_WIDE = 'narrow to wide';

    /** フィールドフィルタ定数：全角 → 半角フィルタ @var string */
    const FILTER_WIDE_TO_NARROW = 'wide to narrow';

    /** フィールドフィルタ定数：郵便番号整形フィルタ @var string */
    const FILTER_POSTAL_CODE = 'postal code';

    /** フィールドフィルタ定数：特殊カナフィルタ（請求内容カナ用） @var string */
    const FILTER_KANA_SPECIAL = 'kana special';

    /**
     * サービスのデフォルトオプション構成
     *
     * @static
     * @access protected
     * @var array
     */
    protected static $__default_options;

    /**
     * サービス全般のデフォルトオプションを取得する
     *
     * @static
     * @return array
     */
    public static function getDefaultOptions() {
        if(!self::$__default_options) {
            // 各種オプションの初期値を構築
            self::$__default_options = array(
                    self::OPT_SERVICE_TEXT_ENC          => self::DEFAULT_SERVICE_TEXT_ENC,
                    self::OPT_SERVICE_BASE_URL          => self::DEFAULT_SERVICE_BASE_URL,
                    self::OPT_SERVICE_INTERFACE_PATH    => self::DEFAULT_SERVICE_INTERFACE_PATH,
                    self::OPT_SERVICE_REQ_TIMEOUT       => self::DEFAULT_SERVICE_REQ_TIMEOUT,
                    self::OPT_SERVICE_REQ_RETRY         => self::DEFAULT_SERVICE_REQ_RETRY,
                    self::OPT_SERVICE_ADAPTER           => self::DEFAULT_SERVICE_ADAPTER
            );
        }
        return self::$__default_options;
    }

    /**
     * サービスのデフォルトオプションを設定する
     *
     * @static
     * @param array $defaultOptions
     */
    public static function setDefaultOptions(array $defalutOptions) {
        self::getDefaultOptions();
        if(!is_array($defalutOptions)) $defalutOptions = array();
        foreach($defalutOptions as $key => $value) {
            switch($key) {
                case self::OPT_SERVICE_TEXT_ENC :
                case self::OPT_SERVICE_BASE_URL :
                case self::OPT_SERVICE_INTERFACE_PATH :
                case self::OPT_SERVICE_REQ_TIMEOUT :
                case self::OPT_SERVICE_REQ_RETRY :
                case self::OPT_SERVICE_ADAPTER :
                    self::$__default_options[$key] = $value;
                    break;
            }
        }
    }

    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * アダプタ構成オプション
     *
     * @access protected
     * @var array
     */
    protected $_options;

    /**
     * サービスアダプタ
     *
     * @access protected
     * @var LogicSmbcRelationAdapterAbstract
     */
    protected $_smbcRelationAdapter;

    /**
     * ロガーインスタンス
     *
     * @access protected
     * @var BaseLog
     */
    protected $_logger;

    /**
     * LogicSmbcRelationServiceAbstractの
     * 新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     * @param array $options アダプタ構成オプション
     */
    public function __construct(Adapter $adapter, array $options = array()) {
        $default_options = self::getDefaultOptions();

        // 指定オプションを初期値にマージ
        if(!is_array($options)) $options = array();
        $options = array_merge($default_options, $options);

        $this->setDbAdapter($adapter);
        $this->setOptions($options);
    }

    /**
     * 決済ステーション接続アダプタが使用するDBアダプタを取得する
     *
     * @return Adapter
     */
    public function getDbAdapter() {
        return $this->_adapter;
    }
    /**
     * 決済ステーション接続アダプタが使用するDBアダプタを設定する
     *
     * @param Adapter $adapter アダプタ
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setDbAdapter(Adapter $adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * 決済ステーション接続アダプタ構成オプションを取得する
     *
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }
    /**
     * 決済ステーション接続アダプタ構成オプションを設定する
     *
     * @param array $options アダプタ構成オプション
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setOptions(array $options = array()) {
        if(!is_array($this->_options)) $this->_options = array();
        $this->_options = array_merge($this->_options, $options);

        $this->_optionModified();

        return $this;
    }
    /**
     * 決済ステーション接続アダプタ構成オプションの内容の更新通知を受け取る。
     * 派生クラスで適切にオーバーライドすることで、アダプタ固有のオプション構成処理を実装できる。
     * この抽象アダプタではなにも処理をしない
     */
    protected function _optionModified() {
        // nop
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
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setLogger(BaseLog $logger = null) {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * SMBC決済ステーション連携で使用する送受信時のテキストエンコードを取得する
     *
     * @return string
     */
    public function getServiceTextEncoding() {
        return nvl($this->_options[self::OPT_SERVICE_TEXT_ENC],
                   self::DEFAULT_SERVICE_TEXT_ENC);
    }
    /**
     * SMBC決済ステーション連携で使用する送受信時のテキストエンコードを設定する
     *
     * @param string $enc 使用するテキストエンコード
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setServiceTextEncoding($enc = self::DEFAULT_SERVICE_REQ_TIMEOUT) {
        $this->_options[self::OPT_SERVICE_TEXT_ENC] = $enc;
        return $this;
    }

    /**
     * SMBC決済ステーションのサービスベースURLを取得する
     *
     * @return string ベースURL
     */
    public function getServiceBaseUrl() {
        return nvl($this->_options[self::OPT_SERVICE_BASE_URL],
                   self::DEFAULT_SERVICE_BASE_URL);
    }
    /**
     * SMBC決済ステーションのサービスベースURLを設定する
     *
     * @param string $baseUrl サービスのベースURL
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setServiceBaseUrl($baseUrl) {
        $this->_options[self::OPT_SERVICE_BASE_URL] = $baseUrl;
        return $this;
    }

    /**
     * SMBC決済ステーションのサービスインターフェイスエンドポイントパスを取得する
     *
     * @return string エンドポイントパス
     */
    public function getServiceInterfacePath() {
        return nvl($this->_options[self::OPT_SERVICE_INTERFACE_PATH],
                   self::DEFAULT_SERVICE_INTERFACE_PATH);
    }
    /**
     * SMBC決済ステーションのサービスインターフェイスエンドポイントパスを設定する
     *
     * @param string $path エンドポイントパス
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setServiceInterfacePath($path) {
        $this->_options[self::OPT_SERVICE_INTERFACE_PATH] = $path;
        return $this;
    }

    /**
     * SMBC決済ステーション接続のHTTPタイムアウト値を取得する
     *
     * @return int HTTP接続タイムアウト（秒）
     */
    public function getServiceRequestTimeout() {
        return (int)nvl($this->_options[self::OPT_SERVICE_REQ_TIMEOUT],
                        self::DEFAULT_SERVICE_REQ_TIMEOUT);
    }
    /**
     * SMBC決済ステーション接続のHTTPタイムアウト値を設定する
     *
     * @param int $timeOut HTTP接続タイムアウト（秒）
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setServiceRequestTimeout($timeOut) {
        $timeOut = (int)$timeOut;
        $this->_options[self::OPT_SERVICE_REQ_TIMEOUT] = $timeOut;
        return $this;
    }

    /**
     * SMBC決済ステーション接続のリトライ回数を取得する
     *
     * @return int HTTP接続失敗時のリトライ回数
     */
    public function getServiceRequestRetry() {
        return (int)nvl($this->_options[self::OPT_SERVICE_REQ_RETRY],
                        self::DEFAULT_SERVICE_REQ_RETRY);
    }
    /**
     * SMBC決済ステーション接続のリトライ回数を設定する
     *
     * @param int $retry HTTP接続失敗時のリトライ回数
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setServiceRequestRetry($retry) {
        $retry = (int)$retry;
        $this->_options[self::OPT_SERVICE_REQ_RETRY] = $retry;
        return $this;
    }

    /**
     * SMBC決済ステーションへ接続するためのサービスアダプタ名を取得する。
     * アダプタ名は実際のアダプタクラス名から名前空間'LogicSmbcRelationAdapter'を取り除いた形式となる
     *
     * @return string
     */
    public function getServiceAdapter() {
        return $this->_options[self::OPT_SERVICE_ADAPTER];
    }
    /**
     * SMBC決済ステーションへ接続するためのサービスアダプタ名を設定する
     * アダプタ名は実際のアダプタクラス名から名前空間'LogicSmbcRelationAdapter'を取り除いた形式となる
     *
     * @param string $adapterName サービスアダプタ名
     * @return LogicSmbcRelationServiceAbstract このインスタンス
     */
    public function setServiceAdapter($adapterName) {
        $this->_options[self::OPT_SERVICE_ADAPTER] = $adapterName;
        return $this;
    }

    /**
     * 対象の決済ステーション機能を指定するための識別コードを取得する
     *
     * @abstract
     * @return int 機能識別コード
     */
    abstract public function getTargetFunctionCode();

    /**
     * SMBC決済ステーションへのデータ送信処理による連携を開始する
     *
     * @final
     * @param int $claimHistorySeq 請求履歴SEQ
     * @return array 決済ステーションからの受信データ
     */
    public final function sendTo($claimHistorySeq) {

        $adapter = $this->getDbAdapter()->getDriver()->getConnection()->getConnectionParameters();
        $adapt = new Adapter($adapter);

        $mdlch = new TableClaimHistory($this->_adapter);
        $mdlsrc = new TableSmbcRelationControl($adapt);

        $srcdata = array();
        $oseq = $mdlch->find($claimHistorySeq)->current()['OrderSeq'];

        $sql = " SELECT Seq, OrderCnt FROM T_SmbcRelationControl WHERE OrderSeq = :OrderSeq ";
        $srclist = $adapt->query($sql)->execute(array(":OrderSeq" => $oseq))->current();
        $ocnt = 1;

        if (nvl($srclist['OrderCnt'], 0) > 0){
            $ocnt = $srclist['OrderCnt'] + 1;
            $srcdata = array(
                    'OrderCnt' => $ocnt,
            );
            $mdlsrc->saveUpdate($srcdata, $srclist['Seq']);
        } else {
            $srcdata = array(
                    'ClaimHistorySeq' => 0,
                    'OrderSeq' => $oseq,
                    'OrderCnt' => $ocnt,
            );
            $mdlsrc->saveNew($srcdata);
        }

        $adapt->getDriver()->getConnection()->disconnect();
        $adapt = null;

        // 送信用情報を構築する
        $data = $this->buildSendParams($claimHistorySeq);
        $accSeq = (int)$data['ClaimAccountSeq'];

        $sqlo = " SELECT Cnl_Status FROM T_Order WHERE OrderSeq = :OrderSeq ";
        $cnlstatus = $this->getDbAdapter()->query($sqlo)->execute(array(":OrderSeq" => $oseq))->current()['Cnl_Status'];

        if (nvl($cnlstatus, 0) == 0) {
            $data['shoporder_no'] = sprintf('%013s', $oseq) . sprintf('%04s', $ocnt);
        }
        try {
            // サービスアダプタを初期化
            $this->_smbcRelationAdapter = $this->createServiceAdapter();

            // 送信準備
            $seq = $this->preparseSend($accSeq, $data);

            // 送信可否のチェック
            if(!$this->canSendTo()) {
                throw new LogicSmbcRelationException('メンテナンス中につき送信不可');
            }

            // 送信実行
            $rcv = $this->_sendTo($data);

            // 送信後処理
            return $this->sent($seq, $rcv);
        } catch(\Exception $err) {
            $this->warn(sprintf('[sendTo] an error has occured. seq = %s, err = %s (%s)', nvl($seq, '(n/a)'), $err->getMessage(), get_class($err)));

            if($seq) {
                // 例外が発生したので受信失敗で処理を終了
                $this->failure($seq, $err);
            }

            // 例外は上位へ通知する
            throw $err;
        }
    }

    /**
     * 決済ステーションへ接続するためのサービスアダプタを初期化生成する
     *
     * @access protected
     * @return LogicSmbcRelationAdapterAbstract サービスアダプタ
     */
    protected function createServiceAdapter() {
        $url = f_path($this->getServiceBaseUrl(), $this->getServiceInterfacePath(), '/');

        $options = array(
            LogicSmbcRelationAdapter::OPT_TIMEOUT => $this->getServiceRequestTimeout(),
            LogicSmbcRelationAdapter::OPT_RETRY => $this->getServiceRequestRetry(),
            LogicSmbcRelationAdapter::OPT_TEXT_ENC => $this->getServiceTextEncoding(),
            LogicSmbcRelationAdapter::OPT_TARGET_FUNC => $this->getTargetFunctionCode()
        );

        $this->debug(sprintf('[createServiceAdapter] url = %s, options = %s', $url, var_export($options, true)));
        $adapter = LogicSmbcRelationAdapter::create($this->getServiceAdapter(), $url, $options);
        $adapter->setLogger($this->getLogger());
        return $adapter;
    }

    /**
     * 指定された請求履歴のデータを基に、SMBC決済ステーションへ送信するデータを構築する。
     * このクラスの実装では、送信に必要なデータのうち1回のSQL発行で取得可能な
     * データを取得するだけなので、過不足は派生クラス側でオーバーライドして調整する必要がある
     *
     * @access protected
     * @param int $claimHistroySeq 請求履歴SEQ
     * @return array 決済ステーション向け送信用データ
     */
    protected function buildSendParams($claimHistorySeq) {
        $q = <<<EOQ
SELECT
    DISTINCT ORD.P_OrderSeq AS OrderSeq,
    OCA.ClaimAccountSeq,
    SRA.ApiVersion AS version,
    SRA.BillMethod AS bill_method,
    SRA.KessaiId AS kessai_id,
    SRA.ShopCd AS shop_cd,
    (CASE WHEN IFNULL(ENT.LinePayUseFlg, 0) = 1
          THEN (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN SyunoCoCd5
                WHEN 2 THEN SyunoCoCd6
                ELSE SyunoCoCd4
                END)
          ELSE (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN SyunoCoCd2
                WHEN 2 THEN SyunoCoCd3
                ELSE SyunoCoCd1
               END)
    END) AS syuno_co_cd,
    (CASE WHEN IFNULL(ENT.LinePayUseFlg, 0) = 1
          THEN (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN ShopPwd5
                WHEN 2 THEN ShopPwd6
                ELSE ShopPwd4
                END)
          ELSE (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN ShopPwd2
                WHEN 2 THEN ShopPwd3
                ELSE ShopPwd1
                END)
    END) AS shop_pwd,
    LPAD(CONCAT(CAST(OCA.OrderSeq AS CHAR), LPAD(CAST(OCA.InnerSeq AS CHAR), 4, '0')), 17, '0') AS shoporder_no,
    (
        (SELECT SUM(I.SumMoney)
         FROM   T_OrderItems I
                INNER JOIN T_Order O ON (I.OrderSeq = O.OrderSeq)
         WHERE  O.P_OrderSeq = ORD.OrderSeq
         AND    I.ValidFlg = 1
        ) +
        IFNULL(HIS.ClaimFee, 0) +
        IFNULL(HIS.AdditionalClaimFee, 0) +
        IFNULL(HIS.DamageInterestAmount, 0)
    ) AS seikyuu_kingaku,
    IFNULL(OCA.TaxAmount, 0) AS shouhi_tax,
    (SELECT SUM(I.SumMoney)
     FROM   T_OrderItems I
            INNER JOIN T_Order O ON (I.OrderSeq = O.OrderSeq)
     WHERE O.P_OrderSeq = ORD.OrderSeq
     AND   I.DataClass = 2
    ) AS souryou,
    LPAD(CAST(ENT.EnterpriseId AS CHAR), 12, '0') AS bill_no,
    CUS.NameKj AS bill_name,
    CUS.PostalCode AS bill_zip,
    CUS.UnitingAddress AS bill_adr_1,
    '' AS bill_adr_2,
    '' AS bill_adr_3,
    '' AS bill_adr_4,
    '' AS bill_adr_5,
    DATE_FORMAT(ORD.RegistDate, '%Y%m%d') AS seiyaku_date,
	CASE WHEN SIT.BarcodeLimitDays=999 THEN '99999999'
	ELSE  DATE_FORMAT(DATE_ADD(HIS.LimitDate, INTERVAL SIT.BarcodeLimitDays DAY), '%Y%m%d')
	END  AS shiharai_date,
    SRA.SeikyuuName AS seikyuu_name,
    SRA.SeikyuuKana AS seikyuu_kana,
    DATE_FORMAT(ORD.RegistDate, '%Y%m') AS riyou_nengetsu,
    DATE_FORMAT(HIS.ClaimDate, '%Y%m') AS seikyuu_nengetsu,
    '2' AS hakkou_kbn,
    '2' AS yuusousaki_kbn
FROM
    T_ClaimHistory HIS INNER JOIN
    T_OemClaimAccountInfo OCA ON HIS.Seq = OCA.ClaimHistorySeq INNER JOIN
    T_Order ORD ON ORD.P_OrderSeq = HIS.OrderSeq INNER JOIN
    T_Customer CUS ON CUS.OrderSeq = ORD.OrderSeq INNER JOIN
    T_Enterprise ENT ON ENT.EnterpriseId = ORD.EnterpriseId INNER JOIN
    T_SmbcRelationAccount SRA ON SRA.OemId = IFNULL(ENT.OemId, 0)
    LEFT OUTER JOIN T_Site SIT ON ORD.SiteId = SIT.SiteId
WHERE
    HIS.Seq = :Seq
AND HIS.ValidFlg = 1
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':Seq' => $claimHistorySeq))->current();
        if(!$row) {
            throw new LogicSmbcRelationException('cannot build target data');
        }

        return $row;
    }

    /**
     * SMBC決済ステーションへのデータ送信を準備する。
     * このメソッドを実行することにより、新規に決済ステーション送受信ログデータが生成される
     *
     * @access protected
     * @param int $claimAccountSeq 請求口座SEQ
     * @param array $params 決済ステーション向け送信データ
     * @return 決済ステーション送受信ログSEQ
     */
    protected function preparseSend($claimAccountSeq, array $params) {
        $log_table = new TableSmbcRelationLog($this->getDbAdapter());

        // 空データをインサート
        $seq = $log_table->saveNew($claimAccountSeq, $this->getTargetFunctionCode(), $params);

        // 送信済みに更新
        $log_table->updateBySend($params, $seq);

        return $seq;
    }

    /**
     * 現在SMBC決済ステーションへデータ送信が可能であるかを判断する
     *
     * @access protected
     * @return boolean 登録可能状態ならtrue、それ以外はfalse
     */
    protected function canSendTo() {
        return true;
        // TODO: 折りを見てT_EventScheduleの設定に基づいた判断を実装する
    }

    /**
     * 決済ステーションへのデータ送信を実行する
     *
     * @access protected
     * @param array $data 決済ステーション向け送信データ
     * @return array 受信データ
     */
    protected function _sendTo(array $data) {
        // 不要キーの除去
        $data = $this->applyFieldNameFilter($data);

        // 送信実行
        return $this->_smbcRelationAdapter->send($data);
    }

    /**
     * 指定の送信用データに、未定義キーを削除するためのフィルタ処理を適用する
     *
     * @access protected
     * @param array $data 送信用データ
     * @return array $dataから不要キーを除去したデータ
     */
    protected function applyFieldNameFilter(array $data) {
        $valid_list = LogicSmbcRelation::getValidFieldsFor($this->getTargetFunctionCode());
        $result = array();
        foreach($data as $key => $value) {
            if(in_array($key, $valid_list)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 指定のフィールド値に指定モードの変換フィルタを適用する
     *
     * @access protected
     * @var string $value 適用対象のフィールド値
     * @var strnig $mode フィルタモード。このクラスのFILTER_*定数を指定する
     * @return string フィルタ適用後のフィールド値
     */
    protected function applyFieldValueFilter($value, $mode) {
        switch($mode) {
            case self::FILTER_NARROW_TO_WIDE :
                $value = BaseGeneralUtils::convertNarrowToWideEx($value);
                break;
            case self::FILTER_WIDE_TO_NARROW :
                $value = BaseGeneralUtils::convertWideToNarrowEx($value);
                break;
            case self::FILTER_POSTAL_CODE :
                $value = CoralValidateUtility::fixPostalCode($value, true);
                break;
            case self::FILTER_KANA_SPECIAL :
                $value = $this->applyFilterForSeikyuuKanaValue($value);
                break;
            default:
                break;
        }
        return f_trim($value);
    }

    /**
     * SMBC決済ステーションの登録項目「請求内容（カナ）」で使用できない文字を削除する
     * フィールド値用特殊フィルタ。
     * 許容文字は「インターフェイス仕様書【データ連携方式】仕様書」の「別紙6・請求内容（カナ）使用可能文字一覧」を
     * 踏襲している
     *
     * @access protected
     * @var string $value
     * @return string
     */
    protected function applyFilterForSeikyuuKanaValue($value) {
        $chars = join('', array(
                '０１２３４５６７８９',
                'ＡＢＣＤＥＦＧＨＩＪ',
                'ＫＬＭＮＯＰＱＲＳＴ',
                'ＵＶＷＸＹＺ',
                'ａｂｃｄｅｆｇｈｉｊ',
                'ｋｌｍｎｏｐｑｒｓｔ',
                'ｕｖｗｘｙｚ',
                'ァアィイゥウェエォオ',
                'カガキギクグケゲコゴ',
                'サザシジスズセゼソゾ',
                'タダチヂッツヅテデト',
                'ドナニヌネノ',
                'ハバパヒビピフブプヘ',
                'ベペホボポ',
                'マミムメモ',
                'ャヤュユョヨ',
                'ラリルレロ',
                'ヮワヰヱヲンヴヵヶ',
                '．（）－／ー‐',  // ハイフンはSJIS:815B（長音）、SJIS:817C（マイナス）、SJIS:815D（ハイフン）
                '「」',
                '　'
        ));
        $reg = sprintf('[^%s]', $chars);
        return mb_ereg_replace($reg, '', $value);
    }

    /**
     * SMBC決済ステーションからの受信を完了する。
     * このメソッドを実行することにより、指定の決済ステーション送受信ログが受信済み状態に更新される
     *
     * @access protected
     * @param int $smbcRelSeq 決済ステーション送受信ログSEQ
     * @param array $rcvData 受信データ
     * @return array 受信データ
     */
    protected function sent($smbcRelSeq, array $rcvData) {
        // 受信データに結果コードが含まれていない場合はエラー
        if(!isset($rcvData['rescd'])) {
            throw new LogicSmbcRelationException('決済ステーションエラー：結果コードがありません', $rcvData);
        }
        // 結果コードが正常終了でない場合はエラー
        if(!preg_match('/^000000$/', nvl($rcvData['rescd']))) {
            throw new LogicSmbcRelationException(sprintf('決済ステーションエラー(%s : %s)', $rcvData['rescd'], $rcvData['res']), $rcvData);
        }

        $log_table = new TableSmbcRelationLog($this->getDbAdapter());

        // 指定決済ステーション連携ログのReceivedDataに受信データを反映し、Statusを2：受信済みに更新する
        $log_table->updateByReceive($rcvData, $smbcRelSeq);

        return $rcvData;
    }

    /**
     * SMBC決済ステーションへのデータ送信の失敗をログに反映する
     *
     * @access protected
     * @param int $smbcRelSeq 決済ステーション送受信ログSEQ
     * @param \Exception $err 例外
     */
    protected function failure($smbcRelSeq, \Exception $err) {
        $log_table = new TableSmbcRelationLog($this->getDbAdapter());

        // 指定決済ステーション連携ログのErrorReasonに例外メッセージを反映し、Statusを9：受信失敗に更新する
        $msg = sprintf('%s (%s)', $err->getMessage(), get_class($err));

        // 例外がLogicSmbcRelationExceptionの場合はアプリケーションデータを使用する
        $data = ($err instanceof LogicSmbcRelationException) ? $err->getApplicationData() : null;

        $log_table->updateByReceiveFailure($msg, $smbcRelSeq, $data);
    }

    /**
     * 指定文字列をCP932変換した場合に指定バイト長以内に収まるよう分割する
     *
     * @access protected
     * @param string $str 分割する文字列
     * @param int $limit_bytes 1パートあたりの最大バイト長（CP932変換時）
     * @return array $strを指定条件で分割された文字列
     */
    protected function splitAndTrimString($str, $limit_bytes) {
        $limit = $limit_bytes;          // 各パートの最大バイト数
        $max_len = mb_strlen($str);    // 処理する最大文字数（＝結合住所の文字数）
        $result = array();              // 分割結果を格納する配列

        $buf = '';          // 作業バッファ
        $idx = 0;           // カーソル位置
        $rem_len = $limit;  // 処理中バッファの残りバイト数

        // カーソル位置が有効な限りループ
        while($idx < $max_len) {
            // 残りバイト数の半分の長さを候補文字列として取り出す
            $len = floor($rem_len / 2);
            $part = mb_substr($str, $idx, $len);

            $enc = $this->getServiceTextEncoding();
            if($len > 0 && bytes_as_spec_enc($part, $enc) <= $rem_len) {
                // 取り出した文字列の長さが残りバイト数以下の場合
                $buf .= $part;                              // 候補文字列をバッファに追加
                $idx += $len;                               // カーソルを移動
                $rem_len = $limit - bytes_as_spec_enc($buf, $enc);  // 残りバイト数を更新
            } else {
                // 取り出した文字の長さが残りバイトを超過した場合
                $result[] = $buf;                           // 処理中バッファを確定
                $buf = '';                                  // バッファクリア
                $rem_len = $limit;                          // 残りバイト数初期化
            }
        }
        // バッファの残があったら確定させる
        if(strlen($buf)) $result[] = $buf;

        // 分割済み結果を返す
        return $result;
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
}
