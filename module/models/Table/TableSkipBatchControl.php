<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SkipBatchControl(スキップ対象者リスト作成バッチ管理)テーブルへのアダプタ
 */
class TableSkipBatchControl
{
    protected $_name = 'T_SkipBatchControl';
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
     * 請求バッチ管理を取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_SkipBatchControl WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_SkipBatchControl (ExecDate, TargetYears) VALUES (";
        $sql .= "   :ExecDate ";
        $sql .= " , :TargetYears ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ExecDate' => $data['ExecDate'],
                ':TargetYears' => $data['TargetYears'],
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

        $sql  = " UPDATE T_SkipBatchControl ";
        $sql .= " SET ";
        $sql .= "     ExecDate = :ExecDate ";
        $sql .= " ,   TargetYears = :TargetYears ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':ExecDate' => $row['ExecDate'],
                ':TargetYears' => $row['TargetYears'],
        );

        return $stm->execute($prm);
    }
}
