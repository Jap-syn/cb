<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_Agency(代理店マスター)テーブルへのアダプタ
 */
class TableAgency
{
    protected $_name = 'M_Agency';
    protected $_primary = array('AgencyId');
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
     * 代理店マスターデータを取得する
     *
     * @param int $agencyId 代理店ID
     * @return ResultInterface
     */
    public function find($agencyId)
    {
        $sql = " SELECT * FROM M_Agency WHERE AgencyId = :AgencyId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $agencyId,
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
        $sql  = " INSERT INTO M_Agency (OemId, ApplicationDate, AgencyNameKj, AgencyNameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, RepNameKj, RepNameKn, Phone, Fax, Salesman, FfName, FfCode, BranchName, FfBranchCode, AccountNumber, FfAccountClass, AccountHolder, ChargeClass, TransferFeeClass, FeePaymentThreshold, FeeUnpaidBalance, Note, ExaminationResult, Comment, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :ApplicationDate ";
        $sql .= " , :AgencyNameKj ";
        $sql .= " , :AgencyNameKn ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :City ";
        $sql .= " , :Town ";
        $sql .= " , :Building ";
        $sql .= " , :RepNameKj ";
        $sql .= " , :RepNameKn ";
        $sql .= " , :Phone ";
        $sql .= " , :Fax ";
        $sql .= " , :Salesman ";
        $sql .= " , :FfName ";
        $sql .= " , :FfCode ";
        $sql .= " , :BranchName ";
        $sql .= " , :FfBranchCode ";
        $sql .= " , :AccountNumber ";
        $sql .= " , :FfAccountClass ";
        $sql .= " , :AccountHolder ";
        $sql .= " , :ChargeClass ";
        $sql .= " , :TransferFeeClass ";
        $sql .= " , :FeePaymentThreshold ";
        $sql .= " , :FeeUnpaidBalance ";
        $sql .= " , :Note ";
        $sql .= " , :ExaminationResult ";
        $sql .= " , :Comment ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':ApplicationDate' => $data['ApplicationDate'],
                ':AgencyNameKj' => $data['AgencyNameKj'],
                ':AgencyNameKn' => $data['AgencyNameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':RepNameKj' => $data['RepNameKj'],
                ':RepNameKn' => $data['RepNameKn'],
                ':Phone' => $data['Phone'],
                ':Fax' => $data['Fax'],
                ':Salesman' => $data['Salesman'],
                ':FfName' => $data['FfName'],
                ':FfCode' => $data['FfCode'],
                ':BranchName' => $data['BranchName'],
                ':FfBranchCode' => $data['FfBranchCode'],
                ':AccountNumber' => $data['AccountNumber'],
                ':FfAccountClass' => $data['FfAccountClass'],
                ':AccountHolder' => $data['AccountHolder'],
                ':ChargeClass' => $data['ChargeClass'],
                ':TransferFeeClass' => $data['TransferFeeClass'],
                ':FeePaymentThreshold' => $data['FeePaymentThreshold'],
                ':FeeUnpaidBalance' => $data['FeeUnpaidBalance'],
                ':Note' => $data['Note'],
                ':ExaminationResult' => $data['ExaminationResult'],
                ':Comment' => $data['Comment'],
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
     * @param int $agencyId 代理店ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $agencyId)
    {
        $row = $this->find($agencyId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_Agency ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   ApplicationDate = :ApplicationDate ";
        $sql .= " ,   AgencyNameKj = :AgencyNameKj ";
        $sql .= " ,   AgencyNameKn = :AgencyNameKn ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   City = :City ";
        $sql .= " ,   Town = :Town ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   RepNameKj = :RepNameKj ";
        $sql .= " ,   RepNameKn = :RepNameKn ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   Fax = :Fax ";
        $sql .= " ,   Salesman = :Salesman ";
        $sql .= " ,   FfName = :FfName ";
        $sql .= " ,   FfCode = :FfCode ";
        $sql .= " ,   BranchName = :BranchName ";
        $sql .= " ,   FfBranchCode = :FfBranchCode ";
        $sql .= " ,   AccountNumber = :AccountNumber ";
        $sql .= " ,   FfAccountClass = :FfAccountClass ";
        $sql .= " ,   AccountHolder = :AccountHolder ";
        $sql .= " ,   ChargeClass = :ChargeClass ";
        $sql .= " ,   TransferFeeClass = :TransferFeeClass ";
        $sql .= " ,   FeePaymentThreshold = :FeePaymentThreshold ";
        $sql .= " ,   FeeUnpaidBalance = :FeeUnpaidBalance ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   ExaminationResult = :ExaminationResult ";
        $sql .= " ,   Comment = :Comment ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE AgencyId = :AgencyId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $agencyId,
                ':OemId' => $row['OemId'],
                ':ApplicationDate' => $row['ApplicationDate'],
                ':AgencyNameKj' => $row['AgencyNameKj'],
                ':AgencyNameKn' => $row['AgencyNameKn'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':City' => $row['City'],
                ':Town' => $row['Town'],
                ':Building' => $row['Building'],
                ':RepNameKj' => $row['RepNameKj'],
                ':RepNameKn' => $row['RepNameKn'],
                ':Phone' => $row['Phone'],
                ':Fax' => $row['Fax'],
                ':Salesman' => $row['Salesman'],
                ':FfName' => $row['FfName'],
                ':FfCode' => $row['FfCode'],
                ':BranchName' => $row['BranchName'],
                ':FfBranchCode' => $row['FfBranchCode'],
                ':AccountNumber' => $row['AccountNumber'],
                ':FfAccountClass' => $row['FfAccountClass'],
                ':AccountHolder' => $row['AccountHolder'],
                ':ChargeClass' => $row['ChargeClass'],
                ':TransferFeeClass' => $row['TransferFeeClass'],
                ':FeePaymentThreshold' => $row['FeePaymentThreshold'],
                ':FeeUnpaidBalance' => $row['FeeUnpaidBalance'],
                ':Note' => $row['Note'],
                ':ExaminationResult' => $row['ExaminationResult'],
                ':Comment' => $row['Comment'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 全ての代理店マスターデータを取得する
     *
     * @return ResultInterface
     */
    public function getAll()
    {
        $sql = " SELECT * FROM M_Agency ORDER BY OemId, AgencyId ";
        return $this->_adapter->query($sql)->execute(null);
    }
}
