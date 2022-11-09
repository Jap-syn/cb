<?php
namespace models\Table;

use Coral\Base\BaseGeneralUtils;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SBPaymentSendResultHistoryテーブルへのアダプタ
 */
class TableSBPaymentSendResultHistory
{
    protected $_name = 'T_SBPaymentSendResultHistory';
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
     * データを取得する
     *
     * @param int $seq Seq（プライマリキー）
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_SBPaymentSendResultHistory WHERE seq = :seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * データを取得する
     *
     * @param int $orderSeq 注文Seq
     * @return ResultInterface
     */
    public function findOrderSeq($orderSeq)
    {
        $sql = " SELECT * FROM T_SBPaymentSendResultHistory WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
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
        $sql  = " INSERT INTO T_SBPaymentSendResultHistory (OrderSeq, OrderId, ResResult, ResSpsTransactionId, ResProcessDate, ResErrCode, ResDate, ErrorMessage, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OrderId ";
        $sql .= " , :ResResult ";
        $sql .= " , :ResSpsTransactionId ";
        $sql .= " , :ResProcessDate ";
        $sql .= " , :ResErrCode ";
        $sql .= " , :ResDate ";
        $sql .= " , :ErrorMessage ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq'   => $data['OrderSeq'],
                ':OrderId'    => $data['OrderId'],
                ':ResResult'  => $data['ResResult'],
                ':ResSpsTransactionId' => $data['ResSpsTransactionId'],
                ':ResProcessDate' => $data['ResProcessDate'],
                ':ResErrCode' => $data['ResErrCode'],
                ':ResDate'    => $data['ResDate'],
                ':ErrorMessage' => $data['ErrorMessage'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId'   => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId'   => $data['UpdateId'],
                ':ValidFlg'   => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $codeId コード識別ID
     * @param int $keyCode KEYコード
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

        $sql  = " UPDATE T_SBPaymentSendResultHistory ";
        $sql .= " SET ";
        $sql .= "   OrderSeq = :OrderSeq ";
        $sql .= " , OrderId = :OrderId ";
        $sql .= " , ResResult = :ResResult ";
        $sql .= " , ResSpsTransactionId = :ResSpsTransactionId ";
        $sql .= " , ResProcessDate = :ResProcessDate ";
        $sql .= " , ResErrCode = :ResErrCode ";
        $sql .= " , ResDate = :ResDate ";
        $sql .= " , ErrorMessage = :ErrorMessage ";
        $sql .= " , RegistDate = :RegistDate ";
        $sql .= " , RegistId = :RegistId ";
        $sql .= " , UpdateDate = :UpdateDate ";
        $sql .= " , UpdateId = :UpdateId ";
        $sql .= " , ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq'        => $seq,
                ':OrderSeq'   => $data['OrderSeq'],
                ':OrderId'    => $data['OrderId'],
                ':ResResult'  => $data['ResResult'],
                ':ResSpsTransactionId' => $data['ResSpsTransactionId'],
                ':ResProcessDate' => $data['ResProcessDate'],
                ':ResErrCode' => $data['ResErrCode'],
                ':ResDate'    => $data['ResDate'],
                ':ErrorMessage'    => $data['ErrorMessage'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId'   => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId'   => $data['UpdateId'],
                ':ValidFlg'   => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        return $stm->execute($prm);
    }

    /**
     * すべてのデータを取得する
     *
     * @return ResultInterface
     */
    public function getAll()
    {
        $sql = " SELECT * FROM T_SBPaymentSendResultHistory ORDER BY Seq, OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * すべてのデータを取得する
     *
     * @return ResultInterface
     */
    public function getNgAll()
    {
        $sql = " SELECT * FROM T_SBPaymentSendResultHistory WHERE ResResult = 'NG' ORDER BY Seq, OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * すべてのデータを取得する
     *
     * @return ResultInterface
     */
    public function getNgRegistDate($RegistDate)
    {
        // 発生日時
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'RegistDate',
            BaseGeneralUtils::convertWideToNarrow($RegistDate),
            BaseGeneralUtils::convertWideToNarrow($RegistDate)
        );

        $sql  = "SELECT *";
        $sql .= " FROM T_SBPaymentSendResultHistory";
        $sql .= " WHERE ResResult = 'NG'";
        $sql .= " AND ". $wRegistDate;
        $sql .= " ORDER BY";
        $sql .= " RegistDate";
        $sql .= ", OrderSeq ";

        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * すべてのデータを取得する
     *
     * @return ResultInterface
     */
    public function getNgRegistDateBetween($RegistDateF, $RegistDateT)
    {
        // 発生日時
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'RegistDate',
            BaseGeneralUtils::convertWideToNarrow($RegistDateF),
            BaseGeneralUtils::convertWideToNarrow($RegistDateT)
        );

        $sql  = "SELECT *";
        $sql .= " FROM T_SBPaymentSendResultHistory";
        $sql .= " WHERE ResResult = 'NG'";
        $sql .= " AND ". $wRegistDate;
        $sql .= " ORDER BY";
        $sql .= " RegistDate";
        $sql .= ", OrderSeq ";

        return $this->_adapter->query($sql)->execute(null);
    }

}
