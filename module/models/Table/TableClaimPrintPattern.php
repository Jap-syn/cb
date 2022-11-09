<?php

namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_ClaimPrintCheckテーブルへのアダプタ
 */
class TableClaimPrintPattern
{
    protected $_name = 'T_ClaimPrintPattern';
    protected $_primary = array('ClaimPrintPatternSeq');
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

    public function find($EnterpriseId, $SiteId, $PrintIssueCountCd)
    {
        $sql = " SELECT * FROM T_ClaimPrintPattern WHERE EnterpriseId = :EnterpriseId AND SiteId = :SiteId AND PrintIssueCountCd = :PrintIssueCountCd AND ValidFlg = 1 ORDER BY ClaimPrintPatternSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':EnterpriseId' => $EnterpriseId,
            ':SiteId' => $SiteId,
            ':PrintIssueCountCd' => $PrintIssueCountCd,
        );

        return $stm->execute($prm);
    }

    public function findPkey($ClaimPrintPatternSeq)
    {
        $sql = " SELECT * FROM T_ClaimPrintPattern WHERE ClaimPrintPatternSeq = :ClaimPrintPatternSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':ClaimPrintPatternSeq' => $ClaimPrintPatternSeq,
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
        $sql  = " INSERT INTO T_ClaimPrintPattern (EnterpriseId, SiteId, PrintIssueCountCd, PrintFormCd, PrintPatternCd, PrintTypeCd, EnclosedSpecCd, PrintIssueCd, SpPaymentCd, AdCd, EnclosedAdCd, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :SiteId ";
        $sql .= " , :PrintIssueCountCd ";
        $sql .= " , :PrintFormCd ";
        $sql .= " , :PrintPatternCd ";
        $sql .= " , :PrintTypeCd ";
        $sql .= " , :EnclosedSpecCd ";
        $sql .= " , :PrintIssueCd ";
        $sql .= " , :SpPaymentCd ";
        $sql .= " , :AdCd ";
        $sql .= " , :EnclosedAdCd ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':EnterpriseId' => $data['EnterpriseId'],
            ':SiteId' => $data['SiteId'],
            ':PrintIssueCountCd' => $data['PrintIssueCountCd'],
            ':PrintFormCd' => $data['PrintFormCd'],
            ':PrintPatternCd' => $data['PrintPatternCd'],
            ':PrintTypeCd' => $data['PrintTypeCd'],
            ':EnclosedSpecCd' => $data['EnclosedSpecCd'],
            ':PrintIssueCd' => $data['PrintIssueCd'],
            ':SpPaymentCd' => $data['SpPaymentCd'],
            ':AdCd' => $data['AdCd'],
            ':EnclosedAdCd' => $data['EnclosedAdCd'],
            ':RegistDate' => date('Y-m-d H:i:s'),
            ':RegistId' => $data['RegistId'],
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $data['RegistId'],
            ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $pkey P-Key
     * @return ResultInterface
     */
    public function saveUpdate($data, $pkey)
    {
        $row = $this->findPkey($pkey)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_ClaimPrintPattern ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   SiteId = :SiteId ";
        $sql .= " ,   PrintIssueCountCd = :PrintIssueCountCd ";
        $sql .= " ,   PrintFormCd = :PrintFormCd ";
        $sql .= " ,   PrintPatternCd = :PrintPatternCd ";
        $sql .= " ,   PrintTypeCd = :PrintTypeCd ";
        $sql .= " ,   EnclosedSpecCd = :EnclosedSpecCd ";
        $sql .= " ,   PrintIssueCd = :PrintIssueCd ";
        $sql .= " ,   SpPaymentCd = :SpPaymentCd ";
        $sql .= " ,   AdCd = :AdCd ";
        $sql .= " ,   EnclosedAdCd = :EnclosedAdCd ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE ClaimPrintPatternSeq = :ClaimPrintPatternSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':ClaimPrintPatternSeq' => $pkey,
            ':EnterpriseId' => $row['EnterpriseId'],
            ':SiteId' => $row['SiteId'],
            ':PrintIssueCountCd' => $row['PrintIssueCountCd'],
            ':PrintFormCd' => $row['PrintFormCd'],
            ':PrintPatternCd' => $row['PrintPatternCd'],
            ':PrintTypeCd' => $row['PrintTypeCd'],
            ':EnclosedSpecCd' => $row['EnclosedSpecCd'],
            ':PrintIssueCd' => $row['PrintIssueCd'],
            ':SpPaymentCd' => $row['SpPaymentCd'],
            ':AdCd' => $row['AdCd'],
            ':EnclosedAdCd' => $row['EnclosedAdCd'],
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $row['UpdateId'],
            ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
