<?php
namespace models\Logic\SmbcRelation\Service;

use Coral\Base\BaseGeneralUtils;
use models\Table\TableSmbcRelationLog;
use models\Table\TableOrderItems;

/**
 * SMBC決済ステーションと連携し、請求情報登録を行うサービスクラス
 */
class LogicSmbcRelationServiceRegister extends LogicSmbcRelationServiceAbstract {
    /** オプションデフォルト定数：決済ステーション請求情報登録インターフェイスのエンドポイントパス @var string */
    const DEFAULT_SERVICE_INTERFACE_PATH = 'sf/at/ksuketsukeinforeg/uketsukeInfoRegInit.do';

    /**
     * 対象の決済ステーション機能を指定するための識別コードを取得する
     *
     * @return int 機能識別コード
     */
    public function getTargetFunctionCode() {
        return TableSmbcRelationLog::TARGET_FUNC_REGISTER;
    }

    /**
     * 指定された請求履歴のデータを基に、SMBC決済ステーション向け請求情報を構築する
     *
     * @access protected
     * @param int $claimHistroySeq 請求履歴SEQ
     * @return array 請求情報登録用データ
     */
    protected function buildSendParams($claimHistorySeq) {
        // 基本的なデータを親の実装から取得
        $data = parent::buildSendParams($claimHistorySeq);

        $oseq = (int)$data['OrderSeq'];

        // 住所を1～5に分割
        $data = $this->splitAddresses($data);

        // 対象フィールドを全角変換しCP932で指定バイト以内になるよう切り詰める
        $confs = array(
            array(
                'key' => 'bill_name',
                'len' => 60,
                'filter' => self::FILTER_NARROW_TO_WIDE
            ),
            array(
                'key' => 'bill_zip',
                'filter' => self::FILTER_POSTAL_CODE
            ),
            array(
                'key' => 'seikyuu_name',
                'len' => 100,
                'filter' => self::FILTER_NARROW_TO_WIDE
            ),
            array(
                'key' => 'seikyuu_kana',
                'len' => 48,
                'filter' => self::FILTER_KANA_SPECIAL
            )
        );
        foreach($confs as $conf) {
            $key = $conf['key'];
            if(!isset($data[$key])) continue;

            $len = isset($conf['len']) ? $conf['len'] : null;
            $filter = isset($conf['filter']) ? $conf['filter'] : null;

            $val = $data[$key];
            if($filter !== null) {
                $val = $this->applyFieldValueFilter($val, $filter);
            }
            if($len !== null) {
                $splitted = $this->splitAndTrimString($val, $len);
                $val = $splitted[0];
            }
            $data[$key] = $val;
        }

        // 注文商品情報を補完する
        $data = $this->fixOrderItems($data, $oseq);

        return $data;
    }

    /**
     * 指定の住所文字列をCP932勘定で50バイトづつ、最大5パートに分割する
     *
     * @access protected
     * @param array $data 請求情報送信用データ
     * @return array $dataの'bill_adr_[1～5]'に住所文字列を分割格納した請求情報送信用データ
     */
    protected function splitAddresses(array $data) {
        // 住所文字列を全角変換し、前後の空白を除去
        $addr = f_trim(BaseGeneralUtils::convertNarrowToWide($data['bill_adr_1']));

        // 住所を50バイトづつに分割
        $addrs = $this->splitAndTrimString($addr, 50);

        // 分割結果を最大5パート使ってbill_adr_*に割り当てる
        for($i = 0; $i < 5; $i++) {
            $key = sprintf('bill_adr_%d', ($i + 1));
            if(isset($addrs[$i])) {
                $data[$key] = $addrs[$i];
            } else {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * 指定の請求情報用データに商品明細情報を補完する
     *
     * @access protected
     * @param array $data 請求情報用データ
     * @param int $oseq 注文SEQ
     * @return array
     */
    protected function fixOrderItems(array $data, $oseq) {

        $items_table = new TableOrderItems($this->_adapter);

        $sql = <<<EOQ
SELECT  i.*
FROM    T_OrderItems i
        INNER JOIN T_Order o ON (o.OrderSeq = i.OrderSeq)
WHERE   o.P_OrderSeq = :OrderSeq
AND     i.DataClass = 1
AND     i.ValidFlg = 1
ORDER BY
        i.OrderItemId ASC
EOQ;
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq));

        $items = array();
        $limit_index = 19;
        $limit_index = 0;
        $i = 0;
        foreach ($ri as $row) {

            $names = $this->splitAndTrimString($row['ItemNameKj'], 100);
            $unit_price = (int)$row['UnitPrice'];
            $quantity = (int)$row['ItemNum'];
            $sum = (int)$row['SumMoney'];

            if($unit_price < 0) $unit_price = 0;
            if($quantity < 0) $quantity = 0;
            if($sum < 0) $sum = 0;

            if($i > $limit_index) {
                // インデックス上限以降は上限品目目に集約する
                // → 上限以降を出力しない仕様に変更（2014.11.4 eda）
                break;
                $items[$limit_index]['goods_name'] = sprintf('その他 %d 点', $i - $limit_index);
                $items[$limit_index]['unit_price'] = ($items[$limit_index]['unit_price'] * $items[$limit_index]['quantity']) + (int)$sum;
                $items[$limit_index]['quantity'] = 1;
            } else {
                $items[] = array(
                        'goods_name' => $names[0],
                        'unit_price' => $unit_price,
                        'quantity' => $quantity
                );
            }

            $i++;
        }

        // 請求情報に埋め込む
        foreach($items as $i => $item) {
            foreach($item as $key => $value) {
                $data[sprintf('%s_%d', $key, ($i + 1))] = $value;
            }
        }

        return $data;
    }

}
