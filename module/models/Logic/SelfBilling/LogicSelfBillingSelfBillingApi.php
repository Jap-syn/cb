<?php
namespace models\Logic\SelfBilling;

use models\Logic\LogicSelfBilling;
use models\Logic\SelfBilling\LogicSelfBillingException;
use models\Table\TableClaimHistory;
use models\Table\TableOrder;
use models\View\ViewWaitForFirstClaim;
use api\Application;
use models\Table\TableSystemProperty;
// require_once 'functions.php';

/**
 * 請求書同梱ツールAPIロジック
 */
class LogicSelfBillingSelfBillingApi extends LogicSelfBilling {
    /**
     * 結果
     */
    const RES_KEY_RESULT = 'Result';

    /**
     * 注文ID
     */
    const RES_KEY_ORDER_ID = 'OrderId';

    /**
     * 実行結果
     */
    const RES_KEY_EXEC_RESULT = 'ExecResult';

    /**
     * カウント
     */
    const RES_KEY_COUNT = 'Count';

    /**
     * エラーコード
     */
    const RES_KEY_ERROR_CD = 'ErrorCd';

    /**
     * エラーメッセージ
     */
    const RES_KEY_ERROR_MSG = 'ErrorMessage';

    // 事業者ID
    protected $_enterpriseId;

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

        $this->_enterpriseId = $data['EnterpriseId'];

        switch($cmd_name) {
            case self::CMD_CAN_ENQUEUE:
                // ジョブ転送可否要求
                return $this->execCanEnqueue($params);
                    default:
                    break;
        }
        return parent::dispatch($data);
    }

    /**
     * 絶対に呼ばれない
     * Noop要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execNoop($params)
    {
        // 親クラスのオーバーライド
        // 親クラスでしている処理は行わない。
    }

    /**
     *　絶対に呼ばれない
     * タスク開始要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execPrepareSession($params)
    {
        // 親クラスのオーバーライド
        // 親クラスでしている処理は行わない。
    }

    /**
     *　絶対に呼ばれない
     * タスク完了要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execTerminateSession($params)
    {
        // 親クラスのオーバーライド
        // 親クラスでしている処理は行わない。
    }

    /**
     *　絶対に呼ばれない
     * レポート設定取得要求を実行する
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execGetReportSettings($params)
    {
        // 親クラスのオーバーライド
        // 親クラスでしている処理は行わない。
    }


    /**
     * ジョブ転送可能リスト取得要求を実行する
     * Action:FetchPreTargets
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execGetPreTargets($params)
    {
        $result = array();

        // 親クラスのメソッドを呼び出して実行
        $data = parent::execGetPreTargets($params);

        // データ整形
        $tmpret = array();
        foreach ($data as $key => $value) {
           if($key == parent::RES_KEY_RESULT) {
                foreach ($value as $i => $val) {
                    // 日時のフォーマット
                    $val['ReceiptOrderDate'] = f_df($val['ReceiptOrderDate'], 'Y/m/d');
                    $val['RegistDate'] = f_df($val['RegistDate'], 'Y/m/d H:i:s');
                   // OrderSeq, IsAnotherDeliを削除
                    unset($val['OrderSeq']);
                    unset($val['IsAnotherDeli']);
                    array_push($tmpret, $val);
                }
           }
        }

        $result = array( self::RES_KEY_RESULT => $tmpret );
        return $result;
    }

        /**
         * ジョブ転送可否問合せを実行する
         * Action:CanEnqueue
         *
         * @param array @params コマンドパラメータ
         * @return array
         */
        public function execCanEnqueue($params) {

            $result = array();

            $vwfc = new ViewWaitForFirstClaim($this->_db);

            $setdata = array();
            foreach($params['Param'] as $key => $value) {
                $errorMsg = "";
                $errorCd = "";

                $orderId = $this->getOrderIdForNewSystem($value['OrderId']);

                try {
                    $ret = array();

                    // 注文Seqの整形
                    $order = $this->getOrderData($orderId);
                    if(empty($order)) {
                        $errorMsg = "指定の注文がありません。正しい注文IDを指定してください。";
                        $errorCd = "E001";
                        throw new \Exception("注文ID指定エラー");
                    };

                    // ジョブ転送可否問合せ
                    $arrOrderId = $vwfc->getToPrintSBCount($orderId);

                    if(!empty($arrOrderId)) {
                        $ret[self::RES_KEY_ORDER_ID] = $value['OrderId'];
                        $ret[self::RES_KEY_EXEC_RESULT] = 1;
                    } else {
                        $ret[self::RES_KEY_ORDER_ID] = $value['OrderId'];
                        $ret[self::RES_KEY_EXEC_RESULT] = 0;
                    }
                } catch(LogicSelfBillingException $sberr) {

Application::getInstance()->logger->info(sprintf('%s#execCanEnqueue() OrderId:%s  ERROR: %s', get_class($this), $orderId, $sberr->getMessage()));
                    array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => -1,
                                                self::RES_KEY_ERROR_CD => "E003",
                                                self::RES_KEY_ERROR_MSG => $sberr->getMessage(),
                                       )
                    );
                    continue;
                } catch(\Exception $e) {
// その他のエラー
Application::getInstance()->logger->info(sprintf('%s#execCanEnqueue() OrderId:%s  ERROR: %s', get_class($this), $orderId, $errorMsg));
                    array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => -1,
                                                self::RES_KEY_ERROR_CD => $errorCd,
                                                self::RES_KEY_ERROR_MSG => $errorMsg,
                                            )
                        );
                    continue;
                }
                // 結果情報のセット
                array_push($setdata, $ret);
            }

            // データセット
            $result[self::RES_KEY_RESULT] = $setdata;
             return $result;
        }

     /**
     * ジョブ転送要求を実行する
     * Action:Enqueue
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execSendPreTargetsTo($params, $to_cb = false)
    {
            // 一時的な格納
            $setdata = array();

            $mdlsp = new TableSystemProperty($this->_db);
            $reniId = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'RENIEnterpriseId');

            if ($params['EnterpriseId'] == $reniId) {
                $sql = " SELECT PropId FROM T_SystemProperty WHERE Module = :Module  AND Category = :Category AND Name = :Name ";
                $prm = array(':Module' => '[DEFAULT]', ':Category' => 'systeminfo', ':Name' => 'ApiDuplicateFlg2');
                $pId = $this->_db->query($sql)->execute($prm)->current()['PropId'];

                $sql = ' UPDATE T_SystemProperty SET PropValue = 1 WHERE PropId = :PropId ';
                $rows = $this->_db->query($sql)->execute(array(':PropId' => $pId))->getAffectedRows();
                if ($rows == 0) {
                    throw new \Exception("同梱API重複登録エラー");
                }
            }

            // OrderId毎に一件ずつジョブ転送要求を実行する
            foreach($params['Param'] as $key => $value) {
                $errorMsg = "";
                $errorCd = "";

                $orderId = $this->getOrderIdForNewSystem($value['OrderId']);

                //同一注文フラグ
                $sameOrderFlg = false;
                try {
                    // 処理結果
                    $execResult = -1;

                    // 注文Seqの整形
                    $order = $this->getOrderData($orderId);

                    if(empty($order)) {
                        $errorMsg = "指定の注文がありません。正しい注文IDを指定してください。";
                        $errorCd = "E001";
                        throw new \Exception("注文ID指定エラー");
                    };

                    $sql = "SELECT * FROM W_Enqueue WHERE OrderSeq = :OrderSeq";
                    $data = $this->_db->query($sql)->execute(array (':OrderSeq' => $order['OrderSeq']))->current();

                    if(empty($data)){
                        //同一注文Seqが存在しない場合
                        $sql  = "INSERT INTO W_Enqueue (OrderSeq) VALUES (:OrderSeq)";
                        // 新規追加
                        $this->_db->query($sql)->execute(array (':OrderSeq' => $order['OrderSeq']));
                    } else {
                        //同一注文Seqが存在する場合
                        $sameOrderFlg = true;
                        $errorMsg = "指定の注文は現在処理中です。";
                        $errorCd = "E004";
                        throw new \Exception("同一注文ID指定エラー");
                    }

                    $params['Seqs'] = array('OrderSeq' => $order['OrderSeq']);

                    if($value['Mode'] == 0) {
                        // 同梱
                        $ret = parent::execSendPreTargetsTo($params);
                        $execResult = $ret[parent::RES_KEY_RESULT]['count'];
                    } else if($value['Mode'] == 1) {
                        // 別送
                        $ret = parent::execSendPreTargetsTo($params, true);
                        $execResult = $ret[parent::RES_KEY_RESULT]['count'];
                    } else {
                        $errorMsg = "同梱・別送モードの設定が誤っています。モードを正しく設定してください。";
                        $errorCd = "E002";
                        throw new \Exception("モード指定エラー");
                    }
                }  catch(LogicSelfBillingException $sberr) {

Application::getInstance()->logger->info(sprintf('%s#execSendPreTargetsTo() OrderId:%s  ERROR: %s', get_class($this), $orderId, $sberr->getMessage()));
                    array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => $execResult,
                                                self::RES_KEY_ERROR_CD => "E003",
                                                self::RES_KEY_ERROR_MSG => $sberr->getMessage(),
                                       )
                    );
                    // 印刷キュー転送実行ワークの物理削除
                    $sql = " DELETE FROM W_Enqueue WHERE OrderSeq = :OrderSeq ";
                    $this->_db->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']));
                    continue;
                } catch(\Exception $e) {
// その他のエラー
Application::getInstance()->logger->info(sprintf('%s#execSendPreTargetsTo() OrderId:%s  ERROR: %s', get_class($this), $orderId, $errorMsg));
                    array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => $execResult,
                                                self::RES_KEY_ERROR_CD => $errorCd,
                                                self::RES_KEY_ERROR_MSG => $errorMsg,
                                         )
                    );
                    if(!$sameOrderFlg){
                        // 印刷キュー転送実行ワークの物理削除
                        $sql = " DELETE FROM W_Enqueue WHERE OrderSeq = :OrderSeq ";
                        $this->_db->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']));
                    }
                    continue;
                }

                array_push($setdata, array(
                                        self::RES_KEY_ORDER_ID => $value['OrderId'],
                                        self::RES_KEY_EXEC_RESULT => $execResult,
                                     )
                );

                // 印刷キュー転送実行ワークの物理削除
                $sql = " DELETE FROM W_Enqueue WHERE OrderSeq = :OrderSeq ";
                $this->_db->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']));

            }
            // データセット
            $result = array( self::RES_KEY_RESULT => $setdata );

            if ($params['EnterpriseId'] == $reniId) {
                $mdlsp->saveUpdate(array('PropValue' => 0), $pId);
            }

            return $result;
    }

    /**
     * ジョブ転送可能件数取得要求を実行する
     * Action:CountPreTargets
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execCountPreTargets($params)
    {
        $result = array();

        // 親クラスのメソッドを呼び出して実行
        $data = parent::execCountPreTargets($params);

        foreach ($data[parent::RES_KEY_RESULT] as $key => $value) {
            if($key = 'count') $result[self::RES_KEY_RESULT] = array(array(self::RES_KEY_COUNT => $value));
        }

        return $result;
    }

    /**
     * 印刷対象件数取得要求を実行する
     * Action:CountTargets
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execCountTargets($params)
    {
        $result = array();

        // 親クラスのメソッドを呼び出して実行
        $data = parent::execCountTargets($params);

        foreach ($data[parent::RES_KEY_RESULT] as $key => $value) {
            if($key = 'count')$result[self::RES_KEY_RESULT] = array(array(self::RES_KEY_COUNT => $value));
        }

        return $result;
    }

    /**
     * 印刷可能判断要求を実行する
     *　Action:IsTarget
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execJudgePrintable($params)
    {
        $result = array();

        // 1件ごとに印刷可能判断要求を行う
        $setdata = array();
        foreach($params['Param'] as $i => $value) {
            $errorMsg = "";
            $errorCd = "";

            $orderId = $this->getOrderIdForNewSystem($value['OrderId']);

            try {
                $ret = array();

                $order = $this->getOrderData($orderId);

                if(empty($order)) {
                    $errorMsg = "指定の注文がありません。正しい注文IDを指定してください。";
                    $errorCd = "E001";
                    throw new \Exception("注文ID指定エラー");
                };

                $params['Seqs'] = array('OrderSeq' => $order['OrderSeq']);

                // 親クラスのメソッドを呼び出して実行
                $data = parent::execJudgePrintable($params);

                // 結果の組み立て
                $resultCount = 0;
                if($data[parent::RES_KEY_RESULT]) {
                    $resultCount = count($data[parent::RES_KEY_RESULT]);
                }
                if($resultCount == 1) {
                    $ret[self::RES_KEY_ORDER_ID] = $value['OrderId'];
                    $ret[self::RES_KEY_EXEC_RESULT] = 1;
                } else {
                    $ret[self::RES_KEY_ORDER_ID] = $value['OrderId'];
                    $ret[self::RES_KEY_EXEC_RESULT] = 0;
                }
                array_push($setdata, $ret);
            } catch(LogicSelfBillingException $sberr) {
//
Application::getInstance()->logger->info(sprintf('%s#execJudgePrintable() OrderId:%s  ERROR: %s', get_class($this), $orderId, $sberr->getMessage()));
                    array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => -1,
                                                self::RES_KEY_ERROR_CD => "E003",
                                                self::RES_KEY_ERROR_MSG => $sberr->getMessage(),
                                       )
            );
                continue;
            } catch(\Exception $e) {
// その他のエラー
Application::getInstance()->logger->info(sprintf('%s#execJudgePrintable() OrderId:%s  ERROR: %s', get_class($this), $orderId, $errorMsg));
                    array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => -1,
                                                self::RES_KEY_ERROR_CD => $errorCd,
                                                self::RES_KEY_ERROR_MSG => $errorMsg,
                                            )
                        );
                    continue;
               }
            }

            // データセット
            $result[self::RES_KEY_RESULT] = $setdata;
            return $result;
    }

    /**
     * 印刷対象リスト取得要求を実行する
     * Action:FetchTargets
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execGetTargets($params)
    {
        $result = array();

        // 親クラスのメソッドを呼び出して実行
        $odata = parent::execGetTargets($params);
        // データ整形
        $resultarray = array();
        foreach ($odata as $key => $value) {
           if($key == parent::RES_KEY_RESULT) {
                foreach ($value as $i => $val) {
                    $tmparr = array();
                    // 顧客郵便番号
                    $tmparr['PostalCode'] = $val['PostalCode'];
                    // 顧客住所
                    $tmparr['UnitingAddress'] = $val['UnitingAddress'];
                    // 顧客氏名
                    $tmparr['NameKj'] = $val['NameKj'];
                    // 注文ＩＤ
                    $tmparr['OrderId'] = $val['OrderId'];
                    // 注文日
                    $tmparr['ReceiptOrderDate'] = f_df($val['ReceiptOrderDate'], 'Y/m/d');
                    // 購入店名
                    $tmparr['SiteNameKj'] = $val['SiteNameKj'];
                    // 購入店URL
                    $tmparr['Url'] = $val['Url'];
                    // 購入店電話番号
                    $tmparr['Phone'] = $val['Phone'];
                    // 請求金額
                    $tmparr['UseAmount'] = $val['UseAmount'];
                    // 小計(請求金額-(送料+決済手数料+外税))
                    $subtotal = $val['UseAmount'] - ($val['CarriageFee'] + $val['ChargeFee'] );
                    $tmparr['SubTotal'] = $subtotal;
                    // 送料
                    $tmparr['CarriageFee'] = $val['CarriageFee'];
                    // 決済手数料
                    $tmparr['ChargeFee'] = $val['ChargeFee'];
                    // 請求回数
                    $tmparr['ReIssueCount'] = $val['ReIssueCount'];
                    // 支払期限日
                    $tmparr['LimitDate'] = f_df($val['LimitDate'], 'Y/m/d');
                    // バーコードデータ
                    $tmparr['Cv_BarcodeData'] = $val['Cv_BarcodeData'];
                    // バーコード文字列1
                    $tmparr['Cv_BarcodeString1'] = $val['Cv_BarcodeString1'];
                    // バーコード文字列2
                    $tmparr['Cv_BarcodeString2'] = $val['Cv_BarcodeString2'];
                    // ゆうちょDT用データ
                    $tmparr['Yu_DtCode'] = $val['Yu_DtCode'];

                    // 注文商品情報の取得
                    $params['Seqs'] = array('OrderSeq' => $val['OrderSeq']);
                    $itemdata = $this->getItemData($val['OrderSeq']);
                    $tmpitem = array();
                    $j = 0;
                    foreach($itemdata as $row) {
                        $j += 1; // 1から開始

                        // 単価に-が含まれている消費税率を0にする
                            if($row['UnitPrice'] < 0){
                               $row['TaxRate'] = 0;

                        }

                        array_push($tmpitem, array(
                                'ItemNameKj'.$j => $row['ItemNameKj'],
                                'ItemNum'.$j => $row['ItemNum'],
                                'UnitPrice'.$j => $row['UnitPrice'],
                                'SumMoney'.$j => $row['SumMoney'],
                                'TaxRate'.$j => $row['TaxRate'],
                                )
                        );
                    }

                    $tmparr['OrderItems'] = $tmpitem;

                    // 小計
                    $tmparr['SubTotal'] = $subtotal;
                    // 任意注文番号
                    $tmparr['Ent_OrderId'] = $val['Ent_OrderId'];
                    // うち消費税額
                    $tmparr['TaxAmount'] = $val['TaxAmount'];
                    // ８％対象合計金額
                    $tmparr['SubUseAmount_1'] = $val['SubUseAmount_1'];
                    // ８％対象消費税額
                    $tmparr['SubTaxAmount_1'] = $val['SubTaxAmount_1'];
                    // １０％対象合計金額
                    $tmparr['SubUseAmount_2'] = $val['SubUseAmount_2'];
                    // １０％対象消費税額
                    $tmparr['SubTaxAmount_2'] = $val['SubTaxAmount_2'];
                    // CVS収納代行会社名
                    $tmparr['Cv_ReceiptAgentName'] = $val['Cv_ReceiptAgentName'];
                    // CVS収納代行加入者名
                    $tmparr['Cv_SubscriberName'] = $val['Cv_SubscriberName'];
                    // 銀行口座 - 銀行コード
                    $tmparr['Bk_BankCode'] = $val['Bk_BankCode'];
                    // 銀行口座 - 支店コード
                    $tmparr['Bk_BranchCode'] = $val['Bk_BranchCode'];
                    // 銀行口座 - 銀行名
                    $tmparr['Bk_BankName'] = $val['Bk_BankName'];
                    // 銀行口座 - 支店名
                    $tmparr['Bk_BranchName'] = $val['Bk_BranchName'];
                    // 銀行口座 - 口座種別
                    $tmparr['Bk_DepositClass'] = $val['Bk_DepositClass'];
                    // 銀行口座 - 口座番号
                    $tmparr['Bk_AccountNumber'] = $val['Bk_AccountNumber'];
                    // 銀行口座 - 口座名義
                    $tmparr['Bk_AccountHolder'] = $val['Bk_AccountHolder'];
                    // 銀行口座 - 口座名義カナ
                    $tmparr['Bk_AccountHolderKn'] = $val['Bk_AccountHolderKn'];
                    // ゆうちょ口座 - 加入者名
                    $tmparr['Yu_SubscriberName'] = $val['Yu_SubscriberName'];
                    // ゆうちょ口座 - 口座番号
                    $tmparr['Yu_AccountNumber'] = $val['Yu_AccountNumber'];
                    // ゆうちょ口座 - 払込負担区分
                    $tmparr['Yu_ChargeClass'] = $val['Yu_ChargeClass'];
                    // ゆうちょ口座 - MT用OCRコード1
                    $tmparr['Yu_MtOcrCode1'] = $val['Yu_MtOcrCode1'];
                    // ゆうちょ口座 - MT用OCRコード2
                    $tmparr['Yu_MtOcrCode2'] = $val['Yu_MtOcrCode2'];
                    //事業者登録番号
                    $tmparr['CorporationNumber'] = "";
                    //ペイジー確認番号
                    $tmparr['ConfirmNumber'] = $val['ConfirmNumber'];
                    //ペイジーお客様番号
                    $tmparr['CustomerNumber'] = $val['CustomerNumber'];
                    //ペイジー収納機関番号
                    $tmparr['Bk_Number'] = $val['Bk_Number'];
                    //注文マイページの利用」が1：利用するの場合
                    if($val['OrderpageUseFlg'] == 1){
                        //マイページログインパスワード
                        $tmparr['MypagePassword'] = $val['MypagePassword'];
                        //マイページＵＲＬ
                        $tmparr['MypageUrl'] = $val['MypageUrl'];
                    }
                    if(isset($val['CreditLimitDate'])){
                        //クレジット手続き期限日
                        $tmparr['CreditLimitDate'] = $val['CreditLimitDate'];
                    }
                    array_push($resultarray, $tmparr);
                }
           }
        }

        $result = array( self::RES_KEY_RESULT => $resultarray );
        return $result;
    }

    /**
     * 印刷対象リスト取得要求を実行する
     * Action:FetchTargets
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execGetTargetConditions($params)
    {
        $result = array();

        // 親クラスのメソッドを呼び出して実行
        $odata = parent::execGetTargetConditions($params);
        // データ整形
        $resultarray = array();
        foreach ($odata as $key => $value) {
            if($key == parent::RES_KEY_RESULT) {
                foreach ($value as $i => $val) {
                    $tmparr = array();
                    // 顧客郵便番号
                    $tmparr['PostalCode'] = $val['PostalCode'];
                    // 顧客住所
                    $tmparr['UnitingAddress'] = $val['UnitingAddress'];
                    // 顧客氏名
                    $tmparr['NameKj'] = $val['NameKj'];
                    // 注文ＩＤ
                    $tmparr['OrderId'] = $val['OrderId'];
                    // 注文日
                    $tmparr['ReceiptOrderDate'] = f_df($val['ReceiptOrderDate'], 'Y/m/d');
                    // 購入店名
                    $tmparr['SiteNameKj'] = $val['SiteNameKj'];
                    // 購入店URL
                    $tmparr['Url'] = $val['Url'];
                    // 購入店電話番号
                    $tmparr['Phone'] = $val['Phone'];
                    // 請求金額
                    $tmparr['UseAmount'] = $val['UseAmount'];
                    // 小計(請求金額-(送料+決済手数料+外税))
                    $subtotal = $val['UseAmount'] - ($val['CarriageFee'] + $val['ChargeFee'] );
                    $tmparr['SubTotal'] = $subtotal;
                    // 送料
                    $tmparr['CarriageFee'] = $val['CarriageFee'];
                    // 決済手数料
                    $tmparr['ChargeFee'] = $val['ChargeFee'];
                    // 請求回数
                    $tmparr['ReIssueCount'] = $val['ReIssueCount'];
                    // 支払期限日
                    $tmparr['LimitDate'] = f_df($val['LimitDate'], 'Y/m/d');
                    // バーコードデータ
                    $tmparr['Cv_BarcodeData'] = $val['Cv_BarcodeData'];
                    // バーコード文字列1
                    $tmparr['Cv_BarcodeString1'] = $val['Cv_BarcodeString1'];
                    // バーコード文字列2
                    $tmparr['Cv_BarcodeString2'] = $val['Cv_BarcodeString2'];
                    // ゆうちょDT用データ
                    $tmparr['Yu_DtCode'] = $val['Yu_DtCode'];

                    // 注文商品情報の取得
                    $params['Seqs'] = array('OrderSeq' => $val['OrderSeq']);
                    $itemdata = $this->getItemData($val['OrderSeq']);
                    $tmpitem = array();
                    $j = 0;
                    foreach($itemdata as $row) {
                        $j += 1; // 1から開始

                        // 単価に-が含まれている消費税率を0にする
                        if($row['UnitPrice'] < 0){
                            $row['TaxRate'] = 0;

                        }

                        array_push($tmpitem, array(
                                               'ItemNameKj'.$j => $row['ItemNameKj'],
                                               'ItemNum'.$j => $row['ItemNum'],
                                               'UnitPrice'.$j => $row['UnitPrice'],
                                               'SumMoney'.$j => $row['SumMoney'],
                                               'TaxRate'.$j => $row['TaxRate'],
                                           )
                        );
                    }

                    $tmparr['OrderItems'] = $tmpitem;

                    // 小計
                    $tmparr['SubTotal'] = $subtotal;
                    // 任意注文番号
                    $tmparr['Ent_OrderId'] = $val['Ent_OrderId'];
                    // うち消費税額
                    $tmparr['TaxAmount'] = $val['TaxAmount'];
                    // ８％対象合計金額
                    $tmparr['SubUseAmount_1'] = $val['SubUseAmount_1'];
                    // ８％対象消費税額
                    $tmparr['SubTaxAmount_1'] = $val['SubTaxAmount_1'];
                    // １０％対象合計金額
                    $tmparr['SubUseAmount_2'] = $val['SubUseAmount_2'];
                    // １０％対象消費税額
                    $tmparr['SubTaxAmount_2'] = $val['SubTaxAmount_2'];
                    // CVS収納代行会社名
                    $tmparr['Cv_ReceiptAgentName'] = $val['Cv_ReceiptAgentName'];
                    // CVS収納代行加入者名
                    $tmparr['Cv_SubscriberName'] = $val['Cv_SubscriberName'];
                    // 銀行口座 - 銀行コード
                    $tmparr['Bk_BankCode'] = $val['Bk_BankCode'];
                    // 銀行口座 - 支店コード
                    $tmparr['Bk_BranchCode'] = $val['Bk_BranchCode'];
                    // 銀行口座 - 銀行名
                    $tmparr['Bk_BankName'] = $val['Bk_BankName'];
                    // 銀行口座 - 支店名
                    $tmparr['Bk_BranchName'] = $val['Bk_BranchName'];
                    // 銀行口座 - 口座種別
                    $tmparr['Bk_DepositClass'] = $val['Bk_DepositClass'];
                    // 銀行口座 - 口座番号
                    $tmparr['Bk_AccountNumber'] = $val['Bk_AccountNumber'];
                    // 銀行口座 - 口座名義
                    $tmparr['Bk_AccountHolder'] = $val['Bk_AccountHolder'];
                    // 銀行口座 - 口座名義カナ
                    $tmparr['Bk_AccountHolderKn'] = $val['Bk_AccountHolderKn'];
                    // ゆうちょ口座 - 加入者名
                    $tmparr['Yu_SubscriberName'] = $val['Yu_SubscriberName'];
                    // ゆうちょ口座 - 口座番号
                    $tmparr['Yu_AccountNumber'] = $val['Yu_AccountNumber'];
                    // ゆうちょ口座 - 払込負担区分
                    $tmparr['Yu_ChargeClass'] = $val['Yu_ChargeClass'];
                    // ゆうちょ口座 - MT用OCRコード1
                    $tmparr['Yu_MtOcrCode1'] = $val['Yu_MtOcrCode1'];
                    // ゆうちょ口座 - MT用OCRコード2
                    $tmparr['Yu_MtOcrCode2'] = $val['Yu_MtOcrCode2'];
                    //事業者登録番号
                    $tmparr['CorporationNumber'] = "";
                    //ペイジー確認番号
                    $tmparr['ConfirmNumber'] = $val['ConfirmNumber'];
                    //ペイジーお客様番号
                    $tmparr['CustomerNumber'] = $val['CustomerNumber'];
                    //ペイジー収納機関番号
                    $tmparr['Bk_Number'] = $val['Bk_Number'];
                    //注文マイページの利用」が1：利用するの場合
                    if($val['OrderpageUseFlg'] == 1){
                        //マイページログインパスワード
                        $tmparr['MypagePassword'] = $val['MypagePassword'];
                        //マイページＵＲＬ
                        $tmparr['MypageUrl'] = $val['MypageUrl'];
                    }
                    if(isset($val['CreditLimitDate'])){
                        //クレジット手続き期限日
                        $tmparr['CreditLimitDate'] = $val['CreditLimitDate'];
                    }
                    array_push($resultarray, $tmparr);
                }
            }
        }

        $result = array( self::RES_KEY_RESULT => $resultarray );
        return $result;
    }

    /**
     * 印刷済み設定要求を実行する
     * Action：Processed
     *
     * @param array $params コマンドパラメータ
     * @return array
     */
    public function execSetPrinted($params)
    {
             // 結果返却用
            $result = array();

            // 一時的な格納
            $setdata = array();

            $mdlsp = new TableSystemProperty($this->_db);
            $reniId = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'RENIEnterpriseId');

            $mdlch = new TableClaimHistory($this->_db);

            if ($params['EnterpriseId'] == $reniId) {
                $sql = " SELECT PropId FROM T_SystemProperty WHERE Module = :Module  AND Category = :Category AND Name = :Name ";
                $prm = array(':Module' => '[DEFAULT]', ':Category' => 'systeminfo', ':Name' => 'ApiDuplicateFlg');
                $pId = $this->_db->query($sql)->execute($prm)->current()['PropId'];

                $sql = ' UPDATE T_SystemProperty SET PropValue = 1 WHERE PropId = :PropId ';
                $rows = $this->_db->query($sql)->execute(array(':PropId' => $pId))->getAffectedRows();
                if ($rows == 0) {
                    throw new \Exception("同梱API重複登録エラー");
                }
            }

            // OrderId毎に一件ずつジョブ転送要求を実行する
            foreach($params['Param'] as $key => $value) {
                $errorMsg = "";
                $errorCd = "";

                $orderId = $this->getOrderIdForNewSystem($value['OrderId']);

                try {
                    // 処理結果
                    $execResult = -1;

                    // 注文Seqの整形
                    $order = $this->getOrderData($orderId);
                    if(empty($order)) {
                        $errorMsg = "指定の注文がありません。正しい注文IDを指定してください。";
                        $errorCd = "E001";
                        throw new \Exception("注文ID指定エラー");
                    };
                    $params['Seqs'] = array('OrderSeq' => $order['OrderSeq']);

                    $exec_flg = true;
                    // 請求履歴データを取得
                    if (isset($value['SmartFlg']) && ($value['SmartFlg'] == 1)){
                        $data = $mdlch->findClaimHistory(array( 'PrintedFlg' => 1, 'ValidFlg' => 1, 'OrderSeq' => $order['OrderSeq'] ))->current();
                        if ($data['PrintedStatus'] == 9) {
                            $execResult = 3;
                            $exec_flg = false;
                        }
                    }

                    // 親クラスのメソッドを呼び出して実行
                    if ($exec_flg) {
                        $ret = parent::execSetPrinted($params);
                        $execResult = $ret[parent::RES_KEY_RESULT]['count'];
                    }
                } catch(LogicSelfBillingException $sberr) {
//
Application::getInstance()->logger->info(sprintf('%s#execSetPrinted() OrderId:%s  ERROR: %s', get_class($this), $orderId, $sberr->getMessage()));
                    array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => $execResult,
                                                self::RES_KEY_ERROR_CD => "E003",
                                                self::RES_KEY_ERROR_MSG => $sberr->getMessage(),
                                       )
                    );
                    continue;
                } catch(\Exception $e) {
// その他のエラー
Application::getInstance()->logger->info(sprintf('%s#execSetPrinted() OrderId:%s  ERROR: %s', get_class($this), $orderId, $errorMsg));
                         array_push($setdata, array(
                                                self::RES_KEY_ORDER_ID => $value['OrderId'],
                                                self::RES_KEY_EXEC_RESULT => $execResult,
                                                self::RES_KEY_ERROR_CD => $errorCd,
                                                self::RES_KEY_ERROR_MSG => $errorMsg,
                                            )
                        );
                    continue;
               }

                array_push($setdata, array(
                                        self::RES_KEY_ORDER_ID => $value['OrderId'],
                                        self::RES_KEY_EXEC_RESULT => $execResult
                                    )
                );
            }

            // 1つでも何らかの結果が出ている場合
            $result = array( self::RES_KEY_RESULT => $setdata );

            if ($params['EnterpriseId'] == $reniId) {
                $mdlsp->saveUpdate(array('PropValue' => 0), $pId);
            }

            return $result;
    }

    /**
     * 指定注文IDの注文データを取得する。
     *
     * @access protected
     * @param string $orderId 注文ID
     * @return Rowset
     */
    protected function getOrderData($orderId)
    {
        $mdo = new TableOrder($this->_db);

        $order = $mdo->findOrder(array( 'OrderId' => $orderId, 'EnterpriseId' => $this->_enterpriseId ));

        return $order->current();
    }

    /**
     * 指定注文に紐づく取りまとめ注文全体の商品明細情報を取得する
     * @param int $orderSeq
     */
    protected function getItemData($orderSeq)
    {
$sql = <<<EOQ
            SELECT  OrderItemId
                ,   OrderSeq
                ,   ItemNameKj
                ,   UnitPrice
                ,   ItemNum
                ,   SumMoney
                ,   TaxRate
                ,   DataClass
                ,   Deli_ConfirmArrivalDate AS ConfirmArrivalDate
            FROM    T_OrderItems
            WHERE   OrderSeq IN ( SELECT OrderSeq FROM T_Order WHERE Cnl_Status = 0 AND P_OrderSeq = :OrderSeq )
            AND     DataClass = 1
            ORDER BY
                    OrderSeq
                ,   OrderItemId
EOQ;
        $prm = array(
            ':OrderSeq' => $orderSeq,
        );

        $ri = $this->_db->query($sql)->execute($prm);

        return $ri;
    }

}
