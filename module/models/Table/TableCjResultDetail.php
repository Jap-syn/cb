<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CjResultDetailテーブルへのアダプタ
 */
class TableCjResultDetail
{
	protected $_name = 'T_CjResult_Detail';
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
	 * 与信審査結果詳細データを取得する(有効フラグ＝有効データに限る)
	 *
	 * @param int $seq シーケンス
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql = " SELECT * FROM T_CjResult_Detail WHERE ValidFlg = 1 AND Seq = :Seq ";

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
        $sql  = " INSERT INTO T_CjResult_Detail (CjrSeq, OrderSeq, DetectionPatternNo, DetectionPatternName, DetectionPatternScore, DetectionPatternScoreWeighting, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CjrSeq ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :DetectionPatternNo ";
        $sql .= " , :DetectionPatternName ";
        $sql .= " , :DetectionPatternScore ";
        $sql .= " , :DetectionPatternScoreWeighting ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CjrSeq' => $data['CjrSeq'],
                ':OrderSeq' => $data['OrderSeq'],
                ':DetectionPatternNo' => $data['DetectionPatternNo'],
                ':DetectionPatternName' => $data['DetectionPatternName'],
                ':DetectionPatternScore' => $data['DetectionPatternScore'],
                ':DetectionPatternScoreWeighting' => $data['DetectionPatternScoreWeighting'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq シーケンス
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

        $sql  = " UPDATE T_CjResult_Detail ";
        $sql .= " SET ";
        $sql .= "     CjrSeq = :CjrSeq ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   DetectionPatternNo = :DetectionPatternNo ";
        $sql .= " ,   DetectionPatternName = :DetectionPatternName ";
        $sql .= " ,   DetectionPatternScore = :DetectionPatternScore ";
        $sql .= " ,   DetectionPatternScoreWeighting = :DetectionPatternScoreWeighting ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':CjrSeq' => $row['CjrSeq'],
                ':OrderSeq' => $row['OrderSeq'],
                ':DetectionPatternNo' => $row['DetectionPatternNo'],
                ':DetectionPatternName' => $row['DetectionPatternName'],
                ':DetectionPatternScore' => $row['DetectionPatternScore'],
                ':DetectionPatternScoreWeighting' => $row['DetectionPatternScoreWeighting'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}


	/**
	 * 指定条件（AND）でデータを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findCjResult($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_CjResult_Detail WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY Seq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 複数条件のOrderSeqを検索する
	 *
	 * @param string $orderSeq ,で区切りられた条件
	 * @return ResultInterface
	 */
	public function orderSeqSearch($orderSeq)
	{
	    $seqs = array();
        foreach(explode(',', $orderSeq) as $seq) {
            if (!is_numeric(trim($seq))) continue;
            $seqs[] = trim($seq);
        }
        if (empty($seqs)) $seqs[] = 0;

        $phraseIn = MakeQueryValStrPhraseIn($seqs);

        $sql  = " SELECT T_CjResult.Seq, T_CjResult.OrderSeq, T_CjResult_Detail.DetectionPatternName, T_CjResult_Detail.DetectionPatternScore, T_CjResult_Detail.DetectionPatternScoreWeighting ";
        $sql .= " FROM   T_CjResult_Detail LEFT JOIN T_CjResult ON T_CjResult_Detail.CjrSeq = T_CjResult.Seq ";
        $sql .= " WHERE  T_CjResult.OrderSeq IN (" . $phraseIn . ") ";
        $sql .= " ORDER BY T_CjResult.Seq DESC ";

        return $this->_adapter->query($sql)->execute(null);
	}
}
