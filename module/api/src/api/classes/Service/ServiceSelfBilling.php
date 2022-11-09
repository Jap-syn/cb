<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use models\Table\TableEnterprise;
use models\Table\TableOrder;
use models\Table\TableSelfBillingProperty;
use models\Logic\LogicSelfBilling;
use models\Logic\SelfBilling\LogicSelfBillingException;
use models\Table\TableUser;
use models\Logic\SelfBilling\LogicSelfBillingSelfBillingApi;
use api\classes\Service\Response\ServiceResponseSelfBilling;
use api\classes\Service\SelfBilling\ServiceSelfBillingConst;

/**
 * 同梱API用サービスクラス
 */
class ServiceSelfBilling extends ServiceAbstract {

    /**
    * ジョブ転送候補リストを取得するコマンド
    * @var string
    */
    const CMD_FETCH_PRE_TARGETS = 'FetchPreTargets';

    /**
    * ジョブ転送可能データの件数を取得するコマンド
    * @var string
    */
    const CMD_COUNT_PRE_TARGETS = 'CountPreTargets';

    /**
    * ジョブ転送可否を取得するコマンド
    * @var string
    */
    const CMD_CAN_ENQUEUE = 'CanEnqueue';

    /**
    * 印刷キュー転送実行コマンド
    * @var string
    */
    const CMD_ENQUEUE = 'Enqueue';

    /**
    * 請求対象件数を取得するコマンド
    * @var string
    */
    const CMD_COUNT_TARGETS = 'CountTargets';

    /**
    * 請求対象・対象外を問い合わせるコマンド
    * @var string
    */
    const CMD_IS_TARGET = 'IsTarget';

    /**
     * 請求対象リストを取得するコマンド
     * @var string
     */
    const CMD_FETCH_TARGETS = 'FetchTargets';

    /**
     * 請求対象リスト(条件付き)を取得するコマンド
     * @var string
     */
    const CMD_FETCH_TARGET_CONDITIONS = 'FetchTargetConditions';

    /**
     * 処理済みを設定するコマンド
     * @var string
     */
    const CMD_PROCESSED = 'Processed';

    /**
     * 同梱アクセスキーハッシュ
     * @var string
     */
    protected $_accessToken;

    /**
     * アクション
     * @var string
     */
    protected $_action;

    /**
     * コマンド
     * @var string
     */
    protected $_cmd;

    /**
     * 同梱API用のサービスID
     * @var string
     */
    protected $_serviceId = "10";

    /**
     * 事業者情報
     * @var string
     */
    protected $_ent;

    /**
     * SelfBillingLogic
     */
    protected $_logic;

    /**
     * 初期化処理
     *
     * @access protected
     */
    protected function init() {
        $app = Application::getInstance();

        // サイトIDチェックは行わない
        $this->_checkSiteId = false;

        // レスポンスを初期化
        $this->_response = new ServiceResponseSelfBilling();

        // 認証用
        $this->_apiUserId    = $this->_data[ServiceSelfBillingConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceSelfBillingConst::ENTERPRISE_ID];
        $this->_accessToken  = $this->_data[ServiceSelfBillingConst::ACCESS_TOKEN];
        $this->_action       = $this->_data[ServiceSelfBillingConst::ACTION];

        // 事業者情報の設定
        $enterprises = new TableEnterprise($this->_db);
        $this->_ent = $enterprises->findEnterprise2($this->_enterpriseId)->current();

        // リスト取得上限件数の設定
        if ($this->_ent['TargetListLimit'] == NULL) {
            $target_list_limit = $app->selfBillingConfig['target_list_limit'];
        } else {
            $target_list_limit = $this->_ent['TargetListLimit'];
        }

        // APIユーザーID
        $mdluser = new TableUser($this->_db);
        $userId = $mdluser->getUserId(3, $this->_apiUserId);
        // SelfBillLogicのインスタンス生成
        $this->_logic = new LogicSelfBillingSelfBillingApi($this->_db, $this->_ent['EnterpriseId'], $userId, Application::getInstance()->getAuthUtility());

        $this->_logic->setSystemSelfBillingEnabled($app->selfBillingConfig['use_selfbilling']);
        $this->_logic->setPaymentLimitDays($app->selfBillingConfig['payment_limit_days']);
        $this->_logic->setThresholdClientVersion($app->selfBillingConfig['threshold_version']);
        $this->_logic->setTargetListLimit($target_list_limit);
        $this->_logic->importStampFeeLogicSettings($app->stampFeeLogicSettings);
        $this->_logic->setLogger($app->logger);

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            join(', ', array(
                sprintf('%s: %s', ServiceSelfBillingConst::API_USER_ID, $this->_enterpriseId),
                sprintf('%s: %s', ServiceSelfBillingConst::ENTERPRISE_ID, $this->_apiUserId),
                sprintf('%s: %s', ServiceSelfBillingConst::ACCESS_TOKEN, $this->_accessToken),
                sprintf('%s: %s', ServiceSelfBillingConst::ACTION, $this->_action),
                sprintf('RemoteAddr: %s', f_get_client_address())       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
            )) );
    }

    /**
     * 認証処理
     *
     */
    protected function auth() {
        $result = true;

        // 親検証
        $result = parent::auth();

        if($result) {
            // 次期システムではアクセスキーの正当性チェックは行わず、同梱ツール利用可能チェックのみとする

            // 現在のアカウントが同梱ツール利用可能かをチェック
            if(!$this->_ent['SelfBillingMode']) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * 入力に対する検証を行う
     *
     * @access protected
     * @return boolean 検証結果
     */
    protected function check() {
        return true;
    }

    /**
     * サービスを実行する
     *
     * @access protected
     * @return boolean サービス実行結果
     */
    protected function exec() {
        $result = true;

        // Logic用送信用データ
        $senddata = array();

        // コマンド（アクションのセット) $_cmdへセットされる
        if(!$this->checkAction($this->_action)) return false;
        $senddata['Command'] = $this->_cmd;

        // iniからバージョン情報を取得
        $threshold_version = Application::getInstance()->selfBillingConfig->threshold_version;

        // バージョン情報を送信情報にセット
        $this->_data['Version'] =  $threshold_version;

        // パラメータ設定
        $senddata['Parameters'] = $this->_data;

        // 加盟店設定
        $senddata['EnterpriseId'] = $this->_enterpriseId;

        // LogicSelfBillingのdispatch
        try {
            $data = $this->_logic->dispatch($senddata);
            if(is_array($data)) {
                $this->_response->addResult($data);
            }
        } catch(LogicSelfBillingException $sberr) {
//
Application::getInstance()->logger->info(sprintf('%s#exec() ERROR: %s', get_class($this), $sberr->getMessage()));
            // エラーレスポンスを作成
            $data = $this->_logic->generateErrorResponse($sberr);

            // エラーコードによってエラーメッセージを分ける
            switch($data['Code']) {
                case LogicSelfBillingException::ERR_DISPATCH_INVALID_COMMAND:
                // コマンド不正
                    $this->_response->addMessage("E" . $this->_serviceId . "104", "アクション種別が正しく設定されていません。".
                    "アクション種別を正しく設定してください。");
                    break;
                default:
                    $this->_response->addMessage("E" . $this->_serviceId . "901", "システムで障害が発生しました。".
                    "サポートセンターまでお問合せください。");
                    break;
            }
            $result = false;
        } catch(\Exception $err) {
//
Application::getInstance()->logger->info(sprintf('%s#exec() ERROR: %s', get_class($this), $err->getMessage()));
            // エラーレスポンスを作成
            $this->_response->addMessage("E" . $this->_serviceId . "901",
                    "システムで障害が発生しました。".
                    "サポートセンターまでお問合せください。");
            $result = false;
        }
        return $result;
    }


    /**
     * コマンドをLogicSelfBilling用に変更する
     *
     * @access protected
     * @param string cmd
     * @return string
     */
    protected function checkAction($act_name)
    {
        // レスポンスにアクション名を設定
        $this->_response->addAction($act_name);

        //
        switch($act_name) {
            case self::CMD_FETCH_PRE_TARGETS:
                $this->_cmd = LogicSelfBilling::CMD_GET_PRE_TARGETS;
                break;

            case self::CMD_COUNT_PRE_TARGETS:
                $this->_cmd = LogicSelfBilling::CMD_COUNT_PRE_TARGETS;
                break;

            case self::CMD_CAN_ENQUEUE:
                $this->_cmd = LogicSelfBilling::CMD_CAN_ENQUEUE; // TODO:おそらく差分だと思うが、LogicSelfBilling自体に差分反映されていないと思われる。
                break;

            case self::CMD_ENQUEUE:
                $this->_cmd = LogicSelfBilling::CMD_SEND_PRE_TARGETS_TO;
                break;

            case self::CMD_COUNT_TARGETS:
                $this->_cmd = LogicSelfBilling::CMD_COUNT_TARGETS;
                break;

            case self::CMD_IS_TARGET:
                $this->_cmd = LogicSelfBilling::CMD_JUDGE_PRINTABLE;
                break;

            case self::CMD_FETCH_TARGETS:
                $this->_cmd = LogicSelfBilling::CMD_GET_TARGETS;
                break;

            case self::CMD_FETCH_TARGET_CONDITIONS:
                $this->_cmd = LogicSelfBilling::CMD_GET_TARGET_CONDITIONS;
                break;

            case self::CMD_PROCESSED:
                $this->_cmd = LogicSelfBilling::CMD_SET_PRINTED;
                break;

            default:
                $result = false;
                break;
        }

        return true;
    }

	/**
	 * 指定注文IDの注文データを取得する。
	 *
	 * @access protected
	 * @param string $orderId 注文ID
	 * @return array
	 */
	protected function getOrderData($orderId) {

		$orders = new TableOrder($this->_db);
		$where = array(
			'OrderId' => $orderId,
			'EnterpriseId' => $this->_enterpriseId
		);

		$order = $orders->findOrder($where);

		$orderData = $order->current();

		return $orderData;
	}


	/**
	 * 処理結果を文字列として返却する
	 *
	 * @access protected
	 * @return string 処理結果
	 */
	protected function returnResponse() {
		return $this->_response->serialize();
	}

}