<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_Order(注文_会計)テーブルへのアダプタ
 */
class TableOrderNotClose
{
    protected $_name = 'T_OrderNotClose';
    protected $_primary = array('OrderSeq');
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
     * 注文_会計データを取得する
     *
     * @param int $orderSeq 注文SEQ
     * @return ResultInterface
     */
    public function find($orderSeq)
    {
        $sql = " SELECT * FROM AT_Order WHERE OrderSeq = :OrderSeq ";

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
        $sql  = " INSERT INTO T_OrderNotClose (OrderSeq, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq'   => $data['OrderSeq'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId'   => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId'   => $data['UpdateId'],
                ':ValidFlg'   => isset($data['ValidFlg']) ? $data['ValidFlg'] : "1",
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $orderSeq 注文SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $orderSeq)
    {
        $row = $this->find($orderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OrderNotClose ";
        $sql .= " SET ";
        $sql .= "   RegistDate = :RegistDate ";
        $sql .= " , RegistId   = :RegistId ";
        $sql .= " , UpdateDate = :UpdateDate ";
        $sql .= " , UpdateId   = :UpdateId ";
        $sql .= " , ValidFlg   = :ValidFlg ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $prm = array(
                ':OrderSeq' => $orderSeq,
                ':OrderSeq'   => $row['OrderSeq'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId'   => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId'   => $row['UpdateId'],
                ':ValidFlg'   => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 管理顧客番号より過去2年間の注文を取得する
     *
     * @param string $regUnitingAddress 購入者.正規化された住所
     * @param string $regPhone 購入者.正規化された電話番号
     */
    public function getPastNotCloseOrderSeqs($regUnitingAddress, $regPhone, $siteId) {
        $sql  = " SELECT O.OrderSeq ";
        $sql .= " FROM T_OrderNotClose O ";
        $sql .= " INNER JOIN T_Customer C ON C.OrderSeq = O.OrderSeq ";
        $sql .= " INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq ";
        $sql .= " INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId ";
        $sql .= " INNER JOIN T_Order AS O2 ON O2.OrderSeq = O.OrderSeq ";
        $sql .= " INNER JOIN T_Site AS S ON S.SiteId = O2.SiteId";
        $sql .= " INNER JOIN T_Enterprise AS E ON E.EnterpriseId = S.EnterpriseId";
        $sql .= " WHERE  1 = 1 ";
        // 【正規化結合住所と一致する】または【正規化電話番号と一致する】
        $sql .= " AND (MC.RegUnitingAddress = :RegUnitingAddress OR MC.RegPhone = :RegPhone) ";
        // 実行日（システム日付）の２年前以降に作成されている
        $sql .= " AND O.RegistDate >= :RegistDate ";
        // 【請求代行プランを利用しない】または【他サイトを対象外にしない】または【与信対象の注文を登録したサイト】
        $sql .= " AND (E.BillingAgentFlg = 0 OR S.OtherSitesAuthCheckFlg = 0 OR S.SiteId = :SiteId) ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RegUnitingAddress' => $regUnitingAddress,
                ':RegPhone' => $regPhone,
                ':RegistDate' => date("Y-m-d",strtotime("-2 year")),
                ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
    }

}
