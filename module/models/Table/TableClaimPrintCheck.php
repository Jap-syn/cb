<?php

namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_ClaimPrintCheckテーブルへのアダプタ
 */
class TableClaimPrintCheck
{
    protected $_name = 'M_ClaimPrintCheck';
    protected $_primary = array('ClaimPrintCheckSeq');
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

    public function find($printFormCd, $printTypeCd, $printIssueCd, $printIssueCountCd)
    {
        $sql = " SELECT * FROM M_ClaimPrintCheck WHERE PrintFormCd = :PrintFormCd AND PrintTypeCd = :PrintTypeCd AND PrintIssueCd = :PrintIssueCd AND PrintIssueCountCd = :PrintIssueCountCd AND ValidFlg = 1 ORDER BY ClaimPrintCheckSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':PrintFormCd' => $printFormCd,
            ':PrintTypeCd' => $printTypeCd,
            ':PrintIssueCd' => $printIssueCd,
            ':PrintIssueCountCd' => $printIssueCountCd,
        );

        return $stm->execute($prm);
    }
}
