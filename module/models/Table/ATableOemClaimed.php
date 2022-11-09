<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_OemClaimed(OEM請求_会計)テーブルへのアダプタ
 */
class ATableOemClaimed
{
    protected $_name = 'AT_OemClaimed';
    protected $_primary = array('OemClaimedSeq');
    protected $_adapter = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * 加盟店入金履歴_会計データを取得する
     *
     * @param int $oemClaimedSeq OEM請求データシーケンス
     * @return ResultInterface
     */
    public function find($oemClaimedSeq)
    {
        $sql = " SELECT * FROM AT_OemClaimed WHERE OemClaimedSeq = :OemClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $oemClaimedSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO AT_OemClaimed (OemClaimedSeq, DailySummaryFlg) VALUES (";
        $sql .= "   :OemClaimedSeq ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $data['OemClaimedSeq'],
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $oemClaimedSeq OEM請求データシーケンス
     * @return ResultInterface
     */
    public function saveUpdate($data, $oemClaimedSeq)
    {
        $row = $this->find($oemClaimedSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_OemClaimed ";
        $sql .= " SET ";
        $sql .= "     DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " WHERE OemClaimedSeq = :OemClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $oemClaimedSeq,
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
        );

        return $stm->execute($prm);
    }
}
