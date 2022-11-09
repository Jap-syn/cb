<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Coral\Base\BaseLog;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableSite;
use models\Table\TableSystemProperty;
use models\Table\TableBatchLock;

/**
 * 配送伝票番号登録ロジック
 */
class LogicShipping {
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * ユーザID
	 *
	 * @var int ユーザID
	 */
	protected $_userId = null;

	/**
	 * ロガーインスタンス
	 *
	 * @access protected
	 * @var BaseLog
	 */
	protected $_logger;

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
	 * @return LogicShipping
	 */
	public function setLogger(BaseLog $logger = null) {
	    $this->_logger = $logger;
	    return $this;
	}

    /**
     * データベースアダプタを指定してLogicShippingの新しい
     * インスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     * @param int $userId ユーザID
     */
    public function __construct(Adapter $adapter, $userId) {
        $this->_adapter = $adapter;
        $this->_userId = $userId;
    }

    /**
     * TableOrderのインスタンスを取得する
     *
     * @return TableOrder
     */
    public function getOrderTable() {
        return new \models\Table\TableOrder($this->_adapter);
    }

    /**
     * TableEnterpriseのインスタンスを取得する
     *
     * @return TableEnterprise
     */
    public function getEnterpriseTable() {
        return new \models\Table\TableEnterprise($this->_adapter);
    }

    /**
     * TableOemSettlementFeeを取得する
     *
     * @return TableOemSettlementFee
     */
    public function getOemSettlementFeeTable() {
        return new \models\Table\TableOemSettlementFee($this->_adapter);
    }

    /**
     * TableOrderItemsを取得する
     *
     * @return TableOrderItems
     */
    public function getOrderItemsTable() {
        return new \models\Table\TableOrderItems($this->_adapter);
    }

    /**
     * TableOrderSummaryを取得する
     *
     * @return TableOrderSummary
     */
    public function getOrderSummaryTable() {
        return new \models\Table\TableOrderSummary($this->_adapter);
    }

    /**
     * TableDeliMethodを取得する
     *
     * @return TableDeliMethod
     */
    public function getDeliveryMethodMaster() {
        return new \models\Table\TableDeliMethod($this->_adapter);
    }

    /**
     * TablePayingAndSalesを取得する
     *
     * @return TablePayingAndSales
     */
    public function getPayingAndSalesTable() {
        return new \models\Table\TablePayingAndSales($this->_adapter);
    }

    /**
     * TablePrePayingAndSalesを取得する
     * @return TablePrePayingAndSales
     */
    public function getPrePayingAndSalesTable() {
        return new \models\Table\TablePrePayingAndSales($this->_adapter);
    }

    /**
     * TableBatchLockを取得する
     * @return TableBatchLock
     */
    public function getBatchLockTable(){
        return new \models\Table\TableBatchLock($this->_adapter);
    }
	/**
	 * LogicDeliveryMethodを取得する
	 *
	 * @return LogicDeliveryMethod
	 */
	public function getDeliveryMethodLogic() {
	    return new LogicDeliveryMethod($this->_adapter);
	}

    /**
     * 指定注文向けに配送伝票番号を登録する
     *
     * @param int $oseq 注文SEQ
     * @param int $deliMethodId 配送方法ID
     * @param string $journalNumber 配送伝票番号
     * @return LogicShipping このインスタンス
     */
    public function registerJournalNumber($oseq, $deliMethodId, $journalNumber) {
        $step_trans = 0;
        $this->debug(sprintf('[registerJournalNumber] method start. oseq = %s, deli-method = %s, journal-number = %s', $oseq, $deliMethodId, $journalNumber));
        try {
            // 注文情報取得
            $step_trans = 1;
            $order = $this->getOrderTable()->find($oseq)->current();
            if(!$order) {
                throw new \Exception(sprintf('cannot found order data: oseq = %s', $oseq));
            }

            // 注文更新データの基本構築
            $step_trans = 2;
            $udata = array(
                'DataStatus' => 41,
                'Cnl_CantCancelFlg' => $this->getCancelEnebled($deliMethodId) ? 0 : 1
            );

            // 注文商品の更新
            $step_trans = 3;
            $itemsTable = $this->getOrderItemsTable();
            $ri = $itemsTable->findByOrderSeq($oseq);
            foreach($ri as $orderItem) {
                $itemsTable->saveUpdate(array(
                    'Deli_DeliveryMethod' => $deliMethodId,
                    'Deli_JournalNumber' => $journalNumber,
                    'Deli_JournalIncDate' => date('Y-m-d H:i:s')
                ), $orderItem['OrderItemId']);
            }

            // 請求取りまとめ関連処理
            $step_trans = 4;
            $moHelper = new \models\Logic\MergeOrder\LogicMergeOrderHelper($this->_adapter, $oseq);
            $ccTargetStatus = $moHelper->chkCcTargetStatusByDelivery($deliMethodId);
            if($ccTargetStatus != 9) {
                $udata['CombinedClaimTargetStatus'] = $ccTargetStatus;
            }

            // 注文情報更新
            $step_trans = 5;
            $this->getOrderTable()->saveUpdate($udata, $oseq);

            // 注文情報再取得
            $step_trans = 6;
            $order = $this->getOrderTable()->find($oseq)->current();

            // 立替・売上データの作成
            $step_trans = 7;
            $ent = $this->getEnterpriseByOrderSeq($oseq);

            // キャンペーン期間中はキャンペーン情報で更新/登録する
            // 注文のサイト情報を取得する
            $sid = $order['SiteId'];
            // 詳細情報取得
            $step_trans = 8;
            $logic = new LogicCampaign($this->_adapter);
            $campaign = $logic->getCampaignInfo($ent['EnterpriseId'], $sid);

            // 取得したデータをマージする
            $ent = array_merge($ent, $campaign);
            // 請求手数料(別送)を税込み金額に変換
            $step_trans = 9;
            $mdlSysP = new TableSystemProperty($this->_adapter);
            $ent['ClaimFeeBS'] = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $ent['ClaimFeeBS']);

            $step_trans = 10;
            $savedata = $this->getPayingAndSalesTable()->newRow($oseq, $order['UseAmount'], $ent['SettlementFeeRate'], $ent['ClaimFeeBS']);
            $savedata = array_merge($savedata, array('RegistId' => $this->_userId, 'UpdateId' => $this->_userId));

            //立替精算仮締めバッジロック状態取得
            $step_trans = 11;
            $Locked = $this->getBatchLockTable()->findId(4, 1)->current()['BatchLock'];

            //立替精算仮締め処理中は「退避_立替・売上管理テーブル」に保存
            if($Locked == 0){
                $step_trans = 12;
                $seq_pas = $this->getPayingAndSalesTable()->saveNew($savedata);
            }else{
                $step_trans = 13;
                $seq_pas = $this->getPrePayingAndSalesTable()->saveNew($savedata);
            }

            // 注文サマリを更新
            $step_trans = 14;
            $this->getOrderSummaryTable()->updateSummary($oseq, $this->_userId);

            // OEM決済手数料登録
            if($order['OemId'] !== null && $order['OemId'] != 0) {
                $step_trans = 15;
                $this->getOemSettlementFeeTable()->saveOemSettlementFee($order);
            }

            // 立替精算仮締め処理中でないときのみ保存
            if($Locked == 0){
                $step_trans = 16;
                // AT_PayingAndSales登録
                $mdl_atpas = new \models\Table\ATablePayingAndSales($this->_adapter);
                $mdl_atpas->saveNew(array('Seq' => $seq_pas));
            }

            $this->debug(sprintf('[registerJournalNumber] journal-number registered. oseq = %s, journal-number = %s', $oseq, $journalNumber));
            return $this;
        } catch(\Exception $innerError) {
            $this->notice(sprintf('[registerJournalNumber] an error has occured. oseq = %s, step_trans = %s, err = %s (%s)', $oseq, $step_trans, $innerError->getMessage(), get_class($innerError)));
            throw $innerError;
        }
    }

    /**
     * 指定の注文が伝票番号自動仮登録対象事業者の注文であった場合に
     * 伝票番号の仮登録を実行する
     *
     * @param int $oseq 注文SEQ
     * @return boolean 仮登録の成否。仮登録対象外の場合もfalseを返す
     */
    public function registerTemporaryJournalNumber($oseq) {
        $this->debug(sprintf('[registerTemporaryJournalNumber] method start. oseq = %s', $oseq));
        // 注文の状態を精査 ------------------------------------------
        $order = $this->getOrderTable()->find($oseq)->current();

        // 該当注文が存在しないかキャンセル済みなら処理しない
        if(!$order || $order['Cnl_Status']) {
            $this->debug(sprintf('[registerTemporaryJournalNumber] order-data not found. oseq = %s', $oseq));
            return false;
        }

        // DataStatusが31：伝票番号入力待ちでない場合は処理しない
        if($order['DataStatus'] != 31) {
            $this->debug(sprintf('[registerTemporaryJournalNumber] data-status is exempt. oseq = %s, data-status = %s', $oseq, $order['DataStatus']));
            return false;
        }

        // サイト設定を取得 ------------------------------------------
        $ent = $this->getSiteByOrderSeq($oseq);

        // 自動仮登録が無効なら処理しない
        if(!$ent['AutoJournalIncMode']) {
            $this->debug(sprintf('[registerTemporaryJournalNumber] AutoJournalIncMode is exempt. oseq = %s', $oseq));
            return false;
        }

        // 仮伝票番号を生成 ------------------------------------------
        preg_match('/^[^\.]*\.(\d{6})/', microtime(), $buf);
        $us = $buf[1];
        $journalNumber = sprintf('TMP%06d-%s-%s', $ent['EnterpriseId'], date('Ymd-His'), $us);

        // 仮登録実行
        try {
            $this->debug(sprintf('[registerTemporaryJournalNumber] registering temporary journal-number. oseq = %s', $oseq));
            $this->registerJournalNumber($oseq, $ent['AutoJournalDeliMethodId'], $journalNumber);
        } catch(\Exception $err) {
            // 例外が発生したので失敗で終了
            $this->debug(sprintf('[registerTemporaryJournalNumber] an error has occured. oseq = %s, err = %s (%s)', $oseq, $err->getMessage(), get_class($err)));
            return false;
        }

        // 注文履歴へ登録
        $history = new CoralHistoryOrder($this->_adapter);
        $history->InsOrderHistory($oseq, 33, $this->_userId);

        $this->debug(sprintf('[registerTemporaryJournalNumber] temporary journal-number registered normally. oseq = %s', $oseq));
        return true;
    }

    /**
     * 指定の注文が請求時伝票番号自動仮登録対象事業者の注文であった場合に
     * 伝票番号の仮登録を実行する
     *
     * @param int $oseq 注文SEQ
     * @return boolean 仮登録の成否。仮登録対象外の場合もfalseを返す
     */
    public function registerClaimTemporaryJournalNumber($oseq) {
        $this->debug(sprintf('[registerTemporaryJournalNumber] method start. oseq = %s', $oseq));
        // 注文の状態を精査 ------------------------------------------
        $order = $this->getOrderTable()->find($oseq)->current();

        // 該当注文が存在しないかキャンセル済みなら処理しない
        if(!$order || $order['Cnl_Status']) {
            $this->debug(sprintf('[registerTemporaryJournalNumber] order-data not found. oseq = %s', $oseq));
            return false;
        }

        // DataStatusが31：伝票番号入力待ちでない場合は処理しない
        if($order['DataStatus'] != 31) {
            $this->debug(sprintf('[registerTemporaryJournalNumber] data-status is exempt. oseq = %s, data-status = %s', $oseq, $order['DataStatus']));
            return false;
        }


        // サイト設定を取得 ------------------------------------------
        $site = $this->getSiteByOrderSeq($oseq);

        // 請求時自動仮登録が無効なら処理しない
        if(!$site['ClaimAutoJournalIncMode']) {
            $this->debug(sprintf('[registerTemporaryJournalNumber] AutoJournalIncMode is exempt. oseq = %s', $oseq));
            return false;
        }

        // 仮伝票番号を生成 ------------------------------------------
        preg_match('/^[^\.]*\.(\d{6})/', microtime(), $buf);
        $us = $buf[1];
        $journalNumber = sprintf('TMP%06d-%s-%s', $site['EnterpriseId'], date('Ymd-His'), $us);

        // 仮登録実行
        try {
            $this->debug(sprintf('[registerTemporaryJournalNumber] registering temporary journal-number. oseq = %s', $oseq));
            $this->registerJournalNumber($oseq, $site['AutoJournalDeliMethodId'], $journalNumber);
        } catch(\Exception $err) {
            // 例外が発生したので失敗で終了
            $this->debug(sprintf('[registerTemporaryJournalNumber] an error has occured. oseq = %s, err = %s (%s)', $oseq, $err->getMessage(), get_class($err)));
            return false;
        }

        // 注文履歴へ登録
        $history = new CoralHistoryOrder($this->_adapter);
        $history->InsOrderHistory($oseq, 33, $this->_userId);

        $this->debug(sprintf('[registerTemporaryJournalNumber] temporary journal-number registered normally. oseq = %s', $oseq));
        return true;
    }

    /**
     * 指定注文を所有する事業者のデータを取得する
     *
     * @param int $oseq 注文SEQ
     * @return array 事業者データ
     */
    public function getEnterpriseByOrderSeq($oseq) {
        $order = $this->getOrderTable()->find($oseq)->current();
        if(!$order) {
            throw new \Exception(sprintf('cannot found order data: oseq = %s', $oseq));
        }
        $ent = $this->getEnterpriseTable()->find($order['EnterpriseId'])->current();
        return $ent;
    }

    /**
     * 指定配送方法で伝票登録した注文のキャンセルを許可するかを判断する
     *
     * @param int $deliMethodId 配送方法ID
     * @return boolean 指定配送方法で伝票登録された注文のキャンセルが許可されていればtrue、それ以外はfalse
     */
    public function getCancelEnebled($deliMethodId) {
        $deliTable = $this->getDeliveryMethodMaster();
        $deliMethod = $deliTable->find($deliMethodId)->current();
        if(!$deliMethod) {
            // 配送方法指定が不正な場合はキャンセル可能とする
            return true;
        }
        return ($deliMethod['EnableCancelFlg'] == 1) ? true : false;
    }

    /**
     * 指定OEM先で利用可能な配送方法を、配送方法IDをキー、配送方法名を値にした
     * 連想配列で取得する
     *
     * @param null | int $oemId OEM ID。省略時は0
     * @return array
     */
    public function getDeliMethodListByOemId($oemId = 0) {
        $result = array();
        $deliList = $this->getDeliveryMethodLogic()->getDeliMethodList($oemId);
        foreach($deliList as $row) {
            $result[$row['DeliMethodId']] = $row['DeliMethodName'];
        }
        return $result;
    }

    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($message, $priority) {
		$message = sprintf('[%s] %s', get_class($this), $message);
		if($this->_logger) {
			$this->_logger->log($priority, $message);
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
     * テスト注文時のクローズ処理
     *
     * @param int $oseq 注文SEQ
     */
	public function closeIfTestOrder($oseq) {
        $sql = " SELECT T_OrderClass FROM T_Order WHERE OrderSeq = :OrderSeq ";

        $row = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

        // テスト注文区分が[テスト注文]でない場合は直ちに戻る(以降処理不要)
        if (!($row['T_OrderClass'] == 1)){ return; }

        // クローズ更新処理 (CloseReason:5=>テスト注文クローズ)
        $sql = " UPDATE T_Order SET DataStatus = 91, CloseReason = 5, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq ";
        $this->_adapter->query($sql)->execute(
            array(':UpdateId' => $this->_userId, ':UpdateDate' => date('Y-m-d H:i:s'), ':OrderSeq' => $oseq)
        );

        return;
    }

    /**
     * 指定注文を所有するサイトのデータを取得する
     *
     * @param int $oseq 注文SEQ
     */
    private function getSiteByOrderSeq($oseq) {
        $sql = <<<EOQ
SELECT sit.*
FROM   T_Order odr
       INNER JOIN T_Site sit ON (sit.SiteId = odr.SiteId AND sit.EnterpriseId = odr.EnterpriseId)
WHERE  odr.OrderSeq = :OrderSeq
EOQ;
        return $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
    }
}
