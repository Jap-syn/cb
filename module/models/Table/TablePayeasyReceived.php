<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_PayeasyReceived(Payeasy入金データ)テーブルへのアダプタ
 */
class TablePayeasyReceived
{
    protected $_name = 'T_PayeasyReceived';
    protected $_primary = array('Seq');
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
     * Payeasy入金データを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_PayeasyReceived WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
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
        $sql  = " INSERT INTO T_PayeasyReceived (p_ver, stdate, stran, bkcode, shopid, cshopid, amount, mbtran, bktrans, tranid, ddate, tdate, rsltcd, rchksum) VALUES (";
        $sql .= "   :p_ver ";
        $sql .= " , :stdate ";
        $sql .= " , :stran ";
        $sql .= " , :bkcode ";
        $sql .= " , :shopid ";
        $sql .= " , :cshopid ";
        $sql .= " , :amount ";
        $sql .= " , :mbtran ";
        $sql .= " , :bktrans ";
        $sql .= " , :tranid ";
        $sql .= " , :ddate ";
        $sql .= " , :tdate ";
        $sql .= " , :rsltcd ";
        $sql .= " , :rchksum ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':p_ver'   => $data['p_ver'],
            ':stdate'  => $data['stdate'],
            ':stran'   => $data['stran'],
            ':bkcode'  => $data['bkcode'],
            ':shopid'  => $data['shopid'],
            ':cshopid' => $data['cshopid'],
            ':amount'  => $data['amount'],
            ':mbtran'  => $data['mbtran'],
            ':bktrans' => $data['bktrans'],
            ':tranid'  => $data['tranid'],
            ':ddate'   => $data['ddate'],
            ':tdate'   => $data['tdate'],
            ':rsltcd'  => $data['rsltcd'],
            ':rchksum' => $data['rchksum'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_PayeasyReceived ";
        $sql .= " SET ProcessedFlg = :ProcessedFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':ProcessedFlg' => $row['ProcessedFlg'],
        );

        return $stm->execute($prm);
    }
}
