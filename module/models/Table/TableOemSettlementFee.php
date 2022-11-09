<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseGeneralUtils;
use models\Logic\LogicCampaign;

/**
 * T_OemSettlementFeeテーブルへのアダプタ
 */
class TableOemSettlementFee
{
	protected $_name = 'T_OemSettlementFee';
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
     * OEM決済手数料を登録
     *
     * @param array $orderData 注文データ
     * @return プライマリキーのバリュー
     *
     */
    public function saveOemSettlementFee($orderData)
    {
        $entMdl = new TableEnterprise($this->_adapter);
        $entData = $entMdl->findEnterprise2($orderData['EnterpriseId'])->current();

        $siteMdl = new TableSite($this->_adapter);
        $siteData = $siteMdl->findSite($orderData['SiteId'])->current();

        $entData['OemSettlementFeeRate'] = $siteData['OemSettlementFeeRate'];

        // キャンペーン期間中はキャンペーン情報で更新する
        $logic = new LogicCampaign($this->_adapter);
        $campaign = $logic->getCampaignInfo($orderData['EnterpriseId'], $orderData['SiteId']);
        // 取得した配列をマージする
        $entData = array_merge($entData, $campaign);

        // 2015/10/14 Y.Suzuki Add 会計対応 Stt
        // マスタの情報は[税抜]なので、消費税を算出後、足しこむ。
        $mdlsys = new TableSystemProperty($this->_adapter);
        $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);
        $oemSettlementFeeRate = ((float)$entData['OemSettlementFeeRate'] + ((float)$entData['OemSettlementFeeRate'] * $taxRate));
        $entData['OemSettlementFeeRate'] = (floor($oemSettlementFeeRate * 1000) / 1000);        // 小数点以下第4位を切り捨て
        // 2015/10/14 Y.Suzuki Add 会計対応 End

        $data = array();
        $data['OrderSeq']             = $orderData['OrderSeq'];
        $data['OemId']                = $orderData['OemId'];
        $data['OccDate']              = date('Y-m-d');
        $data['OccPlan']              = $entData['Plan'];
        $data['UseAmount']            = $orderData['UseAmount'];
        $data['AppSettlementFeeRate'] = $entData['OemSettlementFeeRate'];
        $data['SettlementFee']        = floor(strval($entData['OemSettlementFeeRate'] * $orderData['UseAmount'] / 100));
        $data['AddUpFlg']             = 0;
        $data['AddUpFixedMonth']      = null;
        $data['OemClaimedSeq']        = null;
        $data['CancelFlg']            = $orderData['Cnl_Status'];
        $data['RegistId']             = $orderData['RegistId'];
        $data['UpdateId']             = $orderData['UpdateId'];

        return $this->saveNew($data);
    }

    /**
     * OEM決済手数料を更新
     *
     * @param array $orderData 注文データ
     * @return プライマリキーのバリュー
     *
     */
    public function saveUpdateOemSettlementFee($orderData)
    {
        $entMdl = new TableEnterprise($this->_adapter);
        $entData = $entMdl->findEnterprise2($orderData['EnterpriseId'])->current();

        $siteMdl = new TableSite($this->_adapter);
        $siteData = $siteMdl->findSite($orderData['SiteId'])->current();

        $entData['OemSettlementFeeRate'] = $siteData['OemSettlementFeeRate'];

        // キャンペーン期間中はキャンペーン情報で更新する
        $logic = new LogicCampaign($this->_adapter);
        $campaign = $logic->getCampaignInfo($orderData['EnterpriseId'], $orderData['SiteId']);
        // 取得した配列をマージする
        $entData = array_merge($entData, $campaign);

        // マスタの情報は[税抜]なので、消費税を算出後、足しこむ。
        $mdlsys = new TableSystemProperty($this->_adapter);
        $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);
        $oemSettlementFeeRate = ((float)$entData['OemSettlementFeeRate'] + ((float)$entData['OemSettlementFeeRate'] * $taxRate));
        $entData['OemSettlementFeeRate'] = (floor($oemSettlementFeeRate * 1000) / 1000);        // 小数点以下第4位を切り捨て

        $osfData = $this->findOrder($orderData['OrderSeq'], true)->current();

        $data = array();
        $data['OccDate']              = date('Y-m-d');
        $data['OccPlan']              = $entData['Plan'];
        $data['UseAmount']            = $orderData['UseAmount'];
        $data['AppSettlementFeeRate'] = $entData['OemSettlementFeeRate'];
        $data['SettlementFee']        = floor(strval($entData['OemSettlementFeeRate'] * $orderData['UseAmount'] / 100));
        $data['AddUpFlg']             = 0;
        $data['AddUpFixedMonth']      = null;
        $data['OemClaimedSeq']        = null;
        $data['CancelFlg']            = $orderData['Cnl_Status'];
        $data['UpdateId']             = $orderData['UpdateId'];

        return $this->saveUpdate($data, $osfData['Seq']);
    }

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_OemSettlementFee (OrderSeq, OemId, OccDate, OccPlan, UseAmount, AppSettlementFeeRate, SettlementFee, AddUpFlg, AddUpFixedMonth, OemClaimedSeq, CancelFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OemId ";
        $sql .= " , :OccDate ";
        $sql .= " , :OccPlan ";
        $sql .= " , :UseAmount ";
        $sql .= " , :AppSettlementFeeRate ";
        $sql .= " , :SettlementFee ";
        $sql .= " , :AddUpFlg ";
        $sql .= " , :AddUpFixedMonth ";
        $sql .= " , :OemClaimedSeq ";
        $sql .= " , :CancelFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OemId' => $data['OemId'],
                ':OccDate' => $data['OccDate'],
                ':OccPlan' => $data['OccPlan'],
                ':UseAmount' => $data['UseAmount'],
                ':AppSettlementFeeRate' => $data['AppSettlementFeeRate'],
                ':SettlementFee' => $data['SettlementFee'],
                ':AddUpFlg' => $data['AddUpFlg'],
                ':AddUpFixedMonth' => $data['AddUpFixedMonth'],
                ':OemClaimedSeq' => $data['OemClaimedSeq'],
                ':CancelFlg' => $data['CancelFlg'],
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
	 * 指定OEMIDのOEM決済手数料データを取得する。
	 *
	 * @param string $OemId OEMID
	 * @return ResultInterface
	 */
	public function findOem($OemId)
	{
        $sql = " SELECT * FROM T_OemSettlementFee WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $OemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定OEMIDのOEM決済手数料データを取得する。
	 *
	 * @param string $oemId OEMID
	 * @return ResultInterface
	 */
	public function findOem2($oemId)
	{
        return $this->findOem($oemId);
	}

	protected function isPrimaryKey($colName) {
		$primaries = $this->_primary;
		if(is_array($primaries)) {
			return in_array($colName, $primaries);
		} else {
			return $colName == $primaries;
		}
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM T_OemSettlementFee WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemSettlementFee ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   OccDate = :OccDate ";
        $sql .= " ,   OccPlan = :OccPlan ";
        $sql .= " ,   UseAmount = :UseAmount ";
        $sql .= " ,   AppSettlementFeeRate = :AppSettlementFeeRate ";
        $sql .= " ,   SettlementFee = :SettlementFee ";
        $sql .= " ,   AddUpFlg = :AddUpFlg ";
        $sql .= " ,   AddUpFixedMonth = :AddUpFixedMonth ";
        $sql .= " ,   OemClaimedSeq = :OemClaimedSeq ";
        $sql .= " ,   CancelFlg = :CancelFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':OemId' => $row['OemId'],
                ':OccDate' => $row['OccDate'],
                ':OccPlan' => $row['OccPlan'],
                ':UseAmount' => $row['UseAmount'],
                ':AppSettlementFeeRate' => $row['AppSettlementFeeRate'],
                ':SettlementFee' => $row['SettlementFee'],
                ':AddUpFlg' => $row['AddUpFlg'],
                ':AddUpFixedMonth' => $row['AddUpFixedMonth'],
                ':OemClaimedSeq' => $row['OemClaimedSeq'],
                ':CancelFlg' => $row['CancelFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}
	/**
     * OEM決済手数料データをキャンセル中にする。
     *
     * @param int $oseq 注文SEQ
     * @param $opId 担当者
     */
    public function toCanceling($oseq, $opId)
    {
        $this->setCancelFlg($oseq, 1, $opId);
    }

    /**
     * OEM決済手数料データをキャンセルにする。
     *
     * @param int $oseq 注文SEQ
     * @param $opId 担当者
     */
    public function toCanceled($oseq, $opId)
    {
        $this->setCancelFlg($oseq, 2, $opId);
    }

    /**
     * OEM決済手数料データのキャンセルフラッグを設定する。
     *
     * @param int $oseq 注文SEQ
     * @param int $flg キャンセルフラッグ
     * @param $opId 担当者
     */
    public function setCancelFlg($oseq, $flg, $opId)
    {
        $sql = " UPDATE T_OemSettlementFee SET CancelFlg = :CancelFlg, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CancelFlg' => $flg,
                ':OrderSeq' => $oseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定された条件でレコードを更新する。
     *
     * @param array $data 更新内容
     * @param array $conditionArray
     */
    public function saveUpdateWhere($data, $conditionArray)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_OemSettlementFee WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute($prm);

        foreach ($ri AS $row) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = $value;
                }
            }

            // 指定されたレコードを更新する
            $this->saveUpdate($row, $row['Seq']);
        }
    }

    /**
     * 指定注文SEQのOEM決済手数料データを取得する。
     *
     * @param int $orderSeq 注文SEQ
     * @param vool $isValid 有効フラグ
     * @return ResultInterface
     */
    public function findOrder($orderSeq, $isValid = false)
    {
        $sql  = " SELECT * FROM T_OemSettlementFee WHERE OrderSeq = :OrderSeq ";

        if ($isValid) {
            $sql .= ' AND ValidFlg = 1 ';
        }

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
    }
}
