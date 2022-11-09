<?php
namespace models\Logic\SmbcRelation;

use Zend\Db\Adapter\Adapter;
use Coral\Base\IO\BaseIOCsvReader;
use models\Logic\SmbcRelation\Receipt\LogicSmbcRelationReceiptItem;

/**
 * SMBC決済ステーション連携入金処理ロジック
 */
class LogicSmbcRelationReceipt {
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

    /**
     * 入金処理対象データの配列
     *
     * @access protected
     * @var array
     */
    protected $_receives;

    /**
     * 入金処理対象外データの配列
     *
     * @access protected
     * @var array
     */
    protected $_errors;

    /**
     * 使用するDBアダプタを指定して、LogicSmbcRelationReceiptの
     * 新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * 注文テーブルモデルを取得する
     *
     * @return TableOrder
     */
    public function getOrderTable() {
        return new \models\Table\TableOrder($this->_adapter);
    }

    /**
     * OEM請求口座テーブルモデルを取得する
     *
     * @return TableOemClaimAccountInfo
     */
    public function getClaimAccountInfoTable() {
        return new \models\Table\TableOemClaimAccountInfo($this->_adapter);
    }

	/**
	 * SMBC決済ステーション連携アカウントテーブルモデルを取得する
	 *
	 * @return TableSmbcRelationAccount
	 */
	public function getSmbcAccountTable() {
        return new \models\Table\TableSmbcRelationAccount($this->_adapter);
	}

	/**
	 * SMBC決済ステーション連携ログテーブルモデルを取得する
	 *
	 * @return TableSmbcRelationLog
	 */
	public function getSmbcRelationLogTable() {
        return new \models\Table\TableSmbcRelationLog($this->_adapter);
	}

    /**
     * 指定パスの入金履歴データを読み込んで入金処理向けにデータを展開する
     *
     * @param string $path 入金履歴データファイルのパス
     * @return array 入金処理対象データの配列
     */
    public function read($path) {
        $this->_receives = array();
        $this->_errors = array();

        $reader = new BaseIOCsvReader($path, array($this, 'readLine'));
        $reader->read();

        return $this->_receives;
    }

    /**
     * 展開済みの入金可能データを取得する
     *
     * @return array
     */
    public function getValidItems() {
        return $this->_receives;
    }

    /**
     * 展開済みの入金対象外データを取得する
     *
     * @return array
     */
    public function getErrorItems() {
        return $this->_errors;
    }

    /**
     * 入金履歴行データを処理する内部コールバック
     *
     * @access protected
     * @param array $row 入金履歴行データ
     * @param int $index 処理対象行番号（0ベース）
     * @return array 処理済み行データ
     */
    public function readLine($row, $index) {
        $handled = array(
            'index' => $index,
            'raw_data' => $row,
            'data' => null
        );
        // 行フォーマットのチェック
        if(!$this->isValidFormatRow($row)) {
            $this->_errors[] = array_merge($handled, array(
                'reason' => 'レコード形式が不正です'
            ));
            return $row;
        }

        // 入金データアイテムとして初期化
        $item = new LogicSmbcRelationReceiptItem($row);
        $handled['data'] = $item;

        // 対象決済手段であるかのチェック
        if(!$this->isValidBillMethod($item)) {
            $this->_errors[] = array_merge($handled, array(
                'reason' => sprintf("'%s'は処理できない決済手段区分です", $row[4])
            ));
            return $row;
        }

        // 決済ステーション送受信ログの抽出
        $log =
            $this->getSmbcRelationLogTable()->findByAcceptNumber(
                                                                 $item->kessaiNumber,
                                                                 \models\Table\TableSmbcRelationLog::TARGET_FUNC_REGISTER
                                                                 )->current();
        if(!$log) {
            $this->_errors[] = array_merge($handled, array(
                'reason' => '決済受付番号に該当する履歴が見つかりません'
            ));
            return $row;
        }
        $item->orderSeq = $log['OrderSeq'];

        // 注文データの抽出
        $orders = $this->getOrderTable()->find($log['OrderSeq'])->current();
        if(!$orders) {
            $this->_errors[] = array_merge($handled, array(
                'reason' => '該当する注文が見つかりません'
            ));
            return $row;
        }

        $order = $orders;
        $item->orderId = $order['OrderId'];

        // 入金方法がコンビニの場合は、OEM請求口座情報のバーコード情報から印紙代フラグを設定
        if($item->payWayType == 1) {
            $claimAcc = $this->getClaimAccountInfoTable()->find($log['ClaimAccountSeq'])->current();
            if(!$claimAcc) {
                $this->_errors[] = array_merge($handled, array(
                    'reason' => '請求口座情報が取得できません'
                ));
                return $row;
            }
            $item->stampFlag = (int)substr($claimAcc['Cv_BarcodeData'], 36, 1);
        } else {
            $item->stampFlag = 0;
        }

        // 注文の状態を確認
        if (! ($order['Cnl_Status'] == 0 && ($order['DataStatus'] == 51 || $order['DataStatus'] == 61 || ($order['DataStatus'] == 91 && $order['CloseReason'] == 1)))) {
            $this->_errors[] = array_merge($handled, array(
                'reason' => '入金待ちではありません'
            ));
            return $row;
        }

        // 決済ステーション契約コードのチェック → 注文を所有するOEM先の決済ステーションアカウントと突合
        $smbcAcc = $this->getSmbcAccountTable()->findByOemId(nvl($order['OemId'], 0))->current();
        if(!$smbcAcc || $item->shopCd != $smbcAcc['ShopCd']) {
            $this->_errors[] = array_merge($handled, array(
                'reason' => '契約コードが一致しません'
            ));
            return $row;
        }

        if(in_array($item->billMethod, array('02'))) {
            // コンビニ入金時
            if($item->acceptCode != '01') {
                $this->_errors[] = array_merge($handled, array(
                    'reason' => sprintf('種別 %s は処理対象外です', $item->acceptCode)
                ));
                return $row;
            }
        } else {
            // それ以外の処理対象入金時
            if($item->acceptCode == '0700') {
                $this->_errors[] = array_merge($handled, array(
                    'reason' => '不明入金情報です'
                ));
                return $row;
            } else
            if($item->acceptCode != '0200') {
                $this->_errors[] = array_merge($handled, array(
                    'reason' => sprintf('種別 %s は処理対象外です', $item->acceptCode)
                ));
                return $row;
            }
        }
        
        //CB_B2C_DEV-377 ☆クレカ決済後の入金を、入金エラー対象にする改修 Start
        //クレジットカードで支払った注文のチェック
        $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
        $checkCountCredit = $this->_adapter->query($sqlCredit)->execute(array(':OrderSeq' => $log['OrderSeq']))->current()['cnt'];
        if($checkCountCredit >= 1) {
            $this->_errors[] = array_merge($handled, array(
                'reason' => 'クレジットカードで支払った注文'
            ));
            return $row;
        }
        //CB_B2C_DEV-377 ☆クレカ決済後の入金を、入金エラー対象にする改修 End

        // 正常データに追加
        $this->_receives[] = $handled;

        return $row;
    }

    /**
     * 指定行データが処理可能な入金履歴データであるかを判断する
     *
     * @access protected
     * @param array $row 行データ
     * @return boolean
     */
    protected function isValidFormatRow($row) {
        $def = array(
            0 => 7,     // 項番1：契約コード 7桁固定
            3 => 2,     // 項番4：決済手段区分 2桁固定
            4 => 4,     // 項番5：決済種類コード 4桁固定
            9 => 17,    // 項番10：請求番号 17桁固定
            10 => 14    // 項番11：決済受付番号 14桁固定
        );

        foreach($def as $idx => $len) {
            if(!isset($row[$idx]) || strlen($row[$idx]) != $len) {
                // 指定位置の文字数が指定桁でない場合は適合フォーマットではない
                return false;
            }
        }

        return true;
    }

    /**
     * 指定の入金履歴データが処理対象の決済手段であるかを判断する
     *
     * @access protected
     * @param LogicSmbcRelationReceiptItem 入金履歴データ
     * @return boolean
     */
    protected function isValidBillMethod(LogicSmbcRelationReceiptItem $item) {
        $valids = array(
            '02',       // 02：コンビニエンスストア（払込票）
            '04',       // 04：ゆうちょ振替
            '06',       // 06：銀行振込
            '20'        // 20：払込票   → TODO：入金履歴では扱わない可能性があるので要確認
        );
        return in_array($item->billMethod, $valids) ? true : false;
    }
}
