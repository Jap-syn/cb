<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_AgencySite(代理店サイト関連マスター)テーブルへのアダプタ
 */
class TableAgencySite
{
    protected $_name = 'M_AgencySite';
    protected $_primary = array ('AgencyId', 'SiteId');
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
     * 代理店サイト関連マスターデータを取得する
     *
     * @param int $agencyId 代理店ID
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function find($agencyId, $siteId)
    {
        $sql = " SELECT * FROM M_AgencySite WHERE AgencyId = :AgencyId AND SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $agencyId,
                ':SiteId' => $siteId,
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
        $sql  = " INSERT INTO M_AgencySite (AgencyId, SiteId, AgencyFeeRate, AgencyDivideFeeRate, EnterpriseId, MonthlyFee, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :AgencyId ";
        $sql .= " , :SiteId ";
        $sql .= " , :AgencyFeeRate ";
        $sql .= " , :AgencyDivideFeeRate ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $data['AgencyId'],
                ':SiteId' => $data['SiteId'],
                ':AgencyFeeRate' => $data['AgencyFeeRate'],
                ':AgencyDivideFeeRate' => $data['AgencyDivideFeeRate'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':MonthlyFee' => isset($data['MonthlyFee']) ? $data['MonthlyFee'] : 0,
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
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function saveUpdate($data, $agencyId, $siteId)
    {
        $row = $this->find($agencyId, $siteId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_AgencySite ";
        $sql .= " SET ";
        $sql .= "     AgencyFeeRate = :AgencyFeeRate ";
        $sql .= " ,   AgencyDivideFeeRate = :AgencyDivideFeeRate ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE AgencyId = :AgencyId ";
        $sql .= " AND   SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $agencyId,
                ':SiteId' => $siteId,
                ':AgencyFeeRate' => $row['AgencyFeeRate'],
                ':AgencyDivideFeeRate' => $row['AgencyDivideFeeRate'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたサイトIDに紐づくレコードを削除する
     *
     * @param int $agencyId 代理店ID
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function delete($agencyId, $siteId)
    {
        $sql  = " DELETE FROM M_AgencySite WHERE AgencyId = :AgencyId AND SiteId = :SiteId ";

        return $this->_adapter->query($sql)->execute(array( ':AgencyId' => $agencyId, ':SiteId' => $siteId ));
    }
}
