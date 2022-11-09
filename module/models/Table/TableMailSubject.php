<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_MailSubject(メールサブジェクト)テーブルへのアダプタ
 */
class TableMailSubject
{
    protected $_name = 'T_MailSubject';
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
     * メールサブジェクトデータを取得する
     *
     * @param string $mailSubject メールサブジェクト
     * @return ResultInterface
     */
    public function find($mailSubject)
    {
        $sql = " SELECT * FROM T_MailSubject WHERE MailSubject = :MailSubject ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MailSubject' => $mailSubject,
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
        $sql  = " INSERT INTO T_MailSubject (MailSubject, FailAttfileFlg, FailExtfileFlg, FailNameFlg, FailAddressFlg, FailBirthFlg, ChkFlg, ListPrintFlg, RegistDate, UpdateDate, AttfileName) VALUES (";
        $sql .= "   :MailSubject ";
        $sql .= " , :FailAttfileFlg ";
        $sql .= " , :FailExtfileFlg ";
        $sql .= " , :FailNameFlg ";
        $sql .= " , :FailAddressFlg ";
        $sql .= " , :FailBirthFlg ";
        $sql .= " , :ChkFlg ";
        $sql .= " , :ListPrintFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :AttfileName ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MailSubject' => $data['MailSubject'],
                ':FailAttfileFlg' => $data['FailAttfileFlg'],
                ':FailExtfileFlg' => $data['FailExtfileFlg'],
                ':FailNameFlg' => $data['FailNameFlg'],
                ':FailAddressFlg' => $data['FailAddressFlg'],
                ':FailBirthFlg' => $data['FailBirthFlg'],
                ':ChkFlg' => $data['ChkFlg'],
                ':ListPrintFlg' => $data['ListPrintFlg'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':AttfileName' => $data['AttfileName'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param string $mailSubject メールサブジェクト
     * @return ResultInterface
     */
    public function saveUpdate($data, $mailSubject)
    {
        $row = $this->find($mailSubject)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_MailSubject ";
        $sql .= " SET ";
        $sql .= "     FailAttfileFlg = :FailAttfileFlg ";
        $sql .= " ,   FailExtfileFlg = :FailExtfileFlg ";
        $sql .= " ,   FailNameFlg = :FailNameFlg ";
        $sql .= " ,   FailAddressFlg = :FailAddressFlg ";
        $sql .= " ,   FailBirthFlg = :FailBirthFlg ";
        $sql .= " ,   ChkFlg = :ChkFlg ";
        $sql .= " ,   ListPrintFlg = :ListPrintFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   AttfileName = :AttfileName ";
        $sql .= " WHERE MailSubject = :MailSubject ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MailSubject' => $mailSubject,
                ':FailAttfileFlg' => $row['FailAttfileFlg'],
                ':FailExtfileFlg' => $row['FailExtfileFlg'],
                ':FailNameFlg' => $row['FailNameFlg'],
                ':FailAddressFlg' => $row['FailAddressFlg'],
                ':FailBirthFlg' => $row['FailBirthFlg'],
                ':ChkFlg' => $row['ChkFlg'],
                ':ListPrintFlg' => $row['ListPrintFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':AttfileName' => $row['AttfileName'],
        );

        return $stm->execute($prm);
    }

    /**
     * 免許証チェック対象データを取得する
     *
     * @return ResultInterface
     */
    public function getLicenseCheckData()
    {
        $sql = <<<EOQ
SELECT ms.MailSubject
,      ms.FailAttfileFlg
,      ms.FailExtfileFlg
,      ms.AttfileName
,      mc.CustomerId
,      mc.NameSeiKj
,      mc.NameMeiKj
,      mc.Birthday
,      mc.PrefectureName
,      mc.Address
,      mc.Building
FROM   T_MailSubject ms
       LEFT OUTER JOIN T_MypageCustomer mc ON mc.MailSubject = ms.MailSubject
WHERE  ms.ChkFlg = 0
ORDER BY ms.MailSubject
EOQ;

        $ri = $this->_adapter->query($sql)->execute(null);

        return $ri;
    }

    /**
     * 免許証チェックエラーデータを取得する
     *
     * @param $cond 検索条件
     * @return ResultInterface
     */
    public function getLicenseCheckError($cond)
    {
        $sql = <<<EOQ
SELECT MailSubject
,      FailAttfileFlg
,      FailExtfileFlg
,      FailNameFlg
,      FailAddressFlg
,      FailBirthFlg
FROM   T_MailSubject
WHERE  ChkFlg = 1
AND    (   FailAttfileFlg = 1
        OR FailExtfileFlg = 1
        OR FailNameFlg = 1
        OR FailAddressFlg = 1
        OR FailBirthFlg = 1
       )
EOQ;
        $prm = array();

        // 登録日付FROM
        if (isset($cond['RegistDateFrom']) && strlen($cond['RegistDateFrom']) > 0) {
            // パラメータでY-m-d H:i:sで渡されている前提
            $sql .= " AND RegistDate >= :RegistDateFrom ";
            $prm[':RegistDateFrom'] = $cond['RegistDateFrom'];
        }

        // 登録日付TO
        if (isset($cond['RegistDateTo']) && strlen($cond['RegistDateTo']) > 0) {
            // パラメータでY-m-d H:i:sで渡されている前提
            $sql .= " AND RegistDate < :RegistDateTo ";
            $prm[':RegistDateTo'] = $cond['RegistDateTo'];
        }

        // エラーリスト印刷済フラグ
        if (isset($cond['ListPrintFlg']) && strlen($cond['ListPrintFlg']) > 0 && $cond['ListPrintFlg'] == "0") {
            $sql .= " AND ListPrintFlg = 0 ";
        }

        // メールサブジェクト指定
        if (isset($cond['MailSubjectList']) && strlen($cond['MailSubjectList']) > 0) {
            // カンマ区切りで格納されているため、分解してIN句に指定
            $mailSubjectList = explode(',', $cond['MailSubjectList']);

            if (!empty($mailSubjectList)) {
                $sql .= " AND MailSubject IN ( ";
                $i = 0;
                foreach ($mailSubjectList as $mailSubject) {
                    if ($i > 0) {
                        $sql .= ',';
                    }

                    $sql .= ':MailSubject' . $i;

                    $prm[':MailSubject' . $i] = $mailSubject;

                    $i++;
                }
                $sql .= " ) ";
            }
        }

        $sql .= " ORDER BY MailSubject ";

        $ri = $this->_adapter->query($sql)->execute($prm);

        return $ri;
    }
}
